-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 06, 2022 at 08:23 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `iknow`
--

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `ID` bigint(20) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL,
  `question` text DEFAULT NULL,
  `bAnsw1` tinyint(1) NOT NULL DEFAULT 0,
  `Answer1` text DEFAULT NULL,
  `bAnsw2` tinyint(1) NOT NULL DEFAULT 0,
  `Answer2` text DEFAULT NULL,
  `bAnsw3` tinyint(1) NOT NULL DEFAULT 0,
  `Answer3` text DEFAULT NULL,
  `bAnsw4` tinyint(1) NOT NULL DEFAULT 0,
  `Answer4` text DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `bIsReviewed` tinyint(1) NOT NULL DEFAULT 0,
  `isFlagged` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`ID`, `subject`, `class`, `question`, `bAnsw1`, `Answer1`, `bAnsw2`, `Answer2`, `bAnsw3`, `Answer3`, `bAnsw4`, `Answer4`, `explanation`, `time`, `bIsReviewed`, `isFlagged`) VALUES
(2, 'Informatik B.Sc.', 'mathematische Logik', 'Was bedeutet A < B?', 1, 'A ist nicht größer als B', 0, 'B ist nicht größer als A', 0, 'A ist größer als B', 1, 'B ist größer als A', 'Hier ist auf die genaue Stellung der Worte zu achten!', '2022-05-04 13:09:45', 0, 0),
(3, 'Informatik B.Sc.', 'mathematische Logik', 'Wenn A äquivalent B ist, was bedeutet das?', 1, 'Wenn A falsch und B falsch ist, sind A und B äquivalent.', 0, 'Wenn A wahr und B wahr ist, sind A und B disjunkt.', 0, 'Wenn A wahr und B falsch ist, sind A und B äquivalent.', 0, 'Es gibt keine Äquivalenz in mathematischer Logik', 'Äquivalenz nennt man in der Logik den Fall, wenn auf beiden Seiten dasselbe steht. Beide sind entweder falsch oder beide sind richtig, nur dann sind sie äquivalent. Nicht mit dem logischen UND verwechseln, wo beide wahr sein müssen, damit das UND wahr ist.', '2022-05-04 13:17:15', 0, 0),
(4, 'Informatik B.Sc.', 'Machine Learning', 'Was ist ein Knoten?', 1, 'Eine Zusammenkunft von zwei Switches', 0, 'eine kunstvolle Verzwirbelung von Seilen', 0, 'ein finnischer Nachname', 0, 'Es gibt keine Knoten', '', '2022-05-11 12:17:45', 0, 0),
(5, 'Hotelmanagement M.A.', 'Architektur I', 'Warum sollte so oft wie möglich mit Überhängen gearbeitet werden?', 1, 'Weil Überhänge schön sind.', 0, 'Weil niemand Stabilität will', 1, 'Weil Überhänge nachgewiesenermaßen die höchste Stabilität besitzen', 0, 'Weil Säulen hässlich sind.', 'Weiteres hierzu ist, unter anderem, bei Monarch zu finden.', '2022-05-11 12:19:06', 0, 0),
(6, 'Wirtschaftsinformatik B.A.', 'wirtsch. Rechnungswesen', 'Was ist der Unterschied zwischen Aufwand und Kosten?', 0, 'Kosten können kein Aufwand sein.', 1, 'Aufwände sind auch Kosten.', 1, 'Aufwände sind leistungsbezogen.', 0, 'Kosten sind leistungsbezogen.', 'Grundwissen, siehe Wichert et. al. ', '2022-05-11 12:22:15', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `ID` bigint(20) NOT NULL,
  `sessionname` text DEFAULT NULL,
  `hostname` text DEFAULT NULL,
  `heartbeat` bigint(20) NOT NULL DEFAULT 0,
  `userID` bigint(20) DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `class` text DEFAULT NULL,
  `modus` text DEFAULT NULL,
  `ready` tinyint(1) NOT NULL DEFAULT 0,
  `actQuestion` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` bigint(20) NOT NULL,
  `user` text DEFAULT NULL,
  `role` text DEFAULT NULL,
  `passw` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `user`, `role`, `passw`) VALUES
(1, 'admin', 'admin', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4'),
(2, 'guest', 'member', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
