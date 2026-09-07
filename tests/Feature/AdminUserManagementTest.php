<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_user_management(): void
    {
        $user = User::factory()->create(['role' => 'patient']);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_user_with_a_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Awa Traore',
            'email' => 'awa@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'agent_accueil',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $createdUser = User::where('email', 'awa@example.com')->first();

        $this->assertNotNull($createdUser);
        $this->assertSame('agent_accueil', $createdUser->role);
        $this->assertTrue(Hash::check('password123', $createdUser->password));
    }
}
