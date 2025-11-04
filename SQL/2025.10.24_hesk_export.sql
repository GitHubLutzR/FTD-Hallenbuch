-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 04. Nov 2025 um 15:22
-- Server-Version: 10.4.28-MariaDB
-- PHP-Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `hesk`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hb_gruppen`
--

CREATE TABLE `hb_gruppen` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `hb_gruppen`
--

INSERT INTO `hb_gruppen` (`id`, `name`) VALUES
(27, 'Artistik'),
(1, 'Dacapo'),
(2, 'Eltern-Kind-Turnen'),
(3, 'Folklore'),
(4, 'Gymnastik &Uuml;50'),
(5, 'Jugendgarde (JG)'),
(6, 'Kinderturnen'),
(26, 'Kita Wingertstr.'),
(7, 'Konfettis'),
(8, 'Leistungsturnen'),
(9, 'M&auml;dchenturnen'),
(10, 'M&auml;nnerballet (MB)'),
(11, 'Minigarde (MG)'),
(12, 'Moskitos'),
(13, 'Parkour'),
(14, 'Prinzengarde (PG)'),
(15, 'Roadrunners'),
(16, 'Solotanz'),
(17, 'sonstige'),
(18, 'Taka-Tuka-Land (TTL)'),
(19, 'Tanzduo'),
(20, 'Temptation'),
(21, 'Turnschl&auml;ppscher'),
(22, 'Vivendi'),
(23, 'Zumba'),
(24, 'Zwergengarde (ZG)');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hb_hallenbuch`
--

CREATE TABLE `hb_hallenbuch` (
  `id` int(11) NOT NULL,
  `datum` date NOT NULL,
  `von` time NOT NULL,
  `bis` time NOT NULL,
  `gruppe` varchar(50) NOT NULL,
  `trainer` varchar(100) NOT NULL,
  `vermerk` mediumtext DEFAULT NULL,
  `bemerkung` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `hb_hallenbuch`
--

INSERT INTO `hb_hallenbuch` (`id`, `datum`, `von`, `bis`, `gruppe`, `trainer`, `vermerk`, `bemerkung`) VALUES
(15, '2025-09-12', '18:30:00', '20:30:00', 'Moskitos', 'Jill/Marion', '', ''),
(16, '2025-09-12', '20:30:00', '22:30:00', 'M&auml;nnerballet (MB)', 'Jill/Marion', '', ''),
(18, '2025-09-13', '10:00:00', '16:00:00', 'Jugendgarde (JG)', 'Alissa/Luisa', '', ''),
(19, '2025-09-14', '09:00:00', '17:00:00', 'Temptation', 'Isa/Sassi', '', ''),
(20, '2025-09-15', '09:00:00', '11:30:00', 'Kita Wingertstr.', 'Agnes/Peter', '', ''),
(21, '2025-09-15', '14:30:00', '16:45:00', 'Taka-Tuka-Land (TTL)/Zwergengarde (ZG)', 'Cassedy/Stephanie', '', ''),
(22, '2025-09-15', '17:00:00', '18:30:00', 'Minigarde (MG)', 'Selina/Jenny', '', ''),
(23, '2025-09-15', '18:30:00', '20:00:00', 'Jugendgarde (JG)', 'Luisa/Alissa', '', ''),
(24, '2025-09-15', '20:00:00', '22:00:00', 'Prinzengarde (PG)', 'Kl&auml;re/Sophia', '', ''),
(25, '2025-09-16', '10:00:00', '11:00:00', 'Gymnastik &Uuml;50', 'R&ouml;der/Lipps', '', ''),
(26, '2025-09-16', '16:30:00', '18:30:00', 'Leistungsturnen', 'Michaela/Tabea', '', ''),
(27, '2025-09-16', '18:30:00', '20:30:00', 'Roadrunners', 'Dunja/Tamara', '', ''),
(28, '2025-09-16', '20:30:00', '22:30:00', 'Temptation', 'Sassi', '', ''),
(29, '2025-09-17', '15:30:00', '17:00:00', 'M&auml;dchenturnen', 'Mara', '', ''),
(30, '2025-09-17', '20:30:00', '22:30:00', 'Dacapo', 'Lisa/Pero', '', ''),
(31, '2025-09-18', '15:30:00', '17:00:00', 'Eltern-Kind-Turnen', 'Sanae', '', ''),
(32, '2025-09-18', '17:00:00', '18:45:00', 'Tanzduo', 'Lisa', '', ''),
(33, '2025-09-18', '18:30:00', '20:30:00', 'Turnschl&auml;ppscher', 'Tabea', '', ''),
(34, '2025-09-19', '11:00:00', '12:30:00', 'Artistik', 'Mertens', '', ''),
(35, '2025-09-19', '17:00:00', '18:30:00', 'Konfettis', 'Emilia/Ronja', '', ''),
(37, '2025-10-20', '17:00:00', '18:30:00', 'Minigarde (MG)', 'Selina/Jenny', '', ''),
(38, '2025-10-20', '20:00:00', '22:00:00', 'Prinzengarde (PG)', 'Kl&auml;re/Sophia', '', ''),
(40, '2025-10-21', '10:00:00', '11:30:00', 'Gymnastik &Uuml;50', 'Lipps/R&ouml;der', '', ''),
(41, '2025-10-20', '14:30:00', '16:50:00', 'Taka-Tuka-Land (TTL)/Zwergengarde (ZG)', 'Cassedy/Stephanie', '', 'Lichtschalter im &quot;Putz- /Ger&auml;teraum&quot; defekt!!!'),
(42, '2025-10-20', '10:00:00', '11:30:00', 'Leistungsturnen', 'Michaela/Lena/Torben', '', ''),
(43, '2025-10-21', '18:30:00', '20:30:00', 'Roadrunners', 'Tamara/Dunja', '', ''),
(44, '2025-10-21', '20:30:00', '22:30:00', 'Temptation', 'Sassi/Isa', '', ''),
(45, '2025-10-22', '15:30:00', '17:00:00', 'M&auml;dchenturnen', 'Gerlinde/Hannah', '', ''),
(46, '2025-10-22', '17:00:00', '19:00:00', 'Vivendi', 'Merle/Lena', '', ''),
(47, '2025-10-22', '19:00:00', '20:30:00', 'Solotanz', 'Lara/Naomi', '', ''),
(48, '2025-10-22', '20:30:00', '22:30:00', 'Dacapo', 'Lisa/Pero', '', ''),
(49, '2025-10-23', '12:00:00', '13:30:00', 'Artistik', 'Mertens', '', ''),
(52, '2025-10-23', '15:30:00', '17:00:00', 'Eltern-Kind-Turnen', 'Sanae', '', ''),
(53, '2025-10-23', '18:30:00', '20:30:00', 'Turnschl&auml;ppscher', 'Luana/Tabea', '', ''),
(54, '2025-10-24', '09:00:00', '11:30:00', 'Kita Wingertstr.', 'Winter/Fix', '', 're Au&szlig;ent&uuml;r war offen!'),
(55, '2025-10-24', '17:00:00', '18:30:00', 'Konfettis', 'Emilia/Ronja', '', ''),
(56, '2025-10-24', '18:30:00', '20:30:00', 'Moskitos', 'Marion/Jill', '', ''),
(57, '2025-10-24', '20:30:00', '22:30:00', 'M&auml;nnerballet (MB)', 'Marion/Jill', '', ''),
(58, '2025-10-13', '18:30:00', '20:00:00', 'Jugendgarde (JG)', 'Alissa/Luisa', '', ''),
(59, '2025-10-13', '20:00:00', '22:00:00', 'Prinzengarde (PG)', 'Sophia/Kl&auml;re', '', ''),
(60, '2025-10-14', '16:30:00', '18:30:00', 'Leistungsturnen', 'Michaela/Lena/Torben', '', ''),
(61, '2025-10-14', '18:30:00', '20:30:00', 'Roadrunners', 'Tamara/Dunja', '', ''),
(62, '2025-10-14', '20:30:00', '22:30:00', 'Temptation', 'Isa/Saskia', '', ''),
(63, '2025-10-15', '17:00:00', '19:00:00', 'Vivendi', 'Anne/Merle/Lena', '', 'das gro&szlig;e Licht geht nicht'),
(64, '2025-10-15', '19:00:00', '20:30:00', 'Solotanz', 'Lara/Naomi', '', ''),
(65, '2025-10-15', '20:30:00', '22:30:00', 'Dacapo', 'Peroni', '', 'Kippschalte Trainingsanlage defekt'),
(66, '2025-10-16', '11:00:00', '12:30:00', 'Artistik', 'Mertens', '', ''),
(67, '2025-10-16', '16:30:00', '18:30:00', 'Leistungsturnen', 'Michaela/Luana', '', ''),
(68, '2025-10-16', '18:30:00', '20:30:00', 'Turnschl&auml;ppscher', 'Luana/Michaela', '', ''),
(69, '2025-10-16', '17:00:00', '18:30:00', 'Solotanz', 'Sophia', '', ''),
(70, '2025-10-17', '12:00:00', '13:45:00', 'Artistik', 'Mertens', '', ''),
(71, '2025-10-17', '18:30:00', '20:30:00', 'Moskitos', 'Marion/Jill', '', ''),
(72, '2025-10-17', '20:30:00', '22:30:00', 'M&auml;nnerballet (MB)', 'Marion/Jill', '', ''),
(73, '2025-10-18', '11:00:00', '13:00:00', 'Zwergengarde (ZG)/Solotanz', 'Stephanie/Cassedy/Angie bis 12:30', '', ''),
(74, '2025-10-18', '13:00:00', '15:15:00', 'Dacapo', 'Lisa', '', ''),
(75, '2025-10-19', '10:00:00', '18:00:00', 'Moskitos', 'Jill/Marion', '', ''),
(76, '2025-10-04', '10:45:00', '13:45:00', 'Tanzduo', 'Lisa', '', ''),
(77, '2025-10-06', '09:15:00', '11:15:00', 'Kita Wingertstr.', 'Peter', '', ''),
(78, '2025-10-06', '12:00:00', '14:00:00', 'Artistik', 'Mertens', '', ''),
(79, '2025-10-06', '14:30:00', '16:45:00', 'Taka-Tuka-Land (TTL)/Zwergengarde (ZG)', 'Stephanie/Cassedy', '', ''),
(80, '2025-10-06', '20:00:00', '22:00:00', 'Prinzengarde (PG)', 'Sophia/Kl&auml;re', '', ''),
(81, '2025-10-07', '10:00:00', '11:00:00', 'Gymnastik &Uuml;50', 'Lipps', '', ''),
(82, '2025-10-07', '11:30:00', '13:30:00', 'Artistik', 'Mertens', '', ''),
(83, '2025-10-07', '16:30:00', '18:30:00', 'Leistungsturnen', 'Michaela/Lena', '', ''),
(84, '2025-10-07', '18:30:00', '20:30:00', 'Roadrunners', 'Tamara/Dunja', '', ''),
(85, '2025-10-07', '20:30:00', '22:30:00', 'Temptation', 'Saskia/Isa', '', ''),
(86, '2025-10-08', '18:30:00', '20:30:00', 'Solotanz', 'Lara/Naomi', '', ''),
(87, '2025-10-08', '20:30:00', '22:30:00', 'Dacapo', 'Lisa/Peroni', '', ''),
(88, '2025-10-09', '10:00:00', '12:00:00', 'Artistik', 'Mertens', '', ''),
(89, '2025-10-09', '15:30:00', '17:00:00', 'Eltern-Kind-Turnen', 'Sanae', '', ''),
(90, '2025-10-09', '17:00:00', '18:30:00', 'Leistungsturnen', 'Michaela/Tabea', '', ''),
(91, '2025-10-09', '18:30:00', '20:30:00', 'Turnschl&auml;ppscher', 'Luana/Tabea', '', ''),
(92, '2025-10-10', '18:30:00', '20:30:00', 'Moskitos', 'Marion/Jill', '', ''),
(93, '2025-10-10', '20:30:00', '22:30:00', 'M&auml;nnerballet (MB)', 'Marion/Jill', '', ''),
(94, '2025-10-13', '14:30:00', '16:45:00', 'Taka-Tuka-Land (TTL)/Zwergengarde (ZG)', 'Stephanie/Cassedy', '', ''),
(95, '2025-10-13', '17:00:00', '18:30:00', 'Minigarde (MG)', 'Selina/Jenny', '', ''),
(96, '2025-09-26', '18:30:00', '20:30:00', 'Moskitos', 'Marion/Jill', '', ''),
(97, '2025-09-26', '20:30:00', '22:30:00', 'M&auml;nnerballet (MB)', 'Marion/Jill', '', ''),
(98, '2025-09-27', '09:00:00', '14:00:00', 'Vivendi', 'Lena/Merle/Saskia/Anne', '', 'Licht im Ger&auml;teraum geht nicht '),
(99, '2025-09-28', '10:00:00', '18:00:00', 'Moskitos', 'Marion/Jill', '', ''),
(100, '2025-09-29', '14:30:00', '16:45:00', 'Taka-Tuka-Land (TTL)/Zwergengarde (ZG)', 'Stephanie', '', 'Durchgangst&uuml;ren offen '),
(101, '2025-09-29', '17:00:00', '18:30:00', 'Minigarde (MG)', 'Selina/Jenny', '', ''),
(102, '2025-09-29', '18:30:00', '20:00:00', 'Jugendgarde (JG)', 'Luisa/Alissa', '', ''),
(103, '2025-09-29', '20:00:00', '22:00:00', 'Prinzengarde (PG)', 'Kl&auml;re/Sophia', '', ''),
(104, '2025-09-30', '10:00:00', '11:00:00', 'Gymnastik &Uuml;50', 'Lipps/R&ouml;der', '', ''),
(105, '2025-09-30', '16:30:00', '18:30:00', 'Leistungsturnen', 'Michaela/Tabea/Lena', '', ''),
(106, '2025-09-30', '18:30:00', '20:30:00', 'Roadrunners', 'Tamara/Dunja', '', ''),
(107, '2025-09-30', '20:30:00', '22:30:00', 'Temptation', 'Saskia', '', ''),
(108, '2025-10-01', '15:30:00', '17:00:00', 'M&auml;dchenturnen', 'Gerlinde/Hannah', '', ''),
(109, '2025-10-01', '17:00:00', '19:00:00', 'Vivendi', 'Anne/Merle/Lena', '', ''),
(110, '2025-10-01', '19:00:00', '20:30:00', 'Solotanz', 'Lara/Naomi', '', ''),
(111, '2025-10-01', '20:30:00', '22:30:00', 'Dacapo', 'Peroni/Lisa', '', ''),
(112, '2025-10-02', '15:30:00', '17:00:00', 'Eltern-Kind-Turnen', 'Sanae', '', 'Halle war dreckig mit Kr&uuml;mel '),
(113, '2025-10-02', '17:00:00', '18:15:00', 'Tanzduo', 'Lisa', '', ''),
(114, '2025-10-02', '18:30:00', '20:30:00', 'Turnschl&auml;ppscher', 'Tabea/Michaela/Luana', '', ''),
(115, '2025-10-03', '17:00:00', '18:30:00', 'Konfettis', 'Ronja/Merle/Elena', '', ''),
(116, '2025-09-19', '18:30:00', '20:30:00', 'Moskitos', 'Marion/Jill', '', ''),
(117, '2025-09-19', '20:30:00', '22:30:00', 'M&auml;nnerballet (MB)', 'Marion/Jill', '', ''),
(118, '2025-09-20', '10:00:00', '14:00:00', 'Solotanz', 'Lara', '', ''),
(119, '2025-09-21', '09:00:00', '16:30:00', 'Turnschl&auml;ppscher', 'Luana/Tabea', '', ''),
(120, '2025-09-22', '09:00:00', '11:30:00', 'Kita Wingertstr.', 'Agnes/Peter', '', ''),
(121, '2025-09-22', '14:30:00', '16:45:00', 'Taka-Tuka-Land (TTL)/Zwergengarde (ZG)', 'Stephanie', '', ''),
(122, '2025-09-22', '18:30:00', '20:00:00', 'Jugendgarde (JG)', 'Alissa/Luisa', '', ''),
(123, '2025-09-22', '20:00:00', '22:00:00', 'Prinzengarde (PG)', 'Kl&auml;re/Sophia', '', 'Toilettenlicht UK2 geht nicht!'),
(124, '2025-09-23', '10:00:00', '11:00:00', 'Gymnastik &Uuml;50', 'Lipps', '', ''),
(125, '2025-09-23', '11:00:00', '12:30:00', 'Artistik', 'Mertens', '', ''),
(126, '2025-09-23', '16:30:00', '18:30:00', 'Leistungsturnen', 'Michaela/Lara/Tabea', '', ''),
(127, '2025-09-23', '18:30:00', '20:30:00', 'Roadrunners', 'Tamara', '', ''),
(128, '2025-09-23', '20:30:00', '22:30:00', 'Temptation', 'Isa/Saskia', '', ''),
(129, '2025-09-24', '15:30:00', '17:00:00', 'M&auml;dchenturnen', 'Gerlinde/Hannah', '', ''),
(130, '2025-09-24', '19:00:00', '20:30:00', 'Solotanz', 'Lara', '', ''),
(131, '2025-09-24', '20:30:00', '22:30:00', 'Dacapo', 'Lisa/Peroni', '', ''),
(132, '2025-09-25', '15:00:00', '17:30:00', 'Eltern-Kind-Turnen', 'Sanae', '', 'Hofft&uuml;r war komplett auf'),
(133, '2025-09-25', '18:30:00', '20:30:00', 'Turnschl&auml;ppscher', 'Luana/Tabea', '', 'Toilettenlicht UK2 geht nicht '),
(134, '2025-09-26', '17:00:00', '18:30:00', 'Konfettis', 'Ronja/Emilia/Merle', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hb_trainer`
--

CREATE TABLE `hb_trainer` (
  `trname` varchar(100) NOT NULL,
  `gruppe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `hb_trainer`
--

INSERT INTO `hb_trainer` (`trname`, `gruppe_id`) VALUES
('Lena', 22),
('Merle', 22),
('Saskia', 22),
('Anne', 22),
('Marion', 10),
('Marion', 12),
('Stephanie', 18),
('Stephanie', 24),
('Jill', 10),
('Jill', 12),
('Alissa', 5),
('Luisa', 5),
('Isa', 20),
('Saskia', 20),
('Agnes', 26),
('Peter', 26),
('Cassedy', 18),
('Cassedy', 24),
('Selina', 11),
('Jenny', 11),
('Kl&auml;re', 14),
('Sophia', 14),
('R&ouml;der', 4),
('Lipps', 4),
('Tabea', 8),
('Michaela', 8),
('Dunja', 15),
('Tamara', 15),
('Mara', 9),
('Lisa', 1),
('Peroni', 1),
('Sanae', 2),
('Lisa', 19),
('Tabea', 21),
('Mertens', 27),
('Emilia', 7),
('Ronja', 7),
('Torben', 8),
('Gerlinde', 9),
('Hannah', 9),
('Lara', 16),
('Naomi', 16),
('Georg', 13),
('Kristina', 6),
('Ines', 6),
('Sarah', 6),
('Jill', 23),
('Luana', 21),
('Kerstin', 3),
('Hannah', 3),
('Ines', 3),
('Jennifer', 11),
('Jasmin', 5),
('Nathalie', 15);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_attachments`
--

CREATE TABLE `hesk_attachments` (
  `att_id` mediumint(8) UNSIGNED NOT NULL,
  `ticket_id` varchar(13) NOT NULL DEFAULT '',
  `saved_name` varchar(255) NOT NULL DEFAULT '',
  `real_name` varchar(255) NOT NULL DEFAULT '',
  `size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `type` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_banned_emails`
--

CREATE TABLE `hesk_banned_emails` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `banned_by` smallint(5) UNSIGNED NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_banned_ips`
--

CREATE TABLE `hesk_banned_ips` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `ip_from` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ip_to` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ip_display` varchar(100) NOT NULL,
  `banned_by` smallint(5) UNSIGNED NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_categories`
--

CREATE TABLE `hesk_categories` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `cat_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `autoassign` enum('0','1') NOT NULL DEFAULT '1',
  `type` enum('0','1') NOT NULL DEFAULT '0',
  `priority` enum('0','1','2','3') NOT NULL DEFAULT '3'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `hesk_categories`
--

INSERT INTO `hesk_categories` (`id`, `name`, `cat_order`, `autoassign`, `type`, `priority`) VALUES
(1, 'General', 10, '1', '0', '3'),
(2, 'Defekt', 20, '1', '0', '2'),
(3, 'Mangel', 30, '1', '0', '3'),
(4, 'Vorschlag', 40, '1', '0', '3');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_custom_fields`
--

CREATE TABLE `hesk_custom_fields` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `use` enum('0','1','2') NOT NULL DEFAULT '0',
  `place` enum('0','1') NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL DEFAULT 'text',
  `req` enum('0','1','2') NOT NULL DEFAULT '0',
  `category` text DEFAULT NULL,
  `name` text DEFAULT NULL,
  `value` text DEFAULT NULL,
  `order` smallint(5) UNSIGNED NOT NULL DEFAULT 10
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `hesk_custom_fields`
--

INSERT INTO `hesk_custom_fields` (`id`, `use`, `place`, `type`, `req`, `category`, `name`, `value`, `order`) VALUES
(1, '1', '0', 'text', '0', NULL, '{\"English\":\"Location\",\"Deutsch\":\"Ort\"}', '{\"max_length\":50,\"default_value\":\"Halle\"}', 10),
(2, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(3, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(4, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(5, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(6, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(7, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(8, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(9, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(10, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(11, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(12, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(13, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(14, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(15, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(16, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(17, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(18, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(19, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(20, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(21, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(22, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(23, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(24, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(25, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(26, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(27, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(28, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(29, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(30, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(31, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(32, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(33, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(34, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(35, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(36, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(37, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(38, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(39, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(40, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(41, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(42, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(43, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(44, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(45, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(46, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(47, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(48, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(49, '0', '0', 'text', '0', NULL, '', NULL, 1000),
(50, '0', '0', 'text', '0', NULL, '', NULL, 1000);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_custom_statuses`
--

CREATE TABLE `hesk_custom_statuses` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `color` varchar(6) NOT NULL,
  `can_customers_change` enum('0','1') NOT NULL DEFAULT '1',
  `order` smallint(5) UNSIGNED NOT NULL DEFAULT 10
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_kb_articles`
--

CREATE TABLE `hesk_kb_articles` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `catid` smallint(5) UNSIGNED NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `author` smallint(5) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `keywords` mediumtext NOT NULL,
  `rating` float NOT NULL DEFAULT 0,
  `votes` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `views` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `type` enum('0','1','2') NOT NULL DEFAULT '0',
  `html` enum('0','1') NOT NULL DEFAULT '0',
  `sticky` enum('0','1') NOT NULL DEFAULT '0',
  `art_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `history` mediumtext NOT NULL,
  `attachments` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_kb_attachments`
--

CREATE TABLE `hesk_kb_attachments` (
  `att_id` mediumint(8) UNSIGNED NOT NULL,
  `saved_name` varchar(255) NOT NULL DEFAULT '',
  `real_name` varchar(255) NOT NULL DEFAULT '',
  `size` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_kb_categories`
--

CREATE TABLE `hesk_kb_categories` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent` smallint(5) UNSIGNED NOT NULL,
  `articles` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `articles_private` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `articles_draft` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `cat_order` smallint(5) UNSIGNED NOT NULL,
  `type` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `hesk_kb_categories`
--

INSERT INTO `hesk_kb_categories` (`id`, `name`, `parent`, `articles`, `articles_private`, `articles_draft`, `cat_order`, `type`) VALUES
(1, 'Knowledgebase', 0, 0, 0, 0, 10, '0');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_logins`
--

CREATE TABLE `hesk_logins` (
  `ip` varchar(45) NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_log_overdue`
--

CREATE TABLE `hesk_log_overdue` (
  `id` int(10) UNSIGNED NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `ticket` mediumint(8) UNSIGNED NOT NULL,
  `category` smallint(5) UNSIGNED NOT NULL,
  `priority` enum('0','1','2','3') NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `owner` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `due_date` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00',
  `comments` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_mail`
--

CREATE TABLE `hesk_mail` (
  `id` int(10) UNSIGNED NOT NULL,
  `from` smallint(5) UNSIGNED NOT NULL,
  `to` smallint(5) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` mediumtext NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `read` enum('0','1') NOT NULL DEFAULT '0',
  `deletedby` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `hesk_mail`
--

INSERT INTO `hesk_mail` (`id`, `from`, `to`, `subject`, `message`, `dt`, `read`, `deletedby`) VALUES
(1, 9999, 1, 'Hesk quick start guide', '</p><div style=\"text-align:justify; padding-left: 10px; padding-right: 10px;\">\r\n\r\n<h2 style=\"padding-left:0px\">Welcome to Hesk, an excellent tool for improving your customer support!</h2>\r\n\r\n<h3>Below is a short guide to help you get started.</h3>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #1: Set up your profile</h3>\r\n\r\n<ol>\r\n<li>go to <a href=\"profile.php\">Profile</a>,</li>\r\n<li>set your name and email address.</li>\r\n</ol>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #2: Configure Hesk</h3>\r\n\r\n<ol>\r\n<li>go to <a href=\"admin_settings_general.php\">Settings</a>,</li>\r\n<li>for a quick start, modify these settings on the \"General\" tab:<br><br>\r\n<b>Website title</b> - enter the title of your main website (not your help desk),<br>\r\n<b>Website URL</b> - enter the URL of your main website,<br>\r\n<b>Webmaster email</b> - enter an alternative email address people can contact in case your Hesk database is down<br>&nbsp;\r\n</li>\r\n<li>you can come back to the settings page later and explore all the options. To view details about a setting, click the [?]</li>\r\n</ol>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #3: Add support categories</h3>\r\n\r\n<p>Go to <a href=\"manage_categories.php\">Categories</a> to add support ticket categories.</p>\r\n<p>You cannot delete the default category, but you can rename it.</p>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #4: Add your support team members</h3>\r\n\r\n<p>Go to <a href=\"manage_users.php\">Team</a> to create new support staff accounts.</p>\r\n<p>You can use two user types in Hesk:</p>\r\n<ul>\r\n<li><b>Administrators</b> who have full access to all Hesk features</li>\r\n<li><b>Staff</b> who you can restrict access to categories and features</li>\r\n</ul>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #5: Useful tools</h3>\r\n\r\n<p>You can do a lot in the <a href=\"banned_emails.php\">Tools</a> section, for example:</p>\r\n<ul>\r\n<li>create custom ticket statuses,</li>\r\n<li>add custom input fields to the &quot;Submit a ticket&quot; form,</li>\r\n<li>make public announcements (Service messages),</li>\r\n<li>modify email templates,</li>\r\n<li>ban disruptive customers,</li>\r\n<li>and more.</li>\r\n</ul>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #6: Create a Knowledgebase</h3>\r\n\r\n<p>A Knowledgebase is a collection of articles, guides, and answers to frequently asked questions, usually organized in multiple categories.</p>\r\n<p>A clear and comprehensive knowledgebase can drastically reduce the number of support tickets you receive, thereby saving you significant time and effort in the long run.</p>\r\n<p>Go to <a href=\"manage_knowledgebase.php\">Knowledgebase</a> to create categories and write articles for your knowledgebase.</p>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #7: Don\'t repeat yourself</h3>\r\n\r\n<p>Sometimes several support tickets address the same issues - allowing you to use pre-written (&quot;canned&quot;) responses.</p>\r\n<p>To compose canned responses, go to the <a href=\"manage_canned.php\">Templates &gt; Responses</a> page.</p>\r\n<p>Similarly, you can create <a href=\"manage_ticket_templates.php\">Templates &gt; Tickets</a> if your staff will be submitting support tickets on the client\'s behalf, for example, from telephone conversations.</p>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #8: Secure your help desk</h3>\r\n\r\n<p>Make sure your help desk is as secure as possible by going through the <a href=\"https://www.hesk.com/knowledgebase/?article=82\">Hesk security checklist</a>.</p>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #9: Stay updated</h3>\r\n\r\n<p>Hesk regularly receives improvements and bug fixes; make sure you know about them!</p>\r\n<ul>\r\n<li>for fast notifications, <a href=\"https://twitter.com/HESKdotCOM\">follow us on <b>Twitter</b></a></li>\r\n<li>for email notifications, subscribe to our low-volume zero-spam <a href=\"https://www.hesk.com/newsletter.php\">newsletter</a></li>\r\n</ul>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #10: Look professional</h3>\r\n\r\n<p>To not only support Hesk development but also look more professional, <a href=\"https://www.hesk.com/get/hesk3-license\">remove &quot;Powered by&quot; links</a> from your help desk.</p>\r\n\r\n&nbsp;\r\n\r\n<h3>&raquo; Step #11: Too much hassle? Switch to Hesk Cloud for the ultimate experience</h3>\r\n\r\n<p>Experience the best of Hesk by moving your help desk into the Hesk Cloud:</p>\r\n<ul>\r\n<li>exclusive advanced modules,</li>\r\n<li>automated updates,</li>\r\n<li>free migration of your existing Hesk tickets and settings,</li>\r\n<li>we take care of maintenance, server setup and optimization, backups, and more!</li>\r\n</ul>\r\n\r\n<p>&nbsp;<br><a href=\"https://www.hesk.com/get/hesk3-cloud\" class=\"btn btn--blue-border\" style=\"text-decoration:none\">Click here to learn more about Hesk Cloud</a></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Again, welcome to Hesk, and enjoy using it!</p>\r\n\r\n<p>Klemen Stirn<br>\r\nFounder<br>\r\n<a href=\"https://www.hesk.com\">https://www.hesk.com</a></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n</div><p>', '2025-09-26 06:48:19', '1', 9999);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_notes`
--

CREATE TABLE `hesk_notes` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `ticket` mediumint(8) UNSIGNED NOT NULL,
  `who` smallint(5) UNSIGNED NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `message` mediumtext NOT NULL,
  `attachments` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_online`
--

CREATE TABLE `hesk_online` (
  `user_id` smallint(5) UNSIGNED NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tmp` int(11) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_pipe_loops`
--

CREATE TABLE `hesk_pipe_loops` (
  `email` varchar(255) NOT NULL,
  `hits` smallint(1) UNSIGNED NOT NULL DEFAULT 0,
  `message_hash` char(32) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_replies`
--

CREATE TABLE `hesk_replies` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `replyto` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `message` mediumtext NOT NULL,
  `message_html` mediumtext DEFAULT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `attachments` mediumtext DEFAULT NULL,
  `staffid` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `rating` enum('1','5') DEFAULT NULL,
  `read` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_reply_drafts`
--

CREATE TABLE `hesk_reply_drafts` (
  `owner` smallint(5) UNSIGNED NOT NULL,
  `ticket` mediumint(8) UNSIGNED NOT NULL,
  `message` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `message_html` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_reset_password`
--

CREATE TABLE `hesk_reset_password` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `user` smallint(5) UNSIGNED NOT NULL,
  `hash` char(40) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_service_messages`
--

CREATE TABLE `hesk_service_messages` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `author` smallint(5) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` mediumtext NOT NULL,
  `language` varchar(50) DEFAULT NULL,
  `style` enum('0','1','2','3','4') NOT NULL DEFAULT '0',
  `type` enum('0','1') NOT NULL DEFAULT '0',
  `order` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_std_replies`
--

CREATE TABLE `hesk_std_replies` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `message` mediumtext NOT NULL,
  `message_html` mediumtext DEFAULT NULL,
  `reply_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_tickets`
--

CREATE TABLE `hesk_tickets` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `trackid` varchar(13) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(1000) NOT NULL DEFAULT '',
  `category` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `priority` enum('0','1','2','3') NOT NULL DEFAULT '3',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `message` mediumtext NOT NULL,
  `message_html` mediumtext DEFAULT NULL,
  `dt` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00',
  `lastchange` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `firstreply` timestamp NULL DEFAULT NULL,
  `closedat` timestamp NULL DEFAULT NULL,
  `articles` varchar(255) DEFAULT NULL,
  `ip` varchar(45) NOT NULL DEFAULT '',
  `language` varchar(50) DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `openedby` mediumint(8) DEFAULT 0,
  `firstreplyby` smallint(5) UNSIGNED DEFAULT NULL,
  `closedby` mediumint(8) DEFAULT NULL,
  `replies` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `staffreplies` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `owner` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `assignedby` mediumint(8) DEFAULT NULL,
  `time_worked` time NOT NULL DEFAULT '00:00:00',
  `lastreplier` enum('0','1') NOT NULL DEFAULT '0',
  `replierid` smallint(5) UNSIGNED DEFAULT NULL,
  `archive` enum('0','1') NOT NULL DEFAULT '0',
  `locked` enum('0','1') NOT NULL DEFAULT '0',
  `attachments` mediumtext NOT NULL,
  `merged` mediumtext NOT NULL,
  `history` mediumtext NOT NULL,
  `custom1` mediumtext NOT NULL,
  `custom2` mediumtext NOT NULL,
  `custom3` mediumtext NOT NULL,
  `custom4` mediumtext NOT NULL,
  `custom5` mediumtext NOT NULL,
  `custom6` mediumtext NOT NULL,
  `custom7` mediumtext NOT NULL,
  `custom8` mediumtext NOT NULL,
  `custom9` mediumtext NOT NULL,
  `custom10` mediumtext NOT NULL,
  `custom11` mediumtext NOT NULL,
  `custom12` mediumtext NOT NULL,
  `custom13` mediumtext NOT NULL,
  `custom14` mediumtext NOT NULL,
  `custom15` mediumtext NOT NULL,
  `custom16` mediumtext NOT NULL,
  `custom17` mediumtext NOT NULL,
  `custom18` mediumtext NOT NULL,
  `custom19` mediumtext NOT NULL,
  `custom20` mediumtext NOT NULL,
  `custom21` mediumtext NOT NULL,
  `custom22` mediumtext NOT NULL,
  `custom23` mediumtext NOT NULL,
  `custom24` mediumtext NOT NULL,
  `custom25` mediumtext NOT NULL,
  `custom26` mediumtext NOT NULL,
  `custom27` mediumtext NOT NULL,
  `custom28` mediumtext NOT NULL,
  `custom29` mediumtext NOT NULL,
  `custom30` mediumtext NOT NULL,
  `custom31` mediumtext NOT NULL,
  `custom32` mediumtext NOT NULL,
  `custom33` mediumtext NOT NULL,
  `custom34` mediumtext NOT NULL,
  `custom35` mediumtext NOT NULL,
  `custom36` mediumtext NOT NULL,
  `custom37` mediumtext NOT NULL,
  `custom38` mediumtext NOT NULL,
  `custom39` mediumtext NOT NULL,
  `custom40` mediumtext NOT NULL,
  `custom41` mediumtext NOT NULL,
  `custom42` mediumtext NOT NULL,
  `custom43` mediumtext NOT NULL,
  `custom44` mediumtext NOT NULL,
  `custom45` mediumtext NOT NULL,
  `custom46` mediumtext NOT NULL,
  `custom47` mediumtext NOT NULL,
  `custom48` mediumtext NOT NULL,
  `custom49` mediumtext NOT NULL,
  `custom50` mediumtext NOT NULL,
  `due_date` timestamp NULL DEFAULT NULL,
  `overdue_email_sent` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `hesk_tickets`
--

INSERT INTO `hesk_tickets` (`id`, `trackid`, `name`, `email`, `category`, `priority`, `subject`, `message`, `message_html`, `dt`, `lastchange`, `firstreply`, `closedat`, `articles`, `ip`, `language`, `status`, `openedby`, `firstreplyby`, `closedby`, `replies`, `staffreplies`, `owner`, `assignedby`, `time_worked`, `lastreplier`, `replierid`, `archive`, `locked`, `attachments`, `merged`, `history`, `custom1`, `custom2`, `custom3`, `custom4`, `custom5`, `custom6`, `custom7`, `custom8`, `custom9`, `custom10`, `custom11`, `custom12`, `custom13`, `custom14`, `custom15`, `custom16`, `custom17`, `custom18`, `custom19`, `custom20`, `custom21`, `custom22`, `custom23`, `custom24`, `custom25`, `custom26`, `custom27`, `custom28`, `custom29`, `custom30`, `custom31`, `custom32`, `custom33`, `custom34`, `custom35`, `custom36`, `custom37`, `custom38`, `custom39`, `custom40`, `custom41`, `custom42`, `custom43`, `custom44`, `custom45`, `custom46`, `custom47`, `custom48`, `custom49`, `custom50`, `due_date`, `overdue_email_sent`) VALUES
(1, 'U2V-2YJ-W4T9', 'anon', 'ticket@freieturner.com', 1, '3', 'testticket 1', 'das ist ein Test', 'das ist ein Test', '2025-09-26 07:29:47', '2025-09-26 19:00:57', NULL, '2025-09-26 19:00:57', NULL, '172.20.0.3', NULL, 3, 0, NULL, 1, 0, 0, 1, -1, '00:00:00', '0', NULL, '0', '0', '', '', '<li class=\"smaller\">2025-09-26 09:29:47 | submitted by Customer</li><li class=\"smaller\">2025-09-26 09:29:47 | automatically assigned to FTD Admin (Administrator)</li><li class=\"smaller\">2025-09-26 21:00:21 &#124; Erledigt von FTD Admin (Administrator)</li><li class=\"smaller\">2025-09-26 21:00:30 &#124; Status geändert auf <b>In Bearbeitung</b> von FTD Admin (Administrator)</li><li class=\"smaller\">2025-09-26 21:00:34 &#124; Erledigt von FTD Admin (Administrator)</li><li class=\"smaller\">2025-09-26 21:00:54 &#124; Status geändert auf <b>Warte auf Antwort</b> von FTD Admin (Administrator)</li><li class=\"smaller\">2025-09-26 21:00:57 &#124; Erledigt von FTD Admin (Administrator)</li>', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_ticket_templates`
--

CREATE TABLE `hesk_ticket_templates` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `message` mediumtext NOT NULL,
  `message_html` mediumtext DEFAULT NULL,
  `tpl_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `hesk_ticket_templates`
--

INSERT INTO `hesk_ticket_templates` (`id`, `title`, `message`, `message_html`, `tpl_order`) VALUES
(1, 'Ticketvorlage1', 'Ticketvorlage1 Nachricht', 'Ticketvorlage1 Nachricht', 10);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hesk_users`
--

CREATE TABLE `hesk_users` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `user` varchar(20) NOT NULL DEFAULT '',
  `pass` char(40) NOT NULL,
  `isadmin` enum('0','1') NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(1000) NOT NULL DEFAULT '',
  `language` varchar(50) DEFAULT NULL,
  `categories` varchar(500) NOT NULL DEFAULT '',
  `afterreply` enum('0','1','2') NOT NULL DEFAULT '0',
  `autostart` enum('0','1') NOT NULL DEFAULT '1',
  `autoreload` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `notify_customer_new` enum('0','1') NOT NULL DEFAULT '1',
  `notify_customer_reply` enum('0','1') NOT NULL DEFAULT '1',
  `show_suggested` enum('0','1') NOT NULL DEFAULT '1',
  `notify_new_unassigned` enum('0','1') NOT NULL DEFAULT '1',
  `notify_new_my` enum('0','1') NOT NULL DEFAULT '1',
  `notify_reply_unassigned` enum('0','1') NOT NULL DEFAULT '1',
  `notify_reply_my` enum('0','1') NOT NULL DEFAULT '1',
  `notify_assigned` enum('0','1') NOT NULL DEFAULT '1',
  `notify_pm` enum('0','1') NOT NULL DEFAULT '1',
  `notify_note` enum('0','1') NOT NULL DEFAULT '1',
  `notify_overdue_unassigned` enum('0','1') NOT NULL DEFAULT '1',
  `notify_overdue_my` enum('0','1') NOT NULL DEFAULT '1',
  `default_list` varchar(255) NOT NULL DEFAULT '',
  `autoassign` enum('0','1') NOT NULL DEFAULT '1',
  `heskprivileges` varchar(1000) DEFAULT NULL,
  `ratingneg` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `ratingpos` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `rating` float NOT NULL DEFAULT 0,
  `replies` mediumint(8) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `hesk_users`
--

INSERT INTO `hesk_users` (`id`, `user`, `pass`, `isadmin`, `name`, `email`, `signature`, `language`, `categories`, `afterreply`, `autostart`, `autoreload`, `notify_customer_new`, `notify_customer_reply`, `show_suggested`, `notify_new_unassigned`, `notify_new_my`, `notify_reply_unassigned`, `notify_reply_my`, `notify_assigned`, `notify_pm`, `notify_note`, `notify_overdue_unassigned`, `notify_overdue_my`, `default_list`, `autoassign`, `heskprivileges`, `ratingneg`, `ratingpos`, `rating`, `replies`) VALUES
(1, 'Administrator', '88a9742af5ef9f2e6d580fe1b0bc5b0bc535bbc4', '1', 'FTD Admin', 'it@freieturner.com', '', NULL, '', '0', '1', 0, '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '', '0', '', 0, 0, 0, 0),
(2, 'LutzR', 'a26c3bb15017c9ef8833eda1e4c22571077e22a9', '0', 'Lutz Risse', 'lutz.risse@freieturner.com', 'Mit sprtlichen Grüßen\r\nLutz', NULL, '1', '0', '1', 0, '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '', '1', 'can_view_tickets,can_reply_tickets,can_resolve,can_submit_any_cat,can_change_cat,can_assign_self,can_view_unassigned,can_view_online,can_view_tickets', 0, 0, 0, 0),
(3, 'ThomasS', 'a02726482c9029f8af0de131fdb2dd17844a5344', '0', 'Thomas Schäfer', 'thomas.schaefer@freieturner.com', 'Mit sportlichen Grüßen\r\nThomas', NULL, '1', '0', '1', 0, '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '', '1', 'can_view_tickets,can_reply_tickets,can_resolve,can_submit_any_cat,can_change_cat,can_assign_self,can_view_unassigned,can_view_online,can_view_tickets', 0, 0, 0, 0),
(4, 'MatthiasG', '918f5445271a739e5386754bd6cbc477ea605fb3', '0', 'Matthias Gora', 'matthias.gora@freieturner.com', 'Mit sportlichen Grüßen\r\nMathias', NULL, '1', '0', '1', 0, '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '', '1', 'can_view_tickets,can_reply_tickets,can_resolve,can_submit_any_cat,can_change_cat,can_assign_self,can_view_unassigned,can_view_online,can_view_tickets', 0, 0, 0, 0);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `hb_gruppen`
--
ALTER TABLE `hb_gruppen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indizes für die Tabelle `hb_hallenbuch`
--
ALTER TABLE `hb_hallenbuch`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `hesk_attachments`
--
ALTER TABLE `hesk_attachments`
  ADD PRIMARY KEY (`att_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indizes für die Tabelle `hesk_banned_emails`
--
ALTER TABLE `hesk_banned_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indizes für die Tabelle `hesk_banned_ips`
--
ALTER TABLE `hesk_banned_ips`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `hesk_categories`
--
ALTER TABLE `hesk_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);

--
-- Indizes für die Tabelle `hesk_custom_fields`
--
ALTER TABLE `hesk_custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `useType` (`use`,`type`);

--
-- Indizes für die Tabelle `hesk_custom_statuses`
--
ALTER TABLE `hesk_custom_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `hesk_kb_articles`
--
ALTER TABLE `hesk_kb_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `catid` (`catid`),
  ADD KEY `sticky` (`sticky`),
  ADD KEY `type` (`type`);
ALTER TABLE `hesk_kb_articles` ADD FULLTEXT KEY `subject` (`subject`,`content`,`keywords`);

--
-- Indizes für die Tabelle `hesk_kb_attachments`
--
ALTER TABLE `hesk_kb_attachments`
  ADD PRIMARY KEY (`att_id`);

--
-- Indizes für die Tabelle `hesk_kb_categories`
--
ALTER TABLE `hesk_kb_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `parent` (`parent`);

--
-- Indizes für die Tabelle `hesk_logins`
--
ALTER TABLE `hesk_logins`
  ADD UNIQUE KEY `ip` (`ip`);

--
-- Indizes für die Tabelle `hesk_log_overdue`
--
ALTER TABLE `hesk_log_overdue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket` (`ticket`),
  ADD KEY `category` (`category`),
  ADD KEY `priority` (`priority`),
  ADD KEY `status` (`status`),
  ADD KEY `owner` (`owner`);

--
-- Indizes für die Tabelle `hesk_mail`
--
ALTER TABLE `hesk_mail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from` (`from`),
  ADD KEY `to` (`to`,`read`,`deletedby`);

--
-- Indizes für die Tabelle `hesk_notes`
--
ALTER TABLE `hesk_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticketid` (`ticket`);

--
-- Indizes für die Tabelle `hesk_online`
--
ALTER TABLE `hesk_online`
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `dt` (`dt`);

--
-- Indizes für die Tabelle `hesk_pipe_loops`
--
ALTER TABLE `hesk_pipe_loops`
  ADD KEY `email` (`email`,`hits`);

--
-- Indizes für die Tabelle `hesk_replies`
--
ALTER TABLE `hesk_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `replyto` (`replyto`),
  ADD KEY `dt` (`dt`),
  ADD KEY `staffid` (`staffid`);

--
-- Indizes für die Tabelle `hesk_reply_drafts`
--
ALTER TABLE `hesk_reply_drafts`
  ADD KEY `owner` (`owner`),
  ADD KEY `ticket` (`ticket`);

--
-- Indizes für die Tabelle `hesk_reset_password`
--
ALTER TABLE `hesk_reset_password`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`);

--
-- Indizes für die Tabelle `hesk_service_messages`
--
ALTER TABLE `hesk_service_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);

--
-- Indizes für die Tabelle `hesk_std_replies`
--
ALTER TABLE `hesk_std_replies`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `hesk_tickets`
--
ALTER TABLE `hesk_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trackid` (`trackid`),
  ADD KEY `archive` (`archive`),
  ADD KEY `categories` (`category`),
  ADD KEY `statuses` (`status`),
  ADD KEY `owner` (`owner`),
  ADD KEY `openedby` (`openedby`,`firstreplyby`,`closedby`),
  ADD KEY `dt` (`dt`);

--
-- Indizes für die Tabelle `hesk_ticket_templates`
--
ALTER TABLE `hesk_ticket_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `hesk_users`
--
ALTER TABLE `hesk_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autoassign` (`autoassign`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `hb_gruppen`
--
ALTER TABLE `hb_gruppen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT für Tabelle `hb_hallenbuch`
--
ALTER TABLE `hb_hallenbuch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT für Tabelle `hesk_attachments`
--
ALTER TABLE `hesk_attachments`
  MODIFY `att_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_banned_emails`
--
ALTER TABLE `hesk_banned_emails`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_banned_ips`
--
ALTER TABLE `hesk_banned_ips`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_categories`
--
ALTER TABLE `hesk_categories`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT für Tabelle `hesk_kb_articles`
--
ALTER TABLE `hesk_kb_articles`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_kb_attachments`
--
ALTER TABLE `hesk_kb_attachments`
  MODIFY `att_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_kb_categories`
--
ALTER TABLE `hesk_kb_categories`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `hesk_log_overdue`
--
ALTER TABLE `hesk_log_overdue`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_mail`
--
ALTER TABLE `hesk_mail`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `hesk_notes`
--
ALTER TABLE `hesk_notes`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_replies`
--
ALTER TABLE `hesk_replies`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_reset_password`
--
ALTER TABLE `hesk_reset_password`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_service_messages`
--
ALTER TABLE `hesk_service_messages`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_std_replies`
--
ALTER TABLE `hesk_std_replies`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hesk_tickets`
--
ALTER TABLE `hesk_tickets`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `hesk_ticket_templates`
--
ALTER TABLE `hesk_ticket_templates`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `hesk_users`
--
ALTER TABLE `hesk_users`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
