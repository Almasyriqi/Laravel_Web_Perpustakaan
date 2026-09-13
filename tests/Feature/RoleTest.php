<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_kolom_role_dibaca_sebagai_enum_dengan_helper(): void
    {
        $admin = Admin::factory()->create()->user;
        $petugas = Petugas::factory()->create()->user;
        $anggota = Anggota::factory()->create()->user;

        $this->assertSame(Role::Admin, $admin->role);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isPetugas());
        $this->assertTrue($petugas->hasRole('petugas'));
        $this->assertTrue($anggota->hasRole(Role::Admin, Role::Anggota));
        $this->assertFalse($anggota->hasRole(Role::Admin, Role::Petugas));
        $this->assertSame('/petugas', $petugas->role->dashboardPath());
        $this->assertSame('Anggota', Role::Anggota->label());
    }

    public function test_middleware_role_menerima_beberapa_role_sekaligus(): void
    {
        Route::middleware(['web', 'auth', 'role:admin,petugas'])
            ->get('/_uji-role', fn () => 'boleh');

        $this->actingAs(Admin::factory()->create()->user)->get('/_uji-role')->assertOk()->assertSee('boleh');
        $this->actingAs(Petugas::factory()->create()->user)->get('/_uji-role')->assertOk()->assertSee('boleh');
        $this->actingAs(Anggota::factory()->create()->user)->get('/_uji-role')->assertRedirect('/');
    }

    public function test_nilai_role_tersimpan_sebagai_string_di_database(): void
    {
        $user = User::factory()->petugas()->create();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'petugas']);
        $this->assertSame(Role::Petugas, $user->fresh()->role);
    }
}
