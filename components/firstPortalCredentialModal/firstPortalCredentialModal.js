angular.module('FrcPortal')
.controller('firstPortalCredentialModalController', ['$log','$mdDialog', '$scope', 'settingsService','$mdToast',
	firstPortalCredentialModalController
]);
function firstPortalCredentialModalController($log,$mdDialog,$scope,settingsService,$mdToast) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}

	vm.data = {};
	vm.loading = false;

	vm.updateCredentials = function() {
		vm.loading = true;
		settingsService.updateFirstPortalCredentials(vm.data).then(function(response) {
			if(response.status) {
				$mdDialog.hide(response);
			}
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
			vm.loading = false;
		});
	}

	vm.getFirstPortalCredentials = function() {
		vm.loading = true;
		settingsService.getFirstPortalCredentials().then(function(response) {
			if(response.status) {
				vm.data = response.data
			}
			vm.loading = false;
		});
	}
	vm.getFirstPortalCredentials();

}
