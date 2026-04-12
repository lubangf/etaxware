<?php

/**
 * @desc This file is part of the etaxware system
 * @name UserController
 * @file: UserController.php
 * @path: ./app/controller/UserController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class UserController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules

    /**
     *	@name view
     *  @desc view user
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = '', $tab = '', $tabpane = '') {
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $status = new statuses($this->db);
        $userstatuses = $status->getByGroupID(1017);
        $this->f3->set('userstatuses', $userstatuses);
        
        $role = new roles($this->db);
        $roles = $role->getActive($this->appsettings['ACTIVEROLESTATUS']);
        $this->f3->set('roles', $roles);
        
        $branch = new branches($this->db);
        $branches = $branch->getActive($this->appsettings['ACTIVEBRANCHSTATUS']);
        $this->f3->set('branches', $branches);
        
        $permission = new permissions($this->db);
        $permissions = $permission->all();
        $permissionlist = array();
        
        $this->logger->write("User Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
        $this->logger->write("User Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
        
        if (is_string($tab) && is_string($tabpane)){
            $this->logger->write("User Controller : view() : The value of v_id is " . $v_id, 'r');
            $this->logger->write("User Controller : view() : The value of tab is " . $tab, 'r');
            $this->logger->write("User Controller : view() : The value of tabpane " . $tabpane, 'r');
        }
        
        if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
            $id = trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("User Controller : view() : The is a GET call & id to view is " . $id, 'r');
            
            $user = new users($this->db);
            $user->getByID($id);
            $this->f3->set('edit_user', $user);
            
            //Customise the permission list for the selected user
            $permissiondetail = new permissiondetails($this->db);
            $permissiondetails = $permissiondetail->getByGroup($user->permissiongroup);
            
            $this->logger->write("User Controller : view() : The user permission group is " . $user->permissiongroup, 'r');
            
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
                
                //$this->logger->write("User Controller : view() : The option is: " . $l, 'r');
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
            
            $this->f3->set('pagetitle','Edit User | ' . $id);//display the edit form
        } elseif (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id')))) {
            $id = trim($this->f3->get('POST.id'));
            $this->logger->write("User Controller : view() : This is a POST call & the id to view is " . $id, 'r');
            
            $user = new users($this->db);
            $user->getByID($id);
            $this->f3->set('edit_user', $user);
            
            //Customise the permission list for the selected user
            $permissiondetail = new permissiondetails($this->db);
            $permissiondetails = $permissiondetail->getByGroup($user->permissiongroup);
            
            $this->logger->write("User Controller : view() : The user permission group is " . $user->permissiongroup, 'r');
            
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
                
                //$this->logger->write("User Controller : view() : The option is: " . $l, 'r');
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
            
            $this->f3->set('pagetitle','Edit User | ' . $id);//display the edit form
        } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
            $id = trim($v_id);
            $this->logger->write("User Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
            
            $user = new users($this->db);
            $user->getByID($id);
            $this->f3->set('edit_user', $user);
            
            //Customise the permission list for the selected user
            $permissiondetail = new permissiondetails($this->db);
            $permissiondetails = $permissiondetail->getByGroup($user->permissiongroup);
            
            $this->logger->write("User Controller : view() : The user permission group is " . $user->permissiongroup, 'r');
            
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
                
                //$this->logger->write("User Controller : view() : The option is: " . $l, 'r');
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
            
            $this->f3->set('pagetitle','Edit User | ' . $id);//display the edit form
            
            /*Tests confirm that this type of call changes the URL from '/etaxware/viewuser/143' to '/etaxware/adduser'
             We need to preserve the previous URL & tab*/
            //$this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page, but we lose track of the current tab & pane
            $this->f3->set('pagecontent','EditUser.htm');
            $this->f3->set('pagescripts','EditUserFooter.htm');
            echo \Template::instance()->render('Layout.htm');
            exit(); //exit the function so no extra code executes
        } else {
            $this->logger->write("User Controller : view() : No id was selected", 'r');
            $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER'));
            exit();
        }
        
        $this->f3->set('plts', $permissionlist);
        
        $this->f3->set('pagecontent','EditUser.htm');
        $this->f3->set('pagescripts','EditUserFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    /**
     *	@name quickedit
     *  @desc edit a user
     *	@return NULL
     *	@param NULL
     *            
     */
    function quickedit() {
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("User Controller : quickedit() : Editing of user started.", 'r');
        $this->logger->write("User Controller : quickedit() : Ther user being edited is: " . $this->f3->get('POST.usernametobeedited'), 'r');
        $this->logger->write("User Controller : quickedit() : Ther user editing is: " . $this->f3->get('SESSION.username'), 'r');

        //when editing own profile; only allowed to edit 5 fields, i.e. password, email, firstname, middlename & lastname
        if (trim($this->f3->get('POST.usernametobeedited')) == trim($this->f3->get('SESSION.username'))) {
            // if the request is for update, it will update the selected user
            $this->logger->write("User Controller : quickedit() : The user is editing own profile", 'r');
            $user = new users($this->db);
            $user->getByUsername($this->f3->get('POST.usernametobeedited'));
            
            $this->logger->write("User Controller : quickedit() : The old password is " . $user->password, 'r');
            // hash the password in case it has been edited. If not, leave as is
            $newpassword = trim($this->f3->get('POST.password'));

            if (empty($newpassword)) {
                // if password was not set, then set password to existing value
                $this->logger->write("User Controller : quickedit() : The password has not been edited by user", 'r');
                $this->f3->set('POST.password', $user->password);
            } else {
                // hash new password
                $this->logger->write("User Controller : quickedit() : The new plain password is " . $this->f3->get('POST.password'), 'r');
                $this->f3->set('POST.password', password_hash($this->f3->get('POST.password'), PASSWORD_DEFAULT));
                $this->logger->write("User Controller : edit() : The new harshed password is " . $this->f3->get('POST.password'), 'r');
            }
            
            if (is_null(trim($this->f3->get('POST.email')))){
                $this->f3->set('POST.email', $user->email);
            }
                
            if (is_null(trim($this->f3->get('POST.firstname')))){
                $this->f3->set('POST.firstname', $user->firstname);
            }
            
            if (is_null(trim($this->f3->get('POST.lastname')))){
                $this->f3->set('POST.lastname', $user->lastname);
            }
            
            //$this->f3->set('POST.id', $user->id);
            $this->f3->set('POST.username', $user->username);
            $this->f3->set('POST.status', $user->status);
            $this->f3->set('POST.role', $user->role);
            $this->f3->set('POST.branch', $user->branch);
            $this->f3->set('POST.inserteddt', $user->inserteddt);
            $this->f3->set('POST.insertedby', $user->insertedby);
            $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
            $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
            $this->f3->set('POST.lastlogindt', $user->lastlogindt);
            // submit new data to the database
            $user->edit($this->f3->get('POST.id'));
            $this->logger->write("User Controller : quickedit() : The user " . $this->f3->get('POST.username') . " has been edited", 'r');
            //$this->logger->write($this->db->log(TRUE), 'r');
            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The user " . $this->f3->get('POST.id') . " has been edited by " . $this->f3->get('SESSION.username'));
            //$this->f3->reroute('/');
            $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER'));
        }else{
            $this->logger->write("User Controller : quickedit() : The user is editing another user's profile", 'r');
            $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER'));
        }
    }
    
    
    /**
     *	@name add
     *  @desc add user
     *	@return NULL
     *	@param NULL
     **/
    function add(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $status = new statuses($this->db);
        $statuses = $status->getByGroupID(1017);
        $this->f3->set('statuses', $statuses);
                
        $role = new roles($this->db);
        $roles = $role->getActive($this->appsettings['ACTIVEROLESTATUS']);
        $this->f3->set('roles', $roles);
        
        $branch = new branches($this->db);
        $branches = $branch->getActive($this->appsettings['ACTIVEBRANCHSTATUS']);
        $this->f3->set('branches', $branches);
        
        //@TODO Display a new form
        $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
        $this->f3->set('currenttabpane', 'tab_1');
        
        //create a user object and pre-populate with default values
        $defaultstatus = $this->appsettings['DEFAULTUSERSTATUS'];
        
        $user = array(
            "id" => NULL,
            "username" => '',
            "password" => '',
            "firstname" => '',
            "middlename" => '',
            "lastname" => '',
            "email" => '',
            "role" => '',
            "branch" => '',
            "status" => $defaultstatus
        );
        $this->f3->set('edituser', $user);
        
        $this->f3->set('pagetitle','Create User');
        
        $this->f3->set('pagecontent','EditUser.htm');
        $this->f3->set('pagescripts','EditUserFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
       
    
    /**
     *	@name edit
     *  @desc edit a user
     *	@return NULL
     *	@param NULL
     *
     */
    function edit(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $user = new users($this->db);
        $id = '';
        
        if (trim($this->f3->get('POST.edituserid')) !== '' || !empty(trim($this->f3->get('POST.edituserid'))) || trim($this->f3->get('POST.permissionuserid')) !== '' || !empty(trim($this->f3->get('POST.permissionuserid')))) {//EDIT Operation
            $id = !empty(trim($this->f3->get('POST.edituserid')))? trim($this->f3->get('POST.edituserid')) : trim($this->f3->get('POST.permissionuserid'));
            $this->logger->write("User Controller : edit() : The id to be edited is " . $id, 'r');
            $user->getByID($id);
                        
            $currenttab = !empty(trim($this->f3->get('POST.currenttab')))? trim($this->f3->get('POST.currenttab')) : trim($this->f3->get('POST.permissioncurrenttab'));
            $currenttabpane = !empty(trim($this->f3->get('POST.currenttabpane')))? trim($this->f3->get('POST.currenttabpane')) : trim($this->f3->get('POST.permissioncurrenttabpane'));
            
            if ($currenttab == 'tab_general') {
                $this->logger->write("User Controller : edit() : Edting general details started", 'r');
                $this->logger->write("User Controller : edituser() : The old password is " . $user->password, 'r');
                // hash the password in case it has been edited. If not, leave as is
                $newpassword = trim($this->f3->get('POST.edituserpassword'));
                
                if (empty($newpassword)) {
                    // if password was not set, then set password to existing value
                    $this->logger->write("User Controller : edituser() : The password has not been edited by user", 'r');
                    $this->f3->set('POST.password', $user->password);
                } else {
                    // hash new password
                    $this->logger->write("User Controller : edituser() : The new plain password is " . $this->f3->get('POST.edituserpassword'), 'r');
                    $this->f3->set('POST.password', password_hash($this->f3->get('POST.editpassword'), PASSWORD_DEFAULT));
                    $this->logger->write("User Controller : edit() : The new harshed password is " . $this->f3->get('POST.password'), 'r');
                }
                
                if (trim($this->f3->get('POST.editemail')) !== '' || !empty(trim($this->f3->get('POST.editemail')))){                   
                    $this->f3->set('POST.email', $this->f3->get('POST.editemail'));
                } else {
                    $this->f3->set('POST.email', $user->email);
                }
                
                if (trim($this->f3->get('POST.editfirstname')) !== '' || !empty(trim($this->f3->get('POST.editfirstname')))){                   
                    $this->f3->set('POST.firstname', $this->f3->get('POST.editfirstname'));
                } else {
                    $this->f3->set('POST.firstname', $user->firstname);
                }
                
                $this->f3->set('POST.middlename', $this->f3->get('POST.editmiddlename'));
                
                if (trim($this->f3->get('POST.editlastname')) !== '' || !empty(trim($this->f3->get('POST.editlastname')))){
                    $this->f3->set('POST.lastname', $this->f3->get('POST.editlastname'));
                } else {                    
                    $this->f3->set('POST.lastname', $user->lastname);
                }
                                
                if (trim($this->f3->get('POST.editbranch')) !== '' || !empty(trim($this->f3->get('POST.editbranch')))){                   
                    $this->f3->set('POST.branch', (int)$this->f3->get('POST.editbranch'));
                } else {
                    $this->f3->set('POST.branch', $user->branch);
                }
                
                if (trim($this->f3->get('POST.editstatus')) !== '' || !empty(trim($this->f3->get('POST.editstatus')))){                    
                    $this->f3->set('POST.status', (int)$this->f3->get('POST.editstatus'));
                } else {
                    $this->f3->set('POST.status', $user->status);
                }
                
                
                if (trim($this->f3->get('POST.editerpid')) !== '' || !empty(trim($this->f3->get('POST.editerpid')))){
                    $this->f3->set('POST.erpid', $this->f3->get('POST.editerpid'));
                } else {
                    $this->f3->set('POST.erpid', $user->erpid);
                }
                
                if (trim($this->f3->get('POST.editerpcode')) !== '' || !empty(trim($this->f3->get('POST.editerpcode')))){
                    $this->f3->set('POST.erpcode', $this->f3->get('POST.editerpcode'));
                } else {
                    $this->f3->set('POST.erpcode', $user->erpcode);
                }
                
                if (trim($this->f3->get('POST.edituraid')) !== '' || !empty(trim($this->f3->get('POST.edituraid')))){
                    $this->f3->set('POST.uraid', $this->f3->get('POST.edituraid'));
                } else {
                    $this->f3->set('POST.uraid', $user->uraid);
                }
                
                if (trim($this->f3->get('POST.edituracode')) !== '' || !empty(trim($this->f3->get('POST.edituracode')))){
                    $this->f3->set('POST.uracode', $this->f3->get('POST.edituracode'));
                } else {
                    $this->f3->set('POST.uracode', $user->uracode);
                }
                
                $this->logger->write("User Controller : edituser() : The erpuserid is " . $this->f3->get('POST.editerpuserid'), 'r');
                $this->logger->write("User Controller : edituser() : The erpusername is " . $this->f3->get('POST.editerpusername'), 'r');
                $this->logger->write("User Controller : edituser() : The urauserid is " . $this->f3->get('POST.editurauserid'), 'r');
                $this->logger->write("User Controller : edituser() : The urausername is " . $this->f3->get('POST.editurausername'), 'r');
  
                $this->logger->write("User Controller : edituser() : The new role is " . $this->f3->get('POST.editrole'), 'r');
                $this->logger->write("User Controller : edituser() : The old role is " . $user->role, 'r');
                                
                if (trim($this->f3->get('POST.editrole')) !== '' || !empty(trim($this->f3->get('POST.editrole')))) {
                    /**
                     * 1. Verify if there has been a change in role
                     * 2. If the user has a permissiongroup, then remove all permissions inherited from previous role, preserving custom permissions
                     * 3. If the user has no permissiongroup, create new permissions (refer to the process under the ADD operation)
                     * 4. Insert new inherited permissions
                     */
                    
                    //1. Verify if there has been a change in role
                    if (trim($this->f3->get('POST.editrole')) !== trim($user->role)) {
                        
                        $permissiongroup = $user->permissiongroup;
                        $value = 1;
                        $inheritedflag = 1;
                        $entitytype = $this->appsettings['USERENTITYTYPE'];
                        
                        $role = new roles($this->db);
                        $role->getByID($this->f3->get('POST.editrole'));
                        $rolepg = !empty($role->permissiongroup)? $role->permissiongroup : 'NULL';//role-specific permissions
                        $pr = $this->db->exec(array('SELECT DISTINCT p.code, p.value FROM tblpermissiondetails p WHERE p.groupid IN (' . $rolepg . ')'));
                        
                        if ($permissiongroup) {
                            //2. If the user has a permissiongroup, then remove all permissions inherited from previous role, preserving custom permissions
                            try {
                                $this->db->exec(array('DELETE FROM tblpermissiondetails WHERE inheritedflag = 1 AND groupid = COALESCE(' . $permissiongroup . ', NULL)'));
                            } catch (Exception $e) {
                                $this->logger->write("User Controller : edit() : Failed to delete from tables tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                            }                           
                        } else {
                            //3. If the user has no permissiongroup, create new permissions (refer to the process under the ADD operation)
                            try {
                                $permissiongroupdesc = "This is an autogenerated permission group for the user id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblpermissiongroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                            VALUES(' . $id . ', ' . $entitytype . ', "' . $permissiongroupdesc . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpermissiongroups WHERE owner = ' . $id . ' AND entitytype = ' . $entitytype . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ($r as $obj) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $permissiongroup = $pg[0]['id'];
                                    
                                    $this->db->exec(array('UPDATE tblusers SET permissiongroup = ' . $permissiongroup . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("User Controller : edit() : Failed to select from table tblpermissiongroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("User Controller : edit() : Failed to insert into the table tblpermissiongroups. The error message is " . $e->getMessage(), 'r');
                            }
                        }
                        
                        //4. Insert new inherited permissions
                        foreach ($pr as $obj) {
                            try {
                                $this->db->exec(array('INSERT INTO tblpermissiondetails (groupid, code, value, inheritedflag, inserteddt, insertedby, modifieddt, modifiedby)
                                                                VALUES(' . $permissiongroup . ', "' . $obj['code'] . '", ' . $value . ', ' . $inheritedflag . ', NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                            } catch (Exception $e) {
                                $this->logger->write("User Controller : edit() : Failed to insert a permission into the table tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                            }
                        }
                    }
                    $this->f3->set('POST.role', $this->f3->get('POST.editrole'));
                } else {
                    $this->f3->set('POST.role', $user->role);
                }
                
                
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                try {
                    $user->edit($id);
                    $this->logger->write($this->db->log(TRUE), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The user" . $user->id . " - " . $user->username . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The user " . $user->id . " - " . $user->username . " has been edited";
                    $this->logger->write("User Controller : edit() : The user " . $user->id . " - " . $user->username . " has been edited", 'r');
                } catch (Exception $e) {
                    $this->logger->write("User Controller : edit() : The operation to edit user " . $user->id . " - " . $user->username . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit user " . $user->id . " - " . $user->username . " was not successfull");
                    self::$systemalert = "The operation to edit user " . $user->id . " - " . $user->username . " was not successful";
                }
            } elseif ($currenttab = 'tab_permissions'){
                $this->logger->write("User Controller : edit() : Editing permissions started", 'r');
                $permissiongroup = $user->permissiongroup;
                $entitytype = $this->appsettings['USERENTITYTYPE'];
                
                /**
                 * 1. Check if the user has indeed made a selection
                 * 2. Clear out the tables tblpermissiongroups & tblpermissiondetails
                 * 3. Create a group for the role
                 * 4. Populate the table tblpermissiondetails with the choices the user has made
                 */
                
                //1. Check if the user has indeed made a selection
                if (!empty($this->f3->get('POST.edituserpermissions'))) {
                    //2. Clear out the tables tblpermissiongroups & tblpermissiondetails
                    if ($permissiongroup) {
                        try {
                            $this->db->exec(array('DELETE FROM tblpermissiongroups WHERE id = COALESCE(' . $permissiongroup . ', NULL)'));
                            $this->db->exec(array('DELETE FROM tblpermissiondetails WHERE groupid = COALESCE(' . $permissiongroup . ', NULL)'));
                        } catch (Exception $e) {
                            $this->logger->write("User Controller : edt() : Failed to delete from tables tblpermissiongroups & tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
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
                            
                            $this->db->exec(array('UPDATE tblusers SET permissiongroup = ' . $permissiongroup . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                            
                        } catch (Exception $e) {
                            $this->logger->write("User Controller : edit() : Failed to select from table tblpermissiongroups. The error message is " . $e->getMessage(), 'r');
                        }
                    } catch (Exception $e) {
                        $this->logger->write("User Controller : edit() : Failed to insert into the table tblpermissiongroups. The error message is " . $e->getMessage(), 'r');
                    }
                    
                    //4. Populate the table tblpermissiondetails with the choices the user has made
                    $value = 1;
                    
                    foreach ($this->f3->get('POST.edituserpermissions') as $code) {
                        try {
                            $this->db->exec(array('INSERT INTO tblpermissiondetails (groupid, code, value, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $permissiongroup . ', "' . $code . '", ' . $value . ', NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                        } catch (Exception $e) {
                            $this->logger->write("User Controller : edit() : Failed to insert a permission into the table tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The user " . $user->id . " - " . $user->username . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The user " . $user->id . " - " . $user->username . " has been edited";
                    $this->logger->write("User Controller : edit() : The user " . $user->id . " - " . $user->username . " has been edited", 'r');
                } else {
                    $this->logger->write("User Controller : edit() : No permissions were selected. We assume all permissions have been revoked", 'r');
                    //1. Clear out the tables tblpermissiongroups & tblpermissiondetails
                    if ($permissiongroup) {
                        try {
                            $this->db->exec(array('DELETE FROM tblpermissiongroups WHERE id = COALESCE(' . $permissiongroup . ', NULL)'));
                            $this->db->exec(array('DELETE FROM tblpermissiondetails WHERE groupid = COALESCE(' . $permissiongroup . ', NULL)'));
                            $this->db->exec(array('UPDATE tblusers SET permissiongroup = NULL, modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                        } catch (Exception $e) {
                            $this->logger->write("User Controller : edit() : Failed to delete from tables tblpermissiongroups & tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                }
                
                $user->getByID($id);//refresh the user details
            } else {
                $this->logger->write("User Controller : edit() : No operation was specified", 'r');
            }            
        } else {//ADD Operation                       
            //$this->logger->write("User Controller : edit() : The plain password is " . $this->f3->get('POST.edituserpassword'), 'r');
            $this->f3->set('POST.password', password_hash($this->f3->get('POST.edituserpassword'), PASSWORD_DEFAULT));
            //$this->logger->write("User Controller : edit() : The harshed password is " . $this->f3->get('POST.password'), 'r');
            
            $this->f3->set('POST.username', $this->f3->get('POST.editusername'));
            $this->f3->set('POST.email', $this->f3->get('POST.editemail'));
            $this->f3->set('POST.firstname', $this->f3->get('POST.editfirstname'));
            $this->f3->set('POST.middlename', $this->f3->get('POST.editmiddlename'));
            $this->f3->set('POST.lastname', $this->f3->get('POST.editlastname'));
            $this->f3->set('POST.status', $this->appsettings['DEFAULTUSERSTATUS']);            
            $this->f3->set('POST.role', $this->f3->get('POST.editrole'));  
            $this->f3->set('POST.branch', $this->f3->get('POST.editbranch'));
            
            $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
            $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
            $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
            $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
            
            //@TODO check the params for empty/null values
            if (trim($this->f3->get('POST.editusername')) !== '' || !empty(trim($this->f3->get('POST.editusername')))) {
                try {
                    $user->add();
                    
                    try {
                        //retrieve the most recently inserted matchset
                        //@TODO: place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                        $data = array ();
                        
                        $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblusers WHERE insertedby = ' . $this->f3->get('SESSION.id')));
                        foreach ( $r as $obj ) {
                            $data [] = $obj;
                        }
                        
                        $this->logger->write("User Controller : edit() : The user " . $data[0]['id'] . " has been added", 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The user " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                        self::$systemalert = "The user " . $data[0]['id'] . " has been added successfully by " . $this->f3->get('SESSION.username');
                        $id = $data[0]['id'];
                        $user->getByID($id);
                        
                        
                        /**
                         * 1. Create new permission group
                         * 2. Insert new inherited permissions
                         */
                        
                        $value = 1;
                        $inheritedflag = 1;
                        $entitytype = $this->appsettings['USERENTITYTYPE'];
                        
                        $role = new roles($this->db);
                        $role->getByID($this->f3->get('POST.editrole'));
                        $rolepg = !empty($role->permissiongroup)? $role->permissiongroup : 'NULL';//role-specific permissions
                        $pr = $this->db->exec(array('SELECT DISTINCT p.code, p.value FROM tblpermissiondetails p WHERE p.groupid IN (' . $rolepg . ')'));
                        
                        //1. Create new permission group
                        try {
                            $permissiongroupdesc = "This is an autogenerated permission group for the user id " . $id;
                            
                            $this->db->exec(array('INSERT INTO tblpermissiongroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                            VALUES(' . $id . ', ' . $entitytype . ', "' . $permissiongroupdesc . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                            
                            try {
                                $pg = array ();
                                $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpermissiongroups WHERE owner = ' . $id . ' AND entitytype = ' . $entitytype . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                
                                foreach ($r as $obj) {
                                    $pg [] = $obj;
                                }
                                
                                $permissiongroup = $pg[0]['id'];
                                
                                $this->db->exec(array('UPDATE tblusers SET permissiongroup = ' . $permissiongroup . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                
                            } catch (Exception $e) {
                                $this->logger->write("User Controller : edit() : Failed to select from table tblpermissiongroups. The error message is " . $e->getMessage(), 'r');
                            }
                        } catch (Exception $e) {
                            $this->logger->write("User Controller : edit() : Failed to insert into the table tblpermissiongroups. The error message is " . $e->getMessage(), 'r');
                        }
                        
                        //2. Insert inherited permissions
                        foreach ($pr as $obj) {
                            try {
                                $this->db->exec(array('INSERT INTO tblpermissiondetails (groupid, code, value, inheritedflag, inserteddt, insertedby, modifieddt, modifiedby)
                                                            VALUES(' . $permissiongroup . ', "' . $obj['code'] . '", ' . $value . ', ' . $inheritedflag . ', NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                            } catch (Exception $e) {
                                $this->logger->write("User Controller : edit() : Failed to insert a permission into the table tblpermissiondetails. The error message is " . $e->getMessage(), 'r');
                            }
                        }
                    } catch (Exception $e) {
                        $this->logger->write("User Controller : edit() : The operation to retrieve the most recently added user was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added user was not successful");
                        self::$systemalert = "The operation to retrieve the most recently added user was not successful";
                    }
                    
                    $currenttab = 'tab_general';
                    $currenttabpane = 'tab_1';
                    $user->getByID($id);//refresh the user details
                } catch (Exception $e) {
                    $this->logger->write("User Controller : edit() : The operation to add a user was not successful. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a user was not successful");
                    self::$systemalert = "The operation to add a user was not successful";
                    self::add();
                    exit();
                }
            } else {
                $this->logger->write("User Controller : edit() : Opps!, no username was specified", 'r');
                self::add();
                exit();
            }
        }
        
        $status = new statuses($this->db);
        $userstatuses = $status->getByGroupID(1017);
        $this->f3->set('userstatuses', $userstatuses);
        
        $role = new roles($this->db);
        $roles = $role->getActive($this->appsettings['ACTIVEROLESTATUS']);
        $this->f3->set('roles', $roles);
        
        $branch = new branches($this->db);
        $branches = $branch->getActive($this->appsettings['ACTIVEBRANCHSTATUS']);
        $this->f3->set('branches', $branches);
        
        $this->f3->set('edit_user', $user);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);
        
        $permission = new permissions($this->db);
        $permissions = $permission->all();
        $permissionlist = array();
        
        //Customise the permission list for the selected user
        $permissiondetail = new permissiondetails($this->db);
        $permissiondetails = $permissiondetail->getByGroup($user->permissiongroup);
        
        $this->logger->write("User Controller : view() : The user permission group is " . $user->permissiongroup, 'r');
        
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
            
            //$this->logger->write("User Controller : view() : The option is: " . $l, 'r');
            $permissionlist[] = $l;
        }
        $this->f3->set('plts', $permissionlist);
        
        $this->f3->set('systemalert', self::$systemalert);
        
        $this->f3->set('pagetitle','Edit User | ' . $id);
        
        $this->f3->set('pagecontent','EditUser.htm');
        $this->f3->set('pagescripts','EditUserFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    

    /**
     *	@name list
     *  @desc list users
     *	@return JSON-encoded object
     *	@param NULL
     */
    function list(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("User Controller : listusers() : Processing list of users started", 'r');
        $sql = '';
        
        $data = array ();
        
        if ($this->f3->get('SESSION.role') == $this->appsettings['SUPERADMINROLEID']){
            $sql = 'SELECT u.id "ID",
                        u.username "User Name",
                        u.email "Email",
                        u.firstname "First Name",
                        u.lastname "Last Name",
                        b.id "Branch ID",
                        b.name "Branch",
                        r.name "Role",
                        r.id "Role ID",
                        s.id "Status ID",
                        s.name "Status",
                        u.disabled "Disabled",
                        u.inserteddt "Inserted Date",
                        u.modifieddt "Modified Date",
                        u.lastlogindt "Last Login Date"
                    FROM tblusers u
                    LEFT JOIN tblroles r ON r.id = u.role
                    LEFT JOIN tblstatuses s ON u.status = s.id AND s.groupid in (1017)
                    LEFT JOIN tblbranches b on b.id = u.branch';
        }else {
            $sql = 'SELECT u.id "ID",
                        u.username "User Name",
                        u.email "Email",
                        u.firstname "First Name",
                        u.lastname "Last Name",
                        b.id "Branch ID",
                        b.name "Branch",
                        r.name "Role",
                        r.id "Role ID",
                        s.id "Status ID",
                        s.name "Status",
                        u.disabled "Disabled",
                        u.inserteddt "Inserted Date",
                        u.modifieddt "Modified Date",
                        u.lastlogindt "Last Login Date"
                        FROM tblusers u
                    LEFT JOIN tblroles r ON r.id = u.role
                    LEFT JOIN tblstatuses s ON u.status = s.id AND s.groupid in (3)
                    LEFT JOIN tblbranches b on b.id = u.branch ' . ' WHERE u.insertedby = ' . $this->f3->get('SESSION.id');
        }
        
        try {
            $dtls = $this->db->exec($sql);
            
            //$this->logger->write($this->db->log(TRUE), 'r');
            foreach ( $dtls as $obj ) {
                $data [] = $obj;
            }
        } catch(Exception $e) {
            $this->logger->write("User Controller : listusers() : The operation to retrive users was not successful. The error messages is " . $e->getMessage(), 'r');
        }

        
        //send to browser as JSON encoded object
        die(json_encode($data));
    }
}
?>
