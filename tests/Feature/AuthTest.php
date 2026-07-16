<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_cms_login_page_loads(): void
    {
        $this->get('/cms/login')->assertStatus(200);
    }

    public function test_cms_dashboard_redirects_to_login_when_unauthenticated(): void
    {
        $this->get('/cms/dashboard')->assertRedirect('/cms/login');
    }

    public function test_cms_konflik_redirects_to_login_when_unauthenticated(): void
    {
        $this->get('/cms/konflik')->assertRedirect('/cms/login');
    }

    public function test_cms_tambah_konflik_redirects_to_login_when_unauthenticated(): void
    {
        $this->get('/cms/tambah-konflik')->assertRedirect('/cms/login');
    }

    public function test_login_with_valid_credentials(): void
    {
        DB::table('users')->insert([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret'),
            'instansi' => 'Test',
            'role' => 0,
            'is_active' => 1,
        ]);

        Livewire::test(\App\Livewire\LoginComponent::class)
            ->set('email', 'admin@example.com')
            ->set('password', 'secret')
            ->call('login')
            ->assertRedirect('/cms/dashboard');

        $this->assertEquals('Test Admin', session('name'));
        $this->assertEquals(0, session('role_id'));
        $this->assertEquals('admin@example.com', session('email'));
    }

    public function test_login_with_invalid_email(): void
    {
        Livewire::test(\App\Livewire\LoginComponent::class)
            ->set('email', 'nonexistent@test.com')
            ->set('password', 'secret')
            ->call('login')
            ->assertNoRedirect();

        $this->assertNull(session('id'));
    }

    public function test_login_with_wrong_password(): void
    {
        DB::table('users')->insert([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => Hash::make('correct'),
            'instansi' => 'Test',
            'role' => 0,
            'is_active' => 1,
        ]);

        Livewire::test(\App\Livewire\LoginComponent::class)
            ->set('email', 'test@example.com')
            ->set('password', 'wrong')
            ->call('login')
            ->assertNoRedirect();

        $this->assertNull(session('id'));
    }

    public function test_login_page_redirects_to_dashboard_when_already_logged_in(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/login')->assertRedirect('/cms/dashboard');
    }

    public function test_logout_flushes_session(): void
    {
        $this->loginAsAdmin();

        // logout is POST (a GET logout is a CSRF forced-logout vector);
        // disable CSRF for this handler test and post the route.
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->post('/cms/logout')->assertRedirect('/cms/login');

        $this->assertNull(session('id'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/dashboard')->assertStatus(200);
    }

    public function test_authenticated_user_can_access_protected_pages(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/konflik')->assertStatus(200);
        $this->get('/cms/group')->assertStatus(200);
        $this->get('/cms/perusahaan')->assertStatus(200);
        $this->get('/cms/users')->assertStatus(200);
        $this->get('/cms/instansi')->assertStatus(200);
    }
}
