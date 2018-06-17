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
	vm.myHotelRoom = [];

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
		if(vm.event.time_slots_required) {
			$mdDialog.show({
				controller: timeSlotModalController,
				controllerAs: 'vm',
				templateUrl: 'views/partials/timeSlotModal.tmpl.html',
				parent: angular.element(document.body),
				targetEvent: ev,
				//clickOutsideToClose:true,
				fullscreen: true, // Only for -xs, -sm breakpoints.
				multiple: true,
				locals: {
					eventInfo: {
						'event_id': vm.event.event_id,
						'name':vm.event.name,
						'user_id': vm.userInfo.user_id,
					},
					admin: false,
				}
			})
			.then(function(response) {
				relistTS(response);
			}, function() {
				//relistTS(response);
			});
		}
	};

	function relistTS(allTS) {
		var time_slots = [];
		var len = allTS.length;
		for (var i = 0; i < len; i++) {
			var len2 = allTS[i].registrations.length;
			for (var j = 0; j < len2; j++) {
				if(allTS[i].registrations[j].user_id == vm.userInfo.user_id) {
					time_slots.push(allTS[i]);
				}
			}
		}
		vm.registrationForm.event_time_slots = time_slots;
	}

	vm.showRoomListModal = function(ev) {
		$mdDialog.show({
			controller: roomListModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/roomListModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			multiple: true,
			locals: {
				eventInfo: {
					'event_id': vm.event.event_id,
					'name': vm.event.name,
					'userInfo': vm.userInfo,
					//'room_info': vm.event.room_list
				},
				admin: false,
			}
		})
		.then(function(response) {
			vm.registrationForm.event_requirements.event_rooms.users = response;
		}, function() { });
	};

}
