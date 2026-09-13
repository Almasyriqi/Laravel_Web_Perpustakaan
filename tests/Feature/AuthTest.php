<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_dengan_username(): void
    {
        $user = Anggota::factory()->create()->user;

        $this->post('/login', ['email' => $user->username, 'password' => '12345678'])
            ->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_dengan_email(): void
    {
        $user = Anggota::factory()->create()->user;

        $this->post('/login', ['email' => $user->email, 'password' => '12345678'])
            ->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_dengan_password_salah_ditolak(): void
    {
        $user = Anggota::factory()->create()->user;

        $this->from('/login')
            ->post('/login', ['email' => $user->username, 'password' => 'salah-sekali'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_logout_hanya_menerima_post(): void
    {
        $user = Anggota::factory()->create()->user;

        $this->actingAs($user)->get('/logout')->assertStatus(405);
        $this->assertAuthenticatedAs($user);

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_user_belum_verifikasi_diarahkan_ke_halaman_verifikasi(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/home')->assertRedirect('/email/verify');
        $this->actingAs($user)->get('/anggota')->assertRedirect('/email/verify');
    }

    public function test_registrasi_membuat_anggota_dan_mengirim_email_verifikasi(): void
    {
        Notification::fake();

        $this->post('/register', [
            'username' => 'budi',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'nim' => '1941720099',
            'jurusan' => 'Teknologi Informasi',
            'tgl_lahir' => '2001-01-01',
            'no_hp' => '08123456789',
        ])->assertRedirect('/home');

        $user = User::where('username', 'budi')->firstOrFail();
        $this->assertSame(Role::Anggota, $user->role);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_tautan_verifikasi_menandai_email_terverifikasi(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect('/home');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
