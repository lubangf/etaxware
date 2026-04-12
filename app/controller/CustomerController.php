<?php
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;
/**
 * @name CustomerController
 * @desc This file is part of the etaxware system. The is the Customer controller class
 * @date 11-05-2020
 * @file CustomerController.php
 * @path ./app/controller/CustomerController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
Class CustomerController extends MainController{
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
        $permission = 'VIEWCUSTOMERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Customer Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $this->f3->set('pagetitle','Customers');
            $this->f3->set('pagecontent','Customer.htm');
            $this->f3->set('pagescripts','CustomerFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Customer Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    
    
    /**
     *	@name view
     *  @desc view Customer
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = '', $tab = '', $tabpane = '') {
        $operation = NULL; //tblevents
        $permission = 'VIEWCUSTOMERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Customer Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Customer Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Customer Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $status = new statuses($this->db);
            $customerstatuscodes = $status->getByGroupID(1026);
            $this->f3->set('customerstatuscodes', $customerstatuscodes);
            
            $country = new countries($this->db);
            $countries = $country->all();
            $this->f3->set('countries', $countries);
            
            $sector = new sectors($this->db);
            $sectors = $sector->all();
            $this->f3->set('sectors', $sectors);
            
            $buyertype = new buyertypes($this->db);
            $buyertypes = $buyertype->all();
            $this->f3->set('buyertypes', $buyertypes);
            
            $deliveryterm = new deliveryterms($this->db);
            $deliveryterms = $deliveryterm->all();
            $this->f3->set('deliveryterms', $deliveryterms);
            
            if (is_string($tab) && is_string($tabpane)){
                $this->logger->write("Customer Controller : view() : The value of v_id is " . $v_id, 'r');
                $this->logger->write("Customer Controller : view() : The value of tab is " . $tab, 'r');
                $this->logger->write("Customer Controller : view() : The value of tabpane " . $tabpane, 'r');
            } 
            
            if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
                $id = trim($this->f3->get('PARAMS[id]'));
                $this->logger->write("Customer Controller : view() : The is a GET call & id to view is " . $id, 'r');
                
                $customer = new customers($this->db);
                $customer->getByID($id);
                
                
                $this->f3->set('customer', $customer);
                
                if (is_string($tab) && is_string($tabpane)){//this check is necessary for cases where the GET request is system initiated. The params sent to the view functions are non-string.
                    $this->f3->set('currenttab', $tab);
                    $this->f3->set('currenttabpane', $tabpane);
                } else {
                    $this->f3->set('currenttab', 'tab_general');
                    $this->f3->set('currenttabpane', 'tab_1');
                    $this->f3->set('path', '../' . $this->path);
                }
                
                $this->f3->set('pagetitle','Edit Customer | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path); //overide the main solution path
            } elseif (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id')))) {//Open EDIT mode
                $id = trim($this->f3->get('POST.id'));
                $this->logger->write("Customer Controller : view() : This is a POST call & the id to view is " . $id, 'r');
                
                $customer = new customers($this->db);
                $customer->getByID($id);
                
                
                
                $this->f3->set('customer', $customer);
                
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
                
                $this->f3->set('pagetitle','Edit Customer | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
            } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
                $id = trim($v_id);
                $this->logger->write("Customer Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
                
                $customer = new customers($this->db);
                $customer->getByID($id);
                
                
                
                $this->f3->set('customer', $customer);
                
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
                
                $this->f3->set('pagetitle','Edit Customer | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
                
                $this->f3->set('pagecontent','EditCustomer.htm');
                $this->f3->set('pagescripts','EditCustomerFooter.htm');
                echo \Template::instance()->render('Layout.htm');
                exit(); //exit the function so no extra code executes
            } else {
                $this->logger->write("Customer Controller : view() : No id was selected", 'r');
                $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page
                exit();
            }
            
            $this->logger->write("Customer Controller : view() : The currenttab has been set to " . $this->f3->get('currenttab'), 'r');
            $this->logger->write("Customer Controller : view() : The currenttabpane has been set to " . $this->f3->get('currenttabpane'), 'r');
            
            $this->f3->set('pagecontent','EditCustomer.htm');
            $this->f3->set('pagescripts','EditCustomerFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Customer Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name add
     *  @desc add Customer
     *	@return NULL
     *	@param NULL
     **/
    function add() {
        $operation = NULL; //tblevents
        $permission = 'CREATECUSTOMERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Customer Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {           

            
            $status = new statuses($this->db);
            $customerstatuscodes = $status->getByGroupID(1026);
            $this->f3->set('customerstatuscodes', $customerstatuscodes);
            
            $country = new countries($this->db);
            $countries = $country->all();
            $this->f3->set('countries', $countries);
            
            $sector = new sectors($this->db);
            $sectors = $sector->all();
            $this->f3->set('sectors', $sectors);
            
            $buyertype = new buyertypes($this->db);
            $buyertypes = $buyertype->all();
            $this->f3->set('buyertypes', $buyertypes);
            
            $deliveryterm = new deliveryterms($this->db);
            $deliveryterms = $deliveryterm->all();
            $this->f3->set('deliveryterms', $deliveryterms);
            
            $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
            $this->f3->set('currenttabpane', 'tab_1');
            
            
            $customer = array(
                "id" => NULL,
                "legalname" => ''
            );
            $this->f3->set('customer', $customer);
            
            $this->f3->set('pagetitle','Create Customer');
            
            $this->f3->set('pagecontent','EditCustomer.htm');
            $this->f3->set('pagescripts','EditCustomerFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Customer Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    /**
     * edit customer
     *
     * @name edit
     * @return NULL
     * @param
     *            NULL
     */
    function edit(){
        $customer = new customers($this->db);
        $sector = new sectors($this->db);
        $country = new countries($this->db);
        $currenttab = trim($this->f3->get('POST.currenttab'));
        $currenttabpane = trim($this->f3->get('POST.currenttabpane'));
        $id = 0;
        
        if (trim($this->f3->get('POST.customerid')) !== '' || ! empty(trim($this->f3->get('POST.customerid')))) { // EDIT Operation
            $operation = NULL; // tblevents
            $permission = 'EDITCUSTOMERS'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications
            
            $this->logger->write("Customer Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $id = trim($this->f3->get('POST.customerid'));
                $this->logger->write("Customer Controller : edit() : The id to be edited is " . $id, 'r');
                $customer->getByID($id);
                
                if ($currenttab == 'tab_general') {
                    // @TODO check the params for empty/null values
                    
                    if (trim($this->f3->get('POST.customertype')) !== '' || ! empty(trim($this->f3->get('POST.customertype')))) {
                        $this->f3->set('POST.type', $this->f3->get('POST.customertype'));
                    } else {
                        $this->f3->set('POST.type', $customer->type);
                    }
                    
                    
                    if (trim($this->f3->get('POST.customerstatus')) !== '' || ! empty(trim($this->f3->get('POST.customerstatus')))) {
                        $this->f3->set('POST.status', $this->f3->get('POST.customerstatus'));
                    } else {
                        $this->f3->set('POST.status', $customer->status);
                    }
                    
                    if (trim($this->f3->get('POST.customercountrycode')) !== '' || ! empty(trim($this->f3->get('POST.customercountrycode')))) {
                        $this->f3->set('POST.countryCode', $this->f3->get('POST.customercountrycode'));
                        $country->getByCode($this->f3->get('POST.customercountrycode'));
                        $this->f3->set('POST.citizineship', $country->name);
                    } else {
                        $this->f3->set('POST.countryCode', $customer->countryCode);
                    }
                    
                    if (trim($this->f3->get('POST.customersector')) !== '' || ! empty(trim($this->f3->get('POST.customersector')))) {
                        $this->f3->set('POST.sectorCode', $this->f3->get('POST.customersector'));
                        $sector->getByCode($this->f3->get('POST.customersector'));
                        $this->f3->set('POST.sector', $sector->name);
                    } else {
                        $this->f3->set('POST.sectorCode', $customer->sectorCode);
                    }
                    
                    
                    if (trim($this->f3->get('POST.customertin')) !== '' || ! empty(trim($this->f3->get('POST.customertin')))) {
                        $this->f3->set('POST.tin', $this->f3->get('POST.customertin'));
                    } else {
                        $this->f3->set('POST.tin', $customer->tin);
                    }
                    
                    if (trim($this->f3->get('POST.customerninbrn')) !== '' || ! empty(trim($this->f3->get('POST.customerninbrn')))) {
                        $this->f3->set('POST.ninbrn', $this->f3->get('POST.customerninbrn'));
                    } else {
                        $this->f3->set('POST.ninbrn', $customer->ninbrn);
                    }
                    
                    if (trim($this->f3->get('POST.customerlegalname')) !== '' || ! empty(trim($this->f3->get('POST.customerlegalname')))) {
                        $this->f3->set('POST.legalname', $this->f3->get('POST.customerlegalname'));
                    } else {
                        $this->f3->set('POST.legalname', $customer->legalname);
                    }
                    
                    if (trim($this->f3->get('POST.customerbusinessname')) !== '' || ! empty(trim($this->f3->get('POST.customerbusinessname')))) {
                        $this->f3->set('POST.businessname', $this->f3->get('POST.customerbusinessname'));
                    } else {
                        $this->f3->set('POST.businessname', $customer->businessname);
                    }
                    
                    if (trim($this->f3->get('POST.customeraddress')) !== '' || ! empty(trim($this->f3->get('POST.customeraddress')))) {
                        $this->f3->set('POST.address', $this->f3->get('POST.customeraddress'));
                    } else {
                        $this->f3->set('POST.address', $customer->address);
                    }
                    
                    if (trim($this->f3->get('POST.customermobilephone')) !== '' || ! empty(trim($this->f3->get('POST.customermobilephone')))) {
                        $this->f3->set('POST.mobilephone', $this->f3->get('POST.customermobilephone'));
                    } else {
                        $this->f3->set('POST.mobilephone', $customer->mobilephone);
                    }
                    
                    if (trim($this->f3->get('POST.customerlinephone')) !== '' || ! empty(trim($this->f3->get('POST.customerlinephone')))) {
                        $this->f3->set('POST.linephone', $this->f3->get('POST.customerlinephone'));
                    } else {
                        $this->f3->set('POST.linephone', $customer->linephone);
                    }
                    
                    if (trim($this->f3->get('POST.customeremailaddress')) !== '' || ! empty(trim($this->f3->get('POST.customeremailaddress')))) {
                        $this->f3->set('POST.emailaddress', $this->f3->get('POST.customeremailaddress'));
                    } else {
                        $this->f3->set('POST.emailaddress', $customer->emailaddress);
                    }
                    
                    if (trim($this->f3->get('POST.customerplaceofbusiness')) !== '' || ! empty(trim($this->f3->get('POST.customerplaceofbusiness')))) {
                        $this->f3->set('POST.placeofbusiness', $this->f3->get('POST.customerplaceofbusiness'));
                    } else {
                        $this->f3->set('POST.placeofbusiness', $customer->placeofbusiness);
                    }
                    
                    if (trim($this->f3->get('POST.customerpassportnum')) !== '' || ! empty(trim($this->f3->get('POST.customerpassportnum')))) {
                        $this->f3->set('POST.PassportNum', $this->f3->get('POST.customerpassportnum'));
                    } else {
                        $this->f3->set('POST.PassportNum', $customer->PassportNum);
                    }
                    
                    $this->logger->write("Customer Controller : edit() : nonResidentFlag = " . $this->f3->get('POST.nonResidentFlag'), 'r');
                    
                    if ($this->f3->get('POST.nonResidentFlag') == 'on') {
                        $this->logger->write("Customer Controller : edit() : Here 1", 'r');
                        $this->f3->set('POST.nonResidentFlag', true);
                    }elseif ($this->f3->get('POST.nonResidentFlag') == '1'){
                        $this->logger->write("Customer Controller : edit() : Here 2", 'r');
                        $this->f3->set('POST.nonResidentFlag', true);
                    }else {
                        $this->logger->write("Customer Controller : edit() : Here 3", 'r');
                        //$this->f3->set('POST.nonResidentFlag', $customer->nonResidentFlag);
                        $this->f3->set('POST.nonResidentFlag', false);
                    }
                    
                    if (trim($this->f3->get('POST.vatProjectId')) !== '' || ! empty(trim($this->f3->get('POST.vatProjectId')))) {
                        $this->f3->set('POST.vatProjectId', $this->f3->get('POST.vatProjectId'));
                    } else {
                        //$this->f3->set('POST.vatProjectId', $customer->vatProjectId);
                        $this->f3->set('POST.vatProjectId', null);
                    }
                    
                    if (trim($this->f3->get('POST.vatProjectName')) !== '' || ! empty(trim($this->f3->get('POST.vatProjectName')))) {
                        $this->f3->set('POST.vatProjectName', $this->f3->get('POST.vatProjectName'));
                    } else {
                        //$this->f3->set('POST.vatProjectName', $customer->vatProjectName);
                        $this->f3->set('POST.vatProjectName', null);
                    }
                    
                    if (trim($this->f3->get('POST.deliveryTermsCode')) !== '' || ! empty(trim($this->f3->get('POST.deliveryTermsCode')))) {
                        $this->f3->set('POST.deliveryTermsCode', $this->f3->get('POST.deliveryTermsCode'));
                    } else {
                        //$this->f3->set('POST.deliveryTermsCode', $customer->deliveryTermsCode);
                        $this->f3->set('POST.deliveryTermsCode', null);
                    }
                }
                
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                try {
                    $customer->edit($id);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The customer - " . $customer->id . " - " . $customer->legalname . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The customer " . $customer->legalname . " has been edited";
                    $this->logger->write("Customer Controller : edit() : The customer " . $customer->legalname . " has been edited", 'r');
                } catch (Exception $e) {
                    $this->logger->write("Customer Controller : edit() : The operation to edit customer - " . $customer->id . " - " . $customer->legalname . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit customer - " . $customer->id . " - " . $customer->legalname . " was not successfull");
                    self::$systemalert = "The operation to edit customer " . $customer->legalname . " was not successful";
                }
            } else {
                $this->logger->write("Customer Controller : edit() : The user is not allowed to perform this function", 'r');
                $this->f3->reroute('/forbidden');
            }
        } else { // ADD Operation: mainly handles the GENERAL parameters, as the rest of the parameters will be added using the EDIT option
            $operation = NULL; // tblevents
            $permission = 'CREATECUSTOMERS'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotificationsPOST.customerlegalname
            
            $this->logger->write("Customer Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $this->logger->write("Customer Controller : edit() : Adding of customer started.", 'r');
                
                
                
                $this->f3->set('POST.type', $this->f3->get('POST.customertype'));
                $this->f3->set('POST.status', $this->f3->get('POST.customerstatus'));
                
                $this->f3->set('POST.countryCode', $this->f3->get('POST.customercountrycode'));
                $country->getByCode($this->f3->get('POST.customercountrycode'));
                $this->f3->set('POST.citizineship', $country->name);
                
                $this->f3->set('POST.sectorCode', $this->f3->get('POST.customersector'));
                $sector->getByCode($this->f3->get('POST.customersector'));
                $this->f3->set('POST.sector', $sector->name);
                
                $this->f3->set('POST.tin', $this->f3->get('POST.customertin'));
                $this->f3->set('POST.ninbrn', $this->f3->get('POST.customerninbrn'));
                $this->f3->set('POST.legalname', $this->f3->get('POST.customerlegalname'));
                $this->f3->set('POST.businessname', $this->f3->get('POST.customerbusinessname'));
                $this->f3->set('POST.address', $this->f3->get('POST.customeraddress'));
                $this->f3->set('POST.mobilephone', $this->f3->get('POST.customermobilephone'));
                $this->f3->set('POST.linephone', $this->f3->get('POST.customerlinephone'));
                $this->f3->set('POST.emailaddress', $this->f3->get('POST.customeremailaddress'));
                $this->f3->set('POST.placeofbusiness', $this->f3->get('POST.customerplaceofbusiness'));
                $this->f3->set('POST.PassportNum', $this->f3->get('POST.customerpassportnum'));
                $this->f3->set('POST.nonResidentFlag', $this->f3->get('POST.nonResidentFlag'));
                $this->f3->set('POST.vatProjectId', $this->f3->get('POST.vatProjectId'));
                $this->f3->set('POST.vatProjectName', $this->f3->get('POST.vatProjectName'));
                $this->f3->set('POST.deliveryTermsCode', $this->f3->get('POST.deliveryTermsCode'));
                
                
                $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                // @TODO check the params for empty/null values
                if (trim($this->f3->get('POST.customerlegalname')) !== '' || ! empty(trim($this->f3->get('POST.customerlegalname')))) {
                    try {
                        // Proceed & create
                        $customer->add();
                        // $this->logger->write("Customer Controller : edit() : A new customer has been added", 'r');
                        try {
                            // retrieve the most recently inserted customer
                            // @TODO place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                            $data = array();
                            $r = $this->db->exec(array(
                                'SELECT MAX(id) "id" FROM tblcustomers WHERE insertedby = ' . $this->f3->get('SESSION.id')
                            ));
                            foreach ($r as $obj) {
                                $data[] = $obj;
                            }
                            
                            // $this->logger->write("Customer Controller : edit() : The customer " . $data[0]['id'] . " has been added", 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The customer id " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                            self::$systemalert = "The customer has been added";
                            $id = $data[0]['id'];
                            $customer->getByID($id);
                        } catch (Exception $e) {
                            $this->logger->write("Customer Controller : edit() : The operation to retrieve the most recently added customer was not successful. The error messages is " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added customer was not successful");
                            self::$systemalert = "The operation to retrieve the most recently added customer was not successful";
                        }
                    } catch (Exception $e) {
                        $this->logger->write("Customer Controller : edit() : The operation to add a customer was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a customer was not successful");
                        self::$systemalert = "The operation to add a customer was not successful. An internal error occured, or you are trying to add a duplicate code";
                        $this->f3->set('systemalert', self::$systemalert);
                        self::add();
                        exit();
                    }
                } else {
                    $this->logger->write("Customer Controller : edit() : The user is not allowed to perform this function", 'r');
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
        $customerstatuscodes = $status->getByGroupID(1026);
        $this->f3->set('customerstatuscodes', $customerstatuscodes);
        
        $buyertype = new buyertypes($this->db);
        $buyertypes = $buyertype->all();
        $this->f3->set('buyertypes', $buyertypes);
        
        $deliveryterm = new deliveryterms($this->db);
        $deliveryterms = $deliveryterm->all();
        $this->f3->set('deliveryterms', $deliveryterms);
        
        $this->f3->set('customer', $customer);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);
        
        $this->f3->set('systemalert', self::$systemalert);
        
        $this->f3->set('pagetitle', 'Edit Customer | ' . $id);
        $this->f3->set('pagecontent', 'EditCustomer.htm');
        $this->f3->set('pagescripts', 'EditCustomerFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    /**
     *	@name list
     *  @desc List customers
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function list(){
        $operation = NULL; //tblevents
        $permission = 'VIEWCUSTOMERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Customer Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Customer Controller : list() : Processing list of customers started", 'r');
            
            $name = trim($this->f3->get('POST.name'));
            
            $this->logger->write("Customer Controller : list() : The name param is : " . $name, 'r');
            
            if ($name !== '' || !empty($name)) {
                $subquery = " '%" . $name . "%' ";
                
                $sql = 'SELECT  p.id "ID",
                        p.legalname "Name",
                        p.erpcustomerid "ERP ID",
                        p.erpcustomercode "ERP Code",
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
                    FROM tblcustomers p
                    LEFT JOIN tblbuyertypes bt ON bt.code = p.type
                    LEFT JOIN tblusers s ON p.modifiedby = s.id
                    WHERE p.legalname LIKE ' . $subquery . '
                    ORDER By p.id DESC';
            } else {
                $sql = 'SELECT  p.id "ID",
                        p.legalname "Name",
                        p.erpcustomerid "ERP ID",
                        p.erpcustomercode "ERP Code",
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
                    FROM tblcustomers p
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
                $this->logger->write("Customer Controller : list() : The operation to list the customers was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Customer Controller : index() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
    
    
    /**
     *	@name downloadErpCustomers
     *  @desc download customers from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function downloadErpCustomers(){
        $operation = NULL; //tblevents
        $permission = 'SYNCPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Customer Controller : downloadErpCustomers() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            
            $startDate = $this->f3->get('POST.downloaderpcustomersstartdate');
            $endDate = $this->f3->get('POST.downloaderpcustomersenddate');
            $customerNo = $this->f3->get('POST.downloaderpcustomernumber');
            
            $this->logger->write("Customer Controller : downloadErpCustomers() : startDate: " . $startDate, 'r');
            $this->logger->write("Customer Controller : downloadErpCustomers() : endDate: " . $endDate, 'r');
            $this->logger->write("Customer Controller : downloadErpCustomers() : customerNo: " . $customerNo, 'r');
            
            $startDate = empty($startDate)? date('Y-m-d') : date('Y-m-d', strtotime($startDate));
            $endDate = empty($endDate)? date('Y-m-d') : date('Y-m-d', strtotime($endDate));
            $customerNo = empty($customerNo)? 'NULL' : $customerNo;
            
            
            
            if ($this->platformMode == 'ERP') {
                $this->logger->write("Customer Controller : downloadErpCustomers() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
            } else {
                $this->logger->write("Customer Controller : downloadErpCustomers() : The platform is integrated.", 'r');

                if ($this->integratedErp) {
                    /**
                     * Check on integrated ERP type
                     */
                    $this->logger->write("Customer Controller : downloadErpCustomers() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                    
                    if (strtoupper($this->integratedErp) == 'QBO') {
                        $this->logger->write("Customer Controller : downloadErpCustomers() : The integrated ERP is Quicbooks Online.", 'r');
                        
                        
                        $qry = 'SELECT * FROM Customer';
                        $qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "' And Metadata.LastUpdatedTime <= '" . $endDate . "'";
                        $this->logger->write("Customer Controller : downloadErpCustomers() : The query is: " . $qry, 'r');
                        
                        try {
                            //if ($this->appsettings['QBACCESSTOKEN'] !== null) {
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
                                
                                $customers = $dataService->Query($qry);
                                
                                $error = $dataService->getLastError();
                                
                                if ($error) {
                                    $this->logger->write("Customer Controller : downloadErpCustomers() : The operation to download ERP customers was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was successful");
                                    self::$systemalert = "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was successful.";
                                }
                                else {
                                    //print_r($customers);
                                    if(isset($customers)){
                                        if ($customers) {
                                            $customer = new customers($this->db);
                                            
                                            $cust = array(
                                                'id' => NULL,
                                                'erpcustomerid' => NULL,
                                                'erpcustomercode' => NULL,
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
                                                'type' => NULL,
                                                'citizineship' => NULL,
                                                'countryCode' => NULL,
                                                'sector' => NULL,
                                                'sectorCode' => NULL,
                                                'datasource' => 'ERP',
                                                'status' => $this->appsettings['ACTIVECUSTOMERSTATUSID'],
                                            );
                                            
                                            foreach($customers as $elem){
                                                
                                                try {
                                                    $this->logger->write("Customer Controller : downloadErpCustomers() : Customer Name: " . $elem->FullyQualifiedName, 'r');
                                                    
                                                    
                                                    $erpcustomerid = $elem->Id;
                                                    $erpcustomercode = $elem->Id;
                                                    $legalname = $elem->FullyQualifiedName;
                                                    $businessname = $elem->FullyQualifiedName;
                                                    
                                                    if(isset($elem->PrimaryPhone)){
                                                        $mobilephone = $elem->PrimaryPhone->FreeFormNumber;
                                                        $cust['mobilephone'] = $mobilephone;
                                                    }
                                                    
                                                    if(isset($elem->PrimaryEmailAddr)){
                                                        $emailaddress = $elem->PrimaryEmailAddr->Address;
                                                        $cust['emailaddress'] = $emailaddress;
                                                    }
                                                    
                                                    if(isset($elem->BillAddr)){
                                                        $address = $elem->BillAddr->Line1;
                                                        $cust['address'] = $address;
                                                    }
                                                    
                                                    $this->logger->write("Customer Controller : downloadErpCustomers() : Mobile: " . $mobilephone, 'r');
                                                    $this->logger->write("Customer Controller : downloadErpCustomers() : Email: " . $emailaddress, 'r');
                                                    
                                                    $cust['erpcustomerid'] = $erpcustomerid;
                                                    $cust['erpcustomercode'] = $erpcustomercode;
                                                    $cust['legalname'] = $legalname;
                                                    $cust['businessname'] = $businessname;
                                                    
                                                    if ($elem->Active == true) {
                                                        if ($erpcustomercode && $legalname) {
                                                            $customer->getByCode($erpcustomercode);
                                                            
                                                            if ($customer->dry()) {
                                                                $this->logger->write("Customer Controller : downloadErpCustomers() : The customer does not exist", 'r');
                                                                $cust_status = $this->util->createcustomer($cust, $this->f3->get('SESSION.id'));
                                                                
                                                                if ($cust_status) {
                                                                    $this->logger->write("Customer Controller : downloadErpCustomers() : The customer " . $cust['legalname'] . " was created.", 'r');
                                                                } else {
                                                                    $this->logger->write("Customer Controller : downloadErpCustomers() : The customer " . $cust['legalname'] . " was NOT created.", 'r');
                                                                }
                                                            } else {
                                                                $this->logger->write("Customer Controller : downloadErpCustomers() : The customer exists", 'r');
                                                                $cust['id'] = $customer->id;
                                                                
                                                                $cust_status = $this->util->updatecustomer($cust, $this->f3->get('SESSION.id'));
                                                                
                                                                if ($cust_status) {
                                                                    $this->logger->write("Customer Controller : downloadErpCustomers() : The customer " . $cust['legalname'] . " was updated.", 'r');
                                                                } else {
                                                                    $this->logger->write("Customer Controller : downloadErpCustomers() : The customer " . $cust['legalname'] . " was NOT updated.", 'r');
                                                                }
                                                                
                                                            }
                                                        } else {
                                                            $this->logger->write("Customer Controller : downloadErpCustomers() : The customer has no Id.", 'r');
                                                        }
                                                    } else {
                                                        $this->logger->write("Customer Controller : downloadErpCustomers() : The customer is not ACTIVE.", 'r');
                                                    }
                                                    
                                                } catch (Exception $e) {
                                                    $this->logger->write("Customer Controller : downloadErpCustomers() : There was an error when processing Item " . $elem->FullyQualifiedName . ". The error is " . $e->getMessage(), 'r');
                                                }
                                            }
                                        }
                                    } else {
                                        $this->logger->write("Customer Controller : downloadErpCustomers() : The operation to download ERP customers did not return records.", 'r');
                                    }
                                }
                                
                                $this->logger->write("Customer Controller : downloadErpCustomers() : The operation to download ERP customers was successful.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was successful");
                                self::$systemalert = "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was successful.";
                            } else {
                                $this->logger->write("Customer Controller : downloadErpCustomers() : The operation to download ERP customers was not successful. Please connect to ERP first.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                self::$systemalert = "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                            }
                            
                        } catch (Exception $e) {
                            $this->logger->write("Customer Controller : downloadErpCustomers() : The operation to download ERP customers was not successful. The error is: " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                        }
                    } else {
                        $this->logger->write("Customer Controller : downloadErpCustomers() : The integrated ERP is unknown.", 'r');
                        self::$systemalert = "Sorry. The integrated ERP is unknown.";
                    }
                } else {
                    $this->logger->write("Customer Controller : downloadErpCustomers() : We are unable to indentify the currently integrated ERP.", 'r');
                    self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                }
            }
        } else {
            $this->logger->write("Customer Controller : downloadErpCustomers() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
    
    /**
     *	@name fetchErpCustomer
     *  @desc download customers from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function fetchErpCustomer(){
        $operation = NULL; //tblevents
        $permission = 'SYNCPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Customer Controller : fetchErpCustomer() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            $id = $this->f3->get('POST.erpdownloadcustomerid');
            $customer = new customers($this->db);
            $customer->getByID($id);
            $this->logger->write("Product Controller : fetchErpCustomer() : The customer id is " . $this->f3->get('POST.erpdownloadcustomerid'), 'r');
            
            if ($id) {
                
                if ($customer->erpcustomerid) {
                    if ($this->platformMode == 'ERP') {
                        $this->logger->write("Customer Controller : fetchErpCustomer() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                        self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
                    } else {
                        $this->logger->write("Customer Controller : fetchErpCustomer() : The platform is integrated.", 'r');
                        
                        if ($this->integratedErp) {
                            /**
                             * Check on integrated ERP type
                             */
                            $this->logger->write("Customer Controller : fetchErpCustomer() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                            
                            if (strtoupper($this->integratedErp) == 'QBO') {
                                $this->logger->write("Customer Controller : fetchErpCustomer() : The integrated ERP is Quicbooks Online.", 'r');
                                
                                
                                
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
                                        
                                        $customers = $dataService->FindbyId('customer', $customer->erpcustomerid);
                                        
                                        $error = $dataService->getLastError();
                                        
                                        if ($error) {
                                            $this->logger->write("Customer Controller : fetchErpCustomer() : The operation to download ERP customers was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was successful");
                                            self::$systemalert = "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was successful.";
                                        }
                                        else {
                                            //print_r($customers);
                                            if(isset($customers)){
                                                if ($customers) {
                                                    
                                                    $cust = array(
                                                        'id' => NULL,
                                                        'erpcustomerid' => NULL,
                                                        'erpcustomercode' => NULL,
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
                                                        'type' => NULL,
                                                        'citizineship' => NULL,
                                                        'countryCode' => NULL,
                                                        'sector' => NULL,
                                                        'sectorCode' => NULL,
                                                        'datasource' => 'ERP',
                                                        'status' => NULL,
                                                    );
                                                    
                                                    try {
                                                        $this->logger->write("Customer Controller : fetchErpCustomer() : Customer Name: " . $customers->FullyQualifiedName, 'r');
                                                        
                                                        
                                                        $erpcustomerid = $customers->Id;
                                                        $erpcustomercode = $customers->Id;
                                                        $legalname = $customers->FullyQualifiedName;
                                                        $businessname = $customers->FullyQualifiedName;
                                                        
                                                        if(isset($customers->PrimaryPhone)){
                                                            $mobilephone = $customers->PrimaryPhone->FreeFormNumber;
                                                            $cust['mobilephone'] = $mobilephone;
                                                        }
                                                        
                                                        if(isset($customers->PrimaryEmailAddr)){
                                                            $emailaddress = $customers->PrimaryEmailAddr->Address;
                                                            $cust['emailaddress'] = $emailaddress;
                                                        }
                                                        
                                                        if(isset($customers->BillAddr)){
                                                            $address = $customers->BillAddr->Line1;
                                                            $cust['address'] = $address;
                                                        }
                                                        
                                                        $this->logger->write("Customer Controller : fetchErpCustomer() : Mobile: " . $mobilephone, 'r');
                                                        $this->logger->write("Customer Controller : fetchErpCustomer() : Email: " . $emailaddress, 'r');
                                                        
                                                        $cust['erpcustomerid'] = $erpcustomerid;
                                                        $cust['erpcustomercode'] = $erpcustomercode;
                                                        $cust['legalname'] = $legalname;
                                                        $cust['businessname'] = $businessname;
                                                        
                                                        
                                                        
                                                        if ($customers->Active == false) {
                                                            $cust['status'] = $this->appsettings['INACTIVECUSTOMERSTATUSID'];
                                                            $this->logger->write("Customer Controller : fetchErpCustomer() : The customer is not ACTIVE.", 'r');
                                                        } else {
                                                            $cust['status'] = $this->appsettings['ACTIVECUSTOMERSTATUSID'];
                                                            $this->logger->write("Customer Controller : fetchErpCustomer() : The customer is ACTIVE.", 'r');
                                                        }
                                                        
                                                        
                                                        if ($erpcustomercode && $legalname) {
                                                            
                                                            $cust['id'] = $id;
                                                            
                                                            
                                                            $cust_status = $this->util->updatecustomer($cust, $this->f3->get('SESSION.id'));
                                                            
                                                            if ($cust_status) {
                                                                $this->logger->write("Customer Controller : fetchErpCustomer() : The customer " . $cust['legalname'] . " was updated.", 'r');
                                                            } else {
                                                                $this->logger->write("Customer Controller : fetchErpCustomer() : The customer " . $cust['legalname'] . " was NOT updated.", 'r');
                                                            }
                                                            
                                                        } else {
                                                            $this->logger->write("Customer Controller : fetchErpCustomer() : The customer has no Id.", 'r');
                                                        }
                                                        
                                                    } catch (Exception $e) {
                                                        $this->logger->write("Customer Controller : fetchErpCustomer() : There was an error when processing Item " . $customers->FullyQualifiedName . ". The error is " . $e->getMessage(), 'r');
                                                    }
                                                }
                                            } else {
                                                $this->logger->write("Customer Controller : fetchErpCustomer() : The operation to download ERP customers did not return records.", 'r');
                                            }
                                        }
                                        
                                        $this->logger->write("Customer Controller : fetchErpCustomer() : The operation to download ERP customers was successful.", 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was successful");
                                        self::$systemalert = "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was successful.";
                                    } else {
                                        $this->logger->write("Customer Controller : fetchErpCustomer() : The operation to download ERP customers was not successful. Please connect to ERP first.", 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                        self::$systemalert = "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                                    }
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Customer Controller : fetchErpCustomer() : The operation to download ERP customers was not successful. The error is: " . $e->getMessage(), 'r');
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was not successful");
                                    self::$systemalert = "The operation to download ERP customers by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                                }
                            } else {
                                $this->logger->write("Customer Controller : fetchErpCustomer() : The integrated ERP is unknown.", 'r');
                                self::$systemalert = "Sorry. The integrated ERP is unknown.";
                            }
                        } else {
                            $this->logger->write("Customer Controller : fetchErpCustomer() : We are unable to indentify the currently integrated ERP.", 'r');
                            self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                        }
                    }
                } else {
                    $this->logger->write("Customer Controller : fetchErpCustomer() : The customer was not created by the ERP.", 'r');
                    self::$systemalert = "Sorry. The customer was not created by the ERP.";
                    
                    $this->f3->set('systemalert', self::$systemalert);
                    self::index();
                }
                
            } else {
                $this->logger->write("Customer Controller : fetchErpCustomer() : The customer was not specified.", 'r');
                self::$systemalert = "Sorry. The customer was not specified.";
                
                $this->f3->set('systemalert', self::$systemalert);
                self::index();
            }
        } else {
            $this->logger->write("Customer Controller : fetchErpCustomer() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name checkcustomer
     *  @desc Check whether the taxpayer is tax exempt/Deemed
     *	@return NULL
     *	@param NULL
     **/
    function checkcustomer(){
        $operation = NULL; //tblevents
        $permission = 'VIEWCUSTOMERS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.checkcustomerid');
        $commodity = $this->f3->get('POST.checkcustomercommoditycode');
        
        $customer = new customers($this->db);
        $customer->getByID($id);
        $this->logger->write("Customer Controller : checkcustomer() : The customer id is " . $id, 'r');
        $this->logger->write("Customer Controller : checkcustomer() : The commodity is " . $commodity, 'r');
        
        $this->logger->write("Customer Controller : checkcustomer() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $data = $this->util->checktaxpayertype($this->f3->get('SESSION.id'), $customer->tin, $commodity);//will return JSON.
            //var_dump($data);
            $data = json_decode($data, true);
            
            if (isset($data['returnCode'])){
                $this->logger->write("Customer Controller : checkcustomer() : The operation to check the customer was not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to check the customer by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to check the customer was not successful. The error message is " . $data['returnMessage'];
            } else {
                
                
                if ($data) {
                    $taxpayerType = !isset($data['taxpayerType'])? NULL : $data['taxpayerType'];
                    //$exemptType = !isset($data['exemptType'])? NULL : $data['exemptType'];
                    //$commodityCategoryCode = '';
                    $commodityCategoryTaxpayerType = '';
                    
                    if (isset($data['commodityCategory'])){
                        if ($data['commodityCategory']) {
                            
                            foreach($data['commodityCategory'] as $elem){
                                //$commodityCategoryCode = !isset($elem['commodityCategoryCode'])? NULL : $elem['commodityCategoryCode'];
                                $commodityCategoryTaxpayerType = !isset($elem['commodityCategoryTaxpayerType'])? NULL : $elem['commodityCategoryTaxpayerType'];
                            }
                        }
                    }
                    
                    $fb = '';
                    $cc = new commoditycategories($this->db);
                    $tptype = new taxpayertypes($this->db);
                    
                    
                    if ($taxpayerType) {
                        $tptype->getByCode($taxpayerType);
                        $fb = $fb . 'The general tax payer type is: ' . $tptype->name . ', ';
                    }
                    
                    if ($commodityCategoryTaxpayerType) {
                        $cc->getByCode($commodity);
                        $tptype->getByCode($commodityCategoryTaxpayerType);
                        $fb = $fb . 'The tax payer type for the commodity: ' . $cc->commodityname . ' is: ' . $tptype->name;
                    }
                    
                    $this->logger->write("Customer Controller : checkcustomer() : The operation to check the customer was succesful", 'r');
                    self::$systemalert = $fb;
                    
                    
                } else {
                    $this->logger->write("Customer Controller : checkcustomer() : The API did not return anything", 'r');
                    self::$systemalert = "The operation to check the customer was not successful";
                }
            }
            $this->f3->set('systemalert', self::$systemalert);
            
            $this->f3->set('pagetitle','Customers');
            $this->f3->set('pagecontent','Customer.htm');
            $this->f3->set('pagescripts','CustomerFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Customer Controller : checkcustomer() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
}

?>