/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the creditnote module
 * @date: 06-09-2020
 * @file: creditnote.js
 * @path: ./app/public/js/creditnote.js
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
$(function(){
	'use strict';
	
	/**
	 * @desc initialise select2 dropdowns
	 */
	// 2026-04-12: exclude modal goods item controls from generic init.
	// They are initialized with modal-specific dropdownParent for stable typing/focus in Bootstrap modals.
	$(".select2").not('#additem, #edititem').select2();		
	
	/**
	 * @desc Create a datatable called tbl-creditnote-list
	 */
	$('#tbl-creditnote-list').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-creditnote-list
	
	/**
	 * @desc Create a datatable called tbl-creditnote-taxes
	 */
	$('#tbl-creditnote-taxes').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-creditnote-taxes	
	
		/**
	 * @desc Create a datatable called tbl-creditnote-payments
	 */
	$('#tbl-creditnote-payments').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-creditnote-payments	
	
	/**
	 * @desc Create a datatable called tbl-creditnote-goods
	 */
	$('#tbl-creditnote-goods').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-creditnote-goods	
	
	/**
	 * @desc Change some elements on the products form depending on the choice of execise flag
	 */	
	var lockExciseInputs = function(prefix) {
		$(prefix + 'exciseflag').attr('disabled', 'disabled');
		$(prefix + 'exciserate').attr('disabled', 'disabled');
		$(prefix + 'exciserule').attr('disabled', 'disabled');
		$(prefix + 'excisetax').attr('disabled', 'disabled');
		$(prefix + 'pack').attr('disabled', 'disabled');
		$(prefix + 'stick').attr('disabled', 'disabled');
		$(prefix + 'exciseunit').attr('disabled', 'disabled');
		$(prefix + 'excisecurrency').attr('disabled', 'disabled');
		$(prefix + 'exciseratename').attr('disabled', 'disabled');
	};

	var clearExciseFields = function(prefix) {
		$(prefix + 'exciseflag').val('2');
		$(prefix + 'exciserate').val('');
		$(prefix + 'exciserule').val('');
		$(prefix + 'excisetax').val('');
		$(prefix + 'pack').val('');
		$(prefix + 'stick').val('');
		$(prefix + 'exciseunit').val('');
		$(prefix + 'excisecurrency').val('');
		$(prefix + 'exciseratename').val('');
	};

	var setComputedExciseFields = function(prefix, computed) {
		var ruleLabels = {
			1: 'Calculated by tax rate',
			2: 'Calculated by Quantity',
			3: 'Nil tax rate'
		};
		var ruleCode = Number((computed && computed.exciserule) || 0);
		var ruleLabel = ruleLabels[ruleCode] || '';

		$(prefix + 'exciseflag').val((computed && computed.exciseflag) ? String(computed.exciseflag) : '2');
		$(prefix + 'exciserate').val((computed && computed.exciserate) != null ? computed.exciserate : '');
		$(prefix + 'exciserule').val(ruleLabel);
		$(prefix + 'excisetax').val((computed && computed.excisetax) != null ? computed.excisetax : '');
		$(prefix + 'pack').val((computed && computed.pack) != null ? computed.pack : '');
		$(prefix + 'stick').val((computed && computed.stick) != null ? computed.stick : '');
		$(prefix + 'exciseunit').val((computed && computed.exciseunit) != null ? computed.exciseunit : '');
		$(prefix + 'excisecurrency').val((computed && computed.excisecurrency) != null ? computed.excisecurrency : '');
		$(prefix + 'exciseratename').val((computed && computed.exciseratename) != null ? computed.exciseratename : '');
	};

	// 2026-04-12: mirror invoice UX by previewing computed excise from backend on item/qty/unit changes.
	var requestExcisePreview = function(prefix, selectedItem) {
		var itemCode = $(prefix + 'item').val();
		var qty = $.trim($(prefix + 'qty').val() || '');
		var unitPrice = $.trim($(prefix + 'unitprice').val() || '');
		var hasExciseTax = String((selectedItem && selectedItem.hasExciseTax) || '');

		if (!itemCode) {
			clearExciseFields(prefix);
			return;
		}

		$(prefix + 'exciseflag').val(hasExciseTax === '101' ? '1' : '2');

		$.ajax({
			type: 'POST',
			url: '../etaxware/previewcreditnoteexcise',
			dataType: 'json',
			cache: false,
			data: {
				itemcode: itemCode,
				qty: qty,
				unitprice: unitPrice
			},
			success: function(resp) {
				if (resp && resp.ok) {
					setComputedExciseFields(prefix, resp);
				}
			}
		});
	};

	lockExciseInputs('#add');
	lockExciseInputs('#edit');

	$("#addexciseflag").change(function(e) {
		lockExciseInputs('#add');
	});	


	/**
	 * @desc Change some elements on the products form depending on the choice of execise flag
	 */	
	$("#editexciseflag").change(function(e) {
		lockExciseInputs('#edit');
	});	
	
	/**
	 * @desc Change some elements on the products form depending on the choice of reason
	 */	
	$("#reasoncode").change(function(e) {
		var $option = $(this).find('option:selected');

		var value = $option.val();//to get content of "value" attrib
	    var text = $option.text();//to get <option>Text</option> content
	    
	    //console.log(text + ' has been selected...');
	    //console.log('the value is ' + value);
	    
	    if(value !== '105'){//105 is Other
	    	//console.log('Disabling elements...');
	    	$('#reason').attr('disabled', 'disabled');
	    } else {	    	
	    	//console.log('Refreshing page...');
	    	$('#reason').removeAttr('disabled');
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
	 * @desc populate the invoice search on the credit/debit note screen
	 */	
	$('#searchinvoice').select2({
		placeholder : "Search Invoice...",
		minimumInputLength : 4,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/listinvoices",
			dataType : 'json',
			delay : 250,
			cache : false,
			data : function(params) {
				return {
					number : params.term, // search term
					page : params.page, // page
				};
			},
			processResults : function(data) {
				return {
					results : $.map(data, function(item) {
						return {
							text : item.Number,
							id : item.Id,
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
	 * @desc populate the currency search on the creditnote screen
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
			}
		}
	});

	/**
	 * @desc populate the reason code search on the creditnote screen
	 */
	$('#reasoncode').select2({
		placeholder : "Search Reason Code...",
		minimumInputLength : 1,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchcdnotereasoncodes",
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
	 * @desc populate the data source search on the creditnote screen
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
	 * @desc populate product search on credit note add/edit good modals
	 */
	$('#additem').select2({
		dropdownParent : $('#modal-add-good'),
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
							weight : item.Weight || item.weight,
							hasExciseTax : item.HasExciseTax,
							disabled: Number(item.Disabled)
						};
					})
				};
			}
		}
	});

	$('#additem').on('select2:select', function(e) {
		if ($('#addweight').val() === '' && e.params.data && (e.params.data.weight || e.params.data.weight === 0)) {
			$('#addweight').val(e.params.data.weight);
		}
		requestExcisePreview('#add', e.params.data || {});
	});

	$('#additem').on('select2:clear', function() {
		clearExciseFields('#add');
	});

	$('#edititem').select2({
		dropdownParent : $('#modal-edit-good'),
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
							hasExciseTax : item.HasExciseTax,
							disabled: Number(item.Disabled)
						};
					})
				};
			}
		}
	});

	$('#edititem').on('select2:select', function(e) {
		if ($('#editweight').val() === '' && e.params.data && (e.params.data.weight || e.params.data.weight === 0)) {
			$('#editweight').val(e.params.data.weight);
		}
		requestExcisePreview('#edit', e.params.data || {});
	});

	$('#edititem').on('select2:clear', function() {
		clearExciseFields('#edit');
	});

	$('#addqty, #addunitprice').on('input change blur', function() {
		requestExcisePreview('#add');
	});

	$('#editqty, #editunitprice').on('input change blur', function() {
		requestExcisePreview('#edit');
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
				var d = data;
				if (typeof data === 'string') {
					var trimmed = $.trim(data);
					if (trimmed.charAt(0) === '<') {
						console.warn('listgoods returned HTML instead of JSON.');
						return;
					}
					try {
						d = JSON.parse(trimmed);
					} catch (e) {
						console.error('Unable to parse goods list JSON payload.', e);
						return;
					}
				}

				if (!$.isArray(d)) {
					console.warn('listgoods payload format is unexpected.', d);
					return;
				}
				
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
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href='' title='Edit' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Item Code'] + "\" data-discountflag=\"" + row['Discount Flag'] + "\" data-deemedflag=\"" + row['Deemed Flag'] + "\" data-exciseflag=\"" + row['Excise Flag'] + "\" data-item=\"" + row['Item'] + "\" data-qty=\"" + row['Qty'] + "\" data-unitprice=\"" + row['Unit Price'] + "\" data-taxid=\"" + row['Tax Id'] + "\" data-discountpercentage=\"" + row['Discount Percentage'] + "\" data-exciserate=\"" + row['Excise Rate'] + "\" data-exciserule=\"" + row['Excise Rule'] + "\" data-excisetax=\"" + row['Excise Tax'] + "\" data-pack=\"" + row['Pack'] + "\" data-stick=\"" + row['Stick'] + "\" data-exciseunit=\"" + row['Excise Unit'] + "\" data-excisecurrency=\"" + row['Excise Currency'] + "\" data-exciseratename=\"" + row['Excise Rate Name'] + "\" data-totalweight=\"" + row['Total Weight'] + "\" data-groupid=\"" + row['Group Id'] + "\" data-target='#modal-edit-good'><i class='fa fa-edit'></i></a> | <a class='text-red' href='' title='Delete' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Item Code'] + "\" data-target='#modal-delete-good'><i class='fa fa-remove'></i></a>";}}
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
						"Total Weight": d[i]['Total Weight'],
						"Disabled": d[i]['Disabled'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-creditnote-goods').remove(); 
				
				$('#tbl-creditnote-goods').DataTable().destroy(); // destory existing datatable
				$('#tbl-creditnote-goods').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-creditnote-goods').DataTable({
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
						"Good Id": d[i]['Good Id'],
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
				$('#otbl-creditnote-taxes').remove(); 
				
				$('#tbl-creditnote-taxes').DataTable().destroy(); // destory existing datatable
				$('#tbl-creditnote-taxes').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-creditnote-taxes').DataTable({
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
					{"title": "Order Number", "data": "Order Number"},	
					{"title": "Payment Mode", "data": "Payment Mode"},
					{"title": "Payment Amount", "data": "Payment Amount"},									
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href='' title='Delete Item' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-order=\"" + row['Order Number'] + "\" data-target='#modal-delete-payment'><i class='fa fa-remove'></i></a> ";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Group Id": d[i]['Group Id'],   
						"Payment Mode": d[i]['Payment Mode'],
						"Payment Amount": d[i]['Payment Amount'],
						"Order Number": d[i]['Order Number'],				
						"Disabled": d[i]['Disabled'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-creditnote-payments').remove(); 
				
				$('#tbl-creditnote-payments').DataTable().destroy(); // destory existing datatable
				$('#tbl-creditnote-payments').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-creditnote-payments').DataTable({
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
	 * @desc Fetch a list of creditnotes asyncronously and populate a datatable
	 */
	window.listcreditnotes = function(){
		var columns = [
			{"title": "ID", "data": "ID"},
			{"title": "Original Invoice Id", "data": "Original Invoice Id"},
			{"title": "Original Invoice No", "data": "Original Invoice No"},
			{"title": "Currency", "data": "Currency"},
			{"title": "Credit Note No", "data": "Refund Inv No"},
			{"title": "Reference No", "data": "Reference No"},
			{"title": "Appl Id", "data": "Appl Id"},
			{"title": "Appl Time", "data": "Appl Time"},
			{"title": "Gross Amount", "data": "Gross Amount"},
			{"title": "Approve Status", "data": "Approve Status Name"},
			{"title": "Modified Date", "data": "Modified Date"},
			{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewcreditnote\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a> | <a class='text-red' href='' title='Cancel/Void' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-creditnote-cancel'><i class='fa fa-remove'></i></a> | <a href='' title='Upload' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-creditnote-upload'><i class='fa fa-upload'></i></a> | <a href='' title='Download' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-creditnote-download'><i class='fa fa-download'></i></a> | <a href=\"" + "../../etaxware/pviewcreditnote\\" + row['ID'] + "\" title='Print' id=''><i class='fa fa-print'></i></a> | <a href='' title='ERP Download' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-creditnote-erp-download'><i class='fa fa-cloud-download'></i></a> | <a href='' title='ERP Update' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-target='#modal-creditnote-erp-update'><i class='fa fa-cloud-upload'></i></a>";}}
		];

		$('#otbl-creditnote-list').remove();

		if ($.fn.dataTable.isDataTable('#tbl-creditnote-list')) {
			$('#tbl-creditnote-list').DataTable().destroy();
			$('#tbl-creditnote-list').empty();
		}

		$('#tbl-creditnote-list').DataTable({
			"dom": "Bfrtip",
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": '../etaxware/listcreditnotes',
				"type": 'POST',
				"dataSrc": 'data',
				"error": function(){
					$('#otbl-creditnote-list').remove();
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

		$('#otbl-creditnote-list').remove();
	};//listcreditnotes
	
	/**
     * @desc Populate a modal called modal-creditnote-upload
     */
    
	$("#modal-creditnote-upload").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
		
		$('#uploadcreditnoteid').val(id);		
		$('#uploadcreditnoteid_2').val(id);
	});//modal-creditnote-upload 		
	
	/**
     * @desc Populate a modal called modal-creditnote-download
     */
    
	$("#modal-creditnote-download").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
		
		$('#downloadcreditnoteid').val(id);		
		$('#downloadcreditnoteid_2').val(id);
	});//modal-creditnote-download 	
		
	/**
     * @desc Populate a modal called modal-creditnote-cancel
     */
    
	$("#modal-creditnote-cancel").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
		
		$('#cancelcreditnoteid').val(id);		
		$('#cancelcreditnoteid_2').val(id);
	});//modal-creditnote-cancel 	
		
		
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
		var totalweight = tlink.data("totalweight");
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
		$('#editweight').val((totalweight === undefined || totalweight === null) ? '' : totalweight);
		$('#editgoodgroupid').val(groupid);
		
		if(discountflag == '2'){//2 is No
	    	$('#editdiscountpercentage').attr('disabled', 'disabled');
	    } else {	    	
	    	$('#editdiscountpercentage').removeAttr('disabled');
	    }
		
		
		lockExciseInputs('#edit');
		requestExcisePreview('#edit', { hasExciseTax: (String(exciseflag) === '1' ? '101' : '102') });
		
	});//modal-edit-good	
		
		/**
     * @desc Populate a modal called modal-delete-payment
     */
    
	$("#modal-delete-payment").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		var order = tlink.data("order");
				
		$('#deletepaymentid').val(id);
		$('#deletepaymentorderno').val(order);
	});//modal-delete-good	
	
	$("#modal-creditnote-erp-download").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		
		$('#erpdownloadcreditnoteid').val(id);		
		$('#erpdownloadcreditnoteid_2').val(id);
	});//modal-creditnote-erp-download	
	
	$("#modal-creditnote-erp-update").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var id = tlink.data("id");
		
		$('#erpupdatecreditnoteid').val(id);		
		$('#erpupdatecreditnoteid_2').val(id);
	});//modal-creditnote-erp-update							
});