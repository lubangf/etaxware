/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the view credit/debit note module
 * @date: 06-09-2020
 * @file: viewcreditnote.js
 * @path: ./app/public/js/viewcreditnote.js
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
$(function(){
	'use strict';	
	
		
	/**
	 * @desc Fetch an creditnote's QR string and print it
	 */
	window.printqr = function(creditnote){
		$.ajax({
			url: '../etaxware/listcreditnotes',
			data: {
				//insert parameters here
				'creditnoteid': creditnote
			},
			method: 'POST',
			success: function(data){

				//console.log(data);
				var rows = { "aaData": [] };
				var d = JSON.parse(data);
				//var qr = '020000001149OC120012337999000003D09000000094F80A20A021D534A1000121462A1000094968~MM INTERGRATED STEEL MILLS (UGANDA) LIMITED~Ediomu & Company~Iron Sheets';
				var qr = d[0]['QR Code'];
				
				var qropts = {
					render: 'canvas',	
					minVersion: 1,
					maxVersion: 40,	
					ecLevel: 'L',
					left: 0,
					top: 0,
					size: 150,
					fill: '#000',
					background: null,
					text: qr,
					quiet: 0,
					mode: 0,
					mSize: 0.1,
				    mPosX: 0.5,
				   	mPosY: 0.5,
				    label: 'no label',
				    fontname: 'sans',
				    fontcolor: '#000',
				    image: null};
					
				$('#datamatrix').qrcode(qropts);	
			},
			error: function(data) {
				console.log(data);
			}
		});//.ajax
	};//printqr	
							
});