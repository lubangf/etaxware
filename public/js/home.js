/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the file home module
 * @date: 13-06-2020
 * @file: home.js
 * @path: ./app/public/js/home.js
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
	 * @desc populate the search bar on the home page
	 */	
	$('#searchoperations').select2({
		placeholder : "What do you want to do...",
		minimumInputLength : 4,
		allowClear : true,
		closeOnSelect : false,
		multiple : true,
		ajax : {
			type : 'GET',
			url : "../etaxware/listoperations",
			dataType : 'json',
			delay : 0,
			cache : false,
			data : function(params) {
				return {
					q : params.term, // search term
					page : params.page, // page
				};
			},
			processResults : function(data) {
				return {
					results : $.map(data, function(item) {
						return {
							text : item.Name,
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
});