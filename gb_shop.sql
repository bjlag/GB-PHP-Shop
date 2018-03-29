-- MySQL dump 10.13  Distrib 5.7.17, for macos10.12 (x86_64)
--
-- Host: localhost    Database: db_shop
-- ------------------------------------------------------
-- Server version	5.6.38

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
-- Table structure for table `basket`
--

DROP TABLE IF EXISTS `basket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `basket` (
  `id_basket` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_goods` int(11) NOT NULL,
  `count` tinyint(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_basket`),
  KEY `fk_basket_catalog_goods_idx` (`id_goods`),
  KEY `fk_basket_user_idx` (`id_user`),
  CONSTRAINT `fk_basket_catalog_goods` FOREIGN KEY (`id_goods`) REFERENCES `catalog_goods` (`id_goods`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_basket_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `basket`
--

LOCK TABLES `basket` WRITE;
/*!40000 ALTER TABLE `basket` DISABLE KEYS */;
/*!40000 ALTER TABLE `basket` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id_brands` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id_brands`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (1,'Salomon'),(2,'Nike');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_goods`
--

DROP TABLE IF EXISTS `catalog_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_goods` (
  `id_goods` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `id_brands` int(11) DEFAULT NULL,
  `image_preview` varchar(128) DEFAULT NULL,
  `image_full` varchar(128) DEFAULT NULL,
  `text_preview` text,
  `text_full` text,
  `rating` tinyint(2) DEFAULT '0',
  `price` decimal(8,2) DEFAULT '0.00',
  `discount` float DEFAULT '0',
  PRIMARY KEY (`id_goods`),
  KEY `fk_id_brands_idx` (`id_brands`),
  CONSTRAINT `fk_id_brands` FOREIGN KEY (`id_brands`) REFERENCES `brands` (`id_brands`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_goods`
--

LOCK TABLES `catalog_goods` WRITE;
/*!40000 ALTER TABLE `catalog_goods` DISABLE KEYS */;
INSERT INTO `catalog_goods` VALUES (1,'Кроссовки Salomon Elios 2 GTX M',1,'/upload/catalog/resize/16.jpg','/upload/catalog/16.jpg','Кроссовки Salomon Elios 2 GTX M, с мембраной Gore-Tex - это новая модель для горного приключения в любое время года, обеспечивающая легкость, защиту и непревзойденное удобство.','Кроссовки Salomon Elios 2 GTX M, с мембраной Gore-Tex - это новая модель для горного приключения в любое время года, обеспечивающая легкость, защиту и непревзойденное удобство. Верх выполнен из водонепроницаемого текстиля с отделкой из натуральной кожи. Также предусмотрена защитная накладка в области задника. Внутренняя отделка - из плотного непромокаемого материала. Удобная шнуровка и вшитый язычок гарантируют комфорт и надежно фиксируют модель на стопе. Стелька OrthoLite, выполненная из EVA пластика, улучшает амортизацию. Резиновая подошва с протектором против скольжения для оптимального сцепления. В таких кроссовках вашим ногам будет сухо и уютно. Они подчеркнут ваш стиль и индивидуальность!',3,9300.00,15),(2,'Кроссовки FREE TRAINER 3.0 V3',2,'/upload/catalog/resize/17.jpg','/upload/catalog/17.jpg','Кроссовки Nike Free Trainer 3.0 V3 обеспечивают комфорт без ущерба для функциональности.','Кроссовки Nike Free Trainer 3.0 V3 обеспечивают комфорт без ущерба для функциональности. Самая легкая и гибкая тренировочная обувь в своей категории. Конструкция дает стабильность при приземлении, гибкость при выпрыгивании и скорость при движении в любую сторону.',4,6490.00,0);
/*!40000 ALTER TABLE `catalog_goods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_goods_size`
--

DROP TABLE IF EXISTS `catalog_goods_size`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_goods_size` (
  `id_goods` int(11) NOT NULL,
  `id_size` int(11) NOT NULL,
  PRIMARY KEY (`id_goods`,`id_size`),
  KEY `fk_size_idx` (`id_size`),
  CONSTRAINT `fk_goods` FOREIGN KEY (`id_goods`) REFERENCES `catalog_goods` (`id_goods`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_size` FOREIGN KEY (`id_size`) REFERENCES `catalog_size` (`id_size`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_goods_size`
--

LOCK TABLES `catalog_goods_size` WRITE;
/*!40000 ALTER TABLE `catalog_goods_size` DISABLE KEYS */;
INSERT INTO `catalog_goods_size` VALUES (1,1),(1,2),(2,2),(1,3),(2,3);
/*!40000 ALTER TABLE `catalog_goods_size` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_size`
--

DROP TABLE IF EXISTS `catalog_size`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_size` (
  `id_size` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id_size`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_size`
--

LOCK TABLES `catalog_size` WRITE;
/*!40000 ALTER TABLE `catalog_size` DISABLE KEYS */;
INSERT INTO `catalog_size` VALUES (1,'41'),(2,'42'),(3,'43'),(4,'44'),(5,'45'),(6,'46');
/*!40000 ALTER TABLE `catalog_size` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `name` varchar(75) NOT NULL,
  `email` varchar(45) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `city` varchar(45) NOT NULL,
  `address_delivery` varchar(100) NOT NULL,
  `method_delivery` varchar(45) NOT NULL,
  `delivery_cost` decimal(8,2) NOT NULL DEFAULT '0.00',
  `method_payment` varchar(45) NOT NULL,
  `comment` text,
  `date_create` datetime NOT NULL,
  PRIMARY KEY (`id_order`),
  UNIQUE KEY `id_order_UNIQUE` (`id_order`),
  KEY `fk_order_user_idx` (`id_user`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (15,9,'Влад','vlad@ya.ru','7 911 222-33-44','Санкт-Петербург','ул. Рижская, д. 1','pickup',0.00,'cash','После 12:00','2018-03-28 11:04:10'),(16,9,'Иван','test@test.ru','7 921 333-22-45','Санкт-Петербург','ул. Рижская, д. 1','pickup',0.00,'card','Нужна сдача с 25000 руб.','2018-03-28 11:10:57'),(18,9,'Алексей','alex@alex.ru','7 999 444-55-66','Санкт-Петербург','ул. Рижская, д. 1','pickup',0.00,'card','','2018-03-28 12:30:00'),(19,9,'Алексей','info@webstride.ru','11111','Санкт-Петербург','ул. Рижская, д. 1','courier',200.00,'card','','2018-03-28 16:42:43'),(20,11,'Иван','ivan@vs.ru','7 999 555-45-23','Москва','ул. Правды, д. 11, кв. 45','courier',200.00,'cash','Готов принять заказ в любое время','2018-03-28 18:38:12'),(22,9,'Влад','info@webstride.ru','11111','Санкт-Петербург','ул. Рижская, д. 1','pickup',0.00,'cash','','2018-03-29 13:52:02');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders_goods`
--

DROP TABLE IF EXISTS `orders_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders_goods` (
  `id_order` int(11) NOT NULL,
  `id_goods` int(11) NOT NULL,
  `count` tinyint(255) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `discount_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_order`,`id_goods`),
  KEY `fk_order_goods_catalog_goods_idx` (`id_goods`),
  CONSTRAINT `fk_orders_goods_catalog_goods` FOREIGN KEY (`id_goods`) REFERENCES `catalog_goods` (`id_goods`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_orders_goods_orders` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders_goods`
--

LOCK TABLES `orders_goods` WRITE;
/*!40000 ALTER TABLE `orders_goods` DISABLE KEYS */;
INSERT INTO `orders_goods` VALUES (15,1,1,7905.00,1395.00),(16,1,2,7905.00,1395.00),(16,2,1,6490.00,0.00),(18,2,1,6490.00,0.00),(19,1,1,7905.00,1395.00),(20,1,1,7905.00,1395.00),(22,1,1,7905.00,1395.00);
/*!40000 ALTER TABLE `orders_goods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` char(60) NOT NULL,
  `cookie` char(10) DEFAULT NULL,
  `name` varchar(75) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL COMMENT '79112734003',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username_UNIQUE` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (9,'admin','$2y$08$YWY2NGEyYTBiZDdlMTFkYOykrCihwdV7DnUceS3OYtS58Oqlg3mj.','bF#>dsg5ME','Администратор','admin@admin.ru','7 911 222-33-44'),(10,'user','$2y$08$YmI5NmJjNGFjY2I3MjI4ZeNVZS965Z2tzPaP1xGMhX0fnHc3fpO82',NULL,'Пользователь',NULL,NULL),(11,'manager','$2y$08$YTM0MWU2MDFlOGQ3OTgxYulfZTxlBQq17cdU6mRJgXXwmiIgFGDi6',NULL,'Менеджер','manager@m.ru','7 333 666-55-66');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-03-29 13:52:55
