/*
Navicat MySQL Data Transfer

Source Server         : 192.168.1.198_3306
Source Server Version : 50505
Source Host           : 192.168.1.198:3306
Source Database       : wx

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-06-30 11:34:29
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `wxstate`
-- ----------------------------
DROP TABLE IF EXISTS `wxstate`;
CREATE TABLE `wxstate` (
  `fromusername` varchar(100) NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fromusername`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


