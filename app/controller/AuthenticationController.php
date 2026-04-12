<?php
/**
 * This file is part of the etaxware system
 * The is the authentication controller class
 * @date: 14-08-2022
 * @file: AuthenticationController.php
 * @path: ./app/controller/AuthenticationController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class AuthenticationController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules

    protected static $feedback; //feedback on login screen

    /**
     * implement beforeroute() function here to overide the one in the maincontroller.
     * This ensures that the login page displays whether there is a session or not
     *
     * @return NULL
     * @param NULL
     *            
     */
    function beforeroute(){
        // code goes here
    }

    /**
     * Invoke after session
     *
     * @return NULL
     * @param NULL
     *            
     */
    function afteroute(){
        // code goes here
    }
    
    /**
     *	@name logout
     *  @desc Clear current user's session
     *	@return NULL
     *	@param NULL
     **/
    function logout(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        //Take user offline
        try {
            $this->db->exec(array('UPDATE tblusers SET online = 0 WHERE id = ' . $this->f3->get('SESSION.id')));
        } catch (Exception $e) {
            $this->logger->write("Authentication Controller : logout() : Failed to update the table tblusers. The error message is " . $e->getMessage(), 'r');
        }
        
        $this->f3->clear('SESSION');
        $this->f3->clear('CACHE'); //clear the whole cache content
        
        $this->f3->reroute('/');       
    }

    /**
     * @name login
     * @desc Render the login page
     *
     * @return NULL
     * @param NULL
     *            
     */
    function login(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->f3->set('pagetitle','Login');
        $this->f3->set('feedback', self::$feedback);
        echo \Template::instance()->render('Login.htm');
    }

    /**
     * autheticate method will be executed by the form called 'signin-form' in the Login.htm page
     *
     * @return NULL
     * @param NULL
     *            
     */
    function authenticate() {
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Authentication Controller : authenticate() : Authentication started", 'r');
        // record username POST'ed by the html form
        $username = $this->f3->get('POST.username');
        // record password POST'ed by the html form
        $password = $this->f3->get('POST.password');
        // create a new instance of the user model and pass the db object
        $user = new users($this->db);
        // pass the username to the getByUsername method
        $user->getByUsername($username);
        $this->logger->write($this->db->log(TRUE), 'r');
        // the dry method checks if the mapper is empty
        if ($user->dry()) {
            // if the mapper is empty, reroute to the login page
            if (!empty($username)) {
                $this->logger->write("Authentication Controller : authenticate() : The User " . $username . " does not exist", 'r');
                // add some visual feedback to user
                self::$feedback = "The user " . $username . " does not exist";
            }
            // re-render page to display feedback
            $this->login();
            // should we exit script here?
            exit();
        }

        $user->isActive($username, $this->appsettings['ACTIVEUSERSTATUSID']);
        $this->logger->write($this->db->log(TRUE), 'r');

        if ($user->dry()) {
            $this->logger->write("Authentication Controller : authenticate() : The User " . $username . " is not active", 'r');
            self::$feedback = "The User " . $username . " is not active";
            $this->login();
            exit();
        }
        
        $user->isOnline($username);
        $this->logger->write($this->db->log(TRUE), 'r');
        
        if ($user->dry()) {
            $this->logger->write("Authentication Controller : authenticate() : The User " . $username . " is already logged in", 'r');
            self::$feedback = "The user " . $username . " is already logged in. Please use the reset option.";
            $this->login();
            exit();
        }

        if (password_verify($password, $user->password)) {
            // assign user's username to the session variable user
            $this->f3->set('SESSION.username', $user->username);
            $this->f3->set('SESSION.id', $user->id);
            $this->f3->set('SESSION.role', $user->role);
            $this->f3->set('SESSION.lastActivityDate', date('Y-m-d H:i:s'));
            $this->logger->write("Authentication Controller : authenticate() : A session has been created for user " . $user->username, 'r');
            // route user to the application after successful login
            $this->logger->write("Authentication Controller : authenticate() : Login was successful", 'r');
            
            $this->logger->write("Authentication Controller : authenticate() : The variable SESSION.username has been set to: " . $this->f3->get('SESSION.username'), 'r');
            $this->util->createauditlog($this->f3->get('SESSION.id'), "Login was successful");
            $sql = "UPDATE tblusers SET online = 1, lastlogindt = '" . date('Y-m-d H:i:s') . "' WHERE username = '" . $user->username . "'";

            $this->db->exec(array(
                $sql
            ));

            $this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->reroute('/');
            exit();
        } else {
            // wrong password
            $this->logger->write("Authentication Controller : authenticate() : The Password " . $password . " for User " . $username . " is wrong", 'r');
            self::$feedback = "Wrong Password. Please try again!";
            $this->login();
            exit();
        }
    }
    
    /**
     *	@name resetaccount
     *  @desc Reset a users account. This is useful if the user is marked as online in the database, but has lost the session on the UI
     *	@return NULL
     *	@param NULL
     **/
    function resetaccount(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Authentication Controller : resetaccount() : Resetting the user started", 'r');
        // record username POST'ed by the html form
        $username = $this->f3->get('POST.resetusername');
        // record password POST'ed by the html form
        $password = $this->f3->get('POST.resetpassword');
        
        $user = new users($this->db);
        // pass the username to the getByUsername method
        $user->getByUsername($username);
        $this->logger->write($this->db->log(TRUE), 'r');
        // the dry method checks if the mapper is empty
        if ($user->dry()) {
            // if the mapper is empty, reroute to the login page
            if (!empty($username)) {
                $this->logger->write("Authentication Controller : resetaccount() : The user " . $username . " does not exist", 'r');
                // add some visual feedback to user
                self::$feedback = "The user " . $username . " does not exist";
            }
            // re-render page to display feedback
            $this->login();
            // should we exit script here?
            exit();
        } else {
            $user->isActive($username, $this->appsettings['ACTIVEUSERSTATUSID']);
            $this->logger->write($this->db->log(TRUE), 'r');
            
            if ($user->dry()) {
                $this->logger->write("Authentication Controller : resetaccount() : The user " . $username . " is not active", 'r');
                self::$feedback = "The user " . $username . " is not active";
                $this->login();
                exit();
            } else {
                if (password_verify($password, $user->password)) {
                    //Take user offline
                    try {
                        $this->db->exec(array('UPDATE tblusers SET online = 0, modifieddt = "' . date('Y-m-d H:i:s') . '", modifiedby = "' . $user->id . '" WHERE id = ' . $user->id));
                        $this->logger->write("Authentication Controller : resetaccount() : Resetting was successful", 'r');
                        //clear any sessions & cache, just in-case
                        $this->f3->clear('SESSION');
                        $this->f3->clear('CACHE');
                        self::$feedback = "The account was reset successfully. Please login!";
                    } catch (Exception $e) {
                        $this->logger->write("Authentication Controller : resetaccount() : Failed to update the table tblusers. The error message is " . $e->getMessage(), 'r');
                        self::$feedback = "Opps. An internal error occurred. Please try again!";
                    }
                    
                    $this->login();
                    exit();
                } else {
                    // wrong password
                    $this->logger->write("Authentication Controller : authenticate() : The Password " . $password . " for User " . $username . " is wrong", 'r');
                    self::$feedback = "Wrong password. Please try again!";
                    $this->login();
                    exit();
                }
            }
        }   
    }
    
    /**
     *	@name resetpassword
     *  @desc Reset a user's password. Create a new password and email it to the user.
     *	@return NULL
     *	@param NULL
     **/
    function resetpassword(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Authentication Controller : resetpassword() : Resetting of the password started", 'r');
        
        // record username POST'ed by the html form
        $username = $this->f3->get('POST.forgotusername');
        // record email POST'ed by the html form
        $email = $this->f3->get('POST.forgotemail');
        
        $user = new users($this->db);
        // pass the username to the getByUsername method
        $user->getByUsername($username);
        $this->logger->write($this->db->log(TRUE), 'r');
        // the dry method checks if the mapper is empty
        if ($user->dry()) {
            // if the mapper is empty, reroute to the login page
            if (!empty($username)) {
                $this->logger->write("Authentication Controller : resetpassword() : The user " . $username . " does not exist", 'r');
                // add some visual feedback to user
                self::$feedback = "The user " . $username . " does not exist";
            }
            // re-render page to display feedback
            $this->login();
            // should we exit script here?
            exit();
        } else {
            $user->isActive($username, $this->appsettings['ACTIVEUSERSTATUSID']);
            $this->logger->write($this->db->log(TRUE), 'r');
            
            if ($user->dry()) {
                $this->logger->write("Authentication Controller : resetaccount() : The user " . $username . " is not active", 'r');
                self::$feedback = "The user " . $username . " is not active";
                $this->login();
                exit();
            } else {
                $user->verifyEmail($username, $email);
                $this->logger->write($this->db->log(TRUE), 'r');
                
                if ($user->dry()) {
                    $this->logger->write("Authentication Controller : resetaccount() : The username and email do not match", 'r');
                    self::$feedback = "The username and email do not match";
                    $this->login();
                    exit();
                } else {
                    $tmppassword = md5(uniqid(rand(), true));
                    $recipient = $user->firstname . ' ' . $user->lastname;
                    $body = 'Hello ' . $user->firstname . ',<br>Your password has been reset to <b>' . $tmppassword . '</b><br>Please login and reset it immediately';
                    
                    try {
                        $this->logger->write("Authentication Controller : resetpassword() : Updating the database", 'r');
                        $this->db->exec(array('UPDATE tblusers SET password = "' . password_hash($tmppassword, PASSWORD_DEFAULT) . '", modifieddt = "' . date('Y-m-d H:i:s') . '", modifiedby = "' . $user->id . '" WHERE id = ' . $user->id));
                        
                        $this->util->sendemailnotification_v2($recipient, $email, 'Password Reset', $body, NULL, $this->appsettings['INTERNALAPI'], $this->appsettings['APPVERSION']);
                        
                        $this->logger->write("Authentication Controller : resetpassword() : Your password has been reset succesfully!", 'r');
                        self::$feedback = "Your password has been reset succesfully. Please check your email for your new password!";
                        $this->f3->clear('SESSION');
                        $this->f3->clear('CACHE');
                        $this->login();
                        exit();
                    } catch (Exception $e) {
                        $this->logger->write("Authentication Controller : resetpassword() : Failed to update table tblusers. The error message is " . $e->getMessage(), 'r');
                        self::$feedback = "Opps. An internal error occurred. Please try again!";
                        $this->login();
                        exit();
                    } 
                }
            }
        }
    }
}

?>
