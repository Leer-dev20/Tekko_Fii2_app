-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 14, 2026 at 03:53 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tekko_fii_a`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`) VALUES
(1, 'E-commerce', 'ri-store-2-line'),
(2, 'Applications Mobiles', 'ri-smartphone-line'),
(3, 'Plugins & Extensions', 'ri-code-box-line'),
(4, 'Tableaux de Bord', 'ri-dashboard-line'),
(5, 'Sites Portfolio', 'ri-user-line'),
(6, 'Blogs & CMS', 'ri-article-line'),
(7, 'Jeux', 'ri-gamepad-line'),
(8, 'API & Services', 'ri-cloud-line');

-- --------------------------------------------------------

--
-- Table structure for table `developers`
--

CREATE TABLE `developers` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `hourly_rate` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `availability_status` enum('available','partial','unavailable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `years_experience` tinyint UNSIGNED DEFAULT '0',
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `average_rating` decimal(3,2) UNSIGNED DEFAULT '0.00',
  `review_count` int UNSIGNED DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `developers`
--

INSERT INTO `developers` (`id`, `user_id`, `hourly_rate`, `availability_status`, `location`, `years_experience`, `title`, `bio`, `average_rating`, `review_count`, `created_at`, `updated_at`) VALUES
(1, 1, 750.00, 'available', 'Sénégal', 5, 'Développeur Full Stack', '5 ans d\'expérience en développement web. Spécialisé dans les applications React et les API RESTful.', 4.90, 48, '2026-02-12 15:57:30', '2026-02-13 14:09:57'),
(2, 2, 900.00, 'available', 'Sénégal', 3, 'Développeur Front-end', '5 ans d\'expérience en développement front-end. Passionnée par l\'UI/UX et l\'accessibilité web.', 4.80, 36, '2026-02-12 15:57:30', '2026-02-13 14:28:27'),
(3, 3, 812.00, 'partial', 'Sénégal', 8, 'Développeur Back-end', '3 ans d\'expérience en développement back-end. Expert en architecture de bases de données et sécurité.', 4.70, 52, '2026-02-12 15:57:30', '2026-02-13 14:29:14'),
(4, 4, 866.00, 'available', 'Sénégal', 7, 'Développeuse UI/UX', '7 ans d\'expérience en design d\'interface et développement front-end. Spécialisée en expérience utilisateur.\n7 ans d\'expérience en design d\'interface et développement front-end. Spécialisée en expérience utilisateur.', 4.90, 41, '2026-02-12 23:00:00', '2026-02-13 15:07:48'),
(5, 5, 650.00, 'unavailable', 'Maroc', 2, 'Développeur WordPress', '2 ans d\'expérience en développement WordPress. Expert en création de thèmes et plugins sur mesure.', 4.60, 33, '2026-02-13 09:02:35', '2026-02-13 15:07:59'),
(6, 6, 758.00, 'available', 'Maroc', 2, 'Développeur Mobile', '2 ans d\'expérience en développement mobile. Spécialisée dans les applications cross-platform.', 5.00, 29, '2026-02-13 10:03:47', '2026-02-13 15:08:07'),
(7, 7, 0.00, 'available', NULL, 0, NULL, NULL, 0.00, 0, '2026-02-14 10:28:55', '2026-02-14 10:28:55');

-- --------------------------------------------------------

--
-- Table structure for table `developer_skills`
--

CREATE TABLE `developer_skills` (
  `developer_id` int UNSIGNED NOT NULL,
  `skill_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `developer_skills`
--

INSERT INTO `developer_skills` (`developer_id`, `skill_id`) VALUES
(1, 1),
(3, 2),
(1, 5),
(2, 6),
(1, 8),
(3, 10),
(3, 12),
(1, 13),
(2, 14),
(2, 15),
(2, 16);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int UNSIGNED NOT NULL,
  `item_type` enum('developer','project') COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int UNSIGNED NOT NULL,
  `sender_id` int UNSIGNED NOT NULL,
  `receiver_id` int UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int UNSIGNED NOT NULL,
  `buyer_id` int UNSIGNED NOT NULL,
  `project_id` int UNSIGNED NOT NULL,
  `amount` decimal(10,2) UNSIGNED NOT NULL,
  `status` enum('pending','completed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_items`
--

CREATE TABLE `portfolio_items` (
  `id` int UNSIGNED NOT NULL,
  `developer_id` int UNSIGNED NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `technologies` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `portfolio_items`
--

INSERT INTO `portfolio_items` (`id`, `developer_id`, `title`, `description`, `technologies`, `created_at`) VALUES
(1, 1, 'E-commerce responsive avec React', 'Plateforme de vente en ligne', 'React, Node.js', '2026-02-12 15:57:31'),
(2, 1, 'Dashboard administrateur avec Next.js', 'Interface analytics', 'Next.js, MongoDB', '2026-02-12 15:57:31'),
(3, 1, 'API de gestion de contenu', 'RESTful avec Node.js', 'Node.js, Express', '2026-02-12 15:57:31'),
(4, 2, 'Site vitrine agence immobilière', 'Design moderne', 'Vue.js, Tailwind', '2026-02-12 15:57:31'),
(5, 2, 'Application de réservation', 'Service de rendez-vous', 'Vue.js, Firebase', '2026-02-12 15:57:31'),
(6, 2, 'Refonte UI plateforme éducative', 'UX/UI design', 'Figma, Vue.js', '2026-02-12 15:57:31'),
(7, 3, 'Système de paiement Stripe', 'Intégration sécurisée', 'Laravel, Stripe', '2026-02-12 15:57:31'),
(8, 3, 'API pour app mobile de livraison', 'Backend haute performance', 'Laravel, MySQL', '2026-02-12 15:57:31'),
(9, 3, 'Migration base de données legacy', 'Optimisation', 'MySQL, PHP', '2026-02-12 15:57:31'),
(10, 4, 'Refonte UX d\'une application bancaire', NULL, NULL, '2026-01-31 23:00:00'),
(11, 4, 'Design system pour startup SaaS', NULL, NULL, '2025-12-02 23:00:00'),
(12, 4, 'Prototype d\'application de santé', NULL, NULL, '2025-08-13 23:00:00'),
(13, 5, 'E-commerce avec WooCommerce', NULL, NULL, '2025-02-05 23:00:00'),
(14, 5, 'Plugin de réservation personnalisé', NULL, NULL, '2025-06-18 23:00:00'),
(15, 5, 'AgriLink application de ferme', 'AgriLink: Laison entre l\'agriculture et la transformation', NULL, '2025-07-24 23:00:00'),
(16, 6, 'Application de fitness avec suivi GPS', NULL, NULL, '2024-02-14 23:00:00'),
(17, 6, 'Application de livraison de repas', NULL, NULL, '2025-04-23 23:00:00'),
(18, 6, 'Application bancaire avec authentification', NULL, NULL, '2025-11-28 23:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int UNSIGNED NOT NULL,
  `developer_id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) UNSIGNED NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('published','draft','sold') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `developer_id`, `category_id`, `title`, `description`, `price`, `image_path`, `zip_file_path`, `github_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'E-commerce App', 'Une application E-commerce complète avec système de paiement intégré, gestion de panier et tableau de bord administrateur. Développée avec React pour le frontend et Node.js pour le backend.', 1625.00, 'img/projetcard1.jpg', NULL, '#', 'published', '2026-02-12 15:57:31', '2026-02-13 15:51:49'),
(2, 2, 5, 'Site Portfolio', 'Description: Site portfolio moderne et responsive avec animations fluides. Parfait pour présenter vos travaux et compétences. Code propre et bien documenté.', 541.00, 'img/projetcard4.jpg', NULL, '#', 'published', '2026-02-12 15:57:31', '2026-02-13 16:15:10'),
(3, 3, 6, 'Système de Blog CMS', 'Blog avec PHP natif', 1300.00, 'img/projetcard5.jpg', NULL, '#', 'published', '2026-02-12 15:57:31', '2026-02-12 15:57:31'),
(4, 5, NULL, 'Kit UI Mobile', 'Description: Kit d\'interface utilisateur moderne pour applications mobiles. Comprend plus de 50 composants personnalisables et un système de design cohérent.', 920.00, 'uploads/projects/img_698f61b27f956.jpg', 'uploads/projects/project_698f61b261615.zip', NULL, 'published', '2026-02-13 17:38:58', '2026-02-13 17:38:58'),
(5, 5, NULL, 'Agrilink', 'hhdhhgghgyjgyugyg', 3000.00, 'uploads/projects/img_699040b3a6795.jfif', 'uploads/projects/project_699040b382b63.zip', NULL, 'published', '2026-02-14 09:30:27', '2026-02-14 09:30:27'),
(6, 6, NULL, 'Tableau de Bord Analytics', 'Description: Tableau de bord analytique avec visualisations interactives et temps réel. Intègre plusieurs sources de données et offre des options d\'export.', 2383.00, 'uploads/projects/img_69904dc9c5e0e.jpg', 'uploads/projects/project_69904dc9c3bff.zip', NULL, 'published', '2026-02-14 10:26:17', '2026-02-14 10:26:17');

-- --------------------------------------------------------

--
-- Table structure for table `project_technologies`
--

CREATE TABLE `project_technologies` (
  `project_id` int UNSIGNED NOT NULL,
  `skill_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_technologies`
--

INSERT INTO `project_technologies` (`project_id`, `skill_id`) VALUES
(4, 1),
(3, 2),
(4, 2),
(1, 5),
(5, 5),
(6, 5),
(1, 8),
(5, 10),
(3, 12),
(2, 15),
(6, 33);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int UNSIGNED NOT NULL,
  `reviewer_id` int UNSIGNED NOT NULL,
  `item_type` enum('developer','project') COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` int UNSIGNED NOT NULL,
  `rating` tinyint UNSIGNED NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`, `created_at`) VALUES
(1, 'JavaScript', '2026-02-12 15:54:54'),
(2, 'PHP', '2026-02-12 15:54:54'),
(3, 'Python', '2026-02-12 15:54:54'),
(4, 'Java', '2026-02-12 15:54:54'),
(5, 'React', '2026-02-12 15:54:54'),
(6, 'Vue.js', '2026-02-12 15:54:54'),
(7, 'Angular', '2026-02-12 15:54:54'),
(8, 'Node.js', '2026-02-12 15:54:54'),
(9, 'Django', '2026-02-12 15:54:54'),
(10, 'Laravel', '2026-02-12 15:54:54'),
(11, 'WordPress', '2026-02-12 15:54:54'),
(12, 'MySQL', '2026-02-12 15:54:54'),
(13, 'MongoDB', '2026-02-12 15:54:54'),
(14, 'Figma', '2026-02-12 15:54:54'),
(15, 'HTML/CSS', '2026-02-12 15:54:54'),
(16, 'Tailwind', '2026-02-12 15:54:54'),
(33, 'API', '2026-02-14 10:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int UNSIGNED NOT NULL,
  `company_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint UNSIGNED DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` enum('developer','client') COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `user_type`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'ibrahimalo407@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ibrahima LO', 'developer', 'img/ibrahamalo.jpg', '2026-02-12 15:57:30', '2026-02-13 10:35:05'),
(2, 'mactar.seck@tekko-fii.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mactar Seck', 'developer', 'img/matar.jpg', '2026-02-12 15:57:30', '2026-02-12 15:57:30'),
(3, 'dame.seck@tekko-fii.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dame Seck', 'developer', 'img/dame.jpg', '2026-02-12 15:57:30', '2026-02-12 15:57:30'),
(4, 'ndeyesalimatas6@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Saly Sene', 'developer', 'img/salysene.jpg', '2026-02-12 15:57:30', '2026-02-13 10:34:00'),
(5, 'dozolerr@gmail.com', '$2y$10$1xRUqp0sMNyn74eZyo5/euJcJkRKS96f7k5FxrVew.yqs3srgZb3q', 'Mor Sene', 'developer', 'img/morsene.jpg', '2026-02-13 09:02:35', '2026-02-13 10:51:40'),
(6, 'elhadjiibrahimac@gmail.com', '$2y$10$zmGuQx2e3jZ1rknr3dF/1ebtvOPrC8zC4rZdrB3fgy6uxa1JJH03W', 'Ibrahima Cissé', 'developer', 'img/ibrahimacisse.jpg', '2026-02-13 10:03:46', '2026-02-13 10:25:22'),
(7, 'thiern@gmail.com', '$2y$10$V2wfqE3Adh9TrmcXjI43Z.X7qZGOIq/A1ntDd1c0IBAynvuFzUNG.', 'Thierno', 'developer', NULL, '2026-02-14 10:28:55', '2026-02-14 10:28:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `developers`
--
ALTER TABLE `developers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_availability` (`availability_status`),
  ADD KEY `idx_rate` (`hourly_rate`);

--
-- Indexes for table `developer_skills`
--
ALTER TABLE `developer_skills`
  ADD PRIMARY KEY (`developer_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`item_type`,`item_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `idx_conversation` (`sender_id`,`receiver_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `idx_order_buyer` (`buyer_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `developer_id` (`developer_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `developer_id` (`developer_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `project_technologies`
--
ALTER TABLE `project_technologies`
  ADD PRIMARY KEY (`project_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`reviewer_id`,`item_type`,`item_id`),
  ADD KEY `idx_item` (`item_type`,`item_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `developers`
--
ALTER TABLE `developers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `developers`
--
ALTER TABLE `developers`
  ADD CONSTRAINT `developers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `developer_skills`
--
ALTER TABLE `developer_skills`
  ADD CONSTRAINT `developer_skills_ibfk_1` FOREIGN KEY (`developer_id`) REFERENCES `developers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `developer_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  ADD CONSTRAINT `portfolio_items_ibfk_1` FOREIGN KEY (`developer_id`) REFERENCES `developers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`developer_id`) REFERENCES `developers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_technologies`
--
ALTER TABLE `project_technologies`
  ADD CONSTRAINT `project_technologies_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_technologies_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
