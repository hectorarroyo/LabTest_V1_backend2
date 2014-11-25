-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.0.24a-community-nt


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema foodanddrinks
--

CREATE DATABASE IF NOT EXISTS FoodAndDrinks;
USE foodanddrinks;

--
-- Definition of table `account`
--

DROP TABLE IF EXISTS `Account`;
CREATE TABLE `Account` (
  `AccountId` int(10) unsigned NOT NULL auto_increment,
  `Login` varchar(45) NOT NULL default '',
  `Password` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`AccountId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `account`
--

/*!40000 ALTER TABLE `account` DISABLE KEYS */;
/*!40000 ALTER TABLE `account` ENABLE KEYS */;


--
-- Definition of table `order`
--

DROP TABLE IF EXISTS `Order`;
CREATE TABLE `Order` (
  `OrderId` int(10) unsigned NOT NULL auto_increment,
  `AccountId` int(10) unsigned NOT NULL default '0',
  `OrderDate` datetime default NULL,
  `DeliveryDate` datetime default NULL,
  `DeliveryAddress` varchar(150) default NULL,
  `Status` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`OrderId`),
  KEY `FK_Order_Account` (`AccountId`),
  CONSTRAINT `FK_Order_Account` FOREIGN KEY (`AccountId`) REFERENCES `account` (`AccountId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `order`
--

/*!40000 ALTER TABLE `order` DISABLE KEYS */;
/*!40000 ALTER TABLE `order` ENABLE KEYS */;


--
-- Definition of table `orderline`
--

DROP TABLE IF EXISTS `OrderLine`;
CREATE TABLE `OrderLine` (
  `OrderId` int(10) unsigned NOT NULL auto_increment,
  `ProductId` int(10) unsigned NOT NULL default '0',
  `Quantity` smallint(5) unsigned NOT NULL default '0',
  `Price` double unsigned NOT NULL default '0',
  PRIMARY KEY  (`OrderId`,`ProductId`),
  KEY `FK_OrderLine_Product` (`ProductId`),
  CONSTRAINT `FK_OrderLine_Product` FOREIGN KEY (`ProductId`) REFERENCES `product` (`ProductId`),
  CONSTRAINT `FK_OrderLine_Order` FOREIGN KEY (`OrderId`) REFERENCES `order` (`OrderId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orderline`
--

/*!40000 ALTER TABLE `orderline` DISABLE KEYS */;
/*!40000 ALTER TABLE `orderline` ENABLE KEYS */;


--
-- Definition of table `product`
--

DROP TABLE IF EXISTS `Product`;
CREATE TABLE `Product` (
  `ProductId` int(10) unsigned zerofill NOT NULL auto_increment,
  `Name` varchar(100) NOT NULL default '',
  `Price` double unsigned NOT NULL default '0',
  `PictureFileName` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`ProductId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `product`
--

/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `Product` (`ProductId`,`Name`,`Price`,`PictureFileName`) VALUES
 (0000000001,'Bananas',1,'bananas.jpg');
INSERT INTO `Product` (`ProductId`,`Name`,`Price`,`PictureFileName`) VALUES
 (0000000002,'Grapes',5,'grapes.jpg');
INSERT INTO `Product` (`ProductId`,`Name`,`Price`,`PictureFileName`) VALUES
 (0000000003,'Lettuce',8,'green_leaf_lettuce.jpg');
INSERT INTO `Product` (`ProductId`,`Name`,`Price`,`PictureFileName`) VALUES
 (0000000004,'Oranges',1,'orange.jpg');
INSERT INTO `Product` (`ProductId`,`Name`,`Price`,`PictureFileName`) VALUES
 (0000000005,'Strawberries',1,'strawberries.jpg');
INSERT INTO `Product` (`ProductId`,`Name`,`Price`,`PictureFileName`) VALUES
 (0000000006,'Apples',8,'apples.jpg');
 /*!40000 ALTER TABLE `product` ENABLE KEYS */;

/*!40000 ALTER TABLE `account` DISABLE KEYS */;
INSERT INTO `Account` (`AccountId`,`Login`,`Password`) VALUES
 (0000000001,'Joe Grocer','goshopping');
 /*!40000 ALTER TABLE `account` ENABLE KEYS */;


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;