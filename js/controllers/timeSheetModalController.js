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
	vm.dateEdit = null;
	//function time sheeet
	vm.getSignInTimeSheet = function () {
		var date = moment(vm.date).format('YYYY-MM-DD');
		timeService.getSignInTimeSheet(date).then(function(response){
			vm.users = response.data;
		});
	};

	vm.editDate = function (date) {
		vm.dateEdit = date.type_id;
	};
	vm.cancelEdit = function () {
		vm.dateEdit = null;
	};

	vm.updateDate = function (event_type) {
	/*	vm.promise =	eventsService.updateEventType(event_type).then(function(response) {
			if(response.status) {
				vm.eventTypeEdit = null;
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});*/
	};
}
