-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 16, 2019 at 07:38 PM
-- Server version: 5.7.26-0ubuntu0.16.04.1
-- PHP Version: 7.0.33-0ubuntu0.16.04.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mis19018`
--

-- --------------------------------------------------------

--
-- Table structure for table `hangman_words`
--

CREATE TABLE `hangman_words` (
  `word` varchar(50) CHARACTER SET utf8 NOT NULL,
  `difficulty` enum('Easy','Medium','Hard','') NOT NULL,
  `id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `help` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hangman_words`
--

INSERT INTO `hangman_words` (`word`, `difficulty`, `id`, `points`, `help`) VALUES
('EXERCISE', 'Easy', 1, 10, 'Students doing in home...'),
('PROGRAMMING', 'Easy', 2, 10, 'In this lesson, we learn...'),
('MOBILE', 'Easy', 3, 10, 'We use it to make calls...'),
('TOURISM', 'Easy', 4, 10, 'Greek big industry...'),
('HOBBY', 'Medium', 5, 20, 'In our free time...'),
('BOOK', 'Medium', 6, 20, 'We read from...'),
('NETWORKS', 'Medium', 7, 20, 'Help us to communicate...'),
('PASSPORT', 'Hard', 8, 30, 'We use it on airport...'),
('CRIMINALS', 'Hard', 9, 30, 'People doing bad things...'),
('WATERMELON', 'Hard', 10, 30, 'Summer fruit...');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hangman_words`
--
ALTER TABLE `hangman_words`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `word` (`word`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hangman_words`
--
ALTER TABLE `hangman_words`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
