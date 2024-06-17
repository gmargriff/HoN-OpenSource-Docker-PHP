-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Tempo de geração: 17/06/2024 às 12:42
-- Versão do servidor: 10.1.48-MariaDB-1~bionic
-- Versão do PHP: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `database`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `games`
--

CREATE TABLE `games` (
  `id` int(11) UNSIGNED NOT NULL,
  `identifier` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `start` double DEFAULT NULL,
  `mode` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `duration` int(10) UNSIGNED DEFAULT NULL,
  `duration_r` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `winner` tinyint(3) UNSIGNED DEFAULT NULL,
  `win_reward` double DEFAULT NULL,
  `lose_reward` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `games_players`
--

CREATE TABLE `games_players` (
  `id` int(11) UNSIGNED NOT NULL,
  `games_id` int(10) UNSIGNED DEFAULT NULL,
  `players_id` int(10) UNSIGNED DEFAULT NULL,
  `winner` tinyint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `players`
--

CREATE TABLE `players` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salt` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verifier` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `points` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `mmpoints` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `cookie` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `playerskins`
--

CREATE TABLE `playerskins` (
  `id` int(11) UNSIGNED NOT NULL,
  `player` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `skins`
--

CREATE TABLE `skins` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `silver` int(10) UNSIGNED DEFAULT NULL,
  `gold` int(10) UNSIGNED DEFAULT NULL,
  `identifier` int(10) UNSIGNED DEFAULT NULL,
  `local_content` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `purchasable` tinyint(3) UNSIGNED DEFAULT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `games_players`
--
ALTER TABLE `games_players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_4ffc17e8e380f591a5bcdc16d9b7d1d900bc1053` (`games_id`,`players_id`),
  ADD KEY `index_foreignkey_games_players_games` (`games_id`),
  ADD KEY `index_foreignkey_games_players_players` (`players_id`);

--
-- Índices de tabela `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `playerskins`
--
ALTER TABLE `playerskins`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `skins`
--
ALTER TABLE `skins`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `games_players`
--
ALTER TABLE `games_players`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `playerskins`
--
ALTER TABLE `playerskins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `skins`
--
ALTER TABLE `skins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `games_players`
--
ALTER TABLE `games_players`
  ADD CONSTRAINT `c_fk_games_players_games_id` FOREIGN KEY (`games_id`) REFERENCES `games` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `c_fk_games_players_players_id` FOREIGN KEY (`players_id`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
