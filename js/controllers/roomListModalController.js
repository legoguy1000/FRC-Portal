angular.module('FrcPortal')
.controller('roomListModalController', ['$log','$element','$mdDialog', '$scope', 'eventInfo', 'usersService', 'schoolsService', 'seasonsService',
	roomListModalController
]);
function roomListModalController($log,$element,$mdDialog,$scope,eventInfo,usersService,eventsService,seasonsService) {
	var vm = this;

	vm.eventInfo = eventInfo;
	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.room_list = {};
	//function get room list
	vm.getEventRoomList = function () {
		vm.promise = eventsService.getEventRoomList(vm.eventInfo.event_id).then(function(response){
			vm.room_list = response.data;
		});
	};
	vm.getEventRoomList();

	vm.updateEventRoomList = function () {
		vm.loading = true;
		var data = {
			event_id: vm.eventInfo.event_id,
			rooms: vm.room_list.room_selection
		};
		eventsService.updateEventRoomList(data).then(function(response){
			vm.loading = false;
		});
	};
}
