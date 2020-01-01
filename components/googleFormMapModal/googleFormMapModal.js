angular.module('FrcPortal')
.controller('googleFormMapModalController', ['$log','$element','$mdDialog', '$scope', 'usersService','$mdToast','seasonInfo',
	googleFormMapModalController
]);
function googleFormMapModalController($log,$element,$mdDialog,$scope,usersService,$mdToast,seasonInfo) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.currentMap = seasonInfo.membership_form_map;
	vm.membership_form_sheet = seasonInfo.membership_form_sheet;


	vm.submit = function() {
		var data = {
			currentMap: vm.currentMap,
			membership_form_sheet: vm.membership_form_sheet
		}
		$mdDialog.hide(data);
	};

}
