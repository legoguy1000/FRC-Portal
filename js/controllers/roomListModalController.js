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
		vm.promise = eventsService.getEventRoomList().then(function(response){
			vm.room_list = response.data;
		});
	};
	vm.getEventRoomList();
}
