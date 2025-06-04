-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2025 at 08:54 AM
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
-- Database: hostel_rector
--

-- --------------------------------------------------------

--
-- Table structure for table hostel_rector_complaints
--

CREATE TABLE hostel_rector_complaints (
  reference_number varchar(20) NOT NULL,
  complaint_details text NOT NULL,
  status varchar(50) DEFAULT 'Pending',
  submitted_at datetime DEFAULT current_timestamp(),
  comment text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table hostel_rector_complaints
--

INSERT INTO hostel_rector_complaints (reference_number, complaint_details, status, submitted_at, comment) VALUES
('HRM1743688521181', 'Sample complaint', 'Pending', '2025-04-03 15:55:21', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table hostel_rector_complaints
--
ALTER TABLE hostel_rector_complaints
  ADD PRIMARY KEY (reference_number);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Comments:
-- This database is specifically designed for managing hostel-related complaints
-- The reference_number format is HRM (Hostel Rector Management) followed by timestamp
-- Status can be: Pending, In Progress, Resolved, or Rejected
-- submitted_at automatically records the timestamp when a complaint is submitted 