<?php

/**
 * This file is part of the etaxware system
 * The is the user model
 * @date: 07-08-2022
 * @file: users.php
 * @path: ./app/view/users.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class users extends \DB\SQL\Mapper
{

    public function __construct(\DB\SQL $db)
    {
        parent::__construct($db, 'tblusers');
    }

    public function all()
    {
        $this->load();
        return $this->query;
    }

    public function getByUsername($UserName)
    {
        $this->load(array(
            'username=?',
            $UserName
        ));
        
        //return $this->query;
    }

    public function getActiveByUsername($username, $status)
    {
        $this->load(array(
            'username=? AND status=?',
            $username,
            10
        ));
    }

    public function getByID($id)
    {
        $this->load(array(
            'id=?',
            $id
        ));
        //return $this->query;
    }

    public function getActiveByID($id, $status)
    {
        $this->load(array(
            'id=? AND status=?',
            $id,
            $status
        ));
    }

    public function getByApiUserCode($UserCode, $status=1017)
    {
        $this->load(array(
            'upper(username)=? AND status=?',
            strtoupper($UserCode),
            $status
        ));
        //return $this->query;
    }
    
    public function getByErpUserCode($UserCode, $status=1017)
    {
        $this->load(array(
            'upper(erpcode)=? AND status=?',
            strtoupper($UserCode),
            $status
        ));
        //return $this->query;
    }
    
    public function getAllActive($status)
    {
        $this->load(array(
            'status=?',
            $status
        ));
        return $this->query;
    }

    public function isAdmin($username, $status, $role)
    {
        $this->load(array(
            'username=? AND status=? AND role=?',
            $username,
            $status,
            $role
        ));
    }
    
    public function verifyEmail($username, $email)
    {
        $this->load(array(
            'username=? AND email=?',
            $username,
            $email
        ));
    }

    public function isActive($username, $status)
    {
        $this->load(array(
            'username=? AND status=?',
            $username,
            $status
        ));
    }
    
    public function isOnline($username)
    {
        $this->load(array(
            'username=? AND online=?',
            $username,
            0
        ));
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