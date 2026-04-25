<?php
session_start();
$conn = new mysqli("localhost", "root", "", "streaming_db");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$id_query = $_GET['id'];
$email_utente = $_SESSION['email'];

// 1. RECUPERO DATI FILM
$stmt = $conn->prepare("SELECT * FROM film WHERE cod_f = ?");
$stmt->bind_param("i", $id_query);
$stmt->execute();
$film = $stmt->get_result()->fetch_assoc();

if (!$film) {
    die("Errore: Il film con codice " . htmlspecialchars($id_query) . " non esiste.");
}

// 2. RECUPERO DATI UTENTE (Saldo e Premium)
$stmt_user = $conn->prepare("SELECT saldo, tipo_tessera, scadenza_tessera FROM utente WHERE email = ?");
$stmt_user->bind_param("s", $email_utente);
$stmt_user->execute();
$dati_user = $stmt_user->get_result()->fetch_assoc();

$saldo_reale = $dati_user['saldo'] ?? 0;
$is_premium = false;
if ($dati_user && $dati_user['tipo_tessera'] === 'premium' && strtotime($dati_user['scadenza_tessera']) >= time()) {
    $is_premium = true;
}

// 3. CONTROLLO SE IL FILM È GIÀ STATO VISTO (Per lo sconto 50%)
$stmt_visto = $conn->prepare("SELECT COUNT(*) as totale FROM visionare WHERE cod_u = ? AND cod_f = ?");
$stmt_visto->bind_param("si", $email_utente, $id_query);
$stmt_visto->execute();
$gia_visto = ($stmt_visto->get_result()->fetch_assoc()['totale'] > 0);

// 4. CALCOLO PREZZO FINALE (Logica Sconto)
$prezzo_base = (float)$film['prezzo'];
$prezzo_finale = $gia_visto ? ($prezzo_base / 2) : $prezzo_base;

// Se premium, il prezzo è 0
if ($is_premium) {
    $prezzo_finale = 0;
}

// 5. CONTROLLO SE PUÒ COMPRARE
$puo_acquistare = ($is_premium || $saldo_reale >= $prezzo_finale);

$titolo = $film['titolo'];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroStream | <?php echo htmlspecialchars($film['titolo']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --space-black: #0b0d17;
            --nebula-purple: #240046;
            --star-gold: #ff9e00;
            --starlight: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.07);
            --neon-blue: #00d4ff;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: radial-gradient(circle at center, var(--nebula-purple) 0%, var(--space-black) 100%);
            color: var(--starlight);
            margin: 0;
            min-height: 100vh;
        }

        header {
            padding: 15px 8%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 158, 0, 0.2);
        }

        .btn-back {
            color: var(--star-gold);
            text-decoration: none;
            font-family: 'Orbitron';
            font-size: 0.75rem;
            border: 1px solid var(--star-gold);
            padding: 8px 18px;
            border-radius: 20px;
            transition: 0.3s;
            text-transform: uppercase;
        }

        .btn-back:hover {
            background: var(--star-gold);
            color: black;
            box-shadow: 0 0 15px var(--star-gold);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 50px;
        }

        .preview-poster img {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 158, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .movie-details h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 3rem;
            color: var(--star-gold);
            margin: 0 0 15px 0;
            text-shadow: 0 0 15px rgba(255, 158, 0, 0.4);
        }

        .meta-data { margin-bottom: 25px; }

        .info-pill {
            display: inline-block;
            background: var(--card-bg);
            padding: 6px 16px;
            border-radius: 20px;
            margin-right: 10px;
            font-size: 0.85rem;
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--neon-blue);
        }

        .description {
            line-height: 1.8;
            margin: 30px 0;
            color: #bdc3c7;
            font-size: 1.1rem;
        }

        .action-zone {
            background: rgba(255, 255, 255, 0.03);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 158, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .price-tag {
            font-size: 2.2rem;
            font-family: 'Orbitron';
            color: var(--starlight);
        }

        .btn-rent {
            background: linear-gradient(45deg, #ff9e00, #ff5400);
            color: black;
            border: none;
            padding: 18px 45px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 40px;
            cursor: pointer;
            font-family: 'Orbitron';
            transition: 0.4s;
            text-transform: uppercase;
        }

        .btn-rent:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 84, 0, 0.4);
        }

        .rating-container {
            text-align: center;
            padding: 10px;
            border-left: 1px solid rgba(255,255,255,0.1);
            padding-left: 30px;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
        }

        .star-rating input { display: none; }

        .star-rating label {
            font-size: 35px;
            color: #333;
            cursor: pointer;
            transition: 0.2s;
        }

        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: var(--star-gold);
            text-shadow: 0 0 10px var(--star-gold);
        }

        @media (max-width: 900px) {
            .container { grid-template-columns: 1fr; text-align: center; }
            .preview-poster img { max-width: 300px; }
            .action-zone { flex-direction: column; gap: 20px; }
            .rating-container { border-left: none; padding-left: 10px; border-top: 1px solid rgba(255,255,255,0.1); width: 100%; }
        }
    </style>
</head>
<body>

    <header>
        <div class="logo"><h2 style="font-family:'Orbitron'; margin:0;">ASTRO<span style="color:var(--star-gold)">STREAM</span></h2></div>
        <a href="home.php" class="btn-back">← Torna al Catalogo</a>
    </header>

    <main class="container">
        <section class="preview-poster">
            <img src="immagini/<?php echo htmlspecialchars($film['immagine']); ?>" 
                 alt="<?php echo htmlspecialchars($film['titolo']); ?>">
        </section>

        <section class="movie-details">
            <h1><?php echo strtoupper(htmlspecialchars($film['titolo'])); ?></h1>
            
            <div class="meta-data">
                <span class="info-pill">👨‍🚀 <?php echo htmlspecialchars($film['regista']); ?></span>
                <span class="info-pill">🛸 <?php echo htmlspecialchars($film['genere']); ?></span>
                <span class="info-pill">⭐ <?php echo number_format($film['v_medio'], 1); ?> / 5.0</span>
            </div>

            <div class="description">
                <h3 style="color:var(--star-gold); font-family:'Orbitron'; font-size: 0.9rem;">LOG DI MISSIONE</h3>
                <p>
                    Il film è attualmente disponibile per lo streaming. 
                    Assicurati di avere abbastanza crediti per procedere.
                </p>
            </div>

            <div class="action-zone">
                <div class="price-info">
                    <small style="color:#888; display:block; margin-bottom:5px;">COSTO DI ACCESSO</small>
                    <span class="price-tag">
                        <?php if ($is_premium): ?>
                            <span style="color: var(--neon-blue);">INCLUSO</span>
                        <?php else: ?>
                            € <?php echo number_format($prezzo_finale, 2, ',', '.'); ?>
                            <?php if ($gia_visto): ?>
                                <br><small style="font-size: 0.7rem; color: var(--star-gold);">SCONTO FEDELTÀ 50% ATTIVO</small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </span>
                </div>

                <?php if ($gia_visto): ?>
                <div class="rating-container">
                    <small style="color:var(--star-gold); font-family:'Orbitron'; display:block; margin-bottom:10px;">VALUTA MISSIONE</small>
                    <div class="star-rating">
                        <?php 
                        // Recuperiamo l'ultimo voto dato (se esiste) per pre-selezionare la stella
                        $stmt_v = $conn->prepare("SELECT voto FROM visionare WHERE cod_u = ? AND cod_f = ? ORDER BY data_v DESC LIMIT 1");
                        $stmt_v->bind_param("si", $email_utente, $id_query);
                        $stmt_v->execute();
                        $voto_utente = $stmt_v->get_result()->fetch_assoc()['voto'] ?? 0;

                        for($i=5; $i>=1; $i--): 
                            $checked = ($voto_utente == $i) ? "checked" : "";
                        ?>
                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $checked; ?> onclick="inviaVoto(<?php echo $id_query; ?>, <?php echo $i; ?>)">
                            <label for="star<?php echo $i; ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <form action="noleggia.php" method="POST">
                    <input type="hidden" name="cod_f" value="<?php echo $film['cod_f']; ?>">
                    <button type="submit" class="btn-rent">
                        <?php echo ($gia_visto || $is_premium) ? "Avvia Streaming" : "Acquista Accesso"; ?>
                    </button>
                </form>
            </div> 

            <?php if (isset($_GET['errore']) && $_GET['errore'] == 'credito_insufficiente'): ?>
                <div style="color: #ff4d4d; border: 1px solid #ff4d4d; padding: 15px; margin-top: 20px; border-radius: 10px; font-family: 'Orbitron'; font-size: 0.8rem; text-align: center; background: rgba(255, 77, 77, 0.1); width: 100%; box-sizing: border-box;">
                    ⚠️ ENERGIA INSUFFICIENTE: Il tuo saldo non permette l'avvio della missione. 
                    <br><small style="font-family: 'Roboto';">Ricarica i tuoi crediti galattici.</small>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
    function inviaVoto(idFilm, valore) {
        const formData = new FormData();
        formData.append('cod_f', idFilm);
        formData.append('voto', valore);

        fetch('vota.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log("Valutazione sincronizzata con il comando centrale.");
        })
        .catch(error => {
            console.error("Errore di trasmissione:", error);
        });
    }
    </script>
</body>
</html>