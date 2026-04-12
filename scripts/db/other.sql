ALTER TABLE `etaxware`.`tblproductdetails` 
ADD COLUMN `serviceMark` INT NULL DEFAULT 102 AFTER `stick`,
ADD COLUMN `goodsTypeCode` INT NULL DEFAULT 101 AFTER `serviceMark`,
ADD COLUMN `remarks` VARCHAR(1000) NULL AFTER `goodsTypeCode`;
