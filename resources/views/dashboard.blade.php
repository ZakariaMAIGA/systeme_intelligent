<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hôpital du Point G - Gestion de Files d'Attente</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <style>
        /* Small inline fixes */
        .header-logo {
            animation: heartBeat 2s infinite;
        }
        @keyframes heartBeat {
            0% { transform: scale(1); }
            14% { transform: scale(1.1); }
            28% { transform: scale(1); }
            42% { transform: scale(1.1); }
            70% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Navbar -->
        <header class="navbar">
            <a href="{{ route('dashboard') }}" class="navbar-brand">
                <!-- SVG Heartbeat activity icon -->
                <svg class="header-logo" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                <span>Hôpital du Point G <span>• Files d'Attente</span></span>
            </a>
            
            <div class="navbar-actions">
                <!-- Logged in user info -->
                @auth
                    <div style="font-size: 0.85rem; color: var(--text-secondary); text-align: right; display: flex; flex-direction: column; justify-content: center; margin-right: 0.5rem;">
                        <span style="font-weight: bold; color: var(--text-primary);">{{ auth()->user()->name }}</span>
                        <span style="font-size: 0.75rem; color: var(--primary); font-weight: 600;">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
                    </div>
                @endauth

                <!-- Role switcher (Admin sandbox) -->
                @if(auth()->user()->role === 'admin')
                <div class="role-selector">
                    <a href="{{ route('dashboard', ['role' => 'patient']) }}" class="role-tab {{ $activeRole === 'patient' ? 'active' : '' }}">
                        👤 Patient
                    </a>
                    <a href="{{ route('dashboard', ['role' => 'agent_accueil']) }}" class="role-tab {{ $activeRole === 'agent_accueil' ? 'active' : '' }}">
                        🏢 Accueil
                    </a>
                    <a href="{{ route('dashboard', ['role' => 'personnel_medical']) }}" class="role-tab {{ $activeRole === 'personnel_medical' ? 'active' : '' }}">
                        🩺 Personnel
                    </a>
                    <a href="{{ route('dashboard', ['role' => 'responsable']) }}" class="role-tab {{ $activeRole === 'responsable' ? 'active' : '' }}">
                        📊 Stats/Admin
                    </a>
                    <a href="{{ route('dashboard', ['role' => 'admin']) }}" class="role-tab {{ $activeRole === 'admin' ? 'active' : '' }}">
                        ⚙️ IA
                    </a>
                </div>
                @endif

                <!-- Theme switcher -->
                <button class="theme-switch" onclick="toggleTheme()" title="Basculer le thème">
                    <svg id="theme-icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                    <svg id="theme-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="M6.34 17.66l-1.41 1.41"/><path d="M19.07 4.93l-1.41 1.41"/></svg>
                </button>

                <!-- Log out button -->
                @auth
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0; display: inline-flex;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.5rem; display: inline-flex; align-items: center; justify-content: center; border-color: var(--border-color);" title="Se déconnecter">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        </button>
                    </form>
                @endauth
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            
            <!-- Dashboard Grid -->
            <div class="grid-cols-3">
                
                <!-- Left Column: Active Role Panel -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    
                    <!-- --- ROLE: PATIENT --- -->
                    @if($activeRole === 'patient')
                        <div class="dashboard-header">
                          <h1>Espace Patient</h1>
                          <p>Consultez les temps d'attente et réservez votre ticket virtuel sans faire la queue physiquement.</p>
                        </div>

                        <!-- Active Ticket status if exists -->
                        @if($patientTicket)
                            @php
                                $srv = $services->firstWhere('id', $patientTicket->service_id);
                            @endphp
                            <div class="glass-panel ticket-container">
                                <div class="ticket-header">Mon Ticket Actuel</div>
                                <div class="ticket-number">{{ $patientTicket->numero }}</div>
                                <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;">
                                    Service : {{ $srv?->nom }}
                                </div>
                                <div style="margin-bottom: 1rem;">
                                    Statut : 
                                    @if($patientTicket->status === 'en_attente')
                                        <span class="badge badge-warning">En attente</span>
                                    @elseif($patientTicket->status === 'appele')
                                        <span class="badge badge-info voice-indicator">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 2px;"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg> Appelé
                                        </span>
                                    @elseif($patientTicket->status === 'en_cours')
                                        <span class="badge badge-success">En consultation</span>
                                    @elseif($patientTicket->status === 'termine')
                                        <span class="badge badge-success" style="background-color: var(--success-bg); color: var(--success-text)">Terminé</span>
                                    @elseif($patientTicket->status === 'absent')
                                        <span class="badge badge-danger">Absent</span>
                                    @elseif($patientTicket->status === 'annule')
                                        <span class="badge badge-danger" style="opacity: 0.5;">Annulé</span>
                                    @endif
                                </div>

                                @if($patientTicket->status === 'en_attente')
                                    <div style="width: 100%;">
                                        <div class="alert-box alert-info" style="justify-content: center;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Restez à proximité. Nous vous appellerons à votre tour.
                                        </div>
                                        <div class="ticket-divider"></div>
                                        <div class="stats-row">
                                            <div class="stat-item">
                                                <div class="stat-value">{{ $patientPosition }}</div>
                                                <div class="stat-label">Position dans la file</div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-value">{{ max(0, $patientPosition - 1) }}</div>
                                                <div class="stat-label">Personnes devant vous</div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-value" style="color: var(--primary);">{{ $patientWaitTime }} min</div>
                                                <div class="stat-label">Attente estimée</div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($patientTicket->status === 'appele')
                                    @php
                                        $desk = $desks->firstWhere('id', $patientTicket->desk_id);
                                    @endphp
                                    <div class="alert-box alert-warning" style="flex-direction: column; align-items: center; width: 100%;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--warning-text);"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        <div style="font-weight: 700; font-size: 1.2rem; margin-top: 0.5rem;">C'est votre tour !</div>
                                        <div style="text-align: center; margin-top: 0.25rem;">
                                            Veuillez vous présenter au <strong>{{ $desk?->name ?? 'Guichet d\'appel' }}</strong> immédiatement.
                                        </div>
                                    </div>
                                @elseif($patientTicket->status === 'en_cours')
                                    @php
                                        $desk = $desks->firstWhere('id', $patientTicket->desk_id);
                                    @endphp
                                    <div class="alert-box alert-info" style="flex-direction: column; align-items: center; width: 100%;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #2563eb;"><path d="M4.828 17.172a8 8 0 1 0 11.314-11.314l-5.656 5.656-5.658-5.656a8 8 0 0 0 0 11.314z"/></svg>
                                        <div style="font-weight: 700; font-size: 1.1rem; margin-top: 0.5rem;">Consultation en cours</div>
                                        <div style="text-align: center; margin-top: 0.25rem;">
                                            Vous êtes actuellement en consultation au <strong>{{ $desk?->name }}</strong>.
                                        </div>
                                    </div>
                                @elseif($patientTicket->status === 'termine')
                                    <div class="alert-box alert-info" style="background-color: var(--success-bg); border-color: var(--success); color: var(--success-text); flex-direction: column; align-items: center; width: 100%;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #059669;"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                        <div style="font-weight: 700; font-size: 1.1rem; margin-top: 0.5rem;">Consultation terminée</div>
                                        <div style="text-align: center; margin-top: 0.25rem;">
                                            Merci de votre visite à l'Hôpital du Point G. Bon rétablissement !
                                        </div>
                                    </div>
                                @endif

                                <form action="{{ route('ticket.status', ['ticket_id' => $patientTicket->id, 'status' => 'annule']) }}" method="POST" style="margin-top: 1.5rem; width: 100%; text-align: center;">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger-text); border-color: rgba(239,68,68,0.2); display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg> Annuler mon ticket
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Services Grid -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <h2 style="font-family: var(--font-heading);">Prendre un ticket virtuel</h2>
                            <div class="dashboard-grid">
                                @foreach($services as $srv)
                                    <div class="service-card glass-card">
                                        <div class="service-card-header">
                                            <div>
                                                <div class="service-title">
                                                    <span>{{ $srv->icon }}</span> {{ $srv->nom }}
                                                </div>
                                                <div class="service-desc">{{ $srv->description }}</div>
                                            </div>
                                            @if($srv->status === 'surcharge')
                                                <span class="badge badge-danger">Surcharge</span>
                                            @endif
                                        </div>

                                        <div class="service-stats">
                                            <div>
                                                <div class="service-stat-label">En attente</div>
                                                <div class="service-stat-val">{{ $srv->waitingCount }} pers.</div>
                                            </div>
                                            <div>
                                                <div class="service-stat-label">Temps estimé</div>
                                                <div class="service-stat-val" style="color: {{ $srv->status === 'surcharge' ? 'var(--danger)' : 'var(--primary)' }};">
                                                    {{ $srv->calculatedWait }} min
                                                </div>
                                            </div>
                                        </div>

                                        <form action="{{ route('ticket.take') }}" method="POST" style="margin-top: 0.5rem;">
                                            @csrf
                                            <input type="hidden" name="service_id" value="{{ $srv->id }}">
                                            <input type="hidden" name="priority" value="normal">
                                            <input type="hidden" name="patient_name" value="{{ auth()->user()->name }}">
                                            <button type="submit" class="btn btn-primary" style="width: 100%; gap: 0.25rem;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Prendre un Ticket
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- --- ROLE: ACCUEIL --- -->
                    @if($activeRole === 'agent_accueil' || $activeRole === 'accueil')
                        <div class="dashboard-header">
                          <h1>Espace Accueil & Enregistrement</h1>
                          <p>Enregistrez les patients physiques arrivant à l'hôpital et imprimez-leur un ticket papier.</p>
                        </div>

                        <div class="glass-panel">
                            <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                Enregistrer un nouveau Patient
                            </h2>
                            
                            <form action="{{ route('ticket.take') }}" method="POST">
                                @csrf
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div class="form-group">
                                        <label class="form-label">Nom Complet du Patient</label>
                                        <input type="text" name="patient_name" class="form-input" placeholder="Ex: Fanta Keïta" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Numéro de Dossier Médical</label>
                                        <input type="text" name="patient_folder" class="form-input" placeholder="Ex: PTG-89510">
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div class="form-group">
                                        <label class="form-label">Service d'Orientation</label>
                                        <select name="service_id" class="form-select">
                                            @foreach($services as $srv)
                                                <option value="{{ $srv->id }}">{{ $srv->nom }} ({{ $srv->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Niveau de Priorité</label>
                                        <select name="priority" class="form-select">
                                            <option value="normal">Normal (Ordre d'arrivée)</option>
                                            <option value="prioritaire">Prioritaire (Personne âgée, Urgence, Enceinte)</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; gap: 0.5rem;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
                                    Générer & Imprimer le Ticket
                                </button>
                            </form>
                        </div>

                        <div>
                            <h2 style="margin-bottom: 1rem;">Files d'attente actuelles</h2>
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Tickets en attente</th>
                                            <th>Temps moyen estimé</th>
                                            <th>Guichets Actifs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($services as $srv)
                                            <tr>
                                                <td><strong>{{ $srv->icon }} {{ $srv->nom }}</strong></td>
                                                <td>{{ $srv->waitingCount }} patients</td>
                                                <td style="color: {{ $srv->status === 'surcharge' ? 'var(--danger)' : 'var(--primary)' }};">
                                                    {{ $srv->calculatedWait }} min
                                                </td>
                                                <td>{{ $srv->activeDesksCount }} poste(s) ouvert(s)</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- --- ROLE: PERSONNEL --- -->
                    @if($activeRole === 'personnel_medical' || $activeRole === 'medical')
                        <div class="dashboard-header">
                          <h1>Espace Personnel Médical / Praticien</h1>
                          <p>Sélectionnez votre guichet pour appeler et traiter les patients de votre service.</p>
                        </div>

                        <!-- Desk Selector -->
                        <div class="glass-panel" style="padding: 1.5rem;">
                            <form action="{{ route('dashboard') }}" method="GET" id="desk-form">
                                <input type="hidden" name="role" value="personnel_medical">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Sélectionner votre Poste / Cabine de Consultation</label>
                                    <select name="desk_id" class="form-select" onchange="document.getElementById('desk-form').submit()">
                                        @foreach($desks->where('active', true) as $d)
                                            @php
                                                $dService = $services->firstWhere('id', $d->service_id);
                                            @endphp
                                            <option value="{{ $d->id }}" {{ $selectedDeskId == $d->id ? 'selected' : '' }}>
                                                {{ $d->name }} — {{ $dService?->nom ?? 'Aucun' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                        </div>

                        <!-- Desk Controls -->
                        @php
                            $desk = $desks->firstWhere('id', $selectedDeskId);
                        @endphp
                        @if($desk)
                            @php
                                $dService = $services->firstWhere('id', $desk->service_id);
                                $activeTicket = $tickets->where('desk_id', $desk->id)->whereIn('status', ['appele', 'en_cours'])->first();
                            @endphp
                            <div class="glass-panel" style="display: flex; flex-direction: column; gap: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h2 style="margin: 0;">Gestion du {{ $desk->name }}</h2>
                                        <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                            Service assigné : <strong>{{ $dService?->nom ?? 'Non spécifié' }}</strong>
                                        </div>
                                    </div>
                                    <span class="badge {{ $activeTicket ? 'badge-info' : 'badge-success' }}">
                                        {{ $activeTicket ? 'Occupé' : 'Libre' }}
                                    </span>
                                </div>

                                <div class="ticket-divider" style="margin: 0;"></div>

                                @if(!$activeTicket)
                                    <div style="text-align: center; padding: 2rem 1rem;">
                                        <div style="color: var(--text-muted); margin-bottom: 1.5rem;">
                                            Aucun patient en consultation à ce guichet.
                                        </div>
                                        <form action="{{ route('desk.call', ['desk_id' => $desk->id]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; gap: 0.5rem;">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                                                Appeler le Prochain Patient
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                                        <div class="ticket-container" style="padding: 1.5rem; border-style: solid;">
                                            <div class="ticket-header">Patient Appelé</div>
                                            <div class="ticket-number" style="font-size: 2.5rem;">{{ $activeTicket->numero }}</div>
                                            <div style="font-weight: 700; font-size: 1.1rem;">{{ $activeTicket->patient_name }}</div>
                                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                                Priorité : 
                                                @if($activeTicket->priority === 'prioritaire')
                                                    <span style="color: var(--priority); font-weight: bold;">PRIORITAIRE</span>
                                                @else
                                                    Normal
                                                @endif
                                            </div>
                                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                                                Appelé à : {{ $activeTicket->called_at ? $activeTicket->called_at->format('H:i:s') : '--:--' }}
                                            </div>
                                            <div style="margin-top: 0.75rem;">
                                                Statut : 
                                                @if($activeTicket->status === 'appele')
                                                    <span class="badge badge-info voice-indicator">Appelé</span>
                                                @else
                                                    <span class="badge badge-success">En consultation</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                            @if($activeTicket->status === 'appele')
                                                <form action="{{ route('ticket.status', ['ticket_id' => $activeTicket->id, 'status' => 'en_cours']) }}" method="POST" style="width: 100%;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success" style="width: 100%; gap: 0.25rem;">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg> Commencer
                                                    </button>
                                                </form>
                                            @elseif($activeTicket->status === 'en_cours')
                                                <form action="{{ route('ticket.status', ['ticket_id' => $activeTicket->id, 'status' => 'termine']) }}" method="POST" style="width: 100%;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary" style="width: 100%; gap: 0.25rem;">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Terminer
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('ticket.status', ['ticket_id' => $activeTicket->id, 'status' => 'absent']) }}" method="POST" style="width: 100%;">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" style="width: 100%; gap: 0.25rem;">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Absent
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Transfer -->
                                        <div class="glass-card" style="margin-top: 1rem; background-color: var(--bg-primary);">
                                            <h3 style="font-size: 1rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                                Transférer vers un autre service
                                            </h3>
                                            <form action="{{ route('ticket.transfer', ['ticket_id' => $activeTicket->id]) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="desk_id" value="{{ $desk->id }}">
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <select name="target_service_id" class="form-select" style="flex: 1; padding: 0.5rem;">
                                                        @foreach($services->where('id', '!=', $desk->service_id) as $s)
                                                            <option value="{{ $s->id }}">{{ $s->nom }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-primary btn-sm">Transférer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif

                    <!-- --- ROLE: RESPONSABLE --- -->
                    @if($activeRole === 'responsable')
                        <div class="dashboard-header">
                          <h1>Statistiques & Gestion de l'Hôpital</h1>
                          <p>Supervisez l'affluence en temps réel, ouvrez des guichets et modifiez les affectations des médecins.</p>
                        </div>

                        <!-- Stats Row -->
                        <div class="dashboard-grid">
                            <div class="stats-card-mini">
                                <div class="stats-card-icon" style="background-color: var(--primary-glow); color: var(--primary);">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </div>
                                <div class="stats-card-info">
                                    <div class="stats-card-val">{{ $stats['total_tickets'] }}</div>
                                    <div class="stats-card-lbl">Tickets générés</div>
                                </div>
                            </div>
                            
                            <div class="stats-card-mini">
                                <div class="stats-card-icon" style="background-color: rgba(59, 130, 246, 0.1); color: var(--secondary);">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <div class="stats-card-info">
                                    <div class="stats-card-val">{{ $stats['avg_wait_time'] }} min</div>
                                    <div class="stats-card-lbl">Attente moyenne</div>
                                </div>
                            </div>

                            <div class="stats-card-mini">
                                <div class="stats-card-icon" style="background-color: var(--success-bg); color: var(--success);">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </div>
                                <div class="stats-card-info">
                                    <div class="stats-card-val">{{ $stats['patients_consulted'] }}</div>
                                    <div class="stats-card-lbl">Patients traités</div>
                                </div>
                            </div>
                        </div>

                        <!-- Chart -->
                        <div class="glass-panel">
                            <h2 style="margin-bottom: 1rem; font-size: 1.25rem;">Affluence par heure (Heures de pointe estimées)</h2>
                            <div class="chart-bar-container">
                                <div class="chart-bar" style="height: 20%;" title="08:00 - 10 patients"></div>
                                <div class="chart-bar active" style="height: 75%;" title="09:00 - 45 patients"></div>
                                <div class="chart-bar active" style="height: 90%;" title="10:00 - 55 patients"></div>
                                <div class="chart-bar active" style="height: 65%;" title="11:00 - 38 patients"></div>
                                <div class="chart-bar" style="height: 40%;" title="12:00 - 20 patients"></div>
                                <div class="chart-bar" style="height: 30%;" title="13:00 - 15 patients"></div>
                                <div class="chart-bar" style="height: 55%;" title="14:00 - 30 patients"></div>
                                <div class="chart-bar" style="height: 80%;" title="15:00 - 48 patients"></div>
                                <div class="chart-bar" style="height: 45%;" title="16:00 - 24 patients"></div>
                                <div class="chart-bar" style="height: 15%;" title="17:00 - 8 patients"></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0 0.5rem;">
                                <span class="chart-label">08h</span>
                                <span class="chart-label">10h (Pic)</span>
                                <span class="chart-label">12h</span>
                                <span class="chart-label">15h (Pic)</span>
                                <span class="chart-label">17h</span>
                            </div>
                        </div>

                        <!-- Desk Manager -->
                        <div class="glass-panel">
                            <h2 style="margin-bottom: 1.25rem;">Configuration des Guichets / Postes</h2>
                            <div class="desk-grid">
                                @foreach($desks as $d)
                                    @php
                                        $dService = $services->firstWhere('id', $d->service_id);
                                        $dTicket = $tickets->where('desk_id', $d->id)->whereIn('status', ['appele', 'en_cours'])->first();
                                    @endphp
                                    <div class="desk-card {{ $d->active ? 'active' : '' }} {{ $dTicket ? 'busy' : '' }}">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <strong>{{ $d->name }}</strong>
                                            <form action="{{ route('desk.configure', ['desk_id' => $d->id]) }}" method="POST" id="config-active-{{ $d->id }}">
                                                @csrf
                                                <input type="hidden" name="active" value="{{ $d->active ? '0' : '1' }}">
                                                <input type="checkbox" {{ $d->active ? 'checked' : '' }} onchange="document.getElementById('config-active-{{ $d->id }}').submit()">
                                            </form>
                                        </div>

                                        @if($d->active)
                                            <form action="{{ route('desk.configure', ['desk_id' => $d->id]) }}" method="POST" id="config-service-{{ $d->id }}">
                                                @csrf
                                                <div class="form-group" style="margin: 0.5rem 0;">
                                                    <select name="service_id" class="form-select" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onchange="document.getElementById('config-service-{{ $d->id }}').submit()">
                                                        <option value="">Aucun service</option>
                                                        @foreach($services as $s)
                                                            <option value="{{ $s->id }}" {{ $d->service_id == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </form>

                                            <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                                @if($dTicket)
                                                    <span>En cours : <strong style="color: var(--primary);">{{ $dTicket->numero }}</strong></span>
                                                @else
                                                    <span style="color: var(--success);">Disponible</span>
                                                @endif
                                            </div>
                                        @else
                                            <span style="font-size: 0.8rem; color: var(--text-muted);">Poste inactif</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Reset system -->
                        <form action="{{ route('system.reset') }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment réinitialiser tout le système ?')">
                            @csrf
                            <button type="submit" class="btn btn-secondary" style="color: var(--danger-text);">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg> Réinitialiser la base de données
                            </button>
                        </form>
                    @endif

                    <!-- --- ROLE: ADMIN --- -->
                    @if($activeRole === 'admin')
                        <div class="dashboard-header">
                          <h1>Console Assistant IA QueueOptimizer™</h1>
                          <p>L'intelligence artificielle analyse le flux historique de l'Hôpital du Point G et gère les files.</p>
                        </div>

                        <div class="glass-panel ai-card">
                            <h2 style="display: flex; align-items: center; gap: 0.5rem; margin: 0; font-size: 1.3rem;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M12 6v6l4 2"/></svg>
                                Calcul Prédictif du Temps d'Attente (TAE)
                            </h2>
                            <p style="margin-top: 0.75rem; font-size: 0.95rem;">
                                Le système intelligent utilise l'équation prédictive suivante en temps réel :
                            </p>
                            <div style="margin: 1rem 0; padding: 1rem; background-color: var(--bg-primary); border-radius: var(--radius-sm); font-family: var(--font-mono); font-size: 0.9rem; text-align: center;">
                                TAE = (Position de Patient × Temps moyen) ÷ Nombre de guichets actifs
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-secondary);">
                                L'algorithme se recalcule à chaque appel de ticket et s'adapte automatiquement à l'ouverture ou la fermeture des cabinets de consultation.
                            </p>
                        </div>

                        <div class="glass-panel">
                            <h2 style="margin-bottom: 1rem;">Recommandations d'optimisation IA</h2>
                            @if(count($aiRecommendations) === 0)
                                <div style="color: var(--success); font-weight: 600; display: flex; align-items: center; gap: 0.5rem; padding: 1rem 0;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg> Aucun problème de file d'attente détecté. Le flux hospitalier est optimal.
                                </div>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    @foreach($aiRecommendations as $rec)
                                        <div class="ai-recommendation-item" style="border-left-color: {{ $rec['type'] === 'danger' ? 'var(--danger)' : 'var(--warning)' }};">
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                <div>
                                                    <strong style="font-size: 0.95rem; color: var(--text-primary);">{{ $rec['title'] }}</strong>
                                                    <p style="color: var(--text-secondary); margin-top: 0.25rem;">{{ $rec['message'] }}</p>
                                                </div>
                                                <span class="ai-pill">Recommandé</span>
                                            </div>
                                            <a href="{{ $rec['actionUrl'] }}" class="btn btn-primary btn-sm" style="margin-top: 0.75rem; padding: 0.25rem 0.75rem; font-size: 0.8rem;">
                                                Appliquer la recommandation
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

                <!-- Right Column: Live TV Monitor + Simulator -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    
                    <!-- Live TV screen -->
                    <div class="glass-panel" style="border: 2px solid var(--secondary);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h2 style="font-size: 1.2rem; font-family: var(--font-heading); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                                <svg class="voice-indicator" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="color: var(--secondary);"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                                Écran Salle d'Attente
                            </h2>
                            <span class="badge badge-info" style="animation: pulse 1.5s infinite;">Live TV</span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-weight: bold; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-color); padding-bottom: 0.25rem;">
                                <span>Ticket</span>
                                <span>Poste / Bureau</span>
                            </div>

                            @foreach($liveMonitor as $t)
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; align-items: center; padding: 0.75rem 0.5rem; border-radius: var(--radius-sm); {{ $t->status === 'appele' ? 'background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); animation: pulse 2s infinite;' : '' }}">
                                    <span style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 800; color: {{ $t->status === 'appele' ? 'var(--secondary)' : 'var(--text-primary)' }};">
                                        {{ $t->numero }}
                                    </span>
                                    <span style="font-weight: 600; font-size: 0.95rem;">
                                        {{ $t->deskName }}
                                    </span>
                                </div>
                            @endforeach

                            @if($liveMonitor->isEmpty())
                                <div style="text-align: center; padding: 1rem; color: var(--text-muted); font-size: 0.9rem;">
                                    Aucun appel en cours.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Flash notifications -->
                    @if($notification)
                        <div class="alert-box alert-info" style="border-left: 4px solid var(--primary); padding: 0.75rem 1rem;">
                            <div>{{ $notification['text'] }}</div>
                        </div>
                    @endif

                    <!-- Simulator Controls (Admin & Responsable only) -->
                    @if(in_array(auth()->user()->role, ['admin', 'responsable']))
                    <div class="glass-panel">
                        <div class="simulator-header">
                            <div class="simulator-title">
                                <span class="sim-indicator active"></span>
                                Simulateur de Temps Réel
                            </div>
                            <form action="{{ route('simulate.progress') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm" style="border-radius: var(--radius-full); gap: 0.25rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                    Avancer Simulation
                                </button>
                            </form>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.85rem; margin-top: 1rem;">
                            <!-- Voice Call checkbox -->
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Synthèse Vocale (Voix)</span>
                                <input type="checkbox" id="voice-checkbox" checked onchange="toggleVoice(this.checked)">
                            </div>

                            <!-- Simulator logs -->
                            <div style="margin-top: 0.5rem;">
                                <div style="font-weight: bold; margin-bottom: 0.25rem; color: var(--text-secondary);">Historique de simulation :</div>
                                <div style="max-height: 120px; overflow-y: auto; background-color: var(--bg-primary); padding: 0.5rem; border-radius: 4px; font-family: var(--font-mono); font-size: 0.75rem; display: flex; flex-direction: column; gap: 0.25rem;">
                                    @if(empty($simulationLogs))
                                        <span style="color: var(--text-muted);">En attente d'événements. Cliquez sur "Avancer la simulation" pour faire évoluer la file en temps réel.</span>
                                    @else
                                        @foreach($simulationLogs as $log)
                                            <div>
                                                <span style="color: var(--text-muted);">[{{ $log['time'] }}]</span> {{ $log['text'] }}
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

            </div>

        </main>
    </div>

    <!-- Synthesis Vocal and Audio Script -->
    <script>
        // Set light/dark theme on startup
        const storedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', storedTheme);
        updateThemeUI(storedTheme);

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeUI(newTheme);
        }

        function updateThemeUI(theme) {
            if (theme === 'dark') {
                document.getElementById('theme-icon-moon').style.display = 'none';
                document.getElementById('theme-icon-sun').style.display = 'block';
            } else {
                document.getElementById('theme-icon-moon').style.display = 'block';
                document.getElementById('theme-icon-sun').style.display = 'none';
            }
        }

        // Voice call options
        let voiceEnabled = localStorage.getItem('voice_enabled') !== 'false';
        const voiceCheckbox = document.getElementById('voice-checkbox');
        if (voiceCheckbox) {
            voiceCheckbox.checked = voiceEnabled;
        }

        function toggleVoice(enabled) {
            voiceEnabled = enabled;
            localStorage.setItem('voice_enabled', enabled ? 'true' : 'false');
        }

        // Play chime (E5, G5)
        let audioCtx = null;
        function playChime() {
            try {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                const now = audioCtx.currentTime;
                const notes = [659.25, 783.99]; // E5, G5
                notes.forEach((freq, idx) => {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, now + idx * 0.15);
                    gain.gain.setValueAtTime(0, now + idx * 0.15);
                    gain.gain.linearRampToValueAtTime(0.2, now + idx * 0.15 + 0.05);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + idx * 0.15 + 0.6);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now + idx * 0.15);
                    osc.stop(now + idx * 0.15 + 0.65);
                });
            } catch (e) {
                console.log(e);
            }
        }

        // Run voice call if triggered from backend session flash
        window.onload = function() {
            @if(session('voice_call'))
                const code = "{{ session('voice_call')['code'] }}";
                const desk = "{{ session('voice_call')['desk'] }}";
                
                if (voiceEnabled) {
                    playChime();
                    setTimeout(() => {
                        if ('speechSynthesis' in window) {
                            window.speechSynthesis.cancel();
                            const cleanCode = code.split('-').join(' ');
                            const utterance = new SpeechSynthesisUtterance("Ticket " + cleanCode + ", veuillez vous présenter au " + desk + ".");
                            utterance.lang = 'fr-FR';
                            utterance.rate = 0.9;
                            window.speechSynthesis.speak(utterance);
                        }
                    }, 650);
                }
            @endif
        }
    </script>
</body>
</html>
