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
            --bg-light: #f8fafc;
            --text-main: #0f172a;
            --accent-blue: #0d6efd;
            --card-border: #e2e8f0;
            --header-dark: #1e293b;
            --success-green: #10b981;
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
            background: #ffffff;
            padding: 1.5vh 4vw;
            border-bottom: 3px solid var(--accent-blue);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .title-app { font-weight: 800; font-size: clamp(1.2rem, 2vw, 1.8rem); margin: 0; color: var(--header-dark); }
        #clock-time { font-weight: 800; font-size: clamp(1.8rem, 3.5vw, 2.5rem); line-height: 1; color: var(--accent-blue); }
        #clock-date { font-size: 0.9rem; color: #64748b; font-weight: 700; text-transform: uppercase; }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2vh;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 40px 40px;
            overflow: hidden;
        }

        .row-antrian {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-content: center;
            gap: 20px;
            width: 100%;
            height: 100%;
        }

        .card-loket {
            background: #ffffff;
            border-radius: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            border: 2px solid var(--card-border);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
        }

        /* PERBAIKAN: Status Selesai tetap berwarna biru cerah */
        .status-selesai {
            opacity: 1; /* Menghilangkan efek pudar */
            border-color: var(--card-border);
        }
        
        /* Menghapus pewarnaan abu-abu pada nomor antrian agar tetap biru */
        .status-selesai .nomor-antrian { 
            color: var(--accent-blue) !important; 
        }

        .status-selesai .loket-header { 
            background: var(--header-dark); /* Tetap konsisten dengan header loket lain */
        }

        .calling-now {
            animation: pulse-border 2s infinite;
            border-color: var(--accent-blue) !important;
            transform: scale(1.05);
            z-index: 100;
            background-color: #fff;
        }

        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0px rgba(13, 110, 253, 0.7); }
            70% { box-shadow: 0 0 0 30px rgba(13, 110, 253, 0); }
            100% { box-shadow: 0 0 0 0px rgba(13, 110, 253, 0); }
        }

        .loket-header {
            background: var(--header-dark);
            color: #ffffff;
            padding: 1.2vh;
            font-size: 1.2rem;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
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
            font-weight: 900;
            color: var(--accent-blue);
            margin: 0;
            line-height: 0.9;
            letter-spacing: -2px;
            transition: color 0.3s ease;
        }

        .layanan-tag {
            background: #f1f5f9;
            color: #334155;
            padding: 0.8vh 1.5vw;
            border-radius: 12px;
            font-weight: 700;
            margin-top: 1.5vh;
            border: 1px solid #e2e8f0;
            width: 95%;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .footer-bar {
            background: var(--header-dark);
            height: 6vh;
            display: flex;
            align-items: center;
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
            flex-shrink: 0;
            border-top: 4px solid var(--accent-blue);
        }

        #audio-lock {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white; z-index: 9999;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            cursor: pointer; text-align: center;
        }
    </style>
</head>
<body>

    <div id="audio-lock" onclick="enableAudio()">
        <div class="p-5 rounded-4 bg-white bg-opacity-10 backdrop-blur text-center">
            <i class="fas fa-volume-up fa-5x mb-4 text-primary animate-bounce"></i>
            <h1 class="fw-bold display-4 mb-3">SISTEM ANTRIAN AKTIF</h1>
            <p class="fs-4 mb-4">Klik di mana saja untuk mengaktifkan suara panggilan</p>
            <button class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold shadow-lg">BUKA MONITOR</button>
        </div>
    </div>

    <header class="header-monitor">
        <h1 class="title-app">
            <i class="fas fa-circle-nodes me-2 text-primary"></i>
            MONITOR ANTRIAN TERPADU
        </h1>
        <div class="text-end">
            <div id="clock-time">00:00:00</div>
            <div id="clock-date">Memuat Tanggal...</div>
        </div>
    </header>

    <main class="main-content">
        <div id="antrian-container" class="row-antrian">
            </div>
    </main>

    <footer class="footer-bar">
        <marquee scrollamount="8">
            &bull; Selamat Datang di Layanan Publik &bull; Budayakan Mengantri demi Kenyamanan Bersama &bull; Pastikan Dokumen Anda Lengkap Sebelum Menuju Loket Pengambilan &bull; Layanan Kami Gratis Tidak Dipungut Biaya Tambahan &bull;
        </marquee>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let lastTokenMap = {}; 
        let lastRawData = [];
        let speechQueue = [];
        let isSpeaking = false;
        let audioEnabled = false;
        let indonesianVoice = null;

        const bell = new Audio("https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3");

        function loadVoices() {
            const voices = window.speechSynthesis.getVoices();
            indonesianVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes('Google')) || 
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
                ejaanAngka = "kosong, kosong, kosong";
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
            
            renderUI(lastRawData, item.loket);

            bell.play().then(() => {
                setTimeout(() => {
                    const nomorEja = formatEjaan(item.nomor);
                    const teks = `Nomor antrian, ${nomorEja}, silakan menuju ke, ${item.loket}`;
                    const utter = new SpeechSynthesisUtterance(teks);
                    utter.lang = 'id-ID';
                    utter.rate = 0.85; 
                    utter.pitch = 1.0; 
                    
                    if (indonesianVoice) utter.voice = indonesianVoice;

                    utter.onend = function() {
                        isSpeaking = false;
                        renderUI(lastRawData, null); 
                        setTimeout(processQueue, 1500);
                    };
                    window.speechSynthesis.speak(utter);
                }, 1000);
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
                    const tokenSekarang = item.updated_token;

                    if (item.status === 'dipanggil' && !nomorSekarang.endsWith('000')) {
                        if (lastTokenMap[loketKey] !== tokenSekarang) {
                            if (audioEnabled) {
                                speechQueue.push({ nomor: nomorSekarang, loket: loketKey });
                                hasNewCall = true;
                            }
                        }
                    }
                    lastTokenMap[loketKey] = tokenSekarang;
                });

                lastRawData = data;
                if (!isSpeaking) renderUI(data, null);
                if (hasNewCall && !isSpeaking) processQueue();
            })
            .fail(function() {
                console.log("Gagal mengambil data dari server.");
            });
        }

        function renderUI(data, activeLoketName) {
            let html = '';
            if(!data || data.length === 0) {
                html = `<div class="text-center text-muted w-100 mt-5"><h2>MENUNGGU PETUGAS AKTIF...</h2></div>`;
            } else {
                const count = data.length;
                let basis, headerSize, numberSize, tagSize;

                if (count <= 3) {
                    basis = '30%'; headerSize = '1.8rem'; numberSize = '8rem'; tagSize = '1.4rem';
                } else if (count <= 6) {
                    basis = '30%'; headerSize = '1.3rem'; numberSize = '6rem'; tagSize = '1.1rem';
                } else {
                    basis = '22%'; headerSize = '1.1rem'; numberSize = '4.5rem'; tagSize = '0.9rem';
                }

                data.forEach(q => {
                    const isActive = (q.loket.nama_loket === activeLoketName);
                    const activeClass = isActive ? 'calling-now' : '';
                    
                    const isFinished = q.layanan.nama_layanan.includes('(Selesai)') || 
                                     q.layanan.nama_layanan.includes('(Menuju Pengambilan)');
                    const finishedClass = isFinished ? 'status-selesai' : '';

                    html += `
                    <div class="card-loket ${activeClass} ${finishedClass}" style="flex: 1 1 ${basis}; max-width: 450px;">
                        <div class="loket-header" style="font-size: ${headerSize}">${q.loket.nama_loket}</div>
                        <div class="card-body">
                            <div class="nomor-antrian" style="font-size: ${numberSize}">${q.nomor_antrian}</div>
                            <div class="layanan-tag" style="font-size: ${tagSize}">
                                <i class="fas fa-tag me-1 small"></i> ${q.layanan.nama_layanan}
                            </div>
                        </div>
                    </div>`;
                });
            }
            $('#antrian-container').html(html);
        }

        $(document).ready(function() {
            $.get('/api/display-data', function(data) {
                if(data && data.length > 0) {
                    data.forEach(item => { 
                        lastTokenMap[item.loket.nama_loket] = item.updated_token; 
                    });
                    lastRawData = data;
                    renderUI(data, null);
                }
            });

            setInterval(updateDisplay, 2000);

            setInterval(() => {
                const now = new Date();
                $('#clock-time').text(
                    now.getHours().toString().padStart(2, '0') + ':' + 
                    now.getMinutes().toString().padStart(2, '0') + ':' + 
                    now.getSeconds().toString().padStart(2, '0')
                );
                $('#clock-date').text(now.toLocaleDateString('id-ID', { 
                    weekday:'long', day:'numeric', month:'long', year:'numeric' 
                }));
            }, 1000);
        });
    </script>
</body>
</html>