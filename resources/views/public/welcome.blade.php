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
            /* Scroll hanya aktif di mobile jika konten melebihi layar */
            overflow-x: hidden; 
        }

        /* Background Gradient Bergerak */
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

        /* Wrapper Responsif */
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

        /* Glassmorphism Card */
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
            font-size: clamp(2rem, 5vw, 3.5rem); /* Ukuran font dinamis */
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

        /* Footer Info */
        .footer-info {
            margin-top: 50px;
            font-weight: 700;
            color: #475569;
        }

        /* Penyesuaian khusus Monitor/Layar Lebar */
        @media (min-width: 992px) {
            body { overflow: hidden; } /* Lock scroll di monitor */
            .wrapper { padding: 0; }
        }

        /* Penyesuaian khusus Mobile */
        @media (max-width: 768px) {
            .display-4 { margin-top: 20px; }
            .card-custom { padding: 20px !important; }
            .footer-info { position: static; margin-bottom: 20px; }
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>