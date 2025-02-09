-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 09, 2025 at 07:21 AM
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
-- Database: `workshop_work_io`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_emp`
--

CREATE TABLE `tbl_emp` (
  `m_id` char(5) NOT NULL,
  `m_username` varchar(50) NOT NULL,
  `m_password` varchar(50) NOT NULL,
  `m_firstname` varchar(50) NOT NULL,
  `m_name` varchar(100) NOT NULL,
  `m_lastname` varchar(100) NOT NULL,
  `m_position` varchar(100) NOT NULL,
  `m_img` varchar(100) DEFAULT NULL,
  `m_phone` varchar(20) NOT NULL,
  `m_email` varchar(50) NOT NULL,
  `m_level` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_emp`
--

INSERT INTO `tbl_emp` (`m_id`, `m_username`, `m_password`, `m_firstname`, `m_name`, `m_lastname`, `m_position`, `m_img`, `m_phone`, `m_email`, `m_level`) VALUES
('00001', '111', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2', 'นาย', 'ทดสอบ', 'ระบบ', 'โปรแกรมเมอร์', 'uploads/675048dd941b8.png', '0948616709', 'devbanban@gmail.com', 'staff'),
('00002', '222', '1c6637a8f2e1f75e06ff9984894d6bd16a3a36a9', 'นาย', 'พนง.', 'ในบริษัท', 'โปรแกรมเมอร์', 'e2.png', '0948616709', 'devbanban@gmail.com', 'staff'),
('00003', '333', '43814346e21444aaf4f70841bf7ed5ae93f55a9d', 'นางสาว', 'frontend', 'dd', 'frontend', 'e3.png', '0948616709', 'devbanban@gmail.com', 'staff'),
('00004', '444', '9a3e61b6bcc8abec08f195526c3132d5a4a98cc0', 'นาย', 'admin', 'naja', 'admin', 'e1.png', '0948616709', 'devbanban@gmail.com', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_work_io`
--

CREATE TABLE `tbl_work_io` (
  `id` int(11) NOT NULL,
  `m_id` char(5) DEFAULT NULL,
  `workdate` date NOT NULL,
  `workin` time NOT NULL,
  `workout` time DEFAULT NULL,
  `checkin_reason` text DEFAULT NULL,
  `checkout_reason` text DEFAULT NULL,
  `checkin_distance` decimal(10,2) DEFAULT NULL,
  `checkout_distance` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_work_io`
--

INSERT INTO `tbl_work_io` (`id`, `m_id`, `workdate`, `workin`, `workout`, `checkin_reason`, `checkout_reason`, `checkin_distance`, `checkout_distance`) VALUES
(1, '00001', '2024-12-04', '16:17:00', '00:00:00', NULL, NULL, NULL, NULL),
(2, '00002', '2024-12-04', '19:50:14', NULL, '555', NULL, 84775.73, NULL),
(3, '00004', '2024-12-04', '23:57:17', NULL, '555', NULL, 84790.47, NULL),
(4, '00004', '2024-12-05', '00:01:25', NULL, '555', NULL, 84785.03, NULL),
(5, '00001', '2024-12-05', '01:02:41', '18:31:47', '555', '666', 84787.58, 81401.62),
(6, '00002', '2024-12-15', '23:54:57', NULL, 'เเงง', NULL, 81401.62, NULL),
(7, '00004', '2024-12-15', '23:55:28', NULL, '666', NULL, 81401.62, NULL),
(8, '00001', '2024-12-21', '14:34:00', '16:52:00', '555', '', 81401.62, NULL),
(9, '00004', '2024-12-26', '19:45:06', NULL, 'เเงง', NULL, 85920.36, NULL),
(10, '00004', '2025-02-08', '15:01:22', NULL, '555', NULL, 84792.10, NULL),
(11, '00004', '2025-02-09', '11:39:40', NULL, 'เเงง', NULL, 81401.62, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_emp`
--
ALTER TABLE `tbl_emp`
  ADD PRIMARY KEY (`m_id`);

--
-- Indexes for table `tbl_work_io`
--
ALTER TABLE `tbl_work_io`
  ADD PRIMARY KEY (`id`),
  ADD KEY `m_id` (`m_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_work_io`
--
ALTER TABLE `tbl_work_io`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_work_io`
--
ALTER TABLE `tbl_work_io`
  ADD CONSTRAINT `tbl_work_io_ibfk_1` FOREIGN KEY (`m_id`) REFERENCES `tbl_emp` (`m_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
