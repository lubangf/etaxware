# eTaxWare SQLite Schema Reference

Generated from scripts/db/etaxware_schema.sqlite

Total tables: 117

## Module Summary

- Products and Catalog: 17 tables
- Documents and Transactions: 13 tables
- Stock and Inventory Operations: 6 tables
- Access, Security, and Workflow: 13 tables
- Integration and Platform: 11 tables
- Parties and Geography: 7 tables
- Reference and Configuration: 11 tables
- Other: 39 tables

## Products and Catalog

- tblcommoditycategories (29 columns)
  Key columns: id, erpid, erpcode, inserteddt, modifieddt
- tblexcisedutydetailslist (17 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblexcisedutylist (18 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblexcisedutytypes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblgoodsstockadjustment (23 columns)
  Key columns: id, inserteddt, modifieddt
- tblgoodsstocktransfer (17 columns)
  Key columns: id, inserteddt, modifieddt
- tblgoodstransfertypes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblgoodstype (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblgoodstypecodes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblhscodes (13 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblotherunits (11 columns)
  Key columns: id, inserteddt, modifieddt
- tblproductdetails (51 columns)
  Key columns: id, code, name, erpid, erpcode, uraproductidentifier, inserteddt, modifieddt
- tblproductexclusioncodes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblproductoverridelist (10 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblproductsourcecodes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblproductstatuscodes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblrateunits (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt

## Documents and Transactions

- tblcreditmemos (66 columns)
  Key columns: id, inserteddt, modifieddt
- tblcreditnotes (79 columns)
  Key columns: id, inserteddt, modifieddt
- tbldebitnotereasoncodes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbldebitnotes (79 columns)
  Key columns: id, inserteddt, modifieddt
- tblgooddetailgroups (9 columns)
  Key columns: id, inserteddt, modifieddt
- tblgooddetails (48 columns)
  Key columns: id, inserteddt, modifieddt
- tblinvoicekinds (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblinvoices (67 columns)
  Key columns: id, inserteddt, modifieddt
- tblinvoicetypes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblpaymentdetailgroups (9 columns)
  Key columns: id, inserteddt, modifieddt
- tblpaymentdetails (11 columns)
  Key columns: id, inserteddt, modifieddt
- tblpaymentmodes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblpurchaseorders (29 columns)
  Key columns: id, inserteddt, modifieddt

## Stock and Inventory Operations

- tblopeningstocklogs (10 columns)
  Key columns: id, inserteddt, modifieddt
- tblopeningstockruns (17 columns)
  Key columns: id, inserteddt, modifieddt
- tblstockadjustmenttypes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblstockintypes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblstocklimits (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblstockoperationtypes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt

## Access, Security, and Workflow

- tblauditlogs (11 columns)
  Key columns: id, inserteddt, modifieddt
- tbleventnotifications (14 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblevents (12 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblmodules (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblnotifications (17 columns)
  Key columns: id, inserteddt, modifieddt
- tblnotificationsubtypes (10 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblnotificationtypes (10 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblpermissiondetails (10 columns)
  Key columns: id, code, inserteddt, modifieddt
- tblpermissiongroups (9 columns)
  Key columns: id, inserteddt, modifieddt
- tblpermissions (12 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblroles (11 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblsubmodules (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblusers (23 columns)
  Key columns: id, erpid, erpcode, inserteddt, modifieddt

## Integration and Platform

- tbldevices (18 columns)
  Key columns: id, inserteddt, modifieddt
- tbldevicestatuses (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbldevicetypes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbledcdetails (17 columns)
  Key columns: id, erpid, erpcode, inserteddt, modifieddt
- tblefrismode (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblerpauditlogs (18 columns)
  Key columns: id, inserteddt, modifieddt
- tblerpdocumentypes (10 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblerptypes (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblplatformmode (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblsqlcache (8 columns)
  Key columns: id, code, inserteddt, modifieddt
- tbltcsdetails (18 columns)
  Key columns: id, inserteddt, modifieddt

## Parties and Geography

- tblbranches (17 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblbuyers (35 columns)
  Key columns: id, inserteddt, modifieddt
- tblbuyertypes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblcurrencies (16 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblcustomers (37 columns)
  Key columns: id, inserteddt, modifieddt
- tblorganisations (36 columns)
  Key columns: id, inserteddt, modifieddt
- tblsellers (16 columns)
  Key columns: id, inserteddt, modifieddt

## Reference and Configuration

- tblchoices (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblflags (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblmodes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbloperations (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblreportformats (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblreportgroups (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblreports (12 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblsettinggroups (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblsettings (13 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblstatuses (11 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblstatusgroups (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt

## Other

- file_11 (18 columns)
  Key columns: id, inserteddt, modifieddt
- file_13 (18 columns)
  Key columns: id, inserteddt, modifieddt
- file_14 (18 columns)
  Key columns: id, inserteddt, modifieddt
- file_15 (18 columns)
  Key columns: id, inserteddt, modifieddt
- file_16 (18 columns)
  Key columns: id, inserteddt, modifieddt
- tblagententity (13 columns)
  Key columns: id, erpid, erpcode, inserteddt, modifieddt
- tblapikeys (12 columns)
  Key columns: id, inserteddt, modifieddt
- tblbusinesstypes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblcdnoteapplycategorycodes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblcdnoteapprovestatuses (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblcdnotereasoncodes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblcountries (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbldatasources (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbldeliverytermscodes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblenforcetaxexclusionlist (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblentitytypes (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblexportrateunits (14 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblfeesmapping (9 columns)
  Key columns: id, inserteddt, modifieddt
- tblfieldgroups (8 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblfields (16 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblfileuploads (14 columns)
  Key columns: id, inserteddt, modifieddt
- tblimportedsellers (23 columns)
  Key columns: id, inserteddt, modifieddt
- tblimportedservices (47 columns)
  Key columns: id, inserteddt, modifieddt
- tblimportservicessellers (16 columns)
  Key columns: id, erpid, erpcode, inserteddt, modifieddt
- tblindustries (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblpriorities (9 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblsectors (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tblsuppliers (25 columns)
  Key columns: id, inserteddt, modifieddt
- tbltaxdetailgroups (9 columns)
  Key columns: id, inserteddt, modifieddt
- tbltaxdetails (18 columns)
  Key columns: id, inserteddt, modifieddt
- tbltaxpayerregistrationrtatuses (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbltaxpayerstatuses (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbltaxpayertypes (11 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbltaxrates (14 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbltaxtypes (12 columns)
  Key columns: id, code, name, erpid, erpcode, inserteddt, modifieddt
- tbltemplateformats (10 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tbltemplates (10 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tbltemplatetypes (10 columns)
  Key columns: id, code, name, inserteddt, modifieddt
- tblurabranches (9 columns)
  Key columns: id, name, inserteddt, modifieddt
