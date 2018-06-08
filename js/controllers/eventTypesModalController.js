angular.module('FrcPortal')
.controller('eventTypesModalController', ['$log','$element','$mdDialog', '$scope', 'eventsService','$mdToast',
	eventTypesModalController
]);
function eventTypesModalController($log,$element,$mdDialog,$scope,eventsService,$mdToast) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.event_types = [];
	vm.query = {
		filter: '',
		limit: 10,
		order: '-student_count',
		page: 1
	};
	vm.limitOptions = [10,25,50,100];
	vm.loading = false;
	//function get room list
	vm.getEventTypeList = function () {
		vm.loading = true;
		vm.promise =	eventsService.getEventTypes().then(function(response){
			vm.event_types = response.data;
			vm.loading = false;
		});
	};
	vm.getEventTypeList();

/*
	vm.updateEventCarList = function (close) {
		vm.loading = true;
		var data = {
			event_id: vm.eventInfo.event_id,
			cars: vm.car_list.car_selection
		};
		eventsService.updateEventCarList(data).then(function(response){
			vm.loading = false;
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
			if(close) {
				$mdDialog.hide(response);
			}
		});
	}; */

}
