angular.module('FrcPortal')
.controller('SeasonHoursGraphModalController', ['$mdDialog', 'usersService', 'data',
	SeasonHoursGraphModalController
]);
function SeasonHoursGraphModalController($mdDialog, usersService, data) {
	var vm = this;

	vm.user_id = data.user_id;
	vm.year = data.year;
	
	vm.options = {legend: {display: true,position: 'bottom'}};
	vm.labels = [];
	vm.series = [];
	vm.data = [];
	var getHours = function() {
		usersService.userHoursbyDate(vm.user_id,vm.year).then(function(response){
			vm.labels = response.data.labels;
			vm.series = response.data.series;
			vm.data = response.data.data;
		});
	}
	getHours();
	
	vm.cancel = function() {
		$mdDialog.cancel();
	}
}