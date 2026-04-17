<?php
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\CreditMemo;
use QuickBooksOnline\API\Facades\RefundReceipt;
/**
 * @name CreditnoteController
 * @desc This file is part of the etaxware system. The is the Credit note controller class
 * @date 11-05-2020
 * @file CreditnoteController.php
 * @path ./app/controller/CreditnoteController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
Class CreditnoteController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules

    private function toFloat($value){
        $trimmed = trim((string)$value);
        if ($trimmed === '' || strtoupper($trimmed) === 'NULL') {
            return 0.0;
        }
        return (float)$trimmed;
    }

    private function deriveExciseFromRules($product, $qty, $unitPrice){
        $result = array(
            'exciseflag' => '2',
            'exciserate' => null,
            'exciserule' => null,
            'excisetax' => null,
            'pack' => null,
            'stick' => null,
            'exciseunit' => null,
            'excisecurrency' => null,
            'exciseratename' => null
        );

        if (!is_object($product) || trim((string)$product->hasexcisetax) !== '101') {
            return $result;
        }

        $exciseDutyCode = trim((string)$product->exciseDutyCode);
        if ($exciseDutyCode === '') {
            $exciseDutyCode = trim((string)$product->excisedutylist);
        }
        if ($exciseDutyCode === '') {
            return $result;
        }

        $result['exciseflag'] = '1';
        $result['pack'] = $this->toFloat($product->pack) == 0.0 ? null : $product->pack;
        $result['stick'] = $this->toFloat($product->stick) == 0.0 ? null : $product->stick;

        try {
            $dutyRows = $this->db->exec(
                'SELECT uraid FROM tblexcisedutylist WHERE TRIM(code) = ? LIMIT 1',
                array($exciseDutyCode)
            );
            if (empty($dutyRows)) {
                return $result;
            }

            $detailRows = $this->db->exec(
                'SELECT rate, type, unit, currency FROM tblexcisedutydetailslist WHERE exciseDutyId = ? AND disabled = 0',
                array(trim((string)$dutyRows[0]['uraid']))
            );
            if (empty($detailRows)) {
                return $result;
            }

            $qtyValue = $this->toFloat($qty);
            $unitValue = $this->toFloat($unitPrice);
            $pieceUnitPrice = $this->toFloat($product->pieceunitprice);
            $pieceScaledValue = $this->toFloat($product->piecescaledvalue);

            $bestTax = -1.0;
            $bestRule = null;

            foreach ($detailRows as $row) {
                $typeCode = trim((string)$row['type']);
                $rateRaw = trim((string)$row['rate']);
                $rateValue = $this->toFloat($rateRaw);
                $ruleTax = 0.0;
                $ruleId = 3;
                $ruleRateValue = $rateRaw;
                $ruleRateName = $rateRaw;

                if ($typeCode === '101') {
                    $ruleTax = $unitValue * ($rateValue / 100) * $qtyValue;
                    $ruleId = 1;
                    $ruleRateValue = ($rateValue / 100);
                    $ruleRateName = $rateRaw . '%';
                } elseif ($typeCode === '102') {
                    $ruleTax = $qtyValue * $pieceUnitPrice * $pieceScaledValue;
                    $ruleId = 2;
                }

                if ($ruleTax >= $bestTax) {
                    $bestTax = $ruleTax;
                    $bestRule = array(
                        'exciserate' => $ruleRateValue,
                        'exciserule' => $ruleId,
                        'excisetax' => $ruleTax,
                        'exciseunit' => is_numeric(trim((string)$row['unit'])) ? (int)trim((string)$row['unit']) : null,
                        'excisecurrency' => trim((string)$row['currency']) === '' ? null : trim((string)$row['currency']),
                        'exciseratename' => $ruleRateName
                    );
                }
            }

            if ($bestRule !== null) {
                $result['exciserate'] = $bestRule['exciserate'];
                $result['exciserule'] = $bestRule['exciserule'];
                $result['excisetax'] = number_format($bestRule['excisetax'], 8, '.', '');
                $result['exciseunit'] = $bestRule['exciseunit'];
                $result['excisecurrency'] = $bestRule['excisecurrency'];
                $result['exciseratename'] = $bestRule['exciseratename'];
            }
        } catch (Exception $e) {
            $this->logger->write('Creditnote Controller : deriveExciseFromRules() : Failed. Error=' . $e->getMessage(), 'r');
        }

        return $result;
    }

    private function resolveLineWeightFromPost($postKey, $fallbackWeight){
        $postedWeight = trim((string)$this->f3->get($postKey));
        if ($postedWeight !== '' && strtoupper($postedWeight) !== 'NULL' && is_numeric($postedWeight)) {
            return $this->toFloat($postedWeight);
        }
        return $this->toFloat($fallbackWeight);
    }

    private function syncCreditnoteTotalWeight($creditnoteId, $goodDetailGroupId){
        $creditnoteId = (int)$creditnoteId;
        $goodDetailGroupId = (int)$goodDetailGroupId;
        if ($creditnoteId <= 0 || $goodDetailGroupId <= 0) {
            return;
        }

        try {
            $rows = $this->db->exec(
                'SELECT IFNULL(SUM(IFNULL(totalWeight, 0)), 0) AS totalWeight FROM tblgooddetails WHERE groupid = ?',
                array($goodDetailGroupId)
            );
            $totalWeight = 0;
            if (!empty($rows) && isset($rows[0]['totalWeight'])) {
                $totalWeight = round($this->toFloat($rows[0]['totalWeight']), 4);
            }

            $this->db->exec(
                'UPDATE tblcreditnotes SET totalWeight = ?, modifieddt = NOW(), modifiedby = ? WHERE id = ?',
                array($totalWeight, $this->f3->get('SESSION.id'), $creditnoteId)
            );
        } catch (Exception $e) {
            $this->logger->write('Creditnote Controller : syncCreditnoteTotalWeight() : Failed. Error=' . $e->getMessage(), 'r');
        }
    }

    private function ensureBuyerFromCustomer($customerId){
        $customerId = (int)$customerId;
        if ($customerId <= 0) {
            return null;
        }

        $customer = new customers($this->db);
        $customer->getByID($customerId);
        if ($customer->dry()) {
            return null;
        }

        try {
            $tin = trim((string)$customer->tin);
            $legalName = trim((string)$customer->legalname);

            if ($tin !== '') {
                $rows = $this->db->exec(
                    'SELECT id FROM tblbuyers WHERE TRIM(tin) = ? ORDER BY id DESC LIMIT 1',
                    array($tin)
                );
                if (!empty($rows)) {
                    return (int)$rows[0]['id'];
                }
            }

            if ($legalName !== '') {
                $rows = $this->db->exec(
                    'SELECT id FROM tblbuyers WHERE TRIM(legalname) = ? ORDER BY id DESC LIMIT 1',
                    array($legalName)
                );
                if (!empty($rows)) {
                    return (int)$rows[0]['id'];
                }
            }

            // Legacy invoices can point to tblcustomers only; create a buyer clone so credit note flows can proceed.
            $buyerType = trim((string)$customer->type) === '' ? '1' : trim((string)$customer->type);
            $this->db->exec(
                'INSERT INTO tblbuyers
                    (erpbuyerid, erpbuyercode, tin, ninbrn, PassportNum, legalname, businessname, address, mobilephone,
                     linephone, emailaddress, placeofbusiness, type, citizineship, sector, datasource, disabled,
                     inserteddt, insertedby, modifieddt, modifiedby, deliveryTermsCode, nonResidentFlag)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), ?, NOW(), ?, ?, ?)',
                array(
                    $customer->erpcustomerid,
                    $customer->erpcustomercode,
                    $customer->tin,
                    $customer->ninbrn,
                    $customer->PassportNum,
                    $customer->legalname,
                    $customer->businessname,
                    $customer->address,
                    $customer->mobilephone,
                    $customer->linephone,
                    $customer->emailaddress,
                    $customer->placeofbusiness,
                    $buyerType,
                    $customer->citizineship,
                    $customer->sector,
                    trim((string)$customer->datasource) === '' ? 'MW' : $customer->datasource,
                    $this->f3->get('SESSION.id'),
                    $this->f3->get('SESSION.id'),
                    $customer->deliveryTermsCode,
                    (trim((string)$customer->nonResidentFlag) === '' ? 0 : (int)$customer->nonResidentFlag)
                )
            );

            $inserted = $this->db->exec('SELECT LAST_INSERT_ID() AS id');
            if (!empty($inserted) && !empty($inserted[0]['id'])) {
                return (int)$inserted[0]['id'];
            }
        } catch (Exception $e) {
            $this->logger->write('Creditnote Controller : ensureBuyerFromCustomer() : Failed. Error=' . $e->getMessage(), 'r');
        }

        return null;
    }

    private function resolveBuyerIdFromOriginalInvoice($invoice, $existingBuyerId = null){
        if (!is_object($invoice)) {
            return null;
        }

        $invoiceBuyerId = trim((string)$invoice->buyerid);
        if ($invoiceBuyerId !== '' && $invoiceBuyerId !== '0') {
            $buyer = new buyers($this->db);
            $buyer->getByID((int)$invoiceBuyerId);
            if (!$buyer->dry()) {
                return (int)$buyer->id;
            }

            $mappedBuyerId = $this->ensureBuyerFromCustomer((int)$invoiceBuyerId);
            if ($mappedBuyerId !== null) {
                return $mappedBuyerId;
            }
        }

        // Some legacy ERP invoice rows keep customer codes on invoice ids instead of buyerid.
        $erpCandidate = trim((string)$invoice->erpinvoiceid);
        if ($erpCandidate === '') {
            $erpCandidate = trim((string)$invoice->erpinvoiceno);
        }

        if ($erpCandidate !== '') {
            $erpCandidateTrimmed = trim((string)$erpCandidate);
            $erpCandidateUpper = strtoupper($erpCandidateTrimmed);
            $erpCandidateNoPrefix = preg_replace('/^[A-Za-z]+/', '', $erpCandidateTrimmed);

            $buyerRows = $this->db->exec(
                'SELECT id FROM tblbuyers
                 WHERE TRIM(erpbuyercode) = ? OR TRIM(erpbuyercode) = ? OR TRIM(erpbuyercode) = ? OR CAST(erpbuyerid AS CHAR) = ?
                 ORDER BY id DESC LIMIT 1',
                array($erpCandidateTrimmed, $erpCandidateUpper, $erpCandidateNoPrefix, $erpCandidateNoPrefix)
            );
            if (!empty($buyerRows)) {
                return (int)$buyerRows[0]['id'];
            }

            $customerRows = $this->db->exec(
                'SELECT id FROM tblcustomers
                 WHERE TRIM(erpcustomerid) = ? OR TRIM(erpcustomerid) = ? OR TRIM(erpcustomerid) = ?
                    OR TRIM(erpcustomercode) = ? OR TRIM(erpcustomercode) = ? OR TRIM(erpcustomercode) = ?
                 ORDER BY id DESC LIMIT 1',
                array($erpCandidateTrimmed, $erpCandidateUpper, $erpCandidateNoPrefix, $erpCandidateTrimmed, $erpCandidateUpper, $erpCandidateNoPrefix)
            );

            if (!empty($customerRows)) {
                $mappedBuyerId = $this->ensureBuyerFromCustomer((int)$customerRows[0]['id']);
                if ($mappedBuyerId !== null) {
                    return $mappedBuyerId;
                }
            }
        }

        // Never clear an already selected buyer when source invoice has no buyer mapping.
        $existingBuyerId = trim((string)$existingBuyerId);
        if ($existingBuyerId !== '' && $existingBuyerId !== '0') {
            return (int)$existingBuyerId;
        }

        return null;
    }
    
    /**
     *	@name index
     *  @desc Loads the index page
     *	@return NULL
     *	@param NULL
     **/
    function index(){
        $operation = NULL; //tblevents
        $permission = 'VIEWCREDITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Creditnote Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $invoicekind = new invoicekinds($this->db);
            $invoicekinds = $invoicekind->all();
            $this->f3->set('invoicekinds', $invoicekinds);
            
            $erpdoctype = new erpdoctypes($this->db);
            $erpdoctypes = $erpdoctype->getByCat($this->appsettings['CREDITNOTEERPDOCCAT']);
            $this->f3->set('erpdoctypes', $erpdoctypes);
            
            $this->f3->set('pagetitle','Credit Notes');
            $this->f3->set('pagecontent','Creditnote.htm');
            $this->f3->set('pagescripts','CreditnoteFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Creditnote Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    /**
     *	@name view
     *  @desc view Creditnote
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = '', $tab = '', $tabpane = '') {
        $operation = NULL; //tblevents
        $permission = 'VIEWCREDITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotificationsss
        
        $this->logger->write("Creditnote Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Creditnote Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Creditnote Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $cdnoteapplycategorycode = new cdnoteapplycategorycodes($this->db);
            $cdnoteapplycategorycodes = $cdnoteapplycategorycode->all();
            $this->f3->set('cdnoteapplycategorycodes', $cdnoteapplycategorycodes);
            
            $cdnoteapprovestatus = new cdnoteapprovestatuses($this->db);
            $cdnoteapprovestatuses = $cdnoteapprovestatus->all();
            $this->f3->set('cdnoteapprovestatuses', $cdnoteapprovestatuses);
            
            $cdnotereasoncode = new cdnotereasoncodes($this->db);
            $cdnotereasoncodes = $cdnotereasoncode->all();
            $this->f3->set('cdnotereasoncodes', $cdnotereasoncodes);
            
            $paymentmode = new paymentmodes($this->db);
            $paymentmodes = $paymentmode->all();
            $this->f3->set('paymentmodes', $paymentmodes);
            
            $datasource = new datasources($this->db);
            $datasources = $datasource->all();
            $this->f3->set('datasources', $datasources);
            
            $industry = new industries($this->db);
            $industries = $industry->all();
            $this->f3->set('industries', $industries);
            
            $currency = new currencies($this->db);
            $currencies = $currency->all();
            $this->f3->set('currencies', $currencies);
            
            $mode = new modes($this->db);
            $modes = $mode->all();
            $this->f3->set('modes', $modes);
            
            $buyertype = new buyertypes($this->db);
            $buyertypes = $buyertype->all();
            $this->f3->set('buyertypes', $buyertypes);
            
            $product = new products($this->db);
            $products = $product->all();
            $this->f3->set('products', $products);
            
            $flag = new flags($this->db);
            $flags = $flag->all();
            $this->f3->set('flags', $flags);
            
            $taxrate = new taxrates($this->db);
            $taxrates = $taxrate->all();
            $this->f3->set('taxrates', $taxrates);
            
            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
            $this->f3->set('seller', $org);
            //$this->logger->write($this->db->log(TRUE), 'r');
            
            $deliveryterm = new deliveryterms($this->db);
            $deliveryterms = $deliveryterm->all();
            $this->f3->set('deliveryterms', $deliveryterms);
            
            if (is_string($tab) && is_string($tabpane)){
                $this->logger->write("Creditnote Controller : view() : The value of v_id is " . $v_id, 'r');
                $this->logger->write("Creditnote Controller : view() : The value of tab is " . $tab, 'r');
                $this->logger->write("Creditnote Controller : view() : The value of tabpane " . $tabpane, 'r');
            } 
            
            if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
                $id = trim($this->f3->get('PARAMS[id]'));
                $this->logger->write("Creditnote Controller : view() : The is a GET call & id to view is " . $id, 'r');
                
                $creditnote = new creditnotes($this->db);
                $creditnote->getByID($id);
                $this->f3->set('creditnote', $creditnote);
                
                $buyer = new buyers($this->db);
                $buyer->getByID($creditnote->buyerid);
                $this->f3->set('buyer', $buyer);
                
                if (is_string($tab) && is_string($tabpane)){//this check is necessary for cases where the GET request is system initiated. The params sent to the view functions are non-string.
                    $this->f3->set('currenttab', $tab);
                    $this->f3->set('currenttabpane', $tabpane);
                } else {
                    $this->f3->set('currenttab', 'tab_general');
                    $this->f3->set('currenttabpane', 'tab_1');
                    $this->f3->set('path', '../' . $this->path);
                }
                
                $this->f3->set('pagetitle','Edit Credit Note | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path); //overide the main solution path
            } elseif (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id')))) {//Open EDIT mode
                $id = trim($this->f3->get('POST.id'));
                $this->logger->write("Creditnote Controller : view() : This is a POST call & the id to view is " . $id, 'r');
                
                $creditnote = new creditnotes($this->db);
                $creditnote->getByID($id);
                $this->f3->set('creditnote', $creditnote);
                
                $buyer = new buyers($this->db);
                $buyer->getByID($creditnote->buyerid);
                $this->f3->set('buyer', $buyer);
                
                if (trim($this->f3->get('POST.currenttab')) !== '' || !empty(trim($this->f3->get('POST.currenttab')))) {
                    $this->f3->set('currenttab', trim($this->f3->get('POST.currenttab')));
                } else {
                    $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
                }
                
                if (trim($this->f3->get('POST.currenttabpane')) !== '' || !empty(trim($this->f3->get('POST.currenttabpane')))) {
                    $this->f3->set('currenttabpane', trim($this->f3->get('POST.currenttabpane')));
                } else {
                    $this->f3->set('currenttabpane', 'tab_1');
                }
                
                $this->f3->set('pagetitle','Edit Credit Note | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
            } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
                $id = trim($v_id);
                $this->logger->write("Creditnote Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
                
                $creditnote = new creditnotes($this->db);
                $creditnote->getByID($id);
                $this->f3->set('creditnote', $creditnote);
                
                $buyer = new buyers($this->db);
                $buyer->getByID($creditnote->buyerid);
                $this->f3->set('buyer', $buyer);
                
                if (trim($tab) !== '' || !empty(trim($tab))) {
                    $this->f3->set('currenttab', $tab);
                } else {
                    $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
                }
                
                if (trim($tabpane) !== '' || !empty(trim($tabpane))) {
                    $this->f3->set('currenttabpane', $tabpane);
                } else {
                    $this->f3->set('currenttabpane', 'tab_1');
                }
                
                $this->f3->set('pagetitle','Edit Credit Note | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
                
                $this->f3->set('pagecontent','EditCreditnote.htm');
                $this->f3->set('pagescripts','EditCreditnoteFooter.htm');
                echo \Template::instance()->render('Layout.htm');
                exit(); //exit the function so no extra code executes
            } else {
                $this->logger->write("Creditnote Controller : view() : No id was selected", 'r');
                $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page
                exit();
            }
            
            $this->logger->write("Creditnote Controller : view() : The currenttab has been set to " . $this->f3->get('currenttab'), 'r');
            $this->logger->write("Creditnote Controller : view() : The currenttabpane has been set to " . $this->f3->get('currenttabpane'), 'r');
            
            $this->f3->set('pagecontent','EditCreditnote.htm');
            $this->f3->set('pagescripts','EditCreditnoteFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Creditnote Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name add
     *  @desc add Creditnote
     *	@return NULL
     *	@param NULL
     **/
    function add() {
        $operation = NULL; //tblevents
        $permission = 'CREATECREDITNOTE'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Creditnote Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {           
            //@TODO Display a new form
            
            $cdnoteapplycategorycode = new cdnoteapplycategorycodes($this->db);
            $cdnoteapplycategorycodes = $cdnoteapplycategorycode->all();
            $this->f3->set('cdnoteapplycategorycodes', $cdnoteapplycategorycodes);
            
            $cdnoteapprovestatus = new cdnoteapprovestatuses($this->db);
            $cdnoteapprovestatuses = $cdnoteapprovestatus->all();
            $this->f3->set('cdnoteapprovestatuses', $cdnoteapprovestatuses);
            
            $cdnotereasoncode = new cdnotereasoncodes($this->db);
            $cdnotereasoncodes = $cdnotereasoncode->all();
            $this->f3->set('cdnotereasoncodes', $cdnotereasoncodes);
            
            $paymentmode = new paymentmodes($this->db);
            $paymentmodes = $paymentmode->all();
            $this->f3->set('paymentmodes', $paymentmodes);           
            
            $datasource = new datasources($this->db);
            $datasources = $datasource->all();
            $this->f3->set('datasources', $datasources);
            
            $industry = new industries($this->db);
            $industries = $industry->all();
            $this->f3->set('industries', $industries);
            
            $currency = new currencies($this->db);
            $currencies = $currency->all();
            $this->f3->set('currencies', $currencies);
            
            $mode = new modes($this->db);
            $modes = $mode->all();
            $this->f3->set('modes', $modes);
            
            $buyertype = new buyertypes($this->db);
            $buyertypes = $buyertype->all();
            $this->f3->set('buyertypes', $buyertypes);
            
            $product = new products($this->db);
            $products = $product->all();
            $this->f3->set('products', $products);
            
            $flag = new flags($this->db);
            $flags = $flag->all();
            $this->f3->set('flags', $flags);
            
            $taxrate = new taxrates($this->db);
            $taxrates = $taxrate->all();
            $this->f3->set('taxrates', $taxrates);
            
            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
            $this->f3->set('seller', $org);
            
            $deliveryterm = new deliveryterms($this->db);
            $deliveryterms = $deliveryterm->all();
            $this->f3->set('deliveryterms', $deliveryterms);
            
            $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
            $this->f3->set('currenttabpane', 'tab_1');

            // Surface one-time warning messages after redirects in create flow.
            $flashAlert = trim((string)$this->f3->get('SESSION.systemalert'));
            if ($flashAlert !== '') {
                self::$systemalert = $flashAlert;
                $this->f3->clear('SESSION.systemalert');
            }
            $this->f3->set('systemalert', self::$systemalert);
            
            
            $creditnote = array(
                "id" => NULL,
                "name" => '',
                "code" => '',
                "description" => ''
            );
            $this->f3->set('creditnote', $creditnote);
            
            $this->f3->set('pagetitle','Create Credit Note');
            
            $this->f3->set('pagecontent','EditCreditnote.htm');
            $this->f3->set('pagescripts','EditCreditnoteFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Creditnote Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    /**
     * edit creditnote
     *
     * @name edit
     * @return NULL
     * @param NULL
     */
    function edit(){
        $creditnote = new creditnotes($this->db);
        $currenttab = trim($this->f3->get('POST.currenttab'));
        $currenttabpane = trim($this->f3->get('POST.currenttabpane'));
        $id = 0;
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        if (trim($this->f3->get('POST.creditnoteid')) !== '' || !empty(trim($this->f3->get('POST.creditnoteid'))) || trim($this->f3->get('POST.sellercreditnoteid')) !== '' || !empty(trim($this->f3->get('POST.sellercreditnoteid'))) || trim($this->f3->get('POST.buyercreditnoteid')) !== '' || !empty(trim($this->f3->get('POST.buyercreditnoteid'))) || trim($this->f3->get('POST.goodcreditnoteid')) !== '' || !empty(trim($this->f3->get('POST.goodcreditnoteid'))) || trim($this->f3->get('POST.paymentcreditnoteid')) !== '' || !empty(trim($this->f3->get('POST.paymentcreditnoteid'))) || trim($this->f3->get('POST.deletegoodcreditnoteid')) !== '' || !empty(trim($this->f3->get('POST.deletegoodcreditnoteid'))) || trim($this->f3->get('POST.addpaymentcreditnoteid')) !== '' || !empty(trim($this->f3->get('POST.addpaymentcreditnoteid'))) || trim($this->f3->get('POST.deletepaymentcreditnoteid')) !== '' || !empty(trim($this->f3->get('POST.deletepaymentcreditnoteid'))) || trim($this->f3->get('POST.editgoodcreditnoteid')) !== '' || !empty(trim($this->f3->get('POST.editgoodcreditnoteid')))){
            $operation = NULL; // tblevents
            $permission = 'EDITCREDITNOTE'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications
            
            $this->logger->write("Creditnote Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                
                $currenttab = !empty(trim($this->f3->get('POST.currenttab')))? trim($this->f3->get('POST.currenttab')) : (!empty(trim($this->f3->get('POST.sellercurrenttab')))? trim($this->f3->get('POST.sellercurrenttab')) : (!empty(trim($this->f3->get('POST.buyercurrenttab')))? trim($this->f3->get('POST.buyercurrenttab')) : (!empty(trim($this->f3->get('POST.goodcurrenttab')))? trim($this->f3->get('POST.goodcurrenttab')) : (!empty(trim($this->f3->get('POST.paymentcurrenttab')))? trim($this->f3->get('POST.paymentcurrenttab')) : (!empty(trim($this->f3->get('POST.deletegoodcurrenttab')))? trim($this->f3->get('POST.deletegoodcurrenttab')) : (!empty(trim($this->f3->get('POST.deletepaymentcurrenttab')))? trim($this->f3->get('POST.deletepaymentcurrenttab')) : (!empty(trim($this->f3->get('POST.addpaymentcurrenttab')))? trim($this->f3->get('POST.addpaymentcurrenttab')) : trim($this->f3->get('POST.editgoodcurrenttab')))))))));
                $currenttabpane = !empty(trim($this->f3->get('POST.currenttabpane')))? trim($this->f3->get('POST.currenttabpane')) : (!empty(trim($this->f3->get('POST.sellercurrenttabpane')))? trim($this->f3->get('POST.sellercurrenttabpane')) : (!empty(trim($this->f3->get('POST.buyercurrenttabpane')))? trim($this->f3->get('POST.buyercurrenttabpane')) : (!empty(trim($this->f3->get('POST.goodcurrenttabpane')))? trim($this->f3->get('POST.goodcurrenttabpane')) : (!empty(trim($this->f3->get('POST.paymentcurrenttabpane')))? trim($this->f3->get('POST.paymentcurrenttabpane')) : (!empty(trim($this->f3->get('POST.deletegoodcurrenttabpane')))? trim($this->f3->get('POST.deletegoodcurrenttabpane')) : (!empty(trim($this->f3->get('POST.addpaymentcurrenttabpane')))? trim($this->f3->get('POST.addpaymentcurrenttabpane')) : (!empty(trim($this->f3->get('POST.deletepaymentcurrenttabpane')))? trim($this->f3->get('POST.deletepaymentcurrenttabpane')) : trim($this->f3->get('POST.editgoodcurrenttabpane')))))))));
                                
                if ($currenttab == 'tab_general') {
                    $id = trim($this->f3->get('POST.creditnoteid'));
                    $this->logger->write("Creditnote Controller : edit() : tab_general :  The id to be edited is " . $id, 'r');
                    $creditnote->getByID($id);
                    
                    $this->f3->set('POST.erpcreditnoteid', $this->f3->get('POST.erpcreditnoteid'));
                    
                    $this->f3->set('POST.erprefundinvoiceno', $this->f3->get('POST.erprefundinvoiceno'));
                    
                    $this->f3->set('POST.invoiceapplycategorycode', trim($this->appsettings['CREDITDEFAULTAPPCATEGORY']));
                    
                    if(trim($this->f3->get('POST.reasoncode')) !== '' || ! empty(trim($this->f3->get('POST.reasoncode')))) {
                        $this->f3->set('POST.reasoncode', $this->f3->get('POST.reasoncode'));
                    } else {
                        $this->f3->set('POST.reasoncode', $creditnote->reasoncode);
                    }
                    
                    if(trim($this->f3->get('POST.reason')) !== '' || ! empty(trim($this->f3->get('POST.reason')))) {
                        $this->f3->set('POST.reason', $this->f3->get('POST.reason'));
                    } else {
                        $this->f3->set('POST.reason', $creditnote->reason);
                    }
                    
                    if(trim($this->f3->get('POST.datasource')) !== '' || ! empty(trim($this->f3->get('POST.datasource')))) {
                        $this->f3->set('POST.datasource', $this->f3->get('POST.datasource'));
                    } else {
                        $this->f3->set('POST.datasource', $creditnote->datasource);
                    }
                                       
                    $this->f3->set('POST.remarks', $this->f3->get('POST.remarks'));
                    
                    $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                    $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                    
                    try {
                        /**
                         * Logic for editing the original invoice of a credit note
                         * 1. Check if the user wishes to change the original invoice
                         * 2. Retrieve sidea & sideb datasource details from tbldatasourcegroupdetails
                         * 3. Update/insert sidea & sideb datasource details into tblmatchsetdatasources. Enable all available datasources by default
                         * 4. Retrieve rule details from table tblruledetails
                         * 5. Update/insert rule details into tblmatchsetrules. Enable all availabe rules by default
                         * */
                        $editoriinvoiceidflag = 'N';
                        
                        if (trim($this->f3->get('POST.searchinvoice')) !== '' || !empty(trim($this->f3->get('POST.searchinvoice')))) {
                            
                            $this->logger->write("Creditnote Controller : edit() : The new oriinvoiceid is " . trim($this->f3->get('POST.searchinvoice')), 'r');
                            $this->logger->write("Creditnote Controller : edit() : The existing oriinvoiceid is " . $creditnote->oriinvoiceid, 'r');
                            
                            if (trim($this->f3->get('POST.searchinvoice')) !== trim($creditnote->oriinvoiceid)) {
                                $this->logger->write("Creditnote Controller : edit() : The user wishes to change the oriinvoiceid.", 'r');
                                $editoriinvoiceidflag = 'Y';
                            } else {
                                $this->logger->write("Creditnote Controller : edit() : The user does not wish to change the oriinvoiceid.", 'r');
                            }
                        }
                        
                        $oriinvoiceid = $this->f3->get('POST.searchinvoice');
                        $invoice = new invoices($this->db);
                        $invoice->getByInvoiceID($oriinvoiceid);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        $this->logger->write("Creditnote Controller : edit() : invoice = " . $oriinvoiceid, 'r');
                        
                        $this->f3->set('POST.operator', $this->f3->get('SESSION.username'));
                        
                        $this->f3->set('POST.deviceno', $invoice->deviceno);
                        $this->f3->set('POST.oriinvoiceid', $invoice->einvoiceid);
                        $this->f3->set('POST.oriinvoiceno', $invoice->einvoicenumber);
                        $this->f3->set('POST.currency', $invoice->currency);
                        $this->f3->set('POST.origrossamount', $invoice->grossamount);
                        // Buyer must be inherited from the original invoice (with legacy customer fallback) so Goods tab stays accessible.
                        $resolvedBuyerId = $this->resolveBuyerIdFromOriginalInvoice($invoice, $creditnote->buyerid);
                        $this->f3->set('POST.buyerid', $resolvedBuyerId);
                        if ($resolvedBuyerId === null) {
                            $this->logger->write('Creditnote Controller : edit() : Unable to resolve buyer from original invoice id ' . $invoice->einvoiceid . ' (invoice row id ' . $invoice->id . ')', 'r');
                        }
                        $this->f3->set('POST.invoiceindustrycode', $invoice->invoiceindustrycode);
                        $this->f3->set('POST.deliveryTermsCode', $invoice->deliveryTermsCode);
                        
                        $creditnote->edit($id);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        
                        //Proceed and edit oriinvoiceid details
                        if ($editoriinvoiceidflag == 'Y') {
                            /**
                             * 1. Delete all GROUPIDs
                             * 2. Add a GROUPID for goods and store it in a field called gooddetailgroupid
                             * 3. Add a GROUPID for payments and store it in a field called paymentdetailgroupid
                             * 4. Add a GROUPID for tax details and store it in a field called taxdetailgroupid
                             */
                            
                            $gooddetailgroup = $creditnote->gooddetailgroupid;
                            $taxdetailgroup = $creditnote->taxdetailgroupid;
                            $paymentdetailgroup = $creditnote->paymentdetailgroupid;
                            
                            if ($gooddetailgroup) {
                                try {
                                    $this->db->exec(array('DELETE FROM tblgooddetailgroups WHERE id = COALESCE(' . $gooddetailgroup . ', NULL)'));
                                    $this->db->exec(array('DELETE FROM tblgooddetails WHERE groupid = COALESCE(' . $gooddetailgroup . ', NULL)'));
                                } catch (Exception $e) {
                                    $this->logger->write("Creditnote Controller : edit() : Failed to delete from tabled tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            }
                            
                            if ($taxdetailgroup) {
                                try {
                                    $this->db->exec(array('DELETE FROM tbltaxdetailgroups WHERE id = COALESCE(' . $taxdetailgroup . ', NULL)'));
                                    $this->db->exec(array('DELETE FROM tbltaxdetails WHERE groupid = COALESCE(' . $taxdetailgroup . ', NULL)'));
                                } catch (Exception $e) {
                                    $this->logger->write("Creditnote Controller : edit() : Failed to delete from tabled tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            }
                            
                            if ($paymentdetailgroup) {
                                try {
                                    $this->db->exec(array('DELETE FROM tblpaymentdetailgroups WHERE id = COALESCE(' . $paymentdetailgroup . ', NULL)'));
                                    $this->db->exec(array('DELETE FROM tblpaymentdetails WHERE groupid = COALESCE(' . $paymentdetailgroup . ', NULL)'));
                                } catch (Exception $e) {
                                    $this->logger->write("Creditnote Controller : edit() : Failed to delete from tabled tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            }
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                            VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $taxdetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tblcreditnotes SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Creditnote Controller : edit() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $gooddetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tblcreditnotes SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                    /**
                                     * 1. Retrieve good details from the parent invoice
                                     * 2(a). Copy good details to the debit note
                                     * 2(b). Retrieve the new goodid
                                     * 2(c). Copy related tax to the debit note
                                     *
                                     * */
                                    try{
                                        
                                        $temp = $this->db->exec(array('SELECT id, groupid, item, itemcode, qty, unitofmeasure, unitprice, total, taxrate, tax, ifnull(discounttotal, NULL) discounttotal, ifnull(discounttaxrate, NULL) discounttaxrate, ifnull(ordernumber, NULL) ordernumber, discountflag, deemedflag, exciseflag, ifnull(categoryid, NULL) categoryid, categoryname, goodscategoryid, goodscategoryname
                                                                        , taxid, discountpercentage, exciserate, ifnull(exciserule, NULL) exciserule, ifnull(excisetax, NULL) excisetax, ifnull(pack, NULL) pack, ifnull(stick, NULL) stick, ifnull(exciseunit, NULL) exciseunit, excisecurrency, exciseratename, taxcategory, displayCategoryCode, unitofmeasurename, ifnull(totalWeight, NULL) totalWeight, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tblgooddetails WHERE groupid = ' . $invoice->gooddetailgroupid));
                                        
                                        $this->logger->write($this->db->log(TRUE), 'r');
                                        
                                        $k = 0;
                                        foreach ($temp as $obj) {
                                            $o_goodid = $obj['id'];//original good id
                                            $this->logger->write("Creditnote Controller : edit() : The original good id is " . $o_goodid, 'r');
                                            
                                            try {
                                                $this->db->exec(array('INSERT INTO tblgooddetails (groupid, item, itemcode, qty, unitofmeasure, unitprice, total, taxrate, tax, discounttotal, discounttaxrate, ordernumber, discountflag, deemedflag, exciseflag, categoryid, categoryname, goodscategoryid, goodscategoryname
                                                                        , exciserate, taxid, discountpercentage, exciserule, excisetax, pack, stick, exciseunit, excisecurrency, exciseratename, taxcategory, displayCategoryCode, unitofmeasurename, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                        VALUES( '. $gooddetailgroupid . ', "' . $obj['item'] . '", "' . $obj['itemcode'] . '", ' . $obj['qty'] . ', "' . $obj['unitofmeasure'] . '", ' . $obj['unitprice'] . ', ' . $obj['total'] . ', ' . $obj['taxrate'] . ', ' . $obj['tax'] . ', ' . $obj['discounttotal'] . ', ' . $obj['discounttaxrate'] . ', ' . (empty($obj['ordernumber'])? strval($k) : $obj['ordernumber']) . ', ' . $obj['discountflag'] . ', ' . $obj['deemedflag'] . ', ' . $obj['exciseflag'] . ', ' . (empty($obj['categoryid'])? 'NULL' : $obj['categoryid']) . ', "' . $obj['categoryname'] . '", ' . $obj['goodscategoryid'] . ', "' . $obj['goodscategoryname'] . '", "' .
                                                                            $obj['exciserate'] . '", ' . (empty($obj['taxid'])? 'NULL' : $obj['taxid']) . ', ' . (empty($obj['discountpercentage'])? 'NULL' : $obj['discountpercentage']) . ', ' . (empty($obj['exciserule'])? 'NULL' : $obj['exciserule']) . ', ' . (empty($obj['excisetax'])? 'NULL' : $obj['excisetax']) . ', ' . (empty($obj['pack'])? 'NULL' : $obj['pack']) . ', ' . (empty($obj['stick'])? 'NULL' : $obj['stick']) . ', ' . (empty($obj['exciseunit'])? 'NULL' : $obj['exciseunit']) . ', "' . $obj['excisecurrency'] . '", "' . $obj['exciseratename'] . '", "' . $obj['taxcategory'] . '", "' . $obj['displayCategoryCode'] . '", "' . $obj['unitofmeasurename'] . '", ' . $obj['disabled'] . ', NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            
                                            $g = array ();
                                            $gr = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetails WHERE groupid = ' . $gooddetailgroupid . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                            
                                            foreach ( $gr as $obj ) {
                                                $g[] = $obj;
                                            }
                                            
                                            $goodid = $g[0]['id'];
                                            
                                            try {
                                                if ($this->vatRegistered == 'Y') {
                                                    /*Insert tax details from the parent invoice*/
                                                    $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                            SELECT '. $taxdetailgroupid . ', ' . $goodid . ', taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tbltaxdetails WHERE groupid = ' . $invoice->taxdetailgroupid . ' AND goodid = ' . $o_goodid));
                                                    
                                                    $this->logger->write($this->db->log(TRUE), 'r');
                                                }
                                                
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            
                                            $k = $k + 1;
                                        }
                                        
                                    } catch (Exception $e) {
                                        $this->logger->write("Creditnote Controller : edit() : Failed to insert into table tblgooddetails & tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                                    }
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Creditnote Controller : edit() : Failed to select from table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $paymentdetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tblcreditnotes SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                    /*Insert payments details from the parent invoice*/
                                    try{
                                        $this->db->exec(array('INSERT INTO tblpaymentdetails (groupid, paymentmode, paymentmodename, paymentamount, ordernumber, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    SELECT '. $paymentdetailgroupid . ', paymentmode, paymentmodename, paymentamount, ordernumber, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tblpaymentdetails WHERE groupid = ' . $invoice->paymentdetailgroupid));
                                        $this->logger->write($this->db->log(TRUE), 'r');
                                    } catch (Exception $e) {
                                        $this->logger->write("Creditnote Controller : edit() : Failed to insert into table tblpaymentdetails. The error message is " . $e->getMessage(), 'r');
                                    }
                                } catch (Exception $e) {
                                    $this->logger->write("Creditnote Controller : edit() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                        }
                        
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The creditnote - " . $creditnote->id . " has been edited by " . $this->f3->get('SESSION.username'));
                        self::$systemalert = "The credit note  - " . $creditnote->id . " has been edited";
                        $this->logger->write("Creditnote Controller : edit() : The credit note  - " . $creditnote->id . " has been edited", 'r');
                        $creditnote->getByID($id);//refresh
                    } catch (Exception $e) {
                        $this->logger->write("Creditnote Controller : edit() : The operation to edit creditnote - " . $creditnote->id . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit creditnote - " . $creditnote->id . " was not successfull");
                        self::$systemalert = "The operation to edit creditnote - " . $creditnote->id . " was not successful";
                    }
                } elseif ($currenttab == 'tab_seller'){
                    $id = trim($this->f3->get('POST.sellercreditnoteid'));
                    $this->logger->write("Creditnote Controller : edit() : tab_seller : The id to be edited is " . $id, 'r');
                    $creditnote->getByID($id);
                    
                    $org = new organisations($this->db);
                    $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
                    
                    //$this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                    //$this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                    
                    //$this->f3->set('POST.referenceno', $this->f3->get('POST.referenceno'));
                    
                    //$org->edit($this->appsettings['SELLER_RECORD_ID']);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The seller details on creditnote - " . $creditnote->id . " have been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The seller details on creditnote  - " . $creditnote->id . " have been edited";
                    $this->logger->write("Creditnote Controller : edit() : The seller details on creditnote  - " . $creditnote->id . " have been edited", 'r');
                } elseif ($currenttab == 'tab_buyer'){
                    ;
                } elseif ($currenttab == 'tab_good'){
                    $id = trim($this->f3->get('POST.goodcreditnoteid'))? trim($this->f3->get('POST.goodcreditnoteid')) : (!empty(trim($this->f3->get('POST.deletegoodcreditnoteid')))? trim($this->f3->get('POST.deletegoodcreditnoteid')) : trim($this->f3->get('POST.editgoodcreditnoteid')));
                    $this->logger->write("Creditnote Controller : edit() : tab_good : The id to be edited is " . $id, 'r');
                    $creditnote->getByID($id);
                    
                    $good = new goods($this->db);
                    $product = new products($this->db);
                                        
                    $commoditycategory = new commoditycategories($this->db);
                    
                    $this->logger->write("Creditnote Controller : edit() : editgoodid = : " . $this->f3->get('POST.editgoodid'), 'r');
                    $this->logger->write("Creditnote Controller : edit() : deletegoodid = : " . $this->f3->get('POST.deletegoodid'), 'r');
                    $this->logger->write("Creditnote Controller : edit() : deletegoodcreditnoteid = : " . $this->f3->get('POST.deletegoodcreditnoteid'), 'r');
                    
                    if (trim($this->f3->get('POST.editgoodid')) !== '' || !empty(trim($this->f3->get('POST.editgoodid')))) {
                        $this->logger->write("Creditnote Controller : edit() : tab_good : Edit operation", 'r');
                        
                        /**
                         * The following algorithm will be followed when editing a good.
                         * 1. Pick the value of fields from the form
                         * 2. Determine if there is a discount
                         * 3. If [2] is true, populate the discount rate based on the tax rate choice
                         * 4. Retrieve the tax rate from the tax/rate choice made by the user
                         * 5. Calculate TAX
                         * 6. Calculate GROSS AMOUNT
                         * 7. calculate NET AMOUNT
                         *
                         */
                        
                        if ($creditnote->referenceno) {
                            $this->logger->write("Creditnote Controller : edit() : This credit note is already uploaded", 'r');
                            self::$systemalert = "This credit note is already uploaded";
                        } else {
                            $goodid = $this->f3->get('POST.editgoodid');
                            $good->getByID($goodid);
                            //$this->f3->set('POST.groupid', $creditnote->paymentdetailgroupid);
                            $this->f3->set('POST.discountflag', $this->f3->get('POST.editdiscountflag'));
                            
                            
                            $product->getByCode($this->f3->get('POST.edititem'));
                            
                            $this->f3->set('POST.groupid', $creditnote->gooddetailgroupid);
                            $this->f3->set('POST.itemcode', $product->code);
                            $this->f3->set('POST.qty', $this->f3->get('POST.editqty'));
                            
                            $measureunit = new measureunits($this->db);
                            $measureunit->getByCode($product->measureunit);
                            $this->logger->write($this->db->log(TRUE), 'r');
                            
                            $this->f3->set('POST.unitofmeasure', $measureunit->code);
                            $this->f3->set('POST.unitofmeasurename', $measureunit->name);
                            
                            $this->f3->set('POST.unitprice', $this->f3->get('POST.editunitprice'));
                            $this->f3->set('POST.item', $product->name);
                            $this->f3->set('POST.totalWeight', $this->resolveLineWeightFromPost('POST.editweight', $product->weight));
                            $this->f3->set('POST.pieceQty', $this->f3->get('POST.editqty'));
                            $this->f3->set('POST.pieceMeasureUnit', $product->piecemeasureunit);
                            $this->f3->set('POST.deemedExemptCode', $product->deemedExemptCode);
                            
                            /**
                             * 1. Retrieve the good's commodity category
                             * 2. Retrieve the buyer's TIN
                             * 3. Check EFRIS for the customer's status against the commodity code
                             * 4. If the check in step [3] returns EXEMPT or DEEMED, then override the tax
                             */
                            
                            $commoditycategory->getByCode($product->commoditycategorycode);
                            
                            $this->f3->set('POST.goodscategoryid', $commoditycategory->commoditycode);
                            $this->f3->set('POST.goodscategoryname', $commoditycategory->commodityname);
                            
                            $buyer_g = new buyers($this->db);
                            $buyer_g->getByID($invoice->buyerid);
                            $this->logger->write($this->db->log(TRUE), 'r');
                            
                            $data_c = $this->util->checktaxpayertype($this->f3->get('SESSION.id'), $buyer_g->tin, $commoditycategory->commoditycode);//will return JSON.
                            $data_c = json_decode($data_c, true);
                            
                            if ($data_c) {
                                $commodityCategoryTaxpayerType = NULL;
                                
                                if (isset($data_c['commodityCategory'])){
                                    if ($data_c['commodityCategory']) {
                                        
                                        foreach($data_c['commodityCategory'] as $elem){
                                            $commodityCategoryTaxpayerType = !isset($elem['commodityCategoryTaxpayerType'])? NULL : $elem['commodityCategoryTaxpayerType'];
                                        }
                                    }
                                }
                                
                                
                            }
                            
                            
                            
                            //Calculate
                            $tr = new taxrates($this->db);
                            
                            if ($commodityCategoryTaxpayerType == '103') {//DEEMED
                                $this->logger->write("Creditnote Controller : edit() : tab_good : Tax rate has been overidden to DEEMED", 'r');
                                $this->f3->set('POST.taxid', $this->appsettings['DEEMEDTAXRATE']);
                                $tr->getByID($this->appsettings['DEEMEDTAXRATE']);
                            } elseif ($commodityCategoryTaxpayerType == '102'){//EXEMPT
                                $this->logger->write("Creditnote Controller : edit() : tab_good : Tax rate has been overidden to EXEMPT", 'r');
                                $this->f3->set('POST.taxid', $this->appsettings['EXPEMPTTAXRATE']);
                                $tr->getByID($this->appsettings['EXPEMPTTAXRATE']);
                            } else {
                                $this->logger->write("Creditnote Controller : edit() : tab_good : Tax rate has not been overidden", 'r');
                                $this->f3->set('POST.taxid', $this->f3->get('POST.edittaxrate'));
                                $tr->getByID($this->f3->get('POST.edittaxrate'));
                            }
                            
                            $taxcode = $tr->code;
                            $taxname = $tr->name;
                            $taxcategory = $tr->category;
                            $taxdescription = $tr->description;
                            $rate = $tr->rate? $tr->rate : 0;
                            $qty = $this->f3->get('POST.editqty');
                            $unit = $this->f3->get('POST.editunitprice');
                            $discountpct = empty($this->f3->get('POST.editdiscountpercentage'))? 0 : (float)$this->f3->get('POST.editdiscountpercentage');
                            $taxdisplaycategory = $tr->displayCategoryCode;
                            
                            /*$total = ($qty * $unit);
                            $discount = ($discountpct/100) * $total;
                            
                            $gross = $total - $discount;
                            $discount = (-1) * $discount;
                            
                            $tax = ($gross/($rate + 1)) * $rate;
                            $net = $gross - $tax;*/
                            
                            /**
                             * Modification Date: 2021-01-26
                             * Modified By: Francis Lubanga
                             * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                             */
                            $d_gross = 0;
                            $d_tax = 0;
                            $d_net = 0;
                            
                            $tmpqty = $qty;
                            
                            if ($qty == 0) {
                                $tmpqty = 1;
                            }
                            
                            $derivedExcise = $this->deriveExciseFromRules($product, $qty, $unit);
                            $qtyValueForRebase = (float)$qty;
                            $unitValueForRebase = (float)$unit;
                            $exciseTaxForRebase = (float)$this->toFloat($derivedExcise['excisetax']);
                            if ($qtyValueForRebase > 0 && $exciseTaxForRebase > 0) {
                                $unit = $unitValueForRebase + ($exciseTaxForRebase / $qtyValueForRebase);
                            } else {
                                $unit = $unitValueForRebase;
                            }

                            $total = ($tmpqty * $unit);
                            $discount = ($discountpct/100) * $total;
                            
                            //$gross = $total - $discount;
                            $gross = $total;
                            $discount = (-1) * $discount;
                            
                            $tax = ($gross/($rate + 1)) * $rate;
                            $net = $gross - $tax;
                            
                            
                            //$total = ($qty * $unit);
                            if ($discountpct > 0) {
                                $this->f3->set('POST.discounttaxrate', $rate);
                                $this->f3->set('POST.discountpercentage', $discountpct);
                                /*
                                 $discount = ($discountpct/100) * $total;
                                 */
                                
                                $d_gross = $discount;
                                $d_tax = ($d_gross/($rate + 1)) * $rate;
                                
                                $d_net = $d_gross - $d_tax;
                                
                                $this->logger->write("Creditnote Controller : edit() : tab_good : disc_gross = " . $d_gross, 'r');
                                $this->logger->write("Creditnote Controller : edit() : tab_good : disc_tax = " . $d_tax, 'r');
                                $this->logger->write("Creditnote Controller : edit() : tab_good : disc_net = " . $d_net, 'r');
                                
                            } else {
                                $this->f3->set('POST.discounttaxrate', 0);
                                /*
                                 $gross = $total;
                                 
                                 $tax = ($gross/($rate + 1)) * $rate;
                                 $net = $gross - $tax;
                                 */
                            }
                            
                            $this->logger->write("Creditnote Controller : edit() : tab_good : discountpct = " . $discountpct, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : total = " . $total, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : discount = " . $discount, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : gross = " . $gross, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : taxcode = " . $taxcode, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : rate = " . $rate, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : qty = " . $qty, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : rate = " . $rate, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : tax = " . $tax, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : net = " . $net, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : unit = " . $unit, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : taxcategory = " . $taxcategory, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : taxdescription = " . $taxdescription, 'r');
                            
                            $this->f3->set('POST.total', $total);
                            $this->f3->set('POST.taxrate', $rate);
                            $this->f3->set('POST.discounttotal', $discount);
                            
                            if ($this->vatRegistered == 'Y') {
                                $this->f3->set('POST.tax', $tax);
                                $this->f3->set('POST.taxcategory', $taxcategory);
                                $this->f3->set('POST.displayCategoryCode', $taxdisplaycategory);
                            } else {
                                $this->f3->set('POST.tax', 0);
                                $this->f3->set('POST.taxcategory', NULL);
                                $this->f3->set('POST.displayCategoryCode', NULL);
                            }
                            
                            
                            $this->f3->set('POST.deemedflag', $this->f3->get('POST.editdeemedflag'));
                            $this->f3->set('POST.exciseflag', $derivedExcise['exciseflag']);
                            $this->f3->set('POST.categoryid', $this->f3->get('POST.editcategoryid'));
                            $this->f3->set('POST.categoryname', $this->f3->get('POST.editcategoryname'));
                            
                            
                            
                            $this->f3->set('POST.exciserate', $derivedExcise['exciserate']);
                            $this->f3->set('POST.exciserule', $derivedExcise['exciserule']);
                            $this->f3->set('POST.excisetax', $derivedExcise['excisetax']);
                            $this->f3->set('POST.pack', $derivedExcise['pack']);
                            $this->f3->set('POST.stick', $derivedExcise['stick']);
                            $this->f3->set('POST.exciseunit', $derivedExcise['exciseunit']);
                            $this->f3->set('POST.excisecurrency', $derivedExcise['excisecurrency']);
                            $this->f3->set('POST.exciseratename', $derivedExcise['exciseratename']);

                            $exciseUnitSql = 'NULL';
                            if ($derivedExcise['exciseunit'] !== null && trim((string)$derivedExcise['exciseunit']) !== '') {
                                $exciseUnitValue = trim((string)$derivedExcise['exciseunit']);
                                $exciseUnitSql = is_numeric($exciseUnitValue)
                                    ? $exciseUnitValue
                                    : '"' . addslashes($exciseUnitValue) . '"';
                            }

                            $exciseCurrencySql = 'NULL';
                            if ($derivedExcise['excisecurrency'] !== null && trim((string)$derivedExcise['excisecurrency']) !== '') {
                                $exciseCurrencyValue = trim((string)$derivedExcise['excisecurrency']);
                                $exciseCurrencySql = is_numeric($exciseCurrencyValue)
                                    ? $exciseCurrencyValue
                                    : '"' . addslashes($exciseCurrencyValue) . '"';
                            }
                            
                            
                            $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                            $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                            
                            $good->edit($goodid);
                            
                            try{
                                $this->db->exec(array('DELETE FROM tbltaxdetails WHERE goodid = ' . $good->id . ' AND groupid = ' . $creditnote->taxdetailgroupid));
                                
                                $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $creditnote->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcategory . '", "' . $taxcode . '", ' . ($net + $d_net) . ', ' . $rate . ', ' . ($tax + $d_tax) . ', ' . ($gross + $d_gross) . ', ' . $exciseUnitSql . ', ' . $exciseCurrencySql . ', "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));

                                $mainNetAmount = (float) ($net + $d_net);
                                $exciseTaxAmount = (float) $this->toFloat($derivedExcise['excisetax']);
                                $exciseRateValue = $this->toFloat($derivedExcise['exciserate']);
                                $exciseRateSql = ($exciseRateValue === null || $exciseRateValue === '') ? 'NULL' : (float) $exciseRateValue;
                                $exciseRateNameSql = empty($derivedExcise['exciseratename']) ? 'NULL' : '"' . addslashes($derivedExcise['exciseratename']) . '"';

                                if ($exciseTaxAmount > 0) {
                                    $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                                            VALUES(' . $creditnote->taxdetailgroupid . ', ' . $goodid . ', "E: Excise Duty", "05", ' . max(0, ($mainNetAmount - $exciseTaxAmount)) . ', ' . $exciseRateSql . ', ' . $exciseTaxAmount . ', ' . $mainNetAmount . ', ' . $exciseUnitSql . ', ' . $exciseCurrencySql . ', ' . $exciseRateNameSql . ', "E", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                }
                                
                                //insert a tax record for the discount
                                /*
                                 if ($discountpct > 0) {
                                 $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                 VALUES(' . $creditnote->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcode . '", ' . $d_net . ', ' . $rate . ', ' . $d_tax . ', ' . $d_gross . ', NULL, NULL, "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                 }*/
                                
                            } catch (Exception $e) {
                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into table tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                            }
                        }
                        
                        $creditnote->getByID($id);//refresh
                    } elseif (trim($this->f3->get('POST.deletegoodid')) !== '' || !empty(trim($this->f3->get('POST.deletegoodid')))) {
                        $this->logger->write("Creditnote Controller : edit() : tab_good : Delete operation", 'r');
                        $good->getByID($this->f3->get('POST.deletegoodid'));
                        $good->delete($this->f3->get('POST.deletegoodid'));
                        
                        try{
                            $this->db->exec(array('DELETE FROM tbltaxdetails WHERE goodid = ' . $good->id));
                        } catch (Exception $e) {
                            $this->logger->write("Creditnote Controller : edit() : The operation to delete the related tax details was not successful. The error messages is " . $e->getMessage(), 'r');
                        }
                    } else {
                        $this->logger->write("Creditnote Controller : edit() : tab_good : Add operation", 'r');
                        
                        /**
                         * The following algorithm will be followed when adding a good.
                         * 1. Pick the value of fields from the form
                         * 2. Determine if there is a discount
                         * 3. If [2] is true, populate the discount rate based on the tax rate choice
                         * 4. Retrieve the tax rate from the tax/rate choice made by the user
                         * 5. Calculate TAX
                         * 6. Calculate GROSS AMOUNT
                         * 7. calculate NET AMOUNT
                         * 
                         */
                        
                        $this->f3->set('POST.groupid', $creditnote->paymentdetailgroupid);
                        $this->f3->set('POST.ordernumber', $this->f3->get('POST.addordernumber'));
                        $this->f3->set('POST.discountflag', $this->f3->get('POST.adddiscountflag'));
                        
                        
                        $product->getByCode($this->f3->get('POST.additem'));
                        
                        $this->f3->set('POST.groupid', $creditnote->gooddetailgroupid);
                        $this->f3->set('POST.itemcode', $product->code);
                        $this->f3->set('POST.qty', $this->f3->get('POST.addqty'));
                        
                        $measureunit = new measureunits($this->db);
                        $measureunit->getByCode($product->measureunit);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        
                        $this->f3->set('POST.unitofmeasure', $measureunit->code);
                        $this->f3->set('POST.unitofmeasurename', $measureunit->name);
                        
                        $this->f3->set('POST.unitprice', $this->f3->get('POST.addunitprice'));
                        $this->f3->set('POST.item', $product->name);
                        $this->f3->set('POST.totalWeight', $this->resolveLineWeightFromPost('POST.addweight', $product->weight));
                        $this->f3->set('POST.pieceQty', $this->f3->get('POST.addqty'));
                        $this->f3->set('POST.pieceMeasureUnit', $product->piecemeasureunit);
                        $this->f3->set('POST.deemedExemptCode', $product->deemedExemptCode);
                        
                        /**
                         * 1. Retrieve the good's commodity category
                         * 2. Retrieve the buyer's TIN
                         * 3. Check EFRIS for the customer's status against the commodity code
                         * 4. If the check in step [3] returns EXEMPT or DEEMED, then override the tax
                         */
                        
                        $commoditycategory->getByCode($product->commoditycategorycode);
                        
                        $this->f3->set('POST.goodscategoryid', $commoditycategory->commoditycode);
                        $this->f3->set('POST.goodscategoryname', $commoditycategory->commodityname);
                        
                        $buyer_g = new buyers($this->db);
                        $buyer_g->getByID($invoice->buyerid);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        
                        $data_c = $this->util->checktaxpayertype($this->f3->get('SESSION.id'), $buyer_g->tin, $commoditycategory->commoditycode);//will return JSON.
                        $data_c = json_decode($data_c, true);
                        
                        if ($data_c) {
                            $commodityCategoryTaxpayerType = NULL;
                            
                            if (isset($data_c['commodityCategory'])){
                                if ($data_c['commodityCategory']) {
                                    
                                    foreach($data_c['commodityCategory'] as $elem){
                                        $commodityCategoryTaxpayerType = !isset($elem['commodityCategoryTaxpayerType'])? NULL : $elem['commodityCategoryTaxpayerType'];
                                    }
                                }
                            }
                            
                            
                        }
                        
                        
                        //Calculate
                        $tr = new taxrates($this->db);
                        
                        if ($commodityCategoryTaxpayerType == '103') {//DEEMED
                            $this->logger->write("Creditnote Controller : edit() : tab_good : Tax rate has been overidden to DEEMED", 'r');
                            $this->f3->set('POST.taxid', $this->appsettings['DEEMEDTAXRATE']);
                            $tr->getByID($this->appsettings['DEEMEDTAXRATE']);
                        } elseif ($commodityCategoryTaxpayerType == '102'){//EXEMPT
                            $this->logger->write("Creditnote Controller : edit() : tab_good : Tax rate has been overidden to EXEMPT", 'r');
                            $this->f3->set('POST.taxid', $this->appsettings['EXPEMPTTAXRATE']);
                            $tr->getByID($this->appsettings['EXPEMPTTAXRATE']);
                        } else {
                            $this->logger->write("Creditnote Controller : edit() : tab_good : Tax rate has not been overidden", 'r');
                            $this->f3->set('POST.taxid', $this->f3->get('POST.addtaxrate'));
                            $tr->getByID($this->f3->get('POST.addtaxrate'));
                        }
                        
                        $taxcode = $tr->code;
                        $taxname = $tr->name;
                        $taxcategory = $tr->category;
                        $taxdescription = $tr->description;
                        $rate = $tr->rate? $tr->rate : 0;
                        $qty = $this->f3->get('POST.addqty');
                        $unit = $this->f3->get('POST.addunitprice');
                        $discountpct = empty($this->f3->get('POST.adddiscountpercentage'))? 0 : (float)$this->f3->get('POST.adddiscountpercentage');
                        
                        
                        /*$total = ($qty * $unit);
                        $discount = ($discountpct/100) * $total;
                        
                        $gross = $total - $discount;
                        $discount = (-1) * $discount;
                        
                        $tax = ($gross/($rate + 1)) * $rate;
                        $net = $gross - $tax;*/
                        
                        /**
                         * Modification Date: 2021-01-26
                         * Modified By: Francis Lubanga
                         * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                         */
                        $d_gross = 0;
                        $d_tax = 0;
                        $d_net = 0;
                        
                        $tmpqty = $qty;
                        
                        if ($qty == 0) {
                            $tmpqty = 1;
                        }
                        
                        $derivedExcise = $this->deriveExciseFromRules($product, $qty, $unit);
                        $qtyValueForRebase = (float)$qty;
                        $unitValueForRebase = (float)$unit;
                        $exciseTaxForRebase = (float)$this->toFloat($derivedExcise['excisetax']);
                        if ($qtyValueForRebase > 0 && $exciseTaxForRebase > 0) {
                            $unit = $unitValueForRebase + ($exciseTaxForRebase / $qtyValueForRebase);
                        } else {
                            $unit = $unitValueForRebase;
                        }

                        $total = ($tmpqty * $unit);
                        $discount = ($discountpct/100) * $total;
                        
                        //$gross = $total - $discount;
                        $gross = $total;
                        $discount = (-1) * $discount;
                        
                        $tax = ($gross/($rate + 1)) * $rate;
                        $net = $gross - $tax;
                        
                        
                        //$total = ($qty * $unit);
                        if ($discountpct > 0) {
                            $this->f3->set('POST.discounttaxrate', $rate);
                            $this->f3->set('POST.discountpercentage', $discountpct);
                            /*
                             $discount = ($discountpct/100) * $total;
                             */
                            
                            $d_gross = $discount;
                            $d_tax = ($d_gross/($rate + 1)) * $rate;
                            
                            $d_net = $d_gross - $d_tax;
                            
                            $this->logger->write("Creditnote Controller : edit() : tab_good : disc_gross = " . $d_gross, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : disc_tax = " . $d_tax, 'r');
                            $this->logger->write("Creditnote Controller : edit() : tab_good : disc_net = " . $d_net, 'r');
                            
                        } else {
                            $this->f3->set('POST.discounttaxrate', 0);
                            /*
                             $gross = $total;
                             
                             $tax = ($gross/($rate + 1)) * $rate;
                             $net = $gross - $tax;
                             */
                        }
                        
                        $this->logger->write("Creditnote Controller : edit() : tab_good : discountpct = " . $discountpct, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : total = " . $total, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : discount = " . $discount, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : gross = " . $gross, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : taxcode = " . $taxcode, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : rate = " . $rate, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : qty = " . $qty, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : rate = " . $rate, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : tax = " . $tax, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : net = " . $net, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : unit = " . $unit, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : taxcategory = " . $taxcategory, 'r');
                        $this->logger->write("Creditnote Controller : edit() : tab_good : taxdescription = " . $taxdescription, 'r');
                        
                        $this->f3->set('POST.total', $total);
                        $this->f3->set('POST.taxrate', $rate);
                        $this->f3->set('POST.discounttotal', $discount);
                        
                        if ($this->vatRegistered == 'Y') {
                            $this->f3->set('POST.tax', $tax);
                            $this->f3->set('POST.taxcategory', $taxcategory);
                        } else {
                            $this->f3->set('POST.tax', 0);
                            $this->f3->set('POST.taxcategory', NULL);
                        }
                        
                        
                        $this->f3->set('POST.deemedflag', $this->f3->get('POST.adddeemedflag'));
                        $this->f3->set('POST.exciseflag', $derivedExcise['exciseflag']);
                        $this->f3->set('POST.categoryid', $this->f3->get('POST.addcategoryid'));
                        $this->f3->set('POST.categoryname', $this->f3->get('POST.addcategoryname'));
                        
                        
                        
                        $this->f3->set('POST.exciserate', $derivedExcise['exciserate']);
                        $this->f3->set('POST.exciserule', $derivedExcise['exciserule']);
                        $this->f3->set('POST.excisetax', $derivedExcise['excisetax']);
                        $this->f3->set('POST.pack', $derivedExcise['pack']);
                        $this->f3->set('POST.stick', $derivedExcise['stick']);
                        $this->f3->set('POST.exciseunit', $derivedExcise['exciseunit']);
                        $this->f3->set('POST.excisecurrency', $derivedExcise['excisecurrency']);
                        $this->f3->set('POST.exciseratename', $derivedExcise['exciseratename']);

                        $exciseUnitSql = 'NULL';
                        if ($derivedExcise['exciseunit'] !== null && trim((string)$derivedExcise['exciseunit']) !== '') {
                            $exciseUnitValue = trim((string)$derivedExcise['exciseunit']);
                            $exciseUnitSql = is_numeric($exciseUnitValue)
                                ? $exciseUnitValue
                                : '"' . addslashes($exciseUnitValue) . '"';
                        }

                        $exciseCurrencySql = 'NULL';
                        if ($derivedExcise['excisecurrency'] !== null && trim((string)$derivedExcise['excisecurrency']) !== '') {
                            $exciseCurrencyValue = trim((string)$derivedExcise['excisecurrency']);
                            $exciseCurrencySql = is_numeric($exciseCurrencyValue)
                                ? $exciseCurrencyValue
                                : '"' . addslashes($exciseCurrencyValue) . '"';
                        }
                        
                        
                        $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                        $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                        
                        $good->add();
                        
                        try{
                            $data = array ();
                            $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetails WHERE insertedby = ' . $this->f3->get('SESSION.id')));
                            foreach ( $r as $obj ) {
                                $data [] = $obj;
                            }
                            
                            $goodid = $data[0]['id'];
                            
                            if ($this->vatRegistered == 'Y') {
                                $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $creditnote->taxdetailgroupid . ', ' . $goodid . ', "' . $taxcode . '", ' . ($net + $d_net) . ', ' . $rate . ', ' . ($tax + $d_tax) . ', ' . ($gross + $d_gross) . ', ' . $exciseUnitSql . ', ' . $exciseCurrencySql . ', "' . $taxname . '", "' . $taxdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));

                                $mainNetAmount = (float) ($net + $d_net);
                                $exciseTaxAmount = (float) $this->toFloat($derivedExcise['excisetax']);
                                $exciseRateValue = $this->toFloat($derivedExcise['exciserate']);
                                $exciseRateSql = ($exciseRateValue === null || $exciseRateValue === '') ? 'NULL' : (float) $exciseRateValue;
                                $exciseRateNameSql = empty($derivedExcise['exciseratename']) ? 'NULL' : '"' . addslashes($derivedExcise['exciseratename']) . '"';

                                if ($exciseTaxAmount > 0) {
                                    $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, inserteddt, insertedby, modifieddt, modifiedby)
                                                            VALUES(' . $creditnote->taxdetailgroupid . ', ' . $goodid . ', "E: Excise Duty", "05", ' . max(0, ($mainNetAmount - $exciseTaxAmount)) . ', ' . $exciseRateSql . ', ' . $exciseTaxAmount . ', ' . $mainNetAmount . ', ' . $exciseUnitSql . ', ' . $exciseCurrencySql . ', ' . $exciseRateNameSql . ', "E", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                }
                            }
                            
                            
                        } catch (Exception $e) {
                            $this->logger->write("Creditnote Controller : edit() : Failed to insert into table tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                    
                    $this->syncCreditnoteTotalWeight($creditnote->id, $creditnote->gooddetailgroupid);
                    $creditnote->getByID($id);

                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The good details on creditnote - " . $creditnote->id . " have been edited by " . $this->f3->get('SESSION.username'));
                    
                    
                    if ($creditnote->referenceno) {
                        $this->logger->write("Creditnote Controller : edit() : This credit note is already uploaded", 'r');
                        self::$systemalert = "This credit note is already uploaded";
                    } else {
                        self::$systemalert = "The good details on creditnote  - " . $creditnote->id . " have been edited";
                        $this->logger->write("Creditnote Controller : edit() : The good details on creditnote  - " . $creditnote->id . " have been edited", 'r');
                    }
                } elseif ($currenttab == 'tab_tax'){
                    ;
                } elseif ($currenttab == 'tab_payment'){
                    $id = trim($this->f3->get('POST.addpaymentcreditnoteid'))? trim($this->f3->get('POST.addpaymentcreditnoteid')) : trim($this->f3->get('POST.deletepaymentcreditnoteid'));
                    $this->logger->write("Creditnote Controller : edit() : tab_payment : The id to be edited is " . $id, 'r');
                    $creditnote->getByID($id);
                    
                    $payment = new payments($this->db);
                    
                    if (trim($this->f3->get('POST.editpaymentid')) !== '' || !empty(trim($this->f3->get('POST.editpaymentid')))) {
                        $this->logger->write("Creditnote Controller : edit() : tab_payment : Edit operation", 'r');
                    
                    } elseif (trim($this->f3->get('POST.deletepaymentid')) !== '' || !empty(trim($this->f3->get('POST.deletepaymentid')))) {
                        $this->logger->write("Creditnote Controller : edit() : tab_payment : Delete operation", 'r');
                        $payment->getByID($this->f3->get('POST.deletepaymentid'));
                        $payment->delete($this->f3->get('POST.deletepaymentid'));
                    } else {
                        $this->logger->write("Creditnote Controller : edit() : tab_payment : Add operation", 'r');
                        
                        $this->f3->set('POST.groupid', $creditnote->paymentdetailgroupid);
                        $this->f3->set('POST.ordernumber', $this->f3->get('POST.addpaymentordernumber'));
                        $this->f3->set('POST.paymentamount', $this->f3->get('POST.addpaymentamount'));
                        $this->f3->set('POST.paymentmode', $this->f3->get('POST.addpaymentmode'));
                        
                        $paymentmode = new paymentmodes($this->db);
                        $paymentmode->getByCode($this->f3->get('POST.addpaymentmode'));
                        
                        $this->f3->set('POST.paymentmodename', $paymentmode->name);
                        
                        $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                        $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                        $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                        
                        $payment->add();
                    }
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The payment details on creditnote - " . $creditnote->id . " have been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The payment details on creditnote  - " . $creditnote->id . " have been edited";
                    $this->logger->write("Creditnote Controller : edit() : The payment details on creditnote  - " . $creditnote->id . " have been edited", 'r');
                } else {
                    $this->logger->write("Creditnote Controller : edit() :No TAB was selected", 'r');
                    $this->f3->reroute('/creditnote');
                }

            } else {
                $this->logger->write("Creditnote Controller : edit() : The user is not allowed to perform this function", 'r');
                $this->f3->reroute('/forbidden');
            }
        } else { // ADD Operation: mainly handles the GENERAL parameters, as the rest of the parameters will be added using the EDIT option
            $operation = NULL; // tblevents
            $permission = 'CREATECREDITNOTE'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications
            
            $this->logger->write("Creditnote Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $this->logger->write("Creditnote Controller : edit() : Adding of credit note started.", 'r');
                
                
                $oriinvoiceid = $this->f3->get('POST.searchinvoice');
                $invoice = new invoices($this->db);
                $invoice->getByInvoiceID($oriinvoiceid);
                $this->logger->write($this->db->log(TRUE), 'r');
                $this->logger->write("Creditnote Controller : edit() : invoice = " . $oriinvoiceid, 'r');
                
                $this->f3->set('POST.erpcreditnoteid', $this->f3->get('POST.erpcreditnoteid'));
                $this->f3->set('POST.erprefundinvoiceno', $this->f3->get('POST.erprefundinvoiceno'));                
                $this->f3->set('POST.invoiceapplycategorycode', trim($this->appsettings['CREDITDEFAULTAPPCATEGORY']));
                $this->f3->set('POST.reasoncode', $this->f3->get('POST.reasoncode'));
                $this->f3->set('POST.reason', $this->f3->get('POST.reason'));               
                $this->f3->set('POST.datasource', $this->f3->get('POST.datasource'));
                $this->f3->set('POST.remarks', $this->f3->get('POST.remarks'));    
                $this->f3->set('POST.operator', $this->f3->get('SESSION.username'));
                
                $this->f3->set('POST.deviceno', $invoice->deviceno);
                $this->f3->set('POST.oriinvoiceid', $invoice->einvoiceid);
                $this->f3->set('POST.oriinvoiceno', $invoice->einvoicenumber);
                $this->f3->set('POST.currency', $invoice->currency);
                $this->f3->set('POST.origrossamount', $invoice->grossamount);
                // Buyer must be inherited from the original invoice (with legacy customer fallback) so Goods tab stays accessible.
                $resolvedBuyerId = $this->resolveBuyerIdFromOriginalInvoice($invoice);
                $this->f3->set('POST.buyerid', $resolvedBuyerId);
                if ($resolvedBuyerId === null) {
                    self::$systemalert = 'Buyer could not be inherited from the selected original invoice. Update buyer information on the source invoice, then try again.';
                    $this->f3->set('SESSION.systemalert', self::$systemalert);
                    $this->logger->write('Creditnote Controller : edit() : Create blocked because buyer could not be resolved for original invoice id ' . $invoice->einvoiceid . ' (invoice row id ' . $invoice->id . ')', 'r');
                    $this->f3->reroute('/createcreditnote');
                    return;
                }
                //$this->f3->set('POST.einvoicedatamatrixcode', $invoice->einvoicedatamatrixcode);
                
                $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                // @TODO check the params for empty/null values
                if (trim($this->f3->get('POST.searchinvoice')) !== '' || ! empty(trim($this->f3->get('POST.searchinvoice')))) {
                    try {
                        // Proceed & create
                        $creditnote->add();
                        // $this->logger->write("Creditnote Controller : edit() : A new creditnote has been added", 'r');
                        try {
                            // retrieve the most recently inserted creditnote
                            // @TODO place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                            $data = array();
                            $r = $this->db->exec(array(
                                'SELECT MAX(id) "id" FROM tblcreditnotes WHERE insertedby = ' . $this->f3->get('SESSION.id')
                            ));
                            foreach ($r as $obj) {
                                $data[] = $obj;
                            }
                            
                            // $this->logger->write("Creditnote Controller : edit() : The creditnote " . $data[0]['id'] . " has been added", 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The creditnote id " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                            self::$systemalert = "The credit note id " . $data[0]['id'] . " has been added";
                            $id = $data[0]['id'];
                            $creditnote->getByID($id);
                            
                            /**
                             * 1. Add a GROUPID for goods and store it in a field called gooddetailgroupid
                             * 1. Add a GROUPID for payments and store it in a field called paymentdetailgroupid
                             * 1. Add a GROUPID for tax details and store it in a field called taxdetailgroupid
                             */
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $taxdetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tblcreditnotes SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                } catch (Exception $e) {
                                    $this->logger->write("Creditnote Controller : edit() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the debitnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $gooddetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tblcreditnotes SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                    
                                    /**
                                     * 1. Retrieve good details from the parent invoice
                                     * 2(a). Copy good details to the debit note
                                     * 2(b). Retrieve the new goodid
                                     * 2(c). Copy related tax to the credit note
                                     *
                                     * */
                                    try{
                                        
                                        $temp = $this->db->exec(array('SELECT id, groupid, item, itemcode, qty, unitofmeasure, unitprice, total, taxrate, tax, ifnull(discounttotal, NULL) discounttotal, ifnull(discounttaxrate, NULL) discounttaxrate, ifnull(ordernumber, NULL) ordernumber, discountflag, deemedflag, exciseflag, ifnull(categoryid, NULL) categoryid, categoryname, goodscategoryid, goodscategoryname
                                                                        , taxid, discountpercentage, exciserate, ifnull(exciserule, NULL) exciserule, ifnull(excisetax, NULL) excisetax, ifnull(pack, NULL) pack, ifnull(stick, NULL) stick, ifnull(exciseunit, NULL) exciseunit, excisecurrency, exciseratename, taxcategory, displayCategoryCode, unitofmeasurename, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tblgooddetails WHERE groupid = ' . $invoice->gooddetailgroupid));
                                        
                                        $k = 0;
                                        
                                        foreach ($temp as $obj) {
                                            $o_goodid = $obj['id'];//original good id
                                            $this->logger->write("Creditnote Controller : edit() : The original good id is " . $o_goodid, 'r');
                                            
                                            try {
                                                $this->db->exec(array('INSERT INTO tblgooddetails (groupid, item, itemcode, qty, unitofmeasure, unitprice, total, taxrate, tax, discounttotal, discounttaxrate, ordernumber, discountflag, deemedflag, exciseflag, categoryid, categoryname, goodscategoryid, goodscategoryname
                                                                    , exciserate, taxid, discountpercentage, exciserule, excisetax, pack, stick, exciseunit, excisecurrency, exciseratename, taxcategory, displayCategoryCode, unitofmeasurename, totalWeight, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES( '. $gooddetailgroupid . ', "' . $obj['item'] . '", "' . $obj['itemcode'] . '", ' . $obj['qty'] . ', "' . $obj['unitofmeasure'] . '", ' . $obj['unitprice'] . ', ' . $obj['total'] . ', ' . $obj['taxrate'] . ', ' . $obj['tax'] . ', ' . $obj['discounttotal'] . ', ' . $obj['discounttaxrate'] . ', ' . (empty($obj['ordernumber'])? strval($k) : $obj['ordernumber']) . ', ' . $obj['discountflag'] . ', ' . $obj['deemedflag'] . ', ' . $obj['exciseflag'] . ', ' . (empty($obj['categoryid'])? 'NULL' : $obj['categoryid']) . ', "' . $obj['categoryname'] . '", ' . $obj['goodscategoryid'] . ', "' . $obj['goodscategoryname'] . '", "' .
                                                    $obj['exciserate'] . '", ' . (empty($obj['taxid'])? 'NULL' : $obj['taxid']) . ', ' . (empty($obj['discountpercentage'])? 'NULL' : $obj['discountpercentage']) . ', ' . (empty($obj['exciserule'])? 'NULL' : $obj['exciserule']) . ', ' . (empty($obj['excisetax'])? 'NULL' : $obj['excisetax']) . ', ' . (empty($obj['pack'])? 'NULL' : $obj['pack']) . ', ' . (empty($obj['stick'])? 'NULL' : $obj['stick']) . ', ' . (empty($obj['exciseunit'])? 'NULL' : $obj['exciseunit']) . ', "' . $obj['excisecurrency'] . '", "' . $obj['exciseratename'] . '", "' . $obj['taxcategory'] . '", "' . $obj['displayCategoryCode'] . '", "' . $obj['unitofmeasurename'] . '", ' . (empty($obj['totalWeight']) ? 'NULL' : $obj['totalWeight']) . ', ' . $obj['disabled'] . ', NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            
                                            $g = array ();
                                            $gr = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetails WHERE groupid = ' . $gooddetailgroupid . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                            
                                            foreach ( $gr as $obj ) {
                                                $g[] = $obj;
                                            }
                                            
                                            $goodid = $g[0]['id'];
                                            
                                            try {
                                                if ($this->vatRegistered == 'Y') {
                                                    /*Insert tax details from the parent invoice*/
                                                    $this->db->exec(array('INSERT INTO tbltaxdetails (groupid, goodid, taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, disabled, inserteddt, insertedby, modifieddt, modifiedby)
                                                                            SELECT '. $taxdetailgroupid . ', ' . $goodid . ', taxcategory, taxcategoryCode, netamount, taxrate, taxamount, grossamount, exciseunit, excisecurrency, taxratename, taxdescription, disabled, NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ' FROM tbltaxdetails WHERE groupid = ' . $invoice->taxdetailgroupid . ' AND goodid = ' . $o_goodid));
                                                    
                                                    $this->logger->write($this->db->log(TRUE), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into table tblgooddetails. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            
                                            $k = $k + 1;
                                        }
                                        
                                    } catch (Exception $e) {
                                        $this->logger->write("Creditnote Controller : edit() : Failed to insert into table tblgooddetails & tbltaxdetails. The error message is " . $e->getMessage(), 'r');
                                    }
                                    
                                } catch (Exception $e) {
                                    $this->logger->write("Creditnote Controller : edit() : Failed to select from table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            
                            try {
                                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;
                                
                                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                        VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                
                                try {
                                    $pg = array ();
                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                    
                                    foreach ( $r as $obj ) {
                                        $pg [] = $obj;
                                    }
                                    
                                    $paymentdetailgroupid = $pg[0]['id'];
                                    $this->db->exec(array('UPDATE tblcreditnotes SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                } catch (Exception $e) {
                                    $this->logger->write("Creditnote Controller : edit() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                }
                            } catch (Exception $e) {
                                $this->logger->write("Creditnote Controller : edit() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                            }
                            
                            $creditnote->getByID($id);//refresh the creditnote object
                        } catch (Exception $e) {
                            $this->logger->write("Creditnote Controller : edit() : The operation to retrieve the most recently added creditnote was not successful. The error messages is " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added creditnote was not successful");
                            self::$systemalert = "The operation to retrieve the most recently added credit note was not successful";
                        }
                    } catch (Exception $e) {
                        $this->logger->write("Creditnote Controller : edit() : The operation to add a creditnote was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a creditnote was not successful");
                        self::$systemalert = "The operation to add a credit note was not successful. An internal error occured.";
                        $this->f3->set('systemalert', self::$systemalert);
                        self::add();
                        exit();
                    }
                } else {
                    $this->logger->write("Creditnote Controller : edit() : The user is not allowed to perform this function", 'r');
                    $this->f3->reroute('/forbidden');
                }
            } else { // some params are empty
                // ABORT MISSION
                self::$systemalert = "The operation to add a credit tnote was not successful. An internal error occured.";
                $this->f3->set('systemalert', self::$systemalert);
                self::add();
                exit();
            }
        }
        
        $cdnoteapplycategorycode = new cdnoteapplycategorycodes($this->db);
        $cdnoteapplycategorycodes = $cdnoteapplycategorycode->all();
        $this->f3->set('cdnoteapplycategorycodes', $cdnoteapplycategorycodes);
        
        $cdnoteapprovestatus = new cdnoteapprovestatuses($this->db);
        $cdnoteapprovestatuses = $cdnoteapprovestatus->all();
        $this->f3->set('cdnoteapprovestatuses', $cdnoteapprovestatuses);
        
        $cdnotereasoncode = new cdnotereasoncodes($this->db);
        $cdnotereasoncodes = $cdnotereasoncode->all();
        $this->f3->set('cdnotereasoncodes', $cdnotereasoncodes);
        
        $datasource = new datasources($this->db);
        $datasources = $datasource->all();
        $this->f3->set('datasources', $datasources);
        
        $industry = new industries($this->db);
        $industries = $industry->all();
        $this->f3->set('industries', $industries);
        
        $currency = new currencies($this->db);
        $currencies = $currency->all();
        $this->f3->set('currencies', $currencies);
        
        $mode = new modes($this->db);
        $modes = $mode->all();
        $this->f3->set('modes', $modes);
        
        $buyertype = new buyertypes($this->db);
        $buyertypes = $buyertype->all();
        $this->f3->set('buyertypes', $buyertypes);
        
        $taxrate = new taxrates($this->db);
        $taxrates = $taxrate->all();
        $this->f3->set('taxrates', $taxrates);
        
        $org = new organisations($this->db);
        $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
        $this->f3->set('seller', $org);
        
        $buyer = new buyers($this->db);
        $buyer->getByID($creditnote->buyerid);
        $this->f3->set('buyer', $buyer);
        
        $product = new products($this->db);
        $products = $product->all();
        $this->f3->set('products', $products);
        
        $flag = new flags($this->db);
        $flags = $flag->all();
        $this->f3->set('flags', $flags);
        
        $paymentmode = new paymentmodes($this->db);
        $paymentmodes = $paymentmode->all();
        $this->f3->set('paymentmodes', $paymentmodes);
        
        $deliveryterm = new deliveryterms($this->db);
        $deliveryterms = $deliveryterm->all();
        $this->f3->set('deliveryterms', $deliveryterms);
        
        $this->f3->set('creditnote', $creditnote);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);
        
        $this->f3->set('systemalert', self::$systemalert);
        
        $this->f3->set('pagetitle', 'Edit Credit Note | ' . $id);
        $this->f3->set('pagecontent', 'EditCreditnote.htm');
        $this->f3->set('pagescripts', 'EditCreditnoteFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    
    /**
     *	@name list
     *  @desc List creditnotes
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function list(){
        $operation = NULL; //tblevents
        $permission = 'VIEWCREDITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Creditnote Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Creditnote Controller : list() : Processing list of creditnotes started", 'r');
            $creditnoteid = trim((string)$this->f3->get('REQUEST.creditnoteid'));

            $isDataTablesRequest = ($this->f3->get('REQUEST.draw') !== NULL && $this->f3->get('REQUEST.start') !== NULL && $this->f3->get('REQUEST.length') !== NULL);

            if ($isDataTablesRequest) {
                $draw = (int)$this->f3->get('REQUEST.draw');
                $start = max(0, (int)$this->f3->get('REQUEST.start'));
                $length = (int)$this->f3->get('REQUEST.length');
                $length = ($length > 0 && $length <= 200)? $length : 10;

                $searchValue = trim((string)$this->f3->get('REQUEST.search.value'));
                $orderColumnIndex = (int)$this->f3->get('REQUEST.order.0.column');
                $orderDir = strtolower(trim((string)$this->f3->get('REQUEST.order.0.dir'))) === 'asc'? 'ASC' : 'DESC';

                $columnMap = array(
                    0 => 'i.id',
                    1 => 'i.oriinvoiceid',
                    2 => 'i.oriinvoiceno',
                    3 => 'i.currency',
                    4 => 'i.refundinvoiceno',
                    5 => 'i.referenceno',
                    6 => 'i.creditnoteapplicationid',
                    7 => 'i.applicationtime',
                    8 => 'i.grossamount',
                    9 => 'cns.name',
                    10 => 'i.modifieddt'
                );

                $orderBy = array_key_exists($orderColumnIndex, $columnMap)? $columnMap[$orderColumnIndex] : 'i.id';

                $where = '';
                if ($searchValue !== '') {
                    $searchEscaped = addslashes($searchValue);
                    $where = " WHERE (i.oriinvoiceno LIKE '%" . $searchEscaped . "%'"
                        . " OR i.refundinvoiceno LIKE '%" . $searchEscaped . "%'"
                        . " OR i.referenceno LIKE '%" . $searchEscaped . "%'"
                        . " OR i.creditnoteapplicationid LIKE '%" . $searchEscaped . "%'"
                        . " OR i.currency LIKE '%" . $searchEscaped . "%')";
                }

                $countTotalSql = 'SELECT COUNT(*) "c" FROM tblcreditnotes i';
                $countFilteredSql = 'SELECT COUNT(*) "c" FROM tblcreditnotes i' . $where;

                $sql = 'SELECT  i.id "ID",
                        i.oriinvoiceid "Original Invoice Id",
                        i.oriinvoiceno "Original Invoice No",
                        i.issueddate "Issued Date",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.einvoicedatamatrixcode "QR Code",
                        i.refundinvoiceno "Refund Inv No",
                        i.referenceno "Reference No",
                        i.creditnoteapplicationid "Appl Id",
                        i.applicationtime "Appl Time",
                        i.approvestatus "Approve Status Code",
                        cns.name "Approve Status Name",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblcreditnotes i
                    LEFT JOIN tblusers s ON i.modifiedby = s.id
                    LEFT JOIN tblcdnoteapprovestatuses cns ON cns.code = i.approvestatus'
                    . $where
                    . ' ORDER By ' . $orderBy . ' ' . $orderDir
                    . ' LIMIT ' . $start . ', ' . $length;

                try {
                    $countTotalRow = $this->db->exec($countTotalSql);
                    $countFilteredRow = $this->db->exec($countFilteredSql);
                    $dtls = $this->db->exec($sql);

                    $recordsTotal = isset($countTotalRow[0]['c'])? (int)$countTotalRow[0]['c'] : 0;
                    $recordsFiltered = isset($countFilteredRow[0]['c'])? (int)$countFilteredRow[0]['c'] : 0;

                    $this->logger->write('Creditnote Controller : list() : DataTables mode - start=' . $start . ', length=' . $length . ', filtered=' . $recordsFiltered, 'r');

                    die(json_encode(array(
                        'draw' => $draw,
                        'recordsTotal' => $recordsTotal,
                        'recordsFiltered' => $recordsFiltered,
                        'data' => $dtls
                    )));
                } catch (Exception $e) {
                    $this->logger->write("Creditnote Controller : list() : The operation to list paged creditnotes was not successful. The error message is " . $e->getMessage(), 'r');
                    die(json_encode(array(
                        'draw' => $draw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => array()
                    )));
                }
            }
            
            if ($creditnoteid !== '' || !empty($creditnoteid)) {
                $sql = 'SELECT  i.id "ID",
                        i.oriinvoiceid "Original Invoice Id",
                        i.oriinvoiceno "Original Invoice No",
                        i.issueddate "Issued Date",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.einvoicedatamatrixcode "QR Code",
                        i.refundinvoiceno "Refund Inv No",
                        i.referenceno "Reference No",
                        i.creditnoteapplicationid "Appl Id",
                        i.applicationtime "Appl Time",
                        i.approvestatus "Approve Status Code",
                        cns.name "Approve Status Name",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblcreditnotes i
                    LEFT JOIN tblusers s ON i.modifiedby = s.id
                    LEFT JOIN tblcdnoteapprovestatuses cns ON cns.code = i.approvestatus
                    WHERE i.id = ' . $creditnoteid . '
                    ORDER By i.id DESC';
            } else {
                $sql = 'SELECT  i.id "ID",
                        i.oriinvoiceid "Original Invoice Id",
                        i.oriinvoiceno "Original Invoice No",
                        i.issueddate "Issued Date",
                        i.currency "Currency",
                        FORMAT(i.netamount, 2) "Net Amount",
                        FORMAT(i.taxamount, 2) "Tax Amount",
                        FORMAT(i.grossamount, 2) "Gross Amount",
                        FORMAT(i.itemcount, 0) "Item Count",
                        i.einvoicedatamatrixcode "QR Code",
                        i.refundinvoiceno "Refund Inv No",
                        i.referenceno "Reference No",
                        i.creditnoteapplicationid "Appl Id",
                        i.applicationtime "Appl Time",
                        i.approvestatus "Approve Status Code",
                        cns.name "Approve Status Name",
                        i.disabled "Disabled",
                        i.inserteddt "Creation Date",
                        i.insertedby "Created By",
                        i.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblcreditnotes i
                    LEFT JOIN tblusers s ON i.modifiedby = s.id
                    LEFT JOIN tblcdnoteapprovestatuses cns ON cns.code = i.approvestatus
                    ORDER By i.id DESC';
            }

            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Creditnote Controller : list() : The operation to list the creditnotes was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Creditnote Controller : index() : The user is not allowed to perform this function", 'r');
        }
                     
        die(json_encode($data));
    }

    /**
     *	@name searchcdnotereasoncodes
     *  @desc List credit note reason codes for Select2 search
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function searchcdnotereasoncodes(){
        $permission = 'VIEWCREDITNOTES';
        $data = array();

        $this->logger->write("Creditnote Controller : searchcdnotereasoncodes() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $name = trim($this->f3->get('POST.name'));

            if ($name !== '' || ! empty($name)) {
                $subquery = " '%" . $name . "%' ";

                $sql = 'SELECT r.id "Id",
                        r.code "Code",
                        r.name "Name",
                        r.description "Description",
                        r.disabled "Disabled"
                    FROM tblcdnotereasoncodes r
                    WHERE r.name LIKE ' . $subquery . ' OR r.code LIKE ' . $subquery . '
                    ORDER BY r.id DESC';
            } else {
                $sql = 'SELECT r.id "Id",
                        r.code "Code",
                        r.name "Name",
                        r.description "Description",
                        r.disabled "Disabled"
                    FROM tblcdnotereasoncodes r
                    ORDER BY r.id DESC';
            }

            try {
                $dtls = $this->db->exec($sql);
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Creditnote Controller : searchcdnotereasoncodes() : The operation was not successful. The error message is " . $e->getMessage(), 'r');
            }
        }

        die(json_encode($data));
    }

    function previewcreditnoteexcise(){
        $permission = 'VIEWCREDITNOTES';
        $data = array('ok' => false);

        if (!$this->userpermissions[$permission]) {
            $data['message'] = 'Forbidden';
            die(json_encode($data));
        }

        $itemCode = trim((string)$this->f3->get('POST.itemcode'));
        $qty = $this->toFloat($this->f3->get('POST.qty'));
        $unitPrice = $this->toFloat($this->f3->get('POST.unitprice'));

        if ($itemCode === '') {
            $data['message'] = 'Missing item code';
            die(json_encode($data));
        }

        $product = new products($this->db);
        $product->getByCode($itemCode);
        if ($product->dry()) {
            $data['message'] = 'Product not found';
            die(json_encode($data));
        }

        $derivedExcise = $this->deriveExciseFromRules($product, $qty, $unitPrice);
        $data = array_merge(array('ok' => true), $derivedExcise);
        die(json_encode($data));
    }
    

    /**
     *	@name syncefriscreditnotes
     *  @desc sync credit notes from EFRIS
     *	@return
     *	@param
     **/
    function syncefriscreditnotes(){
        $operation = NULL; //tblevents
        $permission = 'SYNCCREDITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The operation type is " . $this->f3->get('POST.operationType'), 'r');
        
        
        
        if (trim($this->f3->get('POST.operationType')) !== '' || !empty(trim($this->f3->get('POST.operationType')))) {
            $operationType = $this->f3->get('POST.operationType');
        } else {
            $operationType = 'sync';
        }
                
        if (trim($this->f3->get('POST.startdate')) !== '' || !empty(trim($this->f3->get('POST.startdate')))) {
            $startdate = $this->f3->get('POST.startdate');
            $startdate = date("Y-m-d", strtotime($startdate));
        } else {
            $startdate = date('Y-m-d');
        }
        
        if (trim($this->f3->get('POST.enddate')) !== '' || !empty(trim($this->f3->get('POST.enddate')))) {
            $enddate = $this->f3->get('POST.enddate');
            $enddate = date("Y-m-d", strtotime($enddate));
        } else {
            $enddate = date('Y-m-d');
        }
        
        
        
        $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            if ($operationType == 'update') {
                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Update Operation", 'r');
                
                $cn_check = $this->db->exec(array('SELECT id FROM tblcreditnotes WHERE DATE(applicationtime) BETWEEN \'' . $startdate . '\' AND \'' . $enddate . '\''));
                $this->logger->write($this->db->log(TRUE), 'r');
                
                
                if($cn_check){
                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Creditnotes retrieved", 'r');
                    $creditnote = new creditnotes($this->db);
                    $id = NULL;
                    
                    
                    foreach ($cn_check as $obj) {
                        $id = $obj['id'];
                        $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Creditnote Id: " . $id, 'r');
                        $creditnote->getByID($id);
                        $this->logger->write($this->db->log(TRUE), 'r');
                        $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Creditnote RefNo: " . $creditnote->referenceno, 'r');
                        
                        $data = $this->util->downloadcreditnote($this->f3->get('SESSION.id'), $id);//will return JSON.
                        //var_dump($data);
                        
                        $data = json_decode($data, true);
                        //$this->logger->write("Creditnote Controller : downloadcreditnote() : The response content is: " . $data, 'r');
                        //var_dump($data);
                        
                        if (isset($data['returnCode'])){
                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The operation to download the credit note not successful. The error message is " . $data['returnMessage'], 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                        } elseif (isset($data['records'])){
                            
                            if ($data['records']) {
                                
                                foreach($data['records'] as $elem){
                                    
                                    
                                    $refundInvoiceNo = $elem['invoiceNo'];
                                    $approveStatusCode = $elem['approveStatus'];
                                    $applicationTime = $elem['applicationTime']; //28/09/2020 00:43:29
                                    $referenceNo = $elem['referenceNo']; //21PL010073993
                                    
                                    $applicationTime = str_replace('/', '-', $applicationTime);//Replace / with -
                                    $applicationTime = date("Y-m-d H:i:s", strtotime($applicationTime));
                                    
                                    $grossAmount = $elem['grossAmount'];
                                    $totalAmount = $elem['totalAmount'];
                                    $appId = $elem['id'];
                                    
                                    try{
                                        
                                        if ($referenceNo == $creditnote->referenceno) {
                                            
                                            $this->db->exec(array('UPDATE tblcreditnotes SET refundinvoiceno = "' . $refundInvoiceNo .
                                                '", approvestatus = "' . $approveStatusCode .
                                                '", grossamount = "' . $grossAmount .
                                                '", totalamount = "' . $totalAmount .
                                                '", creditnoteapplicationid = "' . $appId .
                                                '", applicationtime = "' . $applicationTime .
                                                '", referenceno = "' . $referenceNo .
                                                '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                ' WHERE referenceno = "' . $referenceNo . '"'));
                                            
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                            
                                            $creditnote->getByID($id);
                                            
                                            /**
                                             * If the invoice has been issued with a refund invoice number, then retrive it.
                                             */
                                            if (trim($refundInvoiceNo) !== '' || !empty(trim($refundInvoiceNo))) {
                                                $data_i = $this->util->downloadrefundinvoice($this->f3->get('SESSION.id'), $refundInvoiceNo);//will return JSON.
                                                
                                                $data_i = json_decode($data_i, true);
                                                
                                                if (isset($data_i['returnCode'])){
                                                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The operation to download the refund invoice not successful. The error message is " . $data_i['returnMessage'], 'r');
                                                } else {
                                                    if (isset($data_i['basicInformation'])){
                                                        
                                                        $antifakeCode = $data_i['basicInformation']['antifakeCode']; //32966911991799104051
                                                        $invoiceId = $data_i['basicInformation']['invoiceId']; //3257429764295992735
                                                        $invoiceNo = $data_i['basicInformation']['invoiceNo']; //3120012276043
                                                        
                                                        $issuedDate = $data_i['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                                                        $issuedDate = str_replace('/', '-', $issuedDate);//Replace / with -
                                                        $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                                                        
                                                        $issuedTime = $data_i['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                                                        $issuedTime = str_replace('/', '-', $issuedTime);//Replace / with -
                                                        $issuedTime = date("Y-m-d H:i:s", strtotime($issuedTime));
                                                        
                                                        $issuedDatePdf = $data_i['basicInformation']['issuedDatePdf']; //318/09/2020 17:14:12
                                                        $issuedDatePdf = str_replace('/', '-', $issuedDatePdf);//Replace / with -
                                                        $issuedDatePdf = date("Y-m-d H:i:s", strtotime($issuedDatePdf));
                                                        
                                                        $oriInvoiceId = $data_i['basicInformation']['oriInvoiceId'];//1
                                                        $isInvalid = $data_i['basicInformation']['isInvalid'];//1
                                                        $isRefund = $data_i['basicInformation']['isRefund'];//1
                                                        
                                                        $deviceNo = $data_i['basicInformation']['deviceNo'];
                                                        $invoiceIndustryCode = $data_i['basicInformation']['invoiceIndustryCode'];
                                                        $invoiceKind = $data_i['basicInformation']['invoiceKind'];
                                                        $invoiceType = $data_i['basicInformation']['invoiceType'];
                                                        $isBatch = $data_i['basicInformation']['isBatch'];
                                                        $operator = $data_i['basicInformation']['operator'];
                                                        
                                                        $currencyRate = $data_i['basicInformation']['currencyRate'];
                                                        
                                                        
                                                        
                                                        try{
                                                            $this->db->exec(array('UPDATE tblcreditnotes SET antifakeCode = "' . $antifakeCode .
                                                                '", einvoiceid = "' . $invoiceId .
                                                                '", einvoicenumber = "' . $invoiceNo .
                                                                '", issueddate = "' . $issuedDate .
                                                                '", issueddatepdf = "' . $issuedDatePdf .
                                                                '", oriinvoiceid = "' . $oriInvoiceId .
                                                                '", isinvalid = ' . $isInvalid .
                                                                ', isrefund = ' . $isRefund .
                                                                ', issuedtime = "' . $issuedTime .
                                                                '", deviceno = "' . $deviceNo .
                                                                '", invoiceindustrycode = ' . $invoiceIndustryCode .
                                                                ', invoicekind = ' . $invoiceKind .
                                                                ', invoicetype = ' . $invoiceType .
                                                                ', isbatch = "' . $isBatch .
                                                                '", operator = "' . $operator .
                                                                '", currencyRate = ' . $currencyRate .
                                                                ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                ' WHERE referenceno = "' . $referenceNo . '"'));
                                                            
                                                            $this->logger->write($this->db->log(TRUE), 'r');
                                                        } catch (Exception $e) {
                                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                                        }
                                                        
                                                    }
                                                    
                                                    if (isset($data_i['sellerDetails'])){
                                                        /*"address":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                                                         "branchCode":"00",
                                                         "branchId":"912550336846912433",
                                                         "branchName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                                         "businessName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                                         "emailAddress":"editesti06@gmail.com",
                                                         "legalName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                                         "linePhone":"256787695360",
                                                         "mobilePhone":"256782356088",
                                                         "ninBrn":"/80020002851201",
                                                         "placeOfBusiness":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                                                         "referenceNo":"21",
                                                         "tin":"1017918269"*/
                                                        
                                                        $branchCode = $data_i['sellerDetails']['branchCode'];
                                                        $branchId = $data_i['sellerDetails']['branchId'];
                                                        $sellerReferenceNo = $data_i['sellerDetails']['referenceNo'];
                                                        
                                                        $creditnote->getByID($id);
                                                        
                                                        try{
                                                            $this->db->exec(array('UPDATE tblcreditnotes SET branchCode = "' . $branchCode .
                                                                '", branchId = "' . $branchId .
                                                                '", erpcreditnoteid = "' . addslashes($sellerReferenceNo) .
                                                                '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                ' WHERE referenceno = "' . $referenceNo . '"'));
                                                            
                                                            $this->logger->write($this->db->log(TRUE), 'r');
                                                        } catch (Exception $e) {
                                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                                        }
                                                    }
                                                    
                                                    if (isset($data_i['extend'])){
                                                        
                                                        
                                                        $reason = $data_i['extend']['reason'];
                                                        $reasonCode = $data_i['extend']['reasonCode'];
                                                        
                                                        
                                                        
                                                        try{
                                                            $this->db->exec(array('UPDATE tblcreditnotes SET reason = "' . addslashes($reason) .
                                                                '", reasoncode = "' . $reasonCode .
                                                                '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                ' WHERE referenceno = "' . $referenceNo . '"'));
                                                            
                                                            $this->logger->write($this->db->log(TRUE), 'r');
                                                        } catch (Exception $e) {
                                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                                        }
                                                    }
                                                    
                                                    if (isset($data_i['summary'])){
                                                        $grossAmount = $data_i['summary']['grossAmount']; //832000
                                                        $itemCount = $data_i['summary']['itemCount']; //1
                                                        $netAmount = $data_i['summary']['netAmount']; //705084.75
                                                        $qrCode = $data_i['summary']['qrCode']; //020000001149IC1200122760430004F588000000C1A8450A20A021D534A1000121462A1000094968~MM INTERGRATED STEEL MILLS (UGANDA) LIMITED~Ediomu & Company~Kiboko Galv Corr. Sheet 36G
                                                        $taxAmount = $data_i['summary']['taxAmount'];//126915.25
                                                        $modeCode = $data_i['summary']['modeCode'];//0
                                                        
                                                        $mode = new modes($this->db);
                                                        $mode->getByCode($modeCode);
                                                        $modeName = $mode->name;//online
                                                        
                                                        $f = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
                                                        $grossAmountWords = $f->format($grossAmount);//two million
                                                        
                                                        try{
                                                            $this->db->exec(array('UPDATE tblcreditnotes SET grossamount = ' . $grossAmount .
                                                                ', itemcount = ' . $itemCount .
                                                                ', netamount = ' . $netAmount .
                                                                ', einvoicedatamatrixcode = "' . addslashes($qrCode) .
                                                                '", taxamount = ' . $taxAmount .
                                                                ', modecode = "' . $modeCode .
                                                                '", modename = "' . $modeName .
                                                                '", grossamountword = "' . addslashes($grossAmountWords) .
                                                                '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                ' WHERE referenceno = "' . $referenceNo . '"'));
                                                            
                                                            $this->logger->write($this->db->log(TRUE), 'r');
                                                        } catch (Exception $e) {
                                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                                        }
                                                    }
                                                    
                                                    if (isset($data_i['goodsDetails'])){
                                                        /*"deemedFlag":"2",
                                                         "discountFlag":"2",
                                                         "exciseFlag":"2",
                                                         "exciseTax":"0",
                                                         "goodsCategoryId":"72152507",
                                                         "goodsCategoryName":"Vinyl floor tile and sheet installation service",
                                                         "item":"Iron Sheets",
                                                         "itemCode":"Iron2021",
                                                         "orderNumber":"0",
                                                         "qty":"1",
                                                         "tax":"1525.42",
                                                         "taxRate":"0.18",
                                                         "total":"10000",
                                                         "unitOfMeasure":"MTR",
                                                         "unitPrice":"10000"*/
                                                        
                                                        if ($data_i['goodsDetails']) {
                                                            
                                                            try{
                                                                $this->db->exec(array('DELETE FROM tblgooddetails WHERE groupid = ' . $creditnote->gooddetailgroupid));
                                                            } catch (Exception $e) {
                                                                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The operation to delete from table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                            }
                                                            
                                                            foreach($data_i['goodsDetails'] as $elem){
                                                                
                                                                try{
                                                                    
                                                                    
                                                                    $deemedFlag = empty($elem['deemedFlag'])? 'NULL' : $elem['deemedFlag'];
                                                                    $discountFlag = empty($elem['discountFlag'])? 'NULL' : $elem['discountFlag'];
                                                                    $exciseFlag = empty($elem['exciseFlag'])? 'NULL' : $elem['exciseFlag'];
                                                                    $exciseTax = empty($elem['exciseTax'])? 'NULL' : $elem['exciseTax'];
                                                                    $goodsCategoryId = empty($elem['goodsCategoryId'])? '' : $elem['goodsCategoryId'];
                                                                    $goodsCategoryName = empty($elem['goodsCategoryName'])? '' : $elem['goodsCategoryName'];
                                                                    $item = empty($elem['item'])? '' : $elem['item'];
                                                                    $itemCode = empty($elem['itemCode'])? '' : $elem['itemCode'];
                                                                    $orderNumber = empty($elem['orderNumber'])? 'NULL' : $elem['orderNumber'];
                                                                    $qty = empty($elem['qty'])? 'NULL' : $elem['qty'];
                                                                    $tax = empty($elem['tax'])? 'NULL' : $elem['tax'];
                                                                    $taxRate = empty($elem['taxRate'])? 'NULL' : $elem['taxRate'];
                                                                    $total = empty($elem['total'])? 'NULL' : $elem['total'];
                                                                    $unitOfMeasure = empty($elem['unitOfMeasure'])? '' : $elem['unitOfMeasure'];
                                                                    $unitPrice = empty($elem['unitPrice'])? 'NULL' : $elem['unitPrice'];
                                                                    
                                                                    $measureunit = new measureunits($this->db);
                                                                    $measureunit->getByCode($unitOfMeasure);
                                                                    
                                                                    $unitofmeasurename = $measureunit->name;
                                                                    
                                                                    //Tax ID
                                                                    //Tax Category
                                                                    
                                                                    $this->db->exec(array('INSERT INTO tblgooddetails
                                                                                                    (groupid,
                                                                                                    deemedflag,
                                                                                                    discountflag,
                                                                                                    exciseflag,
                                                                                                    excisetax,
                                                                                                    goodscategoryid,
                                                                                                    goodscategoryname,
                                                                                                    item,
                                                                                                    itemcode,
                                                                                                    ordernumber,
                                                                                                    qty,
                                                                                                    tax,
                                                                                                    taxrate,
                                                                                                    total,
                                                                                                    unitofmeasure,
                                                                                                    unitprice,
                                                                                                    unitofmeasurename,
                                                                                                    inserteddt,
                                                                                                    insertedby,
                                                                                                    modifieddt,
                                                                                                    modifiedby)
                                                                                                    VALUES
                                                                                                    (' . $creditnote->gooddetailgroupid . ',
                                                                                                    ' . $deemedFlag . ',
                                                                                                    ' . $discountFlag . ',
                                                                                                    ' . $exciseFlag . ',
                                                                                                    ' . $exciseTax . ',
                                                                                                    "' . addslashes($goodsCategoryId) . '",
                                                                                                    "' . addslashes($goodsCategoryName) . '",
                                                                                                    "' . addslashes($item) . '",
                                                                                                    "' . addslashes($itemCode) . '",
                                                                                                    ' . $orderNumber . ',
                                                                                                    ' . $qty . ',
                                                                                                    ' . $tax . ',
                                                                                                    ' . $taxRate . ',
                                                                                                    ' . $total . ',
                                                                                                    "' . addslashes($unitOfMeasure) . '",
                                                                                                    ' . $unitPrice . ',
                                                                                                    "' . addslashes($unitofmeasurename) . '", NOW(),
                                                                                                    ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                                                    ' . $this->f3->get('SESSION.id') . ')'));
                                                                    
                                                                    $this->logger->write($this->db->log(TRUE), 'r');
                                                                } catch (Exception $e) {
                                                                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The operation to insert into table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                                }
                                                                
                                                            }
                                                            
                                                        } else {//NOTHING RETURNED BY API
                                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The API did not return anything", 'r');
                                                        }
                                                    }
                                                    
                                                    if (isset($data_i['buyerDetails'])){
                                                        
                                                        /*
                                                         "buyerDetails":{
                                                         "dateFormat":"dd/MM/yyyy",
                                                         "nowTime":"2021/06/10 15:26:37",
                                                         "pageIndex":0,
                                                         "pageNo":0,
                                                         "pageSize":0,
                                                         "timeFormat":"dd/MM/yyyy HH24:mi:ss"
                                                         }*/
                                                        
                                                        /*$buyerAddress = $data_i['buyerDetails']['buyerAddress'];
                                                         $buyerBusinessName = $data_i['buyerDetails']['buyerBusinessName'];
                                                         $buyerEmail = $data_i['buyerDetails']['buyerEmail'];
                                                         $buyerLegalName = $data_i['buyerDetails']['buyerLegalName'];
                                                         $buyerMobilePhone = $data_i['buyerDetails']['buyerMobilePhone'];
                                                         $buyerTin = $data_i['buyerDetails']['buyerTin'];
                                                         $buyerType = $data_i['buyerDetails']['buyerType'];*/
                                                        
                                                        $creditnote->getByID($id);
                                                        
                                                        if (!empty(trim($creditnote->buyerid))) {
                                                            //if (trim($creditnote->buyerid) !== '0' || trim($creditnote->buyerid) !== '' || !empty(trim($creditnote->buyerid))) {
                                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The buyer is already set", 'r');
                                                            
                                                            
                                                        } else {
                                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The buyer is NOT set", 'r');
                                                            
                                                            
                                                        }
                                                    }
                                                    
                                                    
                                                    
                                                    if (isset($data_i['payWay'])){
                                                        /*"dateFormat":"dd/MM/yyyy",
                                                         "nowTime":"2021/05/23 14:14:18",
                                                         "orderNumber":"0",
                                                         "paymentAmount":"45000",
                                                         "paymentMode":"102",
                                                         "timeFormat":"dd/MM/yyyy HH24:mi:ss"*/
                                                        
                                                        if ($data_i['payWay']) {
                                                            try{
                                                                $this->db->exec(array('DELETE FROM tblpaymentdetails WHERE groupid = ' . $creditnote->paymentdetailgroupid));
                                                            } catch (Exception $e) {
                                                                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The operation to delete from table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                            }
                                                            
                                                            foreach($data_i['payWay'] as $elem){
                                                                
                                                                try{
                                                                    
                                                                    
                                                                    $orderNumber = $elem['orderNumber'];
                                                                    $paymentAmount = $elem['paymentAmount'];
                                                                    $paymentMode = $elem['paymentMode'];
                                                                    
                                                                    $pm = new paymentmodes($this->db);
                                                                    $pm->getByCode($paymentMode);
                                                                    
                                                                    $paymentmodename = $pm->name;
                                                                    
                                                                    $this->db->exec(array('INSERT INTO tblpaymentdetails
                                                                (groupid,
                                                                ordernumber,
                                                                paymentamount,
                                                                paymentmode,
                                                                paymentmodename,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $creditnote->paymentdetailgroupid . ',
                                                                ' . $orderNumber . ',
                                                                ' . $paymentAmount . ',
                                                                ' . $paymentMode . ',
                                                                "' . addslashes($paymentmodename) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                                                } catch (Exception $e) {
                                                                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The operation to insert into table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                                }
                                                                
                                                            }
                                                            
                                                        } else {//NOTHING RETURNED BY API
                                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The API did not return anything", 'r');
                                                        }
                                                    }
                                                    
                                                    if (isset($data_i['taxDetails'])){
                                                        /*"taxCategory":"Standard",
                                                         "netAmount":"123389830.51",
                                                         "taxRate":"0.18",
                                                         "taxAmount":"22210169.49",
                                                         "grossAmount":"145600000.00",
                                                         "exciseUnit":"",
                                                         "exciseCurrency":"",
                                                         "taxRateName":"18%"*/
                                                        
                                                        if ($data_i['taxDetails']) {
                                                            try{
                                                                $this->db->exec(array('DELETE FROM tbltaxdetails WHERE groupid = ' . $creditnote->taxdetailgroupid));
                                                            } catch (Exception $e) {
                                                                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The operation to delete from table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                            }
                                                            
                                                            foreach($data_i['taxDetails'] as $elem){
                                                                
                                                                try{
                                                                    
                                                                    
                                                                    $taxCategory = $elem['taxCategory'];
                                                                    $netAmount = $elem['netAmount'];
                                                                    $taxRate = $elem['taxRate'];
                                                                    $taxAmount = $elem['taxAmount'];
                                                                    $grossAmount = $elem['grossAmount'];
                                                                    $exciseUnit = $elem['exciseUnit'];
                                                                    $exciseCurrency = $elem['exciseCurrency'];
                                                                    $taxRateName = $elem['taxRateName'];
                                                                    
                                                                    $tr = new taxrates($this->db);
                                                                    $tr->getByName($taxRateName);
                                                                    
                                                                    $taxdescription = $tr->description;//A: Standard (18%)
                                                                    
                                                                    $this->db->exec(array('INSERT INTO tbltaxdetails
                                                                (groupid,
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
                                                                VALUES
                                                                (' . $creditnote->taxdetailgroupid . ', 0,
                                                                "' . addslashes($taxCategory) . '",
                                                                ' . $netAmount . ',
                                                                ' . $taxRate . ',
                                                                ' . $taxAmount . ',
                                                                ' . $grossAmount . ',
                                                                "' . addslashes($exciseUnit) . '",
                                                                "' . addslashes($exciseCurrency) . '",
                                                                "' . addslashes($taxRateName) . '",
                                                                "' . addslashes($taxdescription) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                                                } catch (Exception $e) {
                                                                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The operation to insert into table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                                }
                                                                
                                                            }
                                                            
                                                        } else {//NOTHING RETURNED BY API
                                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The API did not return anything", 'r');
                                                        }
                                                    }
                                                    
                                                }
                                            }
                                        } else {
                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The reference number does not match", 'r');
                                        }
                                        
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update the credit notes by " . $this->f3->get('SESSION.username') . " was successful");
                                        self::$systemalert = "The operation to update the credit notes by " . $this->f3->get('SESSION.username') . " was successful";
                                    } catch (Exception $e) {
                                        $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                    }
                                    
                                }
                            } else {
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the credit note first");
                            }
                            
                            
                        } else {
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful");
                        }
                    }/*FOREACH*/
                    
                } else {
                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : No creditnotes were retrieved", 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update creditnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                    self::$systemalert = "The operation to update creditnotes by " . $this->f3->get('SESSION.username') . " was not successful. No creditnotes were retrieved.";
                    
                }
                
            } else {
                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Sync Operation", 'r');
                
                $pageNo = 1;
                $pageSize = 90;
                $pageCount = 1;
                
                do {
                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : pageCount = " . $pageCount, 'r');
                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : pageNo = " . $pageNo, 'r');
                    
                    $data = $this->util->syncefriscreditnotes($this->f3->get('SESSION.id'), $startdate, $enddate, $pageNo, $pageSize);//will return JSON.
                    
                    $data = json_decode($data, true);
                    
                    if(isset($data['page'])){
                        $pageCount = $data['page']['pageCount'];
                        
                        $pageNo = $pageNo + 1;
                    }
                    
                    
                    if (isset($data['returnCode'])){
                        $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The operation to sync creditnotes not successful. The error message is " . $data['returnMessage'], 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync creditnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                        self::$systemalert = "The operation to sync creditnotes by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                    } else {
                        
                        
                        if ($data) {
                            
                            
                            if(isset($data['records'])){
                                $creditnote = new creditnotes($this->db);
                                
                                foreach($data['records'] as $elem){
                                    /*"applicationTime":"14/04/2021 15:07:49",
                                     "approveStatus":"101",
                                     "businessName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                     "buyerBusinessName":"Mr. FRANCIS LUBANGA",
                                     "buyerLegalName":"Mr. FRANCIS LUBANGA",
                                     "buyerTin":"1000562249",
                                     "createDate":1618402069000,
                                     "currency":"UGX",
                                     "dataSource":"106",
                                     "grossAmount":"-34000",
                                     "id":"135939256860140882",
                                     "invoiceApplyCategoryCode":"101",
                                     "invoiceNo":"320017556755",
                                     "legalName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                     "nin":"/80020002851201",
                                     "nowTime":"2021/05/25 21:26:24",
                                     "oriGrossAmount":"230000",
                                     "oriInvoiceNo":"320017554739",
                                     "pageIndex":0,
                                     "pageNo":0,
                                     "pageSize":0,
                                     "referenceNo":"21PL010384903",
                                     "source":"106",
                                     "tin":"1017918269",
                                     "totalAmount":"-34000",
                                     "waitingDate":"0"*/
                                    
                                    $buyerBusinessName = $elem['buyerBusinessName'];
                                    $buyerLegalName = $elem['buyerLegalName'];
                                    $buyerTin = $elem['buyerTin'];
                                    
                                    $applicationTime = $elem['applicationTime']; //14/04/2021 15:07:49",
                                    $applicationTime = str_replace('/', '-', $applicationTime);//Replace / with -
                                    $applicationTime = date("Y-m-d H:i:s", strtotime($applicationTime));
                                    
                                    
                                    $approveStatus = $elem['approveStatus']; //101",
                                    $invoiceApplyCategoryCode = $elem['invoiceApplyCategoryCode']; //101",
                                    $referenceNo = $elem['referenceNo']; //21PL010384903",
                                    $grossAmount = $elem['grossAmount']; //-34000",
                                    $oriGrossAmount = $elem['oriGrossAmount']; //230000",
                                    $totalAmount = $elem['totalAmount']; //-34000",
                                    
                                    $invoiceId = $elem['id']; //3257429764295992735
                                    $invoiceNo = $elem['invoiceNo']; //3120012276043
                                    
                                    $oriInvoiceId = $elem['oriInvoiceId']; //358494246814125369",
                                    $oriInvoiceNo = $elem['oriInvoiceNo']; //320017745845",
                                    
                                    $issuedDate = $elem['issuedDate']; //18/09/2020 17:14:12
                                    $issuedDate = str_replace('/', '-', $issuedDate);//Replace / with -
                                    $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                                    
                                    $issuedTime = $elem['issuedDate']; //18/09/2020 17:14:12
                                    $issuedTime = str_replace('/', '-', $issuedTime);//Replace / with -
                                    $issuedTime = date("Y-m-d H:i:s", strtotime($issuedTime));
                                    
                                    $currency = $elem['currency'];//1
                                    $dataSource = $elem['dataSource'];//1
                                    
                                    
                                    
                                    $creditnote->getByRefNo($referenceNo);
                                    $this->logger->write($this->db->log(TRUE), 'r');
                                    
                                    if ($creditnote->dry()) {
                                        $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The creditnote does not exist", 'r');
                                        
                                        try{
                                            
                                            /**
                                             * 1. Insert the details into the tblcreditnotes
                                             * 2. Retrive the record inserted.
                                             * 3. Generate the following details
                                             * 3.3. Good details group
                                             * 3.4. Payment details group
                                             * 3.5. Tax details group
                                             * 4. Update the invoice record with these details
                                             */
                                            
                                            $this->db->exec(array('INSERT INTO tblcreditnotes
                                                                (creditnoteapplicationid,
                                                                refundinvoiceno,
                                                                oriinvoiceid,
                                                                oriinvoiceno,
                                                                issueddate,
                                                                datasource,
                                                                currency,
                                                                applicationtime,
                                                                approvestatus,
                                                                invoiceapplycategorycode,
                                                                referenceno,
                                                                grossamount,
                                                                origrossamount,
                                                                totalamount,
                                                                sellerid,
                                                                issuedtime,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                ("' . $invoiceId . '",
                                                                "' . $invoiceNo . '",
                                                                "' . $oriInvoiceId . '",
                                                                "' . $oriInvoiceNo . '",
                                                                "' . $issuedDate . '",
                                                                ' . $dataSource . ',
                                                                "' . $currency . '",
                                                                "' . $applicationTime . '",
                                                                "' . $approveStatus . '",
                                                                ' . $invoiceApplyCategoryCode . ',
                                                                "' . $referenceNo . '",
                                                                ' . $grossAmount . ',
                                                                ' . $oriGrossAmount . ',
                                                                ' . $totalAmount . ',
                                                                ' . $this->appsettings['SELLER_RECORD_ID'] . ',
                                                                "' . $issuedTime . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                            
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                            
                                            //Retrieve the now inserted credit note
                                            $creditnote->getByRefNo($referenceNo);
                                            
                                            $id = $creditnote->id;
                                            
                                            /**
                                             * 1. Add a GROUPID for goods and store it in a field called gooddetailgroupid
                                             * 1. Add a GROUPID for payments and store it in a field called paymentdetailgroupid
                                             * 1. Add a GROUPID for tax details and store it in a field called taxdetailgroupid
                                             */
                                            
                                            try {
                                                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;
                                                
                                                $this->db->exec(array('INSERT INTO tblgooddetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                try {
                                                    $pg = array ();
                                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblgooddetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                                    
                                                    foreach ( $r as $obj ) {
                                                        $pg [] = $obj;
                                                    }
                                                    
                                                    $gooddetailgroupid = $pg[0]['id'];
                                                    $this->db->exec(array('UPDATE tblcreditnotes SET gooddetailgroupid = ' . $gooddetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to select from table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to insert into the table tblgooddetailgroups. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            try {
                                                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;
                                                
                                                $this->db->exec(array('INSERT INTO tblpaymentdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                try {
                                                    $pg = array ();
                                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tblpaymentdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                                    
                                                    foreach ( $r as $obj ) {
                                                        $pg [] = $obj;
                                                    }
                                                    
                                                    $paymentdetailgroupid = $pg[0]['id'];
                                                    $this->db->exec(array('UPDATE tblcreditnotes SET paymentdetailgroupid = ' . $paymentdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to select from table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to insert into the table tblpaymentdetailgroups. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            
                                            try {
                                                $paramgroupdescription = "This is an autogenerated group id for the creditnote id " . $id;
                                                
                                                $this->db->exec(array('INSERT INTO tbltaxdetailgroups (owner, entitytype, description, inserteddt, insertedby, modifieddt, modifiedby)
                                                                    VALUES(' . $id . ', ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ', "' . $paramgroupdescription . '", NOW(), ' . $this->f3->get('SESSION.id') . ', NOW(), ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                try {
                                                    $pg = array ();
                                                    $r = $this->db->exec(array('SELECT MAX(id) "id" FROM tbltaxdetailgroups WHERE owner = ' . $id . ' AND entitytype = ' . $this->appsettings['CREDITNOTEENTITYTYPE'] . ' AND insertedby = ' . $this->f3->get('SESSION.id')));
                                                    
                                                    foreach ( $r as $obj ) {
                                                        $pg [] = $obj;
                                                    }
                                                    
                                                    $taxdetailgroupid = $pg[0]['id'];
                                                    $this->db->exec(array('UPDATE tblcreditnotes SET taxdetailgroupid = ' . $taxdetailgroupid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to select from table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to insert into the table tbltaxdetailgroups. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                            $creditnote->getByRefNo($referenceNo);
                                            
                                            /**
                                             * INSERT BUYER DETAILS HERE
                                             */
                                            
                                            try{
                                                $this->db->exec(array('INSERT INTO tblbuyers
                                                                (businessname,
                                                                legalname,
                                                                referenceno,
                                                                tin,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                ("' . addslashes($buyerBusinessName) . '",
                                                                "' . addslashes($buyerLegalName) . '",
                                                                "' . addslashes($referenceNo) . '",
                                                                "' . $buyerTin . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                                
                                                
                                                
                                                try {
                                                    if (trim($referenceNo) !== '' || !empty(trim($referenceNo))) {
                                                        $b = array ();
                                                        $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE TRIM(referenceno) = "' . $referenceNo . '"'));
                                                        
                                                        foreach ( $r as $obj ) {
                                                            $b [] = $obj;
                                                        }
                                                        
                                                        $buyerid = $b[0]['id'];
                                                        
                                                        $this->db->exec(array('UPDATE tblcreditnotes SET buyerid = ' . $buyerid .
                                                            ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                            ' WHERE TRIM(referenceno) = "' . $referenceNo . '"'));
                                                    } else {
                                                        ;
                                                    }
                                                    
                                                } catch (Exception $e) {
                                                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to select from table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                                }
                                                
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to insert into the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                        } catch (Exception $e) {
                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to insert into the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                        
                                    } else {
                                        $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The creditnote exists", 'r');
                                        
                                        try{
                                            $this->db->exec(array('UPDATE tblcreditnotes SET creditnoteapplicationid = "' . $invoiceId .
                                                '", refundinvoiceno = "' . $invoiceNo .
                                                '", oriinvoiceid = "' . $oriInvoiceId .
                                                '", oriinvoiceno = "' . $oriInvoiceNo .
                                                '", issueddate = "' . $issuedDate .
                                                '", datasource = ' . $dataSource .
                                                ', currency = "' . $currency .
                                                '", issuedtime = "' . $issuedTime .
                                                '", applicationtime = "' . $applicationTime .
                                                '", approvestatus = "' . $approveStatus .
                                                '", invoiceapplycategorycode = ' . $invoiceApplyCategoryCode .
                                                ', referenceno = "' . $referenceNo .
                                                '", grossamount = ' . $grossAmount .
                                                ', origrossamount = ' . $oriGrossAmount .
                                                ', totalamount = ' . $totalAmount .
                                                ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                ' WHERE referenceno = "' . $referenceNo . '"'));
                                            
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                        } catch (Exception $e) {
                                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                        }
                                        
                                        $creditnote->getByRefNo($referenceNo);
                                        
                                        /**
                                         * INSERT/UPDATE BUYER DETAILS HERE
                                         */
                                        
                                        if (empty($creditnote->buyerid) || trim($creditnote->buyerid) == '0') {
                                            try{
                                                $this->db->exec(array('INSERT INTO tblbuyers
                                                                (businessname,
                                                                legalname,
                                                                referenceno,
                                                                tin,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                ("' . addslashes($buyerBusinessName) . '",
                                                                "' . addslashes($buyerLegalName) . '",
                                                                "' . addslashes($referenceNo) . '",
                                                                "' . $buyerTin . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                                
                                                
                                                
                                                try {
                                                    if (trim($referenceNo) !== '' || !empty(trim($referenceNo))) {
                                                        $b = array ();
                                                        $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE TRIM(referenceno) = "' . $referenceNo . '"'));
                                                        
                                                        foreach ( $r as $obj ) {
                                                            $b [] = $obj;
                                                        }
                                                        
                                                        $buyerid = $b[0]['id'];
                                                        
                                                        $this->db->exec(array('UPDATE tblcreditnotes SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE TRIM(referenceno) = "' . $referenceNo . '"'));
                                                    } else {
                                                        ;
                                                    }
                                                    
                                                } catch (Exception $e) {
                                                    $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to select from table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                                }
                                                
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to insert into the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                            }
                                        } else {
                                            
                                            try{
                                                $this->db->exec(array('UPDATE tblbuyers SET businessname = "' . addslashes($buyerBusinessName) .
                                                    '", legalname = "' . addslashes($buyerLegalName) .
                                                    '", referenceno = "' . addslashes($referenceNo) .
                                                    '", tin = "' . $buyerTin .
                                                    '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                    ' WHERE id = ' . $creditnote->buyerid));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : syncefriscreditnotes() : Failed to update the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                        }
                                        
                                        
                                    }
                                }
                            }
                            
                        } else {//NOTHING RETURNED BY API
                            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The API did not return anything", 'r');
                        }
                        
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync credit notes by " . $this->f3->get('SESSION.username') . " was successful");
                        self::$systemalert = "The operation to sync credit notes by " . $this->f3->get('SESSION.username') . " was successful";
                    }
                } while ($pageNo <= $pageCount);
            }

            //die($data);
        } else {
            $this->logger->write("Creditnote Controller : syncefriscreditnotes() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
    
    /**
     *	@name uploadcreditnote
     *  @desc upload a credit note to EFRIS
     *	@return
     *	@param 
     **/
    function uploadcreditnote(){
        $operation = NULL; //tblevents
        $permission = 'UPLOADCREDITNOTE'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.uploadcreditnoteid');
        $creditnote = new creditnotes($this->db);
        $creditnote->getByID($id);
        $this->logger->write("Creditnote Controller : uploadcreditnote() : The creditnote id is " . $this->f3->get('POST.uploadcreditnoteid'), 'r');
        
        $this->logger->write("Creditnote Controller : uploadcreditnote() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            if ($creditnote->referenceno) {
                $this->logger->write("Creditnote Controller : uploadcreditnote() : This credit note is already uploaded", 'r');
                self::$systemalert = "This credit note is already uploaded";
                $this->f3->set('systemalert', self::$systemalert);
                self::view($id);
            } else {
                $data = $this->util->uploadcreditnote($this->f3->get('SESSION.id'), $id, $this->vatRegistered);//will return JSON.
                //var_dump($data);
                
                $data = json_decode($data, true);
                //$this->logger->write("Creditnote Controller : uploadcreditnote() : The response content is: " . $data, 'r');
                //var_dump($data);
                
                
                if (isset($data['returnCode'])){
                    $this->logger->write("Creditnote Controller : uploadcreditnote() : The operation to upload the credit note was not successful. The error message is " . $data['returnMessage'], 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to upload the credit by " . $this->f3->get('SESSION.username') . " was not successful");
                    self::$systemalert = "The operation to upload the credit note by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                } else {
                    if (isset($data['referenceNo'])){
                        $referenceNo = $data['referenceNo']; //21PL010073993
                        
                        try{
                            $this->db->exec(array('UPDATE tblcreditnotes SET referenceno = "' . $referenceNo . '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE id = ' . $id));
                            $this->logger->write($this->db->log(TRUE), 'r');
                            
                            //Fetch details of the newly uploaded credit note
                            /*$n_data = $this->util->downloadcreditnote($this->f3->get('SESSION.id'), $id);//will return JSON.
                            //var_dump($data);
                            
                            $n_data = json_decode($n_data, true);
                            //var_dump($n_data);
                            
                            
                            if(isset($n_data['records'])){
                                
                                if ($n_data['records']) {
                                    ;
                                } else {
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the credit note first");
                                    //self::$systemalert = "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the credit note first";
                                }
                                
                                foreach($n_data['records'] as $elem){
                                    $refundInvoiceNo = $elem['invoiceNo'];
                                    $approveStatusCode = $elem['approveStatus'];
                                    $applicationTime = $elem['applicationTime']; //28/09/2020 00:43:29
                                    $referenceNo = $elem['referenceNo']; //21PL010073993
                                    
                                    $applicationTime = str_replace('/', '-', $applicationTime);//Replace / with -
                                    $applicationTime = date("Y-m-d H:i:s", strtotime($applicationTime));
                                    
                                    $grossAmount = $elem['grossAmount'];
                                    $totalAmount = $elem['totalAmount'];
                                    //$refundIssuedDate = $elem['refundIssuedDate'];
                                    $refundIssuedDate = $applicationTime;//28-09-2020 00:43:29
                                    $appId = $elem['id'];
                                    
                                    try{
                                        $this->db->exec(array('UPDATE tblcreditnotes SET refundinvoiceno = "' . $refundInvoiceNo . '", approvestatus = "' . $approveStatusCode . '", grossamount = "' . $grossAmount . '", totalamount = "' . $totalAmount . '", issueddate = "' . $refundIssuedDate . '", issuedtime = "' . $refundIssuedDate . '", creditnoteapplicationid = "' . $appId . '", applicationtime = "' . $applicationTime . '", referenceno = "' . $referenceNo . '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE referenceno = "' . $referenceNo . '"'));
                                        $this->logger->write($this->db->log(TRUE), 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was successful");
                                        //self::$systemalert = "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was successful";
                                    } catch (Exception $e) {
                                        $this->logger->write("Creditnote Controller : uploadcreditnote() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                        //self::$systemalert = "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful";
                                    }
                                    
                                }
                            } elseif (isset($n_data['returnCode'])){
                                $this->logger->write("Creditnote Controller : uploadcreditnote() : The operation to download the credit note not successful. The error message is " . $n_data['returnMessage'], 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful");
                                //self::$systemalert = "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $n_data['returnMessage'];
                            }*/
                            
                            
                        } catch (Exception $e) {
                            $this->logger->write("Creditnote Controller : uploadcreditnote() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                        }
                    }
                    
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to upload the creditnote by " . $this->f3->get('SESSION.username') . " was successful");
                    self::$systemalert = "The operation to upload the credit note by " . $this->f3->get('SESSION.username') . " was successful";
                }
            }
        } else {
            $this->logger->write("Creditnote Controller : uploadcreditnote() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name downloadcreditnote
     *  @desc download a credit note from EFRIS
     *	@return
     *	@param
     **/
    function downloadcreditnote(){
        $operation = NULL; //tblevents
        $permission = 'DOWNLOADCREDITNOTE'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.downloadcreditnoteid');
        $creditnote = new creditnotes($this->db);
        $creditnote->getByID($id);
        $this->logger->write("Creditnote Controller : downloadcreditnote() : The creditnote id is " . $this->f3->get('POST.downloadcreditnoteid'), 'r');
        
        $this->logger->write("Creditnote Controller : downloadcreditnote() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            $data = $this->util->downloadcreditnote($this->f3->get('SESSION.id'), $id);//will return JSON.
            //var_dump($data);
            
            $data = json_decode($data, true);
            //$this->logger->write("Creditnote Controller : downloadcreditnote() : The response content is: " . $data, 'r');
            //var_dump($data);
            
            if (isset($data['returnCode'])){
                $this->logger->write("Creditnote Controller : downloadcreditnote() : The operation to download the credit note not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
            } elseif (isset($data['records'])){
                
                if ($data['records']) {
                    
                    foreach($data['records'] as $elem){
                        
                        
                        $refundInvoiceNo = $elem['invoiceNo'];
                        $approveStatusCode = $elem['approveStatus'];
                        $applicationTime = $elem['applicationTime']; //28/09/2020 00:43:29
                        $referenceNo = $elem['referenceNo']; //21PL010073993
                        
                        $applicationTime = str_replace('/', '-', $applicationTime);//Replace / with -
                        $applicationTime = date("Y-m-d H:i:s", strtotime($applicationTime));
                        
                        $grossAmount = $elem['grossAmount'];
                        $totalAmount = $elem['totalAmount'];
                        $appId = $elem['id'];
                        
                        try{
                            
                            if ($referenceNo == $creditnote->referenceno) {
                                
                                $this->db->exec(array('UPDATE tblcreditnotes SET refundinvoiceno = "' . $refundInvoiceNo . 
                                                                            '", approvestatus = "' . $approveStatusCode . 
                                                                            '", grossamount = "' . $grossAmount . 
                                                                            '", totalamount = "' . $totalAmount . 
                                                                            '", creditnoteapplicationid = "' . $appId . 
                                                                            '", applicationtime = "' . $applicationTime . 
                                                                            '", referenceno = "' . $referenceNo . 
                                                                            '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . 
                                                                            ' WHERE referenceno = "' . $referenceNo . '"'));
                                
                                $this->logger->write($this->db->log(TRUE), 'r');
                                
                                $creditnote->getByID($id);
                                
                                /**
                                 * If the invoice has been issued with a refund invoice number, then retrive it.
                                 */
                                if (trim($refundInvoiceNo) !== '' || !empty(trim($refundInvoiceNo))) {
                                    $data_i = $this->util->downloadrefundinvoice($this->f3->get('SESSION.id'), $refundInvoiceNo);//will return JSON.
                                    
                                    $data_i = json_decode($data_i, true);
                                    
                                    if (isset($data_i['returnCode'])){
                                        $this->logger->write("Creditnote Controller : downloadcreditnote() : The operation to download the refund invoice not successful. The error message is " . $data_i['returnMessage'], 'r');
                                    } else {
                                        if (isset($data_i['basicInformation'])){
                                            
                                            $antifakeCode = $data_i['basicInformation']['antifakeCode']; //32966911991799104051
                                            $invoiceId = $data_i['basicInformation']['invoiceId']; //3257429764295992735
                                            $invoiceNo = $data_i['basicInformation']['invoiceNo']; //3120012276043
                                            
                                            $issuedDate = $data_i['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                                            $issuedDate = str_replace('/', '-', $issuedDate);//Replace / with -
                                            $issuedDate = date("Y-m-d H:i:s", strtotime($issuedDate));
                                            
                                            $issuedTime = $data_i['basicInformation']['issuedDate']; //18/09/2020 17:14:12
                                            $issuedTime = str_replace('/', '-', $issuedTime);//Replace / with -
                                            $issuedTime = date("Y-m-d H:i:s", strtotime($issuedTime));
                                            
                                            $issuedDatePdf = $data_i['basicInformation']['issuedDatePdf']; //318/09/2020 17:14:12
                                            $issuedDatePdf = str_replace('/', '-', $issuedDatePdf);//Replace / with -
                                            $issuedDatePdf = date("Y-m-d H:i:s", strtotime($issuedDatePdf));
                                            
                                            $oriInvoiceId = $data_i['basicInformation']['oriInvoiceId'];//1
                                            $isInvalid = $data_i['basicInformation']['isInvalid'];//1
                                            $isRefund = $data_i['basicInformation']['isRefund'];//1
                                            
                                            $deviceNo = $data_i['basicInformation']['deviceNo'];
                                            $invoiceIndustryCode = $data_i['basicInformation']['invoiceIndustryCode'];
                                            $invoiceKind = $data_i['basicInformation']['invoiceKind'];
                                            $invoiceType = $data_i['basicInformation']['invoiceType'];
                                            $isBatch = $data_i['basicInformation']['isBatch'];
                                            $operator = $data_i['basicInformation']['operator'];
                                            
                                            $currencyRate = $data_i['basicInformation']['currencyRate'];
                                            
                                            
                                            
                                            try{
                                                $this->db->exec(array('UPDATE tblcreditnotes SET antifakeCode = "' . $antifakeCode .
                                                    '", einvoiceid = "' . $invoiceId .
                                                    '", einvoicenumber = "' . $invoiceNo .
                                                    '", issueddate = "' . $issuedDate .
                                                    '", issueddatepdf = "' . $issuedDatePdf .
                                                    '", oriinvoiceid = "' . $oriInvoiceId .
                                                    '", isinvalid = ' . $isInvalid .
                                                    ', isrefund = ' . $isRefund .
                                                    ', issuedtime = "' . $issuedTime .
                                                    '", deviceno = "' . $deviceNo .
                                                    '", invoiceindustrycode = ' . $invoiceIndustryCode .
                                                    ', invoicekind = ' . $invoiceKind .
                                                    ', invoicetype = ' . $invoiceType .
                                                    ', isbatch = "' . $isBatch .
                                                    '", operator = "' . $operator .
                                                    '", currencyRate = ' . $currencyRate .
                                                    ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                    ' WHERE referenceno = "' . $referenceNo . '"'));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : downloadcreditnote() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                            }
                                            
                                        }
                                        
                                        if (isset($data_i['sellerDetails'])){
                                            /*"address":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                                             "branchCode":"00",
                                             "branchId":"912550336846912433",
                                             "branchName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                             "businessName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                             "emailAddress":"editesti06@gmail.com",
                                             "legalName":"FTS GROUP CONSULTING SERVICES LIMITED",
                                             "linePhone":"256787695360",
                                             "mobilePhone":"256782356088",
                                             "ninBrn":"/80020002851201",
                                             "placeOfBusiness":"NTINDA KAMPALA NAKAWA DIVISION NAKAWA DIVISION NTINDA",
                                             "referenceNo":"21",
                                             "tin":"1017918269"*/
                                            
                                            $branchCode = $data_i['sellerDetails']['branchCode'];
                                            $branchId = $data_i['sellerDetails']['branchId'];
                                            $sellerReferenceNo = $data_i['sellerDetails']['referenceNo'];
                                            
                                            $creditnote->getByID($id);
                                            
                                            try{
                                                $this->db->exec(array('UPDATE tblcreditnotes SET branchCode = "' . $branchCode .
                                                    '", branchId = "' . $branchId .
                                                    '", erpcreditnoteid = "' . addslashes($sellerReferenceNo) .
                                                    '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                    ' WHERE referenceno = "' . $referenceNo . '"'));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : downloadcreditnote() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                            }
                                        }
                                        
                                        if (isset($data_i['extend'])){
                                            
                                            
                                            $reason = $data_i['extend']['reason'];
                                            $reasonCode = $data_i['extend']['reasonCode'];
                                            
                                            
                                            
                                            try{
                                                $this->db->exec(array('UPDATE tblcreditnotes SET reason = "' . addslashes($reason) .
                                                    '", reasoncode = "' . $reasonCode .
                                                    '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                    ' WHERE referenceno = "' . $referenceNo . '"'));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : downloadcreditnote() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                            }
                                        }
                                        
                                        if (isset($data_i['summary'])){
                                            $grossAmount = $data_i['summary']['grossAmount']; //832000
                                            $itemCount = $data_i['summary']['itemCount']; //1
                                            $netAmount = $data_i['summary']['netAmount']; //705084.75
                                            $qrCode = $data_i['summary']['qrCode']; //020000001149IC1200122760430004F588000000C1A8450A20A021D534A1000121462A1000094968~MM INTERGRATED STEEL MILLS (UGANDA) LIMITED~Ediomu & Company~Kiboko Galv Corr. Sheet 36G
                                            $taxAmount = $data_i['summary']['taxAmount'];//126915.25
                                            $modeCode = $data_i['summary']['modeCode'];//0
                                            
                                            $mode = new modes($this->db);
                                            $mode->getByCode($modeCode);
                                            $modeName = $mode->name;//online
                                            
                                            $f = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
                                            $grossAmountWords = $f->format($grossAmount);//two million
                                            
                                            try{
                                                $this->db->exec(array('UPDATE tblcreditnotes SET grossamount = ' . $grossAmount . 
                                                    ', itemcount = ' . $itemCount . 
                                                    ', netamount = ' . $netAmount . 
                                                    ', einvoicedatamatrixcode = "' . addslashes($qrCode) . 
                                                    '", taxamount = ' . $taxAmount . 
                                                    ', modecode = "' . $modeCode . 
                                                    '", modename = "' . $modeName . 
                                                    '", grossamountword = "' . addslashes($grossAmountWords) . 
                                                    '", modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . 
                                                    ' WHERE referenceno = "' . $referenceNo . '"'));
                                                
                                                $this->logger->write($this->db->log(TRUE), 'r');
                                            } catch (Exception $e) {
                                                $this->logger->write("Creditnote Controller : downloadcreditnote() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                                            }
                                        }
                                        
                                        if (isset($data_i['goodsDetails'])){
                                            /*"deemedFlag":"2",
                                             "discountFlag":"2",
                                             "exciseFlag":"2",
                                             "exciseTax":"0",
                                             "goodsCategoryId":"72152507",
                                             "goodsCategoryName":"Vinyl floor tile and sheet installation service",
                                             "item":"Iron Sheets",
                                             "itemCode":"Iron2021",
                                             "orderNumber":"0",
                                             "qty":"1",
                                             "tax":"1525.42",
                                             "taxRate":"0.18",
                                             "total":"10000",
                                             "unitOfMeasure":"MTR",
                                             "unitPrice":"10000"*/
                                            
                                            if ($data_i['goodsDetails']) {
                                                
                                                 try{
                                                 $this->db->exec(array('DELETE FROM tblgooddetails WHERE groupid = ' . $creditnote->gooddetailgroupid));
                                                 } catch (Exception $e) {
                                                 $this->logger->write("Creditnote Controller : downloadcreditnote() : The operation to delete from table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                 }
                                                
                                                foreach($data_i['goodsDetails'] as $elem){
                                                    
                                                    try{
                                                        
                                                        $deemedFlag = $elem['deemedFlag'];
                                                        $discountFlag = $elem['discountFlag'];
                                                        $exciseFlag = $elem['exciseFlag'];
                                                        $exciseTax = $elem['exciseTax'];
                                                        $goodsCategoryId = $elem['goodsCategoryId'];
                                                        $goodsCategoryName = $elem['goodsCategoryName'];
                                                        $item = $elem['item'];
                                                        $itemCode = $elem['itemCode'];
                                                        $orderNumber = $elem['orderNumber'];
                                                        $qty = $elem['qty'];
                                                        $tax = $elem['tax'];
                                                        $taxRate = $elem['taxRate'];
                                                        $total = $elem['total'];
                                                        $unitOfMeasure = $elem['unitOfMeasure'];
                                                        $unitPrice = $elem['unitPrice'];
                                                        
                                                        $measureunit = new measureunits($this->db);
                                                        $measureunit->getByCode($unitOfMeasure);
                                                        
                                                        $unitofmeasurename = $measureunit->name;
                                                        
                                                        //Tax ID
                                                        //Tax Category
                                                        
                                                        $this->db->exec(array('INSERT INTO tblgooddetails
                                                                (groupid,
                                                                deemedflag,
                                                                discountflag,
                                                                exciseflag,
                                                                excisetax,
                                                                goodscategoryid,
                                                                goodscategoryname,
                                                                item,
                                                                itemcode,
                                                                ordernumber,
                                                                qty,
                                                                tax,
                                                                taxrate,
                                                                total,
                                                                unitofmeasure,
                                                                unitprice,
                                                                unitofmeasurename,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $creditnote->gooddetailgroupid . ',
                                                                ' . $deemedFlag . ',
                                                                ' . $discountFlag . ',
                                                                ' . $exciseFlag . ',
                                                                ' . $exciseTax . ',
                                                                "' . addslashes($goodsCategoryId) . '",
                                                                "' . addslashes($goodsCategoryName) . '",
                                                                "' . addslashes($item) . '",
                                                                "' . addslashes($itemCode) . '",
                                                                ' . $orderNumber . ',
                                                                ' . $qty . ',
                                                                ' . $tax . ',
                                                                ' . $taxRate . ',
                                                                ' . $total . ',
                                                                "' . addslashes($unitOfMeasure) . '",
                                                                ' . $unitPrice . ',
                                                                "' . addslashes($unitofmeasurename) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                                        
                                                        $this->logger->write($this->db->log(TRUE), 'r');
                                                    } catch (Exception $e) {
                                                        $this->logger->write("Creditnote Controller : downloadcreditnote() : The operation to insert into table tblgooddetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                    }
                                                    
                                                }
                                                
                                            } else {//NOTHING RETURNED BY API
                                                $this->logger->write("Creditnote Controller : downloadcreditnote() : The API did not return anything", 'r');
                                            }
                                        }
                                        
                                        if (isset($data_i['buyerDetails'])){
                                            
                                            /*
                                            "buyerDetails":{
                                            "dateFormat":"dd/MM/yyyy",
                                            "nowTime":"2021/06/10 15:26:37",
                                            "pageIndex":0,
                                            "pageNo":0,
                                            "pageSize":0,
                                            "timeFormat":"dd/MM/yyyy HH24:mi:ss"
                                            }*/
                                            
                                            /*$buyerAddress = $data_i['buyerDetails']['buyerAddress'];
                                            $buyerBusinessName = $data_i['buyerDetails']['buyerBusinessName'];
                                            $buyerEmail = $data_i['buyerDetails']['buyerEmail'];
                                            $buyerLegalName = $data_i['buyerDetails']['buyerLegalName'];
                                            $buyerMobilePhone = $data_i['buyerDetails']['buyerMobilePhone'];
                                            $buyerTin = $data_i['buyerDetails']['buyerTin'];
                                            $buyerType = $data_i['buyerDetails']['buyerType'];*/
                                            
                                            $creditnote->getByID($id);
                                            
                                            if (!empty(trim($creditnote->buyerid))) {
                                            //if (trim($creditnote->buyerid) !== '0' || trim($creditnote->buyerid) !== '' || !empty(trim($creditnote->buyerid))) {
                                                $this->logger->write("Creditnote Controller : downloadcreditnote() : The buyer is already set", 'r');
                                                
                                                try{
                                                    /*$this->db->exec(array('UPDATE tblbuyers SET address = "' . addslashes($buyerAddress) .
                                                        '", businessname = "' . addslashes($buyerBusinessName) .
                                                        '", emailaddress = "' . addslashes($buyerEmail) .
                                                        '", legalname = "' . addslashes($buyerLegalName) .
                                                        '", mobilephone = "' . addslashes($buyerMobilePhone) .
                                                        '", referenceno = "' . addslashes($referenceNo) .
                                                        '", tin = "' . $buyerTin .
                                                        '", type = ' . $buyerType .
                                                        ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                        ' WHERE id = ' . $creditnote->buyerid));
                                                    
                                                    $this->logger->write($this->db->log(TRUE), 'r');*/
                                                } catch (Exception $e) {
                                                    $this->logger->write("Creditnote Controller : downloadcreditnote() : Failed to update the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } else {
                                                $this->logger->write("Creditnote Controller : downloadcreditnote() : The buyer is NOT set", 'r');
                                                
                                                try{
                                                    /*$this->db->exec(array('INSERT INTO tblbuyers
                                                                (address,
                                                                businessname,
                                                                emailaddress,
                                                                legalname,
                                                                mobilephone,
                                                                referenceno,
                                                                tin,
                                                                type,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                ("' . addslashes($buyerAddress) . '",
                                                                "' . addslashes($buyerBusinessName) . '",
                                                                "' . addslashes($buyerEmail) . '",
                                                                "' . addslashes($buyerLegalName) . '",
                                                                "' . addslashes($buyerMobilePhone) . '",
                                                                "' . addslashes($referenceNo) . '",
                                                                "' . $buyerTin . '",
                                                                ' . $buyerType . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                                    
                                                    $this->logger->write($this->db->log(TRUE), 'r');
                                                    
                                                    
                                                    
                                                    try {
                                                        if (trim($referenceNo) !== '' || !empty(trim($referenceNo))) {
                                                            $b = array ();
                                                            $r = $this->db->exec(array('SELECT id "id" FROM tblbuyers WHERE TRIM(referenceno) = "' . $referenceNo . '"'));
                                                            
                                                            foreach ( $r as $obj ) {
                                                                $b [] = $obj;
                                                            }
                                                            
                                                            $buyerid = $b[0]['id'];
                                                            
                                                            $this->db->exec(array('UPDATE tblcreditnotes SET buyerid = ' . $buyerid . ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . ' WHERE TRIM(referenceno) = "' . $referenceNo . '"'));
                                                        } else {
                                                            ;
                                                        }
                                                        
                                                    } catch (Exception $e) {
                                                        $this->logger->write("Creditnote Controller : downloadcreditnote() : Failed to select from table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                                    }*/
                                                    
                                                } catch (Exception $e) {
                                                    $this->logger->write("Creditnote Controller : downloadcreditnote() : Failed to insert into the table tblbuyers. The error message is " . $e->getMessage(), 'r');
                                                }
                                            }
                                        }
                                        
                                        
                                        
                                        if (isset($data_i['payWay'])){
                                            /*"dateFormat":"dd/MM/yyyy",
                                             "nowTime":"2021/05/23 14:14:18",
                                             "orderNumber":"0",
                                             "paymentAmount":"45000",
                                             "paymentMode":"102",
                                             "timeFormat":"dd/MM/yyyy HH24:mi:ss"*/
                                            
                                            if ($data_i['payWay']) {
                                                try{
                                                    $this->db->exec(array('DELETE FROM tblpaymentdetails WHERE groupid = ' . $creditnote->paymentdetailgroupid));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Creditnote Controller : downloadcreditnote() : The operation to delete from table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                }
                                                
                                                foreach($data_i['payWay'] as $elem){
                                                    
                                                    try{
                                                        
                                                        
                                                        $orderNumber = $elem['orderNumber'];
                                                        $paymentAmount = $elem['paymentAmount'];
                                                        $paymentMode = $elem['paymentMode'];
                                                        
                                                        $pm = new paymentmodes($this->db);
                                                        $pm->getByCode($paymentMode);
                                                        
                                                        $paymentmodename = $pm->name;
                                                        
                                                        $this->db->exec(array('INSERT INTO tblpaymentdetails
                                                                (groupid,
                                                                ordernumber,
                                                                paymentamount,
                                                                paymentmode,
                                                                paymentmodename,
                                                                inserteddt,
                                                                insertedby,
                                                                modifieddt,
                                                                modifiedby)
                                                                VALUES
                                                                (' . $creditnote->paymentdetailgroupid . ',
                                                                ' . $orderNumber . ',
                                                                ' . $paymentAmount . ',
                                                                ' . $paymentMode . ',
                                                                "' . addslashes($paymentmodename) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                                    } catch (Exception $e) {
                                                        $this->logger->write("Creditnote Controller : downloadcreditnote() : The operation to insert into table tblpaymentdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                    }
                                                    
                                                }
                                                
                                            } else {//NOTHING RETURNED BY API
                                                $this->logger->write("Creditnote Controller : downloadcreditnote() : The API did not return anything", 'r');
                                            }
                                        }
                                        
                                        if (isset($data_i['taxDetails'])){
                                            /*"taxCategory":"Standard",
                                             "netAmount":"123389830.51",
                                             "taxRate":"0.18",
                                             "taxAmount":"22210169.49",
                                             "grossAmount":"145600000.00",
                                             "exciseUnit":"",
                                             "exciseCurrency":"",
                                             "taxRateName":"18%"*/
                                            
                                            if ($data_i['taxDetails']) {
                                                try{
                                                    $this->db->exec(array('DELETE FROM tbltaxdetails WHERE groupid = ' . $creditnote->taxdetailgroupid));
                                                } catch (Exception $e) {
                                                    $this->logger->write("Creditnote Controller : downloadcreditnote() : The operation to delete from table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                }
                                                
                                                foreach($data_i['taxDetails'] as $elem){
                                                    
                                                    try{
                                                        
                                                        
                                                        $taxCategory = $elem['taxCategory'];
                                                        $netAmount = $elem['netAmount'];
                                                        $taxRate = $elem['taxRate'];
                                                        $taxAmount = $elem['taxAmount'];
                                                        $grossAmount = $elem['grossAmount'];
                                                        $exciseUnit = $elem['exciseUnit'];
                                                        $exciseCurrency = $elem['exciseCurrency'];
                                                        $taxRateName = $elem['taxRateName'];
                                                        
                                                        $tr = new taxrates($this->db);
                                                        $tr->getByName($taxRateName);
                                                        
                                                        $taxdescription = $tr->description;//A: Standard (18%)
                                                        
                                                        $this->db->exec(array('INSERT INTO tbltaxdetails
                                                                (groupid,
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
                                                                VALUES
                                                                (' . $creditnote->taxdetailgroupid . ', 0,
                                                                "' . addslashes($taxCategory) . '",
                                                                ' . $netAmount . ',
                                                                ' . $taxRate . ',
                                                                ' . $taxAmount . ',
                                                                ' . $grossAmount . ',
                                                                "' . addslashes($exciseUnit) . '",
                                                                "' . addslashes($exciseCurrency) . '",
                                                                "' . addslashes($taxRateName) . '",
                                                                "' . addslashes($taxdescription) . '", NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                ' . $this->f3->get('SESSION.id') . ')'));
                                                    } catch (Exception $e) {
                                                        $this->logger->write("Creditnote Controller : downloadcreditnote() : The operation to insert into table tbltaxdetails was not successful. The error message is " . $e->getMessage(), 'r');
                                                    }
                                                    
                                                }
                                                
                                            } else {//NOTHING RETURNED BY API
                                                $this->logger->write("Creditnote Controller : downloadcreditnote() : The API did not return anything", 'r');
                                            }
                                        }                
                                        
                                    }
                                }
                            } else {
                                $this->logger->write("Creditnote Controller : downloadcreditnote() : The reference number does not match", 'r');
                            }
                            
                            
                            
                            
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was successful");
                            self::$systemalert = "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was successful";
                        } catch (Exception $e) {
                            $this->logger->write("Creditnote Controller : downloadcreditnote() : Failed to update the table tblcreditnotes. The error message is " . $e->getMessage(), 'r');
                            self::$systemalert = "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful";
                        }
                        
                    }
                } else {
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the credit note first");
                    self::$systemalert = "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the credit note first";
                }
                
                
            } else {
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to download the credit note by " . $this->f3->get('SESSION.username') . " was not successful";
            }
            
            //die($data);
        } else {
            $this->logger->write("Creditnote Controller : downloadcreditnote() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name cancelcreditnote
     *  @desc cancel a credit note from EFRIS
     *	@return
     *	@param
     **/
    function cancelcreditnote(){
        $operation = NULL; //tblevents
        $permission = 'CANCELCREDITNOTE'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.cancelcreditnoteid');
        $creditnote = new creditnotes($this->db);
        $creditnote->getByID($id);
        $this->logger->write("Creditnote Controller : cancelcreditnote() : The creditnote id is " . $this->f3->get('POST.cancelcreditnoteid'), 'r');
        
        $this->logger->write("Creditnote Controller : cancelcreditnote() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            $data = $this->util->cancelcreditnote($this->f3->get('SESSION.id'), $id);//will return JSON.
            //var_dump($data);
            
            $data = json_decode($data, true);
            //$this->logger->write("Creditnote Controller : cancelcreditnote() : The response content is: " . $data, 'r');
            //var_dump($data);
            
            
            if (isset($data['returnCode'])){
                $this->logger->write("Creditnote Controller : cancelcreditnote() : The operation to cancel the credit note not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to cancel the credit note by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to cancel the credit note by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
            } else {
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to cancel the credit note by " . $this->f3->get('SESSION.username') . " was successful");
                self::$systemalert = "The operation to cancel the credit note by " . $this->f3->get('SESSION.username') . " was successful";
            }
            
            //die($data);
        } else {
            $this->logger->write("Creditnote Controller : cancelcreditnote() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
       
    /**
     *	@name printcreditnote
     *  @desc Print an creditnote
     *	@return NULL
     *	@param NULL
     **/
    function printcreditnote(){
        $operation = NULL; //tblevents
        $permission = 'PRINTCREDITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Creditnote Controller : printcreditnote() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Creditnote Controller : printcreditnote() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Creditnote Controller : printcreditnote() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $id = trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("Creditnote Controller : printcreditnote() : The is a GET call & id to view is " . $id, 'r');
            
            // The Creditnote
            $creditnote = new creditnotes($this->db);
            $creditnote->grossamount2 = 'FORMAT(grossamount, 2)';
            $creditnote->taxamount2 = 'FORMAT(taxamount, 2)';
            $creditnote->netamount2 = 'FORMAT(netamount, 2)';
            $creditnote->getByID($id);
            $this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('creditnote', $creditnote);
            
            // The Invoice
            $invoice = new invoices($this->db);
            $invoice->netamount2 = 'FORMAT(netamount, 2)';
            $invoice->taxamount2 = 'FORMAT(taxamount, 2)';
            $invoice->grossamount2 = 'FORMAT(grossamount, 2)';
            $invoice->getByInvoiceNo($creditnote->oriinvoiceno);
            $this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('invoice', $invoice);
            
            //The Seller
            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
            $this->f3->set('seller', $org);
            
            //The Buyer
            $buyer = new buyers($this->db);
            $buyer->getByID($creditnote->buyerid);
            $this->f3->set('buyer', $buyer);
            
            //The Goods
            try{
                $goods = array();
                
                $temp = $this->db->exec(array('SELECT item, FORMAT(qty, 2) qty, unitofmeasure, FORMAT(unitprice, 2) unitprice, FORMAT(total, 2) total, displayCategoryCode taxcategory, unitofmeasurename, FORMAT(discounttotal, 2) discounttotal FROM tblgooddetails WHERE groupid = COALESCE(' . $creditnote->gooddetailgroupid . ', NULL) ORDER BY inserteddt ASC'));
                
                if (!empty($temp)) {
                    foreach ($temp as $obj) {
                        $goods[] = array(
                            'item' => empty($obj['item'])? '' : $obj['item'],
                            'qty' => empty($obj['qty'])? '' : $obj['qty'],
                            'unitofmeasure' => empty($obj['unitofmeasure'])? '' : $obj['unitofmeasure'],
                            'unitprice' => empty($obj['unitprice'])? '' : $obj['unitprice'],
                            'total' => empty($obj['total'])? '' : $obj['total'],
                            'taxcategory' => empty($obj['taxcategory'])? '' : $obj['taxcategory'],
                            'unitofmeasurename' => empty($obj['unitofmeasurename'])? '' : $obj['unitofmeasurename']
                        );
                        
                        //If there is a discount, add a discount line below the item
                        if ($obj['discounttotal'] < 0) {
                            $goods[] = array(
                                'item' => empty($obj['item'])? '' : $obj['item'] . " (Discount)",
                                'qty' => '',
                                'unitofmeasure' => '',
                                'unitprice' => '',
                                'total' => empty($obj['discounttotal'])? '' : $obj['discounttotal'],
                                'taxcategory' => '',
                                'unitofmeasurename' => ''
                            );
                        }
                    }
                } else {
                    $goods = array(
                        "0" => array()
                    );
                }
            } catch (Exception $e) {
                $this->logger->write("Creditnote Controller : printcreditnote() : The operation to retrieve goods was not successfull. The error messages is " . $e->getMessage(), 'r');
                $goods = array(
                    "0" => array()
                );
            }            
            $this->f3->set('goods', $goods);
            //$this->logger->write($this->db->log(TRUE), 'r');
            
            //The Tax Details
            try{
                $taxes = $this->db->exec(array('SELECT FORMAT(-1*netamount, 2) netamount, FORMAT(taxrate, 2) taxrate, FORMAT(-1*taxamount, 2) taxamount, FORMAT(-1*grossamount, 2) grossamount, taxdescription FROM tbltaxdetails WHERE groupid = COALESCE(' . $creditnote->taxdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Creditnote Controller : printcreditnote() : The operation to retrieve taxes was not successfull. The error messages is " . $e->getMessage(), 'r');
                $taxes = array(
                    "0" => array()
                );
            }
            $this->f3->set('taxes', $taxes);
            
            
            //The Payments
            try{
                $payments = $this->db->exec(array('SELECT paymentmodename, FORMAT(paymentamount, 2) paymentamount FROM tblpaymentdetails WHERE groupid = COALESCE(' . $creditnote->paymentdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Creditnote Controller : printcreditnote() : The operation to retrieve payments was not successfull. The error messages is " . $e->getMessage(), 'r');
                $payments = array(
                    "0" => array()
                );
            }
            $this->f3->set('payments', $payments);
            
            
            $this->f3->set('path', '../' . $this->path);
            $this->f3->set('pagetitle','Print Credit Note | ' . $id);//display the edit form

            
            echo \Template::instance()->render('PrintCreditnote.htm');
        } else {
            $this->logger->write("Creditnote Controller : printcreditnote() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name pviewcreditnote
     *  @desc View an creditnote
     *	@return NULL
     *	@param NULL
     **/
    function pviewcreditnote(){
        $operation = NULL; //tblevents
        $permission = 'VIEWCREDITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Creditnote Controller : pviewcreditnote() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Creditnote Controller : pviewcreditnote() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Creditnote Controller : pviewcreditnote() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            $id = trim($this->f3->get('PARAMS[id]'));
            $this->logger->write("Creditnote Controller : pviewcreditnote() : The is a GET call & id to view is " . $id, 'r');
            
            // The Creditnote
            $creditnote = new creditnotes($this->db);
            $creditnote->grossamount2 = 'FORMAT(grossamount, 2)';
            $creditnote->taxamount2 = 'FORMAT(taxamount, 2)';
            $creditnote->netamount2 = 'FORMAT(netamount, 2)';
            $creditnote->getByID($id);
            $this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('creditnote', $creditnote);
            
            // The Invoice
            $invoice = new invoices($this->db);
            $invoice->netamount2 = 'FORMAT(netamount, 2)';
            $invoice->taxamount2 = 'FORMAT(taxamount, 2)';
            $invoice->grossamount2 = 'FORMAT(grossamount, 2)';
            $invoice->getByInvoiceNo($creditnote->oriinvoiceno);
            $this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('invoice', $invoice);
            
            //The Seller
            $org = new organisations($this->db);
            $org->getBySeller($this->appsettings['SELLER_RECORD_ID']);
            $this->f3->set('seller', $org);
            
            //The Buyer
            $buyer = new buyers($this->db);
            $buyer->getByID($creditnote->buyerid);
            $this->f3->set('buyer', $buyer);
            
            //The Goods
            try{
                $goods = array();
                
                $temp = $this->db->exec(array('SELECT item, FORMAT(qty, 2) qty, unitofmeasure, FORMAT(unitprice, 2) unitprice, FORMAT(total, 2) total, displayCategoryCode taxcategory, unitofmeasurename, FORMAT(discounttotal, 2) discounttotal FROM tblgooddetails WHERE groupid = COALESCE(' . $creditnote->gooddetailgroupid . ', NULL) ORDER BY inserteddt ASC'));
                
                if (!empty($temp)) {
                    foreach ($temp as $obj) {
                        $goods[] = array(
                            'item' => empty($obj['item'])? '' : $obj['item'],
                            'qty' => empty($obj['qty'])? '' : $obj['qty'],
                            'unitofmeasure' => empty($obj['unitofmeasure'])? '' : $obj['unitofmeasure'],
                            'unitprice' => empty($obj['unitprice'])? '' : $obj['unitprice'],
                            'total' => empty($obj['total'])? '' : $obj['total'],
                            'taxcategory' => empty($obj['taxcategory'])? '' : $obj['taxcategory'],
                            'unitofmeasurename' => empty($obj['unitofmeasurename'])? '' : $obj['unitofmeasurename']
                        );
                        
                        //If there is a discount, add a discount line below the item
                        if ($obj['discounttotal'] < 0) {
                            $goods[] = array(
                                'item' => empty($obj['item'])? '' : $obj['item'] . " (Discount)",
                                'qty' => '',
                                'unitofmeasure' => '',
                                'unitprice' => '',
                                'total' => empty($obj['discounttotal'])? '' : $obj['discounttotal'],
                                'taxcategory' => '',
                                'unitofmeasurename' => ''
                            );
                        }
                    }
                } else {
                    $goods = array(
                        "0" => array()
                    );
                }
            } catch (Exception $e) {
                $this->logger->write("Creditnote Controller : pviewcreditnote() : The operation to retrieve goods was not successfull. The error messages is " . $e->getMessage(), 'r');
                $goods = array(
                    "0" => array()
                );
            }
            $this->f3->set('goods', $goods);
            //$this->logger->write($this->db->log(TRUE), 'r');
            
            //The Tax Details
            try{
                $taxes = $this->db->exec(array('SELECT FORMAT(-1*netamount, 2) netamount, FORMAT(taxrate, 2) taxrate, FORMAT(-1*taxamount, 2) taxamount, FORMAT(-1*grossamount, 2) grossamount, taxdescription FROM tbltaxdetails WHERE groupid = COALESCE(' . $creditnote->taxdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Creditnote Controller : pviewcreditnote() : The operation to retrieve taxes was not successfull. The error messages is " . $e->getMessage(), 'r');
                $taxes = array(
                    "0" => array()
                );
            }
            $this->f3->set('taxes', $taxes);
            
            
            //The Payments
            try{
                $payments = $this->db->exec(array('SELECT paymentmodename, FORMAT(paymentamount, 2) paymentamount FROM tblpaymentdetails WHERE groupid = COALESCE(' . $creditnote->paymentdetailgroupid . ', NULL) ORDER BY id ASC'));
            } catch (Exception $e) {
                $this->logger->write("Creditnote Controller : pviewcreditnote() : The operation to retrieve payments was not successfull. The error messages is " . $e->getMessage(), 'r');
                $payments = array(
                    "0" => array()
                );
            }
            $this->f3->set('payments', $payments);
            
            
            $this->f3->set('path', '../' . $this->path);
            $this->f3->set('pagetitle','View Credit Note | ' . $id);//display the edit form
            
            
            $this->f3->set('pagecontent','ViewCreditnote.htm');
            $this->f3->set('pagescripts','ViewCreditnoteFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Creditnote Controller : pviewcreditnote() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name downloadErpCreditnotes
     *  @desc download creditnotes from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function downloadErpCreditnotes(){
        $operation = NULL; //tblevents
        $permission = 'SYNCCREDITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            $tcsdetails = new tcsdetails($this->db);
            $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
            
            $companydetails = new organisations($this->db);
            $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);
            
            $devicedetails = new devices($this->db);
            $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
            
            $startDate = $this->f3->get('POST.downloaderpcreditnotesstartdate');
            $endDate = $this->f3->get('POST.downloaderpcreditnotesenddate');
            $creditnoteNo = $this->f3->get('POST.downloaderpcreditnotenumber');
            $docType = $this->f3->get('POST.downloaderpcreditnoteserpdoctype');
            
            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : startDate: " . $startDate, 'r');
            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : endDate: " . $endDate, 'r');
            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : creditnoteNo: " . $creditnoteNo, 'r');
            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : docType: " . $docType, 'r');
            
            $startDate = empty($startDate)? date('Y-m-d') : date('Y-m-d', strtotime($startDate));
            $endDate = empty($endDate)? date('Y-m-d') : date('Y-m-d', strtotime($endDate));
            //$creditnoteNo = empty($creditnoteNo)? 'NULL' : $creditnoteNo;
            $docType = empty($docType)? $this->appsettings['CREDITMEMOERPDOCTYPE'] : trim($docType);
            
            
            
            if ($this->platformMode == 'ERP') {
                $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
            } else {
                $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The platform is integrated.", 'r');
                
                if ($this->integratedErp) {
                    /**
                     * Check on integrated ERP type
                     */
                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                    
                    if (strtoupper($this->integratedErp) == 'QBO') {
                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The integrated ERP is Quicbooks Online.", 'r');
                        
                        $qry = '';
                        
                        if ($docType == trim($this->appsettings['CREDITMEMOERPDOCTYPE'])) {
                            $qry = 'SELECT * FROM CreditMemo';
                        } elseif ($docType == trim($this->appsettings['REFUNDRECEIPTERPDOCTYPE'])){
                            $qry = 'SELECT * FROM RefundReceipt';
                        } else {
                            $qry = 'SELECT * FROM CreditMemo';
                        }
                        
                        if ($creditnoteNo) {
                            $qry = $qry . " Where DocNumber = '" . $creditnoteNo . "'";
                        } else {
                            $qry = $qry . " Where Metadata.LastUpdatedTime >= '" . $startDate . "' And Metadata.LastUpdatedTime <= '" . $endDate . "'";
                        }
                        
                        
                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The query is: " . $qry, 'r');
                        
                        try {
                            if ($this->appsettings['QBACCESSTOKEN'] !== null) {
                            //if ($this->f3->get('SESSION.sessionAccessToken') !== null) {
                                // Create SDK instance
                                $authMode = $this->appsettings['QBAUTH_MODE'];
                                $ClientID = $this->appsettings['QBCLIENT_ID'];
                                $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                $baseUrl = $this->appsettings['QBBASE_URL'];
                                $QBORealmID = $this->appsettings['QBREALMID'];
                                
                                $accessToken = $this->f3->get('SESSION.sessionAccessToken');
                                
                                $dataService = DataService::Configure(array(
                                    'auth_mode' => $authMode,
                                    'ClientID' => $ClientID,
                                    'ClientSecret' =>  $ClientSecret,
                                    'baseUrl' => $baseUrl,
                                    'refreshTokenKey' => $accessToken->getRefreshToken(),
                                    'QBORealmID' => $QBORealmID,
                                    'accessTokenKey' => $accessToken->getAccessToken()
                                ));
                                
                                $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                $dataService->throwExceptionOnError(true);
                                
                                $creditnotes = $dataService->Query($qry);
                                
                                $error = $dataService->getLastError();
                                
                                if ($error) {
                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The operation to download ERP creditnotes was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP creditnotes by " . $this->f3->get('SESSION.username') . " was successful");
                                    self::$systemalert = "The operation to download ERP creditnotes by " . $this->f3->get('SESSION.username') . " was successful.";
                                }
                                else {
                                    //print_r($creditnotes);
                                    
                                    if(isset($creditnotes)){
                                        if ($creditnotes) {
                                            $creditnote = new creditnotes($this->db);
                                            $customer = new customers($this->db);
                                            
                                            $goods = array();
                                            $taxes = array();
                                            
                                            $deemedflag = 'NO';
                                            $discountflag = 'NO';
                                            
                                            $pricevatinclusive = empty($this->appsettings['PRICEVATINCLUSIVE'])? 'NO' : strtoupper($this->appsettings['PRICEVATINCLUSIVE']);//No
                                            
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
                                                'status' => $this->appsettings['ACTIVECUSTOMERSTATUSID'],
                                            );
                                            
                                            $creditnotedetails = array(
                                                'id' => NULL,
                                                'gooddetailgroupid' => NULL,
                                                'taxdetailgroupid' => NULL,
                                                'paymentdetailgroupid' => NULL,
                                                'erpcreditnoteid' => NULL,
                                                'erpcreditnoteno' => NULL,
                                                'erpinvoiceid' => NULL,
                                                'erpinvoiceno' => NULL,
                                                'antifakecode' => NULL,
                                                'deviceno' => trim($devicedetails->deviceno),
                                                'issueddate' => date('Y-m-d'),
                                                'issuedtime' => date('Y-m-d H:i:s'),
                                                'operator' => NULL,
                                                'currency' => NULL,
                                                'oriinvoiceid' => NULL,
                                                'oriinvoiceno' => NULL,
                                                'invoicetype' => "2",
                                                'invoicekind' => ($this->vatRegistered == 'Y')? "1" : "2",
                                                'datasource' => $this->appsettings['DEFAULTDATASOURCE'],
                                                'invoiceindustrycode' => $this->appsettings['DEFAULTINVOICEINDUSTRY'],
                                                'einvoiceid' => NULL,
                                                'einvoicenumber' => NULL,
                                                'einvoicedatamatrixcode' => NULL,
                                                'isbatch' => NULL,
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
                                                'issueddatepdf' => date('Y-m-d H:i:s'),
                                                'grossamountword' => NULL,
                                                'isinvalid' => 0,
                                                'isrefund' => 0,
                                                'vchtype' => "Credit Note",
                                                'vchtypename' => "Credit Note",
                                                'reasoncode' => NULL,
                                                'reason' => NULL,
                                                'referenceno' => NULL,
                                                'approvestatus' => NULL,
                                                'creditnoteapplicationid' => NULL,
                                                'refundinvoiceno' => NULL,
                                                'applicationtime' => date('Y-m-d H:i:s'),
                                                'invoiceapplycategorycode' => '101', /*101-Credit Note*/
                                                'SyncToken' => NULL,
                                                'docTypeCode' => $docType
                                            );
                                            
                                            $discountAppStatus = 0;
                                            $discountAppBalance = 0;
                                            $discountAppPct = 0;
                                            
                                            foreach($creditnotes as $elem){
                                                
                                                try {
                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Creditnote Number: " . $elem->DocNumber, 'r');
                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : PrivateNote: " . $elem->PrivateNote, 'r');
                                                    $InvStatus = $elem->PrivateNote;
                                                    
                                                    //Original Invoice Id.
                                                    $orivchnumber = $elem->InvoiceRef;
                                                    
                                                    if(trim($orivchnumber) !== '' || ! empty(trim($orivchnumber))) {
                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The associated original invoice was supplied", 'r');
                                                        
                                                        $orig_inv = new DB\SQL\Mapper($this->db, 'tblinvoices');
                                                        $orig_inv->load(array('TRIM(erpinvoiceid)=?', $orivchnumber));
                                                        //$this->logger->write($this->db->log(TRUE), 'r');
                                                        if ($orig_inv->dry()) {
                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : There associated original invoice does not exist in the database", 'r');
                                                        } else {
                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : There is an associated original invoice", 'r');
                                                            $oriinvoiceid = $orig_inv->einvoiceid;
                                                            $oriinvoiceno = $orig_inv->einvoicenumber;
                                                            
                                                            /**
                                                             * Author: frncslubanga@gmail.com
                                                             * Date: 2021-02-28
                                                             * Description: Resolve EFRIS error code 2783: oriInvoiceNo: cannot be empty!
                                                             *
                                                             *
                                                             * 1. Check if oriinvoiceid is empty
                                                             * 2. If oriinvoiceid is NOT empty, then ignore
                                                             * 3. If it is empty, then query EFRIS and retrieve it
                                                             * 4. Update the eTW record of this invoice
                                                             */
                                                            
                                                            if(trim($oriinvoiceid) == '' || empty(trim($oriinvoiceid))) {
                                                                $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The oriinvoiceid is empty", 'r');
                                                                
                                                                if(trim($oriinvoiceno) == '' || empty(trim($oriinvoiceno))) {
                                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The oriinvoiceno is empty", 'r');
                                                                } else {
                                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The oriinvoiceno is NOT empty", 'r');
                                                                    $i_data = $this->util->downloadinvoice($this->f3->get('SESSION.id'), $orig_inv->id);
                                                                    $i_data = json_decode($i_data, true);
                                                                    
                                                                    /*START OF INVOICE BLOCK*/
                                                                    if (isset($i_data['basicInformation'])){
                                                                        $TempInvoiceId = $i_data['basicInformation']['invoiceId']; //3257429764295992735
                                                                        $TempInvoiceNo = $i_data['basicInformation']['invoiceNo']; //3120012276043
                                                                        
                                                                        if (trim($TempInvoiceNo) == trim($oriinvoiceno)) {
                                                                            $oriinvoiceid = $TempInvoiceId;
                                                                        }
                                                                    }
                                                                    /*END INVOICE BLOCK*/
                                                                    
                                                                    try{
                                                                        $this->db->exec(array('UPDATE tblinvoices SET einvoiceid = "' . $oriinvoiceid . '", modifieddt = NOW(), modifiedby = ' . $this->userid . ' WHERE einvoicenumber = "' . $oriinvoiceno . '"'));
                                                                        $this->logger->write($this->db->log(TRUE), 'r');
                                                                    } catch (Exception $e) {
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                                                    }
                                                                }
                                                            } else {
                                                                $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The oriinvoiceid is not empty", 'r');
                                                            }
                                                            
                                                            $reasoncode = '105';
                                                            $reason = 'Credit Memo';
                                                            //INSERT QB Code here.
                                                            
                                                            $creditnotedetails['reasoncode'] = $reasoncode;
                                                            $creditnotedetails['reason'] = $reason;
                                                            
                                                            $CustomerRef = $elem->CustomerRef;
                                                            $DocNumber = $elem->DocNumber;
                                                            $CurrencyRef = $elem->CurrencyRef;
                                                            $TxnDate = $elem->TxnDate;
                                                            $CreditnoteId = $elem->Id;
                                                            $SyncToken = $elem->SyncToken;
                                                            $TxnDate = $elem->TxnDate;
                                                            
                                                            $creditnotedetails['erpcreditnoteid'] = $CreditnoteId;
                                                            $creditnotedetails['erpcreditnoteno'] = $DocNumber;
                                                            
                                                            if ($CustomerRef) {
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
                                                                    
                                                                    
                                                                    $creditnotedetails['buyerid'] = $customer->id;
                                                                } else {
                                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The customer Id " . $CustomerRef . " does not exist on the platform", 'r');
                                                                }
                                                                
                                                                
                                                            }
                                                            
                                                            if(isset($elem->Line)){
                                                                foreach($elem->Line as $items){
                                                                    $LineId = $items->Id;
                                                                    $LineNum = $items->LineNum;
                                                                    $Description = $items->Description;
                                                                    $ErpAmount = $items->Amount;
                                                                    $DetailType = $items->DetailType;
                                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Line Description: " . $Description, 'r');
                                                                    
                                                                    if (strtoupper($items->DetailType) == 'DISCOUNTLINEDETAIL') {
                                                                        if(isset($items->DiscountLineDetail)){
                                                                            $PercentBased = $items->DiscountLineDetail->PercentBased;//true/false
                                                                            $DiscountPercent = $items->DiscountLineDetail->DiscountPercent;
                                                                        }
                                                                        
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Discount Percent: " . $PercentBased, 'r');
                                                                        $discount = empty($ErpAmount)? 0 : (float)$ErpAmount;
                                                                        $discountpct = empty($DiscountPercent)? 0 : (float)$DiscountPercent;
                                                                        
                                                                        if (!empty($ErpAmount)){
                                                                            $discountAppStatus = 1;
                                                                            $discountAppBalance = $ErpAmount;
                                                                            $discountAppPct = $discountpct;
                                                                        }
                                                                    }
                                                                    
                                                                    if (strtoupper($items->DetailType) == 'SALESITEMLINEDETAIL') {
                                                                        if(isset($items->SalesItemLineDetail)){
                                                                            $ItemRef = $items->SalesItemLineDetail->ItemRef;
                                                                            $UnitPrice = $items->SalesItemLineDetail->UnitPrice;
                                                                            $Qty = $items->SalesItemLineDetail->Qty;
                                                                            $TaxCodeRef = $items->SalesItemLineDetail->TaxCodeRef;
                                                                        }
                                                                        
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Unit Price: " . $UnitPrice, 'r');
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Qty: " . $Qty, 'r');
                                                                        
                                                                        
                                                                        $product->getByErpCode($ItemRef);
                                                                        
                                                                        if ($product->code) {
                                                                            $measureunit->getByCode($product->measureunit);
                                                                        } else {
                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The Item does not exist on the platform", 'r');
                                                                        }
                                                                        
                                                                        $qty = $Qty;
                                                                        $unit = $UnitPrice;
                                                                        $amount = $ErpAmount;
                                                                        
                                                                        /**
                                                                         * Can we determine the DISCOUNT PERCENTAGE incase it is a line DISCOUNT provided?
                                                                         */
                                                                        if ($discountpct == 0 && $discount > 0) {
                                                                            $discountpct = $discount/$amount;
                                                                            $discount = 0;
                                                                        } else {
                                                                            $discount = 0;
                                                                        }
                                                                        
                                                                        $taxid = $this->util->getcreditnotetaxrate_v2($this->appsettings['DEFAULTINVOICEINDUSTRY'], $customer->type, $product->code, $customer->tin, $this->appsettings['OVERRIDE_TAXRATE_FLAG'], $this->appsettings['TAXPAYER_CHECK_FLAG']);
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The computed TAXID is " . $taxid, 'r');
                                                                        
                                                                        if (!$taxid) {
                                                                            $taxid = $this->appsettings['STANDARDTAXRATE'];
                                                                        }
                                                                        
                                                                        
                                                                        if ($taxid == $this->appsettings['DEEMEDTAXRATE']) {
                                                                            $deemedflag = 'YES';
                                                                        } else {
                                                                            $deemedflag = 'NO';
                                                                        }
                                                                        
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The final TAXID is " . $taxid, 'r');
                                                                        
                                                                        $tr = new taxrates($this->db);
                                                                        $tr->getByID($taxid);
                                                                        $taxcode = $tr->code;
                                                                        $taxname = $tr->name;
                                                                        $taxcategory = $tr->category;
                                                                        $taxdisplaycategory = $tr->displayCategoryCode;
                                                                        $taxdescription = $tr->description;
                                                                        $rate = $tr->rate? $tr->rate : 0;
                                                                        
                                                                        
                                                                        
                                                                        
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : unit: " . $unit, 'r');
                                                                        
                                                                        if (strtoupper(trim($pricevatinclusive)) == 'YES') {
                                                                            //Use the figures as they come from the ERP
                                                                            $total = ($qty * $unit);//??
                                                                            
                                                                            //$discount = ($discountpct/100) * $total; //discount is already calculated by QB
                                                                            
                                                                            /**
                                                                             * Modification Date: 2021-01-26
                                                                             * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                             * */
                                                                            //$gross = $total - $discount;
                                                                            $gross = $total;
                                                                            
                                                                            $discount = (-1) * $discount;
                                                                            
                                                                            $tax = ($gross/($rate + 1)) * $rate; //??
                                                                            
                                                                            $net = $gross - $tax;
                                                                        } elseif (strtoupper(trim($pricevatinclusive)) == 'NO') {
                                                                            //Manually calculate figures
                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Rebasing the prices", 'r');
                                                                            
                                                                            if ($rate > 0) {
                                                                                $unit = $unit * ($rate + 1);
                                                                            }
                                                                            
                                                                            $total = ($qty * $unit);//??
                                                                            
                                                                            //$discount = ($discountpct/100) * $total;
                                                                            
                                                                            /**
                                                                             * Modification Date: 2021-01-26
                                                                             * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                             * */
                                                                            //$gross = $total - $discount;
                                                                            $gross = $total;
                                                                            
                                                                            $discount = (-1) * $discount;
                                                                            
                                                                            $tax = ($gross/($rate + 1)) * $rate; //??
                                                                            
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
                                                                        
                                                                        /**
                                                                         * Author: frncslubanga@gmail.com
                                                                         * Modification Date: 2021-02-28
                                                                         * Description: Resolving EFRIS error code 1427 - goodsDetails-->item:Must be the same as the original invoice!Collection index:0
                                                                         */
                                                                        
                                                                        /*Reset the order number*/
                                                                        $ordernumber = NULL;
                                                                        
                                                                        try {
                                                                            $o_data = array ();
                                                                            $r = $this->db->exec(array('SELECT g.ordernumber "ordernumber" FROM tblgooddetails g JOIN tblinvoices i ON i.gooddetailgroupid = g.groupid AND i.einvoicenumber = "' . $oriinvoiceno . '" WHERE TRIM(g.itemcode) = "' . trim($product->code) . '" ORDER BY g.id ASC'));
                                                                            $this->logger->write($this->db->log(TRUE), 'r');
                                                                            
                                                                            foreach ( $r as $set ) {
                                                                                $o_data [] = $set;
                                                                            }
                                                                            
                                                                            /**
                                                                             * Author: frncslubanga@gmail.com
                                                                             * Modification Date: 2022-07-22
                                                                             * Description: Resolving EFRIS error code 1423 - goodsDetails-->orderNumber:Must be in ascending order!Collection index:8
                                                                             *              This usually happens when a product is listed more than one on the same creditnote.
                                                                             */
                                                                            $ordernumber = $o_data[0]['ordernumber'];
                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The order number for product " . trim($product->code) . " is: " . $ordernumber, 'r');
                                                                        } catch (Exception $e) {
                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The operation to retrieve the order number was not successful. The error messages is " . $e->getMessage(), 'r');
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
                                                                            'discountflag' => trim($discountflag) == 'NO'? '2' : '1',
                                                                            'deemedflag' => (strtoupper(trim($deemedflag)) == 'NO'? '2' : '1'),
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
                                                                        
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The TAXCODE is " . $taxcode, 'r');
                                                                        
                                                                        
                                                                        if ($this->vatRegistered == 'Y') {
                                                                            $taxes[] = array(
                                                                                'discountflag' => trim($discountflag) == 'NO'? '2' : '1',
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
                                                                }//foreach($elem->Line as $items)
                                                            }//if(isset($elem->Line))
                                                            
                                                            $this->logger->write("Invoice Controller : downloadErpCreditnotes() : Discount App Status: " . $discountAppStatus, 'r');
                                                            $this->logger->write("Invoice Controller : downloadErpCreditnotes() : Discount App Balance: " . $discountAppBalance, 'r');
                                                            $this->logger->write("Invoice Controller : downloadErpCreditnotes() : Discount App Percentage: " . $discountAppPct, 'r');
                                                            
                                                            if ($discountAppStatus == 1) {
                                                                $this->logger->write("Invoice Controller : downloadErpCreditnotes() : Applying Discounts", 'r');
                                                                $this->logger->write("Invoice Controller : downloadErpCreditnotes() : Customer Type " . $customer->type, 'r');
                                                                list($goods, $taxes) = $this->util->applyDiscount($goods, $taxes, $discountAppBalance, $customer->type, $customer->tin, NULL);
                                                            }
                                                            
                                                            if(isset($elem->TxnTaxDetail)){
                                                                $TxnTaxCodeRef = $elem->TxnTaxDetail->TxnTaxCodeRef;
                                                                $TotalTax = $elem->TxnTaxDetail->TotalTax;
                                                                
                                                                $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Tax Ref: " . $TxnTaxCodeRef, 'r');
                                                                
                                                                if(isset($elem->TxnTaxDetail->TaxLine)){
                                                                    $TaxAmount = $elem->TxnTaxDetail->TaxLine->Amount;
                                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Total Tax Amount: " . $TaxAmount, 'r');
                                                                    
                                                                    if(isset($elem->TxnTaxDetail->TaxLine->DetailType)){
                                                                        if (strtoupper($elem->TxnTaxDetail->TaxLine->DetailType) == 'TAXLINEDETAIL') {
                                                                            if(isset($elem->TxnTaxDetail->TaxLine->TaxLineDetail)){
                                                                                $TaxRateRef = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->TaxRateRef;
                                                                                $TaxPercentBased = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->PercentBased;
                                                                                $TaxPercent = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->TaxPercent;
                                                                                $NetAmountTaxable = $elem->TxnTaxDetail->TaxLine->TaxLineDetail->NetAmountTaxable;
                                                                            }
                                                                            
                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Tax Line Net Amount: " . $NetAmountTaxable, 'r');
                                                                        }
                                                                    }
                                                                }
                                                            }//if(isset($elem->TxnTaxDetail))
                                                            
                                                            if(isset($elem->CustomField)){
                                                                foreach($elem->CustomField as $fields){
                                                                    $FieldDefinitionId = $fields->DefinitionId;
                                                                    $FieldName = $fields->Name;
                                                                    $FieldType = $fields->Type;//StringType
                                                                    $FieldStringValue = $fields->StringValue;
                                                                    
                                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Customer Field Name: " . $FieldName, 'r');
                                                                }//foreach($elem->CustomField as $items)
                                                            }//if(isset($elem->CustomField))
                                                            
                                                            $creditnotedetails['operator'] = $this->f3->get('SESSION.username');
                                                            $creditnotedetails['currency'] = $this->util->getcurrency(trim($CurrencyRef));
                                                            $creditnotedetails['SyncToken'] = $SyncToken;
                                                            $creditnotedetails['issueddate'] = $TxnDate;
                                                            $creditnotedetails['issuedtime'] = $TxnDate;
                                                            $creditnotedetails['issueddatepdf'] = $TxnDate;
                                                            $creditnotedetails['itemcount'] = $itemcount;
                                                            
                                                            $creditnotedetails['netamount'] = $netamount;
                                                            $creditnotedetails['taxamount'] = $taxamount;
                                                            $creditnotedetails['grossamount'] = $grossamount;
                                                            $creditnotedetails['origrossamount'] = 0;
                                                            
                                                            if ($docType == trim($this->appsettings['CREDITMEMOERPDOCTYPE'])) {
                                                                $creditnotedetails['remarks'] = "The Credit Meomo DocNumber " . $DocNumber . " and Id " . $CreditnoteId . " uploaded using the QBO API";
                                                            } elseif ($docType == trim($this->appsettings['REFUNDRECEIPTERPDOCTYPE'])){
                                                                $creditnotedetails['remarks'] = "The Refund Receipt DocNumber " . $DocNumber . " and Id " . $CreditnoteId . " uploaded using the QBO API";
                                                            } else {
                                                                $creditnotedetails['remarks'] = "The Credit Memo DocNumber " . $DocNumber . " and Id " . $CreditnoteId . " uploaded using the QBO API";
                                                            }
                                                            
                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The Sync Token is " . $SyncToken, 'r');
                                                            
                                                            if ($CreditnoteId) {
                                                                $creditnote->getByErpId($CreditnoteId);
                                                                
                                                                if ($creditnote->dry()) {
                                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The creditnote does not exist", 'r');
                                                                    $inv_status = $this->util->createcreditnote($creditnotedetails, $goods, $taxes, $buyer, $this->f3->get('SESSION.id'));
                                                                    
                                                                    if ($inv_status) {
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The creditnote " . $DocNumber . " was created.", 'r');
                                                                    } else {
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The creditnote " . $DocNumber . " was NOT created.", 'r');
                                                                    }
                                                                } else {
                                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The creditnote exists", 'r');
                                                                    $creditnotedetails['id'] = $creditnote->id;
                                                                    $creditnotedetails['gooddetailgroupid'] = $creditnote->gooddetailgroupid;
                                                                    $creditnotedetails['taxdetailgroupid'] = $creditnote->taxdetailgroupid;
                                                                    $creditnotedetails['paymentdetailgroupid'] = $creditnote->paymentdetailgroupid;
                                                                    
                                                                    if ($creditnote->einvoiceid) {
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The creditnote " . $DocNumber . " is already fiscalized.", 'r');
                                                                        
                                                                    } else {
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The creditnote " . $DocNumber . " is NOT fiscalized.", 'r');
                                                                        
                                                                        
                                                                        $inv_status = $this->util->updatecreditnote($creditnotedetails, $goods, $taxes, $buyer, $this->f3->get('SESSION.id'));
                                                                        
                                                                        if ($inv_status) {
                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The creditnote " . $DocNumber . " was updated.", 'r');
                                                                        } else {
                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The creditnote " . $DocNumber . " was NOT updated.", 'r');
                                                                        }
                                                                        
                                                                    }
                                                                }
                                                            } else {
                                                                $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The creditnote has no Id.", 'r');
                                                            }
                                                            
                                                        }
                                                    } else {
                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The associated original invoice number was not supplied", 'r');
                                                    }
                                                } catch (Exception $e) {
                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : There was an error when processing creditnote " . $elem->DocNumber . ". The error is " . $e->getMessage(), 'r');
                                                }
                                            }
                                        }
                                    } else {
                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The operation to download ERP creditnotes did not return records.", 'r');
                                    }
                                }
                                
                                $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The operation to download ERP creditnotes was successful.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP creditnotes by " . $this->f3->get('SESSION.username') . " was successful");
                                self::$systemalert = "The operation to download ERP creditnotes by " . $this->f3->get('SESSION.username') . " was successful.";
                            } else {
                                $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The operation to download ERP creditnotes was not successful. Please connect to ERP first.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                self::$systemalert = "The operation to download ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                            }
                            
                        } catch (Exception $e) {
                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The operation to download ERP creditnotes was not successful. The error is: " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to download ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                        }
                    } else {
                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The integrated ERP is unknown.", 'r');
                        self::$systemalert = "Sorry. The integrated ERP is unknown.";
                    }
                } else {
                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : We are unable to indentify the currently integrated ERP.", 'r');
                    self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                }
            }
        } else {
            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
    
    /**
     *	@name fetchErpCreditnote
     *  @desc download creditnotes from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function fetchErpCreditnote(){
        $operation = NULL; //tblevents
        $permission = 'SYNCCREDITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            $id = $this->f3->get('POST.erpdownloadcreditnoteid');
            $creditnote = new creditnotes($this->db);
            $creditnote->getByID($id);
            $this->logger->write("Product Controller : fetchErpCreditnote() : The creditnote id is " . $this->f3->get('POST.erpdownloadcreditnoteid'), 'r');
            
            if ($id) {
                
                if ($creditnote->erpcreditnoteid) {
                    if ($this->platformMode == 'ERP') {
                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                        self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
                    } else {
                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The platform is integrated.", 'r');
                        
                        if ($this->integratedErp) {
                            /**
                             * Check on integrated ERP type
                             */
                            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                            
                            if (strtoupper($this->integratedErp) == 'QBO') {
                                $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The integrated ERP is Quicbooks Online.", 'r');
                                
                                $docType = $creditnote->docTypeCode;
                                $docType = empty($docType)? $this->appsettings['CREDITMEMOERPDOCTYPE'] : trim($docType);
                                
                                $qry = '';
                                
                                if ($docType == trim($this->appsettings['CREDITMEMOERPDOCTYPE'])) {
                                    $qry = 'SELECT * FROM Creditnote';
                                } elseif ($docType == trim($this->appsettings['REFUNDRECEIPTERPDOCTYPE'])){
                                    $qry = 'SELECT * FROM SalesReceipt';
                                } else {
                                    $qry = 'SELECT * FROM Creditnote';
                                }
                                
                                if ($creditnote->erpcreditnoteno) {
                                    $qry = $qry . " Where DocNumber = '" . $creditnote->erpcreditnoteno . "'";
                                    
                                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The query is: " . $qry, 'r');
                                    
                                    
                                    try {
                                        if ($this->appsettings['QBACCESSTOKEN'] !== null) {
                                        //if ($this->f3->get('SESSION.sessionAccessToken') !== null) {
                                            // Create SDK instance
                                            $authMode = $this->appsettings['QBAUTH_MODE'];
                                            $ClientID = $this->appsettings['QBCLIENT_ID'];
                                            $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                            $baseUrl = $this->appsettings['QBBASE_URL'];
                                            $QBORealmID = $this->appsettings['QBREALMID'];
                                            
                                            $accessToken = $this->f3->get('SESSION.sessionAccessToken');
                                            
                                            $dataService = DataService::Configure(array(
                                                'auth_mode' => $authMode,
                                                'ClientID' => $ClientID,
                                                'ClientSecret' =>  $ClientSecret,
                                                'baseUrl' => $baseUrl,
                                                'refreshTokenKey' => $accessToken->getRefreshToken(),
                                                'QBORealmID' => $QBORealmID,
                                                'accessTokenKey' => $accessToken->getAccessToken()
                                            ));
                                            
                                            $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                            $dataService->throwExceptionOnError(true);
                                            
                                            $creditnotes = $dataService->Query($qry);
                                            //$creditnotes = $dataService->FindbyId('creditnote', $creditnote->erpcreditnoteno);
                                            
                                            $error = $dataService->getLastError();
                                            
                                            if ($error) {
                                                $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The operation to fetch ERP creditnotes was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                                                self::$systemalert = "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful.";
                                            }
                                            else {
                                                if(isset($creditnotes)){
                                                    if(!empty($creditnotes) && sizeof($creditnotes) == 1){
                                                        $creditnotes = current($creditnotes);
                                                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Creditnote Number: " . $creditnotes->DocNumber, 'r');
                                                        $creditnote = new creditnotes($this->db);
                                                        $CreditnoteId = $creditnotes->Id;
                                                        
                                                        if ($CreditnoteId) {
                                                            $creditnote->getByErpId($CreditnoteId);
                                                            
                                                            if ($creditnote->dry()) {
                                                                $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The creditnote does not exist", 'r');
                                                                
                                                            } else {
                                                                $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The creditnote exists", 'r');
                                                                
                                                                if ($creditnote->einvoiceid) {
                                                                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The creditnote " . $creditnotes->DocNumber . " is already fiscalized.", 'r');
                                                                    
                                                                } else {
                                                                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The creditnote " . $creditnotes->DocNumber . " is NOT fiscalized.", 'r');
                                                                    
                                                                    
                                                                    //Go Ahead and replace/update ERP details of this creditnote.
                                                                    $tcsdetails = new tcsdetails($this->db);
                                                                    $tcsdetails->getByID($this->appsettings['EFRIS_TCS_RECORD_ID']);
                                                                    
                                                                    $companydetails = new organisations($this->db);
                                                                    $companydetails->getByID($this->appsettings['SELLER_RECORD_ID']);
                                                                    
                                                                    $devicedetails = new devices($this->db);
                                                                    $devicedetails->getByID($this->appsettings['EFRIS_DEVICE_RECORD_ID']);
                                                                    
                                                                    $customer = new customers($this->db);
                                                                    
                                                                    $goods = array();
                                                                    $taxes = array();
                                                                    
                                                                    $deemedflag = 'NO';
                                                                    $discountflag = 'NO';
                                                                    
                                                                    $pricevatinclusive = empty($this->appsettings['PRICEVATINCLUSIVE'])? 'NO' : strtoupper($this->appsettings['PRICEVATINCLUSIVE']);//No
                                                                    
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
                                                                        'status' => $this->appsettings['ACTIVECUSTOMERSTATUSID'],
                                                                    );
                                                                    
                                                                    $creditnotedetails = array(
                                                                        'id' => NULL,
                                                                        'gooddetailgroupid' => NULL,
                                                                        'taxdetailgroupid' => NULL,
                                                                        'paymentdetailgroupid' => NULL,
                                                                        'erpcreditnoteid' => NULL,
                                                                        'erpcreditnoteno' => NULL,
                                                                        'erpinvoiceid' => NULL,
                                                                        'erpinvoiceno' => NULL,
                                                                        'antifakecode' => NULL,
                                                                        'deviceno' => trim($devicedetails->deviceno),
                                                                        'issueddate' => date('Y-m-d'),
                                                                        'issuedtime' => date('Y-m-d H:i:s'),
                                                                        'operator' => NULL,
                                                                        'currency' => NULL,
                                                                        'oriinvoiceid' => NULL,
                                                                        'oriinvoiceno' => NULL,
                                                                        'invoicetype' => "2",
                                                                        'invoicekind' => ($this->vatRegistered == 'Y')? "1" : "2",
                                                                        'datasource' => $this->appsettings['DEFAULTDATASOURCE'],
                                                                        'invoiceindustrycode' => $this->appsettings['DEFAULTINVOICEINDUSTRY'],
                                                                        'einvoiceid' => NULL,
                                                                        'einvoicenumber' => NULL,
                                                                        'einvoicedatamatrixcode' => NULL,
                                                                        'isbatch' => NULL,
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
                                                                        'issueddatepdf' => date('Y-m-d H:i:s'),
                                                                        'grossamountword' => NULL,
                                                                        'isinvalid' => 0,
                                                                        'isrefund' => 0,
                                                                        'vchtype' => "Credit Note",
                                                                        'vchtypename' => "Credit Note",
                                                                        'reasoncode' => NULL,
                                                                        'reason' => NULL,
                                                                        'referenceno' => NULL,
                                                                        'approvestatus' => NULL,
                                                                        'creditnoteapplicationid' => NULL,
                                                                        'refundinvoiceno' => NULL,
                                                                        'applicationtime' => date('Y-m-d H:i:s'),
                                                                        'invoiceapplycategorycode' => '101', /*101-Credit Note*/
                                                                        'SyncToken' => NULL,
                                                                        'docTypeCode' => $docType
                                                                    );
                                                                    
                                                                    $discountAppStatus = 0;
                                                                    $discountAppBalance = 0;
                                                                    $discountAppPct = 0;
                                                                    
                                                                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : PrivateNote: " . $creditnotes->PrivateNote, 'r');
                                                                    $InvStatus = $creditnotes->PrivateNote;
                                                                    
                                                                    //Original Invoice Id.
                                                                    $orivchnumber = $creditnotes->InvoiceRef;
                                                                    
                                                                    if(trim($orivchnumber) !== '' || ! empty(trim($orivchnumber))) {
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The associated original invoice was supplied", 'r');
                                                                        
                                                                        $orig_inv = new DB\SQL\Mapper($this->db, 'tblinvoices');
                                                                        $orig_inv->load(array('TRIM(erpinvoiceid)=?', $orivchnumber));
                                                                        //$this->logger->write($this->db->log(TRUE), 'r');
                                                                        if ($orig_inv->dry()) {
                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : There associated original invoice does not exist in the database", 'r');
                                                                        } else {
                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : There is an associated original invoice", 'r');
                                                                            $oriinvoiceid = $orig_inv->einvoiceid;
                                                                            $oriinvoiceno = $orig_inv->einvoicenumber;
                                                                            
                                                                            /**
                                                                             * Author: frncslubanga@gmail.com
                                                                             * Date: 2021-02-28
                                                                             * Description: Resolve EFRIS error code 2783: oriInvoiceNo: cannot be empty!
                                                                             *
                                                                             *
                                                                             * 1. Check if oriinvoiceid is empty
                                                                             * 2. If oriinvoiceid is NOT empty, then ignore
                                                                             * 3. If it is empty, then query EFRIS and retrieve it
                                                                             * 4. Update the eTW record of this invoice
                                                                             */
                                                                            
                                                                            if(trim($oriinvoiceid) == '' || empty(trim($oriinvoiceid))) {
                                                                                $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The oriinvoiceid is empty", 'r');
                                                                                
                                                                                if(trim($oriinvoiceno) == '' || empty(trim($oriinvoiceno))) {
                                                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The oriinvoiceno is empty", 'r');
                                                                                } else {
                                                                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The oriinvoiceno is NOT empty", 'r');
                                                                                    $i_data = $this->util->downloadinvoice($this->f3->get('SESSION.id'), $orig_inv->id);
                                                                                    $i_data = json_decode($i_data, true);
                                                                                    
                                                                                    /*START OF INVOICE BLOCK*/
                                                                                    if (isset($i_data['basicInformation'])){
                                                                                        $TempInvoiceId = $i_data['basicInformation']['invoiceId']; //3257429764295992735
                                                                                        $TempInvoiceNo = $i_data['basicInformation']['invoiceNo']; //3120012276043
                                                                                        
                                                                                        if (trim($TempInvoiceNo) == trim($oriinvoiceno)) {
                                                                                            $oriinvoiceid = $TempInvoiceId;
                                                                                        }
                                                                                    }
                                                                                    /*END INVOICE BLOCK*/
                                                                                    
                                                                                    try{
                                                                                        $this->db->exec(array('UPDATE tblinvoices SET einvoiceid = "' . $oriinvoiceid . '", modifieddt = NOW(), modifiedby = ' . $this->userid . ' WHERE einvoicenumber = "' . $oriinvoiceno . '"'));
                                                                                        $this->logger->write($this->db->log(TRUE), 'r');
                                                                                    } catch (Exception $e) {
                                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : Failed to update the table tblinvoices. The error message is " . $e->getMessage(), 'r');
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The oriinvoiceid is not empty", 'r');
                                                                            }
                                                                            
                                                                            $reasoncode = '105';
                                                                            $reason = 'Credit Memo';
                                                                            //INSERT QB Code here.
                                                                            
                                                                            $creditnotedetails['reasoncode'] = $reasoncode;
                                                                            $creditnotedetails['reason'] = $reason;
                                                                            
                                                                            $CustomerRef = $creditnotes->CustomerRef;
                                                                            $DocNumber = $creditnotes->DocNumber;
                                                                            $CurrencyRef = $creditnotes->CurrencyRef;
                                                                            $TxnDate = $creditnotes->TxnDate;
                                                                            $CreditnoteId = $creditnotes->Id;
                                                                            $SyncToken = $creditnotes->SyncToken;
                                                                            $TxnDate = $creditnotes->TxnDate;
                                                                            
                                                                            $creditnotedetails['erpcreditnoteid'] = $CreditnoteId;
                                                                            $creditnotedetails['erpcreditnoteno'] = $DocNumber;
                                                                            
                                                                            if ($CustomerRef) {
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
                                                                                    
                                                                                    
                                                                                    $creditnotedetails['buyerid'] = $customer->id;
                                                                                } else {
                                                                                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The customer Id " . $CustomerRef . " does not exist on the platform", 'r');
                                                                                }
                                                                                
                                                                                
                                                                            }
                                                                            
                                                                            if(isset($creditnotes->Line)){
                                                                                foreach($creditnotes->Line as $items){
                                                                                    $LineId = $items->Id;
                                                                                    $LineNum = $items->LineNum;
                                                                                    $Description = $items->Description;
                                                                                    $ErpAmount = $items->Amount;
                                                                                    $DetailType = $items->DetailType;
                                                                                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Line Description: " . $Description, 'r');
                                                                                    
                                                                                    if (strtoupper($items->DetailType) == 'DISCOUNTLINEDETAIL') {
                                                                                        if(isset($items->DiscountLineDetail)){
                                                                                            $PercentBased = $items->DiscountLineDetail->PercentBased;//true/false
                                                                                            $DiscountPercent = $items->DiscountLineDetail->DiscountPercent;
                                                                                        }
                                                                                        
                                                                                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Discount Percent: " . $PercentBased, 'r');
                                                                                        $discount = empty($ErpAmount)? 0 : (float)$ErpAmount;
                                                                                        $discountpct = empty($DiscountPercent)? 0 : (float)$DiscountPercent;
                                                                                        
                                                                                        if (!empty($ErpAmount)){
                                                                                            $discountAppStatus = 1;
                                                                                            $discountAppBalance = $ErpAmount;
                                                                                            $discountAppPct = $discountpct;
                                                                                        }
                                                                                    }
                                                                                    
                                                                                    if (strtoupper($items->DetailType) == 'SALESITEMLINEDETAIL') {
                                                                                        if(isset($items->SalesItemLineDetail)){
                                                                                            $ItemRef = $items->SalesItemLineDetail->ItemRef;
                                                                                            $UnitPrice = $items->SalesItemLineDetail->UnitPrice;
                                                                                            $Qty = $items->SalesItemLineDetail->Qty;
                                                                                            $TaxCodeRef = $items->SalesItemLineDetail->TaxCodeRef;
                                                                                        }
                                                                                        
                                                                                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Sales Line Item Ref: " . $ItemRef, 'r');
                                                                                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Sales Line Unit Price Ref: " . $UnitPrice, 'r');
                                                                                        
                                                                                        
                                                                                        $product->getByErpCode($ItemRef);
                                                                                        
                                                                                        if ($product->code) {
                                                                                            $measureunit->getByCode($product->measureunit);
                                                                                        } else {
                                                                                            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The Item does not exist on the platform", 'r');
                                                                                        }
                                                                                        
                                                                                        $qty = $Qty;
                                                                                        $unit = $UnitPrice;
                                                                                        $amount = $ErpAmount;
                                                                                        
                                                                                        /**
                                                                                         * Can we determine the DISCOUNT PERCENTAGE incase it is a line DISCOUNT provided?
                                                                                         */
                                                                                        if ($discountpct == 0 && $discount > 0) {
                                                                                            $discountpct = $discount/$amount;
                                                                                            $discount = 0;
                                                                                        } else {
                                                                                            $discount = 0;
                                                                                        }
                                                                                        
                                                                                        $taxid = $this->util->getcreditnotetaxrate_v2($this->appsettings['DEFAULTINVOICEINDUSTRY'], $customer->type, $product->code, $customer->tin, $this->appsettings['OVERRIDE_TAXRATE_FLAG'], $this->appsettings['TAXPAYER_CHECK_FLAG']);
                                                                                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The computed TAXID is " . $taxid, 'r');
                                                                                        
                                                                                        if (!$taxid) {
                                                                                            $taxid = $this->appsettings['STANDARDTAXRATE'];
                                                                                        }
                                                                                        
                                                                                        
                                                                                        if ($taxid == $this->appsettings['DEEMEDTAXRATE']) {
                                                                                            $deemedflag = 'YES';
                                                                                        } else {
                                                                                            $deemedflag = 'NO';
                                                                                        }
                                                                                        
                                                                                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The final TAXID is " . $taxid, 'r');
                                                                                        
                                                                                        $tr = new taxrates($this->db);
                                                                                        $tr->getByID($taxid);
                                                                                        $taxcode = $tr->code;
                                                                                        $taxname = $tr->name;
                                                                                        $taxcategory = $tr->category;
                                                                                        $taxdisplaycategory = $tr->displayCategoryCode;
                                                                                        $taxdescription = $tr->description;
                                                                                        $rate = $tr->rate? $tr->rate : 0;
                                                                                        
                                                                                        
                                                                                        if (strtoupper(trim($pricevatinclusive)) == 'YES') {
                                                                                            //Use the figures as they come from the ERP
                                                                                            $total = ($qty * $unit);//??
                                                                                            
                                                                                            //$discount = ($discountpct/100) * $total; //discount is already calculated by QB
                                                                                            
                                                                                            /**
                                                                                             * Modification Date: 2021-01-26
                                                                                             * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                                             * */
                                                                                            //$gross = $total - $discount;
                                                                                            $gross = $total;
                                                                                            
                                                                                            $discount = (-1) * $discount;
                                                                                            
                                                                                            $tax = ($gross/($rate + 1)) * $rate; //??
                                                                                            
                                                                                            $net = $gross - $tax;
                                                                                        } elseif (strtoupper(trim($pricevatinclusive)) == 'NO') {
                                                                                            //Manually calculate figures
                                                                                            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Rebasing the prices", 'r');
                                                                                            
                                                                                            if ($rate > 0) {
                                                                                                $unit = $unit * ($rate + 1);
                                                                                            }
                                                                                            
                                                                                            $total = ($qty * $unit);//??
                                                                                            
                                                                                            //$discount = ($discountpct/100) * $total;
                                                                                            
                                                                                            /**
                                                                                             * Modification Date: 2021-01-26
                                                                                             * Description: Resolving error code 2776 - goodsDetails-->tax: Tax calculation error!Collection index:0
                                                                                             * */
                                                                                            //$gross = $total - $discount;
                                                                                            $gross = $total;
                                                                                            
                                                                                            $discount = (-1) * $discount;
                                                                                            
                                                                                            $tax = ($gross/($rate + 1)) * $rate; //??
                                                                                            
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
                                                                                        
                                                                                        /**
                                                                                         * Author: frncslubanga@gmail.com
                                                                                         * Modification Date: 2021-02-28
                                                                                         * Description: Resolving EFRIS error code 1427 - goodsDetails-->item:Must be the same as the original invoice!Collection index:0
                                                                                         */
                                                                                        
                                                                                        /*Reset the order number*/
                                                                                        $ordernumber = NULL;
                                                                                        
                                                                                        try {
                                                                                            $o_data = array ();
                                                                                            $r = $this->db->exec(array('SELECT g.ordernumber "ordernumber" FROM tblgooddetails g JOIN tblinvoices i ON i.gooddetailgroupid = g.groupid AND i.einvoicenumber = "' . $oriinvoiceno . '" WHERE TRIM(g.itemcode) = "' . trim($product->code) . '" ORDER BY g.id ASC'));
                                                                                            $this->logger->write($this->db->log(TRUE), 'r');
                                                                                            
                                                                                            foreach ( $r as $set ) {
                                                                                                $o_data [] = $set;
                                                                                            }
                                                                                            
                                                                                            /**
                                                                                             * Author: frncslubanga@gmail.com
                                                                                             * Modification Date: 2022-07-22
                                                                                             * Description: Resolving EFRIS error code 1423 - goodsDetails-->orderNumber:Must be in ascending order!Collection index:8
                                                                                             *              This usually happens when a product is listed more than one on the same creditnote.
                                                                                             */
                                                                                            $ordernumber = $o_data[0]['ordernumber'];
                                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The order number for product " . trim($product->code) . " is: " . $ordernumber, 'r');
                                                                                        } catch (Exception $e) {
                                                                                            $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The operation to retrieve the order number was not successful. The error messages is " . $e->getMessage(), 'r');
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
                                                                                            'discountflag' => trim($discountflag) == 'NO'? '2' : '1',
                                                                                            'deemedflag' => (strtoupper(trim($deemedflag)) == 'NO'? '2' : '1'),
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
                                                                                        
                                                                                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The TAXCODE is " . $taxcode, 'r');
                                                                                        
                                                                                        
                                                                                        if ($this->vatRegistered == 'Y') {
                                                                                            $taxes[] = array(
                                                                                                'discountflag' => trim($discountflag) == 'NO'? '2' : '1',
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
                                                                                }//foreach($creditnotes->Line as $items)
                                                                            }//if(isset($creditnotes->Line))
                                                                            
                                                                            $this->logger->write("Invoice Controller : fetchErpCreditnote() : Discount App Status: " . $discountAppStatus, 'r');
                                                                            $this->logger->write("Invoice Controller : fetchErpCreditnote() : Discount App Balance: " . $discountAppBalance, 'r');
                                                                            $this->logger->write("Invoice Controller : fetchErpCreditnote() : Discount App Percentage: " . $discountAppPct, 'r');
                                                                            
                                                                            if ($discountAppStatus == 1) {
                                                                                $this->logger->write("Invoice Controller : fetchErpCreditnote() : Applying Discounts", 'r');
                                                                                $this->logger->write("Invoice Controller : fetchErpCreditnote() : Customer Type " . $customer->type, 'r');
                                                                                list($goods, $taxes) = $this->util->applyDiscount($goods, $taxes, $discountAppBalance, $customer->type, $customer->tin, NULL);
                                                                            }
                                                                            
                                                                            if(isset($creditnotes->TxnTaxDetail)){
                                                                                $TxnTaxCodeRef = $creditnotes->TxnTaxDetail->TxnTaxCodeRef;
                                                                                $TotalTax = $creditnotes->TxnTaxDetail->TotalTax;
                                                                                
                                                                                $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Tax Ref: " . $TxnTaxCodeRef, 'r');
                                                                                
                                                                                if(isset($creditnotes->TxnTaxDetail->TaxLine)){
                                                                                    $TaxAmount = $creditnotes->TxnTaxDetail->TaxLine->Amount;
                                                                                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Total Tax Amount: " . $TaxAmount, 'r');
                                                                                    
                                                                                    if(isset($creditnotes->TxnTaxDetail->TaxLine->DetailType)){
                                                                                        if (strtoupper($creditnotes->TxnTaxDetail->TaxLine->DetailType) == 'TAXLINEDETAIL') {
                                                                                            if(isset($creditnotes->TxnTaxDetail->TaxLine->TaxLineDetail)){
                                                                                                $TaxRateRef = $creditnotes->TxnTaxDetail->TaxLine->TaxLineDetail->TaxRateRef;
                                                                                                $TaxPercentBased = $creditnotes->TxnTaxDetail->TaxLine->TaxLineDetail->PercentBased;
                                                                                                $TaxPercent = $creditnotes->TxnTaxDetail->TaxLine->TaxLineDetail->TaxPercent;
                                                                                                $NetAmountTaxable = $creditnotes->TxnTaxDetail->TaxLine->TaxLineDetail->NetAmountTaxable;
                                                                                            }
                                                                                            
                                                                                            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Tax Line Net Amount: " . $NetAmountTaxable, 'r');
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }//if(isset($creditnotes->TxnTaxDetail))
                                                                            
                                                                            if(isset($creditnotes->CustomField)){
                                                                                foreach($creditnotes->CustomField as $fields){
                                                                                    $FieldDefinitionId = $fields->DefinitionId;
                                                                                    $FieldName = $fields->Name;
                                                                                    $FieldType = $fields->Type;//StringType
                                                                                    $FieldStringValue = $fields->StringValue;
                                                                                    
                                                                                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : Customer Field Name: " . $FieldName, 'r');
                                                                                }//foreach($creditnotes->CustomField as $items)
                                                                            }//if(isset($creditnotes->CustomField))
                                                                            
                                                                            $creditnotedetails['operator'] = $this->f3->get('SESSION.username');
                                                                            $creditnotedetails['currency'] = $this->util->getcurrency(trim($CurrencyRef));
                                                                            $creditnotedetails['SyncToken'] = $SyncToken;
                                                                            $creditnotedetails['issueddate'] = $TxnDate;
                                                                            $creditnotedetails['issuedtime'] = $TxnDate;
                                                                            $creditnotedetails['issueddatepdf'] = $TxnDate;
                                                                            $creditnotedetails['itemcount'] = $itemcount;
                                                                            
                                                                            $creditnotedetails['netamount'] = $netamount;
                                                                            $creditnotedetails['taxamount'] = $taxamount;
                                                                            $creditnotedetails['grossamount'] = $grossamount;
                                                                            $creditnotedetails['origrossamount'] = 0;
                                                                            
                                                                            if ($docType == trim($this->appsettings['CREDITMEMOERPDOCTYPE'])) {
                                                                                $creditnotedetails['remarks'] = "The Credit Meomo DocNumber " . $DocNumber . " and Id " . $CreditnoteId . " uploaded using the QBO API";
                                                                            } elseif ($docType == trim($this->appsettings['REFUNDRECEIPTERPDOCTYPE'])){
                                                                                $creditnotedetails['remarks'] = "The Refund Receipt DocNumber " . $DocNumber . " and Id " . $CreditnoteId . " uploaded using the QBO API";
                                                                            } else {
                                                                                $creditnotedetails['remarks'] = "The Credit Memo DocNumber " . $DocNumber . " and Id " . $CreditnoteId . " uploaded using the QBO API";
                                                                            }
                                                                            
                                                                            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The Sync Token is " . $SyncToken, 'r');
                                                                            
                                                                            
                                                                            $creditnotedetails['id'] = $creditnote->id;
                                                                            $creditnotedetails['gooddetailgroupid'] = $creditnote->gooddetailgroupid;
                                                                            $creditnotedetails['taxdetailgroupid'] = $creditnote->taxdetailgroupid;
                                                                            $creditnotedetails['paymentdetailgroupid'] = $creditnote->paymentdetailgroupid;
                                                                            
                                                                            $inv_status = $this->util->updatecreditnote($creditnotedetails, $goods, $taxes, $buyer, $this->f3->get('SESSION.id'));
                                                                            
                                                                            if ($inv_status) {
                                                                                $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The creditnote " . $DocNumber . " was created.", 'r');
                                                                            } else {
                                                                                $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The creditnote " . $DocNumber . " was NOT created.", 'r');
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The associated original invoice number was not supplied", 'r');
                                                                    }
                                                                }
                                                            }
                                                            
                                                            
                                                            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The operation to fetch ERP creditnotes was successful.", 'r');
                                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " was successful");
                                                            self::$systemalert = "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " was successful.";
                                                        } else {
                                                            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The creditnote has no Id.", 'r');
                                                            
                                                            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The operation to fetch ERP creditnotes was not successful.", 'r');
                                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                                                            self::$systemalert = "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful.";
                                                        }
                                                    } else {
                                                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The operation to fetch ERP creditnotes did not return any records", 'r');
                                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " did not return any records");
                                                        self::$systemalert = "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " did not return any records.";
                                                    }
                                                } else {
                                                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The operation to fetch ERP creditnotes did not return any records", 'r');
                                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " did not return any records");
                                                    self::$systemalert = "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " did not return any records.";
                                                }
                                            }
                                            
                                        } else {
                                            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The operation to fetch ERP creditnotes was not successful. Please connect to ERP first.", 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                            self::$systemalert = "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                                        }
                                    } catch (Exception $e) {
                                        $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The operation to fetch ERP creditnotes was not successful. The error is: " . $e->getMessage(), 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                                        self::$systemalert = "The operation to fetch ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                                    }
                                } else {
                                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The creditnote does not have a Document Number.", 'r');
                                    self::$systemalert = "Sorry. The creditnote does not have a Document Number. Please use the general download option.";
                                }
                            } else {
                                $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The integrated ERP is unknown.", 'r');
                                self::$systemalert = "Sorry. The integrated ERP is unknown.";
                            }
                        } else {
                            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : We are unable to indentify the currently integrated ERP.", 'r');
                            self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                        }
                    }
                } else {
                    $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The creditnote was not created by the ERP.", 'r');
                    self::$systemalert = "Sorry. The creditnote was not created by the ERP.";
                    
                    $this->f3->set('systemalert', self::$systemalert);
                    self::index();
                }
                
            } else {
                $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The creditnote was not specified.", 'r');
                self::$systemalert = "Sorry. The creditnote was not specified.";
                
                $this->f3->set('systemalert', self::$systemalert);
                self::index();
            }
        } else {
            $this->logger->write("Creditnote Controller : fetchErpCreditnote() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    
    
    
    /**
     *	@name updateErpCreditnote
     *  @desc download creditnotes from an ERP
     *	@return NULL
     *	@param NULL
     **/
    function updateErpCreditnote(){
        $operation = NULL; //tblevents
        $permission = 'SYNCCREDITNOTES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Creditnote Controller : updateErpCreditnote() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            $id = $this->f3->get('POST.erpupdatecreditnoteid');
            $creditnote = new creditnotes($this->db);
            $creditnote->getByID($id);
            $this->logger->write("Product Controller : updateErpCreditnote() : The creditnote id is " . $this->f3->get('POST.erpupdatecreditnoteid'), 'r');
            
            if ($id) {
                
                if ($creditnote->erpcreditnoteid) {
                    if ($this->platformMode == 'ERP') {
                        $this->logger->write("Creditnote Controller : updateErpCreditnote() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                        self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
                    } else {
                        $this->logger->write("Creditnote Controller : updateErpCreditnote() : The platform is integrated.", 'r');
                        
                        if ($this->integratedErp) {
                            /**
                             * Check on integrated ERP type
                             */
                            $this->logger->write("Creditnote Controller : updateErpCreditnote() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                            
                            if (strtoupper($this->integratedErp) == 'QBO') {
                                $this->logger->write("Creditnote Controller : updateErpCreditnote() : The integrated ERP is Quicbooks Online.", 'r');
                                
                                $docType = $creditnote->docTypeCode;
                                $docType = empty($docType)? $this->appsettings['CREDITMEMOERPDOCTYPE'] : trim($docType);
                                
                                $qry = '';
                                
                                if ($docType == trim($this->appsettings['CREDITMEMOERPDOCTYPE'])) {
                                    $qry = 'SELECT * FROM Creditnote';
                                } elseif ($docType == trim($this->appsettings['REFUNDRECEIPTERPDOCTYPE'])){
                                    $qry = 'SELECT * FROM SalesReceipt';
                                } else {
                                    $qry = 'SELECT * FROM Creditnote';
                                }
                                
                                if ($creditnote->erpcreditnoteno) {
                                    $qry = $qry . " Where DocNumber = '" . $creditnote->erpcreditnoteno . "'";
                                    
                                    $this->logger->write("Creditnote Controller : downloadErpCreditnotes() : The query is: " . $qry, 'r');
                                    
                                    
                                    try {
                                        if ($this->f3->get('SESSION.sessionAccessToken') !== null) {
                                            // Create SDK instance
                                            $authMode = $this->appsettings['QBAUTH_MODE'];
                                            $ClientID = $this->appsettings['QBCLIENT_ID'];
                                            $ClientSecret = $this->appsettings['QBCLIENT_SECRET'];
                                            $baseUrl = $this->appsettings['QBBASE_URL'];
                                            $QBORealmID = $this->appsettings['QBREALMID'];
                                            
                                            $accessToken = $this->f3->get('SESSION.sessionAccessToken');
                                            
                                            $dataService = DataService::Configure(array(
                                                'auth_mode' => $authMode,
                                                'ClientID' => $ClientID,
                                                'ClientSecret' =>  $ClientSecret,
                                                'baseUrl' => $baseUrl,
                                                'refreshTokenKey' => $accessToken->getRefreshToken(),
                                                'QBORealmID' => $QBORealmID,
                                                'accessTokenKey' => $accessToken->getAccessToken()
                                            ));
                                            
                                            $dataService->setLogLocation($this->appsettings['QBLOG_DIR']);
                                            $dataService->throwExceptionOnError(true);
                                            
                                            $creditnotes = $dataService->Query($qry);
                                            
                                            $error = $dataService->getLastError();
                                            
                                            if ($error) {
                                                $this->logger->write("Creditnote Controller : updateErpCreditnote() : The operation to update ERP creditnotes was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                                                self::$systemalert = "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful.";
                                            } else {
                                                if(isset($creditnotes)){
                                                    if(!empty($creditnotes) && sizeof($creditnotes) == 1){
                                                        $theCreditnote = current($creditnotes);
                                                        
                                                        $this->logger->write("Creditnote Controller : updateErpCreditnote() : The Sync Token is " . $theCreditnote->SyncToken, 'r');
                                                        $this->logger->write("Creditnote Controller : updateErpCreditnote() : The DocNumber is " . $theCreditnote->DocNumber, 'r');
                                                        
                                                        
                                                        if ($docType == trim($this->appsettings['CREDITMEMOERPDOCTYPE'])) {
                                                            $updatedCreditnote = CreditMemo::update($theCreditnote, [
                                                                "sparse" => true,
                                                                "SyncToken" => $theCreditnote->SyncToken,
                                                                "CustomField" => [
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], //Fiscal Doc. Num
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $creditnote->einvoicenumber
                                                                    ],
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], //Verification Co
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $creditnote->antifakecode
                                                                    ],
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOAPPROVALSTATUSDEFINITIONID'], //Approval Status
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $this->util->decodeapprovestatus($creditnote->approvestatus)
                                                                    ]
                                                                ]
                                                            ]);
                                                        } elseif ($docType == trim($this->appsettings['REFUNDRECEIPTERPDOCTYPE'])){
                                                            $updatedCreditnote = RefundReceipt::update($theCreditnote, [
                                                                "sparse" => true,
                                                                "SyncToken" => $theCreditnote->SyncToken,
                                                                "CustomField" => [
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], //Fiscal Doc. Num
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $creditnote->einvoicenumber
                                                                    ],
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], //Verification Co
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $creditnote->antifakecode
                                                                    ],
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOAPPROVALSTATUSDEFINITIONID'], //Approval Status
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $this->util->decodeapprovestatus($creditnote->approvestatus)
                                                                    ]
                                                                ]
                                                            ]);
                                                        } elseif($docType == trim($this->appsettings['CREDITNOTEERPDOCTYPE'])) {
                                                            ;
                                                        }else {
                                                            $updatedCreditnote = CreditMemo::update($theCreditnote, [
                                                                "sparse" => true,
                                                                "SyncToken" => $theCreditnote->SyncToken,
                                                                "CustomField" => [
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOFDNDEFINITIONID'], //Fiscal Doc. Num
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $creditnote->einvoicenumber
                                                                    ],
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOVCDEFINITIONID'], //Verification Co
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $creditnote->antifakecode
                                                                    ],
                                                                    [
                                                                        "DefinitionId" => $this->appsettings['QBOAPPROVALSTATUSDEFINITIONID'], //Approval Status
                                                                        "Type" => "StringType",
                                                                        "StringValue" => $this->util->decodeapprovestatus($creditnote->approvestatus)
                                                                    ]
                                                                ]
                                                            ]);
                                                        }
                                                        
                                                        $updatedResult = $dataService->Update($updatedCreditnote);
                                                        //print_r($updatedResult);
                                                        $updatederror = $dataService->getLastError();
                                                        
                                                        if ($updatederror) {
                                                            $this->logger->write("Creditnote Controller : updateErpCreditnote() : The operation to update ERP creditnotes was not successful. The Response Message is: " . $updatederror->getResponseBody(), 'r');
                                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                                                            self::$systemalert = "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " was notsuccessful.";
                                                        }
                                                        else {
                                                            $this->logger->write("Creditnote Controller : updateErpCreditnote() : The operation to update ERP creditnotes was successful.", 'r');
                                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " was successful");
                                                            self::$systemalert = "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " was successful.";
                                                        }
                                                    } else {
                                                        $this->logger->write("Creditnote Controller : updateErpCreditnote() : The operation to update ERP creditnotes did not return any records", 'r');
                                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " did not return any records");
                                                        self::$systemalert = "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " did not return any records.";
                                                    }
                                                } else {
                                                    $this->logger->write("Creditnote Controller : updateErpCreditnote() : The operation to update ERP creditnotes did not return any records", 'r');
                                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " did not return any records");
                                                    self::$systemalert = "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " did not return any records.";
                                                }
                                            }
                                        } else {
                                            $this->logger->write("Creditnote Controller : updateErpCreditnote() : The operation to update ERP creditnotes was not successful. Please connect to ERP first.", 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.");
                                            self::$systemalert = "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to the ERP first.";
                                        }
                                    } catch (Exception $e) {
                                        $this->logger->write("Creditnote Controller : updateErpCreditnote() : The operation to update ERP creditnotes was not successful. The error is: " . $e->getMessage(), 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful");
                                        self::$systemalert = "The operation to update ERP creditnotes by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to the ERP OR Contact your System Administrator";
                                    }
                                } else {
                                    $this->logger->write("Creditnote Controller : updateErpCreditnote() : The creditnote does not have a Document Number.", 'r');
                                    self::$systemalert = "Sorry. The creditnote does not have a Document Number. Please re-download it.";
                                }
                            } else {
                                $this->logger->write("Creditnote Controller : updateErpCreditnote() : The integrated ERP is unknown.", 'r');
                                self::$systemalert = "Sorry. The integrated ERP is unknown.";
                            }
                        } else {
                            $this->logger->write("Creditnote Controller : updateErpCreditnote() : We are unable to indentify the currently integrated ERP.", 'r');
                            self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                        }
                    }
                } else {
                    $this->logger->write("Creditnote Controller : updateErpCreditnote() : The creditnote was not created by the ERP.", 'r');
                    self::$systemalert = "Sorry. The creditnote was not created by the ERP.";
                    
                    $this->f3->set('systemalert', self::$systemalert);
                    self::index();
                }
                
            } else {
                $this->logger->write("Creditnote Controller : updateErpCreditnote() : The creditnote was not specified.", 'r');
                self::$systemalert = "Sorry. The creditnote was not specified.";
                
                $this->f3->set('systemalert', self::$systemalert);
                self::index();
            }
        } else {
            $this->logger->write("Creditnote Controller : updateErpCreditnote() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
}

?>