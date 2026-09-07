<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_is_sent_to_payment_before_ticket_creation(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $service = Service::create([
            'nom' => 'Consultation Générale',
            'code' => 'CONS',
            'description' => 'Consultations',
            'temps_moyen_traitement' => 10,
            'statut' => 'normal',
            'icon' => 'X',
        ]);

        $response = $this->actingAs($patient)->post(route('ticket.payment.start'), [
            'service_id' => $service->id,
            'priority' => 'normal',
            'patient_name' => $patient->name,
        ]);

        $response->assertRedirect(route('ticket.payment'));
        $this->assertDatabaseCount('tickets', 0);
        $this->actingAs($patient)->get(route('ticket.payment'))->assertOk();
    }

    public function test_confirming_demo_orange_money_payment_creates_the_ticket(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $service = Service::create([
            'nom' => 'Cardiologie',
            'code' => 'CARD',
            'description' => 'Consultations cardiaques',
            'temps_moyen_traitement' => 20,
            'statut' => 'normal',
            'icon' => 'X',
        ]);

        $this->actingAs($patient)->withSession([
            'pending_ticket' => [
                'service_id' => $service->id,
                'priority' => 'normal',
                'patient_name' => $patient->name,
            ],
        ])->post(route('ticket.payment.confirm'), [
            'payment_method' => 'orange_money',
            'demo_confirmation' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('tickets', [
            'patient_name' => $patient->name,
            'service_id' => $service->id,
            'status' => 'en_attente',
        ]);
        $this->assertDatabaseHas('payments', [
            'user_id' => $patient->id,
            'amount' => 1000,
            'currency' => 'XOF',
            'payment_method' => 'orange_money',
            'status' => 'paid',
        ]);
    }

    public function test_patient_cannot_bypass_payment_with_the_direct_ticket_route(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->post(route('ticket.take'), [
            'service_id' => 1,
            'priority' => 'normal',
            'patient_name' => $patient->name,
        ])->assertForbidden();

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_responsable_sees_total_paid_and_distinct_paying_patients(): void
    {
        $responsable = User::factory()->create(['role' => 'responsable']);
        $patient = User::factory()->create(['role' => 'patient']);

        Payment::create([
            'user_id' => $patient->id,
            'amount' => 1000,
            'currency' => 'XOF',
            'payment_method' => 'orange_money',
            'status' => 'paid',
            'reference' => 'DEMO-STATS-001',
            'paid_at' => now(),
        ]);

        $this->actingAs($responsable)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 000 F CFA')
            ->assertSee('Patients payeurs');
    }
}