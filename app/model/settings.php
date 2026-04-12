<?php

/**
 * This file is part of the etaxware system
 * The is the settings model
 * @date: 08-04-2019
 * @file: settings.php
 * @path: ./app/view/settings.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */

class settings extends \DB\SQL\Mapper
{

    public function __construct(\DB\SQL $db)
    {
        parent::__construct($db, 'tblsettings');
    }

    public function all()
    {
        $this->load();
        return $this->query;
    }
    
    public function getNoneSensitive()
    {
        $this->load(array(
            'sensitivityflag=?',
            0
        ));
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
    
    public function getByGroupCode($code)
    {
        $this->load(array(
            'groupcode=?',
            $code
        ));
        return $this->query;
    }
    
    public function getByGroupId($id)
    {
        $this->load(array(
            'groupid=?',
            $id
        ));
        return $this->query;
    }

    public function add()
    {
        $this->copyFrom('POST');
        $this->save();
    }

    public function edit($code)
    {
        $this->load(array(
            'code=?',
            $code
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