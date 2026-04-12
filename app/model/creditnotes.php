<?php

/**
 * This file is part of the etaxware system
 * The is the credit/debit notes model
 * @date: 27-09-2020
 * @file: creditnotes.php
 * @path: ./app/view/creditnotes.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class creditnotes extends \DB\SQL\Mapper
{

    public function __construct(\DB\SQL $db)
    {
        parent::__construct($db, 'tblcreditnotes');
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
    
    public function getByCode($code)
    {
        $this->load(array(
            'code=?',
            $code
        ));
        return $this->query;
    } 

    public function getByInvoiceID($id)
    {
        $this->load(array(
            'creditnoteid=?',
            $id
        ));
        return $this->query;
    }
    
    public function getByInvoiceNo($no)
    {
        $this->load(array(
            'creditnoteno=?',
            $no
        ));
        return $this->query;
    }
    
    public function getByRefNo($no)
    {
        $this->load(array(
            'TRIM(referenceno)=?',
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