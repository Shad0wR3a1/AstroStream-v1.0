<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "streaming_db");

$email = $_SESSION['email'];
$cod_f = $_POST['cod_f'];
$data_attuale = date("Y-m-d H:i:s");

// 1. RECUPERO DATI
$stmt_u = $conn->prepare("SELECT saldo, tipo_tessera, scadenza_tessera FROM utente WHERE email = ?");
$stmt_u->bind_param("s", $email);
$stmt_u->execute();
$user = $stmt_u->get_result()->fetch_assoc();

$stmt_f = $conn->prepare("SELECT prezzo FROM film WHERE cod_f = ?");
$stmt_f->bind_param("i", $cod_f);
$stmt_f->execute();
$film = $stmt_f->get_result()->fetch_assoc();

// 2. CONTROLLO SE È GIÀ STATO VISTO (per lo sconto)
$stmt_c = $conn->prepare("SELECT COUNT(*) as conteggio FROM visionare WHERE cod_u = ? AND cod_f = ?");
$stmt_c->bind_param("si", $email, $cod_f);
$stmt_c->execute();
$gia_visto = ($stmt_c->get_result()->fetch_assoc()['conteggio'] > 0);

// 3. CALCOLO PREZZO
$is_premium = ($user['tipo_tessera'] === 'premium' && strtotime($user['scadenza_tessera'] ?? '') >= time());
$prezzo_finale = $is_premium ? 0.00 : ($gia_visto ? ($film['prezzo'] / 2) : $film['prezzo']);

// 4. CONTROLLO SALDO
if ($user['saldo'] < $prezzo_finale) {
    header("Location: preview.php?id=$cod_f&errore=credito_insufficiente");
    exit();
}

// 5. TRANSAZIONE ATOMICA
$conn->begin_transaction();

try {
    // A. Detrazione saldo
    if ($prezzo_finale > 0) {
        $stmt_pay = $conn->prepare("UPDATE utente SET saldo = saldo - ? WHERE email = ?");
        $stmt_pay->bind_param("ds", $prezzo_finale, $email);
        $stmt_pay->execute();
    }

    // B. Inserimento in NOLEGGIO
    $stmt_nol = $conn->prepare("INSERT INTO noleggio (email, cod_f, data_noleggio, costo_pagato) VALUES (?, ?, ?, ?)");
    $stmt_nol->bind_param("sisd", $email, $cod_f, $data_attuale, $prezzo_finale);
    $stmt_nol->execute();

    // C. Inserimento in VISIONARE (cronologia)
    $stmt_vis = $conn->prepare("INSERT INTO visionare (cod_u, cod_f, data_v, costo_pagato) VALUES (?, ?, ?, ?)");
    $stmt_vis->bind_param("sisd", $email, $cod_f, $data_attuale, $prezzo_finale);
    $stmt_vis->execute();

    $conn->commit();
    header("Location: film.php?id=$cod_f");
} catch (Exception $e) {
    $conn->rollback();
    die("Errore durante l'acquisto: " . $e->getMessage());
}
exit();
?>