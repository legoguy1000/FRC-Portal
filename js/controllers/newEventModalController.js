angular.module('FrcPortal')
.controller('newEventModalController', ['$log','$element','$mdDialog', '$scope', 'usersService', 'schoolsService', 'seasonsService','$mdToast','$sce',
	newEventModalController
]);
function newEventModalController($log,$element,$mdDialog,$scope,usersService,eventsService,seasonsService,$mdToast,$sce) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	//vm.data = eventData;
	vm.data = null;
	vm.oldData = null;
	vm.htmlDetails = '';
	vm.startDate = moment();
	vm.loading = {
		searchGoogle: false,
	}
	vm.backToSearch = function() {
		$mdDialog.cancel();
	}

	vm.searchEventModal = function() {
		vm.oldData = angular.copy(vm.data);
		vm.data = null;
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
			vm.htmlDetails = $sce.trustAsHtml(vm.data.details);
		}, function() {
			if(vm.oldData == null) {
				vm.cancel();
			}
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

	$rootScope.$on('400BadRequest', function(event,response) {
		vm.loading = false;
		$mdToast.show(
			$mdToast.simple()
				.textContent(response.msg)
				.position('top right')
				.hideDelay(3000)
		);
	});
}
