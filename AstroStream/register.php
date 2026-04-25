<?php
$conn = new mysqli("localhost", "root", "", "streaming_db");

if ($conn->connect_error) {
    die("Errore connessione: " . $conn->connect_error);
}

$errore = "";
$successo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $data_n = $_POST['data_n'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $check = $conn->prepare("SELECT cod_u FROM utente WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $errore = "Email già registrata ❌";
    } else {

        $stmt = $conn->prepare("INSERT INTO utente (nome, cognome, data_n, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nome, $cognome, $data_n, $email, $password);

        if ($stmt->execute()) {
            $successo = "Registrazione completata 🚀";
        } else {
            $errore = "Errore durante la registrazione ❌";
        }

        $stmt->close();
    }

    $check->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroStream | Registrazione</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --space-black: #0b0d17;
            --nebula-purple: #240046;
            --star-gold: #ff9e00;
            --starlight: #ffffff;
            --glass-bg: rgba(255, 255, 255, 0.07);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

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

        #star-canvas {
            position: fixed;
            top: 0;
            left: 0;
            z-index: -1;
        }

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
            color: var(--star-gold);
            text-shadow: 0 0 15px var(--star-gold);
        }

        .login-header p {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            color: var(--star-gold);
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
            border-color: var(--star-gold);
            box-shadow: 0 0 10px rgba(255, 158, 0, 0.3);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--star-gold);
            color: black;
            border: none;
            border-radius: 12px;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: white;
            box-shadow: 0 0 25px var(--star-gold);
        }

        .error { color: #ff4d4d; margin-bottom: 10px; }
        .success { color: #4dff9e; margin-bottom: 10px; }

        .register-text {
            margin-top: 20px;
        }

        .register-text a {
            color: var(--star-gold);
            text-decoration: none;
        }
    </style>
</head>

<body>

<canvas id="star-canvas"></canvas>

<div class="login-card">
    <div class="login-header">
        <h1>ASTRO<span>STREAM</span></h1>
        <p>Creazione Nuovo Navigatore</p>
    </div>

    <?php if($errore): ?>
        <div class="error"><?php echo $errore; ?></div>
    <?php endif; ?>

    <?php if($successo): ?>
        <div class="success"><?php echo $successo; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>NOME</label>
            <input type="text" name="nome" required>
        </div>

        <div class="form-group">
            <label>COGNOME</label>
            <input type="text" name="cognome" required>
        </div>

        <div class="form-group">
            <label>DATA DI NASCITA</label>
            <input type="date" name="data_n" required>
        </div>

        <div class="form-group">
            <label>IDENTIFICATIVO EMAIL</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>CODICE D'ACCESSO</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn-login">CREA ACCOUNT</button>
    </form>

    <div class="register-text">
        <a href="index.php">Hai già un account? Accedi</a>
    </div>
</div>

<script>
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