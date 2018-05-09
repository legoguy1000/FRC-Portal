angular.module('FrcPortal')
.controller('main.admin.metricsController', ['$timeout', '$q', '$scope', '$state', '$timeout', 'metricsService',
	mainAdminMetricsController
]);
function mainAdminMetricsController($timeout, $q, $scope, $state, $timeout, metricsService) {
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

	vm.myInput = 2016
	vm.labels = [];
	vm.series = [];
	vm.data = [];
	vm.csvData = [];
	vm.start_date1 = new Date().getFullYear()-2;
	vm.end_date1 = new Date().getFullYear();
	vm.reportsAvgHrsPerPersonPerYear = function () {
		if(vm.start_date1 != null and vm.end_date1 != null) {
			metricsService.reportsAvgHrsPerPersonPerYear(vm.start_date1, vm.end_date1).then(function(response){
				vm.labels = response.labels;
				vm.series = response.series;
				vm.data = response.data;
				vm.csvData = response.csvData;
			});
		}
	};
	//vm.reportsAvgHrsPerPersonPerYear();


	vm.labels2 = [];
	vm.series2 = [];
	vm.data2 = [];
	vm.csvData2 = [];
	vm.start_date2 = new Date().getFullYear()-2;
	vm.end_date2 = new Date().getFullYear();
	vm.reportsAvgHrsPerUserTypePerYear = function () {
		metricsService.reportsAvgHrsPerUserTypePerYear(vm.start_date2, vm.end_date2).then(function(response){
			vm.labels2 = response.labels;
			vm.series2 = response.series;
			vm.data2 = response.data;
			vm.csvData2 = response.csvData;
		});
	};
//	vm.reportsAvgHrsPerUserTypePerYear();


	vm.labels3 = [];
	vm.series3 = [];
	vm.data3 = [];
	vm.csvData3 = [];
	vm.start_date3 = new Date().getFullYear()-2;
	vm.end_date3 = new Date().getFullYear();

	vm.reportsActiveUsersPerYear = function () {
		metricsService.reportsActiveUsersPerYear(vm.start_date3, vm.end_date3).then(function(response){
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
//	vm.reportsActiveUsersPerYear();


	vm.labels4 = [];
	vm.series4 = [];
	vm.data4 = [];
	vm.csvData4 = [];
	vm.year4 = new Date().getFullYear();

	vm.reportsHoursPerEventPerYear = function () {
		metricsService.reportsHoursPerEventPerYear(vm.year4).then(function(response){
			vm.labels4 = response.labels;
			vm.series4 = response.series;
			vm.data4 = response.data;
			vm.csvData4 = response.csvData;
		});
	};
//	vm.reportsHoursPerEventPerYear();

	vm.labels5 = [];
	vm.series5 = [];
	vm.data5 = [];
	vm.csvData6 = [];
	vm.start_date5 = new Date().getFullYear()-2;
	vm.end_date5 = new Date().getFullYear();

	vm.reportsAvgHrsPerGenderPerYear = function () {
		metricsService.reportsAvgHrsPerGenderPerYear(vm.start_date5, vm.end_date5).then(function(response){
			vm.labels5 = response.labels;
			vm.series5 = response.series;
			vm.data5 = response.data;
			vm.csvData5 = response.csvData;
		});
	};
//	vm.reportsAvgHrsPerGenderPerYear();

	vm.labels6 = [];
	vm.series6 = [];
	vm.data6 = [];
	vm.csvData6 = [];
	vm.year6= new Date().getFullYear();

	vm.reportsHoursPerWeek = function () {
		metricsService.reportsHoursPerWeek(vm.year6).then(function(response){
			vm.labels6 = response.labels;
			vm.series6 = response.series;
			vm.data6 = response.data;
			vm.csvData6 = response.csvData;
		});
	};
//	vm.reportsHoursPerWeek();

	vm.labels7 = [];
	vm.series7 = [];
	vm.data7 = [];
	vm.csvData7 = [];
	vm.start_date7 = new Date().getFullYear()-2;
	vm.end_date7 = new Date().getFullYear();

	vm.reportsHoursPerGradePerYear = function () {
		metricsService.reportsHoursPerGradePerYear(vm.start_date7, vm.end_date7).then(function(response){
			vm.labels7 = response.labels;
			vm.series7 = response.series;
			vm.data7 = response.data;
			vm.csvData7 = response.csvData;
		});
	};
	//vm.reportsHoursPerGradePerYear();
}
