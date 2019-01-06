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

	vm.oauth_urls = {
		google: '',
		facebook: '',
		microsoft: '',
		amazon: '',
		github: '',
	}
	//Google
	var hdBool = configItems.require_team_email && configItems.team_domain != '';
	var hdVar = hdBool ?  : '';
	var googleData = {
		clientId: configItems.google_oauth_client_id,
		authorizationEndpoint: 'https://accounts.google.com/o/oauth2/auth',
		redirectUri: window.location.origin+'/oauth/google',
		scope: ['openid', 'profile', 'email'],
		scopeDelimiter: ' ',
		hd: hdBool ? '&hd='+configItems.team_domain : '',
	}
	vm.oauth_urls.google = googleData.authorizationEndpoint+'?scope='+googleData.scope.join(googleData.scopeDelimiter)+'&redirect_uri='+googleData.redirectUri+'&response_type=code&client_id='+googleData.clientId+googleData.hd;
	//Facebook
	var facebookData = {
		clientId: configItems.facebook_oauth_client_id,
		authorizationEndpoint: 'https://www.facebook.com/v3.0/dialog/oauth',
		redirectUri: window.location.origin+'/oauth/facebook',
		scope: ['public_profile','email'],
		auth_type: 'rerequest',
		scopeDelimiter: ',',
	}
	vm.oauth_urls.facebook = facebookData.authorizationEndpoint+'?scope='+facebookData.scope.join(facebookData.scopeDelimiter)+'&redirect_uri='+facebookData.redirectUri+'&response_type=code&client_id='+facebookData.clientId;
	//microsoft
	var microsoftData = {
		clientId: configItems.microsoft_oauth_client_id,
		authorizationEndpoint: 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
		redirectUri: window.location.origin+'/oauth/microsoft',
		scope: ['openid','email',' profile','User.Read'],
		scopeDelimiter: ' ',
		//display: 'popup',
	}
	vm.oauth_urls.microsoft = microsoftData.authorizationEndpoint+'?scope='+microsoftData.scope.join(microsoftData.scopeDelimiter)+'&redirect_uri='+microsoftData.redirectUri+'&response_type=code&client_id='+microsoftData.clientId;





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
