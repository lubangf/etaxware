<?php

/**
 * This file is part of the etaxware system
 * The is the dashboard controller class
 * @date: 08-04-2019
 * @file: DashboardController.php
 * @path: ./app/controller/DashboardController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
class DashboardController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules

    function index(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $sql = '';
        $data = array ();
        
        if ($this->f3->get('SESSION.userrole') == $this->appsettings['SUPERADMINROLEID']){
            $sql = 'SELECT 1 "kpi", count(*) "value" FROM rematch.tblmatchrunschedules WHERE runningflag = "Y"
                    UNION 
                    SELECT 2 "kpi", count(*) "value" FROM tblmatchrunschedulehistory
                    UNION
                    SELECT 3 "kpi", count(*) "value" FROM rematch.tblmatchrunschedules WHERE nextdate IS NOT NULL AND (enddate IS NULL OR enddate >= NOW())
                    UNION
                    SELECT 4 "kpi", count(*) "value" FROM rematch.tblmatchrunschedules WHERE status = "Y"';
        }else {
            $sql = 'SELECT 1 "kpi", count(*) "value" FROM rematch.tblmatchrunschedules WHERE runningflag = "Y"
                    UNION
                    SELECT 2 "kpi", count(*) "value" FROM tblmatchrunschedulehistory
                    UNION
                    SELECT 3 "kpi", count(*) "value" FROM rematch.tblmatchrunschedules WHERE nextdate IS NOT NULL AND (enddate IS NULL OR enddate >= NOW())
                    UNION
                    SELECT 4 "kpi", count(*) "value" FROM rematch.tblmatchrunschedules WHERE status = "Y"';
        }
        
        try {
            $d = $this->db->exec($sql);
            //$this->logger->write($this->db->log(TRUE), 'r');
            foreach ($d as $obj) {
                $data[] = $obj;
            }
        } catch(Exception $e) {
            $this->logger->write("Dashboard Controller : list() : The operation to calculate the dashboard KPIs was not successful. The error messages is " . $e->getMessage(), 'r');
        }
        
        $this->f3->set('kpis', $data);
        
        $this->f3->set('pagetitle','Dashboard');
        $this->f3->set('pagecontent','Dashboard.htm');
        $this->f3->set('pagescripts','DashboardFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
}

?>