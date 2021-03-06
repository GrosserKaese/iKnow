-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 14, 2022 at 01:33 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 8.0.1

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
-- Table structure for table `donequestions`
--

CREATE TABLE `donequestions` (
  `ID` bigint(20) NOT NULL,
  `question` bigint(20) DEFAULT NULL COMMENT 'ID of the question in the catalogue',
  `sessionname` int(11) NOT NULL,
  `user` text DEFAULT NULL,
  `bAnsw1` tinyint(1) NOT NULL DEFAULT 0,
  `bAnsw2` tinyint(1) NOT NULL DEFAULT 0,
  `bAnsw3` tinyint(1) NOT NULL DEFAULT 0,
  `bAnsw4` tinyint(1) NOT NULL DEFAULT 0,
  `qCounter` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `isFlagged` tinyint(1) NOT NULL DEFAULT 0,
  `flaggedExplanation` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`ID`, `subject`, `class`, `question`, `bAnsw1`, `Answer1`, `bAnsw2`, `Answer2`, `bAnsw3`, `Answer3`, `bAnsw4`, `Answer4`, `explanation`, `time`, `bIsReviewed`, `isFlagged`, `flaggedExplanation`) VALUES
(2, 'Informatik B.Sc.', 'mathematische Logik', 'Was bedeutet A < B?', 1, 'A ist nicht gr????er als B', 0, 'B ist nicht gr????er als A', 0, 'A ist gr????er als B', 1, 'B ist gr????er als A', 'Hier ist auf die genaue Stellung der Worte zu achten!', '2022-05-04 13:09:45', 1, 0, NULL),
(3, 'Informatik B.Sc.', 'mathematische Logik', 'Wenn A ??quivalent B ist, was bedeutet das?', 1, 'Wenn A falsch und B falsch ist, sind A und B ??quivalent.', 0, 'Wenn A wahr und B wahr ist, sind A und B disjunkt.', 0, 'Wenn A wahr und B falsch ist, sind A und B ??quivalent.', 0, 'Es gibt keine ??quivalenz in mathematischer Logik', '??quivalenz nennt man in der Logik den Fall, wenn auf beiden Seiten dasselbe steht. Beide sind entweder falsch oder beide sind richtig, nur dann sind sie ??quivalent. Nicht mit dem logischen UND verwechseln, wo beide wahr sein m??ssen, damit das UND wahr ist.', '2022-05-04 13:17:15', 1, 0, NULL),
(4, 'Informatik B.Sc.', 'Machine Learning', 'Was ist ein Knoten?', 1, 'Eine Zusammenkunft von zwei Switches', 0, 'eine kunstvolle Verzwirbelung von Seilen', 0, 'ein finnischer Nachname', 0, 'Es gibt keine Knoten', '', '2022-05-11 12:17:45', 1, 0, NULL),
(5, 'Hotelmanagement M.A.', 'Architektur I', 'Warum sollte so oft wie m??glich mit ??berh??ngen gearbeitet werden?', 1, 'Weil ??berh??nge sch??n sind.', 0, 'Weil niemand Stabilit??t will', 1, 'Weil ??berh??nge nachgewiesenerma??en die h??chste Stabilit??t besitzen', 0, 'Weil S??ulen h??sslich sind.', 'Weiteres hierzu ist, unter anderem, bei Monarch zu finden.', '2022-05-11 12:19:06', 0, 0, NULL),
(6, 'Wirtschaftsinformatik B.A.', 'wirtsch. Rechnungswesen', 'Was ist der Unterschied zwischen Aufwand und Kosten?', 0, 'Kosten k??nnen kein Aufwand sein.', 1, 'Aufw??nde sind auch Kosten.', 1, 'Aufw??nde sind leistungsbezogen.', 0, 'Kosten sind leistungsbezogen.', 'Grundwissen, siehe Wichert et. al. ', '2022-05-11 12:22:15', 0, 0, NULL),
(7, 'Informatik B.Sc.', 'mathematische Logik', 'Was ist 4+5?', 1, '2x4,5', 0, '5', 0, '4', 1, '9', '', '2022-06-11 10:40:43', 1, 1, 'albern'),
(8, 'Informatik B.Sc.', 'mathematische Logik', 'Warum ist 2 gr????er als 1?', 1, 'Weil 2 gr????er als 1 ist.', 0, 'Weil im Joghurt keine Gr??ten sind.', 0, 'Bill Gates will es so.', 0, 'Das ??ndert sich von Fall zu Fall.', '', '2022-06-11 10:42:05', 1, 0, NULL);

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
  `actQuestion` bigint(20) DEFAULT 0,
  `control` tinyint(1) NOT NULL DEFAULT 0,
  `bGameStarted` tinyint(1) NOT NULL DEFAULT 0
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
-- Indexes for table `donequestions`
--
ALTER TABLE `donequestions`
  ADD PRIMARY KEY (`ID`);

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
-- AUTO_INCREMENT for table `donequestions`
--
ALTER TABLE `donequestions`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=472;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=395;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
