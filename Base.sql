/*
SQLyog Enterprise - MySQL GUI v8.1 
MySQL - 8.2.0 : Database - gestion_employe
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`gestion_employe` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `gestion_employe`;

/*Table structure for table `conge` */

DROP TABLE IF EXISTS `conge`;

CREATE TABLE `conge` (
  `id_conge` int NOT NULL AUTO_INCREMENT,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `duree_jours_conge` int DEFAULT NULL,
  `duree_heure_conge` int DEFAULT NULL,
  `motif` text,
  `statut_conge` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'En attente',
  `date_demande` datetime DEFAULT NULL,
  `matricule_emp` int DEFAULT NULL,
  `message` text,
  `type_notif_conge` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_conge`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `conge` */

/*Table structure for table `direction` */

DROP TABLE IF EXISTS `direction`;

CREATE TABLE `direction` (
  `id_direction` int NOT NULL AUTO_INCREMENT,
  `nom_direction` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_direction`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `direction` */

/*Table structure for table `employer_login` */

DROP TABLE IF EXISTS `employer_login`;

CREATE TABLE `employer_login` (
  `matricule_emp` int NOT NULL AUTO_INCREMENT,
  `matricule` varchar(10) NOT NULL,
  `nom_emp` varchar(255) DEFAULT NULL,
  `prenom_emp` varchar(255) DEFAULT NULL,
  `date_embauche` date DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `id_service` int DEFAULT NULL,
  `mail_emp` varchar(255) DEFAULT NULL,
  `mdp_emp` varchar(100) DEFAULT NULL,
  `profil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '../uploads/default.jpg',
  PRIMARY KEY (`matricule_emp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `employer_login` */

/*Table structure for table `grade` */

DROP TABLE IF EXISTS `grade`;

CREATE TABLE `grade` (
  `id_grade` int NOT NULL AUTO_INCREMENT,
  `nom_grade` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_grade`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `grade` */

insert  into `grade`(id_grade,nom_grade) values (1,'Stagiaire'),(2,'Senior'),(3,'Junior');

/*Table structure for table `notifications` */

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id_notification` int NOT NULL AUTO_INCREMENT,
  `Matricule_emp` int DEFAULT NULL,
  `Genre` varchar(100) DEFAULT NULL,
  `Message` text,
  `Type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `date_notif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Statut_notif` varchar(100) DEFAULT 'non lu',
  UNIQUE KEY `id_notification` (`id_notification`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `notifications` */

/*Table structure for table `notifications_responsable` */

DROP TABLE IF EXISTS `notifications_responsable`;

CREATE TABLE `notifications_responsable` (
  `id_notification_resp` int NOT NULL AUTO_INCREMENT,
  `matricule_emp` int DEFAULT NULL,
  `Matricule_resp` int DEFAULT NULL,
  `Genre_notif` varchar(50) DEFAULT NULL,
  `Message_resp` text,
  `Type` varchar(255) DEFAULT 'Nouveau',
  `date_notif_resp` datetime DEFAULT CURRENT_TIMESTAMP,
  `Statut_notif_resp` enum('lu','non lu') DEFAULT 'non lu',
  PRIMARY KEY (`id_notification_resp`),
  KEY `Matricule_responsable` (`Matricule_resp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `notifications_responsable` */

/*Table structure for table `obtenir` */

DROP TABLE IF EXISTS `obtenir`;

CREATE TABLE `obtenir` (
  `matricule_emp` int DEFAULT NULL,
  `id_grade` int DEFAULT NULL,
  `date_obtention_grade` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `obtenir` */

/*Table structure for table `permission` */

DROP TABLE IF EXISTS `permission`;

CREATE TABLE `permission` (
  `id_permission` int NOT NULL AUTO_INCREMENT,
  `date_debut_per` datetime DEFAULT NULL,
  `date_fin_per` datetime DEFAULT NULL,
  `motif_per` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `duree_jour_per` int DEFAULT NULL,
  `duree_heure_per` int DEFAULT NULL,
  `date_demande_per` datetime DEFAULT NULL,
  `Statut_permission` varchar(100) DEFAULT 'En attente',
  `matricule_emp` int DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`id_permission`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `permission` */

/*Table structure for table `responsable` */

DROP TABLE IF EXISTS `responsable`;

CREATE TABLE `responsable` (
  `Matricule_resp` int NOT NULL AUTO_INCREMENT,
  `nom_resp` varchar(255) DEFAULT NULL,
  `prenom_resp` varchar(255) DEFAULT NULL,
  `id_service` int DEFAULT NULL,
  `mail_resp` varchar(255) DEFAULT NULL,
  `mdp_resp` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`Matricule_resp`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `responsable` */

insert  into `responsable`(Matricule_resp,nom_resp,prenom_resp,id_service,mail_resp,mdp_resp) values (1,'Randrianasolo','Solo',1,'Randria@gmail.com','123');

/*Table structure for table `service` */

DROP TABLE IF EXISTS `service`;

CREATE TABLE `service` (
  `id_service` int NOT NULL AUTO_INCREMENT,
  `nom_service` varchar(100) DEFAULT NULL,
  `Matricule_resp` int DEFAULT NULL,
  PRIMARY KEY (`id_service`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `service` */

insert  into `service`(id_service,nom_service,Matricule_resp) values (1,'Audit',1),(2,'Comptabilité',2);

/*Table structure for table `timesheet` */

DROP TABLE IF EXISTS `timesheet`;

CREATE TABLE `timesheet` (
  `id_timesheet` int NOT NULL AUTO_INCREMENT,
  `tache` varchar(255) DEFAULT NULL,
  `date_tache` date DEFAULT NULL,
  `duree_tache` int DEFAULT NULL,
  `client` varchar(255) DEFAULT NULL,
  `mission` varchar(255) DEFAULT NULL,
  `description_tache` text,
  `note` text,
  `matricule_emp` int DEFAULT NULL,
  PRIMARY KEY (`id_timesheet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `timesheet` */

/* Trigger structure for table `employer_login` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `before_insert_employe` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `before_insert_employe` BEFORE INSERT ON `employer_login` FOR EACH ROW BEGIN
    DECLARE next_matricule_emp INT;
    -- Si aucun matricule_emp encore (premier enregistrement), on simule à 1
    IF NEW.matricule_emp IS NULL THEN
        SET next_matricule_emp = (SELECT IFNULL(MAX(matricule_emp), 0) + 1 FROM employer_login);
    ELSE
        SET next_matricule_emp = NEW.matricule_emp;
    END IF;
    SET NEW.matricule = CONCAT('EMP', LPAD(next_matricule_emp, 3, '0'));
END */$$


DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
