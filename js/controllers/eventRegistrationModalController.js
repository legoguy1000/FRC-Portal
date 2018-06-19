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

	vm.selectRoom = function(room_id) {
		var old_room_id = vm.registrationForm.room_id;
		vm.registrationForm.room_id = room_id;
		var len = vm.event.event_rooms.length;
		var user = {};
		var new_room_index = null;
		for (var j = 0; j < len; j++) {
			if(vm.event.event_rooms[j].room_id == room_id) {
				var new_room_index = j;
				break;
			}
		}
		for (var j = 0; j < len; j++) {
			var users = vm.event.event_rooms[j].users;
			var len2 = users.length;
			for (var i = 0; i < len2; i++) {
				if(users[i].user_id == vm.userInfo.user_id) {
					var user = vm.event.event_rooms[j].users[i];
					vm.event.event_rooms[j].users.splice(i,1);
					vm.event.event_rooms[new_room_index].users.push(user);
					break;
				}
			}
		}
	}


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
			vm.registrationForm.event_rooms.users = response;
		}, function() { });
	};

}
