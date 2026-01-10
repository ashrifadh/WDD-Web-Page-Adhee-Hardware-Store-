-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2025 at 12:14 PM
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
-- Database: `adhee_hardware`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `date_added`) VALUES
(41, 9, 26, 1, '2025-04-17 10:07:24'),
(42, 9, 27, 1, '2025-04-17 10:07:24');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `image`) VALUES
(25, 'Drilling Machine', 'Tools', 3500.00, 'https://m.media-amazon.com/images/I/713MLrv1mTL._SS1000_.jpg'),
(26, 'Spray Paint', 'Paint', 1500.00, 'https://www.auto-paint.co.uk/images/cache/aerosol_full_size6ab.600.webp'),
(27, 'Safety Helmet', 'Safety Equipment', 700.00, 'https://d11ak7fd9ypfb7.cloudfront.net/styles1100px/PS61YER.jpg'),
(28, 'Cement Bag', 'Building Materials', 2500.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTJN9AENDgzLbfYPVatNFjOj6ruU22jr3Q74Q&s'),
(30, 'LED Bulb 9(W)', 'Electrical', 350.00, 'https://objectstorage.ap-mumbai-1.oraclecloud.com/n/softlogicbicloud/b/cdn/o/products/BULPBUM07097--1--1529047255.jpeg'),
(31, 'Ceiling Fan', 'Electrical', 10000.00, 'https://objectstorage.ap-mumbai-1.oraclecloud.com/n/softlogicbicloud/b/cdn/o/products/New%20Quanta02-01_0--1633324327.jpg'),
(32, 'PVC pipe (10ft, 1\")', 'Plumbing', 800.00, 'https://www.millerplastics.com/wp-content/uploads/2021/11/millerplasticproductsinc-94814-development-pvc-piping-blogbanner1.jpg'),
(33, 'Pipe Fitting (elbow)', 'Plumbing', 70.00, 'https://images-cdn.ubuy.co.in/6358fe71cda3e96590137213-sourcing-map-3-way-elbow-pvc-pipe.jpg'),
(34, 'Safety Gloves', 'Safety Equipment', 250.00, 'https://images.thdstatic.com/productImages/ebf6e44c-4c3c-44f1-80db-4510fd57b698/svn/west-chester-protective-gear-work-gloves-37208-mcc6-64_600.jpg'),
(35, 'Screwdriver Set', 'Tools', 1200.00, 'https://m.media-amazon.com/images/I/71sJ7IbukJL._AC_SL1500_.jpg'),
(36, 'Measuring Tape (5m)', 'Tools', 500.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVbdQhj5eptOHpIFNiAtf6UuFwW-lautVpXA&s'),
(38, 'Hammer', 'Tools', 3200.00, 'https://tchristy.com/wp-content/uploads/sites/2/2022/11/TC.HM_.034.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, '1028488@bcas.lk', 'adsee@gmail.com', '1972', '2025-04-09 09:48:21'),
(3, 'hussain', 'byvvyhv@ytdty', '123', '2025-04-09 10:31:19'),
(4, 'adsee@gmail.com', 'ashrifxo@gmail.com', '$2y$10$DBXP8FV14rLZyIOV0NAzxetGvJd7g6xANLIyIwufZ.WYD1QtgPvJ.', '2025-04-09 10:51:48'),
(5, 'aaddd', 'aadd@gmail.com', '$2y$10$MSzYcco2/2ctfUTbaCPBF.aCctNKu4/i0bKVTWIFIXuZQnKnWtOm.', '2025-04-09 10:54:35'),
(6, 'Mohamed', 'Mohamed@gmail.com', 'Mohamed123', '2025-04-09 10:58:48'),
(7, '1', '1', '1', '2025-04-11 06:49:23'),
(8, '2', '2@gmail.com', '2', '2025-04-16 10:37:33'),
(9, 'aha', 'aha@gmail.com', '123', '2025-04-16 12:44:31'),
(10, 'Adh', 'adh@gmail.com', 'adh123', '2025-04-17 08:28:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
