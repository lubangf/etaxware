/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the currency module
 * @date: 06-09-2020
 * @file: currency.js
 * @path: ./app/public/js/currency.js
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
$(function(){
	'use strict';	
	
	/**
	 * @desc Create a datatable called tbl-currency
	 */
	$('#tbl-currency-list').DataTable({
        "processing": true,
    	"paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX":true,
        "autoWidth": true,
        "destroy": true
	});//tbl-currency-list
	
	/**
	 * @desc Fetch a list of currencies asyncronously and populate a datatable
	 */
	window.listcurrencies = function(){
		$.ajax({
			url: '../etaxware/listcurrencies',
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
					{"title": "ERP ID", "data": "ERP ID"},
					{"title": "ERP Code", "data": "ERP Code"},
					{"title": "Code", "data": "Code"},
					{"title": "Name", "data": "Name"},
					{"title": "Description", "data": "Description"},
					{"title": "Rate", "data": "Rate"},	
					{"title": "exportLevy", "data": "exportLevy"},
					{"title": "importDutyLevy", "data": "importDutyLevy"},
					{"title": "inComeTax", "data": "inComeTax"},								
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewcurrency\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a>";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"ERP ID": d[i]['ERP ID'],
						"ERP Code": d[i]['ERP Code'],
						"Code": d[i]['Code'],
						"Name": d[i]['Name'],
						"Description": d[i]['Description'],
						"Rate": d[i]['Rate'],
						"exportLevy": d[i]['exportLevy'],
						"importDutyLevy": d[i]['importDutyLevy'],
						"inComeTax": d[i]['inComeTax'],
						"Disabled": d[i]['Disabled'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-currency-list').remove(); 
				
				$('#tbl-currency-list').DataTable().destroy(); // destory existing datatable
				$('#tbl-currency-list').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-currency-list').DataTable({
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
	};//listcurrencies	
	
	
	/**
	 * @desc Fetch currency rates asyncronously
	 */
	window.fetchcurrencyrates = function(){
		$.ajax({
			url: '../etaxware/fetchcurrencyrates',
			data: {
				//insert parameters here
			},
			method: 'POST',
			success: function(data){
				console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				
				//push the column metadata into a variable called columns
				var columns = { "aaColumn": [
					{"title": "ID", "data": "ID"},
					{"title": "ERP ID", "data": "ERP ID"},
					{"title": "ERP Code", "data": "ERP Code"},
					{"title": "Code", "data": "Code"},
					{"title": "Name", "data": "Name"},
					{"title": "Description", "data": "Description"},
					{"title": "Rate", "data": "Rate"},
					{"title": "exportLevy", "data": "exportLevy"},
					{"title": "importDutyLevy", "data": "importDutyLevy"},
					{"title": "inComeTax", "data": "inComeTax"},									
					{"title": "Modified Date", "data": "Modified Date"},
					{"title": "Actions", "render": function(data, type, row, meta){return "<a href=\"" + "../../etaxware/viewcurrency\\" + row['ID'] + "\" title='View' id=''><i class='fa fa-eye'></i></a>";}}
				]}; 
				
				//push data into a variable called rows
				for(var i in d) {
					rows.aaData.push({
						"ID": d[i]['ID'],
						"ERP ID": d[i]['ERP ID'],
						"ERP Code": d[i]['ERP Code'],
						"Code": d[i]['Code'],
						"Name": d[i]['Name'],
						"Description": d[i]['Description'],
						"Rate": d[i]['Rate'],
						"exportLevy": d[i]['exportLevy'],
						"importDutyLevy": d[i]['importDutyLevy'],
						"inComeTax": d[i]['inComeTax'],
						"Disabled": d[i]['Disabled'],
						"Modified Date": d[i]['Modified Date']
					});
				}
				
				// remove the <overlay> element which is loaded on pageload
				$('#otbl-currency-list').remove(); 
				
				$('#tbl-currency-list').DataTable().destroy(); // destory existing datatable
				$('#tbl-currency-list').empty(); // flush the datatable in case the columns change
				
				var table = $('#tbl-currency-list').DataTable({
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
	};//fetchcurrencyrates			
});