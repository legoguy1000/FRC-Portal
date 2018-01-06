angular.module('FrcPortal')
.controller('newSeasonModalController', ['$log','$mdDialog', '$scope', 'userInfo', 'usersService', 'schoolsService',
	newSeasonModalController
]);
function newSeasonModalController($log,$mdDialog,$scope,userInfo,usersService,seasonsService) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	
	vm.data = {};
	
	vm.addSeason = function() {
		seasonsService.addSeason(vm.data).then(function(response) {
			if(response.status) {
				$mdDialog.hide(response);
			}
		});
	}
	
	vm.onYearChange = function (newValue, oldValue) {
		$log.log('Meeting changed from ' + oldValue + ' to ' + newValue);
	};
}