angular.module('FrcPortal')
.controller('newEventModalController', ['$log','$element','$mdDialog', '$scope', 'usersService', 'schoolsService', 'seasonsService','$mdToast',
	newEventModalController
]);
function newEventModalController($log,$element,$mdDialog,$scope,usersService,eventsService,seasonsService,$mdToast) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	//vm.data = eventData;
	vm.data = {};
	vm.loading = {
		searchGoogle: false,
	}
	vm.backToSearch = function() {
		$mdDialog.cancel();
	}

	vm.searchEventModal = function() {
		vm.oldData = angular.copy(vm.data);
		vm.data = {};
		$mdDialog.show({
			controller: eventSearchModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/eventSearchModal.tmpl.html',
			parent: angular.element(document.body),
			//targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			multiple: true,
			locals: {
			}
		})
		.then(function(event) {
			vm.data = event;
		}, function() {
			vm.data = angular.copy(vm.oldData);
			$log.info('Dialog dismissed at: ' + new Date());
		});
	}
	vm.searchEventModal();

	vm.getEventTypeList = function () {
		vm.promise =	eventsService.getEventTypes().then(function(response){
			vm.eventTypes = response.data;
		});
	};
	vm.getEventTypeList();

	vm.addEvent = function() {
		eventsService.addEvent(vm.data).then(function(response) {
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
}
