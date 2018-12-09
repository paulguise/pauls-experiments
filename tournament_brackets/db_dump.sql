-- phpMyAdmin SQL Dump
-- version 3.4.3.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 13, 2012 at 12:18 PM
-- Server version: 5.0.77
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `boss_phalox_lan`
--

-- --------------------------------------------------------

--
-- Table structure for table `lan_brackets`
--

DROP TABLE IF EXISTS `lan_brackets`;
CREATE TABLE IF NOT EXISTS `lan_brackets` (
  `id` int(11) NOT NULL auto_increment,
  `tid` int(11) NOT NULL,
  `json` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `lan_brackets`
--

INSERT INTO `lan_brackets` (`id`, `tid`, `json`) VALUES
(2, 1, '{"teams":[["Devon","Toon"],["Jonas","Didi"],["Jos","Tom"],["Jan","Jef"]],"results":[[[[10,3],[5,3],[18,7],[19,4]],[[7,9],[3,8]],[[35,2],[7,2]]]]}'),
(3, 3, '{"teams":[["rtrst","hystfh"],["Joske",""]],"results":[[[[0,0],[0,null]],[[null,null],[null,null]]]]}'),
(4, 5, '{"teams":[["Devon",""],["",""]],"results":[[[[0,null],[null,null]],[[null,null],[null,null]]]]}'),
(6, 4, '{"teams":[["Devon",""],["",""]],"results":[[[[0,null],[null,null]],[[null,null],[null,null]]]]}');

-- --------------------------------------------------------

--
-- Table structure for table `lan_tournaments`
--

DROP TABLE IF EXISTS `lan_tournaments`;
CREATE TABLE IF NOT EXISTS `lan_tournaments` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `lan_tournaments`
--

INSERT INTO `lan_tournaments` (`id`, `name`) VALUES
(1, 'Team Fortress 2'),
(2, 'Unreal Tournament 2004'),
(3, 'Trackmania Nations');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
