angular.module('FrcPortal')
.controller('newSeasonModalController', ['$log','$mdDialog', '$scope', 'userInfo', 'seasonsService','$mdToast',
	newSeasonModalController
]);
function newSeasonModalController($log,$mdDialog,$scope,userInfo,seasonsService,$mdToast) {
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
		vm.start_min_date = moment(vm.data.year+'0101');
		console.log(vm.data.year+'0101');
	};

	vm.onStartChange = function (newValue, oldValue) {
		vm.end_min_date = moment(vm.data.start_date_full);
		console.log(vm.data.start_date_full);
	};
}
