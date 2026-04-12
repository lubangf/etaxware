<?php

/**
 * @name CurrencyController
 * @desc This file is part of the etaxware system. The is the Currency controller class
 * @date 11-05-2020
 * @file CurrencyController.php
 * @path ./app/controller/CurrencyController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
class CurrencyController extends MainController{

    protected static $module = NULL;// tblmodules
    protected static $submodule = NULL;// tblsubmodules

    /**
     * Loads the index page
     *
     * @name index
     * @return NULL
     * @param
     *            NULL
     */
    function index(){
        $operation = NULL; // tblevents
        $permission = 'VIEWCURRENCIES'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications

        $this->logger->write("Currency Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->f3->set('pagetitle', 'Currencies');
            $this->f3->set('pagecontent', 'Currency.htm');
            $this->f3->set('pagescripts', 'CurrencyFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Currency Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }

    /**
     * view currency
     *
     * @name view
     * @return NULL
     * @param
     *            NULL
     */
    function view($v_id = '', $tab = '', $tabpane = ''){
        $operation = NULL; // tblevents
        $permission = 'VIEWCURRENCIES'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications

        $this->logger->write("Currency Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Currency Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Currency Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');

            if (is_string($tab) && is_string($tabpane)) {
                $this->logger->write("Currency Controller : view() : The value of v_id is " . $v_id, 'r');
                $this->logger->write("Currency Controller : view() : The value of tab is " . $tab, 'r');
                $this->logger->write("Currency Controller : view() : The value of tabpane " . $tabpane, 'r');
            }

            if (trim($this->f3->get('PARAMS[id]')) !== '' || ! empty(trim($this->f3->get('PARAMS[id]')))) { // Open EDIT mode
                $id = trim($this->f3->get('PARAMS[id]'));
                $this->logger->write("Currency Controller : view() : The is a GET call & id to view is " . $id, 'r');

                $currency = new currencies($this->db);
                $currency->getByID($id);
                $this->f3->set('currency', $currency);

                if (is_string($tab) && is_string($tabpane)) { // this check is necessary for cases where the GET request is system initiated. The params sent to the view functions are non-string.
                    $this->f3->set('currenttab', $tab);
                    $this->f3->set('currenttabpane', $tabpane);
                } else {
                    $this->f3->set('currenttab', 'tab_general');
                    $this->f3->set('currenttabpane', 'tab_1');
                    $this->f3->set('path', '../' . $this->path);
                }

                $this->f3->set('pagetitle', 'Edit Currency | ' . $id); // display the edit form
                                                                       // $this->f3->set('path', '../' . $this->path); //overide the main solution path
            } elseif (trim($this->f3->get('POST.id')) !== '' || ! empty(trim($this->f3->get('POST.id')))) { // Open EDIT mode
                $id = trim($this->f3->get('POST.id'));
                $this->logger->write("Currency Controller : view() : This is a POST call & the id to view is " . $id, 'r');

                $currency = new currencies($this->db);
                $currency->getByID($id);
                $this->f3->set('currency', $currency);

                if (trim($this->f3->get('POST.currenttab')) !== '' || ! empty(trim($this->f3->get('POST.currenttab')))) {
                    $this->f3->set('currenttab', trim($this->f3->get('POST.currenttab')));
                } else {
                    $this->f3->set('currenttab', 'tab_general'); // set the GENERAL tab as ACTIVE
                }

                if (trim($this->f3->get('POST.currenttabpane')) !== '' || ! empty(trim($this->f3->get('POST.currenttabpane')))) {
                    $this->f3->set('currenttabpane', trim($this->f3->get('POST.currenttabpane')));
                } else {
                    $this->f3->set('currenttabpane', 'tab_1');
                }

                $this->f3->set('pagetitle', 'Edit Currency | ' . $id); // display the edit form
                                                                       // $this->f3->set('path', '../' . $this->path);
            } elseif (trim($v_id) !== '' || ! empty(trim($v_id))) {
                $id = trim($v_id);
                $this->logger->write("Currency Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');

                $currency = new currencies($this->db);
                $currency->getByID($id);
                $this->f3->set('currency', $currency);

                if (trim($tab) !== '' || ! empty(trim($tab))) {
                    $this->f3->set('currenttab', $tab);
                } else {
                    $this->f3->set('currenttab', 'tab_general'); // set the GENERAL tab as ACTIVE
                }

                if (trim($tabpane) !== '' || ! empty(trim($tabpane))) {
                    $this->f3->set('currenttabpane', $tabpane);
                } else {
                    $this->f3->set('currenttabpane', 'tab_1');
                }

                $this->f3->set('pagetitle', 'Edit Currency | ' . $id); // display the edit form
                                                                       // $this->f3->set('path', '../' . $this->path);

                $this->f3->set('pagecontent', 'EditCurrency.htm');
                $this->f3->set('pagescripts', 'EditCurrencyFooter.htm');
                echo \Template::instance()->render('Layout.htm');
                exit(); // exit the function so no extra code executes
            } else {
                $this->logger->write("Currency Controller : view() : No id was selected", 'r');
                $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); // return to the previous page
                exit();
            }

            $this->logger->write("Currency Controller : view() : The currenttab has been set to " . $this->f3->get('currenttab'), 'r');
            $this->logger->write("Currency Controller : view() : The currenttabpane has been set to " . $this->f3->get('currenttabpane'), 'r');

            $this->f3->set('pagecontent', 'EditCurrency.htm');
            $this->f3->set('pagescripts', 'EditCurrencyFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Currency Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }

    /**
     * add currency
     *
     * @name add
     * @return NULL
     * @param
     *            NULL
     */
    function add(){
        $operation = NULL; // tblevents
        $permission = 'CREATECURRENCY'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications

        $this->logger->write("Currency Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            // @TODO Display a new form
            $this->f3->set('currenttab', 'tab_general'); // set the GENERAL tab as ACTIVE
            $this->f3->set('currenttabpane', 'tab_1');

            $currency = array(
                "id" => NULL,
                "name" => '',
                "code" => '',
                "description" => ''
            );
            $this->f3->set('currency', $currency);

            $this->f3->set('pagetitle', 'Create Currency');

            $this->f3->set('pagecontent', 'EditCurrency.htm');
            $this->f3->set('pagescripts', 'EditCurrencyFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Currency Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }

    /**
     * edit currency
     *
     * @name edit
     * @return NULL
     * @param
     *            NULL
     */
    function edit(){
        $currency = new currencies($this->db);
        $currenttab = trim($this->f3->get('POST.currenttab'));
        $currenttabpane = trim($this->f3->get('POST.currenttabpane'));
        $id = 0;
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        if (trim($this->f3->get('POST.currencyid')) !== '' || ! empty(trim($this->f3->get('POST.currencyid')))) { // EDIT Operation
            $operation = NULL; // tblevents
            $permission = 'EDITCURRENCY'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications

            $this->logger->write("Currency Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $id = trim($this->f3->get('POST.currencyid'));
                $this->logger->write("Currency Controller : edit() : The id to be edited is " . $id, 'r');
                $currency->getByID($id);

                if ($currenttab == 'tab_general') {
                    // @TODO check the params for empty/null values

                    if (trim($this->f3->get('POST.currencyname')) !== '' || ! empty(trim($this->f3->get('POST.currencyname')))) {
                        $this->f3->set('POST.name', $this->f3->get('POST.currencyname'));
                    } else {
                        $this->f3->set('POST.name', $currency->name);
                    }

                    if (trim($this->f3->get('POST.currencycode')) !== '' || ! empty(trim($this->f3->get('POST.currencycode')))) {
                        $this->f3->set('POST.code', $this->f3->get('POST.currencycode'));
                    } else {
                        $this->f3->set('POST.code', $currency->code);
                    }

                    if (trim($this->f3->get('POST.currencydescription')) !== '' || ! empty(trim($this->f3->get('POST.currencydescription')))) {
                        $this->f3->set('POST.description', $this->f3->get('POST.currencydescription'));
                    } else {
                        $this->f3->set('POST.description', $currency->description);
                    }

                    if (trim($this->f3->get('POST.currencyerpid')) !== '' || ! empty(trim($this->f3->get('POST.currencyerpid')))) {
                        $this->f3->set('POST.erpid', $this->f3->get('POST.currencyerpid'));
                    } else {
                        $this->f3->set('POST.erpid', $currency->erpid);
                    }

                    if (trim($this->f3->get('POST.currencyerpcode')) !== '' || ! empty(trim($this->f3->get('POST.currencyerpcode')))) {
                        $this->f3->set('POST.erpcode', $this->f3->get('POST.currencyerpcode'));
                    } else {
                        $this->f3->set('POST.erpcode', $currency->erpcode);
                    }
                }

                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));

                try {
                    $currency->edit($id);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The currency - " . $currency->id . " - " . $currency->code . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The currency  - " . $currency->id . " - " . $currency->code . " has been edited";
                    $this->logger->write("Currency Controller : edit() : The currency  - " . $currency->id . " - " . $currency->code . " has been edited", 'r');
                } catch (Exception $e) {
                    $this->logger->write("Currency Controller : edit() : The operation to edit currency - " . $currency->id . " - " . $currency->code . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit currency - " . $currency->id . " - " . $currency->code . " was not successfull");
                    self::$systemalert = "The operation to edit currency - " . $currency->id . " - " . $currency->code . " was not successful";
                }
            } else {
                $this->logger->write("Currency Controller : edit() : The user is not allowed to perform this function", 'r');
                $this->f3->reroute('/forbidden');
            }
        } else { // ADD Operation: mainly handles the GENERAL parameters, as the rest of the parameters will be added using the EDIT option
            $operation = NULL; // tblevents
            $permission = 'CREATECURRENCY'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications

            $this->logger->write("Currency Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $this->logger->write("Currency Controller : edit() : Adding of currency started.", 'r');

                $this->f3->set('POST.name', $this->f3->get('POST.currencyname'));
                $this->f3->set('POST.code', $this->f3->get('POST.currencycode'));
                $this->f3->set('POST.description', $this->f3->get('POST.currencydescription'));

                $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));

                // @TODO check the params for empty/null values
                if (trim($this->f3->get('POST.name')) !== '' || ! empty(trim($this->f3->get('POST.name')))) {
                    try {
                        // Proceed & create
                        $currency->add();
                        // $this->logger->write("Currency Controller : edit() : A new currency has been added", 'r');
                        try {
                            // retrieve the most recently inserted currency
                            // @TODO place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                            $data = array();
                            $r = $this->db->exec(array(
                                'SELECT MAX(id) "id" FROM tblcurrencies WHERE insertedby = ' . $this->f3->get('SESSION.id')
                            ));
                            foreach ($r as $obj) {
                                $data[] = $obj;
                            }

                            // $this->logger->write("Currency Controller : edit() : The currency " . $data[0]['id'] . " has been added", 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The currency id " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                            self::$systemalert = "The currency id " . $data[0]['id'] . " has been added";
                            $id = $data[0]['id'];
                            $currency->getByID($id);
                        } catch (Exception $e) {
                            $this->logger->write("Currency Controller : edit() : The operation to retrieve the most recently added currency was not successful. The error messages is " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added currency was not successful");
                            self::$systemalert = "The operation to retrieve the most recently added currency was not successful";
                        }
                    } catch (Exception $e) {
                        $this->logger->write("Currency Controller : edit() : The operation to add a currency was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a currency was not successful");
                        self::$systemalert = "The operation to add a currency was not successful";
                        $this->f3->set('systemalert', self::$systemalert);
                        self::add();
                        exit();
                    }
                } else {
                    $this->logger->write("Currency Controller : edit() : The user is not allowed to perform this function", 'r');
                    $this->f3->reroute('/forbidden');
                }
            } else { // some params are empty
                     // ABORT MISSION
                self::add();
                exit();
            }
        }

        $this->f3->set('currency', $currency);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);

        $this->f3->set('systemalert', self::$systemalert);

        $this->f3->set('pagetitle', 'Edit Currency | ' . $id);
        $this->f3->set('pagecontent', 'EditCurrency.htm');
        $this->f3->set('pagescripts', 'EditCurrencyFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }

    /**
     * List currencies
     *
     * @name list
     * @return JSON-encoded object
     * @param
     *            NULL
     */
    function list(){
        $operation = NULL; // tblevents
        $permission = 'VIEWCURRENCIES'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications

        $data = array();

        $this->logger->write("Currency Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Currency Controller : list() : Processing list of currencies started", 'r');

            $sql = 'SELECT  c.id "ID",
                        c.erpid "ERP ID",
                        c.erpcode "ERP Code",
                        c.code "Code",
                        c.name "Name",
                        c.description "Description",
                        c.rate "Rate",
                        c.exportLevy "exportLevy",
                        c.importDutyLevy "importDutyLevy",
                        c.inComeTax "inComeTax",
                        c.disabled "Disabled",
                        c.inserteddt "Creation Date",
                        c.insertedby "Created By",
                        c.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblcurrencies c
                    LEFT JOIN tblusers s ON c.modifiedby = s.id
                    ORDER By c.id DESC';

            try {
                $dtls = $this->db->exec($sql);

                // $this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Currencies Controller : list() : The operation to list the currencies was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Currency Controller : index() : The user is not allowed to perform this function", 'r');
        }

        die(json_encode($data));
    }

    /**
     * fetch currencyrates from EFRIS
     *
     * @name fetchcurrencyrates
     * @return
     * @param $userid int
     */
    function fetchcurrencyrates(){
        $operation = NULL; // tblevents
        $permission = 'FETCHCURRENCYRATES'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Currency Controller : fetchcurrencyrates() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            // $data = json_encode(new stdClass);
            
            $c_data = array();
            $data = $this->util->fetchcurrencyrates($this->f3->get('SESSION.id')); // will return JSON.
            // var_dump($data);
            
            $data = json_decode($data, true);
            
            if (isset($data['returnCode'])){
                $this->logger->write("Currency Controller : fetchcurrencyrates() : The operation to fetch currencies was not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync EFRIS branches by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to fetch currencies by " . $this->f3->get('SESSION.username') . " was not successful. The error message is " . $data['returnMessage'];
            } else {
                if ($data) {
                    foreach ($data as $elem) {
                        // $this->logger->write("Currency Controller : fetchcurrencyrates() : The currencies are " . $elem['currency']. ", ".$elem['nowTime']. ", ".$elem['timeFormat']. ", ".$elem['rate'], 'r');
                        $currency = new currencies($this->db);
                        $currency->getByName(trim($elem['currency']));
                        
                        if ($currency->dry()) {
                            $this->logger->write("Currency Controller : fetchcurrencyrates() : The currency does not exist", 'r');
                            
                            /*try {
                                $this->db->exec(array(
                                    'INSERT INTO tblcurrencies (name, rate, inserteddt, insertedby, modifieddt, modifiedby) VALUES("' . $elem['currency'] .
                                                        '", ' . $elem['rate'] . 
                                                        ', "' . date('Y-m-d H:i:s') .
                                                        '", ' . $this->f3->get('SESSION.id') .
                                                        ', "' . date('Y-m-d H:i:s') .
                                                        '", ' . $this->f3->get('SESSION.id') . ')'
                                ));
                                
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The currency: " . $elem['currency'] . " was inserted by " . $this->f3->get('SESSION.username'));
                            } catch (Exception $e) {
                                $this->logger->write("Currencies Controller : fetchcurrencyrates() : The operation to insert the currency was not successful. The error message is " . $e->getMessage(), 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to insert the currency by " . $this->f3->get('SESSION.username') . " was not successful");
                            }*/
                        } else {
                            $this->logger->write("Currency Controller : fetchcurrencyrates() : The currency exists", 'r');
                            
                            try {
                                $this->db->exec(array(
                                    'UPDATE tblcurrencies SET rate = ' . $elem['rate'] . 
                                                            ', exportLevy = ' . $elem['exportLevy'] . 
                                                            ', importDutyLevy = ' . $elem['importDutyLevy'] . 
                                                            ', inComeTax = ' . $elem['inComeTax'] . 
                                                            ', modifieddt = "' . $elem['nowTime'] . 
                                                            '", modifiedby = ' . $this->f3->get('SESSION.id') . 
                                                            ' WHERE TRIM(name) = "' . $elem['currency'] . '"'
                                ));
                                
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The currency rate for " . $elem['currency'] . " has been fetched by " . $this->f3->get('SESSION.username'));
                            } catch (Exception $e) {
                                $this->logger->write("Currencies Controller : fetchcurrencyrates() : The operation to update the currencies was not successful. The error message is " . $e->getMessage(), 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch currencies by " . $this->f3->get('SESSION.username') . " was not successful");
                            }
                        }
                                               
                        
                    }
                } else {//NOTHING RETURNED BY API
                    $this->logger->write("Currency Controller : fetchcurrencyrates() : The API did not return anything", 'r');
                }
            }
            
            //Retrieve the updated list of currencies and return to the browser
            $sql = 'SELECT  c.id "ID",
                                        c.erpid "ERP ID",
                                        c.erpcode "ERP Code",
                                        c.code "Code",
                                        c.name "Name",
                                        c.description "Description",
                                        c.rate "Rate",
                                        c.exportLevy "exportLevy",
                                        c.importDutyLevy "importDutyLevy",
                                        c.inComeTax "inComeTax",
                                        c.disabled "Disabled",
                                        c.inserteddt "Creation Date",
                                        c.insertedby "Created By",
                                        c.modifieddt "Modified Date",
                                        s.username "Modified By"
                                    FROM tblcurrencies c
                                    LEFT JOIN tblusers s ON c.modifiedby = s.id
                                    ORDER By c.id DESC';
            
            try {
                $dtls = $this->db->exec($sql);                
                $this->logger->write($this->db->log(TRUE), 'r');
                
                if ($dtls) {
                    foreach ($dtls as $obj) {
                        $c_data[] = $obj;
                    }
                }
                
            } catch (Exception $e) {
                $this->logger->write("Currencies Controller : fetchcurrencyrates() : The operation to list the currencies was not successful. The error message is " . $e->getMessage(), 'r');
            }
            
            die(json_encode($c_data));
        } else {
            $this->logger->write("Currency Controller : fetchcurrencyrates() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
}

?>