-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2025 at 08:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sbm`
--

-- --------------------------------------------------------

--
-- Table structure for table `deputy_registrar_complaints`
--

CREATE TABLE `deputy_registrar_complaints` (
  `reference_number` varchar(20) NOT NULL,
  `complaint_details` text NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_chair_complaints`
--

CREATE TABLE `program_chair_complaints` (
  `reference_number` varchar(20) NOT NULL,
  `complaint_details` text NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_chair_complaints`
--

INSERT INTO `program_chair_complaints` (`reference_number`, `complaint_details`, `status`, `submitted_at`) VALUES
('SBM1743780979210', 'qwertyui', 'Resolved', '2025-04-04 17:36:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `deputy_registrar_complaints`
--
ALTER TABLE `deputy_registrar_complaints`
  ADD PRIMARY KEY (`reference_number`);

--
-- Indexes for table `program_chair_complaints`
--
ALTER TABLE `program_chair_complaints`
  ADD PRIMARY KEY (`reference_number`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
