-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 28, 2017 at 08:22 PM
-- Server version: 5.6.33
-- PHP Version: 5.6.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: ``
--

-- --------------------------------------------------------

--
-- Table structure for table `trader_rates`
--

CREATE TABLE `trader_rates` (
  `id` int(11) NOT NULL,
  `code` varchar(8) NOT NULL,
  `description` varchar(32) NOT NULL,
  `rate` float NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `trader_rates`
--

INSERT INTO `trader_rates` (`id`, `code`, `description`, `rate`, `timestamp`) VALUES
(1, 'ZAR', 'South African Rands', 13.3054, '2017-01-01 00:00:00'),
(2, 'GBP', 'British Pound', 0.651178, '2017-01-01 00:00:00'),
(3, 'EUR', 'Euro', 0.884872, '2017-01-01 00:00:00'),
(4, 'KES', 'Kenyan Shilling', 103.86, '2017-01-01 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `trader_rates`
--
ALTER TABLE `trader_rates`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `trader_rates`
--
ALTER TABLE `trader_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
