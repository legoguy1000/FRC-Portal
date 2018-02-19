angular.module('FrcPortal')
.controller('roomListModalController', ['$log','$element','$mdDialog', '$scope', 'userInfo', 'usersService', 'schoolsService', 'seasonsService',
	roomListModalController
]);
function roomListModalController($log,$element,$mdDialog,$scope,userInfo,usersService,eventsService,seasonsService) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}

	

}
