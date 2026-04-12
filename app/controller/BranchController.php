<?php

/**
 * This file is part of the etaxware system
 * The is the branch controller class
 * @date: 08-04-2020
 * @file: BranchController.php
 * @path: ./app/controller/BranchController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class BranchController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules

    /**
     *	@name view
     *  @desc view branch
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = '', $tab = '', $tabpane = '') {  
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        
        $status = new statuses($this->db);
        $branchestatuses = $status->getByGroupID(1018);
        $this->f3->set('branchestatuses', $branchestatuses);
        
        $urabranch = new urabranches($this->db);
        $urabranches = $urabranch->all();
        $this->f3->set('urabranches', $urabranches);
                                      
        $this->logger->write("Branch Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
        $this->logger->write("Branch Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
        
        if (is_string($tab) && is_string($tabpane)){
            $this->logger->write("Branch Controller : view() : The value of v_id is " . $v_id, 'r');
            $this->logger->write("Branch Controller : view() : The value of tab is " . $tab, 'r');
            $this->logger->write("Branch Controller : view() : The value of tabpane " . $tabpane, 'r');
        }
        
        if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
            $id = trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("Branch Controller : view() : The is a GET call & id to view is " . $id, 'r');
            
            $branch = new branches($this->db);
            $branch->getByID($id);
            $this->f3->set('branch', $branch); 

            
            if (is_string($tab) && is_string($tabpane)){//this check is necessary for cases where the GET request is system initiated. The params sent to the view functions are non-string.
                $this->f3->set('currenttab', $tab);
                $this->f3->set('currenttabpane', $tabpane);
            } else {
                $this->f3->set('currenttab', 'tab_general');
                $this->f3->set('currenttabpane', 'tab_1');
                $this->f3->set('path', '../' . $this->path);
            }
            
            $this->f3->set('pagetitle','Edit Branch | ' . $id);//display the edit form
        } elseif (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id')))) {
            $id = trim($this->f3->get('POST.id'));
            $this->logger->write("Branch Controller : view() : This is a POST call & the id to view is " . $id, 'r');
            
            $branch = new branches($this->db);
            $branch->getByID($id);
            $this->f3->set('branch', $branch);
            
            
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
            
            $this->f3->set('pagetitle','Edit Branch | ' . $id);//display the edit form
        } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
            $id = trim($v_id);
            $this->logger->write("Branch Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
            
            $branch = new branches($this->db);
            $branch->getByID($id);
            $this->f3->set('branch', $branch);
            
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
            
            $this->f3->set('pagetitle','Edit Branch | ' . $id);//display the edit form
            
            /*Tests confirm that this type of call changes the URL from '/etaxware/viewuser/143' to '/etaxware/adduser'
             We need to preserve the previous URL & tab*/
            //$this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page, but we lose track of the current tab & pane
            $this->f3->set('pagecontent','EditBranch.htm');
            $this->f3->set('pagescripts','EditBranchFooter.htm');
            echo \Template::instance()->render('Layout.htm');
            exit(); //exit the function so no extra code executes
        } else {
            $this->logger->write("Branch Controller : view() : No id was selected", 'r');
            $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER'));
            exit();
        }
        
        $this->f3->set('pagecontent','EditBranch.htm');
        $this->f3->set('pagescripts','EditBranchFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
   
    /**
     *	@name add
     *  @desc add branch
     *	@return NULL
     *	@param NULL
     **/
    function add() {
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        
        $status = new statuses($this->db);
        $branchestatuses = $status->getByGroupID(1018);
        $this->f3->set('branchestatuses', $branchestatuses);
        
        $permission = new permissions($this->db);
        $permissions = $permission->all();
        $this->f3->set('permissions', $permissions);
        
        $urabranch = new urabranches($this->db);
        $urabranches = $urabranch->all();
        $this->f3->set('urabranches', $urabranches);
        
        //@TODO Display a new form
        $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
        $this->f3->set('currenttabpane', 'tab_1');
        
        //create a branch object and pre-populate with default values
        $defaultbranchestatus = $this->appsettings['DEFAULTBRANCHSTATUS'];
        
        $branch = array(
            "id" => NULL,
            "code" => '',
            "name" => '',
            "description" => '',
            "status" => $defaultbranchestatus
        );
        $this->f3->set('branch', $branch);
        
        $this->f3->set('pagetitle','Create Branch');
        
        $this->f3->set('pagecontent','EditBranch.htm');
        $this->f3->set('pagescripts','EditBranchFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
       
    
    /**
     *	@name edit
     *  @desc edit a branch
     *	@return NULL
     *	@param NULL
     *
     */
    function edit() {
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $branch = new branches($this->db);
        $urabranch = new urabranches($this->db);
        $id = '';
        
        if (trim($this->f3->get('POST.branchid')) !== '' || !empty(trim($this->f3->get('POST.branchid')))) {//EDIT Operation
            $id = !empty(trim($this->f3->get('POST.branchid')))? trim($this->f3->get('POST.branchid')) : trim($this->f3->get('POST.permissionbranchid'));
            $this->logger->write("Branch Controller : edit() : The id to be edited is " . $id, 'r');
            $branch->getByID($id);
            $urabranch->getByID(trim($this->f3->get('POST.branchuraid')));
                        
            $currenttab = trim($this->f3->get('POST.currenttab'));
            $currenttabpane = trim($this->f3->get('POST.currenttabpane'));
            
            if ($currenttab == 'tab_general') {
                $this->logger->write("Branch Controller : edit() : Editing general details started", 'r');
                              
                if (is_null(trim($this->f3->get('POST.branchname')))){
                    $this->f3->set('POST.name', $branch->name);
                } else {
                    $this->f3->set('POST.name', $this->f3->get('POST.branchname'));
                }
                                
                if (is_null(trim($this->f3->get('POST.branchdescription')))){
                    $this->f3->set('POST.description', $branch->description);
                } else {
                    $this->f3->set('POST.description', $this->f3->get('POST.branchdescription'));
                }
                
                if (is_null(trim($this->f3->get('POST.branchestatus')))){
                    $this->f3->set('POST.status', $branch->status);
                } else {
                    $this->f3->set('POST.status', $this->f3->get('POST.branchestatus'));
                }
                
                
                
                if (is_null(trim($this->f3->get('POST.brancherpid')))){
                    $this->f3->set('POST.erpid', $branch->erpid);
                } else {
                    $this->f3->set('POST.erpid', $this->f3->get('POST.brancherpid'));
                }
                
                if (is_null(trim($this->f3->get('POST.brancherpcode')))){
                    $this->f3->set('POST.erpcode', $branch->erpcode);
                } else {
                    $this->f3->set('POST.erpcode', $this->f3->get('POST.brancherpcode'));
                }
                
                if (is_null(trim($this->f3->get('POST.brancherpname')))){
                    $this->f3->set('POST.erpname', $branch->erpname);
                } else {
                    $this->f3->set('POST.erpname', $this->f3->get('POST.brancherpname'));
                }
                
                if (trim($this->f3->get('POST.branchefrismap')) !== '' || !empty(trim($this->f3->get('POST.branchefrismap')))) {
                    $urabranch->getByID(trim($this->f3->get('POST.branchefrismap')));
                    
                    $this->f3->set('POST.uraid', $urabranch->branchid);
                    $this->f3->set('POST.uraname', $urabranch->name);
                    $this->f3->set('POST.uramap', $urabranch->id);
                } else {
                    $this->logger->write("Branch Controller : edit() : The EFRIS mapped branch was not selected.", 'r');
                }
                
                /*if (is_null(trim($this->f3->get('POST.branchuraid')))){
                    $this->f3->set('POST.uraid', $branch->uraid);
                } else {
                    $this->f3->set('POST.uraid', $this->f3->get('POST.branchuraid'));
                }
                
                if (is_null(trim($this->f3->get('POST.branchuracode')))){
                    $this->f3->set('POST.uracode', $branch->uracode);
                } else {
                    $this->f3->set('POST.uracode', $this->f3->get('POST.branchuracode'));
                }*/
                
                
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                try {
                    $branch->edit($id);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The branch " . $branch->id . " - " . $branch->name . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The branch " . $branch->id . " - " . $branch->name . " has been edited";
                    $this->logger->write("Branch Controller : edit() : The branch " . $branch->id . " - " . $branch->name . " has been edited", 'r');
                } catch (Exception $e) {
                    $this->logger->write("Branch Controller : edit() : The operation to edit branch " . $branch->id . " - " . $branch->name . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit branch " . $branch->id . " - " . $branch->name . " was not successfull");
                    self::$systemalert = "The operation to edit branch " . $branch->id . " - " . $branch->name . " was not successful";
                }
            } else {
                $this->logger->write("Branch Controller : edit() : No operation was specified", 'r');
            }
            
        } else {//ADD Operation
                                    
            $this->f3->set('POST.code', $this->f3->get('POST.branchcode'));
            $this->f3->set('POST.name', $this->f3->get('POST.branchname'));
            $this->f3->set('POST.description', $this->f3->get('POST.branchdescription'));
            $this->f3->set('POST.status', $this->appsettings['DEFAULTBRANCHSTATUS']);
            
            if (trim($this->f3->get('POST.branchefrismap')) !== '' || !empty(trim($this->f3->get('POST.branchefrismap')))) {
                $urabranch->getByID(trim($this->f3->get('POST.branchefrismap')));
                
                $this->f3->set('POST.uraid', $urabranch->branchid);
                $this->f3->set('POST.uraname', $urabranch->name);
                $this->f3->set('POST.uramap', $urabranch->id);
            } else {
                $this->logger->write("Branch Controller : edit() : The EFRIS mapped branch was not selected.", 'r');
            }
            
            $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
            $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
            $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
            $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
            
            //@TODO check the params for empty/null values
            if (trim($this->f3->get('POST.branchcode')) !== '' || !empty(trim($this->f3->get('POST.branchcode')))) {
                try {
                    $branch->add();
                    
                    try {
                        //retrieve the most recently inserted branch
                        //@TODO: place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                        $data = array ();
                        
                        $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblbranches WHERE insertedby = ' . $this->f3->get('SESSION.id')));
                        foreach ( $r as $obj ) {
                            $data [] = $obj;
                        }
                        
                        $this->logger->write("Branch Controller : edit() : The branch " . $data[0]['id'] . " has been added", 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The branch " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                        self::$systemalert = "The branch " . $data[0]['id'] . " has been added successfully by " . $this->f3->get('SESSION.username');
                        $id = $data[0]['id'];
                        $branch->getByID($id);
                    } catch (Exception $e) {
                        $this->logger->write("Branch Controller : edit() : The operation to retrieve the most recently added branch was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added branch was not successful");
                        self::$systemalert = "The operation to retrieve the most recently added branch was not successful";
                    }
                    
                    $currenttab = 'tab_general';
                    $currenttabpane = 'tab_1';
                    
                } catch (Exception $e) {
                    $this->logger->write("Branch Controller : edit() : The operation to add a branch was not successful. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a branch was not successful");
                    self::$systemalert = "The operation to add a branch was not successful";
                    self::add();
                    exit();
                }
            } else {
                $this->logger->write("Branch Controller : edit() : Opps!, no name was specified", 'r');
                self::add();
                exit();
            }
        }
        
        $status = new statuses($this->db);
        $branchestatuses = $status->getByGroupID(1018);
        $this->f3->set('branchestatuses', $branchestatuses);  
        
        $urabranch = new urabranches($this->db);
        $urabranches = $urabranch->all();
        $this->f3->set('urabranches', $urabranches);
        
        $this->f3->set('branch', $branch);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);
        
        $this->f3->set('systemalert', self::$systemalert);
        
        $this->f3->set('pagetitle','Edit Branch | ' . $id);
        
        $this->f3->set('pagecontent','EditBranch.htm');
        $this->f3->set('pagescripts','EditBranchFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    

    /**
     *	@name list
     *  @desc list branches
     *	@return JSON-encoded object
     *	@param NULL
     */
    function list(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Branch Controller : list() : Processing list of branches started", 'r');
        $sql = '';
        
        $data = array ();
        
        if ($this->f3->get('SESSION.userrole') == $this->appsettings['SUPERADMINROLEID']){
            $sql = 'SELECT r.name "Branch Name",
                        r.id "ID",
                        s.id "Status ID",
                        s.name "Status",
                        r.disabled "Disabled",
                        r.inserteddt "Inserted Date",
                        r.modifieddt "Modified Date"
                    FROM tblbranches r
                    LEFT JOIN tblstatuses s ON r.status = s.id AND s.groupid in (1018)';
        }else {
            $sql = 'SELECT r.name "Branch Name",
                        r.id "ID",
                        s.id "Status ID",
                        s.name "Status",
                        r.disabled "Disabled",
                        r.inserteddt "Inserted Date",
                        r.modifieddt "Modified Date"
                    FROM tblbranches r
                    LEFT JOIN tblstatuses s ON r.status = s.id AND s.groupid in (1018)
                    WHERE r.insertedby = ' . $this->f3->get('SESSION.id');
        }
        
        try {
            $dtls = $this->db->exec($sql);
            
            //$this->logger->write($this->db->log(TRUE), 'r');
            foreach ( $dtls as $obj ) {
                $data [] = $obj;
            }
        } catch(Exception $e) {
            $this->logger->write("Branch Controller : listusers() : The operation to retrive branches was not successful. The error messages is " . $e->getMessage(), 'r');
        }

        
        //send to browser as JSON encoded object
        die(json_encode($data));
    }
}
?>
