/*
Navicat MySQL Data Transfer

Source Server         : 114.215.223.123
Source Server Version : 50629
Source Host           : 114.215.223.123:3306
Source Database       : OAcrm

Target Server Type    : MYSQL
Target Server Version : 50629
File Encoding         : 65001

Date: 2018-11-29 10:42:22
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for organization
-- ----------------------------
DROP TABLE IF EXISTS `organization`;
CREATE TABLE `organization` (
  `organization_id` varchar(60) NOT NULL,
  `parent_id` varchar(60) NOT NULL DEFAULT '0' COMMENT '上级id',
  `organization_name` varchar(30) DEFAULT NULL COMMENT '角色名称',
  `organization_description` varchar(255) DEFAULT NULL COMMENT '角色描述',
  `role_permission_level` tinyint(2) DEFAULT NULL COMMENT '角色权限等级',
  `valid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1有效0无效',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`organization_id`),
  UNIQUE KEY `organization_id` (`organization_id`) USING BTREE,
  UNIQUE KEY `organization_name` (`parent_id`,`organization_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='组织机构表';

-- ----------------------------
-- Records of organization
-- ----------------------------
INSERT INTO `organization` VALUES ('2e6ccb2ddc85aaa153f2a7b10b06a9dc', 'd41d8cd98f00b204e9800998ecf8427e', '分公司2', '', null, '1', '2018-10-07 20:02:38', '2018-10-08 21:32:04');
INSERT INTO `organization` VALUES ('381c5ad7f49ad368ef205c323b414af6', '713da2a784361e2ea8a28ef49d68431a', '测试部', '', null, '1', '2018-10-16 21:48:45', '2018-10-16 21:48:45');
INSERT INTO `organization` VALUES ('41b5f23a21d61d86b78274344fcbc49b', 'cfcd208495d565ef66e7dff9f98764da', '成都分公司', '成都的娃儿出来耍撒', null, '1', '2018-09-28 17:50:02', '2018-09-28 17:50:02');
INSERT INTO `organization` VALUES ('45c48cce2e2d7fbdea1afc51c7c6ad26', 'cfcd208495d565ef66e7dff9f98764da', '业务部2', '', null, '1', '2018-09-25 10:40:23', '2018-09-25 10:40:23');
INSERT INTO `organization` VALUES ('597f946ed3156f636af31baf7006f48b', 'd41d8cd98f00b204e9800998ecf8427e', '重庆分公司', '', null, '1', '2018-10-07 15:13:38', '2018-10-07 17:08:32');
INSERT INTO `organization` VALUES ('6b5d3897926ba0b7ffc745aeb5a26b73', 'cfcd208495d565ef66e7dff9f98764da1', '业务部2', '', null, '1', '2018-10-02 15:32:20', '2018-10-02 15:32:20');
INSERT INTO `organization` VALUES ('713da2a784361e2ea8a28ef49d68431a', '2e6ccb2ddc85aaa153f2a7b10b06a9dc', '分工', '1', null, '1', '2018-10-08 22:02:46', '2018-10-08 22:03:16');
INSERT INTO `organization` VALUES ('71e457e7a403769e7e8f4a04d9e5178a', 'd41d8cd98f00b204e9800998ecf8427e', '测试公司', '哈哈', null, '1', '2018-10-17 15:34:08', '2018-10-17 15:35:09');
INSERT INTO `organization` VALUES ('9ad7357534beb28e29ef831ea5777d44', '2e6ccb2ddc85aaa153f2a7b10b06a9dc', '分公', '123', null, '1', '2018-10-07 20:02:46', '2018-10-08 21:52:45');
INSERT INTO `organization` VALUES ('afe53b7a6f30e9e5e586d79cb58ef589', '381c5ad7f49ad368ef205c323b414af6', '测试员', '', null, '1', '2018-10-16 21:48:58', '2018-10-16 21:48:58');
INSERT INTO `organization` VALUES ('b6d767d2f8ed5d21a44b0e5886680cb9', 'cfcd208495d565ef66e7dff9f98764da', '业务部1', '', null, '1', '2018-09-25 10:39:08', '2018-10-07 18:54:30');
INSERT INTO `organization` VALUES ('c4ca4238a0b923820dcc509a6f75849b', 'd41d8cd98f00b204e9800998ecf8427e', '北京分公司', '虽然不知道为什么,就是想写个签名', null, '1', '2018-09-25 10:29:12', '2018-10-07 19:49:16');
INSERT INTO `organization` VALUES ('cfcd208495d565ef66e7dff9f98764da', 'd41d8cd98f00b204e9800998ecf8427e', '成都分公司', '老虎不在家,猴子称霸王', null, '0', '2018-09-25 10:26:20', '2018-10-15 11:52:32');
INSERT INTO `organization` VALUES ('d41d8cd98f00b204e9800998ecf8427e', '0', '总公司', '2222', null, '1', '2018-09-21 16:37:12', '2018-10-07 16:31:30');
INSERT INTO `organization` VALUES ('fa8fc4e3884a66bbdc5942e9b92aa95f', '71e457e7a403769e7e8f4a04d9e5178a', '测试部门', '啊啊啊23', null, '1', '2018-10-17 15:35:42', '2018-10-17 17:34:33');
