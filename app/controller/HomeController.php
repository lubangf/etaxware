<?php
use QuickBooksOnline\API\DataService\DataService;
/**
 * This file is part of the etaxware system
 * The is the home controller class
 * @date: 13-06-2020
 * @file: HomeController.php
 * @path: ./app/controller/HomeController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
class HomeController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules

    function index(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        
        if ($this->platformMode == 'ERP') {
            $this->logger->write("Home Controller : index() : The platform is not integrated. It is running as an abriged ERP.", 'r');
        } else {
            $this->logger->write("Home Controller : index() : The platform is integrated.", 'r');
            
            if ($this->integratedErp) {
                /**
                 * Check on integrated ERP type
                 */
                $this->logger->write("Home Controller : index() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                
                if (strtoupper($this->integratedErp) == 'QBO') {
                    $this->logger->write("Home Controller : index() : The integrated ERP is Quicbooks Online.", 'r');
                    
                    $authMode = $this->appsettings['QBAUTH_MODE'];
                    $ClientID = $this->appsettings['QBCLIENT_ID'];
                    $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                    $RedirectURI = $this->appsettings['QBOAUTH_REDIRECT_URI'];
                    $scope = $this->appsettings['QBOAUTH_SCOPE'];
                    $baseUrl = $this->appsettings['QBBASE_URL'];
                    
                    $dataService = DataService::Configure(array(
                        'auth_mode' => $authMode,
                        'ClientID' => $ClientID,
                        'ClientSecret' =>  $ClientSecret,
                        'RedirectURI' => $RedirectURI,
                        'scope' => $scope,
                        'baseUrl' => $baseUrl
                    ));
                    
                    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
                    $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
                    
                    // Store the url in PHP Session Object;
                    $this->f3->set('SESSION.authUrl', $authUrl);
                    $this->f3->set('authUrl', $authUrl);
                    
                    $this->logger->write("Home Controller : index() : authUrl: " . $authUrl, 'r');
                    
                    /**
                     * After, the user connects to QBO using the GUI, this code (aka. the call back) will be executed, by the action of reloading the e-TW GUI, which happens automatically after connecting to QBO.
                     * We've to make sure that we tap into the authorization code which is sent back to use by the QBO server after successful authentication
                     * This authorization code is exchanged for an access or refresh token using the TokenEndpoint. 
                     * We set the tokens into a session variable. Access tokens are used in an API request; refresh tokens are used to get fresh short-lived access tokens after they expire
                     */
                    //$this->processCode();
                    $this->connectToQb();
                    
                    //set the access token using the auth object
                    if ($this->f3->get('SESSION.sessionAccessToken') !== null) {
                        
                        //$this->logger->write("Home Controller : index() : sessionAccessToken: " . $this->f3->get('SESSION.sessionAccessToken'), 'r');
                        
                        
                        $accessToken = $this->f3->get('SESSION.sessionAccessToken');
                        
                        
                        $this->f3->set('token_type', 'bearer');
                        $this->f3->set('access_token', $accessToken->getAccessToken());
                        $this->f3->set('refresh_token', $accessToken->getRefreshToken());
                        $this->f3->set('x_refresh_token_expires_in', $accessToken->getRefreshTokenExpiresAt());
                        $this->f3->set('expires_in', $accessToken->getAccessTokenExpiresAt());
                        //$this->f3->set('expires_in', str_replace('/', '-', substr($accessToken->getAccessTokenExpiresAt(), 22, 19)));
                        
                        $dataService->updateOAuth2Token($accessToken);
                        //$oauthLoginHelper = $dataService -> getOAuth2LoginHelper();
                        //$CompanyInfo = $dataService->getCompanyInfo();
                    } else {
                        /**
                         * The first time we load the e-TW GUI, there will be no authorization code present in the browser, hence this part of the code will be executed.
                         */
                        $this->logger->write("Home Controller : index() : The session access token is NOT set. Retrieve from the database.", 'r');
                        
                        /*
                        $authMode = $this->appsettings['QBAUTH_MODE'];
                        $ClientID = $this->appsettings['QBCLIENT_ID'];
                        $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                        $RedirectURI = $this->appsettings['QBOAUTH_REDIRECT_URI'];
                        $scope = $this->appsettings['QBOAUTH_SCOPE'];
                        $baseUrl = $this->appsettings['QBBASE_URL'];
                        
                        $accessToken = $this->appsettings['QBACCESSTOKEN'];
                        
                        $dataService = DataService::Configure(array(
                            'auth_mode' => $authMode,
                            'ClientID' => $ClientID,
                            'ClientSecret' =>  $ClientSecret,
                            'RedirectURI' => $RedirectURI,
                            'scope' => $scope,
                            'baseUrl' => $baseUrl
                        ));
                        */
                        
                        //$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
                        
                        // Get the Authorization URL from the SDK
                        //$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
                        
                        
                        //$dataService->updateOAuth2Token($accessToken);
                        
                        $this->f3->set('token_type', 'bearer');
                        $this->f3->set('access_token', $this->appsettings['QBACCESSTOKEN']);
                        $this->f3->set('refresh_token', $this->appsettings['QBREFRESHTOKEN']);
                        $this->f3->set('x_refresh_token_expires_in', $this->appsettings['QBSESSIONACCESSTOKENEXPIRY']);
                        $this->f3->set('expires_in', $this->appsettings['QBSESSIONACCESSTOKENEXPIRY']);
                        //$this->f3->set('expires_in', str_replace('/', '-', substr($this->appsettings['QBSESSIONACCESSTOKENEXPIRY'], 22, 19)));
                        
                        /**
                         * Date: 2024-11-08
                         * Author: Francis Lubanga 
                         * Description: Added the 2 statements below to resolve an issue with the GUI sync
                         */
                        //$this->f3->set('sessionAccessToken', $this->appsettings['QBACCESSTOKEN']);
                        //$this->f3->set('SESSION.sessionAccessToken', $this->appsettings['QBACCESSTOKEN']);
                    }
                } elseif (strtoupper($this->integratedErp) == 'QBD'){
                    $this->logger->write("Home Controller : index() : The integrated ERP is Quicbooks Desktop.", 'r');
                } elseif (strtoupper($this->integratedErp) == 'SAP'){
                    $this->logger->write("Home Controller : index() : The integrated ERP is SAP.", 'r');
                } elseif (strtoupper($this->integratedErp) == 'TALLY'){
                    $this->logger->write("Home Controller : index() : The integrated ERP is Tally.", 'r');
                } else {
                    $this->logger->write("Home Controller : index() : The integrated ERP is unknown.", 'r');
                }
            } else {
                $this->logger->write("Home Controller : index() : We are unable to indentify the currently integrated ERP.", 'r');
            }
        }
        
        
        
        $this->f3->set('pagetitle','Home');
        $this->f3->set('pageheader','HomeHeader.htm');
        $this->f3->set('pagecontent','Home.htm');
        $this->f3->set('pagescripts','HomeFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    function processCode(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        // Create SDK instance
        $authMode = $this->appsettings['QBAUTH_MODE'];
        $ClientID = $this->appsettings['QBCLIENT_ID'];
        $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
        $RedirectURI = $this->appsettings['QBOAUTH_REDIRECT_URI'];
        $scope = $this->appsettings['QBOAUTH_SCOPE'];
        $baseUrl = $this->appsettings['QBBASE_URL'];
        
        $dataService = DataService::Configure(array(
            'auth_mode' => $authMode,
            'ClientID' => $ClientID,
            'ClientSecret' =>  $ClientSecret,
            'RedirectURI' => $RedirectURI,
            'scope' => $scope,
            'baseUrl' => $baseUrl
        ));
                
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $parseUrl = $this->parseAuthRedirectUrl(htmlspecialchars_decode($_SERVER['QUERY_STRING']));
        
        /*
         * Update the OAuth2Token
         */
        $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);
        $dataService->updateOAuth2Token($accessToken);
        
        /*
         * Setting the accessToken for session variable
         */
        $this->f3->set('SESSION.sessionAccessToken', $accessToken);
        $this->f3->set('sessionAccessToken', $accessToken);
        
        $this->logger->write("Home Controller : processCode() : The code is " . $parseUrl['code'], 'r');
        $this->logger->write("Home Controller : processCode() : The realmId is " . $parseUrl['realmId'], 'r');
        //$this->logger->write("Home Controller : processCode() : The access token is " . $accessToken, 'r');
        
        try {
            $this->db->exec(array('UPDATE tblsettings SET value = "' . $parseUrl['realmId'] .
                '", modifieddt = "' .  date('Y-m-d H:i:s') .
                '", modifiedby = ' . $this->f3->get('SESSION.id') .
                ' WHERE TRIM(code) = "QBREALMID"'));
            
            $this->db->exec(array('UPDATE tblsettings SET value = "' . $accessToken->getAccessToken() .
                '", modifieddt = "' .  date('Y-m-d H:i:s') .
                '", modifiedby = ' . $this->f3->get('SESSION.id') .
                ' WHERE TRIM(code) = "QBACCESSTOKEN"'));
            
            $this->db->exec(array('UPDATE tblsettings SET value = "' . $accessToken->getRefreshToken() .
                '", modifieddt = "' .  date('Y-m-d H:i:s') .
                '", modifiedby = ' . $this->f3->get('SESSION.id') .
                ' WHERE TRIM(code) = "QBREFRESHTOKEN"'));
            
            $this->db->exec(array('UPDATE tblsettings SET value = "' . $accessToken->getAccessTokenExpiresAt() .
                '", modifieddt = "' .  date('Y-m-d H:i:s') .
                '", modifiedby = ' . $this->f3->get('SESSION.id') .
                ' WHERE TRIM(code) = "QBSESSIONACCESSTOKENEXPIRY"'));
            
        } catch (Exception $e) {
            $this->logger->write("Home Controller : processCode() : The operation to update the settings was not successful. The error message is " . $e->getMessage(), 'r');
            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update the settings by " . $this->f3->get('SESSION.username') . " was not successful");
        }
    }
    
    function parseAuthRedirectUrl($url){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $qsArray = array();
        
        parse_str($url, $qsArray);
        
        return array(
            'code' => $qsArray['code'],
            'realmId' => $qsArray['realmId']
        );
    }
    
    function connectToQb(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Home Controller : connectToQb() : Initiating QB Connection!", 'r');
        $this->logger->write("Home Controller : connectToQb() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
        $this->logger->write("Home Controller : connectToQb() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
        $this->logger->write("Home Controller : connectToQb() : The redirect URL is " . $this->f3->get('SERVER.QUERY_STRING'), 'r');
         
        try {
            $this->processCode();
            $this->logger->write("Home Controller : connectToQb() : The operation to connect to Quickbooks was successful.", 'r');
            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to connect to Quickbooks was successful");
            //self::$systemalert = "The operation to connect to Quickbooks was successful";
        } catch (Exception $e) {
            $this->logger->write("Home Controller : connectToQb() : The operation to connect to Quickbooks was not successful. The error messages is " . $e->getMessage(), 'r');
            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to connect to Quickbooks was not successful");
            //self::$systemalert = "The operation to connect to Quickbooks was not successful";
        }
    }
}

?>