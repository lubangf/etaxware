/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the administration module
 * @date: 09-07-2020
 * @file: administration.js
 * @path: ./app/public/js/administration.js
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
$(function(){
	'use strict';	
	
	/**
	 * @desc Create a datatable called tbl-users
	 */
	$('#tbl-users').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-users
	
	/**
	 * @desc Create a datatable called tbl-taxtypes
	 */
	$('#tbl-taxtypes').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-taxtypes	
	
	/**
	 * @desc Create a datatable called tbl-roles
	 */
	$('#tbl-roles').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-roles
	
	/**
	 * @desc Create a datatable called tbl-branches
	 */
	$('#tbl-branches').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-branches		
	
		/**
	 * @desc Create a datatable called tbl-apikeys
	 */
	$('#tbl-apikeys').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-apikeys


	/**
	 * @desc Fetch a list of taxtypes asyncronously and populate a datatable
	 */
	window.listtaxtypes = function(){
		$.ajax({
			url: '../etaxware/listtaxtypes',
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
                    {"title": "Code", "data": "Code"},
                    {"title": "Name", "data": "Name"},
                    {"title": "Registration Date", "data": "Registration Date"},
                    {"title": "Cancellation Date", "data": "Cancellation Date"},
					{"title": "Modified Date", "data": "Modified Date"}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Code": d[i]['Code'],
						"Name": d[i]['Name'],
						"Registration Date": d[i]['Registration Date'],
						"Cancellation Date": d[i]['Cancellation Date'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-taxtypes').remove(); 
				
				$('#tbl-taxtypes').DataTable().destroy(); // destory existing datatable
				$('#tbl-taxtypes').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-taxtypes').DataTable({
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
	};//listtaxtypes
		
	/**
	 * @desc Fetch a list of users asyncronously and populate a datatable
	 */
	window.listusers = function(){
		$.ajax({
			url: '../etaxware/listusers',
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
                    {"title": "User Name", "data": "User Name"},
                    {"title": "Email", "data": "Email"},
                    {"title": "First Name", "data": "First Name"},
                    {"title": "Last Name", "data": "Last Name"},
                    {"title": "Branch", "data": "Branch"},
                    {"title": "Role", "data": "Role"},
                    {"title": "Status", "data": "Status"},
                    {"title": "Last Login Date", "data": "Last Login Date"},										
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewuser\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a>";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"User Name": d[i]['User Name'],
						"Email": d[i]['Email'],
						"First Name": d[i]['First Name'],
						"Last Name": d[i]['Last Name'],
						"Branch": d[i]['Branch'],
						"Role": d[i]['Role'],
						"Status": d[i]['Status'],
						"Last Login Date": d[i]['Last Login Date'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-users').remove(); 
				
				$('#tbl-users').DataTable().destroy(); // destory existing datatable
				$('#tbl-users').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-users').DataTable({
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
	};//listusers		
	
	
	/**
	 * @desc Fetch a list of roles asyncronously and populate a datatable
	 */
	window.listroles = function(){
		$.ajax({
			url: '../etaxware/listroles',
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
                    {"title": "Role Name", "data": "Role Name"},
                    {"title": "Status", "data": "Status"},
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewrole\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a>";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Role Name": d[i]['Role Name'],
						"Status": d[i]['Status'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-roles').remove(); 
				
				$('#tbl-roles').DataTable().destroy(); // destory existing datatable
				$('#tbl-roles').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-roles').DataTable({
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
	};//listroles
	
	/**
	 * @desc Fetch a list of branches asyncronously and populate a datatable
	 */
	window.listbranches = function(){
		$.ajax({
			url: '../etaxware/listbranches',
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
                    {"title": "Branch Name", "data": "Branch Name"},
                    {"title": "Status", "data": "Status"},
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewbranch\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a>";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Branch Name": d[i]['Branch Name'],
						"Status": d[i]['Status'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-branches').remove(); 
				
				$('#tbl-branches').DataTable().destroy(); // destory existing datatable
				$('#tbl-branches').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-branches').DataTable({
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
	};//listbranches	
	
	/**
	 * @desc Fetch a list of apikeys asyncronously and populate a datatable
	 */
	window.listapikeys = function(){
		$.ajax({
			url: '../etaxware/listapikeys',
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
					{"title": "Key", "data": "Key"},
					{"title": "Status", "data": "Status"},
					{"title": "Last Access Date", "data": "Last Access Date"},
					{"title": "Expiry Date", "data": "Expiry Date"},
					{"title": "Modified By", "data": "Modified By"},
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewapikey\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a>";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Key": d[i]['Key'],
						"Status": d[i]['Status'],						
						"Status ID": d[i]['Status ID'],
						"Last Access Date": d[i]['Last Access Date'],
						"Expiry Date": d[i]['Expiry Date'],
						"Modified By": d[i]['Modified By'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-apikeys').remove(); 
				
				$('#tbl-apikeys').DataTable().destroy(); // destory existing datatable
				$('#tbl-apikeys').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-apikeys').DataTable({
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
	};//listapikeys		
			
});