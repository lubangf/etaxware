<?php
/**
 * @name AdministrationController
 * @desc This file is part of the etaxware system. The is the Administration controller class
 * @date 08-09-2022
 * @file AdministrationController.php
 * @path ./app/controller/AdministrationController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
Class AdministrationController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules
    
	/**
	 *	@name index
	 *  @desc Loads the index page
	 *	@return NULL
	 *	@param NULL
	 **/
    function index($tab = '', $tabpane = '', $alert = ''){
	    $operation = NULL; //tblevents
	    $permission = 'VIEWADMIN'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
        
	    $this->logger->write("Administration Controller : index() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {	        
	        $tcsdetail = new tcsdetails($this->db);
	        $tcsdetails =  $tcsdetail->displayByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
	        $this->f3->set('tcsdetails', $tcsdetails);
	        
	        $companydetail = new organisations($this->db);
	        $companydetails =  $companydetail->displayByID($this->appsettings['SELLER_RECORD_ID']);
	        $this->f3->set('companydetails', $companydetails);
	        
	        $devicedetail = new devices($this->db);
	        $devicedetails =  $devicedetail->displayByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
	        $this->f3->set('devicedetails', $devicedetails);
	        
	        $product = new products($this->db);
	        $products = $product->all();
	        $productlist = array();
	        $exclusionlist = array();
	        
	        $productoverridelist = new productoverridelist($this->db);
	        $productoverridelists = $productoverridelist->all();
	        
	        foreach ($products as $p) {
	            if (!empty($productoverridelists)) {
	                foreach ($productoverridelists as $pd) {
	                    if ($p['code'] == $pd['code']) {
	                        $l = '<option value="' . $p['code'] . '" selected>' . $p['name'] . '</option>';
	                        break;
	                    } else {
	                        $l = '<option value="' . $p['code'] . '">' . $p['name'] . '</option>';
	                    }
	                }
	            } else {
	                $l = '<option value="' . $p['code'] . '">' . $p['name'] . '</option>';
	            }
	            
	            $productlist[] = $l;
	        }
	        
	        $this->f3->set('polsts', $productlist);
	        
	        $enforcetaxexclusionlist = new enforcetaxexclusionlist($this->db);
	        $enforcetaxexclusionlists = $enforcetaxexclusionlist->all();
	        
	        foreach ($products as $p) {
	            if (!empty($enforcetaxexclusionlists)) {
	                foreach ($enforcetaxexclusionlists as $pd) {
	                    if ($p['code'] == $pd['code']) {
	                        $l = '<option value="' . $p['code'] . '" selected>' . $p['name'] . '</option>';
	                        break;
	                    } else {
	                        $l = '<option value="' . $p['code'] . '">' . $p['name'] . '</option>';
	                    }
	                }
	            } else {
	                $l = '<option value="' . $p['code'] . '">' . $p['name'] . '</option>';
	            }
	            
	            $exclusionlist[] = $l;
	        }
	        
	        $this->f3->set('pelsts', $exclusionlist);
	        
	        
	        //$this->f3->set('currenttab', $tab);//set the USER tab as ACTIVE
	        //$this->f3->set('currenttabpane', $pane);
	        
	        $this->f3->set('currenttab', 'tab_users');//set the USER tab as ACTIVE
	        $this->f3->set('currenttabpane', 'tab_1');
	        
	        if (is_string($tab) && is_string($tabpane)){
	            $this->f3->set('currenttab', $tab);
	            $this->f3->set('currenttabpane', $tabpane);
	        } else {
	            $this->f3->set('currenttab', 'tab_users');
	            $this->f3->set('currenttabpane', 'tab_1');
	            //$this->f3->set('path', '../' . $this->path);
	        }
	        
	        if (is_string($alert) && $alert !== 'AdministrationController->index'){
	            $this->f3->set('systemalert', $alert);
	        } 
	        	        
	        $this->f3->set('pagetitle','Administration');
	        $this->f3->set('pagecontent','Administration.htm');
	        $this->f3->set('pagescripts','AdministrationFooter.htm');
	        echo \Template::instance()->render('Layout.htm');
	    } else {
	        $this->logger->write("Administration Controller : index() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }	    
	}
	
	/**
	 *	@name edittcsdetails
	 *  @desc Edit TCS details
	 *	@return NULL
	 *	@param NULL
	 **/
	function edittcsdetails(){
	    $operation = NULL; //tblevents
	    $permission = 'EDITTCSDETAILS'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    $this->logger->write("Administration Controller : edittcsdetails() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        $tcsdetails = new tcsdetails($this->db);
	        $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
	        
	        //$this->logger->write("Administration Controller : edittcsdetails() : The set user name is " . $this->f3->get('POST.tcsdetailsusername'), 'r');
	        
	        if (trim($this->f3->get('POST.tcsdetailsusername')) !== '' || !empty(trim($this->f3->get('POST.tcsdetailsusername')))) {
	            $this->f3->set('POST.username', $this->f3->get('POST.tcsdetailsusername'));
	        } else {
	            $this->f3->set('POST.username', $tcsdetails->username);
	        }
	        
	        $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
	        $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
	        
	        try {
	            $tcsdetails->edit($this->appsettings['EFRIS_TCS_RECORD_ID']);

	            $this->logger->write("Administration Controller : edittcsdetails() : The TCS details have been edited", 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The TCS details have been edited by " . $this->f3->get('SESSION.username'));
	            self::$systemalert = "The TCS details have been edited";
	        } catch (Exception $e) {
	            $this->logger->write("Administration Controller : edittcsdetails() : The operation to edit the TCS details was not successful. The error message is " . $e->getMessage(), 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit the TCS details was not successful");
	            self::$systemalert = "The operation to edit the TCS details was not successful";
	        }
	       
	        
	        self::index('tab_tcsdetails', 'tab_5', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : edittcsdetails() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }
	}
	
	/**
	 *	@name editcompanydetails
	 *  @desc Edit company details
	 *	@return NULL
	 *	@param NULL
	 **/
	function editcompanydetails(){
	    $operation = NULL; //tblevents
	    $permission = 'EDITCOMPANYDETAILS'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    $this->logger->write("Administration Controller : editcompanydetails() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        $companydetails = new organisations($this->db);
	        $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);
	        
	        
	        if (trim($this->f3->get('POST.companydetailstin')) !== '' || !empty(trim($this->f3->get('POST.companydetailstin')))) {
	            $this->f3->set('POST.tin', $this->f3->get('POST.companydetailstin'));
	        } else {
	            $this->f3->set('POST.tin', $companydetails->tin);
	        }
	        
	        if (trim($this->f3->get('POST.companydetailstaxpayerid')) !== '' || !empty(trim($this->f3->get('POST.companydetailstaxpayerid')))) {
	            $this->f3->set('POST.taxpayerid', $this->f3->get('POST.companydetailstaxpayerid'));
	        } else {
	            $this->f3->set('POST.taxpayerid', $companydetails->taxpayerid);
	        }
	        
	        if (trim($this->f3->get('POST.companydetailslatitude')) !== '' || !empty(trim($this->f3->get('POST.companydetailslatitude')))) {
	            $this->f3->set('POST.latitude', $this->f3->get('POST.companydetailslatitude'));
	        } else {
	            $this->f3->set('POST.latitude', $companydetails->latitude);
	        }
	        
	        if (trim($this->f3->get('POST.companydetailslongitude')) !== '' || !empty(trim($this->f3->get('POST.companydetailslongitude')))) {
	            $this->f3->set('POST.longitude', $this->f3->get('POST.companydetailslongitude'));
	        } else {
	            $this->f3->set('POST.longitude', $companydetails->longitude);
	        }
	        
	        $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
	        $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
	        
	        try {
	            $companydetails->edit($this->appsettings['SELLER_RECORD_ID']);
	            
	            $this->logger->write("Administration Controller : editcompanydetails() : The company details have been edited", 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The company details have been edited by " . $this->f3->get('SESSION.username'));
	            self::$systemalert = "The company details have been edited";
	        } catch (Exception $e) {
	            $this->logger->write("Administration Controller : editcompanydetails() : The operation to edit the company details was not successful. The error message is " . $e->getMessage(), 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit the company details was not successful");
	            self::$systemalert = "The operation to edit the company details was not successful";
	        }
	        
	        
	        
	        self::index('tab_companydetails', 'tab_6', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : editcompanydetails() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }
	}
	
	
	/**
	 *	@name editproductoverridelist
	 *  @desc Edit the product override list
	 *	@return NULL
	 *	@param NULL
	 **/
	function editproductoverridelist(){
	    $operation = NULL; //tblevents
	    $permission = 'EDITPRODUCTOVERRIDELIST'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    $this->logger->write("Administration Controller : editproductoverridelist() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        
	        if (!empty($this->f3->get('POST.editoverridelists'))) {
	            //Clear out the tables tblproductoverridelist
	            try {
	                $this->db->exec(array('TRUNCATE TABLE tblproductoverridelist'));
	            } catch (Exception $e) {
	                $this->logger->write("Administration Controller : editproductoverridelist() : Failed to truncate table tblproductoverridelist. The error message is " . $e->getMessage(), 'r');
	            }
	            
	            
	            
	            foreach ($this->f3->get('POST.editoverridelists') as $code) {
	                try {
	                    $this->db->exec(array('INSERT INTO tblproductoverridelist (code, name, inserteddt, insertedby, modifieddt, modifiedby)
                                                    VALUES("' . $code . '", NULL, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
	                    
	                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The product override list has been edited by " . $this->f3->get('SESSION.username'));
	                    self::$systemalert = "The product override list has been edited";
	                } catch (Exception $e) {
	                    $this->logger->write("Administration Controller : editproductoverridelist() : Failed to insert into tblproductoverridelist. The error message is " . $e->getMessage(), 'r');
	                    
	                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit the product override list by " . $this->f3->get('SESSION.username') . " was not successful");
	                    self::$systemalert = "The operation to edit the product override list was not successful";
	                }
	            }
	        } else {
	            $this->logger->write("Administration Controller : editproductoverridelist() : No products were selected. We assume the list has been cleared", 'r');
	            
	            try {
	                $this->db->exec(array('TRUNCATE TABLE tblproductoverridelist'));
	                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The product override list has been edited by " . $this->f3->get('SESSION.username'));
	                self::$systemalert = "The product override list has been edited";
	            } catch (Exception $e) {
	                $this->logger->write("Administration Controller : editproductoverridelist() : Failed to truncate table tblproductoverridelist. The error message is " . $e->getMessage(), 'r');
	                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit the product override list by " . $this->f3->get('SESSION.username') . " was not successful");
	                self::$systemalert = "The operation to edit the product override list was not successful";
	            }
	        }
	        
	        self::index('tab_overridelist', 'tab_9', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : editproductoverridelist() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }
	}
	
	/**
	 *	@name editenforcetaxexclusionlist
	 *  @desc Edit the enforce tax exclusion list
	 *	@return NULL
	 *	@param NULL
	 **/
	function editenforcetaxexclusionlist(){
	    $operation = NULL; //tblevents
	    $permission = 'EDITPRODUCTOVERRIDELIST'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    $this->logger->write("Administration Controller : editenforcetaxexclusionlist() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        
	        if (!empty($this->f3->get('POST.editenforcetaxexclusionlists'))) {
	            //Clear out the tables tblenforcetaxexclusionlist
	            try {
	                $this->db->exec(array('TRUNCATE TABLE tblenforcetaxexclusionlist'));
	            } catch (Exception $e) {
	                $this->logger->write("Administration Controller : editenforcetaxexclusionlist() : Failed to truncate table tblenforcetaxexclusionlist. The error message is " . $e->getMessage(), 'r');
	            }
	            
	            
	            
	            foreach ($this->f3->get('POST.editoverridelists') as $code) {
	                try {
	                    $this->db->exec(array('INSERT INTO tblenforcetaxexclusionlist (code, name, inserteddt, insertedby, modifieddt, modifiedby)
                                                    VALUES("' . $code . '", NULL, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
	                    
	                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The enforce tax exclusion list has been edited by " . $this->f3->get('SESSION.username'));
	                    self::$systemalert = "The enforce tax exclusion list has been edited";
	                } catch (Exception $e) {
	                    $this->logger->write("Administration Controller : editenforcetaxexclusionlist() : Failed to insert into tblenforcetaxexclusionlist. The error message is " . $e->getMessage(), 'r');
	                    
	                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit the enforce tax exclusion list by " . $this->f3->get('SESSION.username') . " was not successful");
	                    self::$systemalert = "The operation to edit the enforce tax exclusion list was not successful";
	                }
	            }
	        } else {
	            $this->logger->write("Administration Controller : editenforcetaxexclusionlist() : No products were selected. We assume the list has been cleared", 'r');
	            
	            try {
	                $this->db->exec(array('TRUNCATE TABLE tblenforcetaxexclusionlist'));
	                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The product override list has been edited by " . $this->f3->get('SESSION.username'));
	                self::$systemalert = "The enforce tax exclusion list has been edited";
	            } catch (Exception $e) {
	                $this->logger->write("Administration Controller : editenforcetaxexclusionlist() : Failed to truncate table tblenforcetaxexclusionlist. The error message is " . $e->getMessage(), 'r');
	                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit the enforce tax exclusion list by " . $this->f3->get('SESSION.username') . " was not successful");
	                self::$systemalert = "The operation to edit the enforce tax exclusion list was not successful";
	            }
	        }
	        
	        self::index('tab_enforcetaxexclusionlist', 'tab_10', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : editenforcetaxexclusionlist() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }
	}
	
	/**
	 *	@name editdevicedetails
	 *  @desc Edit device details
	 *	@return NULL
	 *	@param NULL
	 **/
	function editdevicedetails(){
	    $operation = NULL; //tblevents
	    $permission = 'EDITDEVICEDETAILS'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    $this->logger->write("Administration Controller : editdevicedetails() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        $devicedetails = new devices($this->db);
	        $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
	        
	        
	        if (trim($this->f3->get('POST.devicedetailsdeviceno')) !== '' || !empty(trim($this->f3->get('POST.devicedetailsdeviceno')))) {
	            $this->f3->set('POST.deviceno', $this->f3->get('POST.devicedetailsdeviceno'));
	        } else {
	            $this->f3->set('POST.deviceno', $devicedetails->deviceno);
	        }
	        
	        if (trim($this->f3->get('POST.devicedetailsdevicemac')) !== '' || !empty(trim($this->f3->get('POST.devicedetailsdevicemac')))) {
	            $this->f3->set('POST.devicemac', $this->f3->get('POST.devicedetailsdevicemac'));
	        } else {
	            $this->f3->set('POST.devicemac', $devicedetails->devicemac);
	        }
	        
	        $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
	        $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
	        
	        try {
	            $devicedetails->edit($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
	            
	            $this->logger->write("Administration Controller : editdevicedetails() : The device details have been edited", 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The device details have been edited by " . $this->f3->get('SESSION.username'));
	            self::$systemalert = "The device details have been edited";
	        } catch (Exception $e) {
	            $this->logger->write("Administration Controller : editdevicedetails() : The operation to edit the device details was not successful. The error message is " . $e->getMessage(), 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit the device details was not successful");
	            self::$systemalert = "The operation to edit the device details was not successful";
	        }
	        
	        
	        
	        self::index('tab_devicedetails', 'tab_7', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : editdevicedetails() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }
	}
	
	/**
	 *	@name efrislogin
	 *  @desc initialize eTW by logging into EFRIS
	 *	@return
	 *	@param
	 **/
	function efrislogin(){
	    $operation = NULL; //tblevents
	    $permission = 'EFRISLOGIN'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    
	    $this->logger->write("Administration Controller : efrislogin() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        //$data = json_encode(new stdClass);
	        
	        $data = $this->util->efrislogin($this->f3->get('SESSION.id'));//will return JSON.
	        //var_dump($data);
	        $data = json_decode($data, true);
	        
	        
	        if (isset($data['taxpayer'])){
	            /*TAX PAYER*/
	            $taxpayerid = !isset($data['taxpayer']['id'])? 'NULL' : '"' . addslashes($data['taxpayer']['id']) . '"';
	            $ninbrn = !isset($data['taxpayer']['ninBrn'])? 'NULL' : '"' . addslashes($data['taxpayer']['ninBrn']) . '"';
	            $legalname = !isset($data['taxpayer']['legalName'])? 'NULL' : '"' . addslashes($data['taxpayer']['legalName']) . '"';
	            $businessname = !isset($data['taxpayer']['businessName'])? 'NULL' : '"' . addslashes($data['taxpayer']['businessName']) . '"';
	            $address = !isset($data['taxpayer']['placeOfBusiness'])? 'NULL' : '"' . addslashes($data['taxpayer']['placeOfBusiness']) . '"';
	            $mobilephone = !isset($data['taxpayer']['contactMobile'])? 'NULL' : '"' . addslashes($data['taxpayer']['contactMobile']) . '"';
	            $linephone = !isset($data['taxpayer']['contactNumber'])? 'NULL' : '"' . addslashes($data['taxpayer']['contactNumber']) . '"';
	            $emailaddress = !isset($data['taxpayer']['contactEmail'])? 'NULL' : '"' . addslashes($data['taxpayer']['contactEmail']) . '"';
	            $placeofbusiness = !isset($data['taxpayer']['placeOfBusiness'])? 'NULL' : '"' . addslashes($data['taxpayer']['placeOfBusiness']) . '"';
	            $taxpayerRegistrationStatusId = !isset($data['taxpayer']['taxpayerRegistrationStatusId'])? 'NULL' : $data['taxpayer']['taxpayerRegistrationStatusId'];
	            $taxpayerStatusId = !isset($data['taxpayer']['taxpayerStatusId'])? 'NULL' : $data['taxpayer']['taxpayerStatusId'];
	            $taxpayerType = !isset($data['taxpayer']['taxpayerType'])? 'NULL' : $data['taxpayer']['taxpayerType'];
	            $businessType = !isset($data['taxpayer']['businessType'])? 'NULL' : $data['taxpayer']['businessType'];	            
	            
	            /*OTHER DETAILS; related to TAX PAYER*/
	            $isAllowIssueCreditWithoutFDN = !isset($data['isAllowIssueCreditWithoutFDN'])? 'NULL' : $data['isAllowIssueCreditWithoutFDN'];
	            $isDutyFreeTaxpayer = !isset($data['isDutyFreeTaxpayer'])? 'NULL' : $data['isDutyFreeTaxpayer'];
	            $isAllowIssueRebate = !isset($data['isAllowIssueRebate'])? 'NULL' : $data['isAllowIssueRebate'];
	            $isReferenceNumberMandatory = !isset($data['isReferenceNumberMandatory'])? 'NULL' : $data['isReferenceNumberMandatory'];
	            $isAllowBackDate = !isset($data['isAllowBackDate'])? 'NULL' : $data['isAllowBackDate'];
	            $issueTaxTypeRestrictions = !isset($data['issueTaxTypeRestrictions'])? 'NULL' : $data['issueTaxTypeRestrictions'];
	            $goodsStockLimit = !isset($data['goodsStockLimit'])? 'NULL' : $data['goodsStockLimit'];
	            $maxGrossAmount = !isset($data['maxGrossAmount'])? 'NULL' : $data['maxGrossAmount'];
	            $exportInvoiceExciseDuty = !isset($data['exportInvoiceExciseDuty'])? 'NULL' : $data['exportInvoiceExciseDuty'];
	            $exportCommodityTaxRate = !isset($data['exportCommodityTaxRate'])? 'NULL' : $data['exportCommodityTaxRate'];
	            $isTaxCategoryCodeMandatory = !isset($data['isTaxCategoryCodeMandatory'])? 'NULL' : $data['isTaxCategoryCodeMandatory'];
	            
	            /**
	             * Author: Francis Lubanga<frncslubanga@gmail.com>
	             * Date: 2025-06-30
	             * Description: Additional fields, as per interface specification v23.4
	             */
	            $hsCodeVersion = !isset($data['hsCodeVersion'])? 'NULL' : $data['hsCodeVersion'];
	            $issueDebitNote = !isset($data['issueDebitNote'])? 'NULL' : $data['issueDebitNote'];
	            $qrCodeURL = !isset($data['qrCodeURL'])? 'NULL' : '"' . addslashes($data['qrCodeURL']) . '"';
	            
	            /*DEVICE*/
	            $devicemodel = !isset($data['device']['deviceModel'])? 'NULL' : '"' . addslashes($data['device']['deviceModel']) . '"';
	            $devicestatus = !isset($data['device']['deviceStatus'])? 'NULL' : $data['device']['deviceStatus'];/*INT*/
	            $offlineamount = !isset($data['device']['offlineAmount'])? 'NULL' : '"' . addslashes($data['device']['offlineAmount']) . '"';
	            $offlinedays = !isset($data['device']['offlineDays'])? 'NULL' : '"' . addslashes($data['device']['offlineDays']) . '"';
	            $offlinevalue = !isset($data['device']['offlineValue'])? 'NULL' : '"' . addslashes($data['device']['offlineValue']) . '"';
	            $branchCode = !isset($data['device']['branchCode'])? 'NULL' : '"' . addslashes($data['device']['branchCode']) . '"';
	            $branchId = !isset($data['device']['branchId'])? 'NULL' : '"' . addslashes($data['device']['branchId']) . '"';
	            $deviceType = !isset($data['device']['deviceType'])? 'NULL' : $data['device']['deviceType'];;/*INT*/
	            
	            if (isset($data['device']['validPeriod'])) {	                
	                $validPeriod = $data['device']['validPeriod']; //13/06/2019
	                $validPeriod = str_replace('/', '-', $validPeriod);//Replace / with -
	                $validPeriod = date("Y-m-d", strtotime($validPeriod));
	                $validPeriod = '"' . $validPeriod . '"';
	            } else {
	                $validPeriod = 'NULL';
	            }
	            
	            
                /*TCS*/
	            $commodityCategoryVersion = !isset($data['commodityCategoryVersion'])? 'NULL' : '"' . addslashes($data['commodityCategoryVersion']) . '"';
	            $dictionaryVersion = !isset($data['dictionaryVersion'])? 'NULL' : '"' . addslashes($data['dictionaryVersion']) . '"';
	            $exciseDutyVersion = !isset($data['exciseDutyVersion'])? 'NULL' : '"' . addslashes($data['exciseDutyVersion']) . '"';
	            $taxpayerBranchVersion = !isset($data['taxpayerBranchVersion'])? 'NULL' : '"' . addslashes($data['taxpayerBranchVersion']) . '"';
	            
	            try{
	                $this->db->exec(array('UPDATE tblorganisations SET taxpayerid = ' . $taxpayerid . 
                                            	                    ', ninbrn = ' . $ninbrn . 
                                            	                    ', legalname = ' . $legalname .  
                                            	                    ', businessname = ' . $businessname . 
                                            	                    ', address = ' . $address . 
                                            	                    ', mobilephone = ' . $mobilephone . 
                                            	                    ', linephone = ' . $linephone . 
                                            	                    ', emailaddress = ' . $emailaddress . 
                                            	                    ', placeofbusiness = ' . $placeofbusiness . 
                                            	                    ', taxpayerRegistrationStatusId = ' . $taxpayerRegistrationStatusId . 
                                            	                    ', taxpayerStatusId = ' . $taxpayerStatusId . 
                                            	                    ', taxpayerType = ' . $taxpayerType . 
                                            	                    ', businessType = ' . $businessType . 
                                            	                    ', isAllowIssueCreditWithoutFDN = ' . $isAllowIssueCreditWithoutFDN . 
                                            	                    ', isDutyFreeTaxpayer = ' . $isDutyFreeTaxpayer . 
                                            	                    ', isAllowIssueRebate = ' . $isAllowIssueRebate . 
                                            	                    ', isReferenceNumberMandatory = ' . $isReferenceNumberMandatory . 
                                            	                    ', isAllowBackDate = ' . $isAllowBackDate . 
                                            	                    ', issueTaxTypeRestrictions = ' . $issueTaxTypeRestrictions . 
                                            	                    ', goodsStockLimit = ' . $goodsStockLimit . 
                                            	                    ', maxGrossAmount = ' . $maxGrossAmount . 
                                            	                    ', exportInvoiceExciseDuty = ' . $exportInvoiceExciseDuty . 
                                            	                    ', exportCommodityTaxRate = ' . $exportCommodityTaxRate . 
                                            	                    ', isTaxCategoryCodeMandatory = ' . $isTaxCategoryCodeMandatory .
                                            	                    ', hsCodeVersion = ' . $hsCodeVersion .
                                            	                    ', issueDebitNote = ' . $issueDebitNote .
                                            	                    ', qrCodeURL = ' . $qrCodeURL .
                                            	                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . 
                                            	                    ' WHERE id = ' . $this->appsettings['SELLER_RECORD_ID']));
	                //$this->logger->write($this->db->log(TRUE), 'r');
	            } catch (Exception $e) {
	                $this->logger->write("Administration Controller : efrislogin() : Failed to update the table tblorganisations. The error message is " . $e->getMessage(), 'r');
	            }
	            
	            try{
	                $this->db->exec(array('UPDATE tbldevices SET devicemodel = ' . $devicemodel .
                                        	                    ', devicestatus = ' . $devicestatus .
                                        	                    ', offlineamount = ' . $offlineamount .
                                        	                    ', offlinedays = ' . $offlinedays .
                                        	                    ', offlinevalue = ' . $offlinevalue .
                                        	                    ', branchCode = ' . $branchCode .
                                        	                    ', branchId = ' . $branchId .
	                                                            ', deviceType = ' . $deviceType .
                                        	                    ', validPeriod = ' . $validPeriod .
                                        	                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                        	                    ' WHERE id = ' . $this->appsettings['SELLER_RECORD_ID']));
	                //$this->logger->write($this->db->log(TRUE), 'r');
	            } catch (Exception $e) {
	                $this->logger->write("Administration Controller : efrislogin() : Failed to update the table tbldevices. The error message is " . $e->getMessage(), 'r');
	            }
	            
	            try{
	                $this->db->exec(array('UPDATE tbltcsdetails SET commodityCategoryVersion = ' . $commodityCategoryVersion .
                                            	                    ', dictionaryVersion = ' . $dictionaryVersion .
                                            	                    ', exciseDutyVersion = ' . $exciseDutyVersion .
                                            	                    ', taxpayerBranchVersion = ' . $taxpayerBranchVersion .
                                            	                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                            	                    ' WHERE id = ' . $this->appsettings['SELLER_RECORD_ID']));
	                //$this->logger->write($this->db->log(TRUE), 'r');
	            } catch (Exception $e) {
	                $this->logger->write("Administration Controller : efrislogin() : Failed to update the table tbltcsdetails. The error message is " . $e->getMessage(), 'r');
	            }
	            
	            try{
	                $this->db->exec(array('UPDATE tblsettings SET value = ' . $legalname .
                        	                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                        	                    ' WHERE code = "APPDOMAIN"'));
	                //$this->logger->write($this->db->log(TRUE), 'r');
	            } catch (Exception $e) {
	                $this->logger->write("Administration Controller : efrislogin() : Failed to update the table tblsettings. The error message is " . $e->getMessage(), 'r');
	            }
	            
	            
	            /*TAX TYPES*/
	            if (isset($data['taxType'])){
	                
	                try{
	                    $this->db->exec(array('TRUNCATE TABLE tbltaxtypes'));
	                } catch (Exception $e) {
	                    $this->logger->write("Administration Controller : efrislogin() : The operation to truncate table tbltaxtypes was not successful. The error message is " . $e->getMessage(), 'r');
	                }
	                
	                foreach($data['taxType'] as $elem){
	                    
	                    try{
	                        $code = !isset($elem['taxTypeCode'])? 'NULL' : '"' . addslashes($elem['taxTypeCode']) . '"';
	                        $name = !isset($elem['taxTypeName'])? 'NULL' : '"' . addslashes($elem['taxTypeName']) . '"';
	                        
	                        if (isset($elem['registrationDate'])) {
	                            $registrationdate = $elem['registrationDate']; //13/06/2019
	                            $registrationdate = str_replace('/', '-', $registrationdate);//Replace / with -
	                            $registrationdate = date("Y-m-d", strtotime($registrationdate));
	                            $registrationdate = '"' . $registrationdate . '"';
	                        } else {
	                            $registrationdate = 'NULL';
	                        }
	                        
	                        if (isset($elem['cancellationDate'])) {
	                            $cancellationdate = $elem['cancellationDate']; //13/06/2019
	                            $cancellationdate = str_replace('/', '-', $cancellationdate);//Replace / with -
	                            $cancellationdate = date("Y-m-d", strtotime($cancellationdate));
	                            $cancellationdate = '"' . $cancellationdate . '"';
	                        } else {
	                            $cancellationdate = 'NULL';
	                        }
	                         
	                        $this->db->exec(array('INSERT INTO tbltaxtypes
                                                                (code,
                                                                name,
                                                                registrationdate,
                                                                cancellationdate,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $code . ',
                                                                ' . $name . ',
                                                                ' . $registrationdate . ',
                                                                ' . $cancellationdate . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                    } catch (Exception $e) {
	                        $this->logger->write("Product Controller : efrislogin() : The operation to insert into table tbltaxtypes was not successful. The error message is " . $e->getMessage(), 'r');
	                    }
	                }
	            }
	            
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to log into EFRIS by " . $this->f3->get('SESSION.username') . " was successful");
	            self::$systemalert = "The operation to log into EFRIS by " . $this->f3->get('SESSION.username') . " was successful, and all relevant details have been updated.";
	        } elseif (isset($data['returnCode'])){
	            $this->logger->write("Administration Controller : efrislogin() : The operation to log into EFRIS was not successful. The error message is " . $data['returnMessage'], 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to log into EFRIS by " . $this->f3->get('SESSION.username') . " was not successful");
	            self::$systemalert = "The operation to log into EFRIS by " . $this->f3->get('SESSION.username') . " was not successful. The error message is " . $data['returnMessage'];
	        } else {
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to log into EFRIS by " . $this->f3->get('SESSION.username') . " was not successful");
	            self::$systemalert = "The operation to log into EFRIS by " . $this->f3->get('SESSION.username') . " was not successful";
	        }
	        
	        self::index('tab_users', 'tab_1', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : efrislogin() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }	    
	}
	
	/**
	 *	@name syncefrisbranches
	 *  @desc sync efrisbranches from EFRIS
	 *	@return
	 *	@param
	 **/
	function syncefrisbranches(){
	    $operation = NULL; //tblevents
	    $permission = 'SYNCEFRISBRANCHES'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
	        date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
	    }
	    
	    $this->logger->write("Administration Controller : syncefrisbranches() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        //$data = json_encode(new stdClass);
	        
	        $data = $this->util->syncbranches($this->f3->get('SESSION.id'));//will return JSON.
	        //var_dump($data);
	        $data = json_decode($data, true);
	        
	        if (isset($data['returnCode'])){
	            $this->logger->write("Administration Controller : syncefrisbranches() : The operation to sync EFRIS branches was not successful. The error message is " . $data['returnMessage'], 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync EFRIS branches by " . $this->f3->get('SESSION.username') . " was not successful");
	            self::$systemalert = "The operation to sync EFRIS branches by " . $this->f3->get('SESSION.username') . " was not successful. The error message is " . $data['returnMessage'];
	        } else {
	            
	            if ($data) {
	                try{
	                    $this->db->exec(array('TRUNCATE TABLE tblurabranches'));
	                } catch (Exception $e) {
	                    $this->logger->write("Administration Controller : syncefrisbranches() : The operation to truncate table tblurabranches was not successful. The error message is " . $e->getMessage(), 'r');
	                }
	                
	                foreach($data as $elem){
	                    
	                    try{
	                        $branchId = !isset($elem['branchId'])? 'NULL' : '"' . addslashes($elem['branchId']) . '"';
	                        $branchName = !isset($elem['branchName'])? 'NULL' : '"' . addslashes($elem['branchName']) . '"';	                        
	                        
	                        $this->db->exec(array('INSERT INTO tblurabranches
                                                                (branchid,
                                                                name,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $branchId . ',
                                                                ' . $branchName . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                    } catch (Exception $e) {
	                        $this->logger->write("Product Controller : syncefrisbranches() : The operation to insert into table tblurabranches was not successful. The error message is " . $e->getMessage(), 'r');
	                    }
	                }
	                
	                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync EFRIS branches by " . $this->f3->get('SESSION.username') . " was successful");
	                self::$systemalert = "The operation to sync EFRIS branches by " . $this->f3->get('SESSION.username') . " was successful, and all relevant details have been updated.";
	            } else {//NOTHING RETURNED BY API
	                $this->logger->write("Administration Controller : syncefrisbranches() : The API did not return anything", 'r');
	            } 
	        }

	        self::index('tab_users', 'tab_1', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : syncefrisbranches() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }
	}
	
	/**
	 *	@name syncefrisdics
	 *  @desc sync EFRIS dictionaries from EFRIS
	 *	@return
	 *	@param
	 **/
	function syncefrisdics(){
	    $operation = NULL; //tblevents
	    $permission = 'SYNCEFRISDICTIONARIES'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
	        date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
	    }
	    
	    $this->logger->write("Administration Controller : syncefrisdics() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        //$data = json_encode(new stdClass);
	        
	        $data = $this->util->syncdictionaries($this->f3->get('SESSION.id'));//will return JSON.
	        //var_dump($data);
	        $data = json_decode($data, true);
	        
	        if (isset($data['returnCode'])){
	            $this->logger->write("Administration Controller : syncefrisdics() : The operation to sync EFRIS dictionaries was not successful. The error message is " . $data['returnMessage'], 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync EFRIS dictionaries by " . $this->f3->get('SESSION.username') . " was not successful");
	            self::$systemalert = "The operation to sync EFRIS dictionaries by " . $this->f3->get('SESSION.username') . " was not successful. The error message is " . $data['returnMessage'];
	        } else {
	            if ($data) {
	                
	                if (isset($data['countryCode'])){
	                    $country = new countries($this->db);
	                    
	                    foreach($data['countryCode'] as $elem){
	                        
	                        $country->getByCode(trim($elem['value']));
	                        
	                        $code = !isset($elem['value'])? 'NULL' : '"' . addslashes($elem['value']) . '"';
	                        $name = !isset($elem['name'])? 'NULL' : '"' . addslashes($elem['name']) . '"';
	                        
	                        if ($country->dry()) {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The country does not exist", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('INSERT INTO tblcountries
                                                                (code,
                                                                name,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $code . ',
                                                                ' . $name . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to insert into table tblcountries was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        } else {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The country exists", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('UPDATE tblcountries SET code = ' . $code .
                    	                                                        ', name = ' . $name .
                                        	                                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                        	                                    ' WHERE code = ' . $code));
	                                
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to update table tblcountries was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        }
	                        	                        
	                    }
	                }
	                
	                
	                if (isset($data['payWay'])){
	                    $paymentmode = new paymentmodes($this->db);
	                    
	                    foreach($data['payWay'] as $elem){
	                        
	                        $paymentmode->getByCode(trim($elem['value']));
	                        
	                        $code = !isset($elem['value'])? 'NULL' : '"' . addslashes($elem['value']) . '"';
	                        $name = !isset($elem['name'])? 'NULL' : '"' . addslashes($elem['name']) . '"';
	                        
	                        if ($paymentmode->dry()) {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The payway does not exist", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('INSERT INTO tblpaymentmodes
                                                                (code,
                                                                name,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $code . ',
                                                                ' . $name . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to insert into table tblpaymentmodes was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        } else {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The payway exists", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('UPDATE tblpaymentmodes SET code = ' . $code .
                                            	                                    ', name = ' . $name .
                                            	                                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                            	                                    ' WHERE code = ' . $code));
	                                
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to update table tblpaymentmodes was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        }
	                        
	                    }
	                }
	                
	                if (isset($data['sector'])){
	                    $sector = new sectors($this->db);
	                    
	                    foreach($data['sector'] as $elem){
	                        
	                        $sector->getByCode(trim($elem['code']));
	                        
	                        $code = !isset($elem['code'])? 'NULL' : '"' . addslashes($elem['code']) . '"';
	                        $name = !isset($elem['name'])? 'NULL' : '"' . addslashes($elem['name']) . '"';
	                        
	                        if ($sector->dry()) {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The sector does not exist", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('INSERT INTO tblsectors
                                                                (code,
                                                                name,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $code . ',
                                                                ' . $name . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to insert into table tblsectors was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        } else {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The sector exists", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('UPDATE tblsectors SET code = ' . $code .
                                    	                                    ', name = ' . $name .
                                    	                                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                    	                                    ' WHERE code = ' . $code));
	                                
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to update table tblsectors was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        }
	                        
	                    }
	                }
	                
	                
	                if (isset($data['rateUnit'])){
	                    $measureunit = new measureunits($this->db);
	                    
	                    foreach($data['rateUnit'] as $elem){
	                        
	                        $measureunit->getByCode(trim($elem['value']));
	                        
	                        $code = !isset($elem['value'])? 'NULL' : '"' . addslashes($elem['value']) . '"';
	                        $name = !isset($elem['name'])? 'NULL' : '"' . addslashes($elem['name']) . '"';
	                        $desc = !isset($elem['description'])? 'NULL' : '"' . addslashes($elem['description']) . '"';
	                        
	                        if ($measureunit->dry()) {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The rateUnit does not exist", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('INSERT INTO tblrateunits
                                                                (code,
                                                                name,
                                                                description,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $code . ',
                                                                ' . $name . ',
                                                                ' . $desc . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to insert into table tblrateunits was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        } else {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The rateUnit exists", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('UPDATE tblrateunits SET code = ' . $code .
                                        	                                    ', name = ' . $name .
                                        	                                    ', description = ' . $desc .
                                        	                                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                        	                                    ' WHERE code = ' . $code));
	                                
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to update table tblrateunits was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        }
	                        
	                    }
	                }
	                
	                /**
	                 * Author: Francis Lubanga <frncslubanga@gmail.com>
	                 * Date: 2025-08-03
	                 * Description: sync export rate units
	                 */
	                if (isset($data['exportRateUnit'])){
	                    $exportmeasureunits = new exportmeasureunits($this->db);
	                    
	                    foreach($data['exportRateUnit'] as $elem){
	                        
	                        $exportmeasureunits->getByCode(trim($elem['value']));
	                        
	                        $code = !isset($elem['value'])? 'NULL' : '"' . addslashes($elem['value']) . '"';
	                        $name = !isset($elem['name'])? 'NULL' : '"' . addslashes($elem['name']) . '"';
	                        $periodTo = !isset($elem['periodTo'])? 'NULL' : '"' . addslashes($elem['periodTo']) . '"';
	                        $status = !isset($elem['status'])? 'NULL' : '"' . addslashes($elem['status']) . '"';
	                        $validPeriodFrom = !isset($elem['validPeriodFrom'])? 'NULL' : '"' . addslashes($elem['validPeriodFrom']) . '"';
	                        
	                        if ($exportmeasureunits->dry()) {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The exportRateUnit does not exist", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('INSERT INTO tblexportrateunits
                                                                (code,
                                                                name,
                                                                periodTo,
                                                                status,
                                                                validPeriodFrom,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $code . ',
                                                                ' . $name . ',
                                                                ' . $periodTo . ',
                                                                ' . $status . ',
                                                                ' . $validPeriodFrom . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to insert into table tblexportrateunits was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        } else {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The exportRateUnit exists", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('UPDATE tblexportrateunits SET code = ' . $code .
	                                    ', name = ' . $name .
	                                    ', periodTo = ' . $periodTo .
	                                    ', status = ' . $status .
	                                    ', validPeriodFrom = ' . $validPeriodFrom .
	                                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
	                                    ' WHERE code = ' . $code));
	                                
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to update table tblexportrateunits was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        }
	                        
	                    }
	                }
	                
	                if (isset($data['currencyType'])){
	                    $currency = new currencies($this->db);
	                    
	                    foreach($data['currencyType'] as $elem){
	                        
	                        $currency->getByCode(trim($elem['value']));
	                        
	                        $code = !isset($elem['value'])? 'NULL' : '"' . addslashes($elem['value']) . '"';
	                        $name = !isset($elem['name'])? 'NULL' : '"' . addslashes($elem['name']) . '"';
	                        $desc = !isset($elem['description'])? 'NULL' : '"' . addslashes($elem['description']) . '"';
	                        
	                        if ($currency->dry()) {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The currencyType does not exist", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('INSERT INTO tblcurrencies
                                                                (code,
                                                                name,
                                                                description,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $code . ',
                                                                ' . $name . ',
                                                                ' . $desc . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to insert into table tblcurrencies was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        } else {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The currencyType exists", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('UPDATE tblcurrencies SET code = ' . $code .
	                                    ', name = ' . $name .
	                                    ', description = ' . $desc .
	                                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
	                                    ' WHERE code = ' . $code));
	                                
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to update table tblcurrencies was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        }
	                        
	                    }
	                }
	                
	                /**
	                 * Author: Francis Lubanga <frncslubanga@gmail.com>
	                 * Date: 2025-08-03
	                 * Description: Sync hs codes
	                 */
	                /*if (isset($data['currencyType'])){
	                    $hscode = new hscodes($this->db);
	                    
	                    foreach($data['currencyType'] as $elem){
	                        
	                        $hscode->getByCode(trim($elem['value']));
	                        
	                        $code = !isset($elem['value'])? 'NULL' : '"' . addslashes($elem['value']) . '"';
	                        $name = !isset($elem['name'])? 'NULL' : '"' . addslashes($elem['name']) . '"';
	                        $desc = !isset($elem['description'])? 'NULL' : '"' . addslashes($elem['description']) . '"';
	                        
	                        if ($hscode->dry()) {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The currencyType does not exist", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('INSERT INTO tblhscodes
                                                                (code,
                                                                name,
                                                                description,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $code . ',
                                                                ' . $name . ',
                                                                ' . $desc . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to insert into table tblcurrencies was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        } else {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The currencyType exists", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('UPDATE tblcurrencies SET code = ' . $code .
	                                    ', name = ' . $name .
	                                    ', description = ' . $desc .
	                                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
	                                    ' WHERE code = ' . $code));
	                                
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to update table tblcurrencies was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        }
	                        
	                    }
	                }*/
	                
	                
	                /**
	                 * Author: Francis Lubanga <frncslubanga@gmail.com>
	                 * Date: 2025-08-03
	                 * Description: Sync delivery terms
	                 */
	                if (isset($data['deliveryTerms'])){
	                    $deliveryterm = new deliveryterms($this->db);
	                    
	                    foreach($data['deliveryTerms'] as $elem){
	                        
	                        $deliveryterm->getByCode(trim($elem['value']));
	                        
	                        $code = !isset($elem['value'])? 'NULL' : '"' . addslashes($elem['value']) . '"';
	                        $name = !isset($elem['name'])? 'NULL' : '"' . addslashes($elem['name']) . '"';
	                        $desc = !isset($elem['description'])? 'NULL' : '"' . addslashes($elem['description']) . '"';
	                        
	                        if ($deliveryterm->dry()) {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The deliveryTerm does not exist", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('INSERT INTO tbldeliverytermscodes
                                                                (code,
                                                                name,
                                                                description,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $code . ',
                                                                ' . $name . ',
                                                                ' . $desc . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to insert into table tbldeliverytermscodes was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        } else {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The deliveryTerm exists", 'r');
	                            
	                            try{
	                                
	                                $this->db->exec(array('UPDATE tbldeliverytermscodes SET code = ' . $code .
	                                    ', name = ' . $name .
	                                    ', description = ' . $desc .
	                                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
	                                    ' WHERE code = ' . $code));
	                                
	                            } catch (Exception $e) {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The operation to update table tbldeliverytermscodes was not successful. The error message is " . $e->getMessage(), 'r');
	                            }
	                        }
	                        
	                    }
	                }
	                
	                
	                /*if (isset($data['exciseDutyList'])){
	                    
	                    try{
	                        $this->db->exec(array('TRUNCATE TABLE tblexcisedutylist'));
	                        $this->db->exec(array('TRUNCATE TABLE tblexcisedutydetailslist'));
	                    } catch (Exception $e) {
	                        $this->logger->write("Administration Controller : syncefrisdics() : The operation to truncate exciseduty tables was not successful. The error message is " . $e->getMessage(), 'r');
	                    }
	                    
	                    foreach($data['exciseDutyList'] as $elem){
	                        	
	                        if (isset($elem['effectiveDate'])) {
	                            $effectiveDate = $elem['effectiveDate']; //13/06/2019
	                            $effectiveDate = str_replace('/', '-', $effectiveDate);//Replace / with -
	                            $effectiveDate = date("Y-m-d", strtotime($effectiveDate));
	                            $effectiveDate = '"' . $effectiveDate . '"';
	                        } else {
	                            $effectiveDate = 'NULL';
	                        }
	                        
	                        $goodService = !isset($elem['goodService'])? 'NULL' : '"' . addslashes($elem['goodService']) . '"';
	                        $uraid = !isset($elem['id'])? 'NULL' : $elem['id'];
	                        $parentClass = !isset($elem['parentClass'])? 'NULL' : '"' . addslashes($elem['parentClass']) . '"';
	                        $rateText = !isset($elem['rateText'])? 'NULL' : '"' . addslashes($elem['rateText']) . '"';
	                        
	                        try{
	                            
	                            $this->db->exec(array('INSERT INTO tblexcisedutylist
                                                                (effectiveDate,
                                                                goodService,
                                                                uraid,
                                                                parentClass,
                                                                rateText,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $effectiveDate . ',
                                                                ' . $goodService . ',
                                                                ' . $uraid . ',
                                                                ' . $parentClass . ',
                                                                ' . $rateText . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                        } catch (Exception $e) {
	                            $this->logger->write("Administration Controller : syncefrisdics() : The operation to insert into table tblexcisedutylist was not successful. The error message is " . $e->getMessage(), 'r');
	                        }
	                        
	                        if (isset($elem['exciseDutyDetailsList'])){
	                            
	                            if ($elem['exciseDutyDetailsList']) {
	                                
	                                foreach($elem['exciseDutyDetailsList'] as $det){
	                                    
	                                    $currency = !isset($det['currency'])? 'NULL' : $det['currency'];
	                                    $exciseDutyId = !isset($det['exciseDutyId'])? 'NULL' : $det['exciseDutyId'];
	                                    $uraid = !isset($det['id'])? 'NULL' : $det['id'];
	                                    
	                                    $rate = !isset($det['rate'])? 'NULL' : $det['rate'];
	                                    $rate = trim($rate) == 'Nil'? 'NULL' : $det['rate'];
	                                    $this->logger->write("Administration Controller : syncefrisdics() : The rate is: " . $rate, 'r');
	                                    
	                                    $type = !isset($det['type'])? 'NULL' : $det['type'];
	                                    $unit = !isset($det['unit'])? 'NULL' : $det['unit'];
	                                    
	                                    try{
	                                        
	                                        $this->db->exec(array('INSERT INTO tblexcisedutydetailslist
                                                                (currency,
                                                                exciseDutyId,
                                                                uraid,
                                                                rate,
                                                                type,
                                                                unit,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $currency . ',
                                                                ' . $exciseDutyId . ',
                                                                ' . $uraid . ',
                                                                ' . $rate . ',
                                                                ' . $type . ',
                                                                ' . $unit . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                                    } catch (Exception $e) {
	                                        $this->logger->write("Administration Controller : syncefrisdics() : The operation to insert into table tblexcisedutydetailslist was not successful. The error message is " . $e->getMessage(), 'r');
	                                    }
	                                }
	                            } else {
	                                $this->logger->write("Administration Controller : syncefrisdics() : The execise duty: " . $goodService . " has no details", 'r');
	                            }
	                            
	                            
	                        } 
	                        
	                    }
	                }*/
	                
	                
	                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync EFRIS dictionaries by " . $this->f3->get('SESSION.username') . " was successful");
	                self::$systemalert = "The operation to sync EFRIS dictionaries by " . $this->f3->get('SESSION.username') . " was successful, and all relevant details have been updated.";
	            } else {//NOTHING RETURNED BY API
	                $this->logger->write("Administration Controller : syncefrisdics() : The API did not return anything", 'r');
	            }
	        }
	        
	        self::index('tab_users', 'tab_1', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : syncefrisdics() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }
	}
	
	/**
	 *	@name syncefrisexcisedutylist
	 *  @desc sync EFRIS exciseduty list from EFRIS
	 *	@return
	 *	@param
	 **/
	function syncefrisexcisedutylist(){
	    $operation = NULL; //tblevents
	    $permission = 'SYNCEFRISEXCISEDUTYLIST'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
	        date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
	    }
	    
	    $this->logger->write("Administration Controller : syncefrisexcisedutylist() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        //$data = json_encode(new stdClass);
	        
	        $data = $this->util->syncefrisexcisedutylist($this->f3->get('SESSION.id'));//will return JSON.
	        //var_dump($data);
	        $data = json_decode($data, true);
	        
	        if (isset($data['returnCode'])){
	            $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The operation to sync EFRIS excise dutylist was not successful. The error message is " . $data['returnMessage'], 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync EFRIS excise dutylist by " . $this->f3->get('SESSION.username') . " was not successful");
	            self::$systemalert = "The operation to sync EFRIS excise dutylist by " . $this->f3->get('SESSION.username') . " was not successful. The error message is " . $data['returnMessage'];
	        } else {
	            if ($data) {
	                 
	                if (isset($data['exciseDutyList'])){
	                    
	                    try{
	                        $this->db->exec(array('TRUNCATE TABLE tblexcisedutylist'));
	                        $this->db->exec(array('TRUNCATE TABLE tblexcisedutydetailslist'));
	                    } catch (Exception $e) {
	                        $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The operation to truncate exciseduty tables was not successful. The error message is " . $e->getMessage(), 'r');
	                    }
	                    
	                    foreach($data['exciseDutyList'] as $elem){
	                        
	                        if (isset($elem['effectiveDate'])) {
	                            $effectiveDate = $elem['effectiveDate']; //13/06/2019
	                            $effectiveDate = str_replace('/', '-', $effectiveDate);//Replace / with -
	                            $effectiveDate = date("Y-m-d", strtotime($effectiveDate));
	                            // $effectiveDate = '"' . $effectiveDate . '"';
	                        } else {
	                            $effectiveDate = date("Y-m-d", strtotime('1900-01-01'));
	                        }
	                        
	                        $code = !isset($elem['exciseDutyCode'])? '' : $elem['exciseDutyCode'];
	                        $goodService = !isset($elem['goodService'])? '' : $elem['goodService'];
	                        $uraid = !isset($elem['id'])? '' : $elem['id'];
	                        $parentClass = !isset($elem['parentClass'])? '' : $elem['parentClass'];
	                        $rateText = !isset($elem['rateText'])? '' : $elem['rateText'];
	                        
	                        $isLeafNode = !isset($elem['isLeafNode'])? '' : $elem['isLeafNode'];
	                        $parentCode = !isset($elem['parentCode'])? '' : $elem['parentCode'];
	                        
	                        try{
	                            
	                            $sql = 'INSERT INTO tblexcisedutylist
                                                                (code,
                                                                effectiveDate,
                                                                goodService,
                                                                uraid,
                                                                parentClass,
                                                                isLeafNode,
                                                                parentCode,
                                                                rateText,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                ("' . addslashes($code) . '",
                                                                "' . $effectiveDate . '",
                                                                "' . addslashes($goodService) . '",
                                                                "' . addslashes($uraid) . '",
                                                                "' . addslashes($parentClass) . '",
                                                                "' . addslashes($isLeafNode) . '",
	                                                            "' . addslashes($parentCode) . '",
	                                                            "' . addslashes($rateText) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')';
	                            $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The tblexcisedutylist insert sql is: " . $sql, 'r');
	                            $this->db->exec(array(trim($sql)));
	                        } catch (Exception $e) {
	                            $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The operation to insert into table tblexcisedutylist was not successful. The error message is " . $e->getMessage(), 'r');
	                        }
	                        
	                        if (isset($elem['exciseDutyDetailsList'])){
	                            
	                            if ($elem['exciseDutyDetailsList']) {
	                                
	                                foreach($elem['exciseDutyDetailsList'] as $det){
	                                    
	                                    $currency = !isset($det['currency'])? 'NULL' : $det['currency'];
	                                    $exciseDutyId = !isset($det['exciseDutyId'])? '' : $det['exciseDutyId'];
	                                    $uraid = !isset($det['id'])? '' : $det['id'];
	                                    
	                                    $rate = !isset($det['rate'])? '' : $det['rate'];
	                                    // $rate = trim($rate) == 'Nil'? 'NULL' : $det['rate'];
	                                    $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The rate is: " . $rate, 'r');
	                                    
	                                    $type = !isset($det['type'])? 'NULL' : $det['type'];
	                                    $unit = !isset($det['unit'])? '' : $det['unit'];
	                                    
	                                    try{
	                                        $sql_c = 'INSERT INTO tblexcisedutydetailslist
                                                                (currency,
                                                                exciseDutyId,
                                                                uraid,
                                                                rate,
                                                                type,
                                                                unit,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $currency . ',
                                                                "' . addslashes($exciseDutyId) . '",
                                                                "' . addslashes($uraid) . '",
                                                                "' . addslashes($rate) . '",
                                                                ' . $type . ',
                                                                "' . addslashes($unit) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')';
	                                        
	                                        $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The tblexcisedutydetailslist insert sql is: " . $sql, 'r');
	                                        $this->db->exec(array(trim($sql_c)));
	                                    } catch (Exception $e) {
	                                        $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The operation to insert into table tblexcisedutydetailslist was not successful. The error message is " . $e->getMessage(), 'r');
	                                    }
	                                }
	                            } else {
	                                $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The execise duty: " . $goodService . " has no details", 'r');
	                            }
	                            
	                            
	                        } else {
	                            $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The execise duty: " . $goodService . " has no details", 'r');
	                        }
	                        
	                    }
	                }
	                
	                
	                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync EFRIS exciseduty list by " . $this->f3->get('SESSION.username') . " was successful");
	                self::$systemalert = "The operation to sync the EFRIS exciseduty list by " . $this->f3->get('SESSION.username') . " was successful, and all relevant details have been updated.";
	            } else {//NOTHING RETURNED BY API
	                $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The API did not return anything", 'r');
	            }
	        }
	        
	        self::index('tab_users', 'tab_1', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : syncefrisexcisedutylist() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }
	}
	
	
	/**
	 *	@name synchscodelist
	 *  @desc sync HS Code list from EFRIS
	 *	@return
	 *	@param
	 **/
	function synchscodelist(){
	    $operation = NULL; //tblevents
	    $permission = 'SYNCEFRISDICTIONARIES'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
	        date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
	    }
	    
	    $this->logger->write("Administration Controller : synchscodelist() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        //$data = json_encode(new stdClass);
	        
	        $data = $this->util->synchscodelist($this->f3->get('SESSION.id'));//will return JSON.
	        //var_dump($data);
	        $data = json_decode($data, true);
	        
	        if (isset($data['returnCode'])){
	            $this->logger->write("Administration Controller : synchscodelist() : The operation to sync HS codes was not successful. The error message is " . $data['returnMessage'], 'r');
	            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync HS codes by " . $this->f3->get('SESSION.username') . " was not successful");
	            self::$systemalert = "The operation to sync HS codes by " . $this->f3->get('SESSION.username') . " was not successful. The error message is " . $data['returnMessage'];
	        } else {
	            if ($data) {
	                $hscode = new hscodes($this->db);
	                foreach($data as $elem){
	                    $hscode->getByCode(trim($elem['hsCode']));
	                    
	                    $hsCode = !isset($elem['hsCode'])? 'NULL' : '"' . addslashes($elem['hsCode']) . '"';
	                    $name = !isset($elem['description'])? 'NULL' : '"' . addslashes($elem['description']) . '"';
	                    $isLeaf = !isset($elem['isLeaf'])? 'NULL' : '"' . addslashes($elem['isLeaf']) . '"';
	                    $parentClass = !isset($elem['parentClass'])? 'NULL' : '"' . addslashes($elem['parentClass']) . '"';
	                    
	                    
	                    
	                    $this->logger->write("Administration Controller : synchscodelist() : The hsCode is: " . $hsCode, 'r');
	                    
	                    if ($hscode->dry()) {
	                        $this->logger->write("Administration Controller : synchscodelist() : The hsCode does not exist", 'r');
	                        
	                        try{
	                            
	                            $this->db->exec(array('INSERT INTO tblhscodes
                                                                (code,
                                                                isLeaf,
                                                                parentClass,
                                                                name,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $hsCode . ',
                                                                ' . $isLeaf . ',
                                                                ' . $parentClass . ',
                                                                ' . $name . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
	                        } catch (Exception $e) {
	                            $this->logger->write("Administration Controller : synchscodelist() : The operation to insert into table tblhscodes was not successful. The error message is " . $e->getMessage(), 'r');
	                        }
	                    } else {
	                        $this->logger->write("Administration Controller : synchscodelist() : The hsCode exists", 'r');
	                        
	                        try{
	                            
	                            $this->db->exec(array('UPDATE tblhscodes SET code = ' . $hsCode .
	                                ', isLeaf = ' . $isLeaf .
	                                ', parentClass = ' . $parentClass .
	                                ', description = ' . $description .
	                                ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
	                                ' WHERE code = ' . $hsCode));
	                            
	                        } catch (Exception $e) {
	                            $this->logger->write("Administration Controller : synchscodelist() : The operation to update table tblhscodes was not successful. The error message is " . $e->getMessage(), 'r');
	                        }
	                    }
	                }
	                
	                
	                
	                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync HS codes by " . $this->f3->get('SESSION.username') . " was successful");
	                self::$systemalert = "The operation to sync HS codes by " . $this->f3->get('SESSION.username') . " was successful, and all relevant details have been updated.";
	            } else {//NOTHING RETURNED BY API
	                $this->logger->write("Administration Controller : synchscodelist() : The API did not return anything", 'r');
	            }
	        }
	        
	        self::index('tab_users', 'tab_1', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : synchscodelist() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }
	}
	
	
	/**
	 *	@name recyclelogs
	 *  @desc Recycle all eTW logs
	 *	@return
	 *	@param
	 **/
	function recyclelogs(){
	    $operation = NULL; //tblevents
	    $permission = 'RECYLELOGS'; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
	        date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
	    }
	    
	    $this->logger->write("Administration Controller : recyclelogs() : Checking permissions", 'r');
	    if ($this->userpermissions[$permission]) {
	        $logExtension = trim($this->appsettings['LOGEXTENSION']);
	        
	        $appLogName = trim($this->appsettings['APPLOG']);
	        $appUtilLogName = trim($this->appsettings['APPUTIL']);
	        $appErrorLogName = trim($this->appsettings['APPERRORLOG']);
	        

	        $appbasefolder = trim($this->appsettings['HOME']);
	        $appfs = new \FAL\LocalFS($appbasefolder);
	        $this->logger->write("Administration Controller : recyclelogs() : The app base folder is: " . $appbasefolder, 'r');
	        
	        $appLogNewName = '//' . $appLogName . '-' . date('Y-m-d') . '.' . $logExtension;
	        $appLogFullName = '//' . $appLogName . '.' . $logExtension;
	        
        
	        try {
	            if($appfs->exists($appLogFullName)){
	                $this->logger->write("Administration Controller : recyclelogs() : The file exists", 'r');
	                
	                if($appfs->exists($appLogNewName)){
	                    $appLogNewName = '//' . $appLogName . '-' . date('Y-m-d') . ' (' . md5(uniqid(rand(), true)) . ').' . $logExtension;
	                } 	
	                
	                $appfs->move($appLogFullName, $appLogNewName);
	            } else {
	                $this->logger->write("Administration Controller : recyclelogs() : The file does not exist", 'r');
	            }
	        } catch (Exception $e) {
	            $this->logger->write("Administration Controller : recyclelogs() : There was an error. The error message is " . $e->getMessage(), 'r');
	        }
	        
	        $appUtilLogNewName = '//' . $appUtilLogName . '-' . date('Y-m-d') . '.' . $logExtension;
	        $appUtilLogFullName = '//' . $appUtilLogName . '.' . $logExtension;
	        
	        
	        try {
	            if($appfs->exists($appUtilLogFullName)){
	                $this->logger->write("Administration Controller : recyclelogs() : The file exists", 'r');
	                
	                if($appfs->exists($appUtilLogNewName)){
	                    $appUtilLogNewName = '//' . $appUtilLogName . '-' . date('Y-m-d') . ' (' . md5(uniqid(rand(), true)) . ').' . $logExtension;
	                }
	                
	                $appfs->move($appUtilLogFullName, $appUtilLogNewName);
	            } else {
	                $this->logger->write("Administration Controller : recyclelogs() : The file does not exist", 'r');
	            }
	        } catch (Exception $e) {
	            $this->logger->write("Administration Controller : recyclelogs() : There was an error. The error message is " . $e->getMessage(), 'r');
	        }
	        
	        $appErrorLogNewName = '//' . $appErrorLogName . '-' . date('Y-m-d') . '.' . $logExtension;
	        $appErrorLogFullName = '//' . $appErrorLogName . '.' . $logExtension;
	        
	        
	        try {
	            if($appfs->exists($appErrorLogFullName)){
	                $this->logger->write("Administration Controller : recyclelogs() : The file exists", 'r');
	                
	                if($appfs->exists($appErrorLogNewName)){
	                    $appErrorLogNewName = '//' . $appErrorLogName . '-' . date('Y-m-d') . ' (' . md5(uniqid(rand(), true)) . ').' . $logExtension;
	                }
	                
	                $appfs->move($appErrorLogFullName, $appErrorLogNewName);
	            } else {
	                $this->logger->write("Administration Controller : recyclelogs() : The file does not exist", 'r');
	            }
	        } catch (Exception $e) {
	            $this->logger->write("Administration Controller : recyclelogs() : There was an error. The error message is " . $e->getMessage(), 'r');
	        }

	        $apiLogName = trim($this->appsettings['APILOG']);
	        $apiUtilLogName = trim($this->appsettings['APIUTIL']);
	        $apiErrorLogName = trim($this->appsettings['APIERRORLOG']);
	        
	        $apibasefolder = trim($this->appsettings['APIHOME']);
	        $apifs = new \FAL\LocalFS($apibasefolder);
	        $this->logger->write("Administration Controller : recyclelogs() : The api base folder is: " . $apibasefolder, 'r');
	        
	        $apiLogNewName = '//' . $apiLogName . '-' . date('Y-m-d') . '.' . $logExtension;
	        $apiLogFullName = '//' . $apiLogName . '.' . $logExtension;
	        
	        
	        try {
	            if($apifs->exists($apiLogFullName)){
	                $this->logger->write("Administration Controller : recyclelogs() : The file exists", 'r');
	                
	                if($apifs->exists($apiLogNewName)){
	                    $apiLogNewName = '//' . $apiLogName . '-' . date('Y-m-d') . ' (' . md5(uniqid(rand(), true)) . ').' . $logExtension;
	                }
	                
	                $apifs->move($apiLogFullName, $apiLogNewName);
	            } else {
	                $this->logger->write("Administration Controller : recyclelogs() : The file does not exist", 'r');
	            }
	        } catch (Exception $e) {
	            $this->logger->write("Administration Controller : recyclelogs() : There was an error. The error message is " . $e->getMessage(), 'r');
	        }
	        
	        $apiUtilLogNewName = '//' . $apiUtilLogName . '-' . date('Y-m-d') . '.' . $logExtension;
	        $apiUtilLogFullName = '//' . $apiUtilLogName . '.' . $logExtension;
	        
	        
	        try {
	            if($apifs->exists($apiUtilLogFullName)){
	                $this->logger->write("Administration Controller : recyclelogs() : The file exists", 'r');
	                
	                if($apifs->exists($apiUtilLogNewName)){
	                    $apiUtilLogNewName = '//' . $apiUtilLogName . '-' . date('Y-m-d') . ' (' . md5(uniqid(rand(), true)) . ').' . $logExtension;
	                }
	                
	                $apifs->move($apiUtilLogFullName, $apiUtilLogNewName);
	            } else {
	                $this->logger->write("Administration Controller : recyclelogs() : The file does not exist", 'r');
	            }
	        } catch (Exception $e) {
	            $this->logger->write("Administration Controller : recyclelogs() : There was an error. The error message is " . $e->getMessage(), 'r');
	        }
	        
	        $apiErrorLogNewName = '//' . $apiErrorLogName . '-' . date('Y-m-d') . '.' . $logExtension;
	        $apiErrorLogFullName = '//' . $apiErrorLogName . '.' . $logExtension;
	        
	        
	        try {
	            if($apifs->exists($apiErrorLogFullName)){
	                $this->logger->write("Administration Controller : recyclelogs() : The file exists", 'r');
	                
	                if($apifs->exists($apiErrorLogNewName)){
	                    $apiErrorLogNewName = '//' . $apiErrorLogName . '-' . date('Y-m-d') . ' (' . md5(uniqid(rand(), true)) . ').' . $logExtension;
	                }
	                
	                $apifs->move($apiErrorLogFullName, $apiErrorLogNewName);
	            } else {
	                $this->logger->write("Administration Controller : recyclelogs() : The file does not exist", 'r');
	            }
	        } catch (Exception $e) {
	            $this->logger->write("Administration Controller : recyclelogs() : There was an error. The error message is " . $e->getMessage(), 'r');
	        }
	        
	        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to recycle logs by " . $this->f3->get('SESSION.username') . " was successful");
	        self::$systemalert = "The operation to recyle logs by " . $this->f3->get('SESSION.username') . " was successful";
	        
	        self::index('tab_users', 'tab_1', self::$systemalert);
	    } else {
	        $this->logger->write("Administration Controller : recyclelogs() : The user is not allowed to perform this function", 'r');
	        $this->f3->reroute('/forbidden');
	    }
	}
}
?>