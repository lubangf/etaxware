<?php

/**
 * This file is part of the etaxware system
 * The is the base controller class
 * @date: 07-08-2022
 * @file: MainController.php
 * @path: ./app/controller/MainController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class MainController{
    protected $f3;// store an instance of base 
    protected $db;// store database connection here  
    protected $logger;       
    protected $appsettings;// store the setting details here   
    protected $userpermissions;// store the permission details here       
    protected $util;// store utilities here   
    protected $path = 'public/';// resource path    
    protected static $systemalert; //hold alerts sent to the user
    protected $vatRegistered; //Flag to indicate if the tax payer is registered for VAT or not.
    protected $platformMode; //Determine if the platform is running in Integrated Mode OR as an Abridged ERP itself
    protected $efrisMode; //Determine if we are hitting the offline enabler or direct online APIs
    protected $integratedErp; //Determine the Type of ERP Integrated
    
    //protected $api;// store api here  

    /**
     * @name beforeroute
     * @desc Invoke before any session
     *
     * @return NULL
     * @param NULL
     *            
     */
    function beforeroute(){
        
        if(php_sapi_name() == "cli") {
            $this->logger->write("Main Controller : beforeroute() : In CLI mode!", 'r');
        } else {
            $inactivityPeriod = 0;
            
            
            
            if ($this->f3->get('SESSION.lastActivityDate') !== null) {
                $startDt = new DateTime(date('Y-m-d H:i:s'));
                $endDt = new DateTime($this->f3->get('SESSION.lastActivityDate'));
                
                $inactivityPeriod = $startDt->getTimestamp() - $endDt->getTimestamp();
                
                $this->logger->write("Main Controller : beforeroute() : The current time is " . date('Y-m-d H:i:s'), 'r');
                $this->logger->write("Main Controller : beforeroute() : The last activity date is " . $this->f3->get('SESSION.lastActivityDate'), 'r');
                $this->logger->write("Main Controller : beforeroute() : The inactivity period is " . $inactivityPeriod, 'r');
            } else {
                $this->logger->write("Main Controller : beforeroute() : The inactivity period is not yet set.", 'r');
            }
            
            // check if current user is logged into the application
            if ($this->f3->get('SESSION.username') == null) {
                // if there is no session, route to the login page
                $this->logger->write("Main Controller : beforeroute() : A user with no session tried to access the app. Redirecting them to the login page", 'r');
                
                $this->f3->reroute('/login');
                // exit the application
                exit();
            } elseif ($inactivityPeriod > $this->appsettings['MAXINACTIVITYTIME']) {
                // if the inactive period is greater than the maximum allowable inactivity time, then ask the user to login again.
                $this->logger->write("Main Controller : beforeroute() : The user's session expired. Redirecting them to the login page", 'r');
                
                //Reset ONLINE status
                if ($this->f3->get('SESSION.username')) {
                    try {
                        $this->db->exec(array('UPDATE tblusers SET online = 0, modifieddt = "' . date('Y-m-d H:i:s') . '", modifiedby = "' . $this->f3->get('SESSION.id') . '" WHERE id = ' . $this->f3->get('SESSION.id')));
                        $this->logger->write("Main Controller : beforeroute() : Resetting was successful", 'r');
                        //clear any sessions & cache, just in-case
                        $this->f3->clear('SESSION');
                        $this->f3->clear('CACHE');
                    } catch (Exception $e) {
                        $this->logger->write("Main Controller : beforeroute() : Failed to update the table tblusers. The error message is " . $e->getMessage(), 'r');
                    }
                }
                
                
                $this->f3->reroute('/login');
                // exit the application
                exit();
            } else {
                error_reporting(0);
                ini_set('display_errors', 0);
                
                $this->f3->set('path', $this->path);
                $this->logger->write("Main Controller : beforeroute() : The path is " . $this->path, 'r');
                
                $user = new users($this->db);
                $user->getByUsername($this->f3->get('SESSION.username'));
                $this->f3->set('user', $user);
                $this->f3->set('role', $user->role);
                
                //$this->logger->write("Main Controller : beforeroute() : Refreshing notifications", 'r');
                if ($this->f3->exists('notifications')) {
                    $this->f3->clear('notifications');
                }
                
                if ($this->f3->exists('notificationscount')) {
                    $this->f3->clear('notificationscount');
                }
                
                $notification = $this->util->getnotifications(NULL, $this->f3->get('SESSION.id'), $this->appsettings['DEFAULTNOTIFICATIONSTATUS'], $this->appsettings['USERENTITYTYPE'], $this->appsettings['INAPPNOTIFICATION']);
                $this->f3->set('notifications', $notification);
                $this->f3->set('notificationscount', count($notification));
                
                //clear system feedback
                $this->systemalert = '';
                
                if ($this->f3->exists('systemalert')) {
                    $this->f3->clear('systemalert');
                }
                
                self::$systemalert = '';
                $this->f3->set('systemalert', self::$systemalert);
                
                if ($this->f3->exists('appsettings')) {
                    $this->f3->clear('appsettings');
                }
                
                $this->f3->set('appsettings', $this->appsettings);
                
                if ($this->f3->exists('userpermissions')) {
                    $this->f3->clear('userpermissions');
                }
                
                /**
                 * 1. Get the user's permissions, both inherited & customised
                 * 2. Return to the browser
                 */
                $userpg = !empty($user->permissiongroup)? $user->permissiongroup : 'NULL';//user-specific permission
                
                $data = array();
                $pr = $this->db->exec(array('SELECT DISTINCT p.code, p.value FROM tblpermissiondetails p WHERE p.groupid IN (' . $userpg . ')'));
                foreach ($pr as $obj) {
                    $data[$obj['code']] = $obj['value'];//insert a KEY/VALUE pair for each permission
                }
                
                $this->userpermissions = $data;
                
                $this->f3->set('userpermissions', $this->userpermissions);
                
                /*
                 $vat_check = new DB\SQL\Mapper($this->db, 'tbltaxtypes');
                 $vat_check->load(array('TRIM(code)=?', $this->appsettings['EFRIS_VAT_TAX_TYPE_CODE']));
                 
                 if ($vat_check->dry()) {
                 $this->logger->write("Main Controller : beforeroute() : The tax payer is not VAT registered", 'r');
                 $this->vatRegistered = 'N';
                 } else {
                 $this->logger->write("Main Controller : beforeroute() : The tax payer is VAT registered", 'r');
                 $this->vatRegistered = 'Y';
                 }*/
                
                $this->f3->set('vatRegistered', $this->vatRegistered);
                $this->f3->set('platformMode', $this->platformMode);
                $this->f3->set('efrisMode', $this->efrisMode);
                $this->f3->set('integratedErp', $this->integratedErp);
            }
        }
        
    }

    /**
     * @desc Invoke at the end of a session
     * @name afteroute
     * @return NULL
     * @param NULL
     *            
     */
    function afterroute(){
        
        if(php_sapi_name() == "cli") {
            $this->logger->write("Main Controller : afterroute() : In CLI mode!", 'r');
        } else {
            
            if ($this->f3->get('SESSION.id') == null) {
                // if there is no session, route to the login page
                $this->logger->write("Main Controller : afterroute() : There is no active session.", 'r');
                //$this->f3->reroute('/login');
                // exit the application
                //exit();
            } else {
                //Update the user's last activity date
                $this->f3->set('SESSION.lastActivityDate', date('Y-m-d H:i:s'));
                
                try {
                    $this->db->exec(array('UPDATE tblusers SET lastActivityDate = "' . date('Y-m-d H:i:s') . '" WHERE id = ' . $this->f3->get('SESSION.id')));
                } catch (Exception $e) {
                    $this->logger->write("Main Controller : afterroute() : Failed to update the table tblusers. The error message is " . $e->getMessage(), 'r');
                }
            }
        }
        
    }

    /**
     * 
     * @name __constructor
     * @desc Constructor for the MainController class
     * @return NULL
     * @param NULL
     *            
     */
    function __construct(){
        $f3 = Base::instance();
        $this->f3 = $f3;

        $db = new DB\SQL($f3->get('dbserver'), $f3->get('dbuser'), $f3->get('dbpwd'), array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ));

        $this->db = $db;
        
        // 2026-04-26 11:00:00 +03:00 - Split verbose controller traces from operational app logs.
        $logger = new SmartLogger('app.log', 'app-trace.log');
        $this->logger = $logger; 
        
        $data = array();
        $setting = new settings($db);
        $settings = $setting->getNoneSensitive();
        
        foreach ($settings as $obj) {
            $data[$obj['code']] = $obj['value'];//insert a KEY/VALUE pair for each setting
        }
        
        $this->appsettings = $data;
        
        $util = new Utilities();
        $this->util = $util;
        
        /*
        $api = new Api();
        $this->api = $api;*/
        
        $vat_check = new DB\SQL\Mapper($this->db, 'tbltaxtypes');
        $vat_check->load(array('TRIM(code)=?', $this->appsettings['EFRIS_VAT_TAX_TYPE_CODE']));
        
        if ($vat_check->dry()) {
            $this->logger->write("Main Controller : __construct() : The tax payer is not VAT registered", 'r');
            $this->vatRegistered = 'N';
        } else {
            $this->logger->write("Main Controller : __construct() : The tax payer is VAT registered", 'r');
            $this->vatRegistered = 'Y';
        }  
        
        $platformModeCheck = new DB\SQL\Mapper($this->db, 'tblplatformmode');
        $platformModeCheck->load(array('TRIM(code)=?', $this->appsettings['PLATFORMODE']));
        
        if ($platformModeCheck->dry()) {
            $this->logger->write("Main Controller : __construct() : The Platform Mode is not set", 'r');
            $this->platformMode = NULL;
        } else {
            $this->logger->write("Main Controller : __construct() : The Platform Mode is set to: " . $platformModeCheck->name, 'r');
            $this->platformMode = $platformModeCheck->name;
        } 
        
        $efrisModeCheck = new DB\SQL\Mapper($this->db, 'tblefrismode');
        $efrisModeCheck->load(array('TRIM(code)=?', $this->appsettings['EFRISMODE']));
        
        if ($efrisModeCheck->dry()) {
            $this->logger->write("Main Controller : __construct() : The EFRIS Mode is not set", 'r');
            $this->efrisMode = NULL;
        } else {
            $this->logger->write("Main Controller : __construct() : The EFRIS Mode is set to: " . $efrisModeCheck->name, 'r');
            $this->efrisMode = $efrisModeCheck->name;
        } 
        
        $integratedErpCheck = new DB\SQL\Mapper($this->db, 'tblerptypes');
        $integratedErpCheck->load(array('TRIM(code)=?', $this->appsettings['ERPTYPECODE']));
        
        if ($integratedErpCheck->dry()) {
            $this->logger->write("Main Controller : __construct() : The integrated ERP is not set", 'r');
            $this->integratedErp = NULL;
        } else {
            $this->logger->write("Main Controller : __construct() : The integrated ERP is set to: " . $integratedErpCheck->name, 'r');
            $this->integratedErp = $integratedErpCheck->code;
        } 
    }
}

?>