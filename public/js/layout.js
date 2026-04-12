/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the layout module
 * @date: 08-04-2019
 * @file: layout.js
 * @path: ./app/public/js/layout.js
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
$(function(){
	'use strict';
	
	/**
	 * Fetch a list of notifications asyncronously
	 */
	window.listnotifications = function(){
		$.ajax({
			url: '../etaxware/listnotifications',
			data: {
				//insert parameters here
				'id' : notid
			},
			method: 'POST',
			cache: false,
			success: function(data){
				console.log(data);
				var d = JSON.parse(data);
				//console.log(d[0]['id']);
				$('#notid').val(d[0]['id']);
				$('#notinserteddt').val(d[0]['inserteddt']);
				$('#notnotification').val(d[0]['notification']);
			},
			error: function(data) {
				console.log(data);
			}
		});//.ajax
	};//listnotifications
	
	/**
	 * Mark a notification as read
	 */
	window.readnotification = function(){
		$.ajax({
			url: '../etaxware/readnotification',
			data: {
				//insert parameters here
				'id' : notid
			},
			method: 'POST',
			cache: false,
			success: function(data){
				//console.log(data);
			},
			error: function(data) {
				console.log(data);
			}
		});//.ajax
	};//readnotification
});