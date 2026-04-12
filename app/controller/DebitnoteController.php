<?php
/**
 * @name DebitnoteController
 * @desc This file is part of the etaxware system. The is the Debitnote controller class
 * @date 11-05-2020
 * @file DebitnoteController.php
 * @path ./app/controller/DebitnoteController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
Class DebitnoteController extends MainController{
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
        $permission = 'VIEWDEBITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Debitnote Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $invoicekind = new invoicekinds($this->db);
            $invoicekinds = $invoicekind->all();
            $this->f3->set('invoicekinds', $invoicekinds);
            
            
            $this->f3->set('pagetitle','Debit Notes');
            $this->f3->set('pagecontent','Debitnote.htm');
            $this->f3->set('pagescripts','DebitnoteFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Debitnote Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    /**
     *	@name view
     *  @desc view Debitnote
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = '', $tab = '', $tabpane = '') {
        $operation = NULL; //tblevents
        $permission = 'VIEWDEBITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Debitnote Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Debitnote Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Debitnote Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $invoicetype = new invoicetypes($this->db);
            $invoicetypes = $invoicetype->all();
            $this->f3->set('invoicetypes', $invoicetypes);
            
            $debitnotereasoncode = new debitnotereasoncodes($this->db);
            $debitnotereasoncodes = $debitnotereasoncode->all();
            $this->f3->set('debitnotereasoncodes', $debitnotereasoncodes);
            
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
            //$this->logger->write($this->db->log(TRUE), 'r');
            
            $deliveryterm = new deliveryterms($this->db);
            $deliveryterms = $deliveryterm->all();
            $this->f3->set('deliveryterms', $deliveryterms);
            
            if (is_string($tab) && is_string($tabpane)){
                $this->logger->write("Debitnote Controller : view() : The value of v_id is " . $v_id, 'r');
                $this->logger->write("Debitnote Controller : view() : The value of tab is " . $tab, 'r');
                $this->logger->write("Debitnote Controller : view() : The value of tabpane " . $tabpane, 'r');
            } 
            
            if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
                $id = trim($this->f3->get('PARAMS[id]'));
                $this->logger->write("Debitnote Controller : view() : The is a GET call & id to view is " . $id, 'r');
                
                $debitnote = new debitnotes($this->db);
                $debitnote->getByID($id);
                $this->f3->set('debitnote', $debitnote);
                
                $buyer = new buyers($this->db);
                $buyer->getByID($debitnote->buyerid);
                $this->f3->set('buyer', $buyer);
                
                if (is_string($tab) && is_string($tabpane)){//this check is necessary for cases where the GET request is system initiated. The params sent to the view functions are non-string.
                    $this->f3->set('currenttab', $tab);
                    $this->f3->set('currenttabpane', $tabpane);
                } else {
                    $this->f3->set('currenttab', 'tab_general');
                    $this->f3->set('currenttabpane', 'tab_1');
                    $this->f3->set('path', '../' . $this->path);
                }
                
                $this->f3->set('pagetitle','Edit Debitnote | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path); //overide the main solution path
            } elseif (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id')))) {//Open EDIT mode
                $id = trim($this->f3->get('POST.id'));
                $this->logger->write("Debitnote Controller : view() : This is a POST call & the id to view is " . $id, 'r');
                
                $debitnote = new debitnotes($this->db);
                $debitnote->getByID($id);
                $this->f3->set('debitnote', $debitnote);
                
                $buyer = new buyers($this->db);
                $buyer->getByID($debitnote->buyerid);
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
                
                $this->f3->set('pagetitle','Edit Debitnote | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
            } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
                $id = trim($v_id);
                $this->logger->write("Debitnote Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
                
                $debitnote = new debitnotes($this->db);
                $debitnote->getByID($id);
                $this->f3->set('debitnote', $debitnote);
                
                $buyer = new buyers($this->db);
                $buyer->getByID($debitnote->buyerid);
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
                
                $this->f3->set('pagetitle','Edit Debitnote | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
                
                $this->f3->set('pagecontent','EditDebitnote.htm');
                $this->f3->set('pagescripts','EditDebitnoteFooter.htm');
                echo \Template::instance()->render('Layout.htm');
                exit(); //exit the function so no extra code executes
            } else {
                $this->logger->write("Debitnote Controller : view() : No id was selected", 'r');
                $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page
                exit();
            }
            
            $this->logger->write("Debitnote Controller : view() : The currenttab has been set to " . $this->f3->get('currenttab'), 'r');
            $this->logger->write("Debitnote Controller : view() : The currenttabpane has been set to " . $this->f3->get('currenttabpane'), 'r');
            
            $this->f3->set('pagecontent','EditDebitnote.htm');
            $this->f3->set('pagescripts','EditDebitnoteFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Debitnote Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name add
     *  @desc add Debitnote
     *	@return NULL
     *	@param NULL
     **/
    function add() {
        $operation = NULL; //tblevents
        $permission = 'CREATEDEBITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Debitnote Controller : add() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {           
            //@TODO Display a new form
            $invoicetype = new invoicetypes($this->db);
            $invoicetypes = $invoicetype->all();
            $this->f3->set('invoicetypes', $invoicetypes);
            
            $debitnotereasoncode = new debitnotereasoncodes($this->db);
            $debitnotereasoncodes = $debitnotereasoncode->all();
            $this->f3->set('debitnotereasoncodes', $debitnotereasoncodes);
            
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
            
            
            $debitnote = array(
                "id" => NULL,
                "name" => '',
                "code" => '',
                "description" => ''
            );
            $this->f3->set('debitnote', $debitnote);
            
            $this->f3->set('pagetitle','Create Debit Note');
            
            $this->f3->set('pagecontent','EditDebitnote.htm');
            $this->f3->set('pagescripts','EditDebitnoteFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Debitnote Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    /**
     * edit debitnote
     *
     * @name edit
     * @return NULL
     * @param NULL
     */
    function edit(){
        $debitnote = new debitnotes($this->db);
        $currenttab = trim($this->f3->get('POST.currenttab'));
        $currenttabpane = trim($this->f3->get('POST.currenttabpane'));
        $id = 0;
        
        $this->logger->write("Debitnote Controller : edit() : Editing of credit note started", 'r');
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        if (trim($this->f3->get('POST.debitnoteid')) !== '' || !empty(trim($this->f3->get('POST.debitnoteid'))) || trim($this->f3->get('POST.sellerdebitnoteid')) !== '' || !empty(trim($this->f3->get('POST.sellerdebitnoteid'))) || trim($this->f3->get('POST.buyerdebitnoteid')) !== '' || !empty(trim($this->f3->get('POST.buyerdebitnoteid'))) || trim($this->f3->get('POST.gooddebitnoteid')) !== '' || !empty(trim($this->f3->get('POST.gooddebitnoteid'))) || trim($this->f3->get('POST.paymentdebitnoteid')) !== '' || !empty(trim($this->f3->get('POST.paymentdebitnoteid'))) || trim($this->f3->get('POST.deletegooddebitnoteid')) !== '' || !empty(trim($this->f3->get('POST.deletegooddebitnoteid'))) || trim($this->f3->get('POST.addpaymentdebitnoteid')) !== '' || !empty(trim($this->f3->get('POST.addpaymentdebitnoteid'))) || trim($this->f3->get('POST.deletepaymentdebitnoteid')) !== '' || !empty(trim($this->f3->get('POST.deletepaymentdebitnoteid'))) || trim($this->f3->get('POST.editgooddebitnoteid')) !== '' || !empty(trim($this->f3->get('POST.editgooddebitnoteid')))){
            $operation = NULL; // tblevents
            $permission = 'EDITDEBITNOTES'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications
            
            $this->logger->write("Debitnote Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                
                $currenttab = !empty(trim($this->f3->get('POST.currenttab')))? trim($this->f3->get('POST.currenttab')) : (!empty(trim($this->f3->get('POST.sellercurrenttab')))? trim($this->f3->get('POST.sellercurrenttab')) : (!empty(trim($this->f3->get('POST.buyercurrenttab')))? trim($this->f3->get('POST.buyercurrenttab')) : (!empty(trim($this->f3->get('POST.goodcurrenttab')))? trim($this->f3->get('POST.goodcurrenttab')) : (!empty(trim($this->f3->get('POST.paymentcurrenttab')))? trim($this->f3->get('POST.paymentcurrenttab')) : (!empty(trim($this->f3->get('POST.deletegoodcurrenttab')))? trim($this->f3->get('POST.deletegoodcurrenttab')) : (!empty(trim($this->f3->get('POST.deletepaymentcurrenttab')))? trim($this->f3->get('POST.deletepaymentcurrenttab')) : (!empty(trim($this->f3->get('POST.addpaymentcurrenttab')))? trim($this->f3->get('POST.addpaymentcurrenttab')) : trim($this->f3->get('POST.editgoodcurrenttab')))))))));
                $currenttabpane = !empty(trim($this->f3->get('POST.currenttabpane')))? trim($this->f3->get('POST.currenttabpane')) : (!empty(trim($this->f3->get('POST.sellercurrenttabpane')))? trim($this->f3->get('POST.sellercurrenttabpane')) : (!empty(trim($this->f3->get('POST.buyercurrenttabpane')))? trim($this->f3->get('POST.buyercurrenttabpane')) : (!empty(trim($this->f3->get('POST.goodcurrenttabpane')))? trim($this->f3->get('POST.goodcurrenttabpane')) : (!empty(trim($this->f3->get('POST.paymentcurrenttabpane')))? trim($this->f3->get('POST.paymentcurrenttabpane')) : (!empty(trim($this->f3->get('POST.deletegoodcurrenttabpane')))? trim($this->f3->get('POST.deletegoodcurrenttabpane')) : (!empty(trim($this->f3->get('POST.addpaymentcurrenttabpane')))? trim($this->f3->get('POST.addpaymentcurrenttabpane')) : (!empty(trim($this->f3->get('POST.deletepaymentcurrenttabpane')))? trim($this->f3->get('POST.deletepaymentcurrenttabpane')) : trim($this->f3->get('POST.editgoodcurrenttabpane')))))))));
                                
                if ($currenttab == 'tab_general') {
                    $id = trim($this->f3->get('POST.debitnoteid'));
                    $this->logger->write("Debitnote Controller : edit() : tab_general :  The id to be edited is " . $id, 'r');
                    $debitnote->getByID($id);
                    
                    $this->f3->set('POST.erpdebitnoteid', $this->f3->get('POST.erpdebitnoteid'));
                    
                    $this->f3->set('POST.erpdebitnoteno', $this->f3->get('POST.erpdebitnoteno'));

                    $this->f3->set('POST.invoicetype', $this->f3->get('POST.invoicetype'));
                    $this->f3->set('POST.invoicekind', $this->f3->get('POST.invoicekind'));
                    
                    if(trim($this->f3->get('POST.datasource')) !== '' || ! empty(trim($this->f3->get('POST.datasource')))) {
                        $this->f3->set('POST.datasource', $this->f3->get('POST.datasource'));
                    } else {
                        $this->f3->set('POST.datasource', $debitnote->datasource);
                    } 
                    
                    if(trim($this->f3->get('POST.reasoncode')) !== '' || ! empty(trim($this->f3->get('POST.reasoncode')))) {
                        $this->f3->set('POST.reasoncode', $this->f3->get('POST.reasoncode'));
                    } else {
                        $this->f3->set('POST.reasoncode', $debitnote->reasoncode);
                    } 
                    
                    if(trim($this->f3->get('POST.reason')) !== '' || ! empty(trim($this->f3->get('POST.reason')))) {
                        $this->f3->set('POST.reason', $this->f3->get('POST.reason'));
                    } else {
                        $this->f3->set('POST.reason', $debitnote->reason);
                    } 
                    
                    $this->f3->set('POST.remarks', $this->f3->get('POST.remarks'));
                    
                    $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                    $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                    
                    try {
                        /**
                         * Logic for editing the original invoice of a debit note
                         * 1. Check if the user wishes to change the original invoice
                         * 2. Retrieve sidea & sideb datasource details from tbldatasourcegroupdetails
                         * 3. Update/insert sidea & sideb datasource details into tblmatchsetdatasources. Enable all available datasources by default
                         * 4. Retrieve rule details from table tblruledetails
                         * 5. Update/insert rule details into tblmatchsetrules. Enable all availabe rules by default
                         * */
                        $editoriinvoiceidflag = 'N';
                        
                        if (trim($this->f3->get('POST.searchinvoice')) !== '' || !empty(trim($this->f3->get('POST.searchinvoice')))) {
                            
                            $this->logger->write("Debitnote Controller : edit() : The new oriinvoiceid is " . trim($this->f3->get('POST.searchinvoice')), 'r');
                            $this->logger->write("Debitnote Controller : edit() : The existing oriinvoiceid is " . $debitnote->oriinvoiceid, 'r');
                            
                            if (trim($this->f3->get('POST.searchinvoice')) !== trim($debitnote->oriinvoiceid)) {
                                $this->logger->write("Debitnote Controller : edit() : The user wishes to change the oriinvoiceid.", 'r');
                                $editoriinvoiceidflag = 'Y';
                            } else {
                                $this->logger->write("Debitnote Controller : edit() : The user does not wish to change the oriinvoiceid.", 'r');
                            }
                        }
                        
                        $oriinvoiceid = $this->f3->get('POST.searchinvoice');
                        $invoice = new invoices($this->db);
                        $invoice->getByInvoiceID($oriinvoiceid);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        $this->logger->write("Debitnote Controller : edit() : invoice = " . $oriinvoiceid, 'r');
                        
                        $this->f3->set('POST.operator', $this->f3->get('SESSION.username'));
                        
                        $this->f3->set('POST.deviceno', $invoice->deviceno);
                        $this->f3->set('POST.oriinvoiceid', $invoice->einvoiceid);
                        $this->f3->set('POST.oriinvoiceno', $invoice->einvoicenumber);
                        $this->f3->set('POST.currency', $invoice->currency);
                        $this->f3->set('POST.origrossamount', $invoice->grossamount);
                        $this->f3->set('POST.buyerid', $invoice->buyerid);
                        $this->f3->set('POST.invoiceindustrycode', $invoice->invoiceindustrycode);
                        $this->f3->set('POST.deliveryTermsCode', $invoice->deliveryTermsCode);
                        
                        $debitnote->edit($id);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        
                        //Proceed and edit oriinvoiceid details
                        if ($editoriinvoiceidflag == 'Y') {
                            /**
                             * 1. Delete all GROUPIDs
                             * 2. Add a GROUPID for goods and store it in a field called gooddetailgroupid
                             * 3. Add a GROUPID for payments and store it in a field called paymentdetailgroupid
                             * 4. Add a GROUPID for tax details and store it in a field called taxdetailgroupid
                             */
                            
                            $gooddetailgroup = $debitnote->gooddetailgroupid;
                            $taxdetailgroup = $debitnote->taxdetailgroupid;
                            $paymentdetailgroup = $debitnote->paymentdetailgroupid;
                            
                            if ($gooddetailgroup) {
                                try {
                                    $this->db->exec(array('DELETE FROM tblgooddetailgroups WHERE id = COALESCE(' . $gooddetailgroup . ', NULL)'));
                                    $this->db->exec(array('DELETE FROM tblgooddetails WHERE groupid = COALESCE(' . $gooddetailgroup . ', NULL)'));
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : edit() : Failed to delete from tabled tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            }
                            
                            if ($taxdetailgroup) {
                                try {
                                    $this->db->exec(array('DELETE FROM tbltaxdetailgroups WHERE id = COALESCE(' . $taxdetailgroup . ', NULL)'));
                                    $this->db->exec(array('DELETE FROM tbltaxdetails WHERE groupid = COALESCE(' . $taxdetailgroup . ', NULL)'));
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : edit() : Failed to delete from tabled tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            }
                            
                            if ($paymentdetailgroup) {
                                try {
                                    $this->db->exec(array('DELETE FROM tblpaymentdetailgroups WHERE id = COALESCE(' . $paymentdetailgroup . ', NULL)'));
                                    $this->db->exec(array('DELETE FROM tblpaymentdetails WHERE groupid = COALESCE(' . $paymentdetailgroup . ', NULL)'));
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : edit() : Failed to delete from tabled tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            }
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                            VALUES(' . $id . ', ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $taxdetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tbldebitnotes SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : edit() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Debitnote Controller : edit() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $gooddetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tbldebitnotes SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                    /**
                                     * 1. Retrieve good details from the parent invoice
                                     * 2(a). Copy good details to the debit note
                                     * 2(b). Retrieve the new goodid
                                     * 2(c). Copy related tax to the debit note
                                     *
                                     * */
                                    try{
                                        
                                        $temp = $this->db->exec(array('SELECT id, groupid, item, itemcode, qty, unitofmeasure, unitprice, total, taxrate, tax, ifnull(discounttotal, NULL) discounttotal, ifnull(discounttaxrate, NULL) discounttaxrate, ifnull(ordernumber, NULL) ordernumber, discountflag, deemedflag, exciseflag, ifnull(categoryid, NULL) categoryid, categoryname, goodscategoryid, goodscategoryname
                                                                        , exciserate, taxid, discountpercentage, ifnull(exciserule, NULL) exciserule, ifnull(excisetax, NULL) excisetax, ifnull(pack, NULL) pack, ifnull(stick, NULL) stick, ifnull(exciseunit, NULL) exciseunit, excisecurrency, exciseratename, taxcategory, displayCategoryCode, unitofmeasurename, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tblgooddetails WHERE groupid = ' . $invoice->gooddetailgroupid));
                                        
                                        $k = 0;
                                        
                                        foreach ($temp as $obj) {
                                            $o_goodid = $obj['id'];//original good id
                                            $this->logger->write("Debitnote Controller : edit() : The original good id is " . $o_goodid, 'r');
                                            
                                            try{
                                                $this->db->exec(array('INSERT INTO tblgooddetails (groupid, item, itemcode, qty, unitofmeasure, unitprice, total, taxrate, tax, discounttotal, discounttaxrate, ordernumber, discountflag, deemedflag, exciseflag, categoryid, categoryname, goodscategoryid, goodscategoryname
                                                                    , exciserate, taxid, discountpercentage, exciserule, excisetax, pack, stick, exciseunit, excisecurrency, exciseratename, taxcategory, displayCategoryCode, unitofmeasurename, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES( '. $gooddetailgroupid . ', "' . $obj['item'] . '", "' . $obj['itemcode'] . '", ' . $obj['qty'] . ', "' . $obj['unitofmeasure'] . '", ' . $obj['unitprice'] . ', ' . $obj['total'] . ', ' . $obj['taxrate'] . ', ' . $obj['tax'] . ', ' . $obj['discounttotal'] . ', ' . $obj['discounttaxrate'] . ', ' . (empty($obj['ordernumber'])? strval($k) : $obj['ordernumber']) . ', ' . $obj['discountflag'] . ', ' . $obj['deemedflag'] . ', ' . $obj['exciseflag'] . ', ' . (empty($obj['categoryid'])? 'NULL' : $obj['categoryid']) . ', "' . $obj['categoryname'] . '", ' . $obj['goodscategoryid'] . ', "' . $obj['goodscategoryname'] . '", "' .
                                                    $obj['exciserate'] . '", ' . (empty($obj['taxid'])? 'NULL' : $obj['taxid']) . ', ' . (empty($obj['discountpercentage'])? 'NULL' : $obj['discountpercentage']) . ', ' . (empty($obj['exciserule'])? 'NULL' : $obj['exciserule']) . ', ' . (empty($obj['excisetax'])? 'NULL' : $obj['excisetax']) . ', ' . (empty($obj['pack'])? 'NULL' : $obj['pack']) . ', ' . (empty($obj['stick'])? 'NULL' : $obj['stick']) . ', ' . (empty($obj['exciseunit'])? 'NULL' : $obj['exciseunit']) . ', "' . $obj['excisecurrency'] . '", "' . $obj['exciseratename'] . '", "' . $obj['taxcategory'] . '", "' . $obj['displayCategoryCode'] . '", "' . $obj['unitofmeasurename'] . '", ' . $obj['disabled'] . ', NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                            } catch (Exception $e) {
                                                $this->logger->write("Debitnote Controller : edit() : Failed to insert into table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            try {
                                                $g = array ();
                                                $gr = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetails WHERE groupid = ' . $gooddetailgroupid . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                                
                                                foreach ( $gr as $obj ) {
                                                    $g[] = $obj;
                                                }
                                                
                                                $goodid = $g[0]['id'];
                                                
                                                /*Insert tax details from the parent invoice*/
                                                $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                        SELECT '. $taxdetailgroupid . ', ' . $goodid . ', taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tbltaxdetails WHERE groupid = ' . $invoice->taxdetailgroupid . ' AND goodid = ' . $o_goodid));
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                            } catch (Exception $e) {
                                                $this->logger->write("Debitnote Controller : edit() : Failed to insert into table tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            $k = $k + 1;
                                        }
                                        
                                    } catch (Exception $e) {
                                        $this->logger->write("Debitnote Controller : edit() : Failed to insert into table tblgooddetails & tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                                    }
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : edit() : Failed to select from table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Debitnote Controller : edit() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $paymentdetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tbldebitnotes SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                    /*Insert payments details from the parent invoice*/
                                    try{
                                        $this->db->exec(array('INSERT INTO tblpaymentdetails (groupid, paymentmode, paymentmodename, paymentamount, ordernumber, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    SELECT '. $paymentdetailgroupid . ', paymentmode, paymentmodename, paymentamount, ordernumber, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tblpaymentdetails WHERE groupid = ' . $invoice->paymentdetailgroupid));
                                        $this->logger->write($this->db->log(TRUE), 'r');
                                    } catch (Exception $e) {
                                        $this->logger->write("Debitnote Controller : edit() : Failed to insert into table tblpaymentdetails. The error message is " . $e->getMessage(), 'r');
                                    }
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : edit() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Debitnote Controller : edit() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                        }
                        
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The debitnote - " . $debitnote->id . " has been edited by " . $this->f3->get('SESSION.username'));
                        self::$systemalert = "The debitnote  - " . $debitnote->id . " has been edited";
                        $this->logger->write("Debitnote Controller : edit() : The debitnote  - " . $debitnote->id . " has been edited", 'r');
                        $debitnote->getByID($id);//refresh
                    } catch (Exception $e) {
                        $this->logger->write("Debitnote Controller : edit() : The operation to edit debitnote - " . $debitnote->id . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit debitnote - " . $debitnote->id . " was not successfull");
                        self::$systemalert = "The operation to edit debitnote - " . $debitnote->id . " was not successful";
                    }
                    
                    
                } elseif ($currenttab == 'tab_seller'){
                    $id = trim($this->f3->get('POST.sellerdebitnoteid'));
                    $this->logger->write("Debitnote Controller : edit() : tab_seller : The id to be edited is " . $id, 'r');
                    $debitnote->getByID($id);
                    
                    $org = new organisations($this->db);
                    $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
                    
                    //$this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                    //$this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                    
                    //$this->f3->set('POST.referenceno', $this->f3->get('POST.referenceno'));
                    
                    //$org->edit($this->appsettings['SELLER_RECORD_ID']);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The seller details on debitnote - " . $debitnote->id . " have been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The seller details on debitnote  - " . $debitnote->id . " have been edited";
                    $this->logger->write("Debitnote Controller : edit() : The seller details on debitnote  - " . $debitnote->id . " have been edited", 'r');
                } elseif ($currenttab == 'tab_buyer'){
                    ;
                } elseif ($currenttab == 'tab_good'){
                    $id = trim($this->f3->get('POST.gooddebitnoteid'))? trim($this->f3->get('POST.gooddebitnoteid')) : (!empty(trim($this->f3->get('POST.deletegooddebitnoteid')))? trim($this->f3->get('POST.deletegooddebitnoteid')) : trim($this->f3->get('POST.editgooddebitnoteid')));
                    $this->logger->write("Debitnote Controller : edit() : tab_good : The id to be edited is " . $id, 'r');
                    $debitnote->getByID($id);
                    
                    $good = new goods($this->db);
                    $product = new products($this->db);
                                        
                    $commoditycategory = new commoditycategories($this->db);
                                        
                    
                    $this->logger->write("Debitnote Controller : edit() : editgoodid = : " . $this->f3->get('POST.editgoodid'), 'r');
                    $this->logger->write("Debitnote Controller : edit() : deletegoodid = : " . $this->f3->get('POST.deletegoodid'), 'r');
                    $this->logger->write("Debitnote Controller : edit() : deletegooddebitnoteid = : " . $this->f3->get('POST.deletegooddebitnoteid'), 'r');
                    
                    if (trim($this->f3->get('POST.editgoodid')) !== '' || !empty(trim($this->f3->get('POST.editgoodid')))) {
                        $this->logger->write("Debitnote Controller : edit() : tab_good : Edit operation", 'r');
                        
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
                        
                        if ($debitnote->debitnoteno) {
                            $this->logger->write("Debitnote Controller : edit() : This debit note is already uploaded", 'r');
                            self::$systemalert = "This debit note is already uploaded";
                        } else {
                            $goodid = $this->f3->get('POST.editgoodid');
                            $good->getByID($goodid);
                            //$this->f3->set('POST.groupid', $debitnote->paymentdetailgroupid);
                            $this->f3->set('POST.discountflag', $this->f3->get('POST.editdiscountflag'));
                            
                            
                            $product->getByCode($this->f3->get('POST.edititem'));
                            
                            $this->f3->set('POST.groupid', $debitnote->gooddetailgroupid);
                            $this->f3->set('POST.itemcode', $product->code);
                            $this->f3->set('POST.qty', $this->f3->get('POST.editqty'));
                            
                            $measureunit = new measureunits($this->db);
                            $measureunit->getByCode($product->measureunit);
                            $this->logger->write($this->db->log(TRUE), 'r');
                            
                            $this->f3->set('POST.unitofmeasure', $measureunit->code);
                            $this->f3->set('POST.unitofmeasurename', $measureunit->name);
                            
                            $this->f3->set('POST.unitprice', $this->f3->get('POST.editunitprice'));
                            $this->f3->set('POST.item', $product->name);
                            $this->f3->set('POST.taxid', $this->f3->get('POST.edittaxrate'));
                            
                            //Calculate
                            $tr = new taxrates($this->db);
                            $tr->getByID($this->f3->get('POST.edittaxrate'));
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
                                
                                $this->logger->write("Debitnote Controller : edit() : tab_good : disc_gross = " . $d_gross, 'r');
                                $this->logger->write("Debitnote Controller : edit() : tab_good : disc_tax = " . $d_tax, 'r');
                                $this->logger->write("Debitnote Controller : edit() : tab_good : disc_net = " . $d_net, 'r');
                                
                            } else {
                                $this->f3->set('POST.discounttaxrate', 0);
                                /*
                                 $gross = $total;
                                 
                                 $tax = ($gross/($rate + 1)) * $rate;
                                 $net = $gross - $tax;
                                 */
                            }
                            
                            $this->logger->write("Debitnote Controller : edit() : tab_good : discountpct = " . $discountpct, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : total = " . $total, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : discount = " . $discount, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : gross = " . $gross, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : taxcode = " . $taxcode, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : rate = " . $rate, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : qty = " . $qty, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : rate = " . $rate, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : tax = " . $tax, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : net = " . $net, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : unit = " . $unit, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : taxcategory = " . $taxcategory, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : taxdescription = " . $taxdescription, 'r');
                            
                            $this->f3->set('POST.total', $total);
                            $this->f3->set('POST.taxrate', $rate);
                            
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
                            
                            
                            $this->f3->set('POST.deemedflag', $this->f3->get('POST.editdeemedflag'));
                            $this->f3->set('POST.exciseflag', $this->f3->get('POST.editexciseflag'));
                            $this->f3->set('POST.categoryid', $this->f3->get('POST.editcategoryid'));
                            $this->f3->set('POST.categoryname', $this->f3->get('POST.editcategoryname'));
                            
                            $commoditycategory->getByCode($product->commoditycategorycode);
                            
                            $this->f3->set('POST.goodscategoryid', $commoditycategory->commoditycode);
                            $this->f3->set('POST.goodscategoryname', $commoditycategory->commodityname);
                            
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
                            
                            $good->edit($goodid);
                            
                            $this->db->exec(array('DELETE FROM tbltaxdetails WHERE goodid = ' . $good->id . ' AND groupid = ' . $debitnote->taxdetailgroupid));
                            
                            if ($this->vatRegistered == 'Y') {
                                try{
                                                                        
                                    $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                                            VALUES(' . $debitnote->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcategory . '", "' . $taxcode . '", ' . ($net + $d_net) . ', ' . $rate . ', ' . ($tax + $d_tax) . ', ' . ($gross + $d_gross) . ', NULL, NULL, "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                    
                                    $this->logger->write($this->db->log(TRUE), 'r');
                                    //insert a tax record for the discount
                                    /*
                                     if ($discountpct > 0) {
                                     $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                     VALUES(' . $debitnote->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcode . '", ' . $d_net . ', ' . $rate . ', ' . $d_tax . ', ' . $d_gross . ', NULL, NULL, "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                     }*/
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : edit() : Failed to insert into table tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                                }
                            }
                            
                            
                        }

                        $debitnote->getByID($id);//refresh
                    } elseif (trim($this->f3->get('POST.deletegoodid')) !== '' || !empty(trim($this->f3->get('POST.deletegoodid')))) {
                        $this->logger->write("Debitnote Controller : edit() : tab_good : Delete operation", 'r');
                        $good->getByID($this->f3->get('POST.deletegoodid'));
                        $good->delete($this->f3->get('POST.deletegoodid'));
                        
                        try{
                            $this->db->exec(array('DELETE FROM tbltaxdetails WHERE goodid = ' . $good->id));
                        } catch (Exception $e) {
                            $this->logger->write("Debitnote Controller : edit() : The operation to delete the related tax details was not successful. The error messages is " . $e->getMessage(), 'r');
                        }
                    } else {
                        $this->logger->write("Debitnote Controller : edit() : tab_good : Add operation", 'r');
                        
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
                        
                        $this->f3->set('POST.groupid', $debitnote->paymentdetailgroupid);
                        $this->f3->set('POST.discountflag', $this->f3->get('POST.adddiscountflag'));
                        
                        
                        $product->getByCode($this->f3->get('POST.additem'));
                        
                        $this->f3->set('POST.groupid', $debitnote->gooddetailgroupid);
                        $this->f3->set('POST.itemcode', $product->code);
                        $this->f3->set('POST.qty', $this->f3->get('POST.addqty'));
                        
                        $measureunit = new measureunits($this->db);
                        $measureunit->getByCode($product->measureunit);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        
                        $this->f3->set('POST.unitofmeasure', $measureunit->code);
                        $this->f3->set('POST.unitofmeasurename', $measureunit->name);
                        
                        $this->f3->set('POST.unitprice', $this->f3->get('POST.addunitprice'));
                        $this->f3->set('POST.item', $product->name);
                        $this->f3->set('POST.taxid', $this->f3->get('POST.addtaxrate'));
                        
                        //Calculate
                        $tr = new taxrates($this->db);
                        $tr->getByID($this->f3->get('POST.addtaxrate'));
                        $taxcode = $tr->code;
                        $taxname = $tr->name;
                        $taxcategory = $tr->category;
                        $taxdescription = $tr->description;
                        $rate = $tr->rate? $tr->rate : 0;
                        $qty = $this->f3->get('POST.addqty');
                        $unit = $this->f3->get('POST.addunitprice');
                        $discountpct = empty($this->f3->get('POST.adddiscountpercentage'))? 0 : (float)$this->f3->get('POST.adddiscountpercentage');
                        
                        
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
                            
                            $this->logger->write("Debitnote Controller : edit() : tab_good : disc_gross = " . $d_gross, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : disc_tax = " . $d_tax, 'r');
                            $this->logger->write("Debitnote Controller : edit() : tab_good : disc_net = " . $d_net, 'r');
                            
                        } else {
                            $this->f3->set('POST.discounttaxrate', 0);
                            /*
                             $gross = $total;
                             
                             $tax = ($gross/($rate + 1)) * $rate;
                             $net = $gross - $tax;
                             */
                        }
                        
                        $this->logger->write("Debitnote Controller : edit() : tab_good : discountpct = " . $discountpct, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : total = " . $total, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : discount = " . $discount, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : gross = " . $gross, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : taxcode = " . $taxcode, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : rate = " . $rate, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : qty = " . $qty, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : rate = " . $rate, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : tax = " . $tax, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : net = " . $net, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : unit = " . $unit, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : taxcategory = " . $taxcategory, 'r');
                        $this->logger->write("Debitnote Controller : edit() : tab_good : taxdescription = " . $taxdescription, 'r');
                        
                        $this->f3->set('POST.total', $total);
                        $this->f3->set('POST.taxrate', $rate);                        
                        
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
                                               

                        $this->f3->set('POST.deemedflag', $this->f3->get('POST.adddeemedflag'));
                        $this->f3->set('POST.exciseflag', $this->f3->get('POST.addexciseflag'));
                        $this->f3->set('POST.categoryid', $this->f3->get('POST.addcategoryid'));
                        $this->f3->set('POST.categoryname', $this->f3->get('POST.addcategoryname'));
                        
                        $commoditycategory->getByCode($product->commoditycategorycode);
                        
                        $this->f3->set('POST.goodscategoryid', $commoditycategory->commoditycode);
                        $this->f3->set('POST.goodscategoryname', $commoditycategory->commodityname);
                        
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
                        
                        $good->add();
                        
                        try{
                            $data = array ();
                            $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetails WHERE insertedby = ' . $this->f3->get('SESSION.id')));
                            foreach ( $r as $obj ) {
                                $data [] = $obj;
                            }
                            
                            $goodid = $data[0]['id'];
                            
                            if ($this->vatRegistered == 'Y') {
                                $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $debitnote->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcategory . '", "' . $taxcode . '", ' . ($net + $d_net) . ', ' . $rate . ', ' . ($tax + $d_tax) . ', ' . ($gross + $d_gross) . ', NULL, NULL, "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                $this->logger->write($this->db->log(TRUE), 'r');
                                //insert a tax record for the discount
                                /*
                                 if ($discountpct > 0) {
                                 $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                 VALUES(' . $debitnote->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcode . '", ' . $d_net . ', ' . $rate . ', ' . $d_tax . ', ' . $d_gross . ', NULL, NULL, "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                 }*/
                            }
                             
                        } catch (Exception $e) {
                            $this->logger->write("Debitnote Controller : edit() : Failed to insert into table tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The good details on debitnote - " . $debitnote->id . " have been edited by " . $this->f3->get('SESSION.username'));
                    
                    if ($debitnote->debitnoteno) {
                        $this->logger->write("Debitnote Controller : edit() : This debit note is already uploaded", 'r');
                        self::$systemalert = "This debit note is already uploaded";
                    } else {
                        self::$systemalert = "The good details on debitnote  - " . $debitnote->id . " have been edited";
                        $this->logger->write("Debitnote Controller : edit() : The good details on debitnote  - " . $debitnote->id . " have been edited", 'r');
                    }
                    
                } elseif ($currenttab == 'tab_tax'){
                    ;
                } elseif ($currenttab == 'tab_payment'){
                    $id = trim($this->f3->get('POST.addpaymentdebitnoteid'))? trim($this->f3->get('POST.addpaymentdebitnoteid')) : trim($this->f3->get('POST.deletepaymentdebitnoteid'));
                    $this->logger->write("Debitnote Controller : edit() : tab_payment : The id to be edited is " . $id, 'r');
                    $debitnote->getByID($id);
                    
                    $payment = new payments($this->db);
                    
                    if (trim($this->f3->get('POST.editpaymentid')) !== '' || !empty(trim($this->f3->get('POST.editpaymentid')))) {
                        $this->logger->write("Debitnote Controller : edit() : tab_payment : Edit operation", 'r');
                    
                    } elseif (trim($this->f3->get('POST.deletepaymentid')) !== '' || !empty(trim($this->f3->get('POST.deletepaymentid')))) {
                        $this->logger->write("Debitnote Controller : edit() : tab_payment : Delete operation", 'r');
                        $payment->getByID($this->f3->get('POST.deletepaymentid'));
                        $payment->delete($this->f3->get('POST.deletepaymentid'));
                    } else {
                        $this->logger->write("Debitnote Controller : edit() : tab_payment : Add operation", 'r');
                        
                        $this->f3->set('POST.groupid', $debitnote->paymentdetailgroupid);
                        $this->f3->set('POST.paymentamount', $this->f3->get('POST.addpaymentamount'));
                        $this->f3->set('POST.paymentmode', $this->f3->get('POST.addpaymentmode'));
                        
                        $paymentmode = new paymentmodes($this->db);
                        $paymentmode->getByCode($this->f3->get('POST.addpaymentmode'));
                        
                        $this->f3->set('POST.paymentmodename', $paymentmode->name);
                        
                        $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                        $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                        
                        $payment->add();
                    }
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The payment details on debitnote - " . $debitnote->id . " have been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The payment details on debitnote  - " . $debitnote->id . " have been edited";
                    $this->logger->write("Debitnote Controller : edit() : The payment details on debitnote  - " . $debitnote->id . " have been edited", 'r');
                } else {
                    $this->logger->write("Debitnote Controller : edit() :No TAB was selected", 'r');
                    $this->f3->reroute('/debitnote');
                }

            } else {
                $this->logger->write("Debitnote Controller : edit() : The user is not allowed to perform this function", 'r');
                $this->f3->reroute('/forbidden');
            }
        } else { // ADD Operation: mainly handles the GENERAL parameters, as the rest of the parameters will be added using the EDIT option
            $operation = NULL; // tblevents
            $permission = 'CREATEDEBITNOTES'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications
            
            $this->logger->write("Debitnote Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $this->logger->write("Debitnote Controller : edit() : Adding of debitnote started.", 'r');
                
                $tcsdetails = new tcsdetails($this->db);
                $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
                
                $oriinvoiceid = $this->f3->get('POST.searchinvoice');
                $invoice = new invoices($this->db);
                $invoice->getByInvoiceID($oriinvoiceid);
                $this->logger->write($this->db->log(TRUE), 'r');
                $this->logger->write("Debitnote Controller : edit() : invoice = " . $oriinvoiceid, 'r');
                
                $this->f3->set('POST.erpdebitnoteid', $this->f3->get('POST.erpdebitnoteid'));
                $this->f3->set('POST.erpdebitnoteno', $this->f3->get('POST.erpdebitnoteno'));
                
                $this->f3->set('POST.invoicetype', $this->f3->get('POST.invoicetype'));
                $this->f3->set('POST.invoicekind', $this->f3->get('POST.invoicekind'));
                
                $this->f3->set('POST.datasource', $this->f3->get('POST.datasource'));                
                $this->f3->set('POST.remarks', $this->f3->get('POST.remarks')); 
                $this->f3->set('POST.operator', $this->f3->get('SESSION.username'));
                
                $this->f3->set('POST.deviceno', $invoice->deviceno);
                $this->f3->set('POST.oriinvoiceid', $invoice->einvoiceid);
                $this->f3->set('POST.oriinvoiceno', $invoice->einvoicenumber);
                $this->f3->set('POST.currency', $invoice->currency);
                $this->f3->set('POST.origrossamount', $invoice->grossamount);
                $this->f3->set('POST.buyerid', $invoice->buyerid);
                $this->f3->set('POST.invoiceindustrycode', $invoice->invoiceindustrycode);
                $this->f3->set('POST.reasoncode', $this->f3->get('POST.reasoncode'));
                $this->f3->set('POST.reason', $this->f3->get('POST.reason'));
                
                
                
                $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                // @TODO check the params for empty/null values
                if (trim($this->f3->get('POST.searchinvoice')) !== '' || ! empty(trim($this->f3->get('POST.searchinvoice')))) {
                    try {
                        // Proceed & create
                        $debitnote->add();
                        // $this->logger->write("Debitnote Controller : edit() : A new debitnote has been added", 'r');
                        try {
                            // retrieve the most recently inserted debitnote
                            // @TODO place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                            $data = array();
                            $r = $this->db->exec(array(
                                'SELECT MAX(id) "id" FROM tbldebitnotes WHERE insertedby = ' . $this->f3->get('SESSION.id')
                            ));
                            foreach ($r as $obj) {
                                $data[] = $obj;
                            }
                            
                            // $this->logger->write("Debitnote Controller : edit() : The debitnote " . $data[0]['id'] . " has been added", 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The debitnote id " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                            self::$systemalert = "The debitnote id " . $data[0]['id'] . " has been added";
                            $id = $data[0]['id'];
                            $debitnote->getByID($id);
                            
                            /**
                             * 1. Add a GROUPID for goods and store it in a field called gooddetailgroupid
                             * 1. Add a GROUPID for payments and store it in a field called paymentdetailgroupid
                             * 1. Add a GROUPID for tax details and store it in a field called taxdetailgroupid
                             */
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                            VALUES(' . $id . ', ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $taxdetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tbldebitnotes SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : edit() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Debitnote Controller : edit() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $gooddetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tbldebitnotes SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                    /**
                                     * 1. Retrieve good details from the parent invoice
                                     * 2(a). Copy good details to the debit note
                                     * 2(b). Retrieve the new goodid
                                     * 2(c). Copy related tax to the debit note
                                     * 
                                     * */
                                    try{
                                        
                                        $temp = $this->db->exec(array('SELECT id, groupid, item, itemcode, qty, unitofmeasure, unitprice, total, taxrate, tax, ifnull(discounttotal, NULL) discounttotal, ifnull(discounttaxrate, NULL) discounttaxrate, ifnull(ordernumber, NULL) ordernumber, discountflag, deemedflag, exciseflag, ifnull(categoryid, NULL) categoryid, categoryname, goodscategoryid, goodscategoryname
                                                                        , exciserate, taxid, discountpercentage, ifnull(exciserule, NULL) exciserule, ifnull(excisetax, NULL) excisetax, ifnull(pack, NULL) pack, ifnull(stick, NULL) stick, ifnull(exciseunit, NULL) exciseunit, excisecurrency, exciseratename, taxcategory, displayCategoryCode, unitofmeasurename, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tblgooddetails WHERE groupid = ' . $invoice->gooddetailgroupid));
                                        
                                        $k = 0;
                                        foreach ($temp as $obj) {
                                            $o_goodid = $obj['id'];//original good id
                                            $this->logger->write("Debitnote Controller : edit() : The original good id is " . $o_goodid, 'r');
                                            
                                            try {
                                                $this->db->exec(array('INSERT INTO tblgooddetails (groupid, item, itemcode, qty, unitofmeasure, unitprice, total, taxrate, tax, discounttotal, discounttaxrate, ordernumber, discountflag, deemedflag, exciseflag, categoryid, categoryname, goodscategoryid, goodscategoryname
                                                                    , exciserate, taxid, discountpercentage, exciserule, excisetax, pack, stick, exciseunit, excisecurrency, exciseratename, taxcategory, displayCategoryCode, unitofmeasurename, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES( '. $gooddetailgroupid . ', "' . $obj['item'] . '", "' . $obj['itemcode'] . '", ' . $obj['qty'] . ', "' . $obj['unitofmeasure'] . '", ' . $obj['unitprice'] . ', ' . $obj['total'] . ', ' . $obj['taxrate'] . ', ' . $obj['tax'] . ', ' . $obj['discounttotal'] . ', ' . $obj['discounttaxrate'] . ', ' . (empty($obj['ordernumber'])? strval($k) : $obj['ordernumber']) . ', ' . $obj['discountflag'] . ', ' . $obj['deemedflag'] . ', ' . $obj['exciseflag'] . ', ' . (empty($obj['categoryid'])? 'NULL' : $obj['categoryid']) . ', "' . $obj['categoryname'] . '", ' . $obj['goodscategoryid'] . ', "' . $obj['goodscategoryname'] . '", "' .
                                                    $obj['exciserate'] . '", ' . (empty($obj['taxid'])? 'NULL' : $obj['taxid']) . ', ' . (empty($obj['discountpercentage'])? 'NULL' : $obj['discountpercentage']) . ', ' . (empty($obj['exciserule'])? 'NULL' : $obj['exciserule']) . ', ' . (empty($obj['excisetax'])? 'NULL' : $obj['excisetax']) . ', ' . (empty($obj['pack'])? 'NULL' : $obj['pack']) . ', ' . (empty($obj['stick'])? 'NULL' : $obj['stick']) . ', ' . (empty($obj['exciseunit'])? 'NULL' : $obj['exciseunit']) . ', "' . $obj['excisecurrency'] . '", "' . $obj['exciseratename'] . '", "' . $obj['taxcategory'] . '", "' . $obj['displayCategoryCode'] . '", "' . $obj['unitofmeasurename'] . '", ' . $obj['disabled'] . ', NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                            } catch (Exception $e) {
                                                $this->logger->write("Debitnote Controller : edit() : Failed to insert into table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            
                                            $g = array ();
                                            $gr = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetails WHERE groupid = ' . $gooddetailgroupid . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                            
                                            foreach ( $gr as $obj ) {
                                                $g[] = $obj;
                                            }
                                            
                                            $goodid = $g[0]['id'];
                                            
                                            try {
                                                if ($this->vatRegistered == 'Y') {
                                                    /*Insert tax details from the parent invoice*/
                                                    $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                        SELECT '. $taxdetailgroupid . ', ' . $goodid . ', taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tbltaxdetails WHERE groupid = ' . $invoice->taxdetailgroupid . ' AND goodid = ' . $o_goodid));
                                                    
                                                    $this->logger->write($this->db->log(TRUE), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Debitnote Controller : edit() : Failed to insert into table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                                            }
                                            

                                            $k = $k + 1;
                                        }
  
                                    } catch (Exception $e) {
                                        $this->logger->write("Debitnote Controller : edit() : Failed to insert into table tblgooddetails & tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                                    }
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : edit() : Failed to select from table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Debitnote Controller : edit() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $paymentdetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tbldebitnotes SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                    /*Insert payments details from the parent invoice*/
                                    try{
                                        $this->db->exec(array('INSERT INTO tblpaymentdetails (groupid, paymentmode, paymentmodename, paymentamount, ordernumber, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    SELECT '. $paymentdetailgroupid . ', paymentmode, paymentmodename, paymentamount, ordernumber, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tblpaymentdetails WHERE groupid = ' . $invoice->paymentdetailgroupid));
                                        $this->logger->write($this->db->log(TRUE), 'r');
                                    } catch (Exception $e) {
                                        $this->logger->write("Debitnote Controller : edit() : Failed to insert into table tblpaymentdetails. The error message is " . $e->getMessage(), 'r');
                                    }
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : edit() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Debitnote Controller : edit() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                            }

                            
                            $debitnote->getByID($id);//refresh
                        } catch (Exception $e) {
                            $this->logger->write("Debitnote Controller : edit() : The operation to retrieve the most recently added debitnote was not successful. The error messages is " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added debitnote was not successful");
                            self::$systemalert = "The operation to retrieve the most recently added debitnote was not successful";
                        }
                    } catch (Exception $e) {
                        $this->logger->write("Debitnote Controller : edit() : The operation to add a debitnote was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a debitnote was not successful");
                        self::$systemalert = "The operation to add a debitnote was not successful";
                        $this->f3->set('systemalert', self::$systemalert);
                        self::add();
                        exit();
                    }
                } else {                   
                    // ABORT MISSION
                    $this->logger->write("Debitnote Controller : edit() : Some params are empty", 'r');
                    self::$systemalert = "The operation to add a debitnote was not successful. Some parameters were empty";
                    $this->f3->set('systemalert', self::$systemalert);
                    self::add();
                    exit();
                }
            } else { 
                $this->logger->write("Debitnote Controller : edit() : The user is not allowed to perform this function", 'r');
                $this->f3->reroute('/forbidden');
            }
        }
        
        $invoicetype = new invoicetypes($this->db);
        $invoicetypes = $invoicetype->all();
        $this->f3->set('invoicetypes', $invoicetypes);
        
        $debitnotereasoncode = new debitnotereasoncodes($this->db);
        $debitnotereasoncodes = $debitnotereasoncode->all();
        $this->f3->set('debitnotereasoncodes', $debitnotereasoncodes);
        
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
        
        $buyer = new buyers($this->db);
        $buyer->getByID($debitnote->buyerid);
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
        
        $this->logger->write("Debitnote Controller : edit() : currenttab = : " . $currenttab, 'r');
        $this->logger->write("Debitnote Controller : edit() : currenttabpane = : " . $currenttabpane, 'r');
        
        $this->f3->set('debitnote', $debitnote);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);
        
        $this->f3->set('systemalert', self::$systemalert);
        
        $this->f3->set('pagetitle', 'Edit Debitnote | ' . $id);
        $this->f3->set('pagecontent', 'EditDebitnote.htm');
        $this->f3->set('pagescripts', 'EditDebitnoteFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    
    /**
     *	@name list
     *  @desc List debitnotes
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function list(){
        $operation = NULL; //tblevents
        $permission = 'VIEWDEBITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Debitnote Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Debitnote Controller : list() : Processing list of debitnotes started", 'r');
            $debitnoteid = trim((string)$this->f3->get('REQUEST.debitnoteid'));

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
                    1 => 'i.oriinvoiceid',
                    2 => 'i.oriinvoiceno',
                    3 => 'i.currency',
                    4 => 'i.debitnoteno',
                    5 => 'i.netamount',
                    6 => 'i.taxamount',
                    7 => 'i.grossamount',
                    8 => 'i.modifieddt'
                );

                $orderBy = array_key_exists($orderColumnIndex, $columnMap)? $columnMap[$orderColumnIndex] : 'i.id';

                $where = '';
                if ($searchValue !== '') {
                    $searchEscaped = addslashes($searchValue);
                    $where = " WHERE (i.oriinvoiceno LIKE '%" . $searchEscaped . "%'"
                        . " OR i.debitnoteno LIKE '%" . $searchEscaped . "%'"
                        . " OR i.referenceno LIKE '%" . $searchEscaped . "%'"
                        . " OR i.debitnoteapplicationid LIKE '%" . $searchEscaped . "%'"
                        . " OR i.currency LIKE '%" . $searchEscaped . "%')";
                }

                $countTotalSql = 'SELECT COUNT(*) "c" FROM tbldebitnotes i';
                $countFilteredSql = 'SELECT COUNT(*) "c" FROM tbldebitnotes i' . $where;

                $sql = 'SELECT  i.id "ID",
                        i.oriinvoiceid "Original Invoice Id",
                        i.oriinvoiceno "Original Invoice No",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.einvoicedatamatrixcode "QR Code",
                        i.debitnoteno "Debit Note No",
                        i.referenceno "Reference No",
                        i.debitnoteapplicationid "Appl Id",
                        i.applicationtime "Appl Time",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tbldebitnotes i
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

                    $this->logger->write('Debitnote Controller : list() : DataTables mode - start=' . $start . ', length=' . $length . ', filtered=' . $recordsFiltered, 'r');

                    die(json_encode(array(
                        'draw' => $draw,
                        'recordsTotal' => $recordsTotal,
                        'recordsFiltered' => $recordsFiltered,
                        'data' => $dtls
                    )));
                } catch (Exception $e) {
                    $this->logger->write("Debitnote Controller : list() : The operation to list paged debitnotes was not successful. The error message is " . $e->getMessage(), 'r');
                    die(json_encode(array(
                        'draw' => $draw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => array()
                    )));
                }
            }
            
            if ($debitnoteid !== '' || !empty($debitnoteid)) {
                $sql = 'SELECT  i.id "ID",
                        i.oriinvoiceid "Original Invoice Id",
                        i.oriinvoiceno "Original Invoice No",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.einvoicedatamatrixcode "QR Code",
                        i.debitnoteno "Debit Note No",
                        i.referenceno "Reference No",
                        i.debitnoteapplicationid "Appl Id",
                        i.applicationtime "Appl Time",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tbldebitnotes i
                    LEFT JOIN tblusers s ON i.modifiedby = s.id
                    WHERE i.id = ' . $debitnoteid . '
                    ORDER By i.id DESC';
            } else {
                $sql = 'SELECT  i.id "ID",
                        i.oriinvoiceid "Original Invoice Id",
                        i.oriinvoiceno "Original Invoice No",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.einvoicedatamatrixcode "QR Code",
                        i.debitnoteno "Debit Note No",
                        i.referenceno "Reference No",
                        i.debitnoteapplicationid "Appl Id",
                        i.applicationtime "Appl Time",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tbldebitnotes i
                    LEFT JOIN tblusers s ON i.modifiedby = s.id
                    ORDER By i.id DESC';
            }

            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Debitnote Controller : list() : The operation to list the debitnotes was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Debitnote Controller : index() : The user is not allowed to perform this function", 'r');
        }
                     
        die(json_encode($data));
    }

    /**
     *  @name searchdebitnotereasoncodes
     *  @desc List debit note reason codes for Select2 search
     *  @return JSON-encoded object
     **/
    function searchdebitnotereasoncodes(){
        $permission = 'VIEWDEBITNOTES';
        $data = array();

        $this->logger->write("Debitnote Controller : searchdebitnotereasoncodes() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $name = trim($this->f3->get('POST.name'));
            if ($name !== '' || ! empty($name)) {
                $subquery = " '%" . $name . "%' ";
                $sql = 'SELECT r.id "Id", r.code "Code", r.name "Name", r.description "Description", r.disabled "Disabled"
                        FROM tbldebitnotereasoncodes r
                        WHERE r.name LIKE ' . $subquery . ' OR r.code LIKE ' . $subquery . '
                        ORDER BY r.id DESC';
            } else {
                $sql = 'SELECT r.id "Id", r.code "Code", r.name "Name", r.description "Description", r.disabled "Disabled"
                        FROM tbldebitnotereasoncodes r
                        ORDER BY r.id DESC';
            }

            try {
                $dtls = $this->db->exec($sql);
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Debitnote Controller : searchdebitnotereasoncodes() : The operation was not successful. The error message is " . $e->getMessage(), 'r');
            }
        }

        die(json_encode($data));
    }
    
    
    /**
     *	@name uploaddebitnote
     *  @desc upload an debitnote to EFRIS
     *	@return
     *	@param 
     **/
    function uploaddebitnote(){
        $operation = NULL; //tblevents
        $permission = 'UPLOADDEBITNOTE'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.uploaddebitnoteid');
        $debitnote = new debitnotes($this->db);
        $debitnote->getByID($id);
        $this->logger->write("Debitnote Controller : uploaddebitnote() : The debitnote id is " . $this->f3->get('POST.uploaddebitnoteid'), 'r');
        
        $this->logger->write("Debitnote Controller : uploaddebitnote() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            if ($debitnote->debitnoteno) {
                $this->logger->write("Debitnote Controller : uploaddebitnote() : This debit note is already uploaded", 'r');
                self::$systemalert = "This debit note is already uploaded";
                $this->f3->set('systemalert', self::$systemalert);
                self::view($id);
            } else {
                $data = $this->util->uploaddebitnote($this->f3->get('SESSION.id'), $id, $this->vatRegistered);//will return JSON.
                //var_dump($data);
            }
            
            
            $data = json_decode($data, true);
            //$this->logger->write("Debitnote Controller : uploaddebitnote() : The response content is: " . $data, 'r');
            //var_dump($data);
            
            
            if (isset($data['returnCode'])){
                $this->logger->write("Debitnote Controller : uploaddebitnote() : The operation to upload a debit note not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to upload a debit note by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to upload a debit note by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
            } else {
                if (isset($data['basicInformation'])){
                    $antifakeCode = $data['basicInformation']['antifakeCode']; //32966911991799104051
                    $invoiceId = $data['basicInformation']['invoiceId']; //3257429764295992735
                    $invoiceNo = $data['basicInformation']['invoiceNo']; //3120012276043
                    $issuedDate = $data['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                    
                    $issuedDate = str_replace('/', '-', $issuedDate);//Replace / with -
                    $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                    
                    $issuedDatePdf = $data['basicInformation']['issuedDatePdf']; //318/09/2020 17:14:12
                    
                    $issuedDatePdf = str_replace('/', '-', $issuedDatePdf);//Replace / with -
                    $issuedDatePdf = date("Y-m-d H:i:s", strtotime($issuedDatePdf));
                    
                    $isInvalid = $data['basicInformation']['isInvalid'];//1
                    $isRefund = $data['basicInformation']['isRefund'];//1
                    $oriInvoiceId = $data['basicInformation']['oriInvoiceId'];//1
                    $oriInvoiceNo = $data['basicInformation']['oriInvoiceNo'];//1
                    $oriIssuedDate = $data['basicInformation']['oriIssuedDate'];//1
                    
                    $oriIssuedDate = str_replace('/', '-', $oriIssuedDate);//Replace / with -
                    $oriIssuedDate = date("Y-m-d H:i:s", strtotime($oriIssuedDate));
                    
                    $deviceNo = $data['basicInformation']['deviceNo'];
                    $invoiceIndustryCode = $data['basicInformation']['invoiceIndustryCode'];
                    $invoiceKind = $data['basicInformation']['invoiceKind'];
                    $invoiceType = $data['basicInformation']['invoiceType'];
                    $isBatch = $data['basicInformation']['isBatch'];
                    $operator = $data['basicInformation']['operator'];
                    
                    
                    try{
                        $this->db->exec(array('UPDATE tbldebitnotes SET oriissueddate = "' . $oriIssuedDate .
                            '", antifakeCode = "' . $antifakeCode .
                            '", debitnoteid = "' . $invoiceId .
                            '", debitnoteno = "' . $invoiceNo .
                            '", issueddate = "' . $issuedDate .
                            '", issueddatepdf = "' . $issuedDatePdf .
                            '", oriinvoiceid = "' . $oriInvoiceId .
                            '", isinvalid = "' . $isInvalid .
                            '", isrefund = "' . $isRefund .
                            '", oriinvoiceno = "' . $oriInvoiceNo .
                            '", deviceno = "' . $deviceNo .
                            '", invoiceindustrycode = ' . $invoiceIndustryCode .
                            ', invoicekind = ' . $invoiceKind .
                            ', invoicetype = ' . $invoiceType .
                            ', isbatch = "' . $isBatch .
                            '", operator = "' . $operator .
                            '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                            ' WHERE id = ' . $id));
                        
                        $this->logger->write($this->db->log(TRUE), 'r');
                    } catch (Exception $e) {
                        $this->logger->write("Debitnote Controller : uploaddebitnote() : Failed to insert into the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
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
                        $this->db->exec(array('UPDATE tbldebitnotes SET branchCode = "' . $branchCode .
                            '", branchId = "' . $branchId .
                            '", erpdebitnoteid = "' . addslashes($referenceNo) .
                            '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                            ' WHERE id = ' . $id));
                        
                        $this->logger->write($this->db->log(TRUE), 'r');
                    } catch (Exception $e) {
                        $this->logger->write("Debitnote Controller : uploaddebitnote() : Failed to update the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
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
                        $this->db->exec(array('UPDATE tbldebitnotes SET grossamount = ' . $grossAmount . ', itemcount = ' . $itemCount . ', netamount = ' . $netAmount . ', einvoicedatamatrixcode = "' . addslashes($qrCode) . '", taxamount = ' . $taxAmount . ', modecode = "' . $modeCode . '", modename = "' . $modeName . '", grossamountword = "' . addslashes($grossAmountWords) . '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                        $this->logger->write($this->db->log(TRUE), 'r');
                    } catch (Exception $e) {
                        $this->logger->write("Debitnote Controller : uploaddebitnote() : Failed to insert into the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
                    }
                }
                
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to upload the debitnote by " . $this->f3->get('SESSION.username') . " was successful");
                self::$systemalert = "The operation to upload the debitnote by " . $this->f3->get('SESSION.username') . " was successful";
            }
            
            //die($data);
        } else {
            $this->logger->write("Debitnote Controller : uploaddebitnote() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name downloaddebitnote
     *  @desc download a debit note from EFRIS
     *	@return
     *	@param
     **/
    function downloaddebitnote(){
        $operation = NULL; //tblevents
        $permission = 'DOWNLOADDEBITNOTE'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.downloaddebitnoteid');
        $debitnote = new debitnotes($this->db);
        $debitnote->getByID($id);
        $this->logger->write("Debitnote Controller : downloaddebitnote() : The debitnote id is " . $this->f3->get('POST.downloaddebitnoteid'), 'r');
        
        $this->logger->write("Debitnote Controller : downloaddebitnote() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            $data = $this->util->downloaddebitnote($this->f3->get('SESSION.id'), $id);//will return JSON.
            //var_dump($data);
            
            $data = json_decode($data, true);
            //$this->logger->write("Debitnote Controller : downloaddebitnote() : The response content is: " . $data, 'r');
            //var_dump($data);
            
            if (isset($data['returnCode'])){
                $this->logger->write("Debitnote Controller : downloaddebitnote() : The operation to download the invoice not successful. The error message is " . $data['returnMessage'], 'r');
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
                    
                    $issuedDatePdf = $data['basicInformation']['issuedDatePdf']; //318/09/2020 17:14:12
                    
                    $issuedDatePdf = str_replace('/', '-', $issuedDatePdf);//Replace / with -
                    $issuedDatePdf = date("Y-m-d H:i:s", strtotime($issuedDatePdf));
                    
                    $isInvalid = $data['basicInformation']['isInvalid'];//1
                    $isRefund = $data['basicInformation']['isRefund'];//1
                    $oriInvoiceId = $data['basicInformation']['oriInvoiceId'];//1
                    $oriInvoiceNo = $data['basicInformation']['oriInvoiceNo'];//1
                    $oriIssuedDate = $data['basicInformation']['oriIssuedDate'];//1
                    
                    $oriIssuedDate = str_replace('/', '-', $oriIssuedDate);//Replace / with -
                    $oriIssuedDate = date("Y-m-d H:i:s", strtotime($oriIssuedDate));
                    
                    $deviceNo = $data['basicInformation']['deviceNo'];
                    $invoiceIndustryCode = $data['basicInformation']['invoiceIndustryCode'];
                    $invoiceKind = $data['basicInformation']['invoiceKind'];
                    $invoiceType = $data['basicInformation']['invoiceType'];
                    $isBatch = $data['basicInformation']['isBatch'];
                    $operator = $data['basicInformation']['operator'];
                    
                    $currencyRate = $data['basicInformation']['currencyRate'];
                    
                    
                    try{
                        $this->db->exec(array('UPDATE tbldebitnotes SET oriissueddate = "' . $oriIssuedDate .
                            '", antifakeCode = "' . $antifakeCode .
                            '", debitnoteid = "' . $invoiceId .
                            '", debitnoteno = "' . $invoiceNo .
                            '", issueddate = "' . $issuedDate .
                            '", issueddatepdf = "' . $issuedDatePdf .
                            '", oriinvoiceid = "' . $oriInvoiceId .
                            '", isinvalid = "' . $isInvalid .
                            '", isrefund = "' . $isRefund .
                            '", oriinvoiceno = "' . $oriInvoiceNo .
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
                        $this->logger->write("Debitnote Controller : downloaddebitnote() : Failed to insert into the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
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
                        $this->db->exec(array('UPDATE tbldebitnotes SET grossamount = ' . $grossAmount . 
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
                        $this->logger->write("Debitnote Controller : downloaddebitnote() : Failed to insert into the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
                    }
                }
                
                if (isset($data['extend'])){
                    
                    
                    $reason = $data['extend']['reason'];
                    $reasonCode = $data['extend']['reasonCode'];
                    
                    
                    
                    try{
                        $this->db->exec(array('UPDATE tbldebitnotes SET reason = "' . addslashes($reason) .
                                                                    '", reasoncode = "' . $reasonCode .
                                                                    '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                    ' WHERE id = ' . $id));
                        
                        $this->logger->write($this->db->log(TRUE), 'r');
                    } catch (Exception $e) {
                        $this->logger->write("Debitnote Controller : downloadcreditnote() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
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
                    
                    $debitnote->getByID($id);
                    
                    $referenceno = $debitnote->erpdebitnoteno;
                    $referenceno = $referenceno . (empty($debitnote->erpdebitnoteid)? $debitnote->id : strval($debitnote->erpdebitnoteid));
                    
                    
                    if (!empty(trim($debitnote->buyerid))) {
                    //if (trim($debitnote->buyerid) !== '' || !empty(trim($debitnote->buyerid))) {
                        $this->logger->write("Debitnote Controller : downloaddebitnote() : The buyer is already set", 'r');
                        
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
                                ' WHERE id = ' . $debitnote->buyerid));
                            
                            $this->logger->write($this->db->log(TRUE), 'r');
                        } catch (Exception $e) {
                            $this->logger->write("Debitnote Controller : downloaddebitnote() : Failed to update the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                        }
                    } else {
                        $this->logger->write("Debitnote Controller : downloaddebitnote() : The buyer is NOT set", 'r');
                        
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
                                    
                                    $this->db->exec(array('UPDATE tbldebitnotes SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                } else {
                                    ;
                                }
                                
                            } catch (Exception $e) {
                                $this->logger->write("Debitnote Controller : downloaddebitnote() : Failed to select from table tblbuyers. The error message is " . $e->getMessage(), 'r');
                            }
                            
                        } catch (Exception $e) {
                            $this->logger->write("Debitnote Controller : downloaddebitnote() : Failed to insert into the table tblbuyers. The error message is " . $e->getMessage(), 'r');
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
                             $this->db->exec(array('DELETE FROM tblgooddetails WHERE groupid = ' . $debitnote->gooddetailgroupid));
                         } catch (Exception $e) {
                         $this->logger->write("Debitnote Controller : downloadinvoice() : The operation to delete from table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                         }
                        
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
                                                                (' . $debitnote->gooddetailgroupid . ',
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
                                $this->logger->write("Debitnote Controller : downloaddebitnote() : The operation to insert into table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                            }
                            
                        }
                        
                    } else {//NOTHING RETURNED BY API
                        $this->logger->write("Debitnote Controller : downloaddebitnote() : The API did not return anything", 'r');
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
                            $this->db->exec(array('DELETE FROM tblpaymentdetails WHERE groupid = ' . $debitnote->paymentdetailgroupid));
                        } catch (Exception $e) {
                            $this->logger->write("Debitnote Controller : downloadinvoice() : The operation to delete from table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
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
                                                                (' . $debitnote->paymentdetailgroupid . ',
                                                                ' . $orderNumber . ',
                                                                ' . $paymentAmount . ',
                                                                ' . $paymentMode . ',
                                                                "' . addslashes($paymentmodename) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                            } catch (Exception $e) {
                                $this->logger->write("Debitnote Controller : downloaddebitnote() : The operation to insert into table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
                            }
                            
                        }
                        
                    } else {//NOTHING RETURNED BY API
                        $this->logger->write("Debitnote Controller : downloaddebitnote() : The API did not return anything", 'r');
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
                        $this->db->exec(array('UPDATE tbldebitnotes SET branchCode = "' . $branchCode .
                            '", branchId = "' . $branchId .
                            '", erpdebitnoteid = "' . addslashes($referenceNo) .
                            '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                            ' WHERE id = ' . $id));
                        
                        $this->logger->write($this->db->log(TRUE), 'r');
                    } catch (Exception $e) {
                        $this->logger->write("Debitnote Controller : downloaddebitnote() : Failed to update the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
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
                            $this->db->exec(array('DELETE FROM tbltaxdetails WHERE groupid = ' . $debitnote->taxdetailgroupid));
                        } catch (Exception $e) {
                            $this->logger->write("Debitnote Controller : downloadinvoice() : The operation to delete from table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
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
                                                                (' . $debitnote->taxdetailgroupid . ', 0,
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
                                $this->logger->write("Debitnote Controller : downloaddebitnote() : The operation to insert into table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
                            }
                            
                        }
                        
                    } else {//NOTHING RETURNED BY API
                        $this->logger->write("Debitnote Controller : downloaddebitnote() : The API did not return anything", 'r');
                    }
                }
                
                
                
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the invoice by " . $this->f3->get('SESSION.username') . " was successful");
                self::$systemalert = "The operation to download the invoice by " . $this->f3->get('SESSION.username') . " was successful";
            }
            
            //die($data);
        } else {
            $this->logger->write("Debitnote Controller : downloaddebitnote() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
 
    /**
     *	@name syncefrisdebitnotes
     *  @desc sync debit notes from EFRIS
     *	@return
     *	@param
     **/
    function syncefrisdebitnotes(){
        $operation = NULL; //tblevents
        $permission = 'SYNCDEBITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The invoice kind is " . $this->f3->get('POST.invoicekind'), 'r');
        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The operation type is " . $this->f3->get('POST.operationType'), 'r');
        
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
        
                
        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            
            if ($operationType == 'update') {
                $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Update Operation", 'r');
                
                $dn_check = $this->db->exec(array('SELECT id FROM tbldebitnotes WHERE DATE(issueddate) BETWEEN \'' . $startdate . '\' AND \'' . $enddate . '\''));
                $this->logger->write($this->db->log(TRUE), 'r');
                
                
                if($dn_check){
                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Deditnotes retrieved", 'r');
                    $debitnote = new debitnotes($this->db);
                    $id = NULL;
                    
                    foreach ($dn_check as $obj) {
                        $id = $obj['id'];
                        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Invoice Id: " . $id, 'r');
                        $debitnote->getByID($id);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : D/N No: " . $debitnote->debitnoteno, 'r');
                        
                        $data = $this->util->downloaddebitnote($this->f3->get('SESSION.id'), $id);//will return JSON.
                        //var_dump($data);
                        
                        $data = json_decode($data, true);
                        //$this->logger->write("Debitnote Controller : downloaddebitnote() : The response content is: " . $data, 'r');
                        //var_dump($data);
                        
                        if (isset($data['returnCode'])){
                            $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The operation to update the debitnotes not successful. The error message is " . $data['returnMessage'], 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update the debitnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to update the debitnotes by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                        } else {
                            if (isset($data['basicInformation'])){
                                $antifakeCode = $data['basicInformation']['antifakeCode']; //32966911991799104051
                                $invoiceId = $data['basicInformation']['invoiceId']; //3257429764295992735
                                $invoiceNo = $data['basicInformation']['invoiceNo']; //3120012276043
                                $issuedDate = $data['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                                
                                $issuedDate = str_replace('/', '-', $issuedDate);//Replace / with -
                                $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                                
                                $issuedDatePdf = $data['basicInformation']['issuedDatePdf']; //318/09/2020 17:14:12
                                
                                $issuedDatePdf = str_replace('/', '-', $issuedDatePdf);//Replace / with -
                                $issuedDatePdf = date("Y-m-d H:i:s", strtotime($issuedDatePdf));
                                
                                $isInvalid = $data['basicInformation']['isInvalid'];//1
                                $isRefund = $data['basicInformation']['isRefund'];//1
                                $oriInvoiceId = $data['basicInformation']['oriInvoiceId'];//1
                                $oriInvoiceNo = $data['basicInformation']['oriInvoiceNo'];//1
                                $oriIssuedDate = $data['basicInformation']['oriIssuedDate'];//1
                                
                                $oriIssuedDate = str_replace('/', '-', $oriIssuedDate);//Replace / with -
                                $oriIssuedDate = date("Y-m-d H:i:s", strtotime($oriIssuedDate));
                                
                                $deviceNo = $data['basicInformation']['deviceNo'];
                                $invoiceIndustryCode = $data['basicInformation']['invoiceIndustryCode'];
                                $invoiceKind = $data['basicInformation']['invoiceKind'];
                                $invoiceType = $data['basicInformation']['invoiceType'];
                                $isBatch = $data['basicInformation']['isBatch'];
                                $operator = $data['basicInformation']['operator'];
                                
                                $currencyRate = $data['basicInformation']['currencyRate'];
                                
                                
                                try{
                                    $this->db->exec(array('UPDATE tbldebitnotes SET oriissueddate = "' . $oriIssuedDate .
                                        '", antifakeCode = "' . $antifakeCode .
                                        '", debitnoteid = "' . $invoiceId .
                                        '", debitnoteno = "' . $invoiceNo .
                                        '", issueddate = "' . $issuedDate .
                                        '", issueddatepdf = "' . $issuedDatePdf .
                                        '", oriinvoiceid = "' . $oriInvoiceId .
                                        '", isinvalid = "' . $isInvalid .
                                        '", isrefund = "' . $isRefund .
                                        '", oriinvoiceno = "' . $oriInvoiceNo .
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
                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to insert into the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
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
                                    $this->db->exec(array('UPDATE tbldebitnotes SET grossamount = ' . $grossAmount .
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
                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to insert into the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
                                }
                            }
                            
                            if (isset($data['extend'])){
                                
                                
                                $reason = $data['extend']['reason'];
                                $reasonCode = $data['extend']['reasonCode'];
                                
                                
                                
                                try{
                                    $this->db->exec(array('UPDATE tbldebitnotes SET reason = "' . addslashes($reason) .
                                        '", reasoncode = "' . $reasonCode .
                                        '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                        ' WHERE id = ' . $id));
                                    
                                    $this->logger->write($this->db->log(TRUE), 'r');
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
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
                                
                                $debitnote->getByID($id);
                                
                                $referenceno = $debitnote->erpdebitnoteno;
                                $referenceno = $referenceno . (empty($debitnote->erpdebitnoteid)? $debitnote->id : strval($debitnote->erpdebitnoteid));
                                
                                
                                if (!empty(trim($debitnote->buyerid))) {
                                    //if (trim($debitnote->buyerid) !== '' || !empty(trim($debitnote->buyerid))) {
                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The buyer is already set", 'r');
                                    
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
                                            ' WHERE id = ' . $debitnote->buyerid));
                                        
                                        $this->logger->write($this->db->log(TRUE), 'r');
                                    } catch (Exception $e) {
                                        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to update the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                    }
                                } else {
                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The buyer is NOT set", 'r');
                                    
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
                                                
                                                $this->db->exec(array('UPDATE tbldebitnotes SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                            } else {
                                                ;
                                            }
                                            
                                        } catch (Exception $e) {
                                            $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to select from table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                    } catch (Exception $e) {
                                        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to insert into the table tblbuyers. The error message is " . $e->getMessage(), 'r');
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
                                        $this->db->exec(array('DELETE FROM tblgooddetails WHERE groupid = ' . $debitnote->gooddetailgroupid));
                                    } catch (Exception $e) {
                                        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The operation to delete from table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                                    }
                                    
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
                                                                (' . $debitnote->gooddetailgroupid . ',
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
                                            $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The operation to insert into table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                    }
                                    
                                } else {//NOTHING RETURNED BY API
                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The API did not return anything", 'r');
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
                                        $this->db->exec(array('DELETE FROM tblpaymentdetails WHERE groupid = ' . $debitnote->paymentdetailgroupid));
                                    } catch (Exception $e) {
                                        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The operation to delete from table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
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
                                                                (' . $debitnote->paymentdetailgroupid . ',
                                                                ' . $orderNumber . ',
                                                                ' . $paymentAmount . ',
                                                                ' . $paymentMode . ',
                                                                "' . addslashes($paymentmodename) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                        } catch (Exception $e) {
                                            $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The operation to insert into table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                    }
                                    
                                } else {//NOTHING RETURNED BY API
                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The API did not return anything", 'r');
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
                                    $this->db->exec(array('UPDATE tbldebitnotes SET branchCode = "' . $branchCode .
                                        '", branchId = "' . $branchId .
                                        '", erpdebitnoteid = "' . addslashes($referenceNo) .
                                        '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                        ' WHERE id = ' . $id));
                                    
                                    $this->logger->write($this->db->log(TRUE), 'r');
                                } catch (Exception $e) {
                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to update the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
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
                                        $this->db->exec(array('DELETE FROM tbltaxdetails WHERE groupid = ' . $debitnote->taxdetailgroupid));
                                    } catch (Exception $e) {
                                        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The operation to delete from table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
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
                                                                (' . $debitnote->taxdetailgroupid . ', 0,
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
                                            $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The operation to insert into table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                    }
                                    
                                } else {//NOTHING RETURNED BY API
                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The API did not return anything", 'r');
                                }
                            }
                            
                            
                            
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update the debit notes by " . $this->f3->get('SESSION.username') . " was successful");
                            self::$systemalert = "The operation to update the debit notes by " . $this->f3->get('SESSION.username') . " was successful";
                        }
                    }
                    
                } else {
                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : No debitnotes were retrieved", 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update debitnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                    self::$systemalert = "The operation to update debitnotes by " . $this->f3->get('SESSION.username') . " was not successful. No debitnotes were retrieved.";
                    
                }
                
            } else {
                $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Sync Operation", 'r');
                
                $pageNo = 1;
                $pageSize = 90;
                $pageCount = 1;
                
                do {
                    $data = $this->util->syncefrisdebitnotes($this->f3->get('SESSION.id'), $invoicekind, $startdate, $enddate, $pageNo, $pageSize);//will return JSON.
                    
                    $data = json_decode($data, true);
                    
                    if(isset($data['page'])){
                        $pageCount = $data['page']['pageCount'];
                        
                        $pageNo = $pageNo + 1;
                    }
                    
                    
                    if (isset($data['returnCode'])){
                        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The operation to sync debitnotes not successful. The error message is " . $data['returnMessage'], 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync debitnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                        self::$systemalert = "The operation to sync debitnotes by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                    } else {
                        
                        
                        if ($data) {
                            
                            
                            if(isset($data['records'])){
                                $debitnote = new debitnotes($this->db);
                                
                                foreach($data['records'] as $elem){
                                    /*
                                     "businessName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                     "buyerBusinessName":"JESANI CONSTRUCTION LIMITED",
                                     "buyerLegalName":"JESANI CONSTRUCTION LIMITED",
                                     "buyerTin":"1000021928",
                                     "currency":"UGX",
                                     "dataSource":"106",
                                     "dateFormat":"dd/MM/yyyy",
                                     "grossAmount":"7000",
                                     "id":"356512129834183106",
                                     "invoiceNo":"320017776958",
                                     "issuedDate":"25/05/2021 13:41:40",
                                     "legalName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                     "nowTime":"2021/05/25 14:57:46",
                                     "oriInvoiceId":"358494246814125369",
                                     "oriInvoiceNo":"320017745845",
                                     "pageIndex":0,
                                     "pageNo":0,
                                     "pageSize":0,
                                     "tin":"1017918269"
                                     */
                                    
                                    $invoiceId = $elem['id']; //3257429764295992735
                                    $invoiceNo = $elem['invoiceNo']; //3120012276043
                                    
                                    $oriInvoiceId = $elem['oriInvoiceId']; //358494246814125369",
                                    $oriInvoiceNo = $elem['oriInvoiceNo']; //320017745845",
                                    
                                    $issuedDate = $elem['issuedDate']; //18/09/2020 17:14:12
                                    $issuedDate = str_replace('/', '-', $issuedDate);//Replace / with -
                                    $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                                    
                                    $issuedTime = $elem['issuedDate']; //18/09/2020 17:14:12
                                    $issuedTime = str_replace('/', '-', $issuedTime);//Replace / with -
                                    $issuedTime = date("Y-m-d H:i:s", strtotime($issuedTime));
                                    
                                    $currency = $elem['currency'];//1
                                    $dataSource = $elem['dataSource'];//1
                                    
                                    $debitnote->getByInvoiceNo($invoiceNo);
                                    
                                    if ($debitnote->dry()) {
                                        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The debitnote does not exist", 'r');
                                        
                                        try{
                                            
                                            /**
                                             * 1. Insert the details into the tbldebitnotes
                                             * 2. Retrive the record inserted.
                                             * 3. Generate the following details
                                             * 3.3. Good details group
                                             * 3.4. Payment details group
                                             * 3.5. Tax details group
                                             * 4. Update the invoice record with these details
                                             */
                                            
                                            $this->db->exec(array('INSERT INTO tbldebitnotes
                                                                (debitnoteid,
                                                                debitnoteno,
                                                                oriinvoiceid,
                                                                oriinvoiceno,
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
                                                                "' . $oriInvoiceId . '",
                                                                "' . $oriInvoiceNo . '",
                                                                "' . $issuedDate . '",
                                                                ' . $dataSource . ',
                                                                "' . $currency . '",
                                                                ' . $this->appsettings['SELLER_RECORD_ID'] . ',
                                                                "' . $issuedTime . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                            
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                            
                                            //Retrieve the now inserted invoice
                                            $debitnote->getByInvoiceNo($invoiceNo);
                                            
                                            $id = $debitnote->id;
                                            
                                            /**
                                             * 1. Add a GROUPID for goods and store it in a field called gooddetailgroupid
                                             * 1. Add a GROUPID for payments and store it in a field called paymentdetailgroupid
                                             * 1. Add a GROUPID for tax details and store it in a field called taxdetailgroupid
                                             */
                                            
                                            try {
                                                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;
                                                
                                                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                try {
                                                    $pg = array ();
                                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                                    
                                                    foreach ( $r as $obj ) {
                                                        $pg [] = $obj;
                                                    }
                                                    
                                                    $gooddetailgroupid = $pg[0]['id'];
                                                    $this->db->exec(array('UPDATE tbldebitnotes SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to select from table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            try {
                                                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;
                                                
                                                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                try {
                                                    $pg = array ();
                                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                                    
                                                    foreach ( $r as $obj ) {
                                                        $pg [] = $obj;
                                                    }
                                                    
                                                    $paymentdetailgroupid = $pg[0]['id'];
                                                    $this->db->exec(array('UPDATE tbldebitnotes SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            try {
                                                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;
                                                
                                                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                try {
                                                    $pg = array ();
                                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                                    
                                                    foreach ( $r as $obj ) {
                                                        $pg [] = $obj;
                                                    }
                                                    
                                                    $taxdetailgroupid = $pg[0]['id'];
                                                    $this->db->exec(array('UPDATE tbldebitnotes SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                        } catch (Exception $e) {
                                            $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to insert into the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                        
                                    } else {
                                        $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The debitnote exists", 'r');
                                        
                                        try{
                                            $this->db->exec(array('UPDATE tbldebitnotes SET debitnoteid = "' . $invoiceId .
                                                '", debitnoteno = "' . $invoiceNo .
                                                '", oriinvoiceid = "' . $oriInvoiceId .
                                                '", oriinvoiceno = "' . $oriInvoiceNo .
                                                '", issueddate = "' . $issuedDate .
                                                '", datasource = ' . $dataSource .
                                                ', currency = "' . $currency .
                                                '", issuedtime = "' . $issuedTime .
                                                '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                ' WHERE debitnoteno = "' . $invoiceNo . '"'));
                                            
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                        } catch (Exception $e) {
                                            $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : Failed to update the table tbldebitnotes. The error message is " . $e->getMessage(), 'r');
                                        }
                                    }
                                }
                            }
                            
                        } else {//NOTHING RETURNED BY API
                            $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The API did not return anything", 'r');
                        }
                        
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync debit notes by " . $this->f3->get('SESSION.username') . " was successful");
                        self::$systemalert = "The operation to sync debit notes by " . $this->f3->get('SESSION.username') . " was successful";
                    }
                } while ($pageNo <= $pageCount);
            }

            
            //die($data);
        } else {
            $this->logger->write("Debitnote Controller : syncefrisdebitnotes() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
    
    /**
     *	@name printdebitnote
     *  @desc Print an debitnote
     *	@return NULL
     *	@param NULL
     **/
    function printdebitnote(){
        $operation = NULL; //tblevents
        $permission = 'PRINTDEBITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Debitnote Controller : printdebitnote() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Debitnote Controller : printdebitnote() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Debitnote Controller : printdebitnote() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $id = trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("Debitnote Controller : printdebitnote() : The is a GET call & id to view is " . $id, 'r');
            
            // The Debitnote
            $debitnote = new debitnotes($this->db);
            $debitnote->netamount2 = 'FORMAT(netamount, 2)';
            $debitnote->taxamount2 = 'FORMAT(taxamount, 2)';
            $debitnote->grossamount2 = 'FORMAT(grossamount, 2)';
            $debitnote->getByID($id);
            $this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('debitnote', $debitnote);
            
            //The Seller
            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
            $this->f3->set('seller', $org);
            
            //The Buyer
            $buyer = new buyers($this->db);
            $buyer->getByID($debitnote->buyerid);
            $this->f3->set('buyer', $buyer);
            
            //The Goods
            try{
                $goods = array();
                
                $temp = $this->db->exec(array('SELECT item, FORMAT(qty, 2) qty, unitofmeasure, FORMAT(unitprice, 2) unitprice, FORMAT(total, 2) total, displayCategoryCode taxcategory, unitofmeasurename, discounttotal FROM tblgooddetails WHERE groupid = COALESCE(' . $debitnote->gooddetailgroupid . ', NULL) ORDER BY inserteddt ASC'));
                
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
                $this->logger->write("Debitnote Controller : printdebitnote() : The operation to retrieve goods was not successfull. The error messages is " . $e->getMessage(), 'r');
                $goods = array(
                    "0" => array()
                );
            }            
            $this->f3->set('goods', $goods);
            //$this->logger->write($this->db->log(TRUE), 'r');
            
            //The Tax Details
            try{
                $taxes = $this->db->exec(array('SELECT FORMAT(netamount, 2) netamount, FORMAT(taxrate, 2) taxrate, FORMAT(taxamount, 2) taxamount, FORMAT(grossamount, 2) grossamount, taxdescription FROM tbltaxdetails WHERE groupid = COALESCE(' . $debitnote->taxdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Debitnote Controller : printdebitnote() : The operation to retrieve taxes was not successfull. The error messages is " . $e->getMessage(), 'r');
                $taxes = array(
                    "0" => array()
                );
            }
            $this->f3->set('taxes', $taxes);
            
            
            //The Payments
            try{
                $payments = $this->db->exec(array('SELECT paymentmodename, FORMAT(paymentamount, 2) paymentamount FROM tblpaymentdetails WHERE groupid = COALESCE(' . $debitnote->paymentdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Debitnote Controller : printdebitnote() : The operation to retrieve payments was not successfull. The error messages is " . $e->getMessage(), 'r');
                $payments = array(
                    "0" => array()
                );
            }
            $this->f3->set('payments', $payments);
            
            
            $this->f3->set('path', '../' . $this->path);
            $this->f3->set('pagetitle','Print Debit Note | ' . $id);//display the edit form

            
            echo \Template::instance()->render('PrintDebitnote.htm');
        } else {
            $this->logger->write("Debitnote Controller : printdebitnote() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name pviewdebitnote
     *  @desc View an debitnote
     *	@return NULL
     *	@param NULL
     **/
    function pviewdebitnote(){
        $operation = NULL; //tblevents
        $permission = 'VIEWDEBITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Debitnote Controller : pviewdebitnote() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Debitnote Controller : pviewdebitnote() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Debitnote Controller : pviewdebitnote() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $id = trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("Debitnote Controller : pviewdebitnote() : The is a GET call & id to view is " . $id, 'r');
            
            // The Debitnote
            $debitnote = new debitnotes($this->db);
            $debitnote->netamount2 = 'FORMAT(netamount, 2)';
            $debitnote->taxamount2 = 'FORMAT(taxamount, 2)';
            $debitnote->grossamount2 = 'FORMAT(grossamount, 2)';
            $debitnote->getByID($id);
            $this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('debitnote', $debitnote);
            
            //The Seller
            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
            $this->f3->set('seller', $org);
            
            //The Buyer
            $buyer = new buyers($this->db);
            $buyer->getByID($debitnote->buyerid);
            $this->f3->set('buyer', $buyer);
            
            //The Goods
            try{
                $goods = array();
                
                $temp = $this->db->exec(array('SELECT item, FORMAT(qty, 2) qty, unitofmeasure, FORMAT(unitprice, 2) unitprice, FORMAT(total, 2) total, displayCategoryCode taxcategory, unitofmeasurename, discounttotal FROM tblgooddetails WHERE groupid = COALESCE(' . $debitnote->gooddetailgroupid . ', NULL) ORDER BY inserteddt ASC'));
                
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
                $this->logger->write("Debitnote Controller : pviewdebitnote() : The operation to retrieve goods was not successfull. The error messages is " . $e->getMessage(), 'r');
                $goods = array(
                    "0" => array()
                );
            }
            $this->f3->set('goods', $goods);
            //$this->logger->write($this->db->log(TRUE), 'r');
            
            //The Tax Details
            try{
                $taxes = $this->db->exec(array('SELECT FORMAT(netamount, 2) netamount, FORMAT(taxrate, 2) taxrate, FORMAT(taxamount, 2) taxamount, FORMAT(grossamount, 2) grossamount, taxdescription FROM tbltaxdetails WHERE groupid = COALESCE(' . $debitnote->taxdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Debitnote Controller : pviewdebitnote() : The operation to retrieve taxes was not successfull. The error messages is " . $e->getMessage(), 'r');
                $taxes = array(
                    "0" => array()
                );
            }
            $this->f3->set('taxes', $taxes);
            
            
            //The Payments
            try{
                $payments = $this->db->exec(array('SELECT paymentmodename, FORMAT(paymentamount, 2) paymentamount FROM tblpaymentdetails WHERE groupid = COALESCE(' . $debitnote->paymentdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Debitnote Controller : pviewdebitnote() : The operation to retrieve payments was not successfull. The error messages is " . $e->getMessage(), 'r');
                $payments = array(
                    "0" => array()
                );
            }
            $this->f3->set('payments', $payments);
            
            
            $this->f3->set('path', '../' . $this->path);
            $this->f3->set('pagetitle','View Debit Note | ' . $id);//display the edit form
            
            
            $this->f3->set('pagecontent','ViewDebitnote.htm');
            $this->f3->set('pagescripts','ViewDebitnoteFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Debitnote Controller : pviewdebitnote() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
}

?>