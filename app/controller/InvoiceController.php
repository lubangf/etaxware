<?php
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\SalesReceipt;


/**
 * @name InvoiceController
 * @desc This file is part of the etaxware system. The is the Invoice controller class
 * @date 11-05-2020
 * @file InvoiceController.php
 * @path ./app/controller/InvoiceController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
Class InvoiceController extends MainController{
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
        $permission = 'VIEWINVOICES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Invoice Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $invoicekind = new invoicekinds($this->db);
            $invoicekinds = $invoicekind->all();
            $this->f3->set('invoicekinds', $invoicekinds);
            
            $erpdoctype = new erpdoctypes($this->db);
            $erpdoctypes = $erpdoctype->getByCat($this->appsettings['INVOICEERPDOCCAT']);
            $this->f3->set('erpdoctypes', $erpdoctypes);
            
            $this->f3->set('pagetitle','Invoices');
            $this->f3->set('pagecontent','Invoice.htm');
            $this->f3->set('pagescripts','InvoiceFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Invoice Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    /**
     *	@name view
     *  @desc view Invoice
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = '', $tab = '', $tabpane = '') {
        $operation = NULL; //tblevents
        $permission = 'VIEWINVOICES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Invoice Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Invoice Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Invoice Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $invoicetype = new invoicetypes($this->db);
            $invoicetypes = $invoicetype->all();
            $this->f3->set('invoicetypes', $invoicetypes);
            
            $paymentmode = new paymentmodes($this->db);
            $paymentmodes = $paymentmode->all();
            $this->f3->set('paymentmodes', $paymentmodes);
            
            $invoicekind = new invoicekinds($this->db);
            $invoicekinds = $invoicekind->all();
            $this->f3->set('invoicekinds', $invoicekinds);
            
            $datasource = new datasources($this->db);
            $datasources = $datasource->all();
            $this->f3->set('datasources', $datasources);
            
            $industry = new industries($this->db);
            $industries = $industry->all();
            $this->f3->set('industries', $industries);
            
            $currency = new currencies($this->db);
            $currencies = $currency->all();
            $this->f3->set('currencies', $currencies);
            
            $mode = new modes($this->db);
            $modes = $mode->all();
            $this->f3->set('modes', $modes);
            
            $buyertype = new buyertypes($this->db);
            $buyertypes = $buyertype->all();
            $this->f3->set('buyertypes', $buyertypes);
            
            $product = new products($this->db);
            $products = $product->all();
            $this->f3->set('products', $products);
            
            $flag = new flags($this->db);
            $flags = $flag->all();
            $this->f3->set('flags', $flags);
            
            $taxrate = new taxrates($this->db);
            $taxrates = $taxrate->all();
            $this->f3->set('taxrates', $taxrates);
            
            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
            $this->f3->set('seller', $org);
            
            $deliveryterm = new deliveryterms($this->db);
            $deliveryterms = $deliveryterm->all();
            $this->f3->set('deliveryterms', $deliveryterms);
            
            if (is_string($tab) && is_string($tabpane)){
                $this->logger->write("Invoice Controller : view() : The value of v_id is " . $v_id, 'r');
                $this->logger->write("Invoice Controller : view() : The value of tab is " . $tab, 'r');
                $this->logger->write("Invoice Controller : view() : The value of tabpane " . $tabpane, 'r');
            } 
            
            if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
                $id = trim($this->f3->get('PARAMS[id]'));
                $this->logger->write("Invoice Controller : view() : The is a GET call & id to view is " . $id, 'r');
                
                $invoice = new invoices($this->db);
                $invoice->getByID($id);
                $this->f3->set('invoice', $invoice);
                 
                if($this->appsettings['PLATFORMODE'] == 'INT'){
                    $buyer = new customers($this->db);
                } else {
                    $buyer = new buyers($this->db);
                }
                
                $buyer->getByID($invoice->buyerid);
                $this->f3->set('buyer', $buyer);
                
                if (is_string($tab) && is_string($tabpane)){//this check is necessary for cases where the GET request is system initiated. The params sent to the view functions are non-string.
                    $this->f3->set('currenttab', $tab);
                    $this->f3->set('currenttabpane', $tabpane);
                } else {
                    $this->f3->set('currenttab', 'tab_general');
                    $this->f3->set('currenttabpane', 'tab_1');
                    $this->f3->set('path', '../' . $this->path);
                }
                
                $this->f3->set('pagetitle','Edit Invoice | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path); //overide the main solution path
            } elseif (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id')))) {//Open EDIT mode
                $id = trim($this->f3->get('POST.id'));
                $this->logger->write("Invoice Controller : view() : This is a POST call & the id to view is " . $id, 'r');
                
                $invoice = new invoices($this->db);
                $invoice->getByID($id);
                $this->f3->set('invoice', $invoice);
                
                if($this->appsettings['PLATFORMODE'] == 'INT'){
                    $buyer = new customers($this->db);
                } else {
                    $buyer = new buyers($this->db);
                }
                $buyer->getByID($invoice->buyerid);
                $this->f3->set('buyer', $buyer);
                
                
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
                
                $this->f3->set('pagetitle','Edit Invoice | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
            } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
                $id = trim($v_id);
                $this->logger->write("Invoice Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
                
                $invoice = new invoices($this->db);
                $invoice->getByID($id);
                $this->f3->set('invoice', $invoice);
                
                if($this->appsettings['PLATFORMODE'] == 'INT'){
                    $buyer = new customers($this->db);
                } else {
                    $buyer = new buyers($this->db);
                }
                $buyer->getByID($invoice->buyerid);
                $this->f3->set('buyer', $buyer);
                
                
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
                
                $this->f3->set('pagetitle','Edit Invoice | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
                
                $this->f3->set('pagecontent','EditInvoice.htm');
                $this->f3->set('pagescripts','EditInvoiceFooter.htm');
                echo \Template::instance()->render('Layout.htm');
                exit(); //exit the function so no extra code executes
            } else {
                $this->logger->write("Invoice Controller : view() : No id was selected", 'r');
                $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page
                exit();
            }
            
            $this->logger->write("Invoice Controller : view() : The currenttab has been set to " . $this->f3->get('currenttab'), 'r');
            $this->logger->write("Invoice Controller : view() : The currenttabpane has been set to " . $this->f3->get('currenttabpane'), 'r');
            
            $this->f3->set('pagecontent','EditInvoice.htm');
            $this->f3->set('pagescripts','EditInvoiceFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Invoice Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name add
     *  @desc add Invoice
     *	@return NULL
     *	@param NULL
     **/
    function add() {
        $operation = NULL; //tblevents
        $permission = 'CREATEINVOICE'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Invoice Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {           
            //@TODO Display a new form
            
            $invoicetype = new invoicetypes($this->db);
            $invoicetypes = $invoicetype->all();
            $this->f3->set('invoicetypes', $invoicetypes);
            
            $paymentmode = new paymentmodes($this->db);
            $paymentmodes = $paymentmode->all();
            $this->f3->set('paymentmodes', $paymentmodes);
            
            $invoicekind = new invoicekinds($this->db);
            $invoicekinds = $invoicekind->all();
            $this->f3->set('invoicekinds', $invoicekinds);
            
            $datasource = new datasources($this->db);
            $datasources = $datasource->all();
            $this->f3->set('datasources', $datasources);
            
            $industry = new industries($this->db);
            $industries = $industry->all();
            $this->f3->set('industries', $industries);
            
            $currency = new currencies($this->db);
            $currencies = $currency->all();
            $this->f3->set('currencies', $currencies);
            
            $mode = new modes($this->db);
            $modes = $mode->all();
            $this->f3->set('modes', $modes);
            
            $buyertype = new buyertypes($this->db);
            $buyertypes = $buyertype->all();
            $this->f3->set('buyertypes', $buyertypes);
            
            $product = new products($this->db);
            $products = $product->all();
            $this->f3->set('products', $products);
            
            $flag = new flags($this->db);
            $flags = $flag->all();
            $this->f3->set('flags', $flags);
            
            $taxrate = new taxrates($this->db);
            $taxrates = $taxrate->all();
            $this->f3->set('taxrates', $taxrates);
            
            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
            $this->f3->set('seller', $org);
            
            $deliveryterm = new deliveryterms($this->db);
            $deliveryterms = $deliveryterm->all();
            $this->f3->set('deliveryterms', $deliveryterms);
            
            $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
            $this->f3->set('currenttabpane', 'tab_1');
            
            
            $invoice = array(
                "id" => NULL,
                "name" => '',
                "code" => '',
                "description" => ''
            );
            $this->f3->set('invoice', $invoice);
            
            $this->f3->set('pagetitle','Create Invoice');
            
            $this->f3->set('pagecontent','EditInvoice.htm');
            $this->f3->set('pagescripts','EditInvoiceFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Invoice Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    /**
     * edit invoice
     *
     * @name edit
     * @return NULL
     * @param
     *            NULL
     */
    function edit(){
        $invoice = new invoices($this->db);
        $currenttab = trim($this->f3->get('POST.currenttab'));
        $currenttabpane = trim($this->f3->get('POST.currenttabpane'));
        $id = 0;
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        if (trim($this->f3->get('POST.invoiceid')) !== '' || !empty(trim($this->f3->get('POST.invoiceid'))) || trim($this->f3->get('POST.sellerinvoiceid')) !== '' || !empty(trim($this->f3->get('POST.sellerinvoiceid'))) || trim($this->f3->get('POST.buyerinvoiceid')) !== '' || !empty(trim($this->f3->get('POST.buyerinvoiceid'))) || trim($this->f3->get('POST.pickbuyerinvoiceid')) !== '' || !empty(trim($this->f3->get('POST.pickbuyerinvoiceid'))) || trim($this->f3->get('POST.goodinvoiceid')) !== '' || !empty(trim($this->f3->get('POST.goodinvoiceid'))) || trim($this->f3->get('POST.paymentinvoiceid')) !== '' || !empty(trim($this->f3->get('POST.paymentinvoiceid'))) || trim($this->f3->get('POST.deletegoodinvoiceid')) !== '' || !empty(trim($this->f3->get('POST.deletegoodinvoiceid'))) || trim($this->f3->get('POST.addpaymentinvoiceid')) !== '' || !empty(trim($this->f3->get('POST.addpaymentinvoiceid'))) || trim($this->f3->get('POST.deletepaymentinvoiceid')) !== '' || !empty(trim($this->f3->get('POST.deletepaymentinvoiceid'))) || trim($this->f3->get('POST.editgoodinvoiceid')) !== '' || !empty(trim($this->f3->get('POST.editgoodinvoiceid')))){
            $operation = NULL; // tblevents
            $permission = 'EDITINVOICE'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications
			
			var_dump(trim($this->f3->get('POST.invoiceid')));
            
            $this->logger->write("Invoice Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                
                $currenttab = !empty(trim($this->f3->get('POST.currenttab')))? trim($this->f3->get('POST.currenttab')) : (!empty(trim($this->f3->get('POST.sellercurrenttab')))? trim($this->f3->get('POST.sellercurrenttab')) : (!empty(trim($this->f3->get('POST.buyercurrenttab')))? trim($this->f3->get('POST.buyercurrenttab')) : (!empty(trim($this->f3->get('POST.goodcurrenttab')))? trim($this->f3->get('POST.goodcurrenttab')) : (!empty(trim($this->f3->get('POST.paymentcurrenttab')))? trim($this->f3->get('POST.paymentcurrenttab')) : (!empty(trim($this->f3->get('POST.deletegoodcurrenttab')))? trim($this->f3->get('POST.deletegoodcurrenttab')) : (!empty(trim($this->f3->get('POST.deletepaymentcurrenttab')))? trim($this->f3->get('POST.deletepaymentcurrenttab')) : (!empty(trim($this->f3->get('POST.addpaymentcurrenttab')))? trim($this->f3->get('POST.addpaymentcurrenttab')) : (!empty(trim($this->f3->get('POST.pickbuyercurrenttab')))? trim($this->f3->get('POST.pickbuyercurrenttab')) : trim($this->f3->get('POST.editgoodcurrenttab'))))))))));
                $currenttabpane = !empty(trim($this->f3->get('POST.currenttabpane')))? trim($this->f3->get('POST.currenttabpane')) : (!empty(trim($this->f3->get('POST.sellercurrenttabpane')))? trim($this->f3->get('POST.sellercurrenttabpane')) : (!empty(trim($this->f3->get('POST.buyercurrenttabpane')))? trim($this->f3->get('POST.buyercurrenttabpane')) : (!empty(trim($this->f3->get('POST.goodcurrenttabpane')))? trim($this->f3->get('POST.goodcurrenttabpane')) : (!empty(trim($this->f3->get('POST.paymentcurrenttabpane')))? trim($this->f3->get('POST.paymentcurrenttabpane')) : (!empty(trim($this->f3->get('POST.deletegoodcurrenttabpane')))? trim($this->f3->get('POST.deletegoodcurrenttabpane')) : (!empty(trim($this->f3->get('POST.addpaymentcurrenttabpane')))? trim($this->f3->get('POST.addpaymentcurrenttabpane')) : (!empty(trim($this->f3->get('POST.deletepaymentcurrenttabpane')))? trim($this->f3->get('POST.deletepaymentcurrenttabpane')) : (!empty(trim($this->f3->get('POST.pickbuyercurrenttabpane')))? trim($this->f3->get('POST.pickbuyercurrenttabpane')) : trim($this->f3->get('POST.editgoodcurrenttabpane'))))))))));
                                
                if ($currenttab == 'tab_general') {
                    $id = trim($this->f3->get('POST.invoiceid'));
                    $this->logger->write("Invoice Controller : edit() : tab_general :  The id to be edited is " . $id, 'r');
                    $invoice->getByID($id);
                                                   
                    $this->f3->set('POST.erpinvoiceid', $this->f3->get('POST.erpinvoiceid'));
                    
                    $this->f3->set('POST.erpinvoiceno', $this->f3->get('POST.erpinvoiceno'));
                    
                    if(trim($this->f3->get('POST.invoicetype')) !== '' || ! empty(trim($this->f3->get('POST.invoicetype')))) {
                        $this->f3->set('POST.invoicetype', $this->f3->get('POST.invoicetype'));
                    } else {
                        $this->f3->set('POST.invoicetype', $invoice->invoicetype);
                    }
                    
                    if(trim($this->f3->get('POST.invoicekind')) !== '' || ! empty(trim($this->f3->get('POST.currencyname')))) {
                        $this->f3->set('POST.invoicekind', $this->f3->get('POST.invoicekind'));
                    } else {
                        $this->f3->set('POST.invoicekind', $invoice->invoicekind);
                    }
                    
                    if(trim($this->f3->get('POST.datasource')) !== '' || ! empty(trim($this->f3->get('POST.datasource')))) {
                        $this->f3->set('POST.datasource', $this->f3->get('POST.datasource'));
                    } else {
                        $this->f3->set('POST.datasource', $invoice->datasource);
                    }
                    
                    if(trim($this->f3->get('POST.invoiceindustrycode')) !== '' || ! empty(trim($this->f3->get('POST.invoiceindustrycode')))) {
                        $this->f3->set('POST.invoiceindustrycode', $this->f3->get('POST.invoiceindustrycode'));
                    } else {
                        $this->f3->set('POST.invoiceindustrycode', $invoice->invoiceindustrycode);
                    }
                    
                    $this->f3->set('POST.remarks', $this->f3->get('POST.remarks'));
                    
                    if(trim($this->f3->get('POST.invoicedeliveryTermsCode')) !== '' || ! empty(trim($this->f3->get('POST.invoicedeliveryTermsCode')))) {
                        $this->f3->set('POST.deliveryTermsCode', $this->f3->get('POST.invoicedeliveryTermsCode'));
                    } else {
                        $this->f3->set('POST.deliveryTermsCode', $invoice->deliveryTermsCode);
                    }
                    
                    $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                    $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                    
                    try {
                        $invoice->edit($id);
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The invoice - " . $invoice->id . " has been edited by " . $this->f3->get('SESSION.username'));
                        self::$systemalert = "The invoice  - " . $invoice->id . " has been edited";
                        $this->logger->write("Invoice Controller : edit() : The invoice  - " . $invoice->id . " has been edited", 'r');
                    } catch (Exception $e) {
                        $this->logger->write("Invoice Controller : edit() : The operation to edit invoice - " . $invoice->id . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit invoice - " . $invoice->id . " was not successfull");
                        self::$systemalert = "The operation to edit invoice - " . $invoice->id . " was not successful";
                    }
                } elseif ($currenttab == 'tab_seller'){
                    $id = trim($this->f3->get('POST.sellerinvoiceid'));
                    $this->logger->write("Invoice Controller : edit() : tab_seller : The id to be edited is " . $id, 'r');
                    $invoice->getByID($id);
                    
                    $org = new organisations($this->db);
                    $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
                    
                    //$this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                    //$this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                    
                    //$this->f3->set('POST.referenceno', $this->f3->get('POST.referenceno'));
                    
                    //$org->edit($this->appsettings['SELLER_RECORD_ID']);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The seller details on invoice - " . $invoice->id . " have been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The seller details on invoice  - " . $invoice->id . " have been edited";
                    $this->logger->write("Invoice Controller : edit() : The seller details on invoice  - " . $invoice->id . " have been edited", 'r');
                } elseif ($currenttab == 'tab_buyer'){
                    
                    $id = trim($this->f3->get('POST.buyerinvoiceid'))? trim($this->f3->get('POST.buyerinvoiceid')) : (!empty(trim($this->f3->get('POST.pickbuyerinvoiceid')))? trim($this->f3->get('POST.pickbuyerinvoiceid')) : trim($this->f3->get('POST.dropbuyerinvoiceid')));
                    $this->logger->write("Invoice Controller : edit() : tab_buyer : The id to be edited is " . $id, 'r');
                    $invoice->getByID($id);
                    
                    $buyer = new buyers($this->db);
                    $customer = new customers($this->db);
                    
                    $buyerid = (trim($this->f3->get('POST.buyerid')) !== '' || !empty(trim($this->f3->get('POST.buyerid'))))? $this->f3->get('POST.buyerid') : $this->f3->get('POST.pickbuyerid');
                    
                    $this->logger->write("Invoice Controller : edit() : tab_buyer : buyerid = " . $buyerid, 'r');
                    $this->logger->write("Invoice Controller : edit() : tab_buyer : type = " . $this->f3->get('POST.buyertype'), 'r');
                    $this->logger->write("Invoice Controller : edit() : tab_buyer : tin = " . $this->f3->get('POST.buyertin'), 'r');
                    $this->logger->write("Invoice Controller : edit() : tab_buyer : legalname = " . $this->f3->get('POST.buyerlegalname'), 'r');
                    $this->logger->write("Invoice Controller : edit() : tab_buyer : emailaddress = " . $this->f3->get('POST.buyeremailaddress'), 'r');
                    $this->logger->write("Invoice Controller : edit() : tab_buyer : customer id = " . $this->f3->get('POST.pickbuyertemplate'), 'r');
                    
                    
                    $custId = trim($this->f3->get('POST.pickbuyertemplate'));
                    $buyerTinInput = trim($this->f3->get('POST.buyertin'));
                    $buyerLegalNameInput = trim($this->f3->get('POST.buyerlegalname'));

                    // Prevent persisting partial buyer records when no customer template is selected.
                    if (($custId === '' || empty($custId)) && ($buyerTinInput === '' || $buyerLegalNameInput === '')) {
                        $this->logger->write("Invoice Controller : edit() : tab_buyer : Validation failed. Missing required buyer fields (TIN and/or Legal Name) with no selected customer template.", 'r');
                        self::$systemalert = "Buyer save failed: enter Buyer TIN and Legal Name, or pick a buyer template.";
                        $this->f3->set('systemalert', self::$systemalert);
                        self::view($id, 'tab_buyer', 'tab_3');
                        return;
                    }

                    $customer->getByID($custId);
                    
                    if (trim($buyerid) !== '' || !empty(trim($buyerid))) {
                        
                        $buyer->getByID($buyerid);
                        
                        
                        $this->logger->write("Invoice Controller : edit() : tab_buyer : hey", 'r');
                        
                        if (trim($custId) !== '' || !empty(trim($custId))) {
                            
                            $this->f3->set('POST.tin', $customer->tin);
                            $this->f3->set('POST.ninbrn', $customer->ninbrn);
                            $this->f3->set('POST.legalname', $customer->legalname);
                            $this->f3->set('POST.businessname', $customer->businessname);
                            $this->f3->set('POST.address', $customer->address);
                            $this->f3->set('POST.mobilephone', $customer->mobilephone);
                            $this->f3->set('POST.linephone', $customer->linephone);
                            $this->f3->set('POST.emailaddress', $customer->emailaddress);
                            $this->f3->set('POST.placeofbusiness', $customer->placeofbusiness);
                            $this->f3->set('POST.type', $customer->type);
                            $this->f3->set('POST.citizineship', $customer->citizineship);
                            $this->f3->set('POST.sector', $customer->sector);
                            $this->f3->set('POST.PassportNum', $customer->PassportNum);
                        } else {
                            //Edit details
                            $this->f3->set('POST.tin', $this->f3->get('POST.buyertin'));
                            $this->f3->set('POST.ninbrn', $this->f3->get('POST.buyerninbrn'));
                            $this->f3->set('POST.legalname', $this->f3->get('POST.buyerlegalname'));
                            $this->f3->set('POST.businessname', $this->f3->get('POST.buyerbusinessname'));
                            $this->f3->set('POST.address', $this->f3->get('POST.buyeraddress'));
                            $this->f3->set('POST.mobilephone', $this->f3->get('POST.buyermobilephone'));
                            $this->f3->set('POST.linephone', $this->f3->get('POST.buyrlinephone'));
                            $this->f3->set('POST.emailaddress', $this->f3->get('POST.buyeremailaddress'));
                            $this->f3->set('POST.placeofbusiness', $this->f3->get('POST.buyerplaceofbusiness'));
                            $this->f3->set('POST.type', $this->f3->get('POST.buyertype'));
                            $this->f3->set('POST.citizineship', $this->f3->get('POST.buyercitizineship'));
                            $this->f3->set('POST.sector', $this->f3->get('POST.buyersector'));
                            $this->f3->set('POST.referenceno', $this->f3->get('POST.buyerreferenceno'));
                            $this->f3->set('POST.PassportNum', $this->f3->get('POST.buyerpassportnum'));
                        }
                        
                        
                        
                        $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                        
                        $buyer->edit($buyerid);
                        
                    } else {
                        $this->logger->write("Invoice Controller : edit() : tab_buyer : ney", 'r');
                        
                        if (trim($custId) !== '' || !empty(trim($custId))) {
                            
                            $this->f3->set('POST.tin', $customer->tin);
                            $this->f3->set('POST.ninbrn', $customer->ninbrn);
                            $this->f3->set('POST.legalname', $customer->legalname);
                            $this->f3->set('POST.businessname', $customer->businessname);
                            $this->f3->set('POST.address', $customer->address);
                            $this->f3->set('POST.mobilephone', $customer->mobilephone);
                            $this->f3->set('POST.linephone', $customer->linephone);
                            $this->f3->set('POST.emailaddress', $customer->emailaddress);
                            $this->f3->set('POST.placeofbusiness', $customer->placeofbusiness);
                            $this->f3->set('POST.type', $customer->type);
                            $this->f3->set('POST.citizineship', $customer->citizineship);
                            $this->f3->set('POST.sector', $customer->sector);
                            $this->f3->set('POST.PassportNum', $customer->PassportNum);
                        } else {
                            //Edit details
                            $this->f3->set('POST.tin', $this->f3->get('POST.buyertin'));
                            $this->f3->set('POST.ninbrn', $this->f3->get('POST.buyerninbrn'));
                            $this->f3->set('POST.legalname', $this->f3->get('POST.buyerlegalname'));
                            $this->f3->set('POST.businessname', $this->f3->get('POST.buyerbusinessname'));
                            $this->f3->set('POST.address', $this->f3->get('POST.buyeraddress'));
                            $this->f3->set('POST.mobilephone', $this->f3->get('POST.buyermobilephone'));
                            $this->f3->set('POST.linephone', $this->f3->get('POST.buyrlinephone'));
                            $this->f3->set('POST.emailaddress', $this->f3->get('POST.buyeremailaddress'));
                            $this->f3->set('POST.placeofbusiness', $this->f3->get('POST.buyerplaceofbusiness'));
                            $this->f3->set('POST.type', $this->f3->get('POST.buyertype'));
                            $this->f3->set('POST.citizineship', $this->f3->get('POST.buyercitizineship'));
                            $this->f3->set('POST.sector', $this->f3->get('POST.buyersector'));
                            $this->f3->set('POST.referenceno', $this->f3->get('POST.buyerreferenceno'));
                            $this->f3->set('POST.PassportNum', $this->f3->get('POST.buyerpassportnum'));
                        }
                        
                        $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                        $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                        
                        $buyer->add();
                        
                        try {
                            //retrieve the most recently inserted buyer
                            //@TODO: place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                            $data = array ();
                            
                            $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblbuyers WHERE insertedby = ' . $this->f3->get('SESSION.id')));
                            foreach ( $r as $obj ) {
                                $data [] = $obj;
                            }
                            $buyerid = $data[0]['id'];
                            
                            $this->db->exec(array('UPDATE tblinvoices SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                            
                        } catch (Exception $e) {
                            $this->logger->write("Role Controller : edit() : The operation to retrieve the most recently added buyer was not successful. The error messages is " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added buyer was not successful");
                        }
                        
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The buyer details on invoice - " . $invoice->id . " have been edited by " . $this->f3->get('SESSION.username'));
                        self::$systemalert = "The buyer details on invoice  - " . $invoice->id . " have been edited";
                        $this->logger->write("Invoice Controller : edit() : The buyer details on invoice  - " . $invoice->id . " have been edited", 'r');
                    }
                    
                    $invoice->getByID($id);//refresh
                } elseif ($currenttab == 'tab_good'){
                    $id = trim($this->f3->get('POST.goodinvoiceid'))? trim($this->f3->get('POST.goodinvoiceid')) : (!empty(trim($this->f3->get('POST.deletegoodinvoiceid')))? trim($this->f3->get('POST.deletegoodinvoiceid')) : trim($this->f3->get('POST.editgoodinvoiceid')));
                    $this->logger->write("Invoice Controller : edit() : tab_good : The id to be edited is " . $id, 'r');
                    $invoice->getByID($id);
                    
                    $good = new goods($this->db);
                    $product = new products($this->db);
                                        
                    $commoditycategory = new commoditycategories($this->db);
                    
                    $this->logger->write("Invoice Controller : edit() : editgoodid = : " . $this->f3->get('POST.editgoodid'), 'r');
                    $this->logger->write("Invoice Controller : edit() : deletegoodid = : " . $this->f3->get('POST.deletegoodid'), 'r');
                    $this->logger->write("Invoice Controller : edit() : deletegoodinvoiceid = : " . $this->f3->get('POST.deletegoodinvoiceid'), 'r');
                    
                    if (trim($this->f3->get('POST.editgoodid')) !== '' || !empty(trim($this->f3->get('POST.editgoodid')))) {
                        $this->logger->write("Invoice Controller : edit() : tab_good : Edit operation", 'r');
                        /**
                         * The following algorithm will be followed when editing a good.
                         * 1. Pick the value of fields from the form
                         * 2. Determine if there is a discount
                         * 3. If [2] is true, populate the discount rate based on the tax rate choice
                         * 4. Retrieve the tax rate from the tax/rate choice made by the user
                         * 5. Calculate TAX
                         * 6. Calculate GROSS AMOUNT
                         * 7. calculate NET AMOUNT
                         *
                         */
                        
                        if ($invoice->einvoiceid) {
                            $this->logger->write("Invoice Controller : edit() : This invoice is already uploaded", 'r');
                            self::$systemalert = "This invoice is already uploaded";
                        } else {
                            $goodid = $this->f3->get('POST.editgoodid');
                            $good->getByID($goodid);
                            //$this->f3->set('POST.groupid', $invoice->paymentdetailgroupid);
                            //$this->f3->set('POST.discountflag', $this->f3->get('POST.editdiscountflag'));
                            
                            if(trim($this->f3->get('POST.editdiscountpercentage')) !== '' || ! empty(trim($this->f3->get('POST.editdiscountpercentage')))) {
                                if ((float)$this->f3->get('POST.editdiscountpercentage') ==! 0) {
                                    $this->f3->set('POST.discountflag', '1'); ///SET TO 1
                                } else {
                                    $this->f3->set('POST.discountflag', '2'); ///SET TO 2
                                }
                                
                            } else {
                                $this->f3->set('POST.discountflag', '2'); //SET TO 2
                            }
                            
                            $this->logger->write("Invoice Controller : edit() : edititem: " . $this->f3->get('POST.edititem'), 'r');
                            //$product->getByCode($good->itemcode);
                            $product->getByCode($this->f3->get('POST.edititem'));
                            
                            
                            $this->f3->set('POST.groupid', $invoice->gooddetailgroupid);
                            $this->f3->set('POST.itemcode', $product->code);
                            $this->f3->set('POST.qty', $this->f3->get('POST.editqty'));
                            
                            $measureunit = new measureunits($this->db);
                            $measureunit->getByCode($product->measureunit);
                            $this->logger->write($this->db->log(TRUE), 'r');
                            
                            $this->f3->set('POST.unitofmeasure', $measureunit->code);
                            $this->f3->set('POST.unitofmeasurename', $measureunit->name);
                            
                            
                            $this->f3->set('POST.item', $product->name);
                            
                            /**
                             * Author: Francis Lubanga <frncslubanga@gmail.com>
                             * Date: 2025-08-03
                             * Description: Add additional data points to the goods details table.
                             */
                            $hscode = new hscodes($this->db);
                            
                            $this->f3->set('POST.vatApplicableFlag', $product->vatApplicableFlag);
                            $this->f3->set('POST.vatProjectId', $product->vatProjectId);
                            $this->f3->set('POST.vatProjectName', $product->vatProjectName);
                            $this->f3->set('POST.hsCode', $product->hsCode);
                            $this->f3->set('POST.hsName', $product->hsName);
                            $this->f3->set('POST.totalWeight', $product->weight);
                            $this->f3->set('POST.pieceQty', $this->f3->get('POST.addqty'));
                            $this->f3->set('POST.pieceMeasureUnit', $product->piecemeasureunit);
                            $this->f3->set('POST.deemedExemptCode', $product->deemedExemptCode);
                            
                            
                            /**
                             * 1. Retrieve the good's commodity category
                             * 2. Retrieve the buyer's TIN
                             * 3. Check EFRIS for the customer's status against the commodity code
                             * 4. If the check in step [3] returns EXEMPT or DEEMED, then override the tax
                             */
                            
                            $commoditycategory->getByCode($product->commoditycategorycode);
                            
                            $this->f3->set('POST.goodscategoryid', $commoditycategory->commoditycode);
                            $this->f3->set('POST.goodscategoryname', $commoditycategory->commodityname);
                            
                            $buyer_g = new buyers($this->db);
                            $buyer_g->getByID($invoice->buyerid);
                            $this->logger->write($this->db->log(TRUE), 'r');
                            
                            $data_c = $this->util->checktaxpayertype($this->f3->get('SESSION.id'), $buyer_g->tin, $commoditycategory->commoditycode);//will return JSON.
                            $data_c = json_decode($data_c, true);
                            
                            if ($data_c) {
                                $commodityCategoryTaxpayerType = NULL;
                                
                                if (isset($data_c['commodityCategory'])){
                                    if ($data_c['commodityCategory']) {
                                        
                                        foreach($data_c['commodityCategory'] as $elem){
                                            $commodityCategoryTaxpayerType = !isset($elem['commodityCategoryTaxpayerType'])? NULL : $elem['commodityCategoryTaxpayerType'];
                                        }
                                    }
                                }
                                
                                
                            }
 
                            //Calculate
                            $tr = new taxrates($this->db);
                            
                            if ($commodityCategoryTaxpayerType == '103') {//DEEMED
                                $this->logger->write("Invoice Controller : edit() : tab_good : Tax rate has been overidden to DEEMED", 'r');
                                $this->f3->set('POST.taxid', $this->appsettings['DEEMEDTAXRATE']);
                                $tr->getByID($this->appsettings['DEEMEDTAXRATE']);
                            } elseif ($commodityCategoryTaxpayerType == '102'){//EXEMPT
                                $this->logger->write("Invoice Controller : edit() : tab_good : Tax rate has been overidden to EXEMPT", 'r');
                                $this->f3->set('POST.taxid', $this->appsettings['EXPEMPTTAXRATE']);
                                $tr->getByID($this->appsettings['EXPEMPTTAXRATE']);
                            } else {
                                $this->logger->write("Invoice Controller : edit() : tab_good : Tax rate has not been overidden", 'r');
                                $this->f3->set('POST.taxid', $this->f3->get('POST.edittaxrate'));
                                $tr->getByID($this->f3->get('POST.edittaxrate'));
                            }
                            
                            if(trim($this->f3->get('POST.taxid')) == $this->appsettings['DEEMEDTAXRATE']) {
                                $this->f3->set('POST.deemedflag', '1'); ///SET TO 1
                            } else {
                                $this->f3->set('POST.deemedflag', '2'); //SET TO 2
                            }
                            
                            $taxcode = $tr->code;
                            $taxname = $tr->name;
                            $taxcategory = $tr->category;
                            $taxdescription = $tr->description;
                            $rate = $tr->rate? $tr->rate : 0;
                            $qty = $this->f3->get('POST.editqty');
                            $unit = $this->f3->get('POST.editunitprice');
                            $discountpct = empty($this->f3->get('POST.editdiscountpercentage'))? 0 : (float)$this->f3->get('POST.editdiscountpercentage');
                            $taxdisplaycategory = $tr->displayCategoryCode;
                            
                            
                            /*$total = ($qty * $unit);
                            $discount = ($discountpct/100) * $total;
                            
                            $gross = $total - $discount;
                            $discount = (-1) * $discount;
                            
                            $tax = ($gross/($rate + 1)) * $rate;
                            $net = $gross - $tax;*/
                            
                            /**
                             * Modification Date: 2021-01-26
	                         * Modified By: Francis Lubanga
	                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                             */
                            $d_gross = 0;
                            $d_tax = 0;
                            $d_net = 0;
                            
                            $total = ($qty * $unit);
                            $discount = ($discountpct/100) * $total;
                            
                            //$gross = $total - $discount;
                            $gross = $total;
                            $discount = (-1) * $discount;
                            
                            $tax = ($gross/($rate + 1)) * $rate;
                            $net = $gross - $tax;
                            
                            
                            //$total = ($qty * $unit);
                            if ($discountpct > 0) {
                                $this->f3->set('POST.discounttaxrate', $rate);
                                $this->f3->set('POST.discountpercentage', $discountpct);
                                /*
                                 $discount = ($discountpct/100) * $total;
                                 */
                                
                                 $d_gross = $discount;
                                 $d_tax = ($d_gross/($rate + 1)) * $rate;
                                 
                                 $d_net = $d_gross - $d_tax;
                                 
                                 $this->logger->write("Invoice Controller : edit() : tab_good : disc_gross = " . $d_gross, 'r');
                                 $this->logger->write("Invoice Controller : edit() : tab_good : disc_tax = " . $d_tax, 'r');
                                 $this->logger->write("Invoice Controller : edit() : tab_good : disc_net = " . $d_net, 'r');
                                 
                            } else {
                                $this->f3->set('POST.discounttaxrate', 0);
                                /*
                                 $gross = $total;
                                 
                                 $tax = ($gross/($rate + 1)) * $rate;
                                 $net = $gross - $tax;
                                 */
                            }
                            
                            //TRUNCATE
                            $unit = number_format($unit, 8, '.', '');
                            $total = number_format($total, 8, '.', '');
                            $discount = number_format($discount, 8, '.', '');
                            $gross = number_format($gross, 8, '.', '');
                            $tax = number_format($tax, 8, '.', '');
                            $net = number_format($net, 8, '.', '');
                            
                            
                            $this->logger->write("Invoice Controller : edit() : tab_good : discountpct = " . $discountpct, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : total = " . $total, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : discount = " . $discount, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : gross = " . $gross, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : taxcode = " . $taxcode, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : rate = " . $rate, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : qty = " . $qty, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : rate = " . $rate, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : tax = " . $tax, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : net = " . $net, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : unit = " . $unit, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : taxcategory = " . $taxcategory, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : taxdescription = " . $taxdescription, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : taxdisplaycategory = " . $taxdisplaycategory, 'r');
                            
                            $this->f3->set('POST.unitprice', $unit);
                            $this->f3->set('POST.total', $total); 
                            
                            /**
	                         * Modification Date: 2022-01-31
	                         * Modified By: Francis Lubanga
	                         * Description: Resolving error code 1334 - goodsDetails-->taxRate:The tax rate for this product is not tax-zero!
	                         * */
                            $this->f3->set('POST.taxrate', $rate);
                            //$this->f3->set('POST.taxrate', $product->taxrate);
                            
                            if ($this->vatRegistered == 'Y') {
                                $this->f3->set('POST.tax', $tax);
                                $this->f3->set('POST.taxcategory', $taxcategory);
                                $this->f3->set('POST.displayCategoryCode', $taxdisplaycategory);
                            } else {
                                $this->f3->set('POST.tax', 0);
                                $this->f3->set('POST.taxcategory', NULL);
                                $this->f3->set('POST.displayCategoryCode', NULL);
                            }
                            
                            $this->f3->set('POST.discounttotal', $discount);
                            
                            $this->logger->write("Invoice Controller : edit() : tab_good : editexciseflag = " . $this->f3->get('POST.editexciseflag'), 'r');
                            
                            
                            if(trim($this->f3->get('POST.editexciseflag')) !== '' || ! empty(trim($this->f3->get('POST.editexciseflag')))) {
                                if ($this->f3->get('POST.editexciseflag') == '1') {
                                    $this->f3->set('POST.exciseflag', '1'); ///SET TO 1
                                } elseif($this->f3->get('POST.editexciseflag') == '2'){
                                    $this->f3->set('POST.exciseflag', '2'); ///SET TO 2
                                } else {
                                    $this->f3->set('POST.exciseflag', '2'); ///SET TO 2
                                }
                                
                            } else {
                                $this->f3->set('POST.exciseflag', '2'); //SET TO 2
                            }
                            
                            
                            //$this->f3->set('POST.deemedflag', $this->f3->get('POST.editdeemedflag'));
                            //$this->f3->set('POST.exciseflag', $this->f3->get('POST.editexciseflag'));
                            $this->f3->set('POST.categoryid', $this->f3->get('POST.editcategoryid'));
                            $this->f3->set('POST.categoryname', $this->f3->get('POST.editcategoryname'));
                            
                            
                            
                            //$this->f3->set('POST.exciserate', $this->f3->get('POST.editexciserate'));
                            //$this->f3->set('POST.exciserule', $this->f3->get('POST.editexciserule'));
                            //$this->f3->set('POST.excisetax', $this->f3->get('POST.editexcisetax'));
                            //$this->f3->set('POST.pack', $this->f3->get('POST.editpack'));
                            //$this->f3->set('POST.stick', $this->f3->get('POST.editstick'));
                            //$this->f3->set('POST.exciseunit', $this->f3->get('POST.editexciseunit'));
                            //$this->f3->set('POST.excisecurrency', $this->f3->get('POST.editexcisecurrency'));
                            //$this->f3->set('POST.exciseratename', $this->f3->get('POST.editexciseratename'));
                            
                            
                            $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                            $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                            
                            try{
                                $good->edit($goodid);
                                
                                if ($this->vatRegistered == 'Y') {
                                    try{
                                        $this->db->exec(array('DELETE FROM tbltaxdetails WHERE goodid = ' . $good->id . ' AND groupid = ' . $invoice->taxdetailgroupid));
                                        
                                        $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES(' . $invoice->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcategory . '", "' . $taxcode . '", ' . ($net + $d_net) . ', ' . $rate . ', ' . ($tax + $d_tax) . ', ' . ($gross + $d_gross) . ', NULL, NULL, "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                        
                                        //insert a tax record for the discount
                                        /*
                                         if ($discountpct > 0) {
                                         $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                         VALUES(' . $invoice->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcode . '", ' . $d_net . ', ' . $rate . ', ' . $d_tax . ', ' . $d_gross . ', NULL, NULL, "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                         }*/
                                        
                                    } catch (Exception $e) {
                                        $this->logger->write("Invoice Controller : edit() : Failed to insert into table tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                                    }
                                }
                                
                                
                            } catch (Exception $e) {
                                $this->logger->write("Invoice Controller : edit() : Failed to insert into table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                            }
                                                       
                        }

                        $invoice->getByID($id);//refresh
                    } elseif (trim($this->f3->get('POST.deletegoodid')) !== '' || !empty(trim($this->f3->get('POST.deletegoodid')))) {
                        $this->logger->write("Invoice Controller : edit() : tab_good : Delete operation", 'r');
                        $good->getByID($this->f3->get('POST.deletegoodid'));
                        $good->delete($this->f3->get('POST.deletegoodid'));
                        
                        try{
                            $this->db->exec(array('DELETE FROM tbltaxdetails WHERE goodid = ' . $good->id));
                        } catch (Exception $e) {
                            $this->logger->write("Invoice Controller : edit() : The operation to delete the related tax details was not successful. The error messages is " . $e->getMessage(), 'r');
                        }
                    } else {
                        $this->logger->write("Invoice Controller : edit() : tab_good : Add operation", 'r');
                        
                        /**
                         * The following algorithm will be followed when adding a good.
                         * 1. Pick the value of fields from the form
                         * 2. Determine if there is a discount
                         * 3. If [2] is true, populate the discount rate based on the tax rate choice
                         * 4. Retrieve the tax rate from the tax/rate choice made by the user
                         * 5. Calculate TAX
                         * 6. Calculate GROSS AMOUNT
                         * 7. calculate NET AMOUNT
                         * 
                         */
                        
                        $this->logger->write("Invoice Controller : edit() : tab_good : invoice id = " . $id, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : item = " . $this->f3->get('POST.additem'), 'r');
                        
                        $invoice->getByID($id);
                        $this->f3->set('POST.groupid', $invoice->paymentdetailgroupid);
                        $this->f3->set('POST.ordernumber', $this->f3->get('POST.addordernumber'));
                        //$this->f3->set('POST.discountflag', $this->f3->get('POST.adddiscountflag'));
                        
                                                
                        if(trim($this->f3->get('POST.adddiscountpercentage')) !== '' || ! empty(trim($this->f3->get('POST.adddiscountpercentage')))) {
                            if ((float)$this->f3->get('POST.adddiscountpercentage') ==! 0) {
                                $this->f3->set('POST.discountflag', '1'); ///SET TO 1
                            } else {
                                $this->f3->set('POST.discountflag', '2'); ///SET TO 2
                            }
                            
                        } else {
                            $this->f3->set('POST.discountflag', '2'); //SET TO 2
                        }
                        
                        
                        //$product->getByID($this->f3->get('POST.additem'));
                        $product->getByCode($this->f3->get('POST.additem'));
                        
                        $this->f3->set('POST.groupid', $invoice->gooddetailgroupid);
                        $this->f3->set('POST.itemcode', $product->code);
                        $this->f3->set('POST.qty', $this->f3->get('POST.addqty'));
                        
                        $measureunit = new measureunits($this->db);
                        $measureunit->getByCode($product->measureunit);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        
                        $this->f3->set('POST.unitofmeasure', $measureunit->code);
                        $this->f3->set('POST.unitofmeasurename', $measureunit->name);
                        
                        
                        $this->f3->set('POST.item', $product->name);
                        
                        /**
                         * Author: Francis Lubanga <frncslubanga@gmail.com>
                         * Date: 2025-08-03
                         * Description: Add additional data points to the goods details table.
                         */
                        $this->f3->set('POST.vatApplicableFlag', $product->vatApplicableFlag);
                        $this->f3->set('POST.vatProjectId', $product->vatProjectId);
                        $this->f3->set('POST.vatProjectName', $product->vatProjectName);
                        $this->f3->set('POST.hsCode', $product->hsCode);
                        $this->f3->set('POST.hsName', $product->hsName);
                        $this->f3->set('POST.totalWeight', $product->weight);
                        $this->f3->set('POST.pieceQty', $this->f3->get('POST.addqty'));
                        $this->f3->set('POST.pieceMeasureUnit', $product->piecemeasureunit);
                        $this->f3->set('POST.deemedExemptCode', $product->deemedExemptCode);
                        
                        								
                        
                        /**
                         * 1. Retrieve the good's commodity category
                         * 2. Retrieve the buyer's TIN
                         * 3. Check EFRIS for the customer's status against the commodity code
                         * 4. If the check in step [3] returns EXEMPT or DEEMED, then override the tax
                         */
                        
                        $commoditycategory->getByCode($product->commoditycategorycode);
                        
                        $this->f3->set('POST.goodscategoryid', $commoditycategory->commoditycode);
                        $this->f3->set('POST.goodscategoryname', $commoditycategory->commodityname);
                        
                        $buyer_g = new buyers($this->db);
                        $buyer_g->getByID($invoice->buyerid);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        
                        $data_c = $this->util->checktaxpayertype($this->f3->get('SESSION.id'), $buyer_g->tin, $commoditycategory->commoditycode);//will return JSON.
                        $data_c = json_decode($data_c, true);
                        
                        if ($data_c) {
                            $commodityCategoryTaxpayerType = NULL;
                            
                            if (isset($data_c['commodityCategory'])){
                                if ($data_c['commodityCategory']) {
                                    
                                    foreach($data_c['commodityCategory'] as $elem){
                                        $commodityCategoryTaxpayerType = !isset($elem['commodityCategoryTaxpayerType'])? NULL : $elem['commodityCategoryTaxpayerType'];
                                    }
                                }
                            }
                            
                            
                        } 
                        
                        
                        //Calculate
                        $tr = new taxrates($this->db);
                        
                        if ($commodityCategoryTaxpayerType == '103') {//DEEMED
                            $this->logger->write("Invoice Controller : edit() : tab_good : Tax rate has been overidden to DEEMED", 'r');
                            $this->f3->set('POST.taxid', $this->appsettings['DEEMEDTAXRATE']);
                            $tr->getByID($this->appsettings['DEEMEDTAXRATE']);
                        } elseif ($commodityCategoryTaxpayerType == '102'){//EXEMPT
                            $this->logger->write("Invoice Controller : edit() : tab_good : Tax rate has been overidden to EXEMPT", 'r');
                            $this->f3->set('POST.taxid', $this->appsettings['EXPEMPTTAXRATE']);
                            $tr->getByID($this->appsettings['EXPEMPTTAXRATE']);
                        } else {
                            $this->logger->write("Invoice Controller : edit() : tab_good : Tax rate has not been overidden", 'r');
                            $this->f3->set('POST.taxid', $this->f3->get('POST.addtaxrate'));
                            $tr->getByID($this->f3->get('POST.addtaxrate'));
                        }
                        
                        if(trim($this->f3->get('POST.taxid')) == $this->appsettings['DEEMEDTAXRATE']) {
                            $this->f3->set('POST.deemedflag', '1'); ///SET TO 1
                        } else {
                            $this->f3->set('POST.deemedflag', '2'); //SET TO 2
                        }

                        $taxcode = $tr->code;
                        $taxname = $tr->name;
                        $taxcategory = $tr->category;
                        $taxdescription = $tr->description;
                        $rate = $tr->rate? $tr->rate : 0;
                        $qty = $this->f3->get('POST.addqty');
                        $unit = $this->f3->get('POST.addunitprice');
                        $discountpct = empty($this->f3->get('POST.adddiscountpercentage'))? 0 : (float)$this->f3->get('POST.adddiscountpercentage');
                        $taxdisplaycategory = $tr->displayCategoryCode;
                        
                        
                        /*$total = ($qty * $unit);
                        $discount = ($discountpct/100) * $total;
                        
                        $gross = $total - $discount;
                        $discount = (-1) * $discount;
                        
                        $tax = ($gross/($rate + 1)) * $rate;
                        $net = $gross - $tax;*/
                        
                        /**
                         * Modification Date: 2021-01-26
                         * Modified By: Francis Lubanga
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         */
                        $d_gross = 0;
                        $d_tax = 0;
                        $d_net = 0;
                        
                        $total = ($qty * $unit);
                        $discount = ($discountpct/100) * $total;
                        
                        //$gross = $total - $discount;
                        $gross = $total;
                        $discount = (-1) * $discount;
                        
                        $tax = ($gross/($rate + 1)) * $rate;
                        $net = $gross - $tax;
                        
                        
                        //$total = ($qty * $unit);
                        if ($discountpct > 0) {
                            $this->f3->set('POST.discounttaxrate', $rate);
                            $this->f3->set('POST.discountpercentage', $discountpct);
                            /*
                             $discount = ($discountpct/100) * $total;
                             */
                            
                            $d_gross = $discount;
                            $d_tax = ($d_gross/($rate + 1)) * $rate;
                            
                            $d_net = $d_gross - $d_tax;
                            
                            $this->logger->write("Invoice Controller : edit() : tab_good : disc_gross = " . $d_gross, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : disc_tax = " . $d_tax, 'r');
                            $this->logger->write("Invoice Controller : edit() : tab_good : disc_net = " . $d_net, 'r');
                            
                        } else {
                            $this->f3->set('POST.discounttaxrate', 0);
                            /*
                             $gross = $total;
                             
                             $tax = ($gross/($rate + 1)) * $rate;
                             $net = $gross - $tax;
                             */
                        }
                        
                        
                        //TRUNCATE
                        $unit = number_format($unit, 8, '.', '');
                        $total = number_format($total, 8, '.', '');
                        $discount = number_format($discount, 8, '.', '');
                        $gross = number_format($gross, 8, '.', '');
                        $tax = number_format($tax, 8, '.', '');
                        $net = number_format($net, 8, '.', '');
                        
                        $this->logger->write("Invoice Controller : edit() : tab_good : discountpct = " . $discountpct, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : total = " . $total, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : discount = " . $discount, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : gross = " . $gross, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : taxcode = " . $taxcode, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : rate = " . $rate, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : qty = " . $qty, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : rate = " . $rate, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : tax = " . $tax, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : net = " . $net, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : unit = " . $unit, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : taxcategory = " . $taxcategory, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : taxdescription = " . $taxdescription, 'r');
                        $this->logger->write("Invoice Controller : edit() : tab_good : taxdisplaycategory = " . $taxdisplaycategory, 'r');
                        
                        $this->f3->set('POST.unitprice', $unit);
                        $this->f3->set('POST.total', $total);
                        
                        /**
                         * Modification Date: 2022-01-31
                         * Modified By: Francis Lubanga
                         * Description: Resolving error code 1334 - goodsDetails-->taxRate:The tax rate for this product is not tax-zero!
                         * */
                        $this->f3->set('POST.taxrate', $rate);
                        //$this->f3->set('POST.taxrate', $product->taxrate);
                        
                        if ($this->vatRegistered == 'Y') {
                            $this->f3->set('POST.tax', $tax);
                            $this->f3->set('POST.taxcategory', $taxcategory);
                            $this->f3->set('POST.displayCategoryCode', $taxdisplaycategory);
                        } else {
                            $this->f3->set('POST.tax', 0);
                            $this->f3->set('POST.taxcategory', NULL);
                            $this->f3->set('POST.displayCategoryCode', NULL);
                        }
                        
                        $this->f3->set('POST.discounttotal', $discount);
                        
                                               
                        $this->logger->write("Invoice Controller : edit() : tab_good : addexciseflag = " . $this->f3->get('POST.addexciseflag'), 'r');
                        
                        if(trim($this->f3->get('POST.addexciseflag')) !== '' || ! empty(trim($this->f3->get('POST.addexciseflag')))) {
                            if ($this->f3->get('POST.addexciseflag') == '1') {
                                $this->f3->set('POST.exciseflag', '1'); ///SET TO 1
                            } elseif($this->f3->get('POST.addexciseflag') == '2'){
                                $this->f3->set('POST.exciseflag', '2'); ///SET TO 2
                            } else {
                                $this->f3->set('POST.exciseflag', '2'); ///SET TO 2
                            }
                            
                        } else {
                            $this->f3->set('POST.exciseflag', '2'); //SET TO 2
                        }
                        
                        //$this->f3->set('POST.deemedflag', $this->f3->get('POST.adddeemedflag'));
                        //$this->f3->set('POST.exciseflag', $this->f3->get('POST.addexciseflag'));
                        $this->f3->set('POST.categoryid', $this->f3->get('POST.addcategoryid'));
                        $this->f3->set('POST.categoryname', $this->f3->get('POST.addcategoryname'));
                        
                        
                        
                        //$this->f3->set('POST.exciserate', $this->f3->get('POST.addexciserate'));
                        //$this->f3->set('POST.exciserule', $this->f3->get('POST.addexciserule'));
                        //$this->f3->set('POST.excisetax', $this->f3->get('POST.addexcisetax'));
                        //$this->f3->set('POST.pack', $this->f3->get('POST.addpack'));
                        //$this->f3->set('POST.stick', $this->f3->get('POST.addstick'));
                        //$this->f3->set('POST.exciseunit', $this->f3->get('POST.addexciseunit'));
                        //$this->f3->set('POST.excisecurrency', $this->f3->get('POST.addexcisecurrency'));
                        //$this->f3->set('POST.exciseratename', $this->f3->get('POST.addexciseratename'));
                        
                        
                        $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                        $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                        
                        try{
                            $good->add();
                            $this->logger->write($this->db->log(TRUE), 'r');
                            
                            try{
                                $data = array ();
                                $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetails WHERE insertedby = ' . $this->f3->get('SESSION.id')));
                                foreach ( $r as $obj ) {
                                    $data [] = $obj;
                                }
                                
                                $goodid = $data[0]['id'];
                                
                                if ($this->vatRegistered == 'Y') {
                                    $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                                                VALUES(' . $invoice->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcategory . '", "' . $taxcode . '", ' . ($net + $d_net) . ', ' . $rate . ', ' . ($tax + $d_tax) . ', ' . ($gross + $d_gross) . ', NULL, NULL, "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                    
                                    //insert a tax record for the discount
                                    /*
                                     if ($discountpct > 0) {
                                     $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                     VALUES(' . $invoice->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcode . '", ' . $d_net . ', ' . $rate . ', ' . $d_tax . ', ' . $d_gross . ', NULL, NULL, "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                     }*/
                                }
                                
                            } catch (Exception $e) {
                                $this->logger->write("Invoice Controller : edit() : Failed to insert into table tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                            }
                            
                        } catch (Exception $e) {
                            $this->logger->write("Invoice Controller : edit() : Failed to insert into table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                        }
                        
                        
                    }
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The good details on invoice - " . $invoice->id . " have been edited by " . $this->f3->get('SESSION.username'));
                    if ($invoice->einvoiceid) {
                        $this->logger->write("Invoice Controller : edit() : This invoice is already uploaded", 'r');
                        self::$systemalert = "This invoice is already uploaded";
                    } else {
                        self::$systemalert = "The good details on invoice  - " . $invoice->id . " have been edited";
                        $this->logger->write("Invoice Controller : edit() : The good details on invoice  - " . $invoice->id . " have been edited", 'r');
                    }
                    
                } else {
                    $this->logger->write("Invoice Controller : edit() :No TAB was selected", 'r');
                    $this->f3->reroute('/invoice');
                }

            } else {
                $this->logger->write("Invoice Controller : edit() : The user is not allowed to perform this function", 'r');
                $this->f3->reroute('/forbidden');
            }
        } else { // ADD Operation: mainly handles the GENERAL parameters, as the rest of the parameters will be added using the EDIT option
            $operation = NULL; // tblevents
            $permission = 'CREATEINVOICE'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications
            
            $this->logger->write("Invoice Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $this->logger->write("Invoice Controller : edit() : Adding of invoice started.", 'r');
                
                $tcsdetails = new tcsdetails($this->db);
                $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
                
                $devicedetails = new devices($this->db);
                $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
                
                $this->f3->set('POST.erpinvoiceid', $this->f3->get('POST.erpinvoiceid'));
                $this->f3->set('POST.erpinvoiceno', $this->f3->get('POST.erpinvoiceno'));
                $this->f3->set('POST.deviceno', $devicedetails->deviceno);
                $this->f3->set('POST.operator', $this->f3->get('SESSION.username'));
                $this->f3->set('POST.currency', $this->f3->get('POST.currency'));
                $this->f3->set('POST.invoicetype', $this->f3->get('POST.invoicetype'));
                $this->f3->set('POST.invoicekind', $this->f3->get('POST.invoicekind'));
                $this->f3->set('POST.datasource', $this->f3->get('POST.datasource'));
                $this->f3->set('POST.invoiceindustrycode', $this->f3->get('POST.invoiceindustrycode'));
                $this->f3->set('POST.remarks', $this->f3->get('POST.remarks'));
                $this->f3->set('POST.deliveryTermsCode', $this->f3->get('POST.invoicedeliveryTermsCode'));
                
                
                $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                // @TODO check the params for empty/null values
                if (trim($this->f3->get('POST.currency')) !== '' || ! empty(trim($this->f3->get('POST.currency')))) {
                    try {
                        // Proceed & create
                        $invoice->add();
                        // $this->logger->write("Invoice Controller : edit() : A new invoice has been added", 'r');
                        try {
                            // retrieve the most recently inserted invoice
                            // @TODO place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                            $data = array();
                            $r = $this->db->exec(array(
                                'SELECT MAX(id) "id" FROM tblinvoices WHERE insertedby = ' . $this->f3->get('SESSION.id')
                            ));
                            foreach ($r as $obj) {
                                $data[] = $obj;
                            }
                            
                            // $this->logger->write("Invoice Controller : edit() : The invoice " . $data[0]['id'] . " has been added", 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The invoice id " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                            self::$systemalert = "The invoice id " . $data[0]['id'] . " has been added";
                            $id = $data[0]['id'];
                            $invoice->getByID($id);
                            
                            /**
                             * 1. Add a GROUPID for goods and store it in a field called gooddetailgroupid
                             * 1. Add a GROUPID for payments and store it in a field called paymentdetailgroupid
                             * 1. Add a GROUPID for tax details and store it in a field called taxdetailgroupid
                             */
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $gooddetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tblinvoices SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                } catch (Exception $e) {
                                    $this->logger->write("Invoice Controller : edit() : Failed to select from table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Invoice Controller : edit() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $paymentdetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tblinvoices SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                } catch (Exception $e) {
                                    $this->logger->write("Invoice Controller : edit() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Invoice Controller : edit() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $taxdetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tblinvoices SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                } catch (Exception $e) {
                                    $this->logger->write("Invoice Controller : edit() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Invoice Controller : edit() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            $invoice->getByID($id);//refresh
                        } catch (Exception $e) {
                            $this->logger->write("Invoice Controller : edit() : The operation to retrieve the most recently added invoice was not successful. The error messages is " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added invoice was not successful");
                            self::$systemalert = "The operation to retrieve the most recently added invoice was not successful";
                        }
                    } catch (Exception $e) {
                        $this->logger->write("Invoice Controller : edit() : The operation to add a invoice was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a invoice was not successful");
                        self::$systemalert = "The operation to add a invoice was not successful";
                        self::add();
                        exit();
                    }
                } else {
                    $this->logger->write("Invoice Controller : edit() : The user is not allowed to perform this function", 'r');
                    $this->f3->reroute('/forbidden');
                }
            } else { // some params are empty
                // ABORT MISSION
                self::add();
                exit();
            }
        }
        
        $invoicetype = new invoicetypes($this->db);
        $invoicetypes = $invoicetype->all();
        $this->f3->set('invoicetypes', $invoicetypes);
        
        $invoicekind = new invoicekinds($this->db);
        $invoicekinds = $invoicekind->all();
        $this->f3->set('invoicekinds', $invoicekinds);
        
        $datasource = new datasources($this->db);
        $datasources = $datasource->all();
        $this->f3->set('datasources', $datasources);
        
        $industry = new industries($this->db);
        $industries = $industry->all();
        $this->f3->set('industries', $industries);
        
        $currency = new currencies($this->db);
        $currencies = $currency->all();
        $this->f3->set('currencies', $currencies);
        
        $mode = new modes($this->db);
        $modes = $mode->all();
        $this->f3->set('modes', $modes);
        
        $buyertype = new buyertypes($this->db);
        $buyertypes = $buyertype->all();
        $this->f3->set('buyertypes', $buyertypes);
        
        $taxrate = new taxrates($this->db);
        $taxrates = $taxrate->all();
        $this->f3->set('taxrates', $taxrates);
        
        $org = new organisations($this->db);
        $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
        $this->f3->set('seller', $org);
        
        if($this->appsettings['PLATFORMODE'] == 'INT'){
            $buyer = new customers($this->db);
        } else {
            $buyer = new buyers($this->db);
        }
        $buyer->getByID($invoice->buyerid);
        $this->f3->set('buyer', $buyer);
        
        $product = new products($this->db);
        $products = $product->all();
        $this->f3->set('products', $products);
        
        $flag = new flags($this->db);
        $flags = $flag->all();
        $this->f3->set('flags', $flags);
        
        $paymentmode = new paymentmodes($this->db);
        $paymentmodes = $paymentmode->all();
        $this->f3->set('paymentmodes', $paymentmodes);
        
        $deliveryterm = new deliveryterms($this->db);
        $deliveryterms = $deliveryterm->all();
        $this->f3->set('deliveryterms', $deliveryterms);
        
        $this->f3->set('invoice', $invoice);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);
        
        $this->f3->set('systemalert', self::$systemalert);
        
        $this->f3->set('pagetitle', 'Edit Invoice | ' . $id);
        $this->f3->set('pagecontent', 'EditInvoice.htm');
        $this->f3->set('pagescripts', 'EditInvoiceFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    
    /**
     *	@name list
     *  @desc List invoices
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function list(){
        $operation = NULL; //tblevents
        $permission = 'VIEWINVOICES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Invoice Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Invoice Controller : list() : Processing list of invoices started", 'r');
            $id = trim((string)$this->f3->get('REQUEST.invoiceid'));
            
            $this->logger->write("Invoice Controller : list() : The invoice id is : " . $id, 'r');

            $isDataTablesRequest = ($this->f3->get('REQUEST.draw') !== NULL && $this->f3->get('REQUEST.start') !== NULL && $this->f3->get('REQUEST.length') !== NULL);

            if ($isDataTablesRequest) {
                $draw = (int)$this->f3->get('REQUEST.draw');
                $start = max(0, (int)$this->f3->get('REQUEST.start'));
                $length = (int)$this->f3->get('REQUEST.length');
                $length = ($length > 0 && $length <= 200)? $length : 10;

                $searchValue = trim((string)$this->f3->get('REQUEST.search.value'));
                $orderColumnIndex = (int)$this->f3->get('REQUEST.order.0.column');
                $orderDir = strtolower(trim((string)$this->f3->get('REQUEST.order.0.dir'))) === 'asc'? 'ASC' : 'DESC';

                $columnMap = array(
                    0 => 'i.id',
                    1 => 'i.einvoiceid',
                    2 => 'i.einvoicenumber',
                    3 => 'i.erpinvoiceid',
                    4 => 'i.erpinvoiceno',
                    5 => 'i.issueddate',
                    6 => 'i.currency',
                    7 => 'i.netamount',
                    8 => 'i.taxamount',
                    9 => 'i.grossamount',
                    10 => 'i.itemcount',
                    11 => 'i.modifieddt'
                );

                $orderBy = array_key_exists($orderColumnIndex, $columnMap)? $columnMap[$orderColumnIndex] : 'i.id';

                $where = '';
                if ($searchValue !== '') {
                    $searchEscaped = addslashes($searchValue);
                    $where = " WHERE (i.erpinvoiceno LIKE '%" . $searchEscaped . "%'"
                        . " OR i.erpinvoiceid LIKE '%" . $searchEscaped . "%'"
                        . " OR i.einvoiceid LIKE '%" . $searchEscaped . "%'"
                        . " OR i.einvoicenumber LIKE '%" . $searchEscaped . "%'"
                        . " OR i.currency LIKE '%" . $searchEscaped . "%')";
                }

                $countTotalSql = 'SELECT COUNT(*) "c" FROM tblinvoices i';
                $countFilteredSql = 'SELECT COUNT(*) "c" FROM tblinvoices i' . $where;

                $sql = 'SELECT  i.id "ID",
                        i.erpinvoiceid "ERP Invoice Id",
                        i.erpinvoiceno "ERP Invoice No",
                        i.issueddate "Issued Date",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.einvoicedatamatrixcode "QR Code",
                        i.einvoiceid "Id",
                        i.einvoicenumber "Number",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblinvoices i
                    LEFT JOIN tblusers s ON i.modifiedby = s.id'
                    . $where
                    . ' ORDER By ' . $orderBy . ' ' . $orderDir
                    . ' LIMIT ' . $start . ', ' . $length;

                try {
                    $countTotalRow = $this->db->exec($countTotalSql);
                    $countFilteredRow = $this->db->exec($countFilteredSql);
                    $dtls = $this->db->exec($sql);

                    $recordsTotal = isset($countTotalRow[0]['c'])? (int)$countTotalRow[0]['c'] : 0;
                    $recordsFiltered = isset($countFilteredRow[0]['c'])? (int)$countFilteredRow[0]['c'] : 0;

                    $this->logger->write('Invoice Controller : list() : DataTables mode - start=' . $start . ', length=' . $length . ', filtered=' . $recordsFiltered, 'r');

                    die(json_encode(array(
                        'draw' => $draw,
                        'recordsTotal' => $recordsTotal,
                        'recordsFiltered' => $recordsFiltered,
                        'data' => $dtls
                    )));
                } catch (Exception $e) {
                    $this->logger->write("Invoice Controller : list() : The operation to list paged invoices was not successful. The error message is " . $e->getMessage(), 'r');
                    die(json_encode(array(
                        'draw' => $draw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => array()
                    )));
                }
            }
            
            if ($id !== '' || !empty($id)) {
                
                //$subquery = " '%" . $id . "%' ";
                
                $sql = 'SELECT  i.id "ID",
                        i.erpinvoiceid "ERP Invoice Id",
                        i.erpinvoiceno "ERP Invoice No",
                        i.issueddate "Issued Date",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.einvoicedatamatrixcode "QR Code",
                        i.einvoiceid "Id",
                        i.einvoicenumber "Number",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblinvoices i
                    LEFT JOIN tblusers s ON i.modifiedby = s.id
                    WHERE i.id = ' . $id . '
                    ORDER By i.id DESC';
            } else {
                $sql = 'SELECT  i.id "ID",
                        i.erpinvoiceid "ERP Invoice Id",
                        i.erpinvoiceno "ERP Invoice No",
                        i.issueddate "Issued Date",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.einvoicedatamatrixcode "QR Code",
                        i.einvoiceid "Id",
                        i.einvoicenumber "Number",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblinvoices i
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
                $this->logger->write("Invoice Controller : list() : The operation to list the invoices was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Invoice Controller : index() : The user is not allowed to perform this function", 'r');
        }
                     
        die(json_encode($data));
    }
    
    /**
     *	@name searchinvoices
     *  @desc Search invoices
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function searchinvoices(){
        $operation = NULL; //tblevents
        $permission = 'VIEWINVOICES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Invoice Controller : searchinvoices() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Invoice Controller : searchinvoices() : Processing list of invoices started", 'r');
        
            
            $number = trim($this->f3->get('POST.number'));
            $this->logger->write("Invoice Controller : searchinvoices() : The invoice id is : " . $number, 'r');
            
            if ($number !== '' || !empty($number)) {
                
                $subquery = " '%" . $number . "%' ";
                
                $sql = 'SELECT  r.einvoiceid "ID",
                        r.einvoicenumber "Number",
                        r.disabled "Disabled"
                    FROM tblinvoices r
                    WHERE r.einvoicenumber LIKE ' . $subquery . '
                    ORDER BY r.einvoiceid DESC';
            } else {
                $sql = 'SELECT  r.einvoiceid "ID",
                        r.einvoicenumber "Number",
                        r.disabled "Disabled"
                    FROM tblinvoices r
                    ORDER BY r.einvoiceid DESC';
            }

            try {
                $dtls = $this->db->exec($sql);
                
                $this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
                
                $this->logger->write("Invoice Controller : searchinvoices() : The operation to search the invoices was successful.", 'r');
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : searchinvoices() : The operation to search the invoices was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Invoice Controller : searchinvoices() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }

    /**
     *  @name searchinvoicetypes
     *  @desc List invoice types for Select2 search
     *  @return JSON-encoded object
     **/
    function searchinvoicetypes(){
        $permission = 'VIEWINVOICES';
        $data = array();

        $this->logger->write("Invoice Controller : searchinvoicetypes() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $name = trim($this->f3->get('POST.name'));
            if ($name !== '' || ! empty($name)) {
                $subquery = " '%" . $name . "%' ";
                $sql = 'SELECT r.id "Id", r.code "Code", r.name "Name", r.description "Description", r.disabled "Disabled"
                        FROM tblinvoicetypes r
                        WHERE r.name LIKE ' . $subquery . ' OR r.code LIKE ' . $subquery . '
                        ORDER BY r.id DESC';
            } else {
                $sql = 'SELECT r.id "Id", r.code "Code", r.name "Name", r.description "Description", r.disabled "Disabled"
                        FROM tblinvoicetypes r
                        ORDER BY r.id DESC';
            }

            try {
                $dtls = $this->db->exec($sql);
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : searchinvoicetypes() : The operation was not successful. The error message is " . $e->getMessage(), 'r');
            }
        }

        die(json_encode($data));
    }

    /**
     *  @name searchinvoicekinds
     *  @desc List invoice kinds for Select2 search
     *  @return JSON-encoded object
     **/
    function searchinvoicekinds(){
        $permission = 'VIEWINVOICES';
        $data = array();

        $this->logger->write("Invoice Controller : searchinvoicekinds() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $name = trim($this->f3->get('POST.name'));
            if ($name !== '' || ! empty($name)) {
                $subquery = " '%" . $name . "%' ";
                $sql = 'SELECT r.id "Id", r.code "Code", r.name "Name", r.description "Description", r.disabled "Disabled"
                        FROM tblinvoicekinds r
                        WHERE r.name LIKE ' . $subquery . ' OR r.code LIKE ' . $subquery . '
                        ORDER BY r.id DESC';
            } else {
                $sql = 'SELECT r.id "Id", r.code "Code", r.name "Name", r.description "Description", r.disabled "Disabled"
                        FROM tblinvoicekinds r
                        ORDER BY r.id DESC';
            }

            try {
                $dtls = $this->db->exec($sql);
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : searchinvoicekinds() : The operation was not successful. The error message is " . $e->getMessage(), 'r');
            }
        }

        die(json_encode($data));
    }

    /**
     *  @name searchdatasources
     *  @desc List data sources for Select2 search
     *  @return JSON-encoded object
     **/
    function searchdatasources(){
        $permission = 'VIEWINVOICES';
        $data = array();

        $this->logger->write("Invoice Controller : searchdatasources() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $name = trim($this->f3->get('POST.name'));
            if ($name !== '' || ! empty($name)) {
                $subquery = " '%" . $name . "%' ";
                $sql = 'SELECT r.id "Id", r.code "Code", r.name "Name", r.description "Description", r.disabled "Disabled"
                        FROM tbldatasources r
                        WHERE r.name LIKE ' . $subquery . ' OR r.code LIKE ' . $subquery . '
                        ORDER BY r.id DESC';
            } else {
                $sql = 'SELECT r.id "Id", r.code "Code", r.name "Name", r.description "Description", r.disabled "Disabled"
                        FROM tbldatasources r
                        ORDER BY r.id DESC';
            }

            try {
                $dtls = $this->db->exec($sql);
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : searchdatasources() : The operation was not successful. The error message is " . $e->getMessage(), 'r');
            }
        }

        die(json_encode($data));
    }

    /**
     *  @name searchindustries
     *  @desc List industries for Select2 search
     *  @return JSON-encoded object
     **/
    function searchindustries(){
        $permission = 'VIEWINVOICES';
        $data = array();

        $this->logger->write("Invoice Controller : searchindustries() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $name = trim($this->f3->get('POST.name'));
            if ($name !== '' || ! empty($name)) {
                $subquery = " '%" . $name . "%' ";
                $sql = 'SELECT r.id "Id", r.code "Code", r.name "Name", r.description "Description", r.disabled "Disabled"
                        FROM tblindustries r
                        WHERE r.name LIKE ' . $subquery . ' OR r.code LIKE ' . $subquery . '
                        ORDER BY r.id DESC';
            } else {
                $sql = 'SELECT r.id "Id", r.code "Code", r.name "Name", r.description "Description", r.disabled "Disabled"
                        FROM tblindustries r
                        ORDER BY r.id DESC';
            }

            try {
                $dtls = $this->db->exec($sql);
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : searchindustries() : The operation was not successful. The error message is " . $e->getMessage(), 'r');
            }
        }

        die(json_encode($data));
    }
    
    
    /**
     *	@name uploadinvoice
     *  @desc upload an invoice to EFRIS
     *	@return
     *	@param 
     **/
    function uploadinvoice(){
        $operation = NULL; //tblevents
        $permission = 'UPLOADINVOICE'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.uploadinvoiceid');
        $invoice = new invoices($this->db);
        $invoice->getByID($id);
        $this->logger->write("Invoice Controller : uploadinvoice() : The invoice id is " . $this->f3->get('POST.uploadinvoiceid'), 'r');
        
        $this->logger->write("Invoice Controller : uploadinvoice() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            if ($invoice->einvoiceid) {
                $this->logger->write("Invoice Controller : uploadinvoice() : This invoice is already uploaded", 'r');
                self::$systemalert = "This invoice is already uploaded";
                $this->f3->set('systemalert', self::$systemalert);
                self::view($id);
            } else {
                $data = $this->util->uploadinvoice($this->f3->get('SESSION.id'), $id, $this->vatRegistered);//will return JSON.
                //var_dump($data);
            }
            
            
            
            $data = json_decode($data, true);
            //$this->logger->write("Invoice Controller : uploadinvoice() : The response content is: " . $data, 'r');
            //var_dump($data);
            
            
            if (isset($data['returnCode'])){
                $this->logger->write("Invoice Controller : fetchproduct() : The operation to upload the invoice not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to upload the invoice by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to upload the invoice by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
            } else {
                if (isset($data['basicInformation'])){
                    $antifakeCode = $data['basicInformation']['antifakeCode']; //32966911991799104051
                    $invoiceId = $data['basicInformation']['invoiceId']; //3257429764295992735
                    $invoiceNo = $data['basicInformation']['invoiceNo']; //3120012276043
                    
                    $issuedDate = $data['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                    $issuedDate = str_replace('/', '-', $issuedDate);//Replace / with -
                    $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                    
                    $issuedTime = $data['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                    $issuedTime = str_replace('/', '-', $issuedTime);//Replace / with -
                    $issuedTime = date("Y-m-d H:i:s", strtotime($issuedTime));
                    
                    $issuedDatePdf = $data['basicInformation']['issuedDatePdf']; //318/09/2020 17:14:12
                    $issuedDatePdf = str_replace('/', '-', $issuedDatePdf);//Replace / with -
                    $issuedDatePdf = date("Y-m-d H:i:s", strtotime($issuedDatePdf));
                    
                    $oriInvoiceId = $data['basicInformation']['oriInvoiceId'];//1
                    $isInvalid = $data['basicInformation']['isInvalid'];//1
                    $isRefund = $data['basicInformation']['isRefund'];//1
                    
                    $deviceNo = $data['basicInformation']['deviceNo'];
                    $invoiceIndustryCode = $data['basicInformation']['invoiceIndustryCode'];
                    $invoiceKind = $data['basicInformation']['invoiceKind'];
                    $invoiceType = $data['basicInformation']['invoiceType'];
                    $isBatch = $data['basicInformation']['isBatch'];
                    $operator = $data['basicInformation']['operator'];
                    
                    $currencyRate = $data['basicInformation']['currencyRate'];
                    
                    
                    try{
                        $this->db->exec(array('UPDATE tblinvoices SET antifakeCode = "' . $antifakeCode .
                                                                '", einvoiceid = "' . $invoiceId .
                                                                '", einvoicenumber = "' . $invoiceNo .
                                                                '", issueddate = "' . $issuedDate .
                                                                '", issueddatepdf = "' . $issuedDatePdf .
                                                                '", oriinvoiceid = "' . $oriInvoiceId .
                                                                '", isinvalid = ' . $isInvalid .
                                                                ', isrefund = ' . $isRefund .
                                                                ', issuedtime = "' . $issuedTime .
                                                                '", deviceno = "' . $deviceNo .
                                                                '", invoiceindustrycode = ' . $invoiceIndustryCode .
                                                                ', invoicekind = ' . $invoiceKind .
                                                                ', invoicetype = ' . $invoiceType .
                                                                ', isbatch = "' . $isBatch .
                                                                '", operator = "' . $operator .
                                                                '", currencyRate = ' . $currencyRate .
                                                                ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                ' WHERE id = ' . $id));
                        
                        $this->logger->write($this->db->log(TRUE), 'r');
                    } catch (Exception $e) {
                        $this->logger->write("Invoice Controller : uploadinvoice() : Failed to insert into the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                    }
                    
                }
                
                if (isset($data['sellerDetails'])){
                    /*"address":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                     "branchCode":"00",
                     "branchId":"912550336846912433",
                     "branchName":"FTS GROUP CONSULTING SERVICES LIMITED",
                     "businessName":"FTS GROUP CONSULTING SERVICES LIMITED",
                     "emailAddress":"editesti06@gmail.com",
                     "legalName":"FTS GROUP CONSULTING SERVICES LIMITED",
                     "linePhone":"256787695360",
                     "mobilePhone":"256782356088",
                     "ninBrn":"/80020002851201",
                     "placeOfBusiness":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                     "referenceNo":"21",
                     "tin":"1017918269"*/
                    
                    
                    $branchCode = !isset($data['sellerDetails']['branchCode'])? '' : $data['sellerDetails']['branchCode'];
                    $branchId = !isset($data['sellerDetails']['branchId'])? '' : $data['sellerDetails']['branchId'];
                    $referenceNo = !isset($data['sellerDetails']['referenceNo'])? '' : $data['sellerDetails']['referenceNo'];
                    
                    try{
                        $this->db->exec(array('UPDATE tblinvoices SET branchCode = "' . $branchCode .
                            '", branchId = "' . $branchId .
                            '", erpinvoiceno = "' . addslashes($referenceNo) .
                            '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                            ' WHERE id = ' . $id));
                        
                        $this->logger->write($this->db->log(TRUE), 'r');
                    } catch (Exception $e) {
                        $this->logger->write("Invoice Controller : uploadinvoice() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                    }
                }
                
                if (isset($data['summary'])){
                    $grossAmount = $data['summary']['grossAmount']; //832000
                    $itemCount = $data['summary']['itemCount']; //1
                    $netAmount = $data['summary']['netAmount']; //705084.75
                    $qrCode = $data['summary']['qrCode']; //020000001149IC1200122760430004F588000000C1A8450A20A021D534A1000121462A1000094968~MM INTERGRATED STEEL MILLS (UGANDA) LIMITED~Ediomu & Company~Kiboko Galv Corr. Sheet 36G
                    $taxAmount = $data['summary']['taxAmount'];//126915.25
                    $modeCode = $data['summary']['modeCode'];//0
                    
                    $mode = new modes($this->db);
                    $mode->getByCode($modeCode);
                    $modeName = $mode->name;//online
                    
                    $f = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
                    $grossAmountWords = $f->format($grossAmount);//two million
                    
                    try{
                        $this->db->exec(array('UPDATE tblinvoices SET grossamount = ' . $grossAmount . ', itemcount = ' . $itemCount . ', netamount = ' . $netAmount . ', einvoicedatamatrixcode = "' . addslashes($qrCode) . '", taxamount = ' . $taxAmount . ', modecode = "' . $modeCode . '", modename = "' . $modeName . '", grossamountword = "' . addslashes($grossAmountWords) . '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                        $this->logger->write($this->db->log(TRUE), 'r');
                    } catch (Exception $e) {
                        $this->logger->write("Invoice Controller : uploadinvoice() : Failed to insert into the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                    }
                }
                
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to upload the invoice by " . $this->f3->get('SESSION.username') . " was successful");
                self::$systemalert = "The operation to upload the invoice by " . $this->f3->get('SESSION.username') . " was successful";
            }
            
            //die($data);
        } else {
            $this->logger->write("Invoice Controller : uploadinvoice() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name syncefrisinvoices
     *  @desc sync invoices from EFRIS
     *	@return
     *	@param
     **/
    function syncefrisinvoices(){
        $operation = NULL; //tblevents
        $permission = 'SYNCINVOICES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Invoice Controller : syncefrisinvoices() : The invoice kind is " . $this->f3->get('POST.invoicekind'), 'r');
        $this->logger->write("Invoice Controller : syncefrisinvoices() : The operation type is " . $this->f3->get('POST.operationType'), 'r');
        
        
        if (trim($this->f3->get('POST.invoicekind')) !== '' || !empty(trim($this->f3->get('POST.invoicekind')))) {
            $invoicekind = $this->f3->get('POST.invoicekind');
        } else {
            $invoicekind = '1';//invoice
        }
        
        if (trim($this->f3->get('POST.operationType')) !== '' || !empty(trim($this->f3->get('POST.operationType')))) {
            $operationType = $this->f3->get('POST.operationType');
        } else {
            $operationType = 'sync';
        }
        
        if (trim($this->f3->get('POST.startdate')) !== '' || !empty(trim($this->f3->get('POST.startdate')))) {
            $startdate = $this->f3->get('POST.startdate');
            $startdate = date("Y-m-d", strtotime($startdate));
        } else {
            $startdate = date('Y-m-d');
        }
        
        if (trim($this->f3->get('POST.enddate')) !== '' || !empty(trim($this->f3->get('POST.enddate')))) {
            $enddate = $this->f3->get('POST.enddate');
            $enddate = date("Y-m-d", strtotime($enddate));
        } else {
            $enddate = date('Y-m-d');
        }
        
        
        $this->logger->write("Invoice Controller : syncefrisinvoices() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
 
            if ($operationType == 'update') {
                $this->logger->write("Invoice Controller : syncefrisinvoices() : Update Operation", 'r');
                
                $inv_check = $this->db->exec(array('SELECT id FROM tblinvoices WHERE issueddate BETWEEN \'' . $startdate . '\' AND \'' . $enddate . '\''));
                $this->logger->write($this->db->log(TRUE), 'r');
                
                if($inv_check){
                    $this->logger->write("Invoice Controller : syncefrisinvoices() : Invoices retrieved", 'r');
                    
                    $invoice = new invoices($this->db);
                    $id = NULL;
                    
                    foreach ($inv_check as $obj) {
                        $id = $obj['id'];
                        $this->logger->write("Invoice Controller : syncefrisinvoices() : Invoice Id: " . $id, 'r');
                        $invoice->getByID($id);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        $this->logger->write("Invoice Controller : syncefrisinvoices() : Invoice No: " . $invoice->einvoicenumber, 'r');
                        
                        $data = $this->util->downloadinvoice($this->f3->get('SESSION.id'), $id);//will return JSON.
                        $data = json_decode($data, true);
                        
                        
                        if (isset($data['returnCode'])){
                            $this->logger->write("Invoice Controller : syncefrisinvoices() : The operation to update the invoices not successful. The error message is " . $data['returnMessage'], 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update the invoice by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to update the invoices by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                        } else {
                            if (isset($data['basicInformation'])){
                                
                                $antifakeCode = $data['basicInformation']['antifakeCode']; //32966911991799104051
                                $invoiceId = $data['basicInformation']['invoiceId']; //3257429764295992735
                                $invoiceNo = $data['basicInformation']['invoiceNo']; //3120012276043
                                
                                $issuedDate = $data['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                                $issuedDate = str_replace('/', '-', $issuedDate);//Replace / with -
                                $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                                
                                $issuedTime = $data['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                                $issuedTime = str_replace('/', '-', $issuedTime);//Replace / with -
                                $issuedTime = date("Y-m-d H:i:s", strtotime($issuedTime));
                                
                                $issuedDatePdf = $data['basicInformation']['issuedDatePdf']; //318/09/2020 17:14:12
                                $issuedDatePdf = str_replace('/', '-', $issuedDatePdf);//Replace / with -
                                $issuedDatePdf = date("Y-m-d H:i:s", strtotime($issuedDatePdf));
                                
                                $oriInvoiceId = $data['basicInformation']['oriInvoiceId'];//1
                                $isInvalid = $data['basicInformation']['isInvalid'];//1
                                $isRefund = $data['basicInformation']['isRefund'];//1
                                
                                $deviceNo = $data['basicInformation']['deviceNo'];
                                $invoiceIndustryCode = $data['basicInformation']['invoiceIndustryCode'];
                                $invoiceKind = $data['basicInformation']['invoiceKind'];
                                $invoiceType = $data['basicInformation']['invoiceType'];
                                $isBatch = $data['basicInformation']['isBatch'];
                                $operator = $data['basicInformation']['operator'];
                                
                                $currencyRate = $data['basicInformation']['currencyRate'];
                                
                                
                                
                                try{
                                    $this->db->exec(array('UPDATE tblinvoices SET antifakeCode = "' . $antifakeCode .
                                        '", einvoiceid = "' . $invoiceId .
                                        '", einvoicenumber = "' . $invoiceNo .
                                        '", issueddate = "' . $issuedDate .
                                        '", issueddatepdf = "' . $issuedDatePdf .
                                        '", oriinvoiceid = "' . $oriInvoiceId .
                                        '", isinvalid = ' . $isInvalid .
                                        ', isrefund = ' . $isRefund .
                                        ', issuedtime = "' . $issuedTime .
                                        '", deviceno = "' . $deviceNo .
                                        '", invoiceindustrycode = ' . $invoiceIndustryCode .
                                        ', invoicekind = ' . $invoiceKind .
                                        ', invoicetype = ' . $invoiceType .
                                        ', isbatch = "' . $isBatch .
                                        '", operator = "' . $operator .
                                        '", currencyRate = ' . $currencyRate .
                                        ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                        ' WHERE id = ' . $id));
                                    
                                    $this->logger->write($this->db->log(TRUE), 'r');
                                } catch (Exception $e) {
                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                }
                                
                            }
                            
                            if (isset($data['summary'])){
                                $grossAmount = $data['summary']['grossAmount']; //832000
                                $itemCount = $data['summary']['itemCount']; //1
                                $netAmount = $data['summary']['netAmount']; //705084.75
                                $qrCode = $data['summary']['qrCode']; //020000001149IC1200122760430004F588000000C1A8450A20A021D534A1000121462A1000094968~MM INTERGRATED STEEL MILLS (UGANDA) LIMITED~Ediomu & Company~Kiboko Galv Corr. Sheet 36G
                                $taxAmount = $data['summary']['taxAmount'];//126915.25
                                $modeCode = $data['summary']['modeCode'];//0
                                
                                $mode = new modes($this->db);
                                $mode->getByCode($modeCode);
                                $modeName = $mode->name;//online
                                
                                $f = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
                                $grossAmountWords = $f->format($grossAmount);//two million
                                
                                try{
                                    $this->db->exec(array('UPDATE tblinvoices SET grossamount = ' . $grossAmount .
                                        ', itemcount = ' . $itemCount .
                                        ', netamount = ' . $netAmount .
                                        ', einvoicedatamatrixcode = "' . addslashes($qrCode) .
                                        '", taxamount = ' . $taxAmount .
                                        ', modecode = "' . $modeCode .
                                        '", modename = "' . $modeName .
                                        '", grossamountword = "' . addslashes($grossAmountWords) .
                                        '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                        ' WHERE id = ' . $id));
                                    
                                    $this->logger->write($this->db->log(TRUE), 'r');
                                } catch (Exception $e) {
                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                }
                            }
                            
                            if (isset($data['buyerDetails'])){
                                /*"buyerAddress":"16A MARKET STREET ROYAL COMPLEX KAMPALA KAMPALA KAMPALA CENTRAL DIVI KAMPALA CENTRAL DIVISION NAKASERO IV ",
                                 "buyerBusinessName":"GKK ROYAL HOTELS LIMITED",
                                 "buyerEmail":"nthakkar@ura.go.ug",
                                 "buyerLegalName":"GKK ROYAL HOTELS LIMITED",
                                 "buyerMobilePhone":"2560778497936",
                                 "buyerTin":"1000118576",
                                 "buyerType":"0",
                                 "dateFormat":"dd/MM/yyyy",
                                 "nowTime":"2021/05/23 21:53:32",
                                 "pageIndex":0,
                                 "pageNo":0,
                                 "pageSize":0,
                                 "timeFormat":"dd/MM/yyyy HH24:mi:ss"*/
                                
                                $buyerAddress = empty($data['buyerDetails']['buyerAddress'])? '' : $data['buyerDetails']['buyerAddress'];
                                $buyerBusinessName = empty($data['buyerDetails']['buyerBusinessName'])? '' : $data['buyerDetails']['buyerBusinessName'];
                                $buyerEmail = empty($data['buyerDetails']['buyerEmail'])? '' : $data['buyerDetails']['buyerEmail'];
                                $buyerLegalName = empty($data['buyerDetails']['buyerLegalName'])? '' : $data['buyerDetails']['buyerLegalName'];
                                $buyerMobilePhone = empty($data['buyerDetails']['buyerMobilePhone'])? '' : $data['buyerDetails']['buyerMobilePhone'];
                                $buyerTin = empty($data['buyerDetails']['buyerTin'])? '' : $data['buyerDetails']['buyerTin'];
                                $buyerType = empty($data['buyerDetails']['buyerType'])? 'NULL' : $data['buyerDetails']['buyerType'];
                                
                                $invoice->getByID($id); //Refresh invoice details
                                
                                $referenceno = $invoice->erpinvoiceno;
                                $referenceno = $referenceno . (empty($invoice->erpinvoiceid)? $invoice->id : strval($invoice->erpinvoiceid));
                                
                                $this->logger->write("Invoice Controller : syncefrisinvoices() : The buyer is: " . $invoice->buyerid, 'r');
                                if (!empty($invoice->buyerid)) {
                                    //if (trim($invoice->buyerid) !== '0' || trim($invoice->buyerid) !== '' || !empty(trim($invoice->buyerid))) {
                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : The buyer is already set", 'r');
                                    
                                    try{
                                        $this->db->exec(array('UPDATE tblbuyers SET address = "' . addslashes($buyerAddress) .
                                            '", businessname = "' . addslashes($buyerBusinessName) .
                                            '", emailaddress = "' . addslashes($buyerEmail) .
                                            '", legalname = "' . addslashes($buyerLegalName) .
                                            '", mobilephone = "' . addslashes($buyerMobilePhone) .
                                            '", referenceno = "' . addslashes($referenceno) .
                                            '", tin = "' . $buyerTin .
                                            '", type = ' . $buyerType .
                                            ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                            ' WHERE id = ' . $invoice->buyerid));
                                        
                                        $this->logger->write($this->db->log(TRUE), 'r');
                                    } catch (Exception $e) {
                                        $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to update the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                    }
                                } else {
                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : The buyer is NOT set", 'r');
                                    
                                    try{
                                        $this->db->exec(array('INSERT INTO tblbuyers
                                                                (address,
                                                                businessname,
                                                                emailaddress,
                                                                legalname,
                                                                mobilephone,
                                                                referenceno,
                                                                tin,
                                                                type,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                ("' . addslashes($buyerAddress) . '",
                                                                "' . addslashes($buyerBusinessName) . '",
                                                                "' . addslashes($buyerEmail) . '",
                                                                "' . addslashes($buyerLegalName) . '",
                                                                "' . addslashes($buyerMobilePhone) . '",
                                                                "' . addslashes($referenceno) . '",
                                                                "' . $buyerTin . '",
                                                                ' . $buyerType . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                        
                                        $this->logger->write($this->db->log(TRUE), 'r');
                                        
                                        
                                        
                                        try {
                                            if (trim($referenceno) !== '' || !empty(trim($referenceno))) {
                                                $b = array ();
                                                $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE TRIM(referenceno) = "' . $referenceno . '"'));
                                                
                                                foreach ( $r as $obj ) {
                                                    $b [] = $obj;
                                                }
                                                
                                                $buyerid = $b[0]['id'];
                                                
                                                $this->db->exec(array('UPDATE tblinvoices SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                            } else {
                                                ;
                                            }
                                            
                                        } catch (Exception $e) {
                                            $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to select from table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                    } catch (Exception $e) {
                                        $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to insert into the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                    }
                                }
                            }
                            
                            if (isset($data['goodsDetails'])){
                                /*"deemedFlag":"2",
                                 "discountFlag":"2",
                                 "exciseFlag":"2",
                                 "exciseTax":"0",
                                 "goodsCategoryId":"72152507",
                                 "goodsCategoryName":"Vinyl floor tile and sheet installation service",
                                 "item":"Iron Sheets",
                                 "itemCode":"Iron2021",
                                 "orderNumber":"0",
                                 "qty":"1",
                                 "tax":"1525.42",
                                 "taxRate":"0.18",
                                 "total":"10000",
                                 "unitOfMeasure":"MTR",
                                 "unitPrice":"10000"*/
                                
                                /*
                                "goodsDetails":[
                                {
                                    "exciseTax":"0",
                                    "goodsCategoryId":"22101511",
                                    "goodsCategoryName":"Compactors",
                                    "item":"FTS Compactors",
                                    "itemCode":"Test2",
                                    "orderNumber":"0",
                                    "qty":"6",
                                    "tax":"0",
                                    "taxRate":"0.18",
                                    "total":"270000",
                                    "unitOfMeasure":"NMB",
                                    "unitPrice":"45000"
                                }
                                ],*/
                                
                                if ($data['goodsDetails']) {
                                    
                                    try{
                                        $this->db->exec(array('DELETE FROM tblgooddetails WHERE groupid = ' . $invoice->gooddetailgroupid));
                                    } catch (Exception $e) {
                                        $this->logger->write("Invoice Controller : syncefrisinvoices() : The operation to delete from table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                                    }
                                    
                                    $invoice->getByID($id); //Refresh invoice details
                                    
                                    foreach($data['goodsDetails'] as $elem){
                                        
                                        try{
                                            
                                            $deemedFlag = empty($elem['deemedFlag'])? 'NULL' : $elem['deemedFlag'];
                                            $discountFlag = empty($elem['discountFlag'])? 'NULL' : $elem['discountFlag'];
                                            $exciseFlag = empty($elem['exciseFlag'])? 'NULL' : $elem['exciseFlag'];
                                            $exciseTax = empty($elem['exciseTax'])? 'NULL' : $elem['exciseTax'];
                                            $goodsCategoryId = empty($elem['goodsCategoryId'])? '' : $elem['goodsCategoryId'];
                                            $goodsCategoryName = empty($elem['goodsCategoryName'])? '' : $elem['goodsCategoryName'];
                                            $item = empty($elem['item'])? '' : $elem['item']; 
                                            $itemCode = empty($elem['itemCode'])? '' : $elem['itemCode'];
                                            $orderNumber = empty($elem['orderNumber'])? 'NULL' : $elem['orderNumber'];
                                            $qty = empty($elem['qty'])? 'NULL' : $elem['qty'];
                                            $tax = empty($elem['tax'])? 'NULL' : $elem['tax'];
                                            $taxRate = empty($elem['taxRate'])? 'NULL' : $elem['taxRate'];
                                            $total = empty($elem['total'])? 'NULL' : $elem['total'];
                                            $unitOfMeasure = empty($elem['unitOfMeasure'])? '' : $elem['unitOfMeasure'];
                                            $unitPrice = empty($elem['unitPrice'])? 'NULL' : $elem['unitPrice'];
                                            
                                            $measureunit = new measureunits($this->db);
                                            $measureunit->getByCode($unitOfMeasure);
                                            
                                            $unitofmeasurename = $measureunit->name;
                                            
                                            //Tax ID
                                            //Tax Category
                                            
                                            $this->db->exec(array('INSERT INTO tblgooddetails
                                                                (groupid,
                                                                deemedflag,
                                                                discountflag,
                                                                exciseflag,
                                                                excisetax,
                                                                goodscategoryid,
                                                                goodscategoryname,
                                                                item,
                                                                itemcode,
                                                                ordernumber,
                                                                qty,
                                                                tax,
                                                                taxrate,
                                                                total,
                                                                unitofmeasure,
                                                                unitprice,
                                                                unitofmeasurename,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $invoice->gooddetailgroupid . ',
                                                                ' . $deemedFlag . ',
                                                                ' . $discountFlag . ',
                                                                ' . $exciseFlag . ',
                                                                ' . $exciseTax . ',
                                                                "' . addslashes($goodsCategoryId) . '",
                                                                "' . addslashes($goodsCategoryName) . '",
                                                                "' . addslashes($item) . '",
                                                                "' . addslashes($itemCode) . '",
                                                                ' . $orderNumber . ',
                                                                ' . $qty . ',
                                                                ' . $tax . ',
                                                                ' . $taxRate . ',
                                                                ' . $total . ',
                                                                "' . addslashes($unitOfMeasure) . '",
                                                                ' . $unitPrice . ',
                                                                "' . addslashes($unitofmeasurename) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                        } catch (Exception $e) {
                                            $this->logger->write("Invoice Controller : syncefrisinvoices() : The operation to insert into table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                    }
                                    
                                } else {//NOTHING RETURNED BY API
                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : The API did not return anything", 'r');
                                }
                            }
                            
                            if (isset($data['payWay'])){
                                /*"dateFormat":"dd/MM/yyyy",
                                 "nowTime":"2021/05/23 14:14:18",
                                 "orderNumber":"0",
                                 "paymentAmount":"45000",
                                 "paymentMode":"102",
                                 "timeFormat":"dd/MM/yyyy HH24:mi:ss"*/
                                
                                if ($data['payWay']) {
                                    try{
                                        $this->db->exec(array('DELETE FROM tblpaymentdetails WHERE groupid = ' . $invoice->paymentdetailgroupid));
                                    } catch (Exception $e) {
                                        $this->logger->write("Invoice Controller : syncefrisinvoices() : The operation to delete from table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                    }
                                    
                                    foreach($data['payWay'] as $elem){
                                        
                                        try{
                                            
                                            
                                            $orderNumber = $elem['orderNumber'];
                                            $paymentAmount = $elem['paymentAmount'];
                                            $paymentMode = $elem['paymentMode'];
                                            
                                            $pm = new paymentmodes($this->db);
                                            $pm->getByCode($paymentMode);
                                            
                                            $paymentmodename = $pm->name;
                                            
                                            $this->db->exec(array('INSERT INTO tblpaymentdetails
                                                                (groupid,
                                                                ordernumber,
                                                                paymentamount,
                                                                paymentmode,
                                                                paymentmodename,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $invoice->paymentdetailgroupid . ',
                                                                ' . $orderNumber . ',
                                                                ' . $paymentAmount . ',
                                                                ' . $paymentMode . ',
                                                                "' . addslashes($paymentmodename) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                        } catch (Exception $e) {
                                            $this->logger->write("Invoice Controller : syncefrisinvoices() : The operation to insert into table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                    }
                                    
                                } else {//NOTHING RETURNED BY API
                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : The API did not return anything", 'r');
                                }
                            }
                            
                            
                            
                            if (isset($data['sellerDetails'])){
                                /*"address":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                                 "branchCode":"00",
                                 "branchId":"912550336846912433",
                                 "branchName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                 "businessName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                 "emailAddress":"editesti06@gmail.com",
                                 "legalName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                 "linePhone":"256787695360",
                                 "mobilePhone":"256782356088",
                                 "ninBrn":"/80020002851201",
                                 "placeOfBusiness":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                                 "referenceNo":"21",
                                 "tin":"1017918269"*/
                                
                                $branchCode = $data['sellerDetails']['branchCode'];
                                $branchId = $data['sellerDetails']['branchId'];
                                $referenceNo = $data['sellerDetails']['referenceNo'];
                                
                                
                                
                                try{
                                    $this->db->exec(array('UPDATE tblinvoices SET branchCode = "' . $branchCode .
                                        '", branchId = "' . $branchId .
                                        '", erpinvoiceno = "' . addslashes($referenceNo) .
                                        '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                        ' WHERE id = ' . $id));
                                    
                                    $this->logger->write($this->db->log(TRUE), 'r');
                                } catch (Exception $e) {
                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                }
                            }
                            
                            if (isset($data['taxDetails'])){
                                /*"taxCategory":"Standard",
                                 "netAmount":"123389830.51",
                                 "taxRate":"0.18",
                                 "taxAmount":"22210169.49",
                                 "grossAmount":"145600000.00",
                                 "exciseUnit":"",
                                 "exciseCurrency":"",
                                 "taxRateName":"18%"*/
                                
                                if ($data['taxDetails']) {
                                    try{
                                        $this->db->exec(array('DELETE FROM tbltaxdetails WHERE groupid = ' . $invoice->taxdetailgroupid));
                                    } catch (Exception $e) {
                                        $this->logger->write("Invoice Controller : syncefrisinvoices() : The operation to delete from table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                    }
                                    
                                    foreach($data['taxDetails'] as $elem){
                                        
                                        try{
                                            
                                            
                                            $taxCategory = $elem['taxCategory'];
                                            $netAmount = $elem['netAmount'];
                                            $taxRate = $elem['taxRate'];
                                            $taxAmount = $elem['taxAmount'];
                                            $grossAmount = $elem['grossAmount'];
                                            $exciseUnit = $elem['exciseUnit'];
                                            $exciseCurrency = $elem['exciseCurrency'];
                                            $taxRateName = $elem['taxRateName'];
                                            
                                            $tr = new taxrates($this->db);
                                            $tr->getByName($taxRateName);
                                            
                                            $taxdescription = $tr->description;//A: Standard (18%)
                                            
                                            $this->db->exec(array('INSERT INTO tbltaxdetails
                                                                (groupid,
                                                                goodid,
                                                                taxcategory,
                                                                netamount,
                                                                taxrate,
                                                                taxamount,
                                                                grossamount,
                                                                exciseunit,
                                                                excisecurrency,
                                                                taxratename,
                                                                taxdescription,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $invoice->taxdetailgroupid . ', 0,
                                                                "' . addslashes($taxCategory) . '",
                                                                ' . $netAmount . ',
                                                                ' . $taxRate . ',
                                                                ' . $taxAmount . ',
                                                                ' . $grossAmount . ',
                                                                "' . addslashes($exciseUnit) . '",
                                                                "' . addslashes($exciseCurrency) . '",
                                                                "' . addslashes($taxRateName) . '",
                                                                "' . addslashes($taxdescription) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                        } catch (Exception $e) {
                                            $this->logger->write("Invoice Controller : syncefrisinvoices() : The operation to insert into table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                    }
                                    
                                } else {//NOTHING RETURNED BY API
                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : The API did not return anything", 'r');
                                }
                            }
                            
                            
                            
                            
                        }
                    }
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update the invoice by " . $this->f3->get('SESSION.username') . " was successful");
                    self::$systemalert = "The operation to update the invoices by " . $this->f3->get('SESSION.username') . " was successful";
                } else {

                    $this->logger->write("Invoice Controller : syncefrisinvoices() : No invoices were retrieved", 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update invoices by " . $this->f3->get('SESSION.username') . " was not successful");
                    self::$systemalert = "The operation to update invoices by " . $this->f3->get('SESSION.username') . " was not successful. No invoices were retrieved.";
                    
                }
            }else {
                $this->logger->write("Invoice Controller : syncefrisinvoices() : Sync Operation", 'r');
                
                $pageNo = 1;
                $pageSize = 90;
                $pageCount = 1;
                
                do {
                    $data = $this->util->syncefrisinvoices($this->f3->get('SESSION.id'), $invoicekind, $startdate, $enddate, $pageNo, $pageSize);//will return JSON.
                    
                    $data = json_decode($data, true);
                    
                    if(isset($data['page'])){
                        $pageCount = $data['page']['pageCount'];
                        
                        $pageNo = $pageNo + 1;
                    }
                    
                    if (isset($data['returnCode'])){
                        $this->logger->write("Invoice Controller : syncefrisinvoices() : The operation to sync invoices not successful. The error message is " . $data['returnMessage'], 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync invoices by " . $this->f3->get('SESSION.username') . " was not successful");
                        self::$systemalert = "The operation to sync invoices by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                    } else {
                        
                        
                        if ($data) {
                            
                            
                            if(isset($data['records'])){
                                $invoice = new invoices($this->db);
                                
                                foreach($data['records'] as $elem){
                                    /*
                                     "businessName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                     "buyerBusinessName":"Mr. FRANCIS LUBANGA",
                                     "buyerLegalName":"Mr. FRANCIS LUBANGA",
                                     "buyerTin":"1000562249",
                                     "currency":"UGX",
                                     "dataSource":"106",
                                     "dateFormat":"dd/MM/yyyy",
                                     "grossAmount":"180000",
                                     "id":"198722379177211900",
                                     "invoiceNo":"320017773853",
                                     "issuedDate":"23/05/2021 17:51:51",
                                     "legalName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                     "nowTime":"2021/05/23 20:58:52",
                                     "pageIndex":0,
                                     "pageNo":0,
                                     "pageSize":0,
                                     "tin":"1017918269"
                                     */
                                    
                                    $invoiceId = $elem['id']; //3257429764295992735
                                    $invoiceNo = $elem['invoiceNo']; //3120012276043
                                    
                                    $issuedDate = $elem['issuedDate']; //18/09/2020 17:14:12
                                    $issuedDate = str_replace('/', '-', $issuedDate);//Replace / with -
                                    $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                                    
                                    $issuedTime = $elem['issuedDate']; //18/09/2020 17:14:12
                                    $issuedTime = str_replace('/', '-', $issuedTime);//Replace / with -
                                    $issuedTime = date("Y-m-d H:i:s", strtotime($issuedTime));
                                    
                                    $currency = $elem['currency'];//1
                                    $dataSource = $elem['dataSource'];//1
                                    
                                    $invoice->getByInvoiceNo($invoiceNo);
                                    
                                    if ($invoice->dry()) {
                                        $this->logger->write("Invoice Controller : syncefrisinvoices() : The invoice does not exist", 'r');
                                        
                                        try{
                                            
                                            /**
                                             * 1. Insert the details into the tblinvoices
                                             * 2. Retrive the record inserted.
                                             * 3. Generate the following details
                                             * 3.3. Good details group
                                             * 3.4. Payment details group
                                             * 3.5. Tax details group
                                             * 4. Update the invoice record with these details
                                             */
                                            
                                            $this->db->exec(array('INSERT INTO tblinvoices
                                                                (einvoiceid,
                                                                einvoicenumber,
                                                                issueddate,
                                                                datasource,
                                                                currency,
                                                                sellerid,
                                                                issuedtime,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                ("' . $invoiceId . '",
                                                                "' . $invoiceNo . '",
                                                                "' . $issuedDate . '",
                                                                ' . $dataSource . ',
                                                                "' . $currency . '",
                                                                ' . $this->appsettings['SELLER_RECORD_ID'] . ',
                                                                "' . $issuedTime . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                            
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                            
                                            //Retrieve the now inserted invoice
                                            $invoice->getByInvoiceNo($invoiceNo);
                                            
                                            $id = $invoice->id;
                                            
                                            /**
                                             * 1. Add a GROUPID for goods and store it in a field called gooddetailgroupid
                                             * 1. Add a GROUPID for payments and store it in a field called paymentdetailgroupid
                                             * 1. Add a GROUPID for tax details and store it in a field called taxdetailgroupid
                                             */
                                            
                                            try {
                                                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;
                                                
                                                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                try {
                                                    $pg = array ();
                                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                                    
                                                    foreach ( $r as $obj ) {
                                                        $pg [] = $obj;
                                                    }
                                                    
                                                    $gooddetailgroupid = $pg[0]['id'];
                                                    $this->db->exec(array('UPDATE tblinvoices SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to select from table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            try {
                                                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;
                                                
                                                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                try {
                                                    $pg = array ();
                                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                                    
                                                    foreach ( $r as $obj ) {
                                                        $pg [] = $obj;
                                                    }
                                                    
                                                    $paymentdetailgroupid = $pg[0]['id'];
                                                    $this->db->exec(array('UPDATE tblinvoices SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            try {
                                                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;
                                                
                                                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                try {
                                                    $pg = array ();
                                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                                    
                                                    foreach ( $r as $obj ) {
                                                        $pg [] = $obj;
                                                    }
                                                    
                                                    $taxdetailgroupid = $pg[0]['id'];
                                                    $this->db->exec(array('UPDATE tblinvoices SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                        } catch (Exception $e) {
                                            $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to insert into the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                        
                                    } else {
                                        $this->logger->write("Invoice Controller : syncefrisinvoices() : The invoice exists", 'r');
                                        
                                        try{
                                            $this->db->exec(array('UPDATE tblinvoices SET einvoiceid = "' . $invoiceId .
                                                '", einvoicenumber = "' . $invoiceNo .
                                                '", issueddate = "' . $issuedDate .
                                                '", datasource = ' . $dataSource .
                                                ', currency = "' . $currency .
                                                '", issuedtime = "' . $issuedTime .
                                                '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                ' WHERE einvoicenumber = "' . $invoiceNo . '"'));
                                            
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                        } catch (Exception $e) {
                                            $this->logger->write("Invoice Controller : syncefrisinvoices() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                        }
                                    }
                                }
                            }
                            
                        } else {//NOTHING RETURNED BY API
                            $this->logger->write("Invoice Controller : syncefrisinvoices() : The API did not return anything", 'r');
                        }
                        
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync invoices by " . $this->f3->get('SESSION.username') . " was successful");
                        self::$systemalert = "The operation to sync invoices by " . $this->f3->get('SESSION.username') . " was successful";
                    }
                } while ($pageNo <= $pageCount);
            }

            //die($data);
        } else {
            $this->logger->write("Invoice Controller : syncefrisinvoices() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
    
    /**
     *	@name downloadinvoice
     *  @desc download an invoice to EFRIS
     *	@return
     *	@param
     **/
    function downloadinvoice(){
        $operation = NULL; //tblevents
        $permission = 'DOWNLOADINVOICE'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.downloadinvoiceid');
        $invoice = new invoices($this->db);
        $invoice->getByID($id);
        $this->logger->write("Invoice Controller : downloadinvoice() : The invoice id is " . $this->f3->get('POST.downloadinvoiceid'), 'r');
        
        $this->logger->write("Invoice Controller : downloadinvoice() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            if($invoice->einvoicenumber){
                $data = $this->util->downloadinvoice($this->f3->get('SESSION.id'), $id);//will return JSON.
                
                
                
                $data = json_decode($data, true);
                //$this->logger->write("Invoice Controller : downloadinvoice() : The response content is: " . $data, 'r');
                //var_dump($data);
                
                
                if (isset($data['returnCode'])){
                    $this->logger->write("Invoice Controller : downloadinvoice() : The operation to download the invoice not successful. The error message is " . $data['returnMessage'], 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the invoice by " . $this->f3->get('SESSION.username') . " was not successful");
                    self::$systemalert = "The operation to download the invoice by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                } else {
                    if (isset($data['basicInformation'])){
                        
                        $antifakeCode = $data['basicInformation']['antifakeCode']; //32966911991799104051
                        $invoiceId = $data['basicInformation']['invoiceId']; //3257429764295992735
                        $invoiceNo = $data['basicInformation']['invoiceNo']; //3120012276043
                        
                        $issuedDate = $data['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                        $issuedDate = str_replace('/', '-', $issuedDate);//Replace / with -
                        $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                        
                        $issuedTime = $data['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                        $issuedTime = str_replace('/', '-', $issuedTime);//Replace / with -
                        $issuedTime = date("Y-m-d H:i:s", strtotime($issuedTime));
                        
                        $issuedDatePdf = $data['basicInformation']['issuedDatePdf']; //318/09/2020 17:14:12
                        $issuedDatePdf = str_replace('/', '-', $issuedDatePdf);//Replace / with -
                        $issuedDatePdf = date("Y-m-d H:i:s", strtotime($issuedDatePdf));
                        
                        $oriInvoiceId = $data['basicInformation']['oriInvoiceId'];//1
                        $isInvalid = $data['basicInformation']['isInvalid'];//1
                        $isRefund = $data['basicInformation']['isRefund'];//1
                        
                        $deviceNo = $data['basicInformation']['deviceNo'];
                        $invoiceIndustryCode = $data['basicInformation']['invoiceIndustryCode'];
                        $invoiceKind = $data['basicInformation']['invoiceKind'];
                        $invoiceType = $data['basicInformation']['invoiceType'];
                        $isBatch = $data['basicInformation']['isBatch'];
                        $operator = $data['basicInformation']['operator'];
                        
                        $currencyRate = $data['basicInformation']['currencyRate'];
                        
                        
                        
                        try{
                            $this->db->exec(array('UPDATE tblinvoices SET antifakeCode = "' . $antifakeCode .
                                '", einvoiceid = "' . $invoiceId .
                                '", einvoicenumber = "' . $invoiceNo .
                                '", issueddate = "' . $issuedDate .
                                '", issueddatepdf = "' . $issuedDatePdf .
                                '", oriinvoiceid = "' . $oriInvoiceId .
                                '", isinvalid = ' . $isInvalid .
                                ', isrefund = ' . $isRefund .
                                ', issuedtime = "' . $issuedTime .
                                '", deviceno = "' . $deviceNo .
                                '", invoiceindustrycode = ' . $invoiceIndustryCode .
                                ', invoicekind = ' . $invoiceKind .
                                ', invoicetype = ' . $invoiceType .
                                ', isbatch = "' . $isBatch .
                                '", operator = "' . $operator .
                                '", currencyRate = ' . $currencyRate .
                                ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                ' WHERE id = ' . $id));
                            
                            $this->logger->write($this->db->log(TRUE), 'r');
                        } catch (Exception $e) {
                            $this->logger->write("Invoice Controller : downloadinvoice() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                        }
                        
                    }
                    
                    if (isset($data['summary'])){
                        $grossAmount = $data['summary']['grossAmount']; //832000
                        $itemCount = $data['summary']['itemCount']; //1
                        $netAmount = $data['summary']['netAmount']; //705084.75
                        $qrCode = $data['summary']['qrCode']; //020000001149IC1200122760430004F588000000C1A8450A20A021D534A1000121462A1000094968~MM INTERGRATED STEEL MILLS (UGANDA) LIMITED~Ediomu & Company~Kiboko Galv Corr. Sheet 36G
                        $taxAmount = $data['summary']['taxAmount'];//126915.25
                        $modeCode = $data['summary']['modeCode'];//0
                        
                        $mode = new modes($this->db);
                        $mode->getByCode($modeCode);
                        $modeName = $mode->name;//online
                        
                        $f = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
                        $grossAmountWords = $f->format($grossAmount);//two million
                        
                        try{
                            $this->db->exec(array('UPDATE tblinvoices SET grossamount = ' . $grossAmount .
                                ', itemcount = ' . $itemCount .
                                ', netamount = ' . $netAmount .
                                ', einvoicedatamatrixcode = "' . addslashes($qrCode) .
                                '", taxamount = ' . $taxAmount .
                                ', modecode = "' . $modeCode .
                                '", modename = "' . $modeName .
                                '", grossamountword = "' . addslashes($grossAmountWords) .
                                '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                ' WHERE id = ' . $id));
                            
                            $this->logger->write($this->db->log(TRUE), 'r');
                        } catch (Exception $e) {
                            $this->logger->write("Invoice Controller : downloadinvoice() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                    
                    if (isset($data['buyerDetails'])){
                        /*"buyerAddress":"16A MARKET STREET ROYAL COMPLEX KAMPALA KAMPALA KAMPALA CENTRAL DIVI KAMPALA CENTRAL DIVISION NAKASERO IV ",
                         "buyerBusinessName":"GKK ROYAL HOTELS LIMITED",
                         "buyerEmail":"nthakkar@ura.go.ug",
                         "buyerLegalName":"GKK ROYAL HOTELS LIMITED",
                         "buyerMobilePhone":"2560778497936",
                         "buyerTin":"1000118576",
                         "buyerType":"0",
                         "dateFormat":"dd/MM/yyyy",
                         "nowTime":"2021/05/23 21:53:32",
                         "pageIndex":0,
                         "pageNo":0,
                         "pageSize":0,
                         "timeFormat":"dd/MM/yyyy HH24:mi:ss"*/
                        
                        $buyerAddress = $data['buyerDetails']['buyerAddress'];
                        $buyerBusinessName = $data['buyerDetails']['buyerBusinessName'];
                        $buyerEmail = $data['buyerDetails']['buyerEmail'];
                        $buyerLegalName = $data['buyerDetails']['buyerLegalName'];
                        $buyerMobilePhone = $data['buyerDetails']['buyerMobilePhone'];
                        $buyerTin = $data['buyerDetails']['buyerTin'];
                        $buyerType = $data['buyerDetails']['buyerType'];
                        
                        $invoice->getByID($id); //Refresh invoice details
                        
                        $referenceno = $invoice->erpinvoiceno;
                        $referenceno = $referenceno . (empty($invoice->erpinvoiceid)? $invoice->id : strval($invoice->erpinvoiceid));
                        
                        $this->logger->write("Invoice Controller : downloadinvoice() : The buyer is: " . $invoice->buyerid, 'r');
                        if (!empty($invoice->buyerid)) {
                            //if (trim($invoice->buyerid) !== '0' || trim($invoice->buyerid) !== '' || !empty(trim($invoice->buyerid))) {
                            $this->logger->write("Invoice Controller : downloadinvoice() : The buyer is already set", 'r');
                            
                            try{
                                $this->db->exec(array('UPDATE tblbuyers SET address = "' . addslashes($buyerAddress) .
                                    '", businessname = "' . addslashes($buyerBusinessName) .
                                    '", emailaddress = "' . addslashes($buyerEmail) .
                                    '", legalname = "' . addslashes($buyerLegalName) .
                                    '", mobilephone = "' . addslashes($buyerMobilePhone) .
                                    '", referenceno = "' . addslashes($referenceno) .
                                    '", tin = "' . $buyerTin .
                                    '", type = ' . $buyerType .
                                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                    ' WHERE id = ' . $invoice->buyerid));
                                
                                $this->logger->write($this->db->log(TRUE), 'r');
                            } catch (Exception $e) {
                                $this->logger->write("Invoice Controller : downloadinvoice() : Failed to update the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                            }
                        } else {
                            $this->logger->write("Invoice Controller : downloadinvoice() : The buyer is NOT set", 'r');
                            
                            try{
                                $this->db->exec(array('INSERT INTO tblbuyers
                                                                (address,
                                                                businessname,
                                                                emailaddress,
                                                                legalname,
                                                                mobilephone,
                                                                referenceno,
                                                                tin,
                                                                type,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                ("' . addslashes($buyerAddress) . '",
                                                                "' . addslashes($buyerBusinessName) . '",
                                                                "' . addslashes($buyerEmail) . '",
                                                                "' . addslashes($buyerLegalName) . '",
                                                                "' . addslashes($buyerMobilePhone) . '",
                                                                "' . addslashes($referenceno) . '",
                                                                "' . $buyerTin . '",
                                                                ' . $buyerType . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                
                                $this->logger->write($this->db->log(TRUE), 'r');
                                
                                
                                
                                try {
                                    if (trim($referenceno) !== '' || !empty(trim($referenceno))) {
                                        $b = array ();
                                        $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE TRIM(referenceno) = "' . $referenceno . '"'));
                                        
                                        foreach ( $r as $obj ) {
                                            $b [] = $obj;
                                        }
                                        
                                        $buyerid = $b[0]['id'];
                                        
                                        $this->db->exec(array('UPDATE tblinvoices SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    } else {
                                        ;
                                    }
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Invoice Controller : downloadinvoice() : Failed to select from table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                }
                                
                            } catch (Exception $e) {
                                $this->logger->write("Invoice Controller : downloadinvoice() : Failed to insert into the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                            }
                        }
                    }
                    
                    if (isset($data['goodsDetails'])){
                        /*"deemedFlag":"2",
                         "discountFlag":"2",
                         "exciseFlag":"2",
                         "exciseTax":"0",
                         "goodsCategoryId":"72152507",
                         "goodsCategoryName":"Vinyl floor tile and sheet installation service",
                         "item":"Iron Sheets",
                         "itemCode":"Iron2021",
                         "orderNumber":"0",
                         "qty":"1",
                         "tax":"1525.42",
                         "taxRate":"0.18",
                         "total":"10000",
                         "unitOfMeasure":"MTR",
                         "unitPrice":"10000"*/
                        
                        if ($data['goodsDetails']) {
                            
                            try{
                                $this->db->exec(array('DELETE FROM tblgooddetails WHERE groupid = ' . $invoice->gooddetailgroupid));
                            } catch (Exception $e) {
                                $this->logger->write("Invoice Controller : downloadinvoice() : The operation to delete from table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            $invoice->getByID($id); //Refresh invoice details
                            
                            foreach($data['goodsDetails'] as $elem){
                                
                                try{
                                    
                                    $deemedFlag = $elem['deemedFlag'];
                                    $discountFlag = $elem['discountFlag'];
                                    $exciseFlag = $elem['exciseFlag'];
                                    $exciseTax = $elem['exciseTax'];
                                    $goodsCategoryId = $elem['goodsCategoryId'];
                                    $goodsCategoryName = $elem['goodsCategoryName'];
                                    $item = $elem['item'];
                                    $itemCode = $elem['itemCode'];
                                    $orderNumber = $elem['orderNumber'];
                                    $qty = $elem['qty'];
                                    $tax = $elem['tax'];
                                    $taxRate = $elem['taxRate'];
                                    $total = $elem['total'];
                                    $unitOfMeasure = $elem['unitOfMeasure'];
                                    $unitPrice = $elem['unitPrice'];
                                    
                                    $measureunit = new measureunits($this->db);
                                    $measureunit->getByCode($unitOfMeasure);
                                    
                                    $unitofmeasurename = $measureunit->name;
                                    
                                    //Tax ID
                                    //Tax Category
                                    
                                    $this->db->exec(array('INSERT INTO tblgooddetails
                                                                (groupid,
                                                                deemedflag,
                                                                discountflag,
                                                                exciseflag,
                                                                excisetax,
                                                                goodscategoryid,
                                                                goodscategoryname,
                                                                item,
                                                                itemcode,
                                                                ordernumber,
                                                                qty,
                                                                tax,
                                                                taxrate,
                                                                total,
                                                                unitofmeasure,
                                                                unitprice,
                                                                unitofmeasurename,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $invoice->gooddetailgroupid . ',
                                                                ' . $deemedFlag . ',
                                                                ' . $discountFlag . ',
                                                                ' . $exciseFlag . ',
                                                                ' . $exciseTax . ',
                                                                "' . addslashes($goodsCategoryId) . '",
                                                                "' . addslashes($goodsCategoryName) . '",
                                                                "' . addslashes($item) . '",
                                                                "' . addslashes($itemCode) . '",
                                                                ' . $orderNumber . ',
                                                                ' . $qty . ',
                                                                ' . $tax . ',
                                                                ' . $taxRate . ',
                                                                ' . $total . ',
                                                                "' . addslashes($unitOfMeasure) . '",
                                                                ' . $unitPrice . ',
                                                                "' . addslashes($unitofmeasurename) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                } catch (Exception $e) {
                                    $this->logger->write("Invoice Controller : downloadinvoice() : The operation to insert into table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                                }
                                
                            }
                            
                        } else {//NOTHING RETURNED BY API
                            $this->logger->write("Invoice Controller : downloadinvoice() : The API did not return anything", 'r');
                        }
                    }
                    
                    if (isset($data['payWay'])){
                        /*"dateFormat":"dd/MM/yyyy",
                         "nowTime":"2021/05/23 14:14:18",
                         "orderNumber":"0",
                         "paymentAmount":"45000",
                         "paymentMode":"102",
                         "timeFormat":"dd/MM/yyyy HH24:mi:ss"*/
                        
                        if ($data['payWay']) {
                            try{
                                $this->db->exec(array('DELETE FROM tblpaymentdetails WHERE groupid = ' . $invoice->paymentdetailgroupid));
                            } catch (Exception $e) {
                                $this->logger->write("Invoice Controller : downloadinvoice() : The operation to delete from table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            foreach($data['payWay'] as $elem){
                                
                                try{
                                    
                                    
                                    $orderNumber = $elem['orderNumber'];
                                    $paymentAmount = $elem['paymentAmount'];
                                    $paymentMode = $elem['paymentMode'];
                                    
                                    $pm = new paymentmodes($this->db);
                                    $pm->getByCode($paymentMode);
                                    
                                    $paymentmodename = $pm->name;
                                    
                                    $this->db->exec(array('INSERT INTO tblpaymentdetails
                                                                (groupid,
                                                                ordernumber,
                                                                paymentamount,
                                                                paymentmode,
                                                                paymentmodename,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $invoice->paymentdetailgroupid . ',
                                                                ' . $orderNumber . ',
                                                                ' . $paymentAmount . ',
                                                                ' . $paymentMode . ',
                                                                "' . addslashes($paymentmodename) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                } catch (Exception $e) {
                                    $this->logger->write("Invoice Controller : downloadinvoice() : The operation to insert into table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                }
                                
                            }
                            
                        } else {//NOTHING RETURNED BY API
                            $this->logger->write("Invoice Controller : downloadinvoice() : The API did not return anything", 'r');
                        }
                    }
                    
                    
                    
                    if (isset($data['sellerDetails'])){
                        /*"address":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                         "branchCode":"00",
                         "branchId":"912550336846912433",
                         "branchName":"FTS GROUP CONSULTING SERVICES LIMITED",
                         "businessName":"FTS GROUP CONSULTING SERVICES LIMITED",
                         "emailAddress":"editesti06@gmail.com",
                         "legalName":"FTS GROUP CONSULTING SERVICES LIMITED",
                         "linePhone":"256787695360",
                         "mobilePhone":"256782356088",
                         "ninBrn":"/80020002851201",
                         "placeOfBusiness":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                         "referenceNo":"21",
                         "tin":"1017918269"*/
                        
                        $branchCode = $data['sellerDetails']['branchCode'];
                        $branchId = $data['sellerDetails']['branchId'];
                        $referenceNo = $data['sellerDetails']['referenceNo'];
                        
                        
                        
                        try{
                            $this->db->exec(array('UPDATE tblinvoices SET branchCode = "' . $branchCode .
                                '", branchId = "' . $branchId .
                                '", erpinvoiceno = "' . addslashes($referenceNo) .
                                '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                ' WHERE id = ' . $id));
                            
                            $this->logger->write($this->db->log(TRUE), 'r');
                        } catch (Exception $e) {
                            $this->logger->write("Invoice Controller : downloadinvoice() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                    
                    if (isset($data['taxDetails'])){
                        /*"taxCategory":"Standard",
                         "netAmount":"123389830.51",
                         "taxRate":"0.18",
                         "taxAmount":"22210169.49",
                         "grossAmount":"145600000.00",
                         "exciseUnit":"",
                         "exciseCurrency":"",
                         "taxRateName":"18%"*/
                        
                        if ($data['taxDetails']) {
                            try{
                                $this->db->exec(array('DELETE FROM tbltaxdetails WHERE groupid = ' . $invoice->taxdetailgroupid));
                            } catch (Exception $e) {
                                $this->logger->write("Invoice Controller : downloadinvoice() : The operation to delete from table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            foreach($data['taxDetails'] as $elem){
                                
                                try{
                                    
                                    
                                    $taxCategory = $elem['taxCategory'];
                                    $netAmount = $elem['netAmount'];
                                    $taxRate = $elem['taxRate'];
                                    $taxAmount = $elem['taxAmount'];
                                    $grossAmount = $elem['grossAmount'];
                                    $exciseUnit = $elem['exciseUnit'];
                                    $exciseCurrency = $elem['exciseCurrency'];
                                    $taxRateName = $elem['taxRateName'];
                                    
                                    $tr = new taxrates($this->db);
                                    $tr->getByName($taxRateName);
                                    
                                    $taxdescription = $tr->description;//A: Standard (18%)
                                    
                                    $this->db->exec(array('INSERT INTO tbltaxdetails
                                                                (groupid,
                                                                goodid,
                                                                taxcategory,
                                                                netamount,
                                                                taxrate,
                                                                taxamount,
                                                                grossamount,
                                                                exciseunit,
                                                                excisecurrency,
                                                                taxratename,
                                                                taxdescription,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $invoice->taxdetailgroupid . ', 0,
                                                                "' . addslashes($taxCategory) . '",
                                                                ' . $netAmount . ',
                                                                ' . $taxRate . ',
                                                                ' . $taxAmount . ',
                                                                ' . $grossAmount . ',
                                                                "' . addslashes($exciseUnit) . '",
                                                                "' . addslashes($exciseCurrency) . '",
                                                                "' . addslashes($taxRateName) . '",
                                                                "' . addslashes($taxdescription) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                } catch (Exception $e) {
                                    $this->logger->write("Invoice Controller : downloadinvoice() : The operation to insert into table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                }
                                
                            }
                            
                        } else {//NOTHING RETURNED BY API
                            $this->logger->write("Invoice Controller : downloadinvoice() : The API did not return anything", 'r');
                        }
                    }
                    
                    
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the invoice by " . $this->f3->get('SESSION.username') . " was successful");
                    self::$systemalert = "The operation to download the invoice by " . $this->f3->get('SESSION.username') . " was successful";
                }
            } else {
                $this->logger->write("Invoice Controller : downloadinvoice() : The invoice is NOT yet uploaded. Please upload.", 'r');
                self::$systemalert = "The invoice is NOT yet uploaded. Please upload.";
            }
            
            
            //die($data);
        } else {
            $this->logger->write("Invoice Controller : downloadinvoice() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
       
    /**
     *	@name printinvoice
     *  @desc Print an invoice
     *	@return NULL
     *	@param NULL
     **/
    function printinvoice(){
        $operation = NULL; //tblevents
        $permission = 'PRINTINVOICES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Invoice Controller : printinvoice() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Invoice Controller : printinvoice() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Invoice Controller : printinvoice() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $id = trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("Invoice Controller : printinvoice() : The is a GET call & id to view is " . $id, 'r');
            
            // The Invoice
            $invoice = new invoices($this->db);
            $invoice->netamount2 = 'FORMAT(netamount, 2)';
            $invoice->taxamount2 = 'FORMAT(taxamount, 2)';
            $invoice->grossamount2 = 'FORMAT(grossamount, 2)';
            $invoice->getByID($id);
            $this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('invoice', $invoice);
            
            //The Seller
            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
            $this->f3->set('seller', $org);
            
            //The Buyer
            $buyer = new customers($this->db);
            $buyer->getByID($invoice->buyerid);
            $this->f3->set('buyer', $buyer);
            
            $this->logger->write("Invoice Controller : printinvoice() : Buyer ID " . $buyer->id, 'r');
            
            //The Goods
            try{
                $goods = array();
                
                $temp = $this->db->exec(array('SELECT item, FORMAT(qty, 2) qty, unitofmeasure, FORMAT(unitprice, 2) unitprice, FORMAT(total, 2) total, displayCategoryCode taxcategory, unitofmeasurename, FORMAT(discounttotal, 2) discounttotal FROM tblgooddetails WHERE groupid = COALESCE(' . $invoice->gooddetailgroupid . ', NULL) ORDER BY inserteddt ASC'));
                
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
                        
                        //If there is a discount, add a discount line below the item
                        if ($obj['discounttotal'] < 0) {
                            $goods[] = array(
                                'item' => empty($obj['item'])? '' : $obj['item'] . " (Discount)",
                                'qty' => '',
                                'unitofmeasure' => '',
                                'unitprice' => '',
                                'total' => empty($obj['discounttotal'])? '' : $obj['discounttotal'],
                                'taxcategory' => '',
                                'unitofmeasurename' => ''
                            );
                        }
                    }
                } else {
                    $goods = array(
                        "0" => array()
                    );
                }
                
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : printinvoice() : The operation to retrieve goods was not successfull. The error messages is " . $e->getMessage(), 'r');
                $goods = array(
                    "0" => array()
                );
            }            
            $this->f3->set('goods', $goods);
            //$this->logger->write($this->db->log(TRUE), 'r');
            
            //The Tax Details
            try{
                $taxes = $this->db->exec(array('SELECT FORMAT(netamount, 2) netamount, FORMAT(taxrate, 2) taxrate, FORMAT(taxamount, 2) taxamount, FORMAT(grossamount, 2) grossamount, taxdescription FROM tbltaxdetails WHERE groupid = COALESCE(' . $invoice->taxdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : printinvoice() : The operation to retrieve taxes was not successfull. The error messages is " . $e->getMessage(), 'r');
                $taxes = array(
                    "0" => array()
                );
            }
            $this->f3->set('taxes', $taxes);
            
            
            //The Payments
            try{
                $payments = $this->db->exec(array('SELECT paymentmodename, FORMAT(paymentamount, 2) paymentamount FROM tblpaymentdetails WHERE groupid = COALESCE(' . $invoice->paymentdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : printinvoice() : The operation to retrieve payments was not successfull. The error messages is " . $e->getMessage(), 'r');
                $payments = array(
                    "0" => array()
                );
            }
            $this->f3->set('payments', $payments);
            
            
            $this->f3->set('path', '../' . $this->path);
            $this->f3->set('pagetitle','Print Invoice | ' . $id);//display the edit form

            
            echo \Template::instance()->render('PrintInvoice.htm');
        } else {
            $this->logger->write("Invoice Controller : printinvoice() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name pviewinvoice
     *  @desc View an invoice
     *	@return NULL
     *	@param NULL
     **/
    function pviewinvoice(){
        $operation = NULL; //tblevents
        $permission = 'VIEWINVOICES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Invoice Controller : pviewinvoice() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Invoice Controller : pviewinvoice() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Invoice Controller : pviewinvoice() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $id = trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("Invoice Controller : pviewinvoice() : The is a GET call & id to view is " . $id, 'r');
            
            // The Invoice
            $invoice = new invoices($this->db);
            $invoice->netamount2 = 'FORMAT(netamount, 2)';
            $invoice->taxamount2 = 'FORMAT(taxamount, 2)';
            $invoice->grossamount2 = 'FORMAT(grossamount, 2)';
            $invoice->getByID($id);
            $this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('invoice', $invoice);
            
            //The Seller
            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
            $this->f3->set('seller', $org);
            
            //The Buyer
            $buyer = new customers($this->db);
            $buyer->getByID($invoice->buyerid);
            $this->f3->set('buyer', $buyer);
            
            //The Goods
            try{
                $goods = array();
                
                $temp = $this->db->exec(array('SELECT item, FORMAT(qty, 2) qty, unitofmeasure, FORMAT(unitprice, 2) unitprice, FORMAT(total, 2) total, displayCategoryCode taxcategory, unitofmeasurename, FORMAT(discounttotal, 2) discounttotal FROM tblgooddetails WHERE groupid = COALESCE(' . $invoice->gooddetailgroupid . ', NULL) ORDER BY inserteddt ASC'));
                
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
                        
                        //If there is a discount, add a discount line below the item
                        if ($obj['discounttotal'] < 0) {
                            $goods[] = array(
                                'item' => empty($obj['item'])? '' : $obj['item'] . " (Discount)",
                                'qty' => '',
                                'unitofmeasure' => '',
                                'unitprice' => '',
                                'total' => empty($obj['discounttotal'])? '' : $obj['discounttotal'],
                                'taxcategory' => '',
                                'unitofmeasurename' => ''
                            );
                        }
                    }
                } else {
                    $goods = array(
                        "0" => array()
                    );
                }
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : pviewinvoice() : The operation to retrieve goods was not successfull. The error messages is " . $e->getMessage(), 'r');
                $goods = array(
                    "0" => array()
                );
            }
            $this->f3->set('goods', $goods);
            //$this->logger->write($this->db->log(TRUE), 'r');
            
            //The Tax Details
            try{
                $taxes = $this->db->exec(array('SELECT FORMAT(netamount, 2) netamount, FORMAT(taxrate, 2) taxrate, FORMAT(taxamount, 2) taxamount, FORMAT(grossamount, 2) grossamount, taxdescription FROM tbltaxdetails WHERE groupid = COALESCE(' . $invoice->taxdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : pviewinvoice() : The operation to retrieve taxes was not successfull. The error messages is " . $e->getMessage(), 'r');
                $taxes = array(
                    "0" => array()
                );
            }
            $this->f3->set('taxes', $taxes);
            
            
            //The Payments
            try{
                $payments = $this->db->exec(array('SELECT paymentmodename, FORMAT(paymentamount, 2) paymentamount FROM tblpaymentdetails WHERE groupid = COALESCE(' . $invoice->paymentdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Invoice Controller : pviewinvoice() : The operation to retrieve payments was not successfull. The error messages is " . $e->getMessage(), 'r');
                $payments = array(
                    "0" => array()
                );
            }
            $this->f3->set('payments', $payments);
            
            
            $this->f3->set('path', '../' . $this->path);
            $this->f3->set('pagetitle','View Invoice | ' . $id);//display the edit form
            
            $this->f3->set('pagecontent','ViewInvoice.htm');
            $this->f3->set('pagescripts','ViewInvoiceFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Invoice Controller : pviewinvoice() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name downloadErpInvoices
     *  @desc download invoices from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function downloadErpInvoices(){
        $operation = NULL; //tblevents
        $permission = 'SYNCINVOICES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Invoice Controller : downloadErpInvoices() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            
            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);
            
            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
            
            $startDate = $this->f3->get('POST.downloaderpinvoicesstartdate');
            $endDate = $this->f3->get('POST.downloaderpinvoicesenddate');
            $invoiceNo = $this->f3->get('POST.downloaderpinvoicenumber');
            $docType = $this->f3->get('POST.downloaderpinvoiceserpdoctype');
            
            $this->logger->write("Invoice Controller : downloadErpInvoices() : startDate: " . $startDate, 'r');
            $this->logger->write("Invoice Controller : downloadErpInvoices() : endDate: " . $endDate, 'r');
            $this->logger->write("Invoice Controller : downloadErpInvoices() : invoiceNo: " . $invoiceNo, 'r');
            $this->logger->write("Invoice Controller : downloadErpInvoices() : docType: " . $docType, 'r');
            
            $startDate = empty($startDate)? date('Y-m-d') : date('Y-m-d', strtotime($startDate));
            $endDate = empty($endDate)? date('Y-m-d') : date('Y-m-d', strtotime($endDate));
            //$invoiceNo = empty($invoiceNo)? 'NULL' : $invoiceNo;
            $docType = empty($docType)? $this->appsettings['INVOICEERPDOCTYPE'] : trim($docType);
            
            
            
            if ($this->platformMode == 'ERP') {
                $this->logger->write("Invoice Controller : downloadErpInvoices() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
            } else {
                $this->logger->write("Invoice Controller : downloadErpInvoices() : The platform is integrated.", 'r');
                
                if ($this->integratedErp) {
                    /**
                     * Check on integrated ERP type
                     */
                    $this->logger->write("Invoice Controller : downloadErpInvoices() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                    
                    if (strtoupper($this->integratedErp) == 'QBO') {
                        $this->logger->write("Invoice Controller : downloadErpInvoices() : The integrated ERP is Quicbooks Online.", 'r');
                        
                        $qry = '';
                        
                        if ($docType == trim($this->appsettings['INVOICEERPDOCTYPE'])) {
                            $qry = 'SELECT * FROM Invoice';
                        } elseif ($docType == trim($this->appsettings['SALESRECEIPTERPDOCTYPE'])){
                            $qry = 'SELECT * FROM SalesReceipt';
                        } else {
                            $qry = 'SELECT * FROM Invoice';
                        }
                        
                        if ($invoiceNo) {
                            $qry = $qry . " Where DocNumber = '" . $invoiceNo . "'";
                        } else {
                            $qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "' And Metadata.LastUpdatedTime <= '" . $endDate . "'";
                        }
                        
                        
                        $this->logger->write("Invoice Controller : downloadErpInvoices() : The query is: " . $qry, 'r');
                        
                        try {
                            if ($this->appsettings['QBACCESSTOKEN'] !== null) {
                                // Create SDK instance
                                $authMode = $this->appsettings['QBAUTH_MODE'];
                                $ClientID = $this->appsettings['QBCLIENT_ID'];
                                $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                $baseUrl = $this->appsettings['QBBASE_URL'];
                                $QBORealmID = $this->appsettings['QBREALMID'];
                                
                                $accessToken = $this->appsettings['QBACCESSTOKEN'];
                                
                                $dataService = DataService::Configure(array(
                                    'auth_mode' => $authMode,
                                    'ClientID' => $ClientID,
                                    'ClientSecret' =>  $ClientSecret,
                                    'baseUrl' => $baseUrl,
                                    'refreshTokenKey' => $this->appsettings['QBREFRESHTOKEN'],
                                    'QBORealmID' => $QBORealmID,
                                    'accessTokenKey' => $this->appsettings['QBACCESSTOKEN']
                                ));
                                
                                $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                               // $dataService->throwExceptionOnError(true);
                                
                                $invoices = $dataService->Query($qry);
                                
                                $error = $dataService->getLastError();
                                
                                if ($error) {
                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : The operation to download ERP invoices was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful");
                                    self::$systemalert = "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful.";
                                }
                                else {
                                    //print_r($invoices);
                                    
                                    if(isset($invoices)){
                                        if ($invoices) {
                                            $invoice = new invoices($this->db);
                                            $customer = new customers($this->db);
                                            
                                            $goods = array();
                                            $taxes = array();
                                            
                                            $deemedflag = 'NO';
                                            $discountflag = 'NO';
                                            
                                            $pricevatinclusive = empty($this->appsettings['PRICEVATINCLUSIVE'])? 'NO' : strtoupper($this->appsettings['PRICEVATINCLUSIVE']);//No
                                            
                                            $netamount = 0;
                                            $taxamount = 0;
                                            $grossamount = 0;
                                            $itemcount = 0;
                                            
                                            $tr = new taxrates($this->db);
                                            $taxid = NULL;
                                            $taxcode = NULL;
                                            $taxname = NULL;
                                            $taxcategory = NULL;
                                            $taxdisplaycategory = NULL;
                                            $taxdescription = NULL;
                                            $rate = 0;
                                            $qty = 0;
                                            $unit = 0;
                                            $discountpct = 0;
                                            $total = 0;
                                            $discount = 0;
                                            $gross = 0;
                                            $discount = 0;
                                            $tax = 0;
                                            $net = 0;
                                            $amount = 0;
                                            $product = new products($this->db);
                                            $measureunit = new measureunits($this->db);
                                            
                                            $buyer = array(
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
                                            
                                            $invoicedetails = array(
                                                'id' => NULL,
                                                'gooddetailgroupid' => NULL,
                                                'taxdetailgroupid' => NULL,
                                                'paymentdetailgroupid' => NULL,
                                                'erpinvoiceid' => NULL,
                                                'erpinvoiceno' => NULL,
                                                'antifakecode' => NULL,
                                                'deviceno' => trim($devicedetails->deviceno),
                                                'issueddate' => NULL,
                                                'issuedtime' => NULL,
                                                'operator' => NULL,
                                                'currency' => NULL,
                                                'oriinvoiceid' => NULL,
                                                'invoicetype' => "1",
                                                'invoicekind' => ($this->vatRegistered == 'Y')? "1" : "2",
                                                'datasource' => $this->appsettings['DEFAULTDATASOURCE'],
                                                //'invoiceindustrycode' => $this->appsettings['DEFAULTINVOICEINDUSTRY'],
                                                'invoiceindustrycode' => NULL,
                                                'einvoiceid' => NULL,
                                                'einvoicenumber' => NULL,
                                                'einvoicedatamatrixcode' => NULL,
                                                'isbatch' => '0',
                                                'netamount' => NULL,
                                                'taxamount' => NULL,
                                                'grossamount' => NULL,
                                                'origrossamount' => NULL,
                                                'itemcount' => NULL,
                                                'modecode' => NULL,
                                                'modename' => NULL,
                                                'remarks' => NULL,
                                                'buyerid' => NULL,
                                                'sellerid' => $this->appsettings['SELLER_RECORD_ID'],
                                                'issueddatepdf' => NULL,
                                                'grossamountword' => NULL,
                                                'isinvalid' => 0,
                                                'isrefund' => 0,
                                                'vchtype' => "Sales",
                                                'vchtypename' => "Tax Invoice",
                                                'SyncToken' => NULL,
                                                'docTypeCode' => $docType
                                            );
                                            
                                            $discountAppStatus = 0;
                                            $discountAppBalance = 0;
                                            $discountAppPct = 0;
                                            
                                            foreach($invoices as $elem){
                                                
                                                try {
                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : Invoice Number: " . $elem->DocNumber, 'r');
                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : PrivateNote: " . $elem->PrivateNote, 'r');
                                                    $InvStatus = $elem->PrivateNote;
                                                    
                                                    
                                                    $CustomerRef = $elem->CustomerRef;
                                                    $DocNumber = $elem->DocNumber;
                                                    $CurrencyRef = $elem->CurrencyRef;
                                                    $TxnDate = $elem->TxnDate;
                                                    $InvoiceId = $elem->Id;
                                                    $SyncToken = $elem->SyncToken;
                                                    $TxnDate = $elem->TxnDate;
                                                    
                                                    /**
                                                     * Author: Francis Lubanga <frncslubanga@gmail.com>
                                                     * Date: 2025-08-09
                                                     * Description: Use the shipping address country to determine the export transactions
                                                     */
                                                    $ShipAddrCountry = '';
                                                    $CustomerTypeRef = '';
                                                    
                                                    $invoicedetails['erpinvoiceid'] = $InvoiceId;
                                                    $invoicedetails['erpinvoiceno'] = $DocNumber;
                                                    
                                                    if ($CustomerRef) {
                                                        //Let's download the customer
                                                        $customers = $dataService->FindbyId('customer', $CustomerRef);
                                                        
                                                        $custError = $dataService->getLastError();
                                                        
                                                        if ($custError) {
                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : The operation to download ERP customers was not successful. The Response Message is: " . $custError->getResponseBody(), 'r');
                                                        } else {
                                                            if(isset($customers->ShipAddr)){
                                                                $ShipAddrCountry = $customers->ShipAddr->Country;
                                                            }
                                                            
                                                            if(isset($customers->CustomerTypeRef)){
                                                                $CustomerTypeRef = $customers->CustomerTypeRef;
                                                            }
                                                        }
                                                        
                                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : ShipAddrCountry = " . $ShipAddrCountry, 'r');
                                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : CustomerTypeRef = " . $CustomerTypeRef, 'r');
                                                        
                                                        if($ShipAddrCountry && $this->appsettings['EXPORTINVOICEINDUSTRY']){
                                                            if(trim(strtolower($ShipAddrCountry)) == trim(strtolower($this->appsettings['ERPBASECOUNTRY']))){
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The base country and the customer's shipping country is the same!", 'r');
                                                                $invoicedetails['invoiceindustrycode'] = $this->appsettings['DEFAULTINVOICEINDUSTRY'];
                                                            } else {
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The base country and the customer's shipping country is NOT the same!", 'r');
                                                                $invoicedetails['invoiceindustrycode'] = $this->appsettings['EXPORTINVOICEINDUSTRY'];
                                                            }
                                                        } else {
                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : The shipping address was not supplied!", 'r');
                                                        }
                                                        
                                                        
                                                        
                                                        $customer->getByCode($CustomerRef);
                                                        
                                                        if ($customer->id) {
															$this->logger->write("Invoice Controller : downloadErpInvoices() : The customer Id " . $CustomerRef . " exists on the platform", 'r');
															
                                                            $buyer['id'] = $customer->id;
                                                            $buyer['erpcustomerid'] = $customer->erpcustomerid;
                                                            $buyer['erpcustomercode'] = $customer->erpcustomercode;
                                                            $buyer['tin'] = $customer->tin;
                                                            $buyer['ninbrn'] = $customer->ninbrn;
                                                            $buyer['PassportNum'] = $customer->PassportNum;
                                                            $buyer['legalname'] = $customer->legalname;
                                                            $buyer['businessname'] = $customer->businessname;
                                                            $buyer['address'] = $customer->address;
                                                            $buyer['mobilephone'] = $customer->mobilephone;
                                                            $buyer['linephone'] = $customer->linephone;
                                                            $buyer['emailaddress'] = $customer->emailaddress;
                                                            $buyer['placeofbusiness'] = $customer->placeofbusiness;
                                                            $buyer['type'] = empty($customer->type)? 1 : $customer->type;
                                                            $buyer['citizineship'] = $customer->citizineship;
                                                            $buyer['countryCode'] = $customer->countryCode;
                                                            $buyer['sector'] = $customer->sector;
                                                            $buyer['sectorCode'] = $customer->sectorCode;
                                                            $buyer['datasource'] = $customer->datasource;
                                                            $buyer['status'] = $customer->status;
                                                            
                                                            
                                                            $invoicedetails['buyerid'] = $customer->id;
                                                        } else {
                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : The customer Id " . $CustomerRef . " does not exist on the platform", 'r');
                                                            
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
                                                                        'type' => '1', /*default all customers to B2C*/
                                                                        'citizineship' => NULL,
                                                                        'countryCode' => NULL,
                                                                        'sector' => NULL,
                                                                        'sectorCode' => NULL,
                                                                        'datasource' => 'ERP',
                                                                        'status' => NULL,
                                                                    );
                                                                    
                                                                    try {
                                                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : Customer Name: " . $customers->DisplayName, 'r');
                                                                        
                                                                        
                                                                        $erpcustomerid = $customers->Id;
                                                                        $erpcustomercode = $customers->Id;
                                                                        $legalname = empty($customers->FullyQualifiedName)? $customers->DisplayName : $customers->FullyQualifiedName;
                                                                        $businessname = empty($customers->FullyQualifiedName)? $customers->DisplayName : $customers->FullyQualifiedName;
                                                                        
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
                                                                        
                                                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : Mobile: " . $mobilephone, 'r');
                                                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : Email: " . $emailaddress, 'r');
                                                                        
                                                                        $cust['erpcustomerid'] = $erpcustomerid;
                                                                        $cust['erpcustomercode'] = $erpcustomercode;
                                                                        $cust['legalname'] = $legalname;
                                                                        $cust['businessname'] = $businessname;
                                                                        
                                                                        
                                                                        
                                                                        if ($customers->Active == false) {
                                                                            $cust['status'] = $this->appsettings['INACTIVECUSTOMERSTATUSID'];
                                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : The customer is not ACTIVE.", 'r');
                                                                        } else {
                                                                            $cust['status'] = $this->appsettings['ACTIVECUSTOMERSTATUSID'];
                                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : The customer is ACTIVE.", 'r');
                                                                        }
                                                                        
                                                                        
                                                                        if ($erpcustomercode && $legalname) {
                                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : The customer does not exist", 'r');
                                                                            $cust_status = $this->util->createcustomer($cust, $this->f3->get('SESSION.id'));
                                                                            
                                                                            if ($cust_status) {
                                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The customer " . $cust['legalname'] . " was created.", 'r');
                                                                                
                                                                                
                                                                                $customer->getByCode($CustomerRef);
                                                                                
                                                                                if ($customer->id) {
                                                                                    $buyer['id'] = $customer->id;
                                                                                    $buyer['erpcustomerid'] = $customer->erpcustomerid;
                                                                                    $buyer['erpcustomercode'] = $customer->erpcustomercode;
                                                                                    $buyer['tin'] = $customer->tin;
                                                                                    $buyer['ninbrn'] = $customer->ninbrn;
                                                                                    $buyer['PassportNum'] = $customer->PassportNum;
                                                                                    $buyer['legalname'] = $customer->legalname;
                                                                                    $buyer['businessname'] = $customer->businessname;
                                                                                    $buyer['address'] = $customer->address;
                                                                                    $buyer['mobilephone'] = $customer->mobilephone;
                                                                                    $buyer['linephone'] = $customer->linephone;
                                                                                    $buyer['emailaddress'] = $customer->emailaddress;
                                                                                    $buyer['placeofbusiness'] = $customer->placeofbusiness;
                                                                                    //$buyer['type'] = $customer->type;
                                                                                    $buyer['type'] = empty($customer->type)? 1 : $customer->type;
                                                                                    $buyer['citizineship'] = $customer->citizineship;
                                                                                    $buyer['countryCode'] = $customer->countryCode;
                                                                                    $buyer['sector'] = $customer->sector;
                                                                                    $buyer['sectorCode'] = $customer->sectorCode;
                                                                                    $buyer['datasource'] = $customer->datasource;
                                                                                    $buyer['status'] = $customer->status;
                                                                                    
                                                                                    
                                                                                    $invoicedetails['buyerid'] = $customer->id;
                                                                                } else {
                                                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : The created customer was not retrieved successfully.", 'r');
                                                                                }
                                                                            } else {
                                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The customer " . $cust['legalname'] . " was NOT created.", 'r');
                                                                            }
                                                                        } else {
                                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : The customer has no Id.", 'r');
                                                                        }
                                                                        
                                                                    } catch (Exception $e) {
                                                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : There was an error when processing Item " . $customers->DisplayName . ". The error is " . $e->getMessage(), 'r');
                                                                    }
                                                                }
                                                            } else {
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The operation to download ERP customers did not return records.", 'r');
                                                            }
                                                        }
                                                        
                                                        
                                                    }
                                                    
                                                    if(isset($elem->Line)){                                                       
                                                        foreach($elem->Line as $items){
                                                            $LineId = $items->Id;
                                                            $LineNum = $items->LineNum;
                                                            $Description = $items->Description;
                                                            $ErpAmount = $items->Amount;
                                                            $DetailType = $items->DetailType;
                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : Line Description: " . $Description, 'r');
                                                            
                                                            if (strtoupper($items->DetailType) == 'DISCOUNTLINEDETAIL') {
                                                                if(isset($items->DiscountLineDetail)){
                                                                    $PercentBased = $items->DiscountLineDetail->PercentBased;//true/false
                                                                    $DiscountPercent = $items->DiscountLineDetail->DiscountPercent;
                                                                }
                                                                
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : Discount Percent: " . $PercentBased, 'r');
                                                                $discount = empty($ErpAmount)? 0 : (float)$ErpAmount;
                                                                $discountpct = empty($DiscountPercent)? 0 : (float)$DiscountPercent;
                                                                
                                                                if (!empty($ErpAmount)){
                                                                    $discountAppStatus = 1;
                                                                    $discountAppBalance = $ErpAmount;
                                                                    $discountAppPct = $discountpct;
                                                                }
                                                            }  
                                                            
                                                            if (strtoupper($items->DetailType) == 'SALESITEMLINEDETAIL') {
                                                                if(isset($items->SalesItemLineDetail)){
                                                                    $ItemRef = $items->SalesItemLineDetail->ItemRef;
                                                                    $UnitPrice = $items->SalesItemLineDetail->UnitPrice;
                                                                    $Qty = $items->SalesItemLineDetail->Qty;
                                                                    $TaxCodeRef = $items->SalesItemLineDetail->TaxCodeRef;
                                                                }
                                                                
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : Unit Price: " . $UnitPrice, 'r');
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : Qty: " . $Qty, 'r');
                                                                
                                                                
                                                                $product->getByErpCode($ItemRef);
                                                                
                                                                if ($product->code) {
                                                                    $measureunit->getByCode($product->measureunit);
                                                                } else {
                                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : The Item does not exist on the platform", 'r');
                                                                }
                                                                
                                                                $qty = $Qty;
                                                                $unit = $UnitPrice;
                                                                $amount = $ErpAmount;
                                                                
                                                                /**
                                                                 * Can we determine the DISCOUNT PERCENTAGE incase it is a line DISCOUNT provided?
                                                                 */
                                                                if ($discountpct == 0 && $discount > 0) {
                                                                    $discountpct = $discount/$amount;
                                                                    $discount = 0;
                                                                } else {
                                                                    $discount = 0;
                                                                }
                                                                
                                                                /**
                                                                 * Date: 2025-05-30
                                                                 * Author: Francis Lubanga <frncslubanga@gmail.com>
                                                                 * Description: Hard code the tax rate for Commodity Category Code 96010102
                                                                 */
                                                                if (trim($product->commoditycategorycode) == '96010102') {
                                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : The product code is 96010102. Hard coding the tax id to 13", 'r');
                                                                    $taxid = '13'; // 13 is the tax id for VAT OUT OF SCOPE
                                                                } else {
                                                                    $taxid = $this->util->getinvoicetaxrate_v2($invoicedetails['invoiceindustrycode'], $customer->type, $product->code, $customer->tin, $this->appsettings['OVERRIDE_TAXRATE_FLAG'], $this->appsettings['TAXPAYER_CHECK_FLAG']);
                                                                }
                                                                
                                                                //$taxid = $this->util->getinvoicetaxrate_v2($this->appsettings['DEFAULTINVOICEINDUSTRY'], $customer->type, $product->code, $customer->tin, $this->appsettings['OVERRIDE_TAXRATE_FLAG'], $this->appsettings['TAXPAYER_CHECK_FLAG']);
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The computed TAXID is " . $taxid, 'r');
                                                                
                                                                if (!$taxid) {
                                                                    $taxid = $this->appsettings['STANDARDTAXRATE'];
                                                                }
                                                                
                                                                
                                                                if ($taxid == $this->appsettings['DEEMEDTAXRATE']) {
                                                                    $deemedflag = 'YES';
                                                                } else {
                                                                    $deemedflag = 'NO';
                                                                }
                                                                
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The final TAXID is " . $taxid, 'r');
                                                                
                                                                $tr = new taxrates($this->db);
                                                                $tr->getByID($taxid);
                                                                $taxcode = $tr->code;
                                                                $taxname = $tr->name;
                                                                $taxcategory = $tr->category;
                                                                $taxdisplaycategory = $tr->displayCategoryCode;
                                                                $taxdescription = $tr->description;
                                                                $rate = $tr->rate? $tr->rate : 0;
                                                                
                                                                
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : unit: " . $unit, 'r');
                                                                
                                                                if (strtoupper(trim($pricevatinclusive)) == 'YES') {
                                                                    //Use the figures as they come from the ERP
                                                                    $total = ($qty * $unit);//??
                                                                    
                                                                    //$discount = ($discountpct/100) * $total; //discount is already calculated by QB
                                                                    
                                                                    /**
                                                                     * Modification Date: 2021-01-26
                                                                     * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                     * */
                                                                    //$gross = $total - $discount;
                                                                    $gross = $total;
                                                                    
                                                                    $discount = (-1) * $discount;
                                                                    
                                                                    $tax = ($gross/($rate + 1)) * $rate; //??
                                                                    
                                                                    $net = $gross - $tax;
                                                                } elseif (strtoupper(trim($pricevatinclusive)) == 'NO') {
                                                                    //Manually calculate figures
                                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : Rebasing the prices", 'r');
                                                                    
                                                                    if ($rate > 0) {
                                                                        $unit = $unit * ($rate + 1);
                                                                    }
                                                                    
                                                                    $total = ($qty * $unit);//??
                                                                    
                                                                    //$discount = ($discountpct/100) * $total;
                                                                    
                                                                    /**
                                                                     * Modification Date: 2021-01-26
                                                                     * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                     * */
                                                                    //$gross = $total - $discount;
                                                                    $gross = $total;
                                                                    
                                                                    $discount = (-1) * $discount;
                                                                    
                                                                    $tax = ($gross/($rate + 1)) * $rate; //??
                                                                    
                                                                    $net = $gross - $tax;
                                                                }
                                                                
                                                                /**
                                                                 * Over-ride tax, if the tax payer is not VAT registered
                                                                 */
                                                                if ($this->vatRegistered == 'N') {
                                                                    $tax = 0;
                                                                    $taxcategory = NULL;
                                                                    $taxcode = NULL;
                                                                }
                                                                
                                                                
                                                                
                                                                $netamount = $netamount + $net;
                                                                $taxamount = $taxamount + $tax;
                                                                
                                                                $grossamount = $grossamount + $gross;
                                                                $itemcount = $itemcount + 1;
                                                                
                                                                if ($discount == 0) {
                                                                    $discountflag = 'NO';
                                                                } else {
                                                                    $discountflag = 'YES';
                                                                }
                                                                
                                                                $goods[] = array(
                                                                    'groupid' => NULL,
                                                                    'item' => $product->name,
                                                                    'itemcode' => $product->code,
                                                                    'qty' => $qty,
                                                                    'unitofmeasure' => $product->measureunit,
                                                                    'unitprice' => $unit,
                                                                    'total' => $total,
                                                                    'taxid' => $taxid,
                                                                    'taxrate' => $rate,
                                                                    'tax' => $tax,
                                                                    'discounttotal' => $discount,
                                                                    'discounttaxrate' => $rate,
                                                                    'discountpercentage' => $discountpct,
                                                                    'ordernumber' => NULL,
                                                                    'discountflag' => trim($discountflag) == 'NO'? '2' : '1',
                                                                    'deemedflag' => (strtoupper(trim($deemedflag)) == 'NO'? '2' : '1'),
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
                                                                    'taxdisplaycategory' => $taxdisplaycategory,
                                                                    'taxcategory' => $taxcategory,
                                                                    'taxcategoryCode' => $taxcode,
                                                                    'unitofmeasurename' => $measureunit->name,
                                                                    'vatProjectId' => $customer->vatProjectId,
                                                                    'vatProjectName' => $customer->vatProjectName,
                                                                    'hsCode' => $product->hsCode,
                                                                    'hsName' => $product->hsNode,
                                                                    'totalWeight' => empty($product->weight)? 0 : $product->weight,
                                                                    'pieceQty' => trim($invoicedetails['invoiceindustrycode']) == '102'? $qty : 0,
                                                                    'deemedExemptCode' => NULL,
                                                                    'pieceMeasureUnit' => $product->piecemeasureunit
                                                                );
                                                                
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The TAXCODE is " . $taxcode, 'r');
                                                                
                                                                
                                                                if ($this->vatRegistered == 'Y') {
                                                                    $taxes[] = array(
                                                                        'discountflag' => trim($discountflag) == 'NO'? '2' : '1',
                                                                        'discounttotal' => $discount,
                                                                        'discounttaxrate' => $rate,
                                                                        'discountpercentage' => $discountpct,
                                                                        'd_netamount' => NULL,
                                                                        'd_taxamount' => NULL,
                                                                        'd_grossamount' => NULL,
                                                                        'groupid' => NULL,
                                                                        'goodid' => NULL,
                                                                        'taxdisplaycategory' => $taxdisplaycategory,
                                                                        'taxcategory' => $taxcategory,
                                                                        'taxcategoryCode' => $taxcode,
                                                                        'netamount' => $net,
                                                                        'taxrate' => $rate,
                                                                        'taxamount' => $tax,
                                                                        'grossamount' => $gross,
                                                                        'exciseunit' => NULL,
                                                                        'excisecurrency' => NULL,
                                                                        'taxratename' => $taxname,
                                                                        'taxdescription' => $taxdescription
                                                                    );
                                                                }  
                                                            }
                                                        }//foreach($elem->Line as $items)
                                                    }//if(isset($elem->Line))
                                                        
                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : Discount App Status: " . $discountAppStatus, 'r');
                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : Discount App Balance: " . $discountAppBalance, 'r');
                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : Discount App Percentage: " . $discountAppPct, 'r');
                                                    
                                                    if ($discountAppStatus == 1) {
                                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : Applying Discounts", 'r');
                                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : Customer Type " . $customer->type, 'r');
                                                        list($goods, $taxes) = $this->util->applyDiscount($goods, $taxes, $discountAppBalance, $customer->type, $customer->tin, NULL);
                                                    }
                                                        
                                                    if(isset($elem->TxnTaxDetail)){ 
                                                        $TxnTaxCodeRef = $elem->TxnTaxDetail->TxnTaxCodeRef;
                                                        $TotalTax = $elem->TxnTaxDetail->TotalTax;
                                                        
                                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : Tax Ref: " . $TxnTaxCodeRef, 'r');
                                                        
                                                        if(isset($elem->TxnTaxDetail->TaxLine)){
                                                            $TaxAmount = $elem->TxnTaxDetail->TaxLine->Amount;
                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : Total Tax Amount: " . $TaxAmount, 'r');
                                                            
                                                            if(isset($elem->TxnTaxDetail->TaxLine->DetailType)){
                                                                if (strtoupper($elem->TxnTaxDetail->TaxLine->DetailType) == 'TAXLINEDETAIL') {
                                                                    if(isset($elem->TxnTaxDetail->TaxLine->TaxLineDetail)){
                                                                        $TaxRateRef = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->TaxRateRef;
                                                                        $TaxPercentBased = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->PercentBased;
                                                                        $TaxPercent = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->TaxPercent;
                                                                        $NetAmountTaxable = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->NetAmountTaxable;
                                                                    }
                                                                    
                                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : Tax Line Net Amount: " . $NetAmountTaxable, 'r');
                                                                } 
                                                            }   
                                                        }  
                                                    }//if(isset($elem->TxnTaxDetail))
                                                    
                                                    if(isset($elem->CustomField)){
                                                        foreach($elem->CustomField as $fields){
                                                            $FieldDefinitionId = $fields->DefinitionId;
                                                            $FieldName = $fields->Name;
                                                            $FieldType = $fields->Type;//StringType
                                                            $FieldStringValue = $fields->StringValue;
                                                            
                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : Customer Field Name: " . $FieldName, 'r');
                                                        }//foreach($elem->CustomField as $items)
                                                    }//if(isset($elem->CustomField))
                                                    
                                                    $invoicedetails['operator'] = $this->f3->get('SESSION.username');
                                                    $invoicedetails['currency'] = $this->util->getcurrency(trim($CurrencyRef));
                                                    $invoicedetails['SyncToken'] = $SyncToken;
                                                    $invoicedetails['issueddate'] = $TxnDate;
                                                    $invoicedetails['issuedtime'] = $TxnDate;
                                                    $invoicedetails['issueddatepdf'] = $TxnDate;
                                                    $invoicedetails['itemcount'] = $itemcount;
                                                    
                                                    $invoicedetails['netamount'] = $netamount;
                                                    $invoicedetails['taxamount'] = $taxamount;
                                                    $invoicedetails['grossamount'] = $grossamount;
                                                    $invoicedetails['origrossamount'] = 0;
                                                    
                                                    $invoicedetails['remarks'] = "The invoice DocNumber " . $DocNumber . " and Id " . $InvoiceId . " uploaded using the QBO API";
                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : The Sync Token is " . $SyncToken, 'r');
                                                    
                                                    
                                                    
                                                    
                                                    if ($InvoiceId) {
                                                        $invoice->getByErpId($InvoiceId);
                                                        
                                                        if ($invoice->dry()) {
                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : The invoice does not exist", 'r');
                                                            $inv_status = $this->util->createinvoice($invoicedetails, $goods, $taxes, $buyer, $this->f3->get('SESSION.id'));
                                                            
                                                            if ($inv_status) {
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The invoice " . $DocNumber . " was created.", 'r');
                                                            } else {
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The invoice " . $DocNumber . " was NOT created.", 'r');
                                                            }
                                                        } else {
                                                            $this->logger->write("Invoice Controller : downloadErpInvoices() : The invoice exists", 'r');
                                                            $invoicedetails['id'] = $invoice->id;
                                                            $invoicedetails['gooddetailgroupid'] = $invoice->gooddetailgroupid;
                                                            $invoicedetails['taxdetailgroupid'] = $invoice->taxdetailgroupid;
                                                            $invoicedetails['paymentdetailgroupid'] = $invoice->paymentdetailgroupid;
                                                            
                                                            if ($invoice->einvoiceid) {
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The invoice " . $DocNumber . " is already fiscalized.", 'r');
                                                                 
                                                            } else {
                                                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The invoice " . $DocNumber . " is NOT fiscalized.", 'r');
                                                                
                                                                
                                                                 $inv_status = $this->util->updateinvoice($invoicedetails, $goods, $taxes, $buyer, $this->f3->get('SESSION.id'));
                                                                 
                                                                 if ($inv_status) {
                                                                 $this->logger->write("Invoice Controller : downloadErpInvoices() : The invoice " . $DocNumber . " was updated.", 'r');
                                                                 } else {
                                                                 $this->logger->write("Invoice Controller : downloadErpInvoices() : The invoice " . $DocNumber . " was NOT updated.", 'r');
                                                                 }
                                                                 
                                                            }
                                                        }
                                                    } else {
                                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : The invoice has no Id.", 'r');
                                                    }
                                                    
                                                } catch (Exception $e) {
                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : There was an error when processing invoice " . $elem->DocNumber . ". The error is " . $e->getMessage(), 'r');
                                                }
                                                
                                                //Clear/reset some variables
                                                unset($goods);
                                                unset($taxes);
                                                $deemedflag = 'NO';
                                                $discountflag = 'NO';
                                                $netamount = 0;
                                                $taxamount = 0;
                                                $grossamount = 0;
                                                $itemcount = 0;
                                                $taxid = NULL;
                                                $taxcode = NULL;
                                                $taxname = NULL;
                                                $taxcategory = NULL;
                                                $taxdisplaycategory = NULL;
                                                $taxdescription = NULL;
                                                $rate = 0;
                                                $qty = 0;
                                                $unit = 0;
                                                $discountpct = 0;
                                                $total = 0;
                                                $discount = 0;
                                                $gross = 0;
                                                $discount = 0;
                                                $tax = 0;
                                                $net = 0;
                                                $amount = 0;
                                                //unset($buyer);
                                                //unset($invoicedetails);
                                                $discountAppStatus = 0;
                                                $discountAppBalance = 0;
                                                $discountAppPct = 0;
                                                $InvStatus = NULL;
                                                
                                                $CustomerRef = NULL;
                                                $DocNumber = NULL;
                                                $CurrencyRef = NULL;
                                                $TxnDate = NULL;
                                                $InvoiceId = NULL;
                                                $SyncToken = NULL;
                                                $TxnDate = NULL;
                                                
                                            }//foreach
                                        }
                                        
                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : The operation to download ERP invoices was successful.", 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " was successful");
                                        self::$systemalert = "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " was successful.";
                                    } else {
                                        $this->logger->write("Invoice Controller : downloadErpInvoices() : The operation to download ERP invoices did not return records.", 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " did not return records");
                                        self::$systemalert = "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " did not return records.";
                                    }
                                }
                                /*
                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The operation to download ERP invoices was successful.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " was successful");
                                self::$systemalert = "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " was successful.";*/
                            } else {
                                $this->logger->write("Invoice Controller : downloadErpInvoices() : The operation to download ERP invoices was not successful. Please connect to ERP first.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                self::$systemalert = "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                            }
                            
                        } catch (Exception $e) {
                            $this->logger->write("Invoice Controller : downloadErpInvoices() : The operation to download ERP invoices was not successful. The error is: " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to download ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                        }
                    } else {
                        $this->logger->write("Invoice Controller : downloadErpInvoices() : The integrated ERP is unknown.", 'r');
                        self::$systemalert = "Sorry. The integrated ERP is unknown.";
                    }
                } else {
                    $this->logger->write("Invoice Controller : downloadErpInvoices() : We are unable to indentify the currently integrated ERP.", 'r');
                    self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                }
            }
        } else {
            $this->logger->write("Invoice Controller : downloadErpInvoices() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
    
    /**
     *	@name fetchErpInvoice
     *  @desc download invoices from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function fetchErpInvoice(){
        $operation = NULL; //tblevents
        $permission = 'SYNCINVOICES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Invoice Controller : fetchErpInvoice() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            $id = $this->f3->get('POST.erpdownloadinvoiceid');
            $invoice = new invoices($this->db);
            $invoice->getByID($id);
            $this->logger->write("Product Controller : fetchErpInvoice() : The invoice id is " . $this->f3->get('POST.erpdownloadinvoiceid'), 'r');
            
            if ($id) {
                
                if ($invoice->erpinvoiceid) {
                    if ($this->platformMode == 'ERP') {
                        $this->logger->write("Invoice Controller : fetchErpInvoice() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                        self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
                    } else {
                        $this->logger->write("Invoice Controller : fetchErpInvoice() : The platform is integrated.", 'r');
                        
                        if ($this->integratedErp) {
                            /**
                             * Check on integrated ERP type
                             */
                            $this->logger->write("Invoice Controller : fetchErpInvoice() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                            
                            if (strtoupper($this->integratedErp) == 'QBO') {
                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The integrated ERP is Quicbooks Online.", 'r');
                                
                                $docType = $invoice->docTypeCode;
                                $docType = empty($docType)? $this->appsettings['INVOICEERPDOCTYPE'] : trim($docType);
                                
                                $qry = '';
                                
                                if ($docType == trim($this->appsettings['INVOICEERPDOCTYPE'])) {
                                    $qry = 'SELECT * FROM Invoice';
                                } elseif ($docType == trim($this->appsettings['SALESRECEIPTERPDOCTYPE'])){
                                    $qry = 'SELECT * FROM SalesReceipt';
                                } else {
                                    $qry = 'SELECT * FROM Invoice';
                                }
                                
                                if ($invoice->erpinvoiceno) {
                                    $qry = $qry . " Where DocNumber = '" . $invoice->erpinvoiceno . "'";
                                    
                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : The query is: " . $qry, 'r');
                                    
                                    
                                    try {
                                        if ($this->appsettings['QBACCESSTOKEN'] !== null) {
											// Create SDK instance
											$authMode = $this->appsettings['QBAUTH_MODE'];
											$ClientID = $this->appsettings['QBCLIENT_ID'];
											$ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
											$baseUrl = $this->appsettings['QBBASE_URL'];
											$QBORealmID = $this->appsettings['QBREALMID'];
											
											$accessToken = $this->appsettings['QBACCESSTOKEN'];
											
											$dataService = DataService::Configure(array(
												'auth_mode' => $authMode,
												'ClientID' => $ClientID,
												'ClientSecret' =>  $ClientSecret,
												'baseUrl' => $baseUrl,
												'refreshTokenKey' => $this->appsettings['QBREFRESHTOKEN'],
												'QBORealmID' => $QBORealmID,
												'accessTokenKey' => $this->appsettings['QBACCESSTOKEN']
											));
                                            
                                            $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                            $dataService->throwExceptionOnError(true);
                                            
                                            $invoices = $dataService->Query($qry);
                                            //$invoices = $dataService->FindbyId('invoice', $invoice->erpinvoiceno);
                                            
                                            $error = $dataService->getLastError();
                                            
                                            if ($error) {
                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The operation to fetch ERP invoices was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful");
                                                self::$systemalert = "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful.";
                                            }
                                            else {
                                                if(isset($invoices)){
                                                    if(!empty($invoices) && sizeof($invoices) == 1){
                                                        $invoices = current($invoices);
                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : Invoice Number: " . $invoices->DocNumber, 'r');
                                                        $invoice = new invoices($this->db);
                                                        $InvoiceId = $invoices->Id;
                                                        
                                                        if ($InvoiceId) {
                                                            $invoice->getByErpId($InvoiceId);
                                                            
                                                            if ($invoice->dry()) {
                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The invoice does not exist", 'r');
                                                                
                                                            } else {
                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The invoice exists", 'r');
                                                                                                                                
                                                                if ($invoice->einvoiceid) {
                                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : The invoice " . $invoices->DocNumber . " is already fiscalized.", 'r');
                                                                                                                                        
                                                                } else {
                                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : The invoice " . $invoices->DocNumber . " is NOT fiscalized.", 'r');
                                                                    
                                                                    
                                                                    //Go Ahead and replace/update ERP details of this invoice.
                                                                    $tcsdetails = new tcsdetails($this->db);
                                                                    $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
                                                                    
                                                                    $companydetails = new organisations($this->db);
                                                                    $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);
                                                                    
                                                                    $devicedetails = new devices($this->db);
                                                                    $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
                                                                    
                                                                    $customer = new customers($this->db);
                                                                    
                                                                    $goods = array();
                                                                    $taxes = array();
                                                                    
                                                                    $deemedflag = 'NO';
                                                                    $discountflag = 'NO';
                                                                    
                                                                    $pricevatinclusive = empty($this->appsettings['PRICEVATINCLUSIVE'])? 'NO' : strtoupper($this->appsettings['PRICEVATINCLUSIVE']);//No
                                                                    
                                                                    $netamount = 0;
                                                                    $taxamount = 0;
                                                                    $grossamount = 0;
                                                                    $itemcount = 0;
                                                                    
                                                                    $tr = new taxrates($this->db);
                                                                    $taxid = NULL;
                                                                    $taxcode = NULL;
                                                                    $taxname = NULL;
                                                                    $taxcategory = NULL;
                                                                    $taxdisplaycategory = NULL;
                                                                    $taxdescription = NULL;
                                                                    $rate = 0;
                                                                    $qty = 0;
                                                                    $unit = 0;
                                                                    $discountpct = 0;
                                                                    $total = 0;
                                                                    $discount = 0;
                                                                    $gross = 0;
                                                                    $discount = 0;
                                                                    $tax = 0;
                                                                    $net = 0;
                                                                    $amount = 0;
                                                                    $product = new products($this->db);
                                                                    $measureunit = new measureunits($this->db);
                                                                    
                                                                    $buyer = array(
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
                                                                    
                                                                    $invoicedetails = array(
                                                                        'id' => NULL,
                                                                        'gooddetailgroupid' => NULL,
                                                                        'taxdetailgroupid' => NULL,
                                                                        'paymentdetailgroupid' => NULL,
                                                                        'erpinvoiceid' => NULL,
                                                                        'erpinvoiceno' => NULL,
                                                                        'antifakecode' => NULL,
                                                                        'deviceno' => trim($devicedetails->deviceno),
                                                                        'issueddate' => NULL,
                                                                        'issuedtime' => NULL,
                                                                        'operator' => NULL,
                                                                        'currency' => NULL,
                                                                        'oriinvoiceid' => NULL,
                                                                        'invoicetype' => "1",
                                                                        'invoicekind' => ($this->vatRegistered == 'Y')? "1" : "2",
                                                                        'datasource' => $this->appsettings['DEFAULTDATASOURCE'],
                                                                        'invoiceindustrycode' => $this->appsettings['DEFAULTINVOICEINDUSTRY'],
                                                                        'einvoiceid' => NULL,
                                                                        'einvoicenumber' => NULL,
                                                                        'einvoicedatamatrixcode' => NULL,
                                                                        'isbatch' => '0',
                                                                        'netamount' => NULL,
                                                                        'taxamount' => NULL,
                                                                        'grossamount' => NULL,
                                                                        'origrossamount' => NULL,
                                                                        'itemcount' => NULL,
                                                                        'modecode' => NULL,
                                                                        'modename' => NULL,
                                                                        'remarks' => NULL,
                                                                        'buyerid' => NULL,
                                                                        'sellerid' => $this->appsettings['SELLER_RECORD_ID'],
                                                                        'issueddatepdf' => NULL,
                                                                        'grossamountword' => NULL,
                                                                        'isinvalid' => 0,
                                                                        'isrefund' => 0,
                                                                        'vchtype' => "Sales",
                                                                        'vchtypename' => "Tax Invoice",
                                                                        'SyncToken' => NULL
                                                                    );
                                                                    
                                                                    $discountAppStatus = 0;
                                                                    $discountAppBalance = 0;
                                                                    $discountAppPct = 0;
                                                                    
                                                                    $CustomerRef = $invoices->CustomerRef;
                                                                    $DocNumber = $invoices->DocNumber;
                                                                    $CurrencyRef = $invoices->CurrencyRef;
                                                                    $TxnDate = $invoices->TxnDate;
                                                                    $InvoiceId = $invoices->Id;
                                                                    $SyncToken = $invoices->SyncToken;
                                                                    $TxnDate = $invoices->TxnDate;
                                                                    
                                                                    /**
                                                                     * Author: Francis Lubanga <frncslubanga@gmail.com>
                                                                     * Date: 2025-08-09
                                                                     * Description: Use the shipping address country to determine the export transactions
                                                                     */
                                                                    $ShipAddrCountry = '';
                                                                    $CustomerTypeRef = '';
                                                                    
                                                                    $invoicedetails['erpinvoiceid'] = $InvoiceId;
                                                                    $invoicedetails['erpinvoiceno'] = $DocNumber;
                                                                    
                                                                    if ($CustomerRef) {
                                                                        //Let's download the customer
                                                                        $customers = $dataService->FindbyId('customer', $CustomerRef);
                                                                        
                                                                        $custError = $dataService->getLastError();
                                                                        
                                                                        if ($custError) {
                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The operation to download ERP customers was not successful. The Response Message is: " . $custError->getResponseBody(), 'r');
                                                                        } else {
                                                                            if(isset($customers->ShipAddr)){
                                                                                $ShipAddrCountry = $customers->ShipAddr->Country;
                                                                            }
                                                                            
                                                                            if(isset($customers->CustomerTypeRef)){
                                                                                $CustomerTypeRef = $customers->CustomerTypeRef;
                                                                            }
                                                                        }
                                                                        
                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : ShipAddrCountry = " . $ShipAddrCountry, 'r');
                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : CustomerTypeRef = " . $CustomerTypeRef, 'r');
                                                                        
                                                                        if($ShipAddrCountry && $this->appsettings['EXPORTINVOICEINDUSTRY']){
                                                                            if(trim(strtolower($ShipAddrCountry)) == trim(strtolower($this->appsettings['ERPBASECOUNTRY']))){
                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The base country and the customer's shipping country is the same!", 'r');
                                                                                $invoicedetails['invoiceindustrycode'] = $this->appsettings['DEFAULTINVOICEINDUSTRY'];
                                                                            } else {
                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The base country and the customer's shipping country is NOT the same!", 'r');
                                                                                $invoicedetails['invoiceindustrycode'] = $this->appsettings['EXPORTINVOICEINDUSTRY'];
                                                                            }
                                                                        } else {
                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The shipping address was not supplied!", 'r');
                                                                        }
                                                                        
                                                                        $customer->getByCode($CustomerRef);
                                                                        
                                                                        if ($customer->id) {
                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The customer Id " . $CustomerRef . " exists on the platform", 'r');
                                                                            
                                                                            $buyer['id'] = $customer->id;
                                                                            $buyer['erpcustomerid'] = $customer->erpcustomerid;
                                                                            $buyer['erpcustomercode'] = $customer->erpcustomercode;
                                                                            $buyer['tin'] = $customer->tin;
                                                                            $buyer['ninbrn'] = $customer->ninbrn;
                                                                            $buyer['PassportNum'] = $customer->PassportNum;
                                                                            $buyer['legalname'] = $customer->legalname;
                                                                            $buyer['businessname'] = $customer->businessname;
                                                                            $buyer['address'] = $customer->address;
                                                                            $buyer['mobilephone'] = $customer->mobilephone;
                                                                            $buyer['linephone'] = $customer->linephone;
                                                                            $buyer['emailaddress'] = $customer->emailaddress;
                                                                            $buyer['placeofbusiness'] = $customer->placeofbusiness;
                                                                            $buyer['type'] = empty($customer->type)? 1 : $customer->type;
                                                                            $buyer['citizineship'] = $customer->citizineship;
                                                                            $buyer['countryCode'] = $customer->countryCode;
                                                                            $buyer['sector'] = $customer->sector;
                                                                            $buyer['sectorCode'] = $customer->sectorCode;
                                                                            $buyer['datasource'] = $customer->datasource;
                                                                            $buyer['status'] = $customer->status;
                                                                            
                                                                            
                                                                            $invoicedetails['buyerid'] = $customer->id;
                                                                        } {
                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The customer Id " . $CustomerRef . " does not exist on the platform", 'r');
                                                                            
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
                                                                                        'type' => '1', /*default all customers to B2C*/
                                                                                        'citizineship' => NULL,
                                                                                        'countryCode' => NULL,
                                                                                        'sector' => NULL,
                                                                                        'sectorCode' => NULL,
                                                                                        'datasource' => 'ERP',
                                                                                        'status' => NULL,
                                                                                    );
                                                                                    
                                                                                    try {
                                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : Customer Name: " . $customers->DisplayName, 'r');
                                                                                        
                                                                                        
                                                                                        $erpcustomerid = $customers->Id;
                                                                                        $erpcustomercode = $customers->Id;
                                                                                        $legalname = empty($customers->FullyQualifiedName)? $customers->DisplayName : $customers->FullyQualifiedName;
                                                                                        $businessname = empty($customers->FullyQualifiedName)? $customers->DisplayName : $customers->FullyQualifiedName;
                                                                                        
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
                                                                                        
                                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : Mobile: " . $mobilephone, 'r');
                                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : Email: " . $emailaddress, 'r');
                                                                                        
                                                                                        $cust['erpcustomerid'] = $erpcustomerid;
                                                                                        $cust['erpcustomercode'] = $erpcustomercode;
                                                                                        $cust['legalname'] = $legalname;
                                                                                        $cust['businessname'] = $businessname;
                                                                                        
                                                                                        
                                                                                        
                                                                                        if ($customers->Active == false) {
                                                                                            $cust['status'] = $this->appsettings['INACTIVECUSTOMERSTATUSID'];
                                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The customer is not ACTIVE.", 'r');
                                                                                        } else {
                                                                                            $cust['status'] = $this->appsettings['ACTIVECUSTOMERSTATUSID'];
                                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The customer is ACTIVE.", 'r');
                                                                                        }
                                                                                        
                                                                                        
                                                                                        if ($erpcustomercode && $legalname) {
                                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The customer does not exist", 'r');
                                                                                            $cust_status = $this->util->createcustomer($cust, $this->f3->get('SESSION.id'));
                                                                                            
                                                                                            if ($cust_status) {
                                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The customer " . $cust['legalname'] . " was created.", 'r');
                                                                                                
                                                                                                
                                                                                                $customer->getByCode($CustomerRef);
                                                                                                
                                                                                                if ($customer->id) {
                                                                                                    $buyer['id'] = $customer->id;
                                                                                                    $buyer['erpcustomerid'] = $customer->erpcustomerid;
                                                                                                    $buyer['erpcustomercode'] = $customer->erpcustomercode;
                                                                                                    $buyer['tin'] = $customer->tin;
                                                                                                    $buyer['ninbrn'] = $customer->ninbrn;
                                                                                                    $buyer['PassportNum'] = $customer->PassportNum;
                                                                                                    $buyer['legalname'] = $customer->legalname;
                                                                                                    $buyer['businessname'] = $customer->businessname;
                                                                                                    $buyer['address'] = $customer->address;
                                                                                                    $buyer['mobilephone'] = $customer->mobilephone;
                                                                                                    $buyer['linephone'] = $customer->linephone;
                                                                                                    $buyer['emailaddress'] = $customer->emailaddress;
                                                                                                    $buyer['placeofbusiness'] = $customer->placeofbusiness;
                                                                                                    //$buyer['type'] = $customer->type;
                                                                                                    $buyer['type'] = empty($customer->type)? 1 : $customer->type;
                                                                                                    $buyer['citizineship'] = $customer->citizineship;
                                                                                                    $buyer['countryCode'] = $customer->countryCode;
                                                                                                    $buyer['sector'] = $customer->sector;
                                                                                                    $buyer['sectorCode'] = $customer->sectorCode;
                                                                                                    $buyer['datasource'] = $customer->datasource;
                                                                                                    $buyer['status'] = $customer->status;
                                                                                                    
                                                                                                    
                                                                                                    $invoicedetails['buyerid'] = $customer->id;
                                                                                                } else {
                                                                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : The created customer was not retrieved successfully.", 'r');
                                                                                                }
                                                                                            } else {
                                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The customer " . $cust['legalname'] . " was NOT created.", 'r');
                                                                                            }
                                                                                        } else {
                                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The customer has no Id.", 'r');
                                                                                        }
                                                                                        
                                                                                    } catch (Exception $e) {
                                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : There was an error when processing Item " . $customers->DisplayName . ". The error is " . $e->getMessage(), 'r');
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The operation to download ERP customers did not return records.", 'r');
                                                                            }
                                                                        }
                                                                        
                                                                        
                                                                    }
                                                                    
                                                                    if(isset($invoices->Line)){
                                                                        foreach($invoices->Line as $items){
                                                                            $LineId = $items->Id;
                                                                            $LineNum = $items->LineNum;
                                                                            $Description = $items->Description;
                                                                            $ErpAmount = $items->Amount;
                                                                            $DetailType = $items->DetailType;
                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : Line Description: " . $Description, 'r');
                                                                            
                                                                            if (strtoupper($items->DetailType) == 'DISCOUNTLINEDETAIL') {
                                                                                if(isset($items->DiscountLineDetail)){
                                                                                    $PercentBased = $items->DiscountLineDetail->PercentBased;//true/false
                                                                                    $DiscountPercent = $items->DiscountLineDetail->DiscountPercent;
                                                                                }
                                                                                
                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : Discount Percent: " . $PercentBased, 'r');
                                                                                $discount = empty($ErpAmount)? 0 : (float)$ErpAmount;
                                                                                $discountpct = empty($DiscountPercent)? 0 : (float)$DiscountPercent;
                                                                                
                                                                                if (!empty($ErpAmount)){
                                                                                    $discountAppStatus = 1;
                                                                                    $discountAppBalance = $ErpAmount;
                                                                                    $discountAppPct = $discountpct;
                                                                                }
                                                                            }
                                                                            
                                                                            if (strtoupper($items->DetailType) == 'SALESITEMLINEDETAIL') {
                                                                                if(isset($items->SalesItemLineDetail)){
                                                                                    $ItemRef = $items->SalesItemLineDetail->ItemRef;
                                                                                    $UnitPrice = $items->SalesItemLineDetail->UnitPrice;
                                                                                    $Qty = $items->SalesItemLineDetail->Qty;
                                                                                    $TaxCodeRef = $items->SalesItemLineDetail->TaxCodeRef;
                                                                                }
                                                                                
                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : Sales Line Unit Price Ref: " . $UnitPrice, 'r');
                                                                                
                                                                                
                                                                                $product->getByErpCode($ItemRef);
                                                                                
                                                                                if ($product->code) {
                                                                                    $measureunit->getByCode($product->measureunit);
                                                                                } else {
                                                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : The Item does not exist on the platform", 'r');
                                                                                }
                                                                                
                                                                                $qty = $Qty;
                                                                                $unit = $UnitPrice;
                                                                                $amount = $ErpAmount;
                                                                                
                                                                                /**
                                                                                 * Can we determine the DISCOUNT PERCENTAGE incase it is a line DISCOUNT provided?
                                                                                 */
                                                                                if ($discountpct == 0 && $discount > 0) {
                                                                                    $discountpct = $discount/$amount;
                                                                                    $discount = 0;
                                                                                } else {
                                                                                    $discount = 0;
                                                                                }
                                                                                
                                                                                /**
                                                                                 * Date: 2025-05-30
                                                                                 * Author: Francis Lubanga <frncslubanga@gmail.com>
                                                                                 * Description: Hard code the tax rate for Commodity Category Code 96010102
                                                                                 */
                                                                                if (trim($product->commoditycategorycode) == '96010102') {
                                                                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : The product code is 96010102. Hard coding the tax id to 13", 'r');
                                                                                    $taxid = '13'; // 13 is the tax id for VAT OUT OF SCOPE
                                                                                } else {
                                                                                    $taxid = $this->util->getinvoicetaxrate_v2($invoicedetails['invoiceindustrycode'], $customer->type, $product->code, $customer->tin, $this->appsettings['OVERRIDE_TAXRATE_FLAG'], $this->appsettings['TAXPAYER_CHECK_FLAG']);
                                                                                }
                                                                                
                                                                                //$taxid = $this->util->getinvoicetaxrate_v2($this->appsettings['DEFAULTINVOICEINDUSTRY'], $customer->type, $product->code, $customer->tin, $this->appsettings['OVERRIDE_TAXRATE_FLAG'], $this->appsettings['TAXPAYER_CHECK_FLAG']);
                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The computed TAXID is " . $taxid, 'r');
                                                                                
                                                                                if (!$taxid) {
                                                                                    $taxid = $this->appsettings['STANDARDTAXRATE'];
                                                                                }
                                                                                
                                                                                
                                                                                if ($taxid == $this->appsettings['DEEMEDTAXRATE']) {
                                                                                    $deemedflag = 'YES';
                                                                                } else {
                                                                                    $deemedflag = 'NO';
                                                                                }
                                                                                
                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The final TAXID is " . $taxid, 'r');
                                                                                
                                                                                $tr = new taxrates($this->db);
                                                                                $tr->getByID($taxid);
                                                                                $taxcode = $tr->code;
                                                                                $taxname = $tr->name;
                                                                                $taxcategory = $tr->category;
                                                                                $taxdisplaycategory = $tr->displayCategoryCode;
                                                                                $taxdescription = $tr->description;
                                                                                $rate = $tr->rate? $tr->rate : 0;
                                                                                
                                                                                
                                                                                if (strtoupper(trim($pricevatinclusive)) == 'YES') {
                                                                                    //Use the figures as they come from the ERP
                                                                                    $total = ($qty * $unit);//??
                                                                                    
                                                                                    //$discount = ($discountpct/100) * $total; //discount is already calculated by QB
                                                                                    
                                                                                    /**
                                                                                     * Modification Date: 2021-01-26
                                                                                     * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                                     * */
                                                                                    //$gross = $total - $discount;
                                                                                    $gross = $total;
                                                                                    
                                                                                    $discount = (-1) * $discount;
                                                                                    
                                                                                    $tax = ($gross/($rate + 1)) * $rate; //??
                                                                                    
                                                                                    $net = $gross - $tax;
                                                                                } elseif (strtoupper(trim($pricevatinclusive)) == 'NO') {
                                                                                    //Manually calculate figures
                                                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : Rebasing the prices", 'r');
                                                                                    
                                                                                    if ($rate > 0) {
                                                                                        $unit = $unit * ($rate + 1);
                                                                                    }
                                                                                    
                                                                                    $total = ($qty * $unit);//??
                                                                                    
                                                                                    //$discount = ($discountpct/100) * $total;
                                                                                    
                                                                                    /**
                                                                                     * Modification Date: 2021-01-26
                                                                                     * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                                     * */
                                                                                    //$gross = $total - $discount;
                                                                                    $gross = $total;
                                                                                    
                                                                                    $discount = (-1) * $discount;
                                                                                    
                                                                                    $tax = ($gross/($rate + 1)) * $rate; //??
                                                                                    
                                                                                    $net = $gross - $tax;
                                                                                }
                                                                                
                                                                                /**
                                                                                 * Over-ride tax, if the tax payer is not VAT registered
                                                                                 */
                                                                                if ($this->vatRegistered == 'N') {
                                                                                    $tax = 0;
                                                                                    $taxcategory = NULL;
                                                                                    $taxcode = NULL;
                                                                                }
                                                                                
                                                                                
                                                                                
                                                                                $netamount = $netamount + $net;
                                                                                $taxamount = $taxamount + $tax;
                                                                                
                                                                                $grossamount = $grossamount + $gross;
                                                                                $itemcount = $itemcount + 1;
                                                                                
                                                                                if ($discount == 0) {
                                                                                    $discountflag = 'NO';
                                                                                } else {
                                                                                    $discountflag = 'YES';
                                                                                }
                                                                                
                                                                                $goods[] = array(
                                                                                    'groupid' => NULL,
                                                                                    'item' => $product->name,
                                                                                    'itemcode' => $product->code,
                                                                                    'qty' => $qty,
                                                                                    'unitofmeasure' => $product->measureunit,
                                                                                    'unitprice' => $unit,
                                                                                    'total' => $total,
                                                                                    'taxid' => $taxid,
                                                                                    'taxrate' => $rate,
                                                                                    'tax' => $tax,
                                                                                    'discounttotal' => $discount,
                                                                                    'discounttaxrate' => $rate,
                                                                                    'discountpercentage' => $discountpct,
                                                                                    'ordernumber' => NULL,
                                                                                    'discountflag' => trim($discountflag) == 'NO'? '2' : '1',
                                                                                    'deemedflag' => (strtoupper(trim($deemedflag)) == 'NO'? '2' : '1'),
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
                                                                                    'taxdisplaycategory' => $taxdisplaycategory,
                                                                                    'taxcategory' => $taxcategory,
                                                                                    'taxcategoryCode' => $taxcode,
                                                                                    'unitofmeasurename' => $measureunit->name,
                                                                                    'vatProjectId' => $customer->vatProjectId,
                                                                                    'vatProjectName' => $customer->vatProjectName,
                                                                                    'hsCode' => $product->hsCode,
                                                                                    'hsName' => $product->hsNode,
                                                                                    'totalWeight' => empty($product->weight)? 0 : $product->weight,
                                                                                    'pieceQty' => trim($invoicedetails['invoiceindustrycode']) == '102'? $qty : 0,
                                                                                    'deemedExemptCode' => NULL,
                                                                                    'pieceMeasureUnit' => $product->piecemeasureunit
                                                                                );
                                                                                
                                                                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The TAXCODE is " . $taxcode, 'r');
                                                                                
                                                                                
                                                                                if ($this->vatRegistered == 'Y') {
                                                                                    $taxes[] = array(
                                                                                        'discountflag' => trim($discountflag) == 'NO'? '2' : '1',
                                                                                        'discounttotal' => $discount,
                                                                                        'discounttaxrate' => $rate,
                                                                                        'discountpercentage' => $discountpct,
                                                                                        'd_netamount' => NULL,
                                                                                        'd_taxamount' => NULL,
                                                                                        'd_grossamount' => NULL,
                                                                                        'groupid' => NULL,
                                                                                        'goodid' => NULL,
                                                                                        'taxdisplaycategory' => $taxdisplaycategory,
                                                                                        'taxcategory' => $taxcategory,
                                                                                        'taxcategoryCode' => $taxcode,
                                                                                        'netamount' => $net,
                                                                                        'taxrate' => $rate,
                                                                                        'taxamount' => $tax,
                                                                                        'grossamount' => $gross,
                                                                                        'exciseunit' => NULL,
                                                                                        'excisecurrency' => NULL,
                                                                                        'taxratename' => $taxname,
                                                                                        'taxdescription' => $taxdescription
                                                                                    );
                                                                                }
                                                                            }
                                                                        }//foreach($invoices->Line as $items)
                                                                    }//if(isset($invoices->Line))
                                                                        
                                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : Discount App Status: " . $discountAppStatus, 'r');
                                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : Discount App Balance: " . $discountAppBalance, 'r');
                                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : Discount App Percentage: " . $discountAppPct, 'r');
                                                                    
                                                                    if ($discountAppStatus == 1) {
                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : Applying Discounts", 'r');
                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : Customer Type " . $customer->type, 'r');
                                                                        list($goods, $taxes) = $this->util->applyDiscount($goods, $taxes, $discountAppBalance, $customer->type, $customer->tin, NULL);
                                                                    }
                                                                        
                                                                    if(isset($invoices->TxnTaxDetail)){
                                                                        $TxnTaxCodeRef = $invoices->TxnTaxDetail->TxnTaxCodeRef;
                                                                        $TotalTax = $invoices->TxnTaxDetail->TotalTax;
                                                                        
                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : Tax Ref: " . $TxnTaxCodeRef, 'r');
                                                                        
                                                                        if(isset($invoices->TxnTaxDetail->TaxLine)){
                                                                            $TaxAmount = $invoices->TxnTaxDetail->TaxLine->Amount;
                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : Total Tax Amount: " . $TaxAmount, 'r');
                                                                            
                                                                            if(isset($invoices->TxnTaxDetail->TaxLine->DetailType)){
                                                                                if (strtoupper($invoices->TxnTaxDetail->TaxLine->DetailType) == 'TAXLINEDETAIL') {
                                                                                    if(isset($invoices->TxnTaxDetail->TaxLine->TaxLineDetail)){
                                                                                        $TaxRateRef = $invoices->TxnTaxDetail->TaxLine->TaxLineDetail->TaxRateRef;
                                                                                        $TaxPercentBased = $invoices->TxnTaxDetail->TaxLine->TaxLineDetail->PercentBased;
                                                                                        $TaxPercent = $invoices->TxnTaxDetail->TaxLine->TaxLineDetail->TaxPercent;
                                                                                        $NetAmountTaxable = $invoices->TxnTaxDetail->TaxLine->TaxLineDetail->NetAmountTaxable;
                                                                                    }
                                                                                    
                                                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : Tax Line Net Amount: " . $NetAmountTaxable, 'r');
                                                                                }
                                                                            }
                                                                        }
                                                                    }//if(isset($invoices->TxnTaxDetail))
                                                                        
                                                                    if(isset($invoices->CustomField)){
                                                                        foreach($invoices->CustomField as $fields){
                                                                            $FieldDefinitionId = $fields->DefinitionId;
                                                                            $FieldName = $fields->Name;
                                                                            $FieldType = $fields->Type;//StringType
                                                                            $FieldStringValue = $fields->StringValue;
                                                                            
                                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : Customer Field Name: " . $FieldName, 'r');
                                                                        }//foreach($invoices->CustomField as $items)
                                                                    }//if(isset($invoices->CustomField))
                                                                        
                                                                    $invoicedetails['operator'] = $this->f3->get('SESSION.username');
                                                                    $invoicedetails['currency'] = $this->util->getcurrency(trim($CurrencyRef));
                                                                    $invoicedetails['SyncToken'] = $SyncToken;
                                                                    $invoicedetails['issueddate'] = $TxnDate;
                                                                    $invoicedetails['issuedtime'] = $TxnDate;
                                                                    $invoicedetails['issueddatepdf'] = $TxnDate;
                                                                    $invoicedetails['itemcount'] = $itemcount;
                                                                    
                                                                    $invoicedetails['netamount'] = $netamount;
                                                                    $invoicedetails['taxamount'] = $taxamount;
                                                                    $invoicedetails['grossamount'] = $grossamount;
                                                                    $invoicedetails['origrossamount'] = 0;
                                                                    
                                                                    $invoicedetails['remarks'] = "The invoice DocNumber " . $DocNumber . " and Id " . $InvoiceId . " uploaded using the QBO API";
                                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : The Sync Token is " . $SyncToken, 'r');
                                                                    
                                                                    
                                                                    $invoicedetails['id'] = $invoice->id;
                                                                    $invoicedetails['gooddetailgroupid'] = $invoice->gooddetailgroupid;
                                                                    $invoicedetails['taxdetailgroupid'] = $invoice->taxdetailgroupid;
                                                                    $invoicedetails['paymentdetailgroupid'] = $invoice->paymentdetailgroupid;
                                                                    
                                                                    $inv_status = $this->util->updateinvoice($invoicedetails, $goods, $taxes, $buyer, $this->f3->get('SESSION.id'));
                                                                    
                                                                    if ($inv_status) {
                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : The invoice " . $DocNumber . " was created.", 'r');
                                                                    } else {
                                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : The invoice " . $DocNumber . " was NOT created.", 'r');
                                                                    }
                                                                    
                                                                    //Clear/reset some variables
                                                                    unset($goods);
                                                                    unset($taxes);
                                                                    $deemedflag = 'NO';
                                                                    $discountflag = 'NO';
                                                                    $netamount = 0;
                                                                    $taxamount = 0;
                                                                    $grossamount = 0;
                                                                    $itemcount = 0;
                                                                    $taxid = NULL;
                                                                    $taxcode = NULL;
                                                                    $taxname = NULL;
                                                                    $taxcategory = NULL;
                                                                    $taxdisplaycategory = NULL;
                                                                    $taxdescription = NULL;
                                                                    $rate = 0;
                                                                    $qty = 0;
                                                                    $unit = 0;
                                                                    $discountpct = 0;
                                                                    $total = 0;
                                                                    $discount = 0;
                                                                    $gross = 0;
                                                                    $discount = 0;
                                                                    $tax = 0;
                                                                    $net = 0;
                                                                    $amount = 0;
                                                                    //unset($buyer);
                                                                    //unset($invoicedetails);
                                                                    $discountAppStatus = 0;
                                                                    $discountAppBalance = 0;
                                                                    $discountAppPct = 0;
                                                                    
                                                                    $CustomerRef = NULL;
                                                                    $DocNumber = NULL;
                                                                    $CurrencyRef = NULL;
                                                                    $TxnDate = NULL;
                                                                    $InvoiceId = NULL;
                                                                    $SyncToken = NULL;
                                                                    $TxnDate = NULL;
                                                                }
                                                            }
                                                            
                                                            
                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The operation to fetch ERP invoices was successful.", 'r');
                                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " was successful");
                                                            self::$systemalert = "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " was successful.";
                                                        } else {
                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The invoice has no Id.", 'r');
                                                            
                                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The operation to fetch ERP invoices was not successful.", 'r');
                                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful");
                                                            self::$systemalert = "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful.";
                                                        }
                                                    } else {
                                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : The operation to fetch ERP invoices did not return any records", 'r');
                                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " did not return any records");
                                                        self::$systemalert = "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " did not return any records.";
                                                    }
                                                } else {
                                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : The operation to fetch ERP invoices did not return any records", 'r');
                                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " did not return any records");
                                                    self::$systemalert = "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " did not return any records.";
                                                }
                                            }
                                            
                                        } else {
                                            $this->logger->write("Invoice Controller : fetchErpInvoice() : The operation to fetch ERP invoices was not successful. Please connect to ERP first.", 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                            self::$systemalert = "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                                        }
                                    } catch (Exception $e) {
                                        $this->logger->write("Invoice Controller : fetchErpInvoice() : The operation to fetch ERP invoices was not successful. The error is: " . $e->getMessage(), 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful");
                                        self::$systemalert = "The operation to fetch ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                                    }
                                } else {
                                    $this->logger->write("Invoice Controller : fetchErpInvoice() : The invoice does not have a Document Number.", 'r');
                                    self::$systemalert = "Sorry. The invoice does not have a Document Number. Please use the general download option.";
                                }
                            } else {
                                $this->logger->write("Invoice Controller : fetchErpInvoice() : The integrated ERP is unknown.", 'r');
                                self::$systemalert = "Sorry. The integrated ERP is unknown.";
                            }
                        } else {
                            $this->logger->write("Invoice Controller : fetchErpInvoice() : We are unable to indentify the currently integrated ERP.", 'r');
                            self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                        }
                    }
                } else {
                    $this->logger->write("Invoice Controller : fetchErpInvoice() : The invoice was not created by the ERP.", 'r');
                    self::$systemalert = "Sorry. The invoice was not created by the ERP.";
                    
                    $this->f3->set('systemalert', self::$systemalert);
                    self::index();
                }
                
            } else {
                $this->logger->write("Invoice Controller : fetchErpInvoice() : The invoice was not specified.", 'r');
                self::$systemalert = "Sorry. The invoice was not specified.";
                
                $this->f3->set('systemalert', self::$systemalert);
                self::index();
            }
        } else {
            $this->logger->write("Invoice Controller : fetchErpInvoice() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    
    
    
    /**
     *	@name updateErpInvoice
     *  @desc download invoices from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function updateErpInvoice(){
        $operation = NULL; //tblevents
        $permission = 'SYNCINVOICES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Invoice Controller : updateErpInvoice() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            $id = $this->f3->get('POST.erpupdateinvoiceid');
            $invoice = new invoices($this->db);
            $invoice->getByID($id);
            $this->logger->write("Product Controller : updateErpInvoice() : The invoice id is " . $this->f3->get('POST.erpupdateinvoiceid'), 'r');
            
            if ($id) {
                
                if ($invoice->erpinvoiceid) {
                    if ($this->platformMode == 'ERP') {
                        $this->logger->write("Invoice Controller : updateErpInvoice() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                        self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
                    } else {
                        $this->logger->write("Invoice Controller : updateErpInvoice() : The platform is integrated.", 'r');
                        
                        if ($this->integratedErp) {
                            /**
                             * Check on integrated ERP type
                             */
                            $this->logger->write("Invoice Controller : updateErpInvoice() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                            
                            if (strtoupper($this->integratedErp) == 'QBO') {
                                $this->logger->write("Invoice Controller : updateErpInvoice() : The integrated ERP is Quicbooks Online.", 'r');
                                
                                $docType = $invoice->docTypeCode;
                                $docType = empty($docType)? $this->appsettings['INVOICEERPDOCTYPE'] : trim($docType);
                                
                                $qry = '';
                                
                                if ($docType == trim($this->appsettings['INVOICEERPDOCTYPE'])) {
                                    $qry = 'SELECT * FROM Invoice';
                                } elseif ($docType == trim($this->appsettings['SALESRECEIPTERPDOCTYPE'])){
                                    $qry = 'SELECT * FROM SalesReceipt';
                                } else {
                                    $qry = 'SELECT * FROM Invoice';
                                }
                                
                                if ($invoice->erpinvoiceno) {
                                    $qry = $qry . " Where DocNumber = '" . $invoice->erpinvoiceno . "'";
                                    
                                    $this->logger->write("Invoice Controller : downloadErpInvoices() : The query is: " . $qry, 'r');
                                    
                                    
                                    try {
                                        if ($this->appsettings['QBACCESSTOKEN'] !== null) {
                                            // Create SDK instance
                                            $authMode = $this->appsettings['QBAUTH_MODE'];
                                            $ClientID = $this->appsettings['QBCLIENT_ID'];
                                            $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                            $baseUrl = $this->appsettings['QBBASE_URL'];
                                            $QBORealmID = $this->appsettings['QBREALMID'];
                                            
                                            $accessToken = $this->appsettings['QBACCESSTOKEN'];
                                            
                                            $dataService = DataService::Configure(array(
                                                'auth_mode' => $authMode,
                                                'ClientID' => $ClientID,
                                                'ClientSecret' =>  $ClientSecret,
                                                'baseUrl' => $baseUrl,
                                                'refreshTokenKey' => $this->appsettings['QBREFRESHTOKEN'],
                                                'QBORealmID' => $QBORealmID,
                                                'accessTokenKey' => $this->appsettings['QBACCESSTOKEN']
                                            ));
                                            
                                            $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                            $dataService->throwExceptionOnError(true);
                                            
                                            $invoices = $dataService->Query($qry);
                                            
                                            $error = $dataService->getLastError();
                                            
                                            if ($error) {
                                                $this->logger->write("Invoice Controller : updateErpInvoice() : The operation to update ERP invoices was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful");
                                                self::$systemalert = "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful.";
                                            } else {
                                                if(isset($invoices)){
                                                    if(!empty($invoices) && sizeof($invoices) == 1){
                                                        $theInvoice = current($invoices);
                                                        
                                                        $this->logger->write("Invoice Controller : updateErpInvoice() : The Sync Token is " . $theInvoice->SyncToken, 'r');
                                                        $this->logger->write("Invoice Controller : updateErpInvoice() : The DocNumber is " . $theInvoice->DocNumber, 'r');
                                                        
                                                        
                                                        if ($docType == trim($this->appsettings['INVOICEERPDOCTYPE'])) {
                                                            $updatedInvoice = Invoice::update($theInvoice, [
                                                                "sparse" => true,
                                                                "SyncToken" => $theInvoice->SyncToken,
                                                                "CustomField" => [
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], //Fiscal Doc. Num
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $invoice->einvoicenumber
                                                                    ],
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], //Verification Co
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $invoice->antifakecode
                                                                    ]
                                                                ]
                                                            ]);
                                                        } elseif ($docType == trim($this->appsettings['SALESRECEIPTERPDOCTYPE'])){
                                                            $updatedInvoice = SalesReceipt::update($theInvoice, [
                                                                "sparse" => true,
                                                                "SyncToken" => $theInvoice->SyncToken,
                                                                "CustomField" => [
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], //Fiscal Doc. Num
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $invoice->einvoicenumber
                                                                    ],
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], //Verification Co
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $invoice->antifakecode
                                                                    ]
                                                                ]
                                                            ]);
                                                        } else {
                                                            $updatedInvoice = Invoice::update($theInvoice, [
                                                                "sparse" => true,
                                                                "SyncToken" => $theInvoice->SyncToken,
                                                                "CustomField" => [
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], //Fiscal Doc. Num
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $invoice->einvoicenumber
                                                                    ],
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], //Verification Co
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $invoice->antifakecode
                                                                    ]
                                                                ]
                                                            ]);
                                                        }
                                                        
                                                        $updatedResult = $dataService->Update($updatedInvoice);
                                                        //print_r($updatedResult);
                                                        $updatederror = $dataService->getLastError();
                                                        
                                                        if ($updatederror) {
                                                            $this->logger->write("Invoice Controller : updateErpInvoice() : The operation to update ERP invoices was not successful. The Response Message is: " . $updatederror->getResponseBody(), 'r');
                                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful");
                                                            self::$systemalert = "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " was notsuccessful.";
                                                        }
                                                        else {
                                                            $this->logger->write("Invoice Controller : updateErpInvoice() : The operation to update ERP invoices was successful.", 'r');
                                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " was successful");
                                                            self::$systemalert = "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " was successful.";
                                                        }
                                                    } else {
                                                        $this->logger->write("Invoice Controller : updateErpInvoice() : The operation to update ERP invoices did not return any records", 'r');
                                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " did not return any records");
                                                        self::$systemalert = "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " did not return any records.";
                                                    }
                                                } else {
                                                    $this->logger->write("Invoice Controller : updateErpInvoice() : The operation to update ERP invoices did not return any records", 'r');
                                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " did not return any records");
                                                    self::$systemalert = "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " did not return any records.";
                                                }
                                            }
                                        } else {
                                            $this->logger->write("Invoice Controller : updateErpInvoice() : The operation to update ERP invoices was not successful. Please connect to ERP first.", 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                            self::$systemalert = "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                                        }
                                    } catch (Exception $e) {
                                        $this->logger->write("Invoice Controller : updateErpInvoice() : The operation to update ERP invoices was not successful. The error is: " . $e->getMessage(), 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful");
                                        self::$systemalert = "The operation to update ERP invoices by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                                    }
                                } else {
                                    $this->logger->write("Invoice Controller : updateErpInvoice() : The invoice does not have a Document Number.", 'r');
                                    self::$systemalert = "Sorry. The invoice does not have a Document Number. Please re-download it.";
                                } 
                            } else {
                                $this->logger->write("Invoice Controller : updateErpInvoice() : The integrated ERP is unknown.", 'r');
                                self::$systemalert = "Sorry. The integrated ERP is unknown.";
                            }
                        } else {
                            $this->logger->write("Invoice Controller : updateErpInvoice() : We are unable to indentify the currently integrated ERP.", 'r');
                            self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                        }
                    }
                } else {
                    $this->logger->write("Invoice Controller : updateErpInvoice() : The invoice was not created by the ERP.", 'r');
                    self::$systemalert = "Sorry. The invoice was not created by the ERP.";
                    
                    $this->f3->set('systemalert', self::$systemalert);
                    self::index();
                }
                
            } else {
                $this->logger->write("Invoice Controller : updateErpInvoice() : The invoice was not specified.", 'r');
                self::$systemalert = "Sorry. The invoice was not specified.";
                
                $this->f3->set('systemalert', self::$systemalert);
                self::index();
            }
        } else {
            $this->logger->write("Invoice Controller : updateErpInvoice() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
}

?>