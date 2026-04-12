/*Disable foreign key check*/
SET foreign_key_checks = 0;
SET SQL_SAFE_UPDATES = 0;


TRUNCATE TABLE tblnotifications;
TRUNCATE TABLE tblauditlogs;
TRUNCATE TABLE tblauditlogs;
TRUNCATE TABLE tblbuyers;
TRUNCATE TABLE tbldebitnotes;
TRUNCATE TABLE tblcreditnotes;
TRUNCATE TABLE tblcustomers;
TRUNCATE TABLE tbldebitnotes;
TRUNCATE TABLE tblerpauditlogs;
TRUNCATE TABLE tblgooddetailgroups;
TRUNCATE TABLE tblgooddetails;
TRUNCATE TABLE tblinvoices;
TRUNCATE TABLE tblnotifications;
TRUNCATE TABLE tblpaymentdetailgroups;
TRUNCATE TABLE tblpaymentdetails;
/*TRUNCATE TABLE tblproductdetails;*/
DELETE FROM tblproductdetails WHERE id <> 0;
TRUNCATE TABLE tblotherunits;
TRUNCATE TABLE tblsellers;
TRUNCATE TABLE tbltaxdetailgroups;
TRUNCATE TABLE tbltaxdetails;
TRUNCATE TABLE tblcustomers;
TRUNCATE TABLE tblbuyers;
TRUNCATE TABLE tblgoodsstocktransfer;
TRUNCATE TABLE tblgoodsstockadjustment;
TRUNCATE TABLE tblurabranches;
DELETE FROM tblbranches WHERE id NOT IN (1017);
DELETE FROM tblusers WHERE id NOT IN (1000);
DELETE FROM tblpermissiongroups WHERE owner NOT IN (1000);
DELETE FROM tblpermissiondetails WHERE groupid NOT IN (SELECT id FROM tblpermissiongroups WHERE owner IN (1000));
TRUNCATE TABLE tblpurchaseorders;
TRUNCATE TABLE tblsuppliers;

/*Enable foreign key check*/
SET foreign_key_checks = 1;
SET SQL_SAFE_UPDATES = 1;