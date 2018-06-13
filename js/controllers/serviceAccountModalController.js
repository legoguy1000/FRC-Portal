angular.module('FrcPortal')
.controller('serviceAccountModalController', ['$log','$element','$mdDialog', '$scope', 'usersService','$mdToast','credentials',
	serviceAccountModalController
]);
function serviceAccountModalController($log,$element,$mdDialog,$scope,usersService,$mdToast,credentials) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.credentials = credentials;

	vm.uploadFile = function () {
		
	};

	vm.close = function() {
		$mdDialog.hide(vm.credentials);
	}
}
