-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: web_magang_purwobinangun
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_role` varchar(255) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `reference_no` varchar(255) DEFAULT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  KEY `activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `activity_logs_module_action_type_index` (`module`,`action_type`),
  KEY `activity_logs_created_at_index` (`created_at`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `birth_certificates`
--

DROP TABLE IF EXISTS `birth_certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `birth_certificates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `registration_no` varchar(255) NOT NULL,
  `family_card_no` varchar(16) DEFAULT NULL,
  `child_name` varchar(255) NOT NULL,
  `child_nik` varchar(16) DEFAULT NULL,
  `gender` enum('L','P') NOT NULL,
  `birth_place` varchar(255) NOT NULL,
  `birth_date` date NOT NULL,
  `birth_day` varchar(20) DEFAULT NULL,
  `birth_time` varchar(255) DEFAULT NULL,
  `birth_type` varchar(255) NOT NULL DEFAULT 'Tunggal',
  `birth_order` int(11) NOT NULL DEFAULT 1,
  `birth_helper` varchar(255) DEFAULT 'Bidan',
  `birth_place_type` varchar(255) DEFAULT 'Rumah Sakit',
  `weight_kg` decimal(4,2) DEFAULT NULL,
  `weight_gram` int(10) unsigned DEFAULT NULL,
  `length_cm` decimal(4,1) DEFAULT NULL,
  `father_nik` varchar(16) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_birth_date` date DEFAULT NULL,
  `father_age` int(10) unsigned DEFAULT NULL,
  `father_job` varchar(255) DEFAULT NULL,
  `father_address` text DEFAULT NULL,
  `father_rt` varchar(5) DEFAULT NULL,
  `father_rw` varchar(5) DEFAULT NULL,
  `father_village` varchar(100) DEFAULT NULL,
  `father_district` varchar(100) DEFAULT NULL,
  `father_city` varchar(100) DEFAULT NULL,
  `father_province` varchar(100) DEFAULT NULL,
  `father_citizenship` varchar(20) NOT NULL DEFAULT 'WNI',
  `father_nationality` varchar(50) NOT NULL DEFAULT 'INDONESIA',
  `mother_nik` varchar(16) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_birth_date` date DEFAULT NULL,
  `mother_age` int(10) unsigned DEFAULT NULL,
  `mother_job` varchar(255) DEFAULT NULL,
  `mother_address` text DEFAULT NULL,
  `mother_rt` varchar(5) DEFAULT NULL,
  `mother_rw` varchar(5) DEFAULT NULL,
  `mother_village` varchar(100) DEFAULT NULL,
  `mother_district` varchar(100) DEFAULT NULL,
  `mother_city` varchar(100) DEFAULT NULL,
  `mother_province` varchar(100) DEFAULT NULL,
  `mother_citizenship` varchar(20) NOT NULL DEFAULT 'WNI',
  `mother_nationality` varchar(50) NOT NULL DEFAULT 'INDONESIA',
  `marriage_date` date DEFAULT NULL,
  `applicant_nik` varchar(16) NOT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `applicant_birth_date` date DEFAULT NULL,
  `applicant_age` int(10) unsigned DEFAULT NULL,
  `applicant_gender` varchar(10) DEFAULT NULL,
  `applicant_job` varchar(100) DEFAULT NULL,
  `applicant_phone` varchar(20) NOT NULL,
  `applicant_relation` varchar(255) DEFAULT 'Pemohon / Orang Tua',
  `address` text DEFAULT NULL,
  `padukuhan` varchar(255) DEFAULT 'Purwobinangun',
  `rt` varchar(5) DEFAULT '01',
  `rw` varchar(5) DEFAULT '01',
  `applicant_village` varchar(100) DEFAULT 'Purwobinangun',
  `applicant_district` varchar(100) DEFAULT 'Pakem',
  `applicant_city` varchar(100) DEFAULT 'Sleman',
  `applicant_province` varchar(100) DEFAULT 'D.I. Yogyakarta',
  `witness_1_nik` varchar(16) DEFAULT NULL,
  `witness_1_name` varchar(255) DEFAULT NULL,
  `witness_1_birth_date` date DEFAULT NULL,
  `witness_1_age` int(10) unsigned DEFAULT NULL,
  `witness_1_gender` varchar(10) DEFAULT NULL,
  `witness_1_job` varchar(255) DEFAULT NULL,
  `witness_1_address` text DEFAULT NULL,
  `witness_1_rt` varchar(5) DEFAULT NULL,
  `witness_1_rw` varchar(5) DEFAULT NULL,
  `witness_1_village` varchar(100) DEFAULT 'Purwobinangun',
  `witness_1_district` varchar(100) DEFAULT 'Pakem',
  `witness_1_city` varchar(100) DEFAULT 'Sleman',
  `witness_1_province` varchar(100) DEFAULT 'D.I. Yogyakarta',
  `witness_2_nik` varchar(16) DEFAULT NULL,
  `witness_2_name` varchar(255) DEFAULT NULL,
  `witness_2_birth_date` date DEFAULT NULL,
  `witness_2_age` int(10) unsigned DEFAULT NULL,
  `witness_2_gender` varchar(10) DEFAULT NULL,
  `witness_2_job` varchar(255) DEFAULT NULL,
  `witness_2_address` text DEFAULT NULL,
  `witness_2_rt` varchar(5) DEFAULT NULL,
  `witness_2_rw` varchar(5) DEFAULT NULL,
  `witness_2_village` varchar(100) DEFAULT 'Purwobinangun',
  `witness_2_district` varchar(100) DEFAULT 'Pakem',
  `witness_2_city` varchar(100) DEFAULT 'Sleman',
  `witness_2_province` varchar(100) DEFAULT 'D.I. Yogyakarta',
  `doc_birth_cert` varchar(255) DEFAULT NULL,
  `doc_family_card` varchar(255) DEFAULT NULL,
  `doc_marriage_cert` varchar(255) DEFAULT NULL,
  `doc_parents_ktp` varchar(255) DEFAULT NULL,
  `doc_witness_ktp` varchar(255) DEFAULT NULL,
  `applicant_signature` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `rejection_note` text DEFAULT NULL,
  `processed_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `birth_certificates_registration_no_unique` (`registration_no`),
  KEY `birth_certificates_user_id_foreign` (`user_id`),
  KEY `birth_certificates_family_card_no_index` (`family_card_no`),
  CONSTRAINT `birth_certificates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `birth_certificates`
--

LOCK TABLES `birth_certificates` WRITE;
/*!40000 ALTER TABLE `birth_certificates` DISABLE KEYS */;
INSERT INTO `birth_certificates` VALUES (1,2,'AKL-20260901-0001','3404050101900001','Aditya Pratama Nugraha',NULL,'L','Sleman','2026-08-15',NULL,'08:30','Tunggal',1,'Bidan','Rumah Sakit',3.25,NULL,49.5,'3404051205900001','Bambang Nugroho','1990-05-12',NULL,'Wiraswasta',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'WNI','INDONESIA','3404055508930002','Siti Nurhaliza','1993-08-15',NULL,'Ibu Rumah Tangga',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'WNI','INDONESIA',NULL,'3404051205900001','Bambang Nugroho',NULL,NULL,NULL,NULL,'081234567890','Ayah','Kadilobo, Purwobinangun, Pakem, Sleman','Kadilobo','02','05','Purwobinangun','Pakem','Sleman','D.I. Yogyakarta',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Purwobinangun','Pakem','Sleman','D.I. Yogyakarta',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Purwobinangun','Pakem','Sleman','D.I. Yogyakarta',NULL,NULL,NULL,NULL,NULL,NULL,'completed',0,NULL,'Petugas Pelayanan Kalurahan','2026-11-13 12:21:59','2026-11-13 12:21:59'),(2,3,'AKL-20260901-0002','3404050101880002','Kirana Ayudia Putri',NULL,'P','Sleman','2026-08-28',NULL,'14:15','Tunggal',2,'Dokter','Rumah Sakit',3.10,NULL,48.0,'3404051010880003','Agus Setiawan','1988-10-10',NULL,'PNS',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'WNI','INDONESIA','3404055203920004','Dewi Lestari','1992-03-12',NULL,'Guru',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'WNI','INDONESIA',NULL,'3404051010880003','Agus Setiawan',NULL,NULL,NULL,NULL,'081398765432','Ayah','Babadan, Purwobinangun, Pakem, Sleman','Babadan','01','02','Purwobinangun','Pakem','Sleman','D.I. Yogyakarta',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Purwobinangun','Pakem','Sleman','D.I. Yogyakarta',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Purwobinangun','Pakem','Sleman','D.I. Yogyakarta',NULL,NULL,NULL,NULL,NULL,NULL,'in_process',0,NULL,'Petugas Pelayanan Kalurahan','2026-11-13 12:21:59','2026-11-13 12:21:59'),(3,4,'AKL-20260901-0003','3404050101960003','Muhammad Rizqi Ramadhan',NULL,'L','Yogyakarta','2026-08-30',NULL,'21:00','Tunggal',1,'Bidan','Rumah Sakit',3.40,NULL,50.0,'3404051506950005','Rian Hidayat','1995-06-15',NULL,'Karyawan Swasta',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'WNI','INDONESIA','3404054807960006','Fitri Anggraini','1996-07-08',NULL,'Karyawan Swasta',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'WNI','INDONESIA',NULL,'3404054807960006','Fitri Anggraini',NULL,NULL,NULL,NULL,'082155667788','Ibu','Surodadi, Purwobinangun, Pakem, Sleman','Surodadi','03','07','Purwobinangun','Pakem','Sleman','D.I. Yogyakarta',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Purwobinangun','Pakem','Sleman','D.I. Yogyakarta',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Purwobinangun','Pakem','Sleman','D.I. Yogyakarta',NULL,NULL,NULL,NULL,NULL,NULL,'pending',0,NULL,NULL,'2026-11-13 12:21:59','2026-11-13 12:21:59');
/*!40000 ALTER TABLE `birth_certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `death_certificates`
--

DROP TABLE IF EXISTS `death_certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `death_certificates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `registration_no` varchar(255) NOT NULL,
  `family_card_no` varchar(16) DEFAULT NULL,
  `head_of_family_name` varchar(255) DEFAULT NULL,
  `deceased_nik` varchar(16) NOT NULL,
  `deceased_name` varchar(255) NOT NULL,
  `gender` enum('L','P') NOT NULL,
  `birth_date` date DEFAULT NULL,
  `deceased_birth_place` varchar(255) DEFAULT NULL,
  `religion` varchar(255) NOT NULL DEFAULT 'Islam',
  `deceased_job` varchar(255) DEFAULT NULL,
  `padukuhan` varchar(255) NOT NULL,
  `rt` varchar(5) NOT NULL DEFAULT '01',
  `rw` varchar(5) NOT NULL DEFAULT '01',
  `deceased_address` text DEFAULT NULL,
  `deceased_child_order` varchar(50) DEFAULT NULL,
  `death_day` varchar(20) DEFAULT NULL,
  `death_date` date NOT NULL,
  `death_time` varchar(255) DEFAULT NULL,
  `death_place` varchar(255) NOT NULL DEFAULT 'Rumah',
  `cause_of_death` varchar(255) NOT NULL DEFAULT 'Sakit / Usia Tua',
  `death_location_detail` varchar(255) DEFAULT NULL,
  `reported_by_title` varchar(255) NOT NULL DEFAULT 'Kepala Dusun / RT',
  `mother_nik` varchar(16) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_birth_place` varchar(255) DEFAULT NULL,
  `mother_birth_date` date DEFAULT NULL,
  `mother_job` varchar(255) DEFAULT NULL,
  `mother_address` text DEFAULT NULL,
  `mother_citizenship` varchar(50) NOT NULL DEFAULT 'WNI',
  `father_nik` varchar(16) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_birth_place` varchar(255) DEFAULT NULL,
  `father_birth_date` date DEFAULT NULL,
  `father_job` varchar(255) DEFAULT NULL,
  `father_address` text DEFAULT NULL,
  `father_citizenship` varchar(50) NOT NULL DEFAULT 'WNI',
  `applicant_nik` varchar(16) NOT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `applicant_birth_place` varchar(255) DEFAULT NULL,
  `applicant_birth_date` date DEFAULT NULL,
  `applicant_age` int(10) unsigned DEFAULT NULL,
  `applicant_job` varchar(255) DEFAULT NULL,
  `applicant_address` text DEFAULT NULL,
  `applicant_phone` varchar(20) NOT NULL,
  `applicant_relation` varchar(255) NOT NULL,
  `report_date` date DEFAULT NULL,
  `witness1_nik` varchar(16) DEFAULT NULL,
  `witness1_name` varchar(255) DEFAULT NULL,
  `witness1_birth_place` varchar(255) DEFAULT NULL,
  `witness1_birth_date` date DEFAULT NULL,
  `witness1_age` int(10) unsigned DEFAULT NULL,
  `witness1_address` text DEFAULT NULL,
  `witness2_nik` varchar(16) DEFAULT NULL,
  `witness2_name` varchar(255) DEFAULT NULL,
  `witness2_birth_place` varchar(255) DEFAULT NULL,
  `witness2_birth_date` date DEFAULT NULL,
  `witness2_age` int(10) unsigned DEFAULT NULL,
  `witness2_address` text DEFAULT NULL,
  `witness_nik` varchar(16) DEFAULT NULL,
  `witness_name` varchar(255) DEFAULT NULL,
  `doc_death_statement` varchar(255) DEFAULT NULL,
  `doc_rt_rw_statement` varchar(255) DEFAULT NULL,
  `doc_family_card` varchar(255) DEFAULT NULL,
  `doc_deceased_ktp` varchar(255) DEFAULT NULL,
  `doc_heir_family_card` varchar(255) DEFAULT NULL,
  `doc_heir_ktp` varchar(255) DEFAULT NULL,
  `doc_applicant_ktp` varchar(255) DEFAULT NULL,
  `officer_registration_type` varchar(255) DEFAULT NULL,
  `officer_name` varchar(255) DEFAULT 'Teguh Kurniawan, S.Pi',
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `rejection_note` text DEFAULT NULL,
  `processed_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `death_certificates_registration_no_unique` (`registration_no`),
  KEY `death_certificates_user_id_foreign` (`user_id`),
  KEY `death_certificates_family_card_no_index` (`family_card_no`),
  CONSTRAINT `death_certificates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `death_certificates`
--

LOCK TABLES `death_certificates` WRITE;
/*!40000 ALTER TABLE `death_certificates` DISABLE KEYS */;
INSERT INTO `death_certificates` VALUES (1,2,'AKM-20260901-0001','3404050101900001',NULL,'3404050101450001','Mbah Marto Suwito','L','1945-01-01',NULL,'Islam',NULL,'Donolayan','02','03',NULL,NULL,NULL,'2026-08-20','05:45','Rumah','Sakit / Usia Tua',NULL,'Kepala Dusun / RT',NULL,NULL,NULL,NULL,NULL,NULL,'WNI',NULL,NULL,NULL,NULL,NULL,NULL,'WNI','3404051205900001','Bambang Nugroho',NULL,NULL,NULL,NULL,NULL,'081234567890','Cucu',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3404051203750003','Wagimin',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Teguh Kurniawan, S.Pi','completed',0,NULL,'Petugas Pelayanan Kalurahan','2026-11-13 12:21:59','2026-11-13 12:21:59'),(2,5,'AKM-20260901-0002','3404050101800004',NULL,'3404054404500004','Ibu Sumirah','P','1950-04-04',NULL,'Islam',NULL,'Gadingan','01','01',NULL,NULL,NULL,'2026-08-29','11:20','RSUD Sleman','Sakit',NULL,'Dokter',NULL,NULL,NULL,NULL,NULL,NULL,'WNI',NULL,NULL,NULL,NULL,NULL,NULL,'WNI','3404052208800005','Gunawan Wibisono',NULL,NULL,NULL,NULL,NULL,'087811223344','Anak',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3404051909820006','Hartanto',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Teguh Kurniawan, S.Pi','verified',0,NULL,'Petugas Pelayanan Kalurahan','2026-11-13 12:21:59','2026-11-13 12:21:59');
/*!40000 ALTER TABLE `death_certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_members`
--

DROP TABLE IF EXISTS `family_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `family_card_no` varchar(16) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `birth_place` varchar(150) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `family_relationship` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_members_user_id_foreign` (`user_id`),
  KEY `family_members_family_card_no_index` (`family_card_no`),
  KEY `family_members_nik_index` (`nik`),
  CONSTRAINT `family_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_members`
--

LOCK TABLES `family_members` WRITE;
/*!40000 ALTER TABLE `family_members` DISABLE KEYS */;
INSERT INTO `family_members` VALUES (1,6,'3404050101910005','3404055208930005','Ratna Sari','Sleman','1993-08-12','P','Istri','2026-11-13 12:21:58','2026-11-13 12:21:58'),(2,6,'3404050101910005','3404051004180006','Dimas Aditya Pratama','Sleman','2018-04-10','L','Anak','2026-11-13 12:21:58','2026-11-13 12:21:58'),(3,7,'3404050101930006','3404051103900007','Joko Susilo','Sleman','1990-03-11','L','Suami','2026-11-13 12:21:59','2026-11-13 12:21:59'),(4,7,'3404050101930006','3404054509200008','Anindita Putri','Sleman','2020-09-05','P','Anak','2026-11-13 12:21:59','2026-11-13 12:21:59'),(5,8,'3404050101860007','3404055001880009','Maya Indah','Sleman','1988-01-10','P','Istri','2026-11-13 12:21:59','2026-11-13 12:21:59'),(6,8,'3404050101860007','3404051207160010','Bagas Kurnia','Sleman','2016-07-12','L','Anak','2026-11-13 12:21:59','2026-11-13 12:21:59'),(7,9,'3404050101950008','3404052504930011','Fajar Ramadhan','Sleman','1993-04-25','L','Suami','2026-11-13 12:21:59','2026-11-13 12:21:59'),(8,9,'3404050101950008','3404056102210012','Alika Nayla','Sleman','2021-02-21','P','Anak','2026-11-13 12:21:59','2026-11-13 12:21:59');
/*!40000 ALTER TABLE `family_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_01_000001_create_birth_certificates_table',1),(5,'2026_09_01_000002_create_death_certificates_table',1),(6,'2026_09_02_000001_update_users_table_for_warga_auth',1),(7,'2026_09_02_000002_add_family_card_no_to_certificates',1),(8,'2026_09_03_000001_update_status_enum_in_birth_certificates',1),(9,'2026_09_03_000002_add_is_archived_to_certificates_tables',1),(10,'2026_09_04_000001_update_status_column_in_death_certificates',1),(11,'2026_09_04_000002_add_family_relationship_and_create_family_members',1),(12,'2026_09_04_000003_add_doc_family_card_to_users_table',1),(13,'2026_09_04_000004_create_profile_update_requests_table',1),(14,'2026_09_04_000005_add_witnesses_to_birth_certificates_table',1),(15,'2026_09_04_000006_add_template_fields_to_birth_certificates_table',1),(16,'2026_10_25_000001_add_parent_details_to_birth_certificates_table',1),(17,'2026_10_27_000001_add_reporter_details_to_birth_certificates_table',1),(18,'2026_10_27_000002_add_witness_details_to_birth_certificates_table',1),(19,'2026_10_27_000003_add_applicant_signature_to_birth_certificates_table',1),(20,'2026_10_27_000004_create_activity_logs_table',1),(21,'2026_11_01_000001_add_sleman_form_fields_to_death_certificates_table',1),(22,'2026_11_01_000002_add_job_and_witness_birth_to_death_certificates',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_update_requests`
--

DROP TABLE IF EXISTS `profile_update_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_update_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `family_card_no` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `birth_place` varchar(150) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `family_relationship` varchar(100) DEFAULT NULL,
  `doc_family_card` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `family_members_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`family_members_data`)),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `processed_by` varchar(255) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_update_requests_user_id_foreign` (`user_id`),
  KEY `profile_update_requests_status_index` (`status`),
  CONSTRAINT `profile_update_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_update_requests`
--

LOCK TABLES `profile_update_requests` WRITE;
/*!40000 ALTER TABLE `profile_update_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile_update_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(255) NOT NULL DEFAULT 'warga',
  `nik` varchar(16) DEFAULT NULL,
  `family_card_no` varchar(16) DEFAULT NULL,
  `doc_family_card` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `family_relationship` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('pending','active','rejected','archived') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_nik_unique` (`nik`),
  KEY `users_family_card_no_index` (`family_card_no`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin',NULL,NULL,NULL,'Petugas Pelayanan Kalurahan',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'purwo34041601@gmail.com',NULL,'$2y$12$pUJ7f.YD3.ovRCQlx.pCJOtVbIe/Dl84tmN68Sd4JlfOOCwDFOULm','active',NULL,'2026-11-13 12:21:57','Sistem',NULL,'2026-11-13 12:21:57','2026-11-13 12:21:57'),(2,'warga','3404051205900001','3404050101900001',NULL,'Bambang Nugroho','Sleman','1990-05-12','L',NULL,'Kadilobo, Purwobinangun, Pakem, Sleman','02','05','081234567890','bambang.nugroho@example.com',NULL,'$2y$12$ILqMjtn2UQ5sEjnwBRouwO.yhBpv3Bugbo5VAWmPERvxb.SWhoiqK','active',NULL,'2026-11-13 12:21:57','Petugas Pelayanan Kalurahan',NULL,'2026-11-13 12:21:57','2026-11-13 12:21:57'),(3,'warga','3404051010880003','3404050101880002',NULL,'Agus Setiawan','Sleman','1988-10-10','L',NULL,'Babadan, Purwobinangun, Pakem, Sleman','01','02','081398765432','agus.setiawan@example.com',NULL,'$2y$12$h.j.DwFIrcFmc8rtoqc.je8dKaguxNXkOlF8J4zU2MXjR5mu45LM.','active',NULL,'2026-11-13 12:21:58','Petugas Pelayanan Kalurahan',NULL,'2026-11-13 12:21:58','2026-11-13 12:21:58'),(4,'warga','3404054807960006','3404050101960003',NULL,'Fitri Anggraini','Yogyakarta','1996-07-08','P',NULL,'Surodadi, Purwobinangun, Pakem, Sleman','03','07','082155667788','fitri.anggraini@example.com',NULL,'$2y$12$bbYHr5GCFtISCXA/wqEBOe4.5nMQ3cdg.bjiC7.Xy5MM0VWI2dlaS','pending',NULL,NULL,NULL,NULL,'2026-11-13 12:21:58','2026-11-13 12:21:58'),(5,'warga','3404052208800005','3404050101800004',NULL,'Gunawan Wibisono','Sleman','1980-08-22','L',NULL,'Gadingan, Purwobinangun, Pakem, Sleman','01','01','087811223344','gunawan.wibisono@example.com',NULL,'$2y$12$M6HTcXLS/oWW/4ifhdIYN.IEb5bpEWjIIR8OGOu/0TsMmIrmuk/MG','rejected','Nomor KK tidak sesuai dengan data kependudukan Kalurahan. Silakan periksa kembali dan upload data yang valid.','2026-11-13 12:21:58','Petugas Pelayanan Kalurahan',NULL,'2026-11-13 12:21:58','2026-11-13 12:21:58'),(6,'warga','3404051402910001','3404050101910005',NULL,'Eko Prasetyo','Sleman','1991-02-14','L','Kepala Keluarga','Turgo, Purwobinangun, Pakem, Sleman','01','01','081223344551','eko.prasetyo@example.com',NULL,'$2y$12$AJ6YSKLagZ35qUcQku4uLOeX4cYdJN9vEchydtJSdE6aJZqMuALp2','active',NULL,'2026-11-13 12:21:58','Petugas Pelayanan Kalurahan',NULL,'2026-11-13 12:21:58','2026-11-13 12:21:58'),(7,'warga','3404055506930002','3404050101930006',NULL,'Siti Rahmawati','Sleman','1993-06-15','P','Kepala Keluarga','Kemiri, Purwobinangun, Pakem, Sleman','02','03','081223344552','siti.rahmawati@example.com',NULL,'$2y$12$mNBuIv1MVYD96mX5qjEu7uIPTXyrPS86yrAvsbY0jRem0zY2gmykS','active',NULL,'2026-11-13 12:21:59','Petugas Pelayanan Kalurahan',NULL,'2026-11-13 12:21:59','2026-11-13 12:21:59'),(8,'warga','3404051809860003','3404050101860007',NULL,'Hendra Kurniawan','Sleman','1986-09-18','L','Kepala Keluarga','Nganggring, Purwobinangun, Pakem, Sleman','03','04','081223344553','hendra.kurniawan@example.com',NULL,'$2y$12$srJP96nnnZiNRLm9J4TzoeZ3s6YWzu1diinJna3kU2sxxUgOP.yES','active',NULL,'2026-11-13 12:21:59','Petugas Pelayanan Kalurahan',NULL,'2026-11-13 12:21:59','2026-11-13 12:21:59'),(9,'warga','3404056211950004','3404050101950008',NULL,'Dewi Sartika','Sleman','1995-11-22','P','Kepala Keluarga','Gatep, Purwobinangun, Pakem, Sleman','01','02','081223344554','dewi.sartika@example.com',NULL,'$2y$12$ZsQ7b1KeIZu6vS5ptE5OqeFBh1ryZWBCPKh0Wb6b8PwC4jjhvrcru','active',NULL,'2026-11-13 12:21:59','Petugas Pelayanan Kalurahan',NULL,'2026-11-13 12:21:59','2026-11-13 12:21:59');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-13 19:22:16
