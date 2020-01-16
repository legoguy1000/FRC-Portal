angular.module('FrcPortal')
.controller('main.admin.metricsController', ['$timeout', '$q', '$scope', '$state', 'metricsService',
	mainAdminMetricsController
]);
function mainAdminMetricsController($timeout, $q, $scope, $state, metricsService) {
    var vm = this;

	vm.onClick = function (points, evt) {
		console.log(points, evt);
	};
	vm.options = {
		legend: {
			display: true,
			position: 'bottom'
		}
	};
	vm.stackedOptions = angular.copy(vm.options);
	vm.stackedOptions.scales = {
		xAxes: [{
		  stacked: true,
		}],
		yAxes: [{
		  stacked: true
		}]
	};
	vm.start_date = new Date().getFullYear()-2;
	vm.end_date = new Date().getFullYear();

	vm.changeYear = function() {
		vm.reportsAvgHrsPerPersonPerYear();
		vm.reportsAvgHrsPerUserTypePerYear();
		vm.reportsActiveUsersPerYear();
		vm.reportsHoursPerEventPerYear();
		vm.reportsAvgHrsPerGenderPerYear();
		vm.reportsHoursPerWeek();
		vm.reportsHoursPerGradePerYear();
		vm.reportsHoursPerEventTypePerYear();
		vm.reportsHoursPerDayOfWeek();
		vm.reportsHoursPerSchool();

	}

	vm.myInput = 2016
	vm.labels = [];
	vm.series = [];
	vm.data = [];
	vm.csvData = [];
	vm.reportsAvgHrsPerPersonPerYear = function () {
		metricsService.reportsAvgHrsPerPersonPerYear(vm.start_date, vm.end_date).then(function(response){
			vm.labels = response.labels;
			vm.series = response.series;
			vm.data = response.data;
			vm.csvData = response.csvData;
		});
	};
	vm.reportsAvgHrsPerPersonPerYear();


	vm.labels2 = [];
	vm.series2 = [];
	vm.data2 = [];
	vm.csvData2 = [];
	vm.reportsAvgHrsPerUserTypePerYear = function () {
		metricsService.reportsAvgHrsPerUserTypePerYear(vm.start_date, vm.end_date).then(function(response){
			vm.labels2 = response.labels;
			vm.series2 = response.series;
			vm.data2 = response.data;
			vm.csvData2 = response.csvData;
		});
	};
	vm.reportsAvgHrsPerUserTypePerYear();


	vm.labels3 = [];
	vm.series3 = [];
	vm.data3 = [];
	vm.csvData3 = [];
	vm.reportsActiveUsersPerYear = function () {
		metricsService.reportsActiveUsersPerYear(vm.start_date, vm.end_date).then(function(response){
			vm.labels3 = response.labels;
			vm.series3 = response.series;
			vm.data3 = response.data;
			vm.csvData3 = response.csvData;
			vm.datasetOverride3 = [
			  {
				label: vm.series3[0],
				stack: 'Stack 0',
			  }, {
				label: vm.series3[1],
				stack: 'Stack 0',
			  }, {
				label: vm.series3[2],
				stack: 'Stack 1',
			  }, {
				label: vm.series3[3],
				stack: 'Stack 1',
			  }, {
				label: vm.series3[4],
				stack: 'Stack 2',
			  }, {
				label: vm.series3[5],
				stack: 'Stack 2',
			  }, {
				label: vm.series3[6],
				stack: 'Stack 2',
			  }, {
				label: vm.series3[7],
				stack: 'Stack 2',
			  }, {
				label: vm.series3[8],
				stack: 'Stack 2',
			  }, {
				label: vm.series3[9],
				stack: 'Stack 2',
			  }
			];
		});
	};
	vm.reportsActiveUsersPerYear();


	vm.labels4 = [];
	vm.series4 = [];
	vm.data4 = [];
	vm.csvData4 = [];
	vm.reportsHoursPerEventPerYear = function () {
		metricsService.reportsHoursPerEventPerYear(vm.end_date).then(function(response){
			vm.labels4 = response.labels;
			vm.series4 = response.series;
			vm.data4 = response.data;
			vm.csvData4 = response.csvData;
		});
	};
	vm.reportsHoursPerEventPerYear();

	vm.labels5 = [];
	vm.series5 = [];
	vm.data5 = [];
	vm.csvData6 = [];
	vm.reportsAvgHrsPerGenderPerYear = function () {
		metricsService.reportsAvgHrsPerGenderPerYear(vm.start_date, vm.end_date).then(function(response){
			vm.labels5 = response.labels;
			vm.series5 = response.series;
			vm.data5 = response.data;
			vm.csvData5 = response.csvData;
		});
	};
	vm.reportsAvgHrsPerGenderPerYear();

	vm.labels6 = [];
	vm.series6 = [];
	vm.data6 = [];
	vm.csvData6 = [];
	vm.reportsHoursPerWeek = function () {
		metricsService.reportsHoursPerWeek(vm.end_date).then(function(response){
			vm.labels6 = response.labels;
			vm.series6 = response.series;
			vm.data6 = response.data;
			vm.csvData6 = response.csvData;
		});
	};
	vm.reportsHoursPerWeek();

	vm.labels7 = [];
	vm.series7 = [];
	vm.data7 = [];
	vm.csvData7 = [];
	vm.reportsHoursPerGradePerYear = function () {
		metricsService.reportsHoursPerGradePerYear(vm.start_date, vm.end_date).then(function(response){
			vm.labels7 = response.labels;
			vm.series7 = response.series;
			vm.data7 = response.data;
			vm.csvData7 = response.csvData;
		});
	};
	vm.reportsHoursPerGradePerYear();

	vm.labels8 = [];
	vm.series8 = [];
	vm.data8 = [];
	vm.csvData8 = [];
	vm.reportsHoursPerEventTypePerYear = function () {
		metricsService.reportsHoursPerEventTypePerYear(vm.end_date).then(function(response){
			vm.labels8 = response.labels;
			vm.series8 = response.series;
			vm.data8 = response.data;
			vm.csvData8 = response.csvData;
		});
	};
	vm.reportsHoursPerEventTypePerYear();

	vm.labels9 = [];
	vm.series9 = [];
	vm.data9 = [];
	vm.csvData9 = [];
	vm.reportsHoursPerDayOfWeek = function () {
		metricsService.reportsHoursPerDayOfWeek(vm.start_date, vm.end_date).then(function(response){
			vm.labels9 = response.labels;
			vm.series9 = response.series;
			vm.data9 = response.data;
			vm.csvData9 = response.csvData;
			vm.datasetOverride9 = [
				{
				label: vm.series9[0],
				stack: 'Stack 0',
				}, {
				label: vm.series9[1],
				stack: 'Stack 1',
				}, {
				label: vm.series9[2],
				stack: 'Stack 2',
				}, {
				label: vm.series9[3],
				stack: 'Stack 3',
				}, {
				label: vm.series9[4],
				stack: 'Stack 4',
				}, {
				label: vm.series9[5],
				stack: 'Stack 5',
				}, {
				label: vm.series9[6],
				stack: 'Stack 6',
				}
			];
		});
	};
	vm.reportsHoursPerDayOfWeek();

	vm.labels10 = [];
	vm.series10 = [];
	vm.data10 = [];
	vm.csvData10 = [];
	vm.reportsHoursPerSchool = function () {
		metricsService.reportsHoursPerSchool(vm.start_date, vm.end_date).then(function(response){
			vm.labels10 = response.labels;
			vm.series10 = response.series;
			vm.data10 = response.data;
			vm.csvData10 = response.csvData;
		});
	};
	vm.reportsHoursPerSchool();
}
