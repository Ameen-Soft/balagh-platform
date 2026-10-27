<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users with admin roles can authenticate using the login screen', function () {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    $adminRole = \App\Models\Role::where('name', 'Super Admin')->firstOrFail();

    $user = User::factory()->create();
    $user->roles()->sync([$adminRole->id]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('citizens cannot authenticate on the web login screen', function () {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    $citizenRole = \App\Models\Role::where('name', 'Citizen')->firstOrFail();

    $citizen = User::factory()->create();
    $citizen->roles()->sync([$citizenRole->id]);

    $response = $this->post('/login', [
        'email' => $citizen->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['email']);
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});
