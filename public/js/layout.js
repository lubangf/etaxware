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
	 * Show a standardized system alert modal with optional severity.
	 * Supported severities: info, success, warning, danger.
	 */
	window.showSystemAlert = function(message, severity){
		var type = $.trim((severity || 'info').toLowerCase());
		if ($.inArray(type, ['info', 'success', 'warning', 'danger']) === -1) {
			type = 'info';
		}

		var styleMap = {
			info: {
				title: 'System Alert',
				iconClass: 'fa-info-circle'
			},
			success: {
				title: 'Success',
				iconClass: 'fa-check-circle'
			},
			warning: {
				title: 'Warning',
				iconClass: 'fa-exclamation-triangle'
			},
			danger: {
				title: 'Error',
				iconClass: 'fa-times-circle'
			}
		};
		var style = styleMap[type] || styleMap.info;

		var $modal = $('#modal-system-alert');
		$modal.removeClass('modal-info modal-success modal-warning modal-danger');
		$modal.addClass('modal-' + type);
		$('#title-system-alert').text(style.title);
		$('#icon-system-alert')
			.removeClass('fa-info-circle fa-check-circle fa-exclamation-triangle fa-times-circle')
			.addClass(style.iconClass);
		$('#text-system-alert').text(message || '');
		$modal.modal('show');
	};

	/**
	 * Infer alert severity from server-provided system alert text.
	 */
	window.inferSystemAlertSeverity = function(message){
		var text = $.trim((message || '').toLowerCase());
		if (text === '') {
			return 'info';
		}

		var failedCountMatch = text.match(/failed\s*=\s*(\d+)/);
		var failedCount = failedCountMatch ? parseInt(failedCountMatch[1], 10) : null;
		var hasFailedCount = failedCount !== null && !isNaN(failedCount) && failedCount > 0;

		var hasAny = function(keywords) {
			for (var i = 0; i < keywords.length; i++) {
				if (text.indexOf(keywords[i]) !== -1) {
					return true;
				}
			}
			return false;
		};

		var hasRegex = function(regexes) {
			for (var i = 0; i < regexes.length; i++) {
				if (regexes[i].test(text)) {
					return true;
				}
			}
			return false;
		};

		if (hasAny(['was successful', 'completed successfully', 'synced successfully', 'updated successfully', 'created successfully', 'sync completed', 'commodity sync completed', 'synced:']) && !hasAny(['not successful'])) {
			if (!hasFailedCount) {
				return 'success';
			}
		}

		if (hasFailedCount || hasAny(['not successful', 'error', 'exception', 'invalid', 'forbidden', 'not allowed', 'unable', 'blocked', 'denied']) || hasRegex([/\bfailed\b(?!\s*=\s*0)/])) {
			return 'danger';
		}

		if (hasAny(['warning', 'completed with issues', 'completed with errors', 'missing', 'please ensure', 'didnt return anything', 'please complete required field', 'please populate'])) {
			return 'warning';
		}

		if (hasAny(['was successful', 'completed', 'synced successfully', 'updated successfully', 'created successfully'])) {
			return 'success';
		}

		return 'info';
	};

	/**
	 * Show system alert using inferred severity from message text.
	 */
	window.showSystemAlertByMessage = function(message){
		window.showSystemAlert(message, window.inferSystemAlertSeverity(message));
	};
	
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