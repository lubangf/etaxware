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
	 * Date: 2026-04-12
	 * Note: Uses local navigation links as the operation source so quick navigation works even when /listoperations is unavailable.
	 */	
	var findOperations = function() {
		var map = {};
		// Build a unique list of reachable routes from sidebar and dropdown menus.
		$('.sidebar-menu a[href], .dropdown-menu a[href]').each(function() {
			var rawUrl = ($(this).attr('href') || '').trim();
			var label = ($(this).text() || '').replace(/\s+/g, ' ').trim();

			if (!rawUrl || rawUrl === '#' || rawUrl === '') {
				return;
			}

			if (rawUrl.indexOf('javascript:') === 0) {
				return;
			}

			if (label.length < 2) {
				return;
			}

			var absoluteUrl = rawUrl;
			if (rawUrl.indexOf('http') !== 0) {
				absoluteUrl = $('<a/>', { href: rawUrl }).prop('href');
			}

			if (!map[absoluteUrl]) {
				map[absoluteUrl] = {
					id: absoluteUrl,
					text: label,
					url: absoluteUrl
				};
			}
		});

		return $.map(map, function(item) {
			return item;
		});
	};

	var renderDirectLink = function(operation) {
		var $result = $('#search-operation-result');
		var $container = $result.find('.col-xs-12');

		if (!operation || !operation.url) {
			$container.empty();
			$result.hide();
			return;
		}

		var $wrapper = $('<div/>', { 'class': 'alert alert-info', style: 'margin-bottom: 0;' });
		$wrapper.append($('<strong/>').text('Direct link: '));
		$wrapper.append(' ');
		$wrapper.append($('<a/>', {
			href: operation.url,
			text: 'Go to ' + operation.text
		}));

		$container.empty().append($wrapper);
		$result.show();
	};

	var operations = findOperations();

	// Render the selected action as a single explicit "Go to" link for clear user routing.
	$('#searchoperations').select2({
		placeholder : "What do you want to do...",
		minimumInputLength : 1,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		data : operations,
		language : {
			noResults : function() {
				return "No matching pages";
			}
		}
	});

	$('#searchoperations').on('select2:select', function(e) {
		// Show route CTA as soon as an operation is selected.
		renderDirectLink(e.params.data);
	});

	$('#searchoperations').on('select2:clear', function() {
		// Hide route CTA when selection is removed.
		renderDirectLink(null);
	});
});