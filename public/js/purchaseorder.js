/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the purchase orders module
 * @date: 06-09-2022
 * @file: purchaseorder.js
 * @path: ./app/public/js/purchaseorder.js
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) Digital Formulae Limited - All Rights Reserved
 * @version    1.0.0
 */
$(function(){
	'use strict';
	
	/**
	 * @desc Create a datatable called tbl-purchaseorder-list
	 */
	$('#tbl-purchaseorder-list').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-purchaseorder-list	
	
	/**
	 * @desc Fetch a list of purchaseorders asyncronously and populate a datatable
	 */
	window.listpurchaseorders = function(){
		$.ajax({
			url: '../etaxware/listpurchaseorders',
			data: {
				//insert parameters here
			},
			method: 'GET',
			success: function(data){

				console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "ID", "data": "ID"},
					{"title": "ERP PO No", "data": "ERP Purchase Order No"},
					{"title": "Currency", "data": "Currency"},
					{"title": "Gross Amount", "data": "Gross Amount"},
					{"title": "Item Count", "data": "Item Count"},
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewpurchaseorder\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a> | <a href='' title='EFRIS Upload' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-purchaseorder-efris-upload'><i class='fa fa-upload'></i></a> | <a href='' title='ERP Download' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-purchaseorder-erp-download'><i class='fa fa-cloud-download'></i></a>";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"ERP Purchase Order Id": d[i]['ERP Purchase Order Id'],
						"ERP Purchase Order No": d[i]['ERP Purchase Order No'],
						"Issued Date": d[i]['Issued Date'],
						"Currency": d[i]['Currency'],
						"Net Amount": d[i]['Net Amount'],
						"Tax Amount": d[i]['Tax Amount'],
						"Gross Amount": d[i]['Gross Amount'],
						"Item Count": d[i]['Item Count'],	
						"Id": d[i]['Id'],
						"Disabled": d[i]['Disabled'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-purchaseorder-list').remove(); 
				
				$('#tbl-purchaseorder-list').DataTable().destroy(); // destory existing datatable
				$('#tbl-purchaseorder-list').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-purchaseorder-list').DataTable({
					"dom": "Bfrtip",
					"processing": true,
					"dataType": "json",
					"data": rows.aaData,
					"columns": columns.aaColumn,
				    "paging": true,
				    "lengthChange": true,
				    "searching": true,
				    "ordering": true,
				    "info": true,
				    "scrollX": true,
				    "autoWidth": true,
				    "destroy": true
				});
			},
			error: function(data) {
				console.log(data);
			}
		});//.ajax
	};//listpurchaseorders	
	
	$("#modal-purchaseorder-efris-upload").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		
		$('#efrisuploadpurchaseorderid').val(id);		
		$('#efrisuploadpurchaseorderid_2').val(id);
	});//modal-purchaseorder-efris-upload
	
	$("#modal-purchaseorder-erp-download").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		
		$('#erpdownloadpurchaseorderid').val(id);		
		$('#erpdownloadpurchaseorderid_2').val(id);
	});//modal-purchaseorder-erp-download		
});