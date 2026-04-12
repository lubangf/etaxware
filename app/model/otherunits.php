<?php

/**
 * This file is part of the etaxware system
 * The is the otherunits model
 * @date: 21-05-2021
 * @file: otherunits.php
 * @path: ./app/view/otherunits.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class otherunits extends \DB\SQL\Mapper
{

    public function __construct(\DB\SQL $db)
    {
        parent::__construct($db, 'tblotherunits');
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
    
    public function getByProduct($product)
    {
        $this->load(array(
            'productid=?',
            $product
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