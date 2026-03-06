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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fa;
            color: #2d3436;
        }

        .btn-back-top {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: #fff;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            text-decoration: none;
            color: #636e72;
            font-weight: 600;
            display: flex;
            align-items: center;
            border: 1px solid #e0e6ed;
        }

        .btn-back-top:hover {
            background: #f8f9fa;
            transform: translateX(-5px);
            color: #0d6efd;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .hero-section {
            padding: 80px 0 40px;
            background: linear-gradient(135deg, #ffffff 0%, #eef2f7 100%);
            border-bottom: 1px solid #e0e6ed;
            margin-bottom: 40px;
        }

        .card-layanan {
            border: none;
            border-radius: 25px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: #fff;
        }

        .card-layanan:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
        }

        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: rgba(13, 110, 253, 0.08);
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            margin: 0 auto 20px;
            transition: 0.3s;
        }

        .card-layanan:hover .icon-wrapper {
            background: #0d6efd;
            color: #fff;
            transform: scale(1.1);
        }

        .modal-content {
            border-radius: 30px;
            border: none;
        }

        .form-control-custom {
            border-radius: 15px;
            padding: 15px;
            border: 2px solid #eee;
            background-color: #f9f9f9;
        }

        .form-control-custom:focus {
            box-shadow: none;
            border-color: #0d6efd;
            background-color: #fff;
        }

        .btn-ambil {
            padding: 15px;
            border-radius: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Area Cetak */
        #printArea {
            background: #fff;
            border: 2px dashed #d1d8e0;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
        }

        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { 
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 100%; 
                border: none;
            }
        }
    </style>
</head>
<body>

<a href="{{ url('/') }}" class="btn-back-top">
    <i class="fas fa-arrow-left me-2"></i> Beranda
</a>

<div class="hero-section text-center">
    <div class="container">
        <h1 class="display-5 fw-800 mb-2">Pilih Jenis Layanan</h1>
        <p class="lead text-muted px-md-5">Silahkan pilih kategori layanan di bawah ini untuk mendapatkan nomor urut pelayanan Anda.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4 justify-content-center">
        @foreach($layanans as $l)
        <div class="col-md-4">
            <div class="card card-layanan shadow-sm h-100 p-4 text-center">
                <div class="card-body d-flex flex-column">
                    <div class="icon-wrapper">
                        {{-- Mengambil ikon langsung dari Database --}}
                        <i class="{{ $l->icon ?? 'fas fa-file-lines' }} fa-4x"></i>
                    </div>
                    <h3 class="fw-bold mb-3">{{ $l->nama_layanan }}</h3>
                    
                    {{-- Deskripsi dinamis dari Database --}}
                    <p class="text-muted small mb-4">
                        {{ $l->deskripsi ?? 'Pengurusan administrasi kependudukan untuk layanan ' . $l->nama_layanan . '.' }}
                    </p>
                    
                    <button type="button" 
                            class="btn btn-primary btn-lg mt-auto rounded-pill fw-bold" 
                            onclick="pilihLayanan('{{ $l->id }}', '{{ $l->nama_layanan }}', {{ $l->is_nik_required ? 'true' : 'false' }})">
                        Pilih Layanan
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Modal Input Nama & NIK --}}
<div class="modal fade" id="modalInputAntrian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 pt-4 px-4">
                <h4 class="modal-title fw-bold" id="titleLayanan">Detail Pendaftaran</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('user.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="layanan_id" id="selected_layanan_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control form-control-custom" placeholder="Masukkan nama Anda" required>
                    </div>

                    <div class="mb-4" id="nik_container">
                        <label class="form-label fw-bold">NIK (16 Digit)</label>
                        <input type="number" name="nik" id="input_nik" class="form-control form-control-custom" placeholder="Masukkan 16 digit NIK" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="16">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-ambil shadow">
                        <i class="fas fa-ticket-alt me-2"></i> AMBIL NOMOR ANTRIAN
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Sukses --}}
@if(session('success_data'))
<div class="modal fade" id="modalSukses" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <div class="text-success mb-4">
                    <i class="fas fa-check-circle fa-6x"></i>
                </div>
                <h2 class="fw-bold mb-1">Berhasil!</h2>
                <p class="text-muted">Nomor antrian Anda telah diterbitkan.</p>

                <div id="printArea" class="my-4">
                    <h6 class="text-uppercase text-muted mb-1 small">Nomor Antrian</h6>
                    <h1 class="display-1 fw-bold text-primary mb-0" style="letter-spacing: -3px;">
                        {{ session('success_data')['nomor'] }}
                    </h1>
                    <div class="my-3">
                        <hr>
                        <p class="fw-bold mb-0">{{ session('success_data')['layanan'] }}</p>
                        <small class="text-muted">{{ session('success_data')['waktu'] }} WIB</small>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button onclick="printAntrian()" class="btn btn-primary btn-lg rounded-pill fw-bold">
                        <i class="fas fa-print me-2"></i> CETAK NOMOR ANTRIAN
                    </button>
                    <a href="{{ url('/ambil-antrian') }}" class="btn btn-outline-dark btn-lg rounded-pill fw-bold border-2">
                        KEMBALI
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function pilihLayanan(id, nama, nikRequired) {
        document.getElementById('selected_layanan_id').value = id;
        document.getElementById('titleLayanan').innerText = 'Daftar ' + nama;
        
        const nikContainer = document.getElementById('nik_container');
        const inputNik = document.getElementById('input_nik');
        
        if (nikRequired) {
            nikContainer.style.display = 'block';
            inputNik.setAttribute('required', 'required');
        } else {
            nikContainer.style.display = 'none';
            inputNik.removeAttribute('required');
            inputNik.value = ''; 
        }

        const myModal = new bootstrap.Modal(document.getElementById('modalInputAntrian'));
        myModal.show();
    }

    @if(session('success_data'))
        document.addEventListener('DOMContentLoaded', function() {
            const suksesModal = new bootstrap.Modal(document.getElementById('modalSukses'));
            suksesModal.show();
        });
    @endif

    function printAntrian() {
        window.print();
    }
</script>

</body>
</html>