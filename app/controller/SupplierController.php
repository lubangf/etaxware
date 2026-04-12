<?php
use QuickBooksOnline\API\DataService\DataService;
/**
 * @name SupplierController
 * @desc This file is part of the etaxware system. The is the Supplier controller class
 * @date 11-05-2020
 * @file SupplierController.php
 * @path ./app/controller/SupplierController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
Class SupplierController extends MainController{
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
        $permission = 'VIEWSUPPLIERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Supplier Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $this->f3->set('pagetitle','Suppliers');
            $this->f3->set('pagecontent','Supplier.htm');
            $this->f3->set('pagescripts','SupplierFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Supplier Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    
    
    /**
     *	@name view
     *  @desc view Supplier
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = '', $tab = '', $tabpane = '') {
        $operation = NULL; //tblevents
        $permission = 'VIEWSUPPLIERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Supplier Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Supplier Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Supplier Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $status = new statuses($this->db);
            $supplierstatuscodes = $status->getByGroupID(1026);
            $this->f3->set('supplierstatuscodes', $supplierstatuscodes);
            
            $country = new countries($this->db);
            $countries = $country->all();
            $this->f3->set('countries', $countries);
            
            $sector = new sectors($this->db);
            $sectors = $sector->all();
            $this->f3->set('sectors', $sectors);
            
            $buyertype = new buyertypes($this->db);
            $buyertypes = $buyertype->all();
            $this->f3->set('buyertypes', $buyertypes);
            
            if (is_string($tab) && is_string($tabpane)){
                $this->logger->write("Supplier Controller : view() : The value of v_id is " . $v_id, 'r');
                $this->logger->write("Supplier Controller : view() : The value of tab is " . $tab, 'r');
                $this->logger->write("Supplier Controller : view() : The value of tabpane " . $tabpane, 'r');
            } 
            
            if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
                $id = trim($this->f3->get('PARAMS[id]'));
                $this->logger->write("Supplier Controller : view() : The is a GET call & id to view is " . $id, 'r');
                
                $supplier = new suppliers($this->db);
                $supplier->getByID($id);
                
                
                $this->f3->set('supplier', $supplier);
                
                if (is_string($tab) && is_string($tabpane)){//this check is necessary for cases where the GET request is system initiated. The params sent to the view functions are non-string.
                    $this->f3->set('currenttab', $tab);
                    $this->f3->set('currenttabpane', $tabpane);
                } else {
                    $this->f3->set('currenttab', 'tab_general');
                    $this->f3->set('currenttabpane', 'tab_1');
                    $this->f3->set('path', '../' . $this->path);
                }
                
                $this->f3->set('pagetitle','Edit Supplier | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path); //overide the main solution path
            } elseif (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id')))) {//Open EDIT mode
                $id = trim($this->f3->get('POST.id'));
                $this->logger->write("Supplier Controller : view() : This is a POST call & the id to view is " . $id, 'r');
                
                $supplier = new suppliers($this->db);
                $supplier->getByID($id);
                
                
                
                $this->f3->set('supplier', $supplier);
                
                if (trim($this->f3->get('POST.currenttab')) !== '' || !empty(trim($this->f3->get('POST.currenttab')))) {
                    $this->f3->set('currenttab', trim($this->f3->get('POST.currenttab')));
                } else {
                    $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
                }
                
                if (trim($this->f3->get('POST.currenttabpane')) !== '' || !empty(trim($this->f3->get('POST.currenttabpane')))) {
                    $this->f3->set('currenttabpane', trim($this->f3->get('POST.currenttabpane')));
                } else {
                    $this->f3->set('currenttabpane', 'tab_1');
                }
                
                $this->f3->set('pagetitle','Edit Supplier | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
            } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
                $id = trim($v_id);
                $this->logger->write("Supplier Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
                
                $supplier = new suppliers($this->db);
                $supplier->getByID($id);
                
                
                
                $this->f3->set('supplier', $supplier);
                
                if (trim($tab) !== '' || !empty(trim($tab))) {
                    $this->f3->set('currenttab', $tab);
                } else {
                    $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
                }
                
                if (trim($tabpane) !== '' || !empty(trim($tabpane))) {
                    $this->f3->set('currenttabpane', $tabpane);
                } else {
                    $this->f3->set('currenttabpane', 'tab_1');
                }
                
                $this->f3->set('pagetitle','Edit Supplier | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
                
                $this->f3->set('pagecontent','EditSupplier.htm');
                $this->f3->set('pagescripts','EditSupplierFooter.htm');
                echo \Template::instance()->render('Layout.htm');
                exit(); //exit the function so no extra code executes
            } else {
                $this->logger->write("Supplier Controller : view() : No id was selected", 'r');
                $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page
                exit();
            }
            
            $this->logger->write("Supplier Controller : view() : The currenttab has been set to " . $this->f3->get('currenttab'), 'r');
            $this->logger->write("Supplier Controller : view() : The currenttabpane has been set to " . $this->f3->get('currenttabpane'), 'r');
            
            $this->f3->set('pagecontent','EditSupplier.htm');
            $this->f3->set('pagescripts','EditSupplierFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Supplier Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name add
     *  @desc add Supplier
     *	@return NULL
     *	@param NULL
     **/
    function add() {
        $operation = NULL; //tblevents
        $permission = 'CREATESUPPLIERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Supplier Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {           

            
            $status = new statuses($this->db);
            $supplierstatuscodes = $status->getByGroupID(1026);
            $this->f3->set('supplierstatuscodes', $supplierstatuscodes);
            
            $country = new countries($this->db);
            $countries = $country->all();
            $this->f3->set('countries', $countries);
            
            $sector = new sectors($this->db);
            $sectors = $sector->all();
            $this->f3->set('sectors', $sectors);
            
            $buyertype = new buyertypes($this->db);
            $buyertypes = $buyertype->all();
            $this->f3->set('buyertypes', $buyertypes);
            
            $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
            $this->f3->set('currenttabpane', 'tab_1');
            
            
            $supplier = array(
                "id" => NULL,
                "legalname" => ''
            );
            $this->f3->set('supplier', $supplier);
            
            $this->f3->set('pagetitle','Create Supplier');
            
            $this->f3->set('pagecontent','EditSupplier.htm');
            $this->f3->set('pagescripts','EditSupplierFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Supplier Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    /**
     * edit supplier
     *
     * @name edit
     * @return NULL
     * @param
     *            NULL
     */
    function edit(){
        $supplier = new suppliers($this->db);
        $sector = new sectors($this->db);
        $country = new countries($this->db);
        $currenttab = trim($this->f3->get('POST.currenttab'));
        $currenttabpane = trim($this->f3->get('POST.currenttabpane'));
        $id = 0;
        
        if (trim($this->f3->get('POST.supplierid')) !== '' || ! empty(trim($this->f3->get('POST.supplierid')))) { // EDIT Operation
            $operation = NULL; // tblevents
            $permission = 'EDITSUPPLIERS'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications
            
            $this->logger->write("Supplier Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $id = trim($this->f3->get('POST.supplierid'));
                $this->logger->write("Supplier Controller : edit() : The id to be edited is " . $id, 'r');
                $supplier->getByID($id);
                
                if ($currenttab == 'tab_general') {
                    // @TODO check the params for empty/null values
                    
                    /*if (trim($this->f3->get('POST.suppliertype')) !== '' || ! empty(trim($this->f3->get('POST.suppliertype')))) {
                        $this->f3->set('POST.type', $this->f3->get('POST.suppliertype'));
                    } else {
                        $this->f3->set('POST.type', $supplier->type);
                    }*/
                    
                    $this->f3->set('POST.type', $this->appsettings['B2BCODE']);
                    
                    if (trim($this->f3->get('POST.supplierstatus')) !== '' || ! empty(trim($this->f3->get('POST.supplierstatus')))) {
                        $this->f3->set('POST.status', $this->f3->get('POST.supplierstatus'));
                    } else {
                        $this->f3->set('POST.status', $supplier->status);
                    }
                    
                    if (trim($this->f3->get('POST.suppliercountrycode')) !== '' || ! empty(trim($this->f3->get('POST.suppliercountrycode')))) {
                        $this->f3->set('POST.countryCode', $this->f3->get('POST.suppliercountrycode'));
                        $country->getByCode($this->f3->get('POST.suppliercountrycode'));
                        $this->f3->set('POST.citizineship', $country->name);
                    } else {
                        $this->f3->set('POST.countryCode', $supplier->countryCode);
                    }
                    
                    if (trim($this->f3->get('POST.suppliersector')) !== '' || ! empty(trim($this->f3->get('POST.suppliersector')))) {
                        $this->f3->set('POST.sectorCode', $this->f3->get('POST.suppliersector'));
                        $sector->getByCode($this->f3->get('POST.suppliersector'));
                        $this->f3->set('POST.sector', $sector->name);
                    } else {
                        $this->f3->set('POST.sectorCode', $supplier->sectorCode);
                    }
                    
                    
                    if (trim($this->f3->get('POST.suppliertin')) !== '' || ! empty(trim($this->f3->get('POST.suppliertin')))) {
                        $this->f3->set('POST.tin', $this->f3->get('POST.suppliertin'));
                    } else {
                        $this->f3->set('POST.tin', $supplier->tin);
                    }
                    
                    if (trim($this->f3->get('POST.supplierninbrn')) !== '' || ! empty(trim($this->f3->get('POST.supplierninbrn')))) {
                        $this->f3->set('POST.ninbrn', $this->f3->get('POST.supplierninbrn'));
                    } else {
                        $this->f3->set('POST.ninbrn', $supplier->ninbrn);
                    }
                    
                    if (trim($this->f3->get('POST.supplierlegalname')) !== '' || ! empty(trim($this->f3->get('POST.supplierlegalname')))) {
                        $this->f3->set('POST.legalname', $this->f3->get('POST.supplierlegalname'));
                    } else {
                        $this->f3->set('POST.legalname', $supplier->legalname);
                    }
                    
                    if (trim($this->f3->get('POST.supplierbusinessname')) !== '' || ! empty(trim($this->f3->get('POST.supplierbusinessname')))) {
                        $this->f3->set('POST.businessname', $this->f3->get('POST.supplierbusinessname'));
                    } else {
                        $this->f3->set('POST.businessname', $supplier->businessname);
                    }
                    
                    if (trim($this->f3->get('POST.supplieraddress')) !== '' || ! empty(trim($this->f3->get('POST.supplieraddress')))) {
                        $this->f3->set('POST.address', $this->f3->get('POST.supplieraddress'));
                    } else {
                        $this->f3->set('POST.address', $supplier->address);
                    }
                    
                    if (trim($this->f3->get('POST.suppliermobilephone')) !== '' || ! empty(trim($this->f3->get('POST.suppliermobilephone')))) {
                        $this->f3->set('POST.mobilephone', $this->f3->get('POST.suppliermobilephone'));
                    } else {
                        $this->f3->set('POST.mobilephone', $supplier->mobilephone);
                    }
                    
                    if (trim($this->f3->get('POST.supplierlinephone')) !== '' || ! empty(trim($this->f3->get('POST.supplierlinephone')))) {
                        $this->f3->set('POST.linephone', $this->f3->get('POST.supplierlinephone'));
                    } else {
                        $this->f3->set('POST.linephone', $supplier->linephone);
                    }
                    
                    if (trim($this->f3->get('POST.supplieremailaddress')) !== '' || ! empty(trim($this->f3->get('POST.supplieremailaddress')))) {
                        $this->f3->set('POST.emailaddress', $this->f3->get('POST.supplieremailaddress'));
                    } else {
                        $this->f3->set('POST.emailaddress', $supplier->emailaddress);
                    }
                    
                    if (trim($this->f3->get('POST.supplierplaceofbusiness')) !== '' || ! empty(trim($this->f3->get('POST.supplierplaceofbusiness')))) {
                        $this->f3->set('POST.placeofbusiness', $this->f3->get('POST.supplierplaceofbusiness'));
                    } else {
                        $this->f3->set('POST.placeofbusiness', $supplier->placeofbusiness);
                    }
                    
                    if (trim($this->f3->get('POST.supplierpassportnum')) !== '' || ! empty(trim($this->f3->get('POST.supplierpassportnum')))) {
                        $this->f3->set('POST.PassportNum', $this->f3->get('POST.supplierpassportnum'));
                    } else {
                        $this->f3->set('POST.PassportNum', $supplier->PassportNum);
                    }
                }
                
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                try {
                    $supplier->edit($id);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The supplier - " . $supplier->id . " - " . $supplier->legalname . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The supplier " . $supplier->legalname . " has been edited";
                    $this->logger->write("Supplier Controller : edit() : The supplier " . $supplier->legalname . " has been edited", 'r');
                } catch (Exception $e) {
                    $this->logger->write("Supplier Controller : edit() : The operation to edit supplier - " . $supplier->id . " - " . $supplier->legalname . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit supplier - " . $supplier->id . " - " . $supplier->legalname . " was not successfull");
                    self::$systemalert = "The operation to edit supplier " . $supplier->legalname . " was not successful";
                }
            } else {
                $this->logger->write("Supplier Controller : edit() : The user is not allowed to perform this function", 'r');
                $this->f3->reroute('/forbidden');
            }
        } else { // ADD Operation: mainly handles the GENERAL parameters, as the rest of the parameters will be added using the EDIT option
            $operation = NULL; // tblevents
            $permission = 'CREATESUPPLIERS'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotificationsPOST.supplierlegalname
            
            $this->logger->write("Supplier Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $this->logger->write("Supplier Controller : edit() : Adding of supplier started.", 'r');
                
                
                
                $this->f3->set('POST.type', $this->appsettings['B2BCODE']);
                $this->f3->set('POST.status', $this->f3->get('POST.supplierstatus'));
                
                $this->f3->set('POST.countryCode', $this->f3->get('POST.suppliercountrycode'));
                $country->getByCode($this->f3->get('POST.suppliercountrycode'));
                $this->f3->set('POST.citizineship', $country->name);
                
                $this->f3->set('POST.sectorCode', $this->f3->get('POST.suppliersector'));
                $sector->getByCode($this->f3->get('POST.suppliersector'));
                $this->f3->set('POST.sector', $sector->name);
                
                $this->f3->set('POST.tin', $this->f3->get('POST.suppliertin'));
                $this->f3->set('POST.ninbrn', $this->f3->get('POST.supplierninbrn'));
                $this->f3->set('POST.legalname', $this->f3->get('POST.supplierlegalname'));
                $this->f3->set('POST.businessname', $this->f3->get('POST.supplierbusinessname'));
                $this->f3->set('POST.address', $this->f3->get('POST.supplieraddress'));
                $this->f3->set('POST.mobilephone', $this->f3->get('POST.suppliermobilephone'));
                $this->f3->set('POST.linephone', $this->f3->get('POST.supplierlinephone'));
                $this->f3->set('POST.emailaddress', $this->f3->get('POST.supplieremailaddress'));
                $this->f3->set('POST.placeofbusiness', $this->f3->get('POST.supplierplaceofbusiness'));
                $this->f3->set('POST.PassportNum', $this->f3->get('POST.supplierpassportnum'));
                
                
                $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                // @TODO check the params for empty/null values
                if (trim($this->f3->get('POST.supplierlegalname')) !== '' || ! empty(trim($this->f3->get('POST.supplierlegalname')))) {
                    try {
                        // Proceed & create
                        $supplier->add();
                        // $this->logger->write("Supplier Controller : edit() : A new supplier has been added", 'r');
                        try {
                            // retrieve the most recently inserted supplier
                            // @TODO place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                            $data = array();
                            $r = $this->db->exec(array(
                                'SELECT MAX(id) "id" FROM tblsuppliers WHERE insertedby = ' . $this->f3->get('SESSION.id')
                            ));
                            foreach ($r as $obj) {
                                $data[] = $obj;
                            }
                            
                            // $this->logger->write("Supplier Controller : edit() : The supplier " . $data[0]['id'] . " has been added", 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The supplier id " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                            self::$systemalert = "The supplier has been added";
                            $id = $data[0]['id'];
                            $supplier->getByID($id);
                        } catch (Exception $e) {
                            $this->logger->write("Supplier Controller : edit() : The operation to retrieve the most recently added supplier was not successful. The error messages is " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added supplier was not successful");
                            self::$systemalert = "The operation to retrieve the most recently added supplier was not successful";
                        }
                    } catch (Exception $e) {
                        $this->logger->write("Supplier Controller : edit() : The operation to add a supplier was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a supplier was not successful");
                        self::$systemalert = "The operation to add a supplier was not successful. An internal error occured, or you are trying to add a duplicate code";
                        $this->f3->set('systemalert', self::$systemalert);
                        self::add();
                        exit();
                    }
                } else {
                    $this->logger->write("Supplier Controller : edit() : The user is not allowed to perform this function", 'r');
                    $this->f3->reroute('/forbidden');
                }
            } else { // some params are empty
                // ABORT MISSION
                self::add();
                exit();
            }
        }
        
        //$country = new countries($this->db);
        $countries = $country->all();
        $this->f3->set('countries', $countries);
        
        //$sector = new sectors($this->db);
        $sectors = $sector->all();
        $this->f3->set('sectors', $sectors);

        $status = new statuses($this->db);
        $supplierstatuscodes = $status->getByGroupID(1026);
        $this->f3->set('supplierstatuscodes', $supplierstatuscodes);
        
        $buyertype = new buyertypes($this->db);
        $buyertypes = $buyertype->all();
        $this->f3->set('buyertypes', $buyertypes);
        
        $this->f3->set('supplier', $supplier);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);
        
        $this->f3->set('systemalert', self::$systemalert);
        
        $this->f3->set('pagetitle', 'Edit Supplier | ' . $id);
        $this->f3->set('pagecontent', 'EditSupplier.htm');
        $this->f3->set('pagescripts', 'EditSupplierFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    /**
     *	@name list
     *  @desc List suppliers
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function list(){
        $operation = NULL; //tblevents
        $permission = 'VIEWSUPPLIERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Supplier Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Supplier Controller : list() : Processing list of suppliers started", 'r');
            
            $name = trim($this->f3->get('POST.name'));
            
            $this->logger->write("Supplier Controller : list() : The name param is : " . $name, 'r');
            
            if ($name !== '' || !empty($name)) {
                $subquery = " '%" . $name . "%' ";
                
                $sql = 'SELECT  p.id "ID",
                        p.legalname "Name",
                        p.erpsupplierid "ERP ID",
                        p.erpsuppliercode "ERP Code",
                        p.tin "TIN",
                        p.ninbrn "NINBRN",
                        p.PassportNum "Passport Num",
                        p.legalname "Legal Name",
                        p.businessname "Business Name",
                        p.address "Address",
                        p.mobilephone "Mobile Phone",
                        p.linephone "Line Phone",
                        p.emailaddress "Email",
                        p.placeofbusiness "Place of Business",
                        p.type "Type",
                        bt.name "Type Name",
                        p.citizineship "Citizineship",
                        p.countryCode "Country Code",
                        p.sector "Sector",
                        p.sectorCode "Sector Code",
                        p.disabled "Disabled",
                        p.inserteddt "Creation Date",
                        p.insertedby "Created By",
                        p.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblsuppliers p
                    LEFT JOIN tblbuyertypes bt ON bt.code = p.type
                    LEFT JOIN tblusers s ON p.modifiedby = s.id
                    WHERE p.legalname LIKE ' . $subquery . '
                    ORDER By p.id DESC';
            } else {
                $sql = 'SELECT  p.id "ID",
                        p.legalname "Name",
                        p.erpsupplierid "ERP ID",
                        p.erpsuppliercode "ERP Code",
                        p.tin "TIN",
                        p.ninbrn "NINBRN",
                        p.PassportNum "Passport Num",
                        p.legalname "Legal Name",
                        p.businessname "Business Name",
                        p.address "Address",
                        p.mobilephone "Mobile Phone",
                        p.linephone "Line Phone",
                        p.emailaddress "Email",
                        p.placeofbusiness "Place of Business",
                        p.type "Type",
                        bt.name "Type Name",
                        p.citizineship "Citizineship",
                        p.countryCode "Country Code",
                        p.sector "Sector",
                        p.sectorCode "Sector Code",
                        p.disabled "Disabled",
                        p.inserteddt "Creation Date",
                        p.insertedby "Created By",
                        p.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblsuppliers p
                    LEFT JOIN tblbuyertypes bt ON bt.code = p.type
                    LEFT JOIN tblusers s ON p.modifiedby = s.id
                    ORDER By p.id DESC';
            }
            
            
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Supplier Controller : list() : The operation to list the suppliers was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Supplier Controller : index() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
    
    
    /**
     *	@name downloadErpSuppliers
     *  @desc download suppliers from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function downloadErpSuppliers(){
        $operation = NULL; //tblevents
        $permission = 'SYNCPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Supplier Controller : downloadErpSuppliers() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            
            $startDate = $this->f3->get('POST.downloaderpsuppliersstartdate');
            $endDate = $this->f3->get('POST.downloaderpsuppliersenddate');
            $supplierNo = $this->f3->get('POST.downloaderpsuppliernumber');
            
            $this->logger->write("Supplier Controller : downloadErpSuppliers() : startDate: " . $startDate, 'r');
            $this->logger->write("Supplier Controller : downloadErpSuppliers() : endDate: " . $endDate, 'r');
            $this->logger->write("Supplier Controller : downloadErpSuppliers() : supplierNo: " . $supplierNo, 'r');
            
            $startDate = empty($startDate)? date('Y-m-d') : date('Y-m-d', strtotime($startDate));
            $endDate = empty($endDate)? date('Y-m-d') : date('Y-m-d', strtotime($endDate));
            $supplierNo = empty($supplierNo)? 'NULL' : $supplierNo;
            
            
            
            if ($this->platformMode == 'ERP') {
                $this->logger->write("Supplier Controller : downloadErpSuppliers() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
            } else {
                $this->logger->write("Supplier Controller : downloadErpSuppliers() : The platform is integrated.", 'r');

                if ($this->integratedErp) {
                    /**
                     * Check on integrated ERP type
                     */
                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                    
                    if (strtoupper($this->integratedErp) == 'QBO') {
                        $this->logger->write("Supplier Controller : downloadErpSuppliers() : The integrated ERP is Quicbooks Online.", 'r');
                        
                        
                        $qry = 'SELECT * FROM Vendor';
                        $qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "' And Metadata.LastUpdatedTime <= '" . $endDate . "'";
                        $this->logger->write("Supplier Controller : downloadErpSuppliers() : The query is: " . $qry, 'r');
                        
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
                                
                                $suppliers = $dataService->Query($qry);
                                
                                $error = $dataService->getLastError();
                                
                                if ($error) {
                                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : The operation to download ERP suppliers was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was successful");
                                    self::$systemalert = "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was successful.";
                                }
                                else {
                                    //print_r($suppliers);
                                    if(isset($suppliers)){
                                        if ($suppliers) {
                                            $supplier = new suppliers($this->db);
                                            
                                            $cust = array(
                                                'id' => NULL,
                                                'erpsupplierid' => NULL,
                                                'erpsuppliercode' => NULL,
                                                'tin' => NULL,
                                                'ninbrn' => NULL,
                                                'PassportNum' => NULL,
                                                'legalname' => NULL,
                                                'businessname' => NULL,
                                                'address' => NULL,
                                                'mobilephone' => NULL,
                                                'linephone' => NULL,
                                                'emailaddress' => NULL,
                                                'placeofbusiness' => NULL,
                                                'type' => $this->appsettings['B2BCODE'],
                                                'citizineship' => NULL,
                                                'countryCode' => NULL,
                                                'sector' => NULL,
                                                'sectorCode' => NULL,
                                                'datasource' => 'ERP',
                                                'status' => $this->appsettings['ACTIVESUPPLIERSTATUSID'],
                                            );
                                            
                                            foreach($suppliers as $elem){
                                                
                                                try {
                                                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : Supplier Name: " . $elem->CompanyName, 'r');
                                                    
                                                    
                                                    $erpsupplierid = $elem->Id;
                                                    $erpsuppliercode = $elem->Id;
                                                    $legalname = $elem->CompanyName;
                                                    $businessname = $elem->CompanyName;
                                                    
                                                    if(isset($elem->Mobile)){
                                                        $mobilephone = $elem->Mobile->FreeFormNumber;
                                                        $cust['mobilephone'] = $mobilephone;
                                                    }
                                                    
                                                    if(isset($elem->PrimaryPhone)){
                                                        $linephone = $elem->PrimaryPhone->FreeFormNumber;
                                                        $cust['PrimaryPhone'] = $linephone;
                                                    }
                                                    
                                                    
                                                    if(isset($elem->PrimaryEmailAddr)){
                                                        $emailaddress = $elem->PrimaryEmailAddr->Address;
                                                        $cust['emailaddress'] = $emailaddress;
                                                    }
                                                    
                                                    if(isset($elem->BillAddr)){
                                                        $address = $elem->BillAddr->Line1;
                                                        $cust['address'] = $address;
                                                    }
                                                    
                                                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : Mobile: " . $mobilephone, 'r');
                                                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : Email: " . $emailaddress, 'r');
                                                    
                                                    $cust['erpsupplierid'] = $erpsupplierid;
                                                    $cust['erpsuppliercode'] = $erpsuppliercode;
                                                    $cust['legalname'] = $legalname;
                                                    $cust['businessname'] = $businessname;
                                                    
                                                    if ($elem->Active == true) {
                                                        if ($erpsuppliercode && $legalname) {
                                                            $supplier->getByCode($erpsuppliercode);
                                                            
                                                            if ($supplier->dry()) {
                                                                $this->logger->write("Supplier Controller : downloadErpSuppliers() : The supplier does not exist", 'r');
                                                                $cust_status = $this->util->createsupplier($cust, $this->f3->get('SESSION.id'));
                                                                
                                                                if ($cust_status) {
                                                                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : The supplier " . $cust['legalname'] . " was created.", 'r');
                                                                } else {
                                                                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : The supplier " . $cust['legalname'] . " was NOT created.", 'r');
                                                                }
                                                            } else {
                                                                $this->logger->write("Supplier Controller : downloadErpSuppliers() : The supplier exists", 'r');
                                                                $cust['id'] = $supplier->id;
                                                                
                                                                $cust_status = $this->util->updatesupplier($cust, $this->f3->get('SESSION.id'));
                                                                
                                                                if ($cust_status) {
                                                                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : The supplier " . $cust['legalname'] . " was updated.", 'r');
                                                                } else {
                                                                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : The supplier " . $cust['legalname'] . " was NOT updated.", 'r');
                                                                }
                                                                
                                                            }
                                                        } else {
                                                            $this->logger->write("Supplier Controller : downloadErpSuppliers() : The supplier has no Id.", 'r');
                                                        }
                                                    } else {
                                                        $this->logger->write("Supplier Controller : downloadErpSuppliers() : The supplier is not ACTIVE.", 'r');
                                                    }
                                                    
                                                } catch (Exception $e) {
                                                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : There was an error when processing Item " . $elem->FullyQualifiedName . ". The error is " . $e->getMessage(), 'r');
                                                }
                                            }
                                        }
                                    } else {
                                        $this->logger->write("Supplier Controller : downloadErpSuppliers() : The operation to download ERP suppliers did not return records.", 'r');
                                    }
                                }
                                
                                $this->logger->write("Supplier Controller : downloadErpSuppliers() : The operation to download ERP suppliers was successful.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was successful");
                                self::$systemalert = "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was successful.";
                            } else {
                                $this->logger->write("Supplier Controller : downloadErpSuppliers() : The operation to download ERP suppliers was not successful. Please connect to ERP first.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                self::$systemalert = "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                            }
                            
                        } catch (Exception $e) {
                            $this->logger->write("Supplier Controller : downloadErpSuppliers() : The operation to download ERP suppliers was not successful. The error is: " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                        }
                    } else {
                        $this->logger->write("Supplier Controller : downloadErpSuppliers() : The integrated ERP is unknown.", 'r');
                        self::$systemalert = "Sorry. The integrated ERP is unknown.";
                    }
                } else {
                    $this->logger->write("Supplier Controller : downloadErpSuppliers() : We are unable to indentify the currently integrated ERP.", 'r');
                    self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                }
            }
        } else {
            $this->logger->write("Supplier Controller : downloadErpSuppliers() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
    
    /**
     *	@name fetchErpSupplier
     *  @desc download suppliers from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function fetchErpSupplier(){
        $operation = NULL; //tblevents
        $permission = 'SYNCPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Supplier Controller : fetchErpSupplier() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            $id = $this->f3->get('POST.erpdownloadsupplierid');
            $supplier = new suppliers($this->db);
            $supplier->getByID($id);
            $this->logger->write("Product Controller : fetchErpSupplier() : The supplier id is " . $this->f3->get('POST.erpdownloadsupplierid'), 'r');
            
            if ($id) {
                
                if ($supplier->erpsupplierid) {
                    if ($this->platformMode == 'ERP') {
                        $this->logger->write("Supplier Controller : fetchErpSupplier() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                        self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
                    } else {
                        $this->logger->write("Supplier Controller : fetchErpSupplier() : The platform is integrated.", 'r');
                        
                        if ($this->integratedErp) {
                            /**
                             * Check on integrated ERP type
                             */
                            $this->logger->write("Supplier Controller : fetchErpSupplier() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                            
                            if (strtoupper($this->integratedErp) == 'QBO') {
                                $this->logger->write("Supplier Controller : fetchErpSupplier() : The integrated ERP is Quicbooks Online.", 'r');
                                
                                
                                
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
                                        
                                        $suppliers = $dataService->FindbyId('vendor', $supplier->erpsupplierid);
                                        
                                        $error = $dataService->getLastError();
                                        
                                        if ($error) {
                                            $this->logger->write("Supplier Controller : fetchErpSupplier() : The operation to download ERP suppliers was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was successful");
                                            self::$systemalert = "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was successful.";
                                        }
                                        else {
                                            //print_r($suppliers);
                                            if(isset($suppliers)){
                                                if ($suppliers) {
                                                    
                                                    $cust = array(
                                                        'id' => NULL,
                                                        'erpsupplierid' => NULL,
                                                        'erpsuppliercode' => NULL,
                                                        'tin' => NULL,
                                                        'ninbrn' => NULL,
                                                        'PassportNum' => NULL,
                                                        'legalname' => NULL,
                                                        'businessname' => NULL,
                                                        'address' => NULL,
                                                        'mobilephone' => NULL,
                                                        'linephone' => NULL,
                                                        'emailaddress' => NULL,
                                                        'placeofbusiness' => NULL,
                                                        'type' => $this->appsettings['B2BCODE'],
                                                        'citizineship' => NULL,
                                                        'countryCode' => NULL,
                                                        'sector' => NULL,
                                                        'sectorCode' => NULL,
                                                        'datasource' => 'ERP',
                                                        'status' => NULL,
                                                    );
                                                    
                                                    try {
                                                        $this->logger->write("Supplier Controller : fetchErpSupplier() : Supplier Name: " . $suppliers->CompanyName, 'r');
                                                        
                                                        
                                                        $erpsupplierid = $suppliers->Id;
                                                        $erpsuppliercode = $suppliers->Id;
                                                        $legalname = $suppliers->CompanyName;
                                                        $businessname = $suppliers->CompanyName;
                                                        
                                                        if(isset($suppliers->Mobile)){
                                                            $mobilephone = $suppliers->Mobile->FreeFormNumber;
                                                            $cust['mobilephone'] = $mobilephone;
                                                        }
                                                        
                                                        if(isset($suppliers->PrimaryPhone)){
                                                            $linephone = $suppliers->PrimaryPhone->FreeFormNumber;
                                                            $cust['PrimaryPhone'] = $linephone;
                                                        }
                                                        
                                                        
                                                        if(isset($suppliers->PrimaryEmailAddr)){
                                                            $emailaddress = $suppliers->PrimaryEmailAddr->Address;
                                                            $cust['emailaddress'] = $emailaddress;
                                                        }
                                                        
                                                        if(isset($suppliers->BillAddr)){
                                                            $address = $suppliers->BillAddr->Line1;
                                                            $cust['address'] = $address;
                                                        }
                                                        
                                                        $this->logger->write("Supplier Controller : fetchErpSupplier() : Mobile: " . $mobilephone, 'r');
                                                        $this->logger->write("Supplier Controller : fetchErpSupplier() : Email: " . $emailaddress, 'r');
                                                        
                                                        $cust['erpsupplierid'] = $erpsupplierid;
                                                        $cust['erpsuppliercode'] = $erpsuppliercode;
                                                        $cust['legalname'] = $legalname;
                                                        $cust['businessname'] = $businessname;
                                                        
                                                        
                                                        
                                                        if ($suppliers->Active == false) {
                                                            $cust['status'] = $this->appsettings['INACTIVESUPPLIERSTATUSID'];
                                                            $this->logger->write("Supplier Controller : fetchErpSupplier() : The supplier is not ACTIVE.", 'r');
                                                        } else {
                                                            $cust['status'] = $this->appsettings['ACTIVESUPPLIERSTATUSID'];
                                                            $this->logger->write("Supplier Controller : fetchErpSupplier() : The supplier is ACTIVE.", 'r');
                                                        }
                                                        
                                                        
                                                        if ($erpsuppliercode && $legalname) {
                                                            
                                                            $cust['id'] = $id;
                                                            
                                                            
                                                            $cust_status = $this->util->updatesupplier($cust, $this->f3->get('SESSION.id'));
                                                            
                                                            if ($cust_status) {
                                                                $this->logger->write("Supplier Controller : fetchErpSupplier() : The supplier " . $cust['legalname'] . " was updated.", 'r');
                                                            } else {
                                                                $this->logger->write("Supplier Controller : fetchErpSupplier() : The supplier " . $cust['legalname'] . " was NOT updated.", 'r');
                                                            }
                                                            
                                                        } else {
                                                            $this->logger->write("Supplier Controller : fetchErpSupplier() : The supplier has no Id.", 'r');
                                                        }
                                                        
                                                    } catch (Exception $e) {
                                                        $this->logger->write("Supplier Controller : fetchErpSupplier() : There was an error when processing Item " . $suppliers->FullyQualifiedName . ". The error is " . $e->getMessage(), 'r');
                                                    }
                                                }
                                            } else {
                                                $this->logger->write("Supplier Controller : fetchErpSupplier() : The operation to download ERP suppliers did not return records.", 'r');
                                            }
                                        }
                                        
                                        $this->logger->write("Supplier Controller : fetchErpSupplier() : The operation to download ERP suppliers was successful.", 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was successful");
                                        self::$systemalert = "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was successful.";
                                    } else {
                                        $this->logger->write("Supplier Controller : fetchErpSupplier() : The operation to download ERP suppliers was not successful. Please connect to ERP first.", 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                        self::$systemalert = "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                                    }
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Supplier Controller : fetchErpSupplier() : The operation to download ERP suppliers was not successful. The error is: " . $e->getMessage(), 'r');
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was not successful");
                                    self::$systemalert = "The operation to download ERP suppliers by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                                }
                            } else {
                                $this->logger->write("Supplier Controller : fetchErpSupplier() : The integrated ERP is unknown.", 'r');
                                self::$systemalert = "Sorry. The integrated ERP is unknown.";
                            }
                        } else {
                            $this->logger->write("Supplier Controller : fetchErpSupplier() : We are unable to indentify the currently integrated ERP.", 'r');
                            self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                        }
                    }
                } else {
                    $this->logger->write("Supplier Controller : fetchErpSupplier() : The supplier was not created by the ERP.", 'r');
                    self::$systemalert = "Sorry. The supplier was not created by the ERP.";
                    $this->f3->set('systemalert', self::$systemalert);
                    self::index();
                }
                
            } else {
                $this->logger->write("Supplier Controller : fetchErpSupplier() : The supplier was not specified.", 'r');
                self::$systemalert = "Sorry. The supplier was not specified.";
                
                $this->f3->set('systemalert', self::$systemalert);
                self::index();
            }
        } else {
            $this->logger->write("Supplier Controller : fetchErpSupplier() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
}

?>