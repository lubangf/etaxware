/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the product module
 * @date: 06-09-2020
 * @file: product.js
 * @path: ./app/public/js/product.js
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
	 * @desc Create a datatable called tbl-otherunits
	 */
	$('#tbl-otherunits').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-otherunits	
	
	
	/**
	 * @desc Create a datatable called tbl-stockadjustments
	 */
	$('#tbl-stockadjustments').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-stockadjustments	
	
	/**
	 * @desc Create a datatable called tbl-stocktransfers
	 */
	$('#tbl-stocktransfers').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-stocktransfers			
	
	/**
	 * @desc Fetch a list of other units asyncronously and populate a datatable
	 */
	window.listotherunits = function(){
		$.ajax({
			url: '../../etaxware/listotherunits',
			data: {
				//insert parameters here
				'id' : productid_2
			},
			method: 'POST',
			success: function(data){

				//console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "ID", "data": "ID"},
					{"title" : "Code", "data": "Code"},
					{"title" : "Name", "data": "Name"},
					{"title" : "Other Price", "data": "Other Price"},
					{"title": "Other Scaled", "data": "Other Scaled"},
					{"title": "Package Scale Value", "data": "Package Scale Value"},
					{"title": "Modified By", "data": "Modified By"},
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href='#' title='Edit' id='edit-other-units-1' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-otherunit=\"" + row['Code'] + "\" data-otherscaled=\"" + row['Other Scaled'] + "\" data-packagescaled=\"" + row['Package Scale Value'] + "\" data-otherunitname=\"" + row['Name'] + "\" data-otherprice2=\"" + row['Other Price'] + "\" data-target='#modal-edit-other-units'><i class='fa fa-edit'></i></a> | <a href='' title='Delete' id='delete-other-units-1' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-otherunitname=\"" + row['Name'] + "\" data-target='#modal-delete-other-units'><i class='fa fa-remove'></i></a>";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Code": d[i]['Code'],
						"Name": d[i]['Name'],
						"Other Price": d[i]['Other Price'],
						"Other Scaled": d[i]['Other Scaled'],
						"Package Scale Value": d[i]['Package Scale Value'],
						"Modified By": d[i]['Modified By'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-otherunits').remove(); 
				
				$('#tbl-otherunits').DataTable().destroy(); // destory existing datatable
				$('#tbl-otherunits').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-otherunits').DataTable({
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
	};//listotherunits	
	
	/**
	 * @desc Fetch a list of stock adjustments asyncronously and populate a datatable
	 */
	window.liststockadjustments = function(){
		$.ajax({
			url: '../../etaxware/liststockadjustments',
			data: {
				//insert parameters here
				'id' : productid_2
			},
			method: 'POST',
			success: function(data){

				//console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "ID", "data": "ID"},
					{"title": "Operation Type", "data": "Operation Type Name"},
					{"title": "Supplier Tin", "data": "Supplier Tin"},
					{"title": "Supplier Name", "data": "Supplier Name"},
					{"title": "Adjust Type", "data": "Adjust Type Name"},
					{"title": "Remarks", "data": "Remarks"},
					{"title": "StockIn Date", "data": "StockIn Date"},
					{"title": "StockIn Type", "data": "StockIn Type Name"},
					{"title": "BatchNo", "data": "Production BatchNo"},
					{"title": "Production Date", "data": "Production Date"},
					{"title": "Quantity", "data": "Quantity"},
					{"title": "Unit Price", "data": "Unit Price"},					
					{"title": "Modified By", "data": "Modified By"},
					{"title": "Modified Date", "data": "Modified Date"}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],						
						"Operation Type Name": d[i]['Operation Type Name'],
						"Supplier Tin": d[i]['Supplier Tin'],
						"Supplier Name": d[i]['Supplier Name'],
						"Adjust Type Name": d[i]['Adjust Type Name'],
						"Remarks": d[i]['Remarks'],
						"StockIn Date": d[i]['StockIn Date'],
						"StockIn Type Name": d[i]['StockIn Type Name'],
						"Production BatchNo": d[i]['Production BatchNo'],
						"Production Date": d[i]['Production Date'],
						"Quantity": d[i]['Quantity'],
						"Unit Price": d[i]['Unit Price'],
						"Modified By": d[i]['Modified By'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-stockadjustments').remove(); 
				
				$('#tbl-stockadjustments').DataTable().destroy(); // destory existing datatable
				$('#tbl-stockadjustments').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-stockadjustments').DataTable({
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
	};//liststockadjustments	
	
	/**
	 * @desc Fetch a list of stock transfers asyncronously and populate a datatable
	 */
	window.liststocktransfers = function(){
		$.ajax({
			url: '../../etaxware/liststocktransfers',
			data: {
				//insert parameters here
				'id' : productid_2
			},
			method: 'POST',
			success: function(data){

				//console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "ID", "data": "ID"},
					{"title": "Source Branch", "data": "Source Branch Name"},
					{"title": "Destination Branch", "data": "Destination Branch Name"},
					{"title": "Transfer Type", "data": "TransferType Code Name"},
					{"title": "Remarks", "data": "Remarks"},
					{"title": "Quantity", "data": "Quantity"},
					{"title": "Modified By", "data": "Modified By"},
					{"title": "Modified Date", "data": "Modified Date"}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],						
						"Source Branch Name": d[i]['Source Branch Name'],
						"Destination Branch Name": d[i]['Destination Branch Name'],
						"TransferType Code Name": d[i]['TransferType Code Name'],
						"Remarks": d[i]['Remarks'],
						"Quantity": d[i]['Quantity'],
						"Modified By": d[i]['Modified By'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-stocktransfers').remove(); 
				
				$('#tbl-stocktransfers').DataTable().destroy(); // destory existing datatable
				$('#tbl-stocktransfers').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-stocktransfers').DataTable({
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
	};//liststocktransfers	
	
	$("#modal-delete-other-units").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var otherunitname = tlink.data("otherunitname");
		var id = tlink.data("id");
		
		$('#deleteotherunitsproductmeasureunit').val(otherunitname);
		$('#deleteotherunitsid').val(id);
	});//modal-delete-other-units
	
	$("#modal-edit-other-units").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var otherunit = tlink.data("otherunit");
		var otherunitname = tlink.data("otherunitname");
		var otherscaled = tlink.data("otherscaled");
		var otherPrice = tlink.data("otherprice2");
		var packagescaled = tlink.data("packagescaled");
		var id = tlink.data("id");
		
		//$('#editotherunitsproductmeasureunit').val(otherunit);
		
		////console.log(otherPrice);
		
		// 2026-04-11 19:07:14 +03:00 - Normalize modal values so blank and literal NULL/null are treated as empty for Select2 prefill.
		otherunit = $.trim(otherunit || '');
		otherunitname = $.trim(otherunitname || '');
		if (otherunit.toUpperCase() === 'NULL') {
			otherunit = '';
		}

		// 2026-04-11 19:07:14 +03:00 - Guard Select2 prefill against empty/NULL-string code values and use code fallback for display label.
		if (otherunit !== undefined && otherunit !== '') {//if the unit is already set, then set it as the selected choice
			////console.log(otherunit);
			////console.log(otherunitname);		
			$('#editotherunitsproductmeasureunit').empty();
	        	$('#editotherunitsproductmeasureunit').append($("<option/>").val(otherunit).text(otherunitname !== '' ? otherunitname : otherunit));
        	$('#editotherunitsproductmeasureunit').val(otherunit).trigger('change');
		}
				
		$('#editotherunitsproductpieceunitprice').val(otherPrice);
		$('#editotherunitsproductpackagescaledvalue').val(packagescaled);
		$('#editotherunitsproductpiecescaledvalue').val(otherscaled);
		$('#editotherunitsid').val(id);
	});//modal-edit-other-units	
	
	
	$("#modal-delete-other-units").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var otherunitname = tlink.data("otherunitname");
		var id = tlink.data("id");
		
		$('#deleteotherunitsproductmeasureunit').val(otherunitname);
		$('#deleteotherunitsid').val(id);
	});//modal-delete-other-units
	
	
	
	/**
	 * @desc populate the measureunit search on the product screen
	 */	
	$('#productmeasureunit').select2({
		placeholder : "Search Measure Unit...",
		minimumInputLength : 2,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchmeasureunits",
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
	 * @desc populate the currency search on the product screen
	 */
	// 2026-04-11 19:44:49 +03:00 - Use AJAX Select2 currency search to match commodity category behavior.
	$('#productcurrency').select2({
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
	 * @desc populate the hs code search on the product screen
	 */	
	$('#producthscode').select2({
		placeholder : "Search HS Name...",
		minimumInputLength : 2,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchhscodes",
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
	 * @desc populate the measureunit search on the product screen
	 */	
	$('#addotherunitsproductmeasureunit').select2({
		placeholder : "Search Measure Unit...",
		minimumInputLength : 2,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchmeasureunits",
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
	 * @desc populate the piece measureunit search on the product screen
	 */	
	$('#productpiecemeasureunit').select2({
		placeholder : "Search Measure Unit...",
		minimumInputLength : 2,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchmeasureunits",
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
	 * @desc populate the customs measureunit search on the product screen
	 */	
	$('#productcustomsmeasureunit').select2({
		placeholder : "Search Export Measure Unit...",
		minimumInputLength : 2,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/searchexportmeasureunits",
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
	 * @desc populate the commodity category search on the product screen
	 */	
	$('#productcommoditycategory').select2({
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
	 * @desc validate a TIN
	 */
	$('#stockinsuppliertin').blur(function() {
        var tin = $(this).val();		
		$('#tin-val-text').addClass("help-block text-yellow");
		
		if (tin.length == 10){
			$('#tin-val-text').text('Validating the TIN, please wait...');
			//$(this).attr('disabled', 'disabled');
			
			$.ajax({
				url: '../etaxware/querytaxpayer',
				data: {
					//insert parameters here
					'tin' : tin
				},
				method: 'POST',
				cache: false,
				success: function(data){
					//console.log(data);
					var d = JSON.parse(data);
					////console.log(d['tin']);
	
					if(d['tin']){
						$('#tin-val-text').removeClass("help-block text-yellow");
						$('#tin-val-text').addClass("help-block text-green");
						$('#tin-val-text').text("Validation was completed. The supplier's details will be populated soon.");
						
						$('#stockinsuppliername').val(d['legalName']);
						//$('#stockinsuppliername').attr('disabled', 'disabled');
						
					} else {
						$('#tin-val-text').removeClass("help-block text-yellow");
						$('#tin-val-text').addClass("help-block text-red");
						$('#tin-val-text').text('Validation was completed. An internal error occured, or system is in offline mode, or the supllier details do not exist!');	
						//$('#stockinsuppliertin').removeAttr('disabled');
					}
					
				},
				error: function(data) {
					//console.log(data);
					$('#tin-val-text').removeClass("help-block text-yellow");
					$('#tin-val-text').addClass("help-block text-red");
					$('#tin-val-text').text('An Internal Error occured. Validation was not succesfully');
					//$(this).removeAttr('disabled');	
				}
			});//.ajax				
		} else {
			$('#tin-val-text').text('Please, Input 10 characters. You have input '  + tin.length + ' character(s) only!');
		}		
			
	});//stockinsuppliertin	
	
	/**
	 * @desc Create a datatable called tbl-product
	 */
	$('#tbl-product-list').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-product-list
	
	/**
	 * @desc Change some elements on the products form depending on the choice of piece units
	 */	
	var setPieceFieldsDisabled = function(disabled){
		if(disabled){
	    	$('#productpiecemeasureunit').attr('disabled', 'disabled');
	    	$('#productpieceunitprice').attr('disabled', 'disabled');
	    	$('#productpackagescaledvalue').attr('disabled', 'disabled');
	    	$('#productpiecescaledvalue').attr('disabled', 'disabled');
		} else {
	    	$('#productpiecemeasureunit').removeAttr('disabled');
	    	$('#productpieceunitprice').removeAttr('disabled');
	    	$('#productpackagescaledvalue').removeAttr('disabled');
	    	$('#productpiecescaledvalue').removeAttr('disabled');
		}
	};

	var enforceExcisePieceRules = function(){
		var hasExciseTax = $.trim($('#producthasexcisetax').val() || '');
		var havePieceUnit = $.trim($('#producthavepieceunit').val() || '');

		if(hasExciseTax === '101'){
			$('#productexcisedutylist').removeAttr('disabled');
			if(havePieceUnit !== '101'){
				$('#producthavepieceunit').val('101').trigger('change.select2');
			}
			$('#producthavepieceunit').attr('disabled', 'disabled');
			setPieceFieldsDisabled(false);
			$('#productpiecemeasureunit').attr('required', 'required');
			$('#productpieceunitprice').attr('required', 'required');
			$('#productpackagescaledvalue').attr('required', 'required');
			$('#productpiecescaledvalue').attr('required', 'required');
		} else {
			$('#producthavepieceunit').removeAttr('disabled');
			$('#productpiecemeasureunit').removeAttr('required');
			$('#productpieceunitprice').removeAttr('required');
			$('#productpackagescaledvalue').removeAttr('required');
			$('#productpiecescaledvalue').removeAttr('required');

			if(hasExciseTax === '102'){
				$('#productexcisedutylist').val('').change();
				$('#productexcisedutylist').attr('disabled', 'disabled');
			} else {
				$('#productexcisedutylist').removeAttr('disabled');
			}

			if(havePieceUnit === '102'){
				setPieceFieldsDisabled(true);
			} else {
				setPieceFieldsDisabled(false);
			}
		}
	};

	var validateExcisePieceRules = function(){
		var hasExciseTax = $.trim($('#producthasexcisetax').val() || '');
		if(hasExciseTax !== '101'){
			return true;
		}

		var exciseDutyCode = $.trim($('#productexcisedutylist').val() || '');
		var havePieceUnit = $.trim($('#producthavepieceunit').val() || '');
		var pieceMeasureUnit = $.trim($('#productpiecemeasureunit').val() || '');
		var pieceUnitPrice = $.trim($('#productpieceunitprice').val() || '');
		var packageScaledValue = $.trim($('#productpackagescaledvalue').val() || '');
		var pieceScaledValue = $.trim($('#productpiecescaledvalue').val() || '');

		if(exciseDutyCode === '' || havePieceUnit !== '101' || pieceMeasureUnit === '' || pieceUnitPrice === '' || packageScaledValue === '' || pieceScaledValue === ''){
			alert('When Have Excise Duty is Yes, please populate Excise Duty Code, set Have Piece Units to Yes, and fill all piece-unit fields.');
			return false;
		}

		return true;
	};

	$("#producthavepieceunit").change(function(e) {
		var $option = $(this).find('option:selected');

		var value = $option.val();//to get content of "value" attrib
	    var text = $option.text();//to get <option>Text</option> content
	    
	    ////console.log(text + ' has been selected...');
	    ////console.log('the value is ' + value);
	    
	    if(value == '102'){//102 is No
	    	setPieceFieldsDisabled(true);
	    } else {	    	
	    	setPieceFieldsDisabled(false);
	    }

		enforceExcisePieceRules();
	});	
	
	/**
	 * @desc Change some elements on the products form depending on the choice of stockout adjustment type
	 */	
	$("#stockoutadjustmenttype").change(function(e) {
		var $option = $(this).find('option:selected');

		var value = $option.val();//to get content of "value" attrib
	    var text = $option.text();//to get <option>Text</option> content
	    
	    ////console.log(text + ' has been selected...');
	    ////console.log('the value is ' + value);
	    
	    if(value !== '105'){//105 is Other
	    	////console.log('Disabling elements...');
	    	$('#stockoutremarks').attr('disabled', 'disabled');
	    } else {	    	
	    	////console.log('Refreshing page...');
	    	$('#stockoutremarks').removeAttr('disabled');
	    }
	});			
	
	
	/**
	 * @desc Change some elements on the products form depending on the choice of excise duty
	 */	
	$("#producthasexcisetax").change(function(e) {
		var $option = $(this).find('option:selected');

		var value = $option.val();//to get content of "value" attrib
	    var text = $option.text();//to get <option>Text</option> content
	    
	    ////console.log(text + ' has been selected...');
	    ////console.log('the value is ' + value);
	    
		enforceExcisePieceRules();
	});	

	$('form[name="product-form"]').on('submit', function(e){
		if(!validateExcisePieceRules()){
			e.preventDefault();
			return false;
		}
	});

	enforceExcisePieceRules();
	
	/**
	 * @desc Fetch a list of products asyncronously and populate a datatable
	 */
	window.listproducts = function(){
		var columns = [
			{"title": "ID", "data": "ID"},
			{"title": "EFRIS ID", "data": "EFRIS Id"},
			{"title": "ERP ID", "data": "ERP ID"},
			{"title": "ERP Code", "data": "ERP Code"},
			{"title": "Code", "data": "Code"},
			{"title": "Name", "data": "Name"},
			{"title": "Description", "data": "Description"},
			{"title": "ERP Quantity", "data": "ERP Quantity"},
			{"title": "EFRIS Quantity", "data": "EFRIS Quantity"},
			{"title": "Unit Price", "data": "Unit Price"},
			{"title": "Modified Date", "data": "Modified Date"},
			{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewproduct\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a> | <a href='' title='Upload/Update' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Code'] + "\" data-target='#modal-product-upload'><i class='fa fa-upload'></i></a> | <a href='' title='Download' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Code'] + "\" data-target='#modal-fetch-efris-id'><i class='fa fa-download'></i></a> | <a href='' title='Stock-In' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Code'] + "\" data-target='#modal-product-stockin'><i class='fa fa-plus-circle'></i></a> | <a href='' title='Stock-Out' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Code'] + "\" data-target='#modal-product-stockout'><i class='fa fa-minus-circle'></i></a> | <a href='' title='Stock Transfer' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Code'] + "\" data-target='#modal-product-transfer'><i class='fa fa-truck'></i></a> | <a href='' title='Stock Query' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Code'] + "\" data-target='#modal-product-stockquery'><i class='fa fa-binoculars'></i></a> | <a href='' title='Download from ERP' id='' data-toggle='modal' data-id=\"" + row['ID'] + "\" data-code=\"" + row['Code'] + "\" data-target='#modal-fetch-erp-id'><i class='fa fa-cloud-download'></i></a>";}}
		];

		$('#otbl-product-list').remove();

		if ($.fn.dataTable.isDataTable('#tbl-product-list')) {
			$('#tbl-product-list').DataTable().destroy();
			$('#tbl-product-list').empty();
		}

		$('#tbl-product-list').DataTable({
			"dom": "Bfrtip",
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": '../etaxware/listproducts',
				"type": 'POST',
				"dataSrc": 'data',
				"error": function(){
					$('#otbl-product-list').remove();
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

		$('#otbl-product-list').remove();
	};//listproducts	
		
	
    /**
     * @desc Populate a modal called modal-product-upload
     */
    
	$("#modal-product-upload").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
				
		$('#uploadproductcode').val(code);
		$('#uploadproductid').val(id);
	});//modal-product-upload 	
	
    /**
     * @desc Populate a modal called modal-product-stockquery
     */
    
	$("#modal-product-stockquery").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
				
		$('#stockqueryproductcode').val(code);
		$('#stockqueryproductid').val(id);
	});//modal-product-stockquery 	
	
    /**
     * @desc Populate a modal called modal-product-stockin
     */
    
	$("#modal-product-stockin").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
				
		$('#stockinproductcode').val(code);
		$('#stockinproductid').val(id);
	});//modal-product-stockin 
	
    /**
     * @desc Populate a modal called modal-product-transfer
     */
    
	$("#modal-product-transfer").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
				
		$('#transferproductcode').val(code);
		$('#transferproductid').val(id);
	});//modal-product-transfer	
	
    /**
     * @desc Populate a modal called modal-product-stockout
     */
    
	$("#modal-product-stockout").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
				
		$('#stockoutproductcode').val(code);
		$('#stockoutproductid').val(id);
	});//modal-product-stockout 	
	
    /**
     * @desc Populate a modal called modal-fetch-efris-id
     */
    
	$("#modal-fetch-efris-id").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
				
		$('#fetchproductcode').val(code);
		$('#fetchproductid').val(id);
	});//modal-fetch-efris-id 
	
    /**
     * @desc Populate a modal called modal-fetch-erp-id
     */
    
	$("#modal-fetch-erp-id").on('show.bs.modal', function (e) {
		var tlink = $(e.relatedTarget);
		var code = tlink.data("code");
		var id = tlink.data("id");
				
		$('#fetcherpproductcode').val(code);
		$('#fetcherpproductid').val(id);
	});//modal-fetch-erp-id 							
});