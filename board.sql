-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jun 07, 2022 at 07:03 PM
-- Server version: 5.7.24
-- PHP Version: 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `board`
--

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL COMMENT 'view_name',
  `message` text NOT NULL,
  `upload_file` varchar(100) NOT NULL,
  `post_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `user_name`, `message`, `upload_file`, `post_date`) VALUES
(1, '葵屋虫太郎', 'DBテスト投稿', '', '2022-05-22 17:34:50'),
(2, '更新 順太郎', '降順でデータが反映されているかテスト', '', '2022-06-01 18:08:54'),
(4, 'アラート太郎', '<script>alert(\"Hello\");</script>', '', '2022-06-01 18:28:47'),
(5, 'セッション太郎', '名前が保持されるかチェック', '', '2022-06-02 12:40:15'),
(7, '更新チェック', 'F5にて同じ投稿がされないことを確認', '', '2022-06-03 15:32:32'),
(16, '画像テスト太郎', '画像チェックです。\r\nサイズは200×200です。', 'images/200_example.png', '2022-06-07 19:02:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
