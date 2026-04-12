<?php
/**
 * @name TaxController
 * @desc This file is part of the re-match system. The is the Tax Controller class
 * @date 08-04-2020
 * @file TaxController.php
 * @path ./app/controller/TaxController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
Class TaxController extends MainController{
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
        $permission = 'VIEWTAXES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
    }
    
    /**
     *	@name list
     *  @desc List taxes
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function list(){
        $operation = NULL; //tblevents
        $permission = 'VIEWTAXES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Tax Controller : list() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Tax Controller : list() : Processing list of taxes started", 'r');
            
            $groupid = trim($this->f3->get('POST.groupid'));//get the group id from the call
            
            $sql = 'SELECT  t.id "ID",
                        t.groupid "Group Id",
                        t.goodid "Good Id",
                        t.taxcategory "Tax Category",
                        t.netamount "Net Amount",
                        t.taxrate "Tax Rate",
                        t.taxamount "Tax Amount",
                        t.grossamount "Gross Amount",
                        t.exciseunit "Excise Unit",
                        t.excisecurrency "Excise Currency",
                        t.taxratename "Tax Rate Name",
                        t.disabled "Disabled",
                        t.inserteddt "Creation Date",
                        t.insertedby "Created By",
                        t.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tbltaxdetails t
                    LEFT JOIN tblusers s ON t.modifiedby = s.id
                    WHERE t.groupid = ' . $groupid . '
                    ORDER BY t.id DESC';
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Tax Controller : list() : The operation to list the taxes was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Tax Controller : list() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
    
    /**
     *	@name listtaxtypes
     *  @desc List tax types
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function listtaxtypes(){
        $operation = NULL; //tblevents
        $permission = 'VIEWTAXTYPES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Tax Controller : listtaxtypes() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Tax Controller : list() : Processing list of tax types started", 'r');
                        
            $sql = 'SELECT  t.id "ID",
                        t.code "Code",
                        t.name "Name",
                        t.registrationdate "Registration Date",
                        t.cancellationdate "Cancellation Date",
                        t.disabled "Disabled",
                        t.inserteddt "Creation Date",
                        t.insertedby "Created By",
                        t.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tbltaxtypes t
                    LEFT JOIN tblusers s ON t.modifiedby = s.id
                    ORDER BY t.id DESC';
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Tax Controller : listtaxtypes() : The operation to list the tax types was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Tax Controller : listtaxtypes() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
}
?>