<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HospitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Seed Users
        $users = [
            ['email' => 'patient@pointg.ml', 'name' => 'Amadou Touré', 'password' => bcrypt('password'), 'role' => 'patient'],
            ['email' => 'accueil@pointg.ml', 'name' => 'Fatoumata Traoré', 'password' => bcrypt('password'), 'role' => 'agent_accueil'],
            ['email' => 'medecin@pointg.ml', 'name' => 'Dr. Diallo', 'password' => bcrypt('password'), 'role' => 'personnel_medical'],
            ['email' => 'responsable@pointg.ml', 'name' => 'Chef de Service', 'password' => bcrypt('password'), 'role' => 'responsable'],
            ['email' => 'admin@pointg.ml', 'name' => 'Administrateur', 'password' => bcrypt('password'), 'role' => 'admin'],
        ];

        foreach ($users as $u) {
            \App\Models\User::updateOrCreate(['email' => $u['email']], $u);
        }

        // 1. Seed Services
        $services = [
            ['id' => 1, 'nom' => 'Cardiologie', 'code' => 'CARD', 'description' => 'Consultations cardiaques et ECG', 'temps_moyen_traitement' => 20, 'statut' => 'normal', 'icon' => '🫀'],
            ['id' => 2, 'nom' => 'Laboratoire', 'code' => 'Laboratoire', 'description' => 'Prélèvements sanguins et analyses', 'temps_moyen_traitement' => 12, 'statut' => 'normal', 'icon' => '🧪'],
            ['id' => 3, 'nom' => 'Radiologie', 'code' => 'RADIO', 'description' => 'Radiographies, Échographies, IRM', 'temps_moyen_traitement' => 25, 'statut' => 'normal', 'icon' => '🩻'],
            ['id' => 4, 'nom' => 'Consultation Générale', 'code' => 'CONS', 'description' => 'Médecine générale et urgences légères', 'temps_moyen_traitement' => 10, 'statut' => 'normal', 'icon' => '🩺'],
            ['id' => 5, 'nom' => 'Pharmacie', 'code' => 'PHAR', 'description' => 'Retrait des médicaments prescrits', 'temps_moyen_traitement' => 8, 'statut' => 'normal', 'icon' => '💊'],
            ['id' => 6, 'nom' => 'Caisse', 'code' => 'CAIS', 'description' => 'Paiement des actes médicaux et tickets', 'temps_moyen_traitement' => 5, 'statut' => 'normal', 'icon' => '💵'],
        ];

        foreach ($services as $srv) {
            \App\Models\Service::updateOrCreate(['id' => $srv['id']], $srv);
        }

        // 2. Seed Desks
        $desks = [
            ['id' => 1, 'name' => 'Poste 1 (Médecine)', 'active' => true, 'service_id' => 4],
            ['id' => 2, 'name' => 'Poste 2 (Médecine)', 'active' => true, 'service_id' => 4],
            ['id' => 3, 'name' => 'Poste 3 (Analyses)', 'active' => true, 'service_id' => 2],
            ['id' => 4, 'name' => 'Poste 4 (Spécialités)', 'active' => true, 'service_id' => 1],
            ['id' => 5, 'name' => 'Poste 5 (Pharmacie)', 'active' => true, 'service_id' => 5],
            ['id' => 6, 'name' => 'Poste 6 (Radiographie)', 'active' => false, 'service_id' => 3],
            ['id' => 7, 'name' => 'Poste 7 (Caisse Principal)', 'active' => true, 'service_id' => 6],
        ];

        foreach ($desks as $dsk) {
            \App\Models\Desk::updateOrCreate(['id' => $dsk['id']], $dsk);
        }

        // 3. Seed Tickets
        $tickets = [
            [
                'id' => 1,
                'numero' => 'CONS-001',
                'patient_name' => 'Fatoumata Diallo',
                'patient_folder' => 'PTG-12401',
                'service_id' => 4,
                'status' => 'termine',
                'desk_id' => 1,
                'priority' => 'normal',
                'called_at' => now()->subMinutes(60),
                'started_at' => now()->subMinutes(58),
                'finished_at' => now()->subMinutes(45),
            ],
            [
                'id' => 2,
                'numero' => 'LABO-001',
                'patient_name' => 'Moussa Traoré',
                'patient_folder' => 'PTG-98421',
                'service_id' => 2,
                'status' => 'termine',
                'desk_id' => 3,
                'priority' => 'normal',
                'called_at' => now()->subMinutes(50),
                'started_at' => now()->subMinutes(48),
                'finished_at' => now()->subMinutes(35),
            ],
            [
                'id' => 3,
                'numero' => 'CONS-002',
                'patient_name' => 'Ousmane Sylla',
                'patient_folder' => null,
                'service_id' => 4,
                'status' => 'en_cours',
                'desk_id' => 1,
                'priority' => 'normal',
                'called_at' => now()->subMinutes(20),
                'started_at' => now()->subMinutes(18),
                'finished_at' => null,
            ],
            [
                'id' => 4,
                'numero' => 'CARD-001',
                'patient_name' => 'Mariam Coulibaly',
                'patient_folder' => 'PTG-44581',
                'service_id' => 1,
                'status' => 'en_cours',
                'desk_id' => 4,
                'priority' => 'prioritaire',
                'called_at' => now()->subMinutes(10),
                'started_at' => now()->subMinutes(8),
                'finished_at' => null,
            ],
            [
                'id' => 5,
                'numero' => 'PHAR-001',
                'patient_name' => 'Adama Diarra',
                'patient_folder' => null,
                'service_id' => 5,
                'status' => 'en_cours',
                'desk_id' => 5,
                'priority' => 'normal',
                'called_at' => now()->subMinutes(5),
                'started_at' => now()->subMinutes(4),
                'finished_at' => null,
            ],
            [
                'id' => 6,
                'numero' => 'CONS-003',
                'patient_name' => 'Sékou Touré',
                'patient_folder' => null,
                'service_id' => 4,
                'status' => 'en_attente',
                'desk_id' => null,
                'priority' => 'normal',
                'called_at' => null,
                'started_at' => null,
                'finished_at' => null,
            ],
            [
                'id' => 7,
                'numero' => 'RADIO-001',
                'patient_name' => 'Awa Koné',
                'patient_folder' => 'PTG-32104',
                'service_id' => 3,
                'status' => 'en_attente',
                'desk_id' => null,
                'priority' => 'normal',
                'called_at' => null,
                'started_at' => null,
                'finished_at' => null,
            ],
            [
                'id' => 8,
                'numero' => 'CONS-004',
                'patient_name' => 'Ibrahim Maïga',
                'patient_folder' => null,
                'service_id' => 4,
                'status' => 'en_attente',
                'desk_id' => null,
                'priority' => 'prioritaire',
                'called_at' => null,
                'started_at' => null,
                'finished_at' => null,
            ],
        ];

        foreach ($tickets as $t) {
            \App\Models\Ticket::updateOrCreate(['id' => $t['id']], $t);
        }
    }
}
