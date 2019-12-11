angular.module('FrcPortal')
.controller('oAuthCredentialModalController', ['$log','$mdDialog', '$scope', 'settingsService','$mdToast','provider',
	oAuthCredentialModalController
]);
function oAuthCredentialModalController($log,$mdDialog,$scope,settingsService,$mdToast,provider) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}

	vm.data = {};
	vm.loading = false;

	vm.updateCredentials = function() {
		vm.loading = true;
		var data = {
			'client_id': vm.data.client_id,
			'client_secret': vm.data.client_secret,
			'provider': provider,
		};
		settingsService.updateOAuthCredentialsByProvider(data).then(function(response) {
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

	vm.getOAuthCredentialsByProvider = function() {
		vm.loading = true;
		settingsService.getOAuthCredentialsByProvider(provider).then(function(response) {
			if(response.status) {
				vm.data = response.data
			} else {
				$mdToast.show(
					$mdToast.simple()
						.textContent(response.msg)
						.position('top right')
						.hideDelay(3000)
				);
			}
			vm.loading = false;
		});
	}
	vm.getOAuthCredentialsByProvider();
}
