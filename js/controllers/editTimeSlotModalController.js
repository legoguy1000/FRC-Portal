angular.module('FrcPortal')
.controller('editTimeSlotModalController', ['$log','$element','$mdDialog', '$scope', 'eventInfo', 'newTS', 'timeSlotInfo', 'eventsService','$mdToast',
	editTimeSlotModalController
]);
function editTimeSlotModalController($log,$element,$mdDialog,$scope,eventInfo,newTS,timeSlotInfo,eventsService,$mdToast) {
	var vm = this;

	vm.eventInfo = eventInfo;
	vm.newTS = newTS;
	vm.timeSlotInfo = timeSlotInfo;
	vm.loading = false;
	vm.cancel = function() {
		$mdDialog.cancel();
	}

	vm.min_date = moment(vm.eventInfo.date.start.date_time_raw);
	vm.max_date = moment(vm.eventInfo.date.end.date_time_raw);

	vm.submit = function() {
		vm.loading = true;
		var data = {
			'event_id': vm.eventInfo.event_id,
			'name': vm.timeSlotInfo.name,
			'description': vm.timeSlotInfo.description,
			'time_start': vm.timeSlotInfo.time_start,
			'time_end': vm.timeSlotInfo.time_end,
		};
		if(vm.newTS == true) {
			eventsService.addEventTimeSlot(data).then(function(response) {
				if(response.status) {
					$mdDialog.hide(response.data);
				}
				vm.loading = false;
				$mdToast.show(
		      $mdToast.simple()
		        .textContent(response.msg)
		        .position('top right')
		        .hideDelay(3000)
		    );
			});
		} else {
			data.time_slot_id = vm.timeSlotInfo.time_slot_id;
			eventsService.updateEventTimeSlot(data).then(function(response) {
				if(response.status) {
					$mdDialog.hide(response.data);
				}
				vm.loading = false;
				$mdToast.show(
		      $mdToast.simple()
		        .textContent(response.msg)
		        .position('top right')
		        .hideDelay(3000)
		    );
			});
		}
	}


/*
	vm.updateEventRoomList = function (close) {
		vm.loading = true;
		var data = {
			event_id: vm.eventInfo.event_id,
			rooms: vm.room_list.room_selection
		};
		eventsService.updateEventRoomList(data).then(function(response) {
			vm.loading = false;
			if(close) {
				$mdDialog.hide(response);
			}
		});
	};

	vm.addEventRoom = function (close) {
		vm.loading = true;
		var data = vm.newRoom;
		data.event_id = vm.eventInfo.event_id;
		eventsService.addEventRoom(data).then(function(response) {
			vm.loading = false;
			if(response.status) {
				vm.room_list = response.data;
				vm.newRoom = {};
			}
		});
	};
	vm.deleteEventRoom = function (room_id) {
		var data = {
			'room_id': room_id,
			'event_id': vm.eventInfo.event_id,
		}
		vm.loading = true;
		eventsService.deleteEventRoom(data).then(function(response) {
			vm.loading = false;
			if(response.status) {
				vm.room_list = response.data;
			}
		});
	}; */
}
