<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Antrian - Sistem Layanan Publik</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --text-dark: #1a1a1a;
            --text-muted: #333333;
        }

        html, body {
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
            overflow: hidden; /* Mencegah overflow keluar dari layar */
        }

        /* LOGIC GRID DINAMIS */
        .services-grid {
            display: grid;
            /* Default: 3 Kolom */
            grid-template-columns: repeat(3, 1fr);
            grid-auto-rows: 1fr;
            gap: 2vh 2vw;
            width: 100%;
            height: 100%;
        }

        /* Otomatis 4 Kolom jika data lebih dari 6 (Triggered by PHP Class) */
        .grid-layout-wide {
            grid-template-columns: repeat(4, 1fr) !important;
        }

        .card-layanan {
            border: 4px solid #e9ecef;
            border-radius: 30px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            padding: 2vh 2vw !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
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

        /* UKURAN TEXT DIPERBESAR */
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
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            z-index: 100;
            font-size: 1rem;
        }

        /* Modal Styles */
        .modal-content { border-radius: 35px; border: none; }
        .form-control-custom { 
            border-radius: 18px; padding: 18px; 
            background-color: #f8f9fa; border: 3px solid #dee2e6;
            font-size: 1.3rem; font-weight: 800;
        }

        @media (max-width: 992px) {
            .services-grid, .grid-layout-wide {
                grid-template-columns: repeat(2, 1fr) !important;
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
            <div class="card card-layanan">
                <div class="icon-wrapper">
                    <i class="{{ $l->icon ?? 'fas fa-file-invoice' }} fa-2x"></i>
                </div>
                <h3 class="card-title">{{ $l->nama_layanan }}</h3>
                <div class="card-text">
                    {{ $l->deskripsi ?? 'Silahkan ambil nomor antrian untuk mendapatkan pelayanan.' }}
                </div>
                <button type="button" 
                        class="btn btn-primary btn-pilih" 
                        onclick="pilihLayanan('{{ $l->id }}', '{{ $l->nama_layanan }}', {{ $l->is_nik_required ? 'true' : 'false' }})">
                    AMBIL NOMOR
                </button>
            </div>
            @endforeach
        </div>
    </main>
</div>

<div class="modal fade" id="modalInputAntrian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-body p-5">
                <div class="text-center mb-4">
                    <h1 class="fw-800 text-primary" id="titleLayanan" style="font-size: 2.2rem;">DETAIL ANTRIAN</h1>
                    <p class="text-muted fw-bold fs-5">Lengkapi data diri Anda</p>
                </div>

                <form action="{{ route('user.store') }}" method="POST" id="formAntrian">
                    @csrf
                    <input type="hidden" name="layanan_id" id="selected_layanan_id">
                    
                    <div class="mb-4">
                        <label class="form-label fw-800 fs-4">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control form-control-custom" placeholder="Tulis nama sesuai KTP" required autocomplete="off">
                    </div>

                    <div id="sub_layanan_container" style="display: none;" class="mb-4">
                        <label class="form-label fw-800 fs-4">Jenis Urusan</label>
                        <select id="sub_layanan_select" class="form-select form-control-custom" onchange="toggleNikBySubLayanan()">
                            <option value="kematian">Akte Kematian</option>
                            <option value="perkawinan_perceraian">Perkawinan / Perceraian</option>
                        </select>
                    </div>

                    <div class="mb-5" id="nik_container">
                        <label class="form-label fw-800 fs-4">Nomor NIK (16 Digit)</label>
                        <input type="number" name="nik" id="input_nik" class="form-control form-control-custom" placeholder="332..." 
                               oninput="javascript: if (this.value.length > 16) this.value = this.value.slice(0, 16);">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-800 shadow-lg" style="font-size: 1.8rem;">
                        KONFIRMASI
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('success_data'))
<div class="modal fade" id="modalSukses" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content text-center p-5">
            <div class="modal-body">
                <div class="text-success mb-3"><i class="fas fa-check-circle fa-7x"></i></div>
                <h1 class="fw-800 display-4">BERHASIL!</h1>
                <div id="printArea" class="border border-5 rounded-5 p-4 my-4 bg-white shadow-sm">
                    <p class="text-dark fw-800 fs-2 mb-0">NOMOR ANTRIAN</p>
                    <h1 class="display-1 fw-800 text-primary my-1" style="font-size: 7rem;">{{ session('success_data')['nomor'] }}</h1>
                    <h2 class="fw-800 text-dark fs-1">{{ strtoupper(session('success_data')['layanan']) }}</h2>
                    <p class="fw-800 text-muted fs-3">{{ session('success_data')['waktu'] }} WIB</p>
                </div>
                <div class="d-grid gap-3">
                    <button onclick="printAntrian()" class="btn btn-primary btn-lg rounded-pill fw-800 py-3 fs-2">CETAK STRUK</button>
                    <a href="{{ url('/ambil-antrian') }}" class="btn btn-link text-decoration-none fw-800 fs-4 text-muted">SELESAI</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let inputModalObj;

    function pilihLayanan(id, nama, nikRequired) {
        document.getElementById('selected_layanan_id').value = id;
        document.getElementById('titleLayanan').innerText = nama.toUpperCase();
        
        const subContainer = document.getElementById('sub_layanan_container');
        const nikContainer = document.getElementById('nik_container');
        const inputNik = document.getElementById('input_nik');

        document.getElementById('formAntrian').reset();
        document.getElementById('selected_layanan_id').value = id;

        // Logic khusus nama layanan tertentu
        if (nama.toLowerCase().includes("pencatatan sipil khusus")) {
            subContainer.style.display = 'block';
            toggleNikBySubLayanan(); 
        } else {
            subContainer.style.display = 'none';
            if (nikRequired) {
                nikContainer.style.display = 'block';
                inputNik.setAttribute('required', 'required');
            } else {
                nikContainer.style.display = 'none';
                inputNik.removeAttribute('required');
            }
        }
        
        if (!inputModalObj) {
            inputModalObj = new bootstrap.Modal(document.getElementById('modalInputAntrian'));
        }
        inputModalObj.show();
    }

    function toggleNikBySubLayanan() {
        const select = document.getElementById('sub_layanan_select');
        const nikContainer = document.getElementById('nik_container');
        const inputNik = document.getElementById('input_nik');
        if (select.value === 'kematian') {
            nikContainer.style.display = 'none';
            inputNik.removeAttribute('required');
        } else {
            nikContainer.style.display = 'block';
            inputNik.setAttribute('required', 'required');
        }
    }

    @if(session('success_data'))
        document.addEventListener('DOMContentLoaded', function() {
            const modalSuksesObj = new bootstrap.Modal(document.getElementById('modalSukses'));
            modalSuksesObj.show();
        });
    @endif

    function printAntrian() { window.print(); }
</script>

</body>
</html>