<?php
/**
 * @name GoodController
 * @desc This file is part of the etaxware system. The is the Good Controller class
 * @date 08-04-2020
 * @file GoodController.php
 * @path ./app/controller/GoodController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
Class GoodController extends MainController{
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
        $permission = 'VIEWGOODS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
    }
    
    /**
     *	@name list
     *  @desc List goods
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function list(){
        $operation = NULL; //tblevents
        $permission = 'VIEWGOODS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Good Controller : list() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Good Controller : list() : Processing list of goods started", 'r');
            
            $groupid = trim($this->f3->get('POST.groupid'));//get the group id from the call
            
            $sql = 'SELECT  g.id "ID",
                        g.groupid "Group Id",   
                        g.item "Item",
                        g.itemcode "Item Code",
                        g.qty "Qty",
                        g.unitofmeasure "Unit Of Measure",
                        g.unitprice "Unit Price",
                        g.total "Total",
                        g.taxid "Tax Id",
                        g.taxrate "Tax Rate",
                        g.tax "Tax",
                        g.discounttotal "Discount Total",
                        g.discounttaxrate "Discount Tax Rate",
                        g.discountpercentage "Discount Percentage",
                        g.ordernumber "Order Number",
                        g.discountflag "Discount Flag",
                        g.deemedflag "Deemed Flag",
                        g.exciseflag "Excise Flag",
                        g.categoryid "Category Id",
                        g.categoryname "Category Name",
                        g.goodscategoryid "Goods Category Id",
                        g.goodscategoryname "Goods Category Name",
                        g.exciserate "Excise Rate",
                        g.exciserule "Excise Rule",
                        g.excisetax "Excise Tax",
                        g.pack "Pack",
                        g.stick "Stick",
                        g.exciseunit "Excise Unit",
                        g.excisecurrency "Excise Currency",
                        g.exciseratename "Excise Rate Name",
                        g.totalWeight "Total Weight",
                        g.disabled "Disabled",
                        g.inserteddt "Creation Date",
                        g.insertedby "Created By",
                        g.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblgooddetails g
                    LEFT JOIN tblusers s ON g.modifiedby = s.id
                    WHERE g.groupid = ' . $groupid . '
                    ORDER By g.id DESC';
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
                $this->logger->write("Good Controller : list() : The operation to list the goods was successful.", 'r');
            } catch (Exception $e) {
                $this->logger->write("Good Controller : list() : The operation to list the goods was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Good Controller : list() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }

    /**
     *	@name searchgoods
     *  @desc List all available products
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function searchgoods(){
        $operation = NULL; //tblevents
        $permission = 'VIEWGOODS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Good Controller : searchgoods() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Good Controller : searchgoods() : Processing search of products started", 'r');
            $name = trim($this->f3->get('POST.name'));
            
            if ($name !== '' || !empty($name)) {
                
                $subquery = " '%" . $name . "%' ";
                
                $sql = 'SELECT  r.id "ID",
                        r.code "Code",
                        r.name "Name",
                        r.description "Description",
                        r.disabled "Disabled"
                    FROM tblproductdetails r
                    WHERE r.name LIKE ' . $subquery . '
                    ORDER BY r.id DESC';
            } else {
                $sql = 'SELECT  r.id "ID",
                        r.code "Code",
                        r.name "Name",
                        r.description "Description",
                        r.disabled "Disabled"
                    FROM tblproductdetails r
                    ORDER BY r.id DESC';
            }
            
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Good Controller : searchgoods() : The operation to search products was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Good Controller : searchgoods() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
}
?>