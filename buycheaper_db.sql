-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 12 mars 2026 à 18:59
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `buycheaper_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `is_validated` tinyint(1) NOT NULL DEFAULT 0,
  `validated_at` timestamp NULL DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `store_id`, `quantity`, `is_validated`, `validated_at`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(5, 1, 12, 1, 4.00, 0, NULL, NULL, NULL, '2026-01-30 17:31:03', '2026-01-30 17:31:03');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`, `store_id`, `created_at`, `updated_at`) VALUES
(1, 'charcuterie', 'category', 1, '2026-01-17 23:47:56', '2026-01-17 23:47:56'),
(2, 'Fromagerie', 'category', 1, '2026-01-21 20:59:04', '2026-01-21 20:59:04'),
(3, 'Lait', 'category', 1, '2026-01-25 14:50:57', '2026-01-25 14:50:57');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(2, '2019_08_19_000000_create_failed_jobs_table', 1),
(3, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(4, '2026_01_09_000249_create_users_table', 1),
(5, '2026_01_09_000459_create_stores_table', 1),
(6, '2026_01_09_000546_create_categories_table', 1),
(7, '2026_01_09_000622_create_products_table', 1),
(8, '2026_01_09_000647_create_purchases_table', 1),
(9, '2026_01_19_222148_add_deleted_at_to_products_table', 2),
(10, '2026_01_21_054538_make_name_nullable_in_products_table', 3),
(11, '2026_01_21_054740_make_price_fields_nullable_in_products_table', 4),
(12, '2026_01_21_054856_add_missing_fields_to_products_table', 5),
(13, '2026_01_21_055440_add_unit_column_to_products_table', 6),
(14, '2026_01_21_055754_make_unit_nullable_in_products_table', 6),
(15, '2026_01_21_060512_make_product_fields_nullable', 7),
(16, '2026_01_23_061916_2025_12_21_999999_create_cart_items_table', 8);

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'auth_token', '95f25904fe22a17b41a493c90d1fd87859b33d23e8a86cd5d02b5cf0aae8102a', '[\"*\"]', NULL, NULL, '2026-01-13 21:15:02', '2026-01-13 21:15:02'),
(2, 'App\\Models\\User', 1, 'auth_token', '731b21ea57c3de0408355106c07829d0e596714a189335066524f63497193205', '[\"*\"]', NULL, NULL, '2026-01-13 21:15:09', '2026-01-13 21:15:09'),
(3, 'App\\Models\\User', 1, 'auth_token', '0825a7a82b5d62026876d2ba8609361e6f1d1bfa8605189af13e80b8a7a31952', '[\"*\"]', NULL, NULL, '2026-01-13 21:16:12', '2026-01-13 21:16:12'),
(4, 'App\\Models\\User', 1, 'auth_token', 'f0b94f9a9be7c3057cc195e1fce2938ae1634853e289fbb9683c577a0618c857', '[\"*\"]', NULL, NULL, '2026-01-13 21:39:27', '2026-01-13 21:39:27'),
(6, 'App\\Models\\User', 1, 'auth_token', 'f0d6ef82187d9cd1107b672c4423402820241ba52d2baf706a708a9c391a0064', '[\"*\"]', NULL, NULL, '2026-01-14 09:25:48', '2026-01-14 09:25:48'),
(10, 'App\\Models\\User', 2, 'auth_token', '2ef9e06da3e3a1e8cc7e8ca3f511af5299f72bd68880897e23f4a1473abc3ebb', '[\"*\"]', NULL, NULL, '2026-01-14 09:45:35', '2026-01-14 09:45:35'),
(18, 'App\\Models\\User', 2, 'auth_token', '744f8f036f08278daa757ef9563334a335d5c4fc612f6434f3fc6f32f99257d5', '[\"*\"]', '2026-01-15 21:28:57', NULL, '2026-01-15 20:41:37', '2026-01-15 21:28:57'),
(19, 'App\\Models\\User', 2, 'auth_token', 'ff265665cc64eb7d1ed7839271fe5e5c328fff70dca00bb49a225cc56c1fbd9e', '[\"*\"]', '2026-01-15 23:44:42', NULL, '2026-01-15 21:29:15', '2026-01-15 23:44:42'),
(20, 'App\\Models\\User', 2, 'auth_token', 'e9c142f2c3cba1620442cbd819fbfbefaeb5db827f07cacfe84053b616de221f', '[\"*\"]', '2026-01-17 21:58:51', NULL, '2026-01-15 23:44:59', '2026-01-17 21:58:51'),
(22, 'App\\Models\\User', 2, 'auth_token', '98a698b1e5bb7abed0e26821f8b0393769d96a87562a0388817df1ffdb2a739f', '[\"*\"]', '2026-01-17 23:10:24', NULL, '2026-01-17 22:00:43', '2026-01-17 23:10:24'),
(33, 'App\\Models\\User', 1, 'auth_token', 'eb52eb3987dea53252bdbdb8703e6fa67be350a08133a8d8d9c1394b28026910', '[\"*\"]', '2026-01-24 07:07:41', NULL, '2026-01-24 07:06:54', '2026-01-24 07:07:41'),
(37, 'App\\Models\\User', 1, 'auth_token', 'e5cdb4c2e474bc24a6c504026cdd804433c8794d748b454d78e589d3a9bf7e74', '[\"*\"]', '2026-01-24 12:19:44', NULL, '2026-01-24 10:47:15', '2026-01-24 12:19:44'),
(43, 'App\\Models\\User', 1, 'auth_token', '0de1c5a4beb316a93fb06e7859b91ad8bcbf714ff36893213682070df0421269', '[\"*\"]', '2026-01-30 17:39:31', NULL, '2026-01-30 17:27:42', '2026-01-30 17:39:31');

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `promo_price` decimal(10,2) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `promo_start` datetime DEFAULT NULL,
  `promo_end` datetime DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `image`, `original_price`, `promo_price`, `quantity`, `unit`, `promo_start`, `promo_end`, `category_id`, `store_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, NULL, '1768975663_69706d2f1436f.jpg', NULL, NULL, 3.00, NULL, '2026-01-21 17:57:24', '2026-01-22 19:57:24', 1, 1, '2026-01-21 05:07:43', '2026-01-21 05:07:43', NULL),
(2, NULL, NULL, '1768977948_6970761cc2364.jpg', NULL, NULL, 4.00, NULL, '2026-01-21 18:57:24', '2026-01-22 19:57:24', 1, 1, '2026-01-21 05:45:49', '2026-01-21 05:45:49', NULL),
(3, NULL, NULL, '1769011080_6970f788d1bf7.jpg', NULL, NULL, 2.00, NULL, '2026-01-21 19:58:59', '2026-01-22 19:58:59', 1, 1, '2026-01-21 14:58:01', '2026-01-21 14:58:01', NULL),
(4, NULL, NULL, '1769013681_697101b17e667.jpg', NULL, NULL, 2.00, NULL, NULL, NULL, 1, 1, '2026-01-21 15:41:21', '2026-01-21 15:41:21', NULL),
(5, NULL, NULL, '1769017601_6971110184718.jpg', NULL, NULL, 6.00, NULL, '2026-01-21 19:57:24', '2026-01-22 18:57:24', 1, 1, '2026-01-21 16:46:41', '2026-01-21 16:46:41', NULL),
(6, NULL, NULL, '1769023791_6971292f79da4.jpg', NULL, NULL, 5.00, NULL, NULL, '2026-01-22 19:29:51', 1, 1, '2026-01-21 18:29:51', '2026-01-21 18:29:51', NULL),
(7, NULL, NULL, '1769028397_69713b2d12931.jpg', NULL, NULL, 8.00, NULL, NULL, '2026-01-22 20:46:37', 1, 1, '2026-01-21 19:46:37', '2026-01-21 19:46:37', NULL),
(8, NULL, NULL, '1769032870_69714ca6e9ee4.jpg', NULL, NULL, 5.00, NULL, NULL, '2026-01-22 22:01:10', 2, 1, '2026-01-21 21:01:10', '2026-01-21 21:01:10', NULL),
(9, NULL, NULL, '1769255123_6974b0d3bf70f.jpg', NULL, NULL, 5.00, NULL, NULL, '2026-01-25 11:45:23', 2, 1, '2026-01-24 10:45:23', '2026-01-24 10:45:23', NULL),
(10, NULL, NULL, '1769264671_6974d61fcc4c3.jpg', NULL, NULL, 10.00, NULL, NULL, '2026-01-25 14:24:31', 2, 1, '2026-01-24 13:24:31', '2026-01-24 13:24:31', NULL),
(11, NULL, NULL, '1769356220_69763bbcb4222.jpg', NULL, NULL, 8.00, NULL, NULL, '2026-01-26 15:50:20', 2, 1, '2026-01-25 14:50:20', '2026-01-25 14:50:20', NULL),
(12, NULL, NULL, '1769797445_697cf74551815.jpg', NULL, NULL, 10.00, NULL, NULL, '2026-01-31 18:24:05', 3, 1, '2026-01-30 17:24:05', '2026-01-30 17:24:05', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `purchases`
--

CREATE TABLE `purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `opening_time` time NOT NULL,
  `closing_time` time NOT NULL,
  `manager_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `stores`
--

INSERT INTO `stores` (`id`, `name`, `address`, `latitude`, `longitude`, `phone`, `opening_time`, `closing_time`, `manager_id`, `created_at`, `updated_at`) VALUES
(1, 'mag1', 'paris CDG', 48.85660000, 2.35220000, '0335886659', '08:00:00', '20:00:00', 2, '2026-01-17 23:47:32', '2026-01-17 23:47:32');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','manager') NOT NULL DEFAULT 'client',
  `phone` varchar(255) DEFAULT NULL,
  `fcm_token` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `fcm_token`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Dychel OKO', 'dycheloko20@gmail.com', '$2y$12$qnCvyIBBrFyFvJdapJRhRujv7pWiahdK/IhIdRuW2AGy4yPDGwfyW', 'client', '062580066', NULL, NULL, '2026-01-13 21:13:59', '2026-01-13 21:13:59'),
(2, 'Mignon ALANTAN OKO', 'dycheloko@gmail.com', '$2y$12$Ei0iICT80029IAfm/9K3p.d3BfQKk/ANpgGAfmUgoCbgDAZ24rWoq', 'manager', '062803786', NULL, NULL, '2026-01-14 09:45:35', '2026-01-14 09:45:35');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_items_product_id_foreign` (`product_id`),
  ADD KEY `cart_items_user_id_is_validated_index` (`user_id`,`is_validated`),
  ADD KEY `cart_items_store_id_is_validated_index` (`store_id`,`is_validated`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_store_id_foreign` (`store_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_store_id_foreign` (`store_id`);

--
-- Index pour la table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchases_user_id_foreign` (`user_id`),
  ADD KEY `purchases_product_id_foreign` (`product_id`);

--
-- Index pour la table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stores_manager_id_foreign` (`manager_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
