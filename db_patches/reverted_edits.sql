-- MySQL dump 10.13  Distrib 5.1.63, for apple-darwin10.3.0 (i386)
--
-- Host: localhost    Database: wiki
-- ------------------------------------------------------
-- Server version	5.1.63

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `reverted_edits`
--

DROP TABLE IF EXISTS `reverted_edits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE  /*_*/`reverted_edits` (
  `re_reversion_id` int(11) NOT NULL AUTO_INCREMENT,
  `re_undid_rev_id` int(11) NOT NULL,
  `re_curr_rev_id` int(11) NOT NULL,
  `re_page` int(11) NOT NULL,
  `re_title` varbinary(255),
  `re_undid_rev_text_id` int(11) NOT NULL,
  `re_curr_rev_text_id` int(11) NOT NULL,
  `re_comment` tinyblob,
  `re_undid_user` int(11) NOT NULL,
  `re_undid_user_text` text,
  `re_curr_user` int(11) NOT NULL,
  `re_curr_user_text` text,
  `re_user` int(11) NOT NULL,
  `re_user_text` text,
  `re_action` varbinary(8),
  `re_spam` bool DEFAULT NULL,
  `re_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`re_reversion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

CREATE INDEX  /*i*/reverted_edits_undid_rev_id ON /*_*/reverted_edits(re_undid_rev_id);
CREATE INDEX  /*i*/reverted_edits_curr_rev_id ON /*_*/reverted_edits(re_curr_rev_id);
--
-- Dumping data for table `reverted_edits`
--

LOCK TABLES `reverted_edits` WRITE;
/*!40000 ALTER TABLE `reverted_edits` DISABLE KEYS */;
/*!40000 ALTER TABLE `reverted_edits` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-07-12 15:38:23
