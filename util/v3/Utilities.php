<?php

/**
 * This file is part of the etaxware system
 * The is the utilities class
 * @date: 11-04-2026
 * @file: Utilities.php
 * @path: ./util/v3/Utilities.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @version    3.0.0
 */
use PhpOffice\PhpSpreadsheet\IOFactory;

class Utilities
{
    protected $f3;// store an instance of base
    protected $db;// store database connection here
    protected $logger;
    protected $appsettings;// store the setting details here
    protected $userpermissions;// store the permission details here

    protected $username;
    protected $userid;
    protected $branch;
    protected $emailUrl;

    protected $vatRegistered;


    /**
     * @name sendemailnotification
     * @desc send an email notification
     * @return $status boolean
     */
    public function sendemailnotification($recipientname, $recipientemail, $subject, $body, $attachments, $apikey, $version)
    {
        $status = true;
        $response  =  "";
        $request  =  "";
        $web = \Web::instance();
        $url = 'http://127.0.0.1:8080/etaxware-api/sendmail';//api endpoint
        $header = array('Content-Type: application/xml');

        $recipientname = trim($recipientname);
        $recipientemail = trim($recipientemail);
        $subject = trim($subject);
        $body = trim($body);


        $request  =  '<?xml version="1.0" encoding="UTF-8"?>';
        $request .=  '<REQUEST>';
        $request .=  '<VERSION>' . htmlspecialchars($version) . '</VERSION>';
        $request .=  '<ERPUSER></ERPUSER>';
        $request .=  '<WINDOWSUSER></WINDOWSUSER>';
        $request .=  '<IPADDRESS></IPADDRESS>';
        $request .=  '<MACADDRESS></MACADDRESS>';
        $request .=  '<SYSTEMNAME></SYSTEMNAME>';
        $request .=  '<APIKEY>' . htmlspecialchars($apikey) . '</APIKEY>';
        $request .=  '<RECIPIENTNAME>' . htmlspecialchars($recipientname) . '</RECIPIENTNAME>';
        $request .=  '<RECIPIENTEMAIL>' . htmlspecialchars($recipientemail) . '</RECIPIENTEMAIL>';
        $request .=  '<SUBJECT>' . htmlspecialchars($subject) . '</SUBJECT>';
        $request .=  '<BODY>' . htmlspecialchars($body) . '</BODY>';
        $request .=  '<ATTACHMENTS>' . htmlspecialchars($attachments) . '</ATTACHMENTS>';
        $request .=  '</REQUEST>';

        $options = array(
            'method'  => 'POST',
            'content' => $request,
            'header' => $header
        );

        $this->logger->write("Utilities : sendemailnotification() : The request is " . $request, 'r');

        $response = $web->request($url, $options);


        $this->logger->write("Utilities : sendemailnotification() : The response is " . $response['body'], 'r');


        if (trim($response['body']) == '000') {
            $status = true;
        } else {
            $status = false;
        }

        $this->logger->write("Utilities : sendemailnotification() : The final status is " . $status, 'r');
        return $status;
    }


    /**
     * @name sendemailnotification_v2
     * @desc send an email notification. This version communicates with the sendemail endpoint using JSON
     * @return $status boolean
     */
    public function sendemailnotification_v2($recipientname, $recipientemail, $subject, $body, $attachments, $apikey, $version)
    {
        $status = true;
        $response  =  "";
        $request  =  "";
        $web = \Web::instance();

        if (trim($this->emailUrl)) {
            $url = $this->emailUrl;
        } else {
            $url = 'http://127.0.0.1:8080/etaxware-api/sendmail';//api endpoint
        }

        $header = array('Content-Type: application/json');

        $recipientname = trim($recipientname);
        $recipientemail = trim($recipientemail);
        $subject = trim($subject);
        $body = trim($body);

        // prepare json response
        $request = array(
            "VERSION" => $version,
            "ERPUSER" => "",
            "WINDOWSUSER" => "",
            "IPADDRESS" => "",
            "MACADDRESS" => "",
            "SYSTEMNAME" => "",
            "APIKEY" => $apikey,
            "RECIPIENTNAME" => $recipientname,
            "RECIPIENTEMAIL" => $recipientemail,
            "SUBJECT" => $subject,
            "BODY" => $body,
            "ATTACHMENTS" => array()
        );


        $request = json_encode($request);
        $this->logger->write("Utilities : sendemailnotification_v2() : The request is " . $request, 'r');

        $options = array(
            'method'  => 'POST',
            'content' => $request,
            'header' => $header
        );

        $response = $web->request($url, $options);

        $this->logger->write("Utilities : sendemailnotification_v2() : The response is " . $response['body'], 'r');
        $j_response = json_decode($response['body'], true);

        $this->logger->write("Utilities : sendemailnotification_v2() : The responseCode is: " . $j_response['response']['responseCode'], 'r');
        $this->logger->write("Utilities : sendemailnotification_v2() : The responseMessage is: " . $j_response['response']['responseMessage'], 'r');

        if (trim($j_response['response']['responseCode']) == '000') {
            $status = true;
        } else {
            $status = false;
        }

        return $status;
    }

    /**
     * @name getcurrency
     * @desc return the standard name for a currency
     * @return string
     * @param $no string
     *
     */
    public function decodeapprovestatus($code)
    {
        /**
         * 1. Cleanup the status
         * 2. Search the approve status table for the equivalent
         */
        $this->logger->write("Utilities : decodeapprovestatus() : The raw status is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblcdnoteapprovestatuses');
        $code_check->load(array('UPPER(code)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : decodeapprovestatus() : The status does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->name;
        }

        $this->logger->write("Utilities : decodeapprovestatus() : The status name is " . $value, 'r');
        return $value;
    }

    /**
     * @name getnotifications
     * @desc Retrieve notifications from the table tblnotifications;
     *
     * @return array
     * @param $userid int
     * @param $status int
     * @param $entitytype int
     * @param $type int
     */
    public function getnotifications($id = null, $userid = null, $status = null, $entitytype = null, $type = null)
    {
        //$this->logger->write("Utilities : getnotifications() : Processing notifications", 'r');

        //$this->logger->write("Utilities : getnotifications() : id = " . $id, 'r');
        //$this->logger->write("Utilities : getnotifications() : userid = " . $userid, 'r');
        //$this->logger->write("Utilities : getnotifications() : status = " . $status, 'r');
        //$this->logger->write("Utilities : getnotifications() : entitytype = " . $entitytype, 'r');
        //$this->logger->write("Utilities : getnotifications() : type = " . $type, 'r');

        $sql = '';

        $data = array();

        if (!is_null($id)) {
            $sql = "SELECT * FROM tblnotifications WHERE notificationtype = " . $type . " AND id = " . $id . " LIMIT " . $this->appsettings['DEFAULTNOTCOUNT'];
        } else {
            if (is_null($userid)) {
                $sql = "SELECT * FROM tblnotifications WHERE notificationtype = " . $type . " AND entitytype = " . $entitytype . " AND status = " . $status . " ORDER BY id DESC LIMIT " . $this->appsettings['DEFAULTNOTCOUNT'];
            } else {
                $sql = "SELECT * FROM tblnotifications WHERE notificationtype = " . $type . " AND entitytype = " . $entitytype . " AND status = " . $status . " AND recipient = '" . $userid . "' ORDER BY id DESC LIMIT " . $this->appsettings['DEFAULTNOTCOUNT'];
            }
        }

        try {
            //$this->logger->write("Utilities : getnotifications() : The SQL is " . $sql, 'r');
            $dtls = $this->db->exec($sql);
        } catch (Exception $e) {
            $this->logger->write("Utilities : getnotifications() : The operation to retrive notifications was not successful. The error messages is " . $e->getMessage(), 'r');
            return $data;
        }

        foreach ($dtls as $obj) {
            $data [] = $obj;
        }

        return $data;
    }

    /**
     *	@name createinappnotification
     *  @desc Create an In-App notification
     *	@return bool status
     *	@param NULL
     **/
    public function createinappnotification($notificationtype = null, $notificationsubtype = null, $entitytype = null, $module = null, $submodule = null, $operation = null, $event = null, $eventnotification = null, $status = null, $recipient = null, $notification = null)
    {
        //$this->logger->write("Utilities : createinappnotification() : Creating of in-app notification started", 'r');

        if (!$notificationtype) {
            $notificationtype = $this->appsettings['INAPPNOTIFICATION'];
        }

        if (!$notificationsubtype) {
            $notificationsubtype = $this->appsettings['INFONOTIFICATION'];
        }

        if (!$entitytype) {
            $entitytype = $this->appsettings['USERENTITYTYPE'];
        }

        if (!$status) {
            $status = $this->appsettings['DEFAULTNOTIFICATIONSTATUS'];
        }

        if (!$module) {
            $module = 'NULL';
        }

        if (!$submodule) {
            $submodule = 'NULL';
        }
        if (!$operation) {
            $operation = 'NULL';
        }
        if (!$event) {
            $event = 'NULL';
        }
        if (!$eventnotification) {
            $eventnotification = 'NULL';
        }

        if ($recipient && $notification) {
            //sanitize the notification text
            $values = $notificationtype . ", " . $notificationsubtype . ", " . $entitytype . ", " . $recipient . ", " . $module . ", " . $submodule . ", " . $operation . ", " . $event . ", " . $eventnotification . ", " . $status . ", '" . addslashes($notification) . "', " . "NOW(), " . $recipient . ", NOW(), " . $recipient;

            $sql = 'INSERT INTO tblnotifications (notificationtype, notificationsubtype, entitytype, recipient, module, submodule, operation, event, eventnotification, status, notification, inserteddt, insertedby, modifieddt, modifiedby)
                        VALUES (' . $values . ')';
            try {
                //$this->logger->write("Utilities : getnotifications() : The SQL is " . $sql, 'r');
                $this->db->exec(array($sql));
                $this->logger->write("Utilities : createinappnotification() : The operation to create an in-app notification was successful", 'r');
                return true;
            } catch (Exception $e) {
                $this->logger->write("Api : createinappnotification() : Error " . $e->getMessage(), 'r');
                return false;
            }
        } else {
            $this->logger->write("Utilities : createinappnotification() : There was no notification or recipient specified", 'r');
            return false;
        }
    }

    /**
     * @name createauditlog
     * @desc Log user activity
     * @return NULL
     * @param $activity string, $userid int
     *
     */
    public function createauditlog($userid, $activity)
    {
        //sanitize the activity text
        $values = $userid . ", '" . addslashes($activity) . "', " . "NOW(), " . $userid . ", NOW(), " . $userid;


        $sql = 'INSERT INTO tblauditlogs (userid, description, inserteddt, insertedby, modifieddt, modifiedby)
                    VALUES (' . $values . ')';

        try {
            $this->db->exec(array($sql));
            ////$this->logger->write("Utilities : createauditlog() : Query was executed successfully", 'r');
        } catch (Exception $e) {
            $this->logger->write("Utilities : createauditlog() : Error " . $e->getMessage(), 'r');
        }
    }

    /**
     * @name createerpauditlog
     * @desc Log ERP user activity
     * @return NULL
     * @param $userid int, $activity string, $windowsuser string, $ipaddress string, $macaddress string, $systemname string, $payload string, $voucherNumber string, $voucherRef string, $productCode string, $responseCode string, $responseMessage string
     */
    public function createerpauditlog($userid, $activity, $windowsuser, $ipaddress, $macaddress, $systemname, $payload, $voucherNumber = null, $voucherRef = null, $productCode = null, $responseCode = null, $responseMessage = null)
    {
        $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;

        //sanitize the activity text
        $values = "'" . addslashes($activity) . "', '" . addslashes($windowsuser) . "', '" . addslashes($ipaddress) . "', '" . addslashes($macaddress) . "', '" . addslashes($systemname) . "', '" . addslashes($payload) . "', '" . addslashes($voucherNumber) . "', '" . addslashes($voucherRef) . "', '" . addslashes($productCode) . "', '" . addslashes($responseCode) . "', '" . addslashes($responseMessage) . "', NOW(), " . $userid . ", NOW(), " . $userid;

        $sql = 'INSERT INTO tblerpauditlogs (description, windowsuser, ipaddress, macaddress, systemname, payload, voucherNumber, voucherRef, productCode, responseCode, responseMessage, inserteddt, insertedby, modifieddt, modifiedby)
                    VALUES (' . $values . ')';

        try {
            $this->db->exec(array($sql));
            ////$this->logger->write("Utilities : createerpauditlog() : Query was executed successfully", 'r');
        } catch (Exception $e) {
            $this->logger->write("Utilities : createerpauditlog() : Error " . $e->getMessage(), 'r');
        }
    }

    /**
     *	@name generatedirectorypath
     *  @desc Generate an absolute path to a directory
     *	@return $userid string
     *	@param $diretorytype string
     **/
    public function generatedirectorypath($userid, $diretorytype)
    {
        $path = '';

        $user = new users($this->db);
        $user->getByID($userid);

        $HOME = $this->appsettings['HOME'];
        $GENERALDOCPATH = $this->appsettings['GENERALDOCPATH'];
        $USERNAME = $user->username;

        //{{$GENERALDOCPATH}}\{{$USERNAME}}\incoming
        if ($userid) {
            $this->logger->write("Utilities : generatedirectorypath() : The directory owner is: " . $userid, 'r');

            if ($diretorytype == 'INC') {
                $path = $this->appsettings['INCOMINGDIRNAME'];
            } elseif ($diretorytype == 'TMP') {
                $path = $this->appsettings['TEMPDIRNAME'];
            } elseif ($diretorytype == 'WORK') {
                $path = $this->appsettings['WORKDIRNAME'];
            } elseif ($diretorytype == 'ERR') {
                $path = $this->appsettings['ERRORDIRNAME'];
            } elseif ($diretorytype == 'ARC') {
                $path = $this->appsettings['ARCHIVEDIRNAME'];
            } elseif ($diretorytype == 'GEN') {
                $path = $this->appsettings['GENERALDOCPATH'];
            } elseif ($diretorytype == 'RPT') {
                $path = $this->appsettings['REPORTSIRNAME'];
            } else {
                $this->logger->write("Utilities : generatedirectorypath() : The directory type was not specified", 'r');
            }

            $this->logger->write("Utilities : generatedirectorypath() : The temporary directory is: " . $path, 'r');
            $path = str_replace('{{$GENERALDOCPATH}}', $GENERALDOCPATH, $path);
            $path = str_replace('{{$HOME}}', $HOME, $path);
            $path = str_replace('{{$USERNAME}}', $USERNAME, $path);
        } else {
            $this->logger->write("Utilities : generatedirectorypath() : The directory owner was not specified", 'r');
        }
        $this->logger->write("Utilities : generatedirectorypath() : The generated directory is: " . $path, 'r');
        return  $path;
    }

    /**
     * @name applyDiscount
     * @desc apply a discount to the goods
     * @return array
     * @param array $goods, array $taxes, float $discountAppBalance, string $customerType, string $customerTin, string $pricevatinclusive
     *
     */
    public function applyDiscount($goods, $taxes, $discountAppBalance, $customerType, $customerTin, $pricevatinclusive = "YES")
    {
        /**
         * 1. Ensure the goods array is not empty
         * 2. Ensure the discount is greater than 0
         */

        $tempGoods = array();
        $tempTaxes = array();
        $tempDiscountBalance = 0;

        $deemedflag = 'NO';
        $discountflag = 'NO';

        $pricevatinclusive = empty($pricevatinclusive) ? 'YES' : strtoupper($pricevatinclusive);//No


        $tr = new taxrates($this->db);
        $taxid = null;
        $taxcode = null;
        $taxname = null;
        $taxcategory = null;
        $taxdisplaycategory = null;
        $taxdescription = null;
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

        if (isset($goods)) {
            if (sizeof($goods) == 0) {
                $this->logger->write("Utilities : applyDiscount() : The goods array is empty", 'r');
                return array($goods, $taxes);
            }
        } else {
            $this->logger->write("Utilities : applyDiscount() : The goods array is not set", 'r');
            return array($goods, $taxes);
        }

        if ($discountAppBalance <= 0) {
            $this->logger->write("Utilities : applyDiscount() : The discount balance is <= 0", 'r');
            return array($goods, $taxes);
        } else {
            $this->logger->write("Utilities : applyDiscount() : discountAppBalance: " . $discountAppBalance, 'r');
            $tempDiscountBalance = $discountAppBalance;
        }

        if (trim($customerType) == '' || empty(trim($customerType))) {
            $this->logger->write("Utilities : applyDiscount() : The customer type was not specified", 'r');
            return array($goods, $taxes);
        } else {
            $this->logger->write("Utilities : applyDiscount() : The customer type: " . $customerType, 'r');
        }

        foreach ($goods as $obj) {
            $qty = $obj['qty'];
            $unit = $obj['unitprice'];
            $amount = $obj['total'];

            if ($tempDiscountBalance > $amount) {
                $discount = $amount;
                $tempDiscountBalance = $tempDiscountBalance - $amount;
            } else {
                $discount = $tempDiscountBalance;
            }

            $product->getByErpCode($obj['itemcode']);

            if ($product->code) {
                $measureunit->getByCode($product->measureunit);
            } else {
                $this->logger->write("Utilities : applyDiscount() : The Item does not exist on the platform", 'r');
                return array($goods, $taxes);
            }


            $taxid = self::getinvoicetaxrate_v2($this->appsettings['DEFAULTINVOICEINDUSTRY'], $customerType, $product->code, $customerTin, $this->appsettings['OVERRIDE_TAXRATE_FLAG'], $this->appsettings['TAXPAYER_CHECK_FLAG']);
            $this->logger->write("Utilities : applyDiscount() : The computed TAXID is " . $taxid, 'r');

            if (!$taxid) {
                $taxid = $this->appsettings['STANDARDTAXRATE'];
            }


            if ($taxid == $this->appsettings['DEEMEDTAXRATE']) {
                $deemedflag = 'YES';
            } else {
                $deemedflag = 'NO';
            }

            $this->logger->write("Utilities : applyDiscount() : The final TAXID is " . $taxid, 'r');

            $tr = new taxrates($this->db);
            $tr->getByID($taxid);
            $taxcode = $tr->code;
            $taxname = $tr->name;
            $taxcategory = $tr->category;
            $taxdisplaycategory = $tr->displayCategoryCode;
            $taxdescription = $tr->description;
            $rate = $tr->rate ? $tr->rate : 0;


            $this->logger->write("Utilities : applyDiscount() : unit: " . $unit, 'r');

            if (strtoupper(trim($pricevatinclusive)) == 'YES') {
                //Use the figures as they come from the ERP
                $total = ($qty * $unit);//??

                //$discount = ($discountpct/100) * $total;

                $discount = ($discount * $rate) + $discount; //apply tax to the discount
                $discountpct = $discount / $total; //determing the discount pct

                /**
                 * Modification Date: 2021-01-26
                 * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                 * */
                //$gross = $total - $discount;
                $gross = $total;

                $discount = (-1) * $discount;

                $tax = ($gross / ($rate + 1)) * $rate; //??

                $net = $gross - $tax;
            } elseif (strtoupper(trim($pricevatinclusive)) == 'NO') {
                //Manually calculate figures
                $this->logger->write("Utilities : applyDiscount() : Rebasing the prices", 'r');

                if ($rate > 0) {
                    $unit = $unit * ($rate + 1);
                }

                $total = ($qty * $unit);//??

                //$discount = ($discountpct/100) * $total;

                $discount = ($discount * $rate) + $discount; //apply tax to the discount
                $discountpct = $discount / $total; //determing the discount pct

                /**
                 * Modification Date: 2021-01-26
                 * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                 * */
                //$gross = $total - $discount;
                $gross = $total;

                $discount = (-1) * $discount;

                $tax = ($gross / ($rate + 1)) * $rate; //??

                $net = $gross - $tax;
            }

            $this->logger->write("Utilities : applyDiscount() : The discount: " . $discount . " for item: " . $obj['itemcode'], 'r');
            $this->logger->write("Utilities : applyDiscount() : The discount percentage: " . $discountpct . " for item: " . $obj['itemcode'], 'r');

            /**
             * Over-ride tax, if the tax payer is not VAT registered
             */
            if ($this->vatRegistered == 'N') {
                $tax = 0;
                $taxcategory = null;
                $taxcode = null;
            }

            if ($discount == 0) {
                $discountflag = 'NO';
            } else {
                $discountflag = 'YES';
            }

            $tempGoods[] = array(
                'groupid' => null,
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
                'ordernumber' => null,
                'discountflag' => trim($discountflag) == 'NO' ? '2' : '1',
                'deemedflag' => (strtoupper(trim($deemedflag)) == 'NO' ? '2' : '1'),
                'exciseflag' => null,
                'categoryid' => null,
                'categoryname' => null,
                'goodscategoryid' => $product->commoditycategorycode,
                'goodscategoryname' => null,
                'exciserate' => null,
                'exciserule' => null,
                'excisetax' => null,
                'pack' => null,
                'stick' => null,
                'exciseunit' => null,
                'excisecurrency' => null,
                'exciseratename' => null,
                'taxdisplaycategory' => $taxdisplaycategory,
                'taxcategory' => $taxcategory,
                'taxcategoryCode' => $taxcode,
                'unitofmeasurename' => $measureunit->name
            );

            $this->logger->write("Utilities : applyDiscount() : The TAXCODE is " . $taxcode, 'r');


            if ($this->vatRegistered == 'Y') {
                $tempTaxes[] = array(
                    'discountflag' => trim($discountflag) == 'NO' ? '2' : '1',
                    'discounttotal' => $discount,
                    'discounttaxrate' => $rate,
                    'discountpercentage' => $discountpct,
                    'd_netamount' => null,
                    'd_taxamount' => null,
                    'd_grossamount' => null,
                    'groupid' => null,
                    'goodid' => null,
                    'taxdisplaycategory' => $taxdisplaycategory,
                    'taxcategory' => $taxcategory,
                    'taxcategoryCode' => $taxcode,
                    'netamount' => $net,
                    'taxrate' => $rate,
                    'taxamount' => $tax,
                    'grossamount' => $gross,
                    'exciseunit' => null,
                    'excisecurrency' => null,
                    'taxratename' => $taxname,
                    'taxdescription' => $taxdescription
                );
            }
        }//foreach

        return array($tempGoods, $tempTaxes);
    }

    /**
     * @name getinvoicetaxrate
     * @desc Get the tax rate to be used by a good/service item on a  invoice, credit note, debit note
     * @return $taxid int
     * @param $industrycode string, $buyertype string, $deemedflag, $productcode string
     *
     */
    public function getinvoicetaxrate($industrycode, $buyertype, $deemedflag, $productcode)
    {
        //Ensure all params are not EMPTY/NULL
        if (trim($industrycode) == '') {
            $this->logger->write("Utilities : getinvoicetaxrate() : The industrycode is empty", 'r');
            return null;
        } elseif (trim($deemedflag) == '') {
            $this->logger->write("Utilities : getinvoicetaxrate() : The deemedflag is empty", 'r');
            return null;
        } elseif (trim($productcode) == '') {
            $this->logger->write("Utilities : getinvoicetaxrate() : The productcode is empty", 'r');
            return null;
        } elseif (trim($buyertype) == '') {
            $this->logger->write("Utilities : getinvoicetaxrate() : The buyertype is empty", 'r');
            return null;
        }

        $this->logger->write("Utilities : getinvoicetaxrate() : industrycode = " . $industrycode, 'r');
        $this->logger->write("Utilities : getinvoicetaxrate() : buyertype = " . $buyertype, 'r');
        $this->logger->write("Utilities : getinvoicetaxrate() : deemedflag = " . $deemedflag, 'r');
        $this->logger->write("Utilities : getinvoicetaxrate() : productcode = " . $productcode, 'r');

        $taxid = null;

        $pdct = new products($this->db);
        $pdct->getByErpCode($productcode);

        $isexempt = $pdct->isexempt;
        $iszerorated = $pdct->iszerorated;
        $taxrate = $pdct->taxrate;
        $statuscode = $pdct->statuscode;
        $source = $pdct->source;
        $exclusion = $pdct->exclusion;


        $this->logger->write("Utilities : getinvoicetaxrate() : isexempt = " . $isexempt, 'r');
        $this->logger->write("Utilities : getinvoicetaxrate() : iszerorated = " . $iszerorated, 'r');
        $this->logger->write("Utilities : getinvoicetaxrate() : taxrate = " . $taxrate, 'r');
        $this->logger->write("Utilities : getinvoicetaxrate() : statuscode = " . $statuscode, 'r');
        $this->logger->write("Utilities : getinvoicetaxrate() : source = " . $source, 'r');
        $this->logger->write("Utilities : getinvoicetaxrate() : exclusion = " . $exclusion, 'r');


        if (trim($industrycode) == '102') {
            //Export
            $taxid = $this->appsettings['ZEROTAXRATE'];
            $this->logger->write("Utilities : getinvoicetaxrate() : Zero rate tax", 'r');
        } else {
            //Non-Export

            /**
             * If DEEMED flag is YES, use the standard DEEMED tax rate.
             */
            if (strtoupper(trim($deemedflag)) == 'YES') {
                $taxid = $this->appsettings['DEEMEDTAXRATE'];
                $this->logger->write("Utilities : getinvoicetaxrate() : Deemed rate tax", 'r');
            } else {
                //Non-DEEMED


                if (trim($isexempt) == '101') {
                    //Exempt
                    $taxid = $this->appsettings['EXPEMPTTAXRATE'];
                    $this->logger->write("Utilities : getinvoicetaxrate() : Exempt rate tax", 'r');
                } elseif (trim($iszerorated) == '101') {
                    //ZERORATED
                    $taxid = $this->appsettings['ZEROTAXRATE'];
                    $this->logger->write("Utilities : getinvoicetaxrate() : Zero rate tax", 'r');
                } else {
                    $taxid = $this->appsettings['STANDARDTAXRATE'];
                    $this->logger->write("Utilities : getinvoicetaxrate() : Standard rate tax", 'r');
                }
            }
        }


        return $taxid;
    }

    /**
     * @name getinvoicetaxrate_v2
     * @desc Get the tax rate to be used by a good/service item on a  invoice, credit note, debit note
     * @return $taxid int
     * @param $industrycode string, $buyertype string, $productcode string, $tin string, $overrideflag string, $taxpayercheckflag string
     *
     */
    public function getinvoicetaxrate_v2($industrycode, $buyertype, $productcode, $tin, $overrideflag, $taxpayercheckflag)
    {

        //Ensure all params are not EMPTY/NULL
        if (trim($industrycode) == '') {
            $this->logger->write("Utilities : getinvoicetaxrate_v2() : The industrycode is empty", 'r');
            return null;
        } elseif (trim($productcode) == '') {
            $this->logger->write("Utilities : getinvoicetaxrate_v2() : The productcode is empty", 'r');
            return null;
        } elseif (trim($buyertype) == '') {
            $this->logger->write("Utilities : getinvoicetaxrate_v2() : The buyertype is empty", 'r');
            return null;
        }

        $this->logger->write("Utilities : getinvoicetaxrate_v2() : industrycode = " . $industrycode, 'r');
        $this->logger->write("Utilities : getinvoicetaxrate_v2() : buyertype = " . $buyertype, 'r');
        $this->logger->write("Utilities : getinvoicetaxrate_v2() : productcode = " . $productcode, 'r');

        /**
         * 1. If the OVERRIDE_TAXRATE_FLAG is set to 1, then check if the ProductCode is part of the list in tblproductoverridelist
         * 2. If the ProductCode exists in the list, then set $existsinlist to TRUE
         * 2(a). If the $existsinlist is TRUE, set the rate to STANDARD
         * 2(b). If the $existsinlist is FALSE, call getinvoicetaxrate($industrycode, $buyertype, $deemedflag, $productcode)
         * 3. Has the TIN been supplied?
         * 4(a). If TIN is not supplied, then do the following;
         * 4(a)(i). If the $existsinlist is TRUE, set the rate to STANDARD
         * 4(a)(ii). If the $existsinlist is FALSE, then do the following;
         * - Call getinvoicetaxrate($industrycode, $buyertype, $deemedflag, $productcode)
         * 4(b). If TIN is supplied, then do the following;
         * 4(b)(i). If the $taxpayercheckflag is set to FALSE then do the following;
         * - Call getinvoicetaxrate($industrycode, $buyertype, $deemedflag, $productcode)
         * 4(b)(ii). If the $taxpayercheckflag is set to TRUE then do the following;
         * 4(b)(ii)(1). Check URA for the tax status of the tax payer using the TIN and Product
         * 4(b)(ii)(2). Set the tax as per the result from the check
         */
        $pdct = new products($this->db);
        $pdct->getByErpCode($productcode);

        $taxid = null;
        $existsinlist = false;
        $taxpayerType = null;
        $commodityCategoryTaxpayerType = null;

        $list_check = new DB\SQL\Mapper($this->db, 'tblproductoverridelist');
        $list_check->load(array('TRIM(code)=?', $productcode));

        if (!$list_check->dry()) {
            $this->logger->write("Utilities : getinvoicetaxrate_v2() : The product exists in the override list", 'r');
            $existsinlist = true;
        }

        if ($overrideflag == '1') {

            $this->logger->write("Api : getinvoicetaxrate_v2() : The override flag is set to Yes", 'r');

            if ($existsinlist) {
                $taxid = $this->appsettings['STANDARDTAXRATE'];
            } else {
                $taxid = $this->getinvoicetaxrate($industrycode, $buyertype, 'NO', $productcode);
            }

        } else {
            $this->logger->write("Api : getinvoicetaxrate_v2() : The override flag is set to No", 'r');

            if ($tin) {

                if ($taxpayercheckflag == '1') {
                    $this->logger->write("Api : getinvoicetaxrate_v2() : The taxpayer check flag is set to Yes", 'r');

                    $data = $this->checktaxpayer($this->userid, $tin, $pdct['commoditycategorycode']);//will return JSON.
                    //var_dump($data);
                    $data = json_decode($data, true); //{"commodityCategory":[],"taxpayerType":"101"}

                    /*
                        101	Normal taxpayer
                        102	Exempt taxpayer
                        103	Deemed taxpayer
                     */
                    if (isset($data['commodityCategory'])) {

                        foreach ($data['commodityCategory'] as $elem) {

                            if ($elem['commodityCategoryCode'] == $pdct['commoditycategorycode']) {
                                $commodityCategoryTaxpayerType = $elem['commodityCategoryTaxpayerType'];

                                $this->logger->write("Api : getinvoicetaxrate_v2() : The tax payer type for this commodity code is " . $commodityCategoryTaxpayerType, 'r');
                            }

                        }

                        if (isset($data['taxpayerType'])) {
                            $taxpayerType = $data['taxpayerType'];
                            $this->logger->write("Api : getinvoicetaxrate_v2() : The general tax payer type is " . $taxpayerType, 'r');
                        }

                        if ($commodityCategoryTaxpayerType == '101') { //STANDARD
                            $taxid = $this->appsettings['STANDARDTAXRATE'];
                        } elseif ($commodityCategoryTaxpayerType == '102') { //EXEMPT
                            $taxid = $this->appsettings['EXPEMPTTAXRATE'];
                        } elseif ($commodityCategoryTaxpayerType == '103') { //DEEMED
                            $taxid = $this->appsettings['DEEMEDTAXRATE'];
                        } else {
                            $taxid = $this->appsettings['STANDARDTAXRATE'];
                        }

                    } elseif (isset($data['returnCode'])) {
                        $taxid = $this->getinvoicetaxrate($industrycode, $buyertype, 'NO', $productcode);
                    } else {
                        $taxid = $this->getinvoicetaxrate($industrycode, $buyertype, 'NO', $productcode);
                    }

                } else {
                    $this->logger->write("Api : getinvoicetaxrate_v2() : The taxpayer chec flag is set to No", 'r');
                    $taxid = $this->getinvoicetaxrate($industrycode, $buyertype, 'NO', $productcode);
                }

            } else {
                $this->logger->write("Api : getinvoicetaxrate_v2() : The TIN is not supplied", 'r');
                $taxid = $this->getinvoicetaxrate($industrycode, $buyertype, 'NO', $productcode);
            }

        }

        return $taxid;
    }


    /**
     * @name createpurchaseorder
     * @desc create an purchaseorder
     * @return bool
     * @param $purchaseorderdetails array, $goods array
     *
     */
    public function createpurchaseorder($purchaseorderdetails, $goods, $userid)
    {
        /**
         * 0. Insert a new purchaseorder and retrieve its id
         * 1. Create a param group for goods, taxes, and payments
         * 2. Modify the following arrays
         * 2.1 goods
         * 3. Insert into the respective tables
         */


        try {

            $purchaseorderdetails['netamount'] = empty($purchaseorderdetails['netamount']) ? '0.00' : $purchaseorderdetails['netamount'];
            $purchaseorderdetails['taxamount'] = empty($purchaseorderdetails['taxamount']) ? '0.00' : $purchaseorderdetails['taxamount'];
            $purchaseorderdetails['grossamount'] = empty($purchaseorderdetails['grossamount']) ? '0.00' : $purchaseorderdetails['grossamount'];
            $purchaseorderdetails['itemcount'] = empty($purchaseorderdetails['itemcount']) ? '0' : $purchaseorderdetails['itemcount'];
            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;
            $purchaseorderdetails['SyncToken'] = empty($purchaseorderdetails['SyncToken']) ? '0' : $purchaseorderdetails['SyncToken'];

            $sql = 'INSERT INTO tblpurchaseorders
                                    (erpvoucherid,
                                    erpvoucherno,
                                    issueddate,
                                    issuedtime,
                                    operator,
                                    currency,
                                    datasource,
                                    netamount,
                                    taxamount,
                                    grossamount,
                                    itemcount,
                                    remarks,
                                    supplierid,
                                    grossamountword,
                                    vouchertype,
                                    vouchertypename,
                                    SyncToken,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($purchaseorderdetails['erpvoucherid']) . '", "'
                    . addslashes($purchaseorderdetails['erpvoucherno']) . '", "'
                        . $purchaseorderdetails['issueddate'] . '", "'
                            . $purchaseorderdetails['issuedtime'] . '", "'
                                . addslashes($purchaseorderdetails['operator']) . '", "'
                                    . addslashes($purchaseorderdetails['currency']) . '", "'
                                        . addslashes($purchaseorderdetails['datasource']) . '", '
                                            . $purchaseorderdetails['netamount'] . ', '
                                                . $purchaseorderdetails['taxamount'] . ', '
                                                    . $purchaseorderdetails['grossamount'] . ', '
                                                        . $purchaseorderdetails['itemcount'] . ', "'
                                                            . addslashes($purchaseorderdetails['remarks']) . '", '
                                                                . $purchaseorderdetails['supplierid'] . ', "'
                                                                    . addslashes($purchaseorderdetails['grossamountword']) . '", "'
                                                                        . addslashes($purchaseorderdetails['vouchertype']) . '", "'
                                                                            . addslashes($purchaseorderdetails['vouchertypename']) . '", '
                                                                                . $purchaseorderdetails['SyncToken'] . ', "'
                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                        . $userid . ', "'
                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                . $userid . ')';

            $this->logger->write("Utilities : createpurchaseorder() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : createpurchaseorder() : The purchase order has been added", 'r');


            $data = array();
            $r = $this->db->exec(array(
                'SELECT id "id" FROM tblpurchaseorders WHERE TRIM(erpvoucherno) = \'' . $purchaseorderdetails['erpvoucherno'] . '\''
            ));

            foreach ($r as $obj) {
                $data[] = $obj;
            }

            $id = $data[0]['id'];

            try {
                $paramgroupdescription = "This is an autogenerated group id for the purchaseorder id " . $id;

                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['SUPPLIERENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['SUPPLIERENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $gooddetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblpurchaseorders SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    /*Insert Goods*/

                    $i = 0;
                    foreach ($goods as $obj) {


                        $obj['item'] = empty($obj['item']) ? '' : $obj['item'];
                        $obj['itemcode'] = empty($obj['itemcode']) ? '' : $obj['itemcode'];
                        $obj['qty'] = empty($obj['qty']) ? 'NULL' : $obj['qty'];
                        $obj['unitofmeasure'] = empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'];
                        $obj['unitprice'] = empty($obj['unitprice']) ? 'NULL' : $obj['unitprice'];
                        $obj['total'] = empty($obj['total']) ? 'NULL' : $obj['total'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? 'NULL' : $obj['taxrate'];
                        $obj['tax'] = empty($obj['tax']) ? '0.00' : $obj['tax'];
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? 'NULL' : $obj['discounttotal'];
                        $obj['discounttaxrate'] = (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00') ? 'NULL' : $obj['discounttaxrate'];
                        $obj['discountflag'] = empty($obj['discountflag']) ? '2' : $obj['discountflag'];
                        $obj['deemedflag'] = empty($obj['deemedflag']) ? '2' : $obj['deemedflag'];
                        $obj['exciseflag'] = empty($obj['exciseflag']) ? '2' : $obj['exciseflag'];
                        $obj['categoryid'] = empty($obj['categoryid']) ? 'NULL' : $obj['categoryid'];
                        $obj['categoryname'] = empty($obj['categoryname']) ? '' : $obj['categoryname'];
                        $obj['goodscategoryid'] = empty($obj['goodscategoryid']) ? 'NULL' : $obj['goodscategoryid'];
                        $obj['goodscategoryname'] = empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'];
                        $obj['exciserate'] = empty($obj['exciserate']) ? '' : $obj['exciserate'];
                        $obj['exciserule'] = empty($obj['exciserule']) ? 'NULL' : $obj['exciserule'];
                        $obj['excisetax'] = empty($obj['excisetax']) ? 'NULL' : $obj['excisetax'];
                        $obj['pack'] = empty($obj['pack']) ? 'NULL' : $obj['pack'];
                        $obj['stick'] = empty($obj['stick']) ? 'NULL' : $obj['stick'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? 'NULL' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['exciseratename'] = empty($obj['exciseratename']) ? '' : $obj['exciseratename'];

                        $sql = 'INSERT INTO tblgooddetails (
                                    groupid,
                                    item,
                                    itemcode,
                                    qty,
                                    unitofmeasure,
                                    unitprice,
                                    total,
                                    taxid,
                                    taxrate,
                                    tax,
                                    discounttotal,
                                    discounttaxrate,
                                    discountpercentage,
                                    ordernumber,
                                    discountflag,
                                    deemedflag,
                                    exciseflag,
                                    categoryid,
                                    categoryname,
                                    goodscategoryid,
                                    goodscategoryname,
                                    exciserate,
                                    exciserule,
                                    excisetax,
                                    pack,
                                    stick,
                                    exciseunit,
                                    excisecurrency,
                                    exciseratename,
                                    taxcategory,
                                    displayCategoryCode,
                                    unitofmeasurename,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $gooddetailgroupid . ', "'
                                . addslashes($obj['item']) . '", "'
                                    . addslashes($obj['itemcode']) . '", '
                                        . $obj['qty'] . ', "'
                                            . $obj['unitofmeasure'] . '", '
                                                . $obj['unitprice'] . ', '
                                                    . $obj['total'] . ', '
                                                        . $obj['taxid'] . ', '
                                                            . $obj['taxrate'] . ', '
                                                                . $obj['tax'] . ', '
                                                                    . $obj['discounttotal'] . ', '
                                                                        . $obj['discounttaxrate'] . ', '
                                                                            . $obj['discountpercentage'] . ', '
                                                                                . $i . ', '
                                                                                    . $obj['discountflag'] . ', '
                                                                                        . $obj['deemedflag'] . ', '
                                                                                            . $obj['exciseflag'] . ', '
                                                                                                . $obj['categoryid'] . ', "'
                                                                                                    . addslashes($obj['categoryname']) . '", '
                                                                                                        . $obj['goodscategoryid'] . ', "'
                                                                                                            . addslashes($obj['goodscategoryname']) . '", "'
                                                                                                                . $obj['exciserate'] . '", '
                                                                                                                    . $obj['exciserule'] . ', '
                                                                                                                        . $obj['excisetax'] . ', '
                                                                                                                            . $obj['pack'] . ', '
                                                                                                                                . $obj['stick'] . ', '
                                                                                                                                    . $obj['exciseunit'] . ', "'
                                                                                                                                        . $obj['excisecurrency'] . '", "'
                                                                                                                                            . $obj['exciseratename'] . '", "'
                                                                                                                                                . $obj['taxcategory'] . '", "'
                                                                                                                                                    . $obj['taxdisplaycategory'] . '", "'
                                                                                                                                                        . $obj['unitofmeasurename'] . '", "'
                                                                                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                . $userid . ', "'
                                                                                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                        . $userid . ')';

                        $this->logger->write("Utilities : createpurchaseorder() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));

                        $i = $i + 1;
                    }

                } catch (Exception $e) {
                    $this->logger->write("Utilities : createpurchaseorder() : Failed to select and insert into table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createpurchaseorder() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
            }

            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : createpurchaseorder() : The operation to create the purchaseorder was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }
    }


    /**
     * @name updatepurchaseorder
     * @desc update an purchaseorder
     * @return bool
     * @param $purchaseorderdetails array, $goods array
     *
     */
    public function updatepurchaseorder($purchaseorderdetails, $goods, $userid)
    {
        /**
         * 1. Delete details of the purchase order
         * 2. Insert a new purchase order and retrieve its id
         * 3. Create a param group for goods
         * 4. Modify the following arrays
         * 5.1 goods
         * 6. Insert into the respective tables
         */

        if ($purchaseorderdetails['id']) {


            if ($purchaseorderdetails['gooddetailgroupid']) {
                try {
                    $this->db->exec(array('DELETE FROM tblgooddetails g WHERE g.groupid = ' . $purchaseorderdetails['gooddetailgroupid']));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updatepurchaseorder() : Failed to delete from table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                    return false;
                }
            } else {
                $this->logger->write("Utilities : updatepurchaseorder() : The goods group Id was not specified.", 'r');
                return false;
            }

            try {
                $this->db->exec(array('DELETE FROM tblpurchaseorders g WHERE g.id = ' . $purchaseorderdetails['id']));
            } catch (Exception $e) {
                $this->logger->write("Utilities : updatepurchaseorder() : Failed to delete from table tblpurchaseorders. The error message is " . $e->getMessage(), 'r');
                return false;
            }
        } else {
            $this->logger->write("Utilities : updatepurchaseorder() : The PO Id was not specified.", 'r');
            return false;
        }

        try {

            $purchaseorderdetails['netamount'] = empty($purchaseorderdetails['netamount']) ? '0.00' : $purchaseorderdetails['netamount'];
            $purchaseorderdetails['taxamount'] = empty($purchaseorderdetails['taxamount']) ? '0.00' : $purchaseorderdetails['taxamount'];
            $purchaseorderdetails['grossamount'] = empty($purchaseorderdetails['grossamount']) ? '0.00' : $purchaseorderdetails['grossamount'];
            $purchaseorderdetails['itemcount'] = empty($purchaseorderdetails['itemcount']) ? '0' : $purchaseorderdetails['itemcount'];
            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;
            $purchaseorderdetails['SyncToken'] = empty($purchaseorderdetails['SyncToken']) ? '0' : $purchaseorderdetails['SyncToken'];

            $sql = 'INSERT INTO tblpurchaseorders
                                    (erpvoucherid,
                                    erpvoucherno,
                                    issueddate,
                                    issuedtime,
                                    operator,
                                    currency,
                                    datasource,
                                    netamount,
                                    taxamount,
                                    grossamount,
                                    itemcount,
                                    remarks,
                                    supplierid,
                                    grossamountword,
                                    vouchertype,
                                    vouchertypename,
                                    SyncToken,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($purchaseorderdetails['erpvoucherid']) . '", "'
                    . addslashes($purchaseorderdetails['erpvoucherno']) . '", "'
                        . $purchaseorderdetails['issueddate'] . '", "'
                            . $purchaseorderdetails['issuedtime'] . '", "'
                                . addslashes($purchaseorderdetails['operator']) . '", "'
                                    . addslashes($purchaseorderdetails['currency']) . '", "'
                                        . addslashes($purchaseorderdetails['datasource']) . '", '
                                            . $purchaseorderdetails['netamount'] . ', '
                                                . $purchaseorderdetails['taxamount'] . ', '
                                                    . $purchaseorderdetails['grossamount'] . ', '
                                                        . $purchaseorderdetails['itemcount'] . ', "'
                                                            . addslashes($purchaseorderdetails['remarks']) . '", '
                                                                . $purchaseorderdetails['supplierid'] . ', "'
                                                                    . addslashes($purchaseorderdetails['grossamountword']) . '", "'
                                                                        . addslashes($purchaseorderdetails['vouchertype']) . '", "'
                                                                            . addslashes($purchaseorderdetails['vouchertypename']) . '", '
                                                                                . $purchaseorderdetails['SyncToken'] . ', "'
                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                        . $userid . ', "'
                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                . $userid . ')';

            $this->logger->write("Utilities : updatepurchaseorder() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : updatepurchaseorder() : The purchase order has been added", 'r');


            $data = array();
            $r = $this->db->exec(array(
                'SELECT id "id" FROM tblpurchaseorders WHERE TRIM(erpvoucherno) = \'' . $purchaseorderdetails['erpvoucherno'] . '\''
            ));

            foreach ($r as $obj) {
                $data[] = $obj;
            }

            $id = $data[0]['id'];

            try {
                $paramgroupdescription = "This is an autogenerated group id for the purchaseorder id " . $id;

                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['SUPPLIERENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['SUPPLIERENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $gooddetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblpurchaseorders SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    /*Insert Goods*/

                    $i = 0;
                    foreach ($goods as $obj) {


                        $obj['item'] = empty($obj['item']) ? '' : $obj['item'];
                        $obj['itemcode'] = empty($obj['itemcode']) ? '' : $obj['itemcode'];
                        $obj['qty'] = empty($obj['qty']) ? 'NULL' : $obj['qty'];
                        $obj['unitofmeasure'] = empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'];
                        $obj['unitprice'] = empty($obj['unitprice']) ? 'NULL' : $obj['unitprice'];
                        $obj['total'] = empty($obj['total']) ? 'NULL' : $obj['total'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? 'NULL' : $obj['taxrate'];
                        $obj['tax'] = empty($obj['tax']) ? '0.00' : $obj['tax'];
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? 'NULL' : $obj['discounttotal'];
                        $obj['discounttaxrate'] = (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00') ? 'NULL' : $obj['discounttaxrate'];
                        $obj['discountflag'] = empty($obj['discountflag']) ? '2' : $obj['discountflag'];
                        $obj['deemedflag'] = empty($obj['deemedflag']) ? '2' : $obj['deemedflag'];
                        $obj['exciseflag'] = empty($obj['exciseflag']) ? '2' : $obj['exciseflag'];
                        $obj['categoryid'] = empty($obj['categoryid']) ? 'NULL' : $obj['categoryid'];
                        $obj['categoryname'] = empty($obj['categoryname']) ? '' : $obj['categoryname'];
                        $obj['goodscategoryid'] = empty($obj['goodscategoryid']) ? 'NULL' : $obj['goodscategoryid'];
                        $obj['goodscategoryname'] = empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'];
                        $obj['exciserate'] = empty($obj['exciserate']) ? '' : $obj['exciserate'];
                        $obj['exciserule'] = empty($obj['exciserule']) ? 'NULL' : $obj['exciserule'];
                        $obj['excisetax'] = empty($obj['excisetax']) ? 'NULL' : $obj['excisetax'];
                        $obj['pack'] = empty($obj['pack']) ? 'NULL' : $obj['pack'];
                        $obj['stick'] = empty($obj['stick']) ? 'NULL' : $obj['stick'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? 'NULL' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['exciseratename'] = empty($obj['exciseratename']) ? '' : $obj['exciseratename'];

                        $sql = 'INSERT INTO tblgooddetails (
                                    groupid,
                                    item,
                                    itemcode,
                                    qty,
                                    unitofmeasure,
                                    unitprice,
                                    total,
                                    taxid,
                                    taxrate,
                                    tax,
                                    discounttotal,
                                    discounttaxrate,
                                    discountpercentage,
                                    ordernumber,
                                    discountflag,
                                    deemedflag,
                                    exciseflag,
                                    categoryid,
                                    categoryname,
                                    goodscategoryid,
                                    goodscategoryname,
                                    exciserate,
                                    exciserule,
                                    excisetax,
                                    pack,
                                    stick,
                                    exciseunit,
                                    excisecurrency,
                                    exciseratename,
                                    taxcategory,
                                    displayCategoryCode,
                                    unitofmeasurename,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $gooddetailgroupid . ', "'
                                . addslashes($obj['item']) . '", "'
                                    . addslashes($obj['itemcode']) . '", '
                                        . $obj['qty'] . ', "'
                                            . $obj['unitofmeasure'] . '", '
                                                . $obj['unitprice'] . ', '
                                                    . $obj['total'] . ', '
                                                        . $obj['taxid'] . ', '
                                                            . $obj['taxrate'] . ', '
                                                                . $obj['tax'] . ', '
                                                                    . $obj['discounttotal'] . ', '
                                                                        . $obj['discounttaxrate'] . ', '
                                                                            . $obj['discountpercentage'] . ', '
                                                                                . $i . ', '
                                                                                    . $obj['discountflag'] . ', '
                                                                                        . $obj['deemedflag'] . ', '
                                                                                            . $obj['exciseflag'] . ', '
                                                                                                . $obj['categoryid'] . ', "'
                                                                                                    . addslashes($obj['categoryname']) . '", '
                                                                                                        . $obj['goodscategoryid'] . ', "'
                                                                                                            . addslashes($obj['goodscategoryname']) . '", "'
                                                                                                                . $obj['exciserate'] . '", '
                                                                                                                    . $obj['exciserule'] . ', '
                                                                                                                        . $obj['excisetax'] . ', '
                                                                                                                            . $obj['pack'] . ', '
                                                                                                                                . $obj['stick'] . ', '
                                                                                                                                    . $obj['exciseunit'] . ', "'
                                                                                                                                        . $obj['excisecurrency'] . '", "'
                                                                                                                                            . $obj['exciseratename'] . '", "'
                                                                                                                                                . $obj['taxcategory'] . '", "'
                                                                                                                                                    . $obj['taxdisplaycategory'] . '", "'
                                                                                                                                                        . $obj['unitofmeasurename'] . '", "'
                                                                                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                . $userid . ', "'
                                                                                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                        . $userid . ')';

                        $this->logger->write("Utilities : updatepurchaseorder() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));

                        $i = $i + 1;
                    }

                } catch (Exception $e) {
                    $this->logger->write("Utilities : updatepurchaseorder() : Failed to select and insert into table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : updatepurchaseorder() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
            }

            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : updatepurchaseorder() : The operation to update the purchaseorder was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }
    }

    /**
     * @name createinvoice
     * @desc create an invoice
     * @return bool
     * @param $invoicedetails array, $goods array, $taxes array, $buyer array
     *
     */
    public function createinvoice($invoicedetails, $goods, $taxes, $buyer, $userid)
    {
        /**
         * 0. Insert a new invoice and retrieve its id
         * 1. Create a param group for goods, taxes, and payments
         * 2. Modify the following arrays
         * 2.1 goods
         * 2.2 payments
         * 2.3 payments
         * 2.4 buyers
         * 3. Insert into the respective tables
         */


        try {

            $netamount = empty($invoicedetails['netamount']) ? '0.00' : $invoicedetails['netamount'];
            $taxamount = empty($invoicedetails['taxamount']) ? '0.00' : $invoicedetails['taxamount'];
            $grossamount = empty($invoicedetails['grossamount']) ? '0.00' : $invoicedetails['grossamount'];
            $itemcount = empty($invoicedetails['itemcount']) ? '0' : $invoicedetails['itemcount'];
            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;
            $currencyRate = empty($invoicedetails['currencyRate']) ? '1' : $invoicedetails['currencyRate'];

            $createInvoiceAudit = array(
                'erpInvoiceId' => empty($invoicedetails['erpinvoiceid']) ? '' : $invoicedetails['erpinvoiceid'],
                'erpInvoiceNo' => empty($invoicedetails['erpinvoiceno']) ? '' : $invoicedetails['erpinvoiceno'],
                'invoiceType' => empty($invoicedetails['invoicetype']) ? '' : strval($invoicedetails['invoicetype']),
                'invoiceKind' => empty($invoicedetails['invoicekind']) ? '' : strval($invoicedetails['invoicekind']),
                'invoiceIndustryCode' => empty($invoicedetails['invoiceindustrycode']) ? '' : strval($invoicedetails['invoiceindustrycode']),
                'currency' => empty($invoicedetails['currency']) ? '' : $invoicedetails['currency'],
                'currencyRate' => strval($currencyRate),
                'modeCode' => empty($invoicedetails['modecode']) ? '' : $invoicedetails['modecode'],
                'goodsCount' => is_array($goods) ? count($goods) : 0,
                'taxCount' => is_array($taxes) ? count($taxes) : 0
            );
            $this->logger->write("Utilities : createinvoice() : The invoice field audit is " . json_encode($createInvoiceAudit), 'r');

            $sql = 'INSERT INTO tblinvoices
                                    (erpinvoiceid,
                                    erpinvoiceno,
                                    antifakecode,
                                    deviceno,
                                    issueddate,
                                    issuedtime,
                                    operator,
                                    currency,
                                    oriinvoiceid,
                                    invoicetype,
                                    invoicekind,
                                    datasource,
                                    invoiceindustrycode,
                                    einvoiceid,
                                    einvoicenumber,
                                    einvoicedatamatrixcode,
                                    isbatch,
                                    netamount,
                                    taxamount,
                                    grossamount,
                                    origrossamount,
                                    itemcount,
                                    modecode,
                                    modename,
                                    remarks,
                                    buyerid,
                                    sellerid,
                                    issueddatepdf,
                                    grossamountword,
                                    isinvalid,
                                    isrefund,
                                    vouchertype,
                                    vouchertypename,
                                    currencyRate,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($invoicedetails['erpinvoiceid']) . '", "'
                    . addslashes($invoicedetails['erpinvoiceno']) . '", "'
                        . addslashes($invoicedetails['antifakecode']) . '", "'
                            . addslashes($invoicedetails['deviceno']) . '", "'
                                . $invoicedetails['issueddate'] . '", "'
                                        . $invoicedetails['issuedtime'] . '", "'
                                            . addslashes($invoicedetails['operator']) . '", "'
                                                . $invoicedetails['currency'] . '", "'
                                                    . $invoicedetails['oriinvoiceid'] . '", '
                                                        . $invoicedetails['invoicetype'] . ', '
                                                            . $invoicedetails['invoicekind'] . ', '
                                                                . $invoicedetails['datasource'] . ', '
                                                                    . $invoicedetails['invoiceindustrycode'] . ', "'
                                                                        . addslashes($invoicedetails['einvoiceid']) . '", "'
                                                                            . addslashes($invoicedetails['einvoicenumber']) . '", "'
                                                                                . addslashes($invoicedetails['einvoicedatamatrixcode']) . '", "'
                                                                                    . $invoicedetails['isbatch'] . '", '
                                                                                        . $netamount . ', '
                                                                                            . $taxamount . ', '
                                                                                                . $grossamount . ', '
                                                                                                    . $invoicedetails['origrossamount'] . ', '
                                                                                                        . $itemcount . ', "'
                                                                                                            . $invoicedetails['modecode'] . '", "'
                                                                                                                . $invoicedetails['modename'] . '", "'
                                                                                                                    . addslashes($invoicedetails['remarks']) . '", '
                                                                                                                        . 'NULL, '
                                                                                                                            . $invoicedetails['sellerid'] . ', "'
                                                                                                                                . $invoicedetails['issueddatepdf'] . '", "'
                                                                                                                                    . $invoicedetails['grossamountword'] . '", '
                                                                                                                                        . $invoicedetails['isinvalid'] . ', '
                                                                                                                                            . $invoicedetails['isrefund'] . ', "'
                                                                                                                                                . addslashes($invoicedetails['vchtype']) . '", "'
                                                                                                                                                    . addslashes($invoicedetails['vchtypename']) . '", '
                                                                                                                                                        . $currencyRate . ', "'
                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                            . $userid . ', "'
                                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                    . $userid . ')';

            $this->logger->write("Utilities : createinvoice() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : createinvoice() : The invoice has been added", 'r');


            $this->logger->write("Utilities : createinvoice() : The FDN is " . $invoicedetails['antifakecode'], 'r');

            $data = array();
            $r = $this->db->exec(array(
                'SELECT id "id" FROM tblinvoices WHERE TRIM(antifakecode) = \'' . $invoicedetails['antifakecode'] . '\''
            ));

            foreach ($r as $obj) {
                $data[] = $obj;
            }

            $id = $data[0]['id'];

            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $gooddetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblinvoices SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    /*Insert Goods*/

                    $i = 0;
                    foreach ($goods as $obj) {


                        $obj['item'] = empty($obj['item']) ? '' : $obj['item'];
                        $obj['itemcode'] = empty($obj['itemcode']) ? '' : $obj['itemcode'];
                        $obj['qty'] = empty($obj['qty']) ? 'NULL' : $obj['qty'];
                        $obj['unitofmeasure'] = empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'];
                        $obj['unitprice'] = empty($obj['unitprice']) ? 'NULL' : $obj['unitprice'];
                        $obj['total'] = empty($obj['total']) ? 'NULL' : $obj['total'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? 'NULL' : $obj['taxrate'];
                        $obj['tax'] = empty($obj['tax']) ? '0.00' : $obj['tax'];
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? 'NULL' : $obj['discounttotal'];
                        $obj['discounttaxrate'] = (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00') ? 'NULL' : $obj['discounttaxrate'];
                        $obj['discountflag'] = empty($obj['discountflag']) ? '2' : $obj['discountflag'];
                        $obj['deemedflag'] = empty($obj['deemedflag']) ? '2' : $obj['deemedflag'];
                        $obj['exciseflag'] = empty($obj['exciseflag']) ? '2' : $obj['exciseflag'];
                        $obj['categoryid'] = empty($obj['categoryid']) ? 'NULL' : $obj['categoryid'];
                        $obj['categoryname'] = empty($obj['categoryname']) ? '' : $obj['categoryname'];
                        $obj['goodscategoryid'] = empty($obj['goodscategoryid']) ? 'NULL' : $obj['goodscategoryid'];
                        $obj['goodscategoryname'] = empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'];
                        $obj['exciserate'] = empty($obj['exciserate']) ? '' : $obj['exciserate'];
                        $obj['exciserule'] = empty($obj['exciserule']) ? 'NULL' : $obj['exciserule'];
                        $obj['excisetax'] = empty($obj['excisetax']) ? 'NULL' : $obj['excisetax'];
                        $obj['pack'] = empty($obj['pack']) ? 'NULL' : $obj['pack'];
                        $obj['stick'] = empty($obj['stick']) ? 'NULL' : $obj['stick'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? 'NULL' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['exciseratename'] = empty($obj['exciseratename']) ? '' : $obj['exciseratename'];
                        $obj['vatProjectId'] = empty($obj['vatProjectId']) ? '' : $obj['vatProjectId'];
                        $obj['vatProjectName'] = empty($obj['vatProjectName']) ? '' : $obj['vatProjectName'];

                        $sql = 'INSERT INTO tblgooddetails (
                                    groupid,
                                    item,
                                    itemcode,
                                    qty,
                                    unitofmeasure,
                                    unitprice,
                                    total,
                                    taxid,
                                    taxrate,
                                    tax,
                                    discounttotal,
                                    discounttaxrate,
                                    discountpercentage,
                                    ordernumber,
                                    discountflag,
                                    deemedflag,
                                    exciseflag,
                                    categoryid,
                                    categoryname,
                                    goodscategoryid,
                                    goodscategoryname,
                                    exciserate,
                                    exciserule,
                                    excisetax,
                                    pack,
                                    stick,
                                    exciseunit,
                                    excisecurrency,
                                    exciseratename,
                                    taxcategory,
                                    unitofmeasurename,
                                    projectId,
                                    projectName,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $gooddetailgroupid . ', "'
                                . addslashes($obj['item']) . '", "'
                                    . addslashes($obj['itemcode']) . '", '
                                        . $obj['qty'] . ', "'
                                            . $obj['unitofmeasure'] . '", '
                                                . $obj['unitprice'] . ', '
                                                    . $obj['total'] . ', '
                                                        . $obj['taxid'] . ', '
                                                            . $obj['taxrate'] . ', '
                                                                . $obj['tax'] . ', '
                                                                    . $obj['discounttotal'] . ', '
                                                                        . $obj['discounttaxrate'] . ', '
                                                                            . $obj['discountpercentage'] . ', '
                                                                                . $i . ', '
                                                                                    . $obj['discountflag'] . ', '
                                                                                        . $obj['deemedflag'] . ', '
                                                                                            . $obj['exciseflag'] . ', '
                                                                                                . $obj['categoryid'] . ', "'
                                                                                                    . addslashes($obj['categoryname']) . '", '
                                                                                                        . $obj['goodscategoryid'] . ', "'
                                                                                                            . addslashes($obj['goodscategoryname']) . '", "'
                                                                                                                . $obj['exciserate'] . '", '
                                                                                                                    . $obj['exciserule'] . ', '
                                                                                                                        . $obj['excisetax'] . ', '
                                                                                                                            . $obj['pack'] . ', '
                                                                                                                                . $obj['stick'] . ', '
                                                                                                                                    . $obj['exciseunit'] . ', "'
                                                                                                                                        . $obj['excisecurrency'] . '", "'
                                                                                                                                            . $obj['exciseratename'] . '", "'
                                                                                                                                                . $obj['taxdisplaycategory'] . '", "'
                                                                                                                                                    . $obj['unitofmeasurename'] . '", "'
                                                                                                                                                    . $obj['vatProjectId'] . '", "'
                                                                                                                                                    . $obj['vatProjectName'] . '", "'
                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                            . $userid . ', "'
                                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : createinvoice() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));

                        $i = $i + 1;
                    }

                } catch (Exception $e) {
                    $this->logger->write("Utilities : createinvoice() : Failed to select and insert into table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createinvoice() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $paymentdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblinvoices SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createinvoice() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createinvoice() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $taxdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblinvoices SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    //Get details of goods inserted
                    $t_goods = $this->db->exec(array('SELECT * FROM tblgooddetails g WHERE g.groupid = ' . $gooddetailgroupid . ' ORDER BY id ASC'));

                    //Insert Taxes
                    $j = 0;
                    foreach ($taxes as $obj) {
                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : createinvoice() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : createinvoice() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : createinvoice() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }

                        if (strtoupper(trim($obj['taxcategory'])) == 'D: DEEMED (18%)') {
                            $obj['netamount'] = floor(($obj['netamount'] + $obj['d_netamount']) * 100) / 100;
                            $obj['taxamount'] = floor(($obj['taxamount'] + $obj['d_taxamount']) * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 4, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 4, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 4, '.', '');
                        } else {
                            $obj['netamount'] = round(($obj['netamount'] + $obj['d_netamount']), 4);
                            $obj['taxamount'] = round(($obj['taxamount'] + $obj['d_taxamount']), 4);
                            //$obj['grossamount'] = round($obj['grossamount'], 4);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];
                        }

                        $obj['taxcategory'] = empty($obj['taxcategory']) ? '' : $obj['taxcategory'];
                        $obj['netamount'] = empty($obj['netamount']) ? 'NULL' : $obj['netamount'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? '' : $obj['taxrate'];
                        $obj['taxamount'] = empty($obj['taxamount']) ? '0.00' : $obj['taxamount'];
                        $obj['grossamount'] = empty($obj['grossamount']) ? 'NULL' : $obj['grossamount'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? '' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['taxratename'] = empty($obj['taxratename']) ? '' : $obj['taxratename'];

                        //$obj['goodid'] = empty($obj['goodid'])? 'NULL' : $obj['goodid'];
                        $obj['goodid'] = $t_goods[$j]['id'];

                        $sql = 'INSERT INTO tbltaxdetails (
                                    groupid,
                                    goodid,
                                    taxcategory,
                                    netamount,
                                    taxrate,
                                    taxamount,
                                    grossamount,
                                    exciseunit,
                                    excisecurrency,
                                    taxratename,
                                    taxdescription,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $taxdetailgroupid . ', '
                                . $obj['goodid'] . ', "'
                                    . addslashes($obj['taxcategory']) . '", '
                                        . $obj['netamount'] . ', '
                                            . $obj['taxrate'] . ', '
                                                . $obj['taxamount'] . ', '
                                                    . $obj['grossamount'] . ', "'
                                                        . $obj['exciseunit'] . '", "'
                                                            . $obj['excisecurrency'] . '", "'
                                                                . $obj['taxratename'] . '", "'
                                                                    . $obj['taxdescription'] . '", "'
                                                                        . date('Y-m-d H:i:s') . '", '
                                                                            . $userid . ', "'
                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : createinvoice() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));
                        $j = $j + 1;

                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createinvoice() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createinvoice() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            if (trim($buyer['referenceno']) !== '' || !empty(trim($buyer['referenceno']))) {
                try {

                    $sql = 'INSERT INTO tblbuyers (
                                    tin,
                                    ninbrn,
                                    PassportNum,
                                    legalname,
                                    businessname,
                                    address,
                                    mobilephone,
                                    linephone,
                                    emailaddress,
                                    placeofbusiness,
                                    type,
                                    citizineship,
                                    sector,
                                    referenceno,
                                    datasource,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                        . addslashes($buyer['tin']) . '", "'
                            . addslashes($buyer['ninbrn']) . '", "'
                                . addslashes($buyer['PassportNum']) . '", "'
                                    . addslashes($buyer['legalname']) . '", "'
                                        . addslashes($buyer['businessname']) . '", "'
                                            . addslashes($buyer['address']) . '", "'
                                                . addslashes($buyer['mobilephone']) . '", "'
                                                    . addslashes($buyer['linephone']) . '", "'
                                                        . addslashes($buyer['emailaddress']) . '", "'
                                                            . addslashes($buyer['placeofbusiness']) . '", "'
                                                                . $buyer['type'] . '", "'
                                                                    . addslashes($buyer['citizineship']) . '", "'
                                                                        . addslashes($buyer['sector']) . '", "'
                                                                            . addslashes($buyer['referenceno']) . '", "'
                                                                                . $buyer['datasource'] . '", "'
                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                        . $userid . ', "'
                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                . $userid . ')';

                    $this->logger->write("Utilities : createinvoice() : The SQL is " . $sql, 'r');
                    $this->db->exec(array($sql));

                    try {
                        $by = array();
                        $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE referenceno = "' . $buyer['referenceno'] . '" AND insertedby = ' . $userid));

                        foreach ($r as $obj) {
                            $by [] = $obj;
                        }

                        $buyerid = $by[0]['id'];
                        $this->db->exec(array('UPDATE tblinvoices SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                    } catch (Exception $e) {
                        $this->logger->write("Utilities : createinvoice() : Failed to select and update table tblinvoices. The error message is " . $e->getMessage(), 'r');
                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createinvoice() : The operation to create a buyer was not successful. The error message is " . $e->getMessage(), 'r');
                }
            }

            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : createinvoice() : The operation to create the invoice was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }
    }

    /**
     * @name updateinvoice
     * @desc update an invoice
     * @return bool
     * @param $invoicedetails array, $goods array, $taxes array, $buyer array
     *
     */
    public function updateinvoice($invoicedetails, $goods, $taxes, $buyer, $userid)
    {
        /**
         * 0. Insert a new invoice and retrieve its id
         * 1. Create a param group for goods, taxes, and payments
         * 2. Modify the following arrays
         * 2.1 goods
         * 2.2 payments
         * 2.3 payments
         * 2.4 buyers
         * 3. Insert into the respective tables
         */


        if ($invoicedetails['id']) {


            if ($invoicedetails['gooddetailgroupid']) {
                try {
                    $this->db->exec(array('DELETE FROM tblgooddetails g WHERE g.groupid = ' . $invoicedetails['gooddetailgroupid']));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updateinvoice() : Failed to delete from table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                    return false;
                }
            } else {
                $this->logger->write("Utilities : updateinvoice() : The goods group Id was not specified.", 'r');
                //return false;
            }

            if ($invoicedetails['taxdetailgroupid']) {
                try {
                    $this->db->exec(array('DELETE FROM tbltaxdetails g WHERE g.groupid = ' . $invoicedetails['taxdetailgroupid']));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updateinvoice() : Failed to delete from table tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                    return false;
                }
            } else {
                $this->logger->write("Utilities : updateinvoice() : The taxes group Id was not specified.", 'r');
                //return false;
            }


            if ($invoicedetails['paymentdetailgroupid']) {
                try {
                    $this->db->exec(array('DELETE FROM tblpaymentdetails g WHERE g.groupid = ' . $invoicedetails['paymentdetailgroupid']));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updateinvoice() : Failed to delete from table tblpaymentdetails. The error message is " . $e->getMessage(), 'r');
                    return false;
                }
            } else {
                $this->logger->write("Utilities : updateinvoice() : The payments group Id was not specified.", 'r');
                //return false;
            }

            try {
                $this->db->exec(array('DELETE FROM tblinvoices g WHERE g.id = ' . $invoicedetails['id']));
            } catch (Exception $e) {
                $this->logger->write("Utilities : updateinvoice() : Failed to delete from table tblinvoices. The error message is " . $e->getMessage(), 'r');
                return false;
            }
        } else {
            $this->logger->write("Utilities : updateinvoice() : The invoice Id was not specified.", 'r');
            return false;
        }

        try {

            $netamount = empty($invoicedetails['netamount']) ? '0.00' : $invoicedetails['netamount'];
            $taxamount = empty($invoicedetails['taxamount']) ? '0.00' : $invoicedetails['taxamount'];
            $grossamount = empty($invoicedetails['grossamount']) ? '0.00' : $invoicedetails['grossamount'];
            $itemcount = empty($invoicedetails['itemcount']) ? '0' : $invoicedetails['itemcount'];
            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;
            $currencyRate = empty($invoicedetails['currencyRate']) ? '1' : $invoicedetails['currencyRate'];
            $origrossamount = empty($invoicedetails['origrossamount']) ? '0.00' : $invoicedetails['origrossamount'];
            $SyncToken = empty($invoicedetails['SyncToken']) ? '0' : $invoicedetails['SyncToken'];
            $docType = empty($invoicedetails['docTypeCode']) ? $this->appsettings['INVOICEERPDOCTYPE'] : $invoicedetails['docTypeCode'];

            $sql = 'INSERT INTO tblinvoices
                                    (erpinvoiceid,
                                    erpinvoiceno,
                                    antifakecode,
                                    deviceno,
                                    issueddate,
                                    issuedtime,
                                    operator,
                                    currency,
                                    oriinvoiceid,
                                    invoicetype,
                                    invoicekind,
                                    datasource,
                                    invoiceindustrycode,
                                    einvoiceid,
                                    einvoicenumber,
                                    einvoicedatamatrixcode,
                                    isbatch,
                                    netamount,
                                    taxamount,
                                    grossamount,
                                    origrossamount,
                                    itemcount,
                                    modecode,
                                    modename,
                                    remarks,
                                    buyerid,
                                    sellerid,
                                    issueddatepdf,
                                    grossamountword,
                                    isinvalid,
                                    isrefund,
                                    vouchertype,
                                    vouchertypename,
                                    currencyRate,
                                    SyncToken,
                                    docTypeCode,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($invoicedetails['erpinvoiceid']) . '", "'
                    . addslashes($invoicedetails['erpinvoiceno']) . '", "'
                        . addslashes($invoicedetails['antifakecode']) . '", "'
                            . addslashes($invoicedetails['deviceno']) . '", "'
                                . $invoicedetails['issueddate'] . '", "'
                                    . $invoicedetails['issuedtime'] . '", "'
                                        . addslashes($invoicedetails['operator']) . '", "'
                                            . $invoicedetails['currency'] . '", "'
                                                . $invoicedetails['oriinvoiceid'] . '", '
                                                    . $invoicedetails['invoicetype'] . ', '
                                                        . $invoicedetails['invoicekind'] . ', '
                                                            . $invoicedetails['datasource'] . ', '
                                                                . $invoicedetails['invoiceindustrycode'] . ', "'
                                                                    . addslashes($invoicedetails['einvoiceid']) . '", "'
                                                                        . addslashes($invoicedetails['einvoicenumber']) . '", "'
                                                                            . addslashes($invoicedetails['einvoicedatamatrixcode']) . '", "'
                                                                                . $invoicedetails['isbatch'] . '", '
                                                                                    . $netamount . ', '
                                                                                        . $taxamount . ', '
                                                                                            . $grossamount . ', '
                                                                                                . $origrossamount . ', '
                                                                                                    . $itemcount . ', "'
                                                                                                        . $invoicedetails['modecode'] . '", "'
                                                                                                            . $invoicedetails['modename'] . '", "'
                                                                                                                . addslashes($invoicedetails['remarks']) . '", '
                                                                                                                    . $invoicedetails['buyerid'] . ', '
                                                                                                                        . $invoicedetails['sellerid'] . ', "'
                                                                                                                            . $invoicedetails['issueddatepdf'] . '", "'
                                                                                                                                . $invoicedetails['grossamountword'] . '", '
                                                                                                                                    . $invoicedetails['isinvalid'] . ', '
                                                                                                                                        . $invoicedetails['isrefund'] . ', "'
                                                                                                                                            . addslashes($invoicedetails['vchtype']) . '", "'
                                                                                                                                                . addslashes($invoicedetails['vchtypename']) . '", '
                                                                                                                                                    . $currencyRate . ', '
                                                                                                                                                        . $SyncToken . ', "'
                                                                                                                                                            . $docType . '", "'
                                                                                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                . $userid . ', "'
                                                                                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                        . $userid . ')';

            $this->logger->write("Utilities : updateinvoice() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : updateinvoice() : The invoice has been added", 'r');


            $this->logger->write("Utilities : updateinvoice() : The invoice no is " . $invoicedetails['erpinvoiceno'], 'r');

            $data = array();
            $r = $this->db->exec(array(
                'SELECT id "id" FROM tblinvoices WHERE TRIM(erpinvoiceno) = \'' . $invoicedetails['erpinvoiceno'] . '\''
            ));

            foreach ($r as $obj) {
                $data[] = $obj;
            }

            $id = $data[0]['id'];

            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $gooddetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblinvoices SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    /*Insert Goods*/

                    $i = 0;
                    foreach ($goods as $obj) {


                        $obj['item'] = empty($obj['item']) ? '' : $obj['item'];
                        $obj['itemcode'] = empty($obj['itemcode']) ? '' : $obj['itemcode'];
                        $obj['qty'] = empty($obj['qty']) ? 'NULL' : $obj['qty'];
                        $obj['unitofmeasure'] = empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'];
                        $obj['unitprice'] = empty($obj['unitprice']) ? 'NULL' : $obj['unitprice'];
                        $obj['total'] = empty($obj['total']) ? 'NULL' : $obj['total'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? 'NULL' : $obj['taxrate'];
                        $obj['tax'] = empty($obj['tax']) ? '0.00' : $obj['tax'];
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? 'NULL' : $obj['discounttotal'];
                        $obj['discounttaxrate'] = (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00') ? 'NULL' : $obj['discounttaxrate'];
                        $obj['discountflag'] = empty($obj['discountflag']) ? '2' : $obj['discountflag'];
                        $obj['deemedflag'] = empty($obj['deemedflag']) ? '2' : $obj['deemedflag'];
                        $obj['exciseflag'] = empty($obj['exciseflag']) ? '2' : $obj['exciseflag'];
                        $obj['categoryid'] = empty($obj['categoryid']) ? 'NULL' : $obj['categoryid'];
                        $obj['categoryname'] = empty($obj['categoryname']) ? '' : $obj['categoryname'];
                        $obj['goodscategoryid'] = empty($obj['goodscategoryid']) ? 'NULL' : $obj['goodscategoryid'];
                        $obj['goodscategoryname'] = empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'];
                        $obj['exciserate'] = empty($obj['exciserate']) ? '' : $obj['exciserate'];
                        $obj['exciserule'] = empty($obj['exciserule']) ? 'NULL' : $obj['exciserule'];
                        $obj['excisetax'] = empty($obj['excisetax']) ? 'NULL' : $obj['excisetax'];
                        $obj['pack'] = empty($obj['pack']) ? 'NULL' : $obj['pack'];
                        $obj['stick'] = empty($obj['stick']) ? 'NULL' : $obj['stick'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? 'NULL' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['exciseratename'] = empty($obj['exciseratename']) ? '' : $obj['exciseratename'];

                        $obj['totalWeight'] = empty($obj['totalWeight']) ? 'NULL' : $obj['totalWeight'];
                        $obj['pieceQty'] = empty($obj['pieceQty']) ? 'NULL' : $obj['pieceQty'];

                        $sql = 'INSERT INTO tblgooddetails (
                                    groupid,
                                    item,
                                    itemcode,
                                    qty,
                                    unitofmeasure,
                                    unitprice,
                                    total,
                                    taxid,
                                    taxrate,
                                    tax,
                                    discounttotal,
                                    discounttaxrate,
                                    discountpercentage,
                                    ordernumber,
                                    discountflag,
                                    deemedflag,
                                    exciseflag,
                                    categoryid,
                                    categoryname,
                                    goodscategoryid,
                                    goodscategoryname,
                                    exciserate,
                                    exciserule,
                                    excisetax,
                                    pack,
                                    stick,
                                    exciseunit,
                                    excisecurrency,
                                    exciseratename,
                                    taxcategory,
                                    displayCategoryCode,
                                    unitofmeasurename,
vatApplicableFlag,
vatProjectId,
vatProjectName,
hsCode,
hsName,
totalWeight,
pieceQty,
deemedExemptCode,
pieceMeasureUnit,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $gooddetailgroupid . ', "'
                                . addslashes($obj['item']) . '", "'
                                    . addslashes($obj['itemcode']) . '", '
                                        . $obj['qty'] . ', "'
                                            . $obj['unitofmeasure'] . '", '
                                                . $obj['unitprice'] . ', '
                                                    . $obj['total'] . ', '
                                                        . $obj['taxid'] . ', '
                                                            . $obj['taxrate'] . ', '
                                                                . $obj['tax'] . ', '
                                                                    . $obj['discounttotal'] . ', '
                                                                        . $obj['discounttaxrate'] . ', '
                                                                            . $obj['discountpercentage'] . ', '
                                                                                . $i . ', '
                                                                                    . $obj['discountflag'] . ', '
                                                                                        . $obj['deemedflag'] . ', '
                                                                                            . $obj['exciseflag'] . ', '
                                                                                                . $obj['categoryid'] . ', "'
                                                                                                    . addslashes($obj['categoryname']) . '", '
                                                                                                        . $obj['goodscategoryid'] . ', "'
                                                                                                            . addslashes($obj['goodscategoryname']) . '", "'
                                                                                                                . $obj['exciserate'] . '", '
                                                                                                                    . $obj['exciserule'] . ', '
                                                                                                                        . $obj['excisetax'] . ', '
                                                                                                                            . $obj['pack'] . ', '
                                                                                                                                . $obj['stick'] . ', '
                                                                                                                                    . $obj['exciseunit'] . ', "'
                                                                                                                                        . $obj['excisecurrency'] . '", "'
                                                                                                                                            . $obj['exciseratename'] . '", "'
                                                                                                                                                . $obj['taxcategory'] . '", "'
                                                                                                                                                    . $obj['taxdisplaycategory'] . '", "'
                                                                                                                                                        . $obj['unitofmeasurename'] . '", "'
                                                                                                                                                        . $obj['vatApplicableFlag'] . '", "'
                                                                                                                                                            . $obj['vatProjectId'] . '", "'
                                                                                                                                                                . $obj['vatProjectName'] . '", "'
                                                                                                                                                                    . $obj['hsCode'] . '", "'
                                                                                                                                                                        . $obj['hsName'] . '", '
                                                                                                                                                                            . $obj['totalWeight'] . ', '
                                                                                                                                                                                . $obj['pieceQty'] . ', "'
                                                                                                                                                                                    . $obj['deemedExemptCode'] . '", "'
                                                                                                                                                                                        . $obj['pieceMeasureUnit'] . '", "'
                                                                                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                . $userid . ', "'
                                                                                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                        . $userid . ')';

                        $this->logger->write("Utilities : updateinvoice() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));

                        $i = $i + 1;
                    }

                } catch (Exception $e) {
                    $this->logger->write("Utilities : updateinvoice() : Failed to select and insert into table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : updateinvoice() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $paymentdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblinvoices SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updateinvoice() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : updateinvoice() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $taxdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblinvoices SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    //Get details of goods inserted
                    $t_goods = $this->db->exec(array('SELECT * FROM tblgooddetails g WHERE g.groupid = ' . $gooddetailgroupid . ' ORDER BY id ASC'));

                    //Insert Taxes
                    $j = 0;
                    foreach ($taxes as $obj) {
                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : updateinvoice() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : updateinvoice() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : updateinvoice() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }

                        if (strtoupper(trim($obj['taxcategory'])) == 'DEEMED') {
                            $obj['netamount'] = floor(($obj['netamount'] + $obj['d_netamount']) * 100) / 100;
                            $obj['taxamount'] = floor(($obj['taxamount'] + $obj['d_taxamount']) * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 2, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 2, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 2, '.', '');
                        } else {
                            $obj['netamount'] = round(($obj['netamount'] + $obj['d_netamount']), 2);
                            $obj['taxamount'] = round(($obj['taxamount'] + $obj['d_taxamount']), 2);
                            //$obj['grossamount'] = round($obj['grossamount'], 2);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];
                        }

                        $obj['taxcategory'] = empty($obj['taxcategory']) ? '' : $obj['taxcategory'];
                        $obj['netamount'] = empty($obj['netamount']) ? 'NULL' : $obj['netamount'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? '' : $obj['taxrate'];
                        $obj['taxamount'] = empty($obj['taxamount']) ? '0.00' : $obj['taxamount'];
                        $obj['grossamount'] = empty($obj['grossamount']) ? 'NULL' : $obj['grossamount'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? '' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['taxratename'] = empty($obj['taxratename']) ? '' : $obj['taxratename'];

                        //$obj['goodid'] = empty($obj['goodid'])? 'NULL' : $obj['goodid'];
                        $obj['goodid'] = $t_goods[$j]['id'];

                        $sql = 'INSERT INTO tbltaxdetails (
                                    groupid,
                                    goodid,
                                    taxcategory,
                                    taxcategoryCode,
                                    netamount,
                                    taxrate,
                                    taxamount,
                                    grossamount,
                                    exciseunit,
                                    excisecurrency,
                                    taxratename,
                                    taxdescription,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $taxdetailgroupid . ', '
                                . $obj['goodid'] . ', "'
                                    . addslashes($obj['taxcategory']) . '", "' . addslashes($obj['taxcategoryCode']) . '", '
                                        . $obj['netamount'] . ', '
                                            . $obj['taxrate'] . ', '
                                                . $obj['taxamount'] . ', '
                                                    . $obj['grossamount'] . ', "'
                                                        . $obj['exciseunit'] . '", "'
                                                            . $obj['excisecurrency'] . '", "'
                                                                . $obj['taxratename'] . '", "'
                                                                    . $obj['taxdescription'] . '", "'
                                                                        . date('Y-m-d H:i:s') . '", '
                                                                            . $userid . ', "'
                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : updateinvoice() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));
                        $j = $j + 1;

                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updateinvoice() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : updateinvoice() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
            }

            /*
             if (trim($buyer['id']) !== '' || !empty(trim($buyer['id']))) {
             try{

             $sql = 'INSERT INTO tblbuyers (
             tin,
             ninbrn,
             PassportNum,
             legalname,
             businessname,
             address,
             mobilephone,
             linephone,
             emailaddress,
             placeofbusiness,
             type,
             citizineship,
             sector,
             referenceno,
             datasource,
             inserteddt,
             insertedby,
             modifieddt,
             modifiedby)
             VALUES ("'
             . addslashes($buyer['tin']) . '", "'
             . addslashes($buyer['ninbrn']) . '", "'
             . addslashes($buyer['PassportNum']) . '", "'
             . addslashes($buyer['legalname']) . '", "'
             . addslashes($buyer['businessname']) . '", "'
             . addslashes($buyer['address']) . '", "'
             . addslashes($buyer['mobilephone']) . '", "'
             . addslashes($buyer['linephone']) . '", "'
             . addslashes($buyer['emailaddress']) . '", "'
             . addslashes($buyer['placeofbusiness']) . '", "'
             . $buyer['type'] . '", "'
             . addslashes($buyer['citizineship']) . '", "'
             . addslashes($buyer['sector']) . '", "'
             . addslashes($buyer['referenceno']) . '", "'
             . $buyer['datasource'] . '", "'
             . date('Y-m-d H:i:s') . '", '
             . $userid . ', "'
             . date('Y-m-d H:i:s') . '", '
             . $userid . ')';

             $this->logger->write("Utilities : updateinvoice() : The SQL is " . $sql, 'r');
             $this->db->exec(array($sql));

             try {
             $by = array ();
             $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE referenceno = "' . $buyer['referenceno'] . '" AND insertedby = ' . $userid));

             foreach ( $r as $obj ) {
             $by [] = $obj;
             }

             $buyerid = $by[0]['id'];
             $this->db->exec(array('UPDATE tblinvoices SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
             } catch (Exception $e) {
             $this->logger->write("Utilities : updateinvoice() : Failed to select and update table tblinvoices. The error message is " . $e->getMessage(), 'r');
             }
             } catch (Exception $e) {
             $this->logger->write("Utilities : updateinvoice() : The operation to update a buyer was not successful. The error message is " . $e->getMessage(), 'r');
             }
             }*/

            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : updateinvoice() : The operation to update the invoice was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }
    }

    /**
     * @name createdebitnote
     * @desc create an createdebitnote in eTW
     * @return bool
     * @param $debitnotedetails array, $goods array, $taxes array, $buyer array
     *
     */
    public function createdebitnote($debitnotedetails, $goods, $taxes, $buyer, $userid)
    {
        /**
         * 0. Insert a new invoice and retrieve its id
         * 1. Create a param group for goods, taxes, and payments
         * 2. Modify the following arrays
         * 2.1 goods
         * 2.2 payments
         * 2.3 payments
         * 2.4 buyers
         * 3. Insert into the respective tables
         */


        try {

            $netamount = empty($debitnotedetails['netamount']) ? '0.00' : $debitnotedetails['netamount'];
            $taxamount = empty($debitnotedetails['taxamount']) ? '0.00' : $debitnotedetails['taxamount'];
            $grossamount = empty($debitnotedetails['grossamount']) ? '0.00' : $debitnotedetails['grossamount'];
            $itemcount = empty($debitnotedetails['itemcount']) ? '0' : $debitnotedetails['itemcount'];
            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;
            $currencyRate = empty($debitnotedetails['currencyRate']) ? '1' : $debitnotedetails['currencyRate'];

            $sql = 'INSERT INTO tbldebitnotes
                                    (erpinvoiceid,
                                    erpinvoiceno, erpdebitnoteid, erpdebitnoteno,
                                    antifakecode,
                                    deviceno,
                                    issueddate,
                                    issuedtime,
                                    operator,
                                    currency,
                                    oriinvoiceid,
                                    invoicetype,
                                    invoicekind,
                                    datasource,
                                    invoiceindustrycode,
                                    einvoiceid,
                                    einvoicenumber,
                                    einvoicedatamatrixcode,
                                    isbatch,
                                    netamount,
                                    taxamount,
                                    grossamount,
                                    origrossamount,
                                    itemcount,
                                    modecode,
                                    modename,
                                    remarks,
                                    buyerid,
                                    sellerid,
                                    issueddatepdf,
                                    grossamountword,
                                    isinvalid,
                                    isrefund,
                                    vouchertype,
                                    vouchertypename, currencyRate,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($debitnotedetails['erpinvoiceid']) . '", "'
                    . addslashes($debitnotedetails['erpinvoiceno']) . '", "'
                    . addslashes($debitnotedetails['erpdebitnoteid']) . '", "'
                    . addslashes($debitnotedetails['erpdebitnoteno']) . '", "'
                        . addslashes($debitnotedetails['antifakecode']) . '", "'
                            . addslashes($debitnotedetails['deviceno']) . '", "'
                                . $debitnotedetails['issueddate'] . '", "'
                                    . $debitnotedetails['issuedtime'] . '", "'
                                        . addslashes($debitnotedetails['operator']) . '", "'
                                            . $debitnotedetails['currency'] . '", "'
                                                . $debitnotedetails['oriinvoiceid'] . '", '
                                                    . $debitnotedetails['invoicetype'] . ', '
                                                        . $debitnotedetails['invoicekind'] . ', '
                                                            . $debitnotedetails['datasource'] . ', '
                                                                . $debitnotedetails['invoiceindustrycode'] . ', "'
                                                                    . addslashes($debitnotedetails['einvoiceid']) . '", "'
                                                                        . addslashes($debitnotedetails['einvoicenumber']) . '", "'
                                                                            . addslashes($debitnotedetails['einvoicedatamatrixcode']) . '", "'
                                                                                . $debitnotedetails['isbatch'] . '", '
                                                                                    . $netamount . ', '
                                                                                        . $taxamount . ', '
                                                                                            . $grossamount . ', '
                                                                                                . $debitnotedetails['origrossamount'] . ', '
                                                                                                    . $itemcount . ', "'
                                                                                                        . $debitnotedetails['modecode'] . '", "'
                                                                                                            . $debitnotedetails['modename'] . '", "'
                                                                                                                . addslashes($debitnotedetails['remarks']) . '", '
                                                                                                                    . 'NULL, '
                                                                                                                        . $debitnotedetails['sellerid'] . ', "'
                                                                                                                            . $debitnotedetails['issueddatepdf'] . '", "'
                                                                                                                                . $debitnotedetails['grossamountword'] . '", '
                                                                                                                                    . $debitnotedetails['isinvalid'] . ', '
                                                                                                                                        . $debitnotedetails['isrefund'] . ', "'
                                                                                                                                            . addslashes($debitnotedetails['vchtype']) . '", "'
                                                                                                                                                . addslashes($debitnotedetails['vchtypename']) . '", '
                                                                                                                                                    . $currencyRate . ', "'
                                                                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                                                                        . $userid . ', "'
                                                                                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                . $userid . ')';

            $this->logger->write("Utilities : createdebitnote() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : createdebitnote() : The invoice has been added", 'r');


            $this->logger->write("Utilities : createdebitnote() : The FDN is " . $debitnotedetails['antifakecode'], 'r');

            $data = array();
            $r = $this->db->exec(array(
                'SELECT id "id" FROM tbldebitnotes WHERE TRIM(antifakecode) = \'' . $debitnotedetails['antifakecode'] , '\''
            ));

            foreach ($r as $obj) {
                $data[] = $obj;
            }

            $id = $data[0]['id'];

            try {
                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;

                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $gooddetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tbldebitnotes SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    /*Insert Goods*/

                    $i = 0;
                    foreach ($goods as $obj) {


                        $obj['item'] = empty($obj['item']) ? '' : $obj['item'];
                        $obj['itemcode'] = empty($obj['itemcode']) ? '' : $obj['itemcode'];
                        $obj['qty'] = empty($obj['qty']) ? 'NULL' : $obj['qty'];
                        $obj['unitofmeasure'] = empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'];
                        $obj['unitprice'] = empty($obj['unitprice']) ? 'NULL' : $obj['unitprice'];
                        $obj['total'] = empty($obj['total']) ? 'NULL' : $obj['total'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? 'NULL' : $obj['taxrate'];
                        $obj['tax'] = empty($obj['tax']) ? '0.00' : $obj['tax'];
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? 'NULL' : $obj['discounttotal'];
                        $obj['discounttaxrate'] = (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00') ? 'NULL' : $obj['discounttaxrate'];
                        $obj['discountflag'] = empty($obj['discountflag']) ? '2' : $obj['discountflag'];
                        $obj['deemedflag'] = empty($obj['deemedflag']) ? '2' : $obj['deemedflag'];
                        $obj['exciseflag'] = empty($obj['exciseflag']) ? '2' : $obj['exciseflag'];
                        $obj['categoryid'] = empty($obj['categoryid']) ? 'NULL' : $obj['categoryid'];
                        $obj['categoryname'] = empty($obj['categoryname']) ? '' : $obj['categoryname'];
                        $obj['goodscategoryid'] = empty($obj['goodscategoryid']) ? 'NULL' : $obj['goodscategoryid'];
                        $obj['goodscategoryname'] = empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'];
                        $obj['exciserate'] = empty($obj['exciserate']) ? '' : $obj['exciserate'];
                        $obj['exciserule'] = empty($obj['exciserule']) ? 'NULL' : $obj['exciserule'];
                        $obj['excisetax'] = empty($obj['excisetax']) ? 'NULL' : $obj['excisetax'];
                        $obj['pack'] = empty($obj['pack']) ? 'NULL' : $obj['pack'];
                        $obj['stick'] = empty($obj['stick']) ? 'NULL' : $obj['stick'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? 'NULL' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['exciseratename'] = empty($obj['exciseratename']) ? '' : $obj['exciseratename'];
                        $obj['vatProjectId'] = empty($obj['vatProjectId']) ? '' : $obj['vatProjectId'];
                        $obj['vatProjectName'] = empty($obj['vatProjectName']) ? '' : $obj['vatProjectName'];

                        $sql = 'INSERT INTO tblgooddetails (
                                    groupid,
                                    item,
                                    itemcode,
                                    qty,
                                    unitofmeasure,
                                    unitprice,
                                    total,
                                    taxid,
                                    taxrate,
                                    tax,
                                    discounttotal,
                                    discounttaxrate,
                                    discountpercentage,
                                    ordernumber,
                                    discountflag,
                                    deemedflag,
                                    exciseflag,
                                    categoryid,
                                    categoryname,
                                    goodscategoryid,
                                    goodscategoryname,
                                    exciserate,
                                    exciserule,
                                    excisetax,
                                    pack,
                                    stick,
                                    exciseunit,
                                    excisecurrency,
                                    exciseratename,
                                    taxcategory,
                                    unitofmeasurename,
                                    projectId,
                                    projectName,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $gooddetailgroupid . ', "'
                                . addslashes($obj['item']) . '", "'
                                    . addslashes($obj['itemcode']) . '", '
                                        . $obj['qty'] . ', "'
                                            . $obj['unitofmeasure'] . '", '
                                                . $obj['unitprice'] . ', '
                                                    . $obj['total'] . ', '
                                                        . $obj['taxid'] . ', '
                                                            . $obj['taxrate'] . ', '
                                                                . $obj['tax'] . ', '
                                                                    . $obj['discounttotal'] . ', '
                                                                        . $obj['discounttaxrate'] . ', '
                                                                            . $obj['discountpercentage'] . ', '
                                                                                . $i . ', '
                                                                                    . $obj['discountflag'] . ', '
                                                                                        . $obj['deemedflag'] . ', '
                                                                                            . $obj['exciseflag'] . ', '
                                                                                                . $obj['categoryid'] . ', "'
                                                                                                    . addslashes($obj['categoryname']) . '", '
                                                                                                        . $obj['goodscategoryid'] . ', "'
                                                                                                            . addslashes($obj['goodscategoryname']) . '", "'
                                                                                                                . $obj['exciserate'] . '", '
                                                                                                                    . $obj['exciserule'] . ', '
                                                                                                                        . $obj['excisetax'] . ', '
                                                                                                                            . $obj['pack'] . ', '
                                                                                                                                . $obj['stick'] . ', '
                                                                                                                                    . $obj['exciseunit'] . ', "'
                                                                                                                                        . $obj['excisecurrency'] . '", "'
                                                                                                                                            . $obj['exciseratename'] . '", "'
                                                                                                                                                . $obj['taxdisplaycategory'] . '", "'
                                                                                                                                                    . $obj['unitofmeasurename'] . '", "'
                                                                                                                                                    . $obj['vatProjectId'] . '", "'
                                                                                                                                                        . $obj['vatProjectName'] . '", "'
                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                            . $userid . ', "'
                                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : createdebitnote() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));

                        $i = $i + 1;
                    }

                } catch (Exception $e) {
                    $this->logger->write("Utilities : createdebitnote() : Failed to select and insert into table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createdebitnote() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;

                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $paymentdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tbldebitnotes SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createdebitnote() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createdebitnote() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;

                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['DEBITNOTEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $taxdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tbldebitnotes SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    //Get details of goods inserted
                    $t_goods = $this->db->exec(array('SELECT * FROM tblgooddetails g WHERE g.groupid = ' . $gooddetailgroupid . ' ORDER BY id ASC'));

                    //Insert Taxes
                    $j = 0;
                    foreach ($taxes as $obj) {
                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : createdebitnote() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : createdebitnote() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : createdebitnote() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }

                        if (strtoupper(trim($obj['taxcategory'])) == 'D: DEEMED (18%)') {
                            $obj['netamount'] = floor(($obj['netamount'] + $obj['d_netamount']) * 100) / 100;
                            $obj['taxamount'] = floor(($obj['taxamount'] + $obj['d_taxamount']) * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 4, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 4, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 4, '.', '');
                        } else {
                            $obj['netamount'] = round(($obj['netamount'] + $obj['d_netamount']), 4);
                            $obj['taxamount'] = round(($obj['taxamount'] + $obj['d_taxamount']), 4);
                            //$obj['grossamount'] = round($obj['grossamount'], 4);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];
                        }


                        $obj['taxcategory'] = empty($obj['taxcategory']) ? '' : $obj['taxcategory'];
                        $obj['netamount'] = empty($obj['netamount']) ? 'NULL' : $obj['netamount'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? '' : $obj['taxrate'];
                        $obj['taxamount'] = empty($obj['taxamount']) ? '0.00' : $obj['taxamount'];
                        $obj['grossamount'] = empty($obj['grossamount']) ? 'NULL' : $obj['grossamount'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? '' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['taxratename'] = empty($obj['taxratename']) ? '' : $obj['taxratename'];

                        //$obj['goodid'] = empty($obj['goodid'])? 'NULL' : $obj['goodid'];
                        $obj['goodid'] = $t_goods[$j]['id'];

                        $sql = 'INSERT INTO tbltaxdetails (
                                    groupid,
                                    goodid,
                                    taxcategory,
                                    netamount,
                                    taxrate,
                                    taxamount,
                                    grossamount,
                                    exciseunit,
                                    excisecurrency,
                                    taxratename,
                                    taxdescription,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $taxdetailgroupid . ', '
                                . $obj['goodid'] . ', "'
                                    . addslashes($obj['taxcategory']) . '", '
                                        . $obj['netamount'] . ', '
                                            . $obj['taxrate'] . ', '
                                                . $obj['taxamount'] . ', '
                                                    . $obj['grossamount'] . ', "'
                                                        . $obj['exciseunit'] . '", "'
                                                            . $obj['excisecurrency'] . '", "'
                                                                . $obj['taxratename'] . '", "'
                                                                    . $obj['taxdescription'] . '", "'
                                                                        . date('Y-m-d H:i:s') . '", '
                                                                            . $userid . ', "'
                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : createdebitnote() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));
                        $j = $j + 1;

                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createdebitnote() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createdebitnote() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
            }

            if (trim($buyer['referenceno']) !== '' || !empty(trim($buyer['referenceno']))) {
                try {

                    $sql = 'INSERT INTO tblbuyers (
                                    tin,
                                    ninbrn,
                                    PassportNum,
                                    legalname,
                                    businessname,
                                    address,
                                    mobilephone,
                                    linephone,
                                    emailaddress,
                                    placeofbusiness,
                                    type,
                                    citizineship,
                                    sector,
                                    referenceno,
                                    datasource,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                        . addslashes($buyer['tin']) . '", "'
                            . addslashes($buyer['ninbrn']) . '", "'
                                . addslashes($buyer['PassportNum']) . '", "'
                                    . addslashes($buyer['legalname']) . '", "'
                                        . addslashes($buyer['businessname']) . '", "'
                                            . addslashes($buyer['address']) . '", "'
                                                . addslashes($buyer['mobilephone']) . '", "'
                                                    . addslashes($buyer['linephone']) . '", "'
                                                        . addslashes($buyer['emailaddress']) . '", "'
                                                            . addslashes($buyer['placeofbusiness']) . '", "'
                                                                . $buyer['type'] . '", "'
                                                                    . addslashes($buyer['citizineship']) . '", "'
                                                                        . addslashes($buyer['sector']) . '", "'
                                                                            . addslashes($buyer['referenceno']) . '", "'
                                                                                . $buyer['datasource'] . '", "'
                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                        . $userid . ', "'
                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                . $userid . ')';

                    $this->logger->write("Utilities : createdebitnote() : The SQL is " . $sql, 'r');
                    $this->db->exec(array($sql));

                    try {
                        $by = array();
                        $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE referenceno = "' . $buyer['referenceno'] . '" AND insertedby = ' . $userid));

                        foreach ($r as $obj) {
                            $by [] = $obj;
                        }

                        $buyerid = $by[0]['id'];
                        $this->db->exec(array('UPDATE tbldebitnotes SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                    } catch (Exception $e) {
                        $this->logger->write("Utilities : createdebitnote() : Failed to select and update table tblinvoices. The error message is " . $e->getMessage(), 'r');
                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createdebitnote() : The operation to create a buyer was not successful. The error message is " . $e->getMessage(), 'r');
                }
            }


            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : createdebitnote() : The operation to create the invoice was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }
    }


    /**
     * @name updatecreditnote
     * @desc update an updatecreditnote in eTW
     * @return bool
     * @param $creditnotedetails array, $goods array, $taxes array, $buyer array
     *
     */
    public function updatecreditnote($creditnotedetails, $goods, $taxes, $buyer, $userid)
    {
        /**
         * 0. Insert a new credit note and retrieve its id
         * 1. Create a param group for goods, taxes, and payments
         * 2. Modify the following arrays
         * 2.1 goods
         * 2.2 payments
         * 2.3 payments
         * 2.4 buyers
         * 3. Insert into the respective tables
         */

        if ($creditnotedetails['id']) {


            if ($creditnotedetails['gooddetailgroupid']) {
                try {
                    $this->db->exec(array('DELETE FROM tblgooddetails g WHERE g.groupid = ' . $creditnotedetails['gooddetailgroupid']));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updatecreditnote() : Failed to delete from table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                    return false;
                }
            } else {
                $this->logger->write("Utilities : updatecreditnote() : The goods group Id was not specified.", 'r');
                //return false;
            }

            if ($creditnotedetails['taxdetailgroupid']) {
                try {
                    $this->db->exec(array('DELETE FROM tbltaxdetails g WHERE g.groupid = ' . $creditnotedetails['taxdetailgroupid']));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updatecreditnote() : Failed to delete from table tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                    return false;
                }
            } else {
                $this->logger->write("Utilities : updatecreditnote() : The taxes group Id was not specified.", 'r');
                //return false;
            }


            if ($creditnotedetails['paymentdetailgroupid']) {
                try {
                    $this->db->exec(array('DELETE FROM tblpaymentdetails g WHERE g.groupid = ' . $creditnotedetails['paymentdetailgroupid']));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updatecreditnote() : Failed to delete from table tblpaymentdetails. The error message is " . $e->getMessage(), 'r');
                    return false;
                }
            } else {
                $this->logger->write("Utilities : updatecreditnote() : The payments group Id was not specified.", 'r');
                //return false;
            }

            try {
                $this->db->exec(array('DELETE FROM tblcreditnotes g WHERE g.id = ' . $creditnotedetails['id']));
            } catch (Exception $e) {
                $this->logger->write("Utilities : updatecreditnote() : Failed to delete from table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                return false;
            }
        } else {
            $this->logger->write("Utilities : updatecreditnote() : The creditnote Id was not specified.", 'r');
            return false;
        }


        try {

            $netamount = empty($creditnotedetails['netamount']) ? '0.00' : $creditnotedetails['netamount'];
            $taxamount = empty($creditnotedetails['taxamount']) ? '0.00' : $creditnotedetails['taxamount'];
            $grossamount = empty($creditnotedetails['grossamount']) ? '0.00' : $creditnotedetails['grossamount'];
            $itemcount = empty($creditnotedetails['itemcount']) ? '0' : $creditnotedetails['itemcount'];
            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;
            $SyncToken = empty($creditnotedetails['SyncToken']) ? '0' : $creditnotedetails['SyncToken'];
            $docType = empty($creditnotedetails['docTypeCode']) ? $this->$creditnotedetails['CREDITMEMOERPDOCTYPE'] : $creditnotedetails['docTypeCode'];

            $creditnotedetails['origrossamount'] = empty($creditnotedetails['origrossamount']) ? '0' : $creditnotedetails['origrossamount'];

            $sql = 'INSERT INTO tblcreditnotes
                                    (erpinvoiceid,
                                    erpinvoiceno, erpcreditnoteid, erpcreditnoteno,
                                    antifakecode,
                                    deviceno,
                                    issueddate,
                                    issuedtime,
                                    operator,
                                    currency,
                                    oriinvoiceid,
                                    invoicetype,
                                    invoicekind,
                                    datasource,
                                    invoiceindustrycode,
                                    einvoiceid,
                                    einvoicenumber,
                                    einvoicedatamatrixcode,
                                    isbatch,
                                    netamount,
                                    taxamount,
                                    grossamount,
                                    origrossamount,
                                    itemcount,
                                    modecode,
                                    modename,
                                    remarks,
                                    buyerid,
                                    sellerid,
                                    issueddatepdf,
                                    grossamountword,
                                    isinvalid,
                                    isrefund,
                                    vouchertype,
                                    vouchertypename,
                                    oriinvoiceno,
                                    reasoncode,
                                    reason,
                                    referenceno,
                                    invoiceapplycategorycode,
                                    SyncToken,
                                    docTypeCode,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($creditnotedetails['erpinvoiceid']) . '", "'
                    . addslashes($creditnotedetails['erpinvoiceno']) . '", "'
                        . addslashes($creditnotedetails['erpcreditnoteid']) . '", "'
                            . addslashes($creditnotedetails['erpcreditnoteno']) . '", "'
                                . addslashes($creditnotedetails['antifakecode']) . '", "'
                                    . addslashes($creditnotedetails['deviceno']) . '", "'
                                        . $creditnotedetails['issueddate'] . '", "'
                                            . $creditnotedetails['issuedtime'] . '", "'
                                                . addslashes($creditnotedetails['operator']) . '", "'
                                                    . $creditnotedetails['currency'] . '", "'
                                                        . $creditnotedetails['oriinvoiceid'] . '", '
                                                            . $creditnotedetails['invoicetype'] . ', '
                                                                . $creditnotedetails['invoicekind'] . ', '
                                                                    . $creditnotedetails['datasource'] . ', '
                                                                        . $creditnotedetails['invoiceindustrycode'] . ', "'
                                                                            . addslashes($creditnotedetails['einvoiceid']) . '", "'
                                                                                . addslashes($creditnotedetails['einvoicenumber']) . '", "'
                                                                                    . addslashes($creditnotedetails['einvoicedatamatrixcode']) . '", "'
                                                                                        . $creditnotedetails['isbatch'] . '", '
                                                                                            . $netamount . ', '
                                                                                                . $taxamount . ', '
                                                                                                    . $grossamount . ', '
                                                                                                        . $creditnotedetails['origrossamount'] . ', '
                                                                                                            . $itemcount . ', "'
                                                                                                                . $creditnotedetails['modecode'] . '", "'
                                                                                                                    . $creditnotedetails['modename'] . '", "'
                                                                                                                        . addslashes($creditnotedetails['remarks']) . '", '
                                                                                                                            . 'NULL, '
                                                                                                                                . $creditnotedetails['sellerid'] . ', "'
                                                                                                                                    . $creditnotedetails['issueddatepdf'] . '", "'
                                                                                                                                        . $creditnotedetails['grossamountword'] . '", '
                                                                                                                                            . $creditnotedetails['isinvalid'] . ', '
                                                                                                                                                . $creditnotedetails['isrefund'] . ', "'
                                                                                                                                                    . addslashes($creditnotedetails['vchtype']) . '", "'
                                                                                                                                                        . addslashes($creditnotedetails['vchtypename']) . '", "'
                                                                                                                                                            . addslashes($creditnotedetails['oriinvoiceno']) . '", "'
                                                                                                                                                                . addslashes($creditnotedetails['reasoncode']) . '", "'
                                                                                                                                                                    . addslashes($creditnotedetails['reason']) . '", "'
                                                                                                                                                                        . addslashes($creditnotedetails['referenceno']) . '", "'
                                                                                                                                                                            . addslashes($creditnotedetails['invoiceapplycategorycode']) . '", '
                                                                                                                                                                                . $SyncToken . ', "'
                                                                                                                                                                                    . $docType . '", "'
                                                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                                            . $userid . ', "'
                                                                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                                                    . $userid . ')';

            $this->logger->write("Utilities : updatecreditnote() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : updatecreditnote() : The credit note has been added", 'r');


            $this->logger->write("Utilities : updatecreditnote() : The erpcreditnoteno is " . $creditnotedetails['erpcreditnoteno'], 'r');

            $data = array();
            $r = $this->db->exec(array(
                'SELECT id "id" FROM tblcreditnotes WHERE TRIM(erpcreditnoteno) = \'' . $creditnotedetails['erpcreditnoteno'] . '\''
            ));

            foreach ($r as $obj) {
                $data[] = $obj;
            }

            $id = $data[0]['id'];

            try {
                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;

                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $gooddetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblcreditnotes SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    /*Insert Goods*/

                    $i = 0;
                    foreach ($goods as $obj) {


                        $obj['item'] = empty($obj['item']) ? '' : $obj['item'];
                        $obj['itemcode'] = empty($obj['itemcode']) ? '' : $obj['itemcode'];
                        $obj['qty'] = empty($obj['qty']) ? 'NULL' : $obj['qty'];
                        $obj['unitofmeasure'] = empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'];
                        $obj['unitprice'] = empty($obj['unitprice']) ? 'NULL' : $obj['unitprice'];
                        $obj['total'] = empty($obj['total']) ? 'NULL' : $obj['total'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? 'NULL' : $obj['taxrate'];
                        $obj['tax'] = empty($obj['tax']) ? '0.00' : $obj['tax'];
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? 'NULL' : $obj['discounttotal'];
                        $obj['discounttaxrate'] = (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00') ? 'NULL' : $obj['discounttaxrate'];
                        $obj['discountflag'] = empty($obj['discountflag']) ? '2' : $obj['discountflag'];
                        $obj['deemedflag'] = empty($obj['deemedflag']) ? '2' : $obj['deemedflag'];
                        $obj['exciseflag'] = empty($obj['exciseflag']) ? '2' : $obj['exciseflag'];
                        $obj['categoryid'] = empty($obj['categoryid']) ? 'NULL' : $obj['categoryid'];
                        $obj['categoryname'] = empty($obj['categoryname']) ? '' : $obj['categoryname'];
                        $obj['goodscategoryid'] = empty($obj['goodscategoryid']) ? 'NULL' : $obj['goodscategoryid'];
                        $obj['goodscategoryname'] = empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'];
                        $obj['exciserate'] = empty($obj['exciserate']) ? '' : $obj['exciserate'];
                        $obj['exciserule'] = empty($obj['exciserule']) ? 'NULL' : $obj['exciserule'];
                        $obj['excisetax'] = empty($obj['excisetax']) ? 'NULL' : $obj['excisetax'];
                        $obj['pack'] = empty($obj['pack']) ? 'NULL' : $obj['pack'];
                        $obj['stick'] = empty($obj['stick']) ? 'NULL' : $obj['stick'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? 'NULL' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['exciseratename'] = empty($obj['exciseratename']) ? '' : $obj['exciseratename'];
                        $obj['ordernumber'] = empty($obj['ordernumber']) ? $i : $obj['ordernumber'];

                        $sql = 'INSERT INTO tblgooddetails (
                                    groupid,
                                    item,
                                    itemcode,
                                    qty,
                                    unitofmeasure,
                                    unitprice,
                                    total,
                                    taxid,
                                    taxrate,
                                    tax,
                                    discounttotal,
                                    discounttaxrate,
                                    discountpercentage,
                                    ordernumber,
                                    discountflag,
                                    deemedflag,
                                    exciseflag,
                                    categoryid,
                                    categoryname,
                                    goodscategoryid,
                                    goodscategoryname,
                                    exciserate,
                                    exciserule,
                                    excisetax,
                                    pack,
                                    stick,
                                    exciseunit,
                                    excisecurrency,
                                    exciseratename,
                                    taxcategory,
                                    unitofmeasurename,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $gooddetailgroupid . ', "'
                                . addslashes($obj['item']) . '", "'
                                    . addslashes($obj['itemcode']) . '", '
                                        . $obj['qty'] . ', "'
                                            . $obj['unitofmeasure'] . '", '
                                                . $obj['unitprice'] . ', '
                                                    . $obj['total'] . ', '
                                                        . $obj['taxid'] . ', '
                                                            . $obj['taxrate'] . ', '
                                                                . $obj['tax'] . ', '
                                                                    . $obj['discounttotal'] . ', '
                                                                        . $obj['discounttaxrate'] . ', '
                                                                            . $obj['discountpercentage'] . ', '
                                                                                . $obj['ordernumber'] . ', '
                                                                                    . $obj['discountflag'] . ', '
                                                                                        . $obj['deemedflag'] . ', '
                                                                                            . $obj['exciseflag'] . ', '
                                                                                                . $obj['categoryid'] . ', "'
                                                                                                    . addslashes($obj['categoryname']) . '", '
                                                                                                        . $obj['goodscategoryid'] . ', "'
                                                                                                            . addslashes($obj['goodscategoryname']) . '", "'
                                                                                                                . $obj['exciserate'] . '", '
                                                                                                                    . $obj['exciserule'] . ', '
                                                                                                                        . $obj['excisetax'] . ', '
                                                                                                                            . $obj['pack'] . ', '
                                                                                                                                . $obj['stick'] . ', '
                                                                                                                                    . $obj['exciseunit'] . ', "'
                                                                                                                                        . $obj['excisecurrency'] . '", "'
                                                                                                                                            . $obj['exciseratename'] . '", "'
                                                                                                                                                . $obj['taxdisplaycategory'] . '", "'
                                                                                                                                                    . $obj['unitofmeasurename'] . '", "'
                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                            . $userid . ', "'
                                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : updatecreditnote() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));

                        $i = $i + 1;
                    }

                } catch (Exception $e) {
                    $this->logger->write("Utilities : updatecreditnote() : Failed to select and insert into table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : updatecreditnote() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;

                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $paymentdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblcreditnotes SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updatecreditnote() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : updatecreditnote() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;

                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $taxdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblcreditnotes SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    //Get details of goods inserted
                    $t_goods = $this->db->exec(array('SELECT * FROM tblgooddetails g WHERE g.groupid = ' . $gooddetailgroupid . ' ORDER BY id ASC'));

                    //Insert Taxes
                    $j = 0;
                    foreach ($taxes as $obj) {
                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : updatecreditnote() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : updatecreditnote() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : updatecreditnote() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }

                        if (strtoupper(trim($obj['taxcategory'])) == 'DEEMED') {
                            $obj['netamount'] = floor(($obj['netamount'] + $obj['d_netamount']) * 100) / 100;
                            $obj['taxamount'] = floor(($obj['taxamount'] + $obj['d_taxamount']) * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 2, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 2, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 2, '.', '');
                        } else {
                            $obj['netamount'] = round(($obj['netamount'] + $obj['d_netamount']), 2);
                            $obj['taxamount'] = round(($obj['taxamount'] + $obj['d_taxamount']), 2);
                            //$obj['grossamount'] = round($obj['grossamount'], 2);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];
                        }

                        $obj['taxcategory'] = empty($obj['taxcategory']) ? '' : $obj['taxcategory'];
                        $obj['netamount'] = empty($obj['netamount']) ? 'NULL' : $obj['netamount'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? '' : $obj['taxrate'];
                        $obj['taxamount'] = empty($obj['taxamount']) ? '0.00' : $obj['taxamount'];
                        $obj['grossamount'] = empty($obj['grossamount']) ? 'NULL' : $obj['grossamount'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? '' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['taxratename'] = empty($obj['taxratename']) ? '' : $obj['taxratename'];

                        //$obj['goodid'] = empty($obj['goodid'])? 'NULL' : $obj['goodid'];
                        $obj['goodid'] = $t_goods[$j]['id'];

                        $sql = 'INSERT INTO tbltaxdetails (
                                    groupid,
                                    goodid,
                                    taxcategory,
                                    netamount,
                                    taxrate,
                                    taxamount,
                                    grossamount,
                                    exciseunit,
                                    excisecurrency,
                                    taxratename,
                                    taxdescription,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $taxdetailgroupid . ', '
                                . $obj['goodid'] . ', "'
                                    . addslashes($obj['taxcategory']) . '", '
                                        . $obj['netamount'] . ', '
                                            . $obj['taxrate'] . ', '
                                                . $obj['taxamount'] . ', '
                                                    . $obj['grossamount'] . ', "'
                                                        . $obj['exciseunit'] . '", "'
                                                            . $obj['excisecurrency'] . '", "'
                                                                . $obj['taxratename'] . '", "'
                                                                    . $obj['taxdescription'] . '", "'
                                                                        . date('Y-m-d H:i:s') . '", '
                                                                            . $userid . ', "'
                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : updatecreditnote() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));
                        $j = $j + 1;

                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : updatecreditnote() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : updatecreditnote() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : updatecreditnote() : The operation to update the credit note was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }
    }

    /**
     * @name createcreditnote
     * @desc create an createcreditnote
     * @return bool
     * @param $creditnotedetails array, $goods array, $taxes array, $buyer array
     *
     */
    public function createcreditnote($creditnotedetails, $goods, $taxes, $buyer, $userid)
    {
        /**
         * 0. Insert a new credit note and retrieve its id
         * 1. Create a param group for goods, taxes, and payments
         * 2. Modify the following arrays
         * 2.1 goods
         * 2.2 payments
         * 2.3 payments
         * 2.4 buyers
         * 3. Insert into the respective tables
         */


        try {

            $netamount = empty($creditnotedetails['netamount']) ? '0.00' : $creditnotedetails['netamount'];
            $taxamount = empty($creditnotedetails['taxamount']) ? '0.00' : $creditnotedetails['taxamount'];
            $grossamount = empty($creditnotedetails['grossamount']) ? '0.00' : $creditnotedetails['grossamount'];
            $itemcount = empty($creditnotedetails['itemcount']) ? '0' : $creditnotedetails['itemcount'];
            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;

            $creditnotedetails['origrossamount'] = empty($creditnotedetails['origrossamount']) ? '0' : $creditnotedetails['origrossamount'];

            $sql = 'INSERT INTO tblcreditnotes
                                    (erpinvoiceid,
                                    erpinvoiceno, erpcreditnoteid, erpcreditnoteno,
                                    antifakecode,
                                    deviceno,
                                    issueddate,
                                    issuedtime,
                                    operator,
                                    currency,
                                    oriinvoiceid,
                                    invoicetype,
                                    invoicekind,
                                    datasource,
                                    invoiceindustrycode,
                                    einvoiceid,
                                    einvoicenumber,
                                    einvoicedatamatrixcode,
                                    isbatch,
                                    netamount,
                                    taxamount,
                                    grossamount,
                                    origrossamount,
                                    itemcount,
                                    modecode,
                                    modename,
                                    remarks,
                                    buyerid,
                                    sellerid,
                                    issueddatepdf,
                                    grossamountword,
                                    isinvalid,
                                    isrefund,
                                    vouchertype,
                                    vouchertypename,
                                    oriinvoiceno,
                                    reasoncode,
                                    reason,
                                    referenceno,
                                    invoiceapplycategorycode,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($creditnotedetails['erpinvoiceid']) . '", "'
                    . addslashes($creditnotedetails['erpinvoiceno']) . '", "'
                    . addslashes($creditnotedetails['erpcreditnoteid']) . '", "'
                    . addslashes($creditnotedetails['erpcreditnoteno']) . '", "'
                        . addslashes($creditnotedetails['antifakecode']) . '", "'
                            . addslashes($creditnotedetails['deviceno']) . '", "'
                                . $creditnotedetails['issueddate'] . '", "'
                                    . $creditnotedetails['issuedtime'] . '", "'
                                        . addslashes($creditnotedetails['operator']) . '", "'
                                            . $creditnotedetails['currency'] . '", "'
                                                . $creditnotedetails['oriinvoiceid'] . '", '
                                                    . $creditnotedetails['invoicetype'] . ', '
                                                        . $creditnotedetails['invoicekind'] . ', '
                                                            . $creditnotedetails['datasource'] . ', '
                                                                . $creditnotedetails['invoiceindustrycode'] . ', "'
                                                                    . addslashes($creditnotedetails['einvoiceid']) . '", "'
                                                                        . addslashes($creditnotedetails['einvoicenumber']) . '", "'
                                                                            . addslashes($creditnotedetails['einvoicedatamatrixcode']) . '", "'
                                                                                . $creditnotedetails['isbatch'] . '", '
                                                                                    . $netamount . ', '
                                                                                        . $taxamount . ', '
                                                                                            . $grossamount . ', '
                                                                                                . $creditnotedetails['origrossamount'] . ', '
                                                                                                    . $itemcount . ', "'
                                                                                                        . $creditnotedetails['modecode'] . '", "'
                                                                                                            . $creditnotedetails['modename'] . '", "'
                                                                                                                . addslashes($creditnotedetails['remarks']) . '", '
                                                                                                                    . 'NULL, '
                                                                                                                        . $creditnotedetails['sellerid'] . ', "'
                                                                                                                            . $creditnotedetails['issueddatepdf'] . '", "'
                                                                                                                                . $creditnotedetails['grossamountword'] . '", '
                                                                                                                                    . $creditnotedetails['isinvalid'] . ', '
                                                                                                                                        . $creditnotedetails['isrefund'] . ', "'
                                                                                                                                            . addslashes($creditnotedetails['vchtype']) . '", "'
                                                                                                                                                . addslashes($creditnotedetails['vchtypename']) . '", "'
                                                                                                                                                . addslashes($creditnotedetails['oriinvoiceno']) . '", "'
                                                                                                                                                . addslashes($creditnotedetails['reasoncode']) . '", "'
                                                                                                                                                . addslashes($creditnotedetails['reason']) . '", "'
                                                                                                                                                . addslashes($creditnotedetails['referenceno']) . '", "'
                                                                                                                                                . addslashes($creditnotedetails['invoiceapplycategorycode']) . '", "'
                                                                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                                                                        . $userid . ', "'
                                                                                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                . $userid . ')';

            $this->logger->write("Utilities : createcreditnote() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : createcreditnote() : The invoice has been added", 'r');


            $this->logger->write("Utilities : createcreditnote() : The referenceno is " . $creditnotedetails['referenceno'], 'r');

            $data = array();
            $r = $this->db->exec(array(
                'SELECT id "id" FROM tblcreditnotes WHERE TRIM(referenceno) = \'' . $creditnotedetails['referenceno'] . '\''
            ));

            foreach ($r as $obj) {
                $data[] = $obj;
            }

            $id = $data[0]['id'];

            try {
                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;

                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $gooddetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblcreditnotes SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    /*Insert Goods*/

                    $i = 0;
                    foreach ($goods as $obj) {


                        $obj['item'] = empty($obj['item']) ? '' : $obj['item'];
                        $obj['itemcode'] = empty($obj['itemcode']) ? '' : $obj['itemcode'];
                        $obj['qty'] = empty($obj['qty']) ? 'NULL' : $obj['qty'];
                        $obj['unitofmeasure'] = empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'];
                        $obj['unitprice'] = empty($obj['unitprice']) ? 'NULL' : $obj['unitprice'];
                        $obj['total'] = empty($obj['total']) ? 'NULL' : $obj['total'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? 'NULL' : $obj['taxrate'];
                        $obj['tax'] = empty($obj['tax']) ? '0.00' : $obj['tax'];
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? 'NULL' : $obj['discounttotal'];
                        $obj['discounttaxrate'] = (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00') ? 'NULL' : $obj['discounttaxrate'];
                        $obj['discountflag'] = empty($obj['discountflag']) ? '2' : $obj['discountflag'];
                        $obj['deemedflag'] = empty($obj['deemedflag']) ? '2' : $obj['deemedflag'];
                        $obj['exciseflag'] = empty($obj['exciseflag']) ? '2' : $obj['exciseflag'];
                        $obj['categoryid'] = empty($obj['categoryid']) ? 'NULL' : $obj['categoryid'];
                        $obj['categoryname'] = empty($obj['categoryname']) ? '' : $obj['categoryname'];
                        $obj['goodscategoryid'] = empty($obj['goodscategoryid']) ? 'NULL' : $obj['goodscategoryid'];
                        $obj['goodscategoryname'] = empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'];
                        $obj['exciserate'] = empty($obj['exciserate']) ? '' : $obj['exciserate'];
                        $obj['exciserule'] = empty($obj['exciserule']) ? 'NULL' : $obj['exciserule'];
                        $obj['excisetax'] = empty($obj['excisetax']) ? 'NULL' : $obj['excisetax'];
                        $obj['pack'] = empty($obj['pack']) ? 'NULL' : $obj['pack'];
                        $obj['stick'] = empty($obj['stick']) ? 'NULL' : $obj['stick'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? 'NULL' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['exciseratename'] = empty($obj['exciseratename']) ? '' : $obj['exciseratename'];
                        $obj['ordernumber'] = empty($obj['ordernumber']) ? $i : $obj['ordernumber'];
                        $obj['vatProjectId'] = empty($obj['vatProjectId']) ? '' : $obj['vatProjectId'];
                        $obj['vatProjectName'] = empty($obj['vatProjectName']) ? '' : $obj['vatProjectName'];

                        $sql = 'INSERT INTO tblgooddetails (
                                    groupid,
                                    item,
                                    itemcode,
                                    qty,
                                    unitofmeasure,
                                    unitprice,
                                    total,
                                    taxid,
                                    taxrate,
                                    tax,
                                    discounttotal,
                                    discounttaxrate,
                                    discountpercentage,
                                    ordernumber,
                                    discountflag,
                                    deemedflag,
                                    exciseflag,
                                    categoryid,
                                    categoryname,
                                    goodscategoryid,
                                    goodscategoryname,
                                    exciserate,
                                    exciserule,
                                    excisetax,
                                    pack,
                                    stick,
                                    exciseunit,
                                    excisecurrency,
                                    exciseratename,
                                    taxcategory,
                                    unitofmeasurename,
                                    projectId,
                                    projectName,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $gooddetailgroupid . ', "'
                                . addslashes($obj['item']) . '", "'
                                    . addslashes($obj['itemcode']) . '", '
                                        . $obj['qty'] . ', "'
                                            . $obj['unitofmeasure'] . '", '
                                                . $obj['unitprice'] . ', '
                                                    . $obj['total'] . ', '
                                                        . $obj['taxid'] . ', '
                                                            . $obj['taxrate'] . ', '
                                                                . $obj['tax'] . ', '
                                                                    . $obj['discounttotal'] . ', '
                                                                        . $obj['discounttaxrate'] . ', '
                                                                            . $obj['discountpercentage'] . ', '
                                                                                . $obj['ordernumber'] . ', '
                                                                                    . $obj['discountflag'] . ', '
                                                                                        . $obj['deemedflag'] . ', '
                                                                                            . $obj['exciseflag'] . ', '
                                                                                                . $obj['categoryid'] . ', "'
                                                                                                    . addslashes($obj['categoryname']) . '", '
                                                                                                        . $obj['goodscategoryid'] . ', "'
                                                                                                            . addslashes($obj['goodscategoryname']) . '", "'
                                                                                                                . $obj['exciserate'] . '", '
                                                                                                                    . $obj['exciserule'] . ', '
                                                                                                                        . $obj['excisetax'] . ', '
                                                                                                                            . $obj['pack'] . ', '
                                                                                                                                . $obj['stick'] . ', '
                                                                                                                                    . $obj['exciseunit'] . ', "'
                                                                                                                                        . $obj['excisecurrency'] . '", "'
                                                                                                                                            . $obj['exciseratename'] . '", "'
                                                                                                                                                . $obj['taxdisplaycategory'] . '", "'
                                                                                                                                                    . $obj['unitofmeasurename'] . '", "'
                                                                                                                                                    . $obj['vatProjectId'] . '", "'
                                                                                                                                                        . $obj['vatProjectName'] . '", "'
                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                            . $userid . ', "'
                                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : createcreditnote() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));

                        $i = $i + 1;
                    }

                } catch (Exception $e) {
                    $this->logger->write("Utilities : createcreditnote() : Failed to select and insert into table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createcreditnote() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;

                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $paymentdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblcreditnotes SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createcreditnote() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createcreditnote() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;

                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $taxdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblcreditnotes SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    //Get details of goods inserted
                    $t_goods = $this->db->exec(array('SELECT * FROM tblgooddetails g WHERE g.groupid = ' . $gooddetailgroupid . ' ORDER BY id ASC'));

                    //Insert Taxes
                    $j = 0;
                    foreach ($taxes as $obj) {
                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : createcreditnote() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : createcreditnote() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : createcreditnote() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }

                        if (strtoupper(trim($obj['taxcategory'])) == 'D: DEEMED (18%)') {
                            $obj['netamount'] = floor(($obj['netamount'] + $obj['d_netamount']) * 100) / 100;
                            $obj['taxamount'] = floor(($obj['taxamount'] + $obj['d_taxamount']) * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 4, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 4, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 4, '.', '');
                        } else {
                            $obj['netamount'] = round(($obj['netamount'] + $obj['d_netamount']), 4);
                            $obj['taxamount'] = round(($obj['taxamount'] + $obj['d_taxamount']), 4);
                            //$obj['grossamount'] = round($obj['grossamount'], 4);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];
                        }

                        $obj['taxcategory'] = empty($obj['taxcategory']) ? '' : $obj['taxcategory'];
                        $obj['netamount'] = empty($obj['netamount']) ? 'NULL' : $obj['netamount'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? '' : $obj['taxrate'];
                        $obj['taxamount'] = empty($obj['taxamount']) ? '0.00' : $obj['taxamount'];
                        $obj['grossamount'] = empty($obj['grossamount']) ? 'NULL' : $obj['grossamount'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? '' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['taxratename'] = empty($obj['taxratename']) ? '' : $obj['taxratename'];

                        //$obj['goodid'] = empty($obj['goodid'])? 'NULL' : $obj['goodid'];
                        $obj['goodid'] = $t_goods[$j]['id'];

                        $sql = 'INSERT INTO tbltaxdetails (
                                    groupid,
                                    goodid,
                                    taxcategory,
                                    netamount,
                                    taxrate,
                                    taxamount,
                                    grossamount,
                                    exciseunit,
                                    excisecurrency,
                                    taxratename,
                                    taxdescription,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $taxdetailgroupid . ', '
                                . $obj['goodid'] . ', "'
                                    . addslashes($obj['taxcategory']) . '", '
                                        . $obj['netamount'] . ', '
                                            . $obj['taxrate'] . ', '
                                                . $obj['taxamount'] . ', '
                                                    . $obj['grossamount'] . ', "'
                                                        . $obj['exciseunit'] . '", "'
                                                            . $obj['excisecurrency'] . '", "'
                                                                . $obj['taxratename'] . '", "'
                                                                    . $obj['taxdescription'] . '", "'
                                                                        . date('Y-m-d H:i:s') . '", '
                                                                            . $userid . ', "'
                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : createcreditnote() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));
                        $j = $j + 1;

                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createcreditnote() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createcreditnote() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
            }

            if (trim($buyer['referenceno']) !== '' || !empty(trim($buyer['referenceno']))) {
                try {

                    $sql = 'INSERT INTO tblbuyers (
                                    tin,
                                    ninbrn,
                                    PassportNum,
                                    legalname,
                                    businessname,
                                    address,
                                    mobilephone,
                                    linephone,
                                    emailaddress,
                                    placeofbusiness,
                                    type,
                                    citizineship,
                                    sector,
                                    referenceno,
                                    datasource,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                        . addslashes($buyer['tin']) . '", "'
                            . addslashes($buyer['ninbrn']) . '", "'
                                . addslashes($buyer['PassportNum']) . '", "'
                                    . addslashes($buyer['legalname']) . '", "'
                                        . addslashes($buyer['businessname']) . '", "'
                                            . addslashes($buyer['address']) . '", "'
                                                . addslashes($buyer['mobilephone']) . '", "'
                                                    . addslashes($buyer['linephone']) . '", "'
                                                        . addslashes($buyer['emailaddress']) . '", "'
                                                            . addslashes($buyer['placeofbusiness']) . '", "'
                                                                . $buyer['type'] . '", "'
                                                                    . addslashes($buyer['citizineship']) . '", "'
                                                                        . addslashes($buyer['sector']) . '", "'
                                                                            . addslashes($buyer['referenceno']) . '", "'
                                                                                . $buyer['datasource'] . '", "'
                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                        . $userid . ', "'
                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                . $userid . ')';

                    $this->logger->write("Utilities : createcreditnote() : The SQL is " . $sql, 'r');
                    $this->db->exec(array($sql));

                    try {
                        $by = array();
                        $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE referenceno = "' . $buyer['referenceno'] . '" AND insertedby = ' . $userid));

                        foreach ($r as $obj) {
                            $by [] = $obj;
                        }

                        $buyerid = $by[0]['id'];
                        $this->db->exec(array('UPDATE tblcreditnotes SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                    } catch (Exception $e) {
                        $this->logger->write("Utilities : createcreditnote() : Failed to select and update table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createcreditnote() : The operation to create a buyer was not successful. The error message is " . $e->getMessage(), 'r');
                }
            }


            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : createcreditnote() : The operation to create the credit note was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }
    }

    /**
     * @name getcurrency
     * @desc return the standard name for a currency
     * @return string
     * @param $no string
     *
     */
    public function getcurrency($code)
    {
        /**
         * 1. Cleanup the currency
         * 2. Search the currency table for the equivalent
         */
        $this->logger->write("Utilities : getcurrency() : The raw currency is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblcurrencies');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : getcurrency() : The currency does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->name;
        }

        $this->logger->write("Utilities : getcurrency() : The currency code is " . $value, 'r');
        return $value;
    }

    /**
     * @name createcustomer
     * @desc create a customer
     * @return bool
     * @param $customer array
     *
     */
    public function createcustomer($customer, $userid)
    {

        if (!isset($customer)) {
            $this->logger->write("Utilities : createcustomer() : The customer object is not set", 'r');
            return false;
        }

        if (!isset($customer['legalname'])) {
            $this->logger->write("Utilities : createcustomer() : The customer name is not set", 'r');
            return false;
        } else {
            if ($customer['legalname'] == null) {
                $this->logger->write("Utilities : createcustomer() : The customer name is not set", 'r');
                return false;
            }
        }

        try {
            $customer['erpcustomerid'] = (trim($customer['erpcustomerid']) == '' ? '' : $customer['erpcustomerid']);
            $customer['erpcustomercode'] = (trim($customer['erpcustomercode']) == '' ? '' : $customer['erpcustomercode']);
            $customer['tin'] = (trim($customer['tin']) == '' ? '' : $customer['tin']);
            $customer['ninbrn'] = (trim($customer['ninbrn']) == '' ? '' : $customer['ninbrn']);
            $customer['PassportNum'] = (trim($customer['PassportNum']) == '' ? '' : $customer['PassportNum']);
            $customer['legalname'] = (trim($customer['legalname']) == '' ? '' : $customer['legalname']);
            $customer['address'] = (trim($customer['address']) == '' ? '' : $customer['address']);
            $customer['mobilephone'] = (trim($customer['mobilephone']) == '' ? '' : $customer['mobilephone']);
            $customer['linephone'] = (trim($customer['linephone']) == '' ? '' : $customer['linephone']);

            $customer['emailaddress'] = (trim($customer['emailaddress']) == '' ? '' : $customer['emailaddress']);
            $customer['placeofbusiness'] = (trim($customer['placeofbusiness']) == '' ? '' : $customer['placeofbusiness']);
            $customer['type'] = (trim($customer['type']) == '' ? 'NULL' : $customer['type']);
            $customer['citizineship'] = (trim($customer['citizineship']) == '' ? '' : $customer['citizineship']);
            $customer['countryCode'] = (trim($customer['countryCode']) == '' ? '' : $customer['countryCode']);
            $customer['sector'] = (trim($customer['sector']) == '' ? '' : $customer['sector']);
            $customer['sectorCode'] = (trim($customer['sectorCode']) == '' ? '' : $customer['sectorCode']);
            $customer['datasource'] = (trim($customer['datasource']) == '' ? '' : $customer['datasource']);
            $customer['status'] = (trim($customer['status']) == '' ? 'NULL' : $customer['status']);

            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;


            $sql = 'INSERT INTO tblcustomers (
                                    erpcustomerid,
                                    erpcustomercode,
                                    tin,
                                    ninbrn,
                                    PassportNum,
                                    legalname,
                                    businessname,
                                    address,
                                    mobilephone,
                                    linephone,
                                    emailaddress,
                                    placeofbusiness,
                                    type,
                                    citizineship,
                                    countryCode,
                                    sector,
                                    sectorCode,
                                    datasource,
                                    status,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($customer['erpcustomerid']) . '", "'
                    . addslashes($customer['erpcustomercode']) . '", "'
                        . addslashes($customer['tin']) . '", "'
                            . addslashes($customer['ninbrn']) . '", "'
                                . addslashes($customer['PassportNum']) . '", "'
                                    . addslashes($customer['legalname']) . '", "'
                                        . addslashes($customer['businessname']) . '", "'
                                            . addslashes($customer['address']) . '", "'
                                                . addslashes($customer['mobilephone']) . '", "'
                                                    . addslashes($customer['linephone']) . '", "'
                                                        . addslashes($customer['emailaddress']) . '", "'
                                                            . addslashes($customer['placeofbusiness']) . '", '
                                                                . $customer['type'] . ', "'
                                                                    . addslashes($customer['citizineship']) . '", "'
                                                                        . addslashes($customer['countryCode']) . '", "'
                                                                            . addslashes($customer['sector']) . '", "'
                                                                                . addslashes($customer['sectorCode']) . '", "'
                                                                                    . addslashes($customer['datasource']) . '", '
                                                                                        . $customer['status'] . ', "'
                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                . $userid . ', "'
                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                        . $userid . ')';

            $this->logger->write("Utilities : createcustomer() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));

            return true;

        } catch (Exception $e) {
            $this->logger->write("Utilities : createcustomer() : The operation to create the customer was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }

    }

    /**
     * @name updatecustomer
     * @desc update a customer
     * @return bool
     * @param $customer array
     *
     */
    public function updatecustomer($customer, $userid)
    {
        if (!isset($customer)) {
            $this->logger->write("Utilities : updatecustomer() : The customer object is not set", 'r');
            return false;
        }

        if (!isset($customer['legalname'])) {
            $this->logger->write("Utilities : updatecustomer() : The customer name is not set", 'r');
            return false;
        } else {
            if ($customer['legalname'] == null) {
                $this->logger->write("Utilities : updatecustomer() : The customer name is not set", 'r');
                return false;
            }
        }

        if (!isset($customer['id'])) {
            $this->logger->write("Utilities : updatecustomer() : The customer id is not set", 'r');
            return false;
        } else {
            if ($customer['id'] == null) {
                $this->logger->write("Utilities : updatecustomer() : The customer id is not set", 'r');
                return false;
            }
        }

        $cust = new customers($this->db);
        $cust->getByID($customer['id']);

        try {
            $customer['erpcustomerid'] = (trim($customer['erpcustomerid']) == '' ? $cust->erpcustomerid : $customer['erpcustomerid']);
            $customer['erpcustomercode'] = (trim($customer['erpcustomercode']) == '' ? $cust->erpcustomercode : $customer['erpcustomercode']);
            $customer['tin'] = (trim($customer['tin']) == '' ? $cust->tin : $customer['tin']);
            $customer['ninbrn'] = (trim($customer['ninbrn']) == '' ? $cust->ninbrn : $customer['ninbrn']);
            $customer['PassportNum'] = (trim($customer['PassportNum']) == '' ? $cust->PassportNum : $customer['PassportNum']);
            $customer['legalname'] = (trim($customer['legalname']) == '' ? $cust->legalname : $customer['legalname']);
            $customer['address'] = (trim($customer['address']) == '' ? $cust->address : $customer['address']);
            $customer['mobilephone'] = (trim($customer['mobilephone']) == '' ? $cust->mobilephone : $customer['mobilephone']);
            $customer['linephone'] = (trim($customer['linephone']) == '' ? $cust->linephone : $customer['linephone']);

            $customer['emailaddress'] = (trim($customer['emailaddress']) == '' ? $cust->emailaddress : $customer['emailaddress']);
            $customer['placeofbusiness'] = (trim($customer['placeofbusiness']) == '' ? $cust->placeofbusiness : $customer['placeofbusiness']);

            $customer['type'] = (trim($customer['type']) == '' ? $cust->type : $customer['type']);
            $customer['type'] = (trim($customer['type']) == '' ? 'NULL' : $customer['type']);

            $customer['citizineship'] = (trim($customer['citizineship']) == '' ? $cust->citizineship : $customer['citizineship']);
            $customer['countryCode'] = (trim($customer['countryCode']) == '' ? $cust->countryCode : $customer['countryCode']);
            $customer['sector'] = (trim($customer['sector']) == '' ? $cust->sector : $customer['sector']);
            $customer['sectorCode'] = (trim($customer['sectorCode']) == '' ? $cust->sectorCode : $customer['sectorCode']);
            $customer['datasource'] = (trim($customer['datasource']) == '' ? $cust->datasource : $customer['datasource']);

            $customer['status'] = (trim($customer['status']) == '' ? $cust->status : $customer['status']);
            $customer['status'] = (trim($customer['status']) == '' ? 'NULL' : $customer['status']);

            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;

            $sql = 'UPDATE tblcustomers SET
                                    erpcustomerid = "' . addslashes($customer['erpcustomerid']) . '",
                                    erpcustomercode = "' . addslashes($customer['erpcustomercode']) . '",
                                    tin = "' . addslashes($customer['tin']) . '",
                                    ninbrn = "' . addslashes($customer['ninbrn']) . '",
                                    PassportNum = "' . addslashes($customer['PassportNum']) . '",
                                    legalname = "' . addslashes($customer['legalname']) . '",
                                    businessname = "' . addslashes($customer['businessname']) . '",
                                    address = "' . addslashes($customer['address']) . '",
                                    mobilephone = "' . addslashes($customer['mobilephone']) . '",
                                    linephone = "' . addslashes($customer['linephone']) . '",
                                    emailaddress = "' . addslashes($customer['emailaddress']) . '",
                                    placeofbusiness = "' . addslashes($customer['placeofbusiness']) . '",
                                    type = ' . $customer['type'] . ',
                                    citizineship = "' . addslashes($customer['citizineship']) . '",
                                    countryCode = "' . addslashes($customer['countryCode']) . '",
                                    sector = "' . addslashes($customer['sector']) . '",
                                    sectorCode = "' . addslashes($customer['sectorCode']) . '",
                                    datasource = "' . addslashes($customer['datasource']) . '",
                                    status = ' . $customer['status'] . ',
                                    modifieddt = "' .  date('Y-m-d H:i:s') . '",
                                    modifiedby = ' . $userid  . '
                                    WHERE id = ' . $customer['id'];

            $this->logger->write("Utilities : updatecustomer() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : updatecustomer() : The operation to update the customer was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }

    }

    /**
     * @name createsupplier
     * @desc create a supplier
     * @return bool
     * @param $supplier array
     *
     */
    public function createsupplier($supplier, $userid)
    {

        if (!isset($supplier)) {
            $this->logger->write("Utilities : createsupplier() : The supplier object is not set", 'r');
            return false;
        }

        if (!isset($supplier['legalname'])) {
            $this->logger->write("Utilities : createsupplier() : The supplier name is not set", 'r');
            return false;
        } else {
            if ($supplier['legalname'] == null) {
                $this->logger->write("Utilities : createsupplier() : The supplier name is not set", 'r');
                return false;
            }
        }

        try {
            $supplier['erpsupplierid'] = (trim($supplier['erpsupplierid']) == '' ? '' : $supplier['erpsupplierid']);
            $supplier['erpsuppliercode'] = (trim($supplier['erpsuppliercode']) == '' ? '' : $supplier['erpsuppliercode']);
            $supplier['tin'] = (trim($supplier['tin']) == '' ? '' : $supplier['tin']);
            $supplier['ninbrn'] = (trim($supplier['ninbrn']) == '' ? '' : $supplier['ninbrn']);
            $supplier['PassportNum'] = (trim($supplier['PassportNum']) == '' ? '' : $supplier['PassportNum']);
            $supplier['legalname'] = (trim($supplier['legalname']) == '' ? '' : $supplier['legalname']);
            $supplier['address'] = (trim($supplier['address']) == '' ? '' : $supplier['address']);
            $supplier['mobilephone'] = (trim($supplier['mobilephone']) == '' ? '' : $supplier['mobilephone']);
            $supplier['linephone'] = (trim($supplier['linephone']) == '' ? '' : $supplier['linephone']);

            $supplier['emailaddress'] = (trim($supplier['emailaddress']) == '' ? '' : $supplier['emailaddress']);
            $supplier['placeofbusiness'] = (trim($supplier['placeofbusiness']) == '' ? '' : $supplier['placeofbusiness']);
            $supplier['type'] = (trim($supplier['type']) == '' ? 'NULL' : $supplier['type']);
            $supplier['citizineship'] = (trim($supplier['citizineship']) == '' ? '' : $supplier['citizineship']);
            $supplier['countryCode'] = (trim($supplier['countryCode']) == '' ? '' : $supplier['countryCode']);
            $supplier['sector'] = (trim($supplier['sector']) == '' ? '' : $supplier['sector']);
            $supplier['sectorCode'] = (trim($supplier['sectorCode']) == '' ? '' : $supplier['sectorCode']);
            $supplier['datasource'] = (trim($supplier['datasource']) == '' ? '' : $supplier['datasource']);
            $supplier['status'] = (trim($supplier['status']) == '' ? 'NULL' : $supplier['status']);

            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;


            $sql = 'INSERT INTO tblsuppliers (
                                    erpsupplierid,
                                    erpsuppliercode,
                                    tin,
                                    ninbrn,
                                    PassportNum,
                                    legalname,
                                    businessname,
                                    address,
                                    mobilephone,
                                    linephone,
                                    emailaddress,
                                    placeofbusiness,
                                    type,
                                    citizineship,
                                    countryCode,
                                    sector,
                                    sectorCode,
                                    datasource,
                                    status,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($supplier['erpsupplierid']) . '", "'
                    . addslashes($supplier['erpsuppliercode']) . '", "'
                        . addslashes($supplier['tin']) . '", "'
                            . addslashes($supplier['ninbrn']) . '", "'
                                . addslashes($supplier['PassportNum']) . '", "'
                                    . addslashes($supplier['legalname']) . '", "'
                                        . addslashes($supplier['businessname']) . '", "'
                                            . addslashes($supplier['address']) . '", "'
                                                . addslashes($supplier['mobilephone']) . '", "'
                                                    . addslashes($supplier['linephone']) . '", "'
                                                        . addslashes($supplier['emailaddress']) . '", "'
                                                            . addslashes($supplier['placeofbusiness']) . '", '
                                                                . $supplier['type'] . ', "'
                                                                    . addslashes($supplier['citizineship']) . '", "'
                                                                        . addslashes($supplier['countryCode']) . '", "'
                                                                            . addslashes($supplier['sector']) . '", "'
                                                                                . addslashes($supplier['sectorCode']) . '", "'
                                                                                    . addslashes($supplier['datasource']) . '", '
                                                                                        . $supplier['status'] . ', "'
                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                . $userid . ', "'
                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                        . $userid . ')';

            $this->logger->write("Utilities : createsupplier() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));

            return true;

        } catch (Exception $e) {
            $this->logger->write("Utilities : createsupplier() : The operation to create the supplier was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }

    }

    /**
     * @name updatesupplier
     * @desc update a supplier
     * @return bool
     * @param $supplier array
     *
     */
    public function updatesupplier($supplier, $userid)
    {
        if (!isset($supplier)) {
            $this->logger->write("Utilities : updatesupplier() : The supplier object is not set", 'r');
            return false;
        }

        if (!isset($supplier['legalname'])) {
            $this->logger->write("Utilities : updatesupplier() : The supplier name is not set", 'r');
            return false;
        } else {
            if ($supplier['legalname'] == null) {
                $this->logger->write("Utilities : updatesupplier() : The supplier name is not set", 'r');
                return false;
            }
        }

        if (!isset($supplier['id'])) {
            $this->logger->write("Utilities : updatesupplier() : The supplier id is not set", 'r');
            return false;
        } else {
            if ($supplier['id'] == null) {
                $this->logger->write("Utilities : updatesupplier() : The supplier id is not set", 'r');
                return false;
            }
        }

        $cust = new suppliers($this->db);
        $cust->getByID($supplier['id']);

        try {
            $supplier['erpsupplierid'] = (trim($supplier['erpsupplierid']) == '' ? $cust->erpsupplierid : $supplier['erpsupplierid']);
            $supplier['erpsuppliercode'] = (trim($supplier['erpsuppliercode']) == '' ? $cust->erpsuppliercode : $supplier['erpsuppliercode']);
            $supplier['tin'] = (trim($supplier['tin']) == '' ? $cust->tin : $supplier['tin']);
            $supplier['ninbrn'] = (trim($supplier['ninbrn']) == '' ? $cust->ninbrn : $supplier['ninbrn']);
            $supplier['PassportNum'] = (trim($supplier['PassportNum']) == '' ? $cust->PassportNum : $supplier['PassportNum']);
            $supplier['legalname'] = (trim($supplier['legalname']) == '' ? $cust->legalname : $supplier['legalname']);
            $supplier['address'] = (trim($supplier['address']) == '' ? $cust->address : $supplier['address']);
            $supplier['mobilephone'] = (trim($supplier['mobilephone']) == '' ? $cust->mobilephone : $supplier['mobilephone']);
            $supplier['linephone'] = (trim($supplier['linephone']) == '' ? $cust->linephone : $supplier['linephone']);

            $supplier['emailaddress'] = (trim($supplier['emailaddress']) == '' ? $cust->emailaddress : $supplier['emailaddress']);
            $supplier['placeofbusiness'] = (trim($supplier['placeofbusiness']) == '' ? $cust->placeofbusiness : $supplier['placeofbusiness']);

            $supplier['type'] = (trim($supplier['type']) == '' ? $cust->type : $supplier['type']);
            $supplier['type'] = (trim($supplier['type']) == '' ? 'NULL' : $supplier['type']);

            $supplier['citizineship'] = (trim($supplier['citizineship']) == '' ? $cust->citizineship : $supplier['citizineship']);
            $supplier['countryCode'] = (trim($supplier['countryCode']) == '' ? $cust->countryCode : $supplier['countryCode']);
            $supplier['sector'] = (trim($supplier['sector']) == '' ? $cust->sector : $supplier['sector']);
            $supplier['sectorCode'] = (trim($supplier['sectorCode']) == '' ? $cust->sectorCode : $supplier['sectorCode']);
            $supplier['datasource'] = (trim($supplier['datasource']) == '' ? $cust->datasource : $supplier['datasource']);

            $supplier['status'] = (trim($supplier['status']) == '' ? $cust->status : $supplier['status']);
            $supplier['status'] = (trim($supplier['status']) == '' ? 'NULL' : $supplier['status']);

            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;

            $sql = 'UPDATE tblsuppliers SET
                                    erpsupplierid = "' . addslashes($supplier['erpsupplierid']) . '",
                                    erpsuppliercode = "' . addslashes($supplier['erpsuppliercode']) . '",
                                    tin = "' . addslashes($supplier['tin']) . '",
                                    ninbrn = "' . addslashes($supplier['ninbrn']) . '",
                                    PassportNum = "' . addslashes($supplier['PassportNum']) . '",
                                    legalname = "' . addslashes($supplier['legalname']) . '",
                                    businessname = "' . addslashes($supplier['businessname']) . '",
                                    address = "' . addslashes($supplier['address']) . '",
                                    mobilephone = "' . addslashes($supplier['mobilephone']) . '",
                                    linephone = "' . addslashes($supplier['linephone']) . '",
                                    emailaddress = "' . addslashes($supplier['emailaddress']) . '",
                                    placeofbusiness = "' . addslashes($supplier['placeofbusiness']) . '",
                                    type = ' . $supplier['type'] . ',
                                    citizineship = "' . addslashes($supplier['citizineship']) . '",
                                    countryCode = "' . addslashes($supplier['countryCode']) . '",
                                    sector = "' . addslashes($supplier['sector']) . '",
                                    sectorCode = "' . addslashes($supplier['sectorCode']) . '",
                                    datasource = "' . addslashes($supplier['datasource']) . '",
                                    status = ' . $supplier['status'] . ',
                                    modifieddt = "' .  date('Y-m-d H:i:s') . '",
                                    modifiedby = ' . $userid  . '
                                    WHERE id = ' . $supplier['id'];

            $this->logger->write("Utilities : updatesupplier() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : updatesupplier() : The operation to update the supplier was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }

    }

    /**
     * @name fetchcurrencyrates
     * @desc Fetch currency rates from EFRIS
     * @return JSON-encoded object
     * @param $userid int
     *
     */
    public function fetchcurrencyrates($userid)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : fetchcurrencyrates() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : fetchcurrencyrates() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : fetchcurrencyrates() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : fetchcurrencyrates() : The current timezone is " . date_default_timezone_get(), 'r');

        try {
            $this->logger->write("Utilities : fetchcurrencyrates() : Fetching currency rates started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T126';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $data = array(
                'data' => array(
                    'content' => '',
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);
            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : uploadproduct() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : fetchcurrencyrates() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
            } else {
                $this->logger->write("Utilities : fetchcurrencyrates() : The API call was not successful. The return code is: " . $returninfo['returnCode'], 'r');
            }

            if ($dataDesc['zipCode'] == '1') {
                $this->logger->write("Utilities : fetchcurrencyrates() : The response is zipped", 'r');
                return gzdecode(base64_decode($content));
            } else {
                $this->logger->write("Utilities : fetchcurrencyrates() : The response is NOT zipped", 'r');
                return base64_decode($content);
            }
        } catch (Exception $e) {
            $this->logger->write("Utilities : fetchcurrencyrates() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    /**
     * @name uploadproduct
     * @desc Upload a product to EFRIS
     * @return JSON-encoded object
     * @param $userid int, $productid int
     *
     */
    public function uploadproduct($userid, $product, $altunits = array())
    {
        // 2026-04-11 21:31:14 +03:00 - Backward compatibility: support legacy invocation uploadproduct($userid, $productId).
        if (!is_array($product)) {
            $id = (int)$product;
            $pdct = new products($this->db);
            $pdct->getByID($id);

            if ($pdct->dry()) {
                $this->logger->write("Utilities : uploadproduct() : Product id " . $id . " was not found", 'r');
                return json_encode(array('returnCode' => '658', 'returnMessage' => 'goodsCode does not exist!'));
            }

            if (!is_array($altunits) || empty($altunits)) {
                $altunits = array();
                try {
                    $temp = $this->db->exec(array('SELECT * FROM tblotherunits o WHERE o.productid = ' . $id));
                    foreach ($temp as $obj) {
                        $altunits[] = array(
                            'otherunit' => empty($obj['otherunit']) ? '' : $obj['otherunit'],
                            'otherPrice' => empty($obj['otherPrice']) ? '' : $obj['otherPrice'],
                            'otherscaled' => empty($obj['otherscaled']) ? '' : $obj['otherscaled'],
                            'packagescaled' => empty($obj['packagescaled']) ? '' : $obj['packagescaled']
                        );
                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : uploadproduct() : Failed to retrieve other units. " . $e->getMessage(), 'r');
                }
            }

            $product = array(
                'name' => empty($pdct->name) ? '' : $pdct->name,
                'code' => empty($pdct->code) ? '' : $pdct->code,
                'measureunit' => empty($pdct->measureunit) ? '' : $pdct->measureunit,
                'unitprice' => empty($pdct->unitprice) ? '' : $pdct->unitprice,
                'currency' => empty($pdct->currency) ? '' : strval($pdct->currency),
                'commoditycategorycode' => empty($pdct->commoditycategorycode) ? '' : $pdct->commoditycategorycode,
                'hasexcisetax' => empty($pdct->hasexcisetax) ? '' : strval($pdct->hasexcisetax),
                'description' => empty($pdct->description) ? '' : $pdct->description,
                'stockprewarning' => empty($pdct->stockprewarning) ? '0' : $pdct->stockprewarning,
                'piecemeasureunit' => empty($pdct->piecemeasureunit) ? '' : $pdct->piecemeasureunit,
                'havepieceunit' => empty($pdct->havepieceunit) ? '' : strval($pdct->havepieceunit),
                'pieceunitprice' => empty($pdct->pieceunitprice) ? '' : $pdct->pieceunitprice,
                'packagescaledvalue' => empty($pdct->packagescaledvalue) ? '' : $pdct->packagescaledvalue,
                'piecescaledvalue' => empty($pdct->piecescaledvalue) ? '' : $pdct->piecescaledvalue,
                // 2026-04-12 12:24:00 +03:00 - Requirement: always emit canonical `exciseDutyCode`.
                // For historical rows, allow read fallback from legacy `excisedutylist` only.
                'exciseDutyCode' => (empty($pdct->hasexcisetax) || strval($pdct->hasexcisetax) === '102')
                    ? ''
                    : (!empty($pdct->exciseDutyCode) ? $pdct->exciseDutyCode : $pdct->excisedutylist),
                'haveotherunit' => empty($pdct->haveotherunit) ? '' : strval($pdct->haveotherunit),
                'goodsTypeCode' => empty($pdct->goodsTypeCode) ? '101' : $pdct->goodsTypeCode,
                'customsmeasureunit' => empty($pdct->customsmeasureunit) ? '' : $pdct->customsmeasureunit,
                'customsunitprice' => empty($pdct->customsunitprice) ? '' : $pdct->customsunitprice,
                'packagescaledvaluecustoms' => empty($pdct->packagescaledvaluecustoms) ? '' : $pdct->packagescaledvaluecustoms,
                'customsscaledvalue' => empty($pdct->customsscaledvalue) ? '' : $pdct->customsscaledvalue
            );
        }

        if (!is_array($altunits)) {
            $altunits = array();
        }

        // 2026-04-12 12:24:00 +03:00 - Requirement: canonicalize payload input to `exciseDutyCode`.
        // Accept legacy `excisedutylist` as read-compatibility fallback only.
        // New writes must continue through `exciseDutyCode`.
        if (is_array($product)) {
            $hasExciseTax = isset($product['hasexcisetax']) ? trim((string)$product['hasexcisetax']) : '';
            $exciseDutyCode = isset($product['exciseDutyCode']) ? trim((string)$product['exciseDutyCode']) : '';
            $exciseDutyList = isset($product['excisedutylist']) ? trim((string)$product['excisedutylist']) : '';

            if ($exciseDutyCode === '' && $exciseDutyList !== '') {
                $exciseDutyCode = $exciseDutyList;
            }

            if ($hasExciseTax === '102') {
                $exciseDutyCode = '';
            }

            $product['exciseDutyCode'] = $exciseDutyCode;
        }

        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : uploadproduct() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : uploadproduct() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : uploadproduct() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : uploadproduct() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : uploadproduct() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : uploadproduct() : The product code is " . (isset($product['code']) ? $product['code'] : ''), 'r');

        try {
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T130';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            //Check if the product is already uploaded
            $pdct = new products($this->db);
            $pdct->getByErpCode(trim($product['code']));

            if ($pdct->dry()) {
                $this->logger->write("Utilities : uploadproduct() : The product " . $product['code'] . " is new. Uploading started", 'r');
                $operationType = '101'; /*add goods(default)*/
            } else {
                $this->logger->write("Utilities : uploadproduct() : The product " . $product['code'] . " is already uploaded. Updating started", 'r');
                $operationType = '102'; /*modify product*/
            }

            $otherunits = array();

            try {
                foreach ($altunits as $obj) {
                    $otherunits[] = array(
                        'otherUnit' => empty($obj['otherunit']) ? '' : $obj['otherunit'],
                        'otherPrice' => empty($obj['otherPrice']) ? '' : $obj['otherPrice'],
                        'otherScaled' => empty($obj['otherscaled']) ? '' : $obj['otherscaled'],
                        'packageScaled' => empty($obj['packagescaled']) ? '' : $obj['packagescaled']
                    );
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploadproduct() : The operation to process the alternative units was not successful. The error messages is " . $e->getMessage(), 'r');
            }

            $products = array(
                array(
                    'operationType' => $operationType,
                    'goodsName' => $product['name'],
                    'goodsCode' => $product['code'],
                    'measureUnit' => $product['measureunit'],
                    'unitPrice' => empty($product['unitprice']) ? '1' : $product['unitprice'],
                    'currency' => $product['currency'],
                    'commodityCategoryId' => $product['commoditycategorycode'],
                    'haveExciseTax' => $product['hasexcisetax'],
                    'description' => empty($product['description']) ? '' : $product['description'],
                    'stockPrewarning' => $product['stockprewarning'],
                    'pieceMeasureUnit' => $product['piecemeasureunit'],
                    'havePieceUnit' => $product['havepieceunit'],
                    'pieceUnitPrice' => empty($product['pieceunitprice']) ? '' : $product['pieceunitprice'],
                    'packageScaledValue' => empty($product['packagescaledvalue']) ? '' : $product['packagescaledvalue'],
                    'pieceScaledValue' => empty($product['piecescaledvalue']) ? '' : $product['piecescaledvalue'],
                    'exciseDutyCode' => $product['exciseDutyCode'],
                    'haveOtherUnit' => empty($product['haveotherunit']) ? '' : $product['haveotherunit'],
                    'goodsTypeCode' => empty($product['goodsTypeCode']) ? '101' : $product['goodsTypeCode'],
                    /**
                     * Date: 2025-12-04
                     * Author: Francis Lubanga <frncslubanga@gmail.com>
                     * Desc: Temporarily suspend the sending of the commodityGoodsExtendEntity to EFRIS
                     */
                    /*'commodityGoodsExtendEntity' => array(
                        'customsMeasureUnit' => empty($product['customsmeasureunit'])? '' : $product['customsmeasureunit'],
                        'customsUnitPrice' => empty($product['customsunitprice'])? '' : $product['customsunitprice'],
                        'packageScaledValueCustoms' => empty($product['packagescaledvaluecustoms'])? '' : round($product['packagescaledvaluecustoms']),
                        'customsScaledValue' => empty($product['customsscaledvalue'])? '' : round($product['customsscaledvalue'])
                    ),*/
                    'goodsOtherUnits' => $otherunits
                )
            );

            $products = json_encode($products); //JSON-ifiy
            $products = base64_encode($products); //base64 encode
            $this->logger->write("Utilities : uploadproduct() : The encoded product is " . $products, 'r');

            $data = array(
                'data' => array(
                    'content' => $products,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);


            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : uploadproduct() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : uploadproduct() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
            } else {
                $this->logger->write("Utilities : uploadproduct() : The API call was not successful. The return code is: " . $returninfo['returnCode'], 'r');
            }

            if ($dataDesc['zipCode'] == '1') {
                $this->logger->write("Utilities : uploadproduct() : The response is zipped", 'r');
                return gzdecode(base64_decode($content));
            } else {
                $this->logger->write("Utilities : uploadproduct() : The response is NOT zipped", 'r');
                return base64_decode($content);
            }
        } catch (Exception $e) {
            $this->logger->write("Utilities : uploadproduct() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    /**
     * @name updateproduct
     * @desc Update a product to EFRIS
     * @return JSON-encoded object
     * @param $userid int, $productid int
     *
     */
    public function updateproduct($userid, $id)
    {
        // 2026-04-11 21:31:14 +03:00 - Preserve legacy controller contract: updateproduct($userid, $id) performs EFRIS sync payload submission.
        return $this->uploadproduct($userid, $id);
    }

    /**
     * @name fetchproduct
     * @desc Fetch a product from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $productid int
     *
     */
    public function fetchproduct($userid, $product, $urabranchid = '')
    {
        // 2026-04-11 21:31:14 +03:00 - Backward compatibility: support legacy invocation fetchproduct($userid, $productId).
        if (!is_array($product)) {
            $id = (int)$product;
            $pdct = new products($this->db);
            $pdct->getByID($id);
            $product = array(
                'code' => $pdct->dry() ? '' : $pdct->code
            );
        }

        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : fetchproduct() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : fetchproduct() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : fetchproduct() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : fetchproduct() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : fetchproduct() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : fetchproduct() : Fetching product started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T127';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            //$product = new products($this->db);
            //$product->getByID($id);

            $product = array(
                'goodsName' => '',
                'goodsCode' => $product['code'],
                'commodityCategoryName' => '',
                'pageNo' => '1',
                'pageSize' => '10',
                'branchId' => $urabranchid
            );

            $product = json_encode($product); //JSON-ifiy
            $product = base64_encode($product); //base64 encode
            $this->logger->write("Utilities : fetchproduct() : The encoded product is " . $product, 'r');

            $data = array(
                'data' => array(
                    'content' => $product,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : fetchproduct() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : fetchproduct() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : fetchproduct() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : fetchproduct() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : fetchproduct() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : fetchproduct() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name downloadstockadjustments
     * @desc Download stock adjustments from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $productid int, $productionBatchNo string, $invoiceNo string, $referenceNo string, $pageNo int, $pageSize int
     *
     */
    public function downloadstockadjustments($userid, $id, $productionBatchNo = null, $invoiceNo = null, $referenceNo = null, $pageNo = 1, $pageSize = 90)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : downloadstockadjustments() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloadstockadjustments() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : downloadstockadjustments() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloadstockadjustments() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : downloadstockadjustments() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : downloadstockadjustments() : The product id is " . $id, 'r');

        try {
            $this->logger->write("Utilities : downloadstockadjustments() : Downloading stock adjustments started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T145';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $product = new products($this->db);
            $product->getByID($id);

            $product = array(
                'productionBatchNo' => empty($productionBatchNo) ? '' : $productionBatchNo,
                'invoiceNo' => empty($invoiceNo) ? '' : $invoiceNo,
                'referenceNo' => empty($referenceNo) ? '' : $referenceNo,
                'pageNo' => $pageNo,
                'pageSize' => $pageSize
            );

            $product = json_encode($product); //JSON-ifiy
            $product = base64_encode($product); //base64 encode
            $this->logger->write("Utilities : downloadstockadjustments() : The encoded product is " . $product, 'r');

            $data = array(
                'data' => array(
                    'content' => $product,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : downloadstockadjustments() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);
            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : downloadstockadjustments() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : downloadstockadjustments() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : downloadstockadjustments() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : downloadstockadjustments() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : downloadstockadjustments() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name queryproduct
     * @desc Fetch a product from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $productid int
     *
     */
    public function queryproduct($userid, $id)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : queryproduct() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : queryproduct() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : queryproduct() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : queryproduct() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : queryproduct() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : queryproduct() : The product id is " . $id, 'r');

        try {
            $this->logger->write("Utilities : queryproduct() : Uploading product started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T128';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $user = new users($this->db);
            $user->getByID($userid);
            $branch = new branches($this->db);
            $branch->getByID($user->branch);

            $product = new products($this->db);
            $product->getByID($id);

            $product = array(
                'id' => $product->uraproductidentifier,
                'branchId' => empty($branch->uraid) ? $devicedetails->branchId : $branch->uraid,
            );
            $product = json_encode($product); //JSON-ifiy
            $product = base64_encode($product); //base64 encode
            $this->logger->write("Utilities : queryproduct() : The encoded product is " . $product, 'r');

            $data = array(
                'data' => array(
                    'content' => $product,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : queryproduct() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);
            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : queryproduct() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : queryproduct() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : queryproduct() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : queryproduct() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : queryproduct() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    /**
     * @name transferproductstock
     * @desc Transfer stock of a product from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $productid int, $sourcebranch string, $destinationbranch string, $qty float, $remarks string
     *
     */
    public function transferproductstock($userid, $product, $sourcebranch, $destinationbranch, $qty = 0, $remarks = '')
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : transferproductstock() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : transferproductstock() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : transferproductstock() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : transferproductstock() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : transferproductstock() : The user id is " . $userid, 'r');

        if (trim($sourcebranch) == '' || empty($sourcebranch)) {
            $this->logger->write("Utilities : transferproductstock() : The source branch is empty and/or NOT mapped.", 'r');
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'The source branch is empty and/or NOT mapped'));
        }

        if (trim($destinationbranch) == '' || empty($destinationbranch)) {
            $this->logger->write("Utilities : transferproductstock() : The destination branch is empty and/or NOT mapped.", 'r');
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'The destination branch is empty and/or NOT mapped'));
        }

        try {
            $this->logger->write("Utilities : transferproductstock() : Transfer of product stock started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T139';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $user = new users($this->db);
            $user->getByID($userid);
            $branch = new branches($this->db);
            $branch->getByID($user->branch);

            $productId = $product['uraproductidentifier'];
            $productCode = $product['code'];

            $transferdetails[] = array(
                'commodityGoodsId' => $productId,
                'goodsCode' => $productCode,
                'measureUnit' => $product['measureunit'],
                'quantity' => strval($qty),
                'remarks' => $remarks
            );

            $product = array(
                'goodsStockTransfer' => array(
                    'sourceBranchId' => $sourcebranch,
                    'destinationBranchId' => $destinationbranch,
                    'transferTypeCode' => '101',
                    'remarks' => '',
                ),
                'goodsStockTransferItem' => $transferdetails
            );

            $product = json_encode($product); //JSON-ifiy
            $product = base64_encode($product); //base64 encode
            $this->logger->write("Utilities : transferproductstock() : The encoded product is " . $product, 'r');

            $data = array(
                'data' => array(
                    'content' => $product,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : transferproductstock() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);
            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];


            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : transferproductstock() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : transferproductstock() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : transferproductstock() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }

                //self::logstocktransfer($userid, $productCode, $qty, NULL, NULL, NULL, NULL, $remarks, $sourcebranch, $destinationbranch, '101', $productId);
            } else {
                $this->logger->write("Utilities : transferproductstock() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');

                if (trim($content) == '' || empty($content)) {
                    $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
                } else {
                    /**
                     * Modification Date: 2022-06-14
                     * Modified By: Francis Lubanga
                     * Description: Resolving issue of sending the generic PARTIAL ERROR message
                     * */

                    if ($dataDesc['zipCode'] == '1') {
                        $this->logger->write("Utilities : transferproductstock() : The response is zipped", 'r');
                        $content = gzdecode(base64_decode($content));
                    } else {
                        $this->logger->write("Utilities : transferproductstock() : The response is NOT zipped", 'r');
                        $content = base64_decode($content);
                    }
                }
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : transferproductstock() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name syncproducts
     * @desc Sync a product from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $pageNo int, $pageSize int
     *
     */
    public function syncproducts($userid, $pageNo = 1, $pageSize = 90)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : syncproducts() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncproducts() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : syncproducts() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncproducts() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : syncproducts() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : syncproducts() : Sync'ing products started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T127';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $product = array(
                'goodsName' => '',
                'goodsCode' => '',
                'commodityCategoryName' => '',
                'pageNo' => $pageNo,
                'pageSize' => $pageSize
            );
            $product = json_encode($product); //JSON-ifiy
            $product = base64_encode($product); //base64 encode
            $this->logger->write("Utilities : syncproducts() : The encoded product is " . $product, 'r');

            $data = array(
                'data' => array(
                    'content' => $product,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : syncproducts() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];


            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : syncproducts() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : syncproducts() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : syncproducts() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : syncproducts() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : syncproducts() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name syncbranches
     * @desc Sync branches from EFRIS
     * @return JSON-encoded object
     * @param $userid int
     *
     */
    public function syncbranches($userid)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : syncbranches() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncbranches() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : syncbranches() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncbranches() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : syncbranches() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : syncbranches() : Sync'ing branches started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T138';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            //$this->logger->write($this->db->log(TRUE), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $data = array(
                'data' => array(
                    'content' => '',
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);
            $this->logger->write("Utilities : syncbranches() : The request is: " . $data, 'r');

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : syncbranches() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];


            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : syncbranches() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : syncbranches() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : syncbranches() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : syncbranches() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : syncbranches() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name syncefrisinvoices
     * @desc Sync invoices from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $invoicekind int, $startdate date, $enddate date, $pageNo int, $pageSize int
     *
     */
    public function syncefrisinvoices($userid, $invoicekind, $startdate, $enddate, $pageNo = 1, $pageSize = 90)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : syncefrisinvoices() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncefrisinvoices() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : syncefrisinvoices() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncefrisinvoices() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : syncefrisinvoices() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : syncefrisinvoices() : Sync'ing invoices started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T107';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            //$this->logger->write($this->db->log(TRUE), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $invoice = array(
                'oriInvoiceNo' => '',
                'invoiceNo' => '',
                'deviceNo' => $devicedetails->deviceno,
                'buyerTin' => '',
                'buyerNinBrn' => '',
                'buyerLegalName' => '',
                'combineKeywords' => '',
                'invoiceType' => "1",
                'invoiceKind' => strval($invoicekind),
                'isInvalid' => "1",
                'isRefund' => "1",
                'startDate' => $startdate,
                'endDate' => $enddate,
                'pageNo' => $pageNo,
                'pageSize' => $pageSize,
                'referenceNo' => '',
                'branchName' => ''
            );

            $invoice = json_encode($invoice); //JSON-ifiy
            $invoice = base64_encode($invoice); //base64 encode
            $this->logger->write("Utilities : syncinvoices() : The encoded invoice is " . $invoice, 'r');

            $data = array(
                'data' => array(
                    'content' => $invoice,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);
            $this->logger->write("Utilities : syncefrisinvoices() : The request is: " . $data, 'r');

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : syncefrisinvoices() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];


            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : syncefrisinvoices() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : syncefrisinvoices() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : syncefrisinvoices() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : syncefrisinvoices() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : syncefrisinvoices() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name syncefrisdebitnotes
     * @desc Sync debitnotes from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $invoicekind int, $startdate date, $enddate date, $pageNo int, $pageSize int
     *
     */
    public function syncefrisdebitnotes($userid, $invoicekind, $startdate, $enddate, $pageNo = 1, $pageSize = 90)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : syncefrisdebitnotes() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncefrisdebitnotes() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : syncefrisdebitnotes() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncefrisdebitnotes() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : syncefrisdebitnotes() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : syncefrisdebitnotes() : Sync'ing debit notes started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T107';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            //$this->logger->write($this->db->log(TRUE), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $debitnote = array(
                'oriInvoiceNo' => '',
                'debitnoteNo' => '',
                'deviceNo' => $devicedetails->deviceno,
                'buyerTin' => '',
                'buyerNinBrn' => '',
                'buyerLegalName' => '',
                'combineKeywords' => '',
                'invoiceType' => "4",
                'invoiceKind' => strval($invoicekind),
                'isInvalid' => "1",
                'isRefund' => "1",
                'startDate' => $startdate,
                'endDate' => $enddate,
                'pageNo' => $pageNo,
                'pageSize' => $pageSize,
                'referenceNo' => '',
                'branchName' => ''
            );

            $debitnote = json_encode($debitnote); //JSON-ifiy
            $debitnote = base64_encode($debitnote); //base64 encode
            $this->logger->write("Utilities : syncdebitnotes() : The encoded debitnote is " . $debitnote, 'r');

            $data = array(
                'data' => array(
                    'content' => $debitnote,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);
            $this->logger->write("Utilities : syncefrisdebitnotes() : The request is: " . $data, 'r');

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : syncefrisdebitnotes() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];


            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : syncefrisdebitnotes() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : syncefrisdebitnotes() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : syncefrisdebitnotes() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : syncefrisdebitnotes() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : syncefrisdebitnotes() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name syncefriscreditnotes
     * @desc Sync creditnotes from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $startdate date, $enddate date, $pageNo int, $pageSize int
     *
     */
    public function syncefriscreditnotes($userid, $startdate, $enddate, $pageNo = 1, $pageSize = 90)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : syncefriscreditnotes() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncefriscreditnotes() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : syncefriscreditnotes() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncefriscreditnotes() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : syncefriscreditnotes() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : syncefriscreditnotes() : Sync'ing credit notes started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T111';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            //$this->logger->write($this->db->log(TRUE), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $creditnote = array(
                'referenceNo' => '',
                'oriInvoiceNo' => '',
                'invoiceNo' => '',
                'combineKeywords' => '',
                'approveStatus' => '',
                'queryType' => '1',
                'invoiceApplyCategoryCode' => '101',
                'startDate' => $startdate,
                'endDate' => $enddate,
                'pageNo' => $pageNo,
                'pageSize' => $pageSize,
            );

            $creditnote = json_encode($creditnote); //JSON-ifiy
            $creditnote = base64_encode($creditnote); //base64 encode
            $this->logger->write("Utilities : synccreditnotes() : The encoded creditnote is " . $creditnote, 'r');

            $data = array(
                'data' => array(
                    'content' => $creditnote,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);
            $this->logger->write("Utilities : syncefriscreditnotes() : The request is: " . $data, 'r');

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : syncefriscreditnotes() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];


            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : syncefriscreditnotes() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : syncefriscreditnotes() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : syncefriscreditnotes() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : syncefriscreditnotes() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : syncefriscreditnotes() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name syncefrisexcisedutylist
     * @desc Sync the exciseduty list from EFRIS
     * @return JSON-encoded object
     * @param $userid int
     *
     */
    public function syncefrisexcisedutylist($userid)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : syncefrisexcisedutylist() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncefrisexcisedutylist() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : syncefrisexcisedutylist() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncefrisexcisedutylist() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : syncefrisexcisedutylist() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : syncefrisexcisedutylist() : Sync'ing dictionaries started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T125';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            //$this->logger->write($this->db->log(TRUE), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $data = array(
                'data' => array(
                    'content' => '',
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);
            $this->logger->write("Utilities : syncefrisexcisedutylist() : The request is: " . $data, 'r');

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : syncefrisexcisedutylist() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];


            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : syncefrisexcisedutylist() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : syncefrisexcisedutylist() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : syncefrisexcisedutylist() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : syncefrisexcisedutylist() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            $this->logger->write("Utilities : syncefrisexcisedutylist() : The processed response content is: " . $content, 'r');
            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : syncefrisexcisedutylist() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name syncdictionaries
     * @desc Sync dictionaries from EFRIS
     * @return JSON-encoded object
     * @param $userid int
     *
     */
    public function syncdictionaries($userid)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : syncdictionaries() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncdictionaries() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : syncdictionaries() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : syncdictionaries() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : syncdictionaries() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : syncdictionaries() : Sync'ing dictionaries started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T115';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            //$this->logger->write($this->db->log(TRUE), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $data = array(
                'data' => array(
                    'content' => '',
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);
            $this->logger->write("Utilities : syncdictionaries() : The request is: " . $data, 'r');

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : syncdictionaries() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];


            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : syncdictionaries() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : syncdictionaries() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : syncdictionaries() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : syncdictionaries() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            $this->logger->write("Utilities : syncdictionaries() : The processed response content is: " . $content, 'r');
            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : syncdictionaries() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    /**
     * @name synchscodelist
     * @desc Sync dictionaries from EFRIS
     * @return JSON-encoded object
     * @param $userid int
     *
     */
    public function synchscodelist($userid)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : synchscodelist() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : synchscodelist() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : synchscodelist() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : synchscodelist() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : synchscodelist() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : synchscodelist() : Sync'ing dictionaries started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T185';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            //$this->logger->write($this->db->log(TRUE), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $data = array(
                'data' => array(
                    'content' => '',
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);
            $this->logger->write("Utilities : synchscodelist() : The request is: " . $data, 'r');

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : synchscodelist() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];


            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : synchscodelist() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : synchscodelist() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : synchscodelist() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : synchscodelist() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            $this->logger->write("Utilities : synchscodelist() : The processed response content is: " . $content, 'r');
            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : synchscodelist() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    /**
     * @name stockin
     * @desc Upload stock to EFRIS
     * @return JSON-encoded object
     * @param $userid int, $productid int, $batchno string, $qty int, $suppliertin string, $suppliername string, $stockintype string, $productiondate DateTime, $unitprice float
     *
     */
    public function stockin($userid, $branchuraid, $productcode, $batchno, $qty, $suppliertin, $suppliername, $stockintype, $productiondate, $unitprice)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : stockin() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : stockin() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : stockin() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : stockin() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : stockin() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : stockin() : The product code is " . $productcode, 'r');

        try {
            $this->logger->write("Utilities : stockin() : Uploading stock started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T131';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $product = new products($this->db);
            //$product->getByID($id);
            $product->getByErpCode(trim($productcode));

            if ($product->dry()) {
                $this->logger->write("Utilities : stockin() : The product " . $productcode . " does not exist", 'r');
                return json_encode(array('returnCode' => '658', 'returnMessage' => 'goodsCode does not exist!'));
            }

            $stock = array(
                'goodsStockIn' => array(
                    'operationType' => trim($this->appsettings['STOCKINOPERATIONTYPE']),
                    'supplierTin' => $suppliertin,
                    'supplierName' => $suppliername == 'N/A' ? '' : $suppliername,
                    'adjustType' => '',
                    'remarks' => '',
                    'stockInDate' => date('Y-m-d'),
                    'stockInType' => $stockintype,
                    'productionBatchNo' => empty($batchno) ? '' : $batchno,
                    'productionDate' => empty($productiondate) ? '' : $productiondate,
                    'branchId' => empty($branchuraid) ? $devicedetails->branchId : $branchuraid,
                    'invoiceNo' => '',
                    'isCheckBatchNo' => '0'
                ),
                'goodsStockInItem' => array(
                    array(
                        'commodityGoodsId' => empty($product->uraproductidentifier) ? '' : $product->uraproductidentifier,
                        'quantity' => strval($qty),
                        'unitPrice' => $unitprice,
                        'goodsCode' => empty($product->code) ? '' : $product->code,
                        'measureUnit' => empty($product->measureunit) ? '' : $product->measureunit
                    )
                )
            );

            $stock = json_encode($stock); //JSON-ifiy
            $stock = base64_encode($stock); //base64 encode
            $this->logger->write("Utilities : stockin() : The encoded stock is " . $stock, 'r');

            $data = array(
                'data' => array(
                    'content' => $stock,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);
            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : stockin() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : stockin() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');

                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : stockin() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : stockin() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : stockin() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');

                if (trim($content) == '' || empty($content)) {
                    $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
                } else {
                    /**
                     * Modification Date: 2022-06-13
                     * Modified By: Francis Lubanga
                     * Description: Resolving issue of sending the generic PARTIAL ERROR message
                     * */

                    if ($dataDesc['zipCode'] == '1') {
                        $this->logger->write("Utilities : stockin() : The response is zipped", 'r');
                        $content = gzdecode(base64_decode($content));
                    } else {
                        $this->logger->write("Utilities : stockin() : The response is NOT zipped", 'r');
                        $content = base64_decode($content);
                    }
                }
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : stockin() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    /**
     * @name stockout
     * @desc Reduce stock from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $productid int, $batchno string, $qty int, $adjustmenttype string, $remarks string
     *
     */
    public function stockout($userid, $branchuraid, $productcode, $batchno, $qty, $adjustmenttype, $remarks)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : stockout() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : stockout() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : stockout() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : stockout() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : stockout() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : stockout() : The product id is " . $productcode, 'r');

        try {
            $this->logger->write("Utilities : stockout() : Stocking out started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T131';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $product = new products($this->db);
            //$product->getByID($id);
            $product->getByErpCode(trim($productcode));

            if ($product->dry()) {
                $this->logger->write("Utilities : stockout() : The product " . $productcode . " does not exist", 'r');
                return json_encode(array('returnCode' => '658', 'returnMessage' => 'goodsCode does not exist!'));
            }

            $stock = array(
                'goodsStockIn' => array(
                    'operationType' => trim($this->appsettings['STOCKOUTOPERATIONTYPE']),
                    'supplierTin' => '',
                    'supplierName' => '',
                    'adjustType' => $adjustmenttype,
                    'remarks' => $remarks,
                    'stockInDate' => '',
                    /**
                     *Date: 2020-11-18
                     *Authour: Francis Lubanga
                     *Description: Fixing error code 2177
                     *
                     */
                    //'stockInType' => empty($product->stockintype)? '' : $product->stockintype,
                    'stockInType' => '',
                    'productionBatchNo' => '',
                    'productionDate' => '',
                    'branchId' => empty($branchuraid) ? $devicedetails->branchId : $branchuraid,
                    'invoiceNo' => '',
                    'isCheckBatchNo' => '0'
                ),
                'goodsStockInItem' => array(
                    array(
                        'commodityGoodsId' => empty($product->uraproductidentifier) ? '' : $product->uraproductidentifier,
                        'quantity' => strval($qty),
                        'unitPrice' => (empty($product->purchaseprice) || $product->purchaseprice == '0.00') ? '1' : $product->purchaseprice,
                        'goodsCode' => empty($product->code) ? '' : $product->code,
                        'measureUnit' => empty($product->measureunit) ? '' : $product->measureunit
                    )
                )
            );

            $stock = json_encode($stock); //JSON-ifiy
            $stock = base64_encode($stock); //base64 encode
            $this->logger->write("Utilities : stockout() : The encoded stock is " . $stock, 'r');

            $data = array(
                'data' => array(
                    'content' => $stock,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);
            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : stockout() : The response content is: " . $content, 'r');
            //$this->logger->write("Utilities : stockout() : The return info is: " . $returninfo['returnCode'], 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : stockout() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');

                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : stockout() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : stockout() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : stockout() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');

                if (trim($content) == '' || empty($content)) {
                    $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
                } else {
                    /**
                     * Modification Date: 2022-06-13
                     * Modified By: Francis Lubanga
                     * Description: Resolving issue of sending the generic PARTIAL ERROR message
                     * */

                    if ($dataDesc['zipCode'] == '1') {
                        $this->logger->write("Utilities : stockout() : The response is zipped", 'r');
                        $content = gzdecode(base64_decode($content));
                    } else {
                        $this->logger->write("Utilities : stockout() : The response is NOT zipped", 'r');
                        $content = base64_decode($content);
                    }
                }

            }


            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : stockout() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    private function loadLegacyUploadContext($documentType, $documentId, $userid)
    {
        $context = array(
            'ok' => false,
            'message' => 'Unsupported document type',
            'branchuraid' => '',
            'buyer' => array(),
            'document' => array(),
            'goods' => array(),
            'payments' => array(),
            'taxes' => array()
        );

        $documentId = (int)$documentId;
        $userid = (int)$userid;
        if ($documentId <= 0) {
            $context['message'] = 'Document id is missing';
            return $context;
        }

        $table = '';
        if ($documentType === 'invoice') {
            $table = 'tblinvoices';
        } elseif ($documentType === 'creditnote') {
            $table = 'tblcreditnotes';
        } elseif ($documentType === 'debitnote') {
            $table = 'tbldebitnotes';
        }

        if ($table === '') {
            return $context;
        }

        try {
            $rows = $this->db->exec('SELECT * FROM ' . $table . ' WHERE id = ? LIMIT 1', array($documentId));
            if (empty($rows)) {
                $context['message'] = 'Document not found';
                return $context;
            }

            $document = $rows[0];

            $buyerRows = array();
            if (!empty($document['buyerid'])) {
                $buyerRows = $this->db->exec('SELECT * FROM tblbuyers WHERE id = ? LIMIT 1', array((int)$document['buyerid']));
                if (empty($buyerRows)) {
                    $buyerRows = $this->db->exec('SELECT * FROM tblcustomers WHERE id = ? LIMIT 1', array((int)$document['buyerid']));
                }
            }

            $goods = array();
            if (!empty($document['gooddetailgroupid'])) {
                $goods = $this->db->exec('SELECT * FROM tblgooddetails WHERE groupid = ?', array((int)$document['gooddetailgroupid']));
            }

            $payments = array();
            if (!empty($document['paymentdetailgroupid'])) {
                $payments = $this->db->exec('SELECT * FROM tblpaymentdetails WHERE groupid = ?', array((int)$document['paymentdetailgroupid']));
            }

            $taxes = array();
            if (!empty($document['taxdetailgroupid'])) {
                $taxes = $this->db->exec('SELECT * FROM tbltaxdetails WHERE groupid = ?', array((int)$document['taxdetailgroupid']));
            }

            $branchuraid = '';
            if ($userid > 0) {
                $branchRows = $this->db->exec(
                    'SELECT b.uraid FROM tblusers u LEFT JOIN tblbranches b ON b.id = u.branch WHERE u.id = ? LIMIT 1',
                    array($userid)
                );
                if (!empty($branchRows) && isset($branchRows[0]['uraid'])) {
                    $branchuraid = trim((string)$branchRows[0]['uraid']);
                }
            }

            $context['ok'] = true;
            $context['message'] = '';
            $context['branchuraid'] = $branchuraid;
            $context['buyer'] = empty($buyerRows) ? array() : $buyerRows[0];
            $context['document'] = $document;
            $context['goods'] = $goods;
            $context['payments'] = $payments;
            $context['taxes'] = $taxes;
        } catch (Exception $e) {
            $context['message'] = $e->getMessage();
        }

        return $context;
    }

    /**
     * @name uploadinvoice
     * @desc Upload an invoice to EFRIS
     * @return JSON-encoded object
     * @param $userid int, $invoiceid int, $vatRegistered string
     *
     */
    public function uploadinvoice($userid, $branchuraid, $buyer = null, $invoicedetails = null, $goods = null, $payments = null, $taxes = null)
    {
        // Backward compatibility: some controllers still call uploadinvoice($userid, $invoiceid, $vatRegistered).
        if (!is_array($invoicedetails) || !is_array($goods) || !is_array($payments) || !is_array($taxes)) {
            $legacyInvoiceId = is_numeric($branchuraid) ? (int)$branchuraid : 0;
            $legacyContext = $this->loadLegacyUploadContext('invoice', $legacyInvoiceId, $userid);
            if (!$legacyContext['ok']) {
                $this->logger->write('Utilities : uploadinvoice() : Legacy compatibility load failed. ' . $legacyContext['message'], 'r');
                return json_encode(array('returnCode' => '999', 'returnMessage' => $legacyContext['message']));
            }

            $branchuraid = $legacyContext['branchuraid'];
            $buyer = $legacyContext['buyer'];
            $invoicedetails = $legacyContext['document'];
            $goods = $legacyContext['goods'];
            $payments = $legacyContext['payments'];
            $taxes = $legacyContext['taxes'];
        }

        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : uploadinvoice() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : uploadinvoice() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : uploadinvoice() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : uploadinvoice() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : uploadinvoice() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : uploadinvoice() : Uploading invoice started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T109';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            //$invoice = $invoicedetails;

            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);

            $t_goods = array();
            $t_taxes = array();
            $t_payments = array();
            $t_summary = array();
            $t_airlinegoods = array();
            $productArtifactCache = array();

            $netamount = 0;
            $taxamount = 0;
            $grossamount = 0;
            $itemcount = 0;

            // Delivery terms precedence for payload consistency: line override -> header -> buyer default -> empty.
            $headerDeliveryTermsCode = empty($invoicedetails['deliveryTermsCode']) ? '' : trim((string)$invoicedetails['deliveryTermsCode']);
            $buyerDeliveryTermsCode = empty($buyer['deliveryTermsCode']) ? '' : trim((string)$buyer['deliveryTermsCode']);
            $effectiveDeliveryTermsCode = $headerDeliveryTermsCode !== '' ? $headerDeliveryTermsCode : $buyerDeliveryTermsCode;

            try {
                //$temp = $goods;
                $this->logger->write("Utilities : uploadinvoice() : The GOOD is " . $goods[0]['item'], 'r');

                $i = 0;
                foreach ($goods as $obj) {
                    $this->logger->write("Utilities : uploadinvoice() : The unit price is " . round($obj['unitprice'], 8), 'r');
                    $this->logger->write("Utilities : uploadinvoice() : The total is " . floor($obj['total'] * 100) / 100, 'r');
                    $this->logger->write("Utilities : uploadinvoice() : The tax is " . floor($obj['tax'] * 100) / 100, 'r');

                    $itemCode = isset($obj['itemcode']) ? trim((string)$obj['itemcode']) : '';
                    if ($itemCode !== '') {
                        if (!array_key_exists($itemCode, $productArtifactCache)) {
                            $p = new products($this->db);
                            $p->getByCode($itemCode);

                            if ($p->dry()) {
                                $productArtifactCache[$itemCode] = null;
                            } else {
                                $productArtifactCache[$itemCode] = array(
                                    'hasexcisetax' => isset($p->hasexcisetax) ? trim((string)$p->hasexcisetax) : '',
                                    'measureunit' => isset($p->measureunit) ? trim((string)$p->measureunit) : '',
                                    'piecemeasureunit' => isset($p->piecemeasureunit) ? trim((string)$p->piecemeasureunit) : '',
                                    'packagescaledvalue' => isset($p->packagescaledvalue) ? trim((string)$p->packagescaledvalue) : '',
                                    'piecescaledvalue' => isset($p->piecescaledvalue) ? trim((string)$p->piecescaledvalue) : '',
                                    'excisedutylist' => isset($p->excisedutylist) ? trim((string)$p->excisedutylist) : ''
                                );
                            }
                        }

                        $artifact = $productArtifactCache[$itemCode];
                        if (!empty($artifact)) {
                            $isExciseProduct = ($artifact['hasexcisetax'] === '101');

                            if ($isExciseProduct) {
                                if (empty($obj['exciseflag']) || trim((string)$obj['exciseflag']) === '2') {
                                    $obj['exciseflag'] = '1';
                                }

                                if (empty($obj['pieceQty']) || (float)$obj['pieceQty'] <= 0) {
                                    $obj['pieceQty'] = empty($obj['qty']) ? '' : $obj['qty'];
                                }

                                if (empty($obj['pieceMeasureUnit'])) {
                                    $obj['pieceMeasureUnit'] = empty($artifact['piecemeasureunit']) ? '' : $artifact['piecemeasureunit'];
                                }

                                if (empty($obj['pack']) && !empty($artifact['packagescaledvalue'])) {
                                    $obj['pack'] = $artifact['packagescaledvalue'];
                                }

                                if (empty($obj['stick']) && !empty($artifact['piecescaledvalue'])) {
                                    $obj['stick'] = $artifact['piecescaledvalue'];
                                }

                                if (empty($obj['exciseunit'])) {
                                    $obj['exciseunit'] = !empty($artifact['piecemeasureunit']) ? $artifact['piecemeasureunit'] : $artifact['measureunit'];
                                }

                                if (empty($obj['exciseratename']) && !empty($artifact['excisedutylist'])) {
                                    $obj['exciseratename'] = $artifact['excisedutylist'];
                                }
                            }
                        }
                    }

                    if ($obj['deemedflag'] == '1') {
                        $obj['item'] = $obj['item'] . " (Deemed)";

                        //Truncate
                        //$obj['unitprice'] = round($obj['unitprice'], 8);
                        $obj['total'] = floor($obj['total'] * 100) / 100;
                        $obj['tax'] = floor($obj['tax'] * 100) / 100;
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : floor($obj['discounttotal'] * 100) / 100;

                        //Ensure 2 decimal places
                        /*$obj['unitprice'] = $this->truncatenumber($obj['unitprice'], 8);
                        $obj['total'] = $this->truncatenumber($obj['total'], 2);
                        $obj['tax'] = $this->truncatenumber($obj['tax'], 2);
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00')? NULL : $this->truncatenumber($obj['discounttotal'], 2);*/

                    } else {
                        //Round off
                        //$obj['unitprice'] = round($obj['unitprice'], 8);
                        $obj['total'] = round($obj['total'], 4);
                        $obj['tax'] = round($obj['tax'], 4);
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : round($obj['discounttotal'], 4);
                    }

                    //Ensure the right decimal places
                    $obj['qty'] = $this->truncatenumber($obj['qty'], 8);
                    $obj['unitprice'] = $this->truncatenumber($obj['unitprice'], 8);
                    $obj['total'] = $this->truncatenumber($obj['total'], 4);
                    $obj['tax'] = $this->truncatenumber($obj['tax'], 4);
                    $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : $this->truncatenumber($obj['discounttotal'], 4);

                    /**
                     * Author: Francis Lubanga <frncslubanga@gmail.com>
                     * Date: 2026-01-23
                     * Description: Add 3 decimal places to excise tax
                     */
                    $obj['excisetax'] =  $obj['exciseflag'] == '1' ? $this->truncatenumber($obj['excisetax'], 4) : '';

                    $t_goods[] = array(
                        'item' => empty($obj['item']) ? '' : $obj['item'],
                        'itemCode' => empty($obj['itemcode']) ? '' : $obj['itemcode'],
                        'qty' => empty($obj['qty']) ? '' : strval($obj['qty']),
                        'unitOfMeasure' => empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'],
                        'unitPrice' => empty($obj['unitprice']) ? '' : strval($obj['unitprice']),
                        'total' => empty($obj['total']) ? '' : strval($obj['total']),
                        'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '' : number_format($obj['taxrate'], 4, '.', '')),
                        'tax' => empty($obj['tax']) ? '' : strval($obj['tax']),
                        'discountTotal' => (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? '' : strval($obj['discounttotal']),
                        /**
                         * Modification Date: 2023-05-16
                         * Modified By: Francis Lubanga
                         * Description: 1214 - goodsDetails-->discountTaxRate:If 'discountFlag' is '2', 'discountTaxRate' must be empty!Collection index:0
                         * */
                        'discountTaxRate' => (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00' || empty($obj['discountflag']) || trim($obj['discountflag']) == '2') ? '' : $obj['discounttaxrate'],
                        'orderNumber' => strval($i),
                        'discountFlag' => empty($obj['discountflag']) ? '2' : $obj['discountflag'],
                        'deemedFlag' => empty($obj['deemedflag']) ? '2' : $obj['deemedflag'],
                        'exciseFlag' => empty($obj['exciseflag']) ? '2' : $obj['exciseflag'],
                        'categoryId' => empty($obj['categoryid']) ? '' : $obj['categoryid'],
                        'categoryName' => empty($obj['categoryname']) ? '' : $obj['categoryname'],
                        'goodsCategoryId' => empty($obj['goodscategoryid']) ? '' : $obj['goodscategoryid'],
                        'goodsCategoryName' => empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'],
                        'exciseRate' => empty($obj['exciserate']) ? '' : $obj['exciserate'],
                        'exciseRule' => empty($obj['exciserule']) ? '' : $obj['exciserule'],
                        'exciseTax' => empty($obj['excisetax']) || $obj['exciseflag'] == '2' ? '' : strval($obj['excisetax']),
                        'pack' => empty($obj['pack']) ? '' : $obj['pack'],
                        'stick' => empty($obj['stick']) ? '' : $obj['stick'],
                        'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                        'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                        'exciseRateName' => empty($obj['exciseratename']) ? '' : $obj['exciseratename'],
                        /**
                         * Modification Date: 2025-05-30
                         * Modified By: Francis Lubanga
                         * Description: Resolving error code 2857 - goodsDetails-->taxRate:If 'vatApplicableFlag' is '0', 'taxRate' must be '0'!Collection index:1 when sending VAT OUT OF SCOPE items
                         * */
                        'vatApplicableFlag' => (empty($obj['goodscategoryid']) || $obj['goodscategoryid'] !== '96010102') ? '1' : '0',
                        //'vatApplicableFlag' => empty($obj['vatApplicableFlag'])? '' : $obj['vatApplicableFlag'],
                        'deemedExemptCode' => empty($obj['deemedExemptCode']) ? '' : $obj['deemedExemptCode'],
                        'nonResidentFlag' => empty($obj['nonResidentFlag']) ? (empty($buyer['nonResidentFlag']) ? '0' : $buyer['nonResidentFlag']) : $obj['nonResidentFlag'],
                        'deliveryTermsCode' => empty($obj['deliveryTermsCode']) ? $effectiveDeliveryTermsCode : $obj['deliveryTermsCode'],
                        'vatProjectId' => empty($obj['vatProjectId']) ? '' : $obj['vatProjectId'],
                        'vatProjectName' => empty($obj['vatProjectName']) ? '' : $obj['vatProjectName'],
                        'hsCode' => empty($obj['hsCode']) ? '' : $obj['hsCode'],
                        'hsName' => empty($obj['hsName']) ? '' : $obj['hsName'],
                        'totalWeight' => empty($obj['totalWeight']) ? '' : round($obj['totalWeight'], 4),
                        'pieceQty' => empty($obj['pieceQty']) ? '' : round((float)$obj['pieceQty'], 8),
                        'pieceMeasureUnit' => empty($obj['pieceMeasureUnit']) ? '' : $obj['pieceMeasureUnit']
                    );

                    $i = $i + 1;

                    //If there is a discount, add a discount line below the item
                    if ($obj['discounttotal'] < 0) {
                        $t_goods[] = array(
                            'item' => empty($obj['item']) ? '' : $obj['item'] . " (Discount)",
                            'itemCode' => empty($obj['itemcode']) ? '' : $obj['itemcode'],
                            'qty' => '',
                            'unitOfMeasure' => '',
                            'unitPrice' => '',
                            'total' => empty($obj['discounttotal']) ? '' : strval($obj['discounttotal']),
                            'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '' : $obj['taxrate']),
                            /**
                             * Modification Date: 2020-11-15
                             * Modified By: Francis Lubanga
                             * Description: Resolving error code 1200 - goodsDetails-->tax:cannot be empty!Collection index:1
                             * Modification Date: 2021-01-26
                             * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                             * */
                            //'tax' => '',
                            'tax' => strval(number_format((($obj['discounttotal'] / ($obj['taxrate'] + 1)) * $obj['taxrate']), 4, '.', '')),
                            'discountTotal' => '',
                            /**
                             * Modification Date: 2023-05-16
                             * Modified By: Francis Lubanga
                             * Description: 1214 - goodsDetails-->discountTaxRate:If 'discountFlag' is '2', 'discountTaxRate' must be empty!Collection index:0
                             * */
                            'discountTaxRate' => (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00' || empty($obj['discountflag']) || trim($obj['discountflag']) == '2') ? '' : $obj['discounttaxrate'],
                            'orderNumber' => strval($i),
                            'discountFlag' => '0',
                            'deemedFlag' => empty($obj['deemedflag']) ? '2' : $obj['deemedflag'],
                            'exciseFlag' => empty($obj['exciseflag']) ? '2' : $obj['exciseflag'],
                            'categoryId' => empty($obj['categoryid']) ? '' : $obj['categoryid'],
                            'categoryName' => empty($obj['categoryname']) ? '' : $obj['categoryname'],
                            'goodsCategoryId' => empty($obj['goodscategoryid']) ? '' : $obj['goodscategoryid'],
                            'goodsCategoryName' => empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'],
                            'exciseRate' => empty($obj['exciserate']) ? '' : $obj['exciserate'],
                            'exciseRule' => empty($obj['exciserule']) ? '' : $obj['exciserule'],
                            'exciseTax' => empty($obj['excisetax']) || $obj['exciseflag'] == '2' ? '' : strval($obj['excisetax']),
                            'pack' => empty($obj['pack']) ? '' : round((float)$obj['pack'], 8),
                            'stick' => empty($obj['stick']) ? '' : round((float)$obj['stick'], 8),
                            'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                            'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                            'exciseRateName' => empty($obj['exciseratename']) ? '' : $obj['exciseratename'],
                            /**
                             * Modification Date: 2025-05-30
                             * Modified By: Francis Lubanga
                             * Description: Resolving error code 2857 - goodsDetails-->taxRate:If 'vatApplicableFlag' is '0', 'taxRate' must be '0'!Collection index:1 when sending VAT OUT OF SCOPE items
                             * */
                            'vatApplicableFlag' => (empty($obj['goodscategoryid']) || $obj['goodscategoryid'] !== '96010102') ? '1' : '0',
                            //'vatApplicableFlag' => empty($obj['vatApplicableFlag'])? '' : $obj['vatApplicableFlag'],
                            'deemedExemptCode' => empty($obj['deemedExemptCode']) ? '' : $obj['deemedExemptCode'],
                            'nonResidentFlag' => empty($obj['nonResidentFlag']) ? (empty($buyer['nonResidentFlag']) ? '0' : $buyer['nonResidentFlag']) : $obj['nonResidentFlag'],
                            'deliveryTermsCode' => empty($obj['deliveryTermsCode']) ? $effectiveDeliveryTermsCode : $obj['deliveryTermsCode'],
                            'vatProjectId' => empty($obj['vatProjectId']) ? '' : $obj['vatProjectId'],
                            'vatProjectName' => empty($obj['vatProjectName']) ? '' : $obj['vatProjectName'],
                            'hsCode' => empty($obj['hsCode']) ? '' : $obj['hsCode'],
                            'hsName' => empty($obj['hsName']) ? '' : $obj['hsName'],
                            'totalWeight' => empty($obj['totalWeight']) ? '' : round($obj['totalWeight'], 4),
                            'pieceQty' => empty($obj['pieceQty']) ? '' : round((float)$obj['pieceQty'], 8),
                            'pieceMeasureUnit' => empty($obj['pieceMeasureUnit']) ? '' : $obj['pieceMeasureUnit']
                        );

                        $i = $i + 1;
                    }

                    $netamount = $netamount + $obj['total'];
                    $taxamount = $taxamount + $obj['tax'];

                    /**
                     * Author: frncslubanga@gmail.com
                     * Date: 2026-01-11
                     * Description: Add the excise duty tax to the tax amount.
                     */
                    // $taxamount = $taxamount + $obj['tax'] + (float)$obj['excisetax'];

                    $grossamount = $grossamount + $obj['total'];
                    $itemcount = $itemcount + 1;
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploadinvoice() : The operation to retrive the good details was not successful. The error messages is " . $e->getMessage(), 'r');
            }

            //var_dump($goods);
            $deemedflag = 'N';


            try {
                //$temp = $taxes;

                if ($this->vatRegistered == 'Y') {

                    //RESET THE SUMMARIES
                    $netamount = 0;
                    $taxamount = 0;
                    $grossamount = 0;
                    $itemcount = 0;

                    foreach ($taxes as $obj) {

                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : uploadinvoice() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : uploadinvoice() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : uploadinvoice() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }


                        if (strtoupper(trim($obj['taxcategory'])) == 'D: DEEMED (18%)') {
                            $obj['netamount'] = floor(($obj['netamount'] + $obj['d_netamount']) * 100) / 100;
                            $obj['taxamount'] = floor(($obj['taxamount'] + $obj['d_taxamount']) * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 4, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 4, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 4, '.', '');

                            $deemedflag = 'Y';
                        } else {
                            $obj['netamount'] = round(($obj['netamount'] + $obj['d_netamount']), 4);
                            $obj['taxamount'] = round(($obj['taxamount'] + $obj['d_taxamount']), 4);
                            //$obj['grossamount'] = round($obj['grossamount'], 4);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $deemedflag = 'N';
                        }

                        $this->logger->write("Utilities : uploadinvoice() : Calculating taxes. The deemedflag is " . $deemedflag, 'r');

                        $t_taxes[] = array(
                            'taxCategoryCode' => empty($obj['taxcategoryCode']) ? '' : $obj['taxcategoryCode'],
                            'taxCategory' => empty($obj['taxcategory']) ? '' : $obj['taxcategory'],
                            'netAmount' => empty($obj['netamount']) ? '0' : strval($obj['netamount']),
                            'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '0' : number_format($obj['taxrate'], 4, '.', '')),
                            'taxAmount' => empty($obj['taxamount']) ? '0' : strval($obj['taxamount']),
                            'grossAmount' => empty($obj['grossamount']) ? '0' : strval($obj['grossamount']),
                            'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                            'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                            'taxRateName' => empty($obj['taxratename']) ? '' : $obj['taxratename']
                        );

                        /**
                         * Author: frncslubanga@gmail.com
                         * Date: 2026-01-11
                         * Description: Ignore the NET amount on the execise duty
                         */
                        // $netamount = $netamount + $obj['netamount'];

                        if (trim($obj['taxcategory']) != 'E: Excise Duty') {
                            $netamount = $netamount + $obj['netamount'];
                        } else {
                            $netamount = $netamount - $obj['taxamount'];
                        }

                        $taxamount = $taxamount + $obj['taxamount'];

                        /**
                         * Author: frncslubanga@gmail.com
                         * Date: 2026-01-11
                         * Description: Ignore the GROSS amount on the execise duty
                         */
                        // $grossamount = $grossamount + $obj['grossamount'];

                        if (trim($obj['taxcategory']) != 'E: Excise Duty') {
                            $grossamount = $grossamount + $obj['grossamount'];
                        }

                        $itemcount = $itemcount + 1;
                    }
                }


            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploadinvoice() : The operation to retrive the tax details was not successful. The error messages is " . $e->getMessage(), 'r');
            }

            $t_summary[] = array(
                'netamount' => strtoupper(trim($deemedflag)) == 'N' ? round($netamount, 4) : number_format((floor($netamount * 100) / 100), 4, '.', ''),
                'taxamount' => strtoupper(trim($deemedflag)) == 'N' ? round($taxamount, 4) : 0,
                'grossamount' => strtoupper(trim($deemedflag)) == 'N' ? round($grossamount, 4) : number_format((floor($netamount * 100) / 100), 4, '.', ''),
                'itemcount' => sizeof($goods)
            );

            try {
                //$temp = $taxes;

                foreach ($payments as $obj) {
                    $t_payments[] = array(
                        'paymentMode' => empty($obj['paymentmode']) ? '' : $obj['paymentmode'],
                        'paymentAmount' => empty($obj['paymentamount']) ? '' : strval(round($obj['paymentamount'], 4)),
                        'orderNumber' => empty($obj['ordernumber']) ? '' : $obj['ordernumber']
                    );
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploadinvoice() : The operation to retrive the payment details was not successful. The error messages is " . $e->getMessage(), 'r');
            }


            //return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));

            if (trim($invoicedetails['invoiceindustrycode']) == '104') {
                $this->logger->write("Utilities : uploadinvoice() : This is an Imported Services invoice. Proceed and swap the seller with the buyer.", 'r');

                $invoice_u = array(
                    'sellerDetails' => array(
                        'tin' => empty($org->tin) ? '' : $org->tin,
                        'ninBrn' => empty($org->ninbrn) ? '' : addslashes($org->ninbrn),
                        'legalName' => empty($org->legalname) ? '' : addslashes($org->legalname),
                        'businessName' => empty($org->businessname) ? '' : addslashes($org->businessname),
                        'address' => empty($org->address) ? '' : addslashes($org->address),
                        'mobilePhone' => empty($org->mobilephone) ? '' : $org->mobilephone,
                        'linePhone' => empty($org->linephone) ? '' : $org->linephone,
                        'emailAddress' => empty($org->emailaddress) ? '' : addslashes($org->emailaddress),
                        'placeOfBusiness' => empty($org->placeofbusiness) ? '' : addslashes($org->placeofbusiness),
                        'referenceNo' => empty($invoicedetails['erpinvoiceid']) ? '' : $invoicedetails['erpinvoiceid'],
                        'branchId' => empty($branchuraid) ? $devicedetails->branchId : $branchuraid,
                        'isCheckReferenceNo' => '0'
                    ),
                    'basicInformation' => array(
                        'invoiceNo' => empty($invoicedetails['erpinvoiceid']) ? '' : $invoicedetails['erpinvoiceid'],
                        'antifakeCode' => empty($invoicedetails['antifakecode']) ? '' : $invoicedetails['antifakecode'],
                        'deviceNo' => $invoicedetails['deviceno'],
                        'issuedDate' => date('Y-m-d H:i:s'),
                        'operator' => $invoicedetails['operator'],
                        'currency' => $invoicedetails['currency'],
                        'oriInvoiceId' => empty($invoicedetails['oriinvoiceid']) ? '' : $invoicedetails['oriinvoiceid'],
                        'invoiceType' => empty($invoicedetails['invoicetype']) ? '' : strval($invoicedetails['invoicetype']),
                        'invoiceKind' => empty($invoicedetails['invoicekind']) ? '' : strval($invoicedetails['invoicekind']),
                        'dataSource' => empty($invoicedetails['datasource']) ? '' : strval($invoicedetails['datasource']),
                        'invoiceIndustryCode' => empty($invoicedetails['invoiceindustrycode']) ? '' : strval($invoicedetails['invoiceindustrycode']),
                        'vatProjectId' => empty($invoicedetails['vatProjectId']) ? '' : $invoicedetails['vatProjectId'],
                        'vatProjectName' => empty($invoicedetails['vatProjectName']) ? '' : $invoicedetails['vatProjectName'],
                        'deliveryTermsCode' => $effectiveDeliveryTermsCode,
                        'isBatch' => empty($invoicedetails['isbatch']) ? '' : $invoicedetails['isbatch']
                    ),
                    'buyerDetails' => array(
                        'buyerTin' => empty($org->tin) ? '' : $org->tin,
                        'buyerNinBrn' => empty($org->ninbrn) ? '' : addslashes($org->ninbrn),
                        'buyerPassportNum' => '',
                        'buyerLegalName' => empty($org->legalname) ? '' : addslashes($org->legalname),
                        'buyerBusinessName' => empty($org->businessname) ? '' : addslashes($org->businessname),
                        'buyerAddress' => empty($org->address) ? '' : addslashes($org->address),
                        'buyerEmail' => empty($org->emailaddress) ? '' : addslashes($org->emailaddress),
                        'buyerMobilePhone' => empty($org->mobilephone) ? '' : $org->mobilephone,
                        'buyerLinePhone' => empty($org->linephone) ? '' : $org->linephone,
                        'buyerPlaceOfBusi' => empty($org->placeofbusiness) ? '' : addslashes($org->placeofbusiness),
                        'buyerType' => strval($this->appsettings['B2BCODE']),
                        'buyerCitizenship' => '',
                        'buyerSector' => '',
                        'buyerReferenceNo' => empty($buyer['referenceno']) ? '' : $buyer['referenceno'],
                        'nonResidentFlag' => '0',
                        'deliveryTermsCode' => $effectiveDeliveryTermsCode
                    ),
                    'goodsDetails' => $t_goods,
                    'taxDetails' => $t_taxes,
                    'summary' => array(
                        'netAmount' => empty($t_summary[0]['netamount']) ? '0' : strval($t_summary[0]['netamount']),
                        'taxAmount' => empty($t_summary[0]['taxamount']) ? '0' : strval($t_summary[0]['taxamount']),
                        'grossAmount' => empty($t_summary[0]['grossamount']) ? '0' : strval($t_summary[0]['grossamount']),
                        'itemCount' => empty($t_summary[0]['itemcount']) ? '0' : strval($t_summary[0]['itemcount']),
                        'modeCode' => empty($invoicedetails['modecode']) ? '' : $invoicedetails['modecode'],
                        'remarks' => empty($invoicedetails['remarks']) ? '' : $invoicedetails['remarks'],
                        'qrCode' => ''
                    ),
                    'payWay' => $t_payments,
                    'extend' => array(
                        /**
                         * Author: frncslubanga@gmail.com
                         * Date: 2023-05-16
                         * Description: Resolve EFRIS error code 1404 - reasonCode:Invalid field value!
                         */
                        'reason' => trim($invoicedetails['invoicetype']) == '5' ? 'Credit Memo' : '',
                        'reasonCode' => trim($invoicedetails['invoicetype']) == '5' ? '103' : ''
                    ),
                    'importServicesSeller' => array(
                        'importBusinessName' => empty($buyer['businessname']) ? $buyer['legalname'] : $buyer['businessname'],
                        'importEmailAddress' => empty($buyer['emailaddress']) ? '' : $buyer['emailaddress'],
                        'importContactNumber' => empty($buyer['mobilephone']) ? $buyer['linephone'] : $buyer['mobilephone'],
                        'importAddress' => empty($buyer['address']) ? $buyer['placeofbusiness'] : $buyer['address'],
                        'importInvoiceDate' => date('Y-m-d'),
                        'importAttachmentName' => '',
                        'importAttachmentContent' => ''
                    ),
                    'airlineGoodsDetails' => $t_airlinegoods,
                    'buyerExtend' => array(
                        'propertyType' => '',
                        'district' => '',
                        'municipalityCounty' => '',
                        'divisionSubcounty' => '',
                        'town' => '',
                        'cellVillage' => '',
                        'effectiveRegistrationDate' => '',
                        'meterStatus' => ''
                    ),
                );
            } else {
                $invoice_u = array(
                    'sellerDetails' => array(
                        'tin' => empty($org->tin) ? '' : $org->tin,
                        'ninBrn' => empty($org->ninbrn) ? '' : addslashes($org->ninbrn),
                        'legalName' => empty($org->legalname) ? '' : addslashes($org->legalname),
                        'businessName' => empty($org->businessname) ? '' : addslashes($org->businessname),
                        'address' => empty($org->address) ? '' : addslashes($org->address),
                        'mobilePhone' => empty($org->mobilephone) ? '' : $org->mobilephone,
                        'linePhone' => empty($org->linephone) ? '' : $org->linephone,
                        'emailAddress' => empty($org->emailaddress) ? '' : addslashes($org->emailaddress),
                        'placeOfBusiness' => empty($org->placeofbusiness) ? '' : addslashes($org->placeofbusiness),
                        'referenceNo' => empty($invoicedetails['erpinvoiceid']) ? '' : $invoicedetails['erpinvoiceid'],
                        'branchId' => empty($branchuraid) ? $devicedetails->branchId : $branchuraid,
                        'isCheckReferenceNo' => '0'
                    ),
                    'basicInformation' => array(
                        'invoiceNo' => empty($invoicedetails['erpinvoiceid']) ? '' : $invoicedetails['erpinvoiceid'],
                        'antifakeCode' => empty($invoicedetails['antifakecode']) ? '' : $invoicedetails['antifakecode'],
                        'deviceNo' => $invoicedetails['deviceno'],
                        'issuedDate' => date('Y-m-d H:i:s'),
                        'operator' => $invoicedetails['operator'],
                        'currency' => $invoicedetails['currency'],
                        'oriInvoiceId' => empty($invoicedetails['oriinvoiceid']) ? '' : $invoicedetails['oriinvoiceid'],
                        'invoiceType' => empty($invoicedetails['invoicetype']) ? '' : strval($invoicedetails['invoicetype']),
                        'invoiceKind' => empty($invoicedetails['invoicekind']) ? '' : strval($invoicedetails['invoicekind']),
                        'dataSource' => empty($invoicedetails['datasource']) ? '' : strval($invoicedetails['datasource']),
                        'invoiceIndustryCode' => empty($invoicedetails['invoiceindustrycode']) ? '' : strval($invoicedetails['invoiceindustrycode']),
                        'vatProjectId' => empty($invoicedetails['vatProjectId']) ? '' : $invoicedetails['vatProjectId'],
                        'vatProjectName' => empty($invoicedetails['vatProjectName']) ? '' : $invoicedetails['vatProjectName'],
                        'deliveryTermsCode' => $effectiveDeliveryTermsCode,
                        'isBatch' => empty($invoicedetails['isbatch']) ? '' : $invoicedetails['isbatch']
                    ),
                    'buyerDetails' => array(
                        'buyerTin' => empty($buyer['tin']) ? '' : $buyer['tin'],
                        'buyerNinBrn' => empty($buyer['ninbrn']) ? '' : $buyer['ninbrn'],
                        'buyerPassportNum' => empty($buyer['PassportNum']) ? '' : $buyer['PassportNum'],
                        'buyerLegalName' => empty($buyer['legalname']) ? '' : $buyer['legalname'],
                        'buyerBusinessName' => empty($buyer['businessname']) ? '' : $buyer['businessname'],
                        'buyerAddress' => empty($buyer['address']) ? '' : $buyer['address'],
                        'buyerEmail' => empty($buyer['emailaddress']) ? '' : $buyer['emailaddress'],
                        'buyerMobilePhone' => empty($buyer['mobilephone']) ? '' : $buyer['mobilephone'],
                        'buyerLinePhone' => empty($buyer['linephone']) ? '' : $buyer['linephone'],
                        'buyerPlaceOfBusi' => empty($buyer['placeofbusiness']) ? '' : $buyer['placeofbusiness'],
                        'buyerType' => strval($buyer['type']),
                        'buyerCitizenship' => empty($buyer['citizineship']) ? '' : $buyer['citizineship'],
                        'buyerSector' => empty($buyer['sector']) ? '' : $buyer['sector'],
                        'buyerReferenceNo' => empty($buyer['referenceno']) ? '' : $buyer['referenceno'],
                        'nonResidentFlag' => empty($buyer['nonResidentFlag']) ? '0' : $buyer['nonResidentFlag'],
                        'deliveryTermsCode' => $effectiveDeliveryTermsCode
                    ),
                    'goodsDetails' => $t_goods,
                    'taxDetails' => $t_taxes,
                    'summary' => array(
                        'netAmount' => empty($t_summary[0]['netamount']) ? '0' : strval($t_summary[0]['netamount']),
                        'taxAmount' => empty($t_summary[0]['taxamount']) ? '0' : strval($t_summary[0]['taxamount']),
                        'grossAmount' => empty($t_summary[0]['grossamount']) ? '0' : strval($t_summary[0]['grossamount']),
                        'itemCount' => empty($t_summary[0]['itemcount']) ? '0' : strval($t_summary[0]['itemcount']),
                        'modeCode' => empty($invoicedetails['modecode']) ? '' : $invoicedetails['modecode'],
                        'remarks' => empty($invoicedetails['remarks']) ? '' : $invoicedetails['remarks'],
                        'qrCode' => ''
                    ),
                    'payWay' => $t_payments,
                    'extend' => array(
                        /**
                         * Author: frncslubanga@gmail.com
                         * Date: 2023-05-16
                         * Description: Resolve EFRIS error code 1404 - reasonCode:Invalid field value!
                         */
                        'reason' => trim($invoicedetails['invoicetype']) == '5' ? 'Credit Memo' : '',
                        'reasonCode' => trim($invoicedetails['invoicetype']) == '5' ? '103' : ''
                    ),
                    'importServicesSeller' => array(
                        'importBusinessName' => '',
                        'importEmailAddress' => '',
                        'importContactNumber' => '',
                        'importAddress' => '',
                        'importInvoiceDate' => '',
                        'importAttachmentName' => '',
                        'importAttachmentContent' => ''
                    ),
                    'airlineGoodsDetails' => $t_airlinegoods,
                    'buyerExtend' => array(
                        'propertyType' => '',
                        'district' => '',
                        'municipalityCounty' => '',
                        'divisionSubcounty' => '',
                        'town' => '',
                        'cellVillage' => '',
                        'effectiveRegistrationDate' => '',
                        'meterStatus' => ''
                    ),
                );
            }

            //print_r($invoice_u);

            $invoicePayloadAudit = array(
                'invoiceNo' => empty($invoice_u['basicInformation']['invoiceNo']) ? '' : $invoice_u['basicInformation']['invoiceNo'],
                'invoiceIndustryCode' => empty($invoice_u['basicInformation']['invoiceIndustryCode']) ? '' : $invoice_u['basicInformation']['invoiceIndustryCode'],
                'invoiceType' => empty($invoice_u['basicInformation']['invoiceType']) ? '' : $invoice_u['basicInformation']['invoiceType'],
                'buyerTin' => empty($invoice_u['buyerDetails']['buyerTin']) ? '' : $invoice_u['buyerDetails']['buyerTin'],
                'buyerType' => empty($invoice_u['buyerDetails']['buyerType']) ? '' : $invoice_u['buyerDetails']['buyerType'],
                'nonResidentFlag' => empty($invoice_u['buyerDetails']['nonResidentFlag']) ? '' : $invoice_u['buyerDetails']['nonResidentFlag'],
                'deliveryTermsCode' => empty($invoice_u['buyerDetails']['deliveryTermsCode']) ? '' : $invoice_u['buyerDetails']['deliveryTermsCode'],
                'goodsCount' => is_array($invoice_u['goodsDetails']) ? count($invoice_u['goodsDetails']) : 0,
                'taxCount' => is_array($invoice_u['taxDetails']) ? count($invoice_u['taxDetails']) : 0,
                'paymentCount' => is_array($invoice_u['payWay']) ? count($invoice_u['payWay']) : 0
            );

            if (!empty($invoice_u['goodsDetails'][0])) {
                $invoicePayloadAudit['firstGood'] = array(
                    'itemCode' => empty($invoice_u['goodsDetails'][0]['itemCode']) ? '' : $invoice_u['goodsDetails'][0]['itemCode'],
                    'hsCode' => empty($invoice_u['goodsDetails'][0]['hsCode']) ? '' : $invoice_u['goodsDetails'][0]['hsCode'],
                    'vatProjectId' => empty($invoice_u['goodsDetails'][0]['vatProjectId']) ? '' : $invoice_u['goodsDetails'][0]['vatProjectId'],
                    'pieceQty' => empty($invoice_u['goodsDetails'][0]['pieceQty']) ? '' : strval($invoice_u['goodsDetails'][0]['pieceQty']),
                    'pieceMeasureUnit' => empty($invoice_u['goodsDetails'][0]['pieceMeasureUnit']) ? '' : $invoice_u['goodsDetails'][0]['pieceMeasureUnit'],
                    'totalWeight' => empty($invoice_u['goodsDetails'][0]['totalWeight']) ? '' : strval($invoice_u['goodsDetails'][0]['totalWeight'])
                );
            }

            $this->logger->write("Utilities : uploadinvoice() : The invoice payload audit is " . json_encode($invoicePayloadAudit), 'r');

            $invoice_u = json_encode($invoice_u); //JSON-ifiy
            $invoice_u = base64_encode($invoice_u); //base64 encode
            $this->logger->write("Utilities : uploadinvoice() : The encoded invoice is " . $invoice_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $invoice_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);
            //$this->logger->write("Utilities : uploadinvoice() : The response is: " . $j_response, 'r');

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : uploadinvoice() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : uploadinvoice() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : uploadinvoice() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : uploadinvoice() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : uploadinvoice() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : uploadinvoice() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name downloadinvoice
     * @desc download an invoice from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $invoiceid int
     *
     */
    public function downloadinvoice($userid, $invoiceno)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : downloadinvoice() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloadinvoice() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : downloadinvoice() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloadinvoice() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim($invoiceno) == '' || empty($invoiceno)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : downloadinvoice() : The invoice is empty.", 'r');
        }


        try {
            $this->logger->write("Utilities : downloadinvoice() : Downloading credit note started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T108';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);


            $invoice_u = array(
                'invoiceNo' => $invoiceno
            );

            //print_r($invoice_u);

            $invoice_u = json_encode($invoice_u); //JSON-ifiy
            $invoice_u = base64_encode($invoice_u); //base64 encode
            $this->logger->write("Utilities : downloadinvoice() : The encoded invoice is " . $invoice_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $invoice_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : downloadinvoice() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : downloadinvoice() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : downloadinvoice() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : downloadinvoice() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : downloadinvoice() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : downloadinvoice() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    /**
     * @name checktaxpayertype
     * @desc Check whether the taxpayer is tax exempt/Deemed
     * @return JSON-encoded object
     * @param $userid int, $tin string, $commodity string
     *
     */
    public function checktaxpayertype($userid, $tin, $commodity)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : checktaxpayertype() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : checktaxpayertype() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : checktaxpayertype() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : checktaxpayertype() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : checktaxpayertype() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : checktaxpayertype() : The TIN is " . $tin, 'r');
        $this->logger->write("Utilities : checktaxpayertype() : The commodity is " . $commodity, 'r');

        if (trim($tin) == '' || empty($tin)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : checktaxpayertype() : The TIN is empty.", 'r');
        }

        if (trim($commodity) == '' || empty($commodity)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : checktaxpayertype() : The commodity is empty.", 'r');
        }

        try {
            $this->logger->write("Utilities : checktaxpayertype() : Checking the tax payer type started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T137';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);


            $taxpayer = array(
                'tin' => $tin,
                'commodityCategoryCode' => $commodity
            );

            //print_r($invoice_u);

            $taxpayer = json_encode($taxpayer); //JSON-ifiy
            $taxpayer = base64_encode($taxpayer); //base64 encode
            $this->logger->write("Utilities : checktaxpayertype() : The encoded tax payer is " . $taxpayer, 'r');

            $data = array(
                'data' => array(
                    'content' => $taxpayer,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : checktaxpayertype() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);
            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : checktaxpayertype() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : checktaxpayertype() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : checktaxpayertype() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : checktaxpayertype() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : checktaxpayertype() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    /**
     * @name downloadrefundinvoice
     * @desc download an invoice from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $refundInvoiceNo string
     *
     */
    public function downloadrefundinvoice($userid, $refundInvoiceNo)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : downloadrefundinvoice() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloadrefundinvoice() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : downloadrefundinvoice() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloadrefundinvoice() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : downloadrefundinvoice() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : downloadrefundinvoice() : The invoice no is " . $refundInvoiceNo, 'r');

        if (trim($refundInvoiceNo) == '' || empty($refundInvoiceNo)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : downloadrefundinvoice() : The invoice id is empty.", 'r');
        }

        try {
            $this->logger->write("Utilities : downloadrefundinvoice() : Downloading invoice started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T108';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);


            $invoice_u = array(
                'invoiceNo' => $refundInvoiceNo
            );

            //print_r($invoice_u);

            $invoice_u = json_encode($invoice_u); //JSON-ifiy
            $invoice_u = base64_encode($invoice_u); //base64 encode
            $this->logger->write("Utilities : downloadrefundinvoice() : The encoded invoice is " . $invoice_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $invoice_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : downloadrefundinvoice() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);
            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : downloadrefundinvoice() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : downloadrefundinvoice() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : downloadrefundinvoice() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : downloadrefundinvoice() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : downloadrefundinvoice() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name uploadcreditnote
     * @desc upload a credit/debit note to EFRIS
     * @return JSON-encoded object
     * @param $userid int, $creditnoteid int, $vatRegistered string
     *
     */
    public function uploadcreditnote($userid, $buyer, $creditnotedetails = null, $goods = null, $payments = null, $taxes = null)
    {
        // Backward compatibility: some controllers still call uploadcreditnote($userid, $creditnoteid, $vatRegistered).
        if (!is_array($creditnotedetails) || !is_array($goods) || !is_array($payments) || !is_array($taxes)) {
            $legacyCreditnoteId = is_numeric($buyer) ? (int)$buyer : 0;
            $legacyContext = $this->loadLegacyUploadContext('creditnote', $legacyCreditnoteId, $userid);
            if (!$legacyContext['ok']) {
                $this->logger->write('Utilities : uploadcreditnote() : Legacy compatibility load failed. ' . $legacyContext['message'], 'r');
                return json_encode(array('returnCode' => '999', 'returnMessage' => $legacyContext['message']));
            }

            $buyer = $legacyContext['buyer'];
            $creditnotedetails = $legacyContext['document'];
            $goods = $legacyContext['goods'];
            $payments = $legacyContext['payments'];
            $taxes = $legacyContext['taxes'];
        }

        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : uploadcreditnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : uploadcreditnote() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : uploadcreditnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : uploadcreditnote() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : uploadcreditnote() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : uploadcreditnote() : Uploading creditnote started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T110';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            //$creditnote = $creditnotedetails;

            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);

            $t_goods = array();
            $t_taxes = array();
            $t_payments = array();
            $t_summary = array();

            $netamount = 0;
            $taxamount = 0;
            $grossamount = 0;
            $itemcount = 0;

            // Delivery terms precedence for payload consistency: line override -> header -> buyer default -> empty.
            $headerDeliveryTermsCode = empty($creditnotedetails['deliveryTermsCode']) ? '' : trim((string)$creditnotedetails['deliveryTermsCode']);
            $buyerDeliveryTermsCode = empty($buyer['deliveryTermsCode']) ? '' : trim((string)$buyer['deliveryTermsCode']);
            $effectiveDeliveryTermsCode = $headerDeliveryTermsCode !== '' ? $headerDeliveryTermsCode : $buyerDeliveryTermsCode;

            try {
                //$temp = $goods;
                $this->logger->write("Utilities : uploadcreditnote() : The GOOD is " . $goods[0]['item'], 'r');

                $i = 0;
                foreach ($goods as $obj) {
                    $this->logger->write("Utilities : uploadcreditnote() : The unit price is " . round($obj['unitprice'], 8), 'r');
                    $this->logger->write("Utilities : uploadcreditnote() : The total is " . floor($obj['total'] * 100) / 100, 'r');
                    $this->logger->write("Utilities : uploadcreditnote() : The tax is " . floor($obj['tax'] * 100) / 100, 'r');

                    if ($obj['deemedflag'] == '1') {
                        $obj['item'] = $obj['item'] . " (Deemed)";

                        //Truncate
                        //$obj['unitprice'] = round($obj['unitprice'], 8);
                        $obj['total'] = floor($obj['total'] * 100) / 100;
                        $obj['tax'] = floor($obj['tax'] * 100) / 100;
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : floor($obj['discounttotal'] * 100) / 100;

                        //Ensure 2 decimal places
                        /*$obj['unitprice'] = $this->truncatenumber($obj['unitprice'], 8);
                         $obj['total'] = $this->truncatenumber($obj['total'], 4);
                         $obj['tax'] = $this->truncatenumber($obj['tax'], 4);
                         $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00')? NULL : $this->truncatenumber($obj['discounttotal'], 4);*/

                    } else {
                        //Round off
                        //$obj['unitprice'] = round($obj['unitprice'], 8);
                        $obj['total'] = round($obj['total'], 4);
                        $obj['tax'] = round($obj['tax'], 4);
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : round($obj['discounttotal'], 4);
                    }

                    //Ensure the right decimal places
                    $obj['qty'] = $this->truncatenumber($obj['qty'], 8);
                    $obj['unitprice'] = $this->truncatenumber($obj['unitprice'], 8);
                    $obj['total'] = $this->truncatenumber($obj['total'], 4);
                    $obj['tax'] = $this->truncatenumber($obj['tax'], 4);
                    $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : $this->truncatenumber($obj['discounttotal'], 4);

                    /**
                     * Author: Francis Lubanga <frncslubanga@gmail.com>
                     * Date: 2026-01-23
                     * Description: Add 3 decimal places to excise tax
                     */
                    // $obj['excisetax'] = $this->truncatenumber($obj['excisetax'], 4);
                    $obj['excisetax'] =  $obj['exciseflag'] == '1' ? $this->truncatenumber($obj['excisetax'], 4) : '';

                    $t_goods[] = array(
                        'item' => empty($obj['item']) ? '' : $obj['item'],
                        'itemCode' => empty($obj['itemcode']) ? '' : $obj['itemcode'],
                        'qty' => empty($obj['qty']) ? '' : '-' . strval($obj['qty']),
                        'unitOfMeasure' => empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'],
                        'unitPrice' => empty($obj['unitprice']) ? '' : strval($obj['unitprice']),
                        'total' => empty($obj['total']) ? '' : '-' . strval($obj['total']),
                        'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '' : number_format($obj['taxrate'], 4, '.', '')),
                        'tax' => empty($obj['tax']) ? '' : '-' . strval($obj['tax']),
                        'discountTotal' => (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? '' : strval($obj['discounttotal']),
                        /**
                         * Modification Date: 2023-05-16
                         * Modified By: Francis Lubanga
                         * Description: 1214 - goodsDetails-->discountTaxRate:If 'discountFlag' is '2', 'discountTaxRate' must be empty!Collection index:0
                         * */
                        'discountTaxRate' => (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00' || empty($obj['discountflag']) || trim($obj['discountflag']) == '2') ? '' : $obj['discounttaxrate'],
                        'orderNumber' => empty($obj['ordernumber']) ? strval($i) : strval($obj['ordernumber']),
                        'discountFlag' => empty($obj['discountflag']) ? '2' : $obj['discountflag'],
                        'deemedFlag' => empty($obj['deemedflag']) ? '2' : $obj['deemedflag'],
                        'exciseFlag' => empty($obj['exciseflag']) ? '2' : $obj['exciseflag'],
                        'categoryId' => empty($obj['categoryid']) ? '' : $obj['categoryid'],
                        'categoryName' => empty($obj['categoryname']) ? '' : $obj['categoryname'],
                        'goodsCategoryId' => empty($obj['goodscategoryid']) ? '' : $obj['goodscategoryid'],
                        'goodsCategoryName' => empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'],
                        'exciseRate' => empty($obj['exciserate']) ? '' : $obj['exciserate'],
                        'exciseRule' => empty($obj['exciserule']) ? '' : $obj['exciserule'],
                        'exciseTax' => empty($obj['excisetax']) || $obj['exciseflag'] == '2' ? '' : '-' . strval($obj['excisetax']),
                        'pack' => empty($obj['pack']) ? '' : $obj['pack'],
                        'stick' => empty($obj['stick']) ? '' : $obj['stick'],
                        'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                        'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                        'exciseRateName' => empty($obj['exciseratename']) ? '' : $obj['exciseratename'],
                        /**
                         * Modification Date: 2025-05-30
                         * Modified By: Francis Lubanga
                         * Description: Resolving error code 2857 - goodsDetails-->taxRate:If 'vatApplicableFlag' is '0', 'taxRate' must be '0'!Collection index:1 when sending VAT OUT OF SCOPE items
                         * */
                        'vatApplicableFlag' => (empty($obj['goodscategoryid']) || $obj['goodscategoryid'] !== '96010102') ? '1' : '0',
                        //'vatApplicableFlag' => empty($obj['vatApplicableFlag'])? '' : $obj['vatApplicableFlag'],
                        'deemedExemptCode' => empty($obj['deemedExemptCode']) ? '' : $obj['deemedExemptCode'],
                        'nonResidentFlag' => empty($obj['nonResidentFlag']) ? (empty($buyer['nonResidentFlag']) ? '0' : $buyer['nonResidentFlag']) : $obj['nonResidentFlag'],
                        'deliveryTermsCode' => empty($obj['deliveryTermsCode']) ? (empty($buyer['deliveryTermsCode']) ? (empty($creditnotedetails['deliveryTermsCode']) ? '' : $creditnotedetails['deliveryTermsCode']) : $buyer['deliveryTermsCode']) : $obj['deliveryTermsCode'],
                        'vatProjectId' => empty($obj['vatProjectId']) ? '' : $obj['vatProjectId'],
                        'vatProjectName' => empty($obj['vatProjectName']) ? '' : $obj['vatProjectName'],
                        'hsCode' => empty($obj['hsCode']) ? '' : $obj['hsCode'],
                        'hsName' => empty($obj['hsName']) ? '' : $obj['hsName'],
                        'totalWeight' => empty($obj['totalWeight']) ? '' : round($obj['totalWeight'], 4),
                        'pieceQty' => empty($obj['pieceQty']) ? '' : round((float)$obj['pieceQty'], 8),
                        'pieceMeasureUnit' => empty($obj['pieceMeasureUnit']) ? '' : $obj['pieceMeasureUnit']
                    );

                    $i = $i + 1;

                    $netamount = $netamount + $obj['total'];
                    $taxamount = $taxamount + $obj['tax'];

                    $grossamount = $grossamount + $obj['total'];
                    $itemcount = $itemcount + 1;
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploadcreditnote() : The operation to retrive the good details was not successful. The error messages is " . $e->getMessage(), 'r');
            }

            //var_dump($goods);
            $deemedflag = 'N';


            try {
                //$temp = $taxes;
                if ($this->vatRegistered == 'Y') {

                    //RESET THE SUMMARIES
                    $netamount = 0;
                    $taxamount = 0;
                    $grossamount = 0;
                    $itemcount = 0;

                    foreach ($taxes as $obj) {
                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : uploadinvoice() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : uploadinvoice() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : uploadinvoice() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }

                        if (strtoupper(trim($obj['taxcategory'])) == 'D: DEEMED (18%)') {
                            $obj['netamount'] = floor($obj['netamount'] * 100) / 100;
                            $obj['taxamount'] = floor($obj['taxamount'] * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 4, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 4, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 4, '.', '');

                            $deemedflag = 'Y';
                        } else {
                            $obj['netamount'] = round($obj['netamount'], 4);
                            $obj['taxamount'] = round($obj['taxamount'], 4);
                            //$obj['grossamount'] = round($obj['grossamount'], 4);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $deemedflag = 'N';
                        }

                        $t_taxes[] = array(
                            'taxCategoryCode' => empty($obj['taxcategoryCode']) ? '' : $obj['taxcategoryCode'],
                            'taxCategory' => empty($obj['taxcategory']) ? '' : $obj['taxcategory'],
                            'netAmount' => empty($obj['netamount']) ? '0' : '-' . strval($obj['netamount']),
                            'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '0' : number_format($obj['taxrate'], 4, '.', '')),
                            'taxAmount' => empty($obj['taxamount']) ? '0' : '-' . strval($obj['taxamount']),
                            'grossAmount' => empty($obj['grossamount']) ? '0' : '-' . strval($obj['grossamount']),
                            'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                            'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                            'taxRateName' => empty($obj['taxratename']) ? '' : $obj['taxratename']
                        );

                        /**
                         * Author: frncslubanga@gmail.com
                         * Date: 2026-01-11
                         * Description: Ignore the NET amount on the execise duty
                         */
                        // $netamount = $netamount + $obj['netamount'];

                        if (trim($obj['taxcategory']) != 'E: Excise Duty') {
                            $netamount = $netamount + $obj['netamount'];
                        } else {
                            $netamount = $netamount - $obj['taxamount'];
                        }

                        $taxamount = $taxamount + $obj['taxamount'];

                        /**
                         * Author: frncslubanga@gmail.com
                         * Date: 2026-01-11
                         * Description: Ignore the GROSS amount on the execise duty
                         */
                        // $grossamount = $grossamount + $obj['grossamount'];

                        if (trim($obj['taxcategory']) != 'E: Excise Duty') {
                            $grossamount = $grossamount + $obj['grossamount'];
                        }

                        $itemcount = $itemcount + 1;
                    }
                }


            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploadcreditnote() : The operation to retrive the tax details was not successful. The error messages is " . $e->getMessage(), 'r');
            }

            $t_summary[] = array(
                'netamount' => strtoupper(trim($deemedflag)) == 'N' ? round($netamount, 4) : number_format((floor($netamount * 100) / 100), 4, '.', ''),
                'taxamount' => strtoupper(trim($deemedflag)) == 'N' ? round($taxamount, 4) : 0,
                'grossamount' => strtoupper(trim($deemedflag)) == 'N' ? round($grossamount, 4) : number_format((floor($netamount * 100) / 100), 4, '.', ''),
                'itemcount' => sizeof($goods)
            );

            try {
                //$temp = $taxes;

                foreach ($payments as $obj) {
                    $t_payments[] = array(
                        'paymentMode' => empty($obj['paymentmode']) ? '' : $obj['paymentmode'],
                        'paymentAmount' => empty($obj['paymentamount']) ? '' : strval(round($obj['paymentamount'], 4)),
                        'orderNumber' => empty($obj['ordernumber']) ? '' : $obj['ordernumber']
                    );
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploadcreditnote() : The operation to retrive the payment details was not successful. The error messages is " . $e->getMessage(), 'r');
            }


            //return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $creditnote_u = array(
                'currency' => $creditnotedetails['currency'],
                'source' => empty($creditnotedetails['datasource']) ? '' : strval($creditnotedetails['datasource']),
                'oriInvoiceId' => $creditnotedetails['oriinvoiceid'],
                'oriInvoiceNo' => $creditnotedetails['oriinvoiceno'],
                'reasonCode' => $creditnotedetails['reasoncode'],
                'reason' => empty($creditnotedetails['reason']) ? '' : $creditnotedetails['reason'],
                'applicationTime' => empty($creditnotedetails['applicationtime']) ? date('Y-m-d H:i:s') : $creditnotedetails['applicationtime'],
                'invoiceApplyCategoryCode' => strval($creditnotedetails['invoiceapplycategorycode']),
                'contactName' => empty($buyer['legalname']) ? '' : $buyer['legalname'],
                'contactMobileNum' => empty($buyer['mobilephone']) ? '' : $buyer['mobilephone'],
                'contactEmail' => empty($buyer['emailaddress']) ? '' : $buyer['emailaddress'],
                'sellersReferenceNo' => empty($creditnotedetails['erpinvoiceid']) ? '' : $creditnotedetails['erpinvoiceid'],
                'goodsDetails' => $t_goods,
                'taxDetails' => $t_taxes,
                'summary' => array(
                    'netAmount' => empty($t_summary[0]['netamount']) ? '0' : '-' . strval($t_summary[0]['netamount']),
                    'taxAmount' => empty($t_summary[0]['taxamount']) ? '0' : '-' . strval($t_summary[0]['taxamount']),
                    'grossAmount' => empty($t_summary[0]['grossamount']) ? '0' : '-' . strval($t_summary[0]['grossamount']),
                    'itemCount' => empty($t_summary[0]['itemcount']) ? '0' : strval($t_summary[0]['itemcount']),
                    'modeCode' => empty($creditnotedetails['modecode']) ? '' : $creditnotedetails['modecode'],
                    'remarks' => empty($creditnotedetails['remarks']) ? '' : $creditnotedetails['remarks'],
                    'qrCode' => ''
                ),
                'payWay' => $t_payments
            );


            //print_r($creditnote_u);

            $creditnote_u = json_encode($creditnote_u); //JSON-ifiy
            $creditnote_u = base64_encode($creditnote_u); //base64 encode
            $this->logger->write("Utilities : uploadcreditnote() : The encoded creditnote is " . $creditnote_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $creditnote_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : uploadcreditnote() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : uploadcreditnote() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : uploadcreditnote() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : uploadcreditnote() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : uploadcreditnote() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : uploadcreditnote() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name downloadcreditnote
     * @desc download a credit/debit note from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $creditnoteid int
     *
     */
    public function downloadcreditnote($userid, $reference)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : downloadcreditnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloadcreditnote() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : downloadcreditnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloadcreditnote() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : downloadcreditnote() : The user id is " . $userid, 'r');

        if (trim($reference) == '' || empty($reference)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : downloadcreditnote() : The reference is empty.", 'r');
        }

        try {
            $this->logger->write("Utilities : downloadcreditnote() : Downloading credit note started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T111';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $creditdebitnote_u = array(
                'referenceNo' => $reference,
                'oriInvoiceNo' => '',
                'invoiceNo' => '',
                'combineKeywords' => '',
                'approveStatus' => '',
                'queryType' => '1',
                'invoiceApplyCategoryCode' => '101', /*101-Credit Note*/
                'startDate' => '',
                'endDate' => '',
                'pageNo' => '1',
                'pageSize' => '10',
            );

            //print_r($creditdebitnote_u);

            $creditdebitnote_u = json_encode($creditdebitnote_u); //JSON-ifiy
            $creditdebitnote_u = base64_encode($creditdebitnote_u); //base64 encode
            $this->logger->write("Utilities : downloadcreditnote() : The encoded invoice is " . $creditdebitnote_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $creditdebitnote_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : downloadcreditnote() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : downloadcreditnote() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : downloadcreditnote() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : downloadcreditnote() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : downloadcreditnote() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : downloadcreditnote() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name downloaddebitnote
     * @desc download a debit note from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $debitnoteid int
     *
     */
    public function downloaddebitnote($userid, $id)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : downloaddebitnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloaddebitnote() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : downloaddebitnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloaddebitnote() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : downloaddebitnote() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : downloaddebitnote() : The debitnote id is " . $id, 'r');

        if (trim($id) == '' || empty($id)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : downloaddebitnote() : The debitnote id is empty.", 'r');
        }

        try {
            $this->logger->write("Utilities : downloaddebitnote() : Downloading debitnote started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T106';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $debitnote = new debitnotes($this->db);
            $debitnote->getByID($id);


            $debitnote_u = array(
                'oriInvoiceNo' => $debitnote->oriinvoiceno,
                'invoiceNo' => empty($debitnote->debitnoteno) ? '' : $debitnote->debitnoteno,
                'deviceNo' => '',
                'buyerTin' => '',
                'buyerNinBrn' => '',
                'buyerLegalName' => '',
                'combineKeywords' => '',
                'invoiceType' => strval($debitnote->invoicetype),
                'invoiceKind' => strval($debitnote->invoicekind),
                'isInvalid' => '0',
                'isRefund' => '',
                'startDate' => '',
                'endDate' => '',
                'pageNo' => '1',
                'pageSize' => '10',
                'referenceNo' => ''
            );

            //print_r($debitnote_u);

            $debitnote_u = json_encode($debitnote_u); //JSON-ifiy
            $debitnote_u = base64_encode($debitnote_u); //base64 encode
            $this->logger->write("Utilities : downloaddebitnote() : The encoded invoice is " . $debitnote_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $debitnote_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : downloaddebitnote() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : downloaddebitnote() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : downloaddebitnote() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : downloaddebitnote() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : downloaddebitnote() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : downloaddebitnote() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name cancelcreditnote
     * @desc cancel a credit note from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $creditnoteid int, $reasonCode string, $reason string
     *
     */
    public function cancelcreditnote($userid, $oriInvoiceId, $invoiceNo, $reason, $reasonCode, $invoiceApplyCategoryCode)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : cancelcreditnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : cancelcreditnote() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : cancelcreditnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : cancelcreditnote() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : cancelcreditnote() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : cancelcreditnote() : The credit note number is " . $invoiceNo, 'r');

        if (trim($invoiceNo) == '' || empty($invoiceNo)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : cancelcreditnote() : The invoice No is empty.", 'r');
        }

        try {
            $this->logger->write("Utilities : cancelcreditnote() : Cancellation of the credit note started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T120';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);


            $creditdebitnote_u = array(
                'oriInvoiceId' => strval($oriInvoiceId),
                'invoiceNo' => strval($invoiceNo),
                'reason' => strval($reason),
                'reasonCode' => strval($reasonCode),
                'invoiceApplyCategoryCode' => strval($invoiceApplyCategoryCode)
            );


            //print_r($creditdebitnote_u);

            $creditdebitnote_u = json_encode($creditdebitnote_u); //JSON-ifiy
            $creditdebitnote_u = base64_encode($creditdebitnote_u); //base64 encode
            $this->logger->write("Utilities : cancelcreditnote() : The encoded invoice is " . $creditdebitnote_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $creditdebitnote_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : cancelcreditnote() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : cancelcreditnote() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : cancelcreditnote() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : cancelcreditnote() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : cancelcreditnote() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : cancelcreditnote() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name voidcreditnote
     * @desc void a credit note from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $creditnoteid int
     *
     */
    public function voidcreditnote($userid, $applicationid, $invoiceno, $referenceno)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : voidcreditnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : voidcreditnote() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : voidcreditnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : voidcreditnote() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : voidcreditnote() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : voidcreditnote() : The credit note invoice number is " . $invoiceno, 'r');

        if (trim($invoiceno) == '' || empty($invoiceno)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : voidcreditnote() : The creditdebitnote id is empty.", 'r');
        }

        try {
            $this->logger->write("Utilities : voidcreditnote() : Uploading creditdebitnote started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T120';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);


            $creditdebitnote_u = array(
                'businessKey' => strval($applicationid),
                'referenceNo' => strval($referenceno)
            );


            //print_r($creditdebitnote_u);

            $creditdebitnote_u = json_encode($creditdebitnote_u); //JSON-ifiy
            $creditdebitnote_u = base64_encode($creditdebitnote_u); //base64 encode
            $this->logger->write("Utilities : voidcreditnote() : The encoded invoice is " . $creditdebitnote_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $creditdebitnote_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : voidcreditnote() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : voidcreditnote() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : voidcreditnote() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : voidcreditnote() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : voidcreditnote() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : voidcreditnote() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name uploaddebitnote
     * @desc Upload an debitnote to EFRIS
     * @return JSON-encoded object
     * @param $userid int, $debitnoteid int, $vatRegistered string
     *
     */
    public function uploaddebitnote($userid, $branchuraid, $buyer = null, $debitnotedetails = null, $goods = null, $payments = null, $taxes = null)
    {
        // Backward compatibility: some controllers still call uploaddebitnote($userid, $debitnoteid, $vatRegistered).
        if (!is_array($debitnotedetails) || !is_array($goods) || !is_array($payments) || !is_array($taxes)) {
            $legacyDebitnoteId = is_numeric($branchuraid) ? (int)$branchuraid : 0;
            $legacyContext = $this->loadLegacyUploadContext('debitnote', $legacyDebitnoteId, $userid);
            if (!$legacyContext['ok']) {
                $this->logger->write('Utilities : uploaddebitnote() : Legacy compatibility load failed. ' . $legacyContext['message'], 'r');
                return json_encode(array('returnCode' => '999', 'returnMessage' => $legacyContext['message']));
            }

            $branchuraid = $legacyContext['branchuraid'];
            $buyer = $legacyContext['buyer'];
            $debitnotedetails = $legacyContext['document'];
            $goods = $legacyContext['goods'];
            $payments = $legacyContext['payments'];
            $taxes = $legacyContext['taxes'];
        }

        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : uploaddebitnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : uploaddebitnote() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : uploaddebitnote() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : uploaddebitnote() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : uploaddebitnote() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : uploaddebitnote() : Uploading invoice started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T109';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            //$invoice = $debitnotedetails;

            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);

            // Delivery terms precedence: header value first, then buyer default.
            $headerDeliveryTermsCode = '';
            if (!empty($debitnotedetails['deliveryTermsCode'])) {
                $headerDeliveryTermsCode = trim((string)$debitnotedetails['deliveryTermsCode']);
            } elseif (!empty($debitnotedetails['deliverytermscode'])) {
                $headerDeliveryTermsCode = trim((string)$debitnotedetails['deliverytermscode']);
            }

            $buyerDeliveryTermsCode = '';
            if (!empty($buyer['deliveryTermsCode'])) {
                $buyerDeliveryTermsCode = trim((string)$buyer['deliveryTermsCode']);
            } elseif (!empty($buyer['deliverytermscode'])) {
                $buyerDeliveryTermsCode = trim((string)$buyer['deliverytermscode']);
            }

            $effectiveDeliveryTermsCode = $headerDeliveryTermsCode !== '' ? $headerDeliveryTermsCode : $buyerDeliveryTermsCode;

            $t_goods = array();
            $t_taxes = array();
            $t_payments = array();
            $t_summary = array();

            $netamount = 0;
            $taxamount = 0;
            $grossamount = 0;
            $itemcount = 0;

            try {
                //$temp = $goods;
                $this->logger->write("Utilities : uploaddebitnote() : The GOOD is " . $goods[0]['item'], 'r');

                $i = 0;
                foreach ($goods as $obj) {
                    $this->logger->write("Utilities : uploaddebitnote() : The unit price is " . round($obj['unitprice'], 8), 'r');
                    $this->logger->write("Utilities : uploaddebitnote() : The total is " . floor($obj['total'] * 100) / 100, 'r');
                    $this->logger->write("Utilities : uploaddebitnote() : The tax is " . floor($obj['tax'] * 100) / 100, 'r');

                    if ($obj['deemedflag'] == '1') {
                        $obj['item'] = $obj['item'] . " (Deemed)";

                        //Truncate
                        //$obj['unitprice'] = round($obj['unitprice'], 8);
                        $obj['total'] = floor($obj['total'] * 100) / 100;
                        $obj['tax'] = floor($obj['tax'] * 100) / 100;
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : floor($obj['discounttotal'] * 100) / 100;

                        //Ensure 2 decimal places
                        /*$obj['unitprice'] = $this->truncatenumber($obj['unitprice'], 8);
                         $obj['total'] = $this->truncatenumber($obj['total'], 2);
                         $obj['tax'] = $this->truncatenumber($obj['tax'], 2);
                         $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00')? NULL : $this->truncatenumber($obj['discounttotal'], 2);*/

                    } else {
                        //Round off
                        //$obj['unitprice'] = round($obj['unitprice'], 8);
                        $obj['total'] = round($obj['total'], 4);
                        $obj['tax'] = round($obj['tax'], 4);
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : round($obj['discounttotal'], 4);
                    }

                    //Ensure the right decimal places
                    $obj['qty'] = $this->truncatenumber($obj['qty'], 8);
                    $obj['unitprice'] = $this->truncatenumber($obj['unitprice'], 8);
                    $obj['total'] = $this->truncatenumber($obj['total'], 4);
                    $obj['tax'] = $this->truncatenumber($obj['tax'], 4);
                    $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : $this->truncatenumber($obj['discounttotal'], 4);


                    $t_goods[] = array(
                        'item' => empty($obj['item']) ? '' : $obj['item'],
                        'itemCode' => empty($obj['itemcode']) ? '' : $obj['itemcode'],
                        'qty' => empty($obj['qty']) ? '' : strval($obj['qty']),
                        'unitOfMeasure' => empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'],
                        'unitPrice' => empty($obj['unitprice']) ? '' : strval($obj['unitprice']),
                        'total' => empty($obj['total']) ? '' : strval($obj['total']),
                        'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '' : number_format($obj['taxrate'], 4, '.', '')),
                        'tax' => empty($obj['tax']) ? '' : strval($obj['tax']),
                        'discountTotal' => (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? '' : strval($obj['discounttotal']),
                        'discountTaxRate' => (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00') ? '' : $obj['discounttaxrate'],
                        'orderNumber' => strval($i),
                        'discountFlag' => empty($obj['discountflag']) ? '2' : $obj['discountflag'],
                        'deemedFlag' => empty($obj['deemedflag']) ? '2' : $obj['deemedflag'],
                        'exciseFlag' => empty($obj['exciseflag']) ? '2' : $obj['exciseflag'],
                        'categoryId' => empty($obj['categoryid']) ? '' : $obj['categoryid'],
                        'categoryName' => empty($obj['categoryname']) ? '' : $obj['categoryname'],
                        'goodsCategoryId' => empty($obj['goodscategoryid']) ? '' : $obj['goodscategoryid'],
                        'goodsCategoryName' => empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'],
                        'exciseRate' => empty($obj['exciserate']) ? '' : $obj['exciserate'],
                        'exciseRule' => empty($obj['exciserule']) ? '' : $obj['exciserule'],
                        'exciseTax' => empty($obj['excisetax']) ? '' : $obj['excisetax'],
                        'pack' => empty($obj['pack']) ? '' : $obj['pack'],
                        'stick' => empty($obj['stick']) ? '' : $obj['stick'],
                        'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                        'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                        'exciseRateName' => empty($obj['exciseratename']) ? '' : $obj['exciseratename'],
                        'nonResidentFlag' => empty($obj['nonResidentFlag']) ? (empty($buyer['nonResidentFlag']) ? '0' : $buyer['nonResidentFlag']) : $obj['nonResidentFlag'],
                        'deliveryTermsCode' => empty($obj['deliveryTermsCode']) ? $effectiveDeliveryTermsCode : $obj['deliveryTermsCode'],
                        'vatProjectId' => empty($obj['vatProjectId']) ? '' : $obj['vatProjectId'],
                        'vatProjectName' => empty($obj['vatProjectName']) ? '' : $obj['vatProjectName'],
                        'totalWeight' => empty($obj['totalWeight']) ? '' : round($obj['totalWeight'], 4),
                        'pieceQty' => empty($obj['pieceQty']) ? '' : round((float)$obj['pieceQty'], 8),
                        'pieceMeasureUnit' => empty($obj['pieceMeasureUnit']) ? '' : $obj['pieceMeasureUnit']
                    );

                    $i = $i + 1;

                    //If there is a discount, add a discount line below the item
                    if ($obj['discounttotal'] < 0) {
                        $t_goods[] = array(
                            'item' => empty($obj['item']) ? '' : $obj['item'] . " (Discount)",
                            'itemCode' => empty($obj['itemcode']) ? '' : $obj['itemcode'],
                            'qty' => '',
                            'unitOfMeasure' => '',
                            'unitPrice' => '',
                            'total' => empty($obj['discounttotal']) ? '' : strval($obj['discounttotal']),
                            'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '' : $obj['taxrate']),
                            /**
                             * Modification Date: 2020-11-15
                             * Modified By: Francis Lubanga
                             * Description: Resolving error code 1200 - goodsDetails-->tax:cannot be empty!Collection index:1
                             * Modification Date: 2021-01-26
                             * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                             * */
                            //'tax' => '',
                            'tax' => strval(number_format((($obj['discounttotal'] / ($obj['taxrate'] + 1)) * $obj['taxrate']), 4, '.', '')),
                            'discountTotal' => '',
                            'discountTaxRate' => empty($obj['discounttaxrate']) ? '' : $obj['discounttaxrate'],
                            'orderNumber' => strval($i),
                            'discountFlag' => '0',
                            'deemedFlag' => empty($obj['deemedflag']) ? '2' : $obj['deemedflag'],
                            'exciseFlag' => empty($obj['exciseflag']) ? '2' : $obj['exciseflag'],
                            'categoryId' => empty($obj['categoryid']) ? '' : $obj['categoryid'],
                            'categoryName' => empty($obj['categoryname']) ? '' : $obj['categoryname'],
                            'goodsCategoryId' => empty($obj['goodscategoryid']) ? '' : $obj['goodscategoryid'],
                            'goodsCategoryName' => empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'],
                            'exciseRate' => empty($obj['exciserate']) ? '' : $obj['exciserate'],
                            'exciseRule' => empty($obj['exciserule']) ? '' : $obj['exciserule'],
                            'exciseTax' => empty($obj['excisetax']) ? '' : $obj['excisetax'],
                            'pack' => empty($obj['pack']) ? '' : $obj['pack'],
                            'stick' => empty($obj['stick']) ? '' : $obj['stick'],
                            'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                            'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                            'exciseRateName' => empty($obj['exciseratename']) ? '' : $obj['exciseratename'],
                            'nonResidentFlag' => empty($obj['nonResidentFlag']) ? (empty($buyer['nonResidentFlag']) ? '0' : $buyer['nonResidentFlag']) : $obj['nonResidentFlag'],
                            'deliveryTermsCode' => empty($obj['deliveryTermsCode']) ? $effectiveDeliveryTermsCode : $obj['deliveryTermsCode'],
                            'vatProjectId' => empty($obj['vatProjectId']) ? '' : $obj['vatProjectId'],
                            'vatProjectName' => empty($obj['vatProjectName']) ? '' : $obj['vatProjectName'],
                            'totalWeight' => empty($obj['totalWeight']) ? '' : round($obj['totalWeight'], 4),
                            'pieceQty' => empty($obj['pieceQty']) ? '' : round((float)$obj['pieceQty'], 8),
                            'pieceMeasureUnit' => empty($obj['pieceMeasureUnit']) ? '' : $obj['pieceMeasureUnit']
                        );

                        $i = $i + 1;
                    }

                    $netamount = $netamount + $obj['total'];
                    $taxamount = $taxamount + $obj['tax'];

                    $grossamount = $grossamount + $obj['total'];
                    $itemcount = $itemcount + 1;
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploaddebitnote() : The operation to retrive the good details was not successful. The error messages is " . $e->getMessage(), 'r');
            }

            //var_dump($goods);
            $deemedflag = 'N';


            try {
                //$temp = $taxes;
                if ($this->vatRegistered == 'Y') {

                    //RESET THE SUMMARIES
                    $netamount = 0;
                    $taxamount = 0;
                    $grossamount = 0;
                    $itemcount = 0;

                    foreach ($taxes as $obj) {
                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : uploadinvoice() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : uploadinvoice() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : uploadinvoice() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }

                        if (strtoupper(trim($obj['taxcategory'])) == 'D: DEEMED (18%)') {
                            $obj['netamount'] = floor($obj['netamount'] * 100) / 100;
                            $obj['taxamount'] = floor($obj['taxamount'] * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 4, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 4, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 4, '.', '');

                            $deemedflag = 'Y';
                        } else {
                            $obj['netamount'] = round($obj['netamount'], 4);
                            $obj['taxamount'] = round($obj['taxamount'], 4);
                            //$obj['grossamount'] = round($obj['grossamount'], 4);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $deemedflag = 'N';
                        }

                        $t_taxes[] = array(
                            'taxCategoryCode' => empty($obj['taxcategoryCode']) ? '' : $obj['taxcategoryCode'],
                            'taxCategory' => empty($obj['taxcategory']) ? '' : $obj['taxcategory'],
                            'netAmount' => empty($obj['netamount']) ? '0' : strval($obj['netamount']),
                            'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '0' : number_format($obj['taxrate'], 4, '.', '')),
                            'taxAmount' => empty($obj['taxamount']) ? '0' : strval($obj['taxamount']),
                            'grossAmount' => empty($obj['grossamount']) ? '0' : strval($obj['grossamount']),
                            'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                            'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                            'taxRateName' => empty($obj['taxratename']) ? '' : $obj['taxratename']
                        );

                        $netamount = $netamount + $obj['netamount'];
                        $taxamount = $taxamount + $obj['taxamount'];

                        $grossamount = $grossamount + $obj['grossamount'];
                        $itemcount = $itemcount + 1;
                    }
                }


            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploaddebitnote() : The operation to retrive the tax details was not successful. The error messages is " . $e->getMessage(), 'r');
            }

            $t_summary[] = array(
                'netamount' => strtoupper(trim($deemedflag)) == 'N' ? round($netamount, 4) : number_format((floor($netamount * 100) / 100), 4, '.', ''),
                'taxamount' => strtoupper(trim($deemedflag)) == 'N' ? round($taxamount, 4) : 0,
                'grossamount' => strtoupper(trim($deemedflag)) == 'N' ? round($grossamount, 4) : number_format((floor($netamount * 100) / 100), 4, '.', ''),
                'itemcount' => $itemcount
            );

            try {
                //$temp = $taxes;

                foreach ($payments as $obj) {
                    $t_payments[] = array(
                        'paymentMode' => empty($obj['paymentmode']) ? '' : $obj['paymentmode'],
                        'paymentAmount' => empty($obj['paymentamount']) ? '' : strval(round($obj['paymentamount'], 4)),
                        'orderNumber' => empty($obj['ordernumber']) ? '' : $obj['ordernumber']
                    );
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploaddebitnote() : The operation to retrive the payment details was not successful. The error messages is " . $e->getMessage(), 'r');
            }


            //return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));

            $debitSellerReferenceNo = '';
            if (!empty($debitnotedetails['erpdebitnoteno'])) {
                $debitSellerReferenceNo = trim((string)$debitnotedetails['erpdebitnoteno']);
            } elseif (!empty($debitnotedetails['id'])) {
                $debitSellerReferenceNo = strval($debitnotedetails['id']);
            }

            $invoice_u = array(
                'sellerDetails' => array(
                    'tin' => empty($org->tin) ? '' : $org->tin,
                    'ninBrn' => empty($org->ninbrn) ? '' : addslashes($org->ninbrn),
                    'legalName' => empty($org->legalname) ? '' : addslashes($org->legalname),
                    'businessName' => empty($org->businessname) ? '' : addslashes($org->businessname),
                    'address' => empty($org->address) ? '' : addslashes($org->address),
                    'mobilePhone' => empty($org->mobilephone) ? '' : $org->mobilephone,
                    'linePhone' => empty($org->linephone) ? '' : $org->linephone,
                    'emailAddress' => empty($org->emailaddress) ? '' : addslashes($org->emailaddress),
                    'placeOfBusiness' => empty($org->placeofbusiness) ? '' : addslashes($org->placeofbusiness),
                    'referenceNo' => $debitSellerReferenceNo,
                    'branchId' => empty($branchuraid) ? $devicedetails->branchId : $branchuraid,
                    'isCheckReferenceNo' => '0'
                ),
                'basicInformation' => array(
                    'invoiceNo' => empty($debitnotedetails['erpinvoiceid']) ? '' : $debitnotedetails['erpinvoiceid'],
                    'antifakeCode' => empty($debitnotedetails['antifakecode']) ? '' : $debitnotedetails['antifakecode'],
                    'deviceNo' => $debitnotedetails['deviceno'],
                    'issuedDate' => date('Y-m-d H:i:s'),
                    'operator' => $debitnotedetails['operator'],
                    'currency' => $debitnotedetails['currency'],
                    'oriInvoiceId' => empty($debitnotedetails['oriinvoiceid']) ? '' : $debitnotedetails['oriinvoiceid'],
                    'invoiceType' => empty($debitnotedetails['invoicetype']) ? '' : strval($debitnotedetails['invoicetype']),
                    'invoiceKind' => empty($debitnotedetails['invoicekind']) ? '' : strval($debitnotedetails['invoicekind']),
                    'dataSource' => empty($debitnotedetails['datasource']) ? '' : strval($debitnotedetails['datasource']),
                    'invoiceIndustryCode' => empty($debitnotedetails['invoiceindustrycode']) ? '' : strval($debitnotedetails['invoiceindustrycode']),
                    'vatProjectId' => empty($debitnotedetails['vatProjectId']) ? '' : $debitnotedetails['vatProjectId'],
                    'vatProjectName' => empty($debitnotedetails['vatProjectName']) ? '' : $debitnotedetails['vatProjectName'],
                    'deliveryTermsCode' => $effectiveDeliveryTermsCode,
                    'isBatch' => empty($debitnotedetails['isbatch']) ? '' : $debitnotedetails['isbatch']
                ),
                'buyerDetails' => array(
                    'buyerTin' => empty($buyer['tin']) ? '' : $buyer['tin'],
                    'buyerNinBrn' => empty($buyer['ninbrn']) ? '' : $buyer['ninbrn'],
                    'buyerPassportNum' => empty($buyer['PassportNum']) ? '' : $buyer['PassportNum'],
                    'buyerLegalName' => empty($buyer['legalname']) ? '' : $buyer['legalname'],
                    'buyerBusinessName' => empty($buyer['businessname']) ? '' : $buyer['businessname'],
                    'buyerAddress' => empty($buyer['address']) ? '' : $buyer['address'],
                    'buyerEmail' => empty($buyer['emailaddress']) ? '' : $buyer['emailaddress'],
                    'buyerMobilePhone' => empty($buyer['mobilephone']) ? '' : $buyer['mobilephone'],
                    'buyerLinePhone' => empty($buyer['linephone']) ? '' : $buyer['linephone'],
                    'buyerPlaceOfBusi' => empty($buyer['placeofbusiness']) ? '' : $buyer['placeofbusiness'],
                    'buyerType' => strval($buyer['type']),
                    'buyerCitizenship' => empty($buyer['citizineship']) ? '' : $buyer['citizineship'],
                    'buyerSector' => empty($buyer['sector']) ? '' : $buyer['sector'],
                    'buyerReferenceNo' => empty($buyer['referenceno']) ? '' : $buyer['referenceno'],
                    'nonResidentFlag' => empty($buyer['nonResidentFlag']) ? '0' : $buyer['nonResidentFlag'],
                    'deliveryTermsCode' => $effectiveDeliveryTermsCode
                ),
                'goodsDetails' => $t_goods,
                'taxDetails' => $t_taxes,
                'summary' => array(
                    'netAmount' => empty($t_summary[0]['netamount']) ? '0' : strval($t_summary[0]['netamount']),
                    'taxAmount' => empty($t_summary[0]['taxamount']) ? '0' : strval($t_summary[0]['taxamount']),
                    'grossAmount' => empty($t_summary[0]['grossamount']) ? '0' : strval($t_summary[0]['grossamount']),
                    'itemCount' => empty($t_summary[0]['itemcount']) ? '0' : strval($t_summary[0]['itemcount']),
                    'modeCode' => empty($debitnotedetails['modecode']) ? '' : $debitnotedetails['modecode'],
                    'remarks' => empty($debitnotedetails['remarks']) ? '' : $debitnotedetails['remarks'],
                    'qrCode' => ''
                ),
                'payWay' => $t_payments,
                'extend' => array(
                    'reason' => empty($debitnotedetails['reason']) ? '' : $debitnotedetails['reason'],
                    'reasonCode' => empty($debitnotedetails['reasoncode']) ? '' : strval($debitnotedetails['reasoncode'])
                ),
                'importServicesSeller' => array(
                    'importBusinessName' => '',
                    'importEmailAddress' => '',
                    'importContactNumber' => '',
                    'importAddres' => '',
                    'importInvoiceDate' => '',
                    'importAttachmentName' => '',
                    'importAttachmentContent' => ''
                )
            );

            //print_r($invoice_u);

            $debitnotePayloadAudit = array(
                'invoiceNo' => empty($invoice_u['basicInformation']['invoiceNo']) ? '' : $invoice_u['basicInformation']['invoiceNo'],
                'sellerReferenceNo' => empty($invoice_u['sellerDetails']['referenceNo']) ? '' : $invoice_u['sellerDetails']['referenceNo'],
                'invoiceIndustryCode' => empty($invoice_u['basicInformation']['invoiceIndustryCode']) ? '' : $invoice_u['basicInformation']['invoiceIndustryCode'],
                'invoiceType' => empty($invoice_u['basicInformation']['invoiceType']) ? '' : $invoice_u['basicInformation']['invoiceType'],
                'buyerTin' => empty($invoice_u['buyerDetails']['buyerTin']) ? '' : $invoice_u['buyerDetails']['buyerTin'],
                'buyerType' => empty($invoice_u['buyerDetails']['buyerType']) ? '' : $invoice_u['buyerDetails']['buyerType'],
                'nonResidentFlag' => empty($invoice_u['buyerDetails']['nonResidentFlag']) ? '' : $invoice_u['buyerDetails']['nonResidentFlag'],
                'deliveryTermsCode' => empty($invoice_u['buyerDetails']['deliveryTermsCode']) ? '' : $invoice_u['buyerDetails']['deliveryTermsCode'],
                'goodsCount' => is_array($invoice_u['goodsDetails']) ? count($invoice_u['goodsDetails']) : 0,
                'taxCount' => is_array($invoice_u['taxDetails']) ? count($invoice_u['taxDetails']) : 0,
                'paymentCount' => is_array($invoice_u['payWay']) ? count($invoice_u['payWay']) : 0
            );

            if (!empty($invoice_u['goodsDetails'][0])) {
                $debitnotePayloadAudit['firstGood'] = array(
                    'itemCode' => empty($invoice_u['goodsDetails'][0]['itemCode']) ? '' : $invoice_u['goodsDetails'][0]['itemCode'],
                    'vatProjectId' => empty($invoice_u['goodsDetails'][0]['vatProjectId']) ? '' : $invoice_u['goodsDetails'][0]['vatProjectId'],
                    'nonResidentFlag' => empty($invoice_u['goodsDetails'][0]['nonResidentFlag']) ? '' : $invoice_u['goodsDetails'][0]['nonResidentFlag'],
                    'deliveryTermsCode' => empty($invoice_u['goodsDetails'][0]['deliveryTermsCode']) ? '' : $invoice_u['goodsDetails'][0]['deliveryTermsCode'],
                    'pieceQty' => empty($invoice_u['goodsDetails'][0]['pieceQty']) ? '' : strval($invoice_u['goodsDetails'][0]['pieceQty']),
                    'pieceMeasureUnit' => empty($invoice_u['goodsDetails'][0]['pieceMeasureUnit']) ? '' : $invoice_u['goodsDetails'][0]['pieceMeasureUnit'],
                    'totalWeight' => empty($invoice_u['goodsDetails'][0]['totalWeight']) ? '' : strval($invoice_u['goodsDetails'][0]['totalWeight'])
                );
            }

            $this->logger->write("Utilities : uploaddebitnote() : The debit note payload audit is " . json_encode($debitnotePayloadAudit), 'r');

            $invoice_u = json_encode($invoice_u); //JSON-ifiy
            $invoice_u = base64_encode($invoice_u); //base64 encode
            $this->logger->write("Utilities : uploaddebitnote() : The encoded invoice is " . $invoice_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $invoice_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : uploaddebitnote() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : uploaddebitnote() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : uploaddebitnote() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : uploaddebitnote() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : uploaddebitnote() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : uploaddebitnote() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name querytaxpayer
     * @desc query a tax payer by TIN from EFRIS
     * @return JSON-encoded object
     * @param $userid int, $tin string
     *
     */
    public function querytaxpayer($userid, $tin)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : querytaxpayer() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : querytaxpayer() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : querytaxpayer() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : querytaxpayer() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : querytaxpayer() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : querytaxpayer() : The TIN is " . $tin, 'r');

        if (trim($tin) == '' || empty($tin)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : querytaxpayer() : The TIN is empty.", 'r');
        }

        try {
            $this->logger->write("Utilities : querytaxpayer() : Downloading taxpayer started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T119';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);


            $taxpayer_u = array(
                'tin' => strval($tin),
                'ninBrn' => ''
            );

            //print_r($taxpayer_u);

            $taxpayer_u = json_encode($taxpayer_u); //JSON-ifiy
            $taxpayer_u = base64_encode($taxpayer_u); //base64 encode
            $this->logger->write("Utilities : querytaxpayer() : The encoded data is " . $taxpayer_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $taxpayer_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : querytaxpayer() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : querytaxpayer() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : querytaxpayer() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : querytaxpayer() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : querytaxpayer() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : querytaxpayer() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name efrislogin
     * @desc initialize eTW by logging into EFRIS
     * @return JSON-encoded object
     * @param $userid int
     *
     */
    public function efrislogin($userid)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : efrislogin() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : efrislogin() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : efrislogin() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : efrislogin() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : efrislogin() : The user id is " . $userid, 'r');


        try {
            $this->logger->write("Utilities : efrislogin() : Downloading taxpayer started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T103';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);


            $data = array(
                'data' => array(
                    'content' => '',
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);
            $this->logger->write("Utilities : efrislogin() : The request is: " . $data, 'r');

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : efrislogin() : The response content is: " . $content, 'r');

            //var_dump($returninfo);
            //var_dump($content);
            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : efrislogin() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : efrislogin() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : efrislogin() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : efrislogin() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : efrislogin() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name batchstockin
     * @desc Upload stock in a batch to EFRIS
     * @return JSON-encoded object
     * @param $userid int, $branchuraid string, $product array, $batchno string, $suppliertin string, $suppliername string, $stockintype string, $productiondate DateTime
     *
     */
    public function batchstockin($userid, $branchuraid, $products, $batchno, $suppliertin, $suppliername, $stockintype, $productiondate)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : batchstockin() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : batchstockin() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : batchstockin() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : batchstockin() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : batchstockin() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : batchstockin() : Uploading stock started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T131';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $product = new products($this->db);
            //$product->getByID($id);
            //$product->getByErpCode(trim($productcode));

            $t_stock = array();

            foreach ($products as $obj) {
                $product->getByErpCode(trim($obj['productCode']));

                $t_stock[] = array(
                    'commodityGoodsId' => empty($product->uraproductidentifier) ? '' : $product->uraproductidentifier,
                    'quantity' => empty($obj['quantity']) ? '' : $obj['quantity'],
                    'unitPrice' => empty($obj['unitPrice']) ? '' : $obj['unitPrice'],
                    'goodsCode' => empty($product->code) ? '' : $product->code,
                    'measureUnit' => empty($product->measureunit) ? '' : $product->measureunit
                );

            }

            $stock = array(
                'goodsStockIn' => array(
                    'operationType' => trim($this->appsettings['STOCKINOPERATIONTYPE']),
                    'supplierTin' => $suppliertin,
                    'supplierName' => $suppliername == 'N/A' ? '' : $suppliername,
                    'adjustType' => '',
                    'remarks' => '',
                    'stockInDate' => date('Y-m-d'),
                    'stockInType' => $stockintype,
                    'productionBatchNo' => empty($batchno) ? '' : $batchno,
                    'productionDate' => empty($productiondate) ? '' : date('Y-m-d', strtotime($productiondate)),
                    'branchId' => empty($branchuraid) ? $devicedetails->branchId : $branchuraid,
                    'invoiceNo' => '',
                    'isCheckBatchNo' => '0'
                ),
                'goodsStockInItem' => $t_stock
            );

            $stock = json_encode($stock); //JSON-ifiy
            $stock = base64_encode($stock); //base64 encode
            $this->logger->write("Utilities : batchstockin() : The encoded stock is " . $stock, 'r');

            $data = array(
                'data' => array(
                    'content' => $stock,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);
            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : batchstockin() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : batchstockin() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : batchstockin() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : batchstockin() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : batchstockin() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');

                if (trim($content) == '' || empty($content)) {
                    $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
                } else {
                    /**
                     * Modification Date: 2022-06-13
                     * Modified By: Francis Lubanga
                     * Description: Resolving issue of sending the generic PARTIAL ERROR message
                     * */

                    if ($dataDesc['zipCode'] == '1') {
                        $this->logger->write("Utilities : batchstockin() : The response is zipped", 'r');
                        $content = gzdecode(base64_decode($content));
                    } else {
                        $this->logger->write("Utilities : batchstockin() : The response is NOT zipped", 'r');
                        $content = base64_decode($content);
                    }
                }
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : stockin() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

    /**
     * @name logstockadjustment
     * @desc Create a record of the stock adjustment on eTW
     * @return NULL
     * @param $userid int, $productcode string, $batchno string, $qty int, $suppliertin string, $suppliername string, $stockintype int, $productiondate date, $unitprice float, $operationtype int, $vchtype string, $vchtypename string, $vchnumber string, $vchref string, $adjustmenttype int, $remarks string
     *
     */
    public function logstockadjustment($userid, $productcode, $batchno, $qty, $suppliertin, $suppliername, $stockintype, $productiondate, $unitprice, $operationtype, $vchtype, $vchtypename, $vchnumber, $vchref, $adjustmenttype, $remarks)
    {
        $operationtype = empty($operationtype) || is_null($operationtype) ? 'NULL' : $operationtype;
        $adjustmenttype = empty($adjustmenttype) || is_null($adjustmenttype) ? 'NULL' : $adjustmenttype;
        $stockintype = empty($stockintype) || is_null($stockintype) ? 'NULL' : $stockintype;
        $qty = empty($qty) || is_null($qty) ? 'NULL' : $qty;
        $unitprice = empty($unitprice) || is_null($unitprice) ? 'NULL' : $unitprice;
        $productiondate = empty($productiondate) || is_null($productiondate) ? date('Y-m-d') : $productiondate;

        $sql = 'INSERT INTO tblgoodsstockadjustment
                            (operationType,
                             supplierTin,
                             supplierName,
                             adjustType,
                             remarks,
                             stockInDate,
                             stockInType,
                             productionBatchNo,
                             productionDate,
                             quantity,
                             unitPrice,
                             ProductCode,
                             voucherType,
                             voucherTypeName,
                             voucherNumber,
                             voucherRef,
                             inserteddt,
                             insertedby,
                             modifieddt,
                             modifiedby)
                            VALUES ('
            . $operationtype . ', "'
                . addslashes($suppliertin) . '", "'
                    . addslashes($suppliername) . '", '
                        . $adjustmenttype . ', "'
                            . addslashes($remarks) . '", "'
                                . date('Y-m-d') . '", '
                                    . $stockintype . ', "'
                                        . addslashes($batchno) . '", "'
                                            . $productiondate . '", '
                                                . $qty . ', '
                                                    . $unitprice . ', "'
                                                        . addslashes($productcode) . '", "'
                                                            . addslashes($vchtype) . '", "'
                                                                . addslashes($vchtypename) . '", "'
                                                                    . addslashes($vchnumber) . '", "'
                                                                        . addslashes($vchref) . '", "'
                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                . $userid . ', "'
                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                        . $userid . ')';

        $this->logger->write("Utilities : logstockadjustment() : The SQL is " . $sql, 'r');

        try {
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : logstockadjustment() : The stock adjustment record has been added", 'r');
        } catch (Exception $e) {
            $this->logger->write("Utilities : logstockadjustment() : Failed to insert the stock adjustment record. The error message is " . $e->getMessage(), 'r');
        }
    }

    /**
     * @name logstocktransfer
     * @desc Create a record of the stock transfer on eTW
     * @return NULL
     * @param $userid int, $productcode string, $qty float, $vchtype string, $vchtypename string, $vchnumber string, $vchref string, $remarks string, $sourceBranchId string, $destinationBranchId string, $transferTypeCode string, $commodityGoodsId string
     *
     */
    public function logstocktransfer($userid, $productcode, $qty, $vchtype, $vchtypename, $vchnumber, $vchref, $remarks, $sourceBranchId, $destinationBranchId, $transferTypeCode, $commodityGoodsId)
    {
        $transferTypeCode = empty($transferTypeCode) || is_null($transferTypeCode) ? 'NULL' : $transferTypeCode;
        $qty = empty($qty) || is_null($qty) ? 'NULL' : $qty;


        $sql = 'INSERT INTO tblgoodsstocktransfer
                            (sourceBranchId,
                             destinationBranchId,
                             transferTypeCode,
                             remarks,
                             commodityGoodsId,
                             quantity,
                             ProductCode,
                             voucherType,
                             voucherTypeName,
                             voucherNumber,
                             voucherRef,
                             inserteddt,
                             insertedby,
                             modifieddt,
                             modifiedby)
                            VALUES ("'
            . addslashes($sourceBranchId) . '", "'
                . addslashes($destinationBranchId) . '", '
                    . $transferTypeCode . ', "'
                        . addslashes($remarks) . '", "'
                            . addslashes($commodityGoodsId) . '", '
                                . $qty . ', "'
                                    . addslashes($productcode) . '", "'
                                        . addslashes($vchtype) . '", "'
                                            . addslashes($vchtypename) . '", "'
                                                . addslashes($vchnumber) . '", "'
                                                    . addslashes($vchref) . '", "'
                                                        . date('Y-m-d H:i:s') . '", '
                                                            . $userid . ', "'
                                                                . date('Y-m-d H:i:s') . '", '
                                                                    . $userid . ')';

        $this->logger->write("Utilities : logstocktransfer() : The SQL is " . $sql, 'r');

        try {
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : logstocktransfer() : The stock transfer record has been added", 'r');
        } catch (Exception $e) {
            $this->logger->write("Utilities : logstocktransfer() : Failed to insert the stock transfer record. The error message is " . $e->getMessage(), 'r');
        }
    }


    /**
     *
     * @name __constructor
     * @desc Constructor for the Utilities class
     * @return NULL
     * @param NULL
     *
     */
    public function __construct()
    {
        $f3 = Base::instance();
        $this->f3 = $f3;

        $db = new DB\SQL($f3->get('dbserver'), $f3->get('dbuser'), $f3->get('dbpwd'), array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ));

        $this->db = $db;
        $logger = new Log('util.log');
        $this->logger = $logger;

        $data = array();
        $setting = new settings($db);
        $settings = $setting->getNoneSensitive();

        foreach ($settings as $obj) {
            $data[$obj['code']] = $obj['value'];//insert a KEY/VALUE pair for each setting
        }

        $this->appsettings = $data;

        $this->userid = $this->appsettings['APIUSERID'];
        $user = new users($this->db);
        $user->getByID($this->userid);
        $this->username = $user->username;
        $this->branch = $user->branch;

        $this->emailUrl = $this->appsettings['SYSTEMEMAILENDPOINT'];


        $vat_check = new DB\SQL\Mapper($this->db, 'tbltaxtypes');
        $vat_check->load(array('TRIM(code)=?', $this->appsettings['EFRIS_VAT_TAX_TYPE_CODE']));

        if ($vat_check->dry()) {
            $this->logger->write("Utilities : __construct() : The tax payer is not VAT registered", 'r');
            $this->vatRegistered = 'N';
        } else {
            $this->logger->write("Utilities : __construct() : The tax payer is VAT registered", 'r');
            $this->vatRegistered = 'Y';
        }
    }


    public function xss_cleaner($input_str)
    {
        $return_str = str_replace(array('<',';','|','&','>',"'",'"',')','('), array('&lt;','&#58;','&#124;','&#38;','&gt;','&apos;','&#x22;','&#x29;','&#x28;'), $input_str);
        $return_str = str_ireplace('%3Cscript', '', $return_str);
        return $return_str;
    }


    public function truncatenumber($no, $dec = null)
    {
        /**
         * 00. If the input parameter $dec is NULL, then default it to 2
         * 1. Trim the number
         * 2. Remove any commas, if present
         * 3. Check for presence of a decimal point (.)
         * 4. If there is no decimal point (.), do the following
         * 4.1 Append a decimal point (.)
         * 4.2 Append 0's equivalent to the value of the $dec input parameter, e.g. if $dec = 2, then append 2 Zeros (00)
         * 5. If there is a decimal point (.), do the following
         * 6. Get the position of the decimal point
         * 7. Count how many digits/characters are after the decimal point
         * 8. If the digits after the decimal points are less that the value of $dec, then append 0's equivalent to the difference between $dec and the # of digits
         * 9. If the digits after the more then the value of $dec, drop off the excess digits
         *
         */

        $this->logger->write("Utilities : truncatenumber() : The input no is " . $no, 'r');

        $value = '0.00';
        //$no = '8900000.2';
        //$dec = 2;
        $cntr = 0;
        $int_part = '';
        $dec_part = '';

        //00. If the input parameter $dec is NULL, then default it to 2
        if (is_null($dec)) {
            $dec = 2;
        }

        //1. Trim the number
        //2. Remove any commas, if present
        $value = str_replace(array(','), '', trim($no));
        $len = strlen($value);

        $pos = stripos($value, '.');//3. Check for presence of a decimal point (.)

        if ($pos) {
            //4. If there is a decimal point (.)
            $int_part = substr($value, 0, $pos);
            $dec_part = substr($value, $pos + 1, $len);

            //7. Count how many digits/characters are after the decimal point
            $len_dec_part = strlen($dec_part);


            if ((int)$len_dec_part < (int)$dec) {
                //8. If the digits after the decimal points are less that the value of $dec, then append 0's equivalent to the difference between $dec and the # of digits
                $cntr = (int)$dec - (int)$len_dec_part;
                while ($cntr > 0) {
                    $dec_part = $dec_part . '0';
                    $cntr = $cntr - 1;
                }
            } else {
                //9. If the digits after the more then the value of $dec, drop off the excess digits
                $dec_part = substr($dec_part, 0, $dec);
            }

            $value = $int_part . '.' . $dec_part;
        } else {
            //5. If there is no decimal point (.)
            //5.1 Append a decimal point (.)
            $value = $value . '.';

            //5.2 Append 0's equivalent to the value of the $dec input parameter, e.g. if $dec = 2, then append 2 Zeros (00)
            $cntr = (int)$dec;
            while ($cntr > 0) {
                $value = $value . '0';
                $cntr = $cntr - 1;
            }
        }

        $this->logger->write("Utilities : truncatenumber() : The output no is " . $value, 'r');
        return $value;
    }


    public function createcreditmemo($invoicedetails, $goods, $taxes, $buyer, $userid)
    {
        /**
         * 0. Insert a new invoice and retrieve its id
         * 1. Create a param group for goods, taxes, and payments
         * 2. Modify the following arrays
         * 2.1 goods
         * 2.2 payments
         * 2.3 payments
         * 2.4 buyers
         * 3. Insert into the respective tables
         */


        try {

            $netamount = empty($invoicedetails['netamount']) ? '0.00' : $invoicedetails['netamount'];
            $taxamount = empty($invoicedetails['taxamount']) ? '0.00' : $invoicedetails['taxamount'];
            $grossamount = empty($invoicedetails['grossamount']) ? '0.00' : $invoicedetails['grossamount'];
            $itemcount = empty($invoicedetails['itemcount']) ? '0' : $invoicedetails['itemcount'];
            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;
            $currencyRate = empty($invoicedetails['currencyRate']) ? '1' : $invoicedetails['currencyRate'];

            $sql = 'INSERT INTO tblcreditmemos
                                    (erpinvoiceid,
                                    erpinvoiceno,
                                    antifakecode,
                                    deviceno,
                                    issueddate,
                                    issuedtime,
                                    operator,
                                    currency,
                                    oriinvoiceid,
                                    invoicetype,
                                    invoicekind,
                                    datasource,
                                    invoiceindustrycode,
                                    einvoiceid,
                                    einvoicenumber,
                                    einvoicedatamatrixcode,
                                    isbatch,
                                    netamount,
                                    taxamount,
                                    grossamount,
                                    origrossamount,
                                    itemcount,
                                    modecode,
                                    modename,
                                    remarks,
                                    buyerid,
                                    sellerid,
                                    issueddatepdf,
                                    grossamountword,
                                    isinvalid,
                                    isrefund,
                                    vouchertype,
                                    vouchertypename,
                                    currencyRate,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($invoicedetails['erpinvoiceid']) . '", "'
                    . addslashes($invoicedetails['erpinvoiceno']) . '", "'
                        . addslashes($invoicedetails['antifakecode']) . '", "'
                            . addslashes($invoicedetails['deviceno']) . '", "'
                                . $invoicedetails['issueddate'] . '", "'
                                    . $invoicedetails['issuedtime'] . '", "'
                                        . addslashes($invoicedetails['operator']) . '", "'
                                            . $invoicedetails['currency'] . '", "'
                                                . $invoicedetails['oriinvoiceid'] . '", '
                                                    . $invoicedetails['invoicetype'] . ', '
                                                        . $invoicedetails['invoicekind'] . ', '
                                                            . $invoicedetails['datasource'] . ', '
                                                                . $invoicedetails['invoiceindustrycode'] . ', "'
                                                                    . addslashes($invoicedetails['einvoiceid']) . '", "'
                                                                        . addslashes($invoicedetails['einvoicenumber']) . '", "'
                                                                            . addslashes($invoicedetails['einvoicedatamatrixcode']) . '", "'
                                                                                . $invoicedetails['isbatch'] . '", '
                                                                                    . $netamount . ', '
                                                                                        . $taxamount . ', '
                                                                                            . $grossamount . ', '
                                                                                                . $invoicedetails['origrossamount'] . ', '
                                                                                                    . $itemcount . ', "'
                                                                                                        . $invoicedetails['modecode'] . '", "'
                                                                                                            . $invoicedetails['modename'] . '", "'
                                                                                                                . addslashes($invoicedetails['remarks']) . '", '
                                                                                                                    . 'NULL, '
                                                                                                                        . $invoicedetails['sellerid'] . ', "'
                                                                                                                            . $invoicedetails['issueddatepdf'] . '", "'
                                                                                                                                . $invoicedetails['grossamountword'] . '", '
                                                                                                                                    . $invoicedetails['isinvalid'] . ', '
                                                                                                                                        . $invoicedetails['isrefund'] . ', "'
                                                                                                                                            . addslashes($invoicedetails['vchtype']) . '", "'
                                                                                                                                                . addslashes($invoicedetails['vchtypename']) . '", '
                                                                                                                                                    . $currencyRate . ', "'
                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                            . $userid . ', "'
                                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                    . $userid . ')';

            $this->logger->write("Utilities : createcreditmemo() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : createcreditmemo() : The invoice has been added", 'r');


            $this->logger->write("Utilities : createcreditmemo() : The FDN is " . $invoicedetails['antifakecode'], 'r');

            $data = array();
            $r = $this->db->exec(array(
                'SELECT id "id" FROM tblcreditmemos WHERE TRIM(antifakecode) = \'' . $invoicedetails['antifakecode'] . '\''
            ));

            foreach ($r as $obj) {
                $data[] = $obj;
            }

            $id = $data[0]['id'];

            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['CREDITMEMOENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITMEMOENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $gooddetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblcreditmemos SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    /*Insert Goods*/

                    $i = 0;
                    foreach ($goods as $obj) {


                        $obj['item'] = empty($obj['item']) ? '' : $obj['item'];
                        $obj['itemcode'] = empty($obj['itemcode']) ? '' : $obj['itemcode'];
                        $obj['qty'] = empty($obj['qty']) ? 'NULL' : $obj['qty'];
                        $obj['unitofmeasure'] = empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'];
                        $obj['unitprice'] = empty($obj['unitprice']) ? 'NULL' : $obj['unitprice'];
                        $obj['total'] = empty($obj['total']) ? 'NULL' : $obj['total'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? 'NULL' : $obj['taxrate'];
                        $obj['tax'] = empty($obj['tax']) ? '0.00' : $obj['tax'];
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? 'NULL' : $obj['discounttotal'];
                        $obj['discounttaxrate'] = (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00') ? 'NULL' : $obj['discounttaxrate'];
                        $obj['discountflag'] = empty($obj['discountflag']) ? '2' : $obj['discountflag'];
                        $obj['deemedflag'] = empty($obj['deemedflag']) ? '2' : $obj['deemedflag'];
                        $obj['exciseflag'] = empty($obj['exciseflag']) ? '2' : $obj['exciseflag'];
                        $obj['categoryid'] = empty($obj['categoryid']) ? 'NULL' : $obj['categoryid'];
                        $obj['categoryname'] = empty($obj['categoryname']) ? '' : $obj['categoryname'];
                        $obj['goodscategoryid'] = empty($obj['goodscategoryid']) ? 'NULL' : $obj['goodscategoryid'];
                        $obj['goodscategoryname'] = empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'];
                        $obj['exciserate'] = empty($obj['exciserate']) ? '' : $obj['exciserate'];
                        $obj['exciserule'] = empty($obj['exciserule']) ? 'NULL' : $obj['exciserule'];
                        $obj['excisetax'] = empty($obj['excisetax']) ? 'NULL' : $obj['excisetax'];
                        $obj['pack'] = empty($obj['pack']) ? 'NULL' : $obj['pack'];
                        $obj['stick'] = empty($obj['stick']) ? 'NULL' : $obj['stick'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? 'NULL' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['exciseratename'] = empty($obj['exciseratename']) ? '' : $obj['exciseratename'];

                        $sql = 'INSERT INTO tblgooddetails (
                                    groupid,
                                    item,
                                    itemcode,
                                    qty,
                                    unitofmeasure,
                                    unitprice,
                                    total,
                                    taxid,
                                    taxrate,
                                    tax,
                                    discounttotal,
                                    discounttaxrate,
                                    discountpercentage,
                                    ordernumber,
                                    discountflag,
                                    deemedflag,
                                    exciseflag,
                                    categoryid,
                                    categoryname,
                                    goodscategoryid,
                                    goodscategoryname,
                                    exciserate,
                                    exciserule,
                                    excisetax,
                                    pack,
                                    stick,
                                    exciseunit,
                                    excisecurrency,
                                    exciseratename,
                                    taxcategory,
                                    unitofmeasurename,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $gooddetailgroupid . ', "'
                                . addslashes($obj['item']) . '", "'
                                    . addslashes($obj['itemcode']) . '", '
                                        . $obj['qty'] . ', "'
                                            . $obj['unitofmeasure'] . '", '
                                                . $obj['unitprice'] . ', '
                                                    . $obj['total'] . ', '
                                                        . $obj['taxid'] . ', '
                                                            . $obj['taxrate'] . ', '
                                                                . $obj['tax'] . ', '
                                                                    . $obj['discounttotal'] . ', '
                                                                        . $obj['discounttaxrate'] . ', '
                                                                            . $obj['discountpercentage'] . ', '
                                                                                . $i . ', '
                                                                                    . $obj['discountflag'] . ', '
                                                                                        . $obj['deemedflag'] . ', '
                                                                                            . $obj['exciseflag'] . ', '
                                                                                                . $obj['categoryid'] . ', "'
                                                                                                    . addslashes($obj['categoryname']) . '", '
                                                                                                        . $obj['goodscategoryid'] . ', "'
                                                                                                            . addslashes($obj['goodscategoryname']) . '", "'
                                                                                                                . $obj['exciserate'] . '", '
                                                                                                                    . $obj['exciserule'] . ', '
                                                                                                                        . $obj['excisetax'] . ', '
                                                                                                                            . $obj['pack'] . ', '
                                                                                                                                . $obj['stick'] . ', '
                                                                                                                                    . $obj['exciseunit'] . ', "'
                                                                                                                                        . $obj['excisecurrency'] . '", "'
                                                                                                                                            . $obj['exciseratename'] . '", "'
                                                                                                                                                . $obj['taxdisplaycategory'] . '", "'
                                                                                                                                                    . $obj['unitofmeasurename'] . '", "'
                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                            . $userid . ', "'
                                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : createcreditmemo() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));

                        $i = $i + 1;
                    }

                } catch (Exception $e) {
                    $this->logger->write("Utilities : createcreditmemo() : Failed to select and insert into table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createcreditmemo() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['CREDITMEMOENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITMEMOENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $paymentdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblcreditmemos SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createcreditmemo() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createcreditmemo() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['CREDITMEMOENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITMEMOENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $taxdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblcreditmemos SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    //Get details of goods inserted
                    $t_goods = $this->db->exec(array('SELECT * FROM tblgooddetails g WHERE g.groupid = ' . $gooddetailgroupid . ' ORDER BY id ASC'));

                    //Insert Taxes
                    $j = 0;
                    foreach ($taxes as $obj) {
                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : createcreditmemo() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : createcreditmemo() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : createcreditmemo() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }

                        if (strtoupper(trim($obj['taxcategory'])) == 'D: DEEMED (18%)') {
                            $obj['netamount'] = floor(($obj['netamount'] + $obj['d_netamount']) * 100) / 100;
                            $obj['taxamount'] = floor(($obj['taxamount'] + $obj['d_taxamount']) * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 4, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 4, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 4, '.', '');
                        } else {
                            $obj['netamount'] = round(($obj['netamount'] + $obj['d_netamount']), 4);
                            $obj['taxamount'] = round(($obj['taxamount'] + $obj['d_taxamount']), 4);
                            //$obj['grossamount'] = round($obj['grossamount'], 4);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];
                        }

                        $obj['taxcategory'] = empty($obj['taxcategory']) ? '' : $obj['taxcategory'];
                        $obj['netamount'] = empty($obj['netamount']) ? 'NULL' : $obj['netamount'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? '' : $obj['taxrate'];
                        $obj['taxamount'] = empty($obj['taxamount']) ? '0.00' : $obj['taxamount'];
                        $obj['grossamount'] = empty($obj['grossamount']) ? 'NULL' : $obj['grossamount'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? '' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['taxratename'] = empty($obj['taxratename']) ? '' : $obj['taxratename'];

                        //$obj['goodid'] = empty($obj['goodid'])? 'NULL' : $obj['goodid'];
                        $obj['goodid'] = $t_goods[$j]['id'];

                        $sql = 'INSERT INTO tbltaxdetails (
                                    groupid,
                                    goodid,
                                    taxcategory,
                                    netamount,
                                    taxrate,
                                    taxamount,
                                    grossamount,
                                    exciseunit,
                                    excisecurrency,
                                    taxratename,
                                    taxdescription,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $taxdetailgroupid . ', '
                                . $obj['goodid'] . ', "'
                                    . addslashes($obj['taxcategory']) . '", '
                                        . $obj['netamount'] . ', '
                                            . $obj['taxrate'] . ', '
                                                . $obj['taxamount'] . ', '
                                                    . $obj['grossamount'] . ', "'
                                                        . $obj['exciseunit'] . '", "'
                                                            . $obj['excisecurrency'] . '", "'
                                                                . $obj['taxratename'] . '", "'
                                                                    . $obj['taxdescription'] . '", "'
                                                                        . date('Y-m-d H:i:s') . '", '
                                                                            . $userid . ', "'
                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : createcreditmemo() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));
                        $j = $j + 1;

                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createcreditmemo() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createcreditmemo() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            if (trim($buyer['referenceno']) !== '' || !empty(trim($buyer['referenceno']))) {
                try {

                    $sql = 'INSERT INTO tblbuyers (
                                    tin,
                                    ninbrn,
                                    PassportNum,
                                    legalname,
                                    businessname,
                                    address,
                                    mobilephone,
                                    linephone,
                                    emailaddress,
                                    placeofbusiness,
                                    type,
                                    citizineship,
                                    sector,
                                    referenceno,
                                    datasource,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                        . addslashes($buyer['tin']) . '", "'
                            . addslashes($buyer['ninbrn']) . '", "'
                                . addslashes($buyer['PassportNum']) . '", "'
                                    . addslashes($buyer['legalname']) . '", "'
                                        . addslashes($buyer['businessname']) . '", "'
                                            . addslashes($buyer['address']) . '", "'
                                                . addslashes($buyer['mobilephone']) . '", "'
                                                    . addslashes($buyer['linephone']) . '", "'
                                                        . addslashes($buyer['emailaddress']) . '", "'
                                                            . addslashes($buyer['placeofbusiness']) . '", "'
                                                                . $buyer['type'] . '", "'
                                                                    . addslashes($buyer['citizineship']) . '", "'
                                                                        . addslashes($buyer['sector']) . '", "'
                                                                            . addslashes($buyer['referenceno']) . '", "'
                                                                                . $buyer['datasource'] . '", "'
                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                        . $userid . ', "'
                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                . $userid . ')';

                    $this->logger->write("Utilities : createcreditmemo() : The SQL is " . $sql, 'r');
                    $this->db->exec(array($sql));

                    try {
                        $by = array();
                        $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE referenceno = "' . $buyer['referenceno'] . '" AND insertedby = ' . $userid));

                        foreach ($r as $obj) {
                            $by [] = $obj;
                        }

                        $buyerid = $by[0]['id'];
                        $this->db->exec(array('UPDATE tblinvoices SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                    } catch (Exception $e) {
                        $this->logger->write("Utilities : createcreditmemo() : Failed to select and update table tblinvoices. The error message is " . $e->getMessage(), 'r');
                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createcreditmemo() : The operation to create a buyer was not successful. The error message is " . $e->getMessage(), 'r');
                }
            }

            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : createcreditmemo() : The operation to create the invoice was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }
    }


    public function createimportedservice($invoicedetails, $goods, $taxes, $buyer, $userid, $importedseller)
    {
        /**
         * 0. Insert a new imported service invoice and retrieve its id
         * 1. Create a param group for goods, taxes, and payments
         * 2. Modify the following arrays
         * 2.1 goods
         * 2.2 payments
         * 2.3 payments
         * 2.4 buyers
         * 3. Insert into the respective tables
         */


        try {

            $netamount = empty($invoicedetails['netamount']) ? '0.00' : $invoicedetails['netamount'];
            $taxamount = empty($invoicedetails['taxamount']) ? '0.00' : $invoicedetails['taxamount'];
            $grossamount = empty($invoicedetails['grossamount']) ? '0.00' : $invoicedetails['grossamount'];
            $itemcount = empty($invoicedetails['itemcount']) ? '0' : $invoicedetails['itemcount'];
            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;
            $currencyRate = empty($invoicedetails['currencyRate']) ? '1' : $invoicedetails['currencyRate'];

            $sql = 'INSERT INTO tblimportedservices
                                    (erpinvoiceid,
                                    erpinvoiceno,
                                    antifakecode,
                                    deviceno,
                                    issueddate,
                                    issuedtime,
                                    operator,
                                    currency,
                                    oriinvoiceid,
                                    invoicetype,
                                    invoicekind,
                                    datasource,
                                    invoiceindustrycode,
                                    einvoiceid,
                                    einvoicenumber,
                                    einvoicedatamatrixcode,
                                    isbatch,
                                    netamount,
                                    taxamount,
                                    grossamount,
                                    origrossamount,
                                    itemcount,
                                    modecode,
                                    modename,
                                    remarks,
                                    buyerid,
                                    sellerid,
                                    issueddatepdf,
                                    grossamountword,
                                    isinvalid,
                                    isrefund,
                                    vouchertype,
                                    vouchertypename,
                                    currencyRate,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($invoicedetails['erpinvoiceid']) . '", "'
                    . addslashes($invoicedetails['erpinvoiceno']) . '", "'
                        . addslashes($invoicedetails['antifakecode']) . '", "'
                            . addslashes($invoicedetails['deviceno']) . '", "'
                                . $invoicedetails['issueddate'] . '", "'
                                    . $invoicedetails['issuedtime'] . '", "'
                                        . addslashes($invoicedetails['operator']) . '", "'
                                            . $invoicedetails['currency'] . '", "'
                                                . $invoicedetails['oriinvoiceid'] . '", '
                                                    . $invoicedetails['invoicetype'] . ', '
                                                        . $invoicedetails['invoicekind'] . ', '
                                                            . $invoicedetails['datasource'] . ', '
                                                                . $invoicedetails['invoiceindustrycode'] . ', "'
                                                                    . addslashes($invoicedetails['einvoiceid']) . '", "'
                                                                        . addslashes($invoicedetails['einvoicenumber']) . '", "'
                                                                            . addslashes($invoicedetails['einvoicedatamatrixcode']) . '", "'
                                                                                . $invoicedetails['isbatch'] . '", '
                                                                                    . $netamount . ', '
                                                                                        . $taxamount . ', '
                                                                                            . $grossamount . ', '
                                                                                                . $invoicedetails['origrossamount'] . ', '
                                                                                                    . $itemcount . ', "'
                                                                                                        . $invoicedetails['modecode'] . '", "'
                                                                                                            . $invoicedetails['modename'] . '", "'
                                                                                                                . addslashes($invoicedetails['remarks']) . '", '
                                                                                                                    . 'NULL, '
                                                                                                                        . $invoicedetails['sellerid'] . ', "'
                                                                                                                            . $invoicedetails['issueddatepdf'] . '", "'
                                                                                                                                . $invoicedetails['grossamountword'] . '", '
                                                                                                                                    . $invoicedetails['isinvalid'] . ', '
                                                                                                                                        . $invoicedetails['isrefund'] . ', "'
                                                                                                                                            . addslashes($invoicedetails['vchtype']) . '", "'
                                                                                                                                                . addslashes($invoicedetails['vchtypename']) . '", '
                                                                                                                                                    . $currencyRate . ', "'
                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                            . $userid . ', "'
                                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                    . $userid . ')';

            $this->logger->write("Utilities : createimportedservice() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));
            $this->logger->write("Utilities : createimportedservice() : The invoice has been added", 'r');


            $this->logger->write("Utilities : createimportedservice() : The FDN is " . $invoicedetails['antifakecode'], 'r');

            $data = array();
            $r = $this->db->exec(array(
                'SELECT id "id" FROM tblimportedservices WHERE TRIM(antifakecode) = \'' . $invoicedetails['antifakecode'] . '\''
            ));

            foreach ($r as $obj) {
                $data[] = $obj;
            }

            $id = $data[0]['id'];

            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $gooddetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblimportedservices SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    /*Insert Goods*/

                    $i = 0;
                    foreach ($goods as $obj) {


                        $obj['item'] = empty($obj['item']) ? '' : $obj['item'];
                        $obj['itemcode'] = empty($obj['itemcode']) ? '' : $obj['itemcode'];
                        $obj['qty'] = empty($obj['qty']) ? 'NULL' : $obj['qty'];
                        $obj['unitofmeasure'] = empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'];
                        $obj['unitprice'] = empty($obj['unitprice']) ? 'NULL' : $obj['unitprice'];
                        $obj['total'] = empty($obj['total']) ? 'NULL' : $obj['total'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? 'NULL' : $obj['taxrate'];
                        $obj['tax'] = empty($obj['tax']) ? '0.00' : $obj['tax'];
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? 'NULL' : $obj['discounttotal'];
                        $obj['discounttaxrate'] = (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00') ? 'NULL' : $obj['discounttaxrate'];
                        $obj['discountflag'] = empty($obj['discountflag']) ? '2' : $obj['discountflag'];
                        $obj['deemedflag'] = empty($obj['deemedflag']) ? '2' : $obj['deemedflag'];
                        $obj['exciseflag'] = empty($obj['exciseflag']) ? '2' : $obj['exciseflag'];
                        $obj['categoryid'] = empty($obj['categoryid']) ? 'NULL' : $obj['categoryid'];
                        $obj['categoryname'] = empty($obj['categoryname']) ? '' : $obj['categoryname'];
                        $obj['goodscategoryid'] = empty($obj['goodscategoryid']) ? 'NULL' : $obj['goodscategoryid'];
                        $obj['goodscategoryname'] = empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'];
                        $obj['exciserate'] = empty($obj['exciserate']) ? '' : $obj['exciserate'];
                        $obj['exciserule'] = empty($obj['exciserule']) ? 'NULL' : $obj['exciserule'];
                        $obj['excisetax'] = empty($obj['excisetax']) ? 'NULL' : $obj['excisetax'];
                        $obj['pack'] = empty($obj['pack']) ? 'NULL' : $obj['pack'];
                        $obj['stick'] = empty($obj['stick']) ? 'NULL' : $obj['stick'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? 'NULL' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['exciseratename'] = empty($obj['exciseratename']) ? '' : $obj['exciseratename'];

                        $sql = 'INSERT INTO tblgooddetails (
                                    groupid,
                                    item,
                                    itemcode,
                                    qty,
                                    unitofmeasure,
                                    unitprice,
                                    total,
                                    taxid,
                                    taxrate,
                                    tax,
                                    discounttotal,
                                    discounttaxrate,
                                    discountpercentage,
                                    ordernumber,
                                    discountflag,
                                    deemedflag,
                                    exciseflag,
                                    categoryid,
                                    categoryname,
                                    goodscategoryid,
                                    goodscategoryname,
                                    exciserate,
                                    exciserule,
                                    excisetax,
                                    pack,
                                    stick,
                                    exciseunit,
                                    excisecurrency,
                                    exciseratename,
                                    taxcategory,
                                    unitofmeasurename,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $gooddetailgroupid . ', "'
                                . addslashes($obj['item']) . '", "'
                                    . addslashes($obj['itemcode']) . '", '
                                        . $obj['qty'] . ', "'
                                            . $obj['unitofmeasure'] . '", '
                                                . $obj['unitprice'] . ', '
                                                    . $obj['total'] . ', '
                                                        . $obj['taxid'] . ', '
                                                            . $obj['taxrate'] . ', '
                                                                . $obj['tax'] . ', '
                                                                    . $obj['discounttotal'] . ', '
                                                                        . $obj['discounttaxrate'] . ', '
                                                                            . $obj['discountpercentage'] . ', '
                                                                                . $i . ', '
                                                                                    . $obj['discountflag'] . ', '
                                                                                        . $obj['deemedflag'] . ', '
                                                                                            . $obj['exciseflag'] . ', '
                                                                                                . $obj['categoryid'] . ', "'
                                                                                                    . addslashes($obj['categoryname']) . '", '
                                                                                                        . $obj['goodscategoryid'] . ', "'
                                                                                                            . addslashes($obj['goodscategoryname']) . '", "'
                                                                                                                . $obj['exciserate'] . '", '
                                                                                                                    . $obj['exciserule'] . ', '
                                                                                                                        . $obj['excisetax'] . ', '
                                                                                                                            . $obj['pack'] . ', '
                                                                                                                                . $obj['stick'] . ', '
                                                                                                                                    . $obj['exciseunit'] . ', "'
                                                                                                                                        . $obj['excisecurrency'] . '", "'
                                                                                                                                            . $obj['exciseratename'] . '", "'
                                                                                                                                                . $obj['taxdisplaycategory'] . '", "'
                                                                                                                                                    . $obj['unitofmeasurename'] . '", "'
                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                            . $userid . ', "'
                                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : createimportedservice() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));

                        $i = $i + 1;
                    }

                } catch (Exception $e) {
                    $this->logger->write("Utilities : createimportedservice() : Failed to select and insert into table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createimportedservice() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $paymentdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblimportedservices SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createimportedservice() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createimportedservice() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            try {
                $paramgroupdescription = "This is an autogenerated group id for the invoice id " . $id;

                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                            VALUES(' . $id . ', ' . $this->appsettings['INVOICEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $userid . ', NOW(), ' . $userid . ')'));

                try {
                    $pg = array();
                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['INVOICEENTITYTYPE'] . ' AND insertedby = ' . $userid));

                    foreach ($r as $obj) {
                        $pg [] = $obj;
                    }

                    $taxdetailgroupid = $pg[0]['id'];
                    $this->db->exec(array('UPDATE tblimportedservices SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));

                    //Get details of goods inserted
                    $t_goods = $this->db->exec(array('SELECT * FROM tblgooddetails g WHERE g.groupid = ' . $gooddetailgroupid . ' ORDER BY id ASC'));

                    //Insert Taxes
                    $j = 0;
                    foreach ($taxes as $obj) {
                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : createimportedservice() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : createimportedservice() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : createimportedservice() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }

                        if (strtoupper(trim($obj['taxcategory'])) == 'D: DEEMED (18%)') {
                            $obj['netamount'] = floor(($obj['netamount'] + $obj['d_netamount']) * 100) / 100;
                            $obj['taxamount'] = floor(($obj['taxamount'] + $obj['d_taxamount']) * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 4, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 4, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 4, '.', '');
                        } else {
                            $obj['netamount'] = round(($obj['netamount'] + $obj['d_netamount']), 4);
                            $obj['taxamount'] = round(($obj['taxamount'] + $obj['d_taxamount']), 4);
                            //$obj['grossamount'] = round($obj['grossamount'], 4);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];
                        }

                        $obj['taxcategory'] = empty($obj['taxcategory']) ? '' : $obj['taxcategory'];
                        $obj['netamount'] = empty($obj['netamount']) ? 'NULL' : $obj['netamount'];
                        $obj['taxrate'] = empty($obj['taxrate']) ? '' : $obj['taxrate'];
                        $obj['taxamount'] = empty($obj['taxamount']) ? '0.00' : $obj['taxamount'];
                        $obj['grossamount'] = empty($obj['grossamount']) ? 'NULL' : $obj['grossamount'];
                        $obj['exciseunit'] = empty($obj['exciseunit']) ? '' : $obj['exciseunit'];
                        $obj['excisecurrency'] = empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'];
                        $obj['taxratename'] = empty($obj['taxratename']) ? '' : $obj['taxratename'];

                        //$obj['goodid'] = empty($obj['goodid'])? 'NULL' : $obj['goodid'];
                        $obj['goodid'] = $t_goods[$j]['id'];

                        $sql = 'INSERT INTO tbltaxdetails (
                                    groupid,
                                    goodid,
                                    taxcategory,
                                    netamount,
                                    taxrate,
                                    taxamount,
                                    grossamount,
                                    exciseunit,
                                    excisecurrency,
                                    taxratename,
                                    taxdescription,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ('
                            . $taxdetailgroupid . ', '
                                . $obj['goodid'] . ', "'
                                    . addslashes($obj['taxcategory']) . '", '
                                        . $obj['netamount'] . ', '
                                            . $obj['taxrate'] . ', '
                                                . $obj['taxamount'] . ', '
                                                    . $obj['grossamount'] . ', "'
                                                        . $obj['exciseunit'] . '", "'
                                                            . $obj['excisecurrency'] . '", "'
                                                                . $obj['taxratename'] . '", "'
                                                                    . $obj['taxdescription'] . '", "'
                                                                        . date('Y-m-d H:i:s') . '", '
                                                                            . $userid . ', "'
                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                    . $userid . ')';

                        $this->logger->write("Utilities : createimportedservice() : The SQL is " . $sql, 'r');
                        $this->db->exec(array($sql));
                        $j = $j + 1;

                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createimportedservice() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities : createimportedservice() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
            }


            if (trim($buyer['referenceno']) !== '' || !empty(trim($buyer['referenceno']))) {
                try {

                    $sql = 'INSERT INTO tblbuyers (
                                    tin,
                                    ninbrn,
                                    PassportNum,
                                    legalname,
                                    businessname,
                                    address,
                                    mobilephone,
                                    linephone,
                                    emailaddress,
                                    placeofbusiness,
                                    type,
                                    citizineship,
                                    sector,
                                    referenceno,
                                    datasource,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                        . addslashes($buyer['tin']) . '", "'
                            . addslashes($buyer['ninbrn']) . '", "'
                                . addslashes($buyer['PassportNum']) . '", "'
                                    . addslashes($buyer['legalname']) . '", "'
                                        . addslashes($buyer['businessname']) . '", "'
                                            . addslashes($buyer['address']) . '", "'
                                                . addslashes($buyer['mobilephone']) . '", "'
                                                    . addslashes($buyer['linephone']) . '", "'
                                                        . addslashes($buyer['emailaddress']) . '", "'
                                                            . addslashes($buyer['placeofbusiness']) . '", "'
                                                                . $buyer['type'] . '", "'
                                                                    . addslashes($buyer['citizineship']) . '", "'
                                                                        . addslashes($buyer['sector']) . '", "'
                                                                            . addslashes($buyer['referenceno']) . '", "'
                                                                                . $buyer['datasource'] . '", "'
                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                        . $userid . ', "'
                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                . $userid . ')';

                    $this->logger->write("Utilities : createimportedservice() : The SQL is " . $sql, 'r');
                    $this->db->exec(array($sql));

                    try {
                        $by = array();
                        $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE referenceno = "' . $buyer['referenceno'] . '" AND insertedby = ' . $userid));

                        foreach ($r as $obj) {
                            $by [] = $obj;
                        }

                        $buyerid = $by[0]['id'];
                        $this->db->exec(array('UPDATE tblimportedservices SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                    } catch (Exception $e) {
                        $this->logger->write("Utilities : createimportedservice() : Failed to select and update table tblimportedservices. The error message is " . $e->getMessage(), 'r');
                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createimportedservice() : The operation to create a buyer was not successful. The error message is " . $e->getMessage(), 'r');
                }
            }

            if (trim($importedseller['referenceno']) !== '' || !empty(trim($importedseller['referenceno']))) {
                try {

                    $sql = 'INSERT INTO tblimportedsellers (
                                    tin,
                                    ninbrn,
                                    PassportNum,
                                    legalname,
                                    businessname,
                                    address,
                                    mobilephone,
                                    linephone,
                                    emailaddress,
                                    placeofbusiness,
                                    type,
                                    citizineship,
                                    sector,
                                    referenceno,
                                    datasource,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                        . addslashes($importedseller['tin']) . '", "'
                            . addslashes($importedseller['ninbrn']) . '", "'
                                . addslashes($importedseller['PassportNum']) . '", "'
                                    . addslashes($importedseller['legalname']) . '", "'
                                        . addslashes($importedseller['businessname']) . '", "'
                                            . addslashes($importedseller['address']) . '", "'
                                                . addslashes($importedseller['mobilephone']) . '", "'
                                                    . addslashes($importedseller['linephone']) . '", "'
                                                        . addslashes($importedseller['emailaddress']) . '", "'
                                                            . addslashes($importedseller['placeofbusiness']) . '", "'
                                                                . $importedseller['type'] . '", "'
                                                                    . addslashes($importedseller['citizineship']) . '", "'
                                                                        . addslashes($importedseller['sector']) . '", "'
                                                                            . addslashes($importedseller['referenceno']) . '", "'
                                                                                . $importedseller['datasource'] . '", "'
                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                        . $userid . ', "'
                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                . $userid . ')';

                    $this->logger->write("Utilities : createimportedservice() : The SQL is " . $sql, 'r');
                    $this->db->exec(array($sql));

                    try {
                        $by = array();
                        $r = $this->db->exec(array('SELECT id "id" FROM tblimportedsellers WHERE referenceno = "' . $importedseller['referenceno'] . '" AND insertedby = ' . $userid));

                        foreach ($r as $obj) {
                            $by [] = $obj;
                        }

                        $importedsellerid = $by[0]['id'];
                        $this->db->exec(array('UPDATE tblimportedservices SET importedsellerid = ' . $importedsellerid . ', modifieddt = NOW(), modifiedby = ' . $userid . ' WHERE id = ' . $id));
                    } catch (Exception $e) {
                        $this->logger->write("Utilities : createimportedservice() : Failed to select and update table tblimportedservices. The error message is " . $e->getMessage(), 'r');
                    }
                } catch (Exception $e) {
                    $this->logger->write("Utilities : createimportedservice() : The operation to create a supplier was not successful. The error message is " . $e->getMessage(), 'r');
                }
            }

            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : createimportedservice() : The operation to create the imported service invoice was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }
    }


    public function mapmeasureunit($unit)
    {
        /**
         * 1. Cleanup the units
         * 2. Search the measure unit table for the equivalent
         */
        $this->logger->write("Utilities : mapmeasureunit() : The raw unit is " . $unit, 'r');
        $value = '';

        $unit = strtoupper(trim($unit));

        $unit_check = new DB\SQL\Mapper($this->db, 'tblrateunits');
        $unit_check->load(array('UPPER(erpcode)=?', $unit));
        $this->logger->write($this->db->log(true), 'r');

        if ($unit_check->dry()) {
            $this->logger->write("Utilities : mapmeasureunit() : The measure unit does not exist or is not mapped", 'r');
        } else {
            $value = $unit_check->code;
        }

        $this->logger->write("Utilities : mapmeasureunit() : The rate code is " . $value, 'r');
        return $value;
    }


    public function mapexciseduty($code)
    {
        /**
         * 1. Cleanup the units
         * 2. Search the excise duty table for the equivalent
         */
        $this->logger->write("Utilities : mapexciseduty() : The raw code is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $unit_check = new DB\SQL\Mapper($this->db, 'tblexcisedutylist');
        $unit_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($unit_check->dry()) {
            $this->logger->write("Utilities : mapexciseduty() : The excise duty does not exist or is not mapped", 'r');
        } else {
            $value = $unit_check->code;
        }

        $this->logger->write("Utilities : mapexciseduty() : The excise duty code is " . $value, 'r');
        return $value;
    }


    public function maphscode($code)
    {
        /**
         * 1. Cleanup the code
         * 2. Search the HS code table for the equivalent
         */
        $this->logger->write("Utilities : maphscode() : The raw code is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblhscodes');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : maphscode() : The HS code does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->code;
        }

        $this->logger->write("Utilities : maphscode() : The rate code is " . $value, 'r');
        return $value;
    }


    public function mapindustrycode($code)
    {
        /**
         * 1. Cleanup the industry code
         * 2. Search the industry table for the equivalent
         */
        $this->logger->write("Utilities : mapindustrycode() : The raw industry is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblindustries');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapindustrycode() : The industry code does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->code;
        }

        $this->logger->write("Utilities : mapindustrycode() : The industry code is " . $value, 'r');
        return $value;
    }


    public function mapreasoncode($code)
    {
        /**
         * 1. Cleanup the reason code
         * 2. Search the reason table for the equivalent
         */
        $this->logger->write("Utilities : mapreasoncode() : The raw reason is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblcdnotereasoncodes');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapreasoncode() : The reason code does not exist or is not mapped in the credit note reasons", 'r');

            $d_code_check = new DB\SQL\Mapper($this->db, 'tbldebitnotereasoncodes');
            $d_code_check->load(array('UPPER(erpcode)=?', $code));
            $this->logger->write($this->db->log(true), 'r');

            if ($d_code_check->dry()) {
                $this->logger->write("Utilities : mapreasoncode() : The reason code does not exist or is not mapped in the debit note reasons", 'r');
            } else {
                $value = $d_code_check->code;
            }

        } else {
            $value = $code_check->code;
        }

        $this->logger->write("Utilities : mapreasoncode() : The reason code is " . $value, 'r');
        return $value;
    }


    public function decodechoicecode($code)
    {
        /**
         * 1. Cleanup the choice code
         * 2. Search the choice table for the equivalent
         */
        $this->logger->write("Utilities : decodechoicecode() : The raw choice is " . $code, 'r');
        $name = '';

        $code = trim($code);

        $code_check = new DB\SQL\Mapper($this->db, 'tblchoices');
        $code_check->load(array('TRIM(code)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : decodechoicecode() : The choice code does not exist or is not mapped", 'r');
        } else {
            $name = $code_check->name;
        }

        $this->logger->write("Utilities : decodechoicecode() : The choice name is " . $name, 'r');
        return $name;
    }


    public function decodetaxpayertypecode($code)
    {
        /**
         * 1. Cleanup the choice code
         * 2. Search the choice table for the equivalent
         */
        $this->logger->write("Utilities : decodetaxpayertypecode() : The raw tax payer type code is " . $code, 'r');
        $name = '';

        $code = trim($code);

        $code_check = new DB\SQL\Mapper($this->db, 'tbltaxpayertypes');
        $code_check->load(array('TRIM(code)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : decodetaxpayertypecode() : The tax payer type code does not exist or is not mapped", 'r');
        } else {
            $name = $code_check->name;
        }

        $this->logger->write("Utilities : decodetaxpayertypecode() : The tax payer type name is " . $name, 'r');
        return $name;
    }


    public function decodeproductexclusioncode($code)
    {
        /**
         * 1. Cleanup the productexclusion code
         * 2. Search the productexclusion table for the equivalent
         */
        $this->logger->write("Utilities : decodeproductexclusioncode() : The raw productexclusion is " . $code, 'r');
        $name = '';

        $code = trim($code);

        $code_check = new DB\SQL\Mapper($this->db, 'tblproductexclusioncodes');
        $code_check->load(array('TRIM(code)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : decodeproductexclusioncode() : The productexclusion code does not exist or is not mapped", 'r');
        } else {
            $name = $code_check->name;
        }

        $this->logger->write("Utilities : decodeproductexclusioncode() : The productexclusion name is " . $name, 'r');
        return $name;
    }


    public function decodeproductsourcecode($code)
    {
        /**
         * 1. Cleanup the productsource code
         * 2. Search the productsource table for the equivalent
         */
        $this->logger->write("Utilities : decodeproductsourcecode() : The raw productsource is " . $code, 'r');
        $name = '';

        $code = trim($code);

        $code_check = new DB\SQL\Mapper($this->db, 'tblproductsourcecodes');
        $code_check->load(array('TRIM(code)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : decodeproductsourcecode() : The productsource code does not exist or is not mapped", 'r');
        } else {
            $name = $code_check->name;
        }

        $this->logger->write("Utilities : decodeproductsourcecode() : The productsource name is " . $name, 'r');
        return $name;
    }


    public function decodeproductstatuscode($code)
    {
        /**
         * 1. Cleanup the productstatus code
         * 2. Search the productstatus table for the equivalent
         */
        $this->logger->write("Utilities : decodeproductstatuscode() : The raw productstatus is " . $code, 'r');
        $name = '';

        $code = trim($code);

        $code_check = new DB\SQL\Mapper($this->db, 'tblproductstatuscodes');
        $code_check->load(array('TRIM(code)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : decodeproductstatuscode() : The productstatus code does not exist or is not mapped", 'r');
        } else {
            $name = $code_check->name;
        }

        $this->logger->write("Utilities : decodeproductstatuscode() : The productstatus name is " . $name, 'r');
        return $name;
    }


    public function mapstockincode($code)
    {
        /**
         * 1. Cleanup the industry code
         * 2. Search the industry table for the equivalent
         */
        $this->logger->write("Utilities : mapstockincode() : The raw stockin code is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblstockintypes');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapstockincode() : The stockin code does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->code;
        }

        $this->logger->write("Utilities : mapstockincode() : The stockin code is " . $value, 'r');
        return $value;
    }


    public function mapstockadjustmentcode($code)
    {
        /**
         * 1. Cleanup the industry code
         * 2. Search the industry table for the equivalent
         */
        $this->logger->write("Utilities : mapstockadjustmentcode() : The raw adjustment is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblstockadjustmenttypes');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapstockadjustmentcode() : The adjustment code does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->code;
        }

        $this->logger->write("Utilities : mapstockadjustmentcode() : The adjustment code is " . $value, 'r');
        return $value;
    }


    public function mapbranchcode($code)
    {
        /**
         * 1. Cleanup the branch code
         * 2. Search the branch table for the equivalent
         */
        $this->logger->write("Utilities : mapbranchcode() : The raw branch is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblbranches');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapbranchcode() : The branch code does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->uraid;
        }

        $this->logger->write("Utilities : mapbranchcode() : The branch id is " . $value, 'r');
        return $value;
    }


    public function mapbuyertypecode($code)
    {
        /**
         * 1. Cleanup the industry code
         * 2. Search the industry table for the equivalent
         */
        $this->logger->write("Utilities : mapbuyertypecode() : The raw buyer type is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblbuyertypes');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapbuyertypecode() : The buyer type code does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->code;
        }

        $this->logger->write("Utilities : mapbuyertypecode() : The buyer type code is " . $value, 'r');
        return $value;
    }


    public function mapdeliverytermcode($code)
    {
        /**
         * 1. Cleanup the industry code
         * 2. Search the industry table for the equivalent
         */
        $this->logger->write("Utilities : mapdeliverytermcode() : The raw delivery term code is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tbldeliverytermscodes');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapdeliverytermcode() : The delivery term code does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->code;
        }

        $this->logger->write("Utilities : mapdeliverytermcode() : The delivery term code is " . $value, 'r');
        return $value;
    }


    public function removecommas($amount)
    {

        $this->logger->write("Utilities : removecommas() : The raw amount is " . $amount, 'r');
        $value = $amount;//2,80,000.00

        $value = str_replace(array(','), '', $value);//280000.00

        $this->logger->write("Utilities : removecommas() : The final amount is " . $value, 'r');
        return $value;
    }


    public function removeunitsfromrate($amount)
    {

        $this->logger->write("Utilities : removeunitsfromrate() : The raw amount is " . $amount, 'r');
        $value = $amount;//56,000.00/Pcs  25,000.00/Pcs

        $pos = stripos($value, '/');
        //$o_len = strlen($value);
        //$len = 0;
        $start = 0;

        $value = substr($value, $start, $pos);

        $value = str_replace(array(',', '(', ')'), '', $value);//56000.00

        $this->logger->write("Utilities : removeunitsfromrate() : The final amount is " . $value, 'r');
        return $value;
    }


    public function removecommasfromamount($amount)
    {

        $this->logger->write("Utilities : removecommasfromamount() : The raw amount is " . $amount, 'r');
        $value = $amount;//56,000.00$

        $value = preg_replace('/[^0-9.]/', '', $value);//Remove non-digit characters, except periods
        //$value = str_replace(array(',', '(', ')'), '' , $value);//56000.00

        $this->logger->write("Utilities : removecommasfromamount() : The final amount is " . $value, 'r');
        return $value;
    }


    public function removeunitsfromqty($amount)
    {

        $this->logger->write("Utilities : removeunitsfromqty() : The raw amount is " . $amount, 'r');
        $value = $amount;//5 Pcs

        $pos = stripos($value, ' ');
        //$o_len = strlen($value);
        //$len = 0;
        $start = 0;

        $value = substr($value, $start, $pos);

        $value = str_replace(array(',', '(', ')'), '', $value);//5.00

        $this->logger->write("Utilities : removeunitsfromqty() : The final amount is " . $value, 'r');
        return $value;
    }


    public function computeExciseDuty($userid, $product_code, $qty, $unit, $amount, $discount, $discountpct)
    {
        $tax_amount = 0;

        $tax101 = array();
        $tax102 = array();
        $tax103 = array();

        $tax101_amount = 0;
        $tax102_amount = 0;
        $tax103_amount = 0;

        //Check if the product is already uploaded
        $pdct = new products($this->db);
        $pdct->getByErpCode(trim($product_code));

        $excisedutylist = new excisedutylists($this->db);
        $excisedutylist->getByCode(trim($pdct->exciseDutyCode));

        $excisedutydetailslist = new excisedutydetailslists($this->db);
        $temp = $excisedutydetailslist->getByExciseDutyId(trim($excisedutylist->uraid));

        $this->logger->write("Utilities : computeExciseDuty() : The excise duty code is " . $pdct->exciseDutyCode, 'r');
        $this->logger->write("Utilities : computeExciseDuty() : The excise duty URA Id is " . $excisedutylist->uraid, 'r');

        $this->logger->write("Utilities : computeExciseDuty() : The LED rate count: " . sizeof($temp), 'r');

        foreach ($temp as $obj) {
            $this->logger->write("Utilities : computeExciseDuty() : The excise duty details URA Id is " . $obj->uraid, 'r');
            $this->logger->write("Utilities : computeExciseDuty() : The excise duty details Rate is " . $obj->rate, 'r');
            $this->logger->write("Utilities : computeExciseDuty() : The excise duty details Typeis " . $obj->type, 'r'); //101-Percentage, 1012-Unit of measurement, 103-Nil
            $this->logger->write("Utilities : computeExciseDuty() : The excise duty details Unit is " . $obj->unit, 'r');
            $this->logger->write("Utilities : computeExciseDuty() : The excise duty details Currency is " . $obj->currency, 'r');

            if ($obj->type == '101') {
                // 101-Percentage
                $tax101_amount = $unit * ((float)$obj->rate / 100) * (float)$qty;

                $tax101[] = array(
                    'itemCode' => $product_code,
                    'qty' => $qty,
                    'total' => 0,
                    'tax' => 0,
                    'exciseFlag' => 1,
                    'categoryId' => $pdct->exciseDutyCode,
                    'categoryName' => $pdct->exciseDutyName,
                    'exciseRate' => ((float)$obj->rate / 100), // $obj->rate,
                    'exciseRule' => 1,
                    'exciseTax' => $unit * ((float)$obj->rate / 100) * (float)$qty,
                    'pack' => $pdct->pack,
                    'stick' => $pdct->stick,
                    'exciseUnit' => $obj->unit,
                    'exciseCurrency' => $obj->currency,
                    'exciseRateName' => $obj->rate . "%"
                );
            } elseif ($obj->type == '102') {
                // 1012-Unit of measurement
                $tax102_amount = (float)$qty * (float)$pdct->pieceunitprice * (float)$pdct->piecescaledvalue;

                $tax102[] = array(
                    'itemCode' => $product_code,
                    'qty' => $qty,
                    'total' => 0,
                    'tax' => 0,
                    'exciseFlag' => 1,
                    'categoryId' => $pdct->exciseDutyCode,
                    'categoryName' => $pdct->exciseDutyName,
                    'exciseRate' => $obj->rate,
                    'exciseRule' => 2,
                    'exciseTax' => (float)$qty * (float)$pdct->pieceunitprice * (float)$pdct->piecescaledvalue,
                    'pack' => $pdct->pack,
                    'stick' => $pdct->stick,
                    'exciseUnit' => $obj->unit,
                    'exciseCurrency' => $obj->currency,
                    'exciseRateName' => $obj->rate
                );
            } else {
                // 103-Nil
                $tax103_amount = 0;

                $tax103[] = array(
                    'itemCode' => $product_code,
                    'qty' => $qty,
                    'total' => 0,
                    'tax' => 0,
                    'exciseFlag' => 1,
                    'categoryId' => $pdct->exciseDutyCode,
                    'categoryName' => $pdct->exciseDutyName,
                    'exciseRate' => $obj->rate,
                    'exciseRule' => 3,
                    'exciseTax' => 0,
                    'pack' => $pdct->pack,
                    'stick' => $pdct->stick,
                    'exciseUnit' => $obj->unit,
                    'exciseCurrency' => $obj->currency,
                    'exciseRateName' => $obj->rate
                );
            }
        }

        $this->logger->write("Utilities : computeExciseDuty() : The % tax is: " . $tax101_amount, 'r');
        $this->logger->write("Utilities : computeExciseDuty() : The UoM tax is: " . $tax102_amount, 'r');
        $this->logger->write("Utilities : computeExciseDuty() : The Nil tax is: " . $tax103_amount, 'r');

        $tax_amount = max($tax101_amount, $tax102_amount, $tax103_amount);
        $this->logger->write("Utilities : computeExciseDuty() : The highest tax is: " . $tax_amount, 'r');

        if ($tax_amount == $tax101_amount) {
            return $tax101;
        } elseif ($tax_amount == $tax102_amount) {
            return $tax102;
        } elseif ($tax_amount == $tax103_amount) {
            return $tax103;
        } else {
            return array(
                array(
                    'itemCode' => $product_code,
                    'qty' => $qty,
                    'total' => 0,
                    'tax' => 0,
                    'exciseFlag' => 1,
                    'categoryId' => $pdct->exciseDutyCode,
                    'categoryName' => $pdct->exciseDutyName,
                    'exciseRate' => null,
                    'exciseRule' => null,
                    'exciseTax' => null,
                    'pack' => $pdct->pack,
                    'stick' => $pdct->stick,
                    'exciseUnit' => null,
                    'exciseCurrency' => null,
                    'exciseRateName' => null
                )
            );
        }
    }


    public function createproduct($product, $userid)
    {
        /**
         * 1. Pick the fields from the product array
         * 2. Convert the following fields
         * 2.1  measureUnit (getmeasureunit)
         * 2.2  currency (getcurrency)
         * 2.3  commodityCategoryId (getcommodity)
         * 2.4  haveExciseTax (getchoice)
         * 2.5  havePieceUnit (getchoice)
         * 3. Insert into the table tblproductdetails
         */

        try {
            $product['piecemeasureunit'] = (trim($product['piecemeasureunit']) == '' ? 'NULL' : $product['piecemeasureunit']);
            $product['pieceunitprice'] = (trim($product['pieceunitprice']) == '' ? 'NULL' : $product['pieceunitprice']);
            $product['packagescaledvalue'] = (trim($product['packagescaledvalue']) == '' ? 'NULL' : $product['packagescaledvalue']);
            $product['piecescaledvalue'] = (trim($product['piecescaledvalue']) == '' ? 'NULL' : $product['piecescaledvalue']);
            $product['exciseDutyCode'] = (trim($product['exciseDutyCode']) == '' ? 'NULL' : $product['exciseDutyCode']);
            $product['uraquantity'] = (trim($product['uraquantity']) == '' ? 'NULL' : $product['uraquantity']);
            $product['erpquantity'] = (trim($product['erpquantity']) == '' ? 'NULL' : $product['erpquantity']);
            $product['purchaseprice'] = (trim($product['purchaseprice']) == '' ? 'NULL' : $product['purchaseprice']);
            $product['haveotherunit'] = (trim($product['haveotherunit']) == '' ? 'NULL' : $product['haveotherunit']);

            $product['isexempt'] = (trim($product['isexempt']) == '' ? 'NULL' : $product['isexempt']);
            $product['iszerorated'] = (trim($product['iszerorated']) == '' ? 'NULL' : $product['iszerorated']);
            $product['source'] = (trim($product['source']) == '' ? 'NULL' : $product['source']);
            $product['exclusion'] = (trim($product['exclusion']) == '' ? 'NULL' : $product['exclusion']);
            $product['statuscode'] = (trim($product['statuscode']) == '' ? 'NULL' : $product['statuscode']);
            $product['taxrate'] = (trim($product['taxrate']) == '' ? 'NULL' : $product['taxrate']);
            $product['serviceMark'] = (trim($product['serviceMark']) == '' ? 'NULL' : $product['serviceMark']);

            $product['goodsTypeCode'] = (trim($product['goodsTypeCode']) == '' ? 'NULL' : $product['goodsTypeCode']);
            $product['hsCode'] = (trim($product['hsCode']) == '' ? 'NULL' : $product['hsCode']);
            $product['customsmeasureunit'] = (trim($product['customsmeasureunit']) == '' ? 'NULL' : $product['customsmeasureunit']);
            $product['customsunitprice'] = (trim($product['customsunitprice']) == '' ? 'NULL' : $product['customsunitprice']);
            $product['packagescaledvaluecustoms'] = (trim($product['packagescaledvaluecustoms']) == '' ? 'NULL' : $product['packagescaledvaluecustoms']);
            $product['customsscaledvalue'] = (trim($product['customsscaledvalue']) == '' ? 'NULL' : $product['customsscaledvalue']);
            $product['weight'] = (trim($product['weight']) == '' ? 'NULL' : $product['weight']);

            /**
             * Author: Francis Lubanga <frncslubanga@gmail.com>
             * Date: 2026-01-10
             * Description: Add additional data points to the product creation operations.
             */
            $product['haveCustomsUnit'] = (trim($product['haveCustomsUnit']) == '' ? 'NULL' : $product['haveCustomsUnit']);
            $product['pack'] = (trim($product['pack']) == '' ? 'NULL' : $product['pack']);
            $product['stick'] = (trim($product['stick']) == '' ? 'NULL' : $product['stick']);
            $product['exciseDutyName'] = (trim($product['exciseDutyName']) == '' ? '' : $product['exciseDutyName']);
            $product['exciseRate'] = (trim($product['exciseRate']) == '' ? '' : $product['exciseRate']);

            $userid = empty($userid) || trim($userid) == '' ? $this->userid : $userid;

            $sql = 'INSERT INTO tblproductdetails (
                                    uraproductidentifier,
                                    erpid,
                                    erpcode,
                                    name,
                                    code,
                                    measureunit,
                                    unitprice,
                                    currency,
                                    commoditycategorycode,
                                    hasexcisetax,
                                    description,
                                    stockprewarning,
                                    piecemeasureunit,
                                    havepieceunit,
                                    pieceunitprice,
                                    packagescaledvalue,
                                    piecescaledvalue,
                                    exciseDutyCode,
                                    uraquantity,
                                    erpquantity,
                                    purchaseprice,
                                    stockintype,
                                    isexempt,
                                    iszerorated,
                                    source,
                                    exclusion,
                                    statuscode,
                                    taxrate,
                                    haveotherunit,
                                    serviceMark,
                                    goodsTypeCode,
                                    hsCode,
                                    customsmeasureunit,
                                    customsunitprice,
                                    packagescaledvaluecustoms,
                                    customsscaledvalue,
                                    weight,
                                    haveCustomsUnit,
                                    pack,
                                    stick,
                                    exciseDutyName,
                                    exciseRate,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                . addslashes($product['uraproductidentifier']) . '", "'
                    . addslashes($product['erpid']) . '", "'
                        . addslashes($product['erpcode']) . '", "'
                            . addslashes($product['name']) . '", "'
                                . addslashes($product['code']) . '", "'
                                    . addslashes($product['measureunit']) . '", '
                                        . $product['unitprice'] . ', '
                                            . $product['currency'] . ', "'
                                                . addslashes($product['commoditycategorycode']) . '", '
                                                    . $product['hasexcisetax'] . ', "'
                                                        . addslashes($product['description']) . '", '
                                                            . $product['stockprewarning'] . ', "'
                                                                . $product['piecemeasureunit'] . '", '
                                                                    . $product['havepieceunit'] . ', '
                                                                        . $product['pieceunitprice'] . ', '
                                                                            . $product['packagescaledvalue'] . ', '
                                                                                . $product['piecescaledvalue'] . ', "'
                                                                                    . $product['exciseDutyCode'] . '", '
                                                                                        . $product['uraquantity'] . ', '
                                                                                            . $product['erpquantity'] . ', '
                                                                                                . $product['purchaseprice'] . ', "'
                                                                                                    . addslashes($product['stockintype']) . '", '
                                                                                                        . $product['isexempt'] . ', '
                                                                                                            . $product['iszerorated'] . ', '
                                                                                                                . $product['source'] . ', '
                                                                                                                    . $product['exclusion'] . ', '
                                                                                                                        . $product['statuscode'] . ', '
                                                                                                                            . $product['taxrate'] . ', '
                                                                                                                                . $product['haveotherunit'] . ', '
                                                                                                                                    . $product['serviceMark'] . ', '
                                                                                                                                    . $product['goodsTypeCode'] . ', "'
                                                                                                                                    . $product['hsCode'] . '", "'
                                                                                                                                    . $product['customsmeasureunit'] . '", '
                                                                                                                                    . $product['customsunitprice'] . ', '
                                                                                                                                    . $product['packagescaledvaluecustoms'] . ', '
                                                                                                                                    . $product['customsscaledvalue'] . ', '
                                                                                                                                    . $product['weight'] . ', '
                                                                                                                                    . $product['haveCustomsUnit'] . ', '
                                                                                                                                    . $product['pack'] . ', '
                                                                                                                                    . $product['stick'] . ', "'
                                                                                                                                    . addslashes($product['exciseDutyName']) . '", "'
                                                                                                                                    . addslashes($product['exciseRate']) . '", "'
                                                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                                                        . $userid . ', "'
                                                                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                                                                . $userid . ')';

            $this->logger->write("Utilities : createproduct() : The SQL is " . $sql, 'r');
            $this->db->exec(array($sql));

            return true;
        } catch (Exception $e) {
            $this->logger->write("Utilities : createproduct() : The operation to create the product was not successful. The error message is " . $e->getMessage(), 'r');
            return false;
        }

    }


    public function mapcurrency($code)
    {
        /**
         * 1. Cleanup the currency
         * 2. Search the currency table for the equivalent
         */
        $this->logger->write("Utilities : mapcurrency() : The raw currency is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblcurrencies');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapcurrency() : The currency does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->code;
        }

        $this->logger->write("Utilities : mapcurrency() : The currency code is " . $value, 'r');
        return $value;
    }


    public function mapcommodity($code)
    {
        /**
         * 1. Cleanup the commodity
         * 2. Search the commodity table for the equivalent
         */
        $this->logger->write("Utilities : mapcommodity() : The raw commodity is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblcommoditycategories');
        $code_check->load(array('UPPER(commodityname)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapcommodity() : The commodity does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->commoditycode;
        }

        $this->logger->write("Utilities : mapcommodity() : The commodity code is " . $value, 'r');
        return $value;
    }


    public function mapcommodity_v2($code)
    {
        /**
         * 1. Cleanup the commodity
         * 2. Search the commodity table for the equivalent
         */
        $this->logger->write("Utilities : mapcommodity_v2() : The raw commodity is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblcommoditycategories');
        $code_check->load(array('UPPER(erpcode)=?', $code));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapcommodity_v2() : The commodity does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->commoditycode;
        }

        $this->logger->write("Utilities : mapcommodity_v2() : The commodity code is " . $value, 'r');
        return $value;
    }


    public function mapcommodity_v3($code)
    {
        /**
         * 1. Cleanup the commodity
         * 2. Search the commodity table for the equivalent
         */
        $this->logger->write("Utilities : mapcommodity_v2() : The raw commodity is " . $code, 'r');
        $value = '';

        $code = strtoupper(trim($code));

        $code_check = new DB\SQL\Mapper($this->db, 'tblcommoditycategories');
        $code_check->load(array('UPPER(commodityname) LIKE ?', '%' . $code . '%'));
        $this->logger->write($this->db->log(true), 'r');

        if ($code_check->dry()) {
            $this->logger->write("Utilities : mapcommodity_v2() : The commodity does not exist or is not mapped", 'r');
        } else {
            $value = $code_check->commoditycode;
        }

        $this->logger->write("Utilities : mapcommodity_v2() : The commodity code is " . $value, 'r');
        return $value;
    }


    public function getsettings($userid = null, $groupid = null)
    {
        //$this->logger->write("Utilities : getsettings() : Processing settings", 'r');
        $sql = '';

        $data = array();

        if (is_null($groupid)) {
            $sql = 'SELECT s.id "ID",
                    s.code "Code",
                    s.value "Value",
                    s.description "Description",
                    s.inserteddt "Inserted Date",
                    s.modifieddt "Modified Date",
                    NULL "Actions"
                FROM tblsettings s';
        } else {
            $sql = 'SELECT s.id "ID",
                    s.code "Code",
                    s.value "Value",
                    s.description "Description",
                    s.inserteddt "Inserted Date",
                    s.modifieddt "Modified Date",
                    NULL "Actions"
                FROM tblsettings s
                WHERE s. groupid in (' . $groupid . ')';
        }

        try {
            $dtls = $this->db->exec($sql);
        } catch (Exception $e) {
            $this->logger->write("Utilities : getsettings() : Error " . $e->getMessage(), 'r');
            return null;
        }

        foreach ($dtls as $obj) {
            $data [] = $obj;
        }

        return $data;
    }


    public function fetchbranches($userid)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : fetchbranches() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : fetchbranches() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : fetchbranches() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : fetchbranches() : The current timezone is " . date_default_timezone_get(), 'r');

        try {
            $this->logger->write("Utilities : fetchbranches() : Fetching branches started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T138';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $data = array(
                'data' => array(
                    'content' => '',
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);
            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : uploadproduct() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : fetchbranches() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
            } else {
                $this->logger->write("Utilities : fetchbranches() : The API call was not successful. The return code is: " . $returninfo['returnCode'], 'r');
            }

            if ($dataDesc['zipCode'] == '1') {
                $this->logger->write("Utilities : fetchbranches() : The response is zipped", 'r');
                return gzdecode(base64_decode($content));
            } else {
                $this->logger->write("Utilities : fetchbranches() : The response is NOT zipped", 'r');
                return base64_decode($content);
            }
        } catch (Exception $e) {
            $this->logger->write("Utilities : fetchbranches() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    public function stockquery($userid, $branchuraid, $productcode)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : stockquery() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : stockquery() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : stockquery() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : stockquery() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : stockquery() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : stockquery() : The product code is " . $productcode, 'r');

        try {
            $this->logger->write("Utilities : stockquery() : Querying stock started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T128';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $product = new products($this->db);
            //$product->getByID($id);
            $product->getByErpCode(trim($productcode));

            $product = array(
                'id' => $product->uraproductidentifier,
                'branchId' => empty($branchuraid) ? $devicedetails->branchId : $branchuraid,

            );

            $product = json_encode($product); //JSON-ifiy
            $product = base64_encode($product); //base64 encode
            $this->logger->write("Utilities : stockquery() : The encoded stock is " . $product, 'r');

            $data = array(
                'data' => array(
                    'content' => $product,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);
            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : stockquery() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : stockquery() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : stockquery() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : stockquery() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : stockquery() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : stockquery() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    public function batchstockout($userid, $branchuraid, $products, $adjustmenttype, $remarks)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : batchstockout() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : batchstockout() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : batchstockout() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : batchstockout() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : batchstockout() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : batchstockout() : Stocking out started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T131';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            $product = new products($this->db);

            $t_stock = array();

            foreach ($products as $obj) {
                $product->getByErpCode(trim($obj['productCode']));

                $t_stock[] = array(
                    'commodityGoodsId' => empty($product->uraproductidentifier) ? '' : $product->uraproductidentifier,
                    'quantity' => empty($obj['quantity']) ? '' : $obj['quantity'],
                    'unitPrice' => empty($obj['unitPrice']) ? '' : $obj['unitPrice'],
                    'goodsCode' => empty($product->code) ? '' : $product->code,
                    'measureUnit' => empty($product->measureunit) ? '' : $product->measureunit
                );

            }

            $stock = array(
                'goodsStockIn' => array(
                    'operationType' => trim($this->appsettings['STOCKOUTOPERATIONTYPE']),
                    'supplierTin' => '',
                    'supplierName' => '',
                    'adjustType' => $adjustmenttype,
                    'remarks' => $remarks,
                    'stockInDate' => '',
                    /**
                     *Date: 2020-11-18
                     *Authour: Francis Lubanga
                     *Description: Fixing error code 2177
                     *
                     */
                    //'stockInType' => empty($product->stockintype)? '' : $product->stockintype,
                    'stockInType' => '',
                    'productionBatchNo' => '',
                    'productionDate' => '',
                    'branchId' => empty($branchuraid) ? $devicedetails->branchId : $branchuraid,
                    'invoiceNo' => '',
                    'isCheckBatchNo' => '0'
                ),
                'goodsStockInItem' => $t_stock
            );

            $stock = json_encode($stock); //JSON-ifiy
            $stock = base64_encode($stock); //base64 encode
            $this->logger->write("Utilities : batchstockout() : The encoded stock is " . $stock, 'r');

            $data = array(
                'data' => array(
                    'content' => $stock,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);
            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : batchstockout() : The response content is: " . $content, 'r');
            //$this->logger->write("Utilities : batchstockout() : The return info is: " . $returninfo['returnCode'], 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : batchstockout() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : batchstockout() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : batchstockout() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : batchstockout() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');

                if (trim($content) == '' || empty($content)) {
                    $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
                } else {
                    /**
                     * Modification Date: 2022-06-14
                     * Modified By: Francis Lubanga
                     * Description: Resolving issue of sending the generic PARTIAL ERROR message
                     * */

                    if ($dataDesc['zipCode'] == '1') {
                        $this->logger->write("Utilities : batchstockout() : The response is zipped", 'r');
                        $content = gzdecode(base64_decode($content));
                    } else {
                        $this->logger->write("Utilities : batchstockout() : The response is NOT zipped", 'r');
                        $content = base64_decode($content);
                    }
                }
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : batchstockout() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    public function downloadcreditnoteappldetails($userid, $id)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : downloadcreditnoteappldetails() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloadcreditnoteappldetails() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : downloadcreditnoteappldetails() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : downloadcreditnoteappldetails() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : downloadcreditnoteappldetails() : The user id is " . $userid, 'r');

        if (trim($id) == '' || empty($id)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : downloadcreditnoteappldetails() : The application id is empty.", 'r');
        }

        try {
            $this->logger->write("Utilities : downloadcreditnoteappldetails() : Downloading credit note details started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T112';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);


            $creditnote_u = array(
                'id' => $id
            );

            //print_r($creditnote_u);

            $creditnote_u = json_encode($creditnote_u); //JSON-ifiy
            $creditnote_u = base64_encode($creditnote_u); //base64 encode
            $this->logger->write("Utilities : downloadcreditnoteappldetails() : The encoded invoice is " . $creditnote_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $creditnote_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : downloadcreditnoteappldetails() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : downloadcreditnoteappldetails() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : downloadcreditnoteappldetails() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : downloadcreditnoteappldetails() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : downloadcreditnoteappldetails() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : downloadcreditnoteappldetails() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    public function checktaxpayer($userid, $tin, $commodity)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : checktaxpayer() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : checktaxpayer() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : checktaxpayer() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : checktaxpayer() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : checktaxpayer() : The user id is " . $userid, 'r');
        $this->logger->write("Utilities : checktaxpayer() : The TIN is " . $tin, 'r');

        if (trim($tin) == '' || empty($tin)) {
            return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));
            $this->logger->write("Utilities : checktaxpayer() : The TIN is empty.", 'r');
        }

        try {
            $this->logger->write("Utilities : checktaxpayer() : Checking the taxpayer started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T137';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);


            $taxpayer_u = array(
                'tin' => strval($tin),
                'commodityCategoryCode' => strval($commodity)
            );

            //print_r($taxpayer_u);

            $taxpayer_u = json_encode($taxpayer_u); //JSON-ifiy
            $taxpayer_u = base64_encode($taxpayer_u); //base64 encode
            $this->logger->write("Utilities : checktaxpayer() : The encoded data is " . $taxpayer_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $taxpayer_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : checktaxpayer() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : checktaxpayer() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : checktaxpayer() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : checktaxpayer() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : checktaxpayer() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : checktaxpayer() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    public function invoiceinquiry($userid, $pageNo = '1', $pageSize = '10', $queryType = '1', $invoiceKind = '1', $referenceNo = '', $deviceNo = '', $buyerTin = '', $buyerNinBrn = '', $buyerLegalName = '', $startDate = '', $endDate = '', $branchName = '', $dataSource = '')
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : invoiceinquiry() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : invoiceinquiry() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : invoiceinquiry() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : invoiceinquiry() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : invoiceinquiry() : The user id is " . $userid, 'r');


        try {
            $this->logger->write("Utilities : invoiceinquiry() : Invoice inquiry started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T106';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);


            $invoice = array(
                'oriInvoiceNo' => '',
                'invoiceNo' => '',
                'deviceNo' => $deviceNo,
                'buyerTin' => $buyerTin,
                'buyerNinBrn' => $buyerNinBrn,
                'buyerLegalName' => $buyerLegalName,
                'combineKeywords' => '',
                'invoiceType' => '1,2,4,5',
                'invoiceKind' => $invoiceKind,
                'isInvalid' => '',
                'isRefund' => '',
                'startDate' => $startDate,
                'endDate' => $endDate,
                'pageNo' => $pageNo,
                'pageSize' => $pageSize,
                'referenceNo' => $referenceNo,
                'branchName' => $branchName,
                'queryType' => $queryType,
                'dataSource' => $dataSource

            );

            //print_r($debitnote_u);

            $invoice = json_encode($invoice); //JSON-ifiy
            $invoice = base64_encode($invoice); //base64 encode
            $this->logger->write("Utilities : invoiceinquiry() : The encoded invoice is " . $invoice, 'r');

            $data = array(
                'data' => array(
                    'content' => $invoice,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : invoiceinquiry() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : invoiceinquiry() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : invoiceinquiry() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : invoiceinquiry() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : invoiceinquiry() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : invoiceinquiry() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }


    public function uploadimportedservice($userid, $branchuraid, $importedseller, $invoicedetails, $goods, $payments, $taxes)
    {
        $web = \Web::instance();
        $url = $this->appsettings['EFRIS_ENDPOINT'];//api endpoint
        $content = json_encode(new stdClass());// create an empty JSON
        //date_default_timezone_set('UTC');//set timezone to UTC
        $this->logger->write("Utilities : uploadimportedservice() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : uploadimportedservice() : The current timezone is " . date_default_timezone_get(), 'r');

        if (trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))) {
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }

        $this->logger->write("Utilities : uploadimportedservice() : The current time is " . date('Y-m-d H:i:s'), 'r');
        $this->logger->write("Utilities : uploadimportedservice() : The current timezone is " . date_default_timezone_get(), 'r');

        $this->logger->write("Utilities : uploadimportedservice() : The user id is " . $userid, 'r');

        try {
            $this->logger->write("Utilities : uploadimportedservice() : Uploading invoice started", 'r');
            $header = array('Content-Type: application/json');
            $interfaceCode = 'T109';
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            $this->logger->write($this->db->log(true), 'r');

            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);

            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);

            //$invoice = $invoicedetails;

            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);

            $t_goods = array();
            $t_taxes = array();
            $t_payments = array();
            $t_summary = array();

            $netamount = 0;
            $taxamount = 0;
            $grossamount = 0;
            $itemcount = 0;

            try {
                //$temp = $goods;
                $this->logger->write("Utilities : uploadimportedservice() : The GOOD is " . $goods[0]['item'], 'r');

                $i = 0;
                foreach ($goods as $obj) {
                    $this->logger->write("Utilities : uploadimportedservice() : The unit price is " . round($obj['unitprice'], 8), 'r');
                    $this->logger->write("Utilities : uploadimportedservice() : The total is " . floor($obj['total'] * 100) / 100, 'r');
                    $this->logger->write("Utilities : uploadimportedservice() : The tax is " . floor($obj['tax'] * 100) / 100, 'r');

                    if ($obj['deemedflag'] == '1') {
                        $obj['item'] = $obj['item'] . " (Deemed)";

                        //Truncate
                        //$obj['unitprice'] = round($obj['unitprice'], 8);
                        $obj['total'] = floor($obj['total'] * 100) / 100;
                        $obj['tax'] = floor($obj['tax'] * 100) / 100;
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : floor($obj['discounttotal'] * 100) / 100;

                        //Ensure 2 decimal places
                        /*$obj['unitprice'] = $this->truncatenumber($obj['unitprice'], 8);
                         $obj['total'] = $this->truncatenumber($obj['total'], 4);
                         $obj['tax'] = $this->truncatenumber($obj['tax'], 4);
                         $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00')? NULL : $this->truncatenumber($obj['discounttotal'], 4);*/

                    } else {
                        //Round off
                        //$obj['unitprice'] = round($obj['unitprice'], 8);
                        $obj['total'] = round($obj['total'], 4);
                        $obj['tax'] = round($obj['tax'], 4);
                        $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : round($obj['discounttotal'], 4);
                    }

                    //Ensure the right decimal places
                    $obj['qty'] = $this->truncatenumber($obj['qty'], 8);
                    $obj['unitprice'] = $this->truncatenumber($obj['unitprice'], 8);
                    $obj['total'] = $this->truncatenumber($obj['total'], 4);
                    $obj['tax'] = $this->truncatenumber($obj['tax'], 4);
                    $obj['discounttotal'] = (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? null : $this->truncatenumber($obj['discounttotal'], 4);

                    $t_goods[] = array(
                        'item' => empty($obj['item']) ? '' : $obj['item'],
                        'itemCode' => empty($obj['itemcode']) ? '' : $obj['itemcode'],
                        'qty' => empty($obj['qty']) ? '' : strval($obj['qty']),
                        'unitOfMeasure' => empty($obj['unitofmeasure']) ? '' : $obj['unitofmeasure'],
                        'unitPrice' => empty($obj['unitprice']) ? '' : strval($obj['unitprice']),
                        'total' => empty($obj['total']) ? '' : strval($obj['total']),
                        'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '' : number_format($obj['taxrate'], 4, '.', '')),
                        'tax' => empty($obj['tax']) ? '' : strval($obj['tax']),
                        'discountTotal' => (empty($obj['discounttotal']) || $obj['discounttotal'] == '0.00') ? '' : strval($obj['discounttotal']),
                        /**
                         * Modification Date: 2023-05-16
                         * Modified By: Francis Lubanga
                         * Description: 1214 - goodsDetails-->discountTaxRate:If 'discountFlag' is '2', 'discountTaxRate' must be empty!Collection index:0
                         * */
                        'discountTaxRate' => (empty($obj['discounttaxrate']) || $obj['discounttaxrate'] == '0.00' || empty($obj['discountflag']) || trim($obj['discountflag']) == '2') ? '' : $obj['discounttaxrate'],
                        'orderNumber' => strval($i),
                        'discountFlag' => empty($obj['discountflag']) ? '2' : $obj['discountflag'],
                        'deemedFlag' => empty($obj['deemedflag']) ? '2' : $obj['deemedflag'],
                        'exciseFlag' => empty($obj['exciseflag']) ? '2' : $obj['exciseflag'],
                        'categoryId' => empty($obj['categoryid']) ? '' : $obj['categoryid'],
                        'categoryName' => empty($obj['categoryname']) ? '' : $obj['categoryname'],
                        'goodsCategoryId' => empty($obj['goodscategoryid']) ? '' : $obj['goodscategoryid'],
                        'goodsCategoryName' => empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'],
                        'exciseRate' => empty($obj['exciserate']) ? '' : $obj['exciserate'],
                        'exciseRule' => empty($obj['exciserule']) ? '' : $obj['exciserule'],
                        'exciseTax' => empty($obj['excisetax']) ? '' : $obj['excisetax'],
                        'pack' => empty($obj['pack']) ? '' : $obj['pack'],
                        'stick' => empty($obj['stick']) ? '' : $obj['stick'],
                        'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                        'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                        'exciseRateName' => empty($obj['exciseratename']) ? '' : $obj['exciseratename']
                    );

                    $i = $i + 1;

                    //If there is a discount, add a discount line below the item
                    if ($obj['discounttotal'] < 0) {
                        $t_goods[] = array(
                            'item' => empty($obj['item']) ? '' : $obj['item'] . " (Discount)",
                            'itemCode' => empty($obj['itemcode']) ? '' : $obj['itemcode'],
                            'qty' => '',
                            'unitOfMeasure' => '',
                            'unitPrice' => '',
                            'total' => empty($obj['discounttotal']) ? '' : strval($obj['discounttotal']),
                            'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '' : $obj['taxrate']),
                            /**
                             * Modification Date: 2020-11-15
                             * Modified By: Francis Lubanga
                             * Description: Resolving error code 1200 - goodsDetails-->tax:cannot be empty!Collection index:1
                             * Modification Date: 2021-01-26
                             * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                             * */
                            //'tax' => '',
                            'tax' => strval(number_format((($obj['discounttotal'] / ($obj['taxrate'] + 1)) * $obj['taxrate']), 4, '.', '')),
                            'discountTotal' => '',
                            'discountTaxRate' => empty($obj['discounttaxrate']) ? '' : $obj['discounttaxrate'],
                            'orderNumber' => strval($i),
                            'discountFlag' => '0',
                            'deemedFlag' => empty($obj['deemedflag']) ? '2' : $obj['deemedflag'],
                            'exciseFlag' => empty($obj['exciseflag']) ? '2' : $obj['exciseflag'],
                            'categoryId' => empty($obj['categoryid']) ? '' : $obj['categoryid'],
                            'categoryName' => empty($obj['categoryname']) ? '' : $obj['categoryname'],
                            'goodsCategoryId' => empty($obj['goodscategoryid']) ? '' : $obj['goodscategoryid'],
                            'goodsCategoryName' => empty($obj['goodscategoryname']) ? '' : $obj['goodscategoryname'],
                            'exciseRate' => empty($obj['exciserate']) ? '' : $obj['exciserate'],
                            'exciseRule' => empty($obj['exciserule']) ? '' : $obj['exciserule'],
                            'exciseTax' => empty($obj['excisetax']) ? '' : $obj['excisetax'],
                            'pack' => empty($obj['pack']) ? '' : $obj['pack'],
                            'stick' => empty($obj['stick']) ? '' : $obj['stick'],
                            'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                            'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                            'exciseRateName' => empty($obj['exciseratename']) ? '' : $obj['exciseratename']
                        );

                        $i = $i + 1;
                    }

                    $netamount = $netamount + $obj['total'];
                    $taxamount = $taxamount + $obj['tax'];

                    $grossamount = $grossamount + $obj['total'];
                    $itemcount = $itemcount + 1;
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploadimportedservice() : The operation to retrive the good details was not successful. The error messages is " . $e->getMessage(), 'r');
            }

            //var_dump($goods);
            $deemedflag = 'N';


            try {
                //$temp = $taxes;

                if ($this->vatRegistered == 'Y') {

                    //RESET THE SUMMARIES
                    $netamount = 0;
                    $taxamount = 0;
                    $grossamount = 0;
                    $itemcount = 0;

                    foreach ($taxes as $obj) {

                        /**
                         * Modification Date: 2021-01-26
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         * */
                        if (trim($obj['discountflag']) == '1') {
                            $obj['d_grossamount'] = $obj['discounttotal'];/*Should be a negative #*/
                            $obj['d_taxamount'] = ($obj['d_grossamount'] / ($obj['discounttaxrate'] + 1)) * $obj['discounttaxrate'];
                            $obj['d_netamount'] = $obj['d_grossamount'] - $obj['d_taxamount'];

                            $this->logger->write("Utilities : uploadimportedservice() : Calculating taxes. The d_grossamount is " . $obj['d_grossamount'], 'r');
                            $this->logger->write("Utilities : uploadimportedservice() : Calculating taxes. The d_taxamount is " . $obj['d_taxamount'], 'r');
                            $this->logger->write("Utilities : uploadimportedservice() : Calculating taxes. The d_netamount is " . $obj['d_netamount'], 'r');
                        }


                        if (strtoupper(trim($obj['taxcategory'])) == 'D: DEEMED (18%)') {
                            $obj['netamount'] = floor(($obj['netamount'] + $obj['d_netamount']) * 100) / 100;
                            $obj['taxamount'] = floor(($obj['taxamount'] + $obj['d_taxamount']) * 100) / 100;
                            //$obj['grossamount'] = floor($obj['grossamount']*100)/100;
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $obj['netamount'] = number_format($obj['netamount'], 4, '.', '');
                            $obj['taxamount'] = number_format($obj['taxamount'], 4, '.', '');
                            $obj['grossamount'] = number_format($obj['grossamount'], 4, '.', '');

                            $deemedflag = 'Y';
                        } else {
                            $obj['netamount'] = round(($obj['netamount'] + $obj['d_netamount']), 4);
                            $obj['taxamount'] = round(($obj['taxamount'] + $obj['d_taxamount']), 4);
                            //$obj['grossamount'] = round($obj['grossamount'], 4);
                            $obj['grossamount'] = $obj['netamount'] + $obj['taxamount'];

                            $deemedflag = 'N';
                        }

                        $this->logger->write("Utilities : uploadimportedservice() : Calculating taxes. The deemedflag is " . $deemedflag, 'r');

                        $t_taxes[] = array(
                            'taxCategoryCode' => empty($obj['taxcategoryCode']) ? '' : $obj['taxcategoryCode'],
                            'taxCategory' => empty($obj['taxcategory']) ? '' : $obj['taxcategory'],
                            'netAmount' => empty($obj['netamount']) ? '0' : strval($obj['netamount']),
                            'taxRate' => strtoupper(trim($obj['taxcategory'])) == 'C: EXEMPT (-)' ? '-' : (empty($obj['taxrate']) ? '0' : number_format($obj['taxrate'], 4, '.', '')),
                            'taxAmount' => empty($obj['taxamount']) ? '0' : strval($obj['taxamount']),
                            'grossAmount' => empty($obj['grossamount']) ? '0' : strval($obj['grossamount']),
                            'exciseUnit' => empty($obj['exciseunit']) ? '' : $obj['exciseunit'],
                            'exciseCurrency' => empty($obj['excisecurrency']) ? '' : $obj['excisecurrency'],
                            'taxRateName' => empty($obj['taxratename']) ? '' : $obj['taxratename']
                        );

                        $netamount = $netamount + $obj['netamount'];
                        $taxamount = $taxamount + $obj['taxamount'];

                        $grossamount = $grossamount + $obj['grossamount'];
                        $itemcount = $itemcount + 1;
                    }
                }


            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploadimportedservice() : The operation to retrive the tax details was not successful. The error messages is " . $e->getMessage(), 'r');
            }

            $t_summary[] = array(
                'netamount' => strtoupper(trim($deemedflag)) == 'N' ? round($netamount, 4) : number_format((floor($netamount * 100) / 100), 4, '.', ''),
                'taxamount' => strtoupper(trim($deemedflag)) == 'N' ? round($taxamount, 4) : 0,
                'grossamount' => strtoupper(trim($deemedflag)) == 'N' ? round($grossamount, 4) : number_format((floor($netamount * 100) / 100), 4, '.', ''),
                'itemcount' => $itemcount
            );

            try {
                //$temp = $taxes;

                foreach ($payments as $obj) {
                    $t_payments[] = array(
                        'paymentMode' => empty($obj['paymentmode']) ? '' : $obj['paymentmode'],
                        'paymentAmount' => empty($obj['paymentamount']) ? '' : strval(round($obj['paymentamount'], 4)),
                        'orderNumber' => empty($obj['ordernumber']) ? '' : $obj['ordernumber']
                    );
                }
            } catch (Exception $e) {
                $this->logger->write("Utilities Controller : uploadimportedservice() : The operation to retrive the payment details was not successful. The error messages is " . $e->getMessage(), 'r');
            }


            //return json_encode(array('returnCode' => '999', 'returnMessage' => 'Unknown Error'));

            $invoice_u = array(
                'sellerDetails' => array(
                    'tin' => empty($org->tin) ? '' : $org->tin,
                    'ninBrn' => empty($org->ninbrn) ? '' : addslashes($org->ninbrn),
                    'legalName' => empty($org->legalname) ? '' : addslashes($org->legalname),
                    'businessName' => empty($org->businessname) ? '' : addslashes($org->businessname),
                    'address' => empty($org->address) ? '' : addslashes($org->address),
                    'mobilePhone' => empty($org->mobilephone) ? '' : $org->mobilephone,
                    'linePhone' => empty($org->linephone) ? '' : $org->linephone,
                    'emailAddress' => empty($org->emailaddress) ? '' : addslashes($org->emailaddress),
                    'placeOfBusiness' => empty($org->placeofbusiness) ? '' : addslashes($org->placeofbusiness),
                    'referenceNo' => empty($invoicedetails['erpinvoiceid']) ? '' : $invoicedetails['erpinvoiceid'],
                    'branchId' => empty($branchuraid) ? $devicedetails->branchId : $branchuraid,
                    'isCheckReferenceNo' => '0'
                ),
                'basicInformation' => array(
                    'invoiceNo' => empty($invoicedetails['erpinvoiceid']) ? '' : $invoicedetails['erpinvoiceid'],
                    'antifakeCode' => empty($invoicedetails['antifakecode']) ? '' : $invoicedetails['antifakecode'],
                    'deviceNo' => $invoicedetails['deviceno'],
                    'issuedDate' => date('Y-m-d H:i:s'),
                    'operator' => $invoicedetails['operator'],
                    'currency' => $invoicedetails['currency'],
                    'oriInvoiceId' => empty($invoicedetails['oriinvoiceid']) ? '' : $invoicedetails['oriinvoiceid'],
                    'invoiceType' => empty($invoicedetails['invoicetype']) ? '' : strval($invoicedetails['invoicetype']),
                    'invoiceKind' => empty($invoicedetails['invoicekind']) ? '' : strval($invoicedetails['invoicekind']),
                    'dataSource' => empty($invoicedetails['datasource']) ? '' : strval($invoicedetails['datasource']),
                    'invoiceIndustryCode' => empty($invoicedetails['invoiceindustrycode']) ? '' : strval($invoicedetails['invoiceindustrycode']),
                    'isBatch' => empty($invoicedetails['isbatch']) ? '' : $invoicedetails['isbatch']
                ),
                'buyerDetails' => array(
                    'buyerTin' => empty($org->tin) ? '' : $org->tin,
                    'buyerNinBrn' => empty($org->ninbrn) ? '' : addslashes($org->ninbrn),
                    'buyerPassportNum' => '',
                    'buyerLegalName' => empty($org->legalname) ? '' : addslashes($org->legalname),
                    'buyerBusinessName' => empty($org->businessname) ? '' : addslashes($org->businessname),
                    'buyerAddress' => empty($org->address) ? '' : addslashes($org->address),
                    'buyerEmail' => empty($org->emailaddress) ? '' : addslashes($org->emailaddress),
                    'buyerMobilePhone' => empty($org->mobilephone) ? '' : $org->mobilephone,
                    'buyerLinePhone' => empty($org->linephone) ? '' : $org->linephone,
                    'buyerPlaceOfBusi' => empty($org->placeofbusiness) ? '' : addslashes($org->placeofbusiness),
                    'buyerType' => strval($this->appsettings['B2BCODE']),
                    'buyerCitizenship' => '',
                    'buyerSector' => '',
                    'buyerReferenceNo' => ''
                ),
                'goodsDetails' => $t_goods,
                'taxDetails' => $t_taxes,
                'summary' => array(
                    'netAmount' => empty($t_summary[0]['netamount']) ? '0' : strval($t_summary[0]['netamount']),
                    'taxAmount' => empty($t_summary[0]['taxamount']) ? '0' : strval($t_summary[0]['taxamount']),
                    'grossAmount' => empty($t_summary[0]['grossamount']) ? '0' : strval($t_summary[0]['grossamount']),
                    'itemCount' => empty($t_summary[0]['itemcount']) ? '0' : strval($t_summary[0]['itemcount']),
                    'modeCode' => empty($invoicedetails['modecode']) ? '' : $invoicedetails['modecode'],
                    'remarks' => empty($invoicedetails['remarks']) ? '' : $invoicedetails['remarks'],
                    'qrCode' => ''
                ),
                'payWay' => $t_payments,
                'extend' => array(
                    /**
                     * Author: frncslubanga@gmail.com
                     * Date: 2023-05-16
                     * Description: Resolve EFRIS error code 1404 - reasonCode:Invalid field value!
                     */
                    'reason' => trim($invoicedetails['invoicetype']) == '5' ? 'Credit Memo' : '',
                    'reasonCode' => trim($invoicedetails['invoicetype']) == '5' ? '103' : ''
                ),
                'importServicesSeller' => array(
                    'importBusinessName' => empty($importedseller['businessname']) ? $importedseller['legalname'] : $importedseller['businessname'],
                    'importEmailAddress' => empty($importedseller['emailaddress']) ? '' : $importedseller['emailaddress'],
                    'importContactNumber' => empty($importedseller['mobilephone']) ? $importedseller['linephone'] : $importedseller['mobilephone'],
                    'importAddress' => empty($importedseller['address']) ? $importedseller['placeofbusiness'] : $importedseller['address'],
                    'importInvoiceDate' => date('Y-m-d'),
                    'importAttachmentName' => '',
                    'importAttachmentContent' => ''
                )
            );

            //print_r($invoice_u);

            $invoice_u = json_encode($invoice_u); //JSON-ifiy
            $invoice_u = base64_encode($invoice_u); //base64 encode
            $this->logger->write("Utilities : uploadimportedservice() : The encoded invoice is " . $invoice_u, 'r');

            $data = array(
                'data' => array(
                    'content' => $invoice_u,
                    'signature' => '',
                    'dataDescription' => array(
                        'codeType' => '0',
                        'encryptCode' => '2',
                        'zipCode' => '0'
                    )
                ),
                'globalInfo' => array(
                    'appId' => $tcsdetails->appid,
                    'version' => $tcsdetails->version,
                    'dataExchangeId' => $tcsdetails->dataexchangeid,
                    'interfaceCode' => $interfaceCode,
                    'requestCode' => $tcsdetails->requestcode,
                    'requestTime' => date('Y-m-d H:i:s'),
                    'responseCode' => $tcsdetails->resposecode,
                    'userName' => $tcsdetails->username,
                    'deviceMAC' => $devicedetails->devicemac,
                    'deviceNo' => $devicedetails->deviceno,
                    'tin' => $companydetails->tin,
                    'taxpayerID' => $companydetails->taxpayerid,
                    'longitude' => $companydetails->longitude,
                    'latitude' => $companydetails->latitude,
                    'extendField' => array(
                        'responseDateFormat' => $tcsdetails->responsedataformat,
                        'responseTimeFormat' => $tcsdetails->responsetimeformat,
                    )
                ),
                'returnStateInfo' => array(
                    'returnCode' => '',
                    'returnMessage' => '',
                )
            );

            $data = json_encode($data);

            $this->logger->write("Utilities : uploadimportedservice() : The request payload is: " . $data, 'r');

            $options = array(
                'method'  => 'POST',
                'content' => $data,
                'header' => $header
            );

            $response = $web->request($url, $options);
            $j_response = json_decode($response['body'], true);

            //var_dump($j_response);
            //$this->logger->write("Utilities : uploadimportedservice() : The response is: " . print_r($j_response), 'r');

            $returninfo = $j_response['returnStateInfo'];
            $content = $j_response['data']['content'];
            $this->logger->write("Utilities : uploadimportedservice() : The response content is: " . $content, 'r');

            /**
             * We need to find out if the content is zipped.
             */

            $dataDesc = $j_response['data']['dataDescription'];

            //var_dump($returninfo);
            //var_dump($content);

            if ($returninfo['returnCode'] == '00') {
                $this->logger->write("Utilities : uploadimportedservice() : The API call was successful. The return code is: " . $returninfo['returnCode'], 'r');
                if ($dataDesc['zipCode'] == '1') {
                    $this->logger->write("Utilities : uploadimportedservice() : The response is zipped", 'r');
                    $content = gzdecode(base64_decode($content));
                } else {
                    $this->logger->write("Utilities : uploadimportedservice() : The response is NOT zipped", 'r');
                    $content = base64_decode($content);
                }
            } else {
                $this->logger->write("Utilities : uploadimportedservice() : The API call was not successful. The return code is: " . $returninfo['returnCode'] . ' - ' . $returninfo['returnMessage'], 'r');
                $content = json_encode(array('returnCode' => $returninfo['returnCode'], 'returnMessage' => $returninfo['returnMessage']));
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->write("Utilities : uploadimportedservice() : Error " . $e->getMessage(), 'r');
            return $content;
        }
    }

}
