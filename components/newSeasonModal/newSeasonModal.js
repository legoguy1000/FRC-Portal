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
	vm.loading = false;
	vm.addSeason = function() {
		vm.loading = true;
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
			vm.loading = false;
		});
	}

	vm.onYearChange = function (newValue, oldValue) {
		vm.start_min_date = moment(vm.data.year+'0101');
		vm.data.start_date_full = moment().startOf('month').set({'year':vm.data.year, 'month':0, 'isoweekday':6});
		vm.data.end_date_full = moment().endOf('month').set({'year':vm.data.year, 'month':3});
	};

	vm.onStartChange = function (newValue, oldValue) {
		vm.end_min_date = moment(vm.data.start_date_full).add(1, 'day');
	};
}
