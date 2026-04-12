<?php
/**
 * @name TaxPayerController
 * @desc This file is part of the etaxware system. The is the TaxPayer controller class
 * @date 11-05-2020
 * @file TaxPayerController.php
 * @path ./app/controller/TaxPayerController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
Class TaxPayerController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules
    
    
    /**
     *	@name querytaxpayer
     *  @desc query a taxpayer from EFRIS
     *	@return
     *	@param
     **/
    function querytaxpayer(){
        $operation = NULL; //tblevents
        $permission = 'QUERYTAXPAYER'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $tin = $this->f3->get('POST.tin');
        
        $this->logger->write("TaxPayer Controller : querytaxpayer() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            $data = $this->util->querytaxpayer($this->f3->get('SESSION.id'), $tin);//will return JSON.
            //var_dump($data);
            $data = json_decode($data, true);
            
            $response = array(
                'tin' => '',
                'ninBrn' => '',
                'legalName' => '',
                'businessName' => '',
                'contactNumber' => '',
                'contactEmail' => '',
                'address' => ''
            );
            
            if (isset($data['taxpayer'])){               
                $response['tin'] = $data['taxpayer']['tin'];
                $response['ninBrn'] = $data['taxpayer']['ninBrn'];
                $response['legalName'] = $data['taxpayer']['legalName'];
                $response['businessName'] = $data['taxpayer']['businessName'];
                $response['contactNumber'] = $data['taxpayer']['contactNumber'];
                $response['contactEmail'] = $data['taxpayer']['contactEmail'];
                $response['address'] = $data['taxpayer']['address'];
            } elseif (isset($data['returnCode'])){
                $this->logger->write("TaxPayer Controller : querytaxpayer() : The operation to query the taxpayer not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to query the taxpayer by " . $this->f3->get('SESSION.username') . " was not successful");
            } else {
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to query the taxpayer by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to query the taxpayer by " . $this->f3->get('SESSION.username') . " was not successful";
            }
        } else {
            $this->logger->write("TaxPayer Controller : querytaxpayer() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($response));
    }
}
?>