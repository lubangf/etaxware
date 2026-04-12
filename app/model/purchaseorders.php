<?php

/**
 * This file is part of the etaxware system
 * The is the purchase orders model
 * @date: 08-04-2019
 * @file: purchaseorders.php
 * @path: ./app/view/purchaseorders.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class purchaseorders extends \DB\SQL\Mapper
{

    public function __construct(\DB\SQL $db)
    {
        parent::__construct($db, 'tblpurchaseorders');
    }

    public function all()
    {
        $this->load();
        return $this->query;
    }

    public function getByID($id)
    {
        $this->load(array(
            'id=?',
            $id
        ));
        return $this->query;
    } 

    public function getByErpId($id)
    {
        $this->load(array(
            'erpvoucherid=?',
            $id
        ));
        return $this->query;
    } 
    
    public function getByErpNo($no)
    {
        $this->load(array(
            'erpvoucherno=?',
            $no
        ));
        return $this->query;
    } 

    public function add()
    {
        $this->copyFrom('POST');
        $this->save();
    }

    public function edit($id)
    {
        $this->load(array(
            'id=?',
            $id
        ));
        $this->copyFrom('POST');
        $this->update();
    }

    public function delete($id)
    {
        $this->load(array(
            'id=?',
            $id
        ));
        $this->erase();
    }
}

?>