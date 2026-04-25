<?php
session_start();

if(!isset($_SESSION['email'])){
    die("Errore: Sessione non trovata. <a href='index.php'>Torna al Login</a>");
}

$email_sessione = $_SESSION['email'];
$conn = new mysqli("localhost", "root", "", "streaming_db");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// 1. RECUPERO DATI UTENTE
$stmt_user = $conn->prepare("SELECT nome, saldo FROM utente WHERE email = ?");
$stmt_user->bind_param("s", $email_sessione);
$stmt_user->execute();
$dati_utente = $stmt_user->get_result()->fetch_assoc();

if (!$dati_utente) {
    die("Errore: Utente non trovato.");
}

$nome_visualizzato = $dati_utente['nome'];

// 2. CALCOLO TOTALE SPESO
$stmt_totale = $conn->prepare("SELECT SUM(costo_pagato) as totale_investito FROM visionare WHERE cod_u = ?");
$stmt_totale->bind_param("s", $email_sessione);
$stmt_totale->execute();
$res_totale = $stmt_totale->get_result()->fetch_assoc();
$totale_speso = $res_totale['totale_investito'] ?? 0;

// 3. QUERY DELLE VISIONI
$sql_visioni = "SELECT f.titolo, f.immagine, f.genere, v.data_v, v.costo_pagato 
                FROM visionare v
                INNER JOIN film f ON v.cod_f = f.cod_f
                WHERE v.cod_u = ? 
                ORDER BY v.data_v DESC";

$stmt_vis = $conn->prepare($sql_visioni);
$stmt_vis->bind_param("s", $email_sessione); 
$stmt_vis->execute();
$res_visioni = $stmt_vis->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>AstroStream | Cronologia</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --space-black: #0b0d17;
            --star-gold: #ff9e00;
            --neon-blue: #00d4ff;
            --starlight: #ffffff;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: radial-gradient(circle, #240046 0%, #0b0d17 100%);
            color: var(--starlight);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 900px;
            margin: 0 auto 30px;
        }

        .btn-back {
            text-decoration: none; color: white; font-family: 'Orbitron'; font-size: 0.7rem;
            border: 1px solid rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 20px;
            transition: 0.3s;
        }
        .btn-back:hover { border-color: var(--star-gold); color: var(--star-gold); }

        .stats-box {
            background: rgba(255, 158, 0, 0.1);
            border: 1px solid var(--star-gold);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin: 20px auto;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }

        .main-title { font-family: 'Orbitron'; text-align: center; color: var(--star-gold); margin-bottom: 40px; }

        .visione-card {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            margin: 15px auto;
            padding: 20px;
            border-radius: 15px;
            border-left: 5px solid var(--star-gold);
            max-width: 850px;
            backdrop-filter: blur(5px);
            transition: 0.3s;
        }
        .visione-card:hover { background: rgba(255, 255, 255, 0.08); transform: scale(1.01); }

        .poster-img { width: 70px; height: 100px; border-radius: 8px; object-fit: cover; margin-right: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        .info-main { flex-grow: 1; }
        .info-main h4 { margin: 0 0 5px 0; font-family: 'Orbitron'; font-size: 1.1rem; color: var(--starlight); }
        .info-main small { color: #aaa; display: block; }

        .price-info { text-align: right; min-width: 120px; }
        .price-val { display: block; font-family: 'Orbitron'; color: var(--star-gold); font-size: 1.2rem; font-weight: bold; }
        .genere-tag { font-size: 0.7rem; color: var(--neon-blue); text-transform: uppercase; letter-spacing: 1px; }

    </style>
</head>
<body>

    <div class="header-top">
        <a href="home.php" class="btn-back">← TORNA AL CATALOGO</a>
        <div style="font-family: 'Orbitron'; font-size: 0.8rem;">UTENTE: <span style="color:var(--star-gold)"><?php echo htmlspecialchars($nome_visualizzato); ?></span></div>
    </div>

    <div class="stats-box">
        <h3 style="font-family: 'Orbitron'; color: var(--star-gold); font-size: 0.7rem; margin: 0 0 10px 0;">INVESTIMENTO TOTALE</h3>
        <span style="font-size: 2.2rem; font-family: 'Orbitron';">€ <?php echo number_format($totale_speso, 2, ',', '.'); ?></span>
    </div>

    <h2 class="main-title">LOG DI BORDO: REGISTRO MISSIONI</h2>

    <div class="list-container">
        <?php if ($res_visioni->num_rows > 0): ?>
            <?php while($v = $res_visioni->fetch_assoc()): ?>
                <div class="visione-card">
                    <img src="immagini/<?php echo htmlspecialchars($v['immagine']); ?>" class="poster-img">
                    
                    <div class="info-main">
                        <h4><?php echo strtoupper(htmlspecialchars($v['titolo'])); ?></h4>
                        <small>📡 Trasmesso il: <?php echo date("d/m/Y - H:i", strtotime($v['data_v'])); ?></small>
                    </div>

                    <div class="price-info">
                        <span class="genere-tag"><?php echo htmlspecialchars($v['genere']); ?></span>
                        <span class="price-val">€ <?php echo number_format($v['costo_pagato'], 2, ',', '.'); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; color: #888; font-family: 'Orbitron'; margin-top: 50px;">Nessun dato registrato nei sistemi di navigazione.</p>
        <?php endif; ?>
    </div>

</body>
</html>