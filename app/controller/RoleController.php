<?php

/**
 * @desc This file is part of the etaxware system
 * @name RoleController
 * @date: 08-04-2019
 * @file: RoleController.php
 * @path: ./app/controller/RoleController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class RoleController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules

    /**
     *	@name view
     *  @desc view role
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = '', $tab = '', $tabpane = '') { 
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $status = new statuses($this->db);
        $rolestatuses = $status->getByGroupID(1019);
        $this->f3->set('rolestatuses', $rolestatuses);
        
        $permission = new permissions($this->db);
        $permissions = $permission->all();
        //$this->f3->set('permissions', $permissions);
        
        $permissionlist = array();
                                      
        $this->logger->write("Role Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
        $this->logger->write("Role Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
        
        if (is_string($tab) && is_string($tabpane)){
            $this->logger->write("Role Controller : view() : The value of v_id is " . $v_id, 'r');
            $this->logger->write("Role Controller : view() : The value of tab is " . $tab, 'r');
            $this->logger->write("Role Controller : view() : The value of tabpane " . $tabpane, 'r');
        }
        
        if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
            $id = trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("Role Controller : view() : The is a GET call & id to view is " . $id, 'r');
            
            $role = new roles($this->db);
            $role->getByID($id);
            $this->f3->set('role', $role); 
            
            //Customise the permission list for the selected role
            $permissiondetail = new permissiondetails($this->db);
            $permissiondetails = $permissiondetail->getByGroup($role->permissiongroup);
            
            $this->logger->write("Role Controller : view() : The role permission group is " . $role->permissiongroup, 'r');
            
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
                
                //$this->logger->write("Role Controller : view() : The option is: " . $l, 'r');
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
            
            $this->f3->set('pagetitle','Edit Role | ' . $id);//display the edit form
        } elseif (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id')))) {
            $id = trim($this->f3->get('POST.id'));
            $this->logger->write("Role Controller : view() : This is a POST call & the id to view is " . $id, 'r');
            
            $role = new roles($this->db);
            $role->getByID($id);
            $this->f3->set('role', $role);
            
            //Customise the permission list for the selected role
            $permissiondetail = new permissiondetails($this->db);
            $permissiondetails = $permissiondetail->getByGroup($role->permissiongroup);
            
            $this->logger->write("Role Controller : view() : The role permission group is " . $role->permissiongroup, 'r');
            
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
                //$this->logger->write("Role Controller : view() : The option is: " . $l, 'r');
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
            
            $this->f3->set('pagetitle','Edit Role | ' . $id);//display the edit form
        } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
            $id = trim($v_id);
            $this->logger->write("Role Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
            
            $role = new roles($this->db);
            $role->getByID($id);
            $this->f3->set('role', $role);
            
            //Customise the permission list for the selected role
            $permissiondetail = new permissiondetails($this->db);
            $permissiondetails = $permissiondetail->getByGroup($role->permissiongroup);
            
            $this->logger->write("Role Controller : view() : The role permission group is " . $role->permissiongroup, 'r');
            
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
                //$this->logger->write("Role Controller : view() : The option is: " . $l, 'r');
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
            
            $this->f3->set('pagetitle','Edit Role | ' . $id);//display the edit form
            
            /*Tests confirm that this type of call changes the URL from '/etaxware/viewuser/143' to '/etaxware/adduser'
             We need to preserve the previous URL & tab*/
            //$this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page, but we lose track of the current tab & pane
            $this->f3->set('pagecontent','EditRole.htm');
            $this->f3->set('pagescripts','EditRoleFooter.htm');
            echo \Template::instance()->render('Layout.htm');
            exit(); //exit the function so no extra code executes
        } else {
            $this->logger->write("Role Controller : view() : No id was selected", 'r');
            $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER'));
            exit();
        }
        
        $this->f3->set('plts', $permissionlist);
        
        $this->f3->set('pagecontent','EditRole.htm');
        $this->f3->set('pagescripts','EditRoleFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
   
    /**
     *	@name add
     *  @desc add role
     *	@return NULL
     *	@param NULL
     **/
    function add() {
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $status = new statuses($this->db);
        $rolestatuses = $status->getByGroupID(1019);
        $this->f3->set('rolestatuses', $rolestatuses);
        
        $permission = new permissions($this->db);
        $permissions = $permission->all();
        $this->f3->set('permissions', $permissions);
        
        //@TODO Display a new form
        $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
        $this->f3->set('currenttabpane', 'tab_1');
        
        //create a role object and pre-populate with default values
        $defaultrolestatus = $this->appsettings['DEFAULTROLESTATUS'];
        
        $role = array(
            "id" => NULL,
            "code" => '',
            "name" => '',
            "description" => '',
            "status" => $defaultrolestatus
        );
        $this->f3->set('role', $role);
        
        $this->f3->set('pagetitle','Create Role');
        
        $this->f3->set('pagecontent','EditRole.htm');
        $this->f3->set('pagescripts','EditRoleFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
       
    
    /**
     *	@name edit
     *  @desc edit a role
     *	@return NULL
     *	@param NULL
     *
     */
    function edit() {
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $role = new roles($this->db);
        $id = '';
        
        if (trim($this->f3->get('POST.roleid')) !== '' || !empty(trim($this->f3->get('POST.roleid'))) || trim($this->f3->get('POST.permissionroleid')) !== '' || !empty(trim($this->f3->get('POST.permissionroleid')))) {//EDIT Operation
            $id = !empty(trim($this->f3->get('POST.roleid')))? trim($this->f3->get('POST.roleid')) : trim($this->f3->get('POST.permissionroleid'));
            $this->logger->write("Role Controller : edit() : The id to be edited is " . $id, 'r');
            $role->getByID($id);
                        
            $currenttab = !empty(trim($this->f3->get('POST.currenttab')))? trim($this->f3->get('POST.currenttab')) : trim($this->f3->get('POST.permissioncurrenttab'));
            $currenttabpane = !empty(trim($this->f3->get('POST.currenttabpane')))? trim($this->f3->get('POST.currenttabpane')) : trim($this->f3->get('POST.permissioncurrenttabpane'));
            
            if ($currenttab == 'tab_general') {
                $this->logger->write("Role Controller : edit() : Editing general details started", 'r');
                              
                if (is_null(trim($this->f3->get('POST.rolename')))){
                    $this->f3->set('POST.name', $role->name);
                } else {
                    $this->f3->set('POST.name', $this->f3->get('POST.rolename'));
                }
                                
                if (is_null(trim($this->f3->get('POST.roledescription')))){
                    $this->f3->set('POST.description', $role->description);
                } else {
                    $this->f3->set('POST.description', $this->f3->get('POST.roledescription'));
                }
                
                if (is_null(trim($this->f3->get('POST.rolestatus')))){
                    $this->f3->set('POST.status', $role->status);
                } else {
                    $this->f3->set('POST.status', $this->f3->get('POST.rolestatus'));
                }
                
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                try {
                    $role->edit($id);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The role " . $role->id . " - " . $role->name . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The role " . $role->id . " - " . $role->name . " has been edited";
                    $this->logger->write("Role Controller : edit() : The role " . $role->id . " - " . $role->name . " has been edited", 'r');
                } catch (Exception $e) {
                    $this->logger->write("Role Controller : edit() : The operation to edit role " . $role->id . " - " . $role->name . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit role " . $role->id . " - " . $role->name . " was not successfull");
                    self::$systemalert = "The operation to edit role " . $role->id . " - " . $role->name . " was not successful";
                }
            } elseif ($currenttab = 'tab_permissions'){
                $this->logger->write("Role Controller : edit() : Editing permissions started", 'r');
                $permissiongroup = $role->permissiongroup;
                $entitytype = $this->appsettings['ROLEENTITYTYPE'];
                /**
                 * 1. Check if the user has indeed made a selection
                 * 2. Clear out the tables tblpermissiongroups & tblpermissiondetails
                 * 3. Create a group for the role
                 * 4. Populate the table tblpermissiondetails with the choices the user has made
                 */
                
                //1. Check if the user has indeed made a selection
                if (!empty($this->f3->get('POST.rolepermissions'))) {
                    //2. Clear out the tables tblpermissiongroups & tblpermissiondetails
                    if ($permissiongroup) {
                        try {
                            $this->db->exec(array('DELETE FROM tblpermissiongroups WHERE id = COALESCE(' . $permissiongroup . ', NULL)'));
                            $this->db->exec(array('DELETE FROM tblpermissiondetails WHERE groupid = COALESCE(' . $permissiongroup . ', NULL)'));
                        } catch (Exception $e) {
                            $this->logger->write("Role Controller : edt() : Failed to delete from tables tblpermissiongroups & tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                    
                    //3. Create a group for the role
                    try {
                        $permissiongroupdesc = "This is an autogenerated permission group for the role id " . $id;
                        
                        $this->db->exec(array('INSERT INTO tblpermissiongroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                    VALUES(' . $id . ', ' . $entitytype . ', "' . $permissiongroupdesc . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                        
                        try {
                            $pg = array ();
                            $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpermissiongroups WHERE owner = ' . $id . ' AND entitytype = ' . $entitytype . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                            
                            foreach ($r as $obj) {
                                $pg [] = $obj;
                            }
                            
                            $permissiongroup = $pg[0]['id'];
                           
                            $this->db->exec(array('UPDATE tblroles SET permissiongroup = ' . $permissiongroup . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                            
                        } catch (Exception $e) {
                            $this->logger->write("Role Controller : edit() : Failed to select from table tblpermissiongroups. The error message is " . $e->getMessage(), 'r');
                        }
                    } catch (Exception $e) {
                        $this->logger->write("Role Controller : edit() : Failed to insert into the table tblpermissiongroups. The error message is " . $e->getMessage(), 'r');
                    }
                    
                    //4. Populate the table tblpermissiondetails with the choices the user has made
                    $value = 1;
                    
                    foreach ($this->f3->get('POST.rolepermissions') as $code) {
                        try {
                            $this->db->exec(array('INSERT INTO tblpermissiondetails (groupid, code, value, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $permissiongroup . ', "' . $code . '", ' . $value . ', NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                        } catch (Exception $e) {
                            $this->logger->write("Role Controller : edit() : Failed to insert a permission into the table tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The role " . $role->id . " - " . $role->name . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The role " . $role->id . " - " . $role->name . " has been edited";
                    $this->logger->write("Role Controller : edit() : The role " . $role->id . " - " . $role->name . " has been edited", 'r');
                } else {
                    $this->logger->write("Role Controller : edit() : No permissions were selected. We assume all permissions have been revoked", 'r');
                    //1. Clear out the tables tblpermissiongroups & tblpermissiondetails
                    if ($permissiongroup) {
                        try {
                            $this->db->exec(array('DELETE FROM tblpermissiongroups WHERE id = COALESCE(' . $permissiongroup . ', NULL)'));
                            $this->db->exec(array('DELETE FROM tblpermissiondetails WHERE groupid = COALESCE(' . $permissiongroup . ', NULL)'));
                            $this->db->exec(array('UPDATE tblroles SET permissiongroup = NULL, modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                        } catch (Exception $e) {
                            $this->logger->write("Role Controller : edt() : Failed to delete from tables tblpermissiongroups & tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                }
                
                $role->getByID($id);//refresh the role details
            } else {
                $this->logger->write("Role Controller : edit() : No operation was specified", 'r');
            }
            
        } else {//ADD Operation
                                    
            $this->f3->set('POST.code', $this->f3->get('POST.rolecode'));
            $this->f3->set('POST.name', $this->f3->get('POST.rolename'));
            $this->f3->set('POST.description', $this->f3->get('POST.roledescription'));
            $this->f3->set('POST.status', $this->appsettings['DEFAULTROLESTATUS']);
            
            $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
            $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
            $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
            $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
            
            //@TODO check the params for empty/null values
            if (trim($this->f3->get('POST.rolecode')) !== '' || !empty(trim($this->f3->get('POST.rolecode')))) {
                try {
                    $role->add();
                    
                    try {
                        //retrieve the most recently inserted role
                        //@TODO: place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                        $data = array ();
                        
                        $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblroles WHERE insertedby = ' . $this->f3->get('SESSION.id')));
                        foreach ( $r as $obj ) {
                            $data [] = $obj;
                        }
                        
                        $this->logger->write("Role Controller : edit() : The role " . $data[0]['id'] . " has been added", 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The role " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                        self::$systemalert = "The role " . $data[0]['id'] . " has been added successfully by " . $this->f3->get('SESSION.username');
                        $id = $data[0]['id'];
                        $role->getByID($id);
                    } catch (Exception $e) {
                        $this->logger->write("Role Controller : edit() : The operation to retrieve the most recently added role was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added role was not successful");
                        self::$systemalert = "The operation to retrieve the most recently added role was not successful";
                    }
                    
                    $currenttab = 'tab_general';
                    $currenttabpane = 'tab_1';
                    
                } catch (Exception $e) {
                    $this->logger->write("Role Controller : edit() : The operation to add a role was not successful. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a role was not successful");
                    self::$systemalert = "The operation to add a role was not successful";
                    self::add();
                    exit();
                }
            } else {
                $this->logger->write("Role Controller : edit() : Opps!, no name was specified", 'r');
                self::add();
                exit();
            }
        }
        
        $status = new statuses($this->db);
        $rolestatuses = $status->getByGroupID(1019);
        $this->f3->set('rolestatuses', $rolestatuses);   
        
        $permission = new permissions($this->db);
        $permissions = $permission->all();
        //$this->f3->set('permissions', $permissions);
        
        $permissionlist = array();
        
        //Customise the permission list for the selected role        
        $permissiondetail = new permissiondetails($this->db);
        $permissiondetails = $permissiondetail->getByGroup($role->permissiongroup);
        
        $this->logger->write("Role Controller : edit() : The role permission group is " . $role->permissiongroup, 'r');
        
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
            //$this->logger->write("Role Controller : edit() : The option is: " . $l, 'r');
            $permissionlist[] = $l;
        }
        
        $this->f3->set('plts', $permissionlist);
        
        $this->f3->set('role', $role);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);
        
        $this->f3->set('systemalert', self::$systemalert);
        
        $this->f3->set('pagetitle','Edit Role | ' . $id);
        
        $this->f3->set('pagecontent','EditRole.htm');
        $this->f3->set('pagescripts','EditRoleFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    

    /**
     *	@name list
     *  @desc list roles
     *	@return JSON-encoded object
     *	@param NULL
     */
    function list(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Role Controller : list() : Processing list of roles started", 'r');
        $sql = '';
        
        $data = array ();
        
        if ($this->f3->get('SESSION.userrole') == $this->appsettings['SUPERADMINROLEID']){
            $sql = 'SELECT r.name "Role Name",
                        r.id "ID",
                        s.id "Status ID",
                        s.name "Status",
                        r.disabled "Disabled",
                        r.inserteddt "Inserted Date",
                        r.modifieddt "Modified Date"
                    FROM tblroles r
                    LEFT JOIN tblstatuses s ON r.status = s.id AND s.groupid in (1019)';
        }else {
            $sql = 'SELECT r.name "Role Name",
                        r.id "ID",
                        s.id "Status ID",
                        s.name "Status",
                        r.disabled "Disabled",
                        r.inserteddt "Inserted Date",
                        r.modifieddt "Modified Date"
                    FROM tblroles r
                    LEFT JOIN tblstatuses s ON r.status = s.id AND s.groupid in (1019)
                    WHERE r.insertedby = ' . $this->f3->get('SESSION.id');
        }
        
        try {
            $dtls = $this->db->exec($sql);
            
            //$this->logger->write($this->db->log(TRUE), 'r');
            foreach ( $dtls as $obj ) {
                $data [] = $obj;
            }
        } catch(Exception $e) {
            $this->logger->write("Role Controller : listusers() : The operation to retrive roles was not successful. The error messages is " . $e->getMessage(), 'r');
        }

        
        //send to browser as JSON encoded object
        die(json_encode($data));
    }
}
?>
