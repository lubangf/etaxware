<?php
use QuickBooksOnline\API\DataService\DataService;
/**
 * @name ProductController
 * @desc This file is part of the etaxware system. The is the Product controller class
 * @date 30-08-2022
 * @file ProductController.php
 * @path ./app/controller/ProductController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
Class ProductController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules
    
    protected static $fileuploadfeedback;
    protected static $fileuploadstatus;

    private function isBlankValue($value){
        $normalized = trim((string)$value);
        return ($normalized === '' || strtoupper($normalized) === 'NULL');
    }

    private function getExcisePieceValidationErrors($hasExciseTax, $exciseDutyCode, $havePieceUnit, $pieceMeasureUnit, $pieceUnitPrice, $packageScaledValue, $pieceScaledValue){
        $errors = array();
        if (trim((string)$hasExciseTax) === '101') {
            if ($this->isBlankValue($exciseDutyCode)) {
                $errors[] = 'Excise Duty Code is required when Have Excise Duty is Yes.';
            }
            if (trim((string)$havePieceUnit) !== '101') {
                $errors[] = 'Have Piece Units must be Yes when Have Excise Duty is Yes.';
            }
            if ($this->isBlankValue($pieceMeasureUnit)) {
                $errors[] = 'Piece Measure Unit is required when Have Excise Duty is Yes.';
            }
            if ($this->isBlankValue($pieceUnitPrice)) {
                $errors[] = 'Piece Unit Price is required when Have Excise Duty is Yes.';
            }
            if ($this->isBlankValue($packageScaledValue)) {
                $errors[] = 'Package Scale Value is required when Have Excise Duty is Yes.';
            }
            if ($this->isBlankValue($pieceScaledValue)) {
                $errors[] = 'Piece Scale Value is required when Have Excise Duty is Yes.';
            }
        }
        return $errors;
    }

    private function setDerivedExciseMetaOnPost($exciseDutyCode){
        $code = trim((string)$exciseDutyCode);

        if ($code === '') {
            $this->f3->set('POST.exciseDutyName', null);
            $this->f3->set('POST.exciseRate', null);
            return;
        }

        try {
            $rows = $this->db->exec(
                'SELECT goodService, rateText FROM tblexcisedutylist WHERE TRIM(code) = ? LIMIT 1',
                array($code)
            );

            if (!empty($rows)) {
                $dutyName = trim((string)$rows[0]['goodService']);
                $dutyRate = trim((string)$rows[0]['rateText']);

                // Keep DB writes safe against destination column lengths.
                $this->f3->set('POST.exciseDutyName', $dutyName === '' ? null : substr($dutyName, 0, 100));
                $this->f3->set('POST.exciseRate', $dutyRate === '' ? null : substr($dutyRate, 0, 200));
            } else {
                $this->f3->set('POST.exciseDutyName', null);
                $this->f3->set('POST.exciseRate', null);
                $this->logger->write('Product Controller : setDerivedExciseMetaOnPost() : No excise lookup found for code = ' . $code, 'r');
            }
        } catch (Exception $e) {
            $this->f3->set('POST.exciseDutyName', null);
            $this->f3->set('POST.exciseRate', null);
            $this->logger->write('Product Controller : setDerivedExciseMetaOnPost() : Failed to derive excise metadata. Error = ' . $e->getMessage(), 'r');
        }
    }
    
    /**
     *	@name index
     *  @desc Loads the index page
     *	@return NULL
     *	@param NULL
     **/
    function index(){
        $operation = NULL; //tblevents
        $permission = 'VIEWPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $stockadjustmenttype = new stockadjustmenttypes($this->db);
            $stockadjustmenttypes = $stockadjustmenttype->all();
            $this->f3->set('stockadjustmenttypes', $stockadjustmenttypes);
            
            $stockintype = new stockintypes($this->db);
            $stockintypes = $stockintype->all();
            $this->f3->set('stockintypes', $stockintypes);
            
            $urabranch = new urabranches($this->db);
            $urabranches = $urabranch->all();
            $this->f3->set('urabranches', $urabranches);
            
            $stockoperationtype = new stockoperationtypes($this->db);
            $stockoperationtypes = $stockoperationtype->all();
            $this->f3->set('stockoperationtypes', $stockoperationtypes);
            
            $this->f3->set('pagetitle','Products');
            $this->f3->set('pagecontent','Product.htm');
            $this->f3->set('pagescripts','ProductFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Product Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    /**
     *	@name view
     *  @desc view Product
     *	@return NULL
     *	@param NULL
     **/
    function view($v_id = '', $tab = '', $tabpane = '') {
        $operation = NULL; //tblevents
        $permission = 'VIEWPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : view() : The previous URL is " . $this->f3->get('SERVER.HTTP_REFERER'), 'r');
            $this->logger->write("Product Controller : view() : The current URL is " . $this->f3->get('SERVER.REQUEST_URI'), 'r');
            
            /*$measureunit = new measureunits($this->db);
            $measureunits = $measureunit->all();
            $this->f3->set('measureunits', $measureunits);
            
            $commoditycategory = new commoditycategories($this->db);
            $commoditycategories = $commoditycategory->all();
            $this->f3->set('commoditycategories', $commoditycategories);*/
            

            
            $currency = new currencies($this->db);
            $currencies = $currency->all();
            $this->f3->set('currencies', $currencies);
            
            $choice = new choices($this->db);
            $choices = $choice->all();
            $this->f3->set('choices', $choices);
            
            $productsourcecode = new productsourcecodes($this->db);
            $productsourcecodes = $productsourcecode->all();
            $this->f3->set('productsourcecodes', $productsourcecodes);
            
            $productexclusioncode = new productexclusioncodes($this->db);
            $productexclusioncodes = $productexclusioncode->all();
            $this->f3->set('productexclusioncodes', $productexclusioncodes);
            
            $productstatuscode = new productstatuscodes($this->db);
            $productstatuscodes = $productstatuscode->all();
            $this->f3->set('productstatuscodes', $productstatuscodes);
            
            $excisedutylist = new excisedutylists($this->db);
            $excisedutylists = $excisedutylist->all();
            $this->f3->set('excisedutylists', $excisedutylists);
            
            $stockadjustmenttype = new stockadjustmenttypes($this->db);
            $stockadjustmenttypes = $stockadjustmenttype->all();
            $this->f3->set('stockadjustmenttypes', $stockadjustmenttypes);
            
            $stockintype = new stockintypes($this->db);
            $stockintypes = $stockintype->all();
            $this->f3->set('stockintypes', $stockintypes);
            
            $stockoperationtype = new stockoperationtypes($this->db);
            $stockoperationtypes = $stockoperationtype->all();
            $this->f3->set('stockoperationtypes', $stockoperationtypes);
            
            $goodstype = new goodstypes($this->db);
            $goodstypes = $goodstype->all();
            $this->f3->set('goodstypes', $goodstypes);
            
            if (is_string($tab) && is_string($tabpane)){
                $this->logger->write("Product Controller : view() : The value of v_id is " . $v_id, 'r');
                $this->logger->write("Product Controller : view() : The value of tab is " . $tab, 'r');
                $this->logger->write("Product Controller : view() : The value of tabpane " . $tabpane, 'r');
            } 
            
            if (trim($this->f3->get('PARAMS[id]')) !== '' || !empty(trim($this->f3->get('PARAMS[id]')))) { //Open EDIT mode
                $id = trim($this->f3->get('PARAMS[id]'));
                $this->logger->write("Product Controller : view() : The is a GET call & id to view is " . $id, 'r');
                
                $product = new products($this->db);
                $product->getByID($id);
                //$product = new DB\SQL\Mapper($this->db, 'tblproductdetails');
                //$product->load(array('id=?', $id));
                
                //Add some reference fields.
                $product->measureunitName = 'SELECT distinct name FROM tblrateunits WHERE tblproductdetails.measureunit = tblrateunits.code';
                // 2026-04-11 19:44:49 +03:00 - Add currency display name for Select2 prefill on product edit form.
                $product->currencyName = 'SELECT distinct name FROM tblcurrencies WHERE tblproductdetails.currency = tblcurrencies.code';
                $product->commoditycategoryName = 'SELECT distinct commodityname FROM tblcommoditycategories WHERE tblproductdetails.commoditycategorycode = tblcommoditycategories.commoditycode';
                $product->piecemeasureunitName = 'SELECT distinct name FROM tblrateunits WHERE tblproductdetails.piecemeasureunit = tblrateunits.code';
                $product->customsmeasureunitName = 'SELECT distinct name FROM tblexportrateunits WHERE tblproductdetails.customsmeasureunit = tblexportrateunits.code';
                $product->hsName_2 = 'SELECT distinct name FROM tblhscodes WHERE tblproductdetails.hsCode = tblhscodes.code';
                $product->load(array('id=?', $id));
                
                $this->f3->set('product', $product);
                
                if (is_string($tab) && is_string($tabpane)){//this check is necessary for cases where the GET request is system initiated. The params sent to the view functions are non-string.
                    $this->f3->set('currenttab', $tab);
                    $this->f3->set('currenttabpane', $tabpane);
                } else {
                    $this->f3->set('currenttab', 'tab_general');
                    $this->f3->set('currenttabpane', 'tab_1');
                    $this->f3->set('path', '../' . $this->path);
                }
                
                $this->f3->set('pagetitle','Edit Product | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path); //overide the main solution path
            } elseif (trim($this->f3->get('POST.id')) !== '' || !empty(trim($this->f3->get('POST.id')))) {//Open EDIT mode
                $id = trim($this->f3->get('POST.id'));
                $this->logger->write("Product Controller : view() : This is a POST call & the id to view is " . $id, 'r');
                
                $product = new products($this->db);
                $product->getByID($id);
                
                //Add some reference fields.
                $product->measureunitName = 'SELECT distinct name FROM tblrateunits WHERE tblproductdetails.measureunit = tblrateunits.code';
                // 2026-04-11 19:44:49 +03:00 - Add currency display name for Select2 prefill on product edit form.
                $product->currencyName = 'SELECT distinct name FROM tblcurrencies WHERE tblproductdetails.currency = tblcurrencies.code';
                $product->commoditycategoryName = 'SELECT distinct commodityname FROM tblcommoditycategories WHERE tblproductdetails.commoditycategorycode = tblcommoditycategories.commoditycode';
                $product->piecemeasureunitName = 'SELECT distinct name FROM tblrateunits WHERE tblproductdetails.piecemeasureunit = tblrateunits.code';
                $product->customsmeasureunitName = 'SELECT distinct name FROM tblexportrateunits WHERE tblproductdetails.customsmeasureunit = tblexportrateunits.code';
                $product->hsName_2 = 'SELECT distinct name FROM tblhscodes WHERE tblproductdetails.hsCode = tblhscodes.code';
                $product->load(array('id=?', $id));
                
                $this->f3->set('product', $product);
                
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
                
                $this->f3->set('pagetitle','Edit Product | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
            } elseif (trim($v_id) !== '' || !empty(trim($v_id))){
                $id = trim($v_id);
                $this->logger->write("Product Controller : view() : This is an in-class function call & the id to view is " . $id, 'r');
                
                $product = new products($this->db);
                $product->getByID($id);
                
                //Add some reference fields.
                $product->measureunitName = 'SELECT distinct name FROM tblrateunits WHERE tblproductdetails.measureunit = tblrateunits.code';
                // 2026-04-11 19:44:49 +03:00 - Add currency display name for Select2 prefill on product edit form.
                $product->currencyName = 'SELECT distinct name FROM tblcurrencies WHERE tblproductdetails.currency = tblcurrencies.code';
                $product->commoditycategoryName = 'SELECT distinct commodityname FROM tblcommoditycategories WHERE tblproductdetails.commoditycategorycode = tblcommoditycategories.commoditycode';
                $product->piecemeasureunitName = 'SELECT distinct name FROM tblrateunits WHERE tblproductdetails.piecemeasureunit = tblrateunits.code';
                $product->customsmeasureunitName = 'SELECT distinct name FROM tblexportrateunits WHERE tblproductdetails.customsmeasureunit = tblexportrateunits.code';
                $product->hsName_2 = 'SELECT distinct name FROM tblhscodes WHERE tblproductdetails.hsCode = tblhscodes.code';
                $product->load(array('id=?', $id));
                
                $this->f3->set('product', $product);
                
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
                
                $this->f3->set('pagetitle','Edit Product | ' . $id);//display the edit form
                //$this->f3->set('path', '../' . $this->path);
                
                $this->f3->set('pagecontent','EditProduct.htm');
                $this->f3->set('pagescripts','EditProductFooter.htm');
                echo \Template::instance()->render('Layout.htm');
                exit(); //exit the function so no extra code executes
            } else {
                $this->logger->write("Product Controller : view() : No id was selected", 'r');
                $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER')); //return to the previous page
                exit();
            }
            
            $this->logger->write("Product Controller : view() : The currenttab has been set to " . $this->f3->get('currenttab'), 'r');
            $this->logger->write("Product Controller : view() : The currenttabpane has been set to " . $this->f3->get('currenttabpane'), 'r');
            
            $this->f3->set('pagecontent','EditProduct.htm');
            $this->f3->set('pagescripts','EditProductFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Product Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name add
     *  @desc add Product
     *	@return NULL
     *	@param NULL
     **/
    function add() {
        $operation = NULL; //tblevents
        $permission = 'CREATEPRODUCT'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {           
            //@TODO Display a new form
            /*$measureunit = new measureunits($this->db);
            $measureunits = $measureunit->all();
            $this->f3->set('measureunits', $measureunits);
            
            $commoditycategory = new commoditycategories($this->db);
            $commoditycategories = $commoditycategory->all();
            $this->f3->set('commoditycategories', $commoditycategories);*/
            
            $currency = new currencies($this->db);
            $currencies = $currency->all();
            $this->f3->set('currencies', $currencies);
            
            $choice = new choices($this->db);
            $choices = $choice->all();
            $this->f3->set('choices', $choices);
            
            $productsourcecode = new productsourcecodes($this->db);
            $productsourcecodes = $productsourcecode->all();
            $this->f3->set('productsourcecodes', $productsourcecodes);
            
            $productexclusioncode = new productexclusioncodes($this->db);
            $productexclusioncodes = $productexclusioncode->all();
            $this->f3->set('productexclusioncodes', $productexclusioncodes);
            
            $productstatuscode = new productstatuscodes($this->db);
            $productstatuscodes = $productstatuscode->all();
            $this->f3->set('productstatuscodes', $productstatuscodes);
            
            $excisedutylist = new excisedutylists($this->db);
            $excisedutylists = $excisedutylist->all();
            $this->f3->set('excisedutylists', $excisedutylists);
            
            $stockadjustmenttype = new stockadjustmenttypes($this->db);
            $stockadjustmenttypes = $stockadjustmenttype->all();
            $this->f3->set('stockadjustmenttypes', $stockadjustmenttypes);
            
            $stockintype = new stockintypes($this->db);
            $stockintypes = $stockintype->all();
            $this->f3->set('stockintypes', $stockintypes);
            
            $stockoperationtype = new stockoperationtypes($this->db);
            $stockoperationtypes = $stockoperationtype->all();
            $this->f3->set('stockoperationtypes', $stockoperationtypes);
            
            $goodstype = new goodstypes($this->db);
            $goodstypes = $goodstype->all();
            $this->f3->set('goodstypes', $goodstypes);
            
            $this->f3->set('currenttab', 'tab_general');//set the GENERAL tab as ACTIVE
            $this->f3->set('currenttabpane', 'tab_1');
            
            
            $product = array(
                "id" => NULL,
                "name" => '',
                "code" => '',
                "description" => ''
            );
            $this->f3->set('product', $product);
            
            $this->f3->set('pagetitle','Create Product');
            
            $this->f3->set('pagecontent','EditProduct.htm');
            $this->f3->set('pagescripts','EditProductFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Product Controller : index() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    
    /**
     * edit product
     *
     * @name edit
     * @return NULL
     * @param
     *            NULL
     */
    function edit(){
        $product = new products($this->db);
        $currenttab = trim($this->f3->get('POST.currenttab'));
        $currenttabpane = trim($this->f3->get('POST.currenttabpane'));
        $id = 0;
        
        if (trim($this->f3->get('POST.productid')) !== '' || ! empty(trim($this->f3->get('POST.productid')))) { // EDIT Operation
            $operation = NULL; // tblevents
            $permission = 'EDITPRODUCT'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications
            
            $this->logger->write("Product Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $id = trim($this->f3->get('POST.productid'));
                $this->logger->write("Product Controller : edit() : The id to be edited is " . $id, 'r');
                $product->getByID($id);
                
                if ($currenttab == 'tab_general') {
                    // @TODO check the params for empty/null values
                    
                    if (trim($this->f3->get('POST.productname')) !== '' || ! empty(trim($this->f3->get('POST.productname')))) {
                        $this->f3->set('POST.name', $this->f3->get('POST.productname'));
                    } else {
                        //$this->f3->set('POST.name', $product->name);
                        $this->f3->set('POST.name', null);
                    }
                    
                    if (trim($this->f3->get('POST.productcode')) !== '' || ! empty(trim($this->f3->get('POST.productcode')))) {
                        $this->f3->set('POST.code', $this->f3->get('POST.productcode'));
                    } else {
                        $this->f3->set('POST.code', $product->code);
                        //$this->f3->set('POST.code', null);
                    }
                    
                    if (trim($this->f3->get('POST.productdescription')) !== '' || ! empty(trim($this->f3->get('POST.productdescription')))) {
                        $this->f3->set('POST.description', $this->f3->get('POST.productdescription'));
                    } else {
                        //$this->f3->set('POST.description', $product->description);
                        $this->f3->set('POST.description', null);
                    }
                    
                    if (trim($this->f3->get('POST.producterpid')) !== '' || ! empty(trim($this->f3->get('POST.producterpid')))) {
                        $this->f3->set('POST.erpid', $this->f3->get('POST.producterpid'));
                    } else {
                        //$this->f3->set('POST.erpid', $product->erpid);
                        $this->f3->set('POST.erpid', null);
                    }
                    
                    if (trim($this->f3->get('POST.producterpcode')) !== '' || ! empty(trim($this->f3->get('POST.producterpcode')))) {
                        $this->f3->set('POST.erpcode', $this->f3->get('POST.producterpcode'));
                    } else {
                        //$this->f3->set('POST.erpcode', $product->erpcode);
                        $this->f3->set('POST.erpcode', null);
                    }
                    
                    if (trim($this->f3->get('POST.productunitprice')) !== '' || ! empty(trim($this->f3->get('POST.productunitprice')))) {
                        $this->f3->set('POST.unitprice', $this->f3->get('POST.productunitprice'));
                    } else {
                        //$this->f3->set('POST.unitprice', $product->unitprice);
                        $this->f3->set('POST.unitprice', null);
                    }
                    
                    if (trim($this->f3->get('POST.productmeasureunit')) !== '' || ! empty(trim($this->f3->get('POST.productmeasureunit')))) {
                        $this->f3->set('POST.measureunit', $this->f3->get('POST.productmeasureunit'));
                    } else {
                        //$this->f3->set('POST.measureunit', $product->measureunit);
                        $this->f3->set('POST.measureunit', null);
                    }
                    
                    if (trim($this->f3->get('POST.productcurrency')) !== '' || ! empty(trim($this->f3->get('POST.productcurrency')))) {
                        $this->f3->set('POST.currency', $this->f3->get('POST.productcurrency'));
                    } else {
                        //$this->f3->set('POST.currency', $product->currency);
                        $this->f3->set('POST.currency', null);
                    }
                    
                    if (trim($this->f3->get('POST.productcommoditycategory')) !== '' || ! empty(trim($this->f3->get('POST.productcommoditycategory')))) {
                        $this->f3->set('POST.commoditycategorycode', $this->f3->get('POST.productcommoditycategory'));
                    } else {
                        //$this->f3->set('POST.commoditycategorycode', $product->commoditycategorycode);
                        $this->f3->set('POST.commoditycategorycode', null);
                    }
                    
                    if (trim($this->f3->get('POST.productgoodsTypeCode')) !== '' || ! empty(trim($this->f3->get('POST.productgoodsTypeCode')))) {
                        $this->f3->set('POST.goodsTypeCode', $this->f3->get('POST.productgoodsTypeCode'));
                    } else {
                        //$this->f3->set('POST.goodsTypeCode', $product->goodsTypeCode);
                        $this->f3->set('POST.goodsTypeCode', null);
                    }
                    
                    if (trim($this->f3->get('POST.producthasexcisetax')) !== '' || ! empty(trim($this->f3->get('POST.producthasexcisetax')))) {
                        $this->f3->set('POST.hasexcisetax', $this->f3->get('POST.producthasexcisetax'));
                        // 2026-04-12 11:52:00 +03:00 - Canonicalize writes to `exciseDutyCode` only.
                        // Keep fallback reads from legacy `excisedutylist`, but do not mirror writes back to that legacy column.
                        
                        if (trim($this->f3->get('POST.producthasexcisetax')) == '102') {//If HasExciseDuty is set to No, then empty the exciseduty code
                            $this->f3->set('POST.exciseDutyCode', null);
                        } else {
                            if (trim($this->f3->get('POST.productexcisedutylist')) !== '' || ! empty(trim($this->f3->get('POST.productexcisedutylist')))) {
                                $this->f3->set('POST.exciseDutyCode', $this->f3->get('POST.productexcisedutylist'));
                            } else {
                                $fallbackExciseCode = (trim((string)$product->exciseDutyCode) !== '') ? $product->exciseDutyCode : $product->excisedutylist;
                                $this->f3->set('POST.exciseDutyCode', $fallbackExciseCode);
                            }
                        }

                        // 2026-04-12 12:34:00 +03:00 - Requirement: derive excise duty name/rate from selected excise code on backend save.
                        $this->setDerivedExciseMetaOnPost($this->f3->get('POST.exciseDutyCode'));
                    } else {
                        //$this->f3->set('POST.hasexcisetax', $product->hasexcisetax);
                        $this->f3->set('POST.hasexcisetax', null);
                        $this->f3->set('POST.exciseDutyName', null);
                        $this->f3->set('POST.exciseRate', null);
                    }
                    
                    $this->logger->write("Product Controller : edit() : havepieceunit before: " . $product->havepieceunit, 'r');
                    $this->logger->write("Product Controller : edit() : havepieceunit after: " . $this->f3->get('POST.producthavepieceunit'), 'r');
                    
                    if (trim($this->f3->get('POST.producthavepieceunit')) !== '' || ! empty(trim($this->f3->get('POST.producthavepieceunit')))) {
                        $this->f3->set('POST.havepieceunit', $this->f3->get('POST.producthavepieceunit'));
                    } else {
                        if($product->havepieceunit == 0){
                            $this->f3->set('POST.havepieceunit', '102');
                        } else {
                            $this->f3->set('POST.havepieceunit', $product->havepieceunit);
                        }
                        
                    }

                    // 2026-04-11 21:04:12 +03:00 - When Excise is Yes, force Have Piece Units to Yes even if the UI field is disabled and omitted from POST.
                    if (trim((string)$this->f3->get('POST.hasexcisetax')) === '101') {
                        $this->f3->set('POST.havepieceunit', '101');
                    }
                    
                    $this->logger->write("Product Controller : edit() : piecemeasureunit before: " . $product->piecemeasureunit, 'r');
                    $this->logger->write("Product Controller : edit() : piecemeasureunit after: " . $this->f3->get('POST.productpiecemeasureunit'), 'r');
                    
                    if (trim($this->f3->get('POST.productpiecemeasureunit')) !== '' || ! empty(trim($this->f3->get('POST.productpiecemeasureunit')))) {
                        $this->f3->set('POST.piecemeasureunit', $this->f3->get('POST.productpiecemeasureunit'));
                    } else {
                        //$this->f3->set('POST.piecemeasureunit', $product->piecemeasureunit);
                        $this->f3->set('POST.piecemeasureunit', null);
                    }
                    
                    if (trim($this->f3->get('POST.productpieceunitprice')) !== '' || ! empty(trim($this->f3->get('POST.productpieceunitprice')))) {
                        $this->f3->set('POST.pieceunitprice', $this->f3->get('POST.productpieceunitprice'));
                    } else {
                        //$this->f3->set('POST.pieceunitprice', $product->pieceunitprice);
                        $this->f3->set('POST.pieceunitprice', null);
                    }
                    
                    if (trim($this->f3->get('POST.productpackagescaledvalue')) !== '' || ! empty(trim($this->f3->get('POST.productpackagescaledvalue')))) {
                        $this->f3->set('POST.packagescaledvalue', $this->f3->get('POST.productpackagescaledvalue'));
                    } else {
                        //$this->f3->set('POST.packagescaledvalue', $product->packagescaledvalue);
                        $this->f3->set('POST.packagescaledvalue', null);
                    }
                    
                    if (trim($this->f3->get('POST.productpiecescaledvalue')) !== '' || ! empty(trim($this->f3->get('POST.productpiecescaledvalue')))) {
                        $this->f3->set('POST.piecescaledvalue', $this->f3->get('POST.productpiecescaledvalue'));
                    } else {
                        //$this->f3->set('POST.piecescaledvalue', $product->piecescaledvalue);
                        $this->f3->set('POST.piecescaledvalue', null);
                    }
                    
                    if (trim($this->f3->get('POST.producthscode')) !== '' || ! empty(trim($this->f3->get('POST.producthscode')))) {
                        $this->f3->set('POST.hsCode', $this->f3->get('POST.producthscode'));
                    } else {
                        //$this->f3->set('POST.hsCode', $product->hsCode);
                        $this->f3->set('POST.hsCode', null);
                    }
                    
                    if (trim($this->f3->get('POST.productcustomsmeasureunit')) !== '' || ! empty(trim($this->f3->get('POST.productcustomsmeasureunit')))) {
                        $this->f3->set('POST.customsmeasureunit', $this->f3->get('POST.productcustomsmeasureunit'));
                    } else {
                        //$this->f3->set('POST.customsmeasureunit', $product->customsmeasureunit);
                        $this->f3->set('POST.customsmeasureunit', null);
                    }
                    
                    if (trim($this->f3->get('POST.productcustomsunitprice')) !== '' || ! empty(trim($this->f3->get('POST.productcustomsunitprice')))) {
                        $this->f3->set('POST.customsunitprice', $this->f3->get('POST.productcustomsunitprice'));
                    } else {
                        $this->f3->set('POST.customsunitprice', $product->customsunitprice);
                    }
                    
                    if (trim($this->f3->get('POST.packagescaledvaluecustoms')) !== '' || ! empty(trim($this->f3->get('POST.packagescaledvaluecustoms')))) {
                        $this->f3->set('POST.packagescaledvaluecustoms', $this->f3->get('POST.packagescaledvaluecustoms'));
                    } else {
                        //$this->f3->set('POST.packagescaledvaluecustoms', $product->packagescaledvaluecustoms);
                        $this->f3->set('POST.packagescaledvaluecustoms', null);
                    }
                    
                    if (trim($this->f3->get('POST.productcustomsscaledvalue')) !== '' || ! empty(trim($this->f3->get('POST.productcustomsscaledvalue')))) {
                        $this->f3->set('POST.customsscaledvalue', $this->f3->get('POST.productcustomsscaledvalue'));
                    } else {
                        //$this->f3->set('POST.customsscaledvalue', $product->customsscaledvalue);
                        $this->f3->set('POST.customsscaledvalue', null);
                    }
                    
                    if (trim($this->f3->get('POST.productweight')) !== '' || ! empty(trim($this->f3->get('POST.productweight')))) {
                        $this->f3->set('POST.weight', $this->f3->get('POST.productweight'));
                    } else {
                        //$this->f3->set('POST.weight', $product->weight);
                        $this->f3->set('POST.weight', null);
                    }
                    
                    if (trim($this->f3->get('POST.productstockprewarning')) !== '' || ! empty(trim($this->f3->get('POST.productstockprewarning')))) {
                        $this->f3->set('POST.stockprewarning', $this->f3->get('POST.productstockprewarning'));
                    } else {
                        $this->f3->set('POST.stockprewarning', $product->stockprewarning);
                        $this->f3->set('POST.stockprewarning', null);
                    }                                       
                    
                    if (trim($this->f3->get('POST.producthaveotherunit')) !== '' || ! empty(trim($this->f3->get('POST.producthaveotherunit')))) {
                        $this->f3->set('POST.haveotherunit', $this->f3->get('POST.producthaveotherunit'));
                    } else {
                        //$this->f3->set('POST.haveotherunit', $product->haveotherunit);
                        $this->f3->set('POST.haveotherunit', null);
                    }

                    $validationErrors = $this->getExcisePieceValidationErrors(
                        $this->f3->get('POST.hasexcisetax'),
                        $this->f3->get('POST.exciseDutyCode'),
                        $this->f3->get('POST.havepieceunit'),
                        $this->f3->get('POST.piecemeasureunit'),
                        $this->f3->get('POST.pieceunitprice'),
                        $this->f3->get('POST.packagescaledvalue'),
                        $this->f3->get('POST.piecescaledvalue')
                    );
                    if (!empty($validationErrors)) {
                        self::$systemalert = implode(' ', $validationErrors);
                        $this->logger->write('Product Controller : edit() : Excise-piece validation failed. ' . self::$systemalert, 'r');
                        $this->f3->set('systemalert', self::$systemalert);
                        self::view($id);
                        exit();
                    }
                }
                
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                try {
                    $product->edit($id);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The product - " . $product->id . " - " . $product->code . " has been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The product  - " . $product->id . " - " . $product->code . " has been edited";
                    $this->logger->write("Product Controller : edit() : The product  - " . $product->id . " - " . $product->code . " has been edited", 'r');
                } catch (Exception $e) {
                    $this->logger->write("Product Controller : edit() : The operation to edit product - " . $product->id . " - " . $product->code . " was not successfull. The error messages is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit product - " . $product->id . " - " . $product->code . " was not successfull");
                    self::$systemalert = "The operation to edit product - " . $product->id . " - " . $product->code . " was not successful";
                }
            } else {
                $this->logger->write("Product Controller : edit() : The user is not allowed to perform this function", 'r');
                $this->f3->reroute('/forbidden');
            }
        } else { // ADD Operation: mainly handles the GENERAL parameters, as the rest of the parameters will be added using the EDIT option
            $operation = NULL; // tblevents
            $permission = 'CREATEPRODUCT'; // tblpermissions
            $event = NULL; // tblevents
            $eventnotification = NULL; // tbleventnotifications
            
            $this->logger->write("Product Controller : edit() : Checking permissions", 'r');
            if ($this->userpermissions[$permission]) {
                $this->logger->write("Product Controller : edit() : Adding of product started.", 'r');
                
                $this->f3->set('POST.name', $this->f3->get('POST.productname'));
                $this->f3->set('POST.code', $this->f3->get('POST.productcode'));
                $this->f3->set('POST.description', $this->f3->get('POST.productdescription'));
                
                $this->f3->set('POST.unitprice', $this->f3->get('POST.productunitprice'));
                $this->logger->write("Product Controller : edit() : The unit price is: " . $this->f3->get('POST.productunitprice'), 'r');
                $this->f3->set('POST.measureunit', $this->f3->get('POST.productmeasureunit'));
                $this->f3->set('POST.currency', $this->f3->get('POST.productcurrency'));
                $this->f3->set('POST.commoditycategorycode', $this->f3->get('POST.productcommoditycategory'));
                $this->f3->set('POST.hsCode', $this->f3->get('POST.producthscode'));
                $this->f3->set('POST.goodsTypeCode', $this->f3->get('POST.productgoodsTypeCode'));
                
                $this->f3->set('POST.havepieceunit', $this->f3->get('POST.producthavepieceunit'));
                
                $this->logger->write("Product Controller : edit() : producthavepieceunit = " . $this->f3->get('POST.producthavepieceunit'), 'r');
                
                if (trim((string)$this->f3->get('POST.producthavepieceunit')) === '' || trim((string)$this->f3->get('POST.producthavepieceunit')) === '0') {
                    $this->f3->set('POST.havepieceunit', '102');
                } else {
                    $this->f3->set('POST.havepieceunit', $this->f3->get('POST.producthavepieceunit'));
                }
                
                $this->f3->set('POST.piecemeasureunit', $this->f3->get('POST.productpiecemeasureunit'));
                $this->f3->set('POST.pieceunitprice', $this->f3->get('POST.productpieceunitprice'));
                $this->f3->set('POST.packagescaledvalue', $this->f3->get('POST.productpackagescaledvalue'));
                $this->f3->set('POST.piecescaledvalue', $this->f3->get('POST.productpiecescaledvalue'));
                // 2026-04-11 19:13:27 +03:00 - Keep create flow consistent with edit flow for excise duty persistence rules.
                $this->f3->set('POST.hasexcisetax', $this->f3->get('POST.producthasexcisetax'));
                // 2026-04-12 11:52:00 +03:00 - Canonicalize create-flow writes to `exciseDutyCode` only.
                if (trim((string)$this->f3->get('POST.producthasexcisetax')) === '102') {
                    $this->f3->set('POST.exciseDutyCode', null);
                } elseif (trim((string)$this->f3->get('POST.productexcisedutylist')) !== '') {
                    $this->f3->set('POST.exciseDutyCode', $this->f3->get('POST.productexcisedutylist'));
                } else {
                    $this->f3->set('POST.exciseDutyCode', null);
                }
                // 2026-04-12 12:34:00 +03:00 - Requirement: derive excise duty name/rate from selected excise code on backend save.
                $this->setDerivedExciseMetaOnPost($this->f3->get('POST.exciseDutyCode'));

                // 2026-04-11 21:04:12 +03:00 - When Excise is Yes, force Have Piece Units to Yes even if the UI field is disabled and omitted from POST.
                if (trim((string)$this->f3->get('POST.hasexcisetax')) === '101') {
                    $this->f3->set('POST.havepieceunit', '101');
                }
                $this->f3->set('POST.stockprewarning', (float)$this->f3->get('POST.productstockprewarning'));
                
                $this->f3->set('POST.hsCode', $this->f3->get('POST.producthscode'));
                $this->f3->set('POST.customsmeasureunit', $this->f3->get('POST.productcustomsmeasureunit'));
                // 2026-04-11 19:33:53 +03:00 - Normalize optional numeric customs fields to null on create to avoid SQL decimal errors on empty strings.
                $customsUnitPrice = trim((string)$this->f3->get('POST.productcustomsunitprice'));
                $packageScaledValueCustoms = trim((string)$this->f3->get('POST.packagescaledvaluecustoms'));
                $customsScaledValue = trim((string)$this->f3->get('POST.productcustomsscaledvalue'));
                $weight = trim((string)$this->f3->get('POST.productweight'));

                $this->f3->set('POST.customsunitprice', $customsUnitPrice === '' ? null : $customsUnitPrice);
                $this->f3->set('POST.packagescaledvaluecustoms', $packageScaledValueCustoms === '' ? null : $packageScaledValueCustoms);
                $this->f3->set('POST.customsscaledvalue', $customsScaledValue === '' ? null : $customsScaledValue);
                $this->f3->set('POST.weight', $weight === '' ? null : $weight);
                
                $this->f3->set('POST.haveotherunit', $this->f3->get('POST.producthaveotherunit'));

                $validationErrors = $this->getExcisePieceValidationErrors(
                    $this->f3->get('POST.hasexcisetax'),
                    $this->f3->get('POST.exciseDutyCode'),
                    $this->f3->get('POST.havepieceunit'),
                    $this->f3->get('POST.piecemeasureunit'),
                    $this->f3->get('POST.pieceunitprice'),
                    $this->f3->get('POST.packagescaledvalue'),
                    $this->f3->get('POST.piecescaledvalue')
                );
                if (!empty($validationErrors)) {
                    self::$systemalert = implode(' ', $validationErrors);
                    $this->logger->write('Product Controller : edit() : Excise-piece validation failed. ' . self::$systemalert, 'r');
                    $this->f3->set('systemalert', self::$systemalert);
                    self::add();
                    exit();
                }
                
                $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                // @TODO check the params for empty/null values
                if (trim($this->f3->get('POST.name')) !== '' || ! empty(trim($this->f3->get('POST.name')))) {
                    try {
                        // Proceed & create
                        $product->add();
                        // $this->logger->write("Product Controller : edit() : A new product has been added", 'r');
                        try {
                            // retrieve the most recently inserted product
                            // @TODO place the code block below in a try...catch block to handle cases where the db call fails, or the add operation above fails
                            $data = array();
                            $r = $this->db->exec(array(
                                'SELECT MAX(id) "id" FROM tblproductdetails WHERE insertedby = ' . $this->f3->get('SESSION.id')
                            ));
                            foreach ($r as $obj) {
                                $data[] = $obj;
                            }
                            
                            // $this->logger->write("Product Controller : edit() : The product " . $data[0]['id'] . " has been added", 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The product id " . $data[0]['id'] . " has been added by " . $this->f3->get('SESSION.username'));
                            self::$systemalert = "The product id " . $data[0]['id'] . " has been added";
                            $id = $data[0]['id'];
                            $product->getByID($id);
                        } catch (Exception $e) {
                            $this->logger->write("Product Controller : edit() : The operation to retrieve the most recently added product was not successful. The error messages is " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to retrieve the most recently added product was not successful");
                            self::$systemalert = "The operation to retrieve the most recently added product was not successful";
                        }
                    } catch (Exception $e) {
                        $this->logger->write("Product Controller : edit() : The operation to add a product was not successful. The error messages is " . $e->getMessage(), 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add a product was not successful");
                        self::$systemalert = "The operation to add a product was not successful. An internal error occured, or you are trying to add a duplicate code";
                        $this->f3->set('systemalert', self::$systemalert);
                        self::add();
                        exit();
                    }
                } else {
                    $this->logger->write("Product Controller : edit() : The user is not allowed to perform this function", 'r');
                    $this->f3->reroute('/forbidden');
                }
            } else { // some params are empty
                // ABORT MISSION
                self::add();
                exit();
            }
        }
        
        /*$measureunit = new measureunits($this->db);
        $measureunits = $measureunit->all();
        $this->f3->set('measureunits', $measureunits);
        
        $commoditycategory = new commoditycategories($this->db);
        $commoditycategories = $commoditycategory->all();
        $this->f3->set('commoditycategories', $commoditycategories);*/
        

        
        $currency = new currencies($this->db);
        $currencies = $currency->all();
        $this->f3->set('currencies', $currencies);
        
        $choice = new choices($this->db);
        $choices = $choice->all();
        $this->f3->set('choices', $choices);
        
        $productsourcecode = new productsourcecodes($this->db);
        $productsourcecodes = $productsourcecode->all();
        $this->f3->set('productsourcecodes', $productsourcecodes);
        
        $productexclusioncode = new productexclusioncodes($this->db);
        $productexclusioncodes = $productexclusioncode->all();
        $this->f3->set('productexclusioncodes', $productexclusioncodes);
        
        $productstatuscode = new productstatuscodes($this->db);
        $productstatuscodes = $productstatuscode->all();
        $this->f3->set('productstatuscodes', $productstatuscodes);
        
        $excisedutylist = new excisedutylists($this->db);
        $excisedutylists = $excisedutylist->all();
        $this->f3->set('excisedutylists', $excisedutylists);
        
        $stockadjustmenttype = new stockadjustmenttypes($this->db);
        $stockadjustmenttypes = $stockadjustmenttype->all();
        $this->f3->set('stockadjustmenttypes', $stockadjustmenttypes);
        
        $stockintype = new stockintypes($this->db);
        $stockintypes = $stockintype->all();
        $this->f3->set('stockintypes', $stockintypes);
        
        $stockoperationtype = new stockoperationtypes($this->db);
        $stockoperationtypes = $stockoperationtype->all();
        $this->f3->set('stockoperationtypes', $stockoperationtypes);
        
        $goodstype = new goodstypes($this->db);
        $goodstypes = $goodstype->all();
        $this->f3->set('goodstypes', $goodstypes);
        
        $product_n = new products($this->db);
        $product_n->getByID($product->id);
        //Add some reference fields.
        $product_n->measureunitName = 'SELECT distinct name FROM tblrateunits WHERE tblproductdetails.measureunit = tblrateunits.code';
        $product_n->commoditycategoryName = 'SELECT distinct commodityname FROM tblcommoditycategories WHERE tblproductdetails.commoditycategorycode = tblcommoditycategories.commoditycode';
        $product_n->piecemeasureunitName = 'SELECT distinct name FROM tblrateunits WHERE tblproductdetails.piecemeasureunit = tblrateunits.code';
        $product_n->customsmeasureunitName = 'SELECT distinct name FROM tblrateunits WHERE tblproductdetails.customsmeasureunit = tblrateunits.code';
        $product_n->hsName_2 = 'SELECT distinct name FROM tblhscodes WHERE tblproductdetails.hsCode = tblhscodes.code';
        $product_n->load(array('id=?', $product->id));
        
        $this->f3->set('product', $product_n);
        $this->f3->set('currenttab', $currenttab);
        $this->f3->set('currenttabpane', $currenttabpane);
        
        $this->f3->set('systemalert', self::$systemalert);
        
        $this->f3->set('pagetitle', 'Edit Product | ' . $id);
        $this->f3->set('pagecontent', 'EditProduct.htm');
        $this->f3->set('pagescripts', 'EditProductFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    
    /**
     *	@name list
     *  @desc List products
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function list(){
        $operation = NULL; //tblevents
        $permission = 'VIEWPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Product Controller : index() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : list() : Processing list of products started", 'r');
            $name = trim((string)$this->f3->get('REQUEST.name'));
            $this->logger->write("Product Controller : list() : Name: " . $name, 'r');

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
                    0 => 'p.id',
                    1 => 'p.uraproductidentifier',
                    2 => 'p.erpid',
                    3 => 'p.erpcode',
                    4 => 'p.code',
                    5 => 'p.name',
                    6 => 'p.description',
                    7 => 'p.erpquantity',
                    8 => 'p.uraquantity',
                    9 => 'p.unitprice',
                    10 => 'p.modifieddt'
                );

                $orderBy = array_key_exists($orderColumnIndex, $columnMap)? $columnMap[$orderColumnIndex] : 'p.id';

                $where = '';
                if ($searchValue !== '') {
                    $searchEscaped = addslashes($searchValue);
                    $where = " WHERE (p.code LIKE '%" . $searchEscaped . "%'"
                        . " OR p.name LIKE '%" . $searchEscaped . "%'"
                        . " OR p.description LIKE '%" . $searchEscaped . "%'"
                        . " OR p.erpcode LIKE '%" . $searchEscaped . "%'"
                        . " OR p.uraproductidentifier LIKE '%" . $searchEscaped . "%')";
                }

                $countTotalSql = 'SELECT COUNT(*) "c" FROM tblproductdetails p';
                $countFilteredSql = 'SELECT COUNT(*) "c" FROM tblproductdetails p' . $where;

                $sql = 'SELECT  p.id "ID",
                        p.erpid "ERP ID",
                        p.erpcode "ERP Code",
                        p.uraproductidentifier "EFRIS Id",
                        p.code "Code",
                        p.name "Name",
                        p.description "Description",
                        FORMAT(p.erpquantity, 0) "ERP Quantity",
                        FORMAT(p.uraquantity, 0) "EFRIS Quantity",
                        p.measureunit "Measure Unit",
                        FORMAT(p.unitprice, 2) "Unit Price",
                        p.currency "Currency",
                        p.commoditycategorycode "Commodity Category Code",
                        p.hasexcisetax "Has Excise Tax",
                        p.stockprewarning "Stock Prewarning",
                        p.piecemeasureunit "Piece Measure Unit",
                        p.havepieceunit "Have Piece Unit",
                        p.pieceunitprice "Piece Unit Price",
                        p.packagescaledvalue "Package Scaled Value",
                        p.piecescaledvalue "Piece Scaled Value",
                        p.excisedutylist "Exciseduty Code",
                        p.disabled "Disabled",
                        p.inserteddt "Creation Date",
                        p.insertedby "Created By",
                        p.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblproductdetails p
                    LEFT JOIN tblusers s ON p.modifiedby = s.id'
                    . $where
                    . ' ORDER By ' . $orderBy . ' ' . $orderDir
                    . ' LIMIT ' . $start . ', ' . $length;

                try {
                    $countTotalRow = $this->db->exec($countTotalSql);
                    $countFilteredRow = $this->db->exec($countFilteredSql);
                    $dtls = $this->db->exec($sql);

                    $recordsTotal = isset($countTotalRow[0]['c'])? (int)$countTotalRow[0]['c'] : 0;
                    $recordsFiltered = isset($countFilteredRow[0]['c'])? (int)$countFilteredRow[0]['c'] : 0;

                    $this->logger->write('Product Controller : list() : DataTables mode - start=' . $start . ', length=' . $length . ', filtered=' . $recordsFiltered, 'r');

                    die(json_encode(array(
                        'draw' => $draw,
                        'recordsTotal' => $recordsTotal,
                        'recordsFiltered' => $recordsFiltered,
                        'data' => $dtls
                    )));
                } catch (Exception $e) {
                    $this->logger->write("Product Controller : list() : The operation to list paged products was not successful. The error message is " . $e->getMessage(), 'r');
                    die(json_encode(array(
                        'draw' => $draw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => array()
                    )));
                }
            }

            if ($name !== '' || !empty($name)) {
                
                $subquery = " '%" . $name . "%' ";
                
                $sql = 'SELECT  p.id "ID",
                        p.erpid "ERP ID",
                        p.erpcode "ERP Code",
                        p.uraproductidentifier "EFRIS Id",
                        p.code "Code",
                        p.name "Name",
                        p.description "Description",
                        FORMAT(p.erpquantity, 0) "ERP Quantity",
                        FORMAT(p.uraquantity, 0) "EFRIS Quantity",
                        p.measureunit "Measure Unit",
                        FORMAT(p.unitprice, 2) "Unit Price",
                        p.currency "Currency",
                        p.commoditycategorycode "Commodity Category Code",
                        p.hasexcisetax "Has Excise Tax",
                        p.stockprewarning "Stock Prewarning",
                        p.piecemeasureunit "Piece Measure Unit",
                        p.havepieceunit "Have Piece Unit",
                        p.pieceunitprice "Piece Unit Price",
                        p.packagescaledvalue "Package Scaled Value",
                        p.piecescaledvalue "Piece Scaled Value",
                        p.excisedutylist "Exciseduty Code",
                        p.disabled "Disabled",
                        p.inserteddt "Creation Date",
                        p.insertedby "Created By",
                        p.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblproductdetails p
                    LEFT JOIN tblusers s ON p.modifiedby = s.id
                    WHERE p.name LIKE ' . $subquery . '
                    ORDER By p.id DESC';
            } else {
                $sql = 'SELECT  p.id "ID",
                        p.erpid "ERP ID",
                        p.erpcode "ERP Code",
                        p.uraproductidentifier "EFRIS Id",
                        p.code "Code",
                        p.name "Name",
                        p.description "Description",
                        FORMAT(p.erpquantity, 0) "ERP Quantity",
                        FORMAT(p.uraquantity, 0) "EFRIS Quantity",
                        p.measureunit "Measure Unit",
                        FORMAT(p.unitprice, 2) "Unit Price",
                        p.currency "Currency",
                        p.commoditycategorycode "Commodity Category Code",
                        p.hasexcisetax "Has Excise Tax",
                        p.stockprewarning "Stock Prewarning",
                        p.piecemeasureunit "Piece Measure Unit",
                        p.havepieceunit "Have Piece Unit",
                        p.pieceunitprice "Piece Unit Price",
                        p.packagescaledvalue "Package Scaled Value",
                        p.piecescaledvalue "Piece Scaled Value",
                        p.excisedutylist "Exciseduty Code",
                        p.disabled "Disabled",
                        p.inserteddt "Creation Date",
                        p.insertedby "Created By",
                        p.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblproductdetails p
                    LEFT JOIN tblusers s ON p.modifiedby = s.id
                    ORDER By p.id DESC';
            }
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Product Controller : list() : The operation to list the products was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Product Controller : index() : The user is not allowed to perform this function", 'r');
        }
                     
        die(json_encode($data));
    }
    
    /**
     *	@name exportproducts
     *  @desc download list of products from eTW
     *	@return NULL
     *	@param NULL
     **/
    function exportproducts() {
        $operation = NULL; //tblevents
        $permission = 'VIEWPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : exportproducts() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $excel = new Sheet();
            $sql = '';
            
            $this->logger->write("Product Controller : exportproducts() : Download list of products", 'r');
            
            $sql = 'SELECT  p.id "ID",
                        p.erpid "ERP ID",
                        p.erpcode "ERP Code",
                        p.uraproductidentifier "EFRIS Id",
                        p.code "Code",
                        p.name "Name",
                        p.description "Description",
                        FORMAT(p.erpquantity, 0) "ERP Quantity",
                        FORMAT(p.uraquantity, 0) "EFRIS Quantity",
                        ru.name "Measure Unit",
                        FORMAT(p.unitprice, 2) "Unit Price",
                        cy.name "Currency",
                        p.commoditycategorycode "Commodity Category Code",
                        cc.commodityname "Commodity Category Name",
                        IFNULL(c.name, "No") "Has Excise Tax",
                        p.stockprewarning "Stock Prewarning",
                        ru1.name "Piece Measure Unit",
                        IFNULL(c1.name, "No") "Have Piece Unit",
                        p.pieceunitprice "Piece Unit Price",
                        p.packagescaledvalue "Package Scaled Value",
                        p.piecescaledvalue "Piece Scaled Value",
                        p.excisedutylist "Exciseduty Code", 
                        IFNULL(c2.name, "No") "Is Exempt", 
                        IFNULL(c3.name, "No") "Is ZeroRated", 
                        p.taxrate "Tax Rate", 
                        pe.name "Exclusion", 
                        IFNULL(c4.name, "No") "Service Mark",
                        p.inserteddt "Creation Date",
                        s1.username "Created By",
                        p.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblproductdetails p
                    LEFT JOIN tblusers s ON p.modifiedby = s.id
                    LEFT JOIN tblusers s1 ON p.insertedby = s1.id
                    LEFT JOIN tblrateunits ru ON p.measureunit = ru.code
                    LEFT JOIN tblrateunits ru1 ON p.piecemeasureunit = ru1.code
                    LEFT JOIN tblcurrencies cy ON p.currency = cy.code
                    LEFT JOIN tblchoices c ON p.hasexcisetax = c.code
                    LEFT JOIN tblchoices c1 ON p.havepieceunit = c1.code
                    LEFT JOIN tblcommoditycategories cc ON p.commoditycategorycode = cc.commoditycode
                    LEFT JOIN tblchoices c2 ON p.isexempt = c2.code
                    LEFT JOIN tblchoices c3 ON p.iszerorated = c3.code
                    LEFT JOIN tblchoices c4 ON p.serviceMark = c4.code
                    LEFT JOIN tblproductexclusioncodes pe ON p.exclusion = pe.code
                    ORDER By p.id DESC';
            
            $this->f3->set('headers',array('ID', 'ERP ID', 'ERP Code', 'EFRIS Id', 'Code', 'Name', 'Description', 'ERP Quantity', 'EFRIS Quantity', 'Measure Unit', 'Unit Price', 'Currency', 'Commodity Category Code', 'Commodity Category Name', 'Has Excise Tax', 'Stock Prewarning', 'Piece Measure Unit', 'Have Piece Unit', 'Piece Unit Price', 'Package Scaled Value', 'Piece Scaled Value', 'Exciseduty Code', 'Is Exempt', 'Is ZeroRated', 'Tax Rate', 'Exclusion', 'Service Mark', 'Creation Date', 'Created By', 'Modified Date', 'Modified By'));
            $dtls = $this->db->exec($sql);
            //$this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('rows', $dtls);
            echo $excel->renderXLS($this->f3->get('rows'), $this->f3->get('headers'),"ProductList.xls");
        } else {
            $this->logger->write("Product Controller : exportproducts() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name searchmeasureunits
     *  @desc List Measure Units
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function searchmeasureunits(){
        $operation = NULL; //tblevents
        $permission = 'LISTMEASUREUNITS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Product Controller : searchmeasureunits() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : searchmeasureunits() : Processing search of measure units started", 'r');
            $name = trim($this->f3->get('POST.name'));
            
            if ($name !== '' || !empty($name)) {
                
                $subquery = " '%" . $name . "%' ";
                
                $sql = 'SELECT  r.id "Id",
                        r.code "Code",
                        r.name "Name",
                        r.description "Description",
                        r.disabled "Disabled"
                    FROM tblrateunits r
                    WHERE r.name LIKE ' . $subquery . '
                    ORDER BY r.id DESC';
            } else {
                $sql = 'SELECT  r.id "Id",
                        r.code "Code",
                        r.name "Name",
                        r.description "Description",
                        r.disabled "Disabled"
                    FROM tblrateunits r
                    ORDER BY r.id DESC';
            }
            
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Product Controller : searchmeasureunits() : The operation to search measure units was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Product Controller : searchmeasureunits() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }

    /**
     *	@name searchcurrencies
     *  @desc List currencies for product Select2 search
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function searchcurrencies(){
        $operation = NULL; //tblevents
        $permission = 'VIEWCURRENCIES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications

        $data = array();

        $this->logger->write("Product Controller : searchcurrencies() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : searchcurrencies() : Processing search of currencies started", 'r');
            $name = trim($this->f3->get('POST.name'));

            if ($name !== '' || !empty($name)) {

                $subquery = " '%" . $name . "%' ";

                $sql = 'SELECT  c.id "Id",
                        c.code "Code",
                        c.name "Name",
                        c.description "Description",
                        c.disabled "Disabled"
                    FROM tblcurrencies c
                    WHERE c.name LIKE ' . $subquery . ' OR c.code LIKE ' . $subquery . '
                    ORDER BY c.id DESC';
            } else {
                $sql = 'SELECT  c.id "Id",
                        c.code "Code",
                        c.name "Name",
                        c.description "Description",
                        c.disabled "Disabled"
                    FROM tblcurrencies c
                    ORDER BY c.id DESC';
            }


            try {
                $dtls = $this->db->exec($sql);

                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Product Controller : searchcurrencies() : The operation to search currencies was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Product Controller : searchcurrencies() : The user is not allowed to perform this function", 'r');
        }

        die(json_encode($data));
    }

    /**
     *	@name searchproducts
     *  @desc List products for invoice Select2 item search
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function searchproducts(){
        $permission = 'VIEWPRODUCTS';
        $data = array();

        $this->logger->write("Product Controller : searchproducts() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : searchproducts() : Processing search of products started", 'r');
            $name = trim($this->f3->get('POST.name'));

            if ($name !== '' || !empty($name)) {
                $subquery = " '%" . $name . "%' ";

                $sql = 'SELECT p.id "Id",
                        p.code "Code",
                        p.name "Name",
                        p.hasexcisetax "HasExciseTax",
                        p.exciseDutyName "ExciseDutyName",
                        p.exciseRate "ExciseRate",
                        p.pack "Pack",
                        p.stick "Stick",
                        p.description "Description",
                        p.disabled "Disabled"
                    FROM tblproductdetails p
                    WHERE p.name LIKE ' . $subquery . ' OR p.code LIKE ' . $subquery . '
                    ORDER BY p.id DESC';
            } else {
                $sql = 'SELECT p.id "Id",
                        p.code "Code",
                        p.name "Name",
                        p.hasexcisetax "HasExciseTax",
                        p.exciseDutyName "ExciseDutyName",
                        p.exciseRate "ExciseRate",
                        p.pack "Pack",
                        p.stick "Stick",
                        p.description "Description",
                        p.disabled "Disabled"
                    FROM tblproductdetails p
                    ORDER BY p.id DESC';
            }

            try {
                $dtls = $this->db->exec($sql);

                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Product Controller : searchproducts() : The operation to search products was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Product Controller : searchproducts() : The user is not allowed to perform this function", 'r');
        }

        die(json_encode($data));
    }

    /**
     *	@name searchexportmeasureunits
     *  @desc List Measure Units
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function searchexportmeasureunits(){
        $operation = NULL; //tblevents
        $permission = 'LISTMEASUREUNITS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Product Controller : searchexportmeasureunits() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : searchexportmeasureunits() : Processing search of export measure units started", 'r');
            $name = trim($this->f3->get('POST.name'));
            
            if ($name !== '' || !empty($name)) {
                
                $subquery = " '%" . $name . "%' ";
                
                $sql = 'SELECT  r.id "Id",
                        r.code "Code",
                        r.name "Name",
                        r.description "Description",
                        r.disabled "Disabled"
                    FROM tblexportrateunits r
                    WHERE r.name LIKE ' . $subquery . '
                    ORDER BY r.id DESC';
            } else {
                $sql = 'SELECT  r.id "Id",
                        r.code "Code",
                        r.name "Name",
                        r.description "Description",
                        r.disabled "Disabled"
                    FROM tblexportrateunits r
                    ORDER BY r.id DESC';
            }
            
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Product Controller : searchexportmeasureunits() : The operation to search export measure units was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Product Controller : searchexportmeasureunits() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
    
   
    /**
     *	@name searchhscodes
     *  @desc List Measure Units
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function searchhscodes(){
        $operation = NULL; //tblevents
        $permission = 'LISTHSCODES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Product Controller : searchhscodes() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : searchhscodes() : Processing search of hs codes started", 'r');
            $name = trim($this->f3->get('POST.name'));
            
            if ($name !== '' || !empty($name)) {
                
                $subquery = " '%" . $name . "%' ";
                
                $sql = 'SELECT  r.id "Id",
                        r.code "Code",
                        r.name "Name",
                        r.description "Description",
                        r.disabled "Disabled"
                    FROM tblhscodes r
                    WHERE r.name LIKE ' . $subquery . '
                    ORDER BY r.id DESC';
            } else {
                $sql = 'SELECT  r.id "Id",
                        r.code "Code",
                        r.name "Name",
                        r.description "Description",
                        r.disabled "Disabled"
                    FROM tblhscodes r
                    ORDER BY r.id DESC';
            }
            
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Product Controller : searchhscodes() : The operation to search hs codes was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Product Controller : searchhscodes() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
    
    /**
     *	@name searchcommoditycodes
     *  @desc List Measure Units
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function searchcommoditycodes(){
        $operation = NULL; //tblevents
        $permission = 'LISTCOMMODITYCODES'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Product Controller : searchcommoditycodes() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : searchcommoditycodes() : Processing search of commodity codes started", 'r');
            $name = trim($this->f3->get('POST.name'));
            
            if ($name !== '' || !empty($name)) {
                
                $subquery = " '%" . $name . "%' ";
                
                $sql = 'SELECT  c.id "Id",
                        c.commoditycode "Code",
                        c.commodityname "Name",
                        c.disabled "Disabled"
                    FROM tblcommoditycategories c
                    WHERE c.commodityname LIKE ' . $subquery . '
                    ORDER BY c.id DESC';
            } else {
                $sql = 'SELECT  c.id "Id",
                        c.commoditycode "Code",
                        c.commodityname "Name",
                        c.disabled "Disabled"
                    FROM tblcommoditycategories c
                    ORDER BY c.id DESC';
            }
            
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ($dtls as $obj) {
                    $data[] = $obj;
                }
            } catch (Exception $e) {
                $this->logger->write("Product Controller : searchcommoditycodes() : The operation to search commodity codes was not successful. The error message is " . $e->getMessage(), 'r');
            }
        } else {
            $this->logger->write("Product Controller : searchcommoditycodes() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
    
    /**
     *	@name uploadproduct
     *  @desc upload a product to EFRIS
     *	@return
     *	@param 
     **/
    function uploadproduct(){
        $operation = NULL; //tblevents
        $permission = 'UPLOADPRODUCT'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.uploadproductid');
        $product = new products($this->db);
        $product->getByID($id);
        $this->logger->write("Product Controller : uploadproduct() : The product id is " . $this->f3->get('POST.uploadproductid'), 'r');

        $validationErrors = $this->getExcisePieceValidationErrors(
            $product->hasexcisetax,
            $product->excisedutylist,
            $product->havepieceunit,
            $product->piecemeasureunit,
            $product->pieceunitprice,
            $product->packagescaledvalue,
            $product->piecescaledvalue
        );
        if (!empty($validationErrors)) {
            self::$systemalert = implode(' ', $validationErrors);
            $this->logger->write('Product Controller : uploadproduct() : Excise-piece validation failed. ' . self::$systemalert, 'r');
            $this->f3->set('systemalert', self::$systemalert);
            self::view($id);
            exit();
        }
        
        $this->logger->write("Product Controller : uploadproduct() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            if ($product->uraproductidentifier) {
                $this->logger->write("Product Controller : uploadproduct() : This product is already uploaded", 'r');
                $this->logger->write("Product Controller : uploadproduct() : Updating product started", 'r');
                
                $data = $this->util->updateproduct($this->f3->get('SESSION.id'), $id);//will return JSON.
                
                //self::$systemalert = "This product is already uploaded";
                //$this->f3->set('systemalert', self::$systemalert);
                //self::view($id);
            } else {
                $data = $this->util->uploadproduct($this->f3->get('SESSION.id'), $id);//will return JSON.
                //var_dump($data);
            }

            $data = json_decode($data, true);
            //var_dump($data);
            
            if (isset($data['returnCode'])){
                $this->logger->write("Product Controller : uploadproduct() : The operation to upload/update the product was not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to upload/update the product by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to upload/update the product by " . $this->f3->get('SESSION.username') . " was not successful. The error message is " . $data['returnMessage'];
            } else {
                if (empty($data)) {
                    //Fetch the details from EFRIS
                    $n_data = $this->util->fetchproduct($this->f3->get('SESSION.id'), $id);//will return JSON.
                    //var_dump($data);
                    $n_data = json_decode($n_data, true);
                    
                    if(isset($n_data['records'])){
                        
                        if ($n_data['records']) {
                            foreach($n_data['records'] as $elem){
                                
                                
                                try{
                                    $disabled = '0';
                                    $productcode = $elem['goodsCode'];
                                    $productid = $elem['id'];
                                    
                                    $isexempt = $elem['isExempt'];
                                    $iszerorated = $elem['isZeroRate'];
                                    $taxrate = $elem['taxRate'];
                                    $statuscode = $elem['statusCode'];
                                    $source = $elem['source'];
                                    $exclusion = $elem['exclusion'];
                                    $servicemark = $elem['serviceMark'];
                                    $haveExciseTax = !isset($elem['haveExciseTax'])? 'NULL' : $elem['haveExciseTax'];
                                    $exciseDutyCode = !isset($elem['exciseDutyCode'])? '' : trim((string)$elem['exciseDutyCode']);
                                    $exciseDutyCodeSql = ($exciseDutyCode === '' || strtoupper($exciseDutyCode) === 'NULL')
                                        ? 'NULL'
                                        : '"' . addslashes($exciseDutyCode) . '"';

                                    $havePieceUnit = !isset($elem['havePieceUnit'])? 'NULL' : $elem['havePieceUnit'];

                                    $pieceMeasureUnitSql = 'piecemeasureunit';
                                    if (array_key_exists('pieceMeasureUnit', $elem)) {
                                        $pieceMeasureUnitSql = trim((string)$elem['pieceMeasureUnit']) === ''
                                            ? 'NULL'
                                            : '"' . addslashes(trim((string)$elem['pieceMeasureUnit'])) . '"';
                                    }

                                    $pieceUnitPriceSql = 'pieceunitprice';
                                    if (array_key_exists('pieceUnitPrice', $elem)) {
                                        $pieceUnitPriceValue = trim((string)$elem['pieceUnitPrice']);
                                        $pieceUnitPriceSql = ($pieceUnitPriceValue === '' || strtoupper($pieceUnitPriceValue) === 'NULL' || !is_numeric($pieceUnitPriceValue))
                                            ? 'NULL'
                                            : $pieceUnitPriceValue;
                                    }

                                    $packageScaledValueSql = 'packagescaledvalue';
                                    if (array_key_exists('packageScaledValue', $elem)) {
                                        $packageScaledValueValue = trim((string)$elem['packageScaledValue']);
                                        $packageScaledValueSql = ($packageScaledValueValue === '' || strtoupper($packageScaledValueValue) === 'NULL' || !is_numeric($packageScaledValueValue))
                                            ? 'NULL'
                                            : $packageScaledValueValue;
                                    }

                                    $pieceScaledValueSql = 'piecescaledvalue';
                                    if (array_key_exists('pieceScaledValue', $elem)) {
                                        $pieceScaledValueValue = trim((string)$elem['pieceScaledValue']);
                                        $pieceScaledValueSql = ($pieceScaledValueValue === '' || strtoupper($pieceScaledValueValue) === 'NULL' || !is_numeric($pieceScaledValueValue))
                                            ? 'NULL'
                                            : $pieceScaledValueValue;
                                    }

                                    $packSql = 'pack';
                                    if (array_key_exists('pack', $elem) || array_key_exists('Pack', $elem)) {
                                        $packValue = array_key_exists('pack', $elem)
                                            ? trim((string)$elem['pack'])
                                            : trim((string)$elem['Pack']);
                                        $packSql = ($packValue === '' || strtoupper($packValue) === 'NULL' || !is_numeric($packValue))
                                            ? 'NULL'
                                            : $packValue;
                                    }

                                    $stickSql = 'stick';
                                    if (array_key_exists('stick', $elem) || array_key_exists('Stick', $elem)) {
                                        $stickValue = array_key_exists('stick', $elem)
                                            ? trim((string)$elem['stick'])
                                            : trim((string)$elem['Stick']);
                                        $stickSql = ($stickValue === '' || strtoupper($stickValue) === 'NULL' || !is_numeric($stickValue))
                                            ? 'NULL'
                                            : $stickValue;
                                    }
                                    
                                    if ($statuscode == '102') {
                                        $disabled = '1';//Disable product from drop downs in eTW
                                    }
                                    
                                    // 2026-04-12 11:52:00 +03:00 - Sync updates now persist excise to canonical column only.
                                    
                                    $this->db->exec(array('UPDATE tblproductdetails SET disabled = ' . $disabled . 
                                                                                    ', isexempt = ' . $isexempt . 
                                                                                    ', iszerorated = ' . $iszerorated . 
                                                                                    ', source = ' . $source . 
                                                                                    ', exclusion = ' . $exclusion . 
                                                                                    ', statuscode = ' . $statuscode . 
                                                                                    ', taxrate = ' . $taxrate . 
                                                                                    ', serviceMark = ' . $servicemark . 
                                                                                    ', hasexcisetax = ' . $haveExciseTax . 
                                                                                    ', exciseDutyCode = ' . $exciseDutyCodeSql . 
                                                                                    ', havepieceunit = ' . $havePieceUnit . 
                                                                                    ', piecemeasureunit = ' . $pieceMeasureUnitSql . 
                                                                                    ', pieceunitprice = ' . $pieceUnitPriceSql . 
                                                                                    ', packagescaledvalue = ' . $packageScaledValueSql . 
                                                                                    ', piecescaledvalue = ' . $pieceScaledValueSql . 
                                                                                    ', pack = ' . $packSql . 
                                                                                    ', stick = ' . $stickSql . 
                                                                                    ', uraproductidentifier = ' . $productid . 
                                                                                    ', modifieddt = "' .  date('Y-m-d H:i:s') . 
                                                                                    '", modifiedby = ' . $this->f3->get('SESSION.id') . 
                                                                                    ' WHERE TRIM(code) = "' . $productcode . '"'));
                                    
                                    
                                    if ($elem['goodsOtherUnits']) {
                                        $this->logger->write("Product Controller : uploadproduct() : The product has other units", 'r');
                                        
                                        
                                        foreach($elem['goodsOtherUnits'] as $oUnits){
                                            /*"commodityGoodsId":"517272108910432332",
                                             "dateFormat":"dd/MM/yyyy",
                                             "id":"158301922650174456",
                                             "nowTime":"2021/06/08 18:24:52",
                                             "otherPrice":"700",
                                             "otherScaled":"50",
                                             "otherUnit":"107",
                                             "packageScaled":"1",
                                             "pageIndex":0,
                                             "pageNo":0,
                                             "pageSize":0,
                                             "timeFormat":"dd/MM/yyyy HH24:mi:ss"*/
                                            
                                            
                                            $otherPrice = $oUnits['otherPrice']; //700",
                                            $otherScaled = $oUnits['otherScaled']; //50",
                                            $otherUnit = $oUnits['otherUnit']; //107",
                                            $packageScaled = $oUnits['packageScaled']; //1",
                                            
                                            $ou_check = new DB\SQL\Mapper($this->db, 'tblotherunits');
                                            $ou_check->load(array('productid=? AND otherunit=?', $id, $otherUnit));
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                            
                                            if($ou_check->dry ()){
                                                $this->logger->write("Product Controller : uploadproduct() : The unit does not exist. Proceed and insert", 'r');
                                                
                                                try {
                                                    $this->db->exec(array('INSERT INTO tblotherunits
                                                                    (otherPrice,
                                                                    otherscaled,
                                                                    otherunit,
                                                                    productid,
                                                                    packagescaled,
                                                                    inserteddt,
                                                                    insertedby,
                                                                    modifieddt,
                                                                    modifiedby)
                                                                    VALUES
                                                                    (' . $otherPrice . ',
                                                                    ' . $otherScaled . ',
                                                                    ' . $otherUnit . ',
                                                                    ' . $id . ',
                                                                    ' . $packageScaled . ', NOW(),
                                                                    ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                    ' . $this->f3->get('SESSION.id') . ')'));
                                                    $this->logger->write($this->db->log(TRUE), 'r');
                                                } catch (Exception $e) {
                                                    $this->logger->write("Product Controller : uploadproduct() : The operation to insert into table tblotherunits was not successful. The error message is " . $e->getMessage(), 'r');
                                                }
                                            } else {
                                                $this->logger->write("Product Controller : uploadproduct() : The rate exists. Proceed and update", 'r');
                                                
                                                try {
                                                    $this->db->exec(array('UPDATE tblotherunits SET otherPrice = ' . $otherPrice .
                                                                                                ', otherscaled = ' . $otherScaled .
                                                                                                ', packagescaled = ' . $packageScaled .
                                                                                                ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                                                ' WHERE otherunit = ' . $otherUnit . ' AND id = ' . $id));
                                                    $this->logger->write($this->db->log(TRUE), 'r');
                                                } catch (Exception $e) {
                                                    $this->logger->write("Product Controller : uploadproduct() : The operation to update the table tblotherunits was not successful. The error message is " . $e->getMessage(), 'r');
                                                }
                                            }
                                            
                                            
                                        }
                                    }
                                    
                                    
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Product ID - " . $elem['Product'] . " - was fetched successfully by " . $this->f3->get('SESSION.username'));
                                    $this->logger->write("Product Controller : uploadproduct() : Product ID - " . $id . " - was fetched & attributes updated successfully by " . $this->f3->get('SESSION.username'), 'r');
                                    //self::$systemalert = "Product ID - " . $id . " - was fetched & attributes updated successfully by " . $this->f3->get('SESSION.username');
                                } catch (Exception $e) {
                                    $this->logger->write("Product Controller : uploadproduct() : The operation to fetch the product was not successful. The error message is " . $e->getMessage(), 'r');
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful");
                                    //self::$systemalert = "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful";
                                }
                            }
                        } else {
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the product first");
                            //self::$systemalert = "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the product first";
                        }
                    }
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Product ID - " . $elem['Product'] . " - was uploaded successfully by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "Product ID - " . $id . " - was uploaded/updated successfully by " . $this->f3->get('SESSION.username');
                } else {
                    foreach($data as $elem){
                        $this->logger->write("Product Controller : uploadproduct() : The operation to upload/update the product was not successful. The error message is " . $elem['returnMessage'], 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to upload/update the product by " . $this->f3->get('SESSION.username') . " was not successful");
                        self::$systemalert = "The operation to upload/update the product by " . $this->f3->get('SESSION.username') . " was not successful. The error message is " . $elem['returnMessage'];
                    }
                }
            }
            
            
        } else {
            $this->logger->write("Product Controller : uploadproduct() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name stockquery
     *  @desc query stock of a product to EFRIS
     *	@return
     *	@param
     **/
    function stockquery(){
        $operation = NULL; //tblevents
        $permission = 'QUERYPRODUCTSTOCK'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.stockqueryproductid');
        $product = new products($this->db);
        $product->getByID($id);
        $this->logger->write("Product Controller : stockquery() : The product id is " . $this->f3->get('POST.stockqueryproductid'), 'r');
        
        $this->logger->write("Product Controller : stockquery() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            $n_data = $this->util->queryproduct($this->f3->get('SESSION.id'), $id);//will return JSON.
            //var_dump($data);
            $n_data = json_decode($n_data, true);
            
            if (isset($n_data['returnCode'])){
                $this->logger->write("Product Controller : stockquery() : The operation to query the stock was not successful. The error message is " . $n_data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to query the stock by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to query the stock was not successful. The error message is " . $n_data['returnMessage'];
            } else {
                if ($n_data) {
                    
                    $this->logger->write("Product Controller : stockquery() : The stock is " . $n_data['stock'], 'r');
                    $this->logger->write("Product Controller : stockquery() : The stockPrewarning is " . $n_data['stockPrewarning'], 'r');
                    
                    if ($n_data['stock']) {
                        $stock = !isset($n_data['stock'])? 'NULL' : $n_data['stock'];
                        $stockPrewarning = !isset($n_data['stockPrewarning'])? 'NULL' : $n_data['stockPrewarning'];
                        
                        try{
                            
                            $this->db->exec(array('UPDATE tblproductdetails SET uraquantity = ' . $stock .
                                ', stockprewarning = ' . $stockPrewarning .
                                ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                ' WHERE id = ' . $id));
                            
                            $this->logger->write($this->db->log(TRUE), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Product ID - " . $id . " - was fetched successfully by " . $this->f3->get('SESSION.username'));
                            $this->logger->write("Product Controller : stockquery() : Product ID - " . $id . " - was queried & attributes updated successfully by " . $this->f3->get('SESSION.username'), 'r');
                            self::$systemalert = "Product ID - " . $id . " - was queried & attributes updated successfully by " . $this->f3->get('SESSION.username');
                        } catch (Exception $e) {
                            $this->logger->write("Product Controller : stockquery() : The operation to query the product was not successful. The error message is " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to query the stock was not successful";
                        }
                    } else {
                        $this->logger->write("Product Controller : stockquery() : The operation to query the product was not successful. There is NO stock information!", 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful. There is NO stock information!");
                        self::$systemalert = "The operation to query the stock was not successful. There is NO stock information!";
                    }
                    
                    
                } else {
                    $this->logger->write("Product Controller : stockquery() : The API did not return anything", 'r');
                    self::$systemalert = "The operation to query the stock was not successful";
                }
            }
            
            
            
        } else {
            $this->logger->write("Product Controller : stockquery() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    

    /**
     *	@name stocktransfer
     *  @desc transfer stock of a product to EFRIS
     *	@return
     *	@param
     **/
    function stocktransfer(){
        $operation = NULL; //tblevents
        $permission = 'TRANSFERPRODUCTSTOCK'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.transferproductid');
        $product = new products($this->db);
        $product->getByID($id);
        
        $sourcebranch = $this->f3->get('POST.transfersourcebranch');
        $destinationbranch = $this->f3->get('POST.transferdestinationbranch');
        $qty = $this->f3->get('POST.transferproductqty');
        $remarks = $this->f3->get('POST.transferremarks');
        
        $this->logger->write("Product Controller : stocktransfer() : The product id is " . $this->f3->get('POST.transferproductid'), 'r');
        
        $this->logger->write("Product Controller : stocktransfer() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            $n_data = $this->util->transferproductstock($this->f3->get('SESSION.id'), $id, $sourcebranch, $destinationbranch, $qty, $remarks);//will return JSON.
            //var_dump($data);
            $n_data = json_decode($n_data, true);
            
            if (isset($n_data['returnCode'])){
                $this->logger->write("Product Controller : stocktransfer() : The operation to transfer the stock was not successful. The error message is " . $n_data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to transfer the stock by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to transfer the stock was not successful. The error message is " . $n_data['returnMessage'];
            } else {
                if ($n_data) {
                    
                    foreach($n_data as $elem){
                                           
                        if (isset($elem['returnCode'])){
                            $this->logger->write("Product Controller : stocktransfer() : The operation to transfer the stock was not successful. The error message is " . $elem['returnMessage'], 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to transfer the stock by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to transfer the stock was not successful. The error message is " . $elem['returnMessage'];
                        } else {
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Product ID - " . $id . " - was transferred successfully by " . $this->f3->get('SESSION.username'));
                            $this->logger->write("Product Controller : stocktransfer() : Product ID - " . $id . " - was queried & attributes updated successfully by " . $this->f3->get('SESSION.username'), 'r');
                            self::$systemalert = "Product ID - " . $id . " - stock was transferred successfully by " . $this->f3->get('SESSION.username');
                        }
                    }
                    
                    
                } else {
                    $this->logger->write("Product Controller : stocktransfer() : The API did not return anything", 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Product ID - " . $id . " - was transferred successfully by " . $this->f3->get('SESSION.username'));
                    $this->logger->write("Product Controller : stocktransfer() : Product ID - " . $id . " - was queried & attributes updated successfully by " . $this->f3->get('SESSION.username'), 'r');
                    self::$systemalert = "Stock was transferred successfully by " . $this->f3->get('SESSION.username');
                }
            }
            
            
            
        } else {
            $this->logger->write("Product Controller : stocktransfer() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name fetchproduct
     *  @desc fetch a product from EFRIS
     *	@return
     *	@param
     **/
    function fetchproduct(){
        $operation = NULL; //tblevents
        $permission = 'FETCHPRODUCT'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.fetchproductid');
        $product = new products($this->db);
        $product->getByID($id);
        $this->logger->write("Product Controller : fetchproduct() : The product id is " . $this->f3->get('POST.fetchproductid'), 'r');
        
        $this->logger->write("Product Controller : fetchproduct() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            $data = $this->util->fetchproduct($this->f3->get('SESSION.id'), $id);//will return JSON.
            //var_dump($data);
            $data = json_decode($data, true);
            
            if(isset($data['records'])){

                // 2026-04-11 18:55:55 +03:00 - One-time cleanup path to normalize historical blank/NULL-string values to SQL NULL for export/piece code fields.
                // This is idempotent and keeps existing records consistent with current fetchproduct write normalization.
                $this->db->exec(array('UPDATE tblproductdetails
                                        SET piecemeasureunit = CASE
                                                WHEN UPPER(TRIM(IFNULL(piecemeasureunit, ""))) IN ("", "NULL") THEN NULL
                                                ELSE TRIM(piecemeasureunit)
                                            END,
                                            hscode = CASE
                                                WHEN UPPER(TRIM(IFNULL(hscode, ""))) IN ("", "NULL") THEN NULL
                                                ELSE TRIM(hscode)
                                            END,
                                            customsmeasureunit = CASE
                                                WHEN UPPER(TRIM(IFNULL(customsmeasureunit, ""))) IN ("", "NULL") THEN NULL
                                                ELSE TRIM(customsmeasureunit)
                                            END
                                        WHERE TRIM(IFNULL(piecemeasureunit, "")) = ""
                                           OR TRIM(IFNULL(hscode, "")) = ""
                                           OR TRIM(IFNULL(customsmeasureunit, "")) = ""
                                           OR UPPER(TRIM(IFNULL(piecemeasureunit, ""))) = "NULL"
                                           OR UPPER(TRIM(IFNULL(hscode, ""))) = "NULL"
                                           OR UPPER(TRIM(IFNULL(customsmeasureunit, ""))) = "NULL"'));
                
                if ($data['records']) {
                    foreach($data['records'] as $elem){
                        $this->logger->write("Product Controller : fetchproduct() : The product is: " . $elem['goodsCode'], 'r');
                        
                        try{
                            /**
                             {
                                  "commodityCategoryCode":"30171711",
                                  "commodityCategoryName":"Corrugated glass",
                                  "createDate":1662361830000,
                                  "currency":"101",
                                  "dateFormat":"dd/MM/yyyy",
                                  "exclusion":"2",
                                  "goodsCode":"21",
                                  "goodsName":"test-product-120",
                                  "goodsTypeCode":"101",
                                  "haveExciseTax":"102",
                                  "haveOtherUnit":"102",
                                  "havePieceUnit":"102",
                                  "id":"286431815711432064",
                                  "isExempt":"102",
                                  "isZeroRate":"102",
                                  "measureUnit":"PP",
                                  "nowTime":"2022/09/23 14:05:02",
                                  "pageIndex":0,
                                  "pageNo":0,
                                  "pageSize":0,
                                  "remarks":"test-product-120 description",
                                  "serviceMark":"102",
                                  "source":"102",
                                  "statusCode":"101",
                                  "stock":"100",
                                  "stockPrewarning":"10",
                                  "taxRate":"0.18",
                                  "timeFormat":"dd/MM/yyyy HH24:mi:ss",
                                  "unitPrice":"1200",
                                  "updateDateStr":"05/09/2022 10:10:30"
                               }
                             */
                            $disabled = '0';
                            $productcode = $elem['goodsCode'];
                            $productid = $elem['id'];
                            //$uraquantity =  $elem['stock'];
                            
                            $isexempt = !isset($elem['isExempt'])? 'NULL' : $elem['isExempt'];
                            $iszerorated = !isset($elem['isZeroRate'])? 'NULL' : $elem['isZeroRate'];
                            $taxrate = !isset($elem['taxRate'])? 'NULL' : $elem['taxRate'];
                            $statuscode = !isset($elem['statusCode'])? 'NULL' : $elem['statusCode'];
                            $source = !isset($elem['source'])? 'NULL' : $elem['source'];
                            $exclusion = !isset($elem['exclusion'])? 'NULL' : $elem['exclusion'];
                            $servicemark = !isset($elem['serviceMark'])? 'NULL' : $elem['serviceMark'];
                            
                            $commodityCategoryCode = !isset($elem['commodityCategoryCode'])? '' : $elem['commodityCategoryCode']; //":"30171711",
                            $currency = !isset($elem['currency'])? 'NULL' : $elem['currency']; //":"101",
                            $goodsName = !isset($elem['goodsName'])? '' : $elem['goodsName']; //":"test-product-120",
                            $goodsTypeCode = !isset($elem['goodsTypeCode'])? 'NULL' : $elem['goodsTypeCode']; //":"101",
                            $haveExciseTax = !isset($elem['haveExciseTax'])? 'NULL' : $elem['haveExciseTax']; //":"102",
                            $exciseDutyCode = !isset($elem['exciseDutyCode'])? '' : trim((string)$elem['exciseDutyCode']);
                            $exciseDutyCodeSql = ($exciseDutyCode === '' || strtoupper($exciseDutyCode) === 'NULL')
                                ? 'NULL'
                                : '"' . addslashes($exciseDutyCode) . '"';
                            $havePieceUnit = !isset($elem['havePieceUnit'])? 'NULL' : $elem['havePieceUnit']; //":"102",
                            $measureUnit = !isset($elem['measureUnit'])? 'NULL' : $elem['measureUnit']; //":"PP",
                            $remarks = !isset($elem['remarks'])? 'Product/Service synchronised through the API' : $elem['remarks']; //":"test-product-120 description",
                            $stockPrewarning = !isset($elem['stockPrewarning'])? 'NULL' : $elem['stockPrewarning']; //":"10",
                            $unitPrice = !isset($elem['unitPrice'])? 'NULL' : $elem['unitPrice']; //":"10",
                            $stock = !isset($elem['stock'])? 'NULL' : $elem['stock']; //":"10",
                            $haveOtherUnit = !isset($elem['haveOtherUnit'])? 'NULL' : $elem['haveOtherUnit']; //":"10",

                            // 2026-04-11 18:47:56 +03:00 - Normalize optional code fields to avoid persisting empty strings.
                            $measureUnitSql = (isset($elem['measureUnit']) && trim((string)$elem['measureUnit']) !== '')
                                ? '"' . addslashes(trim((string)$elem['measureUnit'])) . '"'
                                : 'NULL';

                            $pieceMeasureUnitSql = 'NULLIF(piecemeasureunit, "")';
                            if (array_key_exists('pieceMeasureUnit', $elem)) {
                                $pieceMeasureUnitSql = trim((string)$elem['pieceMeasureUnit']) === ''
                                    ? 'NULL'
                                    : '"' . addslashes(trim((string)$elem['pieceMeasureUnit'])) . '"';
                            }

                            $pieceUnitPriceSql = 'pieceunitprice';
                            if (array_key_exists('pieceUnitPrice', $elem)) {
                                $pieceUnitPriceValue = trim((string)$elem['pieceUnitPrice']);
                                $pieceUnitPriceSql = ($pieceUnitPriceValue === '' || strtoupper($pieceUnitPriceValue) === 'NULL' || !is_numeric($pieceUnitPriceValue))
                                    ? 'NULL'
                                    : $pieceUnitPriceValue;
                            }

                            $packageScaledValueSql = 'packagescaledvalue';
                            if (array_key_exists('packageScaledValue', $elem)) {
                                $packageScaledValueValue = trim((string)$elem['packageScaledValue']);
                                $packageScaledValueSql = ($packageScaledValueValue === '' || strtoupper($packageScaledValueValue) === 'NULL' || !is_numeric($packageScaledValueValue))
                                    ? 'NULL'
                                    : $packageScaledValueValue;
                            }

                            $pieceScaledValueSql = 'piecescaledvalue';
                            if (array_key_exists('pieceScaledValue', $elem)) {
                                $pieceScaledValueValue = trim((string)$elem['pieceScaledValue']);
                                $pieceScaledValueSql = ($pieceScaledValueValue === '' || strtoupper($pieceScaledValueValue) === 'NULL' || !is_numeric($pieceScaledValueValue))
                                    ? 'NULL'
                                    : $pieceScaledValueValue;
                            }

                            $packSql = 'pack';
                            if (array_key_exists('pack', $elem) || array_key_exists('Pack', $elem)) {
                                $packValue = array_key_exists('pack', $elem)
                                    ? trim((string)$elem['pack'])
                                    : trim((string)$elem['Pack']);
                                $packSql = ($packValue === '' || strtoupper($packValue) === 'NULL' || !is_numeric($packValue))
                                    ? 'NULL'
                                    : $packValue;
                            }

                            $stickSql = 'stick';
                            if (array_key_exists('stick', $elem) || array_key_exists('Stick', $elem)) {
                                $stickValue = array_key_exists('stick', $elem)
                                    ? trim((string)$elem['stick'])
                                    : trim((string)$elem['Stick']);
                                $stickSql = ($stickValue === '' || strtoupper($stickValue) === 'NULL' || !is_numeric($stickValue))
                                    ? 'NULL'
                                    : $stickValue;
                            }

                            $hsCodeSql = 'NULLIF(hscode, "")';
                            if (array_key_exists('hsCode', $elem)) {
                                $hsCodeSql = trim((string)$elem['hsCode']) === ''
                                    ? 'NULL'
                                    : '"' . addslashes(trim((string)$elem['hsCode'])) . '"';
                            }

                            $customsMeasureUnitSql = 'NULLIF(customsmeasureunit, "")';
                            if (array_key_exists('customsMeasureUnit', $elem)) {
                                $customsMeasureUnitSql = trim((string)$elem['customsMeasureUnit']) === ''
                                    ? 'NULL'
                                    : '"' . addslashes(trim((string)$elem['customsMeasureUnit'])) . '"';
                            }
                            
                            if ($statuscode == '102') {
                                $disabled = '1';//Disable product from drop downs in eTW
                            }
                            
                            $this->db->exec(array('UPDATE tblproductdetails SET disabled = ' . $disabled . 
                                                                            ', isexempt = ' . $isexempt . 
                                                                            ', iszerorated = ' . $iszerorated . 
                                                                            ', source = ' . $source . 
                                                                            ', exclusion = ' . $exclusion . 
                                                                            ', statuscode = ' . $statuscode . 
                                                                            ', serviceMark = ' . $servicemark . 
                                                                            ', taxrate = ' . $taxrate . 
                                                                            ', uraproductidentifier = ' . $productid . 
                                                                            ', commoditycategorycode = "' . $commodityCategoryCode . 
                                                                            '", currency = ' . $currency . 
                                                                            ', name = "' . addslashes($goodsName) . 
                                                                            '", goodsTypeCode = ' . $goodsTypeCode . 
                                                                            ', hasexcisetax = ' . $haveExciseTax . 
                                                                            ', exciseDutyCode = ' . $exciseDutyCodeSql . 
                                                                            ', havepieceunit = ' . $havePieceUnit . 
                                                                            ', measureunit = ' . $measureUnitSql . 
                                                                            ', piecemeasureunit = ' . $pieceMeasureUnitSql . 
                                                                            ', pieceunitprice = ' . $pieceUnitPriceSql . 
                                                                            ', packagescaledvalue = ' . $packageScaledValueSql . 
                                                                            ', piecescaledvalue = ' . $pieceScaledValueSql . 
                                                                            ', pack = ' . $packSql . 
                                                                            ', stick = ' . $stickSql . 
                                                                            ', hscode = ' . $hsCodeSql . 
                                                                            ', customsmeasureunit = ' . $customsMeasureUnitSql . 
                                                                            ', remarks = "' . addslashes($remarks) . 
                                                                            '", stockprewarning = ' . $stockPrewarning . 
                                                                            ', unitprice = ' . $unitPrice . 
                                                                            ', uraquantity = ' . $stock . 
                                                                            ', haveotherunit = ' . $haveOtherUnit . 
                                                                            // 2026-04-12 11:52:00 +03:00 - Persist canonical excise field only; legacy `excisedutylist` is read-fallback only.
                                                                            ', modifieddt = "' .  date('Y-m-d H:i:s') . 
                                                                            '", modifiedby = ' . $this->f3->get('SESSION.id') . 
                                                                            ' WHERE TRIM(code) = "' . $productcode . '"'));
                            
                            
                            if ($elem['goodsOtherUnits']) {
                                $this->logger->write("Product Controller : fetchproduct() : The product has other units", 'r');
                                                               
                                
                                foreach($elem['goodsOtherUnits'] as $oUnits){
                                    /*"commodityGoodsId":"517272108910432332",
                                    "dateFormat":"dd/MM/yyyy",
                                    "id":"158301922650174456",
                                    "nowTime":"2021/06/08 18:24:52",
                                    "otherPrice":"700",
                                    "otherScaled":"50",
                                    "otherUnit":"107",
                                    "packageScaled":"1",
                                    "pageIndex":0,
                                    "pageNo":0,
                                    "pageSize":0,
                                    "timeFormat":"dd/MM/yyyy HH24:mi:ss"*/
                                    
                                    
                                    $otherPrice = $oUnits['otherPrice']; //700",
                                    $otherScaled = $oUnits['otherScaled']; //50",
                                    $otherUnit = $oUnits['otherUnit']; //107",
                                    $packageScaled = $oUnits['packageScaled']; //1",
                                    
                                    $ou_check = new DB\SQL\Mapper($this->db, 'tblotherunits');
                                    $ou_check->load(array('productid=? AND otherunit=?', $id, $otherUnit));
                                    $this->logger->write($this->db->log(TRUE), 'r');
                                    
                                    if($ou_check->dry ()){
                                        $this->logger->write("Product Controller : fetchproduct() : The unit does not exist. Proceed and insert", 'r');
                                        
                                        try {
                                            $this->db->exec(array('INSERT INTO tblotherunits
                                                                    (otherPrice,
                                                                    otherscaled,
                                                                    otherunit,
                                                                    productid,
                                                                    packagescaled,
                                                                    inserteddt,
                                                                    insertedby,
                                                                    modifieddt,
                                                                    modifiedby)
                                                                    VALUES
                                                                    (' . $otherPrice . ',
                                                                    ' . $otherScaled . ',
                                                                    ' . $otherUnit . ',
                                                                    ' . $id . ',
                                                                    ' . $packageScaled . ', NOW(),
                                                                    ' . $this->f3->get('SESSION.id') . ', NOW(),
                                                                    ' . $this->f3->get('SESSION.id') . ')'));
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                        } catch (Exception $e) {
                                            $this->logger->write("Product Controller : fetchproduct() : The operation to insert into table tblotherunits was not successful. The error message is " . $e->getMessage(), 'r');
                                        }
                                    } else {
                                        $this->logger->write("Product Controller : fetchproduct() : The rate exists. Proceed and update", 'r');
                                        
                                        try {
                                            $this->db->exec(array('UPDATE tblotherunits SET otherPrice = ' . $otherPrice .
                                                                                            ', otherscaled = ' . $otherScaled .
                                                                                            ', packagescaled = ' . $packageScaled .
                                                                                            ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                                            ' WHERE otherunit = ' . $otherUnit . ' AND id = ' . $id));
                                            $this->logger->write($this->db->log(TRUE), 'r');
                                        } catch (Exception $e) {
                                            $this->logger->write("Product Controller : fetchproduct() : The operation to update the table tblotherunits was not successful. The error message is " . $e->getMessage(), 'r');
                                        }
                                    }
                                    
                                    
                                }
                            }
                            
                            
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Product ID - " . $elem['Product'] . " - was fetched successfully by " . $this->f3->get('SESSION.username'));
                            self::$systemalert = "Product ID - " . $id . " - was fetched & attributes updated successfully by " . $this->f3->get('SESSION.username');
                        } catch (Exception $e) {
                            $this->logger->write("Product Controller : fetchproduct() : The operation to fetch the product was not successful. The error message is " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful";
                        }
                    }
                } else {
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the product first");
                    self::$systemalert = "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the product first";
                }
                
                
            } elseif (isset($data['returnCode'])){
                $this->logger->write("Product Controller : fetchproduct() : The operation to fetch the product not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
            } else {
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful";
            }
            
            //die($data);
        } else {
            $this->logger->write("Product Controller : fetchproduct() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name syncproducts
     *  @desc sync products from EFRIS
     *	@return
     *	@param
     **/
    function syncproducts(){
        $operation = NULL; //tblevents
        $permission = 'SYNCPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $this->logger->write("Product Controller : syncproducts() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            $pageNo = 1;
            $pageSize = 90;
            $pageCount = 1;
            
            do {
                $this->logger->write("Product Controller : syncproducts() : pageNo = " . $pageNo, 'r');
                $this->logger->write("Product Controller : syncproducts() : pageCount = " . $pageCount, 'r');
                
                $data = $this->util->syncproducts($this->f3->get('SESSION.id'), $pageNo, $pageSize);//will return JSON.
                //var_dump($data);
                $data = json_decode($data, true);
                
                if(isset($data['page'])){
                    $pageCount = $data['page']['pageCount'];
                    
                    if ($pageNo < $pageCount) {
                        $pageNo = $pageNo + 1;
                    }
                }
                
                
                if(isset($data['records'])){
                    
                    if ($data['records']) {
                        foreach($data['records'] as $elem){
                            //$this->logger->write("Product Controller : syncproductsrates() : The products are " . $elem['goodsCode']. ", ".$elem['nowTime']. ", ".$elem['timeFormat']. ", ".$elem['id'], 'r');
                            
                            try{
                                $product = new products($this->db);
                                
                                
                                
                                $productcode = $elem['goodsCode'];
                                $productid = $elem['id'];
                                //$uraquantity =  $elem['stock'];
                                
                                $name = $elem['goodsName'];
                                $measureunit = $elem['measureUnit'];
                                $unitprice = $elem['unitPrice'];
                                $currency = $elem['currency'];
                                $commoditycategorycode = $elem['commodityCategoryCode'];
                                $hasexcisetax = empty($elem['haveExciseTax'])? 'NULL' : $elem['haveExciseTax'];
                                $stockprewarning = empty($elem['stockPrewarning'])? 'NULL' : $elem['stockPrewarning'];
                                $havepieceunit = empty($elem['havePieceUnit'])? 'NULL' : $elem['havePieceUnit'];
                                $haveotherunit = empty($elem['haveOtherUnit'])? 'NULL' : $elem['haveOtherUnit'];

                                $piecemeasureunit = (!isset($elem['pieceMeasureUnit']) || trim((string)$elem['pieceMeasureUnit']) === '' || strtoupper(trim((string)$elem['pieceMeasureUnit'])) === 'NULL')
                                    ? 'NULL'
                                    : '"' . addslashes(trim((string)$elem['pieceMeasureUnit'])) . '"';
                                $pieceunitprice = (!isset($elem['pieceUnitPrice']) || trim((string)$elem['pieceUnitPrice']) === '' || strtoupper(trim((string)$elem['pieceUnitPrice'])) === 'NULL' || !is_numeric(trim((string)$elem['pieceUnitPrice'])))
                                    ? 'NULL'
                                    : trim((string)$elem['pieceUnitPrice']);
                                $packagescaledvalue = (!isset($elem['packageScaledValue']) || trim((string)$elem['packageScaledValue']) === '' || strtoupper(trim((string)$elem['packageScaledValue'])) === 'NULL' || !is_numeric(trim((string)$elem['packageScaledValue'])))
                                    ? 'NULL'
                                    : trim((string)$elem['packageScaledValue']);
                                $piecescaledvalue = (!isset($elem['pieceScaledValue']) || trim((string)$elem['pieceScaledValue']) === '' || strtoupper(trim((string)$elem['pieceScaledValue'])) === 'NULL' || !is_numeric(trim((string)$elem['pieceScaledValue'])))
                                    ? 'NULL'
                                    : trim((string)$elem['pieceScaledValue']);

                                $pack = (!isset($elem['pack']) && !isset($elem['Pack']))
                                    ? 'pack'
                                    : trim((string)(isset($elem['pack']) ? $elem['pack'] : $elem['Pack']));
                                if ($pack !== 'pack') {
                                    $pack = ($pack === '' || strtoupper($pack) === 'NULL' || !is_numeric($pack))
                                        ? 'NULL'
                                        : $pack;
                                }

                                $stick = (!isset($elem['stick']) && !isset($elem['Stick']))
                                    ? 'stick'
                                    : trim((string)(isset($elem['stick']) ? $elem['stick'] : $elem['Stick']));
                                if ($stick !== 'stick') {
                                    $stick = ($stick === '' || strtoupper($stick) === 'NULL' || !is_numeric($stick))
                                        ? 'NULL'
                                        : $stick;
                                }
                                
                                $isexempt = empty($elem['isExempt'])? 'NULL' : $elem['isExempt'];
                                $iszerorated = empty($elem['isZeroRate'])? 'NULL' : $elem['isZeroRate'];
                                $taxrate = empty($elem['taxRate'])? 'NULL' : $elem['taxRate'];
                                $statuscode = empty($elem['statusCode'])? 'NULL' : $elem['statusCode'];
                                $source = empty($elem['source'])? 'NULL' : $elem['source'];
                                $exclusion = empty($elem['exclusion'])? 'NULL' : $elem['exclusion'];
                                $servicemark = empty($elem['serviceMark'])? 'NULL' : $elem['serviceMark'];
                                
                                $product->getByCode($productcode);
                                
                                if ($product->dry()) {
                                    $this->logger->write("Product Controller : syncproducts() : The product does not exist", 'r');
                                    
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
                                    excisedutylist,
                                    erpquantity,
                                    purchaseprice,
                                    stockintype,
                                    isexempt,
                                    iszerorated,
                                    source,
                                    exclusion,
                                    statuscode,
                                    taxrate,
                                    serviceMark,
                                    inserteddt,
                                    insertedby,
                                    modifieddt,
                                    modifiedby)
                                    VALUES ("'
                                        . addslashes($productid) . '", NULL, "' 
                                            . addslashes($productcode) . '", "'
                                            . addslashes($name) . '", "'
                                                . addslashes($productcode) . '", "'
                                                    . addslashes($measureunit) . '", '
                                                        . $unitprice . ', '
                                                            . $currency . ', "'
                                                                . $commoditycategorycode . '", '
                                                                    . $hasexcisetax . ', NULL, '
                                                                        . $stockprewarning . ', ' . $piecemeasureunit . ', '
                                                                                . $havepieceunit . ', ' . $pieceunitprice . ', ' . $packagescaledvalue . ', ' . $piecescaledvalue . ', NULL, NULL, NULL, NULL, '
                                                                                . $isexempt . ', '
                                                                                    . $iszerorated . ', '
                                                                                        . $source . ', '
                                                                                            . $exclusion . ', '
                                                                                                . $statuscode . ', '
                                                                                                    . $taxrate . ', '
                                                                                                        . $servicemark . ', "'
                                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                                . $this->f3->get('SESSION.id') . ', "'
                                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                                        . $this->f3->get('SESSION.id') . ')';
                                                                                                                        
                                                                                                                        $this->logger->write("Utilities : syncproducts() : The SQL to create the product is " . $sql, 'r');
                                                                                                                        $this->db->exec(array($sql));
                                } else {
                                    $this->logger->write("Product Controller : syncproducts() : The product exists", 'r');
                                    $this->db->exec(array('UPDATE tblproductdetails SET isexempt = ' . $isexempt .
                                        ', iszerorated = ' . $iszerorated .
                                        ', source = ' . $source .
                                        ', exclusion = ' . $exclusion .
                                        ', statuscode = ' . $statuscode .
                                        ', taxrate = ' . $taxrate .
                                        ', hasexcisetax = ' . $hasexcisetax .
                                        ', havepieceunit = ' . $havepieceunit .
                                        ', piecemeasureunit = ' . $piecemeasureunit .
                                        ', pieceunitprice = ' . $pieceunitprice .
                                        ', packagescaledvalue = ' . $packagescaledvalue .
                                        ', piecescaledvalue = ' . $piecescaledvalue .
                                        ', pack = ' . $pack .
                                        ', stick = ' . $stick .
                                        ', erpcode = "' . addslashes($productcode) .
                                        '", uraproductidentifier = ' . $productid .
                                        ', modifieddt = "' .  date('Y-m-d H:i:s') .
                                        '", modifiedby = ' . $this->f3->get('SESSION.id') .
                                        ' WHERE TRIM(code) = "' . $productcode . '"'));
                                }
                                
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Product ID - " . $elem['Product'] . " - was synced successfully by " . $this->f3->get('SESSION.username'));
                                self::$systemalert = "The products were sync'd & attributes updated successfully by " . $this->f3->get('SESSION.username');
                            } catch (Exception $e) {
                                $this->logger->write("Product Controller : syncproducts() : The operation to sync the products was not successful. The error message is " . $e->getMessage(), 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync the product by " . $this->f3->get('SESSION.username') . " was not successful");
                                self::$systemalert = "The operation to sync the products by " . $this->f3->get('SESSION.username') . " was not successful";
                            }
                        }
                    } else {
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync the product by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the product first");
                        self::$systemalert = "The operation to sync the products by " . $this->f3->get('SESSION.username') . " didnt return anything. Please ensure you have uploaded the product first";
                    }
                    
                    
                } elseif (isset($data['returnCode'])){
                    $this->logger->write("Product Controller : syncproducts() : The operation to sync the product not successful. The error message is " . $data['returnMessage'], 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync the product by " . $this->f3->get('SESSION.username') . " was not successful");
                    self::$systemalert = "The operation to sync the products by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                } else {
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to sync the product by " . $this->f3->get('SESSION.username') . " was not successful");
                    self::$systemalert = "The operation to sync the products by " . $this->f3->get('SESSION.username') . " was not successful";
                }
            } while ($pageNo < $pageCount);
            
            
            
            
            
            //die($data);
        } else {
            $this->logger->write("Product Controller : syncproducts() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
    
    /**
     *	@name stockin
     *  @desc upload stock to EFRIS
     *	@return
     *	@param
     **/
    function stockin(){
        $operation = NULL; //tblevents
        $permission = 'STOCKIN'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $suppliertin = $this->f3->get('POST.stockinsuppliertin');
        $suppliername = $this->f3->get('POST.stockinsuppliername');
        $stockintype = $this->f3->get('POST.stockintype');
        $productiondate = ($stockintype == '103')? $this->f3->get('POST.productiondate') : NULL;
        $unitprice = $this->f3->get('POST.stockinunitprice');
        $batchno = ($stockintype == '103')? $this->f3->get('POST.productionbatchno') : NULL;
        $qty = $this->f3->get('POST.stockinproductqty');
        $id = $this->f3->get('POST.stockinproductid');
        $product = new products($this->db);
        $product->getByID($id);
        $this->logger->write("Product Controller : stockin() : The product id is " . $this->f3->get('POST.stockinproductid'), 'r');
        $this->logger->write("Product Controller : stockin() : stockintype = " . $stockintype, 'r');
        
        $this->logger->write("Product Controller : stockin() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            $data = $this->util->stockin($this->f3->get('SESSION.id'), $id, $batchno, $qty, $suppliertin, $suppliername, $stockintype, $productiondate, $unitprice);//will return JSON.
            //var_dump($data);
            
            $data = json_decode($data, true);
            
            if(isset($data['returnCode'])){
                $this->logger->write("Product Controller : stockin() : The operation to increase stock not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to increase stock by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to increase stock by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
            } else {
                if ($data) {
                    //$this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to increase stock by " . $this->f3->get('SESSION.username') . " was not successful");
                    //self::$systemalert = "The operation to increase stock by " . $this->f3->get('SESSION.username') . " was not successful.";
                    
                    foreach($data as $elem){
                        $this->logger->write("Product Controller : stockin() : The operation to stockin was not successful. The error message is " . $elem['returnMessage'], 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to stockin by " . $this->f3->get('SESSION.username') . " was not successful");
                        self::$systemalert = "The operation to stockin by " . $this->f3->get('SESSION.username') . " was not successful. The error message is " . $elem['returnMessage'];
                    }
                } else {
                    try{
                        $this->db->exec(array('UPDATE tblproductdetails SET stockintype = "' . $stockintype . 
                                                                        '", purchaseprice = ' . $unitprice . 
                                                                        ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') . 
                                                                        ' WHERE id = ' . $id));
                        
                        $this->logger->write($this->db->log(TRUE), 'r');
                    } catch (Exception $e) {
                        $this->logger->write("Product Controller : stockin() : Failed to update the table tblproductdetails. The error message is " . $e->getMessage(), 'r');
                    }
                    
                    //Fetch new details from EFRIS
                    $n_data = $this->util->queryproduct($this->f3->get('SESSION.id'), $id);//will return JSON.
                    //var_dump($data);
                    $n_data = json_decode($n_data, true);
                    
                    if (isset($n_data['returnCode'])){
                        $this->logger->write("Product Controller : stockin() : The operation to query the stock was not successful. The error message is " . $n_data['returnMessage'], 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to query the stock by " . $this->f3->get('SESSION.username') . " was not successful");
                    } else {
                        if ($n_data) {
                            $stock = !isset($n_data['stock'])? 'NULL' : $n_data['stock'];
                            $stockPrewarning = !isset($n_data['stockPrewarning'])? 'NULL' : $n_data['stockPrewarning'];
                            
                            try{
                                
                                $this->db->exec(array('UPDATE tblproductdetails SET uraquantity = ' . $stock .
                                                                                ', stockprewarning = ' . $stockPrewarning .
                                                                                ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                                ' WHERE id = ' . $id));
                                
                                $this->logger->write($this->db->log(TRUE), 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Product ID - " . $id . " - was fetched successfully by " . $this->f3->get('SESSION.username'));
                                $this->logger->write("Product Controller : stockin() : Product ID - " . $id . " - was queried & attributes updated successfully by " . $this->f3->get('SESSION.username'), 'r');
                            } catch (Exception $e) {
                                $this->logger->write("Product Controller : stockin() : The operation to query the product was not successful. The error message is " . $e->getMessage(), 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful");
                            }
                        } else {
                            $this->logger->write("Product Controller : stockin() : The API did not return anything", 'r');
                        }
                    }
                    
                    
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to increase stock of product ID - " . $id . " - by " . $this->f3->get('SESSION.username') . ' was successful');
                    self::$systemalert = "Stock of Product ID - " . $id . " - was increased successfully by " . $this->f3->get('SESSION.username');
                }
            }
            
            //die($data);
        } else {
            $this->logger->write("Product Controller : stockin() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    
    /**
     *	@name stockout
     *  @desc reduce stock from EFRIS
     *	@return
     *	@param
     **/
    function stockout(){
        $operation = NULL; //tblevents
        $permission = 'STOCKOUT'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $batchno = $this->f3->get('POST.productionbatchno');
        $qty = $this->f3->get('POST.stockoutproductqty');
        $id = $this->f3->get('POST.stockoutproductid');
        $adjustmenttype = $this->f3->get('POST.stockoutadjustmenttype');
        $remarks = $this->f3->get('POST.stockoutremarks');
        
        $product = new products($this->db);
        $product->getByID($id);
        $this->logger->write("Product Controller : stockout() : The product id is " . $this->f3->get('POST.stockoutproductid'), 'r');
        
        $this->logger->write("Product Controller : stockout() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //$data = json_encode(new stdClass);
            
            $data = $this->util->stockout($this->f3->get('SESSION.id'), $id, $batchno, $qty, $adjustmenttype, $remarks);//will return JSON.
            //var_dump($data);
            $data = json_decode($data, true);
            
            if(isset($data['returnCode'])){
                $this->logger->write("Product Controller : stockout() : The operation to reduce stock not successful. The error message is " . $data['returnMessage'], 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to reduce stock by " . $this->f3->get('SESSION.username') . " was not successful");
                self::$systemalert = "The operation to reduce stock by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
            } else {
                if ($data) {
                    foreach($data as $elem){
                        $this->logger->write("Product Controller : stockout() : The operation to reduce stock was not successful. The error message is " . $elem['returnMessage'], 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to reduce stock by " . $this->f3->get('SESSION.username') . " was not successful");
                        self::$systemalert = "The operation to reduce stock by " . $this->f3->get('SESSION.username') . " was not successful. The response message is " . $elem['returnCode'] . "-" . $elem['returnMessage'];
                    }
                } else {
                    
                    //Fetch new details from EFRIS
                    $n_data = $this->util->queryproduct($this->f3->get('SESSION.id'), $id);//will return JSON.
                    //var_dump($data);
                    $n_data = json_decode($n_data, true);
                    
                    if (isset($n_data['returnCode'])){
                        $this->logger->write("Product Controller : stockout() : The operation to query the stock was not successful. The error message is " . $n_data['returnMessage'], 'r');
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to query the stock by " . $this->f3->get('SESSION.username') . " was not successful");
                    } else {
                        if ($n_data) {
                            $stock = !isset($n_data['stock'])? 'NULL' : $n_data['stock'];
                            $stockPrewarning = !isset($n_data['stockPrewarning'])? 'NULL' : $n_data['stockPrewarning'];
                            
                            try{
                                
                                $this->db->exec(array('UPDATE tblproductdetails SET uraquantity = ' . $stock .
                                                                                ', stockprewarning = ' . $stockPrewarning .
                                                                                ', modifieddt = NOW(), modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                                ' WHERE id = ' . $id));
                                
                                $this->logger->write($this->db->log(TRUE), 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Product ID - " . $id . " - was fetched successfully by " . $this->f3->get('SESSION.username'));
                                $this->logger->write("Product Controller : stockout() : Product ID - " . $id . " - was queried & attributes updated successfully by " . $this->f3->get('SESSION.username'), 'r');
                            } catch (Exception $e) {
                                $this->logger->write("Product Controller : stockout() : The operation to query the product was not successful. The error message is " . $e->getMessage(), 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to fetch the product by " . $this->f3->get('SESSION.username') . " was not successful");
                            }
                        } else {
                            $this->logger->write("Product Controller : stockin() : The API did not return anything", 'r');
                        }
                    }
                    
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to reduce stock of product ID - " . $id . " - by " . $this->f3->get('SESSION.username') . ' was successful');
                    self::$systemalert = "Stock of Product ID - " . $id . " - was reduced successfully by " . $this->f3->get('SESSION.username');
                }
            }
            
            //die($data);
        } else {
            $this->logger->write("Product Controller : stockout() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name listotherunits
     *  @desc List other units
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function listotherunits(){
        $operation = NULL; //tblevents
        $permission = 'VIEWPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Product Controller : listotherunits() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : listotherunits() : Processing list of other units started", 'r');
            
            
            $id = trim($this->f3->get('POST.id'));
            $this->logger->write("Product Controller : listotherunits() : The ID to process is: " . $id, 'r');
            
            if (trim($id) !== '' || !empty($id)) {
                $sql = 'SELECT  u.id "ID",
                        u.otherunit "Code",
                        mu.name "Name",
                        u.otherPrice "Other Price",
                        u.packagescaled "Package Scale Value",
                        u.otherscaled "Other Scaled",
                        u.disabled "Disabled",
                        u.inserteddt "Creation Date",
                        u.insertedby "Created By",
                        u.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblotherunits u
                    LEFT JOIN tblusers s ON u.modifiedby = s.id
                    LEFT JOIN tblrateunits mu ON mu.code = u.otherunit
                    WHERE u.productid = "' . $id . '"
                    ORDER By u.id DESC';
                
                try {
                    $dtls = $this->db->exec($sql);
                    
                    $this->logger->write($this->db->log(TRUE), 'r');
                    foreach ($dtls as $obj) {
                        $data[] = $obj;
                    }
                } catch (Exception $e) {
                    $this->logger->write("Product Controller : listotherunits() : The operation to list the other units was not successful. The error message is " . $e->getMessage(), 'r');
                }
            } else {
                $this->logger->write("Product Controller : listotherunits() : No id was specified", 'r');
            }
            
        } else {
            $this->logger->write("Product Controller : listotherunits() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
    
 
    /**
     *	@name liststockadjustments
     *  @desc List stock adjustments
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function liststockadjustments(){
        $operation = NULL; //tblevents
        $permission = 'VIEWPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Product Controller : liststockadjustments() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : liststockadjustments() : Processing list of stock adjustments started", 'r');
            
            
            $id = trim($this->f3->get('POST.id'));
            $this->logger->write("Product Controller : liststockadjustments() : The ID to process is: " . $id, 'r');
            
            if (trim($id) !== '' || !empty($id)) {
                $sql = 'SELECT  u.id "ID",
                        u.operationType "Operation Type",
                        ot.name "Operation Type Name",
                        u.supplierTin "Supplier Tin",
                        u.supplierName "Supplier Name",
                        u.adjustType "Adjust Type",
                        at.name "Adjust Type Name",
                        u.remarks "Remarks",
                        u.stockInDate "StockIn Date",
                        u.stockInType "StockIn Type",
                        st.name "StockIn Type Name",
                        u.productionBatchNo "Production BatchNo",
                        u.productionDate "Production Date",
                        u.quantity "Quantity",
                        u.unitPrice "Unit Price",
                        u.ProductCode "Product Code",
                        p.id "Product ID",
                        u.voucherNumber "Voucher Number",
                        u.voucherRef "Voucher Reference",
                        u.inserteddt "Creation Date",
                        u.insertedby "Created By",
                        u.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblgoodsstockadjustment u
                    JOIN tblproductdetails p ON p.code = u.ProductCode
                    LEFT JOIN tblusers s ON u.modifiedby = s.id
                    LEFT JOIN tblstockoperationtypes ot ON u.operationType = ot.code
                    LEFT JOIN tblstockadjustmenttypes at ON u.adjustType = at.code
                    LEFT JOIN tblstockintypes st ON u.stockInType = st.code
                    WHERE p.id = "' . $id . '"
                    ORDER By u.id DESC';
                
                try {
                    $dtls = $this->db->exec($sql);
                    
                    $this->logger->write($this->db->log(TRUE), 'r');
                    foreach ($dtls as $obj) {
                        $data[] = $obj;
                    }
                } catch (Exception $e) {
                    $this->logger->write("Product Controller : liststockadjustments() : The operation to list stock adjustments was not successful. The error message is " . $e->getMessage(), 'r');
                }
            } else {
                $this->logger->write("Product Controller : liststockadjustments() : No id was specified", 'r');
            }
            
        } else {
            $this->logger->write("Product Controller : liststockadjustments() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
    
    
    /**
     *	@name liststocktransfers
     *  @desc List stock transfers
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function liststocktransfers(){
        $operation = NULL; //tblevents
        $permission = 'VIEWPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $data = array();
        
        $this->logger->write("Product Controller : liststocktransfers() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $this->logger->write("Product Controller : liststocktransfers() : Processing list of stock transfers started", 'r');
            
            
            $id = trim($this->f3->get('POST.id'));
            $this->logger->write("Product Controller : liststocktransfers() : The ID to process is: " . $id, 'r');
            
            if (trim($id) !== '' || !empty($id)) {
                $sql = 'SELECT  u.id "ID",
                        u.sourceBranchId "Source Branch ID",
                        sb.name "Source Branch Name",
                        u.destinationBranchId "Destination Branch ID",
                        db.name "Destination Branch Name",
                        u.transferTypeCode "TransferType Code",
                        tt.name "TransferType Code Name",
                        u.remarks "Remarks",
                        u.quantity "Quantity",
                        p.id "Product ID",
                        u.ProductCode "Product Code",
                        u.voucherNumber "Voucher Number",
                        u.voucherRef "Voucher Ref",
                        u.inserteddt "Creation Date",
                        u.insertedby "Created By",
                        u.modifieddt "Modified Date",
                        s.username "Modified By"
                    FROM tblgoodsstocktransfer u
                    JOIN tblproductdetails p ON p.code = u.ProductCode
                    LEFT JOIN tblusers s ON u.modifiedby = s.id
                    LEFT JOIN tblgoodstransfertypes tt ON u.transferTypeCode = tt.code
                    LEFT JOIN tblurabranches sb ON u.sourceBranchId = sb.branchid
                    LEFT JOIN tblurabranches db ON u.destinationBranchId = db.branchid
                    WHERE p.id = "' . $id . '"
                    ORDER By u.id DESC';
                
                try {
                    $dtls = $this->db->exec($sql);
                    
                    $this->logger->write($this->db->log(TRUE), 'r');
                    foreach ($dtls as $obj) {
                        $data[] = $obj;
                    }
                } catch (Exception $e) {
                    $this->logger->write("Product Controller : liststocktransfers() : The operation to list stock transfers was not successful. The error message is " . $e->getMessage(), 'r');
                }
            } else {
                $this->logger->write("Product Controller : liststocktransfers() : No id was specified", 'r');
            }
            
        } else {
            $this->logger->write("Product Controller : liststocktransfers() : The user is not allowed to perform this function", 'r');
        }
        
        die(json_encode($data));
    }
    
    
    /**
     *	@name addotherunits
     *  @desc Add other units to a product
     *	@return NULL
     *	@param NULL
     **/
    function addotherunits(){
        $operation = NULL; //tblevents
        $permission = 'EDITPRODUCT'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : addotherunits() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $otherunit = new otherunits($this->db);
            
            $id = trim($this->f3->get('POST.addotherunitsproductid'));
            $this->logger->write("Product Controller : addotherunits() : The product to be edited is: " . $id, 'r');
            
            if ($id !== '' || !empty($id)) {
                $this->f3->set('POST.productid', $this->f3->get('POST.addotherunitsproductid'));
                $this->f3->set('POST.otherunit', $this->f3->get('POST.addotherunitsproductmeasureunit'));
                $this->logger->write("Product Controller : addotherunits() : The selected unit is: " . $this->f3->get('POST.addotherunitsproductmeasureunit'), 'r');
                
                $this->f3->set('POST.otherPrice', $this->f3->get('POST.addotherunitsproductpieceunitprice'));
                $this->f3->set('POST.otherscaled', $this->f3->get('POST.addotherunitsproductpiecescaledvalue'));
                $this->f3->set('POST.packagescaled', $this->f3->get('POST.addotherunitsproductpackagescaledvalue'));
                
                $this->f3->set('POST.inserteddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.insertedby', $this->f3->get('SESSION.id'));
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                try {
                    $otherunit->add();
                    
                    $this->logger->write("Product Controller : addotherunits() : The units have been added", 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The units have been added by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The units have been added";
                } catch (Exception $e) {
                    $this->logger->write("Product Controller : addotherunits() : The operation to add the units was not successful. The error message is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to add the units was not successful");
                    self::$systemalert = "The operation to add the units was not successful";
                }
            } else {
                $this->logger->write("Product Controller : addotherunits() : The product to edit was not supplied.", 'r');
                self::$systemalert = "The product to edit was not supplied.";
                
                $this->f3->set('systemalert', self::$systemalert);
                self::view();
            }
            
            $this->f3->set('systemalert', self::$systemalert);
            self::view($id, 'tab_otherunits', 'tab_2');
        } else {
            $this->logger->write("Product Controller : addotherunits() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name editotherunits
     *  @desc Edit other units to a product
     *	@return NULL
     *	@param NULL
     **/
    function editotherunits(){
        $operation = NULL; //tblevents
        $permission = 'EDITPRODUCT'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : editotherunits() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $otherunit = new otherunits($this->db);
            
            $id = trim($this->f3->get('POST.editotherunitsproductid'));
            $unit = trim($this->f3->get('POST.editotherunitsid'));
            $this->logger->write("Product Controller : editotherunits() : The product to be edited is: " . $id, 'r');
            $this->logger->write("Product Controller : editotherunits() : The otherunit to be edited is: " . $unit, 'r');
            
            if ($id !== '' || !empty($id)) {
                $otherunit->getByID($unit);
                
                
                if (trim($this->f3->get('POST.editotherunitsproductid')) !== '' || !empty(trim($this->f3->get('POST.editotherunitsproductid')))) {
                    $this->f3->set('POST.productid', $this->f3->get('POST.editotherunitsproductid'));
                } else {
                    $this->f3->set('POST.productid', $otherunit->productid);
                }
                
                if (trim($this->f3->get('POST.editotherunitsproductmeasureunit')) !== '' || !empty(trim($this->f3->get('POST.editotherunitsproductmeasureunit')))) {
                    $this->f3->set('POST.otherunit', $this->f3->get('POST.editotherunitsproductmeasureunit'));
                    $this->logger->write("Product Controller : editotherunits() : The selected unit is: " . $this->f3->get('POST.editotherunitsproductmeasureunit'), 'r');
                } else {
                    $this->f3->set('POST.otherunit', $otherunit->otherunit);
                }
                
                if (trim($this->f3->get('POST.editotherunitsproductpieceunitprice')) !== '' || !empty(trim($this->f3->get('POST.editotherunitsproductpieceunitprice')))) {
                    $this->f3->set('POST.otherPrice', $this->f3->get('POST.editotherunitsproductpieceunitprice'));
                } else {
                    $this->f3->set('POST.otherPrice', $otherunit->otherPrice);
                }
                
                if (trim($this->f3->get('POST.editotherunitsproductpackagescaledvalue')) !== '' || !empty(trim($this->f3->get('POST.editotherunitsproductpackagescaledvalue')))) {
                    $this->f3->set('POST.packagescaled', $this->f3->get('POST.editotherunitsproductpackagescaledvalue'));
                } else {
                    $this->f3->set('POST.packagescaled', $otherunit->packagescaled);
                }
                
                if (trim($this->f3->get('POST.editotherunitsproductpiecescaledvalue')) !== '' || !empty(trim($this->f3->get('POST.editotherunitsproductpiecescaledvalue')))) {
                    $this->f3->set('POST.otherscaled', $this->f3->get('POST.editotherunitsproductpiecescaledvalue'));
                } else {
                    $this->f3->set('POST.otherscaled', $otherunit->packagescaled);
                }
                
                $this->f3->set('POST.modifieddt', date('Y-m-d H:i:s'));
                $this->f3->set('POST.modifiedby', $this->f3->get('SESSION.id'));
                
                try {
                    $otherunit->edit($unit);
                    
                    $this->logger->write("Product Controller : editotherunits() : The units have been edited", 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The units have been edited by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The units have been edited";
                } catch (Exception $e) {
                    $this->logger->write("Product Controller : editotherunits() : The operation to edit the units was not successful. The error message is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to edit the units was not successful");
                    self::$systemalert = "The operation to edit the units was not successful";
                }
            } else {
                $this->logger->write("Product Controller : editotherunits() : The product to edit was not supplied.", 'r');
                self::$systemalert = "The product to edit was not supplied.";
                
                $this->f3->set('systemalert', self::$systemalert);
                self::view();
            }
            
            $this->f3->set('systemalert', self::$systemalert);
            self::view($id, 'tab_otherunits', 'tab_2');
        } else {
            $this->logger->write("Product Controller : editotherunits() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name deleteotherunits
     *  @desc Delete other units to a product
     *	@return NULL
     *	@param NULL
     **/
    function deleteotherunits(){
        $operation = NULL; //tblevents
        $permission = 'EDITPRODUCT'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : deleteotherunits() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            $otherunit = new otherunits($this->db);
            
            
            $id = trim($this->f3->get('POST.deleteotherunitsid'));
            $productid = trim($this->f3->get('POST.deleteotherunitsproductid'));
            $this->logger->write("Product Controller : deleteotherunits() : The unit to be delete is: " . $id, 'r');
            $this->logger->write("Product Controller : deleteotherunits() : The product id is: " . $productid, 'r');
            
            if ($id !== '' || !empty($id)) {
                $otherunit->getByID($id);
                
                try {
                    $otherunit->delete($id);
                    
                    $this->logger->write("Product Controller : deleteotherunits() : The units have been deleted", 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The units have been delete by " . $this->f3->get('SESSION.username'));
                    self::$systemalert = "The units have been deleted";
                } catch (Exception $e) {
                    $this->logger->write("Product Controller : deleteotherunits() : The operation to delete the units was not successful. The error message is " . $e->getMessage(), 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to delete the units was not successful");
                    self::$systemalert = "The operation to delete the units was not successful";
                }
            } else {
                $this->logger->write("Product Controller : deleteotherunits() : The unit to delete was not supplied.", 'r');
                self::$systemalert = "The unit to delete was not supplied.";
                
                $this->f3->set('systemalert', self::$systemalert);
                self::view();
            }
            
            $this->f3->set('systemalert', self::$systemalert);
            self::view($productid, 'tab_otherunits', 'tab_2');
        } else {
            $this->logger->write("Product Controller : deleteotherunits() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name downloadstockadjustments
     *  @desc Download stock adjustment history from EFRIS
     *	@return NULL
     *	@param NULL
     **/
    function downloadstockadjustments(){
        $operation = NULL; //tblevents
        $permission = 'EDITPRODUCT'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : downloadstockadjustments() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $id = trim($this->f3->get('POST.downloadstockadjustmentsproductid'));
            $this->logger->write("Product Controller : downloadstockadjustments() : The product to be edited is: " . $id, 'r');
            
            $product = new products($this->db);
            $product->getByID($id);
            
            if ($id !== '' || !empty($id)) {
                $data = $this->util->downloadstockadjustments($this->f3->get('SESSION.id'), $id);//will return JSON.
                //var_dump($data);
                $data = json_decode($data, true);
                
                if (isset($data['returnCode'])){
                    $this->logger->write("Product Controller : downloadstockadjustments() : The operation to download stock adjustments was not successful. The error message is " . $data['returnMessage'], 'r');
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download stock adjustments by " . $this->f3->get('SESSION.username') . " was not successful");
                    self::$systemalert = "The operation to download stock adjustments by " . $this->f3->get('SESSION.username') . " was not successful The error message is " . $data['returnMessage'];
                } elseif (isset($data['records'])){
                    
                    if ($data['records']) {
                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download stock adjustments by " . $this->f3->get('SESSION.username') . " was successful");
                        self::$systemalert = "The operation to download stock adjustments by " . $this->f3->get('SESSION.username') . " was successful";
                    } else {
                        $this->logger->write("Product Controller : downloadstockadjustments() : The operation to download stock adjustments did not return anything", 'r');
                    }
                }
                
                
            } else {
                $this->logger->write("Product Controller : downloadstockadjustments() : The product to edit was not supplied.", 'r');
                self::$systemalert = "The product to edit was not supplied.";
                
                $this->f3->set('systemalert', self::$systemalert);
                self::view();
            }
            
            $this->f3->set('systemalert', self::$systemalert);
            self::view($id, 'tab_stockadjustments', 'tab_3');
        } else {
            $this->logger->write("Product Controller : downloadstockadjustments() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name downloadErpProducts
     *  @desc download products from QuickBooks
     *	@return NULL
     *	@param NULL
     **/
    function downloadErpProducts(){
        $operation = NULL; //tblevents
        $permission = 'SYNCPRODUCTS'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : downloadErpProducts() : Checking permissions", 'r');
        
        if ($this->userpermissions[$permission]) {
            if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
                date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
            }
            
            if ($this->platformMode == 'ERP') {
                $this->logger->write("Product Controller : downloadErpProducts() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
            } else {
                $this->logger->write("Product Controller : downloadErpProducts() : The platform is integrated.", 'r');
                
                if ($this->integratedErp) {
                    /**
                     * Check on integrated ERP type
                     */
                    $this->logger->write("Product Controller : downloadErpProducts() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                    
                    if (strtoupper($this->integratedErp) == 'QBO') {
                        $this->logger->write("Product Controller : downloadErpProducts() : The integrated ERP is Quicbooks Online.", 'r');
                        
                        try {
                            /**
                             * Date: 2025-08-17
                             * Author: Francis Lubanga <frncslubanga@gmail.com>
                             * Description: Read the token directly from the database to avoid the GUI from returning an expiry error
                             */
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
                                $this->logger->write("Product Controller : downloadErpProducts() : The access token is NOT set in the database. Please manually login.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. The access token is NOT set in the database. Please manually login.");
                                self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. The access token is NOT set in the database. Please manually login.";
                            } else {
                                $this->logger->write("Product Controller : downloadErpProducts() : The access token was retrieved successfuly from the database", 'r');
                                $sessionAccessToken = trim($token_check->value);
                            
                                if ($sessionAccessToken !== null) {
                                //if ($this->f3->get('SESSION.sessionAccessToken') !== null) {
                                    $sessionRefreshToken = $this->appsettings['QBREFRESHTOKEN'];
                                    
                                    if ($this->appsettings['QBSESSIONACCESSTOKENEXPIRY']) {
                                        //$sessionAccessTokenExpiry = $this->appsettings['QBSESSIONACCESSTOKENEXPIRY'];
                                        $sessionAccessTokenExpiry =  str_replace('/', '-', $this->appsettings['QBSESSIONACCESSTOKENEXPIRY']);
                                    } else {
                                        $sessionAccessTokenExpiry = date('Y-m-d H:i:s', strtotime('-1 days'));
                                    }
                                    
                                    $this->logger->write("Product Controller : downloadErpProducts() : The refresh token is " . $sessionRefreshToken, 'r');
                                    $this->logger->write("Product Controller : downloadErpProducts() : The access token expiry is " . $sessionAccessTokenExpiry, 'r');
                                    
                                    $startDt = new DateTime(date('Y-m-d H:i:s'));
                                    $endDt = new DateTime($sessionAccessTokenExpiry);
                                    
                                    $inactivityPeriod = $startDt->getTimestamp() - $endDt->getTimestamp();
                                    
                                    $this->logger->write("Product Controller : downloadErpProducts() : The current time is " . date('Y-m-d H:i:s'), 'r');
                                    $this->logger->write("Product Controller : downloadErpProducts() : The inactivity period is " . $inactivityPeriod, 'r');
                                    
                                    if ($inactivityPeriod > 0) {
                                        $this->logger->write("Product Controller : downloadErpProducts() : The access token expired. Please manually login.", 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. The access token expired. Please manually login.");
                                        self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. The access token expired. Please manually login.";
                                    } else {
                                    
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
                                        
                                        $items = $dataService->Query("SELECT * FROM Item");
                                        
                                        $error = $dataService->getLastError();
                                        
                                        if ($error) {
                                            $this->logger->write("Product Controller : downloadErpProducts() : The operation to download QuickBooks products was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was successful");
                                            self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was successful.";
                                        } else {
                                            if(isset($items)){
                                                if ($items) {
                                                    $product = new products($this->db);
                                                    
                                                    foreach($items as $elem){
                                                        
                                                        try {
                                                            $this->logger->write("Product Controller : downloadErpProducts() : Item Name: " . $elem->Name, 'r');
                                                            
                                                            $productcode = $elem->Id;
                                                            
                                                            $name = $elem->Name;
                                                            $desc = isset($elem->Description)? $elem->Description : "Product/Service created by the QB API";
                                                            $unitprice = isset($elem->UnitPrice) || $elem->UnitPrice == '0'? $elem->UnitPrice : 1;
                                                            $stockprewarning = isset($elem->ReorderPoint)? $elem->ReorderPoint : 0;
                                                            $erpQty = isset($elem->QtyOnHand)? $elem->QtyOnHand : 0;
                                                            
                                                            if ($elem->Active == true) {
                                                                if ($productcode) {
                                                                    $product->getByCode($productcode);
                                                                    
                                                                    if ($product->dry()) {
                                                                        $this->logger->write("Product Controller : downloadErpProducts() : The product does not exist", 'r');
                                                                        
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
                                                                                excisedutylist,
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
                                                                                inserteddt,
                                                                                insertedby,
                                                                                modifieddt,
                                                                                modifiedby)
                                                                                VALUES ('
                                                                            . 'NULL' . ', "' . addslashes($productcode) . '", "'
                                                                                . addslashes($productcode) . '", "'
                                                                                    . addslashes($name) . '", "'
                                                                                        . addslashes($productcode) . '", '
                                                                                            . 'NULL' . ', '
                                                                                                . $unitprice . ', '
                                                                                                    . 'NULL' . ', '
                                                                                                        . 'NULL' . ', '
                                                                                                            . 'NULL' . ', "' . addslashes($desc) . '", '
                                                                                                                . $stockprewarning . ', NULL, '
                                                                                                                    . 'NULL' . ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '
                                                                                                                        . 'NULL' . ', '
                                                                                                                            . 'NULL' . ', '
                                                                                                                                . 'NULL' . ', '
                                                                                                                                    . 'NULL' . ', '
                                                                                                                                        . 'NULL' . ', '
                                                                                                                                            . 'NULL' . ', '
                                                                                                                                                . 'NULL' . ', "'
                                                                                                                                                    . date('Y-m-d H:i:s') . '", '
                                                                                                                                                        . $this->f3->get('SESSION.id') . ', "'
                                                                                                                                                            . date('Y-m-d H:i:s') . '", '
                                                                                                                                                                . $this->f3->get('SESSION.id') . ')';
                                                                                                                                                                
                                                                                                                                                                $this->logger->write("Product Controller : downloadErpProducts() : The SQL to create the product is " . $sql, 'r');
                                                                                                                                                                $this->db->exec(array($sql));
                                                                    } else {
                                                                        $this->logger->write("Product Controller : downloadErpProducts() : The product exists", 'r');
                                                                        
                                                                        $this->db->exec(array('UPDATE tblproductdetails SET name = "' . addslashes($name) .
                                                                            '", erpid = "' . addslashes($productcode) .
                                                                            '", description = "' . addslashes($desc) .
                                                                            '", unitprice = ' . $unitprice .
                                                                            ', erpquantity = ' . $erpQty .
                                                                            ', stockprewarning = ' . $stockprewarning .
                                                                            ', modifieddt = "' .  date('Y-m-d H:i:s') .
                                                                            '", modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                            ' WHERE TRIM(code) = "' . $productcode . '"'));
                                                                    }
                                                                } else {
                                                                    $this->logger->write("Product Controller : downloadErpProducts() : The Item has no Id.", 'r');
                                                                }
                                                            } else {
                                                                $this->logger->write("Product Controller : downloadErpProducts() : The Item is not ACTIVE.", 'r');
                                                            }
                                                            
                                                        } catch (Exception $e) {
                                                            $this->logger->write("Product Controller : downloadErpProducts() : There was an error when processing Item " . $elem->Name . ". The error is " . $e->getMessage(), 'r');
                                                        }
                                                    }
                                                }
                                            } else {
                                                $this->logger->write("Product Controller : downloadErpProducts() : The operation to download QuickBooks products did not return records.", 'r');
                                            }
                                        }
                                        
                                        $this->logger->write("Product Controller : downloadErpProducts() : The operation to download QuickBooks products was successful.", 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was successful");
                                        self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was successful.";
                                    }
                                } else {
                                    $this->logger->write("Product Controller : downloadErpProducts() : The operation to download QuickBooks products was not successful. Please connect to QuickBooks first.", 'r');
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to QuickBooks first.");
                                    self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to QuickBooks first.";
                                }
                            }
                            
                        } catch (Exception $e) {
                            $this->logger->write("Product Controller : downloadErpProducts() : The operation to download QuickBooks products was not successful. The error is: " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to QuickBooks OR Contact your System Administrator";
                        }
                    } else {
                        $this->logger->write("Product Controller : downloadErpProducts() : The integrated ERP is unknown.", 'r');
                        self::$systemalert = "Sorry. The integrated ERP is unknown.";
                    }
                } else {
                    $this->logger->write("Product Controller : downloadErpProducts() : We are unable to indentify the currently integrated ERP.", 'r');
                    self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                }
            }
            
            
        } else {
            $this->logger->write("Product Controller : downloadErpProducts() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::index();
    }
    
    /**
     *	@name fetchErpproduct
     *  @desc fetch a product from QuickBooks
     *	@return
     *	@param
     **/
    function fetchErpproduct(){
        $operation = NULL; //tblevents
        $permission = 'FETCHPRODUCT'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        if(trim(date_default_timezone_get() !== trim($this->appsettings['EFRIS_TIMEZONE']))){
            date_default_timezone_set($this->appsettings['EFRIS_TIMEZONE']);
        }
        
        $id = $this->f3->get('POST.fetcherpproductid');
        $product = new products($this->db);
        $product->getByID($id);
        $this->logger->write("Product Controller : fetchErpproduct() : The product id is " . $this->f3->get('POST.fetcherpproductid'), 'r');
        
        $this->logger->write("Product Controller : fetchErpproduct() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            if ($this->platformMode == 'ERP') {
                $this->logger->write("Product Controller : fetchErpproduct() : The platform is not integrated. It is running as an abriged ERP.", 'r');
                self::$systemalert = "Sorry. The platform is not integrated. It is running as an abriged ERP.";
            } else {
                $this->logger->write("Product Controller : fetchErpproduct() : The platform is integrated.", 'r');
                
                if ($this->integratedErp) {
                    /**
                     * Check on integrated ERP type
                     */
                    $this->logger->write("Product Controller : fetchErpproduct() : integratedErp: " . strtoupper($this->integratedErp), 'r');
                    
                    if (strtoupper($this->integratedErp) == 'QBO') {
                        $this->logger->write("Product Controller : fetchErpproduct() : The integrated ERP is Quicbooks Online.", 'r');
                        
                        try {
                            /**
                             * Date: 2025-08-17
                             * Author: Francis Lubanga <frncslubanga@gmail.com>
                             * Description: Read the token directly from the database to avoid the GUI from returning an expiry error
                             */
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
                                $this->logger->write("Product Controller : fetchErpproduct() : The access token is NOT set in the database. Please manually login.", 'r');
                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. The access token is NOT set in the database. Please manually login.");
                                self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. The access token is NOT set in the database. Please manually login.";
                            } else {
                                $this->logger->write("Product Controller : fetchErpproduct() : The access token was retrieved successfuly from the database", 'r');
                                $sessionAccessToken = trim($token_check->value);
                                
                                if ($sessionAccessToken !== null) {
                                    //if ($this->f3->get('SESSION.sessionAccessToken') !== null) {
                                    $sessionRefreshToken = $this->appsettings['QBREFRESHTOKEN'];
                                    
                                    if ($this->appsettings['QBSESSIONACCESSTOKENEXPIRY']) {
                                        //$sessionAccessTokenExpiry = $this->appsettings['QBSESSIONACCESSTOKENEXPIRY'];
                                        $sessionAccessTokenExpiry =  str_replace('/', '-', $this->appsettings['QBSESSIONACCESSTOKENEXPIRY']);
                                    } else {
                                        $sessionAccessTokenExpiry = date('Y-m-d H:i:s', strtotime('-1 days'));
                                    }
                                    
                                    $this->logger->write("Product Controller : fetchErpproduct() : The refresh token is " . $sessionRefreshToken, 'r');
                                    $this->logger->write("Product Controller : fetchErpproduct() : The access token expiry is " . $sessionAccessTokenExpiry, 'r');
                                    
                                    $startDt = new DateTime(date('Y-m-d H:i:s'));
                                    $endDt = new DateTime($sessionAccessTokenExpiry);
                                    
                                    $inactivityPeriod = $startDt->getTimestamp() - $endDt->getTimestamp();
                                    
                                    $this->logger->write("Product Controller : fetchErpproduct() : The current time is " . date('Y-m-d H:i:s'), 'r');
                                    $this->logger->write("Product Controller : fetchErpproduct() : The inactivity period is " . $inactivityPeriod, 'r');
                                    
                                    if ($inactivityPeriod > 0) {
                                        $this->logger->write("Product Controller : fetchErpproduct() : The access token expired. Please manually login.", 'r');
                                        $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. The access token expired. Please manually login.");
                                        self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. The access token expired. Please manually login.";
                                    } else {
                                        
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
                                        
                                        $item = $dataService->FindbyId('item', $product->erpid);
                                        
                                        $error = $dataService->getLastError();
                                        
                                        if ($error) {
                                            $this->logger->write("Product Controller : fetchErpproduct() : The operation to download QuickBooks products was not successful. The Response Message is: " . $error->getResponseBody(), 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was successful");
                                            self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was successful.";
                                        } else {
                                            
                                            //print_r($item);
                                            
                                            if(isset($item)){
                                                if ($item) {
                                                    try {
                                                        $this->logger->write("Product Controller : fetchErpproduct() : Item Name: " . $item->Name, 'r');
                                                        $this->logger->write("Product Controller : fetchErpproduct() : Unit Price: " . $item->UnitPrice, 'r');
                                                        
                                                        $productcode = $item->Id;
                                                        
                                                        $name = $item->Name;
                                                        $desc = isset($item->Description)? $item->Description : "Product/Service created by the QB API";
                                                        $unitprice = isset($item->UnitPrice) && $item->UnitPrice !== '0'? $item->UnitPrice : 1;
                                                        $stockprewarning = isset($item->ReorderPoint)? $item->ReorderPoint : 0;
                                                        $erpQty = isset($item->QtyOnHand)? $item->QtyOnHand : 0;
                                                        
                                                        if ($item->Active == true) {
                                                            if ($productcode) {
                                                                $product->getByCode($productcode);
                                                                
                                                                if ($product->dry()) {
                                                                    $this->logger->write("Product Controller : fetchErpproduct() : The product does not exist", 'r');
                                                                    
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
                                                            excisedutylist,
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
                                                            inserteddt,
                                                            insertedby,
                                                            modifieddt,
                                                            modifiedby)
                                                            VALUES ('
                                                                        . 'NULL' . ', "' . addslashes($productcode) . '", "'
                                                                            . addslashes($productcode) . '", "'
                                                                                . addslashes($name) . '", "'
                                                                                    . addslashes($productcode) . '", '
                                                                                        . 'NULL' . ', '
                                                                                            . $unitprice . ', '
                                                                                                . 'NULL' . ', '
                                                                                                    . 'NULL' . ', '
                                                                                                        . 'NULL' . ', "' . addslashes($desc) . '", '
                                                                                                            . $stockprewarning . ', NULL, '
                                                                                                                . 'NULL' . ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '
                                                                                                                    . 'NULL' . ', '
                                                                                                                        . 'NULL' . ', '
                                                                                                                            . 'NULL' . ', '
                                                                                                                                . 'NULL' . ', '
                                                                                                                                    . 'NULL' . ', '
                                                                                                                                        . 'NULL' . ', '
                                                                                                                                            . 'NULL' . ', "'
                                                                                                                                                . date('Y-m-d H:i:s') . '", '
                                                                                                                                                    . $this->f3->get('SESSION.id') . ', "'
                                                                                                                                                        . date('Y-m-d H:i:s') . '", '
                                                                                                                                                            . $this->f3->get('SESSION.id') . ')';
                                                                                                                                                            
                                                                                                                                                            $this->logger->write("Product Controller : downloadErpProducts() : The SQL to create the product is " . $sql, 'r');
                                                                                                                                                            $this->db->exec(array($sql));
                                                                } else {
                                                                    $this->logger->write("Product Controller : fetchErpproduct() : The product exists", 'r');
                                                                    
                                                                    $this->db->exec(array('UPDATE tblproductdetails SET name = "' . addslashes($name) .
                                                                                                '", erpid = "' . addslashes($productcode) .
                                                                                                '", description = "' . addslashes($desc) .
                                                                                                '", unitprice = ' . $unitprice .
                                                                                                ', erpquantity = ' . $erpQty .
                                                                                                ', stockprewarning = ' . $stockprewarning .
                                                                                                ', modifieddt = "' .  date('Y-m-d H:i:s') .
                                                                                                '", modifiedby = ' . $this->f3->get('SESSION.id') .
                                                                                            ' WHERE TRIM(code) = "' . $productcode . '"'));
                                                                }
                                                            } else {
                                                                $this->logger->write("Product Controller : fetchErpproduct() : The Item has no Id.", 'r');
                                                            }
                                                        } else {
                                                            $this->logger->write("Product Controller : fetchErpproduct() : The Item is not ACTIVE.", 'r');
                                                        }
                                                        
                                                    } catch (Exception $e) {
                                                        $this->logger->write("Product Controller : fetchErpproduct() : There was an error when processing Item " . $item->Name . ". The error is " . $e->getMessage(), 'r');
                                                    }
                                                }
                                            } else {
                                                $this->logger->write("Product Controller : fetchErpproduct() : The operation to download QuickBooks products did not return records.", 'r');
                                                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. The operation to download QuickBooks products did not return records.");
                                                self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. The operation to download QuickBooks products did not return records.";
                                            }
                                            
                                            $this->logger->write("Product Controller : fetchErpproduct() : The operation to download QuickBooks products was successful.", 'r');
                                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was successful");
                                            self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was successful.";
                                        }
                                    }
                                } else {
                                    $this->logger->write("Product Controller : fetchErpproduct() : The operation to download QuickBooks products was not successful. Please connect to QuickBooks first.", 'r');
                                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to QuickBooks first.");
                                    self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. Please connect to QuickBooks first.";
                                }
                            }
                            
                        } catch (Exception $e) {
                            $this->logger->write("Product Controller : fetchErpproduct() : The operation to download QuickBooks products was not successful. The error is: " . $e->getMessage(), 'r');
                            $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful");
                            self::$systemalert = "The operation to download QuickBooks products by " . $this->f3->get('SESSION.username') . " was not successful. Reconnect to QuickBooks OR Contact your System Administrator";
                        }
                    } else {
                        $this->logger->write("Product Controller : fetchErpproduct() : The integrated ERP is unknown.", 'r');
                        self::$systemalert = "Sorry. The integrated ERP is unknown.";
                    }
                } else {
                    $this->logger->write("Product Controller : fetchErpproduct() : We are unable to indentify the currently integrated ERP.", 'r');
                    self::$systemalert = "Sorry. We are unable to indentify the currently integrated ERP.";
                }
            }
            
        } else {
            $this->logger->write("Product Controller : fetchErpproduct() : The user is not allowed to perform this function", 'r');
            self::$systemalert = "You are not allowed to perform this function";
        }
        
        $this->f3->set('systemalert', self::$systemalert);
        self::view($id);
    }
    
    /**
     *	@name uploadOpeningStock
     *  @desc upload opening stock
     *	@return NULL
     *	@param NULL
     **/
    function uploadOpeningStock() {
        $operation = NULL; //tblevents
        $permission = 'UPLOADOPENINGSTOCK'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : uploadOpeningStock() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $this->f3->set('fileuploadfeedback', self::$fileuploadfeedback);
            $this->f3->set('fileuploadstatus', self::$fileuploadstatus);
            
            $this->f3->set('pagetitle','Opening Stock');
            
            $this->f3->set('pagecontent','OpeningStock.htm');
            $this->f3->set('pagescripts','OpeningStockFooter.htm');
            echo \Template::instance()->render('Layout.htm');
        } else {
            $this->logger->write("Product Controller : uploadOpeningStock() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name openingstockproc
     *  @desc process opening stock
     *	@return NULL
     *	@param NULL
     **/
    function openingstockproc() {
        $operation = NULL; //tblevents
        $permission = 'UPLOADOPENINGSTOCK'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : uploadOpeningStock() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            /**
             * Upload opening stock using the following steps
             * 1. Notify the user visually about commencement of the process (which is a background process)
             * 2. Handing over to a background process called intopeningstockproc
             */
            
            $fileid = $this->f3->get('PARAMS[id]');
            $userid = $this->f3->get('SESSION.id');
            if($fileid !== null){
                $this->logger->write("Product Controller : uploadOpeningStock() : Processing of file " . $fileid . " started", 'r');
                $procstatus = $this->util->intopeningstockproc($fileid, $userid); //initiate processing
                if ($procstatus) {
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Processing of file " . $fileid . " was successful OR has been resubmitted after a failure");
                }else {
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Processing of file " . $fileid . " was not successful");
                }
                //$this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER'));
                $this->f3->reroute('/uploadOpeningStock');
            } else {
                $this->logger->write("Product Controller : uploadOpeningStock() : No file Id was supplied.", 'r');
                $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "No file ID was specified.");
                $this->f3->reroute('/uploadOpeningStock');
            }
        } else {
            $this->logger->write("Product Controller : uploadOpeningStock() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
        
    /**
     *	Return a list of sub folders
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function subfolderlist(){
        $folder = $this->f3->get('POST.folder');
        if($folder !== null){//if folder is not empty
            $d = array ();
            $dl = $this->util->getlistofsubfolders($folder . '\incoming');
            foreach ($dl as $obj) {
                $d [] = $obj;
            }
            
            //filter out non-directories
            $fd = array_filter($d, function($v){ return ($v['type'] == 'dir');});
            $this->logger->write("Product Controller : subfolderlist() : The folder specified was " . $folder . ". The request has been processed succesfully", 'r');
            //$this->f3->set('fd', $fd);
            //$this->logger->write("Product Controller : index() : The JSON string is returned is " . json_encode($fd), 'r');
        }else {
            $this->logger->write("Product Controller : subfolderlist() : There was no folder specified. An empty list was returned", 'r');
            $fd = array();//return an empty array
        }
        
        die(json_encode($fd));
    }
    
    /**
     *	View files
     *	@return NULL
     *	@param NULL
     **/
    function viewfile(){
        $fileid = $this->f3->get('PARAMS[id]');
        //$userid = $this->f3->get('SESSION.id');
        $fileuploads = new fileuploads($this->db);
        $fileuploads->getByID($fileid);
        
        if($fileid !== null){
            $filename = $this->appsettings['GENERALDOCPATH'] . $fileuploads->fullpath . $fileuploads->uploadname;
            $this->logger->write("Product Controller : viewfile() : Sending of file " . $fileid . " to the web client started", 'r');
            $this->logger->write("Product Controller : viewfile() : The full path is " . $filename, 'r');
            $web = \Web::instance();
            if($web->send($filename)){//sending was a success
                $this->logger->write("Product Controller : viewfile() : Sending of file " . $fileid . " to the web client was successful", 'r');
            } else {
                $this->logger->write("Product Controller : viewfile() : Sending of file " . $fileid . " to the web client was not successful", 'r');
                $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER'));//refer the client back to the previous url
            }
            $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER'));//refer the client back to the previous url
        }
    }
    
    /**
     *	Archive files
     *	1. Notify the user visually about commencement of the archiving process (which is a background process)
     *  2. Handing over to a background process called intfilearchiving
     *	@return NULL
     *	@param NULL
     **/
    function archivefile(){
        $fileid = $this->f3->get('PARAMS[id]');
        $userid = $this->f3->get('SESSION.id');
        if($fileid !== null){
            $this->logger->write("Product Controller : archivefile() : Archiving of file " . $fileid . " started", 'r');
            $this->createinappnotification(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $userid, "Started archiving file " . $fileid);
            $archstatus = $this->util->intfilearchiving($fileid, $userid); //initiate validation
            if ($archstatus) {
                $this->createinappnotification(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $userid, "Archiving of file " . $fileid . " was successful");
            }else {
                $this->createinappnotification(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $userid, "Archiving of file " . $fileid . " was NOT successful");
            }
            
            $this->f3->reroute('/uploadOpeningStock');
        }
    }
    
    /**
     *	@name listopeningstockproclogs
     *  @desc Return validation logs
     *	@return NULL
     *	@param NULL
     **/
    function listopeningstockproclogs() {
        $operation = NULL; //tblevents
        $permission = 'UPLOADOPENINGSTOCK'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : uploadOpeningStock() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $this->logger->write("Product Controller : listopeningstockproclogs() : Processing list of processing logs", 'r');
            $sql = '';
            
            $data = array ();
            
            $sql = 'SELECT l.runid "Run Id",
                                    l.filename "File Name",
                                    l.inserteddt "Date",
                                    l.activity "Activity",
                                    concat(u.firstname, " ", u.lastname) "Run By",
                                    l.fileid "File Id"
                                FROM tblopeningstocklogs l
                                LEFT JOIN tblusers u ON l.insertedby = u.id
                                ORDER BY l.inserteddt DESC';
            
            $dtls = $this->db->exec($sql);
            //$this->logger->write($this->db->log(TRUE), 'r');
            
            foreach ( $dtls as $obj ) {
                $data [] = $obj;
            }
            
            //send to browser as JSON encoded object
            die(json_encode($data));
        } else {
            $this->logger->write("Product Controller : listopeningstockproclogs() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name listopeningstockprocruns
     *  @desc Return status of validation processes
     *	@return NULL
     *	@param NULL
     **/
    function listopeningstockprocruns() {
        $operation = NULL; //tblevents
        $permission = 'UPLOADOPENINGSTOCK'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : uploadOpeningStock() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $this->logger->write("Product Controller : listopeningstockprocruns() : Processing list of validation runs", 'r');
            $sql = '';
            
            $data = array ();
            
            $sql = 'SELECT v.runid "Run Id",
                                    v.filename "File Name",
                                    concat(u.firstname, " ", u.lastname) "Started By",
                                    v.startdt "Start Date",
                                    v.enddt "End Date",
                                    v.errorcount "Errors",
                                    s.name "Status",
                                    v.fileid "File Id"
                                FROM tblopeningstockruns v
                                LEFT JOIN tblstatuses s ON v.statusid = s.id AND groupid IN (1028)
                                LEFT JOIN tblusers u ON v.insertedby = u.id
                                ORDER BY v.inserteddt DESC';
            
            $dtls = $this->db->exec($sql);
            //$this->logger->write($this->db->log(TRUE), 'r');
            foreach ( $dtls as $obj ) {
                $data [] = $obj;
            }
            
            //send to browser as JSON encoded object
            die(json_encode($data));
        } else {
            $this->logger->write("Product Controller : listopeningstockprocruns() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name downloadopeningstockprocrpt
     *  @desc download validation report
     *	@return NULL
     *	@param NULL
     **/
    function downloadopeningstockprocrpt() {
        $operation = NULL; //tblevents
        $permission = 'UPLOADOPENINGSTOCK'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : downloadopeningstockprocrpt() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $fileid = $this->f3->get('PARAMS[id]');
            $filetablename = 'file_' . $fileid;
            if($fileid !== null){
                $this->logger->write("Product Controller : downloadopeningstockprocrpt() : Downloading of report for file " . $fileid . " started", 'r');
                $excel = new Sheet();
                $sql = 'SELECT productCode "Product Code",
        					supplierTIN "Supplier TIN",
        					supplierName "Suppier Name",
        					qty "Quantity",
        					unitPrice "Unit Price",
        					processingresults "Processing Results",
        					processingerrors "Processing Errors",
        					additionalprocessingresults "Additonal Processing Results",
        					responseCode "Response Code",
        					responseMessage "Response Message"
        					FROM '. $filetablename;
                
                $this->f3->set('headers',array('Product Code', 'Supplier TIN', 'Suppier Name', 'Quantity', 'Unit Price', 'Processing Results', 'Processing Errors', 'Additonal Processing Results', 'Response Code', 'Response Message'));
                $dtls = $this->db->exec($sql);
                //$this->logger->write($this->db->log(TRUE), 'r');
                $this->f3->set('rows', $dtls);
                echo $excel->renderXLS($this->f3->get('rows'), $this->f3->get('headers'), $filetablename . '.xls');
                $this->logger->write("Product Controller : downloadopeningstockprocrpt() : Downloading of report for file " . $fileid . " was successful", 'r');
            } else {
                $this->logger->write("Product Controller : downloadopeningstockprocrpt() : Opps, no file id is specified", 'r');
                $this->f3->reroute($this->f3->get('SERVER.HTTP_REFERER'));//return control to previous url
            }
        } else {
            $this->logger->write("Product Controller : downloadopeningstockprocrpt() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *	@name downloadfilelist
     *  @desc download list of files from the table tblfileuploads
     *	@return NULL
     *	@param NULL
     **/
    function downloadfilelist() {
        $operation = NULL; //tblevents
        $permission = 'UPLOADOPENINGSTOCK'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : downloadfilelist() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $excel = new Sheet();
            $sql = '';
            
            $this->logger->write("Product Controller : downloadfilelist() : Download list of files", 'r');
            
            $sql = 'SELECT t.id "ID",
                                    t.uploadname "File Name",
                                    t.folder "Folder",
                                    s.name "Status",
                                    v.startdt "Validation Date",
                                    t.inserteddt "Upload Date",
                                    concat(u.firstname, " ", u.lastname) "Uploaded By",
                                    s.id "Status ID",
                                    t.id "File ID",
                                    NULL "Actions"
                                FROM tblfileuploads t
                                LEFT JOIN tblopeningstockruns v ON v.fileid = t.id
                                LEFT JOIN tblstatuses s ON t.status = s.id AND groupid IN (1027)
                                LEFT JOIN tblusers u ON t.insertedby = u.id
                                ORDER BY t.inserteddt DESC';
            
            $this->f3->set('headers',array('ID', 'File Name', 'Folder', 'Status', 'Validation Date', 'Upload Date', 'Uploaded By'));
            $dtls = $this->db->exec($sql);
            //$this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('rows', $dtls);
            echo $excel->renderXLS($this->f3->get('rows'), $this->f3->get('headers'),"FileList.xls");
        } else {
            $this->logger->write("Product Controller : downloadfilelist() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
    
    /**
     *  @name listfiles
     *	@desc Return list of files from the table tblfileuploads
     *	@return JSON-encoded object
     *	@param NULL
     **/
    function listfiles() {
        $operation = NULL; //tblevents
        $permission = 'UPLOADOPENINGSTOCK'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $sql = '';
        $openingstockprocalert = "'The file has been submitted for upload. You can track progress by refreshing this list or from the processing run view'";
        $archivalalert = "'The file has been submitted for archival. You can track progress by refreshing this list'";
        $this->logger->write("Product Controller : listfiles() : Processing list of files", 'r');
        
        $data = array ();
        
        $this->logger->write("Product Controller : listfiles() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            //@TODO Display a new form
            
            
            $sql = 'SELECT t.id "ID",
                                    t.uploadname "File Name",
                                    t.folder "Folder",
                                    s.name "Status",
                                    v.startdt "Processing Date",
                                    t.inserteddt "Upload Date",
                                    concat(u.firstname, " ", u.lastname) "Uploaded By",
                                    s.id "Status ID",
                                    t.id "File ID",
                                    v.runid "Run ID",
                                    NULL "Actions"
                                FROM tblfileuploads t
                                LEFT JOIN tblopeningstockruns v ON v.fileid = t.id
                                LEFT JOIN tblstatuses s ON t.status = s.id AND groupid IN (1027)
                                LEFT JOIN tblusers u ON t.insertedby = u.id
                                ORDER BY t.inserteddt DESC';
            
            $dtls = $this->db->exec($sql);
            //$this->logger->write($this->db->log(TRUE), 'r');
            
            foreach ( $dtls as &$d ) {
                $d['ID'] = '<a href="viewfile/' . $d['ID'] . '">' . $d['ID'] . '</a>';
                
                if(trim($d['Status']) === "Processed Successfully"){
                    $d['Status'] = '<span class="label label-success">' . $d['Status'] . '</span>';
                } elseif (trim($d['Status']) === "Not Processed"){
                    $d['Status'] = '<span class="label label-warning">' . $d['Status'] . '</span>';
                } elseif (trim($d['Status']) === "Failed Processing"){
                    $d['Status'] = '<span class="label label-danger">' . $d['Status'] . '</span>';
                } else {
                    $d['Status'] = '<span class="label label-info">' . $d['Status'] . '</span>';
                }
                
                if($d['Status ID'] == 1041){
                    $d['Actions'] = '<a href="archivefile/' . $d['File ID'] . '" onclick="alert(' . $archivalalert . ')"; target="_blank"> Archive </a> | <a href="openingstockproc/' . $d['File ID'] . '" onclick="alert(' . $openingstockprocalert . ')"; target="_blank"> Process </a>';
                } elseif($d['Status ID'] == 1042){
                    $d['Actions'] = '<a href="archivefile/' . $d['File ID'] . '" onclick="alert(' . $archivalalert . ')"; target="_blank"> Archive </a> | <a href="downloadopeningstockprocrpt/' . $d['File ID'] . '"> Report </a>';
                } elseif($d['Status ID'] == 1040){
                    $d['Actions'] = '<a href="archivefile/' . $d['File ID'] . '" onclick="alert(' . $archivalalert . ')"; target="_blank"> Archive </a> | <a href="downloadopeningstockprocrpt/' . $d['File ID'] . '"> Report </a>';
                } 
            }
            
            foreach ( $dtls as $obj ) {
                $data [] = $obj;
            }
            
            //$this->logger->write("Product Controller : listfiles() : The JSON string is returned is " . json_encode($data), 'r');
            //send to browser as JSON encoded object
            die(json_encode($data));
        } else {
            $this->logger->write("Product Controller : listfiles() : The user is not allowed to perform this function", 'r');
            die(json_encode($data));
        }
    }
    
    /**
     *	@name uploadfiletofilesystem
     *  @desc Accept a file from the client and upload it to a folder
     *	@return NULL
     *	@param NULL
     **/
    function uploadfiletofilesystem() {
        $operation = NULL; //tblevents
        $permission = 'UPLOADOPENINGSTOCK'; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Product Controller : uploadOpeningStock() : Checking permissions", 'r');
        if ($this->userpermissions[$permission]) {
            
            $this->logger->write("Product Controller : uploadfiletofilesystem() : Uploading of file into folder started", 'r');
            $basefolder = $this->appsettings['GENERALDOCPATH'];
            $fs = new \FAL\LocalFS($basefolder);
            $incomingfolder = $this->f3->get('SESSION.username') . '\incoming\\';
            $this->logger->write("Product Controller : uploadfiletofilesystem() : The Incoming folder is: " . $incomingfolder, 'r');
            
            if($fs->isDir($incomingfolder)){
                $this->logger->write("Product Controller : uploadfiletofilesystem() : The Incoming folder : " . $incomingfolder . " exists", 'r');
                //self::$fileuploadfeedback = "The Incoming folder " . $incomingfolder . " exist";
            } else {
                $this->logger->write("Product Controller : uploadfiletofilesystem() : The Incoming folder : " . $incomingfolder . " does not exist", 'r');
                $fs->createDir($incomingfolder);
                //$this->logger->write("Product Controller : uploadfiletofilesystem() : The Incoming folder : " . $incomingfolder . " has been created", 'r');
            }
            
            $existsubfoldername = $this->f3->get('POST.existsubfoldername');
            $this->logger->write("Product Controller : uploadfiletofilesystem() : The existing folder = " . $existsubfoldername, 'r');
            $newsubfoldername = $this->f3->get('POST.newsubfoldername');
            $this->logger->write("Product Controller : uploadfiletofilesystem() : The new folder = " . $newsubfoldername, 'r');
            $description = $this->f3->get('POST.description');
            $filename = $this->f3->get('FILES.uploadfile.name');
            $filesize = $this->f3->get('FILES.uploadfile.size');
            $filetype = $this->f3->get('FILES.uploadfile.type');
            
            if (empty(trim($existsubfoldername)) && empty(trim($newsubfoldername))){//if non of the option was chosen
                $this->logger->write("Product Controller : uploadfiletofilesystem() : No folder was was specified", 'r');
                self::$fileuploadfeedback = "You did not specify an upload folder!";
                self::$fileuploadstatus = 0;
                $this->index();
            } elseif (!empty(trim($existsubfoldername)) && empty(trim($newsubfoldername))) {//if user has chosen an existing folder
                $subfoldername = $existsubfoldername;
                $this->logger->write("Product Controller : uploadfiletofilesystem() : The existing folder " . $existsubfoldername . " will be used", 'r');
            } elseif (!empty(trim($newsubfoldername)) && empty(trim($existsubfoldername))) {//if user has chosen to create a new folder
                $subfoldername = $newsubfoldername;
                $this->logger->write("Product Controller : uploadfiletofilesystem() : A new folder " . $newsubfoldername . " will be created", 'r');
            } else {
                $this->logger->write("Product Controller : uploadfiletofilesystem() : An error occured while uploading file", 'r');
                self::$fileuploadfeedback = "An error occured while uploading file!";
                self::$fileuploadstatus = 0;
                $this->index();
            }
            
            $fulluploadpath = $incomingfolder . $subfoldername . '\\';
            
            if($fs->isDir($fulluploadpath)){
                $this->logger->write("Product Controller : uploadfiletofilesystem() : The full upload path : " . $fulluploadpath . " exists", 'r');
                //self::$fileuploadfeedback = "The Incoming folder " . $incomingfolder . " exist";
            } else {
                $this->logger->write("Product Controller : uploadfiletofilesystem() : The full upload path : " . $fulluploadpath . " does not exist", 'r');
                $fs->createDir($fulluploadpath);
                //$this->logger->write("Product Controller : uploadfiletofilesystem() : The full upload path : " . $fulluploadpath . " has been created", 'r');
            }
            
            $this->f3->set('UPLOADS', $basefolder . $fulluploadpath);
            $overwrite = true; // set to true, to overwrite an existing file; Default: false
            $slug = false; // rename file to filesystem-friendly version
            
            $web = \Web::instance();
            $files = $web->receive(function ($file, $formFieldName) {//$formFieldName is the name of the file input element on the upload form
                if ($file['size'] < 1 || $file['size'] > $this->appsettings['MAXFILESIZE']) { // maybe you want to check the file size
                    return false; // this file is bigger than 2MB or 0B, returning false will skip moving it
                }
                
                return true; // everything went fine, allows the file to be moved from php tmp dir to your defined upload dir
            }, $overwrite, $slug);
                
                //var_dump($files);
                $x = array_keys($files);
                
                if($files[$x[0]]){
                    $this->logger->write("Product Controller : uploadfiletofilesystem() : The file was successfuly uploaded", 'r');
                    self::$fileuploadfeedback = "The file '" . $this->f3->get('FILES.uploadfile.name') . "' was successfully uploaded";
                    self::$fileuploadstatus = 1;
                    $this->util->createauditlog($this->f3->get('SESSION.id'), "Uploaded a file called " . $filename);
                    $this->util->createinappnotification(NULL, NULL, NULL, self::$module, self::$submodule, $operation, $event, $eventnotification, NULL, $this->f3->get('SESSION.id'), "Your file " . $filename . " was successfully uploaded");
                    //$fulluploadpath = $basefolder . $fulluploadpath . $filename; //update fulluploadpath
                    $values = "'" . addslashes($filetype) . "', " . "'" . addslashes($filename) . "', " . "'" . addslashes($subfoldername) . "', " . "'" . addslashes($description) . "', " . "'" . addslashes($fulluploadpath) . "', " . $filesize . ", " . $this->appsettings['DEFAULTFILESTATUS'] . ", NOW(), " . $this->f3->get('SESSION.id') . ", NOW(), " . $this->f3->get('SESSION.id');
                    $sql = 'INSERT INTO tblfileuploads
                            (filetype, uploadname, folder, description, fullpath, bytes, status, inserteddt, insertedby, modifieddt, modifiedby)
                            VALUES (' . $values . ')';
                    $this->logger->write("Product Controller : uploadfiletofilesystem() : The insert SQL is : " . $sql, 'r');
                    $this->db->exec(array($sql));
                } else {
                    self::$fileuploadfeedback = "An error was encountered while uploading the file. Please try again or another file";
                    self::$fileuploadstatus = 0;
                }
                
                $this->f3->reroute('/uploadOpeningStock');
        } else {
            $this->logger->write("Product Controller : uploadOpeningStock() : The user is not allowed to perform this function", 'r');
            $this->f3->reroute('/forbidden');
        }
    }
}
?>