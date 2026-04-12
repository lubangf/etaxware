/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the invoice module
 * @date: 06-09-2020
 * @file: invoice.js
 * @path: ./app/public/js/invoice.js
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
	 * @desc populate the buyer search on the invoice screen
	 */	
	$('#pickbuyertemplate').select2({
		placeholder : "Search Customers...",
		minimumInputLength : 4,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../../etaxware/listcustomers",
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
							id : item.ID,
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
	 * @desc populate the currency search on the invoice screen
	 */
	$('#currency').select2({
		placeholder : "Search Currency...",
		minimumInputLength : 2,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchcurrencies",
			dataType : 'json',
			delay : 250,
			cache : false,
			data : function(params) {
				return {
					name : params.term,
					page : params.page,
				};
			},
			processResults : function(data) {
				return {
					results : $.map(data, function(item) {
						return {
							text : item.Code + ' - ' + item.Name,
							id : item.Name,
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
	 * @desc populate the invoice type search on the invoice screen
	 */
	$('#invoicetype').select2({
		placeholder : "Search Invoice Type...",
		minimumInputLength : 1,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchinvoicetypes",
			dataType : 'json',
			delay : 250,
			cache : false,
			data : function(params) {
				return {
					name : params.term,
					page : params.page,
				};
			},
			processResults : function(data) {
				return {
					results : $.map(data, function(item) {
						return {
							text : item.Code + ' - ' + item.Name,
							id : item.Code,
							disabled: Number(item.Disabled)
						};
					})
				};
			}
		}
	});

	/**
	 * @desc populate the invoice kind search on the invoice screen
	 */
	$('#invoicekind').select2({
		placeholder : "Search Invoice Kind...",
		minimumInputLength : 1,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchinvoicekinds",
			dataType : 'json',
			delay : 250,
			cache : false,
			data : function(params) {
				return {
					name : params.term,
					page : params.page,
				};
			},
			processResults : function(data) {
				return {
					results : $.map(data, function(item) {
						return {
							text : item.Code + ' - ' + item.Name,
							id : item.Code,
							disabled: Number(item.Disabled)
						};
					})
				};
			}
		}
	});

	/**
	 * @desc populate the data source search on the invoice screen
	 */
	$('#datasource').select2({
		placeholder : "Search Data Source...",
		minimumInputLength : 1,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchdatasources",
			dataType : 'json',
			delay : 250,
			cache : false,
			data : function(params) {
				return {
					name : params.term,
					page : params.page,
				};
			},
			processResults : function(data) {
				return {
					results : $.map(data, function(item) {
						return {
							text : item.Code + ' - ' + item.Name,
							id : item.Code,
							disabled: Number(item.Disabled)
						};
					})
				};
			}
		}
	});

	/**
	 * @desc populate the industry search on the invoice screen
	 */
	$('#industri').select2({
		placeholder : "Search Industry...",
		minimumInputLength : 1,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchindustries",
			dataType : 'json',
			delay : 250,
			cache : false,
			data : function(params) {
				return {
					name : params.term,
					page : params.page,
				};
			},
			processResults : function(data) {
				return {
					results : $.map(data, function(item) {
						return {
							text : item.Code + ' - ' + item.Name,
							id : item.Code,
							disabled: Number(item.Disabled)
						};
					})
				};
			}
		}
	});

	/**
	 * @desc populate product search on invoice add/edit good modals
	 */
	$('#additem, #edititem').select2({
		placeholder : "Search Product...",
		minimumInputLength : 2,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchproducts",
			dataType : 'json',
			delay : 250,
			cache : false,
			data : function(params) {
				return {
					name : params.term,
					page : params.page,
				};
			},
			processResults : function(data) {
				return {
					results : $.map(data, function(item) {
						return {
							text : item.Code + ' - ' + item.Name,
							id : item.Code,
							disabled: Number(item.Disabled)
						};
					})
				};
			}
		}
	});
	
	/**
	 * @desc validate a TIN
	 */
	$('#buyertin').blur(function() {
        var tin = $(this).val();
		
		if (tin.length == 10){
			//$(this).attr('disabled', 'disabled');
			$('#tin-val-text').text('');
			$('#modal-validate-buyertin').modal('show');
			
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
						
						$('#buyerninbrn').val(d['ninBrn']);
						//$('#buyerninbrn').attr('disabled', 'disabled');
						
						$('#buyerlegalname').val(d['legalName']);
						//$('#buyerlegalname').attr('disabled', 'disabled');
						
						$('#buyerbusinessname').val(d['businessName']);
						//$('#buyerbusinessname').attr('disabled', 'disabled');
						
						$('#buyeraddress').val(d['address']);
						//$('#buyeraddress').attr('disabled', 'disabled');
						
						$('#buyermobilephone').val(d['contactNumber']);
						//$('#buyermobilephone').attr('disabled', 'disabled');
						
						$('#buyeremailaddress').val(d['contactEmail']);
						//$('#buyeremailaddress').attr('disabled', 'disabled');
						
					} else {
						$('#tin-val-text').removeClass("help-block text-green");
						$('#tin-val-text').addClass("help-block text-red");
						$('#tin-val-text').text('Validation was completed. An internal error occured, or system is in offline mode, or the tax payer does not exist!');	
						//$('#buyertin').removeAttr('disabled');
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
			
			
			$('#modal-validate-buyertin').modal('hide');			
		} else {
			$('#tin-val-text').removeClass("help-block text-green");
			$('#tin-val-text').addClass("help-block text-red");
			$('#tin-val-text').text('Please, Input 10 characters. You have input '  + tin.length + ' character(s) only!');
		}	
	
	});//buyertin
	
	/**
	 * @desc Create a datatable called tbl-invoice-list
	 */
	$('#tbl-invoice-list').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-invoice-list
	
	/**
	 * @desc Create a datatable called tbl-invoice-taxes
	 */
	$('#tbl-invoice-taxes').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-invoice-taxes	
	
		/**
	 * @desc Create a datatable called tbl-invoice-payments
	 */
	$('#tbl-invoice-payments').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-invoice-payments	
	
	/**
	 * @desc Create a datatable called tbl-invoice-goods
	 */
	$('#tbl-invoice-goods').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-invoice-goods	
	
	/**
	 * @desc Change some elements on the products form depending on the choice of execise flag
	 */	
	$("#addexciseflag").change(function(e) {
		var $option = $(this).find('option:selected');

		var value = $option.val();//to get content of "value" attrib
	    var text = $option.text();//to get <option>Text</option> content
	    
	    //console.log(text + ' has been selected...');
	    //console.log('the value is ' + value);
	    
	    if(value == '2'){//2 is No
	    	//console.log('Disabling elements...');
	    	$('#addexciserate').attr('disabled', 'disabled');
	    	$('#addexciserule').attr('disabled', 'disabled');
	    	$('#addexcisetax').attr('disabled', 'disabled');
	    	$('#addpack').attr('disabled', 'disabled');
			$('#addstick').attr('disabled', 'disabled');
			$('#addexciseunit').attr('disabled', 'disabled');
			$('#addexcisecurrency').attr('disabled', 'disabled');
			$('#addexciseratename').attr('disabled', 'disabled');
	    } else {	    	
	    	//console.log('Refreshing page...');
	    	$('#addexciserate').removeAttr('disabled');
	    	$('#addexciserule').removeAttr('disabled');
	    	$('#addexcisetax').removeAttr('disabled');
	    	$('#addpack').removeAttr('disabled');
			$('#addstick').removeAttr('disabled');
			$('#addexciseunit').removeAttr('disabled');
			$('#addexcisecurrency').removeAttr('disabled');
			$('#addexciseratename').removeAttr('disabled');
	    	//window.location.reload(false);//reload page from the browser's cache AS opposed to  SERVER reload
	    }
		
	});	
	
	/**
	 * @desc Change some elements on the products form depending on the choice of execise flag
	 */	
	$("#editexciseflag").change(function(e) {
		var $option = $(this).find('option:selected');

		var value = $option.val();//to get content of "value" attrib
	    var text = $option.text();//to get <option>Text</option> content
	    
	    //console.log(text + ' has been selected...');
	    //console.log('the value is ' + value);
	    
	    if(value == '2'){//2 is No
	    	//console.log('Disabling elements...');
	    	$('#editexciserate').attr('disabled', 'disabled');
	    	$('#editexciserule').attr('disabled', 'disabled');
	    	$('#editexcisetax').attr('disabled', 'disabled');
	    	$('#editpack').attr('disabled', 'disabled');
			$('#editstick').attr('disabled', 'disabled');
			$('#editexciseunit').attr('disabled', 'disabled');
			$('#editexcisecurrency').attr('disabled', 'disabled');
			$('#editexciseratename').attr('disabled', 'disabled');
	    } else {	    	
	    	//console.log('Refreshing page...');
	    	$('#editexciserate').removeAttr('disabled');
	    	$('#editexciserule').removeAttr('disabled');
	    	$('#editexcisetax').removeAttr('disabled');
	    	$('#editpack').removeAttr('disabled');
			$('#editstick').removeAttr('disabled');
			$('#editexciseunit').removeAttr('disabled');
			$('#editexcisecurrency').removeAttr('disabled');
			$('#editexciseratename').removeAttr('disabled');
	    	//window.location.reload(false);//reload page from the browser's cache AS opposed to  SERVER reload
	    }
		
	});		
	
	/**
	 * @desc Change some elements on the products form depending on the choice of deemed
	 */	
	$("#adddeemedflag").change(function(e) {
		var $option = $(this).find('option:selected');

		var value = $option.val();//to get content of "value" attrib
	    var text = $option.text();//to get <option>Text</option> content
	    
	    
	    if(value == '1'){//2 is No
	    	//console.log('Disabling elements...');
			$('#addtaxrate').val("4").change();
			//$('#addtaxrate').attr('disabled', 'disabled');
	    } else {	    	
	    	//console.log('Refreshing page...');
			$('#addtaxrate').val("").change();
			//$('#addtaxrate').removeAttr('disabled');
	    	//window.location.reload(false);//reload page from the browser's cache AS opposed to  SERVER reload
	    }
	});	
	
	/**
	 * @desc Change some elements on the products form depending on the choice of deemed
	 */	
	$("#editdeemedflag").change(function(e) {
		var $option = $(this).find('option:selected');

		var value = $option.val();//to get content of "value" attrib
	    var text = $option.text();//to get <option>Text</option> content
	    
	    
	    if(value == '1'){//2 is No
	    	//console.log('Disabling elements...');
			$('#edittaxrate').val("4").change();
			//$('#edittaxrate').attr('disabled', 'disabled');
	    } else {	    	
	    	//console.log('Refreshing page...');
			$('#edittaxrate').val("").change();
			//$('#edittaxrate').removeAttr('disabled');
	    	//window.location.reload(false);//reload page from the browser's cache AS opposed to  SERVER reload
	    }
	});		
	

	/**
	 * @desc Change some elements on the products form depending on the choice of discount flag
	 */	
	$("#adddiscountflag").change(function(e) {
		var $option = $(this).find('option:selected');

		var value = $option.val();//to get content of "value" attrib
	    var text = $option.text();//to get <option>Text</option> content
	    
	    
	    if(value == '2'){//2 is No
	    	//console.log('Disabling elements...');
	    	$('#adddiscountpercentage').attr('disabled', 'disabled');
	    } else {	    	
	    	//console.log('Refreshing page...');
	    	$('#adddiscountpercentage').removeAttr('disabled');
	    	//window.location.reload(false);//reload page from the browser's cache AS opposed to  SERVER reload
	    }
	});	
	
	/**
	 * @desc Change some elements on the products form depending on the choice of discount flag
	 */	
	$("#editdiscountflag").change(function(e) {
		var $option = $(this).find('option:selected');

		var value = $option.val();//to get content of "value" attrib
	    var text = $option.text();//to get <option>Text</option> content
	    
	    
	    if(value == '2'){//2 is No
	    	$('#editdiscountpercentage').attr('disabled', 'disabled');
	    } else {	    	
	    	$('#editdiscountpercentage').removeAttr('disabled');
	    }
	});		
	
	/**
	 * @desc Fetch a list of goods asyncronously and populate a datatable
	 */
	window.listgoods = function(groupid){
		$.ajax({
			url: '../etaxware/listgoods',
			data: {
				//insert parameters here
				'groupid': groupid
			},
			method: 'POST',
			success: function(data){

				console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "Item", "data": "Item"},
					{"title": "Item Code", "data": "Item Code"},
					{"title": "Qty", "data": "Qty"},
					{"title": "Unit Price", "data": "Unit Price"},
					{"title": "Total", "data": "Total"},
					{"title": "Tax Rate", "data": "Tax Rate"},
					{"title": "Tax", "data": "Tax"},
					{"title": "Discount Total", "data": "Discount Total"},
					{"title": "Discount Tax Rate", "data": "Discount Tax Rate"},					
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href='' title='Edit' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Item Code'] + "\" data-discountflag=\"" + row['Discount Flag'] + "\" data-deemedflag=\"" + row['Deemed Flag'] + "\" data-exciseflag=\"" + row['Excise Flag'] + "\" data-item=\"" + row['Item'] + "\" data-qty=\"" + row['Qty'] + "\" data-unitprice=\"" + row['Unit Price'] + "\" data-taxid=\"" + row['Tax Id'] + "\" data-discountpercentage=\"" + row['Discount Percentage'] + "\" data-exciserate=\"" + row['Excise Rate'] + "\" data-exciserule=\"" + row['Excise Rule'] + "\" data-excisetax=\"" + row['Excise Tax'] + "\" data-pack=\"" + row['Pack'] + "\" data-stick=\"" + row['Stick'] + "\" data-exciseunit=\"" + row['Excise Unit'] + "\" data-excisecurrency=\"" + row['Excise Currency'] + "\" data-exciseratename=\"" + row['Excise Rate Name'] + "\" data-groupid=\"" + row['Group Id'] + "\" data-target='#modal-edit-good'><i class='fa fa-edit'></i></a> | <a class='text-red' href='' title='Delete' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Item Code'] + "\" data-target='#modal-delete-good'><i class='fa fa-remove'></i></a>";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Group Id": d[i]['Group Id'],   
						"Item": d[i]['Item'],
						"Item Code": d[i]['Item Code'],
						"Qty": d[i]['Qty'],
 						"Unit Of Measure": d[i]['Unit Of Measure'],
						"Unit Price": d[i]['Unit Price'],
						"Total": d[i]['Total'],
						"Tax Id": d[i]['Tax Id'],
						"Tax Rate": d[i]['Tax Rate'],
						"Tax": d[i]['Tax'],
						"Discount Total": d[i]['Discount Total'],
						"Discount Tax Rate": d[i]['Discount Tax Rate'],
						"Discount Percentage": d[i]['Discount Percentage'],
						"Order Number": d[i]['Order Number'],
						"Discount Flag": d[i]['Discount Flag'],
						"Deemed Flag": d[i]['Deemed Flag'],
						"Excise Flag": d[i]['Excise Flag'],
						"Category Id": d[i]['Category Id'],
						"Category Name": d[i]['Category Name'],
						"Goods Category Id": d[i]['Goods Category Id'],
						"Goods Category Name": d[i]['Goods Category Name'],
						"Excise Rate": d[i]['Excise Rate'],
						"Excise Rule": d[i]['Excise Rule'],
						"Excise Tax": d[i]['Excise Tax'],
						"Pack": d[i]['Pack'],
						"Stick": d[i]['Stick'],
						"Excise Unit": d[i]['Excise Unit'],
						"Excise Currency": d[i]['Excise Currency'],
						"Excise Rate Name": d[i]['Excise Rate Name'],				
						"Disabled": d[i]['Disabled'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-invoice-goods').remove(); 
				
				$('#tbl-invoice-goods').DataTable().destroy(); // destory existing datatable
				$('#tbl-invoice-goods').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-invoice-goods').DataTable({
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
	};//listgoods	
	
	/**
	 * @desc Fetch a list of taxes asyncronously and populate a datatable
	 */
	window.listtaxes = function(groupid){
		$.ajax({
			url: '../etaxware/listtaxes',
			data: {
				//insert parameters here
				'groupid': groupid
			},
			method: 'POST',
			success: function(data){

				console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "Tax Category", "data": "Tax Category"},
					{"title": "Net Amount", "data": "Net Amount"},
					{"title": "Tax Rate", "data": "Tax Rate"},
					{"title": "Tax Amount", "data": "Tax Amount"},
					{"title": "Gross Amount", "data": "Gross Amount"},
					{"title": "Excise Unit", "data": "Excise Unit"},
					{"title": "Excise Currency", "data": "Excise Currency"},
					{"title": "Tax Rate Name", "data": "Tax Rate Name"},					
					{"title": "Modified Date", "data": "Modified Date"}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Group Id": d[i]['Group Id'],   
						"Goop Id": d[i]['Good Id'],
						"Tax Category": d[i]['Tax Category'],
 						"Net Amount": d[i]['Net Amount'],
						"Tax Rate": d[i]['Tax Rate'],
						"Tax Amount": d[i]['Tax Amount'],
 						"Gross Amount": d[i]['Gross Amount'],
						"Excise Unit": d[i]['Excise Unit'],
						"Excise Currency": d[i]['Excise Currency'],
						"Tax Rate Name": d[i]['Tax Rate Name'],				
						"Disabled": d[i]['Disabled'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-invoice-taxes').remove(); 
				
				$('#tbl-invoice-taxes').DataTable().destroy(); // destory existing datatable
				$('#tbl-invoice-taxes').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-invoice-taxes').DataTable({
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
	};//listtaxes
	
	
	/**
	 * @desc Fetch a list of payments asyncronously and populate a datatable
	 */
	window.listpayments = function(groupid){
		$.ajax({
			url: '../etaxware/listpayments',
			data: {
				//insert parameters here
				'groupid': groupid
			},
			method: 'POST',
			success: function(data){

				console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "Payment Mode", "data": "Payment Mode"},
					{"title": "Payment Mode Name", "data": "Payment Mode Name"},
					{"title": "Payment Amount", "data": "Payment Amount"},									
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href='' title='Delete Item' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-mode=\"" + row['Payment Mode Name'] + "\" data-target='#modal-delete-payment'><i class='fa fa-remove'></i></a> ";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Group Id": d[i]['Group Id'],   
						"Payment Mode": d[i]['Payment Mode'],
						"Payment Mode Name": d[i]['Payment Mode Name'],
						"Payment Amount": d[i]['Payment Amount'],
						"Disabled": d[i]['Disabled'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-invoice-payments').remove(); 
				
				$('#tbl-invoice-payments').DataTable().destroy(); // destory existing datatable
				$('#tbl-invoice-payments').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-invoice-payments').DataTable({
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
	};//listpayments		
		
	/**
	 * @desc Fetch a list of invoices asyncronously and populate a datatable
	 */
	window.listinvoices = function(){
		var columns = [
			{"title": "ID", "data": "ID"},
			{"title": "EFRIS Id", "data": "Id"},
			{"title": "EFRIS Number", "data": "Number"},
			{"title": "ERP Invoice Id", "data": "ERP Invoice Id"},
			{"title": "ERP Invoice No", "data": "ERP Invoice No"},
			{"title": "Issued Date", "data": "Issued Date"},
			{"title": "Currency", "data": "Currency"},
			{"title": "Net Amount", "data": "Net Amount"},
			{"title": "Tax Amount", "data": "Tax Amount"},
			{"title": "Gross Amount", "data": "Gross Amount"},
			{"title": "Item Count", "data": "Item Count"},
			{"title": "Modified Date", "data": "Modified Date"},
			{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewinvoice\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a> | <a href='' title='Upload' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-invoice-upload'><i class='fa fa-upload'></i></a> | <a href='' title='Download' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-invoice-download'><i class='fa fa-download'></i></a> | <a href=\"" + "../../etaxware/pviewinvoice\\" + row['ID'] + "\" title='Print' id=''><i class='fa fa-print'></i></a> | <a href='' title='ERP Download' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-invoice-erp-download'><i class='fa fa-cloud-download'></i></a> | <a href='' title='ERP Update' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-invoice-erp-update'><i class='fa fa-cloud-upload'></i></a>";}}
		];

		$('#otbl-invoice-list').remove();

		if ($.fn.dataTable.isDataTable('#tbl-invoice-list')) {
			$('#tbl-invoice-list').DataTable().destroy();
			$('#tbl-invoice-list').empty();
		}

		$('#tbl-invoice-list').DataTable({
			"dom": "Bfrtip",
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": '../etaxware/listinvoices',
				"type": 'POST',
				"dataSrc": 'data',
				"error": function(){
					$('#otbl-invoice-list').remove();
				}
			},
			"columns": columns,
		    "paging": true,
		    "lengthChange": true,
		    "searching": true,
		    "ordering": true,
		    "info": true,
		    "scrollX": true,
		    "autoWidth": true,
		    "destroy": true
		});

		$('#otbl-invoice-list').remove();
	};//listinvoices
	
	/**
     * @desc Populate a modal called modal-invoice-upload
     */
    
	$("#modal-invoice-upload").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
		
		$('#uploadinvoiceid').val(id);		
		$('#uploadinvoiceid_2').val(id);
	});//modal-invoice-upload 	
	
	/**
     * @desc Populate a modal called modal-invoice-download
     */
    
	$("#modal-invoice-download").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		
		$('#downloadinvoiceid').val(id);		
		$('#downloadinvoiceid_2').val(id);
	});//modal-invoice-download 			
	
	/**
     * @desc Populate a modal called modal-delete-good
     */
    
	$("#modal-delete-good").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		var code = tlink.data("code");
				
		$('#deletegoodid').val(id);
		$('#deleteitemcode').val(code);
	});//modal-delete-good	
	
	/**
     * @desc Populate a modal called modal-edit-good
     */
    
	$("#modal-edit-good").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		var code = tlink.data("code");
		var item = tlink.data("item");
		var discountflag = tlink.data("discountflag");
		var deemedflag = tlink.data("deemedflag");
		var exciseflag = tlink.data("exciseflag");
		var qty = tlink.data("qty");
		var unitprice = tlink.data("unitprice");
		var taxid = tlink.data("taxid");
		var discountpercentage = tlink.data("discountpercentage");
		var exciserate = tlink.data("exciserate");
		var exciserule = tlink.data("exciserule");
		var excisetax = tlink.data("excisetax");
		var pack = tlink.data("pack");
		var stick = tlink.data("stick");
		var exciseunit = tlink.data("exciseunit");
		var excisecurrency = tlink.data("excisecurrency");
		var exciseratename = tlink.data("exciseratename");
		var groupid = tlink.data("groupid");
				
		$('#editgoodid').val(id);
		$('#editdiscountflag').val(discountflag);
		$('#editdeemedflag').val(deemedflag);
		$('#editexciseflag').val(exciseflag);
		$('#edititem').empty();
		$('#edititem').append($('<option/>').val(code).text((code || '') + ((item && item !== '') ? (' - ' + item) : '')));
		$('#edititem').val(code).trigger('change');
		$('#editqty').val(qty);
		$('#editunitprice').val(unitprice);
		$('#edittaxrate').val(taxid);
		$('#editdiscountpercentage').val(discountpercentage);
		$('#editexciserate').val(exciserate);
		$('#editexciserule').val(exciserule);
		$('#editexcisetax').val(excisetax);
		$('#editpack').val(pack);
		$('#editstick').val(stick);
		$('#editexciseunit').val(exciseunit);
		$('#editexcisecurrency').val(excisecurrency);
		$('#editexciseratename').val(exciseratename);
		$('#editgoodgroupid').val(groupid);
		
		if(discountflag == '2'){//2 is No
	    	$('#editdiscountpercentage').attr('disabled', 'disabled');
	    } else {	    	
	    	$('#editdiscountpercentage').removeAttr('disabled');
	    }
		
		
	    if(exciseflag == '2'){//2 is No
	    	$('#editexciserate').attr('disabled', 'disabled');
	    	$('#editexciserule').attr('disabled', 'disabled');
	    	$('#editexcisetax').attr('disabled', 'disabled');
	    	$('#editpack').attr('disabled', 'disabled');
			$('#editstick').attr('disabled', 'disabled');
			$('#editexciseunit').attr('disabled', 'disabled');
			$('#editexcisecurrency').attr('disabled', 'disabled');
			$('#editexciseratename').attr('disabled', 'disabled');
	    } else {	    	
	    	$('#editexciserate').removeAttr('disabled');
	    	$('#editexciserule').removeAttr('disabled');
	    	$('#editexcisetax').removeAttr('disabled');
	    	$('#editpack').removeAttr('disabled');
			$('#editstick').removeAttr('disabled');
			$('#editexciseunit').removeAttr('disabled');
			$('#editexcisecurrency').removeAttr('disabled');
			$('#editexciseratename').removeAttr('disabled');
	    }
		
	});//modal-edit-good	
		/**
     * @desc Populate a modal called modal-delete-payment
     */
    
	$("#modal-delete-payment").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		var mode = tlink.data("mode");
				
		$('#deletepaymentid').val(id);
		$('#deletepaymentmode').val(mode);
	});//modal-delete-good	
	
	$("#modal-invoice-erp-download").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		
		$('#erpdownloadinvoiceid').val(id);		
		$('#erpdownloadinvoiceid_2').val(id);
	});//modal-invoice-erp-download		
	
	$("#modal-invoice-erp-update").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		
		$('#erpupdateinvoiceid').val(id);		
		$('#erpupdateinvoiceid_2').val(id);
	});//modal-invoice-erp-update							
});