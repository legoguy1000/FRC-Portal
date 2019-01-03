angular.module('FrcPortal')
.controller('newSeasonModalController', ['$log','$mdDialog', '$scope', 'userInfo', 'usersService', 'schoolsService','$mdToast',
	newSeasonModalController
]);
function newSeasonModalController($log,$mdDialog,$scope,userInfo,usersService,seasonsService,$mdToast) {
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
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
		});
	}

	vm.onYearChange = function (newValue, oldValue) {
		vm.start_min_date = moment(vm.data.year+'01-01');
	};
}
