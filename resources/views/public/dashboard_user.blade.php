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
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
        }

        .page-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            padding: 20px;
        }

        .compact-header {
            text-align: center;
            margin-bottom: 20px;
            flex: 0 0 auto;
        }

        .header-title {
            font-weight: 800;
            color: #0d6efd;
            font-size: clamp(1.5rem, 4vh, 2.5rem);
            margin-bottom: 5px;
        }

        .header-subtitle {
            color: #636e72;
            font-size: clamp(0.9rem, 2vh, 1.1rem);
            margin: 0;
        }

        .content-section {
            flex: 1;
            display: flex;
            align-items: center; 
            overflow: hidden;
        }

        .card-layanan {
            border: none;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%; 
            display: flex;
            flex-direction: column;
            padding: 15px !important;
        }

        .card-layanan:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            background: rgba(13, 110, 253, 0.08);
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            margin: 0 auto 15px;
            flex-shrink: 0;
        }

        .card-title {
            font-weight: 700;
            font-size: clamp(1rem, 2.2vh, 1.3rem);
            margin-bottom: 8px;
            color: #2d3436;
        }

        .card-text {
            font-size: clamp(0.75rem, 1.8vh, 0.9rem);
            color: #636e72;
            margin-bottom: 15px;
            line-height: 1.4;
            flex-grow: 1; 
        }

        .btn-pilih {
            border-radius: 50px;
            font-weight: 700;
            padding: 10px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-back {
            position: absolute;
            top: 20px;
            left: 20px;
            text-decoration: none;
            color: #0d6efd;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            z-index: 10;
        }

        .modal-content { border-radius: 25px; border: none; }
        .form-control-custom { border-radius: 12px; padding: 12px; background-color: #f1f3f5; border: none; }

        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { 
                position: fixed; 
                left: 0; 
                top: 0; 
                width: 100%; 
                text-align: center; 
                padding: 50px;
                border: none !important;
                background: white !important;
            }
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <a href="{{ url('/') }}" class="btn-back">
        <i class="fas fa-arrow-left me-2"></i> Beranda
    </a>

    <header class="compact-header">
        <h1 class="header-title">Pilih Jenis Layanan</h1>
        <p class="header-subtitle">Silahkan pilih kategori untuk mendapatkan nomor antrian</p>
    </header>

    <main class="content-section">
        <div class="container-fluid px-md-5">
            <div class="row g-3 justify-content-center">
                @foreach($layanans as $l)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card card-layanan text-center">
                        <div class="icon-wrapper">
                            <i class="{{ $l->icon ?? 'fas fa-file-lines' }} fa-2x"></i>
                        </div>
                        <h3 class="card-title">{{ $l->nama_layanan }}</h3>
                        <p class="card-text">
                            {{ $l->deskripsi ?? 'Layanan administrasi ' . $l->nama_layanan . '.' }}
                        </p>
                        <button type="button" 
                                class="btn btn-primary btn-pilih w-100 shadow-sm" 
                                onclick="pilihLayanan('{{ $l->id }}', '{{ $l->nama_layanan }}', {{ $l->is_nik_required ? 'true' : 'false' }})">
                            Ambil Antrian
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="modalInputAntrian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0" id="titleLayanan">Detail Pendaftaran</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger small mb-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('user.store') }}" method="POST" id="formAntrian">
                    @csrf
                    <input type="hidden" name="layanan_id" id="selected_layanan_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control form-control-custom" placeholder="Masukkan nama Anda" required>
                    </div>

                    <div id="sub_layanan_container" style="display: none;" class="mb-3">
                        <label class="form-label fw-bold small">Pilih Jenis Urusan</label>
                        <select id="sub_layanan_select" class="form-select form-control-custom" onchange="toggleNikBySubLayanan()">
                            <option value="kematian">Akte Kematian (Tanpa NIK)</option>
                            <option value="perkawinan_perceraian">Perkawinan / Perceraian Non-Muslim (Wajib NIK)</option>
                        </select>
                    </div>

                    <div class="mb-4" id="nik_container">
                        <label class="form-label fw-bold small">NIK (16 Digit)</label>
                        <input type="number" name="nik" id="input_nik" class="form-control form-control-custom" placeholder="Masukkan NIK" 
                               oninput="javascript: if (this.value.length > 16) this.value = this.value.slice(0, 16);">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                        KONFIRMASI ANTRIAN
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('success_data'))
<div class="modal fade" id="modalSukses" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-body">
                <div class="text-success mb-3">
                    <i class="fas fa-check-circle fa-4x"></i>
                </div>
                <h3 class="fw-bold">Antrian Berhasil!</h3>
                <div id="printArea" class="border rounded-4 p-3 my-3 bg-light text-center">
                    <p class="text-muted small mb-1">NOMOR ANTRIAN</p>
                    <h1 class="display-2 fw-bold text-primary mb-0">{{ session('success_data')['nomor'] }}</h1>
                    <hr>
                    <p class="fw-bold mb-0 text-dark">{{ session('success_data')['layanan'] }}</p>
                    <small class="text-muted">{{ session('success_data')['waktu'] }} WIB</small>
                </div>
                <div class="d-grid gap-2">
                    <button onclick="printAntrian()" class="btn btn-primary btn-lg rounded-pill fw-bold shadow">CETAK NOMOR</button>
                    <a href="{{ url('/ambil-antrian') }}" class="btn btn-light rounded-pill fw-bold">TUTUP</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Inisialisasi variabel modal secara global untuk menghindari konflik re-inisialisasi
    let inputModalObj;

    function pilihLayanan(id, nama, nikRequired) {
        // Set data ke field hidden
        document.getElementById('selected_layanan_id').value = id;
        document.getElementById('titleLayanan').innerText = nama;
        
        const subContainer = document.getElementById('sub_layanan_container');
        const nikContainer = document.getElementById('nik_container');
        const inputNik = document.getElementById('input_nik');

        // Reset form
        document.getElementById('formAntrian').reset();
        document.getElementById('selected_layanan_id').value = id;

        // Logic Custom untuk Pencatatan Sipil Khusus
        if (nama.includes("Pencatatan Sipil Khusus")) {
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
                inputNik.value = '';
            }
        }
        
        // Membuka modal menggunakan instance Bootstrap
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
            inputNik.value = '';
        } else {
            nikContainer.style.display = 'block';
            inputNik.setAttribute('required', 'required');
        }
    }

    // Trigger modal sukses saat halaman selesai dimuat
    @if(session('success_data'))
        document.addEventListener('DOMContentLoaded', function() {
            const elSukses = document.getElementById('modalSukses');
            if (elSukses) {
                const modalSuksesObj = new bootstrap.Modal(elSukses);
                modalSuksesObj.show();
            }
        });
    @endif

    // Fungsi Cetak
    function printAntrian() {
        window.print();
    }
</script>

</body>
</html>