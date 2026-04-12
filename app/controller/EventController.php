<?php
/**
 * @name EventController
 * @desc This file is part of the etaxware system. The is the Event controller class
 * @date 01-06-2020
 * @file EventController.php
 * @path ./app/controller/EventController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
Class EventController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules
    /**
     *	@name listevents
     *  @desc List notifications
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function listevents(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Event Controller : listevents() : Processing list of events started", 'r');
        $sql = '';
        
        $data = array();
        if (trim($this->f3->get('POST.module')) !== '' || !empty(trim($this->f3->get('POST.module')))) {
            $module = trim($this->f3->get('POST.module'));
            $submodule = trim($this->f3->get('POST.submodule'));
            $operation = trim($this->f3->get('POST.operation'));
            $eventid = trim($this->f3->get('POST.event'));
            
            $this->logger->write("Event Controller : listevents() : The module code is: " . $module, 'r');
            $this->logger->write("Event Controller : listevents() : The submodule code is: " . $submodule, 'r');
            $this->logger->write("Event Controller : listevents() : The operation code is: " . $operation, 'r');
            $this->logger->write("Event Controller : listevents() : The event id is: " . $eventid, 'r');
            
            if (trim($this->f3->get('POST.module')) !== '' || !empty(trim($this->f3->get('POST.module')))) {
                $module = '"' . $module . '"';
            } else {
                $module = 'NULL';
            }
            
            if (trim($this->f3->get('POST.submodule')) !== '' || !empty(trim($this->f3->get('POST.submodule')))) {
                $submodule = '"' . $submodule . '"';
            } else {
                $submodule = 'NULL';
            }
            
            if (trim($this->f3->get('POST.operation')) !== '' || !empty(trim($this->f3->get('POST.operation')))) {
                $operation = '"' . $operation . '"';
            } else {
                $operation = 'NULL';
            }
            
            if (trim($this->f3->get('POST.event')) !== '' || !empty(trim($this->f3->get('POST.event')))) {
                $eventid = '"' . $eventid . '"';
            } else {
                $eventid = 'NULL';
            }
            
            $subsql = "CONCAT(l.id, ' - ', l.name)";
            
            $sql = 'SELECT  l.id "Id",
                    ' . $subsql . ' "Name", l.disabled "Disabled"
                    FROM tblevents l
                    LEFT JOIN tbloperations o ON o.id = l.operation
                    LEFT JOIN tblsubmodules sm ON sm.id = l.submodule
                    LEFT JOIN tblmodules m ON m.id = l.module
                    WHERE l.code = COALESCE(' . $eventid . ', l.code)
                    AND o.code = COALESCE(' . $operation . ', l.operation)
                    AND sm.code = COALESCE(' . $submodule . ', l.submodule)
                    AND m.code = COALESCE(' . $module . ', l.module)
                    ORDER By l.id DESC';
            
            //$this->logger->write("Event Controller : listevents() : The sql is: " . $sql, 'r');
            
            try {
                $dtls = $this->db->exec($sql);
                $this->logger->write($this->db->log(TRUE), 'r');
                
                foreach ( $dtls as $obj ) {
                    $data [] = $obj;
                }
            } catch(Exception $e) {
                $this->logger->write("Event Controller : listevents() : The operation to retrive a list of events was not successful. The error messages is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Event Controller : listevents() : No module was specified", 'r');
        }
        
        die(json_encode($data));
	}
	
	/**
	 *	@name listeventnotificationtypes
	 *  @desc List notification types attached to an event
	 *	@return JSON-encoded object
	 *	@param NULL
	 **/
	function listeventnotificationtypes(){
	    $operation = NULL; //tblevents
	    $permission = NULL; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    $this->logger->write("Event Controller : listeventnotificationtypes() : Processing list listnotification types of estarted", 'r');
	    $sql = '';
	    
	    $data = array();
	    
	    $eventid = trim($this->f3->get('POST.event'));
	    $this->logger->write("Event Controller : listeventnotificationtypes() : The event id is: " . $eventid, 'r');
	    
	    if (trim($this->f3->get('POST.event')) == '' || empty(trim($this->f3->get('POST.event')))) {
	        $eventid = 'NULL';
	    }
	    
	    $subsql = "CONCAT(l.notificationtype, ' - ', t.name)";
	    
	    $sql = 'SELECT  l.notificationtype "Id2",
                        ' . $subsql . ' "Name2",  l.disabled "Disabled"
                FROM tbleventnotifications l
                LEFT JOIN tblnotificationtypes t ON t.id = l.notificationtype
                WHERE l.event = COALESCE(' . $eventid . ', l.event)
                ORDER By l.id DESC';
	    
	    //$this->logger->write("Event Controller : listeventnotificationtypes() : The sql is: " . $sql, 'r');
	    
	    try {
	        $dtls = $this->db->exec($sql);
	        $this->logger->write($this->db->log(TRUE), 'r');
	        
	        foreach ( $dtls as $obj ) {
	            $data [] = $obj;
	        }
	    } catch(Exception $e) {
	        $this->logger->write("Event Controller : listeventnotificationtypes() : The operation to retrive a list of listnotification types was not successful. The error messages is " . $e->getMessage(), 'r');
	    }
	    
	    die(json_encode($data));
	}
	
	/**
	 *	@name listeventnotifications
	 *  @desc List event notification attached to an event
	 *	@return JSON-encoded object
	 *	@param NULL
	 **/
	function listeventnotifications(){
	    $operation = NULL; //tblevents
	    $permission = NULL; //tblpermissions
	    $event = NULL; //tblevents
	    $eventnotification = NULL; //tbleventnotifications
	    
	    $this->logger->write("Event Controller : listeventnotifications() : Processing list event listnotification of estarted", 'r');
	    $sql = '';
	    
	    $data = array();
	    
	    $eventid = trim($this->f3->get('POST.event'));
	    $type = trim($this->f3->get('POST.type'));
	    
	    $this->logger->write("Event Controller : listeventnotifications() : The event id is: " . $eventid, 'r');
	    
	    if (trim($this->f3->get('POST.event')) == '' || empty(trim($this->f3->get('POST.event')))) {
	        $eventid = 'NULL';
	    }
	    
	    if (trim($this->f3->get('POST.type')) == '' || empty(trim($this->f3->get('POST.type')))) {
	        $type = 'NULL';
	    }
	    
	    $subsql = "CONCAT(l.id, ' - ', l.name)";
	    
	    $sql = 'SELECT  l.id "Id3",
                        ' . $subsql . ' "Name3",
                        l.id "ID",
                        nt.name "Notification Type",
                        nst.name "Notification Subtype",
                        e.name "Event",
                        tpl.name "Template",
                        l.code "Code",
                        l.name "Name",
                        l.description "Description",
                        l.status "Status",
                        l.disabled "Disabled",
                        l.modifieddt "Modified Date",
                        u.username "Modified By"
                FROM tbleventnotifications l
                LEFT JOIN tblevents e ON l.event = e.id
                LEFT JOIN tblnotificationtypes nt ON l.notificationtype = nt.id
                LEFT JOIN tblnotificationsubtypes nst ON l.notificationsubtype = nst.id
                LEFT JOIN tbltemplates tpl ON l.templateid = tpl.id
                LEFT JOIN tblusers u ON l.modifiedby = u.id
                WHERE l.event = COALESCE(' . $eventid . ', l.event)
                AND l.notificationtype = COALESCE(' . $type . ', l.notificationtype)
                ORDER By l.id DESC';
	    
	    //$this->logger->write("Event Controller : listeventnotifications() : The sql is: " . $sql, 'r');
	    
	    try {
	        $dtls = $this->db->exec($sql);
	        $this->logger->write($this->db->log(TRUE), 'r');
	        
	        foreach ( $dtls as $obj ) {
	            $data [] = $obj;
	        }
	    } catch(Exception $e) {
	        $this->logger->write("Event Controller : listeventnotifications() : The operation to retrive a list of event notifications was not successful. The error messages is " . $e->getMessage(), 'r');
	    }
	    
	    die(json_encode($data));
	}
}
?>