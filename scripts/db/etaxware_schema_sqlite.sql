CREATE TABLE IF NOT EXISTS "file_11" (
    "id" INTEGER PRIMARY KEY,
    "productCode" TEXT DEFAULT NULL,
    "supplierTIN" TEXT DEFAULT NULL,
    "supplierName" TEXT DEFAULT NULL,
    "qty" TEXT DEFAULT NULL,
    "unitPrice" TEXT DEFAULT NULL,
    "responseCode" TEXT DEFAULT NULL,
    "responseMessage" TEXT DEFAULT NULL,
    "processingresults" TEXT,
    "processingerrors" INTEGER DEFAULT '0',
    "additionalprocessingresults" TEXT,
    "fileid" INTEGER DEFAULT NULL,
    "runid" INTEGER DEFAULT NULL,
    "batchid" INTEGER DEFAULT NULL,
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "file_13" (
    "id" INTEGER PRIMARY KEY,
    "productCode" TEXT DEFAULT NULL,
    "supplierTIN" TEXT DEFAULT NULL,
    "supplierName" TEXT DEFAULT NULL,
    "qty" TEXT DEFAULT NULL,
    "unitPrice" TEXT DEFAULT NULL,
    "responseCode" TEXT DEFAULT NULL,
    "responseMessage" TEXT DEFAULT NULL,
    "processingresults" TEXT,
    "processingerrors" INTEGER DEFAULT '0',
    "additionalprocessingresults" TEXT,
    "fileid" INTEGER DEFAULT NULL,
    "runid" INTEGER DEFAULT NULL,
    "batchid" INTEGER DEFAULT NULL,
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "file_14" (
    "id" INTEGER PRIMARY KEY,
    "productCode" TEXT DEFAULT NULL,
    "supplierTIN" TEXT DEFAULT NULL,
    "supplierName" TEXT DEFAULT NULL,
    "qty" TEXT DEFAULT NULL,
    "unitPrice" TEXT DEFAULT NULL,
    "responseCode" TEXT DEFAULT NULL,
    "responseMessage" TEXT DEFAULT NULL,
    "processingresults" TEXT,
    "processingerrors" INTEGER DEFAULT '0',
    "additionalprocessingresults" TEXT,
    "fileid" INTEGER DEFAULT NULL,
    "runid" INTEGER DEFAULT NULL,
    "batchid" INTEGER DEFAULT NULL,
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "file_15" (
    "id" INTEGER PRIMARY KEY,
    "productCode" TEXT DEFAULT NULL,
    "supplierTIN" TEXT DEFAULT NULL,
    "supplierName" TEXT DEFAULT NULL,
    "qty" TEXT DEFAULT NULL,
    "unitPrice" TEXT DEFAULT NULL,
    "responseCode" TEXT DEFAULT NULL,
    "responseMessage" TEXT DEFAULT NULL,
    "processingresults" TEXT,
    "processingerrors" INTEGER DEFAULT '0',
    "additionalprocessingresults" TEXT,
    "fileid" INTEGER DEFAULT NULL,
    "runid" INTEGER DEFAULT NULL,
    "batchid" INTEGER DEFAULT NULL,
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "file_16" (
    "id" INTEGER PRIMARY KEY,
    "productCode" TEXT DEFAULT NULL,
    "supplierTIN" TEXT DEFAULT NULL,
    "supplierName" TEXT DEFAULT NULL,
    "qty" TEXT DEFAULT NULL,
    "unitPrice" TEXT DEFAULT NULL,
    "responseCode" TEXT DEFAULT NULL,
    "responseMessage" TEXT DEFAULT NULL,
    "processingresults" TEXT,
    "processingerrors" INTEGER DEFAULT '0',
    "additionalprocessingresults" TEXT,
    "fileid" INTEGER DEFAULT NULL,
    "runid" INTEGER DEFAULT NULL,
    "batchid" INTEGER DEFAULT NULL,
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblagententity" (
    "id" INTEGER PRIMARY KEY,
    "invoiceid" INTEGER NOT NULL,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "tin" TEXT NOT NULL,
    "legalName" TEXT DEFAULT NULL,
    "businessName" TEXT DEFAULT NULL,
    "address" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblapikeys" (
    "id" INTEGER PRIMARY KEY,
    "apiname" TEXT DEFAULT NULL,
    "apikey" TEXT NOT NULL,
    "permissiongroup" INTEGER DEFAULT NULL,
    "status" INTEGER NOT NULL,
    "lastaccessdt" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "expirydt" TEXT DEFAULT NULL,
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblauditlogs" (
    "id" INTEGER PRIMARY KEY,
    "module" INTEGER DEFAULT NULL,
    "submodule" INTEGER DEFAULT NULL,
    "event" INTEGER DEFAULT NULL,
    "userid" INTEGER DEFAULT NULL,
    "description" TEXT NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblbranches" (
    "id" INTEGER PRIMARY KEY,
    "uramap" INTEGER DEFAULT '0',
    "uraid" TEXT DEFAULT NULL,
    "uracode" TEXT DEFAULT NULL,
    "uraname" TEXT DEFAULT NULL,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "erpname" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "status" INTEGER NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblbusinesstypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblbuyers" (
    "id" INTEGER PRIMARY KEY,
    "erpbuyerid" INTEGER DEFAULT NULL,
    "erpbuyercode" TEXT DEFAULT NULL,
    "tin" TEXT DEFAULT NULL,
    "ninbrn" TEXT DEFAULT NULL,
    "PassportNum" TEXT DEFAULT NULL,
    "legalname" TEXT DEFAULT NULL,
    "businessname" TEXT DEFAULT NULL,
    "address" TEXT DEFAULT NULL,
    "mobilephone" TEXT DEFAULT NULL,
    "linephone" TEXT DEFAULT NULL,
    "emailaddress" TEXT DEFAULT NULL,
    "placeofbusiness" TEXT DEFAULT NULL,
    "type" TEXT NOT NULL,
    "citizineship" TEXT DEFAULT NULL,
    "sector" TEXT DEFAULT NULL,
    "referenceno" TEXT DEFAULT NULL,
    "datasource" TEXT DEFAULT 'MW',
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "propertyType" TEXT DEFAULT NULL,
    "district" TEXT DEFAULT NULL,
    "municipalityCounty" TEXT DEFAULT NULL,
    "divisionSubcounty" TEXT DEFAULT NULL,
    "town" TEXT DEFAULT NULL,
    "cellVillage" TEXT DEFAULT NULL,
    "effectiveRegistrationDate" TEXT DEFAULT NULL,
    "meterStatus" TEXT DEFAULT NULL,
    "vatProjectId" TEXT DEFAULT NULL,
    "vatProjectName" TEXT DEFAULT NULL,
    "deliveryTermsCode" TEXT DEFAULT NULL,
    "nonResidentFlag" INTEGER DEFAULT '0'
);

CREATE TABLE IF NOT EXISTS "tblbuyertypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblcdnoteapplycategorycodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblcdnoteapprovestatuses" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblcdnotereasoncodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblchoices" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblcommoditycategories" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "segmentcode" TEXT DEFAULT NULL,
    "segmentname" TEXT DEFAULT NULL,
    "familycode" TEXT DEFAULT NULL,
    "familyname" TEXT DEFAULT NULL,
    "classcode" TEXT DEFAULT NULL,
    "classname" TEXT DEFAULT NULL,
    "commoditycode" TEXT NOT NULL,
    "commodityname" TEXT NOT NULL,
    "rate" REAL DEFAULT NULL,
    "isLeafNode" INTEGER DEFAULT NULL,
    "serviceMark" INTEGER DEFAULT NULL,
    "isZeroRate" INTEGER DEFAULT NULL,
    "zeroRateStartDate" TEXT DEFAULT NULL,
    "zeroRateEndDate" TEXT DEFAULT NULL,
    "isExempt" INTEGER DEFAULT NULL,
    "exemptRateStartDate" TEXT DEFAULT NULL,
    "exemptRateEndDate" TEXT DEFAULT NULL,
    "enableStatusCode" INTEGER DEFAULT NULL,
    "exclusion" INTEGER DEFAULT NULL,
    "parentCode" TEXT DEFAULT NULL,
    "commodityCategoryLevel" INTEGER DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblcountries" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblcreditmemos" (
    "id" INTEGER PRIMARY KEY,
    "gooddetailgroupid" INTEGER DEFAULT NULL,
    "taxdetailgroupid" INTEGER DEFAULT NULL,
    "paymentdetailgroupid" INTEGER DEFAULT NULL,
    "erpinvoiceid" TEXT DEFAULT NULL,
    "erpinvoiceno" TEXT DEFAULT NULL,
    "antifakecode" TEXT DEFAULT NULL,
    "deviceno" TEXT DEFAULT NULL,
    "issueddate" TEXT DEFAULT NULL,
    "issuedtime" TEXT DEFAULT NULL,
    "operator" TEXT DEFAULT NULL,
    "currency" TEXT DEFAULT NULL,
    "oriinvoiceid" TEXT DEFAULT NULL,
    "invoicetype" INTEGER DEFAULT NULL,
    "invoicekind" INTEGER DEFAULT NULL,
    "datasource" INTEGER DEFAULT NULL,
    "invoiceindustrycode" INTEGER DEFAULT NULL,
    "einvoiceid" TEXT DEFAULT NULL,
    "einvoicenumber" TEXT DEFAULT NULL,
    "einvoicedatamatrixcode" TEXT DEFAULT NULL,
    "isbatch" TEXT DEFAULT '0',
    "netamount" REAL DEFAULT NULL,
    "taxamount" REAL DEFAULT NULL,
    "grossamount" REAL DEFAULT NULL,
    "origrossamount" REAL DEFAULT NULL,
    "itemcount" INTEGER DEFAULT '0',
    "modecode" TEXT DEFAULT '1',
    "modename" TEXT DEFAULT NULL,
    "remarks" TEXT DEFAULT NULL,
    "buyerid" INTEGER DEFAULT NULL,
    "sellerid" INTEGER DEFAULT NULL,
    "issueddatepdf" TEXT DEFAULT NULL,
    "grossamountword" TEXT DEFAULT NULL,
    "isinvalid" INTEGER DEFAULT NULL,
    "isrefund" INTEGER DEFAULT NULL,
    "vouchertype" TEXT DEFAULT NULL,
    "vouchertypename" TEXT DEFAULT NULL,
    "currencyRate" REAL DEFAULT '0.00000000',
    "branchCode" TEXT DEFAULT NULL,
    "branchId" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "SyncToken" INTEGER DEFAULT NULL,
    "docTypeCode" TEXT DEFAULT '10',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "preGrossAmount" REAL DEFAULT NULL,
    "preTaxAmount" REAL DEFAULT NULL,
    "preNetAmount" REAL DEFAULT NULL,
    "deliveryTermsCode" TEXT DEFAULT NULL,
    "branchName" TEXT DEFAULT NULL,
    "sadNumber" TEXT DEFAULT NULL,
    "office" TEXT DEFAULT NULL,
    "cif" TEXT DEFAULT NULL,
    "wareHouseNumber" TEXT DEFAULT NULL,
    "wareHouseName" TEXT DEFAULT NULL,
    "destinationCountry" TEXT DEFAULT NULL,
    "originCountry" TEXT DEFAULT NULL,
    "importExportFlag" TEXT DEFAULT NULL,
    "confirmStatus" TEXT DEFAULT NULL,
    "valuationMethod" TEXT DEFAULT NULL,
    "prn" TEXT DEFAULT NULL,
    "totalWeight" REAL DEFAULT NULL,
    "vatProjectId" TEXT DEFAULT NULL,
    "vatProjectName" TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblcreditnotes" (
    "id" INTEGER PRIMARY KEY,
    "gooddetailgroupid" INTEGER DEFAULT NULL,
    "taxdetailgroupid" INTEGER DEFAULT NULL,
    "paymentdetailgroupid" INTEGER DEFAULT NULL,
    "erpcreditnoteid" TEXT DEFAULT NULL,
    "erpcreditnoteno" TEXT DEFAULT NULL,
    "creditnoteid" TEXT DEFAULT NULL,
    "referenceno" TEXT DEFAULT NULL,
    "approvestatus" TEXT DEFAULT NULL,
    "creditnoteapplicationid" TEXT DEFAULT NULL,
    "refundinvoiceno" TEXT DEFAULT NULL,
    "oriinvoiceid" TEXT DEFAULT NULL,
    "oriinvoiceno" TEXT DEFAULT NULL,
    "reasoncode" TEXT DEFAULT NULL,
    "reason" TEXT DEFAULT NULL,
    "applicationtime" TEXT DEFAULT NULL,
    "invoiceapplycategorycode" INTEGER DEFAULT '101',
    "currency" TEXT DEFAULT NULL,
    "erpinvoiceid" TEXT DEFAULT NULL,
    "erpinvoiceno" TEXT DEFAULT NULL,
    "antifakecode" TEXT DEFAULT NULL,
    "deviceno" TEXT DEFAULT NULL,
    "issueddate" TEXT DEFAULT NULL,
    "issuedtime" TEXT DEFAULT NULL,
    "operator" TEXT DEFAULT NULL,
    "invoicetype" INTEGER DEFAULT NULL,
    "invoicekind" INTEGER DEFAULT NULL,
    "datasource" INTEGER DEFAULT NULL,
    "invoiceindustrycode" INTEGER DEFAULT NULL,
    "einvoiceid" TEXT DEFAULT NULL,
    "einvoicenumber" TEXT DEFAULT NULL,
    "einvoicedatamatrixcode" TEXT DEFAULT NULL,
    "isbatch" TEXT DEFAULT '0',
    "netamount" REAL DEFAULT NULL,
    "taxamount" REAL DEFAULT NULL,
    "grossamount" REAL DEFAULT NULL,
    "origrossamount" REAL DEFAULT NULL,
    "totalamount" REAL DEFAULT NULL,
    "itemcount" INTEGER DEFAULT '0',
    "modecode" TEXT DEFAULT '1',
    "modename" TEXT DEFAULT NULL,
    "remarks" TEXT DEFAULT NULL,
    "buyerid" INTEGER DEFAULT '0',
    "issueddatepdf" TEXT DEFAULT NULL,
    "grossamountword" TEXT DEFAULT NULL,
    "isinvalid" INTEGER DEFAULT NULL,
    "isrefund" INTEGER DEFAULT NULL,
    "vouchertype" TEXT DEFAULT NULL,
    "vouchertypename" TEXT DEFAULT NULL,
    "sellerid" INTEGER DEFAULT NULL,
    "currencyRate" REAL DEFAULT '0.00000000',
    "branchCode" TEXT DEFAULT NULL,
    "branchId" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "SyncToken" INTEGER DEFAULT NULL,
    "docTypeCode" TEXT DEFAULT '14',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "preGrossAmount" REAL DEFAULT NULL,
    "preTaxAmount" REAL DEFAULT NULL,
    "preNetAmount" REAL DEFAULT NULL,
    "deliveryTermsCode" TEXT DEFAULT NULL,
    "branchName" TEXT DEFAULT NULL,
    "sadNumber" TEXT DEFAULT NULL,
    "office" TEXT DEFAULT NULL,
    "cif" TEXT DEFAULT NULL,
    "wareHouseNumber" TEXT DEFAULT NULL,
    "wareHouseName" TEXT DEFAULT NULL,
    "destinationCountry" TEXT DEFAULT NULL,
    "originCountry" TEXT DEFAULT NULL,
    "importExportFlag" TEXT DEFAULT NULL,
    "confirmStatus" TEXT DEFAULT NULL,
    "valuationMethod" TEXT DEFAULT NULL,
    "prn" TEXT DEFAULT NULL,
    "totalWeight" REAL DEFAULT NULL,
    "vatProjectId" TEXT DEFAULT NULL,
    "vatProjectName" TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblcurrencies" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "rate" REAL DEFAULT NULL,
    "exportLevy" REAL DEFAULT '0.00000000',
    "importDutyLevy" REAL DEFAULT '0.00000000',
    "inComeTax" REAL DEFAULT '0.00000000',
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "status" INTEGER DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblcustomers" (
    "id" INTEGER PRIMARY KEY,
    "erpcustomerid" TEXT DEFAULT NULL,
    "erpcustomercode" TEXT DEFAULT NULL,
    "tin" TEXT DEFAULT NULL,
    "ninbrn" TEXT DEFAULT NULL,
    "PassportNum" TEXT DEFAULT NULL,
    "legalname" TEXT NOT NULL,
    "businessname" TEXT DEFAULT NULL,
    "address" TEXT DEFAULT NULL,
    "mobilephone" TEXT DEFAULT NULL,
    "linephone" TEXT DEFAULT NULL,
    "emailaddress" TEXT DEFAULT NULL,
    "placeofbusiness" TEXT DEFAULT NULL,
    "type" TEXT DEFAULT NULL,
    "citizineship" TEXT DEFAULT NULL,
    "countryCode" TEXT DEFAULT NULL,
    "sector" TEXT DEFAULT NULL,
    "sectorCode" TEXT DEFAULT NULL,
    "datasource" TEXT DEFAULT 'MW',
    "status" INTEGER DEFAULT '1036',
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "propertyType" TEXT DEFAULT NULL,
    "district" TEXT DEFAULT NULL,
    "municipalityCounty" TEXT DEFAULT NULL,
    "divisionSubcounty" TEXT DEFAULT NULL,
    "town" TEXT DEFAULT NULL,
    "cellVillage" TEXT DEFAULT NULL,
    "effectiveRegistrationDate" TEXT DEFAULT NULL,
    "meterStatus" TEXT DEFAULT NULL,
    "vatProjectId" TEXT DEFAULT NULL,
    "vatProjectName" TEXT DEFAULT NULL,
    "deliveryTermsCode" TEXT DEFAULT NULL,
    "nonResidentFlag" INTEGER DEFAULT '0'
);

CREATE TABLE IF NOT EXISTS "tbldatasources" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbldebitnotereasoncodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbldebitnotes" (
    "id" INTEGER PRIMARY KEY,
    "gooddetailgroupid" INTEGER DEFAULT NULL,
    "taxdetailgroupid" INTEGER DEFAULT NULL,
    "paymentdetailgroupid" INTEGER DEFAULT NULL,
    "erpdebitnoteid" TEXT DEFAULT NULL,
    "erpdebitnoteno" TEXT DEFAULT NULL,
    "oriinvoiceno" TEXT DEFAULT NULL,
    "oriinvoiceid" TEXT DEFAULT NULL,
    "deviceno" TEXT DEFAULT NULL,
    "applicationdate" TEXT DEFAULT NULL,
    "applicationtime" TEXT DEFAULT NULL,
    "operator" TEXT DEFAULT NULL,
    "currency" TEXT DEFAULT NULL,
    "erpinvoiceid" TEXT DEFAULT NULL,
    "erpinvoiceno" TEXT DEFAULT NULL,
    "invoicetype" INTEGER DEFAULT NULL,
    "invoicekind" INTEGER DEFAULT NULL,
    "datasource" INTEGER DEFAULT NULL,
    "invoiceindustrycode" INTEGER DEFAULT NULL,
    "antifakecode" TEXT DEFAULT NULL,
    "debitnoteapplicationid" TEXT DEFAULT NULL,
    "debitnoteno" TEXT DEFAULT NULL,
    "issueddate" TEXT DEFAULT NULL,
    "issuedtime" TEXT DEFAULT NULL,
    "issueddatepdf" TEXT DEFAULT NULL,
    "debitnoteid" TEXT DEFAULT NULL,
    "oriissueddate" TEXT DEFAULT NULL,
    "einvoicenumber" TEXT DEFAULT NULL,
    "einvoiceid" TEXT DEFAULT NULL,
    "einvoicedatamatrixcode" TEXT DEFAULT NULL,
    "referenceno" TEXT DEFAULT NULL,
    "approvestatus" TEXT DEFAULT NULL,
    "isbatch" TEXT DEFAULT '0',
    "netamount" REAL DEFAULT NULL,
    "taxamount" REAL DEFAULT NULL,
    "grossamount" REAL DEFAULT NULL,
    "origrossamount" REAL DEFAULT NULL,
    "itemcount" INTEGER DEFAULT '0',
    "modecode" TEXT DEFAULT '1',
    "modename" TEXT DEFAULT NULL,
    "remarks" TEXT DEFAULT NULL,
    "buyerid" INTEGER DEFAULT NULL,
    "sellerid" INTEGER DEFAULT NULL,
    "grossamountword" TEXT DEFAULT NULL,
    "isinvalid" INTEGER DEFAULT NULL,
    "isrefund" INTEGER DEFAULT NULL,
    "vouchertype" TEXT DEFAULT NULL,
    "vouchertypename" TEXT DEFAULT NULL,
    "reasoncode" TEXT DEFAULT NULL,
    "reason" TEXT DEFAULT NULL,
    "currencyRate" REAL DEFAULT '0.00000000',
    "branchId" TEXT DEFAULT NULL,
    "branchCode" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "SyncToken" INTEGER DEFAULT NULL,
    "docTypeCode" TEXT DEFAULT '15',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "vatProjectName" TEXT DEFAULT NULL,
    "preGrossAmount" REAL DEFAULT NULL,
    "preTaxAmount" REAL DEFAULT NULL,
    "preNetAmount" REAL DEFAULT NULL,
    "deliveryTermsCode" TEXT DEFAULT NULL,
    "branchName" TEXT DEFAULT NULL,
    "sadNumber" TEXT DEFAULT NULL,
    "office" TEXT DEFAULT NULL,
    "cif" TEXT DEFAULT NULL,
    "wareHouseNumber" TEXT DEFAULT NULL,
    "wareHouseName" TEXT DEFAULT NULL,
    "destinationCountry" TEXT DEFAULT NULL,
    "originCountry" TEXT DEFAULT NULL,
    "importExportFlag" TEXT DEFAULT NULL,
    "confirmStatus" TEXT DEFAULT NULL,
    "valuationMethod" TEXT DEFAULT NULL,
    "prn" TEXT DEFAULT NULL,
    "totalWeight" REAL DEFAULT NULL,
    "vatProjectId" TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbldeliverytermscodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbldevices" (
    "id" INTEGER PRIMARY KEY,
    "devicemodel" TEXT DEFAULT NULL,
    "deviceno" TEXT NOT NULL,
    "devicestatus" INTEGER DEFAULT NULL,
    "offlineamount" REAL DEFAULT NULL,
    "offlinedays" INTEGER DEFAULT NULL,
    "offlinevalue" REAL DEFAULT NULL,
    "devicemac" TEXT DEFAULT NULL,
    "branchCode" TEXT DEFAULT NULL,
    "branch" INTEGER DEFAULT NULL,
    "branchId" TEXT DEFAULT NULL,
    "deviceType" INTEGER DEFAULT NULL,
    "validPeriod" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbldevicestatuses" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbldevicetypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbledcdetails" (
    "id" INTEGER PRIMARY KEY,
    "invoiceid" INTEGER NOT NULL,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "tankNo" TEXT NOT NULL,
    "pumpNo" TEXT NOT NULL,
    "nozzleNo" TEXT DEFAULT NULL,
    "controllerNo" TEXT DEFAULT NULL,
    "acquisitionEquipmentNo" TEXT DEFAULT NULL,
    "levelGaugeNo" TEXT DEFAULT NULL,
    "mvrn" TEXT DEFAULT NULL,
    "updateTimes" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblefrismode" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblenforcetaxexclusionlist" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT DEFAULT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblentitytypes" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblerpauditlogs" (
    "id" INTEGER PRIMARY KEY,
    "windowsuser" TEXT DEFAULT NULL,
    "ipaddress" TEXT DEFAULT NULL,
    "macaddress" TEXT DEFAULT NULL,
    "systemname" TEXT DEFAULT NULL,
    "voucherNumber" TEXT DEFAULT NULL,
    "voucherRef" TEXT DEFAULT NULL,
    "productCode" TEXT DEFAULT NULL,
    "responseCode" TEXT DEFAULT NULL,
    "responseMessage" TEXT DEFAULT NULL,
    "TIN" TEXT DEFAULT NULL,
    "description" TEXT NOT NULL,
    "payload" TEXT,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblerpdocumentypes" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "category" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblerptypes" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbleventnotifications" (
    "id" INTEGER PRIMARY KEY,
    "notificationtype" INTEGER DEFAULT NULL,
    "notificationsubtype" INTEGER DEFAULT NULL,
    "event" INTEGER DEFAULT NULL,
    "templateid" INTEGER DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "status" INTEGER DEFAULT '0',
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblevents" (
    "id" INTEGER PRIMARY KEY,
    "module" INTEGER DEFAULT NULL,
    "submodule" INTEGER DEFAULT NULL,
    "operation" INTEGER DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblexcisedutydetailslist" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT DEFAULT NULL,
    "name" TEXT DEFAULT NULL,
    "description" TEXT DEFAULT NULL,
    "currency" INTEGER DEFAULT NULL,
    "exciseDutyId" TEXT DEFAULT NULL,
    "uraid" TEXT DEFAULT NULL,
    "rate" TEXT DEFAULT NULL,
    "type" INTEGER DEFAULT NULL,
    "unit" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblexcisedutylist" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT DEFAULT NULL,
    "name" TEXT DEFAULT NULL,
    "description" TEXT DEFAULT NULL,
    "effectiveDate" TEXT DEFAULT NULL,
    "goodService" TEXT DEFAULT NULL,
    "uraid" TEXT DEFAULT NULL,
    "parentClass" TEXT DEFAULT NULL,
    "rateText" TEXT DEFAULT NULL,
    "isLeafNode" TEXT,
    "parentCode" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblexcisedutytypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblexportrateunits" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "validPeriodFrom" TEXT DEFAULT NULL,
    "periodTo" TEXT DEFAULT NULL,
    "status" TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblfeesmapping" (
    "id" INTEGER PRIMARY KEY,
    "feecode" TEXT NOT NULL,
    "productcode" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblfieldgroups" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT DEFAULT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblfields" (
    "id" INTEGER PRIMARY KEY,
    "fieldorder" INTEGER DEFAULT '0',
    "primarykeyflg" TEXT DEFAULT 'N',
    "code" TEXT DEFAULT NULL,
    "groupid" INTEGER DEFAULT NULL,
    "groupcode" TEXT DEFAULT NULL,
    "name" TEXT NOT NULL,
    "datatype" TEXT NOT NULL,
    "length" INTEGER NOT NULL,
    "otherdetails" TEXT NOT NULL,
    "fullsql" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblfileuploads" (
    "id" INTEGER PRIMARY KEY,
    "filetype" TEXT DEFAULT NULL,
    "uploadname" TEXT DEFAULT NULL,
    "folder" TEXT DEFAULT NULL,
    "description" TEXT DEFAULT NULL,
    "fullpath" TEXT DEFAULT NULL,
    "bytes" INTEGER DEFAULT '0',
    "records" INTEGER DEFAULT NULL,
    "systemname" TEXT DEFAULT NULL,
    "status" INTEGER DEFAULT '4',
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblflags" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblgooddetailgroups" (
    "id" INTEGER PRIMARY KEY,
    "owner" INTEGER NOT NULL,
    "entitytype" INTEGER DEFAULT NULL,
    "description" TEXT DEFAULT NULL,
    "inserteddt" TEXT NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblgooddetails" (
    "id" INTEGER PRIMARY KEY,
    "groupid" INTEGER NOT NULL DEFAULT '0',
    "item" TEXT NOT NULL,
    "itemcode" TEXT NOT NULL,
    "qty" REAL DEFAULT NULL,
    "unitofmeasure" TEXT DEFAULT NULL,
    "unitprice" REAL DEFAULT NULL,
    "total" REAL DEFAULT '0.00000000',
    "taxid" INTEGER DEFAULT NULL,
    "taxrate" REAL DEFAULT '0.00000000',
    "tax" REAL DEFAULT '0.00000000',
    "discounttotal" REAL DEFAULT '0.00000000',
    "discounttaxrate" REAL DEFAULT '0.00000000',
    "discountpercentage" REAL DEFAULT '0.00000000',
    "ordernumber" INTEGER DEFAULT NULL,
    "discountflag" INTEGER NOT NULL DEFAULT '2',
    "deemedflag" INTEGER NOT NULL DEFAULT '2',
    "exciseflag" INTEGER NOT NULL DEFAULT '2',
    "categoryid" INTEGER DEFAULT NULL,
    "categoryname" TEXT DEFAULT NULL,
    "goodscategoryid" INTEGER NOT NULL,
    "goodscategoryname" TEXT DEFAULT NULL,
    "exciserate" TEXT DEFAULT NULL,
    "exciserule" INTEGER DEFAULT NULL,
    "excisetax" REAL DEFAULT NULL,
    "pack" REAL DEFAULT NULL,
    "stick" REAL DEFAULT NULL,
    "exciseunit" INTEGER DEFAULT NULL,
    "excisecurrency" TEXT DEFAULT NULL,
    "exciseratename" TEXT DEFAULT NULL,
    "taxcategory" TEXT DEFAULT NULL,
    "displayCategoryCode" TEXT DEFAULT NULL,
    "unitofmeasurename" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "invoiceItemId" TEXT DEFAULT NULL,
    "vatApplicableFlag" TEXT DEFAULT NULL,
    "vatProjectId" TEXT DEFAULT NULL,
    "vatProjectName" TEXT DEFAULT NULL,
    "hsCode" TEXT DEFAULT NULL,
    "hsName" TEXT DEFAULT NULL,
    "totalWeight" TEXT DEFAULT NULL,
    "pieceQty" REAL DEFAULT NULL,
    "deemedExemptCode" TEXT DEFAULT NULL,
    "pieceMeasureUnit" TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblgoodsstockadjustment" (
    "id" INTEGER PRIMARY KEY,
    "operationType" INTEGER DEFAULT NULL,
    "supplierTin" TEXT DEFAULT NULL,
    "supplierName" TEXT DEFAULT NULL,
    "adjustType" INTEGER DEFAULT NULL,
    "remarks" TEXT DEFAULT NULL,
    "stockInDate" TEXT NOT NULL,
    "stockInType" INTEGER DEFAULT NULL,
    "productionBatchNo" TEXT DEFAULT NULL,
    "productionDate" TEXT NOT NULL,
    "commodityGoodsId" TEXT DEFAULT NULL,
    "quantity" INTEGER DEFAULT NULL,
    "unitPrice" REAL DEFAULT NULL,
    "ProductCode" TEXT DEFAULT NULL,
    "voucherType" TEXT DEFAULT NULL,
    "voucherTypeName" TEXT DEFAULT NULL,
    "voucherNumber" TEXT DEFAULT NULL,
    "voucherRef" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblgoodsstocktransfer" (
    "id" INTEGER PRIMARY KEY,
    "sourceBranchId" TEXT DEFAULT NULL,
    "destinationBranchId" TEXT DEFAULT NULL,
    "transferTypeCode" INTEGER DEFAULT '101',
    "remarks" TEXT DEFAULT NULL,
    "commodityGoodsId" TEXT DEFAULT NULL,
    "quantity" INTEGER DEFAULT NULL,
    "ProductCode" TEXT DEFAULT NULL,
    "voucherType" TEXT DEFAULT NULL,
    "voucherTypeName" TEXT DEFAULT NULL,
    "voucherNumber" TEXT DEFAULT NULL,
    "voucherRef" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblgoodstransfertypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblgoodstype" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblgoodstypecodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblhscodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "isLeaf" TEXT DEFAULT NULL,
    "parentClass" TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblimportedsellers" (
    "id" INTEGER PRIMARY KEY,
    "erpimportedsellerid" INTEGER DEFAULT NULL,
    "erpimportedsellercode" TEXT DEFAULT NULL,
    "tin" TEXT DEFAULT NULL,
    "ninbrn" TEXT DEFAULT NULL,
    "PassportNum" TEXT DEFAULT NULL,
    "legalname" TEXT DEFAULT NULL,
    "businessname" TEXT DEFAULT NULL,
    "address" TEXT DEFAULT NULL,
    "mobilephone" TEXT DEFAULT NULL,
    "linephone" TEXT DEFAULT NULL,
    "emailaddress" TEXT DEFAULT NULL,
    "placeofbusiness" TEXT DEFAULT NULL,
    "type" TEXT NOT NULL,
    "citizineship" TEXT DEFAULT NULL,
    "sector" TEXT DEFAULT NULL,
    "referenceno" TEXT DEFAULT NULL,
    "datasource" TEXT DEFAULT 'MW',
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblimportedservices" (
    "id" INTEGER PRIMARY KEY,
    "gooddetailgroupid" INTEGER DEFAULT NULL,
    "taxdetailgroupid" INTEGER DEFAULT NULL,
    "paymentdetailgroupid" INTEGER DEFAULT NULL,
    "erpinvoiceid" TEXT DEFAULT NULL,
    "erpinvoiceno" TEXT DEFAULT NULL,
    "antifakecode" TEXT DEFAULT NULL,
    "deviceno" TEXT DEFAULT NULL,
    "issueddate" TEXT DEFAULT NULL,
    "issuedtime" TEXT DEFAULT NULL,
    "operator" TEXT DEFAULT NULL,
    "currency" TEXT DEFAULT NULL,
    "oriinvoiceid" TEXT DEFAULT NULL,
    "invoicetype" INTEGER DEFAULT NULL,
    "invoicekind" INTEGER DEFAULT NULL,
    "datasource" INTEGER DEFAULT NULL,
    "invoiceindustrycode" INTEGER DEFAULT NULL,
    "einvoiceid" TEXT DEFAULT NULL,
    "einvoicenumber" TEXT DEFAULT NULL,
    "einvoicedatamatrixcode" TEXT DEFAULT NULL,
    "isbatch" TEXT DEFAULT '0',
    "netamount" REAL DEFAULT NULL,
    "taxamount" REAL DEFAULT NULL,
    "grossamount" REAL DEFAULT NULL,
    "origrossamount" REAL DEFAULT NULL,
    "itemcount" INTEGER DEFAULT '0',
    "modecode" TEXT DEFAULT '1',
    "modename" TEXT DEFAULT NULL,
    "remarks" TEXT DEFAULT NULL,
    "buyerid" INTEGER DEFAULT NULL,
    "sellerid" INTEGER DEFAULT NULL,
    "issueddatepdf" TEXT DEFAULT NULL,
    "grossamountword" TEXT DEFAULT NULL,
    "isinvalid" INTEGER DEFAULT NULL,
    "isrefund" INTEGER DEFAULT NULL,
    "vouchertype" TEXT DEFAULT NULL,
    "vouchertypename" TEXT DEFAULT NULL,
    "currencyRate" REAL DEFAULT '0.00000000',
    "branchCode" TEXT DEFAULT NULL,
    "branchId" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "SyncToken" INTEGER DEFAULT NULL,
    "docTypeCode" TEXT DEFAULT '10',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblimportservicessellers" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "invoiceid" INTEGER NOT NULL,
    "importBusinessName" TEXT NOT NULL,
    "importEmailAddress" TEXT NOT NULL,
    "importContactNumber" TEXT DEFAULT NULL,
    "importAddress" TEXT DEFAULT NULL,
    "importInvoiceDate" TEXT DEFAULT NULL,
    "importAttachmentName" TEXT DEFAULT NULL,
    "importAttachmentContent" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblindustries" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblinvoicekinds" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblinvoices" (
    "id" INTEGER PRIMARY KEY,
    "gooddetailgroupid" INTEGER DEFAULT NULL,
    "taxdetailgroupid" INTEGER DEFAULT NULL,
    "paymentdetailgroupid" INTEGER DEFAULT NULL,
    "erpinvoiceid" TEXT DEFAULT NULL,
    "erpinvoiceno" TEXT DEFAULT NULL,
    "antifakecode" TEXT DEFAULT NULL,
    "deviceno" TEXT DEFAULT NULL,
    "issueddate" TEXT DEFAULT NULL,
    "issuedtime" TEXT DEFAULT NULL,
    "operator" TEXT DEFAULT NULL,
    "currency" TEXT DEFAULT NULL,
    "oriinvoiceid" TEXT DEFAULT NULL,
    "invoicetype" INTEGER DEFAULT NULL,
    "invoicekind" INTEGER DEFAULT NULL,
    "datasource" INTEGER DEFAULT NULL,
    "invoiceindustrycode" INTEGER DEFAULT NULL,
    "einvoiceid" TEXT DEFAULT NULL,
    "einvoicenumber" TEXT DEFAULT NULL,
    "einvoicedatamatrixcode" TEXT DEFAULT NULL,
    "isbatch" TEXT DEFAULT '0',
    "netamount" REAL DEFAULT NULL,
    "taxamount" REAL DEFAULT NULL,
    "grossamount" REAL DEFAULT NULL,
    "origrossamount" REAL DEFAULT NULL,
    "itemcount" INTEGER DEFAULT '0',
    "modecode" TEXT DEFAULT '1',
    "modename" TEXT DEFAULT NULL,
    "remarks" TEXT DEFAULT NULL,
    "buyerid" INTEGER DEFAULT NULL,
    "sellerid" INTEGER DEFAULT NULL,
    "issueddatepdf" TEXT DEFAULT NULL,
    "grossamountword" TEXT DEFAULT NULL,
    "isinvalid" INTEGER DEFAULT NULL,
    "isrefund" INTEGER DEFAULT NULL,
    "vouchertype" TEXT DEFAULT NULL,
    "vouchertypename" TEXT DEFAULT NULL,
    "currencyRate" REAL DEFAULT '0.00000000',
    "branchCode" TEXT DEFAULT NULL,
    "branchId" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "SyncToken" INTEGER DEFAULT NULL,
    "docTypeCode" TEXT DEFAULT '10',
    "erpUpdateFlag" INTEGER DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "preGrossAmount" REAL DEFAULT NULL,
    "preTaxAmount" REAL DEFAULT NULL,
    "preNetAmount" REAL DEFAULT NULL,
    "deliveryTermsCode" TEXT DEFAULT NULL,
    "branchName" TEXT DEFAULT NULL,
    "sadNumber" TEXT DEFAULT NULL,
    "office" TEXT DEFAULT NULL,
    "cif" TEXT DEFAULT NULL,
    "wareHouseNumber" TEXT DEFAULT NULL,
    "wareHouseName" TEXT DEFAULT NULL,
    "destinationCountry" TEXT DEFAULT NULL,
    "originCountry" TEXT DEFAULT NULL,
    "importExportFlag" TEXT DEFAULT NULL,
    "confirmStatus" TEXT DEFAULT NULL,
    "valuationMethod" TEXT DEFAULT NULL,
    "prn" TEXT DEFAULT NULL,
    "totalWeight" REAL DEFAULT NULL,
    "vatProjectId" TEXT DEFAULT NULL,
    "vatProjectName" TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblinvoicetypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblmodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblmodules" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblnotifications" (
    "id" INTEGER PRIMARY KEY,
    "notificationtype" INTEGER DEFAULT NULL,
    "notificationsubtype" INTEGER DEFAULT NULL,
    "operation" INTEGER DEFAULT NULL,
    "event" INTEGER DEFAULT NULL,
    "eventnotification" INTEGER DEFAULT NULL,
    "module" INTEGER DEFAULT NULL,
    "submodule" INTEGER DEFAULT NULL,
    "entitytype" INTEGER DEFAULT NULL,
    "recipient" INTEGER DEFAULT NULL,
    "status" INTEGER DEFAULT NULL,
    "notification" TEXT NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblnotificationsubtypes" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "status" INTEGER DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblnotificationtypes" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "status" INTEGER DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblopeningstocklogs" (
    "id" INTEGER PRIMARY KEY,
    "batchid" INTEGER DEFAULT NULL,
    "fileid" INTEGER DEFAULT NULL,
    "filename" TEXT DEFAULT NULL,
    "runid" INTEGER DEFAULT NULL,
    "activity" TEXT DEFAULT NULL,
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblopeningstockruns" (
    "id" INTEGER PRIMARY KEY,
    "batchid" INTEGER DEFAULT NULL,
    "fileid" INTEGER DEFAULT NULL,
    "filename" TEXT DEFAULT NULL,
    "runid" INTEGER DEFAULT NULL,
    "statusid" INTEGER DEFAULT NULL,
    "errorcount" INTEGER DEFAULT '0',
    "runparameters" TEXT DEFAULT NULL,
    "environmentvariables" TEXT DEFAULT NULL,
    "tempfiletablename" TEXT DEFAULT NULL,
    "tempcbstablename" TEXT DEFAULT NULL,
    "startdt" TEXT DEFAULT NULL,
    "enddt" TEXT DEFAULT NULL,
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbloperations" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblorganisations" (
    "id" INTEGER PRIMARY KEY,
    "taxpayerid" TEXT DEFAULT NULL,
    "tin" TEXT NOT NULL,
    "ninbrn" TEXT DEFAULT NULL,
    "legalname" TEXT NOT NULL,
    "businessname" TEXT DEFAULT NULL,
    "address" TEXT DEFAULT NULL,
    "mobilephone" TEXT DEFAULT NULL,
    "linephone" TEXT DEFAULT NULL,
    "emailaddress" TEXT DEFAULT NULL,
    "placeofbusiness" TEXT DEFAULT NULL,
    "latitude" TEXT DEFAULT NULL,
    "longitude" TEXT DEFAULT NULL,
    "taxpayerRegistrationStatusId" INTEGER DEFAULT NULL,
    "taxpayerStatusId" INTEGER DEFAULT NULL,
    "taxpayerType" INTEGER DEFAULT NULL,
    "businessType" INTEGER DEFAULT NULL,
    "isAllowIssueCreditWithoutFDN" INTEGER DEFAULT '0',
    "isDutyFreeTaxpayer" INTEGER DEFAULT '0',
    "isAllowIssueRebate" INTEGER DEFAULT '0',
    "isReferenceNumberMandatory" INTEGER DEFAULT '0',
    "isAllowBackDate" INTEGER DEFAULT '0',
    "issueTaxTypeRestrictions" INTEGER DEFAULT '0',
    "goodsStockLimit" INTEGER DEFAULT '101',
    "maxGrossAmount" REAL DEFAULT '0.00000000',
    "exportInvoiceExciseDuty" INTEGER DEFAULT '0',
    "exportCommodityTaxRate" REAL DEFAULT '0.00000000',
    "isTaxCategoryCodeMandatory" INTEGER DEFAULT '0',
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "hsCodeVersion" TEXT DEFAULT NULL,
    "issueDebitNote" TEXT DEFAULT NULL,
    "qrCodeURL" TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblotherunits" (
    "id" INTEGER PRIMARY KEY,
    "productid" INTEGER NOT NULL,
    "otherunit" TEXT DEFAULT NULL,
    "otherscaled" REAL DEFAULT NULL,
    "otherPrice" REAL DEFAULT NULL,
    "packagescaled" REAL DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblpaymentdetailgroups" (
    "id" INTEGER PRIMARY KEY,
    "owner" INTEGER NOT NULL,
    "entitytype" INTEGER DEFAULT NULL,
    "description" TEXT DEFAULT NULL,
    "inserteddt" TEXT NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblpaymentdetails" (
    "id" INTEGER PRIMARY KEY,
    "groupid" INTEGER NOT NULL,
    "paymentmode" INTEGER NOT NULL,
    "paymentmodename" TEXT DEFAULT NULL,
    "paymentamount" REAL NOT NULL,
    "ordernumber" INTEGER DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblpaymentmodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblpermissiondetails" (
    "id" INTEGER PRIMARY KEY,
    "groupid" INTEGER DEFAULT NULL,
    "code" TEXT NOT NULL,
    "value" INTEGER DEFAULT NULL,
    "inheritedflag" INTEGER DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblpermissiongroups" (
    "id" INTEGER PRIMARY KEY,
    "owner" INTEGER NOT NULL,
    "entitytype" INTEGER DEFAULT NULL,
    "description" TEXT DEFAULT NULL,
    "inserteddt" TEXT NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblpermissions" (
    "id" INTEGER PRIMARY KEY,
    "module" INTEGER DEFAULT NULL,
    "submodule" INTEGER DEFAULT NULL,
    "operation" INTEGER DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblplatformmode" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblpriorities" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblproductdetails" (
    "id" INTEGER PRIMARY KEY,
    "uraproductidentifier" TEXT DEFAULT NULL,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "name" TEXT NOT NULL,
    "code" TEXT NOT NULL,
    "measureunit" TEXT DEFAULT NULL,
    "unitprice" REAL DEFAULT '0.00000000',
    "currency" INTEGER DEFAULT NULL,
    "commoditycategorycode" TEXT DEFAULT NULL,
    "hasexcisetax" INTEGER DEFAULT '102',
    "description" TEXT DEFAULT NULL,
    "stockprewarning" REAL DEFAULT '0.00000000',
    "piecemeasureunit" TEXT DEFAULT NULL,
    "havepieceunit" INTEGER DEFAULT '102',
    "pieceunitprice" REAL DEFAULT NULL,
    "packagescaledvalue" REAL DEFAULT NULL,
    "piecescaledvalue" REAL DEFAULT NULL,
    "excisedutylist" TEXT DEFAULT NULL,
    "exciseDutyCode" TEXT DEFAULT NULL,
    "exciseDutyName" TEXT DEFAULT NULL,
    "exciseRate" TEXT DEFAULT NULL,
    "uraquantity" REAL DEFAULT '0.00000000',
    "erpquantity" REAL DEFAULT '0.00000000',
    "purchaseprice" REAL DEFAULT NULL,
    "stockintype" TEXT DEFAULT NULL,
    "haveotherunit" INTEGER DEFAULT '102',
    "isexempt" INTEGER DEFAULT '102',
    "iszerorated" INTEGER DEFAULT '102',
    "taxrate" REAL DEFAULT '0.18000000',
    "statuscode" INTEGER DEFAULT '101',
    "source" INTEGER DEFAULT '102',
    "exclusion" INTEGER DEFAULT '2',
    "pack" REAL DEFAULT NULL,
    "stick" REAL DEFAULT NULL,
    "serviceMark" INTEGER DEFAULT '102',
    "goodsTypeCode" INTEGER DEFAULT '101',
    "remarks" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL,
    "hsCode" TEXT DEFAULT NULL,
    "hsName" TEXT DEFAULT NULL,
    "haveCustomsUnit" INTEGER DEFAULT NULL,
    "customsmeasureunit" TEXT DEFAULT NULL,
    "customsunitprice" REAL DEFAULT NULL,
    "packagescaledvaluecustoms" REAL DEFAULT NULL,
    "customsscaledvalue" REAL DEFAULT NULL,
    "weight" REAL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblproductexclusioncodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblproductoverridelist" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "name" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblproductsourcecodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblproductstatuscodes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblpurchaseorders" (
    "id" INTEGER PRIMARY KEY,
    "gooddetailgroupid" INTEGER DEFAULT NULL,
    "taxdetailgroupid" INTEGER DEFAULT NULL,
    "paymentdetailgroupid" INTEGER DEFAULT NULL,
    "erpvoucherid" TEXT DEFAULT NULL,
    "erpvoucherno" TEXT DEFAULT NULL,
    "issueddate" TEXT DEFAULT NULL,
    "issuedtime" TEXT DEFAULT NULL,
    "operator" TEXT DEFAULT NULL,
    "currency" TEXT DEFAULT NULL,
    "datasource" INTEGER DEFAULT NULL,
    "netamount" REAL DEFAULT NULL,
    "taxamount" REAL DEFAULT NULL,
    "grossamount" REAL DEFAULT NULL,
    "itemcount" INTEGER DEFAULT '0',
    "remarks" TEXT DEFAULT NULL,
    "supplierid" INTEGER DEFAULT NULL,
    "grossamountword" TEXT DEFAULT NULL,
    "vouchertype" TEXT DEFAULT NULL,
    "vouchertypename" TEXT DEFAULT NULL,
    "branchCode" TEXT DEFAULT NULL,
    "branchId" TEXT DEFAULT NULL,
    "SyncToken" INTEGER DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "procStatus" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblrateunits" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblreportformats" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblreportgroups" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblreports" (
    "id" INTEGER PRIMARY KEY,
    "groupid" INTEGER NOT NULL,
    "groupcode" TEXT NOT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "status" INTEGER NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblroles" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "status" INTEGER NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "permissiongroup" INTEGER DEFAULT NULL,
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblsectors" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT DEFAULT NULL,
    "name" TEXT DEFAULT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblsellers" (
    "id" INTEGER PRIMARY KEY,
    "tin" TEXT NOT NULL,
    "ninbrn" TEXT DEFAULT NULL,
    "legalname" TEXT NOT NULL,
    "businessname" TEXT DEFAULT NULL,
    "address" TEXT DEFAULT NULL,
    "mobilephone" TEXT DEFAULT NULL,
    "linephone" TEXT DEFAULT NULL,
    "emailaddress" TEXT NOT NULL,
    "placeofbusiness" TEXT DEFAULT NULL,
    "referenceno" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblsettinggroups" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblsettings" (
    "id" INTEGER PRIMARY KEY,
    "groupid" INTEGER NOT NULL,
    "groupcode" TEXT NOT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "value" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "sensitivityflag" INTEGER NOT NULL,
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblsqlcache" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT DEFAULT NULL,
    "regenerateflg" TEXT DEFAULT 'N',
    "sql" TEXT,
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblstatuses" (
    "id" INTEGER PRIMARY KEY,
    "groupid" INTEGER NOT NULL,
    "groupcode" TEXT NOT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblstatusgroups" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblstockadjustmenttypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblstockintypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblstocklimits" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblstockoperationtypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblsubmodules" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblsuppliers" (
    "id" INTEGER PRIMARY KEY,
    "erpsupplierid" TEXT DEFAULT NULL,
    "erpsuppliercode" TEXT DEFAULT NULL,
    "tin" TEXT DEFAULT NULL,
    "ninbrn" TEXT DEFAULT NULL,
    "PassportNum" TEXT DEFAULT NULL,
    "legalname" TEXT NOT NULL,
    "businessname" TEXT DEFAULT NULL,
    "address" TEXT DEFAULT NULL,
    "mobilephone" TEXT DEFAULT NULL,
    "linephone" TEXT DEFAULT NULL,
    "emailaddress" TEXT DEFAULT NULL,
    "placeofbusiness" TEXT DEFAULT NULL,
    "type" TEXT NOT NULL DEFAULT '0',
    "citizineship" TEXT DEFAULT NULL,
    "countryCode" TEXT DEFAULT NULL,
    "sector" TEXT DEFAULT NULL,
    "sectorCode" TEXT DEFAULT NULL,
    "datasource" TEXT DEFAULT 'MW',
    "status" INTEGER DEFAULT '1036',
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltaxdetailgroups" (
    "id" INTEGER PRIMARY KEY,
    "owner" INTEGER NOT NULL,
    "entitytype" INTEGER DEFAULT NULL,
    "description" TEXT DEFAULT NULL,
    "inserteddt" TEXT NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltaxdetails" (
    "id" INTEGER PRIMARY KEY,
    "groupid" INTEGER NOT NULL,
    "goodid" INTEGER DEFAULT NULL,
    "taxcategory" TEXT NOT NULL,
    "taxcategoryCode" TEXT DEFAULT NULL,
    "netamount" REAL DEFAULT NULL,
    "taxrate" REAL NOT NULL,
    "taxamount" REAL NOT NULL,
    "grossamount" REAL NOT NULL,
    "exciseunit" TEXT DEFAULT NULL,
    "excisecurrency" TEXT DEFAULT NULL,
    "taxratename" TEXT DEFAULT NULL,
    "taxdescription" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltaxpayerregistrationrtatuses" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltaxpayerstatuses" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltaxpayertypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltaxrates" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "category" TEXT NOT NULL,
    "rate" REAL NOT NULL,
    "displayCategoryCode" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltaxtypes" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "registrationdate" TEXT DEFAULT NULL,
    "cancellationdate" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltcsdetails" (
    "id" INTEGER PRIMARY KEY,
    "appid" TEXT DEFAULT NULL,
    "version" TEXT DEFAULT NULL,
    "dataexchangeid" TEXT NOT NULL DEFAULT '1',
    "requestcode" TEXT NOT NULL DEFAULT 'TP',
    "resposecode" TEXT NOT NULL DEFAULT 'TA',
    "username" TEXT NOT NULL DEFAULT 'admin',
    "responsedataformat" TEXT NOT NULL DEFAULT 'dd/MM/yyyy',
    "responsetimeformat" TEXT NOT NULL DEFAULT 'dd/MM/yyyy HH:mm:ss',
    "commodityCategoryVersion" TEXT DEFAULT NULL,
    "dictionaryVersion" TEXT DEFAULT NULL,
    "exciseDutyVersion" TEXT DEFAULT NULL,
    "taxpayerBranchVersion" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT DEFAULT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltemplateformats" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "status" INTEGER DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltemplates" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "status" INTEGER DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tbltemplatetypes" (
    "id" INTEGER PRIMARY KEY,
    "code" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "status" INTEGER DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblurabranches" (
    "id" INTEGER PRIMARY KEY,
    "branchid" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "description" TEXT DEFAULT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "tblusers" (
    "id" INTEGER PRIMARY KEY,
    "erpid" TEXT DEFAULT NULL,
    "erpcode" TEXT DEFAULT NULL,
    "uraid" TEXT DEFAULT NULL,
    "uracode" TEXT DEFAULT NULL,
    "username" TEXT NOT NULL,
    "password" TEXT NOT NULL,
    "email" TEXT DEFAULT NULL,
    "firstname" TEXT NOT NULL,
    "middlename" TEXT DEFAULT NULL,
    "lastname" TEXT NOT NULL,
    "permissiongroup" INTEGER DEFAULT NULL,
    "status" INTEGER NOT NULL,
    "role" INTEGER NOT NULL,
    "branch" INTEGER NOT NULL,
    "disabled" INTEGER NOT NULL DEFAULT '0',
    "online" INTEGER NOT NULL DEFAULT '0',
    "lastlogindt" TEXT DEFAULT NULL,
    "lastActivityDate" TEXT DEFAULT NULL,
    "inserteddt" TEXT NOT NULL,
    "insertedby" INTEGER DEFAULT NULL,
    "modifieddt" TEXT DEFAULT NULL,
    "modifiedby" INTEGER DEFAULT NULL
);
