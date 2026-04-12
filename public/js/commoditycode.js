/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the commoditycode module
 * @date: 16-12-2022
 * @file: commoditycode.js
 * @path: ./app/public/js/commoditycode.js
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
	}).on('change', function () {
		var code = $(this).val();
		$('#code-val-text').text('');
		$('#modal-get-commoditycode').modal('show');

		$.ajax({
            url: '../etaxware/getcommoditycode',
            data: {
                //insert parameters here
                'code' : code
            },
            method: 'POST',
            cache: false,
            success: function(data){
                console.log(data);
                var d = JSON.parse(data);
                //console.log(d['0']['commoditycode']);

                if(d['0']['commoditycode'] == code){
                    $('#code-val-text').removeClass("help-block text-red");
                    $('#code-val-text').addClass("help-block text-green");
                    $('#code-val-text').text('The check was completed. The commodity details will be populated soon.');
                    
                    $('#producttaxrate').val(d['0']['rate']);
                    
                    $('#productisexempt').val(d['0']['isExempt']);
                    
                    $('#productiszerorated').val(d['0']['isZeroRate']);
                    
                    $('#productexclusion').val(d['0']['exclusion']);
                    
                    $('#productservicemark').val(d['0']['serviceMark']);
                      
                    $('#modal-get-commoditycode').modal('hide');                    
                } else {
                    $('#code-val-text').removeClass("help-block text-green");
                    $('#code-val-text').addClass("help-block text-red");
                    $('#code-val-text').text('The check was completed. An internal error occured, or the code does not exist!');	
                    $('#modal-get-commoditycode').modal('hide');
                }
                
            },
            error: function(data) {
                console.log(data);
                $('#code-val-text').removeClass("help-block text-green");
                $('#code-val-text').addClass("help-block text-red");
                $('#code-val-text').text('An Internal Error occured. The check was not succesfully');
                $('#modal-get-commoditycode').modal('hide');
            }
        });//.ajax	

        //$('#modal-get-commoditycode').modal('hide');
	});		
	

	
		
	/**
	 * @desc Fetch commoditycode rates asyncronously
	 */
	window.fetchcommoditycode = function(){
		$.ajax({
			url: '../etaxware/fetchcommoditycode',
			data: {
				//insert parameters here
			},
			method: 'POST',
			success: function(data){
				console.log(data);
				//var rows = { "aaData": [] };
				//var d = JSON.parse(data);
				
				//$('#fetch-commoditycode-2 span').text('Processing...');			
			},
			error: function(data) {
				console.log(data);
			}
		});//.ajax
	};//fetchcommoditycoderates			
});