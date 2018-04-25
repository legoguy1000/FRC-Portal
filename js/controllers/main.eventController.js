angular.module('FrcPortal')
.controller('main.eventController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService',
	mainEventController
]);
function mainEventController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService) {
    var vm = this;

		vm.registrationFormVisible = false;
		vm.state = $state.current.name;

		vm.showRegistrationForm = function() {
			vm.registrationFormVisible = !vm.registrationFormVisible;
			if(vm.registrationForm1.$dirty) {
				vm.registrationForm1.$setPristine();
			}
		}

		vm.registrationForm = {};
		vm.event_id = $stateParams.event_id;
		vm.event = {};
		vm.getEvent = function () {
			vm.promise = eventsService.getEvent(vm.event_id).then(function(response){
				vm.event = response.data;
			});
		};

		vm.registerForEvent = function () {
			var data = vm.registrationForm;
			data.event_id = vm.event.event_id;
			eventsService.registerForEvent(data).then(function(response){
				//vm.event = response.data;
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

	vm.range = function(min, max, step) {
	    step = step || 1;
	    var input = [];
	    for (var i = min; i <= max; i += step) {
	        input.push(i);
	    }
	    return input;
	};
}
