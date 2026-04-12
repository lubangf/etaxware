<?php

/**
 * This file is part of the etaxware system
 * The is the apikey controller class
 * @date: 08-04-2019
 * @file: ApikeyController.php
 * @path: ./app/controller/ApikeyController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
class ApikeyController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules

    /**
     *	@name view
     *  @desc view apikey
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = '', $tab = '', $tabpane = '') { 
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $status = new statuses($this->db);
        $apikeystatuses = $status->getByGroupID(1022);
        $this->f3->set('apikeystatuses', $apikeystatuses);
        
        $permission = new permissions($this->db);
        $permissions = $permission->all();
        //$this->f3->set('permissions', $permissions);
        
        $permissionlist = array();
                                      
        $this->logger->write("Apikey Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
        $this->logger->write("Apikey Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
        
        if (is_string($tab) && is_string($tabpane)){
            $this->logger->write("Apikey Controller : view() : The value of v_id is " . $v_id, 'r');
            $this->logger->write("Apikey Controller : view() : The value of tab is " . $tab, 'r');
            $this->logger->write("Apikey Controller : view() : The value of tabpane " . $tabpane, 'r');
        }
        
        if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
            $id = trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("Apikey Controller : view() : The is a GET call & id to view is " . $id, 'r');
            
            $apikey = new apikeys($this->db);
            $apikey->getByID($id);
            $this->f3->set('apikey', $apikey); 
            
            //Customise the permission list for the selected apikey
            $permissiondetail = new permissiondetails($this->db);
            $permissiondetails = $permissiondetail->getByGroup($apikey->permissiongroup);
            
            $this->logger->write("Apikey Controller : view() : The apikey permission group is " . $apikey->permissiongroup, 'r');
            
            foreach ($permissions as $p) {
                if (!empty($permissiondetails)) {
                    foreach ($permissiondetails as $pd) {
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
                
                //$this->logger->write("Apikey Controller : view() : The option is: " . $l, 'r');
                $permissionlist[] = $l;
            }
            
            if (is_string($tab) && is_string($tabpane)){//this check is necessary for cases where the GET request is system initiated. The params sent to the view functions are non-string.
                $this->f3->set('currenttab', $tab);
                $this->f3->set('currenttabpane', $tabpane);
            } else {
                $this->f3->set('currenttab', 'tab_general');
                $this->f3->set('currenttabpane', 'tab_1');
                $this->f3->set('path', '../' . $this->path);
            }
            
            $this->f3->set('pagetitle','Edit Apikey | ' . $id);//display the edit form
        } elseif (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id')))) {
            $id = trim($this->f3->get('POST.id'));
            $this->logger->write("Apikey Controller : view() : This is a POST call & the id to view is " . $id, 'r');
            
            $apikey = new apikeys($this->db);
            $apikey->getByID($id);
            $this->f3->set('apikey', $apikey);
            
            //Customise the permission list for the selected apikey
            $permissiondetail = new permissiondetails($this->db);
            $permissiondetails = $permissiondetail->getByGroup($apikey->permissiongroup);
            
            $this->logger->write("Apikey Controller : view() : The apikey permission group is " . $apikey->permissiongroup, 'r');
            
            foreach ($permissions as $p) {
                if (!empty($permissiondetails)) {
                    foreach ($permissiondetails as $pd) {
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
                //$this->logger->write("Apikey Controller : view() : The option is: " . $l, 'r');
                $permissionlist[] = $l;
            }
            
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
            
            $this->f3->set('pagetitle','Edit Apikey | ' . $id);//display the edit form
        } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
            $id = trim($v_id);
            $this->logger->write("Apikey Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
            
            $apikey = new apikeys($this->db);
            $apikey->getByID($id);
            $this->f3->set('apikey', $apikey);
            
            //Customise the permission list for the selected apikey
            $permissiondetail = new permissiondetails($this->db);
            $permissiondetails = $permissiondetail->getByGroup($apikey->permissiongroup);
            
            $this->logger->write("Apikey Controller : view() : The apikey permission group is " . $apikey->permissiongroup, 'r');
            
            foreach ($permissions as $p) {
                if (!empty($permissiondetails)) {
                    foreach ($permissiondetails as $pd) {
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
                //$this->logger->write("Apikey Controller : view() : The option is: " . $l, 'r');
                $permissionlist[] = $l;
            }
            
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
            
            $this->f3->set('pagetitle','Edit Apikey | ' . $id);//display the edit form
            
            /*Tests confirm that this type of call changes the URL from '/etaxware/viewuser/143' to '/etaxware/adduser'
             We need to preserve the previous URL & tab*/
            //$this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page, but we lose track of the current tab & pane
            $this->f3->set('pagecontent','EditApikey.htm');
            $this->f3->set('pagescripts','EditApikeyFooter.htm');
            echo \Template::instance()->render('Layout.htm');
            exit(); //exit the function so no extra code executes
        } else {
            $this->logger->write("Apikey Controller : view() : No id was selected", 'r');
            $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER'));
            exit();
        }
        
        $this->f3->set('plts', $permissionlist);
        
        $this->f3->set('pagecontent','EditApikey.htm');
        $this->f3->set('pagescripts','EditApikeyFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
   
    /**
     *	@name add
     *  @desc add apikey
     *	@return NULL
     *	@param NULL
     **/
    function add() {
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $status = new statuses($this->db);
        $apikeystatuses = $status->getByGroupID(1022);
        $this->f3->set('apikeystatuses', $apikeystatuses);
        
        $permission = new permissions($this->db);
        $permissions = $permission->all();
        $this->f3->set('permissions', $permissions);
        
        //@TODO Display a new form
        $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
        $this->f3->set('currenttabpane', 'tab_1');
        
        //create a apikey object and pre-populate with default values
        $defaultapikeystatus = $this->appsettings['DEFAULTAPIKEYSTATUS'];
        
        $apikey = array(
            "id" => NULL,
            "apikey" => '',
            "status" => $defaultapikeystatus
        );
        $this->f3->set('apikey', $apikey);
        
        $this->f3->set('pagetitle','Create Apikey');
        
        $this->f3->set('pagecontent','EditApikey.htm');
        $this->f3->set('pagescripts','EditApikeyFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
       
    
    /**
     *	@name edit
     *  @desc edit a apikey
     *	@return NULL
     *	@param NULL
     *
     */
    function edit() {
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $apikey = new apikeys($this->db);
        $id = '';
        
        if (trim($this->f3->get('POST.apikeyid')) !== '' || !empty(trim($this->f3->get('POST.apikeyid'))) || trim($this->f3->get('POST.permissionapikeyid')) !== '' || !empty(trim($this->f3->get('POST.permissionapikeyid')))) {//EDIT Operation
            $id = !empty(trim($this->f3->get('POST.apikeyid')))? trim($this->f3->get('POST.apikeyid')) : trim($this->f3->get('POST.permissionapikeyid'));
            $this->logger->write("Apikey Controller : edit() : The id to be edited is " . $id, 'r');
            $apikey->getByID($id);
                        
            $currenttab = !empty(trim($this->f3->get('POST.currenttab')))? trim($this->f3->get('POST.currenttab')) : trim($this->f3->get('POST.permissioncurrenttab'));
            $currenttabpane = !empty(trim($this->f3->get('POST.currenttabpane')))? trim($this->f3->get('POST.currenttabpane')) : trim($this->f3->get('POST.permissioncurrenttabpane'));
            
            if ($currenttab == 'tab_general') {
                $this->logger->write("Apikey Controller : edit() : Editing general details started", 'r');
                              
                if (is_null(trim($this->f3->get('POST.apikey')))){
                    $this->f3->set('POST.apikey', $apikey->apikey);
                } else {
                    $this->f3->set('POST.apikey', $this->f3->get('POST.apikey'));
                }                              
                
                if (is_null(trim($this->f3->get('POST.apikeystatus')))){
                    $this->f3->set('POST.status', $apikey->status);
                } else {
                    $this->f3->set('POST.status', $this->f3->get('POST.apikeystatus'));
                }
                
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                try {
                    $apikey->edit($id);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The apikey " . $apikey->id . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The apikey " . $apikey->id . " has been edited";
                    $this->logger->write("Apikey Controller : edit() : The apikey " . $apikey->id . " has been edited", 'r');
                } catch (Exception $e) {
                    $this->logger->write("Apikey Controller : edit() : The operation to edit apikey " . $apikey->id . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, $this->appsettings['ERRORNOTIFICATION'], NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit apikey " . $apikey->id . " was not successfull");
                    self::$systemalert = "The operation to edit apikey " . $apikey->id . " was not successful";
                }
            } elseif ($currenttab = 'tab_permissions'){
                $this->logger->write("Apikey Controller : edit() : Editing permissions started", 'r');
                $permissiongroup = $apikey->permissiongroup;
                $entitytype = $this->appsettings['APIKEYENTITYTYPE'];
                /**
                 * 1. Check if the user has indeed made a selection
                 * 2. Clear out the tables tblpermissiongroups & tblpermissiondetails
                 * 3. Create a group for the apikey
                 * 4. Populate the table tblpermissiondetails with the choices the user has made
                 */
                
                //1. Check if the user has indeed made a selection
                if (!empty($this->f3->get('POST.apikeypermissions'))) {
                    //2. Clear out the tables tblpermissiongroups & tblpermissiondetails
                    if ($permissiongroup) {
                        try {
                            $this->db->exec(array('DELETE FROM tblpermissiongroups WHERE id = COALESCE(' . $permissiongroup . ', NULL)'));
                            $this->db->exec(array('DELETE FROM tblpermissiondetails WHERE groupid = COALESCE(' . $permissiongroup . ', NULL)'));
                        } catch (Exception $e) {
                            $this->logger->write("Apikey Controller : edt() : Failed to delete from tables tblpermissiongroups & tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                    
                    //3. Create a group for the apikey
                    try {
                        $permissiongroupdesc = "This is an autogenerated permission group for the apikey id " . $id;
                        
                        $this->db->exec(array('INSERT INTO tblpermissiongroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                    VALUES(' . $id . ', ' . $entitytype . ', "' . $permissiongroupdesc . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                        
                        try {
                            $pg = array ();
                            $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpermissiongroups WHERE owner = ' . $id . ' AND entitytype = ' . $entitytype . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                            
                            foreach ($r as $obj) {
                                $pg [] = $obj;
                            }
                            
                            $permissiongroup = $pg[0]['id'];
                           
                            $this->db->exec(array('UPDATE tblapikeys SET permissiongroup = ' . $permissiongroup . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                            
                        } catch (Exception $e) {
                            $this->logger->write("Apikey Controller : edit() : Failed to select from table tblpermissiongroups. The error message is " . $e->getMessage(), 'r');
                        }
                    } catch (Exception $e) {
                        $this->logger->write("Apikey Controller : edit() : Failed to insert into the table tblpermissiongroups. The error message is " . $e->getMessage(), 'r');
                    }
                    
                    //4. Populate the table tblpermissiondetails with the choices the user has made
                    $value = 1;
                    
                    foreach ($this->f3->get('POST.apikeypermissions') as $code) {
                        try {
                            $this->db->exec(array('INSERT INTO tblpermissiondetails (groupid, code, value, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $permissiongroup . ', "' . $code . '", ' . $value . ', NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                        } catch (Exception $e) {
                            $this->logger->write("Apikey Controller : edit() : Failed to insert a permission into the table tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The apikey " . $apikey->id . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The apikey " . $apikey->id . " has been edited";
                    $this->logger->write("Apikey Controller : edit() : The apikey " . $apikey->id . " has been edited", 'r');
                } else {
                    $this->logger->write("Apikey Controller : edit() : No permissions were selected. We assume all permissions have been revoked", 'r');
                    //1. Clear out the tables tblpermissiongroups & tblpermissiondetails
                    if ($permissiongroup) {
                        try {
                            $this->db->exec(array('DELETE FROM tblpermissiongroups WHERE id = COALESCE(' . $permissiongroup . ', NULL)'));
                            $this->db->exec(array('DELETE FROM tblpermissiondetails WHERE groupid = COALESCE(' . $permissiongroup . ', NULL)'));
                            $this->db->exec(array('UPDATE tblapikeys SET permissiongroup = NULL, modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                        } catch (Exception $e) {
                            $this->logger->write("Apikey Controller : edt() : Failed to delete from tables tblpermissiongroups & tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                }
                
                $apikey->getByID($id);//refresh the apikey details
            } else {
                $this->logger->write("Apikey Controller : edit() : No operation was specified", 'r');
            }
            
        } else {//ADD Operation
                                    
            $this->f3->set('POST.apikey', $this->f3->get('POST.apikey'));
            $this->f3->set('POST.status', $this->appsettings['DEFAULTAPIKEYSTATUS']);
            
            $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
            $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
            $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
            $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
            
            $this->logger->write("Apikey Controller : edit() : The apikey = " . $this->f3->get('POST.apikey'), 'r');
            $this->logger->write("Apikey Controller : edit() : The status = " . $this->appsettings['DEFAULTAPIKEYSTATUS'], 'r');
            
            //@TODO check the params for empty/null values
            if (trim($this->f3->get('POST.apikey')) !== '' || !empty(trim($this->f3->get('POST.apikey')))) {
                try {
                    
                    $apikey->add();
                    $this->logger->write($this->db->log(TRUE), 'r');
                    
                    try {
                        //retrieve the most recently inserted apikey
                        //@TODO: place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                        $data = array ();
                        
                        $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblapikeys WHERE insertedby = ' . $this->f3->get('SESSION.id')));
                        foreach ( $r as $obj ) {
                            $data [] = $obj;
                        }
                        
                        $this->logger->write("Apikey Controller : edit() : The apikey " . $data[0]['id'] . " has been added", 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The apikey " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                        self::$systemalert = "The apikey " . $data[0]['id'] . " has been added successfully by " . $this->f3->get('SESSION.username');
                        $id = $data[0]['id'];
                        $apikey->getByID($id);
                    } catch (Exception $e) {
                        $this->logger->write("Apikey Controller : edit() : The operation to retrieve the most recently added apikey was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, $this->appsettings['ERRORNOTIFICATION'], NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added apikey was not successful");
                        self::$systemalert = "The operation to retrieve the most recently added apikey was not successful";
                    }
                    
                    $currenttab = 'tab_general';
                    $currenttabpane = 'tab_1';
                    
                } catch (Exception $e) {
                    $this->logger->write("Apikey Controller : edit() : The operation to add an apikey was not successful. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a apikey was not successful");
                    self::$systemalert = "The operation to add a apikey was not successful";
                    self::add();
                    exit();
                }
            } else {
                $this->logger->write("Apikey Controller : edit() : Opps!, no key was specified", 'r');
                self::add();
                exit();
            }
        }
        
        $status = new statuses($this->db);
        $apikeystatuses = $status->getByGroupID(1022);
        $this->f3->set('apikeystatuses', $apikeystatuses);   
        
        $permission = new permissions($this->db);
        $permissions = $permission->all();
        //$this->f3->set('permissions', $permissions);
        
        $permissionlist = array();
        
        //Customise the permission list for the selected apikey        
        $permissiondetail = new permissiondetails($this->db);
        $permissiondetails = $permissiondetail->getByGroup($apikey->permissiongroup);
        
        $this->logger->write("Apikey Controller : edit() : The apikey permission group is " . $apikey->permissiongroup, 'r');
        
        foreach ($permissions as $p) {
            if (!empty($permissiondetails)) {
                foreach ($permissiondetails as $pd) {
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
            //$this->logger->write("Apikey Controller : edit() : The option is: " . $l, 'r');
            $permissionlist[] = $l;
        }
        
        $this->f3->set('plts', $permissionlist);
        
        $this->f3->set('apikey', $apikey);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);
        
        $this->f3->set('systemalert', self::$systemalert);
        
        $this->f3->set('pagetitle','Edit Apikey | ' . $id);
        
        $this->f3->set('pagecontent','EditApikey.htm');
        $this->f3->set('pagescripts','EditApikeyFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    

    /**
     *	@name list
     *  @desc list apikeys
     *	@return JSON-encoded object
     *	@param NULL
     */
    function list(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Apikey Controller : list() : Processing list of api keys started", 'r');
        $sql = '';
        
        $data = array ();
        
        if ($this->f3->get('SESSION.userrole') == $this->appsettings['SUPERADMINROLEID']){
            $sql = 'SELECT a.id "ID",
                        a.apikey "Key",
                        a.status "Status ID",
                        s.name "Status",
                        a.lastaccessdt "Last Access Date",
                        a.disabled "Disabled",
                        a.expirydt "Expiry Date",
                        a.modifieddt "Modified Date",
                        u.username "Modified By"
                    FROM tblapikeys a
                    LEFT JOIN tblstatuses s ON a.status = s.id AND s.groupid in (1022)
                    LEFT JOIN tblusers u on u.id = a.modifiedby';
        }else {
            $sql = 'SELECT a.id "ID",
                        a.apikey "Key",
                        a.status "Status ID",
                        s.name "Status",
                        a.lastaccessdt "Last Access Date",
                        a.disabled "Disabled",
                        a.expirydt "Expiry Date",
                        a.modifieddt "Modified Date",
                        u.username "Modified By"
                    FROM tblapikeys a
                    LEFT JOIN tblstatuses s ON a.status = s.id AND s.groupid in (1022)
                    LEFT JOIN tblusers u on u.id = a.modifiedby ' . ' WHERE a.insertedby = ' . $this->f3->get('SESSION.id');
        }
        
        try {
            $dtls = $this->db->exec($sql);
            
            //$this->logger->write($this->db->log(TRUE), 'r');
            foreach ( $dtls as $obj ) {
                $data [] = $obj;
            }
        } catch(Exception $e) {
            $this->logger->write("Apikey Controller : list() : The operation to retrive api keys was not successful. The error messages is " . $e->getMessage(), 'r');
        }
        
        
        //send to browser as JSON encoded object
        die(json_encode($data));
    }
}
?>
