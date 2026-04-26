/**
 * This file is part of the etaxware system
 * This file contains key javascript routines for the file main dashboard module
 * @date: 08-04-2019
 * @file: dashboard.js
 * @path: ./app/public/js/dashboard.js
 * @author: francis lubanga <frncslubanga@gmail.com>
 * @copyright  (C) d'alytics - All Rights Reserved
 * @version    1.0.0
 */
$(function(){
	'use strict';

	var rawChartData = $.trim($('#dashboard-chart-data').text() || '{}');
	var parsed = {};

	try {
		parsed = JSON.parse(rawChartData);
	} catch (e) {
		parsed = {};
	}

	if (typeof Chart === 'undefined') {
		return;
	}

	var labels = parsed.labels || [];
	var trend = parsed.trend || {};
	var mix = parsed.mix || {};
	var sync = parsed.sync || {};

	var trendCanvas = document.getElementById('dashboard-trend-chart');
	if (trendCanvas) {
		new Chart(trendCanvas, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [
					{
						label: 'Invoices',
						data: trend.invoices || [],
						borderColor: '#00a65a',
						backgroundColor: 'rgba(0,166,90,0.15)',
						fill: true,
						lineTension: 0.2
					},
					{
						label: 'Credit Notes',
						data: trend.creditnotes || [],
						borderColor: '#f39c12',
						backgroundColor: 'rgba(243,156,18,0.12)',
						fill: true,
						lineTension: 0.2
					},
					{
						label: 'Debit Notes',
						data: trend.debitnotes || [],
						borderColor: '#dd4b39',
						backgroundColor: 'rgba(221,75,57,0.12)',
						fill: true,
						lineTension: 0.2
					}
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				legend: {
					display: true
				},
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero: true,
							precision: 0
						}
					}]
				}
			}
		});
	}

	var mixCanvas = document.getElementById('dashboard-mix-chart');
	if (mixCanvas) {
		new Chart(mixCanvas, {
			type: 'doughnut',
			data: {
				labels: ['Invoices', 'Credit Notes', 'Debit Notes'],
				datasets: [{
					data: [mix.invoices || 0, mix.creditnotes || 0, mix.debitnotes || 0],
					backgroundColor: ['#00c0ef', '#f39c12', '#dd4b39']
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				legend: {
					position: 'bottom'
				}
			}
		});
	}

	var syncCanvas = document.getElementById('dashboard-sync-chart');
	if (syncCanvas) {
		new Chart(syncCanvas, {
			type: 'bar',
			data: {
				labels: ['Invoices', 'Credit Notes', 'Debit Notes'],
				datasets: [
					{
						label: 'Synced',
						data: [sync.invoicesSynced || 0, sync.creditSynced || 0, sync.debitSynced || 0],
						backgroundColor: '#00a65a'
					},
					{
						label: 'Pending',
						data: [sync.invoicesPending || 0, sync.creditPending || 0, sync.debitPending || 0],
						backgroundColor: '#f39c12'
					}
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero: true,
							precision: 0
						}
					}]
				}
			}
		});
	}
});