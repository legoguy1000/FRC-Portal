angular.module('FrcPortal')
.controller('main.eventController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService',
	mainEventController
]);
function mainEventController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService) {
    var vm = this;

		vm.registrationFormVisible = false;
		vm.state = $state.current.name;



		vm.registrationForm = {};
		vm.event_id = $stateParams.event_id;
		vm.event = {};
		vm.getEvent = function () {
			vm.promise = eventsService.getEvent(vm.event_id).then(function(response){
				vm.event = response.data;
			});
		};

		vm.getEvent();
		vm.limitOptions = [5,10,25,50,100];
		vm.query = {
			filter: '',
			limit: 5,
			order: 'full_name',
			page: 1
		};

		vm.showRegistrationForm = function() {
			var eventInfo = vm.event;
			delete eventInfo.requirements;
			$mdDialog.show({
	      controller: eventRegistrationController,
				controllerAs: 'vm',
	      templateUrl: 'views/partials/eventRegistrationModal.tmpl.html',
	      parent: angular.element(document.body),
	      targetEvent: ev,
	      clickOutsideToClose:true,
	      fullscreen: true, // Only for -xs, -sm breakpoints.
				locals: {
					'eventInfo': eventInfo
				}
	    })
	    .then(function(answer) {

	    }, function() {

	    });
		}
}
