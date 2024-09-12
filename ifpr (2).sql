-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:33306
-- Tempo de geração: 13/09/2024 às 00:59
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ifpr`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `laboratorio`
--

CREATE TABLE `laboratorio` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `numero_computadores` int(11) NOT NULL,
  `bloco` char(1) NOT NULL,
  `sala` int(11) NOT NULL,
  `liberado` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `laboratorio`
--

INSERT INTO `laboratorio` (`id`, `nome`, `numero_computadores`, `bloco`, `sala`, `liberado`, `criado_em`, `atualizado_em`) VALUES
(1, 'LB01', 35, 'B', 22, 1, '2024-09-08 22:11:18', '2024-09-08 22:11:18'),
(2, 'C25', 25, 'C', 25, 1, '2024-09-09 00:39:45', '2024-09-09 00:39:45'),
(3, 'Z51', 13, 'Z', 51, 0, '2024-09-11 01:15:17', '2024-09-11 01:15:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pessoa`
--

CREATE TABLE `pessoa` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` char(1) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ultimo_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pessoa`
--

INSERT INTO `pessoa` (`id`, `nome`, `email`, `senha`, `tipo`, `criado_em`, `atualizado_em`, `ultimo_login`) VALUES
(1, 'luiz', 'teste@teste.com.br', '$2y$10$18lnVMLgW3w95goX7tNjuOHw068YTVkPMXqAJmPBb9drNTYqHO6qm', 'a', '2024-09-08 22:08:42', '2024-09-08 22:46:39', '2024-09-08 22:46:39'),
(2, 'Luiz Eduardo Zanatta Sendeski', 'eduardosendeski@hotmail.com', '$2y$10$eX55pHXgwEMr1FzAyKMcwemxzl7CJ/o6fwQIrX1wAH33xUeuSVSM6', 'a', '2024-09-08 22:09:44', '2024-09-11 01:12:09', '2024-09-11 01:12:09'),
(3, 'fulano', 'fualinho@fulano.com', '$2y$10$aYTeTBK8LtZ3OPkm28S8v./mh5AI7sQELXuS43ix/W/1lTmN0occe', '', '2024-09-08 22:46:00', '2024-09-11 01:21:07', NULL),
(5, 'gustavo', 'gustavo@gmail.com', '$2y$10$3I3x0y79V492SkBh5bbwA.pS0UyRnZ0jJO8kcRgvLvG67AOFRx/Ci', '', '2024-09-08 22:47:43', '2024-09-11 01:20:57', '2024-09-09 00:08:03'),
(6, 'Vinicius', 'vinicius@hotmail.com', '$2y$10$vmHMUZ8ekhHwUQu0oGy6KOqcgeZaTOxmfzISWG8fTAWX8xHxx7y62', '', '2024-09-09 23:16:52', '2024-09-11 01:16:49', '2024-09-11 01:16:49');

-- --------------------------------------------------------

--
-- Estrutura para tabela `reserva`
--

CREATE TABLE `reserva` (
  `id` int(11) NOT NULL,
  `pessoa_id` int(11) NOT NULL,
  `laboratorio_id` int(11) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `reserva`
--

INSERT INTO `reserva` (`id`, `pessoa_id`, `laboratorio_id`, `descricao`, `data`, `hora_inicio`, `hora_fim`, `criado_em`, `atualizado_em`) VALUES
(1, 1, 1, 'tESTE', '2024-09-09', '20:59:00', '23:59:00', '2024-09-08 22:11:59', '2024-09-08 22:11:59'),
(2, 5, 1, 'TESTE', '2024-09-10', '22:28:00', '23:28:00', '2024-09-08 23:28:43', '2024-09-08 23:28:43'),
(3, 5, 1, 'mmmm', '2024-09-25', '23:21:00', '00:21:00', '2024-09-09 00:21:44', '2024-09-09 00:21:44'),
(4, 2, 1, 'teste', '2024-09-10', '22:25:00', '00:25:00', '2024-09-09 00:25:10', '2024-09-09 00:25:10'),
(5, 5, 1, 'www', '2024-09-04', '23:30:00', '23:36:00', '2024-09-09 00:30:45', '2024-09-09 00:30:45'),
(6, 2, 1, 'ttt', '2024-09-10', '22:32:00', '23:38:00', '2024-09-09 00:32:46', '2024-09-09 00:32:46'),
(7, 1, 1, 'ttt', '2024-09-10', '21:35:00', '23:40:00', '2024-09-09 00:35:44', '2024-09-09 00:35:44'),
(8, 3, 2, 'Teste', '2024-09-09', '09:00:00', '10:00:00', '2024-09-09 00:40:13', '2024-09-09 00:40:13'),
(10, 3, 2, 'tt', '2024-09-09', '11:30:00', '11:00:00', '2024-09-09 00:45:31', '2024-09-09 00:45:31'),
(11, 2, 2, 'teste', '2024-09-18', '10:00:00', '23:59:00', '2024-09-09 00:50:34', '2024-09-09 00:50:34'),
(12, 1, 1, 'rr', '2024-09-30', '00:00:00', '18:00:00', '2024-09-09 00:52:32', '2024-09-09 00:52:32'),
(13, 3, 2, 'yyyy', '2025-01-30', '21:14:00', '23:59:00', '2024-09-09 23:15:06', '2024-09-09 23:15:06'),
(14, 6, 1, 'TESTE', '2024-09-18', '00:00:00', '02:00:00', '2024-09-09 23:17:58', '2024-09-11 01:18:42'),
(16, 5, 3, '666', '2024-09-11', '00:15:00', '22:15:00', '2024-09-11 01:15:51', '2024-09-11 01:15:51');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `laboratorio`
--
ALTER TABLE `laboratorio`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pessoa`
--
ALTER TABLE `pessoa`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `reserva`
--
ALTER TABLE `reserva`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pessoa_id` (`pessoa_id`),
  ADD KEY `laboratorio_id` (`laboratorio_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `laboratorio`
--
ALTER TABLE `laboratorio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `pessoa`
--
ALTER TABLE `pessoa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `reserva`
--
ALTER TABLE `reserva`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `reserva`
--
ALTER TABLE `reserva`
  ADD CONSTRAINT `reserva_ibfk_1` FOREIGN KEY (`pessoa_id`) REFERENCES `pessoa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reserva_ibfk_2` FOREIGN KEY (`laboratorio_id`) REFERENCES `laboratorio` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
