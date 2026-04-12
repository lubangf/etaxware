<?php
/**
 * @name PaymentController
 * @desc This file is part of the re-match system. The is the Payment Controller class
 * @date 08-04-2020
 * @file PaymentController.php
 * @path ./app/controller/PaymentController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
Class PaymentController extends MainController{
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
        $permission = 'VIEWPAYMENTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
    }
    
    /**
     *	@name list
     *  @desc List payments
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function list(){
        $operation = NULL; //tblevents
        $permission = 'VIEWPAYMENTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Payment Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Payment Controller : list() : Processing list of payments started", 'r');
            
            $groupid = trim($this->f3->get('POST.groupid'));//get the group id from the call
            $this->logger->write("Payment Controller : list() : The group is is: " . $groupid, 'r');
            
            $sql = 'SELECT  p.id "ID",
                        p.groupid "Group Id",
                        p.paymentmode "Payment Mode",
                        pm.name "Payment Mode Name",
                        p.paymentamount "Payment Amount",
                        p.ordernumber "Order Number",
                        p.disabled "Disabled",
                        p.inserteddt "Creation Date",
                        p.insertedby "Created By",
                        p.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblpaymentdetails p
                    LEFT JOIN tblpaymentmodes pm on pm.code = p.paymentmode
                    LEFT JOIN tblusers s ON p.modifiedby = s.id
                    WHERE p.groupid = ' . $groupid . '
                    ORDER BY p.id DESC';
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Payment Controller : list() : The operation to list the payments was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Payment Controller : index() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
}
?>