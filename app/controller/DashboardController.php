<?php

/**
 * This file is part of the etaxware system
 * The is the dashboard controller class
 * @date: 08-04-2019
 * @file: DashboardController.php
 * @path: ./app/controller/DashboardController.php
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
class DashboardController extends MainController{
    protected static $module = NULL; //tblmodules
    protected static $submodule = NULL; //tblsubmodules

    protected function fetchsinglestat($sql){
        try {
            $rows = $this->db->exec($sql);
            if (is_array($rows) && isset($rows[0]['value'])) {
                return (int)$rows[0]['value'];
            }
        } catch (Exception $e) {
            $this->logger->write('Dashboard Controller : fetchsinglestat() : Failed query. Error is ' . $e->getMessage(), 'r');
        }

        return 0;
    }

    protected function fetchmonthlycountmap($tableName){
        $result = array();

        // Keep the chart lightweight by only loading the most recent 6 monthly buckets.
        $sql = 'SELECT DATE_FORMAT(inserteddt, "%Y-%m") "bucket", COUNT(*) "value" '
            . 'FROM ' . $tableName . ' '
            . 'WHERE inserteddt >= DATE_SUB(DATE_FORMAT(CURDATE(), "%Y-%m-01"), INTERVAL 5 MONTH) '
            . 'GROUP BY DATE_FORMAT(inserteddt, "%Y-%m") '
            . 'ORDER BY DATE_FORMAT(inserteddt, "%Y-%m") ASC';

        try {
            $rows = $this->db->exec($sql);
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $result[trim((string)$row['bucket'])] = (int)$row['value'];
                }
            }
        } catch (Exception $e) {
            $this->logger->write('Dashboard Controller : fetchmonthlycountmap() : Failed query for ' . $tableName . '. Error is ' . $e->getMessage(), 'r');
        }

        return $result;
    }

    protected function buildlastsixmonthlabels(){
        $labels = array();
        for ($i = 5; $i >= 0; $i--) {
            $labels[] = date('Y-m', strtotime('-' . $i . ' month'));
        }

        return $labels;
    }

    function index(){
        $invoiceCount = $this->fetchsinglestat('SELECT COUNT(*) "value" FROM tblinvoices');
        $invoiceSynced = $this->fetchsinglestat('SELECT COUNT(*) "value" FROM tblinvoices WHERE TRIM(IFNULL(referenceno, "")) <> ""');

        $creditCount = $this->fetchsinglestat('SELECT COUNT(*) "value" FROM tblcreditnotes');
        $creditSynced = $this->fetchsinglestat('SELECT COUNT(*) "value" FROM tblcreditnotes WHERE TRIM(IFNULL(referenceno, "")) <> ""');

        $debitCount = $this->fetchsinglestat('SELECT COUNT(*) "value" FROM tbldebitnotes');
        $debitSynced = $this->fetchsinglestat('SELECT COUNT(*) "value" FROM tbldebitnotes WHERE TRIM(IFNULL(referenceno, "")) <> ""');

        $productCount = $this->fetchsinglestat('SELECT COUNT(*) "value" FROM tblproductdetails');
        $customerCount = $this->fetchsinglestat('SELECT COUNT(*) "value" FROM tblcustomers');
        $supplierCount = $this->fetchsinglestat('SELECT COUNT(*) "value" FROM tblsuppliers');
        $events24h = $this->fetchsinglestat('SELECT COUNT(*) "value" FROM tblevents WHERE inserteddt >= DATE_SUB(NOW(), INTERVAL 1 DAY)');

        $labels = $this->buildlastsixmonthlabels();
        $invoiceTrendMap = $this->fetchmonthlycountmap('tblinvoices');
        $creditTrendMap = $this->fetchmonthlycountmap('tblcreditnotes');
        $debitTrendMap = $this->fetchmonthlycountmap('tbldebitnotes');

        $invoiceTrend = array();
        $creditTrend = array();
        $debitTrend = array();
        foreach ($labels as $bucket) {
            $invoiceTrend[] = isset($invoiceTrendMap[$bucket]) ? $invoiceTrendMap[$bucket] : 0;
            $creditTrend[] = isset($creditTrendMap[$bucket]) ? $creditTrendMap[$bucket] : 0;
            $debitTrend[] = isset($debitTrendMap[$bucket]) ? $debitTrendMap[$bucket] : 0;
        }

        $kpis = array(
            'invoices' => $invoiceCount,
            'invoicesSynced' => $invoiceSynced,
            'creditnotes' => $creditCount,
            'debitnotes' => $debitCount,
            'products' => $productCount,
            'customers' => $customerCount,
            'suppliers' => $supplierCount,
            'events24h' => $events24h
        );

        $chartData = array(
            'labels' => $labels,
            'trend' => array(
                'invoices' => $invoiceTrend,
                'creditnotes' => $creditTrend,
                'debitnotes' => $debitTrend
            ),
            'mix' => array(
                'invoices' => $invoiceCount,
                'creditnotes' => $creditCount,
                'debitnotes' => $debitCount
            ),
            'sync' => array(
                'invoicesSynced' => $invoiceSynced,
                'invoicesPending' => max(0, $invoiceCount - $invoiceSynced),
                'creditSynced' => $creditSynced,
                'creditPending' => max(0, $creditCount - $creditSynced),
                'debitSynced' => $debitSynced,
                'debitPending' => max(0, $debitCount - $debitSynced)
            )
        );

        $this->f3->set('kpis', $kpis);
        $this->f3->set('dashboardChartData', json_encode($chartData));
        
        $this->f3->set('pagetitle','Dashboard');
        $this->f3->set('pagecontent','Dashboard.htm');
        $this->f3->set('pagescripts','DashboardFooter.htm');
        echo \Template::instance()->render('Layout.htm');
    }
}

?>