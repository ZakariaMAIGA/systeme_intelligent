<?php

namespace Tests\Feature;

use App\Models\Desk;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_responsable_can_create_a_service_and_a_desk(): void
    {
        $responsable = User::factory()->create(['role' => 'responsable']);

        $this->actingAs($responsable)->post(route('service.store'), [
            'nom' => 'Pédiatrie',
            'code' => 'PED',
            'description' => 'Soins pour enfants',
            'temps_moyen_traitement' => 18,
            'icon' => '🩺',
        ])->assertRedirect();

        $service = Service::where('code', 'PED')->firstOrFail();

        $this->actingAs($responsable)->post(route('desk.store'), [
            'name' => 'Poste 8 (Pédiatrie)',
            'service_id' => $service->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('services', ['id' => $service->id, 'nom' => 'Pédiatrie']);
        $this->assertDatabaseHas('desks', [
            'name' => 'Poste 8 (Pédiatrie)',
            'service_id' => $service->id,
            'active' => true,
        ]);
    }

    public function test_patient_cannot_create_a_service_or_a_desk(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->post(route('service.store'), [
            'nom' => 'Service interdit',
            'code' => 'NO',
            'temps_moyen_traitement' => 15,
        ])->assertForbidden();

        $this->actingAs($patient)->post(route('desk.store'), [
            'name' => 'Poste interdit',
        ])->assertForbidden();

        $this->assertDatabaseCount('services', 0);
        $this->assertDatabaseCount('desks', 0);
    }
}