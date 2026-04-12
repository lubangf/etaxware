<?php
use QuickBooksOnline\API\DataService\DataService;
/**
 * @name Purchaseorder Controller
 * @desc This file is part of the etaxware system. The is the Purchaseorder controller class
 * @date 11-05-2020
 * @file Purchaseorder Controller.php
 * @path ./app/controller/Purchaseorder Controller.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
Class PurchaseorderController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules
    
    /**
     *	@name index
     *  @desc Loads the index page
     *	@return NULL
     *	@param NULL
     **/
    function index(){
        $operation = NULL; //tblevents
        $permission = 'VIEWPURCHASEORDERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Purchaseorder Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $this->f3->set('pagetitle','Purchase Orders');
            $this->f3->set('pageheader','PurchaseorderHeader.htm');
            $this->f3->set('pagecontent','Purchaseorder.htm');
            $this->f3->set('pagescripts','PurchaseorderFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Purchaseorder Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name list
     *  @desc List purchaseorders
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function list(){
        $operation = NULL; //tblevents
        $permission = 'VIEWPURCHASEORDERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Purchaseorder Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Purchaseorder Controller : list() : Processing list of purchase orders started", 'r');
            
            $data = array();
            
            $this->logger->write("Purchaseorders Controller : index() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $this->logger->write("Purchaseorder Controller : list() : Processing list of purchase orders started", 'r');
                $id = trim($this->f3->get('POST.purchaseordersid'));
                
                $this->logger->write("Purchaseorder Controller : list() : The purchase order id is : " . $id, 'r');
                
                if ($id !== '' || !empty($id)) {
                    
                    //$subquery = " '%" . $id . "%' ";
                    
                    $sql = 'SELECT  i.id "ID",
                        i.erpvoucherid "ERP Purchase Order Id",
                        i.erpvoucherno "ERP Purchase Order No",
                        i.issueddate "Issued Date",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblpurchaseorders i
                    LEFT JOIN tblusers s ON i.modifiedby = s.id
                    WHERE i.id = ' . $id . '
                    ORDER By i.id DESC';
                } else {
                    $sql = 'SELECT  i.id "ID",
                        i.erpvoucherid "ERP Purchase Order Id",
                        i.erpvoucherno "ERP Purchase Order No",
                        i.issueddate "Issued Date",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblpurchaseorders i
                    LEFT JOIN tblusers s ON i.modifiedby = s.id
                    ORDER By i.id DESC';
                }
                
                
                try {
                    $dtls = $this->db->exec($sql);
                    
                    $this->logger->write($this->db->log(TRUE), 'r');
                    foreach ($dtls as $obj) {
                        $data[] = $obj;
                    }
                } catch (Exception $e) {
                    $this->logger->write("Purchaseorder Controller : list() : The operation to list the purchase orders was not successful. The error message is " . $e->getMessage(), 'r');
                }
            } else {
                $this->logger->write("Purchaseorder Controller : index() : The user is not allowed to perform this function", 'r');
            }
            
            die(json_encode($data));
        }
    }
    
    /**
     *	@name view
     *  @desc View a purchase order
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = ''){
        $operation = NULL; //tblevents
        $permission = 'VIEWPURCHASEORDERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Purchaseorder Controller : view() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
                $id = trim($this->f3->get('PARAMS[id]'));
                $this->logger->write("Purchaseorder Controller : view() : The is a GET call & id to view is " . $id, 'r');
            } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
                $id = trim($v_id);
                $this->logger->write("Purchaseorder Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
            } else {
                $this->logger->write("Purchaseorder Controller : view() : The PO to view is not defined.", 'r');
            }
            
            if ($id) {
                // The Purchaseorder
                $purchaseorder = new purchaseorders($this->db);
                $purchaseorder->grossamount2 = 'FORMAT(grossamount, 2)';
                $purchaseorder->getByID($id);
                $this->logger->write($this->db->log(TRUE), 'r');
                $this->f3->set('purchaseorder', $purchaseorder);
                
                //The Supplier
                $supplier = new suppliers($this->db);
                $supplier->getByCode($purchaseorder->supplierid);
                $this->f3->set('supplier', $supplier);
                
                //The Goods
                try{
                    $goods = array();
                    
                    $temp = $this->db->exec(array('SELECT item, FORMAT(qty, 2) qty, unitofmeasure, FORMAT(unitprice, 2) unitprice, FORMAT(total, 2) total, displayCategoryCode taxcategory, unitofmeasurename, FORMAT(discounttotal, 2) discounttotal FROM tblgooddetails WHERE groupid = COALESCE(' . $purchaseorder->gooddetailgroupid . ', NULL) ORDER BY inserteddt ASC'));
                    
                    if (!empty($temp)) {
                        foreach ($temp as $obj) {
                            $goods[] = array(
                                'item' => empty($obj['item'])? '' : $obj['item'],
                                'qty' => empty($obj['qty'])? '' : $obj['qty'],
                                'unitofmeasure' => empty($obj['unitofmeasure'])? '' : $obj['unitofmeasure'],
                                'unitprice' => empty($obj['unitprice'])? '' : $obj['unitprice'],
                                'total' => empty($obj['total'])? '' : $obj['total'],
                                'taxcategory' => empty($obj['taxcategory'])? '' : $obj['taxcategory'],
                                'unitofmeasurename' => empty($obj['unitofmeasurename'])? '' : $obj['unitofmeasurename']
                            );
                        }
                    } else {
                        $goods = array(
                            "0" => array()
                        );
                    }
                } catch (Exception $e) {
                    $this->logger->write("Purchaseorder Controller : view() : The operation to retrieve goods was not successfull. The error messages is " . $e->getMessage(), 'r');
                    $goods = array(
                        "0" => array()
                    );
                }
                $this->f3->set('goods', $goods);
                //$this->logger->write($this->db->log(TRUE), 'r');
                
                //$this->f3->set('path', '../' . $this->path);
                $this->f3->set('pagetitle','View Purchase Order | ' . $id);//display the edit form
                
                $this->f3->set('pageheader','ViewPurchaseorderHeader.htm');
                $this->f3->set('pagecontent','ViewPurchaseorder.htm');
                $this->f3->set('pagescripts','ViewPurchaseorderFooter.htm');
                echo \Template::instance()->render('Layout.htm');
            } else {
                $this->logger->write("Purchaseorder Controller : view() : No id was specified.", 'r');
                $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page
                exit();
            }
        } else {
            $this->logger->write("Purchaseorder Controller : view() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    

    /**
     *	@name uploadpurchaseorder
     *  @desc upload an invoice to EFRIS
     *	@return
     *	@param
     **/
    function uploadpurchaseorder(){
        $operation = NULL; //tblevents
        $permission = 'UPLOADPURCHASEORDERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.efrisuploadpurchaseorderid');
        
        if ($id) {
            $purchaseorder = new purchaseorders($this->db);
            $purchaseorder->getByID($id);
            $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The PO id is " . $this->f3->get('POST.efrisuploadpurchaseorderid'), 'r');

            $supplier = new suppliers($this->db);
            $supplier->getByCode($purchaseorder->supplierid);
            
            $vchtype = $purchaseorder->vouchertype;
            $vchtypename = $purchaseorder->vouchertypename;
            $vchnumber = $purchaseorder->erpvoucherno;
            
            $suppliertin = $supplier->tin;
            $suppliername = $supplier->legalname;
            $this->logger->write("Api : batchstockin() : The supplier TIN is " . $suppliertin, 'r');
            $this->logger->write("Api : batchstockin() : The supplier legalname is " . $suppliername, 'r');
            
            if ($supplier->countryCode) {
                if (trim($supplier->countryCode) == trim($this->appsettings['LOCALCOUNTRYCODE']) ) {
                    $stockintype = $this->appsettings['LOCALPURCHASESTOCKINTYPE']; //local purchase
                } else {
                    $stockintype = $this->appsettings['IMPORTSTOCKINTYPE']; //import
                }
            } else {
                $stockintype = $this->appsettings['LOCALPURCHASESTOCKINTYPE'];//local purchase
            }
            
            
            
            $this->logger->write("Api : batchstockin() : The stockin type is " . $stockintype, 'r');
            
            $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : Checking permissions", 'r');
            
            if ($this->userpermissions[$permission]) {
                $vch_check = new DB\SQL\Mapper($this->db, 'tblgoodsstockadjustment');
                $vch_check->load(array('TRIM(voucherNumber)=? AND TRIM(voucherType)=? AND TRIM(voucherTypeName)=?', $vchnumber, $vchtype, $vchtypename));
                $this->logger->write($this->db->log(TRUE), 'r');
                
                if($vch_check->dry ()){
                    if (trim($stockintype) == $this->appsettings['MANUFACTURESTOCKINTYPE']) {//Manufacture
                        $productiondate = date('Y-m-d');
                        $batchno = $vchnumber;//1
                        $suppliername = '';
                        $suppliertin = '';
                    } else {
                        $productiondate = '';
                        $batchno = '';
                        $suppliername = trim($suppliername);
                        $suppliertin = trim($suppliertin);
                        
                        $errorCount = 0;
                        
                        /**
                         * @desc Validate TIN number, if supplied.
                         * @author frncslubanga@gmail.com
                         * @date 2022-06-13
                         *
                         */
                        
                        if (trim($suppliertin) == '' || empty($suppliertin)) {
                            $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The supplier TIN was not provided!", 'r');
                        } else {
                            $v_data = $this->util->querytaxpayer($this->f3->get('SESSION.id'), $suppliertin);//will return JSON.
                            $v_data = json_decode($v_data, true);
                            
                            if (isset($v_data['taxpayer'])){
                                //$tin = $v_data['taxpayer']['tin'];
                                $legalName = $v_data['taxpayer']['legalName'];
                                
                                $suppliername = $legalName; //Rename the supplier.
                                
                            } elseif (isset($v_data['returnCode'])){
                                $errorCount = $errorCount + 1;
                                $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The operation to validate the supplier TIN was not successful. The error message is " . $v_data['returnMessage'], 'r');
                                
                            } else {
                                $errorCount = $errorCount + 1;
                                $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The operation to validate the supplier TIN was not successful", 'r');
                                
                            }
                        }
                    }
                    
                    if((int)$errorCount == 0){
                        $products = array();
                        $temp = $this->db->exec(array('SELECT * FROM tblgooddetails g WHERE g.groupid = ' . $purchaseorder->gooddetailgroupid));
                        
                        if (isset($temp)) {
                            
                            if (sizeof($temp) == 0) {
                                $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : No goods were supplied!", 'r');
                                self::$systemalert = "Sorry. No goods were supplied!";
                            } else {
                                foreach ($temp as $obj){
                                    $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The PRODUCTCODE is: " . trim($obj['itemcode']), 'r');
                                    $products[] = array(
                                        'productCode' => trim($obj['itemcode']),//8762753
                                        'quantity' => trim($obj['qty']),//23.0
                                        'unitPrice' => trim($obj['unitprice'])//25000.00
                                    );
                                }
                                
                                $user = new users($this->db);
                                $user->getByID($this->f3->get('SESSION.id'));
                                
                                $branch = new branches($this->db);
                                $branch->getByID($user->branch);
                                
                                
                                $data = $this->util->batchstockin($this->f3->get('SESSION.id'), $branch->uraid, $products, $batchno, $suppliertin, $suppliername, $stockintype, $productiondate);//will return JSON.
                                
                                $data = json_decode($data, true);
                                
                                if(isset($data['returnCode'])){
                                    $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The operation to increase stock was not successful. The error message is " . $data['returnMessage'], 'r');
                                    self::$systemalert = "Sorry. The operation to increase stock was not successful.";
                                } else {
                                    if ($data) {
                                        
                                        foreach($data as $elem){
                                            $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The operation to increase stock was not successful. The error message is " . $elem['returnCode'] . " - " . $data['returnMessage'], 'r');
                                        }
                                        
                                        self::$systemalert = "Sorry. The operation to increase stock was not successful.";
                                    } else {
                                        $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The operation to increase stock was successful!", 'r');
                                        self::$systemalert = "The operation to increase stock was successful!";
                                        
                                        //Update the table tblpurchaseorders
                                        try {
                                            $this->db->exec(array('UPDATE tblpurchaseorders SET procStatus = 1, modifieddt = "' . date('Y-m-d H:i:s') . '", modifiedby = "' . $user->id . '" WHERE id = ' . $id));
                                        } catch (Exception $e) {
                                            $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : Failed to update table tblpurchaseorders. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                        foreach ($temp as $obj){
                                            $this->util->logstockadjustment($this->f3->get('SESSION.id'), trim($obj['itemcode']), $batchno, trim($obj['qty']), $suppliertin, $suppliername, $stockintype, $productiondate, trim($obj['unitprice']), trim($this->appsettings['STOCKINOPERATIONTYPE']), $vchtype, $vchtypename, $vchnumber, NULL, NULL, NULL);
                                        } 
                                    } 
                                }
                            }
                        } else {
                            $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : No goods were supplied!", 'r');
                            self::$systemalert = "Sorry. No goods were supplied!";
                            
                        } 
                    } else {
                        $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : An error occured, please re-upload!", 'r');
                        self::$systemalert = "Sorry. An error occured, please re-upload!";
                    }
                } else {
                    $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The purchase order has already been uploaded into EFRIS.", 'r');
                    self::$systemalert = "Sorry. The purchase order has already been uploaded into EFRIS.";
                }
            } else {
                $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The user is not allowed to perform this function", 'r');
                self::$systemalert = "Sorry. The user is not allowed to perform this function.";
            }
        } else {
            $this->logger->write("Purchaseorder Controller : uploadpurchaseorder() : The PO was not specified.", 'r');
            self::$systemalert = "Sorry. The PO was not specified.";
            
            $this->f3->set('systemalert', self::$systemalert);
            self::index();
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name downloadErpPurchaseorders
     *  @desc download purchaseorders from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function downloadErpPurchaseorders(){
        $operation = NULL; //tblevents
        $permission = 'SYNCHPURCHASEORDERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            
            $startDate = $this->f3->get('POST.downloaderppurchaseordersstartdate');
            $endDate = $this->f3->get('POST.downloaderppurchaseordersenddate');
            $purchaseorderNo = $this->f3->get('POST.downloaderppurchaseordernumber');
            
            $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : startDate: " . $startDate, 'r');
            $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : endDate: " . $endDate, 'r');
            $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : purchaseorderNo: " . $purchaseorderNo, 'r');
            
            $startDate = empty($startDate)? date('Y-m-d') : date('Y-m-d', strtotime($startDate));
            $endDate = empty($endDate)? date('Y-m-d') : date('Y-m-d', strtotime($endDate));
            //$purchaseorderNo = empty($purchaseorderNo)? 'NULL' : $purchaseorderNo;
            
            
            
            if ($this->platformMode == 'ERP') {
                $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
            } else {
                $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The platform is integrated.", 'r');
                
                if ($this->integratedErp) {
                    /**
                     * Check on integrated ERP type
                     */
                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                    
                    if (strtoupper($this->integratedErp) == 'QBO') {
                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The integrated ERP is Quicbooks Online.", 'r');
                        
                        
                        $qry = 'SELECT * FROM PurchaseOrder';
                        
                        if ($purchaseorderNo) {
                            $qry = $qry . " Where DocNumber = '" . $purchaseorderNo . "'";
                        } else {
                            $qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "' And Metadata.LastUpdatedTime <= '" . $endDate . "'";
                        }
                        
                        
                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The query is: " . $qry, 'r');
                        
                        try {
                            if ($this->f3->get('SESSION.sessionAccessToken') !== null) {
                                // Create SDK instance
                                $authMode = $this->appsettings['QBAUTH_MODE'];
                                $ClientID = $this->appsettings['QBCLIENT_ID'];
                                $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                $baseUrl = $this->appsettings['QBBASE_URL'];
                                $QBORealmID = $this->appsettings['QBREALMID'];
                                
                                $accessToken = $this->f3->get('SESSION.sessionAccessToken');
                                
                                $dataService = DataService::Configure(array(
                                    'auth_mode' => $authMode,
                                    'ClientID' => $ClientID,
                                    'ClientSecret' =>  $ClientSecret,
                                    'baseUrl' => $baseUrl,
                                    'refreshTokenKey' => $accessToken->getRefreshToken(),
                                    'QBORealmID' => $QBORealmID,
                                    'accessTokenKey' => $accessToken->getAccessToken()
                                ));
                                
                                $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                $dataService->throwExceptionOnError(true);
                                
                                $purchaseorders = $dataService->Query($qry);
                                
                                $error = $dataService->getLastError();
                                
                                if ($error) {
                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The operation to download ERP purchase orders was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchaseorders by " . $this->f3->get('SESSION.username') . " was successful");
                                    self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was successful.";
                                }
                                else {
                                    //print_r($purchaseorders);
                                    if(isset($purchaseorders)){
                                        if ($purchaseorders) {
                                            $purchaseorder = new purchaseorders($this->db);
                                            
                                            $goods = array();
                                            $grossamount = 0;
                                            $itemcount = 0;
                                            
                                            $product = new products($this->db);
                                            $measureunit = new measureunits($this->db);
                                            
                                            $purchaseorderdetails = array(
                                                'id' => NULL,
                                                'gooddetailgroupid' => NULL,
                                                'taxdetailgroupid' => NULL,
                                                'paymentdetailgroupid' => NULL,
                                                'erpvoucherid' => NULL,
                                                'erpvoucherno' => NULL,
                                                'issueddate' => NULL,
                                                'issuedtime' => NULL,
                                                'operator' => NULL,
                                                'currency' => NULL,
                                                'datasource' => $this->appsettings['DEFAULTDATASOURCE'],
                                                'netamount' => '0',
                                                'taxamount' => '0',
                                                'grossamount' => '0',
                                                'itemcount' => '0',
                                                'remarks' => NULL,
                                                'supplierid' => NULL,
                                                'grossamountword' => NULL,
                                                'vouchertype' => "Purchase Order",
                                                'vouchertypename' => "Purchase Order",
                                                'SyncToken' => NULL
                                            );
                                            
                                            foreach($purchaseorders as $elem){
                                                try {
                                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : PO Number: " . $elem->DocNumber, 'r');
                                                    
                                                    $VendorRef = $elem->VendorRef;
                                                    $DocNumber = $elem->DocNumber;
                                                    $CurrencyRef = $elem->CurrencyRef;
                                                    $TxnDate = $elem->TxnDate;
                                                    $POId = $elem->Id;
                                                    $SyncToken = $elem->SyncToken;
                                                    $TxnDate = $elem->TxnDate;
                                                    $POStatus = $elem->POStatus;
                                                    
                                                    if (strtoupper($elem->POStatus) == 'CLOSED') {
                                                        $purchaseorderdetails['erpvoucherid'] = $POId;
                                                        $purchaseorderdetails['erpvoucherno'] = $DocNumber;
                                                        $purchaseorderdetails['supplierid'] = $VendorRef;
                                                        
                                                        if(isset($elem->Line)){
                                                            $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : # of items: " . sizeof($elem->Line), 'r');
                                                            
                                                            if (sizeof($elem->Line) > 1) {
                                                                foreach($elem->Line as $items){
                                                                    $LineId = $items->Id;
                                                                    $LineNum = $items->LineNum;
                                                                    $Description = $items->Description;
                                                                    $ErpAmount = $items->Amount;
                                                                    $DetailType = $items->DetailType;
                                                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : Line Description: " . $Description, 'r');
                                                                    
                                                                    if (strtoupper($items->DetailType) == 'ITEMBASEDEXPENSELINEDETAIL') {
                                                                        if(isset($items->ItemBasedExpenseLineDetail)){
                                                                            $ItemRef = $items->ItemBasedExpenseLineDetail->ItemRef;
                                                                            $UnitPrice = $items->ItemBasedExpenseLineDetail->UnitPrice;
                                                                            $Qty = $items->ItemBasedExpenseLineDetail->Qty;
                                                                        }
                                                                        
                                                                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : Unit Price: " . $UnitPrice, 'r');
                                                                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : Qty: " . $Qty, 'r');
                                                                        
                                                                        
                                                                        $product->getByErpCode($ItemRef);
                                                                        
                                                                        if ($product->code) {
                                                                            $measureunit->getByCode($product->measureunit);
                                                                        } else {
                                                                            $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The Item does not exist on the platform", 'r');
                                                                        }
                                                                        
                                                                        $grossamount = $grossamount + $ErpAmount;
                                                                        $itemcount = $itemcount + 1;
                                                                        
                                                                        $goods[] = array(
                                                                            'groupid' => NULL,
                                                                            'item' => $product->name,
                                                                            'itemcode' => $product->code,
                                                                            'qty' => $Qty,
                                                                            'unitofmeasure' => $product->measureunit,
                                                                            'unitprice' => $UnitPrice,
                                                                            'total' => $ErpAmount,
                                                                            'taxid' => '0',
                                                                            'taxrate' => '0',
                                                                            'tax' => '0',
                                                                            'discounttotal' => '0',
                                                                            'discounttaxrate' => '0',
                                                                            'discountpercentage' => '0.00',
                                                                            'ordernumber' => NULL,
                                                                            'discountflag' => '2',
                                                                            'deemedflag' => '2',
                                                                            'exciseflag' => NULL,
                                                                            'categoryid' => NULL,
                                                                            'categoryname' => NULL,
                                                                            'goodscategoryid' => $product->commoditycategorycode,
                                                                            'goodscategoryname' => NULL,
                                                                            'exciserate' => NULL,
                                                                            'exciserule' => NULL,
                                                                            'excisetax' => NULL,
                                                                            'pack' => NULL,
                                                                            'stick' => NULL,
                                                                            'exciseunit' => NULL,
                                                                            'excisecurrency' => NULL,
                                                                            'exciseratename' => NULL,
                                                                            'taxdisplaycategory' => NULL,
                                                                            'taxcategory' => NULL,
                                                                            'taxcategoryCode' => NULL,
                                                                            'unitofmeasurename' => $measureunit->name
                                                                        );
                                                                    }
                                                                }//foreach($elem->Line as $items){
                                                            } elseif (sizeof($elem->Line) == 1) {
                                                                $LineId = $elem->Line->Id;
                                                                $LineNum = $elem->Line->LineNum;
                                                                $Description = $elem->Line->Description;
                                                                $ErpAmount = $elem->Line->Amount;
                                                                $DetailType = $elem->Line->DetailType;
                                                                $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : Line Description: " . $Description, 'r');
                                                                
                                                                if (strtoupper($elem->Line->DetailType) == 'ITEMBASEDEXPENSELINEDETAIL') {
                                                                    if(isset($elem->Line->ItemBasedExpenseLineDetail)){
                                                                        $ItemRef = $elem->Line->ItemBasedExpenseLineDetail->ItemRef;
                                                                        $UnitPrice = $elem->Line->ItemBasedExpenseLineDetail->UnitPrice;
                                                                        $Qty = $elem->Line->ItemBasedExpenseLineDetail->Qty;
                                                                    }
                                                                    
                                                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : Unit Price: " . $UnitPrice, 'r');
                                                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : Qty: " . $Qty, 'r');
                                                                    
                                                                    
                                                                    $product->getByErpCode($ItemRef);
                                                                    
                                                                    if ($product->code) {
                                                                        $measureunit->getByCode($product->measureunit);
                                                                    } else {
                                                                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The Item does not exist on the platform", 'r');
                                                                    }
                                                                    
                                                                    $grossamount = $grossamount + $ErpAmount;
                                                                    $itemcount = $itemcount + 1;
                                                                    
                                                                    $goods[] = array(
                                                                        'groupid' => NULL,
                                                                        'item' => $product->name,
                                                                        'itemcode' => $product->code,
                                                                        'qty' => $Qty,
                                                                        'unitofmeasure' => $product->measureunit,
                                                                        'unitprice' => $UnitPrice,
                                                                        'total' => $ErpAmount,
                                                                        'taxid' => '0',
                                                                        'taxrate' => '0',
                                                                        'tax' => '0',
                                                                        'discounttotal' => '0',
                                                                        'discounttaxrate' => '0',
                                                                        'discountpercentage' => '0.00',
                                                                        'ordernumber' => NULL,
                                                                        'discountflag' => '2',
                                                                        'deemedflag' => '2',
                                                                        'exciseflag' => NULL,
                                                                        'categoryid' => NULL,
                                                                        'categoryname' => NULL,
                                                                        'goodscategoryid' => $product->commoditycategorycode,
                                                                        'goodscategoryname' => NULL,
                                                                        'exciserate' => NULL,
                                                                        'exciserule' => NULL,
                                                                        'excisetax' => NULL,
                                                                        'pack' => NULL,
                                                                        'stick' => NULL,
                                                                        'exciseunit' => NULL,
                                                                        'excisecurrency' => NULL,
                                                                        'exciseratename' => NULL,
                                                                        'taxdisplaycategory' => NULL,
                                                                        'taxcategory' => NULL,
                                                                        'taxcategoryCode' => NULL,
                                                                        'unitofmeasurename' => $measureunit->name
                                                                    );
                                                                }
                                                            } else {
                                                                $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : There are no line items on the PO", 'r');
                                                            }
                                                        }//if(isset($elem->Line)){
                                                        
                                                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The GOODS count: " . sizeof($goods), 'r');
                                                        
                                                        $purchaseorderdetails['operator'] = $this->f3->get('SESSION.username');
                                                        $purchaseorderdetails['currency'] = $this->util->getcurrency(trim($CurrencyRef));
                                                        $purchaseorderdetails['SyncToken'] = $SyncToken;
                                                        $purchaseorderdetails['issueddate'] = $TxnDate;
                                                        $purchaseorderdetails['issuedtime'] = $TxnDate;
                                                        $purchaseorderdetails['itemcount'] = $itemcount;
                                                        
                                                        $purchaseorderdetails['grossamount'] = $grossamount;
                                                        
                                                        $purchaseorderdetails['remarks'] = "The PO DocNumber " . $DocNumber . " and Id " . $POId . " uploaded using the QBO API";
                                                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The Sync Token is " . $SyncToken, 'r');
                                                        
                                                        if ($POId) {
                                                            $purchaseorder->getByErpId($POId);
                                                            
                                                            if ($purchaseorder->dry()) {
                                                                $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The PO does not exist", 'r');
                                                                $po_status = $this->util->createpurchaseorder($purchaseorderdetails, $goods, $this->f3->get('SESSION.id'));
                                                                
                                                                if ($po_status) {
                                                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The PO " . $DocNumber . " was created.", 'r');
                                                                } else {
                                                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The PO " . $DocNumber . " was NOT created.", 'r');
                                                                }
                                                            } else {
                                                                $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The PO exists", 'r');
                                                                $purchaseorderdetails['id'] = $purchaseorder->id;
                                                                $purchaseorderdetails['gooddetailgroupid'] = $purchaseorder->gooddetailgroupid;
                                                                $purchaseorderdetails['taxdetailgroupid'] = $purchaseorder->taxdetailgroupid;
                                                                $purchaseorderdetails['paymentdetailgroupid'] = $purchaseorder->paymentdetailgroupid;
                                                                
                                                                if ($purchaseorder->procStatus == '1') {
                                                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The PO " . $DocNumber . " is already uploaded into EFRIS.", 'r');
                                                                    
                                                                } else {
                                                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The PO " . $DocNumber . " is NOT uploaded into EFRIS.", 'r');
                                                                    
                                                                    
                                                                    $po_status = $this->util->updatepurchaseorder($purchaseorderdetails, $goods, $this->f3->get('SESSION.id'));
                                                                    
                                                                    if ($po_status) {
                                                                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The PO " . $DocNumber . " was updated.", 'r');
                                                                    } else {
                                                                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The PO " . $DocNumber . " was NOT updated.", 'r');
                                                                    }
                                                                    
                                                                }
                                                            }
                                                        } else {
                                                            $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The PO has no Id.", 'r');
                                                        }
                                                    } else {
                                                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The PO is not Closed. The status is: " . $elem->POStatus, 'r');
                                                    }
                                                    
                                                } catch (Exception $e) {
                                                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : There was an error when processing PO " . $elem->DocNumber . ". The error is " . $e->getMessage(), 'r');
                                                }
                                            }//foreach($purchaseorders as $elem)
                                        } else {
                                            $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The operation to download ERP purchase orders did not return records.", 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchaseorders by " . $this->f3->get('SESSION.username') . " did not return records.");
                                            self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " did not return records.";
                                        }
                                    } else {
                                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The operation to download ERP purchase orders did not return records.", 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchaseorders by " . $this->f3->get('SESSION.username') . " did not return records.");
                                        self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " did not return records.";
                                    }
                                }
                                
                                $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The operation to download ERP purchase orders was successful.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchaseorders by " . $this->f3->get('SESSION.username') . " was successful");
                                self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was successful.";
                            } else {
                                $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The operation to download ERP purchase orders was not successful. Please connect to ERP first.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                            }
                            
                        } catch (Exception $e) {
                            $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The operation to download ERP purchase orders was not successful. The error is: " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                        }
                    } else {
                        $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The integrated ERP is unknown.", 'r');
                        self::$systemalert = "Sorry. The integrated ERP is unknown.";
                    }
                } else {
                    $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : We are unable to indentify the currently integrated ERP.", 'r');
                    self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                }
            }
        } else {
            $this->logger->write("Purchaseorder Controller : downloadErpPurchaseorders() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
    
    /**
     *	@name fetchErpPurchaseorder
     *  @desc download a purchase order from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function fetchErpPurchaseorder(){
        $operation = NULL; //tblevents
        $permission = 'SYNCHPURCHASEORDERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            
            $id = $this->f3->get('POST.erpdownloadpurchaseorderid');
            $purchaseorder = new purchaseorders($this->db);
            $purchaseorder->getByID($id);
            
            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : PO Id: " . $id, 'r');
            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : procStatus: " . $purchaseorder->procStatus, 'r');
            
            if ($id) {
                if ($purchaseorder->procStatus == '0') {
                    if ($this->platformMode == 'ERP') {
                        $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                        self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
                    } else {
                        $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The platform is integrated.", 'r');
                        
                        if ($this->integratedErp) {
                            /**
                             * Check on integrated ERP type
                             */
                            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                            
                            if (strtoupper($this->integratedErp) == 'QBO') {
                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The integrated ERP is Quicbooks Online.", 'r');
                                
                                
                                $qry = 'SELECT * FROM PurchaseOrder';
                                
                                if ($purchaseorder->erpvoucherno) {
                                    $qry = $qry . " Where DocNumber = '" . $purchaseorder->erpvoucherno . "'";
                                    
                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The query is: " . $qry, 'r');
                                    
                                    try {
                                        if ($this->f3->get('SESSION.sessionAccessToken') !== null) {
                                            // Create SDK instance
                                            $authMode = $this->appsettings['QBAUTH_MODE'];
                                            $ClientID = $this->appsettings['QBCLIENT_ID'];
                                            $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                            $baseUrl = $this->appsettings['QBBASE_URL'];
                                            $QBORealmID = $this->appsettings['QBREALMID'];
                                            
                                            $accessToken = $this->f3->get('SESSION.sessionAccessToken');
                                            
                                            $dataService = DataService::Configure(array(
                                                'auth_mode' => $authMode,
                                                'ClientID' => $ClientID,
                                                'ClientSecret' =>  $ClientSecret,
                                                'baseUrl' => $baseUrl,
                                                'refreshTokenKey' => $accessToken->getRefreshToken(),
                                                'QBORealmID' => $QBORealmID,
                                                'accessTokenKey' => $accessToken->getAccessToken()
                                            ));
                                            
                                            $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                            $dataService->throwExceptionOnError(true);
                                            
                                            $purchaseorders = $dataService->Query($qry);
                                            
                                            $error = $dataService->getLastError();
                                            
                                            if ($error) {
                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The operation to download ERP purchase orders was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchaseorders by " . $this->f3->get('SESSION.username') . " was successful");
                                                self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was successful.";
                                            }
                                            else {
                                                //print_r($purchaseorders);
                                                if(isset($purchaseorders)){
                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The size of purchaseorders is: " . sizeof($purchaseorders), 'r');
                                                    
                                                    if(!empty($purchaseorders) && sizeof($purchaseorders) == 1){
                                                        //$purchaseorders = current($purchaseorders);
                                                        //$purchaseorder = new purchaseorders($this->db);
                                                        
                                                        $goods = array();
                                                        $grossamount = 0;
                                                        $itemcount = 0;
                                                        
                                                        $product = new products($this->db);
                                                        $measureunit = new measureunits($this->db);
                                                        
                                                        $purchaseorderdetails = array(
                                                            'id' => NULL,
                                                            'gooddetailgroupid' => NULL,
                                                            'taxdetailgroupid' => NULL,
                                                            'paymentdetailgroupid' => NULL,
                                                            'erpvoucherid' => NULL,
                                                            'erpvoucherno' => NULL,
                                                            'issueddate' => NULL,
                                                            'issuedtime' => NULL,
                                                            'operator' => NULL,
                                                            'currency' => NULL,
                                                            'datasource' => $this->appsettings['DEFAULTDATASOURCE'],
                                                            'netamount' => '0',
                                                            'taxamount' => '0',
                                                            'grossamount' => '0',
                                                            'itemcount' => '0',
                                                            'remarks' => NULL,
                                                            'supplierid' => NULL,
                                                            'grossamountword' => NULL,
                                                            'vouchertype' => "Purchase Order",
                                                            'vouchertypename' => "Purchase Order",
                                                            'SyncToken' => NULL
                                                        );
                                                        
                                                        foreach($purchaseorders as $elem){
                                                            try {
                                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : PO Number: " . $elem->DocNumber, 'r');
                                                                
                                                                $VendorRef = $elem->VendorRef;
                                                                $DocNumber = $elem->DocNumber;
                                                                $CurrencyRef = $elem->CurrencyRef;
                                                                $TxnDate = $elem->TxnDate;
                                                                $POId = $elem->Id;
                                                                $SyncToken = $elem->SyncToken;
                                                                $TxnDate = $elem->TxnDate;
                                                                $POStatus = $elem->POStatus;
                                                                
                                                                if (strtoupper($elem->POStatus) == 'CLOSED') {
                                                                    $purchaseorderdetails['erpvoucherid'] = $POId;
                                                                    $purchaseorderdetails['erpvoucherno'] = $DocNumber;
                                                                    $purchaseorderdetails['supplierid'] = $VendorRef;
                                                                    
                                                                    if(isset($elem->Line)){
                                                                        $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The # of items is: " . sizeof($elem->Line), 'r');
                                                                        if (sizeof($elem->Line) > 1) {
                                                                            foreach($elem->Line as $items){
                                                                                $LineId = $items->Id;
                                                                                $LineNum = $items->LineNum;
                                                                                $Description = $items->Description;
                                                                                $ErpAmount = $items->Amount;
                                                                                $DetailType = $items->DetailType;
                                                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : Line Description: " . $Description, 'r');
                                                                                
                                                                                if (strtoupper($items->DetailType) == 'ITEMBASEDEXPENSELINEDETAIL') {
                                                                                    if(isset($items->ItemBasedExpenseLineDetail)){
                                                                                        $ItemRef = $items->ItemBasedExpenseLineDetail->ItemRef;
                                                                                        $UnitPrice = $items->ItemBasedExpenseLineDetail->UnitPrice;
                                                                                        $Qty = $items->ItemBasedExpenseLineDetail->Qty;
                                                                                    }
                                                                                    
                                                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : Unit Price: " . $UnitPrice, 'r');
                                                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : Qty: " . $Qty, 'r');
                                                                                    
                                                                                    
                                                                                    $product->getByErpCode($ItemRef);
                                                                                    
                                                                                    if ($product->code) {
                                                                                        $measureunit->getByCode($product->measureunit);
                                                                                    } else {
                                                                                        $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The Item does not exist on the platform", 'r');
                                                                                    }
                                                                                    
                                                                                    $grossamount = $grossamount + $ErpAmount;
                                                                                    $itemcount = $itemcount + 1;
                                                                                    
                                                                                    $goods[] = array(
                                                                                        'groupid' => NULL,
                                                                                        'item' => $product->name,
                                                                                        'itemcode' => $product->code,
                                                                                        'qty' => $Qty,
                                                                                        'unitofmeasure' => $product->measureunit,
                                                                                        'unitprice' => $UnitPrice,
                                                                                        'total' => $ErpAmount,
                                                                                        'taxid' => '0',
                                                                                        'taxrate' => '0',
                                                                                        'tax' => '0',
                                                                                        'discounttotal' => '0',
                                                                                        'discounttaxrate' => '0',
                                                                                        'discountpercentage' => '0.00',
                                                                                        'ordernumber' => NULL,
                                                                                        'discountflag' => '2',
                                                                                        'deemedflag' => '2',
                                                                                        'exciseflag' => NULL,
                                                                                        'categoryid' => NULL,
                                                                                        'categoryname' => NULL,
                                                                                        'goodscategoryid' => $product->commoditycategorycode,
                                                                                        'goodscategoryname' => NULL,
                                                                                        'exciserate' => NULL,
                                                                                        'exciserule' => NULL,
                                                                                        'excisetax' => NULL,
                                                                                        'pack' => NULL,
                                                                                        'stick' => NULL,
                                                                                        'exciseunit' => NULL,
                                                                                        'excisecurrency' => NULL,
                                                                                        'exciseratename' => NULL,
                                                                                        'taxdisplaycategory' => NULL,
                                                                                        'taxcategory' => NULL,
                                                                                        'taxcategoryCode' => NULL,
                                                                                        'unitofmeasurename' => $measureunit->name
                                                                                    );
                                                                                }
                                                                            }//foreach($elem->Line as $items){
                                                                        } elseif (sizeof($elem->Line) == 1){
                                                                            $LineId = $elem->Line->Id;
                                                                            $LineNum = $elem->Line->LineNum;
                                                                            $Description = $elem->Line->Description;
                                                                            $ErpAmount = $elem->Line->Amount;
                                                                            $DetailType = $elem->Line->DetailType;
                                                                            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : Line Description: " . $Description, 'r');
                                                                            
                                                                            if (strtoupper($elem->Line->DetailType) == 'ITEMBASEDEXPENSELINEDETAIL') {
                                                                                if(isset($elem->Line->ItemBasedExpenseLineDetail)){
                                                                                    $ItemRef = $elem->Line->ItemBasedExpenseLineDetail->ItemRef;
                                                                                    $UnitPrice = $elem->Line->ItemBasedExpenseLineDetail->UnitPrice;
                                                                                    $Qty = $elem->Line->ItemBasedExpenseLineDetail->Qty;
                                                                                }
                                                                                
                                                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : Unit Price: " . $UnitPrice, 'r');
                                                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : Qty: " . $Qty, 'r');
                                                                                
                                                                                
                                                                                $product->getByErpCode($ItemRef);
                                                                                
                                                                                if ($product->code) {
                                                                                    $measureunit->getByCode($product->measureunit);
                                                                                } else {
                                                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The Item does not exist on the platform", 'r');
                                                                                }
                                                                                
                                                                                $grossamount = $grossamount + $ErpAmount;
                                                                                $itemcount = $itemcount + 1;
                                                                                
                                                                                $goods[] = array(
                                                                                    'groupid' => NULL,
                                                                                    'item' => $product->name,
                                                                                    'itemcode' => $product->code,
                                                                                    'qty' => $Qty,
                                                                                    'unitofmeasure' => $product->measureunit,
                                                                                    'unitprice' => $UnitPrice,
                                                                                    'total' => $ErpAmount,
                                                                                    'taxid' => '0',
                                                                                    'taxrate' => '0',
                                                                                    'tax' => '0',
                                                                                    'discounttotal' => '0',
                                                                                    'discounttaxrate' => '0',
                                                                                    'discountpercentage' => '0.00',
                                                                                    'ordernumber' => NULL,
                                                                                    'discountflag' => '2',
                                                                                    'deemedflag' => '2',
                                                                                    'exciseflag' => NULL,
                                                                                    'categoryid' => NULL,
                                                                                    'categoryname' => NULL,
                                                                                    'goodscategoryid' => $product->commoditycategorycode,
                                                                                    'goodscategoryname' => NULL,
                                                                                    'exciserate' => NULL,
                                                                                    'exciserule' => NULL,
                                                                                    'excisetax' => NULL,
                                                                                    'pack' => NULL,
                                                                                    'stick' => NULL,
                                                                                    'exciseunit' => NULL,
                                                                                    'excisecurrency' => NULL,
                                                                                    'exciseratename' => NULL,
                                                                                    'taxdisplaycategory' => NULL,
                                                                                    'taxcategory' => NULL,
                                                                                    'taxcategoryCode' => NULL,
                                                                                    'unitofmeasurename' => $measureunit->name
                                                                                );
                                                                            }
                                                                        } else {
                                                                            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : There are no line items on the PO.", 'r');
                                                                        }
                                                                    }//if(isset($elem->Line)){
                                                                    
                                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The GOODS count: " . sizeof($goods), 'r');
                                                                    
                                                                    $purchaseorderdetails['operator'] = $this->f3->get('SESSION.username');
                                                                    $purchaseorderdetails['currency'] = $this->util->getcurrency(trim($CurrencyRef));
                                                                    $purchaseorderdetails['SyncToken'] = $SyncToken;
                                                                    $purchaseorderdetails['issueddate'] = $TxnDate;
                                                                    $purchaseorderdetails['issuedtime'] = $TxnDate;
                                                                    $purchaseorderdetails['itemcount'] = $itemcount;
                                                                    
                                                                    $purchaseorderdetails['grossamount'] = $grossamount;
                                                                    
                                                                    $purchaseorderdetails['remarks'] = "The PO DocNumber " . $DocNumber . " and Id " . $POId . " uploaded using the QBO API";
                                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The Sync Token is " . $SyncToken, 'r');
                                                                    
                                                                    if ($POId) {
                                                                        $purchaseorder->getByErpId($POId);
                                                                        
                                                                        if ($purchaseorder->dry()) {
                                                                            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO does not exist", 'r');
                                                                            $po_status = $this->util->createpurchaseorder($purchaseorderdetails, $goods, $this->f3->get('SESSION.id'));
                                                                            
                                                                            if ($po_status) {
                                                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO " . $DocNumber . " was created.", 'r');
                                                                            } else {
                                                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO " . $DocNumber . " was NOT created.", 'r');
                                                                            }
                                                                        } else {
                                                                            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO exists", 'r');
                                                                            $purchaseorderdetails['id'] = $purchaseorder->id;
                                                                            $purchaseorderdetails['gooddetailgroupid'] = $purchaseorder->gooddetailgroupid;
                                                                            $purchaseorderdetails['taxdetailgroupid'] = $purchaseorder->taxdetailgroupid;
                                                                            $purchaseorderdetails['paymentdetailgroupid'] = $purchaseorder->paymentdetailgroupid;
                                                                            
                                                                            if ($purchaseorder->procStatus == '1') {
                                                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO " . $DocNumber . " is already uploaded into EFRIS.", 'r');
                                                                                
                                                                            } else {
                                                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO " . $DocNumber . " is NOT uploaded into EFRIS.", 'r');
                                                                                
                                                                                
                                                                                $po_status = $this->util->updatepurchaseorder($purchaseorderdetails, $goods, $this->f3->get('SESSION.id'));
                                                                                
                                                                                if ($po_status) {
                                                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO " . $DocNumber . " was updated.", 'r');
                                                                                } else {
                                                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO " . $DocNumber . " was NOT updated.", 'r');
                                                                                }
                                                                                
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO has no Id.", 'r');
                                                                    }
                                                                } else {
                                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO is not Closed. The status is: " . $elem->POStatus, 'r');
                                                                }
                                                                
                                                            } catch (Exception $e) {
                                                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : There was an error when processing PO " . $elem->DocNumber . ". The error is " . $e->getMessage(), 'r');
                                                            }
                                                        }//foreach($purchaseorders as $elem)
                                                    } else {
                                                        $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The operation to download ERP purchase orders did not return records.", 'r');
                                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchaseorders by " . $this->f3->get('SESSION.username') . " did not return records.");
                                                        self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " did not return records.";
                                                    }
                                                } else {
                                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The operation to download ERP purchase orders did not return records.", 'r');
                                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchaseorders by " . $this->f3->get('SESSION.username') . " did not return records.");
                                                    self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " did not return records.";
                                                }
                                            }
                                            
                                            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The operation to download ERP purchase orders was successful.", 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchaseorders by " . $this->f3->get('SESSION.username') . " was successful");
                                            self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was successful.";
                                        } else {
                                            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The operation to download ERP purchase orders was not successful. Please connect to ERP first.", 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                            self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                                        }
                                        
                                    } catch (Exception $e) {
                                        $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The operation to download ERP purchase orders was not successful. The error is: " . $e->getMessage(), 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was not successful");
                                        self::$systemalert = "The operation to download ERP purchase orders by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                                    }
                                } else {
                                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : No PO number exists in the database.", 'r');
                                    self::$systemalert = "Sorry, no PO number exists in the database.";
                                }
                            } else {
                                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The integrated ERP is unknown.", 'r');
                                self::$systemalert = "Sorry. The integrated ERP is unknown.";
                            }
                        } else {
                            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : We are unable to indentify the currently integrated ERP.", 'r');
                            self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                        }
                    }
                } else {
                    $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The PO has already been processed.", 'r');
                    self::$systemalert = "Sorry. The PO has already been processed.";
                }
            } else {
                $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : No Id was specified.", 'r');
                self::$systemalert = "Sorry. No PO was specified.";
            }
        } else {
            $this->logger->write("Purchaseorder Controller : fetchErpPurchaseorder() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
}
?>