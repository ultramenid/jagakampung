<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_can_view_user_list_page(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/users')
            ->assertStatus(200)
            ->assertSee('Users');
    }

    public function test_can_view_tambah_user_page(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/tambah-user')
            ->assertStatus(200);
    }

    public function test_can_create_admin_user(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahUser::class)
            ->set('nama', 'Admin Baru')
            ->set('email', 'admin2@test.com')
            ->set('password', 'secret123')
            ->set('role', 0)
            ->set('instansi', 'Kantor Pusat')
            ->call('storeDatabase')
            ->assertRedirect('/cms/users');

        $this->assertDatabaseHas('users', [
            'email' => 'admin2@test.com',
            'name' => 'Admin Baru',
            'instansi' => 'Kantor Pusat',
            'role' => 0,
            'is_active' => 1,
        ]);
    }

    public function test_can_create_regular_user(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahUser::class)
            ->set('nama', 'User Biasa')
            ->set('email', 'user@test.com')
            ->set('password', 'secret123')
            ->set('role', 1)
            ->set('instansi', 'Cabang')
            ->call('storeDatabase')
            ->assertRedirect('/cms/users');

        $this->assertDatabaseHas('users', [
            'email' => 'user@test.com',
            'name' => 'User Biasa',
            'role' => 1,
        ]);
    }

    public function test_create_user_validation_nama_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahUser::class)
            ->set('nama', '')
            ->set('email', 'test@test.com')
            ->set('password', 'secret')
            ->set('role', 0)
            ->set('instansi', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_create_user_validation_email_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahUser::class)
            ->set('nama', 'Test')
            ->set('email', '')
            ->set('password', 'secret')
            ->set('role', 0)
            ->set('instansi', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_create_user_validation_password_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahUser::class)
            ->set('nama', 'Test')
            ->set('email', 'test@test.com')
            ->set('password', '')
            ->set('role', 0)
            ->set('instansi', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_create_user_validation_instansi_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahUser::class)
            ->set('nama', 'Test')
            ->set('email', 'test@test.com')
            ->set('password', 'secret')
            ->set('role', 0)
            ->set('instansi', '')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_create_user_validation_role_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahUser::class)
            ->set('nama', 'Test')
            ->set('email', 'test@test.com')
            ->set('password', 'secret')
            ->set('role', '')
            ->set('instansi', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_create_user_duplicate_email(): void
    {
        $this->loginAsAdmin();

        DB::table('users')->insert([
            'name' => 'Existing',
            'email' => 'existing@test.com',
            'password' => Hash::make('secret'),
            'instansi' => 'Test',
            'role' => 1,
            'is_active' => 1,
        ]);

        Livewire::test(\App\Livewire\TambahUser::class)
            ->set('nama', 'Test')
            ->set('email', 'existing@test.com')
            ->set('password', 'secret')
            ->set('role', 1)
            ->set('instansi', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->assertEquals(
            1,
            DB::table('users')->where('email', 'existing@test.com')->count()
        );
    }

    public function test_password_is_hashed_when_creating_user(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahUser::class)
            ->set('nama', 'Password Test')
            ->set('email', 'password@test.com')
            ->set('password', 'myplainpassword')
            ->set('role', 1)
            ->set('instansi', 'Test')
            ->call('storeDatabase');

        $user = DB::table('users')->where('email', 'password@test.com')->first();

        $this->assertNotEquals('myplainpassword', $user->password);
        $this->assertTrue(Hash::check('myplainpassword', $user->password));
    }

    public function test_can_delete_user(): void
    {
        $this->loginAsAdmin();

        $id = DB::table('users')->insertGetId([
            'name' => 'To Delete',
            'email' => 'delete@test.com',
            'password' => Hash::make('secret'),
            'instansi' => 'Test',
            'role' => 1,
            'is_active' => 1,
        ]);

        Livewire::test(\App\Livewire\CmsUsers::class)
            ->call('delete', $id)
            ->assertSet('deleter', true);

        Livewire::test(\App\Livewire\CmsUsers::class)
            ->call('deleting', $id);

        $this->assertDatabaseMissing('users', ['id' => $id]);
    }

    public function test_user_list_shows_role_badge(): void
    {
        $this->loginAsAdmin();

        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@show.test',
            'password' => Hash::make('secret'),
            'instansi' => 'Test',
            'role' => 0,
            'is_active' => 1,
        ]);

        DB::table('users')->insert([
            'name' => 'Regular User',
            'email' => 'regular@show.test',
            'password' => Hash::make('secret'),
            'instansi' => 'Test',
            'role' => 1,
            'is_active' => 1,
        ]);

        $component = Livewire::test(\App\Livewire\CmsUsers::class);

        $component->assertSee('Admin');
        $component->assertSee('User');
    }

    public function test_cannot_delete_own_account(): void
    {
        $this->loginAsAdmin();
        $adminId = session('id');

        Livewire::test(\App\Livewire\CmsUsers::class)
            ->call('delete', $adminId)
            ->assertSet('deleter', true);

        Livewire::test(\App\Livewire\CmsUsers::class)
            ->call('deleting', $adminId);

        // Self-deletion is intentionally blocked (CmsUsers::deleting guards
        // id === session('id')), so the admin's own account must still exist.
        $this->assertDatabaseHas('users', ['id' => $adminId]);
    }
}
