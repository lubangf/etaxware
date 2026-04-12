<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\SalesReceipt;
use QuickBooksOnline\API\Facades\CreditMemo;
use QuickBooksOnline\API\Facades\RefundReceipt;

// require_once '../../../vendor/consolibyte/quickbooks/QuickBooks.php';

/**
 * @name Api.php
 * @desc This file is part of the etaxware-api app. This is the API version 9
 * @date: 19-05-2024
 * @file: Api.php
 * @path: ./api/e-taxware/v9/Api.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @version    9.0.0
 */
class Api
{
    
    protected $module = NULL;
    
    // tblmodules
    protected $submodule = NULL;
    
    // tblsubmodules
    protected $f3;
    
    // store an instance of base
    protected $db;
    
    // store database connection here
    protected $logger;
    
    protected $appsettings;
    
    // store the setting details here
    protected $util;
    
    // store utilities here
    protected $data;
    
    // store the data/request from the client
    protected $response;
    
    protected $message;
    
    protected $code;
    
    protected $action;
    
    protected $json;
    
    protected $params;
    
    protected $errormessage;
    
    protected $errorcode;
    
    protected $apikey;
    
    // store the API key sent by the client
    protected $version;
    
    // store the version sent by the client
    
    /* API user details. These are populated from a setting, are are using for more admin specific tasks, such as creating audit logs, sending email alerts. */
    protected $userid;
    
    protected $username;
    
    protected $password;
    
    protected $permissions;
    
    /* Current user details */
    protected $userpermissions;
    
    protected $userid_u;
    
    protected $username_u;
    
    protected $userbranch_u;
    
    /* Email Settings */
    protected $recipientname;
    
    protected $recipientemail;
    
    protected $subject;
    
    protected $ccrecipientemail = 'frncslubanga@gmail.com';
    
    protected $ccrecipientname = 'e-TaxWare Developer';
    
    protected $emailhost;
    
    protected $emailport;
    
    protected $vatRegistered;
    
    // Flag to indicate if the tax payer is registered for VAT or not.
    protected $platformMode;
    
    // Determine if the platform is running in Integrated Mode OR as an Abridged ERP itself
    protected $efrisMode;
    
    // Determine if we are hitting the offline enabler or direct online APIs
    protected $integratedErp;
    
    // Determine the Type of ERP Integrated
    
    /**
     * Send Email
     *
     * @name sendmail
     * @return NULL
     * @param
     *            NULL
     */
    function sendmail()
    {
        $operation = NULL; // tblevents
        $permission = 'SENDEMAIL'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications
        
        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $json = json_decode($this->json, TRUE); // convert JSON into array
        
        $recipientname = trim($json['RECIPIENTNAME']);
        $recipientemail = trim($json['RECIPIENTEMAIL']);
        $subject = trim($json['SUBJECT']);
        $body = trim($json['BODY']);
        $attachments = array(); // this is an array
        
        if ($recipientemail && $body) {
            try {
                // Create a new PHPMailer instance
                $mail = new PHPMailer();
                
                // Tell PHPMailer to use SMTP
                $mail->isSMTP();
                
                // Enable SMTP debugging
                // SMTP::DEBUG_OFF = off (for production use)
                // SMTP::DEBUG_CLIENT = client messages
                // SMTP::DEBUG_SERVER = client and server messages
                $mail->SMTPDebug = SMTP::DEBUG_OFF;
                
                // Set the hostname of the mail server
                $mail->Host = $this->emailhost;
                // use
                // $mail->Host = gethostbyname('smtp.gmail.com');
                // if your network does not support SMTP over IPv6
                
                // Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
                $mail->Port = $this->emailport;
                
                // Set the encryption mechanism to use - STARTTLS or SMTPS
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                
                // Whether to use SMTP authentication
                $mail->SMTPAuth = true;
                
                // Username to use for SMTP authentication - use full email address for gmail
                $mail->Username = $this->appsettings['EMAILUSERNAME'];
                
                // Password to use for SMTP authentication
                $mail->Password = $this->appsettings['EMAILPASSWORD'];
                
                // Set who the message is to be sent from
                $mail->setFrom($this->appsettings['EMAILUSERNAME'], 'e-TaxWare App');
                
                // Set an alternative reply-to address
                // $mail->addReplyTo('replyto@example.com', 'First Last');
                
                // Set who the message is to be sent to
                $mail->addAddress($recipientemail, $recipientname);
                $mail->AddCC($this->ccrecipientemail, $this->ccrecipientname);
                
                // Set the subject line
                $mail->Subject = $subject;
                
                // Read an HTML message body from an external file, convert referenced images to embedded,
                // convert HTML into a basic plain-text alternative body
                // $mail->msgHTML(file_get_contents('contents.html'), __DIR__);
                $mail->isHTML(true);
                $mail->Body = $body;
                
                // Replace the plain text body with one created manually
                $mail->AltBody = 'This is a plain-text message body';
                
                // Attach an image file
                // $mail->addAttachment('../scripts/db/rematch.sql');
                foreach ($attachments as $obj) {
                    $mail->addAttachment($obj['path'] . $obj['name']);
                }
                
                // send the message, check for errors
                if (! $mail->send()) {
                    $this->logger->write("Api Controller : sendmail() : The operation to send an email was not successful. The error messages is " . $mail->ErrorInfo, 'r');
                    $this->code = '300';
                    $this->message = 'The operation to send an email was not successful';
                } else {
                    $this->logger->write("Api : sendmail() : The operation to send an email was successful", 'r');
                    $this->code = '000';
                    $this->message = 'The operation to send an email was successful';
                }
            } catch (Exception $e) {
                $this->logger->write("Api Controller : sendmail() : The operation to send an email was not successful. The error messages is " . $e->getMessage(), 'r');
                $this->code = '300';
                $this->message = 'The operation to send an email was not successful';
            }
        } else {
            $this->logger->write("Api : sendmail() : There was no email or body specified", 'r');
            $this->code = '500';
            $this->message = 'There was no email or body specified';
        }
        
        $this->response = array(
            "response" => array(
                "responseCode" => $this->code,
                "responseMessage" => $this->message
            ),
            "data" => array()
        );
        
        $len = sizeof($this->response);
        header("CONTENT-LENGTH:" . $len);
        // print $this->response;
        die(json_encode($this->response));
        return;
    }
    
    /**
     * used to test if the service is running
     *
     * @name index
     * @return string response
     * @param
     *            NULL
     */
    function index()
    {
        $this->logger->write("Api : index() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
        $this->logger->write("Api : index() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
        
        if ($this->errorcode) {
            $this->message = $this->errormessage;
            $this->code = $this->errorcode;
            
            $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The attempt to access the API failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
            
            $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
        } else {
            $this->message = 'It Works!';
            $this->code = '000';
        }
        
        $this->response = array(
            "response" => array(
                "responseCode" => $this->code,
                "responseMessage" => $this->message
            ),
            "data" => array()
        );
        
        $len = sizeof($this->response);
        header("CONTENT-LENGTH:" . $len);
        // print $this->response;
        die(json_encode($this->response));
        return;
    }
    
    /**
     * used to test if the service is running
     *
     * @name testapi
     * @return string response
     * @param
     *            NULL
     */
    function testapi()
    {
        $this->logger->write("Api : testapi() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
        $this->logger->write("Api : testapi() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
        
        if ($this->errorcode) {
            $this->message = $this->errormessage;
            $this->code = $this->errorcode;
            
            $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The attempt to test the API failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
            
            $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
        } else {
            $this->message = 'It Works!';
            $this->code = '000';
        }
        
        $this->response = array(
            "response" => array(
                "responseCode" => $this->code,
                "responseMessage" => $this->message
            ),
            "data" => array()
        );
        
        $len = sizeof($this->response);
        header("CONTENT-LENGTH:" . $len);
        // print $this->response;
        die(json_encode($this->response));
        return;
    }
    
    /**
     * NULL
     *
     * @name importErpInvoices
     * @return NULL
     * @param
     *            NULL
     */
    function importErpInvoices()
    {
        $operation = NULL; // tblevents
        $permission = 'SYNCINVOICES'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications
        
        if ($this->errorcode) {
            $this->message = $this->errormessage;
            $this->code = $this->errorcode;
            
            $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The attempt to access the API failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
            
            $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
        } else {
            $this->logger->write("Api : importErpInvoices() : Initiating QB Invoice Importation!", 'r');
            
            $inactivityPeriod = 0;
            $sessionAccessToken = NULL;
            $sessionRefreshToken = NULL;
            $sessionAccessTokenExpiry = NULL;
            
            // Retrieve the access token from the database.
            $token_check = new DB\SQL\Mapper($this->db, 'tblsettings');
            $token_check->load(array(
                'TRIM(code)=?',
                'QBACCESSTOKEN'
            ));
            
            if ($token_check->dry()) {
                $this->logger->write("Api : importErpInvoices() : The access token is NOT set in the database. Please manually login.", 'r');
                $this->message = 'The access token is NOT set in the database. Please manually login.';
                $this->code = '999';
                // Return ERROR message
                // SEND an email.
            } else {
                $this->logger->write("Api : importErpInvoices() : The access token was retrieved successfuly from the database", 'r');
                $sessionAccessToken = trim($token_check->value);
                
                // $this->logger->write("Api : importErpInvoices() : The access token is " . $sessionAccessToken, 'r');
                
                // set the access token using the auth object
                if ($sessionAccessToken !== null) {
                    
                    $sessionRefreshToken = $this->appsettings['QBREFRESHTOKEN'];
                    
                    if ($this->appsettings['QBSESSIONACCESSTOKENEXPIRY']) {
                        //$sessionAccessTokenExpiry = $this->appsettings['QBSESSIONACCESSTOKENEXPIRY'];
                        $sessionAccessTokenExpiry =  str_replace('/', '-', $this->appsettings['QBSESSIONACCESSTOKENEXPIRY']);
                    } else {
                        $sessionAccessTokenExpiry = date('Y-m-d H:i:s', strtotime('-1 days'));
                    }
                    
                    $this->logger->write("Api : importErpInvoices() : The refresh token is " . $sessionRefreshToken, 'r');
                    $this->logger->write("Api : importErpInvoices() : The access token expiry is " . $sessionAccessTokenExpiry, 'r');
                    
                    $startDt = new DateTime(date('Y-m-d H:i:s'));
                    $endDt = new DateTime($sessionAccessTokenExpiry);
                    
                    $inactivityPeriod = $startDt->getTimestamp() - $endDt->getTimestamp();
                    
                    $this->logger->write("Api : importErpInvoices() : The current time is " . date('Y-m-d H:i:s'), 'r');
                    $this->logger->write("Api : importErpInvoices() : The inactivity period is " . $inactivityPeriod, 'r');
                    
                    if ($inactivityPeriod < 0) {
                        $tcsdetails = new tcsdetails($this->db);
                        $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
                        
                        $companydetails = new organisations($this->db);
                        $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);
                        
                        $devicedetails = new devices($this->db);
                        $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
                        
                        $endDate = date('Y-m-d', strtotime('+1 days')); // date('Y-m-d H:i:s', strtotime('+3 days'));
                        $startDate = date('Y-m-d', strtotime("-" . $this->appsettings['ERPPROCPERIOD'] . " days")); // date('Y-m-d H:i:s', strtotime('-3 days'));
                        $docType = $this->appsettings['INVOICEERPDOCTYPE'];
                        
                        $endTime = date('H:i:s', strtotime('+1 seconds'));
                        $startTime = date('H:i:s', strtotime("-" . $this->appsettings['ERPPROCPERIODTIME'] . " seconds"));
                        
                        $this->logger->write("Api : importErpInvoices() : startDate: " . $startDate, 'r');
                        $this->logger->write("Api : importErpInvoices() : endDate: " . $endDate, 'r');
                        $this->logger->write("Api : importErpInvoices() : docType: " . $docType, 'r');
                        
                        $this->logger->write("Api : importErpInvoices() : startTime: " . $startTime, 'r');
                        $this->logger->write("Api : importErpInvoices() : endTime: " . $endTime, 'r');
                        
                        if ($this->platformMode == 'ERP') {
                            $this->logger->write("Api : importErpInvoices() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                            $this->message = 'The platform is not integrated. It is running as an abriged ERP.';
                            $this->code = '001';
                        } else {
                            $this->logger->write("Api : importErpInvoices() : The platform is integrated.", 'r');
                            
                            if ($this->integratedErp) {
                                /**
                                 * Check on integrated ERP type
                                 */
                                $this->logger->write("Api : importErpInvoices() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                                
                                if (strtoupper($this->integratedErp) == 'QBO') {
                                    $this->logger->write("Api : importErpInvoices() : The integrated ERP is Quicbooks Online.", 'r');
                                    
                                    $qry = '';
                                    
                                    if ($docType == trim($this->appsettings['INVOICEERPDOCTYPE'])) {
                                        $qry = 'SELECT * FROM Invoice';
                                    } elseif ($docType == trim($this->appsettings['SALESRECEIPTERPDOCTYPE'])) {
                                        $qry = 'SELECT * FROM SalesReceipt';
                                    } else {
                                        $qry = 'SELECT * FROM Invoice';
                                    }
                                    
                                    //$endDate = date('Y-m-d', strtotime('-30 days'));
                                    //$qry = $qry . " Where Metadata.CreateTime >= '2022-11-01' And Metadata.CreateTime <= '2022-11-15'";
                                    //$qry = $qry . " Where DocNumber IN ('26380','26379','26257','26021','26020','26019','26018','26017','26016','26015','26014','26013','26012','26011','26010','26009','26008','26007','26006','26005','26004','26003','26002','26001','26000') STARTPOSITION 1 MAXRESULTS 30";
                                    //$qry = $qry . " Where Metadata.CreateTime >= '" . $startDate . "' And Metadata.CreateTime <= '" . $endDate . "' STARTPOSITION 1 MAXRESULTS 60";
                                    //$qry = $qry . " Where Metadata.CreateTime >= '" . $startDate . "' And Metadata.CreateTime <= '" . $endDate . "'";
                                    //$qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "T" . $startTime . "' And Metadata.LastUpdatedTime <= '" . $endDate . "T" . $endTime . "' STARTPOSITION 1 MAXRESULTS 20";
                                    //$qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "' And Metadata.LastUpdatedTime <= '" . $endDate . "' STARTPOSITION 1 MAXRESULTS 20";
                                    //$qry = $qry . " Where Metadata.CreateTime >= '" . $startDate . "' And Metadata.CreateTime <= '" . $endDate . "'";
                                    //$qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "T" . $startTime . "' And Metadata.LastUpdatedTime <= '" . $endDate . "T" . $endTime . "' STARTPOSITION 1 MAXRESULTS 20";
                                    $qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "T" . $startTime . "' And Metadata.LastUpdatedTime <= '" . $endDate . "T" . $endTime . "'";
                                    //$qry = $qry . " Where Metadata.CreateTime >= '" . $startDate . "' And Metadata.CreateTime <= '" . $endDate . "' ORDER BY Id ASC STARTPOSITION 1151 MAXRESULTS 20";
                                    //$qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "T" . $startTime . "' And Metadata.LastUpdatedTime <= '" . $endDate . "T" . $endTime . "'";
									//$qry = $qry . " Where DocNumber IN ('28075', '28106', '28075', '28063', '27955', '28006', '28001', '27977', '27917', '27884', '27852', '27828', '28333', '28345', '28352', '28406', '28453', '28464', '28106', '28167', '28181', '28200', '28237', '28242', '28256', '28258', '28260', '28284', '28284', '28333', '28345', '28352', '28406', '28453', '28464', '26868', '26869') STARTPOSITION 31 MAXRESULTS 10";
                                    //$qry = $qry . " Where DocNumber IN ('28726', '28915', '28914', '28912', '28911', '28907', '28900', '28894', '28887', '28883', '28871', '28870', '28969', '28968', '28966', '28963', '28962', '28959', '28954', '28948', '28942', '28937', '28934', '28933', '28931', '28927', '28925', '28923', '29039', '29020', '29017', '29016', '29009', '29003', '28992', '28989', '28986', '28985', '28983', '28982', '28980', '29123', '29118', '29232', '28486', '28477', '28564', '28565', '28599', '28619', '28605', '28639', '28634', '28627', '28659', '28645') STARTPOSITION 51 MAXRESULTS 10";
                                    
									//$qry = "SELECT * FROM Invoice Where Metadata.CreateTime >= '2024-05-24' And Metadata.CreateTime < '2024-05-25' STARTPOSITION 101 MAXRESULTS 100";
									
                                    $this->logger->write("Api : importErpInvoices() : The query is: " . $qry, 'r');
                                    
                                    try {
                                        if ($sessionAccessToken !== null) {
                                            // Create SDK instance
                                            $authMode = $this->appsettings['QBAUTH_MODE'];
                                            $ClientID = $this->appsettings['QBCLIENT_ID'];
                                            $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                            $baseUrl = $this->appsettings['QBBASE_URL'];
                                            $QBORealmID = $this->appsettings['QBREALMID'];
                                            
                                            $dataService = DataService::Configure(array(
                                                'auth_mode' => $authMode,
                                                'ClientID' => $ClientID,
                                                'ClientSecret' => $ClientSecret,
                                                'baseUrl' => $baseUrl,
                                                'refreshTokenKey' => $sessionRefreshToken,
                                                'QBORealmID' => $QBORealmID,
                                                'accessTokenKey' => $sessionAccessToken
                                            ));
                                            
                                            $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                            $dataService->throwExceptionOnError(true);
                                            
                                            $invoices = $dataService->Query($qry);
                                            
                                            $error = $dataService->getLastError();
                                            
                                            if ($error) {
                                                $this->logger->write("Api : importErpInvoices() : The operation to download ERP invoices was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                                $this->message = 'The operation to download ERP invoices was not successful.';
                                                $this->code = '002';
                                            } else {
                                                // print_r($invoices);
                                                
                                                if (isset($invoices)) {
                                                    if ($invoices) {
                                                        $this->logger->write("Api : importErpInvoices() : The # of invoices is: " . sizeof($invoices), 'r');
                                                        $invoice = new invoices($this->db);
                                                        $customer = new customers($this->db);
                                                        
                                                        /**
                                                         * Author: frncslubanga@gmail.com
                                                         * Date: 2024-05-20
                                                         * Description: Handle fees/taxes which have no rates, and are passed as invoice line items
                                                         */
                                                        $mapped_fees = array();
                                                        
                                                        if(trim($this->appsettings['CHECK_FEE_MAP_FLAG']) == '1'){
                                                            $this->logger->write("Api : importErpInvoices() : The check fees mapping is on", 'r');
                                                            
                                                            $feesmapping = new feesmapping($this->db);
                                                            $feesmappings = $feesmapping->all();
                                                            
                                                            foreach ($feesmappings as $f_obj) {
                                                                $this->logger->write("Api : importErpInvoices() : The fee code is " . $f_obj['feecode'], 'r');
                                                                $this->logger->write("Api : importErpInvoices() : The product code is " . $f_obj['productcode'], 'r');
                                                                
                                                                $mapped_fees[] = array(
                                                                    'id' => empty($f_obj['id'])? '' : $f_obj['id'],
                                                                    'feecode' => empty($f_obj['feecode'])? '' : $f_obj['feecode'],
                                                                    'productcode' => empty($f_obj['productcode'])? '' : $f_obj['productcode'],
                                                                    'amount' => 0
                                                                );
                                                            }
                                                        }
                                                        
                                                        foreach ($invoices as $elem) {
                                                            
                                                            $goods = array();
                                                            $taxes = array();
                                                            
                                                            $deemedflag = 'NO';
                                                            $discountflag = 'NO';
                                                            
                                                            $pricevatinclusive = empty($this->appsettings['PRICEVATINCLUSIVE']) ? 'NO' : strtoupper($this->appsettings['PRICEVATINCLUSIVE']); // No
                                                            
                                                            $netamount = 0;
                                                            $taxamount = 0;
                                                            $grossamount = 0;
                                                            $itemcount = 0;
                                                            
                                                            $tr = new taxrates($this->db);
                                                            $taxid = NULL;
                                                            $taxcode = NULL;
                                                            $taxname = NULL;
                                                            $taxcategory = NULL;
                                                            $taxdisplaycategory = NULL;
                                                            $taxdescription = NULL;
                                                            $rate = 0;
                                                            $qty = 0;
                                                            $unit = 0;
                                                            $discountpct = 0;
                                                            $total = 0;
                                                            $discount = 0;
                                                            $gross = 0;
                                                            $discount = 0;
                                                            $tax = 0;
                                                            $net = 0;
                                                            $amount = 0;
                                                            $product = new products($this->db);
                                                            $measureunit = new measureunits($this->db);
                                                            
                                                            $buyer = array(
                                                                'id' => NULL,
                                                                'erpcustomerid' => NULL,
                                                                'erpcustomercode' => NULL,
                                                                'tin' => NULL,
                                                                'ninbrn' => NULL,
                                                                'PassportNum' => NULL,
                                                                'legalname' => NULL,
                                                                'businessname' => NULL,
                                                                'address' => NULL,
                                                                'mobilephone' => NULL,
                                                                'linephone' => NULL,
                                                                'emailaddress' => NULL,
                                                                'placeofbusiness' => NULL,
                                                                'type' => NULL,
                                                                'citizineship' => NULL,
                                                                'countryCode' => NULL,
                                                                'sector' => NULL,
                                                                'sectorCode' => NULL,
                                                                'datasource' => 'ERP',
                                                                'status' => $this->appsettings['ACTIVECUSTOMERSTATUSID']
                                                            );
                                                            
                                                            $invoicedetails = array(
                                                                'id' => NULL,
                                                                'gooddetailgroupid' => NULL,
                                                                'taxdetailgroupid' => NULL,
                                                                'paymentdetailgroupid' => NULL,
                                                                'erpinvoiceid' => NULL,
                                                                'erpinvoiceno' => NULL,
                                                                'antifakecode' => NULL,
                                                                'deviceno' => trim($devicedetails->deviceno),
                                                                'issueddate' => NULL,
                                                                'issuedtime' => NULL,
                                                                'operator' => NULL,
                                                                'currency' => NULL,
                                                                'oriinvoiceid' => NULL,
                                                                'invoicetype' => "1",
                                                                'invoicekind' => ($this->vatRegistered == 'Y') ? "1" : "2",
                                                                'datasource' => $this->appsettings['DEFAULTDATASOURCE'],
                                                                'invoiceindustrycode' => $this->appsettings['DEFAULTINVOICEINDUSTRY'],
                                                                'einvoiceid' => NULL,
                                                                'einvoicenumber' => NULL,
                                                                'einvoicedatamatrixcode' => NULL,
                                                                'isbatch' => '0',
                                                                'netamount' => NULL,
                                                                'taxamount' => NULL,
                                                                'grossamount' => NULL,
                                                                'origrossamount' => NULL,
                                                                'itemcount' => NULL,
                                                                'modecode' => NULL,
                                                                'modename' => NULL,
                                                                'remarks' => NULL,
                                                                'buyerid' => NULL,
                                                                'sellerid' => $this->appsettings['SELLER_RECORD_ID'],
                                                                'issueddatepdf' => NULL,
                                                                'grossamountword' => NULL,
                                                                'isinvalid' => 0,
                                                                'isrefund' => 0,
                                                                'vchtype' => "Sales",
                                                                'vchtypename' => "Tax Invoice",
                                                                'SyncToken' => NULL,
                                                                'docTypeCode' => $docType
                                                            );
                                                            
                                                            $discountAppStatus = 0;
                                                            $discountAppBalance = 0;
                                                            $discountAppPct = 0;
                                                            
                                                            try {
                                                                $this->logger->write("Api : importErpInvoices() : Invoice Number: " . $elem->DocNumber, 'r');
                                                                $this->logger->write("Api : importErpInvoices() : PrivateNote: " . $elem->PrivateNote, 'r');
                                                                $InvStatus = $elem->PrivateNote;
                                                                
                                                                $CustomerRef = $elem->CustomerRef;
                                                                $DocNumber = $elem->DocNumber;
                                                                $CurrencyRef = $elem->CurrencyRef;
                                                                $TxnDate = $elem->TxnDate;
                                                                $InvoiceId = $elem->Id;
                                                                $SyncToken = $elem->SyncToken;
                                                                $TxnDate = $elem->TxnDate;
                                                                
                                                                $invoicedetails['erpinvoiceid'] = $InvoiceId;
                                                                $invoicedetails['erpinvoiceno'] = $DocNumber;
                                                                
                                                                if ($CustomerRef) {
                                                                    $customer->getByCode($CustomerRef);
                                                                    
                                                                    if ($customer->id) {
                                                                        $this->logger->write("Api : importErpInvoices() : The customer Id " . $CustomerRef . " exists on the platform", 'r');
                                                                        
                                                                        $buyer['id'] = $customer->id;
                                                                        $buyer['erpcustomerid'] = $customer->erpcustomerid;
                                                                        $buyer['erpcustomercode'] = $customer->erpcustomercode;
                                                                        $buyer['tin'] = $customer->tin;
                                                                        $buyer['ninbrn'] = $customer->ninbrn;
                                                                        $buyer['PassportNum'] = $customer->PassportNum;
                                                                        $buyer['legalname'] = $customer->legalname;
                                                                        $buyer['businessname'] = $customer->businessname;
                                                                        $buyer['address'] = $customer->address;
                                                                        $buyer['mobilephone'] = $customer->mobilephone;
                                                                        $buyer['linephone'] = $customer->linephone;
                                                                        $buyer['emailaddress'] = $customer->emailaddress;
                                                                        $buyer['placeofbusiness'] = $customer->placeofbusiness;
                                                                        $buyer['type'] = $customer->type;
                                                                        $buyer['citizineship'] = $customer->citizineship;
                                                                        $buyer['countryCode'] = $customer->countryCode;
                                                                        $buyer['sector'] = $customer->sector;
                                                                        $buyer['sectorCode'] = $customer->sectorCode;
                                                                        $buyer['datasource'] = $customer->datasource;
                                                                        $buyer['status'] = $customer->status;
                                                                        
                                                                        $invoicedetails['buyerid'] = $customer->id;
                                                                    } else {
                                                                        $this->logger->write("Api : importErpInvoices() : The customer Id " . $CustomerRef . " does not exist on the platform", 'r');
                                                                        
                                                                        // Let's download the customer
                                                                        $customers = $dataService->FindbyId('customer', $CustomerRef);
                                                                        
                                                                        $custError = $dataService->getLastError();
                                                                        
                                                                        if ($custError) {
                                                                            $this->logger->write("Api : importErpInvoices() : The operation to download ERP customers was not successful. The Response Message is: " . $custError->getResponseBody(), 'r');
                                                                            
                                                                            /**
                                                                             * Date: 2022-12-30
                                                                             * Authour: Francis Lubanga
                                                                             * Description: Resolving error: buyerDetails-->buyerType:cannot be empty!
                                                                             *              We need to skip to the next invoice in the queue if we fail to download the customer.
                                                                             */
                                                                            continue;
                                                                        } else {
                                                                            // print_r($customers);
                                                                            if (isset($customers)) {
                                                                                if ($customers) {
                                                                                    
                                                                                    $cust = array(
                                                                                        'id' => NULL,
                                                                                        'erpcustomerid' => NULL,
                                                                                        'erpcustomercode' => NULL,
                                                                                        'tin' => NULL,
                                                                                        'ninbrn' => NULL,
                                                                                        'PassportNum' => NULL,
                                                                                        'legalname' => NULL,
                                                                                        'businessname' => NULL,
                                                                                        'address' => NULL,
                                                                                        'mobilephone' => NULL,
                                                                                        'linephone' => NULL,
                                                                                        'emailaddress' => NULL,
                                                                                        'placeofbusiness' => NULL,
                                                                                        'type' => '1', /*default all customers to B2C*/
                                                                                        'citizineship' => NULL,
                                                                                        'countryCode' => NULL,
                                                                                        'sector' => NULL,
                                                                                        'sectorCode' => NULL,
                                                                                        'datasource' => 'ERP',
                                                                                        'status' => NULL
                                                                                    );
                                                                                    
                                                                                    try {
                                                                                        $this->logger->write("Api : importErpInvoices() : Customer Name: " . $customers->DisplayName, 'r');
                                                                                        
                                                                                        $erpcustomerid = $customers->Id;
                                                                                        $erpcustomercode = $customers->Id;
                                                                                        $legalname = empty($customers->FullyQualifiedName) ? $customers->DisplayName : $customers->FullyQualifiedName;
                                                                                        $businessname = empty($customers->FullyQualifiedName) ? $customers->DisplayName : $customers->FullyQualifiedName;
                                                                                        
                                                                                        if (isset($customers->PrimaryPhone)) {
                                                                                            $mobilephone = $customers->PrimaryPhone->FreeFormNumber;
                                                                                            $cust['mobilephone'] = $mobilephone;
                                                                                        }
                                                                                        
                                                                                        if (isset($customers->PrimaryEmailAddr)) {
                                                                                            $emailaddress = $customers->PrimaryEmailAddr->Address;
                                                                                            $cust['emailaddress'] = $emailaddress;
                                                                                        }
                                                                                        
                                                                                        if (isset($customers->BillAddr)) {
                                                                                            $address = $customers->BillAddr->Line1;
                                                                                            $cust['address'] = $address;
                                                                                        }
                                                                                        
                                                                                        $this->logger->write("Api : importErpInvoices() : Mobile: " . $mobilephone, 'r');
                                                                                        $this->logger->write("Api : importErpInvoices() : Email: " . $emailaddress, 'r');
                                                                                        
                                                                                        $cust['erpcustomerid'] = $erpcustomerid;
                                                                                        $cust['erpcustomercode'] = $erpcustomercode;
                                                                                        $cust['legalname'] = $legalname;
                                                                                        $cust['businessname'] = $businessname;
                                                                                        
                                                                                        if ($customers->Active == false) {
                                                                                            $cust['status'] = $this->appsettings['INACTIVECUSTOMERSTATUSID'];
                                                                                            $this->logger->write("Api : importErpInvoices() : The customer is not ACTIVE.", 'r');
                                                                                        } else {
                                                                                            $cust['status'] = $this->appsettings['ACTIVECUSTOMERSTATUSID'];
                                                                                            $this->logger->write("Api : importErpInvoices() : The customer is ACTIVE.", 'r');
                                                                                        }
                                                                                        
                                                                                        if ($erpcustomercode && $legalname) {
                                                                                            $this->logger->write("Api : importErpInvoices() : The customer does not exist", 'r');
                                                                                            $cust_status = $this->util->createcustomer($cust, $this->f3->get('SESSION.id'));
                                                                                            
                                                                                            if ($cust_status) {
                                                                                                $this->logger->write("Api : importErpInvoices() : The customer " . $cust['legalname'] . " was created.", 'r');
                                                                                                
                                                                                                $customer->getByCode($CustomerRef);
                                                                                                
                                                                                                if ($customer->id) {
                                                                                                    $buyer['id'] = $customer->id;
                                                                                                    $buyer['erpcustomerid'] = $customer->erpcustomerid;
                                                                                                    $buyer['erpcustomercode'] = $customer->erpcustomercode;
                                                                                                    $buyer['tin'] = $customer->tin;
                                                                                                    $buyer['ninbrn'] = $customer->ninbrn;
                                                                                                    $buyer['PassportNum'] = $customer->PassportNum;
                                                                                                    $buyer['legalname'] = $customer->legalname;
                                                                                                    $buyer['businessname'] = $customer->businessname;
                                                                                                    $buyer['address'] = $customer->address;
                                                                                                    $buyer['mobilephone'] = $customer->mobilephone;
                                                                                                    $buyer['linephone'] = $customer->linephone;
                                                                                                    $buyer['emailaddress'] = $customer->emailaddress;
                                                                                                    $buyer['placeofbusiness'] = $customer->placeofbusiness;
                                                                                                    $buyer['type'] = $customer->type;
                                                                                                    $buyer['citizineship'] = $customer->citizineship;
                                                                                                    $buyer['countryCode'] = $customer->countryCode;
                                                                                                    $buyer['sector'] = $customer->sector;
                                                                                                    $buyer['sectorCode'] = $customer->sectorCode;
                                                                                                    $buyer['datasource'] = $customer->datasource;
                                                                                                    $buyer['status'] = $customer->status;
                                                                                                    
                                                                                                    $invoicedetails['buyerid'] = $customer->id;
                                                                                                } else {
                                                                                                    $this->logger->write("Api : importErpInvoices() : The created customer was not retrieved successfully.", 'r');
                                                                                                }
                                                                                            } else {
                                                                                                $this->logger->write("Api : importErpInvoices() : The customer " . $cust['legalname'] . " was NOT created.", 'r');
                                                                                            }
                                                                                        } else {
                                                                                            $this->logger->write("Api : importErpInvoices() : The customer has no Id.", 'r');
                                                                                        }
                                                                                    } catch (Exception $e) {
                                                                                        $this->logger->write("Api : importErpInvoices() : There was an error when processing Item " . $customers->DisplayName . ". The error is " . $e->getMessage(), 'r');
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                $this->logger->write("Api : importErpInvoices() : The operation to download ERP customers did not return records.", 'r');
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                
                                                                if (isset($elem->Line)) {
                                                                    foreach ($elem->Line as $items) {
                                                                        $LineId = $items->Id;
                                                                        $LineNum = $items->LineNum;
                                                                        $Description = $items->Description;
                                                                        $ErpAmount = $items->Amount;
                                                                        $DetailType = $items->DetailType;
                                                                        $this->logger->write("Api : importErpInvoices() : Line Description: " . $Description, 'r');
                                                                        
                                                                        if (strtoupper($items->DetailType) == 'DISCOUNTLINEDETAIL') {
                                                                            if (isset($items->DiscountLineDetail)) {
                                                                                $PercentBased = $items->DiscountLineDetail->PercentBased; // true/false
                                                                                $DiscountPercent = $items->DiscountLineDetail->DiscountPercent;
                                                                            }
                                                                            
                                                                            $this->logger->write("Api : importErpInvoices() : Discount Percent: " . $PercentBased, 'r');
                                                                            $discount = empty($ErpAmount) ? 0 : (float) $ErpAmount;
                                                                            $discountpct = empty($DiscountPercent) ? 0 : (float) $DiscountPercent;
                                                                            
                                                                            if (! empty($ErpAmount)) {
                                                                                $discountAppStatus = 1;
                                                                                $discountAppBalance = $ErpAmount;
                                                                                $discountAppPct = $discountpct;
                                                                            }
                                                                        }
                                                                        
                                                                        if (strtoupper($items->DetailType) == 'SALESITEMLINEDETAIL') {
                                                                            if (isset($items->SalesItemLineDetail)) {
                                                                                $ItemRef = $items->SalesItemLineDetail->ItemRef;
                                                                                $UnitPrice = $items->SalesItemLineDetail->UnitPrice;
                                                                                $Qty = $items->SalesItemLineDetail->Qty;
                                                                                $TaxCodeRef = $items->SalesItemLineDetail->TaxCodeRef;
                                                                            }
                                                                            
                                                                            $this->logger->write("Api : importErpInvoices() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                            $this->logger->write("Api : importErpInvoices() : Unit Price: " . $UnitPrice, 'r');
                                                                            $this->logger->write("Api : importErpInvoices() : Qty: " . $Qty, 'r');
                                                                            
                                                                            $product->getByErpCode($ItemRef);
                                                                            
                                                                            if ($product->code) {
                                                                                $measureunit->getByCode($product->measureunit);
                                                                            } else {
                                                                                $this->logger->write("Api : importErpInvoices() : The Item does not exist on the platform", 'r');
                                                                            }
                                                                            
                                                                            $ii = 0;
                                                                            $product_skip_flag = 0;
                                                                            
                                                                            foreach ($mapped_fees as $m_obj) {
                                                                                if(trim($m_obj['productcode']) == $product->code){
                                                                                    $this->logger->write("Api : importErpInvoices() : The product " . $product->code . " is mapped to a tax/fee " . $m_obj['feecode'], 'r');
                                                                                    
                                                                                    $f_qty = $Qty;
                                                                                    $f_unit = $UnitPrice;
                                                                                    
                                                                                    $mapped_fees[$ii]['amount'] = ($f_qty * $f_unit); # Might be problematic. Consider "foreach ($mapped_fees as &$m_obj) {"
                                                                                    $product_skip_flag = 1;
                                                                                    break;
                                                                                }
                                                                                
                                                                                $ii = $ii + 1;
                                                                            }
                                                                            
                                                                            if ($product_skip_flag == 1){
                                                                                continue;
                                                                            }
                                                                            
                                                                            $qty = $Qty;
                                                                            $unit = $UnitPrice;
                                                                            $amount = $ErpAmount;
                                                                            
                                                                            /**
                                                                             * Can we determine the DISCOUNT PERCENTAGE incase it is a line DISCOUNT provided?
                                                                             */
                                                                            if ($discountpct == 0 && $discount > 0) {
                                                                                $discountpct = $discount / $amount;
                                                                                $discount = 0;
                                                                            } else {
                                                                                $discount = 0;
                                                                            }
                                                                            
                                                                            $taxid = $this->util->getinvoicetaxrate_v2($this->appsettings['DEFAULTINVOICEINDUSTRY'], $customer->type, $product->code, $customer->tin, $this->appsettings['OVERRIDE_TAXRATE_FLAG'], $this->appsettings['TAXPAYER_CHECK_FLAG']);
                                                                            $this->logger->write("Api : importErpInvoices() : The computed TAXID is " . $taxid, 'r');
                                                                            
                                                                            if (! $taxid) {
                                                                                $taxid = $this->appsettings['STANDARDTAXRATE'];
                                                                            }
                                                                            
                                                                            if ($taxid == $this->appsettings['DEEMEDTAXRATE']) {
                                                                                $deemedflag = 'YES';
                                                                            } else {
                                                                                $deemedflag = 'NO';
                                                                            }
                                                                            
                                                                            $this->logger->write("Api : importErpInvoices() : The final TAXID is " . $taxid, 'r');
                                                                            
                                                                            $tr = new taxrates($this->db);
                                                                            $tr->getByID($taxid);
                                                                            $taxcode = $tr->code;
                                                                            $taxname = $tr->name;
                                                                            $taxcategory = $tr->category;
                                                                            $taxdisplaycategory = $tr->displayCategoryCode;
                                                                            $taxdescription = $tr->description;
                                                                            $rate = $tr->rate ? $tr->rate : 0;
                                                                            
                                                                            $this->logger->write("Api : importErpInvoices() : unit: " . $unit, 'r');
                                                                            
                                                                            if (strtoupper(trim($pricevatinclusive)) == 'YES') {
                                                                                // Use the figures as they come from the ERP
                                                                                $total = ($qty * $unit); // ??
                                                                                
                                                                                // $discount = ($discountpct/100) * $total; //discount is already calculated by QB
                                                                                
                                                                                /**
                                                                                 * Modification Date: 2021-01-26
                                                                                 * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                                 */
                                                                                // $gross = $total - $discount;
                                                                                $gross = $total;
                                                                                
                                                                                $discount = (- 1) * $discount;
                                                                                
                                                                                $tax = ($gross / ($rate + 1)) * $rate; // ??
                                                                                
                                                                                $net = $gross - $tax;
                                                                            } elseif (strtoupper(trim($pricevatinclusive)) == 'NO') {
                                                                                // Manually calculate figures
                                                                                $this->logger->write("Api : importErpInvoices() : Rebasing the prices", 'r');
                                                                                
                                                                                if ($rate > 0) {
                                                                                    $unit = $unit * ($rate + 1);
                                                                                }
                                                                                
                                                                                $total = ($qty * $unit); // ??
                                                                                
                                                                                // $discount = ($discountpct/100) * $total;
                                                                                
                                                                                /**
                                                                                 * Modification Date: 2021-01-26
                                                                                 * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                                 */
                                                                                // $gross = $total - $discount;
                                                                                $gross = $total;
                                                                                
                                                                                $discount = (- 1) * $discount;
                                                                                
                                                                                $tax = ($gross / ($rate + 1)) * $rate; // ??
                                                                                
                                                                                $net = $gross - $tax;
                                                                            }
                                                                            
                                                                            /**
                                                                             * Over-ride tax, if the tax payer is not VAT registered
                                                                             */
                                                                            if ($this->vatRegistered == 'N') {
                                                                                $tax = 0;
                                                                                $taxcategory = NULL;
                                                                                $taxcode = NULL;
                                                                            }
                                                                            
                                                                            $netamount = $netamount + $net;
                                                                            $taxamount = $taxamount + $tax;
                                                                            
                                                                            $grossamount = $grossamount + $gross;
                                                                            $itemcount = $itemcount + 1;
                                                                            
                                                                            if ($discount == 0) {
                                                                                $discountflag = 'NO';
                                                                            } else {
                                                                                $discountflag = 'YES';
                                                                            }
                                                                            
                                                                            $goods[] = array(
                                                                                'groupid' => NULL,
                                                                                'item' => $product->name,
                                                                                'itemcode' => $product->code,
                                                                                'qty' => $qty,
                                                                                'unitofmeasure' => $product->measureunit,
                                                                                'unitprice' => $unit,
                                                                                'total' => $total,
                                                                                'taxid' => $taxid,
                                                                                'taxrate' => $rate,
                                                                                'tax' => $tax,
                                                                                'discounttotal' => $discount,
                                                                                'discounttaxrate' => $rate,
                                                                                'discountpercentage' => $discountpct,
                                                                                'ordernumber' => NULL,
                                                                                'discountflag' => trim($discountflag) == 'NO' ? '2' : '1',
                                                                                'deemedflag' => (strtoupper(trim($deemedflag)) == 'NO' ? '2' : '1'),
                                                                                'exciseflag' => NULL,
                                                                                'categoryid' => NULL,
                                                                                'categoryname' => NULL,
                                                                                'goodscategoryid' => $product->commoditycategorycode,
                                                                                'goodscategoryname' => NULL,
                                                                                'exciserate' => NULL,
                                                                                'exciserule' => NULL,
                                                                                'excisetax' => NULL,
                                                                                'pack' => NULL,
                                                                                'stick' => NULL,
                                                                                'exciseunit' => NULL,
                                                                                'excisecurrency' => NULL,
                                                                                'exciseratename' => NULL,
                                                                                'taxdisplaycategory' => $taxdisplaycategory,
                                                                                'taxcategory' => $taxcategory,
                                                                                'taxcategoryCode' => $taxcode,
                                                                                'unitofmeasurename' => $measureunit->name
                                                                            );
                                                                            
                                                                            $this->logger->write("Api : importErpInvoices() : The TAXCODE is " . $taxcode, 'r');
                                                                            
                                                                            if ($this->vatRegistered == 'Y') {
                                                                                $taxes[] = array(
                                                                                    'discountflag' => trim($discountflag) == 'NO' ? '2' : '1',
                                                                                    'discounttotal' => $discount,
                                                                                    'discounttaxrate' => $rate,
                                                                                    'discountpercentage' => $discountpct,
                                                                                    'd_netamount' => NULL,
                                                                                    'd_taxamount' => NULL,
                                                                                    'd_grossamount' => NULL,
                                                                                    'groupid' => NULL,
                                                                                    'goodid' => NULL,
                                                                                    'taxdisplaycategory' => $taxdisplaycategory,
                                                                                    'taxcategory' => $taxcategory,
                                                                                    'taxcategoryCode' => $taxcode,
                                                                                    'netamount' => $net,
                                                                                    'taxrate' => $rate,
                                                                                    'taxamount' => $tax,
                                                                                    'grossamount' => $gross,
                                                                                    'exciseunit' => NULL,
                                                                                    'excisecurrency' => NULL,
                                                                                    'taxratename' => $taxname,
                                                                                    'taxdescription' => $taxdescription
                                                                                );
                                                                            }
                                                                        }
                                                                    } // foreach($elem->Line as $items)
                                                                } // if(isset($elem->Line))
                                                                  
                                                                $jj = 0;
                                                                
                                                                foreach ($mapped_fees as $m_obj) {
                                                                    $this->logger->write("Api : importErpInvoices() : Adding fees to the tax array", 'r');
                                                                    $this->logger->write("Api : importErpInvoices() : The amount is: " . $m_obj['amount'], 'r');
                                                                    
                                                                    $tr = new taxrates($this->db);
                                                                    $tr->getByCode($m_obj['feecode']);
                                                                    $taxcode = $tr->code;
                                                                    $taxname = $tr->name;
                                                                    $taxcategory = $tr->category;
                                                                    $taxdescription = $tr->description;
                                                                    $taxdisplaycategory = $tr->displayCategoryCode;
                                                                    
                                                                    if((float)$m_obj['amount'] <> 0){
                                                                        $taxes[] = array(
                                                                            'discountflag' => '1',
                                                                            'discounttotal' => NULL,
                                                                            'discounttaxrate' => NULL,
                                                                            'discountpercentage' => NULL,
                                                                            'd_netamount' => NULL,
                                                                            'd_taxamount' => NULL,
                                                                            'd_grossamount' => NULL,
                                                                            'groupid' => NULL,
                                                                            'goodid' => NULL,
                                                                            'taxdisplaycategory' => $taxdisplaycategory,
                                                                            'taxcategory' => $taxcategory,
                                                                            'taxcategoryCode' => $taxcode,
                                                                            'netamount' => 0,
                                                                            'taxrate' => $m_obj['amount'],
                                                                            'taxamount' => $m_obj['amount'],
                                                                            'grossamount' => $m_obj['amount'],
                                                                            'exciseunit' => NULL,
                                                                            'excisecurrency' => NULL,
                                                                            'taxratename' => $taxname,
                                                                            'taxdescription' => $taxdescription
                                                                        );
                                                                        
                                                                        //We should reset the amount to 0 in preparation for the next iteration.
                                                                        $this->logger->write("Api : importErpInvoices() : Resetting the amount to 0", 'r');
                                                                        $mapped_fees[$jj]['amount'] = 0;
                                                                        $jj = $jj + 1;
                                                                    }
                                                                    else {
                                                                        $jj = $jj + 1;
                                                                        continue;
                                                                    }
                                                                }
                                                                
                                                                $this->logger->write("Api : importErpInvoices() : Discount App Status: " . $discountAppStatus, 'r');
                                                                $this->logger->write("Api : importErpInvoices() : Discount App Balance: " . $discountAppBalance, 'r');
                                                                $this->logger->write("Api : importErpInvoices() : Discount App Percentage: " . $discountAppPct, 'r');
                                                                
                                                                if ($discountAppStatus == 1) {
                                                                    $this->logger->write("Api : importErpInvoices() : Applying Discounts", 'r');
                                                                    $this->logger->write("Api : importErpInvoices() : Customer Type " . $customer->type, 'r');
                                                                    list ($goods, $taxes) = $this->util->applyDiscount($goods, $taxes, $discountAppBalance, $customer->type, $customer->tin, NULL);
                                                                }
                                                                
                                                                if (isset($elem->TxnTaxDetail)) {
                                                                    $TxnTaxCodeRef = $elem->TxnTaxDetail->TxnTaxCodeRef;
                                                                    $TotalTax = $elem->TxnTaxDetail->TotalTax;
                                                                    
                                                                    $this->logger->write("Api : importErpInvoices() : Tax Ref: " . $TxnTaxCodeRef, 'r');
                                                                    
                                                                    if (isset($elem->TxnTaxDetail->TaxLine)) {
                                                                        $TaxAmount = $elem->TxnTaxDetail->TaxLine->Amount;
                                                                        $this->logger->write("Api : importErpInvoices() : Total Tax Amount: " . $TaxAmount, 'r');
                                                                        
                                                                        if (isset($elem->TxnTaxDetail->TaxLine->DetailType)) {
                                                                            if (strtoupper($elem->TxnTaxDetail->TaxLine->DetailType) == 'TAXLINEDETAIL') {
                                                                                if (isset($elem->TxnTaxDetail->TaxLine->TaxLineDetail)) {
                                                                                    $TaxRateRef = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->TaxRateRef;
                                                                                    $TaxPercentBased = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->PercentBased;
                                                                                    $TaxPercent = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->TaxPercent;
                                                                                    $NetAmountTaxable = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->NetAmountTaxable;
                                                                                }
                                                                                
                                                                                $this->logger->write("Api : importErpInvoices() : Tax Line Net Amount: " . $NetAmountTaxable, 'r');
                                                                            }
                                                                        }
                                                                    }
                                                                } // if(isset($elem->TxnTaxDetail))
                                                                
                                                                if (isset($elem->CustomField)) {
                                                                    foreach ($elem->CustomField as $fields) {
                                                                        $FieldDefinitionId = $fields->DefinitionId;
                                                                        $FieldName = $fields->Name;
                                                                        $FieldType = $fields->Type; // StringType
                                                                        $FieldStringValue = $fields->StringValue;
                                                                        
                                                                        $this->logger->write("Api : importErpInvoices() : Customer Field Name: " . $FieldName, 'r');
                                                                    } // foreach($elem->CustomField as $items)
                                                                } // if(isset($elem->CustomField))
                                                                
                                                                $invoicedetails['operator'] = $this->username_u;
                                                                $invoicedetails['currency'] = $this->util->getcurrency(trim($CurrencyRef));
                                                                $invoicedetails['SyncToken'] = $SyncToken;
                                                                $invoicedetails['issueddate'] = $TxnDate;
                                                                $invoicedetails['issuedtime'] = $TxnDate;
                                                                $invoicedetails['issueddatepdf'] = $TxnDate;
                                                                $invoicedetails['itemcount'] = $itemcount;
                                                                
                                                                $invoicedetails['netamount'] = $netamount;
                                                                $invoicedetails['taxamount'] = $taxamount;
                                                                $invoicedetails['grossamount'] = $grossamount;
                                                                $invoicedetails['origrossamount'] = 0;
                                                                
                                                                $invoicedetails['remarks'] = "The invoice DocNumber " . $DocNumber . " and Id " . $InvoiceId . " uploaded using the QBO API";
                                                                $this->logger->write("Api : importErpInvoices() : The Sync Token is " . $SyncToken, 'r');
                                                                
                                                                if ($InvoiceId) {
                                                                    $invoice->getByErpId($InvoiceId);
                                                                    $inv_status = NULL;
                                                                    
                                                                    if ($invoice->dry()) {
                                                                        $this->logger->write("Api : importErpInvoices() : The invoice does not exist", 'r');
                                                                        $inv_status = $this->util->createinvoice($invoicedetails, $goods, $taxes, $buyer, $this->userid_u);
                                                                        
                                                                        if ($inv_status) {
                                                                            $this->logger->write("Api : importErpInvoices() : The invoice " . $DocNumber . " was created.", 'r');
                                                                        } else {
                                                                            $this->logger->write("Api : importErpInvoices() : The invoice " . $DocNumber . " was NOT created.", 'r');
                                                                        }
                                                                    } else {
                                                                        $this->logger->write("Api : importErpInvoices() : The invoice exists", 'r');
                                                                        $invoicedetails['id'] = $invoice->id;
                                                                        $invoicedetails['gooddetailgroupid'] = $invoice->gooddetailgroupid;
                                                                        $invoicedetails['taxdetailgroupid'] = $invoice->taxdetailgroupid;
                                                                        $invoicedetails['paymentdetailgroupid'] = $invoice->paymentdetailgroupid;
                                                                        
                                                                        if ($invoice->einvoiceid) {
                                                                            $this->logger->write("Api : importErpInvoices() : The invoice " . $DocNumber . " is already fiscalized.", 'r');
                                                                        } else {
                                                                            $this->logger->write("Api : importErpInvoices() : The invoice " . $DocNumber . " is NOT fiscalized.", 'r');
                                                                            
                                                                            $inv_status = $this->util->updateinvoice($invoicedetails, $goods, $taxes, $buyer, $this->userid_u);
                                                                            
                                                                            if ($inv_status) {
                                                                                $this->logger->write("Api : importErpInvoices() : The invoice " . $DocNumber . " was updated.", 'r');
                                                                            } else {
                                                                                $this->logger->write("Api : importErpInvoices() : The invoice " . $DocNumber . " was NOT updated.", 'r');
                                                                            }
                                                                        }
                                                                    }
                                                                    
                                                                    // TRY uploading here.
                                                                    if ($inv_status) {
                                                                        $this->logger->write("Api : importErpInvoices() : Uploading the invoice " . $DocNumber . " into EFRIS.", 'r');
                                                                        // **********************START UPLOAD***************
                                                                        $invoice->getByErpId($InvoiceId); // Refresh Invoice
                                                                        
                                                                        if ($invoice->einvoiceid) {
                                                                            $this->logger->write("Api : importErpInvoices() : This invoice is already uploaded", 'r');
                                                                            $this->message = 'This invoice is already uploaded.';
                                                                            $this->code = '005';
                                                                            
                                                                            // $this->util->createerpauditlog($this->userid_u, $permission, NULL, NULL, NULL, NULL, NULL, $invoice->erpinvoiceno, $invoice->erpinvoiceid, NULL, $this->code, $this->message);
                                                                        } else {
                                                                            $data = $this->util->uploadinvoice($this->userid_u, $invoice->id, $this->vatRegistered); // will return JSON.
                                                                            
                                                                            $data = json_decode($data, true);
                                                                            // $this->logger->write("Api : importErpInvoices() : The response content is: " . $data, 'r');
                                                                            // var_dump($data);
                                                                            
                                                                            if (isset($data['returnCode'])) {
                                                                                $this->logger->write("Api : importErpInvoices() : The operation to upload the invoice not successful. The error message is " . $data['returnMessage'], 'r');
                                                                                $this->message = "The operation to upload the invoice not successful. The error message is " . $data['returnMessage'];
                                                                                //$this->code = '008';
                                                                                $this->code = $data['returnCode'];
                                                                                
                                                                                // $this->util->createerpauditlog($this->userid_u, $permission, NULL, NULL, NULL, NULL, NULL, $invoice->erpinvoiceno, $invoice->erpinvoiceid, NULL, $this->code, $this->message);
                                                                            } else {
                                                                                if (isset($data['basicInformation'])) {
                                                                                    $antifakeCode = $data['basicInformation']['antifakeCode']; // 32966911991799104051
                                                                                    $invoiceId = $data['basicInformation']['invoiceId']; // 3257429764295992735
                                                                                    $invoiceNo = $data['basicInformation']['invoiceNo']; // 3120012276043
                                                                                    
                                                                                    $issuedDate = $data['basicInformation']['issuedDate']; // 18/09/2020 17:14:12
                                                                                    $issuedDate = str_replace('/', '-', $issuedDate); // Replace / with -
                                                                                    $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                                                                                    
                                                                                    $issuedTime = $data['basicInformation']['issuedDate']; // 18/09/2020 17:14:12
                                                                                    $issuedTime = str_replace('/', '-', $issuedTime); // Replace / with -
                                                                                    $issuedTime = date("Y-m-d H:i:s", strtotime($issuedTime));
                                                                                    
                                                                                    $issuedDatePdf = $data['basicInformation']['issuedDatePdf']; // 318/09/2020 17:14:12
                                                                                    $issuedDatePdf = str_replace('/', '-', $issuedDatePdf); // Replace / with -
                                                                                    $issuedDatePdf = date("Y-m-d H:i:s", strtotime($issuedDatePdf));
                                                                                    
                                                                                    $oriInvoiceId = $data['basicInformation']['oriInvoiceId']; // 1
                                                                                    $isInvalid = $data['basicInformation']['isInvalid']; // 1
                                                                                    $isRefund = $data['basicInformation']['isRefund']; // 1
                                                                                    
                                                                                    $deviceNo = $data['basicInformation']['deviceNo'];
                                                                                    $invoiceIndustryCode = $data['basicInformation']['invoiceIndustryCode'];
                                                                                    $invoiceKind = $data['basicInformation']['invoiceKind'];
                                                                                    $invoiceType = $data['basicInformation']['invoiceType'];
                                                                                    $isBatch = $data['basicInformation']['isBatch'];
                                                                                    $operator = $data['basicInformation']['operator'];
                                                                                    
                                                                                    $currencyRate = $data['basicInformation']['currencyRate'];
                                                                                    
                                                                                    try {
                                                                                        $this->db->exec(array(
                                                                                            'UPDATE tblinvoices SET antifakeCode = "' . $antifakeCode . '", einvoiceid = "' . $invoiceId . '", einvoicenumber = "' . $invoiceNo . '", issueddate = "' . $issuedDate . '", issueddatepdf = "' . $issuedDatePdf . '", oriinvoiceid = "' . $oriInvoiceId . '", isinvalid = ' . $isInvalid . ', isrefund = ' . $isRefund . ', issuedtime = "' . $issuedTime . '", deviceno = "' . $deviceNo . '", invoiceindustrycode = ' . $invoiceIndustryCode . ', invoicekind = ' . $invoiceKind . ', invoicetype = ' . $invoiceType . ', isbatch = "' . $isBatch . '", operator = "' . $operator . '", currencyRate = ' . $currencyRate . ', modifieddt = NOW(), modifiedby = ' . $this->userid_u . ' WHERE id = ' . $invoice->id
                                                                                        ));
                                                                                        
                                                                                        // $this->logger->write($this->db->log(TRUE), 'r');
                                                                                    } catch (Exception $e) {
                                                                                        $this->logger->write("Api : importErpInvoices() : Failed to insert into the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                                                                    }
                                                                                }
                                                                                
                                                                                if (isset($data['sellerDetails'])) {
                                                                                    /*
                                                                                     * "address":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                                                                                     * "branchCode":"00",
                                                                                     * "branchId":"912550336846912433",
                                                                                     * "branchName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                                                                     * "businessName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                                                                     * "emailAddress":"editesti06@gmail.com",
                                                                                     * "legalName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                                                                     * "linePhone":"256787695360",
                                                                                     * "mobilePhone":"256782356088",
                                                                                     * "ninBrn":"/80020002851201",
                                                                                     * "placeOfBusiness":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                                                                                     * "referenceNo":"21",
                                                                                     * "tin":"1017918269"
                                                                                     */
                                                                                    
                                                                                    $branchCode = ! isset($data['sellerDetails']['branchCode']) ? '' : $data['sellerDetails']['branchCode'];
                                                                                    $branchId = ! isset($data['sellerDetails']['branchId']) ? '' : $data['sellerDetails']['branchId'];
                                                                                    $referenceNo = ! isset($data['sellerDetails']['referenceNo']) ? '' : $data['sellerDetails']['referenceNo'];
                                                                                    
                                                                                    try {
                                                                                        $this->db->exec(array(
                                                                                            'UPDATE tblinvoices SET branchCode = "' . $branchCode . '", branchId = "' . $branchId . '", erpinvoiceno = "' . addslashes($referenceNo) . '", modifieddt = NOW(), modifiedby = ' . $this->userid_u . ' WHERE id = ' . $invoice->id
                                                                                        ));
                                                                                        
                                                                                        // $this->logger->write($this->db->log(TRUE), 'r');
                                                                                    } catch (Exception $e) {
                                                                                        $this->logger->write("Api : importErpInvoices() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                                                                    }
                                                                                }
                                                                                
                                                                                if (isset($data['summary'])) {
                                                                                    $grossAmount = $data['summary']['grossAmount']; // 832000
                                                                                    $itemCount = $data['summary']['itemCount']; // 1
                                                                                    $netAmount = $data['summary']['netAmount']; // 705084.75
                                                                                    $qrCode = $data['summary']['qrCode']; // 020000001149IC1200122760430004F588000000C1A8450A20A021D534A1000121462A1000094968~MM INTERGRATED STEEL MILLS (UGANDA) LIMITED~Ediomu & Company~Kiboko Galv Corr. Sheet 36G
                                                                                    $taxAmount = $data['summary']['taxAmount']; // 126915.25
                                                                                    $modeCode = $data['summary']['modeCode']; // 0
                                                                                    
                                                                                    $mode = new modes($this->db);
                                                                                    $mode->getByCode($modeCode);
                                                                                    $modeName = $mode->name; // online
                                                                                    
                                                                                    $f = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
                                                                                    $grossAmountWords = $f->format($grossAmount); // two million
                                                                                    
                                                                                    try {
                                                                                        $this->db->exec(array(
                                                                                            'UPDATE tblinvoices SET grossamount = ' . $grossAmount . ', itemcount = ' . $itemCount . ', netamount = ' . $netAmount . ', einvoicedatamatrixcode = "' . addslashes($qrCode) . '", taxamount = ' . $taxAmount . ', modecode = "' . $modeCode . '", modename = "' . $modeName . '", grossamountword = "' . addslashes($grossAmountWords) . '", modifieddt = NOW(), modifiedby = ' . $this->userid_u . ' WHERE id = ' . $invoice->id
                                                                                        ));
                                                                                        // $this->logger->write($this->db->log(TRUE), 'r');
                                                                                    } catch (Exception $e) {
                                                                                        $this->logger->write("Api : importErpInvoices() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                                                                    }
                                                                                }
                                                                                
                                                                                $this->message = 'The operation to upload the invoice was successful';
                                                                                $this->code = '000';
                                                                            }
                                                                        }
                                                                        // **********************END UPLOAD*****************
                                                                        
                                                                        $this->util->createerpauditlog($this->userid_u, $permission, NULL, NULL, NULL, NULL, NULL, $invoice->erpinvoiceno, $invoice->erpinvoiceid, NULL, $this->code, $this->message);
                                                                    } else {
                                                                        $this->logger->write("Api : importErpInvoices() : The invoice " . $DocNumber . " will not be uploaded into EFRIS.", 'r');
                                                                    }
                                                                } else {
                                                                    $this->logger->write("Api : importErpInvoices() : The invoice has no Id.", 'r');
                                                                    $this->message = 'The invoice has no Id.';
                                                                    $this->code = '999';
                                                                }
                                                            } catch (Exception $e) {
                                                                $this->logger->write("Api : importErpInvoices() : There was an error when processing invoice " . $elem->DocNumber . ". The error is " . $e->getMessage(), 'r');
                                                            }
                                                            
                                                            // Empty/Reset some variables?
                                                            unset($goods);
                                                            unset($taxes);
                                                            $deemedflag = 'NO';
                                                            $discountflag = 'NO';
                                                            $netamount = 0;
                                                            $taxamount = 0;
                                                            $grossamount = 0;
                                                            $itemcount = 0;
                                                            $taxid = NULL;
                                                            $taxcode = NULL;
                                                            $taxname = NULL;
                                                            $taxcategory = NULL;
                                                            $taxdisplaycategory = NULL;
                                                            $taxdescription = NULL;
                                                            $rate = 0;
                                                            $qty = 0;
                                                            $unit = 0;
                                                            $discountpct = 0;
                                                            $total = 0;
                                                            $discount = 0;
                                                            $gross = 0;
                                                            $discount = 0;
                                                            $tax = 0;
                                                            $net = 0;
                                                            $amount = 0;
                                                            //unset($buyer);
                                                            //unset($invoicedetails);
                                                            $discountAppStatus = 0;
                                                            $discountAppBalance = 0;
                                                            $discountAppPct = 0;
                                                            $InvStatus = NULL;
                                                            
                                                            $CustomerRef = NULL;
                                                            $DocNumber = NULL;
                                                            $CurrencyRef = NULL;
                                                            $TxnDate = NULL;
                                                            $InvoiceId = NULL;
                                                            $SyncToken = NULL;
                                                            $TxnDate = NULL;
                                                        } // foreach
                                                    }
                                                } else {
                                                    $this->logger->write("Api : importErpInvoices() : The operation to download ERP invoices did not return records.", 'r');
                                                    $this->message = 'The operation to download ERP invoices did not return records';
                                                    $this->code = '999';
                                                }
                                            }
                                            
                                            $this->logger->write("Api : importErpInvoices() : The operation to download ERP invoices was successful.", 'r');
                                            $this->message = 'The operation to download ERP invoices was successful.';
                                            $this->code = '000';
                                        } else {
                                            $this->logger->write("Api : importErpInvoices() : The operation to download ERP invoices was not successful. Please connect to ERP first.", 'r');
                                            $this->message = 'The operation to download ERP invoices was not successful. Please connect to ERP first.';
                                            $this->code = '003';
                                        }
                                    } catch (Exception $e) {
                                        $this->logger->write("Api : importErpInvoices() : The operation to download ERP invoices was not successful. The error is: " . $e->getMessage(), 'r');
                                        $this->message = 'The operation to download ERP invoices was not successful. Reconnect to the ERP OR Contact your System Administrator.';
                                        $this->code = '004';
                                    }
                                } else {
                                    $this->logger->write("Api : importErpInvoices() : The integrated ERP is unknown.", 'r');
                                    $this->message = 'The access token is NOT set in the database. Please manually login.';
                                    $this->code = '999';
                                }
                            } else {
                                $this->logger->write("Api : importErpInvoices() : We are unable to indentify the currently integrated ERP.", 'r');
                                $this->message = 'The access token is NOT set in the database. Please manually login.';
                                $this->code = '999';
                            }
                        }
                    } else {
                        $this->logger->write("Api : importErpInvoices() : The access token expired. Please manually login.", 'r');
                        $this->message = 'The access token expired. Please manually login';
                        $this->code = '999';
                    }
                } else {
                    $this->logger->write("Api : importErpInvoices() : The access token is NOT set. Please manually login.", 'r');
                    $this->message = 'The access token is NOT set. Please manually login';
                    $this->code = '999';
                }
            }
            
            if ($this->code !== '000') {
                $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The automated task which processes invoices failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
                
                $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
            }
        }
        
        $this->response = array(
            "response" => array(
                "responseCode" => $this->code,
                "responseMessage" => $this->message
            ),
            "data" => array()
        );
        
        $len = sizeof($this->response);
        header("CONTENT-LENGTH:" . $len);
        // print $this->response;
        die(json_encode($this->response));
        return;
    }
    
    /**
     * NULL
     *
     * @name updateErpInvoices
     * @return NULL
     * @param
     *            NULL
     */
    function updateErpInvoices()
    {
        $operation = NULL; // tblevents
        $permission = 'SYNCINVOICES'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications
        
        if ($this->errorcode) {
            $this->message = $this->errormessage;
            $this->code = $this->errorcode;
            
            $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The attempt to access the API failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
            
            $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
        } else {
            $this->logger->write("Api : updateErpInvoices() : Initiating QB Invoice Importation!", 'r');
            
            $inactivityPeriod = 0;
            $sessionAccessToken = NULL;
            $sessionRefreshToken = NULL;
            $sessionAccessTokenExpiry = NULL;
            
            // Retrieve the access token from the database.
            $token_check = new DB\SQL\Mapper($this->db, 'tblsettings');
            $token_check->load(array(
                'TRIM(code)=?',
                'QBACCESSTOKEN'
            ));
            
            if ($token_check->dry()) {
                $this->logger->write("Api : updateErpInvoices() : The access token is NOT set in the database. Please manually login.", 'r');
                $this->message = 'The access token is NOT set in the database. Please manually login.';
                $this->code = '999';
                // Return ERROR message
                // SEND an email.
            } else {
                $this->logger->write("Api : updateErpInvoices() : The access token was retrieved successfuly from the database", 'r');
                $sessionAccessToken = trim($token_check->value);
                
                // $this->logger->write("Api : updateErpInvoices() : The access token is " . $sessionAccessToken, 'r');
                
                // set the access token using the auth object
                if ($sessionAccessToken !== null) {
                    
                    $sessionRefreshToken = $this->appsettings['QBREFRESHTOKEN'];
                    
                    if ($this->appsettings['QBSESSIONACCESSTOKENEXPIRY']) {
                        //$sessionAccessTokenExpiry = $this->appsettings['QBSESSIONACCESSTOKENEXPIRY'];
                        $sessionAccessTokenExpiry =  str_replace('/', '-', $this->appsettings['QBSESSIONACCESSTOKENEXPIRY']);
                    } else {
                        $sessionAccessTokenExpiry = date('Y-m-d H:i:s', strtotime('-1 days'));
                    }
                    
                    $this->logger->write("Api : updateErpInvoices() : The refresh token is " . $sessionRefreshToken, 'r');
                    $this->logger->write("Api : updateErpInvoices() : The access token expiry is " . $sessionAccessTokenExpiry, 'r');
                    
                    $startDt = new DateTime(date('Y-m-d H:i:s'));
                    $endDt = new DateTime($sessionAccessTokenExpiry);
                    
                    $inactivityPeriod = $startDt->getTimestamp() - $endDt->getTimestamp();
                    
                    $this->logger->write("Api : updateErpInvoices() : The current time is " . date('Y-m-d H:i:s'), 'r');
                    $this->logger->write("Api : updateErpInvoices() : The inactivity period is " . $inactivityPeriod, 'r');
                    
                    if ($inactivityPeriod < 0) {
                        $tcsdetails = new tcsdetails($this->db);
                        $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
                        
                        $companydetails = new organisations($this->db);
                        $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);
                        
                        $devicedetails = new devices($this->db);
                        $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
                        
                        $endDate = date('Y-m-d', strtotime('+1 days')); // date('Y-m-d H:i:s', strtotime('+3 days'));
                        $startDate = date('Y-m-d', strtotime("-" . $this->appsettings['ERPPROCPERIOD'] . " days")); // date('Y-m-d H:i:s', strtotime('-3 days'));
                        $docType = $this->appsettings['INVOICEERPDOCTYPE'];
                        
                        $endTime = date('H:i:s', strtotime('+1 seconds'));
                        $startTime = date('H:i:s', strtotime("-" . $this->appsettings['ERPPROCPERIODTIME'] . " seconds"));
                        
                        $this->logger->write("Api : updateErpInvoices() : startDate: " . $startDate, 'r');
                        $this->logger->write("Api : updateErpInvoices() : endDate: " . $endDate, 'r');
                        $this->logger->write("Api : updateErpInvoices() : docType: " . $docType, 'r');
                        
                        $this->logger->write("Api : updateErpInvoices() : startTime: " . $startTime, 'r');
                        $this->logger->write("Api : updateErpInvoices() : endTime: " . $endTime, 'r');
                        
                        if ($this->platformMode == 'ERP') {
                            $this->logger->write("Api : updateErpInvoices() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                            $this->message = 'The platform is not integrated. It is running as an abriged ERP.';
                            $this->code = '001';
                        } else {
                            $this->logger->write("Api : updateErpInvoices() : The platform is integrated.", 'r');
                            
                            if ($this->integratedErp) {
                                /**
                                 * Check on integrated ERP type
                                 */
                                $this->logger->write("Api : updateErpInvoices() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                                
                                if (strtoupper($this->integratedErp) == 'QBO') {
                                    $this->logger->write("Api : updateErpInvoices() : The integrated ERP is Quicbooks Online.", 'r');
                                    
                                    try{
                                        //$temp = $this->db->exec(array('SELECT id, erpinvoiceid, erpinvoiceno FROM tblinvoices WHERE erpUpdateFlag <> 1 AND einvoicenumber IS NOT NULL AND modifieddt >= "' . $startDate . ' ' . $startTime . '" AND modifieddt <= "' . $endDate . ' ' . $endTime . '" AND docTypeCode = COALESCE(' . $docType . ', NULL) ORDER BY modifieddt DESC'));
                                        $temp = $this->db->exec(array('SELECT id, erpinvoiceid, erpinvoiceno FROM tblinvoices WHERE (length(einvoicenumber) < 1 or erpUpdateFlag = 0) AND einvoicenumber IS NOT NULL AND modifieddt >= "' . $startDate . ' ' . $startTime . '" AND modifieddt <= "' . $endDate . ' ' . $endTime . '" AND docTypeCode = COALESCE(' . $docType . ', NULL) ORDER BY modifieddt DESC'));
										//$temp = $this->db->exec(array('SELECT id, erpinvoiceid, erpinvoiceno FROM tblinvoices WHERE erpUpdateFlag <> 1 AND einvoicenumber IS NOT NULL AND modifieddt >= "' . $startDate . ' ' . $startTime . '" AND modifieddt <= "' . $endDate . ' ' . $endTime . '" AND docTypeCode = COALESCE(' . $docType . ', NULL) ORDER BY modifieddt DESC LIMIT 0, 40'));
                                        $this->logger->write($this->db->log(TRUE), 'r');
                                    } catch (Exception $e) {
                                        $this->logger->write("Api : updateErpInvoices() : The operation to retrieve invoices was not successfull. The error messages is " . $e->getMessage(), 'r');
                                        //$temp = array();
                                    }
                                    
                                    if (!empty($temp)) {
                                        $invoice = new invoices($this->db);
                                        
                                        foreach ($temp as $obj) {
                                            $id = $obj['id'];
                                            $invoice->getByID($id);
                                            $this->logger->write("Api : updateErpInvoices() : The invoice id is " . $id, 'r');
                                            
                                            $data = array();
                                            $data_ = array();
                                            $max_id = 0;
                                            $max_response = '';
                                            
                                            $qry = 'SELECT * FROM Invoice';
                                            
                                            if ($invoice->erpinvoiceno) {
                                                $qry = $qry . " Where DocNumber = '" . $invoice->erpinvoiceno . "'";
                                                
                                                $this->logger->write("Api : updateErpInvoices() : The query is: " . $qry, 'r');
                                                
                                                
                                                try {
                                                    if ($sessionAccessToken !== null) {
                                                        // Create SDK instance
                                                        $authMode = $this->appsettings['QBAUTH_MODE'];
                                                        $ClientID = $this->appsettings['QBCLIENT_ID'];
                                                        $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                                        $baseUrl = $this->appsettings['QBBASE_URL'];
                                                        $QBORealmID = $this->appsettings['QBREALMID'];
                                                        
                                                        $dataService = DataService::Configure(array(
                                                            'auth_mode' => $authMode,
                                                            'ClientID' => $ClientID,
                                                            'ClientSecret' => $ClientSecret,
                                                            'baseUrl' => $baseUrl,
                                                            'refreshTokenKey' => $sessionRefreshToken,
                                                            'QBORealmID' => $QBORealmID,
                                                            'accessTokenKey' => $sessionAccessToken
                                                        ));
                                                        
                                                        $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                                        $dataService->throwExceptionOnError(true);
                                                        
                                                        $invoices = $dataService->Query($qry);
                                                        
                                                        $error = $dataService->getLastError();
                                                        
                                                        if ($error) {
                                                            $this->logger->write("Api : updateErpInvoice() : The operation to update ERP invoices was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                                            $this->message = 'The operation to update ERP invoices was not successful.';
                                                            $this->code = '3001';
                                                        } else {
                                                            if(isset($invoices)){
                                                                if(!empty($invoices) && sizeof($invoices) == 1){
                                                                    $theInvoice = current($invoices);
                                                                    
                                                                    $this->logger->write("Api : updateErpInvoice() : The Sync Token is " . $theInvoice->SyncToken, 'r');
                                                                    $this->logger->write("Api : updateErpInvoice() : The DocNumber is " . $theInvoice->DocNumber, 'r');
                                                                    
                                                                    /**
                                                                     * Author: Francis Lubanga
                                                                     * Date: 2024-11-19
                                                                     * Desc: Push the EFRIS response message into the status custom field.
                                                                     */
                                                                    
                                                                    //Let's get the latest response message from EFRIS
                                                                    
                                                                    
                                                                    $r = $this->db->exec(array(
                                                                        'SELECT max(id) "id" FROM tblerpauditlogs WHERE trim(description) = \'' . 'SYNCINVOICES' . '\'' . ' AND TRIM(voucherNumber) = \'' . $theInvoice->DocNumber . '\''
                                                                    ));
                                                                    
                                                                    foreach ($r as $obj) {
                                                                        $data[] = $obj;
                                                                    }
                                                                    
                                                                    $max_id = $data[0]['id'];
                                                                    
                                                                    $this->logger->write("Api : updateErpInvoice() : The max response id is " . $max_id, 'r');
                                                                    
                                                                    
                                                                    if($max_id){
                                                                        $r = $this->db->exec(array(
                                                                            'SELECT responseCode, responseMessage FROM tblerpauditlogs WHERE trim(description) = \'' . 'SYNCINVOICES' . '\'' . ' AND TRIM(voucherNumber) = \'' . $theInvoice->DocNumber . '\'' . ' AND id = ' . $max_id
                                                                        ));
                                                                        
                                                                        foreach ($r as $obj) {
                                                                            $data_[] = $obj;
                                                                        }
                                                                        
                                                                        //$max_response = $data_[0]['responseCode'] . '-' . $data_[0]['responseMessage'];
                                                                        $max_response = $data_[0]['responseCode'];
                                                                    }
                                                                    
                                                                    
                                                                    $this->logger->write("Api : updateErpInvoice() : The max response is " . $max_response, 'r');
                                                                    $this->logger->write("Api : updateErpInvoice() : The first 12 characters of the respose are: " . substr($max_response, 0, 12), 'r');
                                                                    
                                                                    if ($docType == trim($this->appsettings['INVOICEERPDOCTYPE'])) {
                                                                        $updatedInvoice = Invoice::update($theInvoice, [
                                                                            "sparse" => true,
                                                                            "SyncToken" => $theInvoice->SyncToken,
                                                                            "CustomField" => [
                                                                                [
                                                                                    "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], //Fiscal Doc. Num
                                                                                    "Type" => "StringType",
                                                                                    "StringValue" => $invoice->einvoicenumber
                                                                                ],
                                                                                [
                                                                                    "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], //Verification Co
                                                                                    "Type" => "StringType",
                                                                                    "StringValue" => $invoice->antifakecode
                                                                                ],
                                                                                [
                                                                                    "DefinitionId" => $this->appsettings['QBOAPPROVALSTATUSDEFINITIONID'], //Status
                                                                                    "Type" => "StringType",
                                                                                    "StringValue" => substr($max_response, 0, 12)
                                                                                ]
                                                                            ]
                                                                        ]);
                                                                    } elseif ($docType == trim($this->appsettings['SALESRECEIPTERPDOCTYPE'])){
                                                                        $updatedInvoice = SalesReceipt::update($theInvoice, [
                                                                            "sparse" => true,
                                                                            "SyncToken" => $theInvoice->SyncToken,
                                                                            "CustomField" => [
                                                                                [
                                                                                    "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], //Fiscal Doc. Num
                                                                                    "Type" => "StringType",
                                                                                    "StringValue" => $invoice->einvoicenumber
                                                                                ],
                                                                                [
                                                                                    "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], //Verification Co
                                                                                    "Type" => "StringType",
                                                                                    "StringValue" => $invoice->antifakecode
                                                                                ],
                                                                                [
                                                                                    "DefinitionId" => $this->appsettings['QBOAPPROVALSTATUSDEFINITIONID'], //Status
                                                                                    "Type" => "StringType",
                                                                                    "StringValue" => substr($max_response, 0, 12)
                                                                                ]
                                                                            ]
                                                                        ]);
                                                                    } else {
                                                                        $updatedInvoice = Invoice::update($theInvoice, [
                                                                            "sparse" => true,
                                                                            "SyncToken" => $theInvoice->SyncToken,
                                                                            "CustomField" => [
                                                                                [
                                                                                    "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], //Fiscal Doc. Num
                                                                                    "Type" => "StringType",
                                                                                    "StringValue" => $invoice->einvoicenumber
                                                                                ],
                                                                                [
                                                                                    "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], //Verification Co
                                                                                    "Type" => "StringType",
                                                                                    "StringValue" => $invoice->antifakecode
                                                                                ],
                                                                                [
                                                                                    "DefinitionId" => $this->appsettings['QBOAPPROVALSTATUSDEFINITIONID'], //Status
                                                                                    "Type" => "StringType",
                                                                                    "StringValue" => substr($max_response, 0, 12)
                                                                                ]
                                                                            ]
                                                                        ]);
                                                                    }
                                                                    
                                                                    $updatedResult = $dataService->Update($updatedInvoice);
                                                                    //print_r($updatedResult);
                                                                    $updatederror = $dataService->getLastError();
                                                                    
                                                                    if ($updatederror) {
                                                                        $this->logger->write("Api : updateErpInvoice() : The operation to update ERP invoices was not successful. The Response Message is: " . $updatederror->getResponseBody(), 'r');
                                                                        $this->message = 'The operation to update ERP invoices was not successful.';
                                                                        $this->code = '3002';
                                                                    }
                                                                    else {
                                                                        $this->logger->write("Api : updateErpInvoice() : The operation to update ERP invoices was successful.", 'r');
                                                                        $this->message = 'The operation to update ERP invoices was successful.';
                                                                        $this->code = '000';
                                                                        
                                                                        
                                                                        try{
                                                                            $this->db->exec(array('UPDATE tblinvoices SET erpUpdateFlag = 1, modifieddt = NOW(), modifiedby = ' . $this->userid_u . ' WHERE id = ' . $id));
                                                                            //$this->logger->write($this->db->log(TRUE), 'r');
                                                                        } catch (Exception $e) {
                                                                            $this->logger->write("Api : updateErpInvoices() : The operation to update the table tblinvoices was not successfull. The error messages is " . $e->getMessage(), 'r');
                                                                        }
                                                                    }
                                                                    
                                                                } else {
                                                                    $this->logger->write("Api : updateErpInvoice() : The operation to update ERP invoices did not return any records", 'r');
                                                                    $this->message = 'The operation to update ERP invoices did not return any records.';
                                                                    $this->code = '3004';
                                                                }
                                                            } else {
                                                                $this->logger->write("Api : updateErpInvoice() : The operation to update ERP invoices did not return any records", 'r');
                                                                $this->message = 'The operation to update ERP invoices did not return any records.';
                                                                $this->code = '3004';
                                                            }
                                                        }
                                                    } else {
                                                        $this->logger->write("Api : updateErpInvoice() : The operation to update ERP invoices was not successful. Please connect to ERP first.", 'r');
                                                        $this->message = 'The operation to update ERP invoices was not successful. Please connect to ERP first.';
                                                        $this->code = '3006';
                                                    }
                                                } catch (Exception $e) {
                                                    $this->logger->write("Api : updateErpInvoice() : The operation to update ERP invoices was not successful. The error is: " . $e->getMessage(), 'r');
                                                    $this->message = 'The operation to update ERP invoices was not successful.';
                                                    $this->code = '3007';
                                                }
                                            } else {
                                                $this->logger->write("Api : updateErpInvoice() : The invoice does not have a Document Number.", 'r');
                                                $this->message = 'The invoice does not have a Document Number.';
                                                $this->code = '3008';
                                            } 
                                        }
                                    } else {
                                        $this->logger->write("Api : updateErpInvoices() : The operation to retrieve invoices did not return anything.", 'r');
                                        $this->message = 'The operation to retrieve invoices did not return anything.';
                                        $this->code = '3009';
                                    }
                                } else {
                                    $this->logger->write("Api : updateErpInvoices() : The integrated ERP is unknown.", 'r');
                                    $this->message = 'The access token is NOT set in the database. Please manually login.';
                                    $this->code = '999';
                                }
                            } else {
                                $this->logger->write("Api : updateErpInvoices() : We are unable to indentify the currently integrated ERP.", 'r');
                                $this->message = 'The access token is NOT set in the database. Please manually login.';
                                $this->code = '999';
                            }
                        }
                    } else {
                        $this->logger->write("Api : updateErpInvoices() : The access token expired. Please manually login.", 'r');
                        $this->message = 'The access token expired. Please manually login';
                        $this->code = '999';
                    }
                } else {
                    $this->logger->write("Api : updateErpInvoices() : The access token is NOT set. Please manually login.", 'r');
                    $this->message = 'The access token is NOT set. Please manually login';
                    $this->code = '999';
                }
            }
            
            if ($this->code !== '000') {
                $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The automated task which updates invoices failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
                
                $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
            }
        }
        
        $this->response = array(
            "response" => array(
                "responseCode" => $this->code,
                "responseMessage" => $this->message
            ),
            "data" => array()
        );
        
        $len = sizeof($this->response);
        header("CONTENT-LENGTH:" . $len);
        // print $this->response;
        die(json_encode($this->response));
        return;
    }
    
    /**
     * NULL
     *
     * @name importErpSalesReceipts
     * @return NULL
     * @param
     *            NULL
     */
    function importErpSalesReceipts()
    {
        $operation = NULL; // tblevents
        $permission = 'SYNCINVOICES'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications
        
        if ($this->errorcode) {
            $this->message = $this->errormessage;
            $this->code = $this->errorcode;
            
            $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The attempt to access the API failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
            
            $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
        } else {
            $this->logger->write("Api : importErpSalesReceipts() : Initiating QB Invoice Importation!", 'r');
            
            $inactivityPeriod = 0;
            $sessionAccessToken = NULL;
            $sessionRefreshToken = NULL;
            $sessionAccessTokenExpiry = NULL;
            
            // Retrieve the access token from the database.
            $token_check = new DB\SQL\Mapper($this->db, 'tblsettings');
            $token_check->load(array(
                'TRIM(code)=?',
                'QBACCESSTOKEN'
            ));
            
            if ($token_check->dry()) {
                $this->logger->write("Api : importErpSalesReceipts() : The access token is NOT set in the database. Please manually login.", 'r');
                $this->message = 'The access token is NOT set in the database. Please manually login.';
                $this->code = '999';
                // Return ERROR message
                // SEND an email.
            } else {
                $this->logger->write("Api : importErpSalesReceipts() : The access token was retrieved successfuly from the database", 'r');
                $sessionAccessToken = trim($token_check->value);
                
                // $this->logger->write("Api : importErpSalesReceipts() : The access token is " . $sessionAccessToken, 'r');
                
                // set the access token using the auth object
                if ($sessionAccessToken !== null) {
                    
                    $sessionRefreshToken = $this->appsettings['QBREFRESHTOKEN'];
                    if ($this->appsettings['QBSESSIONACCESSTOKENEXPIRY']) {
                        //$sessionAccessTokenExpiry = $this->appsettings['QBSESSIONACCESSTOKENEXPIRY'];
                        $sessionAccessTokenExpiry =  str_replace('/', '-', $this->appsettings['QBSESSIONACCESSTOKENEXPIRY']);
                    } else {
                        $sessionAccessTokenExpiry = date('Y-m-d H:i:s', strtotime('-1 days'));
                    }
                    
                    $this->logger->write("Api : importErpSalesReceipts() : The refresh token is " . $sessionRefreshToken, 'r');
                    $this->logger->write("Api : importErpSalesReceipts() : The access token expiry is " . $sessionAccessTokenExpiry, 'r');
                    
                    $startDt = new DateTime(date('Y-m-d H:i:s'));
                    $endDt = new DateTime($sessionAccessTokenExpiry);
                    
                    $inactivityPeriod = $startDt->getTimestamp() - $endDt->getTimestamp();
                    
                    $this->logger->write("Api : importErpSalesReceipts() : The current time is " . date('Y-m-d H:i:s'), 'r');
                    $this->logger->write("Api : importErpSalesReceipts() : The inactivity period is " . $inactivityPeriod, 'r');
                    
                    if ($inactivityPeriod < 0) {
                        $tcsdetails = new tcsdetails($this->db);
                        $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
                        
                        $companydetails = new organisations($this->db);
                        $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);
                        
                        $devicedetails = new devices($this->db);
                        $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
                        
                        $endDate = date('Y-m-d', strtotime('+1 days')); // date('Y-m-d H:i:s', strtotime('+3 days'));
                        $startDate = date('Y-m-d', strtotime("-" . $this->appsettings['ERPPROCPERIOD'] . " days")); // date('Y-m-d H:i:s', strtotime('-3 days'));
                        $docType = $this->appsettings['SALESRECEIPTERPDOCTYPE'];
                        
                        $this->logger->write("Api : importErpSalesReceipts() : startDate: " . $startDate, 'r');
                        $this->logger->write("Api : importErpSalesReceipts() : endDate: " . $endDate, 'r');
                        $this->logger->write("Api : importErpSalesReceipts() : docType: " . $docType, 'r');
                        
                        if ($this->platformMode == 'ERP') {
                            $this->logger->write("Api : importErpSalesReceipts() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                            $this->message = 'The platform is not integrated. It is running as an abriged ERP.';
                            $this->code = '001';
                        } else {
                            $this->logger->write("Api : importErpSalesReceipts() : The platform is integrated.", 'r');
                            
                            if ($this->integratedErp) {
                                /**
                                 * Check on integrated ERP type
                                 */
                                $this->logger->write("Api : importErpSalesReceipts() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                                
                                if (strtoupper($this->integratedErp) == 'QBO') {
                                    $this->logger->write("Api : importErpSalesReceipts() : The integrated ERP is Quicbooks Online.", 'r');
                                    
                                    $qry = '';
                                    
                                    if ($docType == trim($this->appsettings['INVOICEERPDOCTYPE'])) {
                                        $qry = 'SELECT * FROM Invoice';
                                    } elseif ($docType == trim($this->appsettings['SALESRECEIPTERPDOCTYPE'])) {
                                        $qry = 'SELECT * FROM SalesReceipt';
                                    } else {
                                        $qry = 'SELECT * FROM Invoice';
                                    }
                                    
                                    $qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "' And Metadata.LastUpdatedTime <= '" . $endDate . "'";
                                    
                                    $this->logger->write("Api : importErpSalesReceipts() : The query is: " . $qry, 'r');
                                    
                                    try {
                                        if ($sessionAccessToken !== null) {
                                            // Create SDK instance
                                            $authMode = $this->appsettings['QBAUTH_MODE'];
                                            $ClientID = $this->appsettings['QBCLIENT_ID'];
                                            $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                            $baseUrl = $this->appsettings['QBBASE_URL'];
                                            $QBORealmID = $this->appsettings['QBREALMID'];
                                            
                                            $dataService = DataService::Configure(array(
                                                'auth_mode' => $authMode,
                                                'ClientID' => $ClientID,
                                                'ClientSecret' => $ClientSecret,
                                                'baseUrl' => $baseUrl,
                                                'refreshTokenKey' => $sessionRefreshToken,
                                                'QBORealmID' => $QBORealmID,
                                                'accessTokenKey' => $sessionAccessToken
                                            ));
                                            
                                            $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                            $dataService->throwExceptionOnError(true);
                                            
                                            $invoices = $dataService->Query($qry);
                                            
                                            $error = $dataService->getLastError();
                                            
                                            if ($error) {
                                                $this->logger->write("Api : importErpSalesReceipts() : The operation to download ERP invoices was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                                $this->message = 'The operation to download ERP invoices was not successful.';
                                                $this->code = '002';
                                            } else {
                                                // print_r($invoices);
                                                
                                                if (isset($invoices)) {
                                                    if ($invoices) {
                                                        $this->logger->write("Api : importErpSalesReceipts() : The # of invoices is: " . sizeof($invoices), 'r');
                                                        $invoice = new invoices($this->db);
                                                        $customer = new customers($this->db);
                                                        
                                                        $goods = array();
                                                        $taxes = array();
                                                        
                                                        $deemedflag = 'NO';
                                                        $discountflag = 'NO';
                                                        
                                                        $pricevatinclusive = empty($this->appsettings['PRICEVATINCLUSIVE']) ? 'NO' : strtoupper($this->appsettings['PRICEVATINCLUSIVE']); // No
                                                        
                                                        $netamount = 0;
                                                        $taxamount = 0;
                                                        $grossamount = 0;
                                                        $itemcount = 0;
                                                        
                                                        $tr = new taxrates($this->db);
                                                        $taxid = NULL;
                                                        $taxcode = NULL;
                                                        $taxname = NULL;
                                                        $taxcategory = NULL;
                                                        $taxdisplaycategory = NULL;
                                                        $taxdescription = NULL;
                                                        $rate = 0;
                                                        $qty = 0;
                                                        $unit = 0;
                                                        $discountpct = 0;
                                                        $total = 0;
                                                        $discount = 0;
                                                        $gross = 0;
                                                        $discount = 0;
                                                        $tax = 0;
                                                        $net = 0;
                                                        $amount = 0;
                                                        $product = new products($this->db);
                                                        $measureunit = new measureunits($this->db);
                                                        
                                                        $buyer = array(
                                                            'id' => NULL,
                                                            'erpcustomerid' => NULL,
                                                            'erpcustomercode' => NULL,
                                                            'tin' => NULL,
                                                            'ninbrn' => NULL,
                                                            'PassportNum' => NULL,
                                                            'legalname' => NULL,
                                                            'businessname' => NULL,
                                                            'address' => NULL,
                                                            'mobilephone' => NULL,
                                                            'linephone' => NULL,
                                                            'emailaddress' => NULL,
                                                            'placeofbusiness' => NULL,
                                                            'type' => NULL,
                                                            'citizineship' => NULL,
                                                            'countryCode' => NULL,
                                                            'sector' => NULL,
                                                            'sectorCode' => NULL,
                                                            'datasource' => 'ERP',
                                                            'status' => $this->appsettings['ACTIVECUSTOMERSTATUSID']
                                                        );
                                                        
                                                        $invoicedetails = array(
                                                            'id' => NULL,
                                                            'gooddetailgroupid' => NULL,
                                                            'taxdetailgroupid' => NULL,
                                                            'paymentdetailgroupid' => NULL,
                                                            'erpinvoiceid' => NULL,
                                                            'erpinvoiceno' => NULL,
                                                            'antifakecode' => NULL,
                                                            'deviceno' => trim($devicedetails->deviceno),
                                                            'issueddate' => NULL,
                                                            'issuedtime' => NULL,
                                                            'operator' => NULL,
                                                            'currency' => NULL,
                                                            'oriinvoiceid' => NULL,
                                                            'invoicetype' => "1",
                                                            'invoicekind' => ($this->vatRegistered == 'Y') ? "1" : "2",
                                                            'datasource' => $this->appsettings['DEFAULTDATASOURCE'],
                                                            'invoiceindustrycode' => $this->appsettings['DEFAULTINVOICEINDUSTRY'],
                                                            'einvoiceid' => NULL,
                                                            'einvoicenumber' => NULL,
                                                            'einvoicedatamatrixcode' => NULL,
                                                            'isbatch' => '0',
                                                            'netamount' => NULL,
                                                            'taxamount' => NULL,
                                                            'grossamount' => NULL,
                                                            'origrossamount' => NULL,
                                                            'itemcount' => NULL,
                                                            'modecode' => NULL,
                                                            'modename' => NULL,
                                                            'remarks' => NULL,
                                                            'buyerid' => NULL,
                                                            'sellerid' => $this->appsettings['SELLER_RECORD_ID'],
                                                            'issueddatepdf' => NULL,
                                                            'grossamountword' => NULL,
                                                            'isinvalid' => 0,
                                                            'isrefund' => 0,
                                                            'vchtype' => "Sales",
                                                            'vchtypename' => "Tax Invoice",
                                                            'SyncToken' => NULL,
                                                            'docTypeCode' => $docType
                                                        );
                                                        
                                                        $discountAppStatus = 0;
                                                        $discountAppBalance = 0;
                                                        $discountAppPct = 0;
                                                        
                                                        /**
                                                         * Author: frncslubanga@gmail.com
                                                         * Date: 2024-05-20
                                                         * Description: Handle fees/taxes which have no rates, and are passed as invoice line items
                                                         */
                                                        $mapped_fees = array();
                                                        
                                                        if(trim($this->appsettings['CHECK_FEE_MAP_FLAG']) == '1'){
                                                            $this->logger->write("Api : importErpSalesReceipts() : The check fees mapping is on", 'r');
                                                            
                                                            $feesmapping = new feesmapping($this->db);
                                                            $feesmappings = $feesmapping->all();
                                                            
                                                            foreach ($feesmappings as $f_obj) {
                                                                $this->logger->write("Api : importErpSalesReceipts() : The fee code is " . $f_obj['feecode'], 'r');
                                                                $this->logger->write("Api : importErpSalesReceipts() : The product code is " . $f_obj['productcode'], 'r');
                                                                
                                                                $mapped_fees[] = array(
                                                                    'id' => empty($f_obj['id'])? '' : $f_obj['id'],
                                                                    'feecode' => empty($f_obj['feecode'])? '' : $f_obj['feecode'],
                                                                    'productcode' => empty($f_obj['productcode'])? '' : $f_obj['productcode'],
                                                                    'amount' => 0
                                                                );
                                                            }
                                                        }
                                                        
                                                        foreach ($invoices as $elem) {
                                                            
                                                            try {
                                                                $this->logger->write("Api : importErpSalesReceipts() : Invoice Number: " . $elem->DocNumber, 'r');
                                                                $this->logger->write("Api : importErpSalesReceipts() : PrivateNote: " . $elem->PrivateNote, 'r');
                                                                $InvStatus = $elem->PrivateNote;
                                                                
                                                                $CustomerRef = $elem->CustomerRef;
                                                                $DocNumber = $elem->DocNumber;
                                                                $CurrencyRef = $elem->CurrencyRef;
                                                                $TxnDate = $elem->TxnDate;
                                                                $InvoiceId = $elem->Id;
                                                                $SyncToken = $elem->SyncToken;
                                                                $TxnDate = $elem->TxnDate;
                                                                
                                                                $invoicedetails['erpinvoiceid'] = $InvoiceId;
                                                                $invoicedetails['erpinvoiceno'] = $DocNumber;
                                                                
                                                                if ($CustomerRef) {
                                                                    $customer->getByCode($CustomerRef);
                                                                    
                                                                    if ($customer->id) {
                                                                        $this->logger->write("Api : importErpSalesReceipts() : The customer Id " . $CustomerRef . " exists on the platform", 'r');
                                                                        
                                                                        $buyer['id'] = $customer->id;
                                                                        $buyer['erpcustomerid'] = $customer->erpcustomerid;
                                                                        $buyer['erpcustomercode'] = $customer->erpcustomercode;
                                                                        $buyer['tin'] = $customer->tin;
                                                                        $buyer['ninbrn'] = $customer->ninbrn;
                                                                        $buyer['PassportNum'] = $customer->PassportNum;
                                                                        $buyer['legalname'] = $customer->legalname;
                                                                        $buyer['businessname'] = $customer->businessname;
                                                                        $buyer['address'] = $customer->address;
                                                                        $buyer['mobilephone'] = $customer->mobilephone;
                                                                        $buyer['linephone'] = $customer->linephone;
                                                                        $buyer['emailaddress'] = $customer->emailaddress;
                                                                        $buyer['placeofbusiness'] = $customer->placeofbusiness;
                                                                        $buyer['type'] = $customer->type;
                                                                        $buyer['citizineship'] = $customer->citizineship;
                                                                        $buyer['countryCode'] = $customer->countryCode;
                                                                        $buyer['sector'] = $customer->sector;
                                                                        $buyer['sectorCode'] = $customer->sectorCode;
                                                                        $buyer['datasource'] = $customer->datasource;
                                                                        $buyer['status'] = $customer->status;
                                                                        
                                                                        $invoicedetails['buyerid'] = $customer->id;
                                                                    } else {
                                                                        $this->logger->write("Api : importErpSalesReceipts() : The customer Id " . $CustomerRef . " does not exist on the platform", 'r');
                                                                        
                                                                        // Let's download the customer
                                                                        $customers = $dataService->FindbyId('customer', $CustomerRef);
                                                                        
                                                                        $custError = $dataService->getLastError();
                                                                        
                                                                        if ($custError) {
                                                                            $this->logger->write("Api : importErpSalesReceipts() : The operation to download ERP customers was not successful. The Response Message is: " . $custError->getResponseBody(), 'r');
                                                                        } else {
                                                                            // print_r($customers);
                                                                            if (isset($customers)) {
                                                                                if ($customers) {
                                                                                    
                                                                                    $cust = array(
                                                                                        'id' => NULL,
                                                                                        'erpcustomerid' => NULL,
                                                                                        'erpcustomercode' => NULL,
                                                                                        'tin' => NULL,
                                                                                        'ninbrn' => NULL,
                                                                                        'PassportNum' => NULL,
                                                                                        'legalname' => NULL,
                                                                                        'businessname' => NULL,
                                                                                        'address' => NULL,
                                                                                        'mobilephone' => NULL,
                                                                                        'linephone' => NULL,
                                                                                        'emailaddress' => NULL,
                                                                                        'placeofbusiness' => NULL,
                                                                                        'type' => '1', /*default all customers to B2C*/
                                                                                        'citizineship' => NULL,
                                                                                        'countryCode' => NULL,
                                                                                        'sector' => NULL,
                                                                                        'sectorCode' => NULL,
                                                                                        'datasource' => 'ERP',
                                                                                        'status' => NULL
                                                                                    );
                                                                                    
                                                                                    try {
                                                                                        $this->logger->write("Api : importErpSalesReceipts() : Customer Name: " . $customers->DisplayName, 'r');
                                                                                        
                                                                                        $erpcustomerid = $customers->Id;
                                                                                        $erpcustomercode = $customers->Id;
                                                                                        $legalname = empty($customers->FullyQualifiedName) ? $customers->DisplayName : $customers->FullyQualifiedName;
                                                                                        $businessname = empty($customers->FullyQualifiedName) ? $customers->DisplayName : $customers->FullyQualifiedName;
                                                                                        
                                                                                        if (isset($customers->PrimaryPhone)) {
                                                                                            $mobilephone = $customers->PrimaryPhone->FreeFormNumber;
                                                                                            $cust['mobilephone'] = $mobilephone;
                                                                                        }
                                                                                        
                                                                                        if (isset($customers->PrimaryEmailAddr)) {
                                                                                            $emailaddress = $customers->PrimaryEmailAddr->Address;
                                                                                            $cust['emailaddress'] = $emailaddress;
                                                                                        }
                                                                                        
                                                                                        if (isset($customers->BillAddr)) {
                                                                                            $address = $customers->BillAddr->Line1;
                                                                                            $cust['address'] = $address;
                                                                                        }
                                                                                        
                                                                                        $this->logger->write("Api : importErpSalesReceipts() : Mobile: " . $mobilephone, 'r');
                                                                                        $this->logger->write("Api : importErpSalesReceipts() : Email: " . $emailaddress, 'r');
                                                                                        
                                                                                        $cust['erpcustomerid'] = $erpcustomerid;
                                                                                        $cust['erpcustomercode'] = $erpcustomercode;
                                                                                        $cust['legalname'] = $legalname;
                                                                                        $cust['businessname'] = $businessname;
                                                                                        
                                                                                        if ($customers->Active == false) {
                                                                                            $cust['status'] = $this->appsettings['INACTIVECUSTOMERSTATUSID'];
                                                                                            $this->logger->write("Api : importErpSalesReceipts() : The customer is not ACTIVE.", 'r');
                                                                                        } else {
                                                                                            $cust['status'] = $this->appsettings['ACTIVECUSTOMERSTATUSID'];
                                                                                            $this->logger->write("Api : importErpSalesReceipts() : The customer is ACTIVE.", 'r');
                                                                                        }
                                                                                        
                                                                                        if ($erpcustomercode && $legalname) {
                                                                                            $this->logger->write("Api : importErpSalesReceipts() : The customer does not exist", 'r');
                                                                                            $cust_status = $this->util->createcustomer($cust, $this->f3->get('SESSION.id'));
                                                                                            
                                                                                            if ($cust_status) {
                                                                                                $this->logger->write("Api : importErpSalesReceipts() : The customer " . $cust['legalname'] . " was created.", 'r');
                                                                                                
                                                                                                $customer->getByCode($CustomerRef);
                                                                                                
                                                                                                if ($customer->id) {
                                                                                                    $buyer['id'] = $customer->id;
                                                                                                    $buyer['erpcustomerid'] = $customer->erpcustomerid;
                                                                                                    $buyer['erpcustomercode'] = $customer->erpcustomercode;
                                                                                                    $buyer['tin'] = $customer->tin;
                                                                                                    $buyer['ninbrn'] = $customer->ninbrn;
                                                                                                    $buyer['PassportNum'] = $customer->PassportNum;
                                                                                                    $buyer['legalname'] = $customer->legalname;
                                                                                                    $buyer['businessname'] = $customer->businessname;
                                                                                                    $buyer['address'] = $customer->address;
                                                                                                    $buyer['mobilephone'] = $customer->mobilephone;
                                                                                                    $buyer['linephone'] = $customer->linephone;
                                                                                                    $buyer['emailaddress'] = $customer->emailaddress;
                                                                                                    $buyer['placeofbusiness'] = $customer->placeofbusiness;
                                                                                                    $buyer['type'] = $customer->type;
                                                                                                    $buyer['citizineship'] = $customer->citizineship;
                                                                                                    $buyer['countryCode'] = $customer->countryCode;
                                                                                                    $buyer['sector'] = $customer->sector;
                                                                                                    $buyer['sectorCode'] = $customer->sectorCode;
                                                                                                    $buyer['datasource'] = $customer->datasource;
                                                                                                    $buyer['status'] = $customer->status;
                                                                                                    
                                                                                                    $invoicedetails['buyerid'] = $customer->id;
                                                                                                } else {
                                                                                                    $this->logger->write("Api : importErpSalesReceipts() : The created customer was not retrieved successfully.", 'r');
                                                                                                }
                                                                                            } else {
                                                                                                $this->logger->write("Api : importErpSalesReceipts() : The customer " . $cust['legalname'] . " was NOT created.", 'r');
                                                                                            }
                                                                                        } else {
                                                                                            $this->logger->write("Api : importErpSalesReceipts() : The customer has no Id.", 'r');
                                                                                        }
                                                                                    } catch (Exception $e) {
                                                                                        $this->logger->write("Api : importErpSalesReceipts() : There was an error when processing Item " . $customers->DisplayName . ". The error is " . $e->getMessage(), 'r');
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                $this->logger->write("Api : importErpSalesReceipts() : The operation to download ERP customers did not return records.", 'r');
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                
                                                                if (isset($elem->Line)) {
                                                                    foreach ($elem->Line as $items) {
                                                                        $LineId = $items->Id;
                                                                        $LineNum = $items->LineNum;
                                                                        $Description = $items->Description;
                                                                        $ErpAmount = $items->Amount;
                                                                        $DetailType = $items->DetailType;
                                                                        $this->logger->write("Api : importErpSalesReceipts() : Line Description: " . $Description, 'r');
                                                                        
                                                                        if (strtoupper($items->DetailType) == 'DISCOUNTLINEDETAIL') {
                                                                            if (isset($items->DiscountLineDetail)) {
                                                                                $PercentBased = $items->DiscountLineDetail->PercentBased; // true/false
                                                                                $DiscountPercent = $items->DiscountLineDetail->DiscountPercent;
                                                                            }
                                                                            
                                                                            $this->logger->write("Api : importErpSalesReceipts() : Discount Percent: " . $PercentBased, 'r');
                                                                            $discount = empty($ErpAmount) ? 0 : (float) $ErpAmount;
                                                                            $discountpct = empty($DiscountPercent) ? 0 : (float) $DiscountPercent;
                                                                            
                                                                            if (! empty($ErpAmount)) {
                                                                                $discountAppStatus = 1;
                                                                                $discountAppBalance = $ErpAmount;
                                                                                $discountAppPct = $discountpct;
                                                                            }
                                                                        }
                                                                        
                                                                        if (strtoupper($items->DetailType) == 'SALESITEMLINEDETAIL') {
                                                                            if (isset($items->SalesItemLineDetail)) {
                                                                                $ItemRef = $items->SalesItemLineDetail->ItemRef;
                                                                                $UnitPrice = $items->SalesItemLineDetail->UnitPrice;
                                                                                $Qty = $items->SalesItemLineDetail->Qty;
                                                                                $TaxCodeRef = $items->SalesItemLineDetail->TaxCodeRef;
                                                                            }
                                                                            
                                                                            $this->logger->write("Api : importErpSalesReceipts() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                            $this->logger->write("Api : importErpSalesReceipts() : Unit Price: " . $UnitPrice, 'r');
                                                                            $this->logger->write("Api : importErpSalesReceipts() : Qty: " . $Qty, 'r');
                                                                            
                                                                            $product->getByErpCode($ItemRef);
                                                                            
                                                                            if ($product->code) {
                                                                                $measureunit->getByCode($product->measureunit);
                                                                            } else {
                                                                                $this->logger->write("Api : importErpSalesReceipts() : The Item does not exist on the platform", 'r');
                                                                            }
                                                                            
                                                                            $ii = 0;
                                                                            $product_skip_flag = 0;
                                                                            
                                                                            foreach ($mapped_fees as $m_obj) {
                                                                                if(trim($m_obj['productcode']) == $product->code){
                                                                                    $this->logger->write("Api : importErpSalesReceipts() : The product " . $product->code . " is mapped to a tax/fee " . $m_obj['feecode'], 'r');
                                                                                    
                                                                                    $f_qty = $Qty;
                                                                                    $f_unit = $UnitPrice;
                                                                                    
                                                                                    $mapped_fees[$ii]['amount'] = ($f_qty * $f_unit); # Might be problematic. Consider "foreach ($mapped_fees as &$m_obj) {"
                                                                                    $product_skip_flag = 1;
                                                                                    break;
                                                                                }
                                                                                
                                                                                $ii = $ii + 1;
                                                                            }
                                                                            
                                                                            if ($product_skip_flag == 1){
                                                                                continue;
                                                                            }
                                                                            
                                                                            $qty = $Qty;
                                                                            $unit = $UnitPrice;
                                                                            $amount = $ErpAmount;
                                                                            
                                                                            /**
                                                                             * Can we determine the DISCOUNT PERCENTAGE incase it is a line DISCOUNT provided?
                                                                             */
                                                                            if ($discountpct == 0 && $discount > 0) {
                                                                                $discountpct = $discount / $amount;
                                                                                $discount = 0;
                                                                            } else {
                                                                                $discount = 0;
                                                                            }
                                                                            
                                                                            $taxid = $this->util->getinvoicetaxrate_v2($this->appsettings['DEFAULTINVOICEINDUSTRY'], $customer->type, $product->code, $customer->tin, $this->appsettings['OVERRIDE_TAXRATE_FLAG'], $this->appsettings['TAXPAYER_CHECK_FLAG']);
                                                                            $this->logger->write("Api : importErpSalesReceipts() : The computed TAXID is " . $taxid, 'r');
                                                                            
                                                                            if (! $taxid) {
                                                                                $taxid = $this->appsettings['STANDARDTAXRATE'];
                                                                            }
                                                                            
                                                                            if ($taxid == $this->appsettings['DEEMEDTAXRATE']) {
                                                                                $deemedflag = 'YES';
                                                                            } else {
                                                                                $deemedflag = 'NO';
                                                                            }
                                                                            
                                                                            $this->logger->write("Api : importErpSalesReceipts() : The final TAXID is " . $taxid, 'r');
                                                                            
                                                                            $tr = new taxrates($this->db);
                                                                            $tr->getByID($taxid);
                                                                            $taxcode = $tr->code;
                                                                            $taxname = $tr->name;
                                                                            $taxcategory = $tr->category;
                                                                            $taxdisplaycategory = $tr->displayCategoryCode;
                                                                            $taxdescription = $tr->description;
                                                                            $rate = $tr->rate ? $tr->rate : 0;
                                                                            
                                                                            $this->logger->write("Api : importErpSalesReceipts() : unit: " . $unit, 'r');
                                                                            
                                                                            if (strtoupper(trim($pricevatinclusive)) == 'YES') {
                                                                                // Use the figures as they come from the ERP
                                                                                $total = ($qty * $unit); // ??
                                                                                
                                                                                // $discount = ($discountpct/100) * $total; //discount is already calculated by QB
                                                                                
                                                                                /**
                                                                                 * Modification Date: 2021-01-26
                                                                                 * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                                 */
                                                                                // $gross = $total - $discount;
                                                                                $gross = $total;
                                                                                
                                                                                $discount = (- 1) * $discount;
                                                                                
                                                                                $tax = ($gross / ($rate + 1)) * $rate; // ??
                                                                                
                                                                                $net = $gross - $tax;
                                                                            } elseif (strtoupper(trim($pricevatinclusive)) == 'NO') {
                                                                                // Manually calculate figures
                                                                                $this->logger->write("Api : importErpSalesReceipts() : Rebasing the prices", 'r');
                                                                                
                                                                                if ($rate > 0) {
                                                                                    $unit = $unit * ($rate + 1);
                                                                                }
                                                                                
                                                                                $total = ($qty * $unit); // ??
                                                                                
                                                                                // $discount = ($discountpct/100) * $total;
                                                                                
                                                                                /**
                                                                                 * Modification Date: 2021-01-26
                                                                                 * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                                 */
                                                                                // $gross = $total - $discount;
                                                                                $gross = $total;
                                                                                
                                                                                $discount = (- 1) * $discount;
                                                                                
                                                                                $tax = ($gross / ($rate + 1)) * $rate; // ??
                                                                                
                                                                                $net = $gross - $tax;
                                                                            }
                                                                            
                                                                            /**
                                                                             * Over-ride tax, if the tax payer is not VAT registered
                                                                             */
                                                                            if ($this->vatRegistered == 'N') {
                                                                                $tax = 0;
                                                                                $taxcategory = NULL;
                                                                                $taxcode = NULL;
                                                                            }
                                                                            
                                                                            $netamount = $netamount + $net;
                                                                            $taxamount = $taxamount + $tax;
                                                                            
                                                                            $grossamount = $grossamount + $gross;
                                                                            $itemcount = $itemcount + 1;
                                                                            
                                                                            if ($discount == 0) {
                                                                                $discountflag = 'NO';
                                                                            } else {
                                                                                $discountflag = 'YES';
                                                                            }
                                                                            
                                                                            $goods[] = array(
                                                                                'groupid' => NULL,
                                                                                'item' => $product->name,
                                                                                'itemcode' => $product->code,
                                                                                'qty' => $qty,
                                                                                'unitofmeasure' => $product->measureunit,
                                                                                'unitprice' => $unit,
                                                                                'total' => $total,
                                                                                'taxid' => $taxid,
                                                                                'taxrate' => $rate,
                                                                                'tax' => $tax,
                                                                                'discounttotal' => $discount,
                                                                                'discounttaxrate' => $rate,
                                                                                'discountpercentage' => $discountpct,
                                                                                'ordernumber' => NULL,
                                                                                'discountflag' => trim($discountflag) == 'NO' ? '2' : '1',
                                                                                'deemedflag' => (strtoupper(trim($deemedflag)) == 'NO' ? '2' : '1'),
                                                                                'exciseflag' => NULL,
                                                                                'categoryid' => NULL,
                                                                                'categoryname' => NULL,
                                                                                'goodscategoryid' => $product->commoditycategorycode,
                                                                                'goodscategoryname' => NULL,
                                                                                'exciserate' => NULL,
                                                                                'exciserule' => NULL,
                                                                                'excisetax' => NULL,
                                                                                'pack' => NULL,
                                                                                'stick' => NULL,
                                                                                'exciseunit' => NULL,
                                                                                'excisecurrency' => NULL,
                                                                                'exciseratename' => NULL,
                                                                                'taxdisplaycategory' => $taxdisplaycategory,
                                                                                'taxcategory' => $taxcategory,
                                                                                'taxcategoryCode' => $taxcode,
                                                                                'unitofmeasurename' => $measureunit->name
                                                                            );
                                                                            
                                                                            $this->logger->write("Api : importErpSalesReceipts() : The TAXCODE is " . $taxcode, 'r');
                                                                            
                                                                            if ($this->vatRegistered == 'Y') {
                                                                                $taxes[] = array(
                                                                                    'discountflag' => trim($discountflag) == 'NO' ? '2' : '1',
                                                                                    'discounttotal' => $discount,
                                                                                    'discounttaxrate' => $rate,
                                                                                    'discountpercentage' => $discountpct,
                                                                                    'd_netamount' => NULL,
                                                                                    'd_taxamount' => NULL,
                                                                                    'd_grossamount' => NULL,
                                                                                    'groupid' => NULL,
                                                                                    'goodid' => NULL,
                                                                                    'taxdisplaycategory' => $taxdisplaycategory,
                                                                                    'taxcategory' => $taxcategory,
                                                                                    'taxcategoryCode' => $taxcode,
                                                                                    'netamount' => $net,
                                                                                    'taxrate' => $rate,
                                                                                    'taxamount' => $tax,
                                                                                    'grossamount' => $gross,
                                                                                    'exciseunit' => NULL,
                                                                                    'excisecurrency' => NULL,
                                                                                    'taxratename' => $taxname,
                                                                                    'taxdescription' => $taxdescription
                                                                                );
                                                                            }
                                                                        }
                                                                    } // foreach($elem->Line as $items)
                                                                } // if(isset($elem->Line))
                                                                    
                                                                $jj = 0;
                                                                
                                                                foreach ($mapped_fees as $m_obj) {
                                                                    $this->logger->write("Api : importErpSalesReceipts() : Adding fees to the tax array", 'r');
                                                                    $this->logger->write("Api : importErpSalesReceipts() : The amount is: " . $m_obj['amount'], 'r');
                                                                    
                                                                    $tr = new taxrates($this->db);
                                                                    $tr->getByCode($m_obj['feecode']);
                                                                    $taxcode = $tr->code;
                                                                    $taxname = $tr->name;
                                                                    $taxcategory = $tr->category;
                                                                    $taxdescription = $tr->description;
                                                                    $taxdisplaycategory = $tr->displayCategoryCode;
                                                                    
                                                                    if((float)$m_obj['amount'] <> 0){
                                                                        $taxes[] = array(
                                                                            'discountflag' => '1',
                                                                            'discounttotal' => NULL,
                                                                            'discounttaxrate' => NULL,
                                                                            'discountpercentage' => NULL,
                                                                            'd_netamount' => NULL,
                                                                            'd_taxamount' => NULL,
                                                                            'd_grossamount' => NULL,
                                                                            'groupid' => NULL,
                                                                            'goodid' => NULL,
                                                                            'taxdisplaycategory' => $taxdisplaycategory,
                                                                            'taxcategory' => $taxcategory,
                                                                            'taxcategoryCode' => $taxcode,
                                                                            'netamount' => 0,
                                                                            'taxrate' => $m_obj['amount'],
                                                                            'taxamount' => $m_obj['amount'],
                                                                            'grossamount' => $m_obj['amount'],
                                                                            'exciseunit' => NULL,
                                                                            'excisecurrency' => NULL,
                                                                            'taxratename' => $taxname,
                                                                            'taxdescription' => $taxdescription
                                                                        );
                                                                        
                                                                        //We should reset the amount to 0 in preparation for the next iteration.
                                                                        $this->logger->write("Api : importErpSalesReceipts() : Resetting the amount to 0", 'r');
                                                                        $mapped_fees[$jj]['amount'] = 0;
                                                                        $jj = $jj + 1;
                                                                    }
                                                                    else {
                                                                        $jj = $jj + 1;
                                                                        continue;
                                                                    }
                                                                }
                                                                
                                                                $this->logger->write("Api : importErpSalesReceipts() : Discount App Status: " . $discountAppStatus, 'r');
                                                                $this->logger->write("Api : importErpSalesReceipts() : Discount App Balance: " . $discountAppBalance, 'r');
                                                                $this->logger->write("Api : importErpSalesReceipts() : Discount App Percentage: " . $discountAppPct, 'r');
                                                                
                                                                if ($discountAppStatus == 1) {
                                                                    $this->logger->write("Api : importErpSalesReceipts() : Applying Discounts", 'r');
                                                                    $this->logger->write("Api : importErpSalesReceipts() : Customer Type " . $customer->type, 'r');
                                                                    list ($goods, $taxes) = $this->util->applyDiscount($goods, $taxes, $discountAppBalance, $customer->type, $customer->tin, NULL);
                                                                }
                                                                
                                                                if (isset($elem->TxnTaxDetail)) {
                                                                    $TxnTaxCodeRef = $elem->TxnTaxDetail->TxnTaxCodeRef;
                                                                    $TotalTax = $elem->TxnTaxDetail->TotalTax;
                                                                    
                                                                    $this->logger->write("Api : importErpSalesReceipts() : Tax Ref: " . $TxnTaxCodeRef, 'r');
                                                                    
                                                                    if (isset($elem->TxnTaxDetail->TaxLine)) {
                                                                        $TaxAmount = $elem->TxnTaxDetail->TaxLine->Amount;
                                                                        $this->logger->write("Api : importErpSalesReceipts() : Total Tax Amount: " . $TaxAmount, 'r');
                                                                        
                                                                        if (isset($elem->TxnTaxDetail->TaxLine->DetailType)) {
                                                                            if (strtoupper($elem->TxnTaxDetail->TaxLine->DetailType) == 'TAXLINEDETAIL') {
                                                                                if (isset($elem->TxnTaxDetail->TaxLine->TaxLineDetail)) {
                                                                                    $TaxRateRef = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->TaxRateRef;
                                                                                    $TaxPercentBased = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->PercentBased;
                                                                                    $TaxPercent = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->TaxPercent;
                                                                                    $NetAmountTaxable = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->NetAmountTaxable;
                                                                                }
                                                                                
                                                                                $this->logger->write("Api : importErpSalesReceipts() : Tax Line Net Amount: " . $NetAmountTaxable, 'r');
                                                                            }
                                                                        }
                                                                    }
                                                                } // if(isset($elem->TxnTaxDetail))
                                                                
                                                                if (isset($elem->CustomField)) {
                                                                    foreach ($elem->CustomField as $fields) {
                                                                        $FieldDefinitionId = $fields->DefinitionId;
                                                                        $FieldName = $fields->Name;
                                                                        $FieldType = $fields->Type; // StringType
                                                                        $FieldStringValue = $fields->StringValue;
                                                                        
                                                                        $this->logger->write("Api : importErpSalesReceipts() : Customer Field Name: " . $FieldName, 'r');
                                                                    } // foreach($elem->CustomField as $items)
                                                                } // if(isset($elem->CustomField))
                                                                
                                                                $invoicedetails['operator'] = $this->username_u;
                                                                $invoicedetails['currency'] = $this->util->getcurrency(trim($CurrencyRef));
                                                                $invoicedetails['SyncToken'] = $SyncToken;
                                                                $invoicedetails['issueddate'] = $TxnDate;
                                                                $invoicedetails['issuedtime'] = $TxnDate;
                                                                $invoicedetails['issueddatepdf'] = $TxnDate;
                                                                $invoicedetails['itemcount'] = $itemcount;
                                                                
                                                                $invoicedetails['netamount'] = $netamount;
                                                                $invoicedetails['taxamount'] = $taxamount;
                                                                $invoicedetails['grossamount'] = $grossamount;
                                                                $invoicedetails['origrossamount'] = 0;
                                                                
                                                                $invoicedetails['remarks'] = "The invoice DocNumber " . $DocNumber . " and Id " . $InvoiceId . " uploaded using the QBO API";
                                                                $this->logger->write("Api : importErpSalesReceipts() : The Sync Token is " . $SyncToken, 'r');
                                                                
                                                                if ($InvoiceId) {
                                                                    $invoice->getByErpId($InvoiceId);
                                                                    $inv_status = NULL;
                                                                    
                                                                    if ($invoice->dry()) {
                                                                        $this->logger->write("Api : importErpSalesReceipts() : The invoice does not exist", 'r');
                                                                        $inv_status = $this->util->createinvoice($invoicedetails, $goods, $taxes, $buyer, $this->userid_u);
                                                                        
                                                                        if ($inv_status) {
                                                                            $this->logger->write("Api : importErpSalesReceipts() : The invoice " . $DocNumber . " was created.", 'r');
                                                                        } else {
                                                                            $this->logger->write("Api : importErpSalesReceipts() : The invoice " . $DocNumber . " was NOT created.", 'r');
                                                                        }
                                                                    } else {
                                                                        $this->logger->write("Api : importErpSalesReceipts() : The invoice exists", 'r');
                                                                        $invoicedetails['id'] = $invoice->id;
                                                                        $invoicedetails['gooddetailgroupid'] = $invoice->gooddetailgroupid;
                                                                        $invoicedetails['taxdetailgroupid'] = $invoice->taxdetailgroupid;
                                                                        $invoicedetails['paymentdetailgroupid'] = $invoice->paymentdetailgroupid;
                                                                        
                                                                        if ($invoice->einvoiceid) {
                                                                            $this->logger->write("Api : importErpSalesReceipts() : The invoice " . $DocNumber . " is already fiscalized.", 'r');
                                                                        } else {
                                                                            $this->logger->write("Api : importErpSalesReceipts() : The invoice " . $DocNumber . " is NOT fiscalized.", 'r');
                                                                            
                                                                            $inv_status = $this->util->updateinvoice($invoicedetails, $goods, $taxes, $buyer, $this->userid_u);
                                                                            
                                                                            if ($inv_status) {
                                                                                $this->logger->write("Api : importErpSalesReceipts() : The invoice " . $DocNumber . " was updated.", 'r');
                                                                            } else {
                                                                                $this->logger->write("Api : importErpSalesReceipts() : The invoice " . $DocNumber . " was NOT updated.", 'r');
                                                                            }
                                                                        }
                                                                    }
                                                                    
                                                                    // TRY uploading here.
                                                                    if ($inv_status) {
                                                                        $this->logger->write("Api : importErpSalesReceipts() : Uploading the invoice " . $DocNumber . " into EFRIS.", 'r');
                                                                        // **********************START UPLOAD***************
                                                                        $invoice->getByErpId($InvoiceId); // Refresh Invoice
                                                                        
                                                                        if ($invoice->einvoiceid) {
                                                                            $this->logger->write("Api : importErpInvoices() : This invoice is already uploaded", 'r');
                                                                            $this->message = 'This invoice is already uploaded.';
                                                                            $this->code = '005';
                                                                            
                                                                            // $this->util->createerpauditlog($this->userid_u, $permission, NULL, NULL, NULL, NULL, NULL, $invoice->erpinvoiceno, $invoice->erpinvoiceid, NULL, $this->code, $this->message);
                                                                        } else {
                                                                            $data = $this->util->uploadinvoice($this->userid_u, $invoice->id, $this->vatRegistered); // will return JSON.
                                                                            
                                                                            $data = json_decode($data, true);
                                                                            // $this->logger->write("Api : importErpInvoices() : The response content is: " . $data, 'r');
                                                                            // var_dump($data);
                                                                            
                                                                            if (isset($data['returnCode'])) {
                                                                                $this->logger->write("Api : importErpInvoices() : The operation to upload the invoice not successful. The error message is " . $data['returnMessage'], 'r');
                                                                                $this->message = "The operation to upload the invoice not successful. The error message is " . $data['returnMessage'];
                                                                                //$this->code = '008';
                                                                                $this->code = $data['returnCode'];
                                                                                
                                                                                // $this->util->createerpauditlog($this->userid_u, $permission, NULL, NULL, NULL, NULL, NULL, $invoice->erpinvoiceno, $invoice->erpinvoiceid, NULL, $this->code, $this->message);
                                                                            } else {
                                                                                if (isset($data['basicInformation'])) {
                                                                                    $antifakeCode = $data['basicInformation']['antifakeCode']; // 32966911991799104051
                                                                                    $invoiceId = $data['basicInformation']['invoiceId']; // 3257429764295992735
                                                                                    $invoiceNo = $data['basicInformation']['invoiceNo']; // 3120012276043
                                                                                    
                                                                                    $issuedDate = $data['basicInformation']['issuedDate']; // 18/09/2020 17:14:12
                                                                                    $issuedDate = str_replace('/', '-', $issuedDate); // Replace / with -
                                                                                    $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                                                                                    
                                                                                    $issuedTime = $data['basicInformation']['issuedDate']; // 18/09/2020 17:14:12
                                                                                    $issuedTime = str_replace('/', '-', $issuedTime); // Replace / with -
                                                                                    $issuedTime = date("Y-m-d H:i:s", strtotime($issuedTime));
                                                                                    
                                                                                    $issuedDatePdf = $data['basicInformation']['issuedDatePdf']; // 318/09/2020 17:14:12
                                                                                    $issuedDatePdf = str_replace('/', '-', $issuedDatePdf); // Replace / with -
                                                                                    $issuedDatePdf = date("Y-m-d H:i:s", strtotime($issuedDatePdf));
                                                                                    
                                                                                    $oriInvoiceId = $data['basicInformation']['oriInvoiceId']; // 1
                                                                                    $isInvalid = $data['basicInformation']['isInvalid']; // 1
                                                                                    $isRefund = $data['basicInformation']['isRefund']; // 1
                                                                                    
                                                                                    $deviceNo = $data['basicInformation']['deviceNo'];
                                                                                    $invoiceIndustryCode = $data['basicInformation']['invoiceIndustryCode'];
                                                                                    $invoiceKind = $data['basicInformation']['invoiceKind'];
                                                                                    $invoiceType = $data['basicInformation']['invoiceType'];
                                                                                    $isBatch = $data['basicInformation']['isBatch'];
                                                                                    $operator = $data['basicInformation']['operator'];
                                                                                    
                                                                                    $currencyRate = $data['basicInformation']['currencyRate'];
                                                                                    
                                                                                    try {
                                                                                        $this->db->exec(array(
                                                                                            'UPDATE tblinvoices SET antifakeCode = "' . $antifakeCode . '", einvoiceid = "' . $invoiceId . '", einvoicenumber = "' . $invoiceNo . '", issueddate = "' . $issuedDate . '", issueddatepdf = "' . $issuedDatePdf . '", oriinvoiceid = "' . $oriInvoiceId . '", isinvalid = ' . $isInvalid . ', isrefund = ' . $isRefund . ', issuedtime = "' . $issuedTime . '", deviceno = "' . $deviceNo . '", invoiceindustrycode = ' . $invoiceIndustryCode . ', invoicekind = ' . $invoiceKind . ', invoicetype = ' . $invoiceType . ', isbatch = "' . $isBatch . '", operator = "' . $operator . '", currencyRate = ' . $currencyRate . ', modifieddt = NOW(), modifiedby = ' . $this->userid_u . ' WHERE id = ' . $invoice->id
                                                                                        ));
                                                                                        
                                                                                        // $this->logger->write($this->db->log(TRUE), 'r');
                                                                                    } catch (Exception $e) {
                                                                                        $this->logger->write("Api : importErpInvoices() : Failed to insert into the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                                                                    }
                                                                                }
                                                                                
                                                                                if (isset($data['sellerDetails'])) {
                                                                                    /*
                                                                                     * "address":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                                                                                     * "branchCode":"00",
                                                                                     * "branchId":"912550336846912433",
                                                                                     * "branchName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                                                                     * "businessName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                                                                     * "emailAddress":"editesti06@gmail.com",
                                                                                     * "legalName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                                                                     * "linePhone":"256787695360",
                                                                                     * "mobilePhone":"256782356088",
                                                                                     * "ninBrn":"/80020002851201",
                                                                                     * "placeOfBusiness":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                                                                                     * "referenceNo":"21",
                                                                                     * "tin":"1017918269"
                                                                                     */
                                                                                    
                                                                                    $branchCode = ! isset($data['sellerDetails']['branchCode']) ? '' : $data['sellerDetails']['branchCode'];
                                                                                    $branchId = ! isset($data['sellerDetails']['branchId']) ? '' : $data['sellerDetails']['branchId'];
                                                                                    $referenceNo = ! isset($data['sellerDetails']['referenceNo']) ? '' : $data['sellerDetails']['referenceNo'];
                                                                                    
                                                                                    try {
                                                                                        $this->db->exec(array(
                                                                                            'UPDATE tblinvoices SET branchCode = "' . $branchCode . '", branchId = "' . $branchId . '", erpinvoiceno = "' . addslashes($referenceNo) . '", modifieddt = NOW(), modifiedby = ' . $this->userid_u . ' WHERE id = ' . $invoice->id
                                                                                        ));
                                                                                        
                                                                                        // $this->logger->write($this->db->log(TRUE), 'r');
                                                                                    } catch (Exception $e) {
                                                                                        $this->logger->write("Api : importErpInvoices() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                                                                    }
                                                                                }
                                                                                
                                                                                if (isset($data['summary'])) {
                                                                                    $grossAmount = $data['summary']['grossAmount']; // 832000
                                                                                    $itemCount = $data['summary']['itemCount']; // 1
                                                                                    $netAmount = $data['summary']['netAmount']; // 705084.75
                                                                                    $qrCode = $data['summary']['qrCode']; // 020000001149IC1200122760430004F588000000C1A8450A20A021D534A1000121462A1000094968~MM INTERGRATED STEEL MILLS (UGANDA) LIMITED~Ediomu & Company~Kiboko Galv Corr. Sheet 36G
                                                                                    $taxAmount = $data['summary']['taxAmount']; // 126915.25
                                                                                    $modeCode = $data['summary']['modeCode']; // 0
                                                                                    
                                                                                    $mode = new modes($this->db);
                                                                                    $mode->getByCode($modeCode);
                                                                                    $modeName = $mode->name; // online
                                                                                    
                                                                                    $f = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
                                                                                    $grossAmountWords = $f->format($grossAmount); // two million
                                                                                    
                                                                                    try {
                                                                                        $this->db->exec(array(
                                                                                            'UPDATE tblinvoices SET grossamount = ' . $grossAmount . ', itemcount = ' . $itemCount . ', netamount = ' . $netAmount . ', einvoicedatamatrixcode = "' . addslashes($qrCode) . '", taxamount = ' . $taxAmount . ', modecode = "' . $modeCode . '", modename = "' . $modeName . '", grossamountword = "' . addslashes($grossAmountWords) . '", modifieddt = NOW(), modifiedby = ' . $this->userid_u . ' WHERE id = ' . $invoice->id
                                                                                        ));
                                                                                        // $this->logger->write($this->db->log(TRUE), 'r');
                                                                                    } catch (Exception $e) {
                                                                                        $this->logger->write("Api : importErpInvoices() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                                                                    }
                                                                                }
                                                                                
                                                                                // **********START UPDATE ERP******************
                                                                                $theInvoice = current($invoices);
                                                                                
                                                                                $invoice->getByErpId($InvoiceId); // Refresh Invoice
                                                                                
                                                                                $this->logger->write("Api : importErpSalesReceipts() : The Sync Token is " . $theInvoice->SyncToken, 'r');
                                                                                $this->logger->write("Api : importErpSalesReceipts() : The DocNumber is " . $theInvoice->DocNumber, 'r');
                                                                                $this->logger->write("Api : importErpSalesReceipts() : The einvoicenumber is " . $invoice->einvoicenumber, 'r');
                                                                                $this->logger->write("Api : importErpSalesReceipts() : The antifakecode is " . $invoice->antifakecode, 'r');
                                                                                
                                                                                if ($docType == trim($this->appsettings['INVOICEERPDOCTYPE'])) {
                                                                                    $updatedInvoice = Invoice::update($theInvoice, [
                                                                                        "sparse" => true,
                                                                                        "SyncToken" => $theInvoice->SyncToken,
                                                                                        "CustomField" => [
                                                                                            [
                                                                                                "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], // Fiscal Doc. Num
                                                                                                "Type" => "StringType",
                                                                                                "StringValue" => $invoice->einvoicenumber
                                                                                            ],
                                                                                            [
                                                                                                "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], // Verification Co
                                                                                                "Type" => "StringType",
                                                                                                "StringValue" => $invoice->antifakecode
                                                                                            ]
                                                                                        ]
                                                                                    ]);
                                                                                } elseif ($docType == trim($this->appsettings['SALESRECEIPTERPDOCTYPE'])) {
                                                                                    $updatedInvoice = SalesReceipt::update($theInvoice, [
                                                                                        "sparse" => true,
                                                                                        "SyncToken" => $theInvoice->SyncToken,
                                                                                        "CustomField" => [
                                                                                            [
                                                                                                "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], // Fiscal Doc. Num
                                                                                                "Type" => "StringType",
                                                                                                "StringValue" => $invoice->einvoicenumber
                                                                                            ],
                                                                                            [
                                                                                                "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], // Verification Co
                                                                                                "Type" => "StringType",
                                                                                                "StringValue" => $invoice->antifakecode
                                                                                            ]
                                                                                        ]
                                                                                    ]);
                                                                                } else {
                                                                                    $updatedInvoice = Invoice::update($theInvoice, [
                                                                                        "sparse" => true,
                                                                                        "SyncToken" => $theInvoice->SyncToken,
                                                                                        "CustomField" => [
                                                                                            [
                                                                                                "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], // Fiscal Doc. Num
                                                                                                "Type" => "StringType",
                                                                                                "StringValue" => $invoice->einvoicenumber
                                                                                            ],
                                                                                            [
                                                                                                "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], // Verification Co
                                                                                                "Type" => "StringType",
                                                                                                "StringValue" => $invoice->antifakecode
                                                                                            ]
                                                                                        ]
                                                                                    ]);
                                                                                }
                                                                                
                                                                                $updatedResult = $dataService->Update($updatedInvoice);
                                                                                // print_r($updatedResult);
                                                                                $updatederror = $dataService->getLastError();
                                                                                
                                                                                if ($updatederror) {
                                                                                    $this->logger->write("Api : importErpSalesReceipts() : The operation to update ERP invoices was not successful. The Response Message is: " . $updatederror->getResponseBody(), 'r');
                                                                                    $this->message = 'The operation to update ERP invoices was not successful.';
                                                                                    $this->code = '008';
                                                                                } else {
                                                                                    $this->logger->write("Api : importErpSalesReceipts() : The operation to update ERP invoices was successful.", 'r');
                                                                                    $this->message = 'The operation to update ERP invoices was successful.';
                                                                                    $this->code = '000';
                                                                                }
                                                                                // **********END UPDATE ERP********************
                                                                                
                                                                                $this->message = 'The operation to upload the invoice was successful';
                                                                                $this->code = '000';
                                                                            }
                                                                        }
                                                                        // **********************END UPLOAD*****************
                                                                        
                                                                        $this->util->createerpauditlog($this->userid_u, $permission, NULL, NULL, NULL, NULL, NULL, $invoice->erpinvoiceno, $invoice->erpinvoiceid, NULL, $this->code, $this->message);
                                                                    } else {
                                                                        $this->logger->write("Api : importErpSalesReceipts() : The invoice " . $DocNumber . " will not be uploaded into EFRIS.", 'r');
                                                                    }
                                                                } else {
                                                                    $this->logger->write("Api : importErpSalesReceipts() : The invoice has no Id.", 'r');
                                                                    $this->message = 'The invoice has no Id.';
                                                                    $this->code = '999';
                                                                }
                                                            } catch (Exception $e) {
                                                                $this->logger->write("Api : importErpSalesReceipts() : There was an error when processing invoice " . $elem->DocNumber . ". The error is " . $e->getMessage(), 'r');
                                                            }
                                                            
                                                            //Clear/Reset variables here
                                                            unset($goods);
                                                            unset($taxes);
                                                            $deemedflag = 'NO';
                                                            $discountflag = 'NO';
                                                            $netamount = 0;
                                                            $taxamount = 0;
                                                            $grossamount = 0;
                                                            $itemcount = 0;
                                                            $taxid = NULL;
                                                            $taxcode = NULL;
                                                            $taxname = NULL;
                                                            $taxcategory = NULL;
                                                            $taxdisplaycategory = NULL;
                                                            $taxdescription = NULL;
                                                            $rate = 0;
                                                            $qty = 0;
                                                            $unit = 0;
                                                            $discountpct = 0;
                                                            $total = 0;
                                                            $discount = 0;
                                                            $gross = 0;
                                                            $discount = 0;
                                                            $tax = 0;
                                                            $net = 0;
                                                            $amount = 0;
                                                            //unset($buyer);
                                                            //unset($invoicedetails);
                                                            $discountAppStatus = 0;
                                                            $discountAppBalance = 0;
                                                            $discountAppPct = 0;
                                                            $InvStatus = NULL;
                                                            
                                                            $CustomerRef = NULL;
                                                            $DocNumber = NULL;
                                                            $CurrencyRef = NULL;
                                                            $TxnDate = NULL;
                                                            $InvoiceId = NULL;
                                                            $SyncToken = NULL;
                                                            $TxnDate = NULL;
                                                        } //foreach
                                                    }
                                                } else {
                                                    $this->logger->write("Api : importErpSalesReceipts() : The operation to download ERP invoices did not return records.", 'r');
                                                    $this->message = 'The operation to download ERP invoices did not return records.';
                                                    $this->code = '999';
                                                }
                                            }
                                            
                                            $this->logger->write("Api : importErpSalesReceipts() : The operation to download ERP invoices was successful.", 'r');
                                            $this->message = 'The operation to download ERP invoices was successful.';
                                            $this->code = '000';
                                        } else {
                                            $this->logger->write("Api : importErpSalesReceipts() : The operation to download ERP invoices was not successful. Please connect to ERP first.", 'r');
                                            $this->message = 'The operation to download ERP invoices was not successful. Please connect to ERP first.';
                                            $this->code = '003';
                                        }
                                    } catch (Exception $e) {
                                        $this->logger->write("Api : importErpSalesReceipts() : The operation to download ERP invoices was not successful. The error is: " . $e->getMessage(), 'r');
                                        $this->message = 'The operation to download ERP invoices was not successful. Reconnect to the ERP OR Contact your System Administrator.';
                                        $this->code = '004';
                                    }
                                } else {
                                    $this->logger->write("Api : importErpSalesReceipts() : The integrated ERP is unknown.", 'r');
                                    $this->message = 'The access token is NOT set in the database. Please manually login.';
                                    $this->code = '999';
                                }
                            } else {
                                $this->logger->write("Api : importErpSalesReceipts() : We are unable to indentify the currently integrated ERP.", 'r');
                                $this->message = 'The access token is NOT set in the database. Please manually login.';
                                $this->code = '999';
                            }
                        }
                    } else {
                        $this->logger->write("Api : importErpSalesReceipts() : The access token expired. Please manually login.", 'r');
                        $this->message = 'The access token expired. Please manually login';
                        $this->code = '999';
                    }
                } else {
                    $this->logger->write("Api : importErpSalesReceipts() : The access token is NOT set. Please manually login.", 'r');
                    $this->message = 'The access token is NOT set. Please manually login';
                    $this->code = '999';
                }
            }
            
            if ($this->code !== '000') {
                $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The automated task which processes sales receipts failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
                
                $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
            }
        }
        
        $this->response = array(
            "response" => array(
                "responseCode" => $this->code,
                "responseMessage" => $this->message
            ),
            "data" => array()
        );
        
        $len = sizeof($this->response);
        header("CONTENT-LENGTH:" . $len);
        // print $this->response;
        die(json_encode($this->response));
        return;
    }
    
    /**
     * NULL
     *
     * @name importPurchaseOrders
     * @return NULL
     * @param
     *            NULL
     */
    function importPurchaseOrders()
    {
        $operation = NULL; // tblevents
        $permission = 'SYNCHPURCHASEORDERS'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications
        
        if ($this->errorcode) {
            $this->message = $this->errormessage;
            $this->code = $this->errorcode;
            
            $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The attempt to access the API failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
            
            $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
        } else {
            $this->logger->write("Api : importPurchaseOrders() : Initiating QB Invoice Importation!", 'r');
            
            $inactivityPeriod = 0;
            $sessionAccessToken = NULL;
            $sessionRefreshToken = NULL;
            $sessionAccessTokenExpiry = NULL;
            
            // Retrieve the access token from the database.
            $token_check = new DB\SQL\Mapper($this->db, 'tblsettings');
            $token_check->load(array(
                'TRIM(code)=?',
                'QBACCESSTOKEN'
            ));
            
            if ($token_check->dry()) {
                $this->logger->write("Api : importPurchaseOrders() : The access token is NOT set in the database. Please manually login.", 'r');
                $this->message = 'The access token is NOT set in the database. Please manually login.';
                $this->code = '999';
            } else {
                $this->logger->write("Api : importPurchaseOrders() : The access token was retrieved successfuly from the database", 'r');
                $sessionAccessToken = trim($token_check->value);
                
                // $this->logger->write("Api : importPurchaseOrders() : The access token is " . $sessionAccessToken, 'r');
                
                // set the access token using the auth object
                if ($sessionAccessToken !== null) {
                    
                    $sessionRefreshToken = $this->appsettings['QBREFRESHTOKEN'];
                    if ($this->appsettings['QBSESSIONACCESSTOKENEXPIRY']) {
                        //$sessionAccessTokenExpiry = $this->appsettings['QBSESSIONACCESSTOKENEXPIRY'];
                        $sessionAccessTokenExpiry =  str_replace('/', '-', $this->appsettings['QBSESSIONACCESSTOKENEXPIRY']);
                    } else {
                        $sessionAccessTokenExpiry = date('Y-m-d H:i:s', strtotime('-1 days'));
                    }
                    
                    $this->logger->write("Api : importPurchaseOrders() : The refresh token is " . $sessionRefreshToken, 'r');
                    $this->logger->write("Api : importPurchaseOrders() : The access token expiry is " . $sessionAccessTokenExpiry, 'r');
                    
                    $startDt = new DateTime(date('Y-m-d H:i:s'));
                    $endDt = new DateTime($sessionAccessTokenExpiry);
                    
                    $inactivityPeriod = $startDt->getTimestamp() - $endDt->getTimestamp();
                    
                    $this->logger->write("Api : importPurchaseOrders() : The current time is " . date('Y-m-d H:i:s'), 'r');
                    $this->logger->write("Api : importPurchaseOrders() : The inactivity period is " . $inactivityPeriod, 'r');
                    
                    if ($inactivityPeriod < 0) {
                        $endDate = date('Y-m-d', strtotime('+1 days')); // date('Y-m-d H:i:s', strtotime('+3 days'));
                        $startDate = date('Y-m-d', strtotime("-" . $this->appsettings['ERPPROCPERIOD'] . " days")); // date('Y-m-d H:i:s', strtotime('-3 days'));
                        
                        $endTime = date('H:i:s', strtotime('+1 seconds'));
                        $startTime = date('H:i:s', strtotime("-" . $this->appsettings['ERPPROCPERIODTIME'] . " seconds"));
                        
                        $this->logger->write("Api : importPurchaseOrders() : startDate: " . $startDate, 'r');
                        $this->logger->write("Api : importPurchaseOrders() : endDate: " . $endDate, 'r');
                        
                        $this->logger->write("Api : importPurchaseOrders() : startTime: " . $startTime, 'r');
                        $this->logger->write("Api : importPurchaseOrders() : endTime: " . $endTime, 'r');
                        
                        if ($this->platformMode == 'ERP') {
                            $this->logger->write("Api : importPurchaseOrders() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                            $this->message = 'The platform is not integrated. It is running as an abriged ERP.';
                            $this->code = '001';
                        } else {
                            $this->logger->write("Api : importPurchaseOrders() : The platform is integrated.", 'r');
                            
                            if ($this->integratedErp) {
                                /**
                                 * Check on integrated ERP type
                                 */
                                $this->logger->write("Api : importPurchaseOrders() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                                
                                if (strtoupper($this->integratedErp) == 'QBO') {
                                    $this->logger->write("Api : importPurchaseOrders() : The integrated ERP is Quicbooks Online.", 'r');
                                    
                                    $qry = 'SELECT * FROM PurchaseOrder';
                                    
                                    //$qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "T" . $startTime . "' And Metadata.LastUpdatedTime <= '" . $endDate . "T" . $endTime . "' STARTPOSITION 1 MAXRESULTS 20";
                                    $qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "T" . $startTime . "' And Metadata.LastUpdatedTime <= '" . $endDate . "T" . $endTime . "'";
                                    
                                    $this->logger->write("Api : importPurchaseOrders() : The query is: " . $qry, 'r');
                                    
                                    try {
                                        if ($sessionAccessToken !== null) {
                                            // Create SDK instance
                                            $authMode = $this->appsettings['QBAUTH_MODE'];
                                            $ClientID = $this->appsettings['QBCLIENT_ID'];
                                            $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                            $baseUrl = $this->appsettings['QBBASE_URL'];
                                            $QBORealmID = $this->appsettings['QBREALMID'];
                                            
                                            $dataService = DataService::Configure(array(
                                                'auth_mode' => $authMode,
                                                'ClientID' => $ClientID,
                                                'ClientSecret' => $ClientSecret,
                                                'baseUrl' => $baseUrl,
                                                'refreshTokenKey' => $sessionRefreshToken,
                                                'QBORealmID' => $QBORealmID,
                                                'accessTokenKey' => $sessionAccessToken
                                            ));
                                            
                                            $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                            $dataService->throwExceptionOnError(true);
                                            
                                            $purchaseorders = $dataService->Query($qry);
                                            
                                            $error = $dataService->getLastError();
                                            
                                            if ($error) {
                                                $this->logger->write("Api : importPurchaseOrders() : The operation to download ERP purchase orders was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                                $this->message = 'The operation to download ERP invoices was not successful.';
                                                $this->code = '002';
                                            } else {
                                                // print_r($purchaseorders);
                                                if (isset($purchaseorders)) {
                                                    if ($purchaseorders) {
                                                        $this->logger->write("Api : importPurchaseOrders() : The # of purchase orders is: " . sizeof($purchaseorders), 'r');
                                                        $purchaseorder = new purchaseorders($this->db);
                                                        
                                                        $goods = array();
                                                        $grossamount = 0;
                                                        $itemcount = 0;
                                                        
                                                        $product = new products($this->db);
                                                        $measureunit = new measureunits($this->db);
                                                        
                                                        $purchaseorderdetails = array(
                                                            'id' => NULL,
                                                            'gooddetailgroupid' => NULL,
                                                            'taxdetailgroupid' => NULL,
                                                            'paymentdetailgroupid' => NULL,
                                                            'erpvoucherid' => NULL,
                                                            'erpvoucherno' => NULL,
                                                            'issueddate' => NULL,
                                                            'issuedtime' => NULL,
                                                            'operator' => NULL,
                                                            'currency' => NULL,
                                                            'datasource' => $this->appsettings['DEFAULTDATASOURCE'],
                                                            'netamount' => '0',
                                                            'taxamount' => '0',
                                                            'grossamount' => '0',
                                                            'itemcount' => '0',
                                                            'remarks' => NULL,
                                                            'supplierid' => NULL,
                                                            'grossamountword' => NULL,
                                                            'vouchertype' => "Purchase Order",
                                                            'vouchertypename' => "Purchase Order",
                                                            'SyncToken' => NULL
                                                        );
                                                        
                                                        foreach ($purchaseorders as $elem) {
                                                            try {
                                                                $this->logger->write("Api : importPurchaseOrders() : PO Number: " . $elem->DocNumber, 'r');
                                                                
                                                                $VendorRef = $elem->VendorRef;
                                                                $DocNumber = $elem->DocNumber;
                                                                $CurrencyRef = $elem->CurrencyRef;
                                                                $TxnDate = $elem->TxnDate;
                                                                $POId = $elem->Id;
                                                                $SyncToken = $elem->SyncToken;
                                                                $TxnDate = $elem->TxnDate;
                                                                $POStatus = $elem->POStatus;
                                                                
                                                                if (strtoupper($elem->POStatus) == 'CLOSED') {
                                                                    $purchaseorderdetails['erpvoucherid'] = $POId;
                                                                    $purchaseorderdetails['erpvoucherno'] = $DocNumber;
                                                                    $purchaseorderdetails['supplierid'] = $VendorRef;
                                                                    
                                                                    if (isset($elem->Line)) {
                                                                        //$this->logger->write("Api : importPurchaseOrders() : # of items: " . sizeof($elem->Line), 'r');
                                                                        
                                                                        if (is_array($elem->Line) && sizeof($elem->Line) > 1) {
                                                                            foreach ($elem->Line as $items) {
                                                                                $LineId = $items->Id;
                                                                                $LineNum = $items->LineNum;
                                                                                $Description = $items->Description;
                                                                                $ErpAmount = $items->Amount;
                                                                                $DetailType = $items->DetailType;
                                                                                $this->logger->write("Api : importPurchaseOrders() : Line Description: " . $Description, 'r');
                                                                                
                                                                                if (strtoupper($items->DetailType) == 'ITEMBASEDEXPENSELINEDETAIL') {
                                                                                    if (isset($items->ItemBasedExpenseLineDetail)) {
                                                                                        $ItemRef = $items->ItemBasedExpenseLineDetail->ItemRef;
                                                                                        $UnitPrice = $items->ItemBasedExpenseLineDetail->UnitPrice;
                                                                                        $Qty = $items->ItemBasedExpenseLineDetail->Qty;
                                                                                    }
                                                                                    
                                                                                    $this->logger->write("Api : importPurchaseOrders() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                                    $this->logger->write("Api : importPurchaseOrders() : Unit Price: " . $UnitPrice, 'r');
                                                                                    $this->logger->write("Api : importPurchaseOrders() : Qty: " . $Qty, 'r');
                                                                                    
                                                                                    $product->getByErpCode($ItemRef);
                                                                                    
                                                                                    if ($product->code) {
                                                                                        $measureunit->getByCode($product->measureunit);
                                                                                    } else {
                                                                                        $this->logger->write("Api : importPurchaseOrders() : The Item does not exist on the platform", 'r');
                                                                                    }
                                                                                    
                                                                                    $grossamount = $grossamount + $ErpAmount;
                                                                                    $itemcount = $itemcount + 1;
                                                                                    
                                                                                    $goods[] = array(
                                                                                        'groupid' => NULL,
                                                                                        'item' => $product->name,
                                                                                        'itemcode' => $product->code,
                                                                                        'qty' => $Qty,
                                                                                        'unitofmeasure' => $product->measureunit,
                                                                                        'unitprice' => $UnitPrice,
                                                                                        'total' => $ErpAmount,
                                                                                        'taxid' => '0',
                                                                                        'taxrate' => '0',
                                                                                        'tax' => '0',
                                                                                        'discounttotal' => '0',
                                                                                        'discounttaxrate' => '0',
                                                                                        'discountpercentage' => '0.00',
                                                                                        'ordernumber' => NULL,
                                                                                        'discountflag' => '2',
                                                                                        'deemedflag' => '2',
                                                                                        'exciseflag' => NULL,
                                                                                        'categoryid' => NULL,
                                                                                        'categoryname' => NULL,
                                                                                        'goodscategoryid' => $product->commoditycategorycode,
                                                                                        'goodscategoryname' => NULL,
                                                                                        'exciserate' => NULL,
                                                                                        'exciserule' => NULL,
                                                                                        'excisetax' => NULL,
                                                                                        'pack' => NULL,
                                                                                        'stick' => NULL,
                                                                                        'exciseunit' => NULL,
                                                                                        'excisecurrency' => NULL,
                                                                                        'exciseratename' => NULL,
                                                                                        'taxdisplaycategory' => NULL,
                                                                                        'taxcategory' => NULL,
                                                                                        'taxcategoryCode' => NULL,
                                                                                        'unitofmeasurename' => $measureunit->name
                                                                                    );
                                                                                }
                                                                            } // foreach($elem->Line as $items){
                                                                        } elseif (is_array($elem->Line) && sizeof($elem->Line) == 1) {
                                                                            $LineId = $elem->Line->Id;
                                                                            $LineNum = $elem->Line->LineNum;
                                                                            $Description = $elem->Line->Description;
                                                                            $ErpAmount = $elem->Line->Amount;
                                                                            $DetailType = $elem->Line->DetailType;
                                                                            $this->logger->write("Api : importPurchaseOrders() : Line Description: " . $Description, 'r');
                                                                            
                                                                            if (strtoupper($elem->Line->DetailType) == 'ITEMBASEDEXPENSELINEDETAIL') {
                                                                                if (isset($elem->Line->ItemBasedExpenseLineDetail)) {
                                                                                    $ItemRef = $elem->Line->ItemBasedExpenseLineDetail->ItemRef;
                                                                                    $UnitPrice = $elem->Line->ItemBasedExpenseLineDetail->UnitPrice;
                                                                                    $Qty = $elem->Line->ItemBasedExpenseLineDetail->Qty;
                                                                                }
                                                                                
                                                                                $this->logger->write("Api : importPurchaseOrders() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                                $this->logger->write("Api : importPurchaseOrders() : Unit Price: " . $UnitPrice, 'r');
                                                                                $this->logger->write("Api : importPurchaseOrders() : Qty: " . $Qty, 'r');
                                                                                
                                                                                $product->getByErpCode($ItemRef);
                                                                                
                                                                                if ($product->code) {
                                                                                    $measureunit->getByCode($product->measureunit);
                                                                                } else {
                                                                                    $this->logger->write("Api : importPurchaseOrders() : The Item does not exist on the platform", 'r');
                                                                                }
                                                                                
                                                                                $grossamount = $grossamount + $ErpAmount;
                                                                                $itemcount = $itemcount + 1;
                                                                                
                                                                                $goods[] = array(
                                                                                    'groupid' => NULL,
                                                                                    'item' => $product->name,
                                                                                    'itemcode' => $product->code,
                                                                                    'qty' => $Qty,
                                                                                    'unitofmeasure' => $product->measureunit,
                                                                                    'unitprice' => $UnitPrice,
                                                                                    'total' => $ErpAmount,
                                                                                    'taxid' => '0',
                                                                                    'taxrate' => '0',
                                                                                    'tax' => '0',
                                                                                    'discounttotal' => '0',
                                                                                    'discounttaxrate' => '0',
                                                                                    'discountpercentage' => '0.00',
                                                                                    'ordernumber' => NULL,
                                                                                    'discountflag' => '2',
                                                                                    'deemedflag' => '2',
                                                                                    'exciseflag' => NULL,
                                                                                    'categoryid' => NULL,
                                                                                    'categoryname' => NULL,
                                                                                    'goodscategoryid' => $product->commoditycategorycode,
                                                                                    'goodscategoryname' => NULL,
                                                                                    'exciserate' => NULL,
                                                                                    'exciserule' => NULL,
                                                                                    'excisetax' => NULL,
                                                                                    'pack' => NULL,
                                                                                    'stick' => NULL,
                                                                                    'exciseunit' => NULL,
                                                                                    'excisecurrency' => NULL,
                                                                                    'exciseratename' => NULL,
                                                                                    'taxdisplaycategory' => NULL,
                                                                                    'taxcategory' => NULL,
                                                                                    'taxcategoryCode' => NULL,
                                                                                    'unitofmeasurename' => $measureunit->name
                                                                                );
                                                                            }
                                                                        } else {
                                                                            $this->logger->write("Api : importPurchaseOrders() : There are no line items on the PO", 'r');
                                                                        }
                                                                    } // if(isset($elem->Line)){
                                                                    
                                                                    //$this->logger->write("Api : importPurchaseOrders() : The GOODS count: " . sizeof($goods), 'r');
                                                                    
                                                                    $purchaseorderdetails['operator'] = $this->username_u;
                                                                    $purchaseorderdetails['currency'] = $this->util->getcurrency(trim($CurrencyRef));
                                                                    $purchaseorderdetails['SyncToken'] = $SyncToken;
                                                                    $purchaseorderdetails['issueddate'] = $TxnDate;
                                                                    $purchaseorderdetails['issuedtime'] = $TxnDate;
                                                                    $purchaseorderdetails['itemcount'] = $itemcount;
                                                                    
                                                                    $purchaseorderdetails['grossamount'] = $grossamount;
                                                                    
                                                                    $purchaseorderdetails['remarks'] = "The PO DocNumber " . $DocNumber . " and Id " . $POId . " uploaded using the QBO API";
                                                                    $this->logger->write("Api : importPurchaseOrders() : The Sync Token is " . $SyncToken, 'r');
                                                                    
                                                                    if ($POId) {
                                                                        $purchaseorder->getByErpId($POId);
                                                                        $po_status = null;
                                                                        
                                                                        if ($purchaseorder->dry()) {
                                                                            $this->logger->write("Api : importPurchaseOrders() : The PO does not exist", 'r');
                                                                            $po_status = $this->util->createpurchaseorder($purchaseorderdetails, $goods, $this->userid_u);
                                                                            
                                                                            if ($po_status) {
                                                                                $this->logger->write("Api : importPurchaseOrders() : The PO " . $DocNumber . " was created.", 'r');
                                                                            } else {
                                                                                $this->logger->write("Api : importPurchaseOrders() : The PO " . $DocNumber . " was NOT created.", 'r');
                                                                            }
                                                                        } else {
                                                                            $this->logger->write("Api : importPurchaseOrders() : The PO exists", 'r');
                                                                            $purchaseorderdetails['id'] = $purchaseorder->id;
                                                                            $purchaseorderdetails['gooddetailgroupid'] = $purchaseorder->gooddetailgroupid;
                                                                            $purchaseorderdetails['taxdetailgroupid'] = $purchaseorder->taxdetailgroupid;
                                                                            $purchaseorderdetails['paymentdetailgroupid'] = $purchaseorder->paymentdetailgroupid;
                                                                            
                                                                            if ($purchaseorder->procStatus == '1') {
                                                                                $this->logger->write("Api : importPurchaseOrders() : The PO " . $DocNumber . " is already uploaded into EFRIS.", 'r');
                                                                            } else {
                                                                                $this->logger->write("Api : importPurchaseOrders() : The PO " . $DocNumber . " is NOT uploaded into EFRIS.", 'r');
                                                                                
                                                                                $po_status = $this->util->updatepurchaseorder($purchaseorderdetails, $goods, $this->userid_u);
                                                                                
                                                                                if ($po_status) {
                                                                                    $this->logger->write("Api : importPurchaseOrders() : The PO " . $DocNumber . " was updated.", 'r');
                                                                                } else {
                                                                                    $this->logger->write("Api : importPurchaseOrders() : The PO " . $DocNumber . " was NOT updated.", 'r');
                                                                                }
                                                                            }
                                                                        }
                                                                        
                                                                        // **********START EFRIS UPLOAD********************
                                                                        if ($po_status) {
                                                                            $this->logger->write("Api : importPurchaseOrders() : The PO " . $DocNumber . " will be uploaded into EFRIS.", 'r');
                                                                            
                                                                            if ($POId) {
                                                                                $purchaseorder->getByErpId($POId); // Refresh PO
                                                                                
                                                                                $supplier = new suppliers($this->db);
                                                                                $supplier->getByCode($purchaseorder->supplierid);
                                                                                
                                                                                $vchtype = $purchaseorder->vouchertype;
                                                                                $vchtypename = $purchaseorder->vouchertypename;
                                                                                $vchnumber = $purchaseorder->erpvoucherno;
                                                                                $vchref = $purchaseorder->erpvoucherid;
                                                                                
                                                                                $suppliertin = $supplier->tin;
                                                                                $suppliername = $supplier->legalname;
                                                                                $this->logger->write("Api : importPurchaseOrders() : The supplier TIN is " . $suppliertin, 'r');
                                                                                $this->logger->write("Api : importPurchaseOrders() : The supplier legalname is " . $suppliername, 'r');
                                                                                
                                                                                if ($supplier->countryCode) {
                                                                                    if (trim($supplier->countryCode) == trim($this->appsettings['LOCALCOUNTRYCODE'])) {
                                                                                        $stockintype = $this->appsettings['LOCALPURCHASESTOCKINTYPE']; // local purchase
                                                                                    } else {
                                                                                        $stockintype = $this->appsettings['IMPORTSTOCKINTYPE']; // import
                                                                                    }
                                                                                } else {
                                                                                    $stockintype = $this->appsettings['LOCALPURCHASESTOCKINTYPE']; // local purchase
                                                                                }
                                                                                
                                                                                $this->logger->write("Api : importPurchaseOrders() : The stockin type is " . $stockintype, 'r');
                                                                                
                                                                                $vch_check = new DB\SQL\Mapper($this->db, 'tblgoodsstockadjustment');
                                                                                $vch_check->load(array(
                                                                                    'TRIM(voucherNumber)=? AND TRIM(voucherType)=? AND TRIM(voucherTypeName)=?',
                                                                                    $vchnumber,
                                                                                    $vchtype,
                                                                                    $vchtypename
                                                                                ));
                                                                                $this->logger->write($this->db->log(TRUE), 'r');
                                                                                
                                                                                if ($vch_check->dry()) {
                                                                                    if (trim($stockintype) == $this->appsettings['MANUFACTURESTOCKINTYPE']) { // Manufacture
                                                                                        $productiondate = date('Y-m-d');
                                                                                        $batchno = $vchnumber; // 1
                                                                                        $suppliername = '';
                                                                                        $suppliertin = '';
                                                                                    } else {
                                                                                        $productiondate = '';
                                                                                        $batchno = '';
                                                                                        $suppliername = trim($suppliername);
                                                                                        $suppliertin = trim($suppliertin);
                                                                                        
                                                                                        $errorCount = 0;
                                                                                        
                                                                                        /**
                                                                                         * Validate TIN number, if supplied.
                                                                                         *
                                                                                         * @author frncslubanga@gmail.com
                                                                                         * @date 2022-06-13
                                                                                         *
                                                                                         */
                                                                                        
                                                                                        if (trim($suppliertin) == '' || empty($suppliertin)) {
                                                                                            $this->logger->write("Api : importPurchaseOrders() : The supplier TIN was not provided!", 'r');
                                                                                        } else {
                                                                                            $v_data = $this->util->querytaxpayer($this->userid_u, $suppliertin); // will return JSON.
                                                                                            $v_data = json_decode($v_data, true);
                                                                                            
                                                                                            if (isset($v_data['taxpayer'])) {
                                                                                                // $tin = $v_data['taxpayer']['tin'];
                                                                                                $legalName = $v_data['taxpayer']['legalName'];
                                                                                                
                                                                                                $suppliername = $legalName; // Rename the supplier.
                                                                                            } elseif (isset($v_data['returnCode'])) {
                                                                                                $errorCount = $errorCount + 1;
                                                                                                $this->logger->write("Api : importPurchaseOrders() : The operation to validate the supplier TIN was not successful. The error message is " . $v_data['returnMessage'], 'r');
                                                                                            } else {
                                                                                                $errorCount = $errorCount + 1;
                                                                                                $this->logger->write("Api : importPurchaseOrders() : The operation to validate the supplier TIN was not successful", 'r');
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                    
                                                                                    if ((int) $errorCount == 0) {
                                                                                        $products = array();
                                                                                        $temp = $this->db->exec(array(
                                                                                            'SELECT * FROM tblgooddetails g WHERE g.groupid = ' . $purchaseorder->gooddetailgroupid
                                                                                        ));
                                                                                        
                                                                                        if (isset($temp)) {
                                                                                            
                                                                                            if (sizeof($temp) == 0) {
                                                                                                $this->logger->write("Api : importPurchaseOrders() : No goods were supplied!", 'r');
                                                                                            } else {
                                                                                                foreach ($temp as $obj) {
                                                                                                    $this->logger->write("Api : importPurchaseOrders() : The PRODUCTCODE is: " . trim($obj['itemcode']), 'r');
                                                                                                    $products[] = array(
                                                                                                        'productCode' => trim($obj['itemcode']), // 8762753
                                                                                                        'quantity' => trim($obj['qty']), // 23.0
                                                                                                        'unitPrice' => trim($obj['unitprice']) // 25000.00
                                                                                                    );
                                                                                                }
                                                                                                
                                                                                                $branch = new branches($this->db);
                                                                                                $branch->getByID($this->userbranch_u);
                                                                                                
                                                                                                $data = $this->util->batchstockin($this->userid_u, $branch->uraid, $products, $batchno, $suppliertin, $suppliername, $stockintype, $productiondate); // will return JSON.
                                                                                                
                                                                                                $data = json_decode($data, true);
                                                                                                
                                                                                                if (isset($data['returnCode'])) {
                                                                                                    $this->logger->write("Api : importPurchaseOrders() : The operation to increase stock was not successful. The error message is " . $data['returnMessage'], 'r');
                                                                                                    $this->message = $data['returnMessage'];
                                                                                                    $this->code = $data['returnCode'];
                                                                                                } else {
                                                                                                    if ($data) {
                                                                                                        
                                                                                                        foreach ($data as $elem) {
                                                                                                            $this->logger->write("Api : importPurchaseOrders() : The operation to increase stock was not successful. The error message is " . $elem['returnCode'] . " - " . $data['returnMessage'], 'r');
                                                                                                        }
                                                                                                    } else {
                                                                                                        $this->logger->write("Api : importPurchaseOrders() : The operation to increase stock was successful!", 'r');
                                                                                                        $this->message = 'The operation to increase stock was successful.';
                                                                                                        $this->code = '000';
                                                                                                        
                                                                                                        // Update the table tblpurchaseorders
                                                                                                        try {
                                                                                                            $this->db->exec(array(
                                                                                                                'UPDATE tblpurchaseorders SET procStatus = 1, modifieddt = "' . date('Y-m-d H:i:s') . '", modifiedby = "' . $this->userid_u . '" WHERE id = ' . $purchaseorder->id
                                                                                                            ));
                                                                                                        } catch (Exception $e) {
                                                                                                            $this->logger->write("Api : importPurchaseOrders() : Failed to update table tblpurchaseorders. The error message is " . $e->getMessage(), 'r');
                                                                                                        }
                                                                                                        
                                                                                                        foreach ($temp as $obj) {
                                                                                                            $this->util->logstockadjustment($this->userid_u, trim($obj['itemcode']), $batchno, trim($obj['qty']), $suppliertin, $suppliername, $stockintype, $productiondate, trim($obj['unitprice']), trim($this->appsettings['STOCKINOPERATIONTYPE']), $vchtype, $vchtypename, $vchnumber, NULL, NULL, NULL);
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                                
                                                                                                $this->util->createerpauditlog($this->userid_u, $permission, NULL, NULL, NULL, NULL, NULL, $vchnumber, $vchref, NULL, $this->code, $this->message);
                                                                                            }
                                                                                        } else {
                                                                                            $this->logger->write("Api : importPurchaseOrders() : No goods were supplied!", 'r');
                                                                                            $this->message = 'No goods were supplied.';
                                                                                            $this->code = '999';
                                                                                        }
                                                                                    } else {
                                                                                        $this->logger->write("Api : importPurchaseOrders() : An error occured, please re-upload!", 'r');
                                                                                        $this->message = 'An error occured, please re-upload.';
                                                                                        $this->code = '999';
                                                                                    }
                                                                                } else {
                                                                                    $this->logger->write("Api : importPurchaseOrders() : The purchase order has already been uploaded into EFRIS.", 'r');
                                                                                    $this->message = 'The purchase order has already been uploaded into EFRIS.';
                                                                                    $this->code = '999';
                                                                                }
                                                                            } else {
                                                                                $this->logger->write("Api : importPurchaseOrders() : The PO was not specified.", 'r');
                                                                                $this->message = 'The PO was not specified.';
                                                                                $this->code = '999';
                                                                            }
                                                                        } else {
                                                                            $this->logger->write("Api : importPurchaseOrders() : The PO " . $DocNumber . " will not be uploaded into EFRIS.", 'r');
                                                                        }
                                                                        // **********END EFRIS UPLOAD********************
                                                                    } else {
                                                                        $this->logger->write("Api : importPurchaseOrders() : The PO has no Id.", 'r');
                                                                        $this->message = 'The PO has no Id.';
                                                                        $this->code = '999';
                                                                    }
                                                                } else {
                                                                    $this->logger->write("Api : importPurchaseOrders() : The PO is not Closed. The status is: " . $elem->POStatus, 'r');
                                                                    $this->message = 'The PO is not Closed. The status is: ' . $elem->POStatus;
                                                                    $this->code = '999';
                                                                }
                                                            } catch (Exception $e) {
                                                                $this->logger->write("Api : importPurchaseOrders() : There was an error when processing PO " . $elem->DocNumber . ". The error is " . $e->getMessage(), 'r');
                                                            }
                                                            
                                                            // Empty/Reset some variables?
                                                            $VendorRef = NULL;
                                                            $DocNumber = NULL;
                                                            $CurrencyRef = NULL;
                                                            $TxnDate = NULL;
                                                            $POId = NULL;
                                                            $SyncToken = NULL;
                                                            $TxnDate = NULL;
                                                            $POStatus = NULL;
                                                            $goods = array();
                                                            $grossamount = 0;
                                                            $itemcount = 0;
                                                            //unset($purchaseorderdetails);
                                                        } // foreach($purchaseorders as $elem)
                                                    } else {
                                                        $this->logger->write("Api : importPurchaseOrders() : The operation to download ERP purchase orders did not return records.", 'r');
                                                        $this->message = 'The operation to download ERP purchase orders did not return records.';
                                                        $this->code = '002';
                                                    }
                                                } else {
                                                    $this->logger->write("Api : importPurchaseOrders() : The operation to download ERP purchase orders did not return records.", 'r');
                                                    $this->message = 'The operation to download ERP purchase orders did not return records.';
                                                    $this->code = '002';
                                                }
                                            }
                                            
                                            $this->logger->write("Api : importPurchaseOrders() : The operation to download ERP purchase orders was successful.", 'r');
                                            $this->message = 'The operation to download ERP purchase orders was successful.';
                                            $this->code = '000';
                                        } else {
                                            $this->logger->write("Api : importPurchaseOrders() : The operation to download ERP purchase orders was not successful. Please connect to ERP first.", 'r');
                                            $this->message = 'The operation to download ERP purchase orders was not successful. Please connect to ERP first.';
                                            $this->code = '002';
                                        }
                                    } catch (Exception $e) {
                                        $this->logger->write("Api : importPurchaseOrders() : The operation to download ERP purchase orders was not successful. The error is: " . $e->getMessage(), 'r');
                                        $this->message = 'The operation to download ERP purchase orders was not successful.';
                                        $this->code = '002';
                                    }
                                } else {
                                    $this->logger->write("Api : importPurchaseOrders() : The integrated ERP is unknown.", 'r');
                                    $this->message = 'The integrated ERP is unknown.';
                                    $this->code = '002';
                                }
                            } else {
                                $this->logger->write("Api : importPurchaseOrders() : We are unable to indentify the currently integrated ERP.", 'r');
                                $this->message = ' We are unable to indentify the currently integrated ERP.';
                                $this->code = '002';
                            }
                        }
                    } else {
                        $this->logger->write("Api : importPurchaseOrders() : The access token expired. Please manually login.", 'r');
                        $this->message = 'The access token expired. Please manually login';
                        $this->code = '999';
                    }
                } else {
                    $this->logger->write("Api : importPurchaseOrders() : The access token is NOT set. Please manually login.", 'r');
                    $this->message = 'The access token is NOT set. Please manually login';
                    $this->code = '999';
                }
            }
            
            if ($this->code !== '000') {
                $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The automated task which processes purchase orders failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
                
                $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
            }
        }
        
        $this->response = array(
            "response" => array(
                "responseCode" => $this->code,
                "responseMessage" => $this->message
            ),
            "data" => array()
        );
        
        $len = sizeof($this->response);
        header("CONTENT-LENGTH:" . $len);
        // print $this->response;
        die(json_encode($this->response));
        return;
    }
    
    /**
     * used to import customers from an ERP
     *
     * @name importErpGeneral
     * @return NULL
     * @param
     *            NULL
     */
    function importErpGeneral()
    {
        $operation = NULL; // tblevents
        $permission = NULL; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications
        
        if ($this->errorcode) {
            $this->message = $this->errormessage;
            $this->code = $this->errorcode;
            
            $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The attempt to test the API failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
            
            $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
        } else {
            if ($this->platformMode == 'ERP') {
                $this->logger->write("Api : importErpGeneral() : The platform is not integrated. It is running as an abriged ERP.", 'r');
            } else {
                $this->logger->write("Api : importErpGeneral() : The platform is integrated.", 'r');
                
                if ($this->integratedErp) {
                    /**
                     * Check on integrated ERP type
                     */
                    $this->logger->write("Api : importErpGeneral() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                    
                    if (strtoupper($this->integratedErp) == 'QBO') {
                        $this->logger->write("Api : importErpGeneral() : The integrated ERP is Quicbooks Online.", 'r');
                    } elseif (strtoupper($this->integratedErp) == 'QBD') {
                        $this->logger->write("Api : importErpGeneral() : The integrated ERP is Quicbooks Desktop.", 'r');
                        
                        // We need to make sure the correct timezone is set
                        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
                            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
                        }
                    } elseif (strtoupper($this->integratedErp) == 'SAP') {
                        $this->logger->write("Api : importErpGeneral() : The integrated ERP is SAP.", 'r');
                    } elseif (strtoupper($this->integratedErp) == 'TALLY') {
                        $this->logger->write("Api : importErpGeneral() : The integrated ERP is Tally.", 'r');
                    } else {
                        $this->logger->write("Api : importErpGeneral() : The integrated ERP is unknown.", 'r');
                    }
                } else {
                    $this->logger->write("Api : importErpGeneral() : We are unable to indentify the currently integrated ERP.", 'r');
                }
            }
        }
        
        /*
         * $this->response = array(
         * "response" => array(
         * "responseCode" => $this->code,
         * "responseMessage" => $this->message
         * ),
         * "data" => array()
         * );
         *
         * $len = sizeof($this->response);
         * header ("CONTENT-LENGTH:".$len);
         * //print $this->response;
         * die(json_encode($this->response));
         */
        return;
    }
    
    /**
     * used to import customers from an ERP
     *
     * @name importErpCustomers
     * @return NULL
     * @param
     *            NULL
     */
    function importErpCustomers()
    {
        $operation = NULL; // tblevents
        $permission = 'SYNCCUSTOMERS'; // tblpermissions
        $event = NULL; // tblevents
        $eventnotification = NULL; // tbleventnotifications
        
        if ($this->errorcode) {
            $this->message = $this->errormessage;
            $this->code = $this->errorcode;
            
            $body = '<html><body>
                    <p>Hello,</p></br>
                    <p>The attempt to test the API failed with the following details;</p>
                    <p>Error Code: <b>' . $this->code . '</b></p>
                    <p>Error Message: <b>' . $this->message . '</b></p>
                    </br>
                    <p>Regards,</p>
                    <p>e-TaxWare Team</p>
                    </body></html>';
            
            $this->util->sendemailnotification_v2($this->recipientname, $this->recipientemail, $this->subject, $body, NULL, $this->apikey, $this->version);
        } else {
            if ($this->platformMode == 'ERP') {
                $this->logger->write("Api : importErpCustomers() : The platform is not integrated. It is running as an abriged ERP.", 'r');
            } else {
                $this->logger->write("Api : importErpCustomers() : The platform is integrated.", 'r');
                
                if ($this->integratedErp) {
                    /**
                     * Check on integrated ERP type
                     */
                    $this->logger->write("Api : importErpCustomers() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                    
                    if (strtoupper($this->integratedErp) == 'QBO') {
                        $this->logger->write("Api : importErpCustomers() : The integrated ERP is Quicbooks Online.", 'r');
                    } elseif (strtoupper($this->integratedErp) == 'QBD') {
                        $this->logger->write("Api : importErpCustomers() : The integrated ERP is Quicbooks Desktop.", 'r');
                    } elseif (strtoupper($this->integratedErp) == 'SAP') {
                        $this->logger->write("Api : importErpCustomers() : The integrated ERP is SAP.", 'r');
                    } elseif (strtoupper($this->integratedErp) == 'TALLY') {
                        $this->logger->write("Api : importErpCustomers() : The integrated ERP is Tally.", 'r');
                    } else {
                        $this->logger->write("Api : importErpCustomers() : The integrated ERP is unknown.", 'r');
                    }
                } else {
                    $this->logger->write("Api : importErpCustomers() : We are unable to indentify the currently integrated ERP.", 'r');
                }
            }
        }
        
        /*
         * $this->response = array(
         * "response" => array(
         * "responseCode" => $this->code,
         * "responseMessage" => $this->message
         * ),
         * "data" => array()
         * );
         *
         * $len = sizeof($this->response);
         * header ("CONTENT-LENGTH:".$len);
         * //print $this->response;
         * die(json_encode($this->response));
         */
        return;
    }
    
    /**
     * invoke before any session
     *
     * @name beforeroute
     * @return NULL
     * @param
     *            NULL
     *
     */
    function beforeroute()
    {
        $this->logger->write("Api : beforeroute() : Checking client details", 'r');
        $REMOTE_ADDR = $this->f3->get('SERVER.REMOTE_ADDR');
        $REMOTE_HOST = $this->f3->get('SERVER.REMOTE_HOST');
        $REMOTE_PORT = $this->f3->get('SERVER.REMOTE_PORT');
        $REMOTE_USER = $this->f3->get('SERVER.REMOTE_USER');
        $REDIRECT_REMOTE_USER = $this->f3->get('SERVER.REDIRECT_REMOTE_USER');
        $HTTP_X_FORWARDED_FOR = $this->f3->get('SERVER.HTTP_X_FORWARDED_FOR');
        
        $this->logger->write("Api : beforeroute() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
        $this->logger->write("Api : beforeroute() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
        
        $url_components = parse_url($this->f3->get('SERVER.REQUEST_URI'));
        parse_str($url_components['query'], $this->params);
        
        $API_KEY = trim($this->params['apikey']);
        
        $this->logger->write("Api : beforeroute() : The apikey is: " . $API_KEY, 'r');
        
        $this->logger->write("Api : beforeroute() : REMOTE_ADDR = " . $REMOTE_ADDR, 'r');
        $this->logger->write("Api : beforeroute() : REMOTE_HOST = " . $REMOTE_HOST, 'r');
        $this->logger->write("Api : beforeroute() : REMOTE_PORT = " . $REMOTE_PORT, 'r');
        $this->logger->write("Api : beforeroute() : REMOTE_USER = " . $REMOTE_USER, 'r');
        $this->logger->write("Api : beforeroute() : REDIRECT_REMOTE_USER = " . $REDIRECT_REMOTE_USER, 'r');
        $this->logger->write("Api : beforeroute() : HTTP_X_FORWARDED_FOR = " . $HTTP_X_FORWARDED_FOR, 'r');
        
        if ($this->platformMode == 'ERP') {
            $this->logger->write("Api : beforeroute() : The platform is not integrated. It is running as an abriged ERP.", 'r');
        } else {
            $this->logger->write("Api : beforeroute() : The platform is integrated.", 'r');
            
            if ($this->integratedErp) {
                /**
                 * Check on integrated ERP type
                 */
                $this->logger->write("Api : beforeroute() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                
                if (strtoupper($this->integratedErp) == 'QBD') {
                    $this->logger->write("Api : beforeroute() : The integrated ERP is Quicbooks Desktop.", 'r');
                    
                    try {
                        $this->userid = $this->appsettings['APIUSERID'];
                        $user = new users($this->db);
                        $user->getByID($this->userid);
                        
                        $this->logger->write("Api : beforeroute() : The current user is: " . $this->userid, 'r');
                        $this->username_u = $user->username;
                        $this->userbranch_u = $user->branch;
                    } catch (Exception $e) {
                        $this->logger->write("Api : beforeroute() : The error message is " . $e->getMessage(), 'r');
                        
                        $this->message = 'An internal error occured!';
                        $this->code = '1000';
                        
                        $this->errorcode = '1000';
                        $this->errormessage = 'An internal error occured!';
                        
                        return;
                    }
                } else {
                    // $this->logger->write("Api : beforeroute() : The integrated ERP is unknown.", 'r');
                    
                    // Pick the JSON content from the client
                    $this->json = file_get_contents('php://input');
                    $this->logger->write("Api : beforeroute() : Raw body content is: " . $this->json, 'r');
                    
                    // Replace special characters
                    /*
                     * $this->xml = htmlspecialchars_decode($this->xml, ENT_XML1);
                     * $this->logger->write("Api : beforeroute() : The xml after replacing special xters" . $this->xml, 'r');
                     */
                    
                    $json = json_decode($this->json, TRUE); // convert JSON into array
                    
                    if (isset($json)) {
                        if (sizeof($json) == 0 && empty($API_KEY)) {
                            
                            $this->message = 'No parameters were sent!';
                            $this->code = '1000';
                            
                            $this->errorcode = '1000';
                            $this->errormessage = 'No parameters were sent!';
                            
                            return;
                            /*
                             * } elseif (!empty($API_KEY)) {
                             * //The API KEY was sent as part of a GET call
                             * $this->logger->write("Api : beforeroute() : The apikey was sent in the GET call", 'r');
                             * $this->logger->write("Api : beforeroute() : The apikey is: " . $API_KEY, 'r');
                             */
                        } else {
                            $this->logger->write("Api : beforeroute() : The apikey is: " . $json['APIKEY'], 'r');
                            
                            if ($json['APIKEY']) {
                                $this->apikey = trim($json['APIKEY']);
                            } else {
                                $this->apikey = $API_KEY;
                            }
                            
                            $this->logger->write("Api : beforeroute() : The new apikey is: " . $this->apikey, 'r');
                            
                            if (empty($this->apikey)) {
                                $this->message = 'No API Key was specified';
                                $this->code = '1001';
                                
                                $this->errorcode = '1001';
                                $this->errormessage = 'No API Key was specified';
                                
                                return;
                            } else {
                                $apikey_check = new DB\SQL\Mapper($this->db, 'tblapikeys');
                                $apikey_check->load(array(
                                    'apikey=? AND status=? AND expirydt > NOW()',
                                    addslashes($this->apikey),
                                    $this->appsettings['APIKEYENABLEDSTATUS']
                                ));
                                $this->logger->write($this->db->log(TRUE), 'r');
                                
                                if ($apikey_check->dry()) {
                                    $this->logger->write("Api : beforeroute() : The api key does not exist or is inactive or expired", 'r');
                                    $this->code = '1002';
                                    $this->message = 'The api key does not exist or is inactive or expired. Please contact your system administrator!';
                                    
                                    $this->errorcode = '1002';
                                    $this->errormessage = 'The api key does not exist or is inactive or expired. Please contact your system administrator!';
                                    
                                    return;
                                } else {
                                    $this->logger->write("Api : beforeroute() : Checking the version of the client", 'r');
                                    $this->version = $json['VERSION'];
                                    
                                    if (trim($this->version) == $this->appsettings['APPVERSION']) {
                                        $this->logger->write("Api : beforeroute() : The client version " . trim($this->version) . " and api version " . $this->appsettings['APPVERSION'] . " match", 'r');
                                    } else {
                                        $this->logger->write("Api : beforeroute() : The versions do not match", 'r');
                                        $this->code = '1003';
                                        $this->message = 'The plugin version does not match. Please contact your system administrator!';
                                        
                                        $this->errorcode = '1003';
                                        $this->errormessage = 'The plugin version does not match. Please contact your system administrator!';
                                        
                                        return;
                                    }
                                    
                                    $this->logger->write("Api : beforeroute() : Retrieving permissions of the api key", 'r');
                                    
                                    /**
                                     * 1.
                                     * Get the api key's permissions, both inherited & customised
                                     * 2. Assign them to the permissions variable
                                     */
                                    $apikeypg = ! empty($apikey_check->permissiongroup) ? $apikey_check->permissiongroup : 'NULL'; // user-specific permission
                                    $this->logger->write("Api : beforeroute() : PERMISSION GROUP = " . $apikeypg, 'r');
                                    
                                    $data = array();
                                    $pr = $this->db->exec(array(
                                        'SELECT DISTINCT p.code, p.value FROM tblpermissiondetails p WHERE p.groupid IN (' . $apikeypg . ')'
                                    ));
                                    foreach ($pr as $obj) {
                                        $data[$obj['code']] = $obj['value']; // insert a KEY/VALUE pair for each permission
                                    }
                                    
                                    $this->permissions = $data;
                                    
                                    $this->logger->write("Api : beforeroute() : Retrieving permissions of the current user", 'r');
                                    /**
                                     * 1.
                                     * Get the user's permissions, both inherited & customised
                                     * 2. Assign them to the userpermissions variable
                                     */
                                    $this->logger->write("Api : beforeroute() : The erp user is: " . $json['ERPUSER'], 'r');
                                    $user_u = new users($this->db);
                                    $apiuser = trim($json['ERPUSER']);
                                    $user_u->getByErpUserCode(strtoupper($apiuser), $this->appsettings['ACTIVEUSERSTATUSID']);
                                    $this->logger->write($this->db->log(TRUE), 'r');
                                    
                                    $this->logger->write("Api : beforeroute() : The current user is: " . $apiuser, 'r');
                                    $this->userid_u = $user_u->id;
                                    $this->username_u = $user_u->username;
                                    $this->userbranch_u = $user_u->branch;
                                    
                                    $userpg = ! empty($user_u->permissiongroup) ? $user_u->permissiongroup : 'NULL'; // user-specific permission
                                    $this->logger->write("Api : beforeroute() : PERMISSION GROUP = " . $userpg, 'r');
                                    
                                    $data = array();
                                    $pr = $this->db->exec(array(
                                        'SELECT DISTINCT p.code, p.value FROM tblpermissiondetails p WHERE p.groupid IN (' . $userpg . ')'
                                    ));
                                    foreach ($pr as $obj) {
                                        $data[$obj['code']] = $obj['value']; // insert a KEY/VALUE pair for each permission
                                    }
                                    
                                    $this->userpermissions = $data;
                                    
                                    // update lastaccessdt for the API
                                    try {
                                        $this->db->exec(array(
                                            "UPDATE tblapikeys SET lastaccessdt = '" . date('Y-m-d H:i:s') . "' WHERE apikey = '" . $this->apikey . "'"
                                        ));
                                    } catch (Exception $e) {
                                        $this->logger->write("Api : beforeroute() : The operation to update the lastaccessdt for the Api was not successful. The error messages is " . $e->getMessage(), 'r');
                                    }
                                }
                            }
                        }
                    } else {
                        $this->message = 'No payload was submitted!';
                        $this->code = '1009';
                        
                        $this->errorcode = '1009';
                        $this->errormessage = 'No payload was submitted!';
                        
                        return;
                    }
                }
            } else {
                $this->logger->write("Api : beforeroute() : We are unable to indentify the currently integrated ERP.", 'r');
            }
        }
        
        // Clear the response
        $this->message = NULL;
        $this->code = NULL;
        $this->response = NULL;
        
        $this->errorcode = NULL;
        $this->errormessage = NULL;
    }
    
    /**
     * invoke after any session
     *
     * @name beforeroute
     * @return NULL
     * @param
     *            NULL
     *
     */
    function afterroute()
    {
        $this->logger->write("Api : afterroute() : Cleaning up", 'r');
        
        // Wipe the content
        $this->xml = NULL;
        $this->message = NULL;
        $this->code = NULL;
        $this->action = NULL;
        $this->response = NULL;
        $this->params = NULL;
        
        $this->errorcode = NULL;
        $this->errormessage = NULL;
        
        $this->apikey = NULL;
        $this->version = NULL;
        
        $this->permissions = NULL;
        $this->userpermissions = NULL;
        
        $this->json = NULL;
    }
    
    /**
     *
     * Constructor for the Api class
     *
     * @name __constructor
     * @return NULL
     * @param
     *            NULL
     *
     */
    function __construct()
    {
        $f3 = Base::instance();
        $this->f3 = $f3;
        
        $db = new DB\SQL($f3->get('dbserver'), $f3->get('dbuser'), $f3->get('dbpwd'), array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ));
        
        $this->db = $db;
        
        $logger = new Log('api.log');
        $this->logger = $logger;
        
        $data = array();
        $setting = new settings($db);
        $settings = $setting->getNoneSensitive();
        
        foreach ($settings as $obj) {
            $data[$obj['code']] = $obj['value']; // insert a KEY/VALUE pair for each setting
        }
        
        $this->appsettings = $data;
        
        $this->userid = $this->appsettings['APIUSERID'];
        $user = new users($this->db);
        $user->getByID($this->userid);
        $this->username = $user->username;
        
        $this->recipientname = $this->appsettings['SYSTEMALERTSRECIPIENTNAME'];
        $this->recipientemail = $this->appsettings['SYSTEMALERTSRECIPIENTEMAIL'];
        $this->emailhost = $this->appsettings['SYSTEMEMAILHOST'];
        $this->emailport = $this->appsettings['SYSTEMEMAILPORT'];
        
        $this->subject = 'e-TaxWare: System Error (' . $this->appsettings['APPDOMAIN'] . ')';
        
        $util = new Utilities();
        $this->util = $util;
        
        $vat_check = new DB\SQL\Mapper($this->db, 'tbltaxtypes');
        $vat_check->load(array(
            'TRIM(code)=?',
            $this->appsettings['EFRIS_VAT_TAX_TYPE_CODE']
        ));
        
        if ($vat_check->dry()) {
            $this->logger->write("Api : beforeroute() : The tax payer is not VAT registered", 'r');
            $this->vatRegistered = 'N';
        } else {
            $this->logger->write("Api : beforeroute() : The tax payer is VAT registered", 'r');
            $this->vatRegistered = 'Y';
        }
        
        $platformModeCheck = new DB\SQL\Mapper($this->db, 'tblplatformmode');
        $platformModeCheck->load(array(
            'TRIM(code)=?',
            $this->appsettings['PLATFORMODE']
        ));
        
        if ($platformModeCheck->dry()) {
            $this->logger->write("Api : __construct() : The Platform Mode is not set", 'r');
            $this->platformMode = NULL;
        } else {
            $this->logger->write("Api : __construct() : The Platform Mode is set to: " . $platformModeCheck->name, 'r');
            $this->platformMode = $platformModeCheck->name;
        }
        
        $efrisModeCheck = new DB\SQL\Mapper($this->db, 'tblefrismode');
        $efrisModeCheck->load(array(
            'TRIM(code)=?',
            $this->appsettings['EFRISMODE']
        ));
        
        if ($efrisModeCheck->dry()) {
            $this->logger->write("Api : __construct() : The EFRIS Mode is not set", 'r');
            $this->efrisMode = NULL;
        } else {
            $this->logger->write("Api : __construct() : The EFRIS Mode is set to: " . $efrisModeCheck->name, 'r');
            $this->efrisMode = $efrisModeCheck->name;
        }
        
        $integratedErpCheck = new DB\SQL\Mapper($this->db, 'tblerptypes');
        $integratedErpCheck->load(array(
            'TRIM(code)=?',
            $this->appsettings['ERPTYPECODE']
        ));
        
        if ($integratedErpCheck->dry()) {
            $this->logger->write("Api : __construct() : The integrated ERP is not set", 'r');
            $this->integratedErp = NULL;
        } else {
            $this->logger->write("Api : __construct() : The integrated ERP is set to: " . $integratedErpCheck->name, 'r');
            $this->integratedErp = $integratedErpCheck->code;
        }
    }
}
?>