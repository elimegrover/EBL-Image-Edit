-- phpMyAdmin SQL Dump
-- version 3.4.11.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 17, 2012 at 11:42 AM
-- Server version: 5.5.27
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `syserror_txp`
--

-- --------------------------------------------------------

--
-- Table structure for table `ebl_crop`
--

CREATE TABLE IF NOT EXISTS `ebl_crop` (
  `name` varchar(64) NOT NULL,
  `width` varchar(16) NOT NULL,
  `height` varchar(16) NOT NULL,
  `thumb` varchar(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ebl_crop`
--

INSERT INTO `ebl_crop` (`name`, `width`, `height`, `thumb`) VALUES
('Image Crop', '500', '500', '0'),
('Thumbnail Square', '100', '100', '1');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
