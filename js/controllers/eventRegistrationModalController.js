angular.module('FrcPortal')
.controller('eventRegistrationController', ['$log','$element','$mdDialog', '$scope', 'eventInfo', 'usersService', 'schoolsService', 'seasonsService','eventInfo','userInfo',
	eventRegistrationController
]);
function eventRegistrationController($log,$element,$mdDialog,$scope,eventInfo,usersService,eventsService,seasonsService,eventInfo,userInfo) {
	var vm = this;

	vm.eventInfo = eventInfo;
	vm.userInfo = userInfo;
	vm.cancel = function() {
		$mdDialog.cancel();
	}

	vm.registrationForm = {};
	vm.registerForEvent = function () {
		var data = vm.registrationForm;
		data.event_id = vm.event.event_id;
		eventsService.registerForEvent(data).then(function(response){
			//vm.event = response.data;
		});
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
