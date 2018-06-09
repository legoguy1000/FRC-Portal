angular.module('FrcPortal')
.controller('timeSheetModalController', ['$log','$element','$mdDialog', '$scope', 'timeService','$mdToast',
	timeSheetModalController
]);
function timeSheetModalController($log,$element,$mdDialog,$scope,timeService,$mdToast) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.users = [];
	vm.loading = false;
	vm.date = null;
	//function time sheeet
	vm.getSignInTimeSheet = function () {
		var date = moment(vm.date).format('YYYY-MM-DD');
		timeService.getSignInTimeSheet(date).then(function(response){
			vm.users = response.data;
		});
	};

}
