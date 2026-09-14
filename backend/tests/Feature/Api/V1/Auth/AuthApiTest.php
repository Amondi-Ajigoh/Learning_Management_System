<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('student', 'web');
    }

    public function test_user_can_register_as_a_student(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Auth',
            'last_name' => 'Verification',
            'email' => 'auth.register@example.test',
            'password' => 'TestPassword!2026',
            'password_confirmation' => 'TestPassword!2026',
            'phone' => '+254700000000',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.email', 'auth.register@example.test')
            ->assertJsonPath('user.roles.0', 'student')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure([
                'message',
                'user',
                'token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'auth.register@example.test',
            'first_name' => 'Auth',
            'last_name' => 'Verification',
            'is_active' => true,
        ]);

        $user = User::where('email', 'auth.register@example.test')->firstOrFail();

        $this->assertTrue($user->hasRole('student'));
        $this->assertTrue(Hash::check('TestPassword!2026', $user->password));
    }

    public function test_registered_user_can_get_their_profile(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Auth',
            'last_name' => 'Verification',
            'email' => 'auth.me@example.test',
            'password' => 'TestPassword!2026',
            'is_active' => true,
        ]);

        $user->assignRole('student');

        $token = $user->createToken('lms-api')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $response
            ->assertOk()
            ->assertJsonPath('data.email', 'auth.me@example.test')
            ->assertJsonPath('data.roles.0', 'student');
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'auth.login@example.test',
            'password' => 'TestPassword!2026',
            'is_active' => true,
        ]);

        $user->assignRole('student');

        $this->postJson('/api/v1/auth/login', [
            'email' => 'auth.login@example.test',
            'password' => 'TestPassword!2026',
        ])
            ->assertOk()
            ->assertJsonPath('user.email', 'auth.login@example.test')
            ->assertJsonPath('user.roles.0', 'student')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure([
                'message',
                'user',
                'token',
                'token_type',
            ]);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'auth.invalid@example.test',
            'password' => 'TestPassword!2026',
            'is_active' => true,
        ]);

        $user->assignRole('student');

        $this->postJson('/api/v1/auth/login', [
            'email' => 'auth.invalid@example.test',
            'password' => 'WrongPassword!2026',
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'auth.inactive@example.test',
            'password' => 'TestPassword!2026',
            'is_active' => false,
        ]);

        $user->assignRole('student');

        $this->postJson('/api/v1/auth/login', [
            'email' => 'auth.inactive@example.test',
            'password' => 'TestPassword!2026',
        ])
            ->assertForbidden()
            ->assertJson([
                'message' => 'This account is inactive.',
            ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'auth.logout@example.test',
            'password' => 'TestPassword!2026',
            'is_active' => true,
        ]);

        $user->assignRole('student');

        $token = $user->createToken('lms-api')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJson([
                'message' => 'Logout successful.',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
