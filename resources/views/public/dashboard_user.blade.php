<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Antrian - Sistem Layanan Publik</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --text-dark: #1a1a1a;
            --text-muted: #333333;
        }

        html,
        body {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f0f2f5;
            color: var(--text-dark);
        }

        .page-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            padding: 2vh 2vw;
        }

        .compact-header {
            text-align: center;
            flex: 0 0 auto;
            margin-bottom: 2vh;
        }

        .header-title {
            font-weight: 800;
            color: var(--primary-color);
            font-size: clamp(2rem, 5vh, 3rem);
            margin: 0;
            text-transform: uppercase;
        }

        .header-subtitle {
            color: var(--text-muted);
            font-size: clamp(1.1rem, 2.2vh, 1.4rem);
            font-weight: 700;
        }

        .content-section {
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .services-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-content: center;
            grid-template-columns: repeat(3, 1fr);
            grid-auto-rows: 1fr;
            gap: 2vh 2vw;
            width: 100%;
            height: 100%;
        }

        .grid-layout-wide {
            grid-template-columns: repeat(4, 1fr) !important;
        }

        .card-layanan {
            border: 4px solid #e9ecef;
            border-radius: 30px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            padding: 2vh 2vw !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            width: calc(33.333% - 2vw);
            min-width: 300px;
        }

        .card-layanan:hover {
            border-color: var(--primary-color);
            transform: translateY(-8px);
            background-color: #f8fbff;
        }

        .icon-wrapper {
            width: clamp(50px, 8vh, 70px);
            height: clamp(50px, 8vh, 70px);
            background: rgba(13, 110, 253, 0.1);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            margin: 0 auto 1.5vh;
            flex-shrink: 0;
        }

        .card-title {
            font-weight: 800;
            font-size: clamp(1.3rem, 2.8vh, 1.8rem);
            color: var(--text-dark);
            margin-bottom: 1.2vh;
            padding-bottom: 1.2vh;
            text-align: center;
            border-bottom: 3px solid #f0f2f5;
            width: 100%;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .card-text {
            font-size: clamp(1rem, 2vh, 1.2rem);
            color: var(--text-muted);
            font-weight: 600;
            text-align: center;
            margin-bottom: 1.5vh;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1.4;
        }

        .btn-pilih {
            width: 100%;
            border-radius: 20px;
            font-weight: 800;
            padding: 1.5vh;
            font-size: clamp(1.1rem, 2.2vh, 1.4rem);
            text-transform: uppercase;
            box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2);
            flex-shrink: 0;
            border: none;
        }

        .btn-back {
            position: absolute;
            top: 2vh;
            left: 2vw;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 800;
            background: white;
            padding: 1vh 1.5vw;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
            font-size: 1rem;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none !important;
                box-shadow: none !important;
            }
        }

        @media (max-width: 992px) {

            .services-grid,
            .grid-layout-wide {
                /*grid-template-columns: repeat(2, 1fr) !important;*/
            }

            .grid-layout-wide .card-layanan {
                width: calc(25% - 2vw) !important;
            }
        }
    </style>
</head>

<body>

    <div class="page-wrapper">
        <a href="{{ url('/') }}" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i> KEMBALI
        </a>

        <header class="compact-header">
            <h1 class="header-title">SISTEM ANTRIAN LAYANAN</h1>
            <p class="header-subtitle">Silahkan pilih jenis layanan yang Anda butuhkan</p>
        </header>

        <main class="content-section">
            <div class="services-grid {{ count($layanans) > 6 ? 'grid-layout-wide' : '' }}">
                @foreach($layanans as $l)
                @php
                // Hitung antrian hari ini secara realtime
                $currentCount = \App\Models\Queue::where('layanan_id', $l->id)
                ->whereDate('created_at', \Carbon\Carbon::today())
                ->count();

                $isFull = ($l->kuota_harian > 0 && $currentCount >= $l->kuota_harian);
                @endphp

                <div class="card card-layanan {{ $isFull ? 'bg-light' : '' }}" style="{{ $isFull ? 'opacity: 0.8;' : '' }}">
                    <div class="icon-wrapper {{ $isFull ? 'bg-secondary text-white' : '' }}">
                        <i class="{{ $l->icon ?? 'fas fa-file-invoice' }} fa-2x"></i>
                    </div>
                    <h3 class="card-title">{{ $l->nama_layanan }}</h3>
                    <div class="card-text">
                        @if($isFull)
                        <span class="text-danger fw-bold fs-4">
                            <i class="fas fa-ban"></i> KUOTA PENUH HARI INI
                        </span>
                        @else
                        {{ $l->deskripsi ?? 'Silahkan ambil nomor antrian untuk mendapatkan pelayanan.' }}
                        @endif
                    </div>

                    {{-- Menambahkan parameter isFull, namaLayanan, dan kuotaHarian ke fungsi --}}
                    <button type="button"
                        class="btn {{ $isFull ? 'btn-danger' : 'btn-primary' }} btn-pilih"
                        onclick="ambilAntrianLangsung(this, '{{ $l->id }}', {{ $isFull ? 'true' : 'false' }}, '{{ $l->nama_layanan }}', {{ $l->kuota_harian }})">
                        {{ $isFull ? 'PENUH' : 'AMBIL NOMOR' }}
                    </button>
                </div>
                @endforeach
            </div>
        </main>
    </div>

    {{-- Form Tersembunyi untuk Proses Auto-Submit --}}
    <form action="{{ route('user.store') }}" method="POST" id="formAntrianOtomatis" style="display: none;">
        @csrf
        <input type="hidden" name="layanan_id" id="selected_layanan_id">
        <input type="hidden" name="nama" value="Pemohon">
    </form>

    {{-- MODAL SUKSES --}}
    @if(session('success_data'))
    <div class="modal fade" id="modalSukses" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content text-center p-5" style="border-radius: 35px;">
                <div class="modal-body">
                    <div class="text-success mb-3"><i class="fas fa-check-circle fa-7x"></i></div>
                    <h1 class="fw-800 display-4">BERHASIL!</h1>
                    <div id="printArea" class="border border-5 rounded-5 p-4 my-4 bg-white shadow-sm">
                        <p class="text-dark fw-800 fs-2 mb-0">NOMOR ANTRIAN</p>
                        <h1 class="display-1 fw-800 text-primary my-1" style="font-size: 7rem;">
                            {{ session('success_data')['nomor'] }}
                        </h1>
                        <h2 class="fw-800 text-dark fs-1">{{ strtoupper(session('success_data')['layanan']) }}</h2>
                        <p class="fw-800 text-muted fs-3">{{ session('success_data')['waktu'] }} WIB</p>
                    </div>
                    <div class="d-grid gap-3">
                        <button onclick="printAntrian()"
                            class="btn btn-primary btn-lg rounded-pill fw-800 py-3 fs-2">CETAK STRUK</button>
                        <a href="{{ url('/ambil-antrian') }}"
                            class="btn btn-link text-decoration-none fw-800 fs-4 text-muted">SELESAI</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        /**
         * Mengambil antrian secara langsung dengan pengecekan kuota harian.
         * @param {HTMLElement} btn - Elemen tombol yang diklik
         * @param {number} layananId - ID Layanan yang dipilih
         * @param {boolean} isFull - Status apakah kuota sudah penuh (dari backend)
         * @param {string} namaLayanan - Nama layanan untuk pesan error
         * @param {number} kuota - Jumlah batas kuota harian
         */
        function ambilAntrianLangsung(btn, layananId, isFull, namaLayanan, kuota) {
            // 1. CEK JIKA KUOTA SUDAH PENUH (Client-side Protection)
            if (isFull) {
                Swal.fire({
                    icon: 'error',
                    title: '<span style="font-weight:800;">KUOTA PENUH</span>',
                    html: `Mohon maaf, pendaftaran untuk <b>${namaLayanan}</b> hari ini sudah mencapai batas (<b>${kuota} antrian</b>).<br><br>Silahkan mencoba kembali besok hari.`,
                    confirmButtonText: 'MENGERTI',
                    confirmButtonColor: '#0d6efd',
                    padding: '2em',
                    customClass: {
                        title: 'text-danger',
                        confirmButton: 'rounded-pill px-5 py-3 fw-bold'
                    }
                });
                return; // Batalkan proses submit
            }

            // 2. PROSES SUBMIT JIKA KUOTA TERSEDIA
            const form = document.getElementById('formAntrianOtomatis');
            const inputLayanan = document.getElementById('selected_layanan_id');

            if (!form || !inputLayanan) return;

            // Kunci tombol agar tidak bisa diklik lagi (mencegah double entry)
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> MEMPROSES...';
            btn.classList.replace('btn-primary', 'btn-secondary');

            // Set data dan kirim
            inputLayanan.value = layananId;

            // Timeout singkat untuk memastikan visual update sebelum halaman berpindah
            setTimeout(() => {
                form.submit();
            }, 150);
        }

        // Tampilkan Modal Sukses setelah Redirect (jika berhasil)
        @if(session('success_data'))
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('modalSukses');
            if (modalElement) {
                const modalSuksesObj = new bootstrap.Modal(modalElement);
                modalSuksesObj.show();
            }
        });
        @endif

        // Tangkap Error dari Backend (Misal: sistem tiba-tiba off atau kuota mendadak habis)
        @if(session('error'))
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'warning',
                title: 'PEMBERITAHUAN',
                text: "{{ session('error') }}",
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'OK'
            });
        });
        @endif

        function printAntrian() {
            window.print();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>