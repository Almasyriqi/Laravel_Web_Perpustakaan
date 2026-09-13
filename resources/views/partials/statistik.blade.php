{{--
    Blok statistik dashboard admin & petugas.
    Variabel: $statistik (dari StatistikService::dashboard()), $prefix ('admin' | 'petugas').
--}}
@php
    $ringkasan = $statistik['ringkasan'];
    $urlKonfirmasi = $prefix === 'petugas' ? '/petugas/transaksi/konfirmasi' : '/admin/peminjaman';
    $urlTransaksi = $prefix === 'petugas' ? '/petugas/transaksi' : '/admin/peminjaman';
@endphp

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $ringkasan['sedang_dipinjam'] }}</h3>
                <p>Sedang dipinjam</p>
            </div>
            <div class="icon"><i class="fas fa-book-reader"></i></div>
            <a href="{{ $urlTransaksi }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $ringkasan['menunggu_konfirmasi'] }}</h3>
                <p>Menunggu konfirmasi</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            <a href="{{ $urlKonfirmasi }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $ringkasan['terlambat'] }}</h3>
                <p>Terlambat</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="#daftar-keterlambatan" class="small-box-footer">
                Lihat daftar <i class="fas fa-arrow-circle-down"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>@currency($ringkasan['denda_bulan_ini'])</h3>
                <p>Denda bulan ini</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            <a href="/{{ $prefix }}/laporan/{{ now()->month }}" class="small-box-footer">
                Laporan <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Tren Peminjaman 12 Bulan Terakhir</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="trenPeminjaman" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-star"></i> Buku Terpopuler</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse ($statistik['terpopuler'] as $i => $buku)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-truncate" title="{{ $buku->judul }}">
                            <b class="text-muted mr-2">{{ $i + 1 }}.</b>{{ $buku->judul }}
                        </span>
                        <span class="badge badge-success badge-pill ml-2">{{ $buku->peminjaman_count }}×</span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted">Belum ada peminjaman dalam 12 bulan terakhir.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-danger card-outline" id="daftar-keterlambatan">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-circle"></i> Daftar Keterlambatan</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Jatuh tempo</th>
                            <th class="text-right">Terlambat</th>
                            <th class="text-right">Estimasi denda</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($statistik['keterlambatan'] as $pinjam)
                        <tr>
                            <td>{{ $pinjam->anggota->user->name }} <small class="text-muted">({{ $pinjam->anggota_id }})</small></td>
                            <td>{{ $pinjam->buku->judul }}</td>
                            <td>{{ date('d-m-Y', strtotime($pinjam->tgl_harus_kembali)) }}</td>
                            <td class="text-right"><span class="badge badge-danger">{{ $pinjam->hariTerlambat() }} hari</span></td>
                            <td class="text-right">@currency($pinjam->estimasiDenda())</td>
                            <td class="text-right">
                                @if ($prefix === 'petugas')
                                <a class="btn btn-xs btn-primary" href="/petugas/transaksi/{{ $pinjam->anggota_id }}/edit">
                                    <i class="fas fa-undo"></i> Proses
                                </a>
                                @else
                                <a class="btn btn-xs btn-info" href="/admin/peminjaman/{{ $pinjam->id }}">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                <i class="fas fa-check-circle text-success"></i> Tidak ada peminjaman yang terlambat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(function () {
        var tren = @json($statistik['tren']);

        new Chart($('#trenPeminjaman').get(0).getContext('2d'), {
            type: 'bar',
            data: {
                labels: tren.labels,
                datasets: [{
                    label: 'Peminjaman',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    data: tren.data
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: { display: false },
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
                }
            }
        });
    });
</script>
@endpush
