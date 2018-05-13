angular.module('FrcPortal')
.controller('carListModalController', ['$log','$element','$mdDialog', '$scope', 'eventInfo', 'usersService', 'schoolsService', 'seasonsService','$mdToast',
	carListModalController
]);
function carListModalController($log,$element,$mdDialog,$scope,eventInfo,usersService,eventsService,seasonsService,$mdToast) {
	var vm = this;

	vm.eventInfo = eventInfo;
	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.car_list = {};
	vm.loading = false;
	//function get room list
	vm.getEventCarList = function () {
		vm.loading = true;
		eventsService.getEventCarList(vm.eventInfo.event_id).then(function(response){
			vm.car_list = response.data;
			vm.loading = false;
		});
	};
	vm.getEventCarList();

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
	};
}
