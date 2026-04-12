ALTER TABLE `etaxware`.`tblinvoices` 
ADD COLUMN `erpUpdateFlag` INT NULL DEFAULT 0 AFTER `docTypeCode`;
