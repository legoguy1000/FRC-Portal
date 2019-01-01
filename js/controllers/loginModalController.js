angular.module('FrcPortal')
.controller('loginModalController', ['$auth', '$mdDialog', '$window', 'configItems', '$mdToast',
	loginModalController
]);
function loginModalController($auth,$mdDialog,$window, configItems, $mdToast) {
	var vm = this;

	vm.configItems = configItems;
	vm.loading = false;
	vm.loginForm = {};
	vm.login = function () {
		vm.loading = true;
		$auth.login(vm.loginForm).then(function(response) {
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.data.msg)
					.position('top right')
					.hideDelay(3000)
			);
			vm.loading = false;
			var authed = $auth.isAuthenticated();
			if(authed) {
				$window.localStorage['userInfo'] = angular.toJson(response.data.userInfo);
				var data = {
					'auth': true,
					'userInfo': response.data.userInfo,
				}
				$mdDialog.hide(data);
			}
		});
  };

	vm.authenticate = function(provider) {
		vm.loading = true;
		$auth.authenticate(provider).then(function(response) {
		//	toastr[response.data.type](response.data.msg, 'Login');
			//alert(response.data.msg)
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.data.msg)
					.position('top right')
					.hideDelay(3000)
			);
			vm.loading = false;
			var authed = $auth.isAuthenticated();
			if(authed) {
				$window.localStorage['userInfo'] = angular.toJson(response.data.userInfo);
				var data = {
					'auth': true,
					'userInfo': response.data.userInfo,
				}
				$mdDialog.hide(data);
			}
		});
  };

	vm.cancel = function() {
		$mdDialog.cancel();
	}
}
