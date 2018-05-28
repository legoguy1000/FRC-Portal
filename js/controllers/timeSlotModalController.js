angular.module('FrcPortal')
.controller('timeSlotModalController', ['$log','$element','$mdDialog', '$scope', 'eventInfo', 'admin', 'usersService', 'eventsService',
	timeSlotModalController
]);
function timeSlotModalController($log,$element,$mdDialog,$scope,eventInfo,admin,usersService,eventsService) {
	var vm = this;

	vm.eventInfo = eventInfo;
	vm.admin = admin && $scope.main.userInfo.admin;
	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.time_slots = {};

	//function get room list
	vm.getEventTimeSlotList = function () {
		vm.promise = eventsService.getEventTimeSlotList(vm.eventInfo.event_id).then(function(response) {
			vm.time_slots = response.data;
		});
	};
	vm.getEventTimeSlotList();

	vm.editTimeSlot = function (ev, newTS = true, timeSlotInfo = {}) {
		if(newTS == false) {
			timeSlotInfo.time_end_moment = moment(timeSlotInfo.time_end);
			timeSlotInfo.time_start_moment = moment(timeSlotInfo.time_start);
		}
		$mdDialog.show({
			controller: editTimeSlotModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/editTimeSlotModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			multiple: true,
			locals: {
				eventInfo: vm.eventInfo,
				newTS: newTS,
				timeSlotInfo: timeSlotInfo
			}
		})
		.then(function(response) {
			vm.time_slots = response;
		}, function() {});
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
