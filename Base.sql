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
  `reference` varchar(255) DEFAULT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `conge` */

insert  into `conge`(id_conge,reference,date_debut,date_fin,duree_jours_conge,duree_heure_conge,motif,statut_conge,date_demande,matricule_emp,message,type_notif_conge) values (44,'1','2025-05-15 10:30:00','2025-05-16 10:30:00',1,0,' teste message head','En attente','2025-05-15 10:30:00',2,NULL,NULL),(42,'1','2025-05-16 12:45:00','2025-05-18 12:45:00',2,0,' Farany sisa','En attente','2025-05-14 12:45:00',2,NULL,NULL),(41,'1','2025-05-15 12:43:00','2025-05-18 12:44:00',3,0,' message','En attente','2025-05-14 12:43:00',2,NULL,NULL),(20,'3','2025-03-27 21:41:00','2025-03-28 23:41:00',1,2,' Teste','Validé','2025-03-26 21:40:00',2,'',NULL),(22,'2','2025-04-02 13:09:00','2025-04-04 13:09:00',2,0,' Rien2','Validé','2025-04-02 13:08:00',2,'',NULL),(24,'sd','2025-04-03 10:11:00','2025-04-09 10:11:00',6,0,' fsd','En attente','2025-04-03 10:11:00',2,NULL,NULL),(25,'2','2025-04-03 12:00:00','2025-04-09 12:00:00',6,0,' hd','En attente','2025-04-03 12:00:00',5,NULL,NULL),(26,'2','2025-04-03 12:02:00','2025-04-07 12:02:00',4,0,' dsf','Refusé','2025-04-03 12:02:00',6,'',NULL),(27,'d','2025-05-17 09:23:00','2025-05-18 09:23:00',1,0,' Teste farany','Validé','2025-05-09 09:23:00',2,'',NULL),(43,'2','2025-05-15 12:45:00','2025-05-18 12:45:00',3,0,' Ty no tena farany','En attente','2025-05-14 12:45:00',2,NULL,NULL),(29,'1','2025-05-20 16:23:00','2025-05-22 16:23:00',2,0,'notification','Validé','2025-05-12 16:22:00',2,'',NULL),(30,'1','2025-05-20 16:23:00','2025-05-22 16:23:00',2,0,'notification','En attente','2025-05-12 16:22:00',2,NULL,NULL),(31,'1','2025-05-20 16:23:00','2025-05-22 16:23:00',2,0,'notification','En attente','2025-05-12 16:22:00',2,NULL,NULL),(47,'1','2025-05-15 10:44:00','2025-05-30 10:44:00',15,0,' dsq','En attente','2025-05-15 10:44:00',2,NULL,NULL),(46,'h','2025-05-15 10:40:00','2025-05-16 10:40:00',1,0,' dg','En attente','2025-05-15 10:40:00',2,NULL,NULL),(45,'1','2025-05-15 10:37:00','2025-05-16 10:37:00',1,0,' sgfs','En attente','2025-05-15 10:37:00',2,NULL,NULL);

/*Table structure for table `departement` */

DROP TABLE IF EXISTS `departement`;

CREATE TABLE `departement` (
  `id_departement` int NOT NULL AUTO_INCREMENT,
  `nom_departement` varchar(100) DEFAULT NULL,
  `id_direction` int DEFAULT NULL,
  `Matricule_resp` int DEFAULT NULL,
  PRIMARY KEY (`id_departement`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `departement` */

insert  into `departement`(id_departement,nom_departement,id_direction,Matricule_resp) values (1,'box1',1,1),(2,'finance',3,3);

/*Table structure for table `direction` */

DROP TABLE IF EXISTS `direction`;

CREATE TABLE `direction` (
  `id_direction` int NOT NULL AUTO_INCREMENT,
  `nom_direction` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_direction`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `direction` */

insert  into `direction`(id_direction,nom_direction) values (1,'RH'),(2,'DAF');

/*Table structure for table `employer_login` */

DROP TABLE IF EXISTS `employer_login`;

CREATE TABLE `employer_login` (
  `matricule_emp` int NOT NULL AUTO_INCREMENT,
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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `employer_login` */

insert  into `employer_login`(matricule_emp,nom_emp,prenom_emp,date_embauche,telephone,role,id_service,mail_emp,mdp_emp,profil) values (2,'RAZAKA','Perline','2024-10-04','2','Simple',1,'Zafy@gmail.com','123','../../uploads/image Perline.png'),(4,'Rah','neny','2025-01-01','1','Simple',1,'Raneny@gmail.com','123','../../uploads/4neny.jpg'),(5,'RA','Kely','2025-04-03','2','Simple',1,'koto@gmail.com','1234',NULL),(6,'Razakanarivony','perl','2025-04-03','1234','Simple',1,'Randria@gmail.com','123',NULL),(7,'Rakoto','perl','2025-05-15','123456',NULL,1,'Rakotokoto@gmail','123',NULL);

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
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `notifications` */

insert  into `notifications`(id_notification,Matricule_emp,Genre,Message,Type,date_notif,Statut_notif) values (43,4,'Congé','Valider congé Raneny','Validé','2025-03-24 09:40:41','lu'),(60,4,'Congé','','Validé','2025-05-12 16:49:30','non lu'),(56,6,'Congé','','Refusé','2025-04-18 15:31:38','non lu'),(51,4,'Congé','','Validé','2025-04-03 10:21:44','lu'),(61,2,'Congé','','Validé','2025-05-12 16:49:41','non lu'),(58,2,'Permission','','Validé','2025-05-09 14:38:40','lu'),(59,2,'Permission','','Refusé','2025-05-09 14:41:18','non lu'),(62,4,'Congé','','Refusé','2025-05-12 16:50:08','non lu');

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
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `notifications_responsable` */

insert  into `notifications_responsable`(id_notification_resp,matricule_emp,Matricule_resp,Genre_notif,Message_resp,Type,date_notif_resp,Statut_notif_resp) values (25,2,NULL,'Nouveau congé','L\'employé RAZAKA Perline a soumis une demande de congé de 2\r\n             jours0 Heures','Congé','2025-05-14 12:45:00','non lu'),(26,2,NULL,'Nouveau congé','L\'employé RAZAKA Perline a soumis une demande de congé de 3\r\n             jours0 Heures','Congé','2025-05-14 12:45:00','lu'),(27,2,NULL,'Nouveau congé','L\'employé RAZAKA Perline a soumis une demande de congé de 1\r\n             jours0 Heures','Congé','2025-05-15 10:30:00','non lu'),(28,2,NULL,'Nouveau congé','L\'employé RAZAKA Perline a soumis une demande de congé de 1\r\n             jours0 Heures','Congé','2025-05-15 10:37:00','non lu'),(29,2,NULL,'Nouveau congé','L\'employé RAZAKA Perline a soumis une demande de congé de 1\r\n             jours0 Heures','Congé','2025-05-15 10:40:00','non lu'),(24,2,NULL,'Nouveau congé','L\'employé RAZAKA Perline a soumis une demande de congé de 3\r\n             jours0 Heures','Congé','2025-05-14 12:43:00','non lu'),(23,2,NULL,'Nouveau congé','L\'employé RAZAKA Perline a soumis une demande de congé de 2\r\n             jours0 Heures','Congé','2025-05-14 12:41:00','non lu'),(21,2,NULL,'Nouvelle permission','L\'employé RAZAKA Perline a soumis une demande de permission de 7\r\n             jours0 Heures','Congé','2025-05-14 12:29:00','non lu'),(22,2,NULL,'Nouveau congé','L\'employé RAZAKA Perline a soumis une demande de congé de 7\r\n             jours0 Heures','Congé','2025-05-14 12:40:00','non lu'),(19,2,NULL,'Nouveau congé','L\'employé RAZAKA Perline a soumis une demande de congé de 2\r\n             jours0 Heures','Congé','2025-05-14 12:04:00','non lu'),(20,2,NULL,'Nouvelle Timesheet','L\'employé RAZAKA Perlinea soumis une timesheet','Timesheet','2025-05-14 00:00:00','non lu'),(30,2,NULL,'Nouveau congé','L\'employé RAZAKA Perline a soumis une demande de congé de 15\r\n             jours0 Heures','Congé','2025-05-15 10:44:00','non lu');

/*Table structure for table `obtenir` */

DROP TABLE IF EXISTS `obtenir`;

CREATE TABLE `obtenir` (
  `matricule_emp` int DEFAULT NULL,
  `id_grade` int DEFAULT NULL,
  `date_obtention_grade` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `obtenir` */

insert  into `obtenir`(matricule_emp,id_grade,date_obtention_grade) values (2,1,'2024-10-11'),(3,1,'2025-03-31'),(4,1,'2025-01-03'),(5,1,'2025-04-03'),(6,1,'2025-04-03'),(7,1,'2025-05-15');

/*Table structure for table `permission` */

DROP TABLE IF EXISTS `permission`;

CREATE TABLE `permission` (
  `id_permission` int NOT NULL AUTO_INCREMENT,
  `reference_perm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `permission` */

insert  into `permission`(id_permission,reference_perm,date_debut_per,date_fin_per,motif_per,duree_jour_per,duree_heure_per,date_demande_per,Statut_permission,matricule_emp,message) values (15,'ds','2025-04-18 15:24:00','2025-04-22 17:24:00',' fds',4,2,'2025-04-18 15:24:00','En attente',2,''),(16,'1','2025-05-10 10:47:00','2025-05-11 10:47:00',' 2',1,2,'2025-05-09 10:47:00','Refusé',2,''),(17,'2','2025-05-15 11:27:00','2025-05-21 11:27:00',' Teste notif per',6,0,'2025-05-14 11:27:00','En attente',2,NULL),(18,'2','2025-05-15 11:27:00','2025-05-21 11:27:00',' Teste notif per',6,0,'2025-05-14 11:27:00','En attente',2,NULL),(31,'g','2025-05-14 11:53:00','2025-05-15 11:53:00',' Erreur',1,0,'2025-05-14 11:53:00','En attente',2,NULL),(27,'3','2025-05-14 11:44:00','2025-05-21 11:44:00',' 123456',7,0,'2025-05-14 11:44:00','En attente',2,NULL),(30,'g','2025-05-14 11:53:00','2025-05-15 11:53:00',' Erreur',1,0,'2025-05-14 11:53:00','En attente',2,NULL),(29,'g','2025-05-14 11:53:00','2025-05-15 11:53:00',' Erreur',1,0,'2025-05-14 11:53:00','En attente',2,NULL),(28,'1','2025-05-14 11:46:00','2025-05-15 11:46:00',' gd',1,0,'2025-05-14 11:46:00','En attente',2,NULL),(32,'g','2025-05-14 11:53:00','2025-05-15 11:53:00',' Erreur',1,0,'2025-05-14 11:53:00','En attente',2,NULL),(33,'g','2025-05-14 11:53:00','2025-05-15 11:53:00',' Erreur',1,0,'2025-05-14 11:53:00','En attente',2,NULL),(34,'g','2025-05-14 11:53:00','2025-05-15 11:53:00',' Erreur',1,0,'2025-05-14 11:53:00','En attente',2,NULL),(35,'g','2025-05-14 11:53:00','2025-05-15 11:53:00',' Erreur',1,0,'2025-05-14 11:53:00','En attente',2,NULL),(36,'g','2025-05-14 11:53:00','2025-05-15 11:53:00',' Erreur',1,0,'2025-05-14 11:53:00','En attente',2,NULL),(37,'s','2025-05-15 11:57:00','2025-05-23 11:57:00',' Erreur',8,0,'2025-05-14 11:57:00','En attente',2,NULL),(38,'1','2025-05-14 12:29:00','2025-05-21 12:29:00',' 123456789',7,0,'2025-05-14 12:29:00','En attente',2,NULL);

/*Table structure for table `responsable` */

DROP TABLE IF EXISTS `responsable`;

CREATE TABLE `responsable` (
  `Matricule_resp` int NOT NULL AUTO_INCREMENT,
  `nom_resp` varchar(255) DEFAULT NULL,
  `prenom_resp` varchar(255) DEFAULT NULL,
  `id_departement` int DEFAULT NULL,
  `id_service` int DEFAULT NULL,
  `mail_resp` varchar(255) DEFAULT NULL,
  `mdp_resp` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`Matricule_resp`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `responsable` */

insert  into `responsable`(Matricule_resp,nom_resp,prenom_resp,id_departement,id_service,mail_resp,mdp_resp) values (1,'Randrianasolo','Solo',1,1,'Randria@gmail.com','123'),(3,'RAKOTO','Kely be',2,2,'Rakoto@gmail.com','123');

/*Table structure for table `service` */

DROP TABLE IF EXISTS `service`;

CREATE TABLE `service` (
  `id_service` int NOT NULL AUTO_INCREMENT,
  `nom_service` varchar(100) DEFAULT NULL,
  `id_departement` int DEFAULT NULL,
  `Matricule_resp` int DEFAULT NULL,
  PRIMARY KEY (`id_service`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `service` */

insert  into `service`(id_service,nom_service,id_departement,Matricule_resp) values (1,'Audit',1,1),(2,'Comptabilité',2,2);

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
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `timesheet` */

insert  into `timesheet`(id_timesheet,tache,date_tache,duree_tache,client,mission,description_tache,note,matricule_emp) values (1,'Immo','2025-05-09',4,'NOVITY',NULL,'booclage','',2),(2,'Immo','2025-05-12',2,'NOVITY',NULL,'rien','',2),(3,'Immo','2025-05-12',8,'NOVITY',NULL,'ttt','',2),(4,'immo','2025-05-12',1,'NOVITY','Booclage','Booclage immo','',2),(5,'immo','2025-05-14',3,'NOVITY','Booclage','Booclage NOVITY','',2),(6,'immo','2025-05-14',1,'NOVITY','Booclage','jglqsjglkdsqjlkg','',2),(7,'immo','2025-05-14',1,'NOVITY','Booclage','ngjd','',2),(8,'immo','2025-05-14',1,'NOVITY','Booclage','hfksdh','',2),(9,'immo','2025-05-14',1,'NOVITY','Booclage','qsdfghjklm','',2),(10,'immo','2025-05-14',1,'NOVITY','Booclage','azerty','',2);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
