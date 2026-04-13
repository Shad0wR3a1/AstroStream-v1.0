-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Apr 12, 2026 alle 21:17
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `streaming_db`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `carte_credito`
--

CREATE TABLE `carte_credito` (
  `id_carta` int(11) NOT NULL,
  `cod_u` int(11) NOT NULL,
  `numero_carta` varchar(16) NOT NULL,
  `intestatario` varchar(100) NOT NULL,
  `scadenza` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dump dei dati per la tabella `carte_credito`
--

INSERT INTO `carte_credito` (`id_carta`, `cod_u`, `numero_carta`, `intestatario`, `scadenza`) VALUES
(13, 14, '123456789', 'Matteo Ongaro', '12/2028');

-- --------------------------------------------------------

--
-- Struttura della tabella `film`
--

CREATE TABLE `film` (
  `cod_f` int(11) NOT NULL,
  `titolo` varchar(100) NOT NULL,
  `regista` varchar(100) DEFAULT NULL,
  `genere` varchar(50) DEFAULT NULL,
  `immagine` varchar(255) DEFAULT 'default.jpg',
  `v_medio` decimal(3,1) DEFAULT 0.0,
  `prezzo` decimal(10,2) DEFAULT 3.99,
  `video` varchar(255) NOT NULL DEFAULT 'trailer_default.mp4'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dump dei dati per la tabella `film`
--

INSERT INTO `film` (`cod_f`, `titolo`, `regista`, `genere`, `immagine`, `v_medio`, `prezzo`, `video`) VALUES
(5, 'Jujutsu Kaisen S.3', 'Sorcery Fight', 'Shounen', 'jjk.jpg', 5.0, 5.00, 'jjk.mp4'),
(6, 'Solo Leveling S.3', 'A-1 Pictures', 'Action', 'SL.png', 4.0, 6.50, 'SL.mp4'),
(7, 'Re:Zero S.4', 'White Fox', 'Isekai', 'rezero.webp', 0.0, 6.00, 'rezero.mp4'),
(8, 'Oshi no Ko S.2', 'Doga Kobo', 'Mystery', 'oshinoko.jpg', 0.0, 7.00, 'oshinoko2.mp4'),
(9, 'Bleach', '20th Century Fox', 'Shounen', 'bleach.jpg', 0.0, 3.99, 'bleach.mp4'),
(10, 'Attack on Titan', 'MAPPA', 'Action', 'aot.jpg', 0.0, 6.00, 'aot.mp4'),
(11, 'Demon Slayer S.3', 'Ufotable', 'Action', 'demonslayer3.jpg', 0.0, 5.00, 'demonslayer3.mp4'),
(12, 'Chainsaw Man', 'MAPPA', 'Shounen', 'chainsawman.png', 0.0, 4.00, 'chainsawman.mp4'),
(13, 'DanDaDan', 'Science SARU', 'Action', 'dandadan.jpg', 0.0, 4.00, 'dandadan.mp4'),
(14, 'Mashle', 'A-1 Pictures', 'Shounen', 'mashle.jpg', 0.0, 4.00, 'mashle.mp4'),
(15, 'Frieren', 'MADHOUSE', 'Fantasy', 'frieren.jpg', 0.0, 5.00, 'frieren.mp4'),
(16, 'One Punch Man', 'MADHOUSE', 'Action', 'onepunch.jpg', 0.0, 6.00, 'onepunch.mp4'),
(17, 'Tokyo Ghoul', 'Studio Pierrot', 'Mystery', 'tokyoghoul.jpg', 0.0, 6.00, 'tokyoghoul.mp4'),
(18, 'Fire Force', 'David Production', 'Action', 'fireforce.jpg', 0.0, 6.00, 'fireforce.mp4'),
(19, 'Call of the Night', 'LIDENFILMS', 'Romance', 'callnight.jpg', 0.0, 7.00, 'callnight.mp4'),
(20, 'My Hero Academia', 'BONES', 'Shounen', 'mha.png', 0.0, 5.00, 'mha.mp4'),
(21, 'The Promised Neverland', 'CloverWorks', 'Drama', 'promised.jpg', 0.0, 5.00, 'promised.mp4'),
(22, 'Blue Lock', '8-bit', 'Sports', 'bluelock.png', 4.0, 5.00, 'bluelock.mp4');

-- --------------------------------------------------------

--
-- Struttura della tabella `noleggio`
--

CREATE TABLE `noleggio` (
  `id_noleggio` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cod_f` int(11) NOT NULL,
  `data_noleggio` datetime NOT NULL,
  `costo_pagato` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dump dei dati per la tabella `noleggio`
--

INSERT INTO `noleggio` (`id_noleggio`, `email`, `cod_f`, `data_noleggio`, `costo_pagato`) VALUES
(36, 'matteo.ongaro.2007@gmail.com', 5, '2026-04-10 11:48:30', 5.00),
(37, 'matteo.ongaro.2007@gmail.com', 6, '2026-04-10 11:48:51', 0.00),
(38, 'matteo.ongaro.2007@gmail.com', 13, '2026-04-10 11:50:04', 0.00),
(39, 'matteo.ongaro.2007@gmail.com', 17, '2026-04-10 11:50:13', 0.00),
(40, 'matteo.ongaro.2007@gmail.com', 20, '2026-04-10 11:50:22', 0.00),
(41, 'matteo.ongaro.2007@gmail.com', 12, '2026-04-10 11:50:29', 0.00),
(42, 'matteo.ongaro.2007@gmail.com', 12, '2026-04-10 11:51:27', 0.00),
(43, 'matteo.ongaro.2007@gmail.com', 19, '2026-04-10 11:51:51', 0.00),
(44, 'matteo.ongaro.2007@gmail.com', 21, '2026-04-10 11:51:58', 0.00),
(45, 'matteo.ongaro.2007@gmail.com', 14, '2026-04-10 11:52:20', 0.00),
(46, 'matteo.ongaro.2007@gmail.com', 5, '2026-04-12 20:50:03', 0.00);

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `cod_u` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `num_tessera` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `data_n` date NOT NULL,
  `saldo` decimal(10,2) DEFAULT 0.00,
  `tipo_tessera` enum('normale','premium') DEFAULT 'normale',
  `scadenza_tessera` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`cod_u`, `nome`, `cognome`, `email`, `num_tessera`, `password`, `data_n`, `saldo`, `tipo_tessera`, `scadenza_tessera`) VALUES
(14, 'Matteo', 'Ongaro', 'matteo.ongaro.2007@gmail.com', 'ASTRO-8909-177581452', 'sus', '2007-01-08', 5.00, 'premium', '2026-05-10');

-- --------------------------------------------------------

--
-- Struttura della tabella `visionare`
--

CREATE TABLE `visionare` (
  `id_visione` int(11) NOT NULL,
  `cod_u` varchar(255) NOT NULL,
  `cod_f` int(11) NOT NULL,
  `data_v` datetime NOT NULL,
  `voto` int(11) DEFAULT NULL,
  `costo_pagato` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dump dei dati per la tabella `visionare`
--

INSERT INTO `visionare` (`id_visione`, `cod_u`, `cod_f`, `data_v`, `voto`, `costo_pagato`) VALUES
(30, 'matteo.ongaro.2007@gmail.com', 5, '2026-04-10 11:48:30', 5, 5.00),
(31, 'matteo.ongaro.2007@gmail.com', 6, '2026-04-10 11:48:51', NULL, 0.00),
(32, 'matteo.ongaro.2007@gmail.com', 13, '2026-04-10 11:50:04', NULL, 0.00),
(33, 'matteo.ongaro.2007@gmail.com', 17, '2026-04-10 11:50:13', NULL, 0.00),
(34, 'matteo.ongaro.2007@gmail.com', 20, '2026-04-10 11:50:22', NULL, 0.00),
(35, 'matteo.ongaro.2007@gmail.com', 12, '2026-04-10 11:50:29', NULL, 0.00),
(36, 'matteo.ongaro.2007@gmail.com', 12, '2026-04-10 11:51:27', NULL, 0.00),
(37, 'matteo.ongaro.2007@gmail.com', 19, '2026-04-10 11:51:51', NULL, 0.00),
(38, 'matteo.ongaro.2007@gmail.com', 21, '2026-04-10 11:51:58', NULL, 0.00),
(39, 'matteo.ongaro.2007@gmail.com', 14, '2026-04-10 11:52:20', NULL, 0.00),
(40, 'matteo.ongaro.2007@gmail.com', 5, '2026-04-12 20:50:03', 5, 0.00);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `carte_credito`
--
ALTER TABLE `carte_credito`
  ADD PRIMARY KEY (`id_carta`),
  ADD KEY `cod_u` (`cod_u`);

--
-- Indici per le tabelle `film`
--
ALTER TABLE `film`
  ADD PRIMARY KEY (`cod_f`);

--
-- Indici per le tabelle `noleggio`
--
ALTER TABLE `noleggio`
  ADD PRIMARY KEY (`id_noleggio`),
  ADD KEY `cod_f` (`cod_f`),
  ADD KEY `fk_noleggio_utente` (`email`);

--
-- Indici per le tabelle `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`cod_u`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `num_tessera` (`num_tessera`);

--
-- Indici per le tabelle `visionare`
--
ALTER TABLE `visionare`
  ADD PRIMARY KEY (`id_visione`),
  ADD KEY `fk_film` (`cod_f`),
  ADD KEY `fk_visionare_utente` (`cod_u`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `carte_credito`
--
ALTER TABLE `carte_credito`
  MODIFY `id_carta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `film`
--
ALTER TABLE `film`
  MODIFY `cod_f` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT per la tabella `noleggio`
--
ALTER TABLE `noleggio`
  MODIFY `id_noleggio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT per la tabella `utente`
--
ALTER TABLE `utente`
  MODIFY `cod_u` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT per la tabella `visionare`
--
ALTER TABLE `visionare`
  MODIFY `id_visione` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `carte_credito`
--
ALTER TABLE `carte_credito`
  ADD CONSTRAINT `carte_credito_ibfk_1` FOREIGN KEY (`cod_u`) REFERENCES `utente` (`cod_u`) ON DELETE CASCADE;

--
-- Limiti per la tabella `noleggio`
--
ALTER TABLE `noleggio`
  ADD CONSTRAINT `fk_noleggio_utente` FOREIGN KEY (`email`) REFERENCES `utente` (`email`) ON DELETE CASCADE,
  ADD CONSTRAINT `noleggio_ibfk_2` FOREIGN KEY (`cod_f`) REFERENCES `film` (`cod_f`);

--
-- Limiti per la tabella `visionare`
--
ALTER TABLE `visionare`
  ADD CONSTRAINT `fk_film` FOREIGN KEY (`cod_f`) REFERENCES `film` (`cod_f`),
  ADD CONSTRAINT `fk_visionare_utente` FOREIGN KEY (`cod_u`) REFERENCES `utente` (`email`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
