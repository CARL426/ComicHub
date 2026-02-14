-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2025 at 01:57 AM
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
-- Database: `comichub`
--

-- --------------------------------------------------------

--
-- Table structure for table `comics`
--

CREATE TABLE `comics` (
  `comic_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `author` varchar(100) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `universe_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comics`
--

INSERT INTO `comics` (`comic_id`, `title`, `description`, `author`, `cover_image`, `price`, `universe_id`, `created_at`, `updated_at`) VALUES
(1, 'ADVENTURES OF SUPERMAN - BOOK OF EL (2025)', 'Superman returns! Fan-favorite Future State: Superman: House of El creators Phillip Kennedy Johnson and Scott Godlewski reunite for Adventures of Superman: Book of El! As the Super-Family settles into a well-earned time of peace, a powerful ally-turned-enemy invades Earth with an army at his back and a god-aspect of Olgrun, mad god of the First World. When even the Super-Family lacks the power to stop him, Superman is sent against his will to a place even he’s never been…where he’ll find allies he could never have expected.', 'Phillip Kennedy Johnson', 'assets/uploads/1758297689_ADVENTURES OF SUPERMAN - BOOK OF EL (2025).jpg', 20.00, 2, '2025-09-09 19:59:31', '2025-09-24 07:32:45'),
(2, 'Godzilla - Awakening (2014)', '0', 'Greg Borenstein', 'assets/uploads/1758297701_Godzilla - Awakening (2014).jpg', 15.00, 4, '2025-09-09 20:01:20', '2025-09-19 16:01:41'),
(3, 'Invincible - Family Matters (2003)', '0', 'Robert Kirkman', 'assets/uploads/1758297710_Invincible - Family Matters (2003).jpg', 25.00, 3, '2025-09-09 20:02:18', '2025-09-19 16:01:50'),
(4, 'Captain America (2025)', 'Ayy Par Kwar', 'Chip Zdarsky', 'assets/uploads/1758299236_Captain America (2025).jpg', 30.00, 5, '2025-09-09 20:04:01', '2025-09-19 16:52:00'),
(9, 'BattleWorld(2025)', 'THE BATTLEWORLD FROM SECRET WARS RETURNS! \"SLAY YOUR ENEMIES, PROVE YOUR WORTH AND ALL YOU DESIRE SHALL BE YOURS IN THE WORLD TO COME!\" Heroes from across the Multiverse are thrown together on a patchwork world to engage in their own Secret Wars for the survival of their timelines! Who or what has created this Battleworld, and for what nefarious purpose? Enter Maestro. Is he friend, or foe?', 'Christos Gage', 'assets/uploads/1758695204_BattleWorld (2025).jpg', 35.00, 5, '2025-09-24 06:26:44', '2025-09-24 06:26:44'),
(11, 'CHALLENGERS OF THE UNKNOWN', 'Spinning out of Absolute Power and the DC All In Special, the terror of the Darkseid shockwave has cascaded across the DC Universe…tearing open the very fabric of time and space itself! Only one band of super-scientists have the right stuff to challenge the fate of a universe…enter: THE CHALLENGERS OF THE UKNOWN. Alongside the Justice League—where the Challengers run day-to-day operations for the massive Watchtower base in orbit above Earth—Ace Morgan, June Robbins, Prof Haley, Red Ryan, and Rocky Davis must team with Superman, Batman, Wonder Woman, and the rest of the League to seal the rifts that threaten the galaxy. But a mysterious foe from the Challengers’ past lurks in the shadows, and its connection to the godshock will put the DCU on borrowed time!', 'Al Ewing', 'assets/uploads/1758735837_CHALLENGERS OF THE UNKNOWN.jpg', 35.00, 2, '2025-09-24 17:43:57', '2025-09-24 17:43:57'),
(12, 'Invincible Compendium', 'Unleash the extraordinary Invincible Compendium, a masterful collection of the epic comic series spanning over 1,000 pages. Dive into the thrilling adventures of Mark Grayson, a seemingly ordinary teenager who discovers his lineage as the son of Omni-Man, a formidable extraterrestrial superhero', 'Robert Kirkman', 'assets/uploads/1758736381_Invincible Compendium.jpg', 35.00, 3, '2025-09-24 17:53:01', '2025-09-24 17:53:01'),
(13, 'Justice League vs. Godzilla vs. Kong', 'DC and Legendary Comics are celebrating the first day of San Diego Comic-Con 2023 with a colossal announcement: DC will collide with Legendary’s Monsterverse in Justice League vs. Godzilla vs. Kong, the cataclysmic crossover event of the year you never expected! In partnership with Toho International, the 7-issue series, launching in October, is from acclaimed writer Brian Buccellato (The Flash, Injustice, Detective Comics), bestselling artist Christian Duce (Batman/Fortnite: Zero Point) and colorist Luis Guerrero!', 'Brian Buccellato', 'assets/uploads/1758736685_Justice League vs. Godzilla vs. Kong.jpg', 35.00, 4, '2025-09-24 17:58:05', '2025-09-24 17:58:05');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`favorite_id`, `user_id`, `comic_id`, `created_at`) VALUES
(29, 13, 11, '2025-09-24 18:12:36'),
(30, 13, 13, '2025-09-25 17:08:10');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount_paid` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `user_id`, `comic_id`, `purchase_date`, `amount_paid`) VALUES
(24, 13, 1, '2025-09-24 17:24:17', 20.00),
(25, 13, 13, '2025-09-25 10:40:30', 35.00),
(26, 13, 12, '2025-09-25 10:42:44', 35.00),
(27, 13, 12, '2025-09-25 10:43:43', 35.00),
(28, 13, 13, '2025-10-03 17:11:31', 35.00);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `comic_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 13, 3, 5, 'So fk awsome', '2025-09-11 12:13:19', '2025-09-11 12:13:19'),
(6, 17, 11, 5, 'Fine', '2025-09-25 10:06:47', '2025-09-25 10:07:56'),
(7, 13, 13, 5, 'The comic is amazing.', '2025-10-03 11:27:06', '2025-10-03 11:27:06');

-- --------------------------------------------------------

--
-- Table structure for table `universes`
--

CREATE TABLE `universes` (
  `universe_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `universes`
--

INSERT INTO `universes` (`universe_id`, `name`, `description`, `created_at`) VALUES
(2, 'DC', 'The DC Universe is a world of legendary heroes and villains, where epic battles and adventures showcase justice, power, and the struggle between good and evil.', '2025-09-09 19:51:24'),
(3, 'INVENCIBLE', 'Invincible follows a young superhero balancing his powers, family, and the fight against evil in a world full of intense battles and shocking twists.', '2025-09-09 19:52:52'),
(4, 'MONSTERVERSE', 'MonsterVerse is a world of gigantic monsters and epic battles, where titans clash and humanity fights to survive.', '2025-09-09 19:53:48'),
(5, 'MARVEL', 'The Marvel Universe is a world of superheroes and villains, where epic battles and adventures explore heroism, power, and the fight between good and evil.', '2025-09-09 19:54:20'),
(6, 'CONJURING', 'The Conjuring Universe is a shared horror film universe created by James Wan, centered on supernatural investigations and demonic hauntings. It began with The Conjuring (2013) and expanded into multiple spin-offs like Annabelle, The Nun, and The Curse of La Llorona. The universe follows paranormal investigators Ed and Lorraine Warren as they confront cursed objects, sinister spirits, and dark entities, making it one of the most successful modern horror franchises.', '2025-10-03 11:19:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `profile_pic` varchar(255) DEFAULT 'assets/images/default-avatar.png',
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `full_name`, `profile_pic`, `is_admin`, `created_at`, `updated_at`) VALUES
(13, 'CARL', 'carl@gmail.com', '$2y$10$y4cl7sc3JBb06SNMlXhLqenXFx9fcVZPFvcOQwFsu62SiEyjo6RV6', 'Khant Zaw Hein', 'assets/uploads/avatar_13_1758784406_55e4e7b7.jpg', 1, '2025-09-09 03:41:10', '2025-09-25 09:37:34'),
(17, 'JAZZY', 'jazzy@gmail.com', '$2y$10$OkN.Y0/UBPvkZjGyrRxmFe20EJTaT3JjZN3kHnFUbuh/Af/0aYxNu', 'Htet Aung Lwin', 'assets/uploads/avatar_17_1758795546_c7f5e3bb.jpg', 0, '2025-09-24 08:13:30', '2025-09-25 10:19:06'),
(22, 'KALAR', 'kalar@gmail.com', '$2y$10$oHMe2Ne2PANGj3BSREPY4..qx0Ug.ZPrvWGzsA1NiTJgQbm/hQoni', 'Aung Pyae', 'assets/images/default-avatar.svg', 1, '2025-10-03 23:45:00', '2025-10-03 23:45:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comics`
--
ALTER TABLE `comics`
  ADD PRIMARY KEY (`comic_id`),
  ADD KEY `universe_id` (`universe_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`comic_id`),
  ADD KEY `comic_id` (`comic_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `comic_id` (`comic_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`user_id`,`comic_id`),
  ADD KEY `comic_id` (`comic_id`);

--
-- Indexes for table `universes`
--
ALTER TABLE `universes`
  ADD PRIMARY KEY (`universe_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comics`
--
ALTER TABLE `comics`
  MODIFY `comic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `universes`
--
ALTER TABLE `universes`
  MODIFY `universe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comics`
--
ALTER TABLE `comics`
  ADD CONSTRAINT `comics_ibfk_1` FOREIGN KEY (`universe_id`) REFERENCES `universes` (`universe_id`) ON DELETE SET NULL;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`comic_id`) ON DELETE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`comic_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`comic_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
