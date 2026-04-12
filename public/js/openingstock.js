/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the opening stock module module
 * @date: 11-01-2023
 * @file: openingstock.js
 * @path: ./app/public/js/openingstock.js
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
$(function(){
	'use strict';
	//initialise select2 dropdowns
	$(".select2").select2();
	
	//ensure the subfolder select allows for adding custom option
	$(".existsubfoldername").select2({
		tags: true
	});
	
	
	$('#existsubfoldername').select2({
		placeholder : "Select existing folder. Or create a new one below",
		minimumInputLength : 0,
		allowClear : true,
		closeOnSelect : true,
		multiple : false,
		ajax : {
			type : 'POST',
			url : "../etaxware/subfolderlist",
			dataType : 'json',
			delay : 0,
			cache : false,
			data : {
				//insert parameters here
				'folder' : username
			},
			processResults : function(data) {
				return {
					results : $.map(data, function(item) {
						return {
							text : item.basename,
							id : item.filename
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
	 * Create a datatable called tbl-file-openingstock
	 */
	$('#tbl-file-openingstock').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true,
        "order": [[ 4, 'desc' ]]
	});//tbl-file-openingstock
	
	/**
	 * Fetch a list of files asyncronously and populate a datatable
	 */
	window.listfiles = function(){
		$.ajax({
			url: '../etaxware/listfiles',
			data: {
				//insert parameters here
			},
			method: 'GET',
			success: function(data){

				console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "ID", "data": "ID"},
					{"title" : "File Name", "data": "File Name"},
					{"title" : "Folder", "data": "Folder"},
					{"title": "Uploaded By", "data": "Uploaded By"},
					{"title": "Upload Date", "data": "Upload Date"},
					{"title": "Processing Date", "data": "Processing Date"},
					{"title": "Status", "data": "Status"},
					{"title": "Actions", "data": "Actions"}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"File Name": d[i]['File Name'],
						"Folder": d[i]['Folder'],
						"Uploaded By": d[i]['Uploaded By'],
						"Upload Date": d[i]['Upload Date'],
						"Processing Date": d[i]['Processing Date'],
						"Status": d[i]['Status'],
						"Actions": d[i]['Actions']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-file-openingstock').remove(); 
				
				$('#tbl-file-openingstock').DataTable().destroy(); // destory existing datatable
				$('#tbl-file-openingstock').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-file-openingstock').DataTable({
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
				    "destroy": true,
				    "order": [[ 4, 'desc' ]]
				});
			},
			error: function(data) {
				console.log(data);
			}
		});//.ajax
	};//listfiles	
	
	
	/**
	 * Create a datatable called tbl-openingstock-status
	 */
	$('#tbl-openingstock-status').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true,
        "order": [[ 3, 'desc' ]]
	});//tbl-openingstock-status
	
	/**
	 * Fetch a list of runs asyncronously and populate a datatable
	 */
	window.listopeningstockprocruns = function(){
		$.ajax({
			url: '../etaxware/listopeningstockprocruns',
			data: {
				//insert parameters here
			},
			method: 'GET',
			success: function(data){

				console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "Run Id", "data": "Run Id"},
					{"title": "File Id", "data": "File Id"},
					{"title" : "File Name", "data": "File Name"},
					{"title" : "Started By", "data": "Started By"},
					{"title": "Start Date", "data": "Start Date"},
					{"title": "End Date", "data": "End Date"},
					{"title": "Errors", "data": "Errors"}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"Run Id": d[i]['Run Id'],
						"File Id": d[i]['File Id'],
						"File Name": d[i]['File Name'],
						"Started By": d[i]['Started By'],
						"Start Date": d[i]['Start Date'],
						"End Date": d[i]['End Date'],
						"Errors": d[i]['Errors']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-openingstock-status').remove(); 
				
				$('#tbl-openingstock-status').DataTable().destroy(); // destory existing datatable
				$('#tbl-openingstock-status').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-openingstock-status').DataTable({
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
				    "destroy": true,
				    "order": [[ 3, 'desc' ]]
				});
			},
			error: function(data) {
				console.log(data);
			}
		});//.ajax
	};//listopeningstockprocruns		
	
	/**
	 * Create a datatable called tbl-openingstock-log
	 */
	$('#tbl-openingstock-log').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true,
        "order": [[ 0, 'desc' ]]
	});//tbl-openingstock-log
	
	/**
	 * Fetch a list of logs asyncronously and populate a datatable
	 */
	window.listopeningstockproclogs = function(){
		$.ajax({
			url: '../etaxware/listopeningstockproclogs',
			data: {
				//insert parameters here
			},
			method: 'GET',
			success: function(data){

				console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "Date", "data": "Date"},
					{"title" : "Run Id", "data": "Run Id"},
					{"title" : "Run By", "data": "Run By"},					
					{"title" : "File Id", "data": "File Id"},
					{"title" : "File Name", "data": "File Name"},
					{"title": "Activity", "data": "Activity"}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"Date": d[i]['Date'],
						"Run Id": d[i]['Run Id'],
						"Run By": d[i]['Run By'],
						"File Id": d[i]['File Id'],
						"File Name": d[i]['File Name'],						
						"Activity": d[i]['Activity']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-openingstock-log').remove(); 
				
				$('#tbl-openingstock-log').DataTable().destroy(); // destory existing datatable
				$('#tbl-openingstock-log').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-openingstock-log').DataTable({
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
				    "destroy": true,
				    "order": [[ 0, 'desc' ]]
				});
			},
			error: function(data) {
				console.log(data);
			}
		});//.ajax
	};//listopeningstockproclogs	
});