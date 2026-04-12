<?php

/**
 * This file is part of the etaxware system
 * The is the Notification controller class
 * @date: 08-04-2019
 * @file: NotificationController.php
 * @path: ./app/controller/NotificationController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
class NotificationController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules
    
    /**
     *	@name index
     *  @desc 
     *	@return NULL
     *	@param NULL
     **/
    function index(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        ;
    }
    
    /**
     *	@name listnotifications
     *  @desc list notifications
     *	@return JSON-encoded notification
     *	@param NULL
     **/
    function listnotifications(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Notification Controller : listnotifications() : Processing list of notifications started", 'r');
        $notification = array();
        
        if (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id'))) || trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) {
            $notid = trim($this->f3->get('POST.id')) ? trim($this->f3->get('POST.id')) : trim($this->f3->get('PARAMS[id]'));
            //$this->logger->write("Notification Controller : listnotifications() : The id is " . $notid, 'r');
            $notification = $this->util->getnotifications($notid, $this->f3->get('SESSION.id'), $this->appsettings['DEFAULTNOTIFICATIONSTATUS'], $this->appsettings['USERENTITYTYPE'],  $this->appsettings['INAPPNOTIFICATION']);           
        } else {
            $this->logger->write("Notification Controller : listnotifications() : No id has been set", 'r');
        }
        
        die(json_encode($notification));
    }
    
    /**
     *	@name readnotification
     *  @desc Read notifications
     *	@return NULL
     *	@param NULL
     **/
    function readnotification(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Notification Controller : readnotification() : Read notification started", 'r');
        
        if (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id'))) || trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) {
            $notification = new notifications($this->db);
            $notid = trim($this->f3->get('POST.id')) ? trim($this->f3->get('POST.id')) : trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("Notification Controller : readnotification() : The id is " . $notid, 'r');
            $notification->getById($notid);
            
            $this->f3->set('POST.status', $this->appsettings['NOTIFICATIONREADSTATUS']);
            $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
            $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
            
            $notification->edit($notid);
            $this->logger->write("Notification Controller : readnotification() : The id " . $notid . " has been marked as read", 'r');
        } else {
            $this->logger->write("Notification Controller : readnotification() : No id has been set", 'r');
        }          
    }
}

?>