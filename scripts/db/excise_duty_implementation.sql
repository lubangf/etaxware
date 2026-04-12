
select * from etaxware.tbltaxrates t 


select * from etaxware.tblexcisedutylist t2 where uraid = '120454851320307457';  

select * from etaxware.tblexcisedutydetailslist t   


select * from etaxware.tblorganisations t 

select * from etaxware.tblapikeys t2 


select * from etaxware.tblrateunits t2 

select * from etaxware.tblproductdetails t2 



alter table etaxware.tblexcisedutylist add column isLeafNode tinytext after rateText;

alter table etaxware.tblexcisedutylist add column parentCode varchar(200) after isLeafNode;

drop table etaxware.tblexcisedutydetailslist;

-- etaxware.tblexcisedutydetailslist definition

CREATE TABLE etaxware.tblexcisedutydetailslist (
  `id` int NOT NULL AUTO_INCREMENT,
  `erpid` varchar(20) DEFAULT NULL,
  `erpcode` varchar(20) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `currency` int DEFAULT NULL,
  `exciseDutyId` varchar(20) DEFAULT NULL,
  `uraid` varchar(20) DEFAULT NULL,
  `rate` varchar(200) DEFAULT NULL,
  `type` int DEFAULT NULL,
  `unit` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



alter table etaxware.tblproductdetails add column exciseDutyCode varchar(20) after excisedutylist;
alter table etaxware.tblproductdetails add column exciseDutyName varchar(100) after exciseDutyCode;
alter table etaxware.tblproductdetails add column exciseRate varchar(200) after exciseDutyName;
alter table etaxware.tblproductdetails add column haveCustomsUnit int after hsName;

-- etaxware.tblproductdetails definition

CREATE TABLE `tblproductdetails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uraproductidentifier` varchar(20) DEFAULT NULL,
  `erpid` varchar(20) DEFAULT NULL,
  `erpcode` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Goodsname cannot be empty, cannot be greater than 50 characters',
  `code` varchar(50) NOT NULL COMMENT 'Goodscode cannot be empty, cannot be greater than 50 characters',
  `measureunit` varchar(50) DEFAULT NULL COMMENT 'T115 rateUnit',
  `unitprice` decimal(20,8) DEFAULT '0.00000000' COMMENT 'The number of integer digits does not exceed 12 digits and the number of decimal places does not exceed 8 digits',
  `currency` int DEFAULT NULL COMMENT 'T115 currencyType',
  `commoditycategorycode` varchar(20) DEFAULT NULL,
  `hasexcisetax` int DEFAULT '102' COMMENT '101:Yes 102:No',
  `description` varchar(1024) DEFAULT NULL,
  `stockprewarning` decimal(20,8) DEFAULT '0.00000000' COMMENT 'The number of integer digits does not exceed 12 digits and the number of decimal places does not exceed 8 digits，can be zero',
  `piecemeasureunit` varchar(50) DEFAULT NULL COMMENT 'if havePieceUnit is 102, pieceMeasureUnit must be empty. If havePieceUnit is 101, pieceMeasureUnit cannot be empty. T115 rateUnit',
  `havepieceunit` int DEFAULT '102' COMMENT 'if haveExciseTax is 102, havePieceUnit must be empty. 101:Yes 102:No',
  `pieceunitprice` decimal(20,8) DEFAULT NULL COMMENT 'if havePieceUnit is 102 pieceUnitPrice must be empty. if havePieceUnit is 101 pieceUnitPrice cannot be empty. The number of integer digits does not exceed 12 digits and the number of decimal places does not exceed 8 digits',
  `packagescaledvalue` decimal(20,8) DEFAULT NULL COMMENT 'if havePieceUnit is 102 packageScaledValue must be empty. if havePieceUnit is 101 packageScaledValue cannot be empty. The number of integer digits does not exceed 12 digits and the number of decimal places does not exceed 8 digits',
  `piecescaledvalue` decimal(20,8) DEFAULT NULL COMMENT 'if havePieceUnit is 102 pieceScaledValue must be empty. if havePieceUnit is 101 pieceScaledValue cannot be empty. The number of integer digits does not exceed 12 digits and the number of decimal places does not exceed 8 digits',
  `excisedutylist` varchar(20) DEFAULT NULL COMMENT 'if haveExciseTax is 102 exciseDutyCode must be empty',
  `uraquantity` decimal(20,8) DEFAULT '0.00000000',
  `erpquantity` decimal(20,8) DEFAULT '0.00000000',
  `purchaseprice` decimal(20,8) DEFAULT NULL,
  `stockintype` varchar(20) DEFAULT NULL,
  `haveotherunit` int DEFAULT '102',
  `isexempt` int DEFAULT '102',
  `iszerorated` int DEFAULT '102',
  `taxrate` decimal(20,8) DEFAULT '0.18000000',
  `statuscode` int DEFAULT '101',
  `source` int DEFAULT '102',
  `exclusion` int DEFAULT '2',
  `pack` decimal(20,8) DEFAULT NULL,
  `stick` decimal(20,8) DEFAULT NULL,
  `serviceMark` int DEFAULT '102',
  `goodsTypeCode` int DEFAULT '101' COMMENT '101: Non-fuel Goods, 102: Fuel',
  `remarks` varchar(4000) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  `hsCode` varchar(200) DEFAULT NULL COMMENT 'Hs Code',
  `hsName` varchar(200) DEFAULT NULL COMMENT 'Hs Name',
  `customsmeasureunit` varchar(50) DEFAULT NULL,
  `customsunitprice` decimal(20,8) DEFAULT NULL,
  `packagescaledvaluecustoms` decimal(20,8) DEFAULT NULL,
  `customsscaledvalue` decimal(20,8) DEFAULT NULL,
  `weight` decimal(20,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_UNIQUE` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1264 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;