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

	

}
