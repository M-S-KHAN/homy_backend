CREATE DATABASE IF NOT EXISTS `homy_db`
/*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */
;

USE `homy_db`;

-- MySQL dump 10.13  Distrib 8.0.34, for macos13 (arm64)
--
-- Host: 127.0.0.1    Database: homy_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.28-MariaDB
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;

/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;

/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;

/*!50503 SET NAMES utf8 */
;

/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */
;

/*!40103 SET TIME_ZONE='+00:00' */
;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */
;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */
;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */
;

/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */
;

--
-- Table structure for table `bids`
--
DROP TABLE IF EXISTS `bids`;

/*!40101 SET @saved_cs_client     = @@character_set_client */
;

/*!50503 SET character_set_client = utf8mb4 */
;

CREATE TABLE `bids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `bid_amount` decimal(10, 2) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bids_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `bids_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */
;

--
-- Dumping data for table `bids`
--
LOCK TABLES `bids` WRITE;

/*!40000 ALTER TABLE `bids` DISABLE KEYS */
;

/*!40000 ALTER TABLE `bids` ENABLE KEYS */
;

UNLOCK TABLES;

--
-- Table structure for table `favorites`
--
DROP TABLE IF EXISTS `favorites`;

/*!40101 SET @saved_cs_client     = @@character_set_client */
;

/*!50503 SET character_set_client = utf8mb4 */
;

CREATE TABLE `favorites` (
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`, `property_id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */
;

--
-- Dumping data for table `favorites`
--
LOCK TABLES `favorites` WRITE;

/*!40000 ALTER TABLE `favorites` DISABLE KEYS */
;

/*!40000 ALTER TABLE `favorites` ENABLE KEYS */
;

UNLOCK TABLES;

--
-- Table structure for table `properties`
--
DROP TABLE IF EXISTS `properties`;

/*!40101 SET @saved_cs_client     = @@character_set_client */
;

/*!50503 SET character_set_client = utf8mb4 */
;

CREATE TABLE `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `lat` decimal(10, 8) DEFAULT NULL,
  `lng` decimal(11, 8) DEFAULT NULL,
  `price` decimal(10, 2) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */
;

--
-- Dumping data for table `properties`
--
LOCK TABLES `properties` WRITE;

/*!40000 ALTER TABLE `properties` DISABLE KEYS */
;

INSERT INTO
  `properties`
VALUES
  (
    1,
    'Cozy Cottage',
    'A beautiful and cozy cottage with 3 bedrooms and a large garden.',
    '123 Maple Street',
    37.77490000,
    -122.41940000,
    35000.00,
    3,
    '2024-04-30 16:39:26'
  ),
  (
    2,
    'Modern Urban Apartment',
    'An upscale apartment in the heart of the city, featuring two bedrooms, two bathrooms, and an open kitchen with modern appliances.',
    '456 City Center Dr, Metropolitan',
    40.71280000,
    -74.00600000,
    44000.00,
    3,
    '2024-04-30 16:43:49'
  ),
  (
    3,
    'Beachfront Villa',
    'Stunning villa right on the beach with beautiful sea views, including 4 bedrooms, a private pool, and ample patio space.',
    '789 Ocean Blvd, Seaside Town',
    34.01950000,
    -118.49120000,
    29000.00,
    4,
    '2024-04-30 16:44:09'
  ),
  (
    4,
    'Country Farmhouse',
    'Charming farmhouse in the countryside, perfect for peace and quiet. Features include three bedrooms, a large barn, and over 50 acres of land.',
    '321 Country Road, Rural Area',
    37.09020000,
    -95.71290000,
    88000.00,
    4,
    '2024-04-30 16:44:22'
  );

/*!40000 ALTER TABLE `properties` ENABLE KEYS */
;

UNLOCK TABLES;

--
-- Table structure for table `property_images`
--
DROP TABLE IF EXISTS `property_images`;

/*!40101 SET @saved_cs_client     = @@character_set_client */
;

/*!50503 SET character_set_client = utf8mb4 */
;

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 12 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */
;

--
-- Dumping data for table `property_images`
--
LOCK TABLES `property_images` WRITE;

/*!40000 ALTER TABLE `property_images` DISABLE KEYS */
;

INSERT INTO
  `property_images`
VALUES
  (
    2,
    1,
    'https://ik.imagekit.io/homy/property_image_1714781773_mdVRqlZlY'
  ),
  (
    3,
    1,
    'https://ik.imagekit.io/homy/property_image_1714781798_l3MGQfaAB'
  ),
  (
    4,
    2,
    'https://ik.imagekit.io/homy/property_image_1714781855_Ra2HizYCZ'
  ),
  (
    5,
    2,
    'https://ik.imagekit.io/homy/property_image_1714781879_u4WWc4Jhq'
  ),
  (
    6,
    2,
    'https://ik.imagekit.io/homy/property_image_1714781902_EDlSdxlJy'
  ),
  (
    7,
    3,
    'https://ik.imagekit.io/homy/property_image_1714781942_2mUa0a3R8'
  ),
  (
    8,
    3,
    'https://ik.imagekit.io/homy/property_image_1714781957_IkNFzgtVc'
  ),
  (
    9,
    3,
    'https://ik.imagekit.io/homy/property_image_1714781969_uaEK5q2as'
  ),
  (
    10,
    4,
    'https://ik.imagekit.io/homy/property_image_1714781984_8Kssc09TX'
  ),
  (
    11,
    4,
    'https://ik.imagekit.io/homy/property_image_1714782007_MA_CYnFUs'
  );

/*!40000 ALTER TABLE `property_images` ENABLE KEYS */
;

UNLOCK TABLES;

--
-- Table structure for table `user_profiles`
--
DROP TABLE IF EXISTS `user_profiles`;

/*!40101 SET @saved_cs_client     = @@character_set_client */
;

/*!50503 SET character_set_client = utf8mb4 */
;

CREATE TABLE `user_profiles` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */
;

--
-- Dumping data for table `user_profiles`
--
LOCK TABLES `user_profiles` WRITE;

/*!40000 ALTER TABLE `user_profiles` DISABLE KEYS */
;

/*!40000 ALTER TABLE `user_profiles` ENABLE KEYS */
;

UNLOCK TABLES;

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;

/*!40101 SET @saved_cs_client     = @@character_set_client */
;

/*!50503 SET character_set_client = utf8mb4 */
;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image_url` varchar(255) DEFAULT NULL,
  `role` enum('admin', 'agent', 'client') DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE = InnoDB AUTO_INCREMENT = 25 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */
;

--
-- Dumping data for table `users`
--
LOCK TABLES `users` WRITE;

/*!40000 ALTER TABLE `users` DISABLE KEYS */
;

INSERT INTO
  `users`
VALUES
  (
    1,
    'admin',
    'admin@gmail.com',
    '$2y$10$mk.6oe6B5x1WTc2rm99HIuTrCSUHF8Um8AFUXtOcqY/1B66t1CBuK',
    NULL,
    'admin',
    '2024-04-19 03:12:39'
  ),
(
    2,
    'client',
    'client@gmail.com',
    '$2y$10$GDfqmFN8yDggS6vGVB11suIpr8TYC7CZN41DRIqH2CoGF4S8ypIsC',
    NULL,
    'client',
    '2024-04-25 20:47:25'
  ),
(
    3,
    'landlord',
    'landlord@gmail.com',
    '$2y$10$kBXOdXePtAR6lysf8i4N8uCr92ZTpWFUYTHwRtu.8gSpdRw4WG4W6',
    NULL,
    'agent',
    '2024-04-26 00:56:49'
  ),
(
    4,
    'landlord2',
    'landlord2@gmail.com',
    '$2y$10$NYviMOghDjBAywTitQcDWubil40mPZMj3GNfM3eFN4zjZLmKbOzR2',
    NULL,
    'agent',
    '2024-05-02 15:59:53'
  );

/*!40000 ALTER TABLE `users` ENABLE KEYS */
;

UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */
;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */
;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */
;

/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */
;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */
;

-- Dump completed on 2024-05-19 12:38:45