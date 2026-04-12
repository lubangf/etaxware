<?php
/**
 * @name ReportController
 * @desc This file is part of the etaxware system. The is the Report controller class
 * @date 08-09-2022
 * @file ReportController.php
 * @path ./app/controller/ReportController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

Class ReportController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules
    
    /**
     *	@name index
     *  @desc Loads the index page
     *	@return NULL
     *	@param NULL
     **/
    function index(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $reportgroup = new reportgroups($this->db);
        $reportgroups = $reportgroup->all();
        $this->f3->set('reportgroups', $reportgroups);
        
        $report = new reports($this->db);
        $reports = $report->getActive();
        $this->f3->set('reports', $reports);
        
        $reportformat = new reportformats($this->db);
        $reportformats = $reportformat->all();
        $this->f3->set('reportformats', $reportformats);
        
        $invoicetype = new invoicetypes($this->db);
        $invoicetypes = $invoicetype->all();
        $this->f3->set('invoicetypes', $invoicetypes);
        
        $invoicekind = new invoicekinds($this->db);
        $invoicekinds = $invoicekind->all();
        $this->f3->set('invoicekinds', $invoicekinds);
        
        $currency = new currencies($this->db);
        $currencies = $currency->all();
        $this->f3->set('currencies', $currencies);
        
        $cdnoteapprovestatus = new cdnoteapprovestatuses($this->db);
        $cdnoteapprovestatuses = $cdnoteapprovestatus->all();
        $this->f3->set('cdnoteapprovestatuses', $cdnoteapprovestatuses);
        
        $user = new users($this->db);
        $users = $user->all();
        $this->f3->set('users', $users);
        
        $this->f3->set('pagetitle','Reports');
        $this->f3->set('pagecontent','Report.htm');
        $this->f3->set('pagescripts','ReportFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
    
    /**
     *	@name listreportgroups
     *  @desc list report groups
     *	@return JSON-encoded object
     *	@param NULL
     */
    function listreportgroups(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Report Controller : listreportgroups() : Processing list of report groups started", 'r');
        $sql = '';
        
        $data = array ();
        
        $sql = 'SELECT rpg.id "ID", rpg.name "Name", rpg.disabled "Disabled"
                FROM tblreportgroups rpg';
        
        try {
            $dtls = $this->db->exec($sql);
            
            //$this->logger->write($this->db->log(TRUE), 'r');
            foreach ( $dtls as $obj ) {
                $data [] = $obj;
            }
        } catch(Exception $e) {
            $this->logger->write("Report Controller : listreportgroups() : The operation to retrive report groups was not successful. The error messages is " . $e->getMessage(), 'r');
        }
        
        //send to browser as JSON encoded object
        die(json_encode($data));
    }
    
    /**
     *	@name listreports
     *  @desc list reports
     *	@return JSON-encoded object
     *	@param NULL
     */
    function listreports(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Report Controller : listreports() : Processing list of reports started", 'r');
        $sql = '';
        
        $data = array ();
        
        $groupid = trim($this->f3->get('POST.id'));
        
        if (trim($groupid) !== '' || !empty(trim($groupid))) {
            $sql = 'SELECT r.id "ID", r.name "Name", r.disabled "Disabled"
                    FROM tblreports r
                    WHERE r.groupid = ' . $groupid;
            
            try {
                $dtls = $this->db->exec($sql);
                
                //$this->logger->write($this->db->log(TRUE), 'r');
                foreach ( $dtls as $obj ) {
                    $data [] = $obj;
                }
            } catch(Exception $e) {
                $this->logger->write("Report Controller : listreports() : The operation to retrive reports was not successful. The error messages is " . $e->getMessage(), 'r');
            }
        }
        
        //send to browser as JSON encoded object
        die(json_encode($data));
    }
    
    /**
     *	@name runreport
     *  @desc execute report
     *	@return NULL
     *	@param NULL
     */
    function runreport(){
        $operation = NULL; //tblevents
        $permission = NULL; //tblpermissions
        $event = NULL; //tblevents
        $eventnotification = NULL; //tbleventnotifications
        
        $this->logger->write("Report Controller : executereport() : Execution of a report started", 'r');
        
        $rptgroup = $this->f3->get('POST.rptgroup');
        $report = $this->f3->get('POST.reports');
        
        $user = $this->f3->get('POST.users');
        
        $format = $this->f3->get('POST.format');
        $startdate = date('Y-m-d H:i:s', strtotime($this->f3->get('POST.startdate')));
        $enddate = date('Y-m-d H:i:s', strtotime($this->f3->get('POST.enddate')));
        
        $product = $this->f3->get('POST.product');
        $fromamount = $this->f3->get('POST.fromamount');
        $toamount = $this->f3->get('POST.toamount');
        $buyertin = $this->f3->get('POST.buyertin');
        $buyerlegalname = $this->f3->get('POST.buyerlegalname');
        $fdn = $this->f3->get('POST.fdn');
        $currency = $this->f3->get('POST.currency');
        $invoicetype = $this->f3->get('POST.invoicetype');
        $invoicekind = $this->f3->get('POST.invoicekind');
        $creditnotestatus = $this->f3->get('POST.creditnotestatus');
        
        $this->logger->write("Report Controller : executereport() : The report group is: " . $rptgroup, 'r');
        $this->logger->write("Report Controller : executereport() : The report id is: " . $report, 'r');
        $this->logger->write("Report Controller : executereport() : The report format is: " . $format, 'r');
        $this->logger->write("Report Controller : executereport() : The report startdate is: " . $startdate, 'r');
        $this->logger->write("Report Controller : executereport() : The report endate is: " . $enddate, 'r');
        
        $excel = new Sheet();
        $sql = '';
        $rpt = new reports($this->db);
        $rpt->getByID($report);
        
        $filename = md5(uniqid(rand(), true));
        
        $basefolder = $this->util->generatedirectorypath($this->f3->get('SESSION.id'), 'RPT');
        $fs = new \FAL\LocalFS($basefolder);
        
        $downloadfolder = '//' . md5(uniqid(rand(), true)) . '//';
        
        $this->logger->write("Report Controller : executereport() : The temp folder is: " . $downloadfolder, 'r');
        
        if($fs->isDir($downloadfolder)){
            $this->logger->write("Report Controller : executereport() : The temp folder : " . $downloadfolder . " exists", 'r');
        } else {
            $this->logger->write("Report Controller : executereport() : The temp folder : " . $downloadfolder . " does not exist", 'r');
            $fs->createDir($downloadfolder);
            $this->logger->write("Report Controller : executereport() : The temp folder : " . $downloadfolder . " has been created", 'r');
        }
        
        $fullreportpath = $basefolder . $downloadfolder;
        
        if ($report == '150') {
            //RPTUSERS
            $sql = '';
            
            $headers = array();
        } elseif($report == '1') {
            //RPTAUDIT
            $sql = 'SELECT  p.id "ID",
                        p.windowsuser "OS User",
                        p.ipaddress "IP Address",
                        p.systemname "System Name",
                        p.voucherNumber "Voucher Number",
                        p.voucherRef "Voucher Ref",
                        p.productCode "Product Code",
                        p.responseCode "Response Code",
                        p.responseMessage "Response Message",
                        p.TIN "TIN",
                        p.description "Operation",
                        p.inserteddt "Creation Date",
                        s.username "Created By"
                    FROM tblerpauditlogs p
                    LEFT JOIN tblusers s ON p.insertedby = s.id
                    ORDER By p.id DESC';
            
            $headers = array('ID', 'OS User', 'IP Address', 'System Name', 'Voucher Number', 'Voucher Ref', 'Product Code', 'Response Code', 'Response Message', 'TIN', 'Operation', 'Creation Date', 'Created By');
        } else {
            $this->logger->write("Report Controller : executereport() : There was no report selected", 'r');
            $this->f3->reroute('/report');
        }
        
        try{
            $dtls = $this->db->exec($sql);
            //$this->logger->write($this->db->log(TRUE), 'r');
            $this->f3->set('rows', $dtls);
            
            $this->f3->set('headers', $headers);
            
            if ($format == '152') {//Excel
                echo $excel->renderXLS($this->f3->get('rows'), $this->f3->get('headers'), $filename. '.xls');
            } elseif ($format == '151'){//Pdf
                $phpExcel = new Spreadsheet();
                $writer = new Xlsx($phpExcel);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                
                $phpExcel->getProperties()->setCreator($this->appsettings['APPLONGNAME'])
                ->setTitle($rpt->name)
                ->setSubject($rpt->description);
                
                $phpExcel->getActiveSheet()->setTitle($rpt->name);
                $phpExcel->setActiveSheetIndex(0)->fromArray($headers, null, 'A1');
                $phpExcel->setActiveSheetIndex(0)->fromArray($dtls, null, 'A2');
                
                
                if($fs->exists($fullreportpath . $filename. '.xlsx')){
                    $this->logger->write("Report Controller : executereport() : There excel file exists in the tmp location", 'r');
                    $fs->delete($fullreportpath . $filename. '.xlsx');
                    $this->logger->write("Report Controller : executereport() : There excel file has been deleted", 'r');
                }
                
                if($fs->exists($fullreportpath . $filename. '.pdf')){
                    $this->logger->write("Report Controller : executereport() : There pdf file exists in the tmp location", 'r');
                    $fs->delete($fullreportpath . $filename. '.pdf');
                    $this->logger->write("Report Controller : executereport() : There pdf file has been deleted", 'r');
                }
                
                $writer->save($fullreportpath . $filename. '.xlsx');
                
                $pdfreader = $reader->load($fullreportpath . $filename. '.xlsx');
                $pdfreader->getDefaultStyle()->applyFromArray(
                    [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ]
                    ]
                    );
                
                $pdfwriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($pdfreader, 'Mpdf');
                $pdfwriter->save($fullreportpath . $filename. '.pdf');
                
                
                header('Content-type: application/pdf');// We'll be outputting a PDF
                header('Content-Disposition: attachment; filename="' . $filename. '.pdf' . '"');
                readfile($fullreportpath . $filename. '.pdf');// The PDF source is in original.pdf
                
                if($fs->isDir($downloadfolder)){
                    $this->logger->write("Report Controller : executereport() : The temp folder : " . $downloadfolder . " exists", 'r');
                    $fs->removeDir($downloadfolder);
                    $this->logger->write("Report Controller : executereport() : The temp folder : " . $downloadfolder . " has been deleted", 'r');
                }
                
            } else {
                $this->logger->write("Report Controller : executereport() : There was no report format selected", 'r');
                $this->f3->reroute('/report');
            }
        } catch (Exception $e) {
            $this->logger->write("Report Controller : executereport() : The operation to execute the report was not successful. The error message is " . $e->getMessage(), 'r');
            $this->f3->reroute('/report');
        }
        
        $this->logger->write("Report Controller : executereport() : Execution of a report completed", 'r');
    }
}