# Sequel Pro dump
# Version 1191
# http://code.google.com/p/sequel-pro
#
# Host: localhost (MySQL 5.1.44)
# Database: actgoat
# Generation Time: 2011-11-03 01:17:21 +0000
# ************************************************************

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table feedback
# ------------------------------------------------------------

DROP TABLE IF EXISTS `feedback`;

CREATE TABLE `feedback` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `username` char(20) NOT NULL,
  `uid` int(10) unsigned DEFAULT NULL,
  `cbody` text NOT NULL,
  `cdate` datetime NOT NULL,
  `upvotes` tinyint(3) unsigned DEFAULT '0',
  `flags` tinyint(3) unsigned DEFAULT '0',
  `visible` char(1) DEFAULT '1',
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
INSERT INTO `feedback` (`comment_id`,`parent_id`,`username`,`uid`,`cbody`,`cdate`,`upvotes`,`flags`,`visible`)
VALUES
	(1,0,'martin',1,'I love this!','2011-10-09 03:00:00',3,2,'1'),
	(2,0,'martin',1,'Second','2011-10-09 03:48:00',2,2,'1'),
	(3,0,'james',0,'Third\n','2011-10-09 03:48:30',3,2,'1'),
	(4,2,'hugh',0,'I\'m a reply','2011-10-09 03:49:49',4,2,'1'),
	(6,0,'magic bob',0,'Viridian','2011-10-09 18:05:18',3,2,'1'),
	(7,3,'Injector',0,'After james','2011-10-09 18:17:38',3,1,'1'),
	(8,6,'Philip',0,'Reply to Bob','2011-10-13 01:40:19',3,3,'1'),
	(9,0,'Gwillym',0,'Now that I\\\'m an awesome human being with full threaded sort capabilities (even if they were done hackishly), I\\\'d love to write a quite extensive comment which could shed further light on the formatting possibilities for a comment which spans multiple lines and contains quite a lot of interesting characters.','2011-10-13 03:16:48',1,3,'1'),
	(11,0,'martin',1,'red','2011-10-20 15:30:14',1,0,'1'),
	(12,0,'martin',1,'I\'m an now valid comment','2011-10-20 16:47:53',0,0,'1'),
	(13,0,'Kevin',0,'[@hugh:]\r\nThat would be wonderful.','2011-10-20 22:31:58',0,0,'1');

/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table preferred_titles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `preferred_titles`;

CREATE TABLE `preferred_titles` (
  `film_id` int(10) unsigned NOT NULL,
  `pref_title` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`film_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `preferred_titles` WRITE;
/*!40000 ALTER TABLE `preferred_titles` DISABLE KEYS */;
INSERT INTO `preferred_titles` (`film_id`,`pref_title`)
VALUES
	(104,'Lola Rennt'),
	(103,'Taxi Driven');

/*!40000 ALTER TABLE `preferred_titles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table searches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `searches`;

CREATE TABLE `searches` (
  `sid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `terms` varchar(40) NOT NULL,
  `num_results` int(10) unsigned DEFAULT NULL,
  `result_urls` text,
  `clicked_link` varchar(20) DEFAULT NULL,
  `from_dropdown` char(1) DEFAULT NULL,
  `sdate` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

LOCK TABLES `searches` WRITE;
/*!40000 ALTER TABLE `searches` DISABLE KEYS */;
INSERT INTO `searches` (`sid`,`terms`,`num_results`,`result_urls`,`clicked_link`,`from_dropdown`,`sdate`)
VALUES
	(1,'john',NULL,'s:14:\"Johnny English\";','/film/9486','1',NULL),
	(2,'frank',NULL,'s:15:\"Frank and Jesse\";','http://oscarworthy.l','0',NULL),
	(4,'stev',NULL,'s:15:\"The Good German\";','/film/182','0',NULL),
	(5,'mesh',NULL,'s:9:\"Adam Mesh\";','/person/215017','1','2011-10-27 01:47:36'),
	(6,'mesh',NULL,'s:9:\"Adam Mesh\";','/person/215017','1','2011-10-27 01:48:09'),
	(7,'gil',NULL,'s:5:\"Gilda\";','/film/3767','1','2011-10-27 01:48:58'),
	(8,'tony',NULL,'s:13:\"Tony Takitani\";','/film/29269','1','2011-10-27 13:55:05'),
	(9,'derailed',NULL,'s:8:\"Derailed\";','/film/8999','0','2011-10-27 14:20:45'),
	(10,'gary',NULL,'s:11:\"Gary Oldman\";','/person/64','1','2011-10-28 01:54:04');

/*!40000 ALTER TABLE `searches` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` enum('member','moderator','admin') DEFAULT 'member',
  `username` varchar(20) NOT NULL,
  `pass_salt` char(73) NOT NULL,
  `realname` varchar(40) NOT NULL,
  `email` varchar(80) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `activation` varchar(16) NOT NULL,
  `register_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`uid`,`role`,`username`,`pass_salt`,`realname`,`email`,`last_login`,`activation`,`register_date`)
VALUES
	(1,'admin','martin','7c0971cf7d392fc342adf295dce548962853ea2c10f90fe3654a7651672d9cf9|e052c349','Martin Nicholson','me@here.com','2011-09-29 17:06:01','ok',NULL),
	(2,'member','qwerty','07d898111ed49cef75490037b1331eaca78c55f7b6bbf30c936698e3e4df5201|c0bcd520','Q-Bert','qb@fb.com','2011-10-01 00:50:26','38cd8d66f71590f3',NULL),
	(5,'member','martinimus69','d13bfa6f4b99e59252d19b85a966e0fd639c33dd68b283d737cef3bdbf5e5173|63453a79','Martin The Great','me@hereio.com','2011-10-09 22:40:41','0c374cdceb2044e5','2011-10-09 22:40:41'),
	(6,'member','mariska','7b9abed6c302cb10660d4f687635913192bbd825232f5672ba42b10d947c32a9|923519e7','Mariska Hargitay','mariska@hargit.ay','2011-10-09 22:49:14','d94ce3dd047f9b20','2011-10-09 22:49:14'),
	(9,'member','Frankie','bbf1db0aaafc0dfa12bc9fef4b096250b6ea412ae5365442cdd878d2c5607a10|b709e142','Frankie Hollywood','martin@life-on-mart.org','2011-10-17 16:43:08','1c8196b35dff848f','2011-10-17 16:43:08'),
	(10,'member','Popo','c6151f474c410c57642ba394f7afda9306b5fc75b2e41111bc48e69c44b9cde6|8a55e6c4','Po Poe','po@po.po.po.po.po','2011-10-18 14:50:36','7f711d4cf7be881e','2011-10-18 14:50:36');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table votes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `votes`;

CREATE TABLE `votes` (
  `vote_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `role_hash` char(8) NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `rating_date` datetime DEFAULT NULL,
  PRIMARY KEY (`vote_id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

LOCK TABLES `votes` WRITE;
/*!40000 ALTER TABLE `votes` DISABLE KEYS */;
INSERT INTO `votes` (`vote_id`,`uid`,`role_hash`,`rating`,`rating_date`)
VALUES
	(1,1,'7f54368d',4,NULL),
	(2,1,'f1c5775a',4,'2011-10-07 18:52:30'),
	(3,1,'6ba68f2c',2,'2011-10-07 18:52:30'),
	(4,1,'1ebb17fb',1,'2011-10-07 18:52:30'),
	(5,1,'4f5f76ff',3,'2011-10-07 22:29:57'),
	(6,1,'fb977687',1,'2011-10-07 22:29:59'),
	(7,1,'246048a4',5,'2011-10-07 22:30:00'),
	(8,1,'6868292c',3,'2011-10-07 22:30:02'),
	(9,1,'4f5f76ff',3,'2011-10-07 23:07:42'),
	(10,1,'fb977687',5,'2011-10-07 23:07:46'),
	(11,1,'246048a4',2,'2011-10-07 23:07:54'),
	(12,1,'6868292c',2,'2011-10-07 23:07:59'),
	(13,1,'fb977687',4,'2011-10-07 23:11:50'),
	(14,1,'4f5f76ff',5,'2011-10-07 23:11:51'),
	(15,1,'246048a4',3,'2011-10-07 23:11:53'),
	(16,1,'4f5f76ff',4,'2011-10-07 23:44:31'),
	(17,1,'df443aa0',5,'2011-10-11 02:15:11'),
	(18,1,'c8ac944d',5,'2011-10-11 02:17:50'),
	(19,1,'bc40b6d2',2,'2011-10-11 02:18:48'),
	(20,1,'2a5a3921',2,'2011-10-11 02:21:35'),
	(21,1,'0d3eca47',4,'2011-10-11 02:21:53'),
	(22,1,'2dddda05',4,'2011-10-11 02:24:37'),
	(23,1,'d7ee8413',2,'2011-10-11 02:27:10'),
	(24,1,'701dc5e8',5,'2011-10-11 16:15:52'),
	(25,1,'40243e4c',2,'2011-10-11 16:15:54'),
	(26,1,'5a86d16a',4,'2011-10-11 16:15:56'),
	(27,1,'d710ebfa',2,'2011-10-11 16:15:58'),
	(28,1,'ac578baf',4,'2011-10-11 16:16:07'),
	(29,5,'d710ebfa',1,'2011-10-11 16:17:05'),
	(30,5,'ac578baf',5,'2011-10-11 16:17:09'),
	(31,5,'5a86d16a',5,'2011-10-11 16:17:12'),
	(32,5,'40243e4c',1,'2011-10-11 16:17:13'),
	(33,5,'701dc5e8',0,'2011-10-11 16:17:15'),
	(34,5,'b13f3927',20,'2011-10-11 18:14:18'),
	(35,5,'a794e648',6,'2011-10-11 18:18:50'),
	(36,5,'a2f42815',8,'2011-10-11 18:18:54'),
	(37,1,'a794e648',2,'2011-10-11 19:40:36'),
	(38,1,'a2f42815',2,'2011-10-11 19:40:38'),
	(39,1,'f1c5775a',6,'2011-10-19 15:14:15'),
	(40,1,'99081c5b',6,'2011-10-19 17:24:41'),
	(41,1,'57b600fc',10,'2011-10-19 17:26:49'),
	(42,1,'440cabbf',2,'2011-10-20 13:56:50'),
	(43,1,'faf59a94',4,'2011-10-20 13:56:55'),
	(44,1,'6150931b',6,'2011-10-20 13:56:59'),
	(45,1,'c2e0a78e',4,'2011-10-20 13:57:01'),
	(46,1,'d0356025',2,'2011-10-20 13:57:03');

/*!40000 ALTER TABLE `votes` ENABLE KEYS */;
UNLOCK TABLES;





/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
