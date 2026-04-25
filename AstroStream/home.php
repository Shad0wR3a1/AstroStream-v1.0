<?php
session_start();

if(!isset($_SESSION['email'])){ 
    header("Location: index.php"); 
    exit();
}

$email_utente = $_SESSION['email']; 

// 2. CONNESSIONE
$host = "localhost";
$user = "root"; 
$pass = "";     
$dbname = "streaming_db"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// 3. RECUPERO DATI REALI + CONTROLLO PREMIUM
$stmt = $conn->prepare("SELECT nome, cognome, saldo, tipo_tessera, scadenza_tessera FROM utente WHERE email = ?");
$stmt->bind_param("s", $email_utente);
$stmt->execute();
$res_user = $stmt->get_result();
$dati_user = $res_user->fetch_assoc();

$is_premium = false;
if ($dati_user) {
    $nome_utente = htmlspecialchars($dati_user['nome'] . " " . $dati_user['cognome']);
    $saldo_reale = (float)$dati_user['saldo']; 
    
    if ($dati_user['tipo_tessera'] === 'premium' && strtotime($dati_user['scadenza_tessera']) >= time()) {
        $is_premium = true;
    }
} else {
    $nome_utente = "Membro Equipaggio";
    $saldo_reale = 0.00;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroStream | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --space-black: #0b0d17;
            --nebula-purple: #240046;
            --star-gold: #ff9e00;
            --starlight: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.07);
            --glass-border: rgba(255, 255, 255, 0.1);
            --neon-blue: #00d4ff;
            --accent-gradient: linear-gradient(45deg, #ff9e00, #ff5400);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Roboto', sans-serif;
            background: var(--space-black);
            background: radial-gradient(circle at center, var(--nebula-purple) 0%, var(--space-black) 100%);
            color: var(--starlight);
            min-height: 100vh;
            overflow-x: hidden;
        }
        #star-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; pointer-events: none; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 15px 8%; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(15px); border-bottom: 2px solid rgba(255, 158, 0, 0.2); position: sticky; top: 0; z-index: 100; }
        .logo h1 { font-family: 'Orbitron', sans-serif; font-size: 1.6rem; letter-spacing: 3px; }
        .logo span { color: var(--star-gold); text-shadow: 0 0 10px var(--star-gold); }
        nav ul { list-style: none; display: flex; gap: 20px; align-items: center; }
        nav a { text-decoration: none; color: var(--starlight); font-weight: bold; text-transform: uppercase; font-size: 0.85rem; transition: 0.3s; }
        nav a:hover { color: var(--star-gold); }
        .user-pill { background: var(--card-bg); padding: 5px 15px; border-radius: 20px; border: 1px solid var(--glass-border); display: flex; align-items: center; gap: 10px; font-size: 0.8rem; }
        .main-grid { max-width: 1400px; margin: 30px auto; padding: 0 5%; display: grid; grid-template-columns: 1fr 350px; gap: 30px; }
        @media (max-width: 1100px) { .main-grid { grid-template-columns: 1fr; } }
        .search-sector { margin-bottom: 40px; }
        .search-bar { width: 100%; padding: 15px 25px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--glass-border); color: white; outline: none; transition: 0.3s; }
        .search-bar:focus { border-color: var(--neon-blue); box-shadow: 0 0 15px rgba(0, 212, 255, 0.2); }
        .filter-tags { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
        .tag { padding: 6px 15px; border-radius: 15px; background: var(--card-bg); font-size: 0.75rem; cursor: pointer; border: 1px solid var(--glass-border); transition: 0.3s; }
        .tag.active, .tag:hover { background: var(--star-gold); color: black; font-weight: bold; }
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; }
        .movie-card { background: var(--card-bg); border-radius: 15px; overflow: hidden; border: 1px solid var(--glass-border); transition: 0.4s ease; display: flex; flex-direction: column; }
        .movie-card:hover { transform: translateY(-10px); border-color: var(--star-gold); }
        .poster-wrapper { position: relative; height: 320px; overflow: hidden; }
        .poster-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .movie-card:hover .poster-wrapper img { filter: brightness(0.3) blur(2px); transform: scale(1.1); }
        .btn-details { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0; background: var(--star-gold); border: none; padding: 10px 20px; border-radius: 20px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .movie-card:hover .btn-details { opacity: 1; }
        .movie-info { padding: 15px; flex-grow: 1; }
        .movie-info h4 { font-family: 'Orbitron', sans-serif; font-size: 0.9rem; margin-bottom: 5px; min-height: 2.2em; }
        .meta { display: flex; justify-content: space-between; font-size: 0.75rem; color: #aaa; }
        .sidebar-card { background: var(--card-bg); border: 1px solid var(--glass-border); border-radius: 20px; padding: 25px; backdrop-filter: blur(10px); height: fit-content; position: sticky; top: 100px; }
        .profile-header { text-align: center; margin-bottom: 25px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; }
        .badge-premium { background: var(--accent-gradient); color: black; padding: 4px 12px; border-radius: 10px; font-size: 0.7rem; font-weight: bold; display: inline-block; margin-top: 5px; }
        .stats-box { background: rgba(0, 0, 0, 0.3); border-radius: 12px; padding: 15px; margin: 15px 0; text-align: center; }
        .stats-box span { color: var(--star-gold); font-size: 1.5rem; font-weight: bold; display: block; }
        .btn-action { display: block; width: 100%; background: transparent; border: 1px solid var(--star-gold); color: var(--star-gold); padding: 12px; border-radius: 10px; cursor: pointer; font-weight: bold; text-decoration: none; text-align: center; transition: 0.3s; margin-bottom: 15px; font-family: 'Orbitron', sans-serif; font-size: 0.8rem; }
        .btn-action:hover { background: var(--star-gold); color: black; }
        footer { text-align: center; padding: 40px; color: #555; font-size: 0.8rem; }

        /* --- ASTROBOT POPUP --- */
        #bot-launcher {
            position: fixed; bottom: 30px; right: 30px;
            width: 70px; height: 70px;
            background: var(--accent-gradient);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 1000;
            box-shadow: 0 0 20px var(--star-gold);
            transition: 0.3s;
        }
        #bot-launcher:hover { transform: scale(1.1); box-shadow: 0 0 30px var(--star-gold); }

        #bot-container {
            position: fixed; bottom: 110px; right: 30px;
            width: 350px; height: 500px;
            background: rgba(11, 13, 23, 0.95);
            border: 1px solid var(--star-gold);
            border-radius: 20px;
            display: none; flex-direction: column;
            overflow: hidden; z-index: 1000;
            backdrop-filter: blur(20px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
        }

        .bot-header {
            background: var(--card-bg);
            padding: 15px; border-bottom: 1px solid var(--glass-border);
            display: flex; justify-content: space-between; align-items: center;
        }

        #chat-window {
            flex-grow: 1; padding: 15px;
            overflow-y: auto; display: flex; flex-direction: column; gap: 10px;
            scrollbar-width: thin; scrollbar-color: var(--star-gold) transparent;
        }

        .msg { padding: 10px 15px; border-radius: 12px; font-size: 0.85rem; max-width: 85%; line-height: 1.4; }
        .bot-msg { background: var(--card-bg); align-self: flex-start; border-left: 3px solid var(--star-gold); }
        .user-msg { background: var(--star-gold); color: black; align-self: flex-end; font-weight: 500; }

        .bot-input-area {
            padding: 15px; background: rgba(255,255,255,0.05);
            display: flex; gap: 10px;
        }

        #user-input {
            flex-grow: 1; background: transparent; border: 1px solid var(--glass-border);
            border-radius: 10px; padding: 8px; color: white; outline: none;
        }
    </style>
</head>
<body>

    <canvas id="star-canvas"></canvas>

    <header>
        <div class="logo">
            <h1>ASTRO<span>STREAM</span></h1>
        </div>
        <nav>
            <ul>
                <li class="user-pill">
                    <span style="color:var(--star-gold)">●</span> <?php echo $nome_utente; ?>
                </li>
            </ul>
        </nav>
    </header>

    <main class="main-grid">
        <section class="content-area">
            <div class="search-sector">
                <form action="ricerca.php" method="GET">
                    <input type="hidden" name="genere" value="Tutti">
                    <input type="text" name="query" class="search-bar" 
                        placeholder="Cerca un film (es. juju, naruto, op)..." 
                        autocomplete="off">
                </form>

                <form action="ricerca.php" method="GET" class="filter-tags">
                    <button type="submit" name="genere" value="Tutti" class="tag active">Tutti</button>
                    <button type="submit" name="genere" value="Shounen" class="tag">Shounen</button>
                    <button type="submit" name="genere" value="Action" class="tag">Action Opera</button>
                    <button type="submit" name="genere" value="Isekai" class="tag">Isekai</button>
                    <button type="submit" name="genere" value="Romance" class="tag">Romance</button>
                    <button type="submit" name="genere" value="Fantasy" class="tag">Fantasy</button>
                    <button type="submit" name="genere" value="Sports" class="tag">Sports</button>
                    <button type="submit" name="genere" value="Drama" class="tag">Drama</button>
                    <button type="submit" name="genere" value="Mystery" class="tag">Mystery</button>
                </form>
            </div>

            <h3 style="font-family:'Orbitron'; margin-bottom:20px; color:var(--star-gold)">In Orbita Ora</h3>
            
            <div class="movie-grid">
                <?php
                if (isset($conn)) {
                    $sql_film = "SELECT * FROM film";
                    $res_film = $conn->query($sql_film);

                    if ($res_film && $res_film->num_rows > 0) {
                        while($film = $res_film->fetch_assoc()) {
                            $path_immagine = "immagini/" . htmlspecialchars($film['immagine']); 
                            ?>
                            <div class="movie-card">
                                <div class="poster-wrapper">
                                    <img src="<?php echo $path_immagine; ?>" alt="<?php echo htmlspecialchars($film['titolo']); ?>">
                                    <a href="preview.php?id=<?php echo $film['cod_f']; ?>">
                                        <input type="button" class="btn-details" value="DETTAGLI">
                                    </a>
                                </div>
                                <div class="movie-info">
                                    <h4><?php echo strtoupper(htmlspecialchars($film['titolo'])); ?></h4>
                                    <p style="font-size:0.75rem; color:var(--star-gold); margin-bottom:8px;">
                                        <?php echo htmlspecialchars($film['regista']); ?>
                                    </p>
                                    <div class="meta">
                                        <span><?php echo htmlspecialchars($film['genere']); ?></span>
                                        <span>⭐ <?php echo number_format($film['v_medio'], 1); ?></span>
                                    </div>
                                    <div style="margin-top:12px; font-weight:bold; color:var(--starlight); font-size: 1.1rem;">
                                        <?php if ($is_premium): ?>
                                            <span style="color: var(--neon-blue); text-shadow: 0 0 5px var(--neon-blue); font-family: 'Orbitron'; font-size: 0.8rem;">
                                                INCLUSO PREMIUM
                                            </span>
                                        <?php else: ?>
                                            € <?php echo number_format($film['prezzo'], 2, ',', '.'); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p style='grid-column: 1/-1; text-align: center; padding: 50px;'>Nessun film rilevato nei radar spaziali.</p>";
                    }
                }
                ?>
            </div>
        </section>

        <aside class="sidebar-area">
            <div class="sidebar-card">
                <div class="profile-header">
                    <h3 style="font-family:'Orbitron'; font-size:1.1rem">ESPLORATORE</h3>
                    <?php if ($is_premium): ?>
                        <span class="badge-premium">MEMBRO ELITE</span>
                    <?php else: ?>
                        <span style="background: #444; color: white; padding: 4px 12px; border-radius: 10px; font-size: 0.7rem; display: inline-block; margin-top: 5px;">
                            MEMBRO STANDARD
                        </span>
                    <?php endif; ?>
                </div>

                <div class="stats-box">
                    <label style="font-size: 0.7rem; color: #888;">CREDITO RESIDUO</label>
                    <span>€ <?php echo number_format($saldo_reale, 2, ',', '.'); ?></span>
                </div>

                <div style="margin-top: 20px;">
                    <a href="ricarica.php" class="btn-action">RICARICA IL CREDITO</a>
                    <a href="ultime_visioni.php" class="btn-action">ULTIME MISSIONI</a>
                    <a href="profilo.php" class="btn-action">AGGIORNA COORDINATE</a>
                </div>

                <a href="index.php" style="display:block; text-align:center; margin-top:15px; font-size:0.75rem; color:#888; text-decoration:none">Termina Sessione</a>
            </div>
        </aside>
    </main>

    <div id="bot-launcher" onclick="toggleChat()">
        <span style="font-size: 30px;">🤖</span>
    </div>

    <div id="bot-container">
        <div class="bot-header">
            <span style="font-family:'Orbitron'; color:var(--star-gold); font-size:0.8rem;">ASTROBOT</span>
            <span onclick="toggleChat()" style="cursor:pointer;">✕</span>
        </div>
        <div id="chat-window">
            <div class="msg bot-msg">Salute, Navigatore! Sono l'unità AstroBot. Come posso assisterti oggi?</div>
        </div>
        <div class="bot-input-area">
            <input type="text" id="user-input" placeholder="Invia comando..." onkeypress="handleKey(event)">
            <button onclick="sendMessage()" style="background:var(--star-gold); border:none; border-radius:5px; padding:0 10px; cursor:pointer;">➤</button>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 AstroStream - Esplora l'infinito cinematografico</p>
    </footer>

    <script>
        // BACKGROUND STARS ANIMATION [ORIGINALE]
        const canvas = document.getElementById('star-canvas');
        const ctx = canvas.getContext('2d');
        let stars = [];
        function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        window.addEventListener('resize', resize); resize();
        for(let i = 0; i < 150; i++) {
            stars.push({ x: Math.random() * canvas.width, y: Math.random() * canvas.height, size: Math.random() * 1.5, opacity: Math.random(), speed: Math.random() * 0.01 + 0.005 });
        }
        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            stars.forEach(s => { ctx.fillStyle = `rgba(255, 255, 255, ${s.opacity})`; ctx.beginPath(); ctx.arc(s.x, s.y, s.size, 0, Math.PI * 2); ctx.fill(); s.opacity += s.speed; if (s.opacity > 1 || s.opacity < 0) s.speed = -s.speed; });
            requestAnimationFrame(draw);
        }
        draw();

        // --- LOGICA CHATBOT ---
        let ultimoArgomento = "";
        let inAttesaConferma = false;
        let isLoggato = true; // Sappiamo che è loggato perché è in dashboard

        function toggleChat() {
            const container = document.getElementById('bot-container');
            container.style.display = (container.style.display === 'flex') ? 'none' : 'flex';
        }

        function handleKey(e) { if(e.key === 'Enter') sendMessage(); }

        function sendMessage() {
            const inputField = document.getElementById('user-input');
            const text = inputField.value.trim();
            if(!text) return;

            addMessage(text, 'user-msg');
            inputField.value = "";

            setTimeout(() => {
                const response = getBotResponse(text);
                addMessage(response, 'bot-msg');
            }, 600);
        }

        function addMessage(text, className) {
            const win = document.getElementById('chat-window');
            const div = document.createElement('div');
            div.className = 'msg ' + className;
            div.innerText = text;
            win.appendChild(div);
            win.scrollTop = win.scrollHeight;
        }

        function getBotResponse(rawInput) {
    const input = rawInput.toLowerCase().replace(/[?.,\/#!$%\^&\*;:{}=\-_`~()]/g, "").trim();

    // --- 1. RICONOSCIMENTO PAROLE CHIAVE (Priorità Massima) ---
    let nuovaParolaRilevata = /(chi sei|tuo nome|astrobot|premium|vantaggi|abbonamento|dove|posizione|tasti|registra|iscriv|account|login|accedi|modifica|cambia|password|visti|cronologia|missioni|voto|stelle|pagato|speso|costo|ricarica|credito|deposito|guarda|film|streaming|video|play|avvia|ciao|grazie)/.test(input);

    // --- 2. GESTIONE CONFERMA / RISPOSTA A DOMANDA ---
    if (inAttesaConferma) {
        inAttesaConferma = false; 
        
        if (nuovaParolaRilevata) {
            ultimoArgomento = ""; 
        } else {
            if (input.match(/^(si|yes|certo|confermo|ok|va bene|dimmi|vai)$/)) {
                let arg = ultimoArgomento;
                ultimoArgomento = ""; 
                if (arg === "premium") return "Perfetto. Per il Premium, vai in 'Aggiorna Coordinate' e seleziona 'Gestisci Abbonamento'.";
                if (arg === "voto") return "Ricevuto. Per votare i film, vai nella sezione 'Ultime Missioni' del tuo profilo.";
            } 
            
            ultimoArgomento = ""; 
            if (input.match(/^(no|non proprio|negativo|nulla)$/)) {
                return "Ricevuto, rotta resettata. Di cosa vuoi parlare allora?";
            }
        }
    }

    // --- 3. RISPOSTE STANDARD ---

    // Ricarica Credito
    if (/(ricarica|credito|soldi|deposito|carica|metti)/.test(input)) {
        ultimoArgomento = ""; 
        return "Per ricaricare il tuo credito galattico, usa il tasto 'RICARICA IL CREDITO' che trovi nella plancia laterale della Home.";
    }

    // Guardare Film
    if (/(guarda|streaming|avvia|play|vedere|visione|film|video)/.test(input)) {
        ultimoArgomento = ""; 
        return "Per avviare una missione visiva, clicca sulla locandina di un film e, dalla schermata di Preview, seleziona 'AVVIA STREAMING'. Assicurati di avere abbastanza credito o un abbonamento Premium attivo!";
    }

    // Identità
    if (/(chi sei|come ti chiami|tuo nome|cos'è astrobot|cosa sei)/.test(input)) {
        ultimoArgomento = ""; 
        return "Sono l'unità AstroBot, l'intelligenza artificiale di bordo. Il mio compito è guidarti tra le nebulose del catalogo!";
    }

    // Premium
    if (/(premium|vantaggi|abbonamento|perchè pagare)/.test(input)) {
        ultimoArgomento = "premium";
        inAttesaConferma = true; 
        return "L'accesso Premium sblocca film esclusivi e ti dona il badge 'Comandante'. Vuoi sapere come ottenerlo?";
    }

    // Posizioni Tasti
    if (/(dove|posizione|trovo|trova|tasti|pulsanti|sezione|come arrivo|menu)/.test(input)) {
        ultimoArgomento = ""; 
        return "Mappa della plancia: \n\n • LOGIN/REGISTRAZIONE: In alto a destra.\n • ULTIME MISSIONI: Nel tuo Profilo.\n • IMPOSTAZIONI: Icona ingranaggio.";
    }

    // Account e Registrazione
    if (/(registra|iscriv|account|utente|nuovo|iscrizione|cancella|elimina|rimuovi|disattiva)/.test(input)) {
        ultimoArgomento = ""; 
        if (/(cancella|elimina|rimuovi|disattiva)/.test(input)) {
            return "L'espulsione definitiva può essere richiesta tramite AstroDelete nelle Impostazioni.";
        }
        return "Per arruolarti, usa il tasto 'Registrazione' in alto a destra.";
    }

    // Login
    if (/(login|accedi|identifica|entrare|accesso|loggare)/.test(input)) {
        ultimoArgomento = ""; 
        return isLoggato ? "Sei già identificato. Ben tornato a bordo!" : "Identificati tramite il Login in alto nella Home Page.";
    }

    // Modifica Profilo
    if (/(modifica|cambia|aggiorna|impostazioni|dati|profilo|password|nascita|carta|credito)/.test(input)) {
        ultimoArgomento = ""; 
        return "Nelle Impostazioni puoi cambiare Password, Nascita e Carte. Nome e Cognome sono bloccati.";
    }

    // Cronologia
    if (/(visti|cronologia|missioni|guardati|elenco|lista|storia)/.test(input)) {
        ultimoArgomento = ""; 
        return isLoggato ? "Il tuo registro missioni è in 'Ultime Missioni' nel tuo profilo." : "Identificati per scansionare i tuoi ricordi visivi.";
    }

    // Voto
    if (/(voto|votazione|gradimento|punteggio|stelle|valutazione|valuta|piaciuto|valuto)/.test(input)) {
        ultimoArgomento = "voto";
        inAttesaConferma = true; 
        return "Puoi votare (1-5 stelle) nella sezione Preview del film già visto. Vuoi sapere come arrivarci?";
    }

    // Spese e Costi
    if (/(pagato|speso|costo|prezzo|soldi|fattura|denaro|euro|spesa)/.test(input)) {
        ultimoArgomento = ""; 
        if (!isLoggato) return "Identificati per accedere ai registri dei crediti spesi.";
        return "Puoi visualizzare quanto hai speso nel riepilogo del tuo profilo.";
    }

    // --- 4. LOGICA DI MEMORIA DIRETTA ---
    if (/(come|dove|lo ottengo|si fa|prenderlo|attivarlo|acquistarlo)/.test(input) && ultimoArgomento !== "") {
        let arg = ultimoArgomento;
        ultimoArgomento = ""; 
        if (arg === "premium") return "Per il Premium, vai nelle 'Impostazioni' e seleziona 'Acquista Abbonamento'.";
        if (arg === "voto") return "Per farlo, devi andare nella sezione 'Ultime Missioni' del tuo profilo.";
    }

    // --- 5. SALUTI ---
    if (input.match(/(ciao|hey|buongiorno|salve)/)) {
        ultimoArgomento = "";
        return "Salute a te, Navigatore!";
    }
    if (input.match(/(grazie|ottimo|perfetto|gentile)/)) {
        ultimoArgomento = "";
        return "Dovere, Navigatore!";
    }

    // --- 6. GESTIONE FALLIMENTO AMBIGUO ---
    if (ultimoArgomento !== "" && !nuovaParolaRilevata) {
        inAttesaConferma = true;
        return "Potrei essermi un attimo perso... stavamo ancora parlando di '" + ultimoArgomento + "', giusto?";
    }

    return "Segnale disturbato... Non ho capito. Prova a chiedermi del Premium, dei voti o di come ricaricare il credito.";
}
    </script>
</body>
</html>