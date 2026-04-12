<?php

/**
 * @name CommoditycodeController
 * @desc This file is part of the etaxware system. The is the Commodity code controller class
 * @date 16-12-2022
 * @file CommoditycodeController.php
 * @path ./app/controller/CommoditycodeController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
class CommoditycodeController extends MainController{

    protected static $module = NULL;// tblmodules
    protected static $submodule = NULL;// tblsubmodules

    /**
     * Loads the index page
     *
     * @name index
     * @return NULL
     * @param
     *            NULL
     */
    function index(){
        $operation = NULL; // tblevents
        $permission = 'VIEWCOMMODITYCODES'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications

        $this->logger->write("Commoditycode Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $this->f3->set('pagetitle', 'Commodity Codes');
            $this->f3->set('pagecontent', 'Commoditycode.htm');
            $this->f3->set('pagescripts', 'CommoditycodeFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Commoditycode Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     * Get commodity category code
     *
     * @name getcommoditycode
     * @return NULL
     * @param
     *            NULL
     */
    function getcommoditycode(){
        $operation = NULL; // tblevents
        $permission = 'VIEWCOMMODITYCODES'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications
        
        $this->logger->write("Commoditycode Controller : getcommoditycode() : Checking permissions", 'r');
        
        $data = array();
        
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Commoditycode Controller : getcommoditycode() : Processing search of commodity codes started", 'r');
            $code = trim($this->f3->get('POST.code'));
            
            $this->logger->write("Commoditycode Controller : getcommoditycode() : The code is: " . $code, 'r');
            
            if ($code !== '' || !empty($code)) {
                
                $sql = 'SELECT  c.id,
                        c.commoditycode,
                        c.commodityname,
                        c.parentCode,
                        c.rate,
                        c.isLeafNode,
                        s.name serviceMark,
                        z.name isZeroRate,
                        e.name isExempt,
                        es.name enableStatusCode,
                        ex.name exclusion,
                        c.disabled
                    FROM tblcommoditycategories c
                    LEFT JOIN tblchoices s ON s.code = c.serviceMark
                    LEFT JOIN tblchoices z ON z.code = c.isZeroRate
                    LEFT JOIN tblchoices e ON e.code = c.isExempt
                    LEFT JOIN tblchoices es ON es.code = c.enableStatusCode
                    LEFT JOIN tblproductexclusioncodes ex ON ex.code = c.exclusion
                    WHERE c.commoditycode = "' . $code . '"';
            } else {
                $this->logger->write("Commoditycode Controller : getcommoditycode() : The code was not submitted", 'r');
            }
            
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Commoditycode Controller : getcommoditycode() : The operation to search commodity codes was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Commoditycode Controller : getcommoditycode() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
    
    /**
     * Fetch commodity codes from URA
     *
     * @name fetchcommoditycode
     * @return NULL
     * @param
     *            NULL
     */
    function fetchcommoditycode(){
        $operation = NULL; // tblevents
        $permission = 'VIEWCOMMODITYCODES'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Commoditycode Controller : fetchcommoditycode() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            $pageNo = 1;
            $pageSize = 90;
            $pageCount = 1;
            
            do {
                $this->logger->write("Commoditycode Controller : fetchcommoditycode() : pageNo = " . $pageNo, 'r');
                $this->logger->write("Commoditycode Controller : fetchcommoditycode() : pageCount = " . $pageCount, 'r');
                
                $data = $this->util->synccommoditycode($this->f3->get('SESSION.id'), $pageNo, $pageSize);//will return JSON.
                //var_dump($data);
                $data = json_decode($data, true);
                
                if(isset($data['page'])){
                    $pageCount = $data['page']['pageCount'];
                    
                    if ($pageNo < $pageCount) {
                        $pageNo = $pageNo + 1;
                    }
                }
                
                
                if(isset($data['records'])){
                    
                    if ($data['records']) {
                        foreach($data['records'] as $elem){
                            //$this->logger->write("Product Controller : syncproductsrates() : The products are " . $elem['goodsCode']. ", ".$elem['nowTime']. ", ".$elem['timeFormat']. ", ".$elem['id'], 'r');
                            
                            try{
                                $commoditycategory = new commoditycategories($this->db);
                                
                                /*
                                 {
                            		"commodityCategoryCode": "10151810",
                            		"commodityCategoryLevel": "4",
                            		"commodityCategoryName": "Mustard seeds or seedlings",
                            		"enableStatusCode": "1",
                            		"exclusion": "2",
                            		"isExempt": "102",
                            		"isLeafNode": "101",
                            		"isZeroRate": "101",
                            		"nowTime": "2022/12/16 18:14:32",
                            		"pageIndex": 0,
                            		"pageNo": 0,
                            		"pageSize": 0,
                            		"parentCode": "10151800",
                            		"rate": "0.18",
                            		"serviceMark": "102",
                            		"zeroRateStartDate": "01/07/2019"
                            	}
                                 */
                                
                                
                                
                                $commodityCategoryCode = empty($elem['commodityCategoryCode'])? 'NULL' : $elem['commodityCategoryCode'];
                                $commodityCategoryLevel = empty($elem['commodityCategoryLevel'])? 'NULL' : $elem['commodityCategoryLevel'];
                                $commodityCategoryName = empty($elem['commodityCategoryName'])? 'NULL' : $elem['commodityCategoryName'];
                                $enableStatusCode = empty($elem['enableStatusCode'])? 'NULL' : $elem['enableStatusCode'];
                                $exclusion = empty($elem['exclusion'])? 'NULL' : $elem['exclusion'];
                                $isExempt = empty($elem['isExempt'])? 'NULL' : $elem['isExempt'];
                                $isLeafNode = empty($elem['isLeafNode'])? 'NULL' : $elem['isLeafNode'];
                                $isZeroRate = empty($elem['isZeroRate'])? 'NULL' : $elem['isZeroRate'];
                                $parentCode = empty($elem['parentCode'])? 'NULL' : $elem['parentCode'];
                                $rate = empty($elem['rate'])? 'NULL' : $elem['rate'];
                                $serviceMark = empty($elem['serviceMark'])? 'NULL' : $elem['serviceMark'];
                                
                                if (!empty($elem['zeroRateStartDate'])) {
                                    $edate = new DateTime($elem['zeroRateStartDate']);
                                    $zeroRateStartDate = $edate->format('Y-m-d');
                                } else {
                                    $zeroRateStartDate = date('Y-m-d');
                                }
                                
                                if (!empty($elem['zeroRateEndDate'])) {
                                    $edate = new DateTime($elem['zeroRateEndDate']);
                                    $zeroRateEndDate = $edate->format('Y-m-d');
                                } else {
                                    $zeroRateEndDate = date('Y-m-d');
                                }
                                
                                if (!empty($elem['exemptRateStartDate'])) {
                                    $edate = new DateTime($elem['exemptRateStartDate']);
                                    $exemptRateStartDate = $edate->format('Y-m-d');
                                } else {
                                    $exemptRateStartDate = date('Y-m-d');
                                }
                                
                                if (!empty($elem['exemptRateEndDate'])) {
                                    $edate = new DateTime($elem['exemptRateEndDate']);
                                    $exemptRateEndDate = $edate->format('Y-m-d');
                                } else {
                                    $exemptRateEndDate = date('Y-m-d');
                                }
                                
                                $commoditycategory->getByCode($commodityCategoryCode);
                                
                                if ($commoditycategory->dry()) {
                                    $this->logger->write("Commoditycode Controller : fetchcommoditycode() : The commodity code does not exist", 'r');
                                    
                                    $sql = 'INSERT INTO tblcommoditycategories(
                                                commoditycode,
                                                commodityname,
                                                rate,
                                                isLeafNode,
                                                serviceMark,
                                                isZeroRate,
                                                zeroRateStartDate,
                                                zeroRateEndDate,
                                                isExempt,
                                                exemptRateStartDate,
                                                exemptRateEndDate,
                                                enableStatusCode,
                                                exclusion,
                                                parentCode,
                                                commodityCategoryLevel,
                                                inserteddt,
                                                insertedby,
                                                modifieddt,
                                                modifiedby)
                                                VALUES ("'
                                        . addslashes($commodityCategoryCode) . '", "'
                                            . addslashes($commodityCategoryName) . '", '
                                                . $rate. ', '
                                                    . $isLeafNode . ', '
                                                        . $serviceMark . ', '
                                                            . $isZeroRate . ', "'
                                                                . $zeroRateStartDate . '", "'
                                                                    . $zeroRateEndDate . '", '
                                                                        . $isExempt . ', "'
                                                                            . $exemptRateStartDate . '", "'
                                                                                . $exemptRateEndDate . '", '
                                                                                    . $enableStatusCode . ', '
                                                                                        . $exclusion . ', "'
                                                                                            . addslashes($parentCode) . '", '
                                                                                                . $commodityCategoryLevel . ', "'
                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                        . $this->f3->get('SESSION.id') . ', "'
                                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                                . $this->f3->get('SESSION.id') . ')';
                                  
                                                                                                                            
                                    $this->logger->write("Commoditycode Controller : fetchcommoditycode() : The SQL to create the commodity code is " . $sql, 'r');
                                    $this->db->exec(array($sql));
                                } else {
                                    $this->logger->write("Commoditycode Controller : fetchcommoditycode() : The commodity code exists", 'r');
                                    
                                    $this->db->exec(array('UPDATE tblcommoditycategories SET rate = ' . $rate .
                                        ', isLeafNode = ' . $isLeafNode .
                                        ', serviceMark = ' . $serviceMark .
                                        ', exclusion = ' . $exclusion .
                                        ', isZeroRate = ' . $isZeroRate .
                                        ', zeroRateStartDate = "' . $zeroRateStartDate .
                                        '", zeroRateEndDate = "' . $zeroRateEndDate .
                                        '", isExempt = ' . $isExempt .
                                        ', exemptRateStartDate = "' . $exemptRateStartDate .
                                        '", exemptRateEndDate = "' . $exemptRateEndDate .
                                        '", enableStatusCode = ' . $enableStatusCode .
                                        ', parentCode = "' . addslashes($parentCode) .
                                        '", commodityCategoryLevel = ' . $commodityCategoryLevel .
                                        ', modifieddt = "' .  date('Y-m-d H:i:s') .
                                        '", modifiedby = ' . $this->f3->get('SESSION.id') .
                                        ' WHERE TRIM(commodityCode) = "' . addslashes($commodityCategoryCode) . '"'));
                                    
                                }
                                
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Commodity Code - " . $elem['commodityCategoryCode'] . " - was synced successfully by " . $this->f3->get('SESSION.username'));
                                self::$systemalert = "The commodity codes were sync'd & attributes updated successfully by " . $this->f3->get('SESSION.username');
                            } catch (Exception $e) {
                                $this->logger->write("Commoditycode Controller : fetchcommoditycode() : The operation to sync the commodity codes was not successful. The error message is " . $e->getMessage(), 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync the commodity code by " . $this->f3->get('SESSION.username') . " was not successful");
                                self::$systemalert = "The operation to sync the products by " . $this->f3->get('SESSION.username') . " was not successful";
                            }
                        }
                    } else {
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync the product by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the product first");
                        self::$systemalert = "The operation to sync the products by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the product first";
                    }
                    
                    
                } elseif (isset($data['returnCode'])){
                    $this->logger->write("Commoditycode Controller : fetchcommoditycode() : The operation to sync the product not successful. The error message is " . $data['returnMessage'], 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync the product by " . $this->f3->get('SESSION.username') . " was not successful");
                    self::$systemalert = "The operation to sync the products by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                } else {
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync the product by " . $this->f3->get('SESSION.username') . " was not successful");
                    self::$systemalert = "The operation to sync the products by " . $this->f3->get('SESSION.username') . " was not successful";
                }
            } while ($pageNo < $pageCount);
        } else {
            $this->logger->write("Commoditycode Controller : fetchcommoditycode() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
    }
}

?>