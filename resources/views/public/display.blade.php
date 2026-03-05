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
            --bg-light: #ffffff;
            --text-main: #0f172a;
            --accent-blue: #0d6efd;
            --card-border: #e2e8f0;
            --header-dark: #1e293b;
        }

        body, html {
            height: 100vh;
            margin: 0; padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .header-monitor {
            background: #f8fafc;
            padding: 1.5vh 4vw;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title-app { font-weight: 800; font-size: clamp(1.2rem, 2.5vw, 2rem); margin: 0; }
        #clock-time { font-weight: 800; font-size: clamp(1.8rem, 3.5vw, 2.5rem); line-height: 1; }
        #clock-date { font-size: 0.9rem; color: #64748b; font-weight: 600; }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2vh 2vw;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .row-antrian {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            width: 100%;
            max-width: 1600px;
            justify-content: center;
        }

        .card-loket {
            background: #ffffff;
            border-radius: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            flex: 0 1 350px; 
            overflow: hidden;
            border: 2px solid var(--card-border);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .calling-now {
            animation: pulse-border 1.2s infinite;
            border-color: var(--accent-blue) !important;
            transform: scale(1.08);
            z-index: 10;
            background-color: #f0f7ff;
        }

        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0px rgba(13, 110, 253, 0.5); }
            70% { box-shadow: 0 0 0 25px rgba(13, 110, 253, 0); }
            100% { box-shadow: 0 0 0 0px rgba(13, 110, 253, 0); }
        }

        .loket-header {
            background: var(--header-dark);
            color: #ffffff;
            padding: 1.5vh;
            font-size: 1.4rem;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3vh 1vh !important;
        }

        .nomor-antrian {
            font-size: clamp(4.5rem, 9vw, 7.5rem);
            font-weight: 900;
            color: var(--accent-blue);
            margin: 0;
            line-height: 1;
        }

        .layanan-tag {
            background: #f1f5f9;
            color: #334155;
            padding: 0.8vh 1.5vw;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1.1rem;
            margin-top: 2vh;
            border: 1px solid #cbd5e1;
            width: 90%;
            text-align: center;
        }

        .footer-bar {
            background: var(--accent-blue);
            height: 6vh;
            display: flex;
            align-items: center;
            font-size: 1.2rem;
            font-weight: 700;
            color: white;
        }

        #audio-lock {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.98);
            color: white; z-index: 9999;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            cursor: pointer; text-align: center;
        }
    </style>
</head>
<body>

    <div id="audio-lock" onclick="enableAudio()">
        <i class="fas fa-volume-up fa-5x mb-4 text-primary animate-bounce"></i>
        <h1 class="fw-bold display-4">KLIK UNTUK MENGAKTIFKAN</h1>
        <p class="fs-4">Monitor memerlukan izin audio untuk memutar suara antrian</p>
        <button class="btn btn-primary btn-lg mt-4 px-5 rounded-pill fw-bold">AKTIFKAN SUARA</button>
    </div>

    <header class="header-monitor">
        <h1 class="title-app"><i class="fas fa-university me-2 text-primary"></i>SISTEM ANTRIAN DUKCAPIL</h1>
        <div class="text-end">
            <div id="clock-time">00:00:00</div>
            <div id="clock-date">Memuat...</div>
        </div>
    </header>

    <main class="main-content">
        <div id="antrian-container" class="row-antrian">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    </main>

    <footer class="footer-bar">
        <marquee scrollamount="10">
            &bull; Selamat Datang di Kantor Layanan Dukcapil &bull; Layanan Cepat, Akurat, dan Transparan &bull; Silakan Menunggu Nomor Anda Dipanggil &bull;
        </marquee>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let lastDataMap = {};
        let lastRawData = [];
        let speechQueue = [];
        let isSpeaking = false;
        let audioEnabled = false;
        let indonesianVoice = null;

        const bell = new Audio("https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3");

        // Fungsi memuat suara (Khusus Chrome/Edge)
        function loadVoices() {
            const voices = window.speechSynthesis.getVoices();
            // Prioritas 1: Google Bahasa Indonesia (Wanita Jernih)
            // Prioritas 2: Suara ID lokal apa saja
            indonesianVoice = voices.find(v => v.name.includes('Indonesian') || v.lang === 'id-ID');
        }

        window.speechSynthesis.onvoiceschanged = loadVoices;
        loadVoices();

        function enableAudio() {
            audioEnabled = true;
            $('#audio-lock').fadeOut();
            // Pancingan suara kosong agar browser mengizinkan audio kedepannya
            const silent = new SpeechSynthesisUtterance('');
            window.speechSynthesis.speak(silent);
        }

        function processQueue() {
            if (isSpeaking || speechQueue.length === 0) return;

            isSpeaking = true;
            const item = speechQueue.shift();
            
            // Efek visual kartu berkedip
            renderUI(lastRawData, item.loket);

            // 1. Putar Bell
            bell.play().catch(e => console.error("Audio error:", e));

            // 2. Jeda setelah bell baru bicara
            setTimeout(() => {
                const nomorEja = item.nomor.split('').join(', ');
                const teks = `Nomor antrian, ${nomorEja}, silakan menuju ke, ${item.loket}`;
                
                const utter = new SpeechSynthesisUtterance(teks);
                utter.lang = 'id-ID';
                utter.rate = 0.9;  // Kecepatan natural
                utter.pitch = 1.2; // Pitch suara wanita sedikit tinggi agar jernih
                
                if (indonesianVoice) {
                    utter.voice = indonesianVoice;
                }

                utter.onend = function() {
                    isSpeaking = false;
                    renderUI(lastRawData, null); // Hapus efek visual
                    setTimeout(processQueue, 1000); // Jeda antar panggilan
                };

                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(utter);
            }, 1500);
        }

        function updateDisplay() {
            $.get('/api/display-data', function(data) {
                if (!data) return;
                lastRawData = data;

                data.forEach(item => {
                    const loketKey = item.loket.nama_loket;
                    const nomorSekarang = item.nomor_antrian;

                    // Deteksi perubahan nomor pada loket yang sama
                    if (lastDataMap[loketKey] !== undefined && lastDataMap[loketKey] !== nomorSekarang) {
                        if (audioEnabled) {
                            speechQueue.push({ nomor: nomorSekarang, loket: loketKey });
                            processQueue();
                        }
                    }
                    
                    lastDataMap[loketKey] = nomorSekarang;
                });

                if (!isSpeaking) {
                    renderUI(data, null);
                }
            });
        }

        function renderUI(data, activeLoketName) {
            let html = '';
            if(data.length === 0) {
                html = `<div class="text-center text-muted"><h2>BELUM ADA ANTRIAN AKTIF HARI INI</h2></div>`;
            } else {
                data.forEach(q => {
                    const activeClass = (q.loket.nama_loket === activeLoketName) ? 'calling-now' : '';
                    html += `
                    <div class="card-loket ${activeClass}">
                        <div class="loket-header">${q.loket.nama_loket}</div>
                        <div class="card-body">
                            <div class="nomor-antrian">${q.nomor_antrian}</div>
                            <div class="layanan-tag">${q.layanan.nama_layanan}</div>
                        </div>
                    </div>`;
                });
            }
            $('#antrian-container').html(html);
        }

        // Jalankan sinkronisasi realtime 3 detik
        setInterval(updateDisplay, 3000);
        
        // Inisialisasi awal tanpa suara
        $.get('/api/display-data', function(data) {
            data.forEach(item => {
                lastDataMap[item.loket.nama_loket] = item.nomor_antrian;
            });
            lastRawData = data;
            renderUI(data, null);
        });

        // Jam Digital
        setInterval(() => {
            const now = new Date();
            $('#clock-time').text(now.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false }).replace(/\./g, ':'));
            $('#clock-date').text(now.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' }));
        }, 1000);
    </script>
</body>
</html>