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
            padding: 1vh 4vw;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .title-app { font-weight: 800; font-size: clamp(1rem, 2vw, 1.8rem); margin: 0; }
        #clock-time { font-weight: 800; font-size: clamp(1.5rem, 3vw, 2.2rem); line-height: 1; }
        #clock-date { font-size: 0.85rem; color: #64748b; font-weight: 600; }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5vh;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 30px 30px;
            overflow: hidden;
        }

        .row-antrian {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-content: center;
            gap: 12px;
            width: 100%;
            height: 100%;
            max-width: 100%;
            margin: 0 auto;
        }

        .card-loket {
            background: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            border: 2px solid var(--card-border);
            transition: all 0.4s ease;
            overflow: hidden;
            min-width: 150px;
        }

        .calling-now {
            animation: pulse-border 1.5s infinite;
            border-color: var(--accent-blue) !important;
            transform: scale(1.03);
            z-index: 10;
            background-color: #f0f7ff;
        }

        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0px rgba(13, 110, 253, 0.6); }
            70% { box-shadow: 0 0 0 20px rgba(13, 110, 253, 0); }
            100% { box-shadow: 0 0 0 0px rgba(13, 110, 253, 0); }
        }

        .loket-header {
            background: var(--header-dark);
            color: #ffffff;
            padding: 0.8vh;
            font-size: 1rem;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5vh 1vh !important;
            flex: 1;
        }

        .nomor-antrian {
            font-weight: 900;
            color: var(--accent-blue);
            margin: 0;
            line-height: 1;
            letter-spacing: -1px;
        }

        .layanan-tag {
            background: #f1f5f9;
            color: #475569;
            padding: 0.5vh 1vw;
            border-radius: 8px;
            font-weight: 700;
            margin-top: 1vh;
            border: 1px solid #cbd5e1;
            width: 90%;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .footer-bar {
            background: var(--accent-blue);
            height: 5vh;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
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
        <i class="fas fa-volume-up fa-5x mb-4 text-primary"></i>
        <h1 class="fw-bold display-4">KLIK UNTUK MENGAKTIFKAN</h1>
        <p class="fs-4">Monitor memerlukan izin audio untuk memutar suara antrian</p>
        <button class="btn btn-primary btn-lg mt-4 px-5 rounded-pill fw-bold shadow">AKTIFKAN SEKARANG</button>
    </div>

    <header class="header-monitor">
        <h1 class="title-app"><i class="fas fa-university me-2 text-primary"></i>SISTEM ANTRIAN DUKCAPIL</h1>
        <div class="text-end">
            <div id="clock-time">00:00:00</div>
            <div id="clock-date">Memuat...</div>
        </div>
    </header>

    <main class="main-content">
        <div id="antrian-container" class="row-antrian"></div>
    </main>

    <footer class="footer-bar">
        <marquee scrollamount="10">
            &bull; Selamat Datang di Kantor Layanan Dukcapil &bull; Layanan Cepat, Akurat, dan Transparan &bull; Silakan Menunggu Nomor Anda Dipanggil &bull;
        </marquee>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // lastTokenMap menyimpan updated_token dari setiap loket untuk deteksi panggil ulang
        let lastTokenMap = {};
        let lastRawData = [];
        let speechQueue = [];
        let isSpeaking = false;
        let audioEnabled = false;
        let indonesianVoice = null;

        const bell = new Audio("https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3");

        function loadVoices() {
            const voices = window.speechSynthesis.getVoices();
            indonesianVoice = voices.find(v => v.lang === 'id-ID' && (v.name.includes('Female') || v.name.includes('Google'))) || 
                             voices.find(v => v.lang === 'id-ID');
        }

        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = loadVoices;
        }
        loadVoices();

        function enableAudio() {
            audioEnabled = true;
            $('#audio-lock').fadeOut();
            const silent = new SpeechSynthesisUtterance('');
            window.speechSynthesis.speak(silent);
        }

        function formatEjaan(nomor) {
            const huruf = nomor.charAt(0);
            const angkaPart = nomor.substring(1); 
            const angkaInt = parseInt(angkaPart);

            let ejaanAngka = "";

            if (angkaInt === 0) {
                ejaanAngka = angkaPart.split('').map(() => 'kosong').join(', ');
            } else if (angkaPart.startsWith('0')) {
                const matchNol = angkaPart.match(/^0+/);
                const jumlahNol = matchNol ? matchNol[0].length : 0;
                const stringNol = Array(jumlahNol).fill('kosong').join(', ');
                ejaanAngka = `${stringNol}, ${angkaInt}`;
            } else {
                ejaanAngka = angkaInt;
            }

            return `${huruf}, ${ejaanAngka}`;
        }

        function processQueue() {
            if (isSpeaking || speechQueue.length === 0) return;

            isSpeaking = true;
            const item = speechQueue.shift();
            
            // Highlight loket yang sedang dipanggil
            renderUI(lastRawData, item.loket);

            bell.play().then(() => {
                setTimeout(() => {
                    const nomorEja = formatEjaan(item.nomor);
                    const teks = `Nomor antrian, ${nomorEja}, silakan menuju ke, ${item.loket}`;
                    const utter = new SpeechSynthesisUtterance(teks);
                    utter.lang = 'id-ID';
                    utter.rate = 0.95; 
                    utter.pitch = 1.2; 
                    
                    if (indonesianVoice) utter.voice = indonesianVoice;

                    utter.onend = function() {
                        isSpeaking = false;
                        renderUI(lastRawData, null); 
                        setTimeout(processQueue, 1500);
                    };
                    window.speechSynthesis.speak(utter);
                }, 1200);
            }).catch(() => { isSpeaking = false; });
        }

        function updateDisplay() {
            $.get('/api/display-data')
            .done(function(data) {
                if (!data || data.length === 0) return;
                let hasNewCall = false;

                data.forEach(item => {
                    const loketKey = item.loket.nama_loket;
                    const nomorSekarang = item.nomor_antrian;
                    const tokenSekarang = item.updated_token; // Token unik (timestamp) dari backend

                    // LOGIKA BARU: Cek apakah status 'dipanggil' DAN token berubah (artinya ada klik panggil/panggil ulang)
                    if (item.status === 'dipanggil' && !nomorSekarang.endsWith('000')) {
                        if (lastTokenMap[loketKey] !== tokenSekarang) {
                            if (audioEnabled) {
                                speechQueue.push({ nomor: nomorSekarang, loket: loketKey });
                                hasNewCall = true;
                            }
                        }
                    }
                    
                    // Simpan token terakhir agar tidak terpanggil berulang kali saat interval refresh
                    lastTokenMap[loketKey] = tokenSekarang;
                });

                lastRawData = data;
                if (!isSpeaking) renderUI(data, null);
                if (hasNewCall && !isSpeaking) processQueue();
            });
        }

        function renderUI(data, activeLoketName) {
            let html = '';
            if(!data || data.length === 0) {
                html = `<div class="text-center text-muted w-100 mt-5"><h2>BELUM ADA PETUGAS AKTIF</h2></div>`;
            } else {
                const count = data.length;
                let basis, headerSize, numberSize, tagSize;

                if (count <= 3) {
                    basis = '30%'; headerSize = '1.8rem'; numberSize = '7rem'; tagSize = '1.3rem';
                } else if (count <= 6) {
                    basis = '45%'; headerSize = '1.4rem'; numberSize = '5.5rem'; tagSize = '1.1rem';
                } else if (count <= 8) {
                    basis = '23%'; headerSize = '1.1rem'; numberSize = '4.5rem'; tagSize = '0.9rem';
                } else {
                    basis = count > 10 ? '18%' : '23%'; 
                    headerSize = '0.9rem'; numberSize = '3.5rem'; tagSize = '0.8rem';
                }

                data.forEach(q => {
                    const isActive = (q.loket.nama_loket === activeLoketName);
                    const activeClass = isActive ? 'calling-now' : '';
                    html += `
                    <div class="card-loket ${activeClass}" style="flex: 1 1 ${basis}; max-width: ${basis === '30%' ? '450px' : '350px'};">
                        <div class="loket-header" style="font-size: ${headerSize}">${q.loket.nama_loket}</div>
                        <div class="card-body">
                            <div class="nomor-antrian" style="font-size: ${numberSize}">${q.nomor_antrian}</div>
                            <div class="layanan-tag" style="font-size: ${tagSize}">${q.layanan.nama_layanan}</div>
                        </div>
                    </div>`;
                });
            }
            $('#antrian-container').html(html);
        }

        $(document).ready(function() {
            // Load awal data untuk inisialisasi lastTokenMap agar tidak langsung bunyi saat page load
            $.get('/api/display-data', function(data) {
                if(data && data.length > 0) {
                    data.forEach(item => { 
                        lastTokenMap[item.loket.nama_loket] = item.updated_token; 
                    });
                    lastRawData = data;
                    renderUI(data, null);
                }
            });

            setInterval(updateDisplay, 3000);

            setInterval(() => {
                const now = new Date();
                const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                               now.getMinutes().toString().padStart(2, '0') + ':' + 
                               now.getSeconds().toString().padStart(2, '0');
                $('#clock-time').text(timeStr);
                $('#clock-date').text(now.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' }));
            }, 1000);
        });
    </script>
</body>
</html>