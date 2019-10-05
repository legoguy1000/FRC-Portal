angular.module('FrcPortal')
.controller('roomListModalController', ['$rootScope','$log','$element','$mdDialog', '$scope', 'eventInfo', 'usersService', 'schoolsService', 'seasonsService','admin','$auth','$mdToast',
	roomListModalController
]);
function roomListModalController($rootScope,$log,$element,$mdDialog,$scope,eventInfo,usersService,eventsService,seasonsService,admin,$auth,$mdToast) {
	var vm = this;

	vm.eventInfo = eventInfo;
	vm.userInfo = vm.eventInfo.userInfo;
	vm.admin = admin && $auth.getPayload().data.admin;
	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.room_list = {};
	vm.newRoom = {};
	vm.myRoom = {};
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
			'user_type':'Adult',
			'gender':''
		}
	];
	//function get room list
	vm.getEventRoomList = function () {
		vm.promise = eventsService.getEventAdminRoomList(vm.eventInfo.event_id).then(function(response) {
			vm.room_list = response.data;
		});
	};
	vm.getEventRoomList();

	vm.updateEventRoomList = function (close) {
		vm.loading = true;
		var rooms = vm.room_list.rooms.map(x=> x.users);
		rooms.non_select = vm.room_list.non_select;
		var data = {
			event_id: vm.eventInfo.event_id,
			rooms: rooms
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

	vm.toggleRoomSelect = function(room_id) {
		vm.loading = true;
		var data = {
			'user_id': vm.userInfo.user_id,
			'event_id': vm.eventInfo.event_id,
			'room_id': room_id,
		};
		usersService.registerEventRoom(data).then(function(response) {
			if(response.status) {
				vm.room_list = response.data;
				vm.userInfo.event_requirements.room_id = room_id;
				vm.myRoom = response.myRoom;
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

	vm.checkReg = function(room_id) {
		var index = false;
		if(!vm.admin) {
			var room = vm.room_list.room_selection[room_id];
			var len = room.length;
			for (var i = 0; i < len; i++) {
				if(room[i].user_id == vm.userInfo.user_id) {
					index = true;
					break;
				}
			}
		}
		return index;
	}

	vm.save = function() {
		$mdDialog.hide(vm.myRoom.users);
	}

	$rootScope.$on('400BadRequest', function(event,response) {
		vm.loading = false;
		$mdToast.show(
			$mdToast.simple()
				.textContent(response.msg)
				.position('top right')
				.hideDelay(3000)
		);
	});
}
