<?php

/**
 * This file is part of the etaxware system
 * The is the enforce tax exclusionlist list model
 * @date: 23-05-2025
 * @file: enforcetaxexclusionlist.php
 * @path: ./app/view/enforcetaxexclusionlist.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
class enforcetaxexclusionlist extends \DB\SQL\Mapper
{

    public function __construct(\DB\SQL $db)
    {
        parent::__construct($db, 'tblenforcetaxexclusionlist');
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
    
    public function getByGroup($code)
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