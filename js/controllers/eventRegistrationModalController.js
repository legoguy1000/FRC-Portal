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

	vm.room_list = [];
	vm.time_slots = [];

	vm.cancel = function() {
		$mdDialog.cancel();
	}

	vm.registerForEvent = function () {
		vm.loading = true;
		var data = {
			'event_id': vm.event.event_id,
			'user_id': vm.userInfo.user_id,
			'registration': vm.registrationForm.registration,
			'can_drive': vm.registrationForm.can_drive,
			'event_cars': {
				'car_space': null,
			},
			'comments': vm.registrationForm.comments,
			'room_id': vm.registrationForm.room_id,
			'event_time_slots': vm.registrationForm.event_time_slots,
		};
		data.event_cars.car_space = vm.userInfo.user_type == 'Mentor' && vm.registrationForm.event_cars != undefined && vm.registrationForm.event_cars.car_space != undefined ? vm.registrationForm.event_cars.car_space:null;
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

	//function get room list
	vm.getEventRoomList = function () {
		vm.promise = eventsService.getEventRoomList(vm.event.event_id).then(function(response) {
			vm.room_list = response.data;
		});
	};
	vm.getEventRoomList();

	//get time slots
	vm.getEventTimeSlotList = function () {
		eventsService.getEventTimeSlotList(vm.event.event_id).then(function(response) {
			vm.time_slots = response.data;
		});
	};
	vm.getEventTimeSlotList();

	vm.checkTSReg = function(ts_i) {
		var index = false;
		if(vm.event.time_slots_required) {
			var regs = vm.time_slots[ts_i].registrations;
			var len = regs.length;
			for (var i = 0; i < len; i++) {
				if(regs[i].user_id == vm.userInfo.user_id) {
					index = true;
					break;
				}
			}
		}
		return index;
	}

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
		var len = vm.room_list.length;
		var user = vm.userInfo;
		var new_room_index = null;
		for (var j = 0; j < len; j++) {
			if(vm.room_list[j].room_id == room_id) {
				var new_room_index = j;
				break;
			}
		}
		if(old_room_id != null) {
			for (var j = 0; j < len; j++) {
				var users = vm.room_list[j].users;
				var len2 = users.length;
				for (var i = 0; i < len2; i++) {
					if(users[i].user_id == vm.userInfo.user_id) {
						var user = vm.room_list[j].users[i];
						vm.room_list[j].users.splice(i,1);
						vm.room_list[new_room_index].users.push(user);
						break;
					}
				}
			}
		} else {
			vm.room_list[new_room_index].users.push(user);
		}
	}

	vm.selectTimeSlot = function(ts_i) {
		var add_remove = vm.checkTSReg(ts_i);
		if(!add_remove) {
			var new_ts = angular.copy(vm.time_slots[ts_i]);
			delete new_ts.registrations;
			vm.registrationForm.event_time_slots.push(new_ts);
			vm.time_slots[ts_i].registrations.push({
				'user_id': vm.userInfo.user_id,
				'user': vm.userInfo,
			});
		} else {
			var ts_id = vm.time_slots[ts_i].time_slot_id;
			var my_ts = vm.registrationForm.event_time_slots;
			var len = my_ts.length;
			for (var j = 0; j < len; j++) {
				if(my_ts[j].time_slot_id == ts_id) {
					vm.registrationForm.event_time_slots.splice(j,1);
					break;
				}
			}
			var regs = vm.time_slots[ts_i].registrations;
			var len = regs.length;
			for (var j = 0; j < len; j++) {
				if(regs[j].user.user_id == vm.userInfo.user_id) {
					vm.time_slots[ts_i].registrations.splice(j,1);
					break;
				}
			}
		}
	}

/*
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
*/
}
