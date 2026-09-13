<?php

namespace App\Enums;

/**
 * Tiga level pengguna; nilainya tersimpan di kolom users.role dan menentukan
 * tabel profil (admin / petugas / anggota) serta dashboard yang dituju.
 */
enum Role: string
{
    case Admin = 'admin';
    case Petugas = 'petugas';
    case Anggota = 'anggota';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Petugas => 'Petugas',
            self::Anggota => 'Anggota',
        };
    }

    /**
     * Prefix URL panel untuk role ini (mis. /admin).
     */
    public function dashboardPath(): string
    {
        return '/'.$this->value;
    }
}
