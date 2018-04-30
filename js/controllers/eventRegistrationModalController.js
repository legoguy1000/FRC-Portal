angular.module('FrcPortal')
.controller('eventRegistrationController', ['$log','$element','$mdDialog', '$scope', 'eventInfo', 'usersService', 'schoolsService', 'seasonsService','eventInfo','userInfo','$mdToast',
	eventRegistrationController
]);
function eventRegistrationController($log,$element,$mdDialog,$scope,eventInfo,usersService,eventsService,seasonsService,eventInfo,userInfo,$mdToast) {
	var vm = this;

	vm.event = eventInfo;
	vm.userInfo = userInfo;
	vm.registrationForm = {};
	vm.loading = false;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	
	vm.registerForEvent = function () {
		vm.loading = true;
		var data = vm.registrationForm;
		data.event_id = vm.event.event_id;
		eventsService.registerForEvent(data).then(function(response){
			if(response.status) {
				$mdDialog.hide(response);
			}
			vm.loading = false;
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
		});
	};

	vm.getEventRegistrationStatus = function () {
		vm.loading = true;
		eventsService.getEventRegistrationStatus(vm.event.event_id,vm.userInfo.user_id).then(function(response){
			if(response.status) {
				vm.registrationForm = response.data[0];
			}
			vm.loading = false;
		});
	};
	vm.getEventRegistrationStatus();

	vm.range = function(min, max, step) {
			step = step || 1;
			var input = [];
			for (var i = min; i <= max; i += step) {
					input.push(i);
			}
			return input;
	};
}
