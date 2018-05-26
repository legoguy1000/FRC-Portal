angular.module('FrcPortal')
.controller('newEventModalController', ['$log','$element','$mdDialog', '$scope', 'userInfo', 'usersService', 'schoolsService', 'seasonsService','$mdToast','eventData',
	newEventModalController
]);
function newEventModalController($log,$element,$mdDialog,$scope,userInfo,usersService,eventsService,seasonsService,$mdToast,eventData) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.data = eventData;

	vm.loading = {
		searchGoogle: false,
	}

	vm.eventTypes = [
		'Demo',
		'Community Serivce',
		'Season Event',
		'Off Season Event',
		'Other'
	];


	vm.backToSearch = function() {
		$mdDialog.cancel();
	}

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
