-- phpMyAdmin SQL Dump
-- version 4.1.14.8
-- http://www.phpmyadmin.net
--
-- Host: 
-- Generation Time: Jun 07, 2018 at 09:12 PM
-- Server version: 5.5.60-0+deb7u1-log
-- PHP Version: 5.4.45-0+deb7u14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: 
--

-- --------------------------------------------------------

--
-- Table structure for table `spots`
--

CREATE TABLE IF NOT EXISTS `spots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE latin1_german2_ci NOT NULL,
  `lat` float NOT NULL,
  `lng` float NOT NULL,
  `id00` mediumint(9) NOT NULL,
  `id01` mediumint(9) NOT NULL,
  `id10` mediumint(9) NOT NULL,
  `id11` mediumint(9) NOT NULL,
  `c00` float NOT NULL,
  `c01` float NOT NULL,
  `c10` float NOT NULL,
  `c11` float NOT NULL,
  `url` text COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `spots`
--

INSERT INTO `spots` (`id`, `name`, `lat`, `lng`, `id00`, `id01`, `id10`, `id11`, `c00`, `c01`, `c10`, `c11`, `url`) VALUES
(1, 'Example', 42.5275, 1.52057, 761766, 761767, 763206, 763207, 0.162835, 0.254889, 0.226981, 0.355296, '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
