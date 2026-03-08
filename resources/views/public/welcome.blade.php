<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - Sistem Antrian Dukcapil</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #007bff;
            --accent-color: #6610f2;
            --glass-bg: rgba(255, 255, 255, 0.85);
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden; 
        }

        .full-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(125deg, #e0eafc, #cfdef3, #a1c4fd, #c2e9fb);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .main-container {
            width: 100%;
            max-width: 1200px;
        }

        .card-custom {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 30px;
            transition: all 0.4s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-custom:hover {
            transform: translateY(-15px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.1) !important;
        }

        .display-4 {
            font-weight: 800;
            font-size: clamp(2rem, 5vw, 3.5rem);
            color: #0f172a;
            letter-spacing: -1.5px;
        }

        .icon-circle {
            width: clamp(80px, 10vw, 110px);
            height: clamp(80px, 10vw, 110px);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: clamp(2rem, 4vw, 3rem);
            color: white;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .icon-primary { background: linear-gradient(135deg, #007bff, #00d4ff); }
        .icon-dark { background: linear-gradient(135deg, #1e293b, #475569); }

        .btn-custom {
            padding: 15px 30px;
            font-weight: 800;
            border-radius: 18px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: linear-gradient(to right, #007bff, #6610f2);
            border: none;
            color: white;
        }

        .footer-info {
            margin-top: 50px;
            font-weight: 700;
            color: #475569;
        }

        /* Modal Styles */
        .modal-content { border-radius: 25px; border: none; }
        
        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { 
                position: fixed; left: 0; top: 0; width: 100%; 
                text-align: center; padding: 30px; border: none !important;
            }
        }

        @media (min-width: 992px) {
            body { overflow: hidden; }
            .wrapper { padding: 0; }
        }

        @media (max-width: 768px) {
            .display-4 { margin-top: 20px; }
            .card-custom { padding: 20px !important; }
        }
    </style>
</head>
<body>

    <div class="full-bg"></div>

    <div class="wrapper">
        <div class="main-container">
            
            <div class="header-section text-center mb-5">
                <h1 class="display-4 mb-2">Sistem Antrian Publik</h1>
                <p class="lead text-secondary fw-bold px-3">Pelayanan Cepat, Nyaman, dan Transparan</p>
            </div>

            <div class="row g-4 g-lg-5 justify-content-center">
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="card card-custom shadow-sm p-4 text-center">
                        <div class="card-body py-4">
                            <div class="icon-circle icon-primary">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <h2 class="fw-bold mb-3">Ambil Antrian</h2>
                            <p class="text-secondary mb-4">Daftar secara mandiri untuk mendapatkan nomor urut pelayanan hari ini.</p>
                            <a href="{{ route('user.dashboard') }}" class="btn btn-primary-custom btn-custom btn-lg w-100 shadow">
                                Mulai Sekarang <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-5">
                    <div class="card card-custom shadow-sm p-4 text-center">
                        <div class="card-body py-4">
                            <div class="icon-circle icon-dark">
                                <i class="fas fa-desktop"></i>
                            </div>
                            <h2 class="fw-bold mb-3">Monitor Display</h2>
                            <p class="text-secondary mb-4">Pantau antrian yang sedang dipanggil melalui layar monitor display.</p>
                            <a href="{{ route('display') }}" class="btn btn-dark btn-custom btn-lg w-100 shadow" style="background: #1e293b; border: none;">
                                Buka Monitor <i class="fas fa-tv ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-info text-center">
                <div class="badge bg-white text-dark shadow-sm p-2 px-3 mb-2" style="border-radius: 10px;">
                    <i class="fas fa-clock me-2 text-primary"></i> 08.00 - 15.00 WIB
                </div>
                <div class="small opacity-75 d-block mt-2">
                    © {{ date('Y') }} Dinas Kependudukan dan Pencatatan Sipil
                </div>
            </div>

        </div>
    </div>

    @if(session('success_data'))
    <div class="modal fade" id="modalSukses" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4 shadow-lg">
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
                        <p class="mb-0 text-dark">{{ session('success_data')['nama'] }}</p>
                        <small class="text-muted">{{ session('success_data')['waktu'] }}</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button onclick="window.print()" class="btn btn-primary btn-lg rounded-pill fw-bold shadow">
                            <i class="fas fa-print me-2"></i>CETAK NOMOR
                        </button>
                        <button type="button" class="btn btn-light btn-lg rounded-pill fw-bold border" data-bs-dismiss="modal">
                            TUTUP
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Trigger modal sukses otomatis jika data session tersedia
        @if(session('success_data'))
            document.addEventListener('DOMContentLoaded', function() {
                const elSukses = document.getElementById('modalSukses');
                if (elSukses) {
                    const modalSuksesObj = new bootstrap.Modal(elSukses);
                    modalSuksesObj.show();
                }
            });
        @endif
    </script>
</body>
</html>