<?php

/**
 * @name SettingController
 * @desc This file is part of the etaxware system. The is the Setting controller class
 * @date 08-04-2019
 * @file SettingController.php
 * @path ./app/controller/SettingController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */

Class SettingController extends MainController{
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
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->f3->set('settings', $this->appsettings);
        
        $role = new roles($this->db);
        $roles = $role->all();
        $this->f3->set('roles', $roles);
        
        $this->f3->set('currenttab', 'tab_general');//set the USER tab as ACTIVE
        $this->f3->set('currenttabpane', 'tab_1');
        
        $this->f3->set('pagetitle','Settings');
        $this->f3->set('pagecontent','Setting.htm');
        $this->f3->set('pagescripts','SettingFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    /**
     *	@name view
     *  @desc view settings
     *	@return NULL
     *	@param NULL
     **/
    function view($tab = '', $tabpane = '') {
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if (is_string($tab) && is_string($tabpane)){
            $this->logger->write("Setting Controller : view() : The value of tab is " . $tab, 'r');
            $this->logger->write("Setting Controller : view() : The value of tabpane " . $tabpane, 'r');
        }
        
        if (is_string($tab) && is_string($tabpane)){
            $this->f3->set('currenttab', $tab);
            $this->f3->set('currenttabpane', $tabpane);
        } else {
            $this->f3->set('currenttab', 'tab_general');
            $this->f3->set('currenttabpane', 'tab_1');
        }
        
        $this->logger->write("Setting Controller : view() : The currenttab has been set to " . $this->f3->get('currenttab'), 'r');
        $this->logger->write("Setting Controller : view() : The currenttabpane has been set to " . $this->f3->get('currenttabpane'), 'r');
        
        $this->f3->set('settings', $this->appsettings);;
        
        $role = new roles($this->db);
        $roles = $role->all();
        $this->f3->set('roles', $roles);
        
        $this->f3->set('pagetitle','Settings');
        $this->f3->set('pagecontent','Setting.htm');
        $this->f3->set('pagescripts','SettingFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    /**
     *	@name edit
     *  @desc Edit settings
     *	@return NULL
     *	@param NULL
     **/
    function edit(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Setting Controller : edit() : Editing of settings started", 'r');
        $data = array();
        $setting = new settings($this->db);
        $settings = $setting->all(); //fetch all the current settings
        foreach ($settings as $obj) {
            $data[$obj['code']] = $obj['value'];//insert a KEY/VALUE pair for each setting
        }
        
        $code = ''; //placeholder for setting's code
        
        $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
        $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
        
        /**
         * 1. Retrieve the value of the setting from the SETTING array
         * 2. Compare the value with what has been setting by the browser
         * 3. If the two are not the same, then update the database, else ignore
         */
        
        if (trim($this->f3->get('POST.APPSHORTNAME')) !== '' || !empty(trim($this->f3->get('POST.APPSHORTNAME')))) {
            $code = 'APPSHORTNAME';
            $this->f3->set('POST.value', $this->f3->get('POST.APPSHORTNAME'));
            
            if ($data[$code] !== $this->f3->get('POST.APPSHORTNAME')) {
                $setting->edit($code);
            }             
        } 
        
        if (trim($this->f3->get('POST.PROCDATE')) !== '' || !empty(trim($this->f3->get('POST.PROCDATE')))) {
            $code = 'PROCDATE';
            $this->f3->set('POST.value', $this->f3->get('POST.PROCDATE'));
            
            if ($data[$code] !== $this->f3->get('POST.PROCDATE')) {
                $setting->edit($code);
            }
        } 
        
        $this->logger->write("Setting Controller : edit() : The operation to edit the settings was successful", 'r');
        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit the settings by " . $this->f3->get('SESSION.username') . " was successful");
        self::$systemalert = "The operation to edit the settings was successful";
        $this->f3->set('systemalert', self::$systemalert);
        self::view($this->f3->get('POST.currenttab'), $this->f3->get('POST.currenttabpane'));
        
    }
}

?>