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
	 * @desc Create a datatable called tbl-productoverridelist
	 */
	$('#tbl-productoverridelist').DataTable({
	"processing": true,
	    "paging": true,
	"lengthChange": true,
	"searching": true,
	"ordering": true,
	"info": true,
	"scrollX":true,
	"autoWidth": true,
	"destroy": true
	});//tbl-productoverridelist

	/**
	 * @desc Create a datatable called tbl-enforcetaxexclusionlist
	 */
	$('#tbl-enforcetaxexclusionlist').DataTable({
	"processing": true,
	    "paging": true,
	"lengthChange": true,
	"searching": true,
	"ordering": true,
	"info": true,
	"scrollX":true,
	"autoWidth": true,
	"destroy": true
	});//tbl-enforcetaxexclusionlist

	/**
	 * @desc Create a datatable called tbl-admin-settings
	 */
	if($('#tbl-admin-settings').length){
		$('#tbl-admin-settings').DataTable({
			"processing": true,
			"paging": true,
			"lengthChange": true,
			"searching": true,
			"ordering": true,
			"info": true,
			"scrollX":true,
			"autoWidth": true,
			"destroy": true
		});//tbl-admin-settings
	}
	
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
	 * @desc Fetch a list of product tax overrides asynchronously and populate a datatable
	 */
	window.listproductoverridelist = function(){
		$.ajax({
			url: '../etaxware/listproductoverridelist',
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
					{"title": "Modified Date", "data": "Modified Date"}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Code": d[i]['Code'],
						"Name": d[i]['Name'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-productoverridelist').remove(); 
				
				$('#tbl-productoverridelist').DataTable().destroy(); // destory existing datatable
				$('#tbl-productoverridelist').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-productoverridelist').DataTable({
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
	};//listproductoverridelist

	/**
	 * @desc Fetch enforce tax exclusion list asynchronously and populate a datatable
	 */
	window.listenforcetaxexclusionlist = function(){
		$.ajax({
			url: '../etaxware/listenforcetaxexclusionlist',
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
					{"title": "Modified Date", "data": "Modified Date"}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Code": d[i]['Code'],
						"Name": d[i]['Name'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-enforcetaxexclusionlist').remove(); 
				
				$('#tbl-enforcetaxexclusionlist').DataTable().destroy(); // destory existing datatable
				$('#tbl-enforcetaxexclusionlist').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-enforcetaxexclusionlist').DataTable({
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
	};//listenforcetaxexclusionlist

	window.adminsettingscache = {};

	/**
	 * @desc Fetch allowlisted admin settings asynchronously and populate a datatable
	 */
	window.listadminsettings = function(){
		if(!$('#tbl-admin-settings').length){
			return;
		}

		$.ajax({
			url: '../etaxware/listadminsettings',
			data: {
				//insert parameters here
			},
			method: 'GET',
			success: function(data){
				var rows = { "aaData": [] };
				var d = [];
				window.adminsettingscache = {};

				if(typeof data === 'string'){
					try {
						d = JSON.parse(data);
					} catch (e) {
						d = [];
					}
				} else if($.isArray(data)) {
					d = data;
				}

				var columns = { "aaColumn": [
					{"title": "ID", "data": "ID"},
					{"title": "Group", "data": "Group"},
					// 2026-04-26: Do not render raw setting code in UI; keep user-facing columns friendly.
					{"title": "Name", "data": "Name"},
					{"title": "Value", "data": "Value"},
					{"title": "Modified By", "data": "Modified By"},
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){
						if(parseInt(row['Editable'], 10) === 1){
							return "<a href=\"#\" title='Edit' onclick='window.openadminsettingmodal(" + row['ID'] + "); return false;'><i class='fa fa-pencil'></i></a>";
						}

						return "<span title='View only'><i class='fa fa-lock text-muted'></i></span>";
					}}
				]};

				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"Group": d[i]['Group'],
						"Name": d[i]['Name'],
						"Value": d[i]['Value'],
						"Type": d[i]['Type'],
						"Editable": d[i]['Editable'],
						"Modified By": d[i]['Modified By'],
						"Modified Date": d[i]['Modified Date']
					});

					window.adminsettingscache[d[i]['ID']] = {
						id: d[i]['ID'],
						// Keep code internal for edit/save API payloads even though it's hidden from the table and modal.
						code: d[i]['Code'],
						name: d[i]['Name'],
						value: d[i]['Value'],
						type: d[i]['Type'],
						editable: d[i]['Editable']
					};
				}

				$('#otbl-admin-settings').remove();

				$('#tbl-admin-settings').DataTable().destroy();
				$('#tbl-admin-settings').empty();

				$('#tbl-admin-settings').DataTable({
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
				$('#otbl-admin-settings').remove();
			}
		});//.ajax
	};//listadminsettings

	/**
	 * @desc Open the admin setting modal and prefill selected setting
	 */
	window.openadminsettingmodal = function(id){
		if(!window.adminsettingscache || !window.adminsettingscache[id]){
			window.showSystemAlertByMessage('The selected setting could not be found. Please refresh and try again.');
			return;
		}

		var setting = window.adminsettingscache[id];
		if(parseInt(setting.editable, 10) !== 1){
			window.showSystemAlertByMessage('This setting is view-only in Administration.');
			return;
		}

		$('#admin-setting-code').val(setting.code);
		$('#admin-setting-name-display').val(setting.name);
		$('#admin-setting-value').val(setting.value);
		$('#admin-setting-value').attr('placeholder', setting.type || 'string');
		$('#modal-edit-admin-setting').modal('show');
	};

	$('#btn-save-admin-setting').on('click', function(e){
		e.preventDefault();

		var settingCode = $('#admin-setting-code').val();
		var settingValue = $('#admin-setting-value').val();

		if(!settingCode){
			window.showSystemAlertByMessage('No setting was selected for editing');
			return;
		}

		$.ajax({
			url: '../etaxware/editadminsetting',
			method: 'POST',
			data: {
				settingcode: settingCode,
				settingvalue: settingValue
			},
			success: function(data){
				var response = data;
				if (typeof data === 'string') {
					try {
						response = JSON.parse(data);
					} catch (e) {
						response = { success: false, message: 'Invalid response while updating setting' };
					}
				}

				if(response.success){
					window.showSystemAlertByMessage(response.message || 'The setting has been updated successfully');
					$('#modal-edit-admin-setting').modal('hide');
					$('#ctnr-tbl-admin-settings').append('<div class="overlay" id="otbl-admin-settings"><i class="fa fa-refresh fa-spin"></i></div>');
					listadminsettings();
				}else{
					window.showSystemAlertByMessage(response.message || 'The operation to edit this setting was not successful');
				}
			},
			error: function(data){
				console.log(data);
				window.showSystemAlertByMessage('The operation to edit this setting was not successful');
			}
		});
	});

	$('#form-edit-admin-setting').on('submit', function(e){
		e.preventDefault();
		$('#btn-save-admin-setting').trigger('click');
	});
		
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
					{"title": "Actions", "render": function(data, type, row, meta){
						var viewLink = "<a href=\"../../etaxware/viewapikey\\" + row['ID'] + "\" title='View'><i class='fa fa-eye'></i></a>";
						var editLink = "<a href=\"../../etaxware/viewapikey\\" + row['ID'] + "\" title='Edit' style='margin-left:8px;'><i class='fa fa-pencil'></i></a>";
						var deleteLink = "<a href=\"#\" title='Delete' style='margin-left:8px;' onclick='window.deleteapikey(" + row['ID'] + "); return false;'><i class='fa fa-trash'></i></a>";
						return viewLink + editLink + deleteLink;
					}}
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

	/**
	 * @desc Delete API key asynchronously and refresh list
	 */
	window.deleteapikey = function(id){
		if(!id){
			return;
		}

		if(!confirm('Are you sure you want to delete this API key?')){
			return;
		}

		$.ajax({
			url: '../etaxware/deleteapikey',
			method: 'POST',
			data: {
				id: id
			},
			success: function(data){
				var response = data;
				if (typeof data === 'string') {
					try {
						response = JSON.parse(data);
					} catch (e) {
						response = { success: false, message: 'Invalid response while deleting API key' };
					}
				}

				if (response.success) {
					window.showSystemAlertByMessage(response.message || 'The API key has been deleted');
					$('#ctnr-tbl-apikeys').append('<div class="overlay" id="otbl-apikeys"><i class="fa fa-refresh fa-spin"></i></div>');
					listapikeys();
				} else {
					window.showSystemAlertByMessage(response.message || 'The operation to delete the API key was not successful');
				}
			},
			error: function(data){
				console.log(data);
				window.showSystemAlertByMessage('The operation to delete the API key was not successful');
			}
		});
	};
			
});