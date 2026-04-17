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
	// 2026-04-12: exclude modal goods item controls from generic init.
	// They are initialized with modal-specific dropdownParent so keyboard typing
	// works consistently in Add/Edit Good modals (Chrome/manual use).
	$(".select2").not('#additem, #edititem').select2();	
	
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
	 * @desc populate the delivery terms search on the invoice buyer screen
	 */
	$('#deliveryTermsCode').select2({
		placeholder : "Search Delivery Terms...",
		minimumInputLength : 1,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchdeliveryterms",
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

	// 2026-04-12: keep visible excise confirmation in sync with selected item.
	var setDerivedExciseFlag = function(flagSelector, selectedItem) {
		var hasExciseTax = String((selectedItem && selectedItem.hasExciseTax) || '');
		var derivedFlag = (hasExciseTax === '101') ? '1' : '2';
		$(flagSelector).val(derivedFlag);
	};

	// 2026-04-12: parse human-readable excise rate text (e.g. "2.50%,USD70 per TNE...")
	// and map values into visible, disabled confirmation fields.
	var parseExciseRateText = function(rateText) {
		var text = $.trim(rateText || '');
		var parsed = {
			ratePercent: '',
			currency: '',
			taxValue: '',
			ruleText: text
		};

		if (text === '') {
			return parsed;
		}

		var pctMatch = text.match(/([0-9]+(?:\.[0-9]+)?)\s*%/);
		if (pctMatch && pctMatch[1]) {
			parsed.ratePercent = pctMatch[1];
		}

		var currencyMatch = text.match(/,\s*([A-Z]{3})\s*([0-9]+(?:\.[0-9]+)?)/);
		if (currencyMatch) {
			parsed.currency = currencyMatch[1] || '';
			parsed.taxValue = currencyMatch[2] || '';
		}

		return parsed;
	};

	var setDerivedExciseFields = function(prefix, selectedItem) {
		var hasExciseTax = String((selectedItem && selectedItem.hasExciseTax) || '');
		var exciseDutyName = $.trim((selectedItem && selectedItem.exciseDutyName) || '');
		var exciseRateText = $.trim((selectedItem && selectedItem.exciseRate) || '');
		var packValue = (selectedItem && selectedItem.pack != null) ? selectedItem.pack : '';
		var stickValue = (selectedItem && selectedItem.stick != null) ? selectedItem.stick : '';
		var parsedRate = parseExciseRateText(exciseRateText);

		if (hasExciseTax !== '101') {
			$(prefix + 'exciserate').val('');
			$(prefix + 'exciserule').val('');
			$(prefix + 'excisetax').val('');
			$(prefix + 'pack').val('');
			$(prefix + 'stick').val('');
			$(prefix + 'exciseunit').val('');
			$(prefix + 'excisecurrency').val('');
			$(prefix + 'exciseratename').val('');
			return;
		}

		$(prefix + 'exciserate').val(parsedRate.ratePercent);
		$(prefix + 'exciserule').val(parsedRate.ruleText);
		$(prefix + 'excisetax').val(parsedRate.taxValue);
		$(prefix + 'pack').val(packValue);
		$(prefix + 'stick').val(stickValue);
		$(prefix + 'exciseunit').val('');
		$(prefix + 'excisecurrency').val(parsedRate.currency);
		$(prefix + 'exciseratename').val(exciseDutyName);
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

	var requestExcisePreview = function(prefix, selectedItem) {
		var itemCode = $(prefix + 'item').val();
		var qty = $.trim($(prefix + 'qty').val() || '');
		var unitPrice = $.trim($(prefix + 'unitprice').val() || '');
		var itemMeta = selectedItem || {};

		if (!itemCode) {
			setDerivedExciseFields(prefix, {});
			$(prefix + 'exciseflag').val('');
			return;
		}

		// Keep the immediate visible fallback before backend response returns.
		setDerivedExciseFlag(prefix + 'exciseflag', itemMeta);
		setDerivedExciseFields(prefix, itemMeta);

		$.ajax({
			type: 'POST',
			url: '../etaxware/previewinvoiceexcise',
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

	/**
	 * @desc populate product search on invoice add good modal
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
							exciseDutyName : item.ExciseDutyName,
							exciseRate : item.ExciseRate,
							pack : item.Pack,
							stick : item.Stick,
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
		setDerivedExciseFlag('#addexciseflag', e.params.data || {});
		setDerivedExciseFields('#add', e.params.data || {});
		requestExcisePreview('#add', e.params.data || {});
	});
	$('#additem').on('select2:clear', function() {
		$('#addexciseflag').val('');
		setDerivedExciseFields('#add', {});
	});
	$('#addqty, #addunitprice').on('input change blur', function() {
		requestExcisePreview('#add');
	});

	// 2026-04-12: prevent silent Add Good failures by enforcing required fields
	// before submit, especially select2-backed item and required selects.
	$(document).on('submit', '#modal-add-good form', function(e) {
		var missing = [];
		var itemCode = $.trim($('#additem').val() || '');
		var qty = $.trim($('#addqty').val() || '');
		var unitPrice = $.trim($('#addunitprice').val() || '');
		var discountFlag = $.trim($('#adddiscountflag').val() || '');
		var deemedFlag = $.trim($('#adddeemedflag').val() || '');
		var taxRate = $.trim($('#addtaxrate').val() || '');

		if (!itemCode) {
			missing.push('Item');
		}
		if (!qty) {
			missing.push('Qty');
		}
		if (!unitPrice) {
			missing.push('Unit Price');
		}
		if (!discountFlag) {
			missing.push('Discount Flag');
		}
		if (!deemedFlag) {
			missing.push('Deemed Flag');
		}
		if (!taxRate) {
			missing.push('Tax Rate');
		}

		if (missing.length > 0) {
			e.preventDefault();
			alert('Please complete required field(s): ' + missing.join(', '));

			if (missing[0] === 'Item') {
				$('#additem').select2('open');
			} else if (missing[0] === 'Qty') {
				$('#addqty').focus();
			} else if (missing[0] === 'Unit Price') {
				$('#addunitprice').focus();
			} else if (missing[0] === 'Discount Flag') {
				$('#adddiscountflag').focus();
			} else if (missing[0] === 'Deemed Flag') {
				$('#adddeemedflag').focus();
			} else if (missing[0] === 'Tax Rate') {
				$('#addtaxrate').focus();
			}

			return false;
		}

		return true;
	});

	/**
	 * @desc populate product search on invoice edit good modal
	 */
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
							exciseDutyName : item.ExciseDutyName,
							exciseRate : item.ExciseRate,
							pack : item.Pack,
							stick : item.Stick,
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
		setDerivedExciseFlag('#editexciseflag', e.params.data || {});
		setDerivedExciseFields('#edit', e.params.data || {});
		requestExcisePreview('#edit', e.params.data || {});
	});
	$('#edititem').on('select2:clear', function() {
		$('#editexciseflag').val('');
		setDerivedExciseFields('#edit', {});
	});
	$('#editqty, #editunitprice').on('input change blur', function() {
		requestExcisePreview('#edit');
	});
	
	/**
	 * @desc validate a TIN
	 */
	$('#buyertin').blur(function() {
        var tin = $(this).val();
		var buyerType = String($('#buyertype').val() || '');
		// 2026-04-12: TIN is required only for B2B buyers (type 0).
		var tinRequired = (buyerType === '0');

		if (tin.length === 0 && !tinRequired) {
			$('#tin-val-text').text('');
			return;
		}
		
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
			$('#tin-val-text').text(tinRequired ? 'TIN is required and must be exactly 10 characters.' : 'When provided, TIN must be exactly 10 characters.');
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

	// 2026-04-12: excise fields are derived from selected item; keep UI inputs read-only.
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

	lockExciseInputs('#add');
	lockExciseInputs('#edit');
	
	/**
	 * @desc Change some elements on the products form depending on the choice of execise flag
	 */	
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