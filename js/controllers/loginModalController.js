angular.module('FrcPortal')
.controller('loginModalController', ['$auth', '$mdDialog', '$window', 'configItems',
	loginModalController
]);
function loginModalController($auth,$mdDialog,$window, configItems) {
	var vm = this;

	vm.configItems = configItems;

	vm.loginForm = {};
	vm.login = function () {
		$auth.login(vm.loginForm).then(function(response) {
			alert(response.data.msg)
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
		$auth.authenticate(provider).then(function(response) {
		//	toastr[response.data.type](response.data.msg, 'Login');
			alert(response.data.msg)
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
