/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the report module
 * @date: 14-06-2020
 * @file: report.js
 * @path: ./app/public/js/report.js
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
$(function(){
	'use strict';	
    var reportCsrfToken = $.trim($('#report-csrf-token').text());
	
	/**
	 * @desc initialise a date picker called startdate
	 */	
	$('#startdate').datepicker({
	  	autoclose: true
	});
	
	$('#startdate').datepicker('update', new Date());	
	
	/**
	 * @desc initialise a date picker called enddate
	 */	
	$('#enddate').datepicker({
	  	autoclose: true
	});
	
	$('#enddate').datepicker('update', new Date());	
	
	
	/**
	 * @desc initialise select2 dropdowns
	 */
	$(".select2").select2();	
	
    /**
     * @desc populate the select2 drop-downs for report groups
     */
    $('#rptgroup').select2({
        placeholder : "Select group",
        //minimumInputLength : 0,
        allowClear : true,
        closeOnSelect : true,
        selectOnClose: true, //Select the highlighted result
        multiple : false,
        ajax : {
            type : 'POST',
            url : "../etaxware/listreportgroups",
            dataType : 'json',
            delay : 0,
            cache : false,
            data : {
                'report_csrf_token': reportCsrfToken
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
    }).on("select2:close", function(e) {
        $("#reports").empty().trigger('change');//clear selections
        var data = $(this).val();
        if (!data) {
            return;
        }
        var rptgroup = data.toString();
        $('#reports').select2({
            placeholder : "Select report",
            //minimumInputLength : 0,
            allowClear : true,
            closeOnSelect : true,
            selectOnClose: true, //Select the highlighted result
            multiple : false,
            ajax : {
                type : 'POST',
                url : "../etaxware/listreports",
                dataType : 'json',
                delay : 0,
                cache : false,
                data : {
                    'report_csrf_token': reportCsrfToken,
                    'id' : rptgroup,                   
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
    });//rptgroup	

	
	/**
	 * @desc populate the product search on the report screen
	 */	
	$('#product').select2({
		placeholder : "Search Product...",
		minimumInputLength : 4,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/listproducts",
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
});