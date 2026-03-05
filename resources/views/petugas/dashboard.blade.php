@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        {{-- PANEL KONTROL PETUGAS --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-desktop me-2"></i> Area Kerja Petugas</h5>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill mb-2">IDENTITAS LOKET</span>
                        <h2 class="fw-800 text-dark mb-0">{{ auth()->user()->loket->nama_loket ?? 'Loket Umum' }}</h2>
                        <p class="text-primary fw-bold mb-0">{{ auth()->user()->layanan->nama_layanan ?? 'Semua Layanan' }}</p>
                        <p class="text-muted small">Petugas: {{ auth()->user()->name }}</p>
                    </div>
                    
                    <hr class="my-4">
                    
                    @if($current)
                        <p class="small text-uppercase fw-bold text-muted mb-2">Antrian Sedang Dilayani</p>
                        <div class="p-4 bg-light rounded-4 mb-4 border border-success shadow-sm">
                            <h1 class="display-1 fw-800 text-success mb-0">{{ $current->nomor_antrian }}</h1>
                            <p class="fw-bold text-dark mb-0 mt-2">{{ $current->layanan->nama_layanan }}</p>
                            <small class="text-muted">An. {{ $current->nama_pendaftar }}</small>
                            @if(!$current->nik)
                                <br><span class="badge bg-warning text-dark mt-2 small">Layanan Kematian (Tanpa NIK)</span>
                            @endif
                        </div>

                        <div class="d-grid gap-2">
                            <form action="{{ route('petugas.status', [$current->id, 'selesai']) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm py-3 mb-2">
                                    SELESAI PELAYANAN <i class="fas fa-check-circle ms-2"></i>
                                </button>
                            </form>
                            
                            <form action="{{ route('petugas.status', [$current->id, 'lewat']) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100 fw-bold border-2">
                                    TIDAK HADIR / LEWATI <i class="fas fa-step-forward ms-2"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="py-4">
                            <i class="fas fa-bullhorn fa-4x text-light mb-3"></i>
                            <p class="text-muted">Klik tombol di bawah untuk memanggil antrian<br><strong>{{ auth()->user()->layanan->nama_layanan ?? '' }}</strong></p>
                        </div>
                        
                        <form action="{{ route('petugas.panggil') }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-lg py-4 border-0" style="background: linear-gradient(45deg, #0d6efd, #0043a8);">
                                <span class="d-block small fw-normal opacity-75">PANGGIL ANTRIAN BERIKUTNYA</span>
                                KLIK UNTUK MEMANGGIL <i class="fas fa-volume-up ms-2"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- DAFTAR ANTRIAN --}}
        <div class="col-md-8">
            {{-- TABEL ANTRIAN MENUNGGU --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-list-ol me-2"></i> Daftar Tunggu</h5>
                    <span id="count-menunggu" class="badge bg-danger rounded-pill px-3">{{ $antrian->count() }} Menunggu</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="table-antrian">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">No. Antrian</th>
                                    <th>Nama Pendaftar</th>
                                    <th>NIK</th>
                                    <th class="text-center">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($antrian as $a)
                                <tr>
                                    <td class="ps-4"><span class="fw-bold text-primary">{{ $a->nomor_antrian }}</span></td>
                                    <td class="fw-bold">{{ $a->nama_pendaftar }}</td>
                                    <td><small class="text-muted">{{ $a->nik ?? '---' }}</small></td>
                                    <td class="text-center small text-muted">{{ $a->created_at->format('H:i') }}</td>
                                </tr>
                                @empty
                                <tr class="empty-row"><td colspan="4" class="text-center py-4 text-muted small">Tidak ada antrian menunggu</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TABEL ANTRIAN DILEWATI --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-secondary"><i class="fas fa-history me-2"></i> Antrian Dilewati / Tidak Hadir</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="table-skipped">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">No. Antrian</th>
                                    <th>Nama Pendaftar</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($skipped as $s)
                                <tr>
                                    <td class="ps-4"><span class="fw-bold text-danger">{{ $s->nomor_antrian }}</span></td>
                                    <td>{{ $s->nama_pendaftar }}</td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('petugas.panggilUlang', $s->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning fw-bold px-3 shadow-sm">
                                                PANGGIL ULANG <i class="fas fa-redo ms-1"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr class="empty-row-skipped"><td colspan="3" class="text-center py-4 text-muted small">Belum ada antrian yang dilewati</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT REALTIME SAJA --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hanya logika Realtime Listener (Laravel Echo)
        if (typeof Echo !== 'undefined') {
            Echo.channel('antrian-channel')
                .listen('.update.antrian', (e) => {
                    window.location.reload();
                });
        }
    });
</script>
@endsection