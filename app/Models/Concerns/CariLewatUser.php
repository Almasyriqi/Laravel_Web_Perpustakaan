<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Scope cari() untuk model profil (Admin, Petugas, Anggota) yang namanya /
 * email-nya ada di relasi user(). Model bisa menambah kolom sendiri lewat
 * kolomCariSendiri().
 */
trait CariLewatUser
{
    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        $kata = trim((string) $kata);

        if ($kata === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($kata) {
            $q->whereHas('user', fn (Builder $user) => $user
                ->where('name', 'like', "%{$kata}%")
                ->orWhere('email', 'like', "%{$kata}%")
                ->orWhere('username', 'like', "%{$kata}%"));

            foreach ($this->kolomCariSendiri() as $kolom) {
                $q->orWhere($kolom, 'like', "%{$kata}%");
            }
        });
    }

    /**
     * Kolom tambahan di tabel profil yang ikut dicari.
     *
     * @return list<string>
     */
    protected function kolomCariSendiri(): array
    {
        return [];
    }
}
