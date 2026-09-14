<?php

namespace App\Models;

use App\Services\PeminjamanService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Buku extends Model
{
    use HasFactory, SoftDeletes;

    /** Disk dan folder tempat sampul buku disimpan (butuh `php artisan storage:link`). */
    public const DISK_SAMPUL = 'public';

    public const FOLDER_SAMPUL = 'sampul';

    protected $table = 'buku';

    protected $fillable = [
        'id',
        'kategori_id',
        'judul',
        'penerbit',
        'penulis',
        'keterangan',
        'stok',
        'gambar',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class);
    }

    /**
     * Pencarian kata kunci pada judul, penulis, atau penerbit (dipakai katalog anggota).
     */
    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        $kata = trim((string) $kata);

        if ($kata === '') {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->where('judul', 'like', "%{$kata}%")
            ->orWhere('penulis', 'like', "%{$kata}%")
            ->orWhere('penerbit', 'like', "%{$kata}%"));
    }

    public function scopeDariKategori(Builder $query, int|string|null $kategoriId): Builder
    {
        return $query->when($kategoriId, fn (Builder $q) => $q->where('kategori_id', $kategoriId));
    }

    /**
     * Hanya buku yang stoknya masih ada.
     */
    public function scopeTersedia(Builder $query): Builder
    {
        return $query->where('stok', '>', 0);
    }

    /**
     * Masih ada eksemplar yang berada di tangan anggota.
     */
    public function sedangDipinjam(): bool
    {
        return $this->peminjaman()->whereIn('status', PeminjamanService::STATUS_MENAHAN_STOK)->exists();
    }

    /**
     * URL sampul untuk <img>. Path lama (`/images/...` di public/ atau URL
     * penuh dari seeder) dikembalikan apa adanya; path baru diambil dari
     * Storage disk public.
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(function () {
            $path = $this->gambar;

            if (blank($path)) {
                return '';
            }

            if (self::pathLegacy($path)) {
                return $path;
            }

            return Storage::disk(self::DISK_SAMPUL)->url($path);
        });
    }

    /**
     * Simpan file unggahan ke disk dan kembalikan path relatifnya (mis. sampul/abc.jpg).
     */
    public static function simpanSampul(UploadedFile $file): string
    {
        return $file->store(self::FOLDER_SAMPUL, self::DISK_SAMPUL);
    }

    /**
     * Ganti sampul: simpan yang baru lalu hapus file lama bila tersimpan di disk.
     */
    public function gantiSampul(UploadedFile $file): void
    {
        $lama = $this->gambar;
        $this->gambar = self::simpanSampul($file);

        if (! blank($lama) && ! self::pathLegacy($lama)) {
            Storage::disk(self::DISK_SAMPUL)->delete($lama);
        }
    }

    private static function pathLegacy(string $path): bool
    {
        return Str::startsWith($path, ['/', 'http://', 'https://']);
    }
}
