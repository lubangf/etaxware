<?php

/**
 * This file is part of the etaxware system
 * The is the access controller class
 * @date: 13-06-2020
 * @file: AccessController.php
 * @path: ./app/controller/AccessController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class AccessController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules
    
    /**
     *	@name forbidden
     *  @desc Loads the forbidden page
     *	@return NULL
     *	@param NULL
     **/
    function forbidden(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
               
        echo \Template::instance()->render('Forbidden.htm');
    }
}

?>