/*Permissions*/
INSERT INTO etaxware.tblpermissions
(module, submodule, operation, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, NULL, 'LISTHSCODES', 'List HS Codes', 'List HS Codes', 0, '2025-07-03 11:42:00', 1000, '2025-07-03 11:42:00', 1000);

INSERT INTO etaxware.tblpermissions
(module, submodule, operation, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, NULL, 'RENEWACCESSTOKEN', 'Renew Access Token', 'Renew Access Token', 0, '2025-07-03 11:42:00', 1000, '2025-07-03 11:42:00', 1000);


select * from etaxware.tblpermissions;


INSERT INTO etaxware.tblindustries
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, '110', 'EDC', 'EDC', 0, '2025-07-17 09:31:42', 1000, '2025-07-17 09:31:42', 1000);

INSERT INTO etaxware.tblindustries
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, '111', 'Auction', 'Auction', 0, '2025-07-17 09:31:42', 1000, '2025-07-17 09:31:42', 1000);

INSERT INTO etaxware.tblindustries
(erpid, erpcode, code, name, description, disabled, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(NULL, NULL, '112', 'Export Service', 'Export Service', 0, '2025-07-17 09:31:42', 1000, '2025-07-17 09:31:42', 1000);


select * from etaxware.tblindustries;


INSERT INTO etaxware.tblsettings
(groupid, groupcode, code, name, value, description, disabled, sensitivityflag, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(3, 'APP', 'ERPBASECOUNTRY', 'ERP Base Country', 'Uganda', NULL, 0, 0, '2025-08-09 11:42:00', 1000, '2025-08-09 11:42:00', 1000);

INSERT INTO etaxware.tblsettings
(groupid, groupcode, code, name, value, description, disabled, sensitivityflag, inserteddt, insertedby, modifieddt, modifiedby)
VALUES(3, 'APP', 'EXPORTINVOICEINDUSTRY', 'Export Industry Code', '102', NULL, 0, 0, '2025-08-09 11:42:00', 1000, '2025-08-09 11:42:00', 1000);

select * from etaxware.tblsettings;