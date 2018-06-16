angular.module('FrcPortal')
.controller('roomListModalController', ['$log','$element','$mdDialog', '$scope', 'eventInfo', 'usersService', 'schoolsService', 'seasonsService','admin',
	roomListModalController
]);
function roomListModalController($log,$element,$mdDialog,$scope,eventInfo,usersService,eventsService,seasonsService,admin) {
	var vm = this;

	vm.eventInfo = eventInfo;
	vm.admin = admin && $auth.getPayload().data.admin;
	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.room_list = {};
	vm.newRoom = {};
	vm.newRoomOpts = [
		{
			'user_type':'Student',
			'gender':'Male'
		},
		{
			'user_type':'Student',
			'gender':'Female'
		},
		{
			'user_type':'Mentor',
			'gender':''
		}
	];
	//function get room list
	vm.getEventRoomList = function () {
		vm.promise = eventsService.getEventRoomList(vm.eventInfo.event_id).then(function(response) {
			vm.room_list = response.data;
		});
	};
	vm.getEventRoomList();

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
	};

	vm.toggleRoomSelect = function(time_slot_id) {
		var data = {
			'user_id': vm.eventInfo.user_id,
			'time_slot_id': time_slot_id,
		};
		usersService.toggleRegistrationEventTimeSlot(data).then(function(response) {
			if(response.status) {
				vm.time_slots = response.data;
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
