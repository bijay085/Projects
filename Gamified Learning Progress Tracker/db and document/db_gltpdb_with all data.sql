-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2024 at 06:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_gltpdb`
--
CREATE DATABASE IF NOT EXISTS `db_gltpdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_gltpdb`;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_badges`
--

DROP TABLE IF EXISTS `tbl_badges`;
CREATE TABLE IF NOT EXISTS `tbl_badges` (
  `badgeid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`badgeid`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_badges`
--

INSERT INTO `tbl_badges` (`badgeid`, `name`, `photo`, `photo_path`, `description`) VALUES
(1, 'Excellence Award', 'excellence.jpg', '/badge/badgephoto/excellence.jpg', 'Awarded for outstanding performance.'),
(2, 'Perfect Attendance', 'attendance.jpg', '/badge/badgephoto/attendance.jpg', 'Awarded for perfect attendance.'),
(3, 'Best Project', 'project.jpg', '/badge/badgephoto/project.jpg', 'Awarded for the best project of the semester.'),
(4, 'Top Scorer', 'top_scorer.jpg', '/badge/badgephoto/top_scorer.jpg', 'It is awarded for achieving the highest score in the last final exam.'),
(5, 'Best Research', 'research.jpg', '/badge/badgephoto/research.jpg', 'Awarded for exceptional research work.'),
(6, 'Leadership Award', 'leadership.jpg', '/badge/badgephoto/leadership.jpg', 'Awarded for excellent leadership skills.'),
(7, 'Community Service', 'community_service.jpg', '/badge/badgephoto/community_service.jpg', 'Awarded for outstanding community service.'),
(8, 'Innovation Award', 'innovation.jpg', '/badge/badgephoto/innovation.jpg', 'Awarded for innovative ideas and solutions.'),
(9, 'Sports Champion', 'sports.jpg', '/badge/badgephoto/sports.jpg', 'Awarded for excellent performance in sports.'),
(10, 'Cultural Excellence', 'cultural.jpg', '/badge/badgephoto/cultural.jpg', 'Awarded for outstanding contribution to cultural activities.'),
(11, 'Punctual', 'e.jpg', '', 'Awarded to those students who always do things on time.'),
(12, 'Pass in last final exam.', 'result.png', '', 'Awarded to those students who passed in last final exam.'),
(13, 'Assignment', 'assignment.jpg', '', 'To those who completed all assignments.');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_overall`
--

DROP TABLE IF EXISTS `tbl_overall`;
CREATE TABLE IF NOT EXISTS `tbl_overall` (
  `oid` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` varchar(10) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `attendance` enum('full','half','none') NOT NULL,
  `assignment` enum('submitted','incomplete','not done') NOT NULL,
  `performance` enum('good','average','bad') NOT NULL,
  `result` enum('pass','fail','not attempted') NOT NULL,
  `description` text DEFAULT NULL,
  `programming_language` varchar(255) DEFAULT NULL,
  `class_activity` decimal(5,2) DEFAULT NULL,
  `performance_rate` decimal(5,2) DEFAULT NULL,
  `behavior` decimal(5,2) DEFAULT NULL,
  `overall` decimal(5,2) DEFAULT NULL,
  `excellence_award` decimal(5,2) DEFAULT 0.00,
  `perfect_attendance` decimal(5,2) DEFAULT 0.00,
  `best_project` decimal(5,2) DEFAULT 0.00,
  `top_scorer` decimal(5,2) DEFAULT 0.00,
  `best_research` decimal(5,2) DEFAULT 0.00,
  `leadership_award` decimal(5,2) DEFAULT 0.00,
  `community_service` decimal(5,2) DEFAULT 0.00,
  `innovation_award` decimal(5,2) DEFAULT 0.00,
  `sports_champion` decimal(5,2) DEFAULT 0.00,
  `cultural_excellence` decimal(5,2) DEFAULT 0.00,
  `punctual` decimal(5,2) DEFAULT 0.00,
  `pass_exam` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`oid`),
  KEY `roleid` (`roleid`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_overall`
--

INSERT INTO `tbl_overall` (`oid`, `roleid`, `fullname`, `attendance`, `assignment`, `performance`, `result`, `description`, `programming_language`, `class_activity`, `performance_rate`, `behavior`, `overall`, `excellence_award`, `perfect_attendance`, `best_project`, `top_scorer`, `best_research`, `leadership_award`, `community_service`, `innovation_award`, `sports_champion`, `cultural_excellence`, `punctual`, `pass_exam`) VALUES
(1, 'S12345', 'John Doe', 'full', 'submitted', 'good', 'pass', 'Excellent performance in all subjects.', 'Python', 95.50, 90.00, 85.00, 90.17, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1.00, 0.00, 0.00, 0.00),
(2, 'S234567', 'Jane Smith', 'half', 'incomplete', 'average', 'fail', 'Needs improvement in completing assignments.', 'Java', 70.00, 65.00, 60.00, 65.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(3, 'S34567', 'Alice Johnson', 'full', 'submitted', 'good', 'pass', 'Consistently high performance.', 'C++', 92.75, 88.00, 83.00, 87.92, 1.00, 1.00, 1.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1.00, 0.00, 1.00, 73.00),
(5, 'S56789', 'Charlie Davis', 'full', 'submitted', 'average', 'pass', 'Satisfactory performance.', 'Ruby', 85.00, 80.00, 75.00, 80.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_roles`
--

DROP TABLE IF EXISTS `tbl_roles`;
CREATE TABLE IF NOT EXISTS `tbl_roles` (
  `roleid` varchar(10) NOT NULL,
  `roletype` enum('student','teacher') NOT NULL,
  PRIMARY KEY (`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_roles`
--

INSERT INTO `tbl_roles` (`roleid`, `roletype`) VALUES
('S12345', 'student'),
('S123456', 'student'),
('S234567', 'student'),
('S34567', 'student'),
('S45678', 'student'),
('S56789', 'student'),
('T12345', 'teacher'),
('T23456', 'teacher'),
('T34567', 'teacher'),
('T45678', 'teacher'),
('T56789', 'teacher');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tworks`
--

DROP TABLE IF EXISTS `tbl_tworks`;
CREATE TABLE IF NOT EXISTS `tbl_tworks` (
  `work_id` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` varchar(10) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `post_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `program` varchar(50) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  PRIMARY KEY (`work_id`),
  KEY `roleid` (`roleid`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_tworks`
--

INSERT INTO `tbl_tworks` (`work_id`, `roleid`, `title`, `description`, `due_date`, `post_date`, `program`, `semester`) VALUES
(1, 'T12345', 'Assignment 1', 'Introduction to Algorithms', '2024-08-01', '2024-07-19 18:15:00', 'Computer Science', 1),
(2, 'T23456', 'Project 1', 'Database Design Project', '2024-08-15', '2024-07-19 18:15:00', 'Information Systems', 2),
(3, 'T34567', 'Lab 1', 'Network Configuration Lab', '2024-08-10', '2024-07-19 18:15:00', 'Computer Networks', 3),
(4, 'T45678', 'Quiz 1', 'Basic Chemistry Quiz', '2024-08-05', '2024-07-19 18:15:00', 'Chemistry', 1),
(5, 'T56789', 'Research Paper', 'Advanced Machine Learning Techniques', '2024-09-01', '2024-07-19 18:15:00', 'Artificial Intelligence', 4);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` varchar(10) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `program` varchar(50) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `badgeid` int(11) DEFAULT NULL,
  PRIMARY KEY (`userid`),
  KEY `roleid` (`roleid`),
  KEY `fk_users_badges` (`badgeid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`userid`, `roleid`, `username`, `password`, `fullname`, `dob`, `gender`, `address`, `phone`, `program`, `semester`, `admission_date`, `photo`, `badgeid`) VALUES
(1, 'S12345', 'student1', '$2y$10$fZJaohcWlAmB913eHu2U5O730/GxNIoL3450xxilHvEapI.G7k/Qe', 'John Doe', '2000-01-01', 'male', '123 Main St', '555-1234', 'BCA', 1, '2019-09-01', 'photo1.webp', NULL),
(2, 'S234567', 'student2', '$2y$10$hibyQiLAOVuJUlNtDkAbYuWCIkzRY8quNnTwYz1xUrB6.gwOCFLai', 'Jane Smith', '2001-02-02', 'female', '456 Oak Ave', '555-5678', 'BCA', 2, '2020-01-15', 'photo2.webp\r\n', NULL),
(3, 'S34567', 'student3', '$2y$10$ZmS8/wwaH3bRRROMFskUBeRjUYGqqdX2tpL.ya63uMyT5C1hqMVWS', 'Alice Johnson', '2002-03-03', 'female', '789 Pine Rd', '555-9101', 'BCA', 3, '2020-08-21', 'photo3.webp', NULL),
(4, 'S45678', 'student4', '$2y$10$VN0Uwrl/G/cSqmrC8ACfEe6IUwMGfBQ/yt.waSOy.YO77XUJfKe4i', 'Bob Brown', '2001-04-04', 'male', '321 Maple St', '555-1122', 'BCA', 4, '2019-09-01', 'photo4.webp', NULL),
(5, 'S56789', 'student5', '$2y$10$dKyCsvUC8VYa85J4TaGkdeY9Bj0oycszyz3YaOsgHHTxfICt2nHb6', 'Charlie Davis', '2000-05-05', 'other', '654 Elm Ave', '555-3344', 'BCA', 5, '2019-09-01', 'photo5.webp', NULL),
(6, 'T12345', 'teacher1', '$2y$10$Yu88M.jaaSO.Mi5jltaTTuzVKifpMQWUnBKdYUXw/VpzgmWr.iV4e', 'Dr. Eva Green', '1980-06-06', 'female', '987 Birch Rd', '555-5566', NULL, NULL, NULL, 'photo6.webp', NULL),
(7, 'T23456', 'teacher2', '$2y$10$wOrfygjYF3EruLSTzmd4j.tk6XOhChsnkmXiYK5uIE0b7eC029AC.', 'Mr. Frank White', '1975-07-07', 'male', '147 Cedar St', '555-7788', NULL, NULL, NULL, 'photo7.webp', NULL),
(8, 'T34567', 'teacher3', '$2y$10$TRvwiD2p3NhM3GxFWmzyBOZUl1ZsU.OFbHSs7yQiGU0ouXsfJVV2u', 'Prof. Grace Black', '1985-08-08', 'female', '258 Aspen Ave', '555-9900', NULL, NULL, NULL, 'photo8.webp', NULL),
(9, 'T45678', 'teacher4', '$2y$10$Il50kkTwXUqrQW57dp.qP..N3QOcNqeu71A94/cU9S3KvpDiRXsPm', 'Dr. Henry Blue', '1970-09-09', 'male', '369 Spruce Rd', '555-2233', NULL, NULL, NULL, 'photo9.webp', NULL),
(10, 'T56789', 'teacher5', '$2y$10$ttAwSGo3Ve7MWk8Vb42S2en9j.kROdazwWxaazjUOw1ngS62o5Y7W', 'Ms. Ivy Red', '1982-10-10', 'female', '741 Fir St', '555-4455', NULL, NULL, NULL, 'photo10.webp', NULL),
(11, 'S123456', 'bijay', '$2y$10$sTSMQyDaENVixu.JK00STORD3tm13DwGmrNCnQI8GqaYCsCxTdXWe', 'Bijay Koirala ', '2000-07-01', 'male', 'Kathmandu bkt', '9746665541', 'BCA', 5, '2021-07-06', 'bijay.webp', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_badges`
--

DROP TABLE IF EXISTS `tbl_user_badges`;
CREATE TABLE IF NOT EXISTS `tbl_user_badges` (
  `userid` int(11) NOT NULL,
  `badgeid` int(11) NOT NULL,
  PRIMARY KEY (`userid`,`badgeid`),
  KEY `badgeid` (`badgeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user_badges`
--

INSERT INTO `tbl_user_badges` (`userid`, `badgeid`) VALUES
(4, 13),
(11, 2),
(11, 6),
(11, 7),
(11, 8),
(11, 9),
(11, 13);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_overall`
--
ALTER TABLE `tbl_overall`
  ADD CONSTRAINT `tbl_overall_ibfk_1` FOREIGN KEY (`roleid`) REFERENCES `tbl_roles` (`roleid`);

--
-- Constraints for table `tbl_tworks`
--
ALTER TABLE `tbl_tworks`
  ADD CONSTRAINT `tbl_tworks_ibfk_1` FOREIGN KEY (`roleid`) REFERENCES `tbl_roles` (`roleid`);

--
-- Constraints for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD CONSTRAINT `fk_badgeid` FOREIGN KEY (`badgeid`) REFERENCES `tbl_badges` (`badgeid`),
  ADD CONSTRAINT `fk_users_badgeid` FOREIGN KEY (`badgeid`) REFERENCES `tbl_badges` (`badgeid`),
  ADD CONSTRAINT `fk_users_badges` FOREIGN KEY (`badgeid`) REFERENCES `tbl_badges` (`badgeid`),
  ADD CONSTRAINT `tbl_users_ibfk_1` FOREIGN KEY (`roleid`) REFERENCES `tbl_roles` (`roleid`);

--
-- Constraints for table `tbl_user_badges`
--
ALTER TABLE `tbl_user_badges`
  ADD CONSTRAINT `tbl_user_badges_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `tbl_users` (`userid`),
  ADD CONSTRAINT `tbl_user_badges_ibfk_2` FOREIGN KEY (`badgeid`) REFERENCES `tbl_badges` (`badgeid`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
