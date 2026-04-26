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
        // 2026-04-26: Standalone Settings page is deprecated; keep route for backward compatibility.
        $this->f3->set('SESSION.systemalert', 'Settings has moved to Administration. Please use the Settings tab there.');
        $this->f3->reroute('/administration#tab_11');
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
        // 2026-04-26: Legacy edit endpoint is deprecated; settings edits now happen in Administration tab.
        $this->f3->set('SESSION.systemalert', 'Legacy Settings edits are no longer supported. Please use Administration > Settings.');
        $this->f3->reroute('/administration#tab_11');
        
    }
}

?>