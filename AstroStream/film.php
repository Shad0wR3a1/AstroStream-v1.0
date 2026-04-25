<?php

$cod_f = $_GET['id'];
$conn = new mysqli("localhost", "root", "", "streaming_db");

// 1. RECUPERO TITOLO E PATH VIDEO DAL DB
$stmt = $conn->prepare("SELECT titolo, video FROM film WHERE cod_f = ?");
$stmt->bind_param("i", $cod_f);
$stmt->execute();
$res = $stmt->get_result();
$film = $res->fetch_assoc();

// Se il film non esiste o non ha un video associato
if (!$film || empty($film['video'])) {
    die("Errore: File video non trovato nei sistemi di bordo.");
}

$path_video = "video/" . $film['video']; 
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>AstroStream | <?php echo htmlspecialchars($film['titolo']); ?></title>
    <style>
        body { margin: 0; background: #000; display: flex; flex-direction: column; height: 100vh; font-family: 'Roboto', sans-serif; overflow: hidden; }
        
        .back-nav { position: absolute; top: 25px; left: 25px; z-index: 100; }
        .back-link {
            color: white; text-decoration: none; font-size: 1.5rem;
            background: rgba(255, 255, 255, 0.1); width: 45px; height: 45px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; backdrop-filter: blur(5px); transition: 0.3s;
            border: 1px solid rgba(255,158,0,0.3);
        }
        .back-link:hover { background: #ff9e00; color: black; box-shadow: 0 0 15px #ff9e00; }

        .video-wrapper {
            flex-grow: 1; display: flex; align-items: center; justify-content: center;
            background: radial-gradient(circle, #1a1a1a 0%, #000 100%);
        }

        video {
            width: 90%; max-width: 1100px; border-radius: 8px;
            box-shadow: 0 0 50px rgba(255, 158, 0, 0.1);
            outline: none;
        }
    </style>
</head>
<body>

    <div class="back-nav">
        <a href="home.php" class="back-link">←</a>
    </div>

    <div class="video-wrapper">
        <video controls autoplay>
            <source src="<?php echo htmlspecialchars($path_video); ?>" type="video/mp4">
            Il tuo browser non supporta il protocollo di trasmissione video.
        </video>
    </div>

</body>
</html>