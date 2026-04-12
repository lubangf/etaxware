/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the customer module
 * @date: 06-09-2020
 * @file: customer.js
 * @path: ./app/public/js/customer.js
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
$(function(){
	'use strict';	
	
	/**
	 * @desc initialise select2 dropdowns
	 */
	$(".select2").select2();	
	
	
	/**
	 * @desc Create a datatable called tbl-customer
	 */
	$('#tbl-customer-list').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-customer-list
	
	/**
	 * @desc populate the commodity category search on the customers screen
	 */	
	$('#checkcustomercommoditycode').select2({
		placeholder : "Search Commodity Category...",
		minimumInputLength : 2,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchcommoditycodes",
			dataType : 'json',
			delay : 250,
			cache : false,
			data : function(params) {
				return {
					name : params.term, // search term
					page : params.page, // page
				};
			},
			processResults : function(data) {
				return {
					results : $.map(data, function(item) {
						return {
							text : item.Name,
							id : item.Code,
							disabled: Number(item.Disabled)
						};
					})
				};
			},
			formatNoResults : function() {
				return "No results returned";
			},
			formatAjaxError : function() {
				return "An error occured";
			}
		}
	});	
	
	
	/**
	 * @desc Fetch a list of customers asyncronously and populate a datatable
	 */
	window.listcustomers = function(){
		$.ajax({
			url: '../etaxware/listcustomers',
			data: {
				//insert parameters here
			},
			method: 'GET',
			success: function(data){

				//console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "ID", "data": "ID"},
					{"title": "TIN", "data": "TIN"},
					{"title": "Legal Name", "data": "Legal Name"},
					{"title": "Mobile Phone", "data": "Mobile Phone"},
					{"title": "Email", "data": "Email"},
					{"title": "Type", "data": "Type Name"},						
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewcustomer\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a> | <a href='' title='Check' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-customer-check'><i class='fa fa-binoculars'></i></a> | <a href='' title='ERP Download' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-customer-erp-download'><i class='fa fa-cloud-download'></i></a>";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"ERP ID": d[i]['ERP ID'],
						"ERP Code": d[i]['ERP Code'],												
						"TIN": d[i]['TIN'],
						"NINBRN": d[i]['NINBRN'],
						"Passport Num": d[i]['Passport Num'],
						"Legal Name": d[i]['Legal Name'],						
						"Business Name": d[i]['Business Name'],
						"Address": d[i]['Address'],
						"Mobile Phone": d[i]['Mobile Phone'], 
						"Line Phone": d[i]['Line Phone'], 
						"Email": d[i]['Email'], 
						"Place of Business": d[i]['Place of Business'], 
						"Type Name": d[i]['Type Name'], 
						"Citizineship": d[i]['Citizineship'], 
						"Sector": d[i]['Sector'], 																	
						"Disabled": d[i]['Disabled'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-customer-list').remove(); 
				
				$('#tbl-customer-list').DataTable().destroy(); // destory existing datatable
				$('#tbl-customer-list').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-customer-list').DataTable({
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
				//console.log(data);
			}
		});//.ajax
	};//listcustomers	

	/**
	 * @desc validate a TIN
	 */
	$('#customertin').blur(function() {
        var tin = $(this).val();
		
		if (tin.length == 10){
			//$(this).attr('disabled', 'disabled');
			$('#tin-val-text').text('');
			$('#modal-validate-customertin').modal('show');
			
			$.ajax({
				url: '../etaxware/querytaxpayer',
				data: {
					//insert parameters here
					'tin' : tin
				},
				method: 'POST',
				cache: false,
				success: function(data){
					console.log(data);
					var d = JSON.parse(data);
					//console.log(d['tin']);
	
					if(d['tin']){
						$('#tin-val-text').removeClass("help-block text-red");
						$('#tin-val-text').addClass("help-block text-green");
						$('#tin-val-text').text('Validation was completed. The tax payer details will be populated soon.');
						
						$('#customerninbrn').val(d['ninBrn']);
						//$('#customerninbrn').attr('disabled', 'disabled');
						
						$('#customerlegalname').val(d['legalName']);
						//$('#customerlegalname').attr('disabled', 'disabled');
						
						$('#customerbusinessname').val(d['businessName']);
						//$('#customerbusinessname').attr('disabled', 'disabled');
						
						$('#customeraddress').val(d['address']);
						//$('#customeraddress').attr('disabled', 'disabled');
						
						$('#customermobilephone').val(d['contactNumber']);
						//$('#customermobilephone').attr('disabled', 'disabled');
						
						$('#customeremailaddress').val(d['contactEmail']);
						//$('#customeremailaddress').attr('disabled', 'disabled');
						
					} else {
						$('#tin-val-text').removeClass("help-block text-green");
						$('#tin-val-text').addClass("help-block text-red");
						$('#tin-val-text').text('Validation was completed. An internal error occured, or system is in offline mode, or the tax payer does not exist!');	
						//$('#customertin').removeAttr('disabled');
					}
					
				},
				error: function(data) {
					console.log(data);
					$('#tin-val-text').removeClass("help-block text-green");
					$('#tin-val-text').addClass("help-block text-red");
					$('#tin-val-text').text('An Internal Error occured. Validation was not succesfully');
					//$(this).removeAttr('disabled');	
				}
			});//.ajax	
			
			
			$('#modal-validate-customertin').modal('hide');			
		} else {
			$('#tin-val-text').removeClass("help-block text-green");
			$('#tin-val-text').addClass("help-block text-red");
			$('#tin-val-text').text('Please, Input 10 characters. You have input '  + tin.length + ' character(s) only!');
		}	
	
	});//customertin
	
	/**
     * @desc Populate a modal called modal-customer-check
     */
    
	$("#modal-customer-check").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		
		$('#checkcustomerid').val(id);		
		$('#checkcustomerid_2').val(id);
	});//modal-customer-check 	
	
	$("#modal-customer-erp-download").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		
		$('#erpdownloadcustomerid').val(id);		
		$('#erpdownloadcustomerid_2').val(id);
	});//modal-customer-check-erp-download							
});