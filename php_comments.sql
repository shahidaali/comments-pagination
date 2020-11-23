-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 23, 2020 at 07:44 AM
-- Server version: 5.7.24
-- PHP Version: 7.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `php_comments`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT '0',
  `comment` text,
  `post_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `parent_id`, `comment`, `post_id`, `created_at`) VALUES
(1, 0, 'parent 1', 1, '2020-11-22 16:53:12'),
(2, 1, 'child 1', 1, '2020-11-22 16:53:12'),
(3, 2, 'subchild 1', 1, '2020-11-22 16:53:12'),
(4, 0, 'Second comment', 1, '2020-11-22 16:56:27'),
(5, 4, 'Second comment child', 1, '2020-11-22 16:58:36'),
(6, 4, 'Second comment child 2', 1, '2020-11-22 16:58:36'),
(7, 4, 'Second comment child 3', 1, '2020-11-22 16:58:36'),
(8, 5, 'Second comment sub child 1', 1, '2020-11-22 16:58:36'),
(9, 5, 'Second comment sub child 2', 1, '2020-11-22 16:58:36'),
(10, 5, 'Second comment sub child 3', 1, '2020-11-22 16:58:36'),
(11, 8, 'Second comment level 3 1', 1, '2020-11-22 17:01:38'),
(12, 8, 'Second comment level 3 2', 1, '2020-11-22 17:01:38'),
(13, 0, 'Third comment', 1, '2020-11-22 17:02:11');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
