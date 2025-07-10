<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_index_page_loads_successfully(): void
    {
        $response = $this->get('/users');
        $response->assertStatus(200);
    }

    public function test_user_create_page_loads_successfully(): void
    {
        $response = $this->get('/users/create');
        $response->assertStatus(200);
    }

    public function test_user_can_be_created(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/users', $userData);
        $response->assertRedirect('/users');
        
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_can_be_updated(): void
    {
        $user = User::factory()->create();
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->put("/users/{$user->id}", $updateData);
        $response->assertRedirect('/users');
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create();
        
        $response = $this->delete("/users/{$user->id}");
        $response->assertRedirect('/users');
        
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}