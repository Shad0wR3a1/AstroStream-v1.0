<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "streaming_db");
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }

$email_sessione = $_SESSION['email'];
$errore = "";
$successo = "";

// 1. Recupero dati utente
$stmt = $conn->prepare("SELECT * FROM utente WHERE email = ?");
$stmt->bind_param("s", $email_sessione);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$id_utente = $user['cod_u'];

// 2. Gestione aggiornamento dati personali
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $nuova_data = $_POST['data_n'];
    $nuova_pass = $_POST['password'];
    $update = $conn->prepare("UPDATE utente SET data_n = ?, password = ? WHERE email = ?");
    $update->bind_param("sss", $nuova_data, $nuova_pass, $email_sessione);
    if ($update->execute()) {
        $successo = "Sistemi ricalibrati! 🛰️";
        $user['data_n'] = $nuova_data;
        $user['password'] = $nuova_pass;
    }
}

// 3. Gestione AGGIUNTA Carta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_card'])) {
    $n_carta = $_POST['n_carta'];
    $scad_carta = $_POST['scad_carta'];
    $intestatario = $user['nome'] . " " . $user['cognome'];

    $ins_card = $conn->prepare("INSERT INTO carte_credito (cod_u, numero_carta, intestatario, scadenza) VALUES (?, ?, ?, ?)");
    $ins_card->bind_param("isss", $id_utente, $n_carta, $intestatario, $scad_carta);
    $ins_card->execute();
    header("Location: profilo.php");
}

// --- 4. Gestione RIMOZIONE Carta ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_card'])) {
    $id_da_togliere = $_POST['id_carta']; 

    $del_card = $conn->prepare("DELETE FROM carte_credito WHERE id_carta = ? AND cod_u = ?");
    $del_card->bind_param("ii", $id_da_togliere, $id_utente);
    $del_card->execute();
    header("Location: profilo.php");
}

// 5. Recupero Carte di Credito
$res_cards = $conn->query("SELECT * FROM carte_credito WHERE cod_u = $id_utente");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>AstroStream | Profilo</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --space-black: #0b0d17; --nebula-purple: #240046; --star-gold: #ff9e00;
            --neon-blue: #00d4ff; --starlight: #ffffff; --glass-bg: rgba(255, 255, 255, 0.07);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: radial-gradient(circle, var(--nebula-purple) 0%, var(--space-black) 100%);
            color: var(--starlight);
            padding: 40px 20px;
        }

        .container { max-width: 800px; margin: auto; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: var(--glass-bg); backdrop-filter: blur(15px); border: 1px solid var(--glass-border); border-radius: 20px; padding: 25px; display: flex; flex-direction: column; }
        .full-width { grid-column: span 2; }
        h2 { font-family: 'Orbitron'; color: var(--star-gold); font-size: 1.2rem; margin-bottom: 20px; text-transform: uppercase; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; background: <?php echo ($user['tipo_tessera'] == 'premium') ? 'var(--star-gold)' : '#555'; ?>; color: <?php echo ($user['tipo_tessera'] == 'premium') ? 'black' : 'white'; ?>; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 0.7rem; color: var(--neon-blue); margin-bottom: 5px; text-transform: uppercase; }
        input { width: 100%; padding: 10px; background: rgba(0,0,0,0.4); border: 1px solid var(--glass-border); border-radius: 8px; color: white; box-sizing: border-box; }
        input[readonly] { opacity: 0.5; cursor: not-allowed; }
        .btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-family: 'Orbitron'; cursor: pointer; transition: 0.3s; margin-top: 10px; display: block; text-align: center; text-decoration: none; }
        .btn-gold { background: var(--star-gold); color: black; }
        .btn-gold:hover { box-shadow: 0 0 15px var(--star-gold); }
        .btn-outline { background: transparent; border: 1px solid var(--star-gold); color: var(--star-gold); margin-top: auto; }
        .card-item { background: rgba(255,255,255,0.05); padding: 10px; border-radius: 10px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; }
        .delete-btn { background: none; border: none; color: #ff4d4d; cursor: pointer; font-size: 1.2rem; transition: 0.2s; }
        .delete-btn:hover { transform: scale(1.2); }
        .nav-back { text-decoration: none; color: var(--neon-blue); font-family: 'Orbitron'; font-size: 0.8rem; margin-bottom: 20px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <a href="home.php" class="nav-back">← TORNA ALLA DASHBOARD</a>
    
    <div class="card full-width">
        <h2>Dati Identificativi Navigatore</h2>
        <?php if($successo) echo "<p style='color:var(--star-gold)'>$successo</p>"; ?>
        <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Nome (Immutabile)</label>
                <input type="text" value="<?php echo $user['nome']; ?>" readonly>
            </div>
            <div class="form-group">
                <label>Cognome (Immutabile)</label>
                <input type="text" value="<?php echo $user['cognome']; ?>" readonly>
            </div>
            <div class="form-group">
                <label>Data di Nascita</label>
                <input type="date" name="data_n" value="<?php echo $user['data_n']; ?>">
            </div>
            <div class="form-group">
                <label>Password di Accesso</label>
                <input type="text" name="password" value="<?php echo $user['password']; ?>">
            </div>
            <div class="full-width">
                <button type="submit" name="update_profile" class="btn btn-gold">SALVA MODIFICHE</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Stato Missione (Tessera)</h2>
        <p>Tipo Tessera: <span class="status-badge"><?php echo $user['tipo_tessera']; ?></span></p>
        <p style="margin-top:10px; font-size: 0.8rem;">
            Codice Tessera: <strong>#<?php echo $user['num_tessera'] ?? 'NON ATTIVA'; ?></strong>
        </p>
        <?php if($user['tipo_tessera'] == 'premium'): ?>
            <p style="font-size: 0.8rem; color: var(--neon-blue);">Scadenza: <?php echo date("d/m/Y", strtotime($user['scadenza_tessera'])); ?></p>
        <?php else: ?>
            <p style="font-size: 0.8rem; color: #aaa;">Accesso Standard: paga per singola visione.</p>
        <?php endif; ?>
        <a href="abbonamento.php" class="btn btn-outline">GESTISCI ABBONAMENTO</a>
        <?php if (isset($user['is_admin']) && $user['is_admin'] == 1): ?>
    <a href="admin.php" class="btn" style="background: #ff4d4d; color: white; border: 1px solid #ff4d4d; box-shadow: 0 0 15px rgba(255, 77, 77, 0.4); margin-top: 15px;">
        ⚡ ACCESSO AMMINISTRATORE
    </a>
<?php endif; ?>
    </div>

    <div class="card">
        <h2>Moduli di Pagamento</h2>
        <div id="lista-carte">
            <?php while($c = $res_cards->fetch_assoc()): ?>
                <div class="card-item">
                    <span>💳 **** <?php echo substr($c['numero_carta'], -4); ?> (<?php echo $c['scadenza']; ?>)</span>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id_carta" value="<?php echo $c['id_carta']; ?>">
                        <button type="submit" name="remove_card" class="delete-btn" onclick="return confirm('Eliminare questo modulo?')">🗑️</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
        
        <form method="POST" style="margin-top: 15px; border-top: 1px solid var(--glass-border); padding-top: 15px;">
            <label>Nuova Carta (16 cifre)</label>
            <input type="text" name="n_carta" maxlength="16" placeholder="0000 0000 0000 0000" required>
            <label style="margin-top:5px;">Scadenza (MM/AAAA)</label>
            <input type="text" name="scad_carta" placeholder="12/2028" required>
            <button type="submit" name="add_card" class="btn btn-gold" style="font-size: 0.7rem;">AGGIUNGI MODULO</button>
        </form>
    </div>

    <div class="full-width" style="text-align: center; margin-top: 20px;">
        <a href="quit.php" style="color: #ff4d4d; text-decoration: none; font-size: 0.8rem; font-family: 'Orbitron';">ELIMINA ACCOUNT E DATI DI VOLO</a>
    </div>
</div>

</body>
</html>