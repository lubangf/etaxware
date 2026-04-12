--  Update the etaxware version
UPDATE etaxware.tblsettings
	SET value='5.0.0'
	WHERE id=14;

  
/*Log in*/

ALTER TABLE etaxware.tblorganisations ADD hsCodeVersion varchar(100) NULL COMMENT 'HS Code version';

ALTER TABLE etaxware.tblorganisations ADD issueDebitNote varchar(1) NULL COMMENT 'issueDebit Note';

ALTER TABLE etaxware.tblorganisations ADD qrCodeURL varchar(200) NULL COMMENT 'qrCode URL';

select * from etaxware.tblorganisations;

/*Invoice details*/
ALTER TABLE etaxware.tblcreditmemos ADD preGrossAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total gross amount';

ALTER TABLE etaxware.tblcreditmemos ADD preTaxAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total tax amount';

ALTER TABLE etaxware.tblcreditmemos ADD preNetAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total net amount';

ALTER TABLE etaxware.tblcreditmemos ADD deliveryTermsCode varchar(200) NULL COMMENT 'delivery Terms Code';

ALTER TABLE etaxware.tblcreditmemos ADD branchName varchar(200) NULL COMMENT 'branch Name';

ALTER TABLE etaxware.tblcreditmemos ADD sadNumber varchar(200) NULL COMMENT 'sad Number';

ALTER TABLE etaxware.tblcreditmemos ADD office varchar(200) NULL COMMENT 'office';

ALTER TABLE etaxware.tblcreditmemos ADD cif varchar(200) NULL COMMENT 'cif';

ALTER TABLE etaxware.tblcreditmemos ADD wareHouseNumber varchar(200) NULL COMMENT 'wareHouse Number';

ALTER TABLE etaxware.tblcreditmemos ADD wareHouseName varchar(200) NULL COMMENT 'wareHouse Name';

ALTER TABLE etaxware.tblcreditmemos ADD destinationCountry varchar(200) NULL COMMENT 'destination Country';

ALTER TABLE etaxware.tblcreditmemos ADD originCountry varchar(200) NULL COMMENT 'origin Country';

ALTER TABLE etaxware.tblcreditmemos ADD importExportFlag varchar(200) NULL COMMENT 'import Export Flag';

ALTER TABLE etaxware.tblcreditmemos ADD confirmStatus varchar(1) NULL COMMENT 'confirm Status';

ALTER TABLE etaxware.tblcreditmemos ADD valuationMethod varchar(1) NULL COMMENT 'valuation Method';

ALTER TABLE etaxware.tblcreditmemos ADD prn varchar(200) NULL COMMENT 'prn';

ALTER TABLE etaxware.tblcreditmemos ADD totalWeight decimal(20,8) NULL COMMENT 'total Weight';

ALTER TABLE etaxware.tblcreditmemos ADD vatProjectId varchar(300) NULL;

ALTER TABLE etaxware.tblcreditmemos ADD vatProjectName varchar(300) NULL;

select * from etaxware.tblcreditmemos;


ALTER TABLE etaxware.tblcreditnotes ADD preGrossAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total gross amount';

ALTER TABLE etaxware.tblcreditnotes ADD preTaxAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total tax amount';

ALTER TABLE etaxware.tblcreditnotes ADD preNetAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total net amount';

ALTER TABLE etaxware.tblcreditnotes ADD deliveryTermsCode varchar(200) NULL COMMENT 'delivery Terms Code';

ALTER TABLE etaxware.tblcreditnotes ADD branchName varchar(200) NULL COMMENT 'branch Name';

ALTER TABLE etaxware.tblcreditnotes ADD sadNumber varchar(200) NULL COMMENT 'sad Number';

ALTER TABLE etaxware.tblcreditnotes ADD office varchar(200) NULL COMMENT 'office';

ALTER TABLE etaxware.tblcreditnotes ADD cif varchar(200) NULL COMMENT 'cif';

ALTER TABLE etaxware.tblcreditnotes ADD wareHouseNumber varchar(200) NULL COMMENT 'wareHouse Number';

ALTER TABLE etaxware.tblcreditnotes ADD wareHouseName varchar(200) NULL COMMENT 'wareHouse Name';

ALTER TABLE etaxware.tblcreditnotes ADD destinationCountry varchar(200) NULL COMMENT 'destination Country';

ALTER TABLE etaxware.tblcreditnotes ADD originCountry varchar(200) NULL COMMENT 'origin Country';

ALTER TABLE etaxware.tblcreditnotes ADD importExportFlag varchar(200) NULL COMMENT 'import Export Flag';

ALTER TABLE etaxware.tblcreditnotes ADD confirmStatus varchar(1) NULL COMMENT 'confirm Status';

ALTER TABLE etaxware.tblcreditnotes ADD valuationMethod varchar(1) NULL COMMENT 'valuation Method';

ALTER TABLE etaxware.tblcreditnotes ADD prn varchar(200) NULL COMMENT 'prn';

ALTER TABLE etaxware.tblcreditnotes ADD totalWeight decimal(20,8) NULL COMMENT 'total Weight';

ALTER TABLE etaxware.tblcreditnotes ADD vatProjectId varchar(300) NULL;

ALTER TABLE etaxware.tblcreditnotes ADD vatProjectName varchar(300) NULL;

select * from etaxware.tblcreditnotes;

ALTER TABLE etaxware.tbldebitnotes ADD preGrossAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total gross amount';

ALTER TABLE etaxware.tbldebitnotes ADD preTaxAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total tax amount';

ALTER TABLE etaxware.tbldebitnotes ADD preNetAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total net amount';

ALTER TABLE etaxware.tbldebitnotes ADD deliveryTermsCode varchar(200) NULL COMMENT 'delivery Terms Code';

ALTER TABLE etaxware.tbldebitnotes ADD branchName varchar(200) NULL COMMENT 'branch Name';

ALTER TABLE etaxware.tbldebitnotes ADD sadNumber varchar(200) NULL COMMENT 'sad Number';

ALTER TABLE etaxware.tbldebitnotes ADD office varchar(200) NULL COMMENT 'office';

ALTER TABLE etaxware.tbldebitnotes ADD cif varchar(200) NULL COMMENT 'cif';

ALTER TABLE etaxware.tbldebitnotes ADD wareHouseNumber varchar(200) NULL COMMENT 'wareHouse Number';

ALTER TABLE etaxware.tbldebitnotes ADD wareHouseName varchar(200) NULL COMMENT 'wareHouse Name';

ALTER TABLE etaxware.tbldebitnotes ADD destinationCountry varchar(200) NULL COMMENT 'destination Country';

ALTER TABLE etaxware.tbldebitnotes ADD originCountry varchar(200) NULL COMMENT 'origin Country';

ALTER TABLE etaxware.tbldebitnotes ADD importExportFlag varchar(200) NULL COMMENT 'import Export Flag';

ALTER TABLE etaxware.tbldebitnotes ADD confirmStatus varchar(1) NULL COMMENT 'confirm Status';

ALTER TABLE etaxware.tbldebitnotes ADD valuationMethod varchar(1) NULL COMMENT 'valuation Method';

ALTER TABLE etaxware.tbldebitnotes ADD prn varchar(200) NULL COMMENT 'prn';

ALTER TABLE etaxware.tbldebitnotes ADD totalWeight decimal(20,8) NULL COMMENT 'total Weight';

ALTER TABLE etaxware.tbldebitnotes ADD vatProjectId varchar(300) NULL;

ALTER TABLE etaxware.tbldebitnotes ADD vatProjectName varchar(300) NULL;

select * from etaxware.tbldebitnotes;

ALTER TABLE etaxware.tblinvoices ADD preGrossAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total gross amount';

ALTER TABLE etaxware.tblinvoices ADD preTaxAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total tax amount';

ALTER TABLE etaxware.tblinvoices ADD preNetAmount decimal(20,8) NULL COMMENT 'Original Invoice/Receipt total net amount';

ALTER TABLE etaxware.tblinvoices ADD deliveryTermsCode varchar(200) NULL COMMENT 'delivery Terms Code';

ALTER TABLE etaxware.tblinvoices ADD branchName varchar(200) NULL COMMENT 'branch Name';

ALTER TABLE etaxware.tblinvoices ADD sadNumber varchar(200) NULL COMMENT 'sad Number';

ALTER TABLE etaxware.tblinvoices ADD office varchar(200) NULL COMMENT 'office';

ALTER TABLE etaxware.tblinvoices ADD cif varchar(200) NULL COMMENT 'cif';

ALTER TABLE etaxware.tblinvoices ADD wareHouseNumber varchar(200) NULL COMMENT 'wareHouse Number';

ALTER TABLE etaxware.tblinvoices ADD wareHouseName varchar(200) NULL COMMENT 'wareHouse Name';

ALTER TABLE etaxware.tblinvoices ADD destinationCountry varchar(200) NULL COMMENT 'destination Country';

ALTER TABLE etaxware.tblinvoices ADD originCountry varchar(200) NULL COMMENT 'origin Country';

ALTER TABLE etaxware.tblinvoices ADD importExportFlag varchar(200) NULL COMMENT 'import Export Flag';

ALTER TABLE etaxware.tblinvoices ADD confirmStatus varchar(1) NULL COMMENT 'confirm Status';

ALTER TABLE etaxware.tblinvoices ADD valuationMethod varchar(1) NULL COMMENT 'valuation Method';

ALTER TABLE etaxware.tblinvoices ADD prn varchar(200) NULL COMMENT 'prn';

ALTER TABLE etaxware.tblinvoices ADD totalWeight decimal(20,8) NULL COMMENT 'total Weight';

ALTER TABLE etaxware.tblinvoices ADD vatProjectId varchar(300) NULL;

ALTER TABLE etaxware.tblinvoices ADD vatProjectName varchar(300) NULL;

select * from etaxware.tblinvoices;


/*product details*/
ALTER TABLE etaxware.tblproductdetails ADD hsCode varchar(200) NULL COMMENT 'Hs Code';

ALTER TABLE etaxware.tblproductdetails ADD hsName varchar(200) NULL COMMENT 'Hs Name';

ALTER TABLE etaxware.tblproductdetails ADD goodsTypeCode varchar(3) NULL COMMENT 'goods Type Code';

ALTER TABLE etaxware.tblproductdetails ADD customsmeasureunit varchar(50) NULL;

ALTER TABLE etaxware.tblproductdetails ADD customsunitprice decimal(20,8) NULL;

ALTER TABLE etaxware.tblproductdetails ADD packagescaledvaluecustoms decimal(20,8) NULL;

ALTER TABLE etaxware.tblproductdetails ADD customsscaledvalue decimal(20,8) NULL;

ALTER TABLE etaxware.tblproductdetails ADD weight decimal(20,8) NULL;

select * from etaxware.tblproductdetails;

/* goods details*/
ALTER TABLE etaxware.tblgooddetails ADD hsCode varchar(200) NULL COMMENT 'Hs Code';

ALTER TABLE etaxware.tblgooddetails ADD hsName varchar(200) NULL COMMENT 'Hs Name';

ALTER TABLE etaxware.tblgooddetails ADD totalWeight varchar(200) NULL COMMENT 'totalWeight is required when invoice/receipt is export';

ALTER TABLE etaxware.tblgooddetails ADD pieceQty decimal(20,8) NULL COMMENT 'piece Qty';

/*ALTER TABLE etaxware.tblgooddetails ADD pieceMeasureUnit decimal(20,8) NULL COMMENT 'This code is from dictionary table, code type is rateUnit';*/

ALTER TABLE etaxware.tblgooddetails ADD deemedExemptCode varchar(200) NULL COMMENT 'deemed Exempt Code, 101:Deemed; 02:Exempt';

ALTER TABLE etaxware.tblgooddetails MODIFY COLUMN projectName varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL;

ALTER TABLE etaxware.tblgooddetails ADD invoiceItemId varchar(200) NULL;

ALTER TABLE etaxware.tblgooddetails ADD vatApplicableFlag varchar(1) NULL;

ALTER TABLE etaxware.tblgooddetails ADD vatProjectId varchar(300) NULL;

ALTER TABLE etaxware.tblgooddetails ADD vatProjectName varchar(300) NULL;

ALTER TABLE etaxware.tblgooddetails DROP projectId;

ALTER TABLE etaxware.tblgooddetails DROP projectName;

/*ALTER TABLE etaxware.tblgooddetails DROP pieceMeasureUnit;*/

ALTER TABLE etaxware.tblgooddetails ADD pieceMeasureUnit varchar(100) NULL COMMENT 'This code is from dictionary table, code type is rateUnit';

select * from etaxware.tblgooddetails;


/*Delivery Terms*/
-- etaxware.tbldeliverytermscodes definition
CREATE TABLE `tbldeliverytermscodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `erpid` varchar(100) DEFAULT NULL,
  `erpcode` varchar(100) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE etaxware.tbldeliverytermscodes DROP erpcode;

ALTER TABLE etaxware.tbldeliverytermscodes ADD erpcode varchar(100) NULL;

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'CFR', 'Cost and Freight', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'CIF', 'Cost Insurance and Freight', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'CIP', 'Carriage and Insurance Paid To', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'CPT', 'Carriage Paid to', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'DAP', 'Delivered at Place', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'DDP', 'Delivered Duty Paid', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'DPU', 'Delivered at Place Unloaded', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'EXW', 'Ex Works', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'FAS', 'Free Alongside Ship', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'FCA', 'Free Carrier', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'FOB', 'Free on Board', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.tbldeliverytermscodes
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, 'N/A', 'Not Applicable', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

select * from etaxware.tbldeliverytermscodes;



/*HS List Codes*/
-- etaxware.tblhscodes definition
CREATE TABLE `tblhscodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `erpid` varchar(100) DEFAULT NULL,
  `erpcode` varchar(100) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE etaxware.tblhscodes ADD isLeaf varchar(1) NULL;

ALTER TABLE etaxware.tblhscodes ADD parentClass varchar(200) NULL;

select * from etaxware.tblhscodes;


/*EDC Details*/
-- etaxware.tbledcdetails definition
CREATE TABLE `tbledcdetails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoiceid` int NOT NULL,
  `erpid` varchar(100) DEFAULT NULL,
  `erpcode` varchar(100) DEFAULT NULL,
  `tankNo` varchar(50) NOT NULL,
  `pumpNo` varchar(100) NOT NULL,
  `nozzleNo` varchar(200) DEFAULT NULL,
  `controllerNo` varchar(200) DEFAULT NULL,
  `acquisitionEquipmentNo` varchar(200) DEFAULT NULL,
  `levelGaugeNo` varchar(200) DEFAULT NULL,
  `mvrn` varchar(200) DEFAULT NULL,
  `updateTimes` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoiceid` (`invoiceid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


/*Agent Entity*/
-- etaxware.tblagententity definition
CREATE TABLE `tblagententity` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoiceid` int NOT NULL,
  `erpid` varchar(100) DEFAULT NULL,
  `erpcode` varchar(100) DEFAULT NULL,
  `tin` varchar(50) NOT NULL,
  `legalName` varchar(500) DEFAULT NULL,
  `businessName` varchar(500) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoiceid` (`invoiceid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


/*buyer extend*/
ALTER TABLE etaxware.tblbuyers ADD propertyType varchar(200) NULL;

ALTER TABLE etaxware.tblbuyers ADD district varchar(200) NULL;

ALTER TABLE etaxware.tblbuyers ADD municipalityCounty varchar(200) NULL;

ALTER TABLE etaxware.tblbuyers ADD divisionSubcounty varchar(200) NULL;

ALTER TABLE etaxware.tblbuyers ADD town varchar(200) NULL;

ALTER TABLE etaxware.tblbuyers ADD cellVillage varchar(200) NULL;

ALTER TABLE etaxware.tblbuyers ADD effectiveRegistrationDate varchar(200) NULL;

ALTER TABLE etaxware.tblbuyers ADD meterStatus varchar(200) NULL;

ALTER TABLE etaxware.tblbuyers DROP nonResidentFlag;

ALTER TABLE etaxware.tblbuyers ADD nonResidentFlag boolean NULL;

ALTER TABLE etaxware.tblbuyers ALTER COLUMN nonResidentFlag SET DEFAULT false;

ALTER TABLE etaxware.tblbuyers ADD vatProjectId varchar(300) NULL;

ALTER TABLE etaxware.tblbuyers ADD vatProjectName varchar(300) NULL;

ALTER TABLE etaxware.tblbuyers ADD deliveryTermsCode varchar(200) NULL COMMENT 'delivery Terms Code';

select * from etaxware.tblbuyers;

ALTER TABLE etaxware.tblcustomers ADD propertyType varchar(200) NULL;

ALTER TABLE etaxware.tblcustomers ADD district varchar(200) NULL;

ALTER TABLE etaxware.tblcustomers ADD municipalityCounty varchar(200) NULL;

ALTER TABLE etaxware.tblcustomers ADD divisionSubcounty varchar(200) NULL;

ALTER TABLE etaxware.tblcustomers ADD town varchar(200) NULL;

ALTER TABLE etaxware.tblcustomers ADD cellVillage varchar(200) NULL;

ALTER TABLE etaxware.tblcustomers ADD effectiveRegistrationDate varchar(200) NULL;

ALTER TABLE etaxware.tblcustomers ADD meterStatus varchar(200) NULL;

ALTER TABLE etaxware.tblcustomers DROP nonResidentFlag;

ALTER TABLE etaxware.tblcustomers ADD nonResidentFlag boolean NULL;

ALTER TABLE etaxware.tblcustomers ALTER COLUMN nonResidentFlag SET DEFAULT false;

ALTER TABLE etaxware.tblcustomers ADD vatProjectId varchar(300) NULL;

ALTER TABLE etaxware.tblcustomers ADD vatProjectName varchar(300) NULL;

ALTER TABLE etaxware.tblcustomers ADD deliveryTermsCode varchar(200) NULL COMMENT 'delivery Terms Code';

select * from etaxware.tblcustomers;







/*Import Services Seller*/
-- etaxware.tblimportservicessellers definition
CREATE TABLE `tblimportservicessellers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `erpid` varchar(100) DEFAULT NULL,
  `erpcode` varchar(100) DEFAULT NULL,
  `invoiceid` int NOT NULL,
  `importBusinessName` varchar(200) NOT NULL,
  `importEmailAddress` varchar(100) NOT NULL,
  `importContactNumber` varchar(200) DEFAULT NULL,
  `importAddress` varchar(500) DEFAULT NULL,
  `importInvoiceDate` varchar(20) DEFAULT NULL,
  `importAttachmentName` varchar(200) DEFAULT NULL,
  `importAttachmentContent` varchar(1000) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoiceid` (`invoiceid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


/*Permissions*/
INSERT INTO etaxware.tblpermissions
(id, module, submodule, operation, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(1, NULL, NULL, NULL, 'LISTHSCODES', 'List HS Codes', 'List HS Codes', 0, '2025-07-03 11:42:00', 1000, '2025-07-03 11:42:00', 1000);


select * from etaxware.tblpermissions;



/*Good Type*/
-- etaxware.tblgoodstypecodes definition
CREATE TABLE etaxware.`tblgoodstypecodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `erpid` varchar(100) DEFAULT NULL,
  `erpcode` varchar(100) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



INSERT INTO etaxware.`tblgoodstypecodes`
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, '101', 'Goods', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);

INSERT INTO etaxware.`tblgoodstypecodes`
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, '102', 'Fuel', NULL, 0, '2025-06-30 11:42:00', 1000, '2025-06-30 11:42:00', 1000);


select * from etaxware.`tblgoodstypecodes`;

/*Export Rate Units*/
-- etaxware.tblexportrateunits definition
CREATE TABLE etaxware.`tblexportrateunits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `erpid` varchar(100) DEFAULT NULL,
  `erpcode` varchar(100) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE etaxware.tblexportrateunits ADD validPeriodFrom varchar(50) NULL;

ALTER TABLE etaxware.tblexportrateunits ADD periodTo varchar(50) NULL;

ALTER TABLE etaxware.tblexportrateunits ADD status varchar(3) NULL;


select * from etaxware.`tblexportrateunits`;