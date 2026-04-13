<?php
session_start();

// Controllo accesso
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$host = "localhost";
$user = "root";     
$pass = "";      
$dbname = "streaming_db"; 

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("Connessione fallita"); }

$email = $_SESSION['email'];
$messaggio = "";

// 1. Recupero dati utente
$res = $conn->query("SELECT cod_u, saldo FROM utente WHERE email = '$email'");
$user_data = $res->fetch_assoc();
$id_utente = $user_data['cod_u'];

// 2. Controllo carte
$check_carte = $conn->query("SELECT * FROM carte_credito WHERE cod_u = $id_utente");
$ha_carte = ($check_carte->num_rows > 0);

// 3. Gestione Ricarica
if (isset($_POST['conferma_ricarica'])) {
    
    // se non ha carte, blocca l'operazione
    if (!$ha_carte) {
        $messaggio = "<p style='color: #ff4d4d;'>⚠️ Errore: Protocollo negato. Collega una carta per ricaricare il credito.</p>";
    } else {
        $importo = floatval($_POST['importo']);
        
        if ($importo > 0) {
            $sql = "UPDATE utente SET saldo = saldo + $importo WHERE cod_u = $id_utente";
            
            if ($conn->query($sql) === TRUE) {
                $messaggio = "<p style='color: #00ff00;'>Ricarica di €".number_format($importo, 2)." completata! 🚀</p>";
                // Aggiorniamo il saldo locale per la visualizzazione immediata
                $user_data['saldo'] += $importo;
            } else {
                $messaggio = "<p style='color: #ff0000;'>Errore SQL: " . $conn->error . "</p>";
            }
        }
    }
}

$saldo_attuale = $user_data['saldo'] ?? 0;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroStream | Ricarica Credito</title>
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
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: radial-gradient(circle at center, var(--nebula-purple) 0%, var(--space-black) 100%);
            color: var(--starlight);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .ricarica-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
        }

        h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--star-gold);
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .saldo-display {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: var(--neon-blue);
        }

        .saldo-display span {
            font-weight: bold;
            font-size: 1.8rem;
            display: block;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 0.8rem;
            color: #aaa;
            text-transform: uppercase;
        }

        select {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            outline: none;
        }

        .btn-ricarica {
            width: 100%;
            padding: 15px;
            background: var(--star-gold);
            border: none;
            border-radius: 10px;
            color: black;
            font-weight: bold;
            font-family: 'Orbitron', sans-serif;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
        }

        .btn-ricarica:hover:not(:disabled) {
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(255, 158, 0, 0.4);
        }

        .btn-ricarica:disabled {
            background: #444;
            color: #777;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            color: #888;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .back-link:hover { color: var(--starlight); }
    </style>
</head>
<body>

    <div class="ricarica-card">
        <h2>STAZIONE DI RICARICA</h2>
        
        <div class="saldo-display">
            SALDO ATTUALE
            <span>€ <?php echo number_format($saldo_attuale, 2, ',', '.'); ?></span>
        </div>

        <?php echo $messaggio; ?>

        <form method="POST">
            <div class="form-group">
                <label>Seleziona Importo</label>
                <select name="importo" required <?php echo !$ha_carte ? 'disabled' : ''; ?>>
                    <option value="5">€ 5,00 (Scout)</option>
                    <option value="10">€ 10,00 (Explorer)</option>
                    <option value="20">€ 20,00 (Commander)</option>
                    <option value="50">€ 50,00 (Admiral)</option>
                </select>
            </div>

            <button type="submit" name="conferma_ricarica" class="btn-ricarica" <?php echo !$ha_carte ? 'disabled' : ''; ?>>
                <?php echo $ha_carte ? 'Inizializza Ricarica' : 'Metodo mancante'; ?>
            </button>
        </form>

        <a href="home.php" class="back-link">← Torna alla dashboard</a>
    </div>

</body>
</html>