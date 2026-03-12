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
                        <p class="text-primary fw-bold mb-0">{{ auth()->user()->layanan?->nama_layanan ?? 'Unit Pengambilan Dokumen' }}</p>
                        <p class="text-muted small">Petugas: {{ auth()->user()->name }}</p>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div id="status-pelayanan">
                        @if($current)
                            {{-- KONDISI 1: JIKA ADA ANTRIAN YANG SEDANG DILAYANI --}}
                            <p class="small text-uppercase fw-bold text-muted mb-2">Antrian Sedang Dilayani</p>
                            <div class="p-4 bg-light rounded-4 mb-4 border border-primary shadow-sm" style="position: relative; overflow: hidden;">
                                <div class="spinner-grow spinner-grow-sm text-primary" role="status" style="position: absolute; top: 15px; right: 15px;"></div>
                                
                                <h1 class="display-1 fw-800 text-primary mb-0">{{ $current->nomor_antrian }}</h1>
                                <p class="fw-bold text-dark mb-0 mt-2">{{ $current->layanan?->nama_layanan ?? 'Lintas Layanan' }}</p>
                                <div class="mt-2">
                                    <h5 class="mb-0 fw-bold">{{ $current->nama_pendaftar }}</h5>
                                    <span class="badge bg-secondary">{{ $current->nik ?? 'Tanpa NIK / Non-Penduduk' }}</span>
                                </div>
                            </div>

                            <div class="d-grid gap-3">
                                {{-- POSISI 1: TOMBOL PANGGIL BERIKUTNYA --}}
                                <form action="{{ route('petugas.panggil') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-lg py-3 border-0 action-btn" style="background: linear-gradient(45deg, #0d6efd, #0043a8);">
                                        NOMOR BERIKUTNYA <i class="fas fa-chevron-right ms-2"></i>
                                    </button>
                                </form>

                                {{-- POSISI 2: TOMBOL LEWATI --}}
                                {{-- PERBAIKAN LOGIKA: Tombol Lewati hanya aktif jika ada antrian lain di daftar tunggu --}}
                                <form action="{{ route('petugas.aksi', ['id' => $current->id, 'status' => 'lewat']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-100 fw-bold py-3 action-btn" 
                                        @if($antrian->count() == 0) disabled title="Tidak ada antrian berikutnya untuk dipanggil" @endif>
                                        LEWATI & PANGGIL BARU <i class="fas fa-step-forward ms-1"></i>
                                    </button>
                                </form>

                                {{-- POSISI 3: TOMBOL SELESAI --}}
                                @php
                                    $isLoketPengambilan = is_null(auth()->user()->layanan_id);
                                    $isRekamKTP = str_contains(strtolower(auth()->user()->layanan?->nama_layanan ?? ''), 'rekam');
                                @endphp

                                @if($isRekamKTP)
                                    <form action="{{ route('petugas.aksi', ['id' => $current->id, 'status' => 'selesai']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm border-0 action-btn">
                                            <i class="fas fa-check-circle me-2"></i> SELESAI
                                        </button>
                                    </form>
                                    <small class="text-muted d-block mt-n2">* Layanan Rekam KTP langsung diarsipkan.</small>

                                @elseif($isLoketPengambilan)
                                    <form action="{{ route('petugas.aksi', ['id' => $current->id, 'status' => 'selesai']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm border-0 action-btn">
                                            <i class="fas fa-check-double me-2"></i> SELESAI PENGAMBILAN
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('petugas.aksi', ['id' => $current->id, 'status' => 'selesai diproses']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold py-3 shadow-sm border-0 text-white action-btn">
                                            <i class="fas fa-arrow-right me-2"></i> SELESAI PELAYANAN
                                        </button>
                                    </form>
                                    <small class="text-muted d-block mt-n2">* Nomor akan tetap tampil di monitor dengan status Selesai.</small>
                                @endif
                            </div>

                        @elseif($antrian->count() > 0)
                            <div class="py-4">
                                <div class="mb-3">
                                    <i class="fas fa-user-clock fa-4x text-primary opacity-25"></i>
                                </div>
                                <h5 class="fw-bold">Antrian Tersedia</h5>
                                <p class="text-muted small">Ada <strong id="wait-count">{{ $antrian->count() }}</strong> orang menunggu.</p>
                            </div>
                            
                            <form action="{{ route('petugas.panggil') }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-lg py-4 border-0 action-btn" style="background: linear-gradient(45deg, #0d6efd, #0043a8);">
                                    PANGGIL ANTRIAN <i class="fas fa-volume-up ms-2"></i>
                                </button>
                            </form>
                        @else
                            <div id="empty-state" class="py-4">
                                <div class="mb-3">
                                    <i class="fas fa-mug-hot fa-4x text-light"></i>
                                </div>
                                <h5 class="fw-bold text-muted">Belum Ada Antrian</h5>
                                <p class="text-muted small">Daftar tunggu saat ini kosong.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- DAFTAR ANTRIAN --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-list-ol me-2"></i> 
                        {{ is_null(auth()->user()->layanan_id) ? 'Siap Diambil (Dokumen Selesai Pelayanan)' : 'Daftar Tunggu Unit' }}
                    </h5>
                    <span id="count-menunggu" class="badge bg-danger rounded-pill px-3">{{ $antrian->count() }} Orang</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" width="120">No</th>
                                    <th>NIK</th>
                                    <th>Nama Pendaftar</th>
                                    <th>Layanan Asal</th>
                                    <th class="text-center">Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-menunggu">
                                @forelse($antrian as $a)
                                <tr>
                                    <td class="ps-4"><span class="fw-bold text-primary h5 mb-0">{{ $a->nomor_antrian }}</span></td>
                                    <td><code class="text-dark">{{ $a->nik ?? '-' }}</code></td>
                                    <td class="fw-bold">{{ $a->nama_pendaftar }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $a->layanan?->nama_layanan ?? 'N/A' }}</span></td>
                                    <td class="text-center small text-muted">
                                        {{ is_null(auth()->user()->layanan_id) ? 'Selesai: '.$a->updated_at->format('H:i') : 'Daftar: '.$a->created_at->format('H:i') }}
                                    </td>
                                </tr>
                                @empty
                                <tr class="empty-row">
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada antrian.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TABEL DAFTAR DILEWATI --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-secondary"><i class="fas fa-history me-2"></i> Baru Saja Dilewati</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" width="120">No</th>
                                    <th>NIK / Nama</th>
                                    <th class="text-end pe-4">Aksi Balas</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-skipped">
                                @forelse($skipped as $s)
                                <tr>
                                    <td class="ps-4"><span class="fw-bold text-danger">{{ $s->nomor_antrian }}</span></td>
                                    <td>
                                        <div class="fw-bold">{{ $s->nama_pendaftar }}</div>
                                        <small class="text-muted">{{ $s->nik ?? 'Tanpa NIK' }} | {{ $s->updated_at->format('H:i') }}</small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('petugas.panggilUlang', $s->id) }}" method="POST" class="recall-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning fw-bold px-3 shadow-sm rounded-pill recall-btn">
                                                RECALL <i class="fas fa-redo ms-1"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted small">Tidak ada nomor dilewati.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TABEL DAFTAR SELESAI --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-success"><i class="fas fa-check-double me-2"></i> Daftar Selesai Hari Ini</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" width="120">No</th>
                                    <th>Nama Pendaftar</th>
                                    <th>Status Akhir</th>
                                    <th class="text-center">Jam Selesai</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-selesai">
                                @forelse($selesai ?? [] as $sl)
                                <tr>
                                    <td class="ps-4"><span class="fw-bold text-success">{{ $sl->nomor_antrian }}</span></td>
                                    <td class="fw-bold">{{ $sl->nama_pendaftar }}</td>
                                    <td>
                                        @if($sl->status == 'selesai diproses')
                                            <span class="badge bg-soft-warning text-warning">Selesai Pelayanan</span>
                                        @else
                                            <span class="badge bg-soft-success text-success">Arsip Selesai</span>
                                        @endif
                                    </td>
                                    <td class="text-center small text-muted">{{ $sl->updated_at->format('H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">Belum ada antrian selesai hari ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let lastAntrianCount = {{ $antrian->count() }};
    let isCurrentEmpty = {{ $current ? 'false' : 'true' }};

    $(document).on('submit', 'form', function() {
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);
        $btn.html('<span class="spinner-border spinner-border-sm me-2"></span> PROSES...');
        $('.action-btn, .recall-btn').addClass('disabled').prop('disabled', true);
    });

    function fetchAntrianRealtime() {
        $.ajax({
            url: "{{ route('petugas.dashboard') }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                $('#count-menunggu').text(response.count + ' Orang');
                $('#wait-count').text(response.count);

                // Reload otomatis jika ada antrian masuk saat petugas standby
                if (isCurrentEmpty && response.count > 0 && lastAntrianCount === 0) {
                    location.reload(); 
                }
                
                // Update status tombol Lewati secara realtime
                if (response.count === 0) {
                    $('form[action*="status=lewat"] button').prop('disabled', true).attr('title', 'Tidak ada antrian berikutnya');
                } else {
                    $('form[action*="status=lewat"] button').prop('disabled', false).removeAttr('title');
                }

                lastAntrianCount = response.count;

                // Update Tabel Menunggu
                let htmlMenunggu = '';
                if (response.antrian.length > 0) {
                    response.antrian.forEach(function(a) {
                        let namaLayanan = a.layanan ? a.layanan.nama_layanan : '---';
                        htmlMenunggu += `
                            <tr>
                                <td class="ps-4"><span class="fw-bold text-primary h5 mb-0">${a.nomor_antrian}</span></td>
                                <td><code class="text-dark">${a.nik ?? '-'}</code></td>
                                <td class="fw-bold">${a.nama_pendaftar}</td>
                                <td><span class="badge bg-info text-dark">${namaLayanan}</span></td>
                                <td class="text-center small text-muted">Baru saja</td>
                            </tr>`;
                    });
                } else {
                    htmlMenunggu = '<tr><td colspan="5" class="text-center py-5 text-muted small">Belum ada antrian.</td></tr>';
                }
                $('#tbody-menunggu').html(htmlMenunggu);

                // Update Tabel Dilewati
                let htmlSkipped = '';
                if (response.skipped.length > 0) {
                    response.skipped.forEach(function(s) {
                        let recallUrl = "{{ route('petugas.panggilUlang', ':id') }}".replace(':id', s.id);
                        htmlSkipped += `
                            <tr>
                                <td class="ps-4"><span class="fw-bold text-danger">${s.nomor_antrian}</span></td>
                                <td>
                                    <div class="fw-bold">${s.nama_pendaftar}</div>
                                    <small class="text-muted">${s.nik ?? 'Tanpa NIK'}</small>
                                </td>
                                <td class="text-end pe-4">
                                    <form action="${recallUrl}" method="POST" class="d-inline recall-form">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-sm btn-warning fw-bold px-3 shadow-sm rounded-pill recall-btn">
                                            RECALL <i class="fas fa-redo ms-1"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>`;
                    });
                } else {
                    htmlSkipped = '<tr><td colspan="3" class="text-center py-4 text-muted small">Tidak ada nomor dilewati.</td></tr>';
                }
                $('#tbody-skipped').html(htmlSkipped);
            }
        });
    }

    $(document).ready(function() {
        setInterval(fetchAntrianRealtime, 5000);
    });
</script>

<style>
    .fw-800 { font-weight: 800; }
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .rounded-4 { border-radius: 1.5rem !important; }
    .table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .btn-lg { border-radius: 12px; transition: all 0.3s; }
    .btn-lg:hover:not(:disabled) { transform: translateY(-2px); filter: brightness(1.1); }
    code { font-family: 'Courier New', Courier, monospace; color: #e83e8c; }
    .action-btn.disabled { opacity: 0.6; pointer-events: none; }
    button:disabled { cursor: not-allowed; }
</style>
@endsection