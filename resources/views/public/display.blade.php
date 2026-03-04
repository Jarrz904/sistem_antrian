<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Antrian - Layanan Publik</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #0a0e17;
            --accent-blue: #4cc9f0;
            --accent-pink: #f72585;
            --card-bg: rgba(255, 255, 255, 0.98);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at center, #111827 0%, #030712 100%);
            color: #fff;
            height: 100vh;
            margin: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* HEADER DYNAMIS */
        .header-monitor {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(15px);
            padding: 1.5vh 4vw; /* Menggunakan viewport units */
            border-bottom: 2px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .title-app {
            font-weight: 800;
            font-size: 2rem; /* Skala font menyesuaikan */
            margin: 0;
            background: linear-gradient(to right, #fff, var(--accent-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .date-container { text-align: right; }
        #clock-time {
            display: block;
            font-weight: 800;
            font-size: 2.5rem;
            line-height: 1;
            color: var(--accent-blue);
        }
        #clock-date {
            font-weight: 600;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
        }

        /* MAIN CONTENT - AUTO SCALE */
        .main-content {
            flex: 1;
            padding: 2vh 2vw;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .row-antrian {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            width: 100%;
            height: 100%;
            justify-content: center;
            align-content: center; /* Memastikan kartu di tengah jika baris sedikit */
        }

        /* KARTU LOKET RESPONSIF */
        .card-loket {
            background: var(--card-bg);
            border-radius: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            /* Ukuran kartu menyesuaikan jumlah loket (maksimal 4 per baris di monitor besar) */
            flex: 1 1 calc(25% - 40px); 
            min-width: 300px;
            max-height: 80vh; 
            overflow: hidden;
            border: none;
        }

        .loket-header {
            background: #1e293b;
            color: #fff;
            padding: 1.5vh;
            font-size: 1.8rem;
            font-weight: 800;
            text-align: center;
            border-bottom: 6px solid var(--accent-blue);
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2vh !important;
            flex: 1;
        }

        .nomor-antrian {
            /* Menggunakan clamp agar teks tidak pecah di laptop dan tetap raksasa di TV */
            font-size: clamp(5rem, 15vh, 12rem);
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            line-height: 1;
            letter-spacing: -2px;
        }

        .layanan-tag {
            background: #f1f5f9;
            color: #475569;
            padding: 0.8vh 1.5vw;
            border-radius: 1rem;
            font-weight: 700;
            font-size: clamp(1rem, 2vh, 1.5rem);
            margin-top: 1vh;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        /* FOOTER */
        .footer-bar {
            background: var(--accent-pink);
            height: 5vh;
            min-height: 40px;
            display: flex;
            align-items: center;
            font-size: 1.2rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* MEDIA QUERIES UNTUK LAPTOP KECIL */
        @media (max-height: 700px) {
            .title-app { font-size: 1.5rem; }
            #clock-time { font-size: 1.8rem; }
            .loket-header { font-size: 1.4rem; }
            .nomor-antrian { font-size: clamp(4rem, 20vh, 7rem); }
        }
    </style>
</head>
<body>

    <header class="header-monitor">
        <h1 class="title-app">
            <i class="fas fa-university me-2"></i>PELAYANAN DUKCAPIL
        </h1>
        <div class="date-container">
            <span id="clock-time">00:00:00</span>
            <span id="clock-date">Memuat Tanggal...</span>
        </div>
    </header>

    <main class="main-content">
        <div id="antrian-container" class="row-antrian">
            <div class="text-center">
                <div class="spinner-border text-info" style="width: 3rem; height: 3rem;"></div>
            </div>
        </div>
    </main>

    <footer class="footer-bar">
        <marquee scrollamount="8">
            &bull; Selamat Datang di Kantor Layanan Dukcapil &bull; Budayakan Mengantri dengan Tertib &bull; Layanan Kami Gratis Tidak Dipungut Biaya &bull;
        </marquee>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let lastData = "";

        function updateDisplay() {
            $.get('/api/display-data', function(data) {
                let currentDataString = JSON.stringify(data);
                if (currentDataString === lastData) return;
                lastData = currentDataString;

                let html = '';
                data.forEach(q => {
                    html += `
                    <div class="card-loket">
                        <div class="loket-header">${q.loket.nama_loket.toUpperCase()}</div>
                        <div class="card-body">
                            <div class="nomor-antrian">${q.nomor_antrian}</div>
                            <div class="layanan-tag">${q.layanan.nama_layanan}</div>
                        </div>
                    </div>`;
                });

                if(data.length === 0) {
                    html = `<h1 style="opacity:0.2; font-size:4rem; font-weight:800;">BELUM ADA ANTRIAN</h1>`;
                }

                $('#antrian-container').html(html);
            });
        }

        setInterval(updateDisplay, 3000);
        updateDisplay();

        setInterval(() => {
            const now = new Date();
            $('#clock-time').text(now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }));
            $('#clock-date').text(now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
        }, 1000);
    </script>
</body>
</html>