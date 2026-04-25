<?php
session_start();
session_unset();
session_destroy();
session_start();

$conn = new mysqli("localhost", "root", "", "streaming_db");

if ($conn->connect_error) {
    die("Errore connessione: " . $conn->connect_error);
}

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM utente WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Controllo password
        if ($password == $user['password']) {
            
            // --- LOGICA ADMIN ---
            $_SESSION['email'] = $email;
            
            // Se is_admin è 1, lo impostiamo come true, altrimenti false
            if ($user['is_admin'] == 1) {
                $_SESSION['role'] = 'admin';
            } else {
                $_SESSION['role'] = 'user';
            }
            // --------------------

            header("Location: home.php");
            exit();
        } else {
            $errore = "Password errata ❌";
        }
    } else {
        $errore = "Utente non trovato ❌";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroStream | Accesso</title>
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
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            color: var(--star-gold);
            margin-bottom: 8px;
            margin-left: 5px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 15px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: 0.3s;
            outline: none;
        }

        input:focus {
            border-color: var(--star-gold);
            box-shadow: 0 0 10px rgba(255, 158, 0, 0.3);
            background: rgba(0, 0, 0, 0.5);
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
            font-size: 1rem;
            cursor: pointer;
            transition: 0.4s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: white;
            box-shadow: 0 0 25px var(--star-gold);
            transform: scale(1.02);
        }

        .error {
            color: #ff4d4d;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .extra-links {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
        }

        .extra-links a {
            color: #888;
            text-decoration: none;
        }

        .extra-links a:hover {
            color: var(--star-gold);
        }

        .register-text {
            margin-top: 30px;
            font-size: 0.9rem;
            color: #ccc;
        }

        .register-text a {
            color: var(--star-gold);
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<canvas id="star-canvas"></canvas>

<div class="login-card">
    <div class="login-header">
        <h1>ASTRO<span>STREAM</span></h1>
        <p>Inizializzazione Sessione Navigatore</p>
    </div>

    <?php if($errore): ?>
        <div class="error"><?php echo $errore; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>IDENTIFICATIVO EMAIL</label>
            <input type="email" name="email" placeholder="nome@galassia.it" required>
        </div>

        <div class="form-group">
            <label>CODICE D'ACCESSO</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-login">AVVIA MOTORI</button>
    </form>

    <div class="extra-links">
        <a href="cambia.php">Coordinate smarrite?</a>
        <a href="supporto.php">Supporto Tecnico</a>
    </div>

    <div class="register-text">
        Nuovo esploratore? <a href="register.php">Unisciti alla flotta</a><br>
        Abbandoni la Flotta? <a href="quit.php">Cedi la tuta</a>
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
        ctx.fillStyle = `rgba(255, 255, 255, ${s.opacity})`;
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