angular.module('FrcPortal')
.controller('loginModalController', ['$auth', '$mdDialog', '$window', 'configItems', '$mdToast', 'loading','$state','$stateParams',
	loginModalController
]);
function loginModalController($auth,$mdDialog,$window, configItems, $mdToast, loading, $state, $stateParams) {
	var vm = this;

	vm.configItems = configItems;
	vm.loading = loading != undefined ? loading:false;
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
	var state_params = $stateParams;
	delete state_params['#'];
	//Google
	var hdBool = configItems.require_team_email && configItems.team_domain != '';
	var googleData = {
		clientId: configItems.google_oauth_client_id,
		authorizationEndpoint: 'https://accounts.google.com/o/oauth2/auth',
		redirectUri: window.location.origin+'/oauth/google',
		scope: ['openid', 'profile', 'email'],
		scopeDelimiter: ' ',
		hd: hdBool ? '&hd='+configItems.team_domain : '',
	  state: {
			'current_state': $state.current.name,
			'state_params': state_params
		},
	}
	vm.oauth_urls.google = googleData.authorizationEndpoint+'?scope='+googleData.scope.join(googleData.scopeDelimiter)+'&redirect_uri='+googleData.redirectUri+'&response_type=code&client_id='+googleData.clientId+'&state='+JSON.stringify(googleData.state)+googleData.hd;
	//Facebook
	var facebookData = {
		clientId: configItems.facebook_oauth_client_id,
		authorizationEndpoint: 'https://www.facebook.com/v3.0/dialog/oauth',
		redirectUri: window.location.origin+'/oauth/facebook',
		scope: ['public_profile','email'],
		auth_type: 'rerequest',
		scopeDelimiter: ',',
	  state: {
			'current_state': $state.current.name,
			'state_params': state_params
		},
	}
	vm.oauth_urls.facebook = facebookData.authorizationEndpoint+'?scope='+facebookData.scope.join(facebookData.scopeDelimiter)+'&redirect_uri='+facebookData.redirectUri+'&response_type=code&client_id='+facebookData.clientId+'&state='+JSON.stringify(facebookData.state);
	//microsoft
	var microsoftData = {
		clientId: configItems.microsoft_oauth_client_id,
		authorizationEndpoint: 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
		redirectUri: window.location.origin+'/oauth/microsoft',
		scope: ['openid','email',' profile','User.Read'],
		scopeDelimiter: ' ',
	  state: {
			'current_state': $state.current.name,
			'state_params': state_params
		},
	}
	vm.oauth_urls.microsoft = microsoftData.authorizationEndpoint+'?scope='+microsoftData.scope.join(microsoftData.scopeDelimiter)+'&redirect_uri='+microsoftData.redirectUri+'&response_type=code&client_id='+microsoftData.clientId+'&state='+JSON.stringify(microsoftData.state);
	//github
	var githubData = {
	  clientId: configItems.github_oauth_client_id,
	  redirectUri: window.location.origin+'/oauth/github',
	  authorizationEndpoint: 'https://github.com/login/oauth/authorize',
	  scope: ['read:user', 'user:email'],
	  scopeDelimiter: ' ',
	  state: {
			'current_state': $state.current.name,
			'state_params': state_params
		},
	}
	vm.oauth_urls.github = githubData.authorizationEndpoint+'?scope='+githubData.scope.join(githubData.scopeDelimiter)+'&redirect_uri='+githubData.redirectUri+'&response_type=code&client_id='+githubData.clientId+'&state='+JSON.stringify(githubData.state);
	//amazon
	var amazonData = {
	  clientId: configItems.amazon_oauth_client_id,
	  redirectUri: window.location.origin+'/oauth/amazon',
		authorizationEndpoint: 'https://www.amazon.com/ap/oa',
		scope: ['profile'],
	  scopeDelimiter: ' ',
	  state: {
			'current_state': $state.current.name,
			'state_params': state_params
		},
	}
	vm.oauth_urls.amazon = amazonData.authorizationEndpoint+'?scope='+amazonData.scope.join(amazonData.scopeDelimiter)+'&redirect_uri='+amazonData.redirectUri+'&response_type=code&client_id='+amazonData.clientId+'&state='+JSON.stringify(amazonData.state);




/*
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
  }; */

	vm.cancel = function() {
		$mdDialog.cancel();
	}
}
