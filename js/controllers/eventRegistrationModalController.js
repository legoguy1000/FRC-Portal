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
		data.user_id = vm.userInfo.user_id;
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
		usersService.getUserEventRequirements(vm.userInfo.user_id,vm.event.event_id).then(function(response){
			if(response.status) {
				vm.registrationForm = response.data.event_requirements;
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

	vm.showTimeSlotListModal = function(ev) {
		$mdDialog.show({
			controller: timeSlotModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/timeSlotModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			multiple: true,
			locals: {
				eventInfo: {
					'event_id': vm.event_id,
					'name':vm.event.name,
				},
				admin: false,
			}
		})
		.then(function(response) {
			//vm.users = response.data;
		}, function() { });
	};


}
