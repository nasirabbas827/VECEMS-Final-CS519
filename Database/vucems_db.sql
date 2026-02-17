-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2026 at 08:22 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vucems_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `event_type` enum('physical','virtual') NOT NULL DEFAULT 'physical',
  `venue` varchar(255) DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `capacity` int(11) DEFAULT 0,
  `registration_deadline` datetime DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled','completed') DEFAULT 'pending',
  `banner_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `description`, `organizer_id`, `category_id`, `event_type`, `venue`, `meeting_link`, `start_datetime`, `end_datetime`, `capacity`, `registration_deadline`, `status`, `banner_image`, `created_at`, `updated_at`) VALUES
(1, 'Art Exhibition: Modern Art', 'Explore contemporary artworks from local artists.', 1, 1, 'physical', 'City Art Gallery', NULL, '2026-02-18 12:04:58', '2026-02-18 14:04:58', 100, '2026-02-17 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(2, 'Charity Run 2026', 'Join us for a 5k charity run to support education.', 1, 2, 'physical', 'Central Park', NULL, '2026-02-19 12:04:58', '2026-02-19 14:04:58', 200, '2026-02-18 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(3, 'Community Clean-Up', 'Volunteer to clean up the neighborhood park.', 1, 3, 'physical', 'Neighborhood Park', NULL, '2026-02-20 12:04:58', '2026-02-20 14:04:58', 50, '2026-02-19 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(4, 'Spring Festival 2026', 'Enjoy local food, music, and games.', 1, 4, 'physical', 'Town Square', NULL, '2026-02-21 12:04:58', '2026-02-21 16:04:58', 300, '2026-02-20 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(5, 'Summer Music Concert', 'Live performance by popular bands.', 1, 5, 'physical', 'City Stadium', NULL, '2026-02-22 12:04:58', '2026-02-22 15:04:58', 500, '2026-02-21 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(6, 'Tech Networking Meetup', 'Connect with tech professionals.', 1, 6, 'physical', 'Innovation Hub', NULL, '2026-02-23 12:04:58', '2026-02-23 14:04:58', 150, '2026-02-22 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(7, 'AI Conference 2026', 'Attend talks on AI and machine learning.', 1, 7, 'virtual', NULL, 'https://meet.link/ai2026', '2026-02-24 12:04:58', '2026-02-24 16:04:58', 300, '2026-02-23 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(8, 'City Marathon', 'Run with the community and stay fit.', 1, 8, 'physical', 'Main Street', NULL, '2026-02-25 12:04:58', '2026-02-25 15:04:58', 400, '2026-02-24 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(9, 'Tech Innovators Meetup', 'Discuss latest tech trends.', 1, 9, 'virtual', NULL, 'https://meet.link/techinnovators', '2026-02-26 12:04:58', '2026-02-26 14:04:58', 200, '2026-02-25 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(10, 'Photography Workshop', 'Learn photography tips and tricks.', 1, 10, 'physical', 'Photo Studio', NULL, '2026-02-27 12:04:58', '2026-02-27 15:04:58', 50, '2026-02-26 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(11, 'Art Exhibition: Classic Masters', 'Explore classical artworks.', 1, 1, 'physical', 'City Museum', NULL, '2026-02-28 12:04:58', '2026-02-28 14:04:58', 100, '2026-02-27 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(12, 'Charity Gala Night', 'Fundraising event for community projects.', 1, 2, 'physical', 'Grand Hall', NULL, '2026-03-01 12:04:58', '2026-03-01 15:04:58', 150, '2026-02-28 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(13, 'Neighborhood Volunteering', 'Help seniors in your community.', 1, 3, 'physical', 'Community Center', NULL, '2026-03-02 12:04:58', '2026-03-02 14:04:58', 50, '2026-03-01 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(14, 'Autumn Festival', 'Local food, music, and games.', 1, 4, 'physical', 'Town Square', NULL, '2026-03-03 12:04:58', '2026-03-03 16:04:58', 300, '2026-03-02 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(15, 'Jazz Night Concert', 'Live jazz performances.', 1, 5, 'physical', 'Music Hall', NULL, '2026-03-04 12:04:58', '2026-03-04 15:04:58', 200, '2026-03-03 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(16, 'Startup Networking', 'Meet new startups and founders.', 1, 6, 'physical', 'Co-working Space', NULL, '2026-03-05 12:04:58', '2026-03-05 14:04:58', 150, '2026-03-04 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(17, 'Cloud Computing Seminar', 'Learn about cloud technologies.', 1, 7, 'virtual', NULL, 'https://meet.link/cloudseminar', '2026-03-06 12:04:58', '2026-03-06 16:04:58', 200, '2026-03-05 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(18, 'Basketball Tournament', 'Compete in local basketball games.', 1, 8, 'physical', 'City Sports Arena', NULL, '2026-03-07 12:04:58', '2026-03-07 15:04:58', 100, '2026-03-06 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(19, 'AI Tech Meetup', 'Discuss AI innovations.', 1, 9, 'virtual', NULL, 'https://meet.link/aitech', '2026-03-08 12:04:58', '2026-03-08 14:04:58', 150, '2026-03-07 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(20, 'Painting Workshop', 'Hands-on painting experience.', 1, 10, 'physical', 'Art Studio', NULL, '2026-03-09 12:04:58', '2026-03-09 15:04:58', 30, '2026-03-08 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(21, 'Art: Sculpture Exhibit', 'Exhibition of sculptures.', 1, 1, 'physical', 'City Sculpture Park', NULL, '2026-03-10 12:04:58', '2026-03-10 14:04:58', 80, '2026-03-09 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(22, 'Charity Bake Sale', 'Bake and sell to support charity.', 1, 2, 'physical', 'Community Hall', NULL, '2026-03-11 12:04:58', '2026-03-11 14:04:58', 50, '2026-03-10 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(23, 'Park Volunteer Day', 'Plant trees and clean parks.', 1, 3, 'physical', 'City Park', NULL, '2026-03-12 12:04:58', '2026-03-12 14:04:58', 60, '2026-03-11 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(24, 'Winter Festival', 'Seasonal festivities and food.', 1, 4, 'physical', 'Town Hall', NULL, '2026-03-13 12:04:58', '2026-03-13 16:04:58', 300, '2026-03-12 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(25, 'Rock Concert', 'Live rock music event.', 1, 5, 'physical', 'City Arena', NULL, '2026-03-14 12:04:58', '2026-03-14 15:04:58', 400, '2026-03-13 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(26, 'Professional Networking', 'Meet professionals in your field.', 1, 6, 'physical', 'Conference Hall', NULL, '2026-03-15 12:04:58', '2026-03-15 14:04:58', 150, '2026-03-14 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(27, 'Data Science Conference', 'Learn data science trends.', 1, 7, 'virtual', NULL, 'https://meet.link/dsconference', '2026-03-16 12:04:58', '2026-03-16 16:04:58', 300, '2026-03-15 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(28, 'Soccer Championship', 'Compete in local soccer matches.', 1, 8, 'physical', 'Sports Field', NULL, '2026-03-17 12:04:58', '2026-03-17 15:04:58', 120, '2026-03-16 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(29, 'Tech Startup Meetup', 'Connect with innovative startups.', 1, 9, 'virtual', NULL, 'https://meet.link/startupmeetup', '2026-03-18 12:04:58', '2026-03-18 14:04:58', 150, '2026-03-17 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58'),
(30, 'Creative Writing Workshop', 'Improve your writing skills.', 1, 10, 'physical', 'Community Library', NULL, '2026-03-19 12:04:58', '2026-03-19 15:04:58', 40, '2026-03-18 12:04:58', 'approved', '1771307937_699403a16e2f1.jpg', '2026-02-17 12:04:58', '2026-02-17 12:04:58');

-- --------------------------------------------------------

--
-- Table structure for table `event_categories`
--

CREATE TABLE `event_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_categories`
--

INSERT INTO `event_categories` (`id`, `name`) VALUES
(1, 'Art Exhibitions'),
(2, 'Charity Events'),
(3, 'Community Service'),
(4, 'Festivals'),
(5, 'Music Concerts'),
(6, 'Networking Events'),
(7, 'Seminars & Conferences'),
(8, 'Sports Events'),
(9, 'Tech Meetups'),
(10, 'Workshops');

-- --------------------------------------------------------

--
-- Table structure for table `event_feedback`
--

CREATE TABLE `event_feedback` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `feedback_text` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_feedback`
--

INSERT INTO `event_feedback` (`id`, `event_id`, `user_id`, `rating`, `feedback_text`, `submitted_at`) VALUES
(1, 2, 2, 2, 'fdsaf', '2026-02-17 06:31:08');

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','waitlisted','cancelled','attended','no_show') DEFAULT 'pending',
  `cancellation_reason` text DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `event_id`, `user_id`, `status`, `cancellation_reason`, `registered_at`) VALUES
(1, 2, 2, 'confirmed', 'FDSAfd', '2026-01-07 06:56:19'),
(2, 22, 2, 'pending', NULL, '2026-02-17 07:05:48'),
(3, 6, 2, 'pending', NULL, '2026-02-17 07:20:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('Student','Faculty','Event Organizer') NOT NULL,
  `department` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `system_notifications` tinyint(1) DEFAULT 1,
  `status` enum('pending','active','suspended') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `department`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `profile_image`, `system_notifications`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Event Organizer', 'Electrical Engineering', 'NASIR', 'ABBAS', 'nasiryt.827@gmail.com', '$2y$10$r5NZ.E2kqBn0.8sUCM8YJezib7vPerwYKe8M5d0pnviuH9v9wYMcO', '03176526827', '6994016b37f26.png', 1, 'active', '2026-02-17 05:48:39', '2026-02-17 05:49:31'),
(2, 'Student', 'Electrical Engineering', 'Haider', 'Ali', 'saifx280@gmail.com', '$2y$10$KcyAf9UCjiTSQ6cGcHbMnezca3fhgWGCFBKSZcIjmZUXxo6vOBOVy', '03120558090', '699405d26c193.png', 1, 'active', '2026-02-17 06:03:07', '2026-02-17 06:56:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `organizer_id` (`organizer_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `event_categories`
--
ALTER TABLE `event_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `event_feedback`
--
ALTER TABLE `event_feedback`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_feedback` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_event_user` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `event_categories`
--
ALTER TABLE `event_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `event_feedback`
--
ALTER TABLE `event_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `event_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `event_feedback`
--
ALTER TABLE `event_feedback`
  ADD CONSTRAINT `event_feedback_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_feedback_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
