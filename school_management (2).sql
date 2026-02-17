-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2026 at 06:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `publish_date` date DEFAULT curdate(),
  `target_role` enum('all','student','teacher') DEFAULT 'all'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `date`, `status`) VALUES
(3, 21, '2026-02-10', 'Present'),
(4, 23, '2026-02-10', 'Present'),
(5, 21, '2026-02-11', 'Present'),
(6, 23, '2026-02-11', 'Present'),
(7, 21, '2026-02-09', 'Present'),
(8, 23, '2026-02-09', 'Present'),
(10, 26, '2026-02-10', 'Present'),
(11, 25, '2026-02-10', 'Present'),
(15, 26, '2026-02-13', 'Present'),
(16, 25, '2026-02-13', 'Present'),
(17, 28, '2026-02-13', 'Present'),
(18, 30, '2026-02-16', 'Present'),
(19, 21, '2026-02-16', 'Present'),
(20, 23, '2026-02-16', 'Present'),
(21, 31, '2026-02-16', 'Present'),
(22, 30, '2026-02-07', 'Present'),
(23, 21, '2026-02-07', 'Present'),
(24, 23, '2026-02-07', 'Present'),
(25, 31, '2026-02-07', 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `class_master_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`, `class_master_id`, `deleted_at`, `deleted_by`) VALUES
(1, 'Play Group', NULL, NULL, NULL),
(2, '1st', NULL, NULL, NULL),
(3, '2nd', 14, NULL, NULL),
(4, '3rd', NULL, NULL, NULL),
(5, '4th', NULL, NULL, NULL),
(15, '8th', NULL, NULL, NULL),
(16, '5th', 13, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `book_name` varchar(255) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_subjects`
--

INSERT INTO `class_subjects` (`class_id`, `subject_id`, `book_name`, `deleted_at`, `deleted_by`) VALUES
(15, 6, 'Elementary Physics', NULL, NULL),
(15, 8, 'Functional Maths', NULL, NULL),
(15, 9, 'Functional English', NULL, NULL),
(15, 10, 'Pak Studies', NULL, NULL),
(16, 2, 'CS Major', '2026-02-11 14:41:10', 1),
(16, 8, 'Maths Oxford', NULL, NULL),
(16, 9, 'Functional English', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `exam_name` varchar(100) NOT NULL,
  `exam_type` enum('Monthly','Midterm','Final','Quiz') NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `total_marks` int(11) DEFAULT 100,
  `passing_marks` int(11) DEFAULT 40,
  `academic_year` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_invoices`
--

CREATE TABLE `fee_invoices` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Unpaid','Partially Paid','Paid','Overdue') DEFAULT 'Unpaid',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('Cash','Bank Transfer') NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Verified','Rejected') DEFAULT 'Pending',
  `payment_date` datetime DEFAULT current_timestamp(),
  `collected_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `term` varchar(50) NOT NULL,
  `exam_id` int(11) DEFAULT NULL
) ;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject_id`, `score`, `term`, `exam_id`) VALUES
(3, 21, 9, 69, 'mid', NULL),
(4, 23, 9, 44, 'mid', NULL),
(5, 21, 8, 89, 'mid', NULL),
(6, 23, 8, 89, 'mid', NULL),
(8, 26, 8, 70, 'mid', NULL),
(9, 25, 8, 80, 'mid', NULL),
(11, 26, 9, 89, 'mid', NULL),
(12, 25, 9, 21, 'mid', NULL),
(13, 28, 9, 89, 'mid', NULL),
(15, 26, 2, 89, 'mid', NULL),
(16, 25, 2, 73, 'mid', NULL),
(17, 28, 2, 67, 'mid', NULL),
(18, 28, 8, 88, 'mid', NULL),
(19, 21, 6, 79, 'mid', NULL),
(20, 23, 6, 67, 'mid', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `id` int(11) NOT NULL,
  `guardian_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `relationship_to_student` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guardians`
--

INSERT INTO `guardians` (`id`, `guardian_name`, `contact_number`, `email`, `address`, `relationship_to_student`) VALUES
(14, 'Ahmad Ali', '0217361412', 'a23128@gmail.com', 'Near Session Court', 'Father'),
(15, 'Jeffery epstein', '0000000000', 'epstein69@gmail.com', 'Epstein Island, united states of america', 'Father'),
(16, 'Muhammad Ali', '1308137', 'a23128@gmail.com', 'dfadfa', 'father'),
(17, 'Ali Khan', '033367672', 'alikhan@gmail.com', 'kohat', 'Father'),
(18, 'Javed Iqbal', '0314 7827231', 'javed@gmail.com', 'kohat', 'Father'),
(19, 'Ahmad ALi Khan', '03717314812', 'alikhan33@gmail.com', 'karak', 'Father'),
(20, 'Mr. Imran Khan', '0333 1628482', 'imrankhan@gmail.com', 'Karak', 'Father'),
(21, 'Mr Imran Khan', '0332 7788331', 'imrankhan@gmail.com', 'Karak', 'Father'),
(22, 'Tahir Khan', '0331 7733114', 'tahirkhan@gmail.com', 'karak', 'Father'),
(23, 'Ahmad Ali', '0331 7379231', 'ahmad@gmail.com', 'karka', 'Father'),
(24, 'Sohaib Khan', '0312 7283281', 'sohaib@gmail.com', 'karak', 'Father'),
(25, 'Shahzaib Khan', '0334 8967482', 'khan@gmail.com', 'Karak', 'Father');

-- --------------------------------------------------------

--
-- Table structure for table `homework`
--

CREATE TABLE `homework` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_payments`
--

CREATE TABLE `salary_payments` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `bonus_or_deduction` decimal(10,2) DEFAULT 0.00,
  `payment_date` date NOT NULL,
  `payment_month` varchar(20) NOT NULL,
  `status` enum('Paid','Partial','Pending') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_expenses`
--

CREATE TABLE `school_expenses` (
  `id` int(11) NOT NULL,
  `expense_category` enum('Utilities','Rent','Maintenance','Stationery','Other') NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `admission_date` date NOT NULL,
  `dob` date DEFAULT NULL,
  `guardian_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(20) NOT NULL,
  `class_id` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `guardian_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `full_name`, `admission_date`, `dob`, `guardian_name`, `contact_number`, `email`, `class_id`, `status`, `guardian_id`, `deleted_at`, `deleted_by`) VALUES
(21, 'Hassan Khan', '2026-02-23', NULL, '', '03044904907', 'abc@gmail.com', 15, 'active', 14, NULL, NULL),
(23, 'rafy ahmad', '2026-12-02', NULL, '', '11111', 'azwalker537@gmail.co', 15, 'active', 16, NULL, NULL),
(25, 'Mudassir Javed', '2026-12-23', NULL, '', '0334 4413412', 'mudassir@gmail.com', 16, 'active', 18, NULL, NULL),
(26, 'Amal Ahmad', '2026-02-23', NULL, '', '032111122', 'amal@gmail.com', 16, 'active', 19, NULL, NULL),
(27, 'Ahmad Khan', '2026-02-11', '2008-03-12', '', '0333 67678271', 'ahmadkhan@gmail.com', 2, 'active', 21, NULL, NULL),
(28, 'Naafy Khan', '2026-02-23', '2008-06-12', '', '0331 6721641', 'naafykhan@gmail.com', 16, 'active', 22, NULL, NULL),
(29, 'Sahaf Ahmad', '2026-12-22', '2006-12-04', '', '0333 7867321', 'sahaf@gmail.com', 15, 'inactive', 23, '2026-02-13 14:51:28', 1),
(30, 'Anas Khan', '2026-02-15', '2002-03-12', '', '0331 8373231', 'anaskhan@gmail.com', 15, 'active', 24, NULL, NULL),
(31, 'Sahaf Khan', '2026-02-16', '2008-02-20', '', '0334 8922984', 'sahafkhan@gmail.com', 15, 'active', 25, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `deleted_at`, `deleted_by`) VALUES
(2, 'Computer science', NULL, NULL),
(4, 'Chemistry', NULL, NULL),
(5, 'Chapraasi', NULL, NULL),
(6, 'Physics', NULL, NULL),
(7, 'Islamiyat', NULL, NULL),
(8, 'Maths', NULL, NULL),
(9, 'English', NULL, NULL),
(10, 'Social Studies', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `remaining_payment` decimal(10,2) DEFAULT 0.00,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `name`, `father_name`, `salary`, `phone`, `email`, `remaining_payment`, `deleted_at`, `deleted_by`) VALUES
(13, 'Qadeem Khan', 'Khan Wali', 16000.00, '171619471', 'qadeem@gmail.com', 0.00, NULL, 1),
(14, 'Taimoor Khan', 'Umair Iqbal', 16000.00, '331', 'taimoorkhan@gmail.com', 0.00, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_assignments`
--

INSERT INTO `teacher_assignments` (`id`, `teacher_id`, `subject_id`, `class_id`, `academic_year`) VALUES
(4, 13, 2, 16, '2027'),
(11, 13, 8, 15, '2027'),
(12, 14, 9, 15, '2027'),
(13, 13, 8, 16, '2027');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_attendance`
--

CREATE TABLE `teacher_attendance` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late','Leave') NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subjects`
--

CREATE TABLE `teacher_subjects` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subjects`
--

INSERT INTO `teacher_subjects` (`id`, `teacher_id`, `subject_id`) VALUES
(9, 13, 2),
(10, 13, 8);

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `related_id`, `created_at`, `failed_attempts`, `locked_until`, `last_login`, `is_active`) VALUES
(1, 'admin', '$2y$10$YS/H5HcY6WFj9bj1ddPWveOuSJiHM9kHT71..Wk3Bf5v8.GhmITJ.', 'admin', NULL, '2026-01-28 13:35:18', 0, NULL, '2026-02-16 09:52:17', 1),
(2, 'aarizkhan_33', '$2y$10$MivaMEKrAX6XLMoFWrVnxOR1LEZt4s2YxVMOL4uTMW/8NtyfuVZMu', 'teacher', 9, '2026-01-28 15:39:05', 0, NULL, NULL, 1),
(3, 'student1', '$2y$10$hmZEpovmEcFfizY0SQpZp.aLJ00SCtkdTV9oiJP6XgdqQeEtut8kK', 'student', 10, '2026-02-01 05:52:41', 0, NULL, NULL, 1),
(5, 'Aariz', '$2y$10$Ac9GiQK2/EkQM.rt2Vu3eubHTvN/Fh/puFzhzzfbCruPfX2o8ukEW', 'teacher', 9, '2026-02-02 05:56:23', 0, NULL, NULL, 1),
(6, 'student', '$2y$10$IYmGtXPE0t/XxOpGboYaFeLsKykXQWUqYFvtQj074kJ2Ete7xtQ4.', 'student', 17, '2026-02-05 11:03:39', 0, NULL, NULL, 1),
(8, 'Faculty_13', '$2y$10$VGpSUkBeW1bSZn9KFZr9vuiLkd7UIR7F75aXVx28cEpc4mCnRQCfK', 'teacher', 13, '2026-02-10 17:48:01', 0, NULL, '2026-02-16 09:13:07', 1),
(9, 'Hassan Khan', '$2y$10$Mebvb/hw4jiv1aAqU8wU/.PxW27RhZzbBQRpCeD.LOYova.T84Dgi', 'student', 21, '2026-02-11 10:47:46', 0, NULL, '2026-02-16 09:54:21', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `idx_student_date` (`student_id`,`date`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `id_2` (`id`),
  ADD KEY `fk_class_master` (`class_master_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`class_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reference_no` (`reference_no`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `fk_grade_exam` (`exam_id`),
  ADD KEY `idx_student_term` (`student_id`,`term`),
  ADD KEY `idx_subject` (`subject_id`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `homework`
--
ALTER TABLE `homework`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary_payments`
--
ALTER TABLE `salary_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `school_expenses`
--
ALTER TABLE `school_expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_student_email` (`email`),
  ADD KEY `class-id` (`class_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `id` (`id`),
  ADD KEY `id_2` (`id`),
  ADD KEY `full_name` (`full_name`),
  ADD KEY `fk_student_guardian` (`guardian_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_teacher_email` (`email`),
  ADD UNIQUE KEY `unique_teacher_phone` (`phone`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_class_subject` (`class_id`,`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`,`subject_id`,`class_id`),
  ADD KEY `subject_teacher_relation` (`subject_id`);

--
-- Indexes for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `homework`
--
ALTER TABLE `homework`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_payments`
--
ALTER TABLE `salary_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_expenses`
--
ALTER TABLE `school_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `fk_class_master` FOREIGN KEY (`class_master_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD CONSTRAINT `fee_invoices_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_grade_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_grade_relation` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_grade_relation` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `homework`
--
ALTER TABLE `homework`
  ADD CONSTRAINT `homework_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `homework_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_payments`
--
ALTER TABLE `salary_payments`
  ADD CONSTRAINT `salary_payments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_student_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_student_guardian` FOREIGN KEY (`guardian_id`) REFERENCES `guardians` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD CONSTRAINT `subject_teacher_relation` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_assignment_relation` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_class_relation` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD CONSTRAINT `teacher_attendance_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  ADD CONSTRAINT `teacher_subjects_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `teacher_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `timetable`
--
ALTER TABLE `timetable`
  ADD CONSTRAINT `timetable_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetable_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
