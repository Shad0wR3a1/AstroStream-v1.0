<?php
$conn = new mysqli("localhost", "root", "", "streaming_db");

if ($conn->connect_error) {
    die("Errore connessione: " . $conn->connect_error);
}

$errore = "";
$successo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // 1. Verifichiamo se l'utente esiste
    $check = $conn->prepare("SELECT email FROM utente WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // INIZIO TRANSAZIONE
        $conn->begin_transaction();

        try {
            // 2. Eliminiamo i record dipendenti (la cronologia)
            $del_visioni = $conn->prepare("DELETE FROM visionare WHERE cod_u = ?");
            $del_visioni->bind_param("s", $email);
            $del_visioni->execute();

            // 3. eliminiamo l'utente
            $del_utente = $conn->prepare("DELETE FROM utente WHERE email = ?");
            $del_utente->bind_param("s", $email);
            $del_utente->execute();

            // Stringa di conferma delle modifiche
            $conn->commit();
            $successo = "Navigatore rimosso e dati disintegrati 🛸";
            
        } catch (mysqli_sql_exception $e) {
            // In caso di errore, annulliamo tutto
            $conn->rollback();
            $errore = "Errore durante la disintegrazione: " . $e->getMessage();
        }
    } else {
        $errore = "Email non trovata nel database 🌌";
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroStream | Rimozione</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --space-black: #0b0d17;
            --nebula-purple: #240046;
            --danger-red: #ff4d4d;
            --starlight: #ffffff;
            --glass-bg: rgba(255, 255, 255, 0.07);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Roboto', sans-serif;
            background: radial-gradient(circle at center, var(--nebula-purple) 0%, var(--space-black) 100%);
            color: var(--starlight);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        #star-canvas { position: fixed; top: 0; left: 0; z-index: -1; }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            text-align: center;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            letter-spacing: 5px;
            margin-bottom: 10px;
        }

        .login-header span {
            color: var(--danger-red);
            text-shadow: 0 0 15px var(--danger-red);
        }

        .login-header p {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .form-group { margin-bottom: 25px; text-align: left; }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            color: var(--danger-red);
            margin-bottom: 6px;
            margin-left: 5px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 14px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            outline: none;
        }

        input:focus {
            border-color: var(--danger-red);
            box-shadow: 0 0 10px rgba(255, 77, 77, 0.3);
        }

        .btn-delete {
            width: 100%;
            padding: 15px;
            background: var(--danger-red);
            color: white;
            border: none;
            border-radius: 12px;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-delete:hover {
            background: white;
            color: var(--danger-red);
            box-shadow: 0 0 25px var(--danger-red);
        }

        .error { color: #ff4d4d; margin-bottom: 20px; font-weight: bold; }
        .success { color: #4dff9e; margin-bottom: 20px; font-weight: bold; }

        .back-link { margin-top: 25px; }
        .back-link a { color: #aaa; text-decoration: none; font-size: 0.9rem; }
        .back-link a:hover { color: white; }
    </style>
</head>
<body>

<canvas id="star-canvas"></canvas>

<div class="login-card">
    <div class="login-header">
        <h1>ASTRO<span>DELETE</span></h1>
        <p>Espulsione Navigatore dal Sistema</p>
    </div>

    <?php if($errore): ?>
        <div class="error"><?php echo $errore; ?></div>
    <?php endif; ?>

    <?php if($successo): ?>
        <div class="success"><?php echo $successo; ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare definitivamente questo utente?');">
        <div class="form-group">
            <label>IDENTIFICATIVO EMAIL DA RIMUOVERE</label>
            <input type="email" name="email" placeholder="email@esempio.it" required>
        </div>

        <button type="submit" class="btn-delete">ELIMINA ACCOUNT</button>
    </form>

    <div class="back-link">
        <a href="index.php">← Torna alla Dashboard</a>
    </div>
</div>

<script>
    // Script stelle identico al precedente per coerenza visiva
    const canvas = document.getElementById('star-canvas');
    const ctx = canvas.getContext('2d');
    let stars = [];

    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resize);
    resize();

    for(let i = 0; i < 150; i++) {
        stars.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: Math.random() * 1.5,
            opacity: Math.random(),
            speed: Math.random() * 0.02
        });
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        stars.forEach(s => {
            ctx.fillStyle = `rgba(255,255,255,${s.opacity})`;
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.size, 0, Math.PI * 2);
            ctx.fill();
            s.opacity += s.speed;
            if (s.opacity > 1 || s.opacity < 0) s.speed = -s.speed;
        });
        requestAnimationFrame(draw);
    }
    draw();
</script>

</body>
</html>