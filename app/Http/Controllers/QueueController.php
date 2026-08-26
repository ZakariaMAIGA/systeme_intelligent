<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\Desk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class QueueController extends Controller
{
    /**
     * Display the Queue Management Dashboard.
     */
    public function index(Request $request)
    {
        // 1. Force active role strictly based on authenticated user's role
        $userRole = auth()->user()->role;
        if ($userRole === 'admin') {
            if ($request->has('role')) {
                Session::put('active_role', $request->input('role'));
            }
            $activeRole = Session::get('active_role', 'admin');
        } else {
            $activeRole = $userRole;
        }
        
        if ($request->has('desk_id')) {
            Session::put('selected_desk_id', (int) $request->input('desk_id'));
        }

        $selectedDeskId = Session::get('selected_desk_id', 1);

        // 2. Fetch Data
        $services = Service::all();
        $desks = Desk::all();
        $tickets = Ticket::orderBy('created_at', 'desc')->get();
        $users = \App\Models\User::all();

        // 3. Dynamic Service Metrics
        $services = $services->map(function ($srv) use ($tickets, $desks) {
            $waitingTickets = $tickets->where('service_id', $srv->id)->where('status', 'en_attente');
            $srv->waitingCount = $waitingTickets->count();
            
            $activeDesksCount = $desks->where('service_id', $srv->id)->where('active', true)->count();
            $effectiveDesks = $activeDesksCount ?: 1;
            
            $srv->calculatedWait = (int) round(($srv->waitingCount * $srv->temps_moyen_traitement) / $effectiveDesks);
            $srv->activeDesksCount = $activeDesksCount;
            
            $srv->status = $srv->calculatedWait >= 40 ? 'surcharge' : 'normal';
            return $srv;
        });

        // 4. Generate AI Recommendations
        $aiRecommendations = [];
        foreach ($services as $srv) {
            if ($srv->status === 'surcharge') {
                $closedDesk = $desks->where('active', false)->first();
                $aiRecommendations[] = [
                    'id' => "rec-surcharge-{$srv->id}",
                    'type' => 'warning',
                    'title' => "Surcharge en {$srv->nom}",
                    'message' => "Attence estimée de {$srv->calculatedWait} min ({$srv->waitingCount} patients en attente).",
                    'actionLabel' => $closedDesk ? "Ouvrir {$closedDesk->name} pour ce service" : "Réaffecter un guichet",
                    'actionUrl' => route('ia.apply', ['action' => 'resolve_surcharge', 'service_id' => $srv->id])
                ];
            }
            if ($srv->waitingCount > 0 && $srv->activeDesksCount === 0) {
                $aiRecommendations[] = [
                    'id' => "rec-closed-{$srv->id}",
                    'type' => 'danger',
                    'title' => "Poste requis pour {$srv->nom}",
                    'message' => "{$srv->waitingCount} patient(s) en attente, mais aucun guichet n'est ouvert.",
                    'actionLabel' => "Ouvrir un guichet",
                    'actionUrl' => route('ia.apply', ['action' => 'open_first_desk', 'service_id' => $srv->id])
                ];
            }
        }

        // 5. Active Patient Ticket Information
        $activePatientTicketId = Session::get('active_patient_ticket_id', '');
        $patientTicket = null;
        $patientPosition = 0;
        $patientWaitTime = 0;
        
        if ($activePatientTicketId) {
            $patientTicket = Ticket::find($activePatientTicketId);
            if ($patientTicket && $patientTicket->status === 'en_attente') {
                $patientPosition = $this->getTicketPosition($patientTicket);
                $srv = $services->where('id', $patientTicket->service_id)->first();
                $activeDesks = $srv ? $srv->activeDesksCount : 1;
                $patientWaitTime = (int) round(($patientPosition * ($srv ? $srv->temps_moyen_traitement : 15)) / ($activeDesks ?: 1));
            }
        }

        // 6. Global Stats
        $stats = [
            'total_tickets' => Ticket::count(),
            'patients_consulted' => Ticket::where('status', 'termine')->count(),
            'avg_wait_time' => $this->getAverageWaitTime(),
        ];

        // 7. Called/Current board
        $liveMonitor = Ticket::whereIn('status', ['appele', 'en_cours'])
            ->orderBy('called_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($t) use ($desks) {
                $t->deskName = $desks->where('id', $t->desk_id)->first()?->name ?? 'Bureau';
                return $t;
            });

        // 8. Session-based logs & notifications
        $simulationLogs = Session::get('simulation_logs', []);
        $notification = Session::get('notification', null);

        return view('dashboard', compact(
            'activeRole', 'selectedDeskId', 'services', 'desks', 'tickets', 
            'aiRecommendations', 'patientTicket', 'patientPosition', 'patientWaitTime',
            'stats', 'liveMonitor', 'simulationLogs', 'notification', 'users'
        ));
    }

    /**
     * Take a new virtual ticket.
     */
    public function takeTicket(Request $request)
    {
        if (!in_array(auth()->user()->role, ['patient', 'agent_accueil', 'admin'])) {
            abort(403, 'Action non autorisée.');
        }
        $request->validate([
            'patient_name' => 'required|string|max:100',
            'service_id' => 'required|exists:services,id',
            'priority' => 'required|in:normal,prioritaire',
            'patient_folder' => 'nullable|string|max:50',
        ]);

        $service = Service::find($request->input('service_id'));
        
        // Count tickets for today
        $count = Ticket::where('service_id', $service->id)->count() + 1;
        $numero = $service->code . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $ticket = Ticket::create([
            'numero' => $numero,
            'patient_name' => $request->input('patient_name'),
            'patient_folder' => $request->input('patient_folder'),
            'service_id' => $service->id,
            'status' => 'en_attente',
            'priority' => $request->input('priority'),
        ]);

        $this->addLog("Ticket généré : {$numero} pour {$ticket->patient_name} (" . ($ticket->priority === 'prioritaire' ? 'Prioritaire' : 'Normal') . ")");

        $activeRole = Session::get('active_role', 'patient');
        if ($activeRole === 'patient') {
            Session::put('active_patient_ticket_id', $ticket->id);
            Session::flash('notification', ['text' => "Votre ticket {$numero} a été généré avec succès !", 'type' => 'success']);
        } else {
            Session::flash('notification', ['text' => "Ticket {$numero} créé pour le patient {$ticket->patient_name}.", 'type' => 'success']);
        }

        return redirect()->back();
    }

    /**
     * Call the next patient in queue for a desk.
     */
    public function callNext(Request $request, $deskId)
    {
        if (!in_array(auth()->user()->role, ['personnel_medical', 'admin'])) {
            abort(403, 'Action non autorisée.');
        }
        $desk = Desk::find($deskId);
        if (!$desk || !$desk->service_id) {
            return redirect()->back();
        }

        // Fetch waiting tickets
        $nextTicket = Ticket::where('service_id', $desk->service_id)
            ->where('status', 'en_attente')
            ->orderByRaw("CASE WHEN priority = 'prioritaire' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$nextTicket) {
            Session::flash('notification', ['text' => "Aucun patient en attente pour ce service.", 'type' => 'info']);
            return redirect()->back();
        }

        $nextTicket->update([
            'status' => 'appele',
            'called_at' => now(),
            'desk_id' => $desk->id,
        ]);

        $this->addLog("{$desk->name} appelle le ticket {$nextTicket->numero}.");
        
        // Flash trigger for vocal call in Javascript
        Session::flash('voice_call', [
            'code' => $nextTicket->numero,
            'desk' => $desk->name
        ]);

        return redirect()->back();
    }

    /**
     * Update ticket status (en_cours, termine, absent, annule).
     */
    public function updateStatus(Request $request, $ticketId, $status)
    {
        // Patient can only cancel their own ticket
        if ($status === 'annule' || $status === 'annulé') {
            if (auth()->user()->role === 'patient' && Session::get('active_patient_ticket_id') != $ticketId) {
                abort(403, 'Action non autorisée.');
            }
        } else {
            // Other status updates are restricted to medical staff and admins
            if (!in_array(auth()->user()->role, ['personnel_medical', 'admin'])) {
                abort(403, 'Action non autorisée.');
            }
        }
        $ticket = Ticket::find($ticketId);
        if (!$ticket) return redirect()->back();

        $updateData = ['status' => $status];
        if ($status === 'en_cours') {
            $updateData['started_at'] = now();
        } elseif ($status === 'termine') {
            $updateData['finished_at'] = now();
        }

        $ticket->update($updateData);
        $statusName = $status === 'termine' ? 'terminé' : ($status === 'en_cours' ? 'en consultation' : $status);
        $this->addLog("Ticket {$ticket->numero} marqué comme {$statusName}.");

        // Clear active ticket of patient if cancelled
        if ($status === 'annule' && Session::get('active_patient_ticket_id') == $ticketId) {
            Session::forget('active_patient_ticket_id');
        }

        return redirect()->back();
    }

    /**
     * Transfer patient to another service.
     */
    public function transfer(Request $request, $ticketId)
    {
        if (!in_array(auth()->user()->role, ['personnel_medical', 'admin'])) {
            abort(403, 'Action non autorisée.');
        }
        $request->validate([
            'target_service_id' => 'required|exists:services,id',
            'desk_id' => 'required|exists:desks,id',
        ]);

        $ticket = Ticket::find($ticketId);
        $desk = Desk::find($request->input('desk_id'));
        if (!$ticket || !$desk) return redirect()->back();

        // 1. Finish current ticket
        $ticket->update([
            'status' => 'termine',
            'finished_at' => now(),
        ]);

        // 2. Generate new ticket in target service
        $targetService = Service::find($request->input('target_service_id'));
        $count = Ticket::where('service_id', $targetService->id)->count() + 1;
        $numero = $targetService->code . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        Ticket::create([
            'numero' => $numero,
            'patient_name' => $ticket->patient_name,
            'patient_folder' => $ticket->patient_folder,
            'service_id' => $targetService->id,
            'status' => 'en_attente',
            'priority' => $ticket->priority,
        ]);

        $this->addLog("Patient {$ticket->patient_name} transféré du {$desk->name} vers {$targetService->nom}.");
        Session::flash('notification', ['text' => "Patient transféré avec succès vers le service {$targetService->nom} (Ticket {$numero}).", 'type' => 'success']);

        return redirect()->back();
    }

    /**
     * Open/Close guichet or change service assignment.
     */
    public function configureDesk(Request $request, $deskId)
    {
        if (!in_array(auth()->user()->role, ['responsable', 'admin'])) {
            abort(403, 'Action non autorisée.');
        }
        $desk = Desk::find($deskId);
        if (!$desk) return redirect()->back();

        if ($request->has('active')) {
            $desk->active = $request->input('active') == '1';
            $this->addLog("[Admin] {$desk->name} a été " . ($desk->active ? 'ACTIVÉ' : 'DESACTIVÉ') . ".");
        }
        if ($request->has('service_id')) {
            $desk->service_id = $request->input('service_id') ?: null;
            $srvName = $desk->service_id ? Service::find($desk->service_id)->nom : 'Aucun';
            $this->addLog("[Admin] {$desk->name} réaffecté au service : {$srvName}.");
        }
        $desk->save();

        return redirect()->back();
    }

    /**
     * Apply AI Recommendation.
     */
    public function applyRecommendation($action, $serviceId)
    {
        if (!in_array(auth()->user()->role, ['admin'])) {
            abort(403, 'Action non autorisée.');
        }
        $srv = Service::find($serviceId);
        if (!$srv) return redirect()->back();

        if ($action === 'resolve_surcharge' || $action === 'open_first_desk') {
            $closedDesk = Desk::where('active', false)->first();
            if ($closedDesk) {
                $closedDesk->update([
                    'active' => true,
                    'service_id' => $srv->id,
                ]);
                $this->addLog("[IA] Recommandation appliquée : {$closedDesk->name} activé pour désengorger {$srv->nom}.");
            } else {
                // Reallocate busiest desk
                $quietDesk = Desk::where('active', true)
                    ->where('service_id', '!=', $srv->id)
                    ->get()
                    ->filter(function ($d) {
                        return (Service::find($d->service_id)->calculatedWait ?? 0) < 10;
                      })
                    ->first();
                if ($quietDesk) {
                  $quietDesk->update(['service_id' => $srv->id]);
                  $this->addLog("[IA] Recommandation appliquée : {$quietDesk->name} réaffecté au service {$srv->nom}.");
                }
            }
        }

        Session::flash('notification', ['text' => "Recommandation appliquée avec succès !", 'type' => 'success']);
        return redirect()->back();
    }

    /**
     * Simulate progress (one tick of the queue simulation).
     */
    public function simulateProgress()
    {
        if (!in_array(auth()->user()->role, ['admin', 'responsable'])) {
            abort(403, 'Action non autorisée.');
        }
        // 1. Chance of new patient arrival (45% chance)
        if (rand(0, 100) < 45) {
            $randomSrv = Service::inRandomOrder()->first();
            $names = ['Oumar Dembélé', 'Fanta Bagayoko', 'Salif Keïta', 'Aminata Konaté', 'Souleymane Sidibé', 'Rokiatou Maïga', 'Bakary Samaké', 'Kadidia Sangaré'];
            $randomName = $names[array_rand($names)];
            $isUrgent = rand(0, 100) < 15; // 15% urgent cases

            $count = Ticket::where('service_id', $randomSrv->id)->count() + 1;
            $numero = $randomSrv->code . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            Ticket::create([
                'numero' => $numero,
                'patient_name' => $randomName,
                'service_id' => $randomSrv->id,
                'status' => 'en_attente',
                'priority' => $isUrgent ? 'prioritaire' : 'normal',
            ]);

            $this->addLog("[Simulateur] Nouveau patient enregistré : {$numero} ({$randomName}).");
        }

        // 2. Active desks process patients
        $desks = Desk::where('active', true)->get();
        foreach ($desks as $desk) {
            $activeTicket = Ticket::where('desk_id', $desk->id)->whereIn('status', ['appele', 'en_cours'])->first();

            if ($activeTicket) {
                if ($activeTicket->status === 'en_cours') {
                    // 40% chance of finishing
                    if (rand(0, 100) < 40) {
                        $activeTicket->update([
                            'status' => 'termine',
                            'finished_at' => now(),
                        ]);
                        $this->addLog("[Simulateur] {$desk->name} a terminé le traitement pour le ticket {$activeTicket->numero}.");
                    }
                } elseif ($activeTicket->status === 'appele') {
                    // 60% chance patient arrives
                    if (rand(0, 100) < 60) {
                        $activeTicket->update([
                            'status' => 'en_cours',
                            'started_at' => now(),
                        ]);
                        $this->addLog("[Simulateur] Patient {$activeTicket->numero} s'est présenté au {$desk->name}. Début de prise en charge.");
                    }
                }
            } else {
                // Desk is free, 50% chance of calling next patient
                if (rand(0, 100) < 50 && $desk->service_id) {
                    $nextTicket = Ticket::where('service_id', $desk->service_id)
                        ->where('status', 'en_attente')
                        ->orderByRaw("CASE WHEN priority = 'prioritaire' THEN 0 ELSE 1 END")
                        ->orderBy('created_at', 'asc')
                        ->first();

                    if ($nextTicket) {
                        $nextTicket->update([
                            'status' => 'appele',
                            'called_at' => now(),
                            'desk_id' => $desk->id,
                        ]);
                        $this->addLog("[Simulateur] {$desk->name} appelle le ticket {$nextTicket->numero}.");
                        
                        // Set vocal call trigger
                        Session::flash('voice_call', [
                            'code' => $nextTicket->numero,
                            'desk' => $desk->name
                        ]);
                    }
                }
            }
        }

        Session::flash('notification', ['text' => "Simulation avancée d'une étape.", 'type' => 'info']);
        return redirect()->back();
    }

    /**
     * Reset database.
     */
    public function reset()
    {
        if (!in_array(auth()->user()->role, ['admin'])) {
            abort(403, 'Action non autorisée.');
        }
        Ticket::truncate();
        
        // Re-run the seeder
        $seeder = new \Database\Seeders\HospitalSeeder();
        $seeder->run();

        Session::forget('active_patient_ticket_id');
        Session::forget('simulation_logs');

        $this->addLog("Le système de tickets a été entièrement réinitialisé par l'administrateur.");
        Session::flash('notification', ['text' => "Base de données réinitialisée.", 'type' => 'success']);

        return redirect()->back();
    }

    // --- Private Helper Methods ---

    private function getTicketPosition($ticket)
    {
        $queue = Ticket::where('service_id', $ticket->service_id)
            ->where('status', 'en_attente')
            ->get();

        $sortedQueue = $queue->sort(function ($a, $b) {
            if ($a->priority === 'prioritaire' && $b->priority !== 'prioritaire') return -1;
            if ($a->priority !== 'prioritaire' && $b->priority === 'prioritaire') return 1;
            return strcmp($a->created_at, $b->created_at);
        })->values();

        $index = $sortedQueue->search(function ($item) use ($ticket) {
            return $item->id === $ticket->id;
        });

        return $index !== false ? $index + 1 : 0;
    }

    private function getAverageWaitTime()
    {
        $completedTickets = Ticket::where('status', 'termine')->get();
        if ($completedTickets->isEmpty()) {
            return 12; // default
        }

        $totalMinutes = 0;
        foreach ($completedTickets as $t) {
            $totalMinutes += $t->called_at->diffInMinutes($t->created_at);
        }

        return (int) round($totalMinutes / $completedTickets->count());
    }

    private function addLog($text)
    {
        $logs = Session::get('simulation_logs', []);
        array_unshift($logs, [
            'id' => uniqid(),
            'text' => $text,
            'time' => now()->format('H:i:s'),
        ]);
        Session::put('simulation_logs', array_slice($logs, 0, 15));
    }

    /**
     * Update user role (admin only).
     */
    public function updateUserRole(Request $request, $userId)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Action non autorisée.');
        }

        $request->validate([
            'role' => 'required|in:patient,agent_accueil,personnel_medical,responsable,admin',
        ]);

        $user = \App\Models\User::findOrFail($userId);
        $user->update(['role' => $request->input('role')]);

        $this->addLog("[Admin] Rôle de {$user->name} modifié en " . ucfirst($request->input('role')) . ".");
        Session::flash('notification', ['text' => "Le rôle de {$user->name} a été modifié avec succès.", 'type' => 'success']);

        return redirect()->back();
    }
}
