angular.module('FrcPortal')
.controller('googleFormMapModalController', ['$log','$element','$mdDialog', '$scope', 'usersService','$mdToast','seasonInfo',
	googleFormMapModalController
]);
function serviceAccountModalController($log,$element,$mdDialog,$scope,usersService,$mdToast,seasonInfo) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.currentMap = seasonInfo.membership_form_map;


	vm.submit = function() {
		$mdDialog.hide(vm.currentMap);
	};

}
