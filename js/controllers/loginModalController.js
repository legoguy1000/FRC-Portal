angular.module('FrcPortal')
.controller('loginModalController', ['$rootScope','$auth', '$mdDialog', '$window', 'configItems', '$mdToast', 'loginData','$state','$stateParams',
	loginModalController
]);
function loginModalController($rootScope,$auth,$mdDialog,$window, configItems, $mdToast, loginData, $state, $stateParams) {
	var vm = this;

	vm.configItems = configItems;
	vm.loading = loginData.loading != undefined ? loginData.loading:false;
	vm.link_accounts = loginData.link_accounts != undefined ? loginData.link_accounts:false;
	vm.state = loginData.state != undefined ? loginData.state:$state.current.name;
	var state_params = $stateParams;
	delete state_params['#'];
	vm.state_params = loginData.state_params != undefined ? loginData.state_params:state_params;
	vm.state_from = loginData.state_from != undefined ? loginData.state_from:null;
	vm.urlState = {
		'current_state': vm.state,
		'state_params': state_params,
		'state_from': vm.state_from
	};

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
				$rootScope.$broadcast('afterLoginAction');
				$state.go(vm.state, vm.state_params);
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
	var googleData = {
		clientId: configItems.google_oauth_client_id,
		authorizationEndpoint: 'https://accounts.google.com/o/oauth2/auth',
		redirectUri: window.location.origin+'/oauth/google',
		scope: ['openid', 'profile', 'email'],
		scopeDelimiter: ' ',
		hd: hdBool ? '&hd='+configItems.team_domain : '',
	  state: vm.urlState,
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
	  state: vm.urlState,
	}
	vm.oauth_urls.facebook = facebookData.authorizationEndpoint+'?scope='+facebookData.scope.join(facebookData.scopeDelimiter)+'&redirect_uri='+facebookData.redirectUri+'&response_type=code&client_id='+facebookData.clientId+'&state='+JSON.stringify(facebookData.state);
	//microsoft
	var microsoftData = {
		clientId: configItems.microsoft_oauth_client_id,
		authorizationEndpoint: 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
		redirectUri: window.location.origin+'/oauth/microsoft',
		scope: ['openid','email',' profile','User.Read'],
		scopeDelimiter: ' ',
	  state: vm.urlState,
	}
	vm.oauth_urls.microsoft = microsoftData.authorizationEndpoint+'?scope='+microsoftData.scope.join(microsoftData.scopeDelimiter)+'&redirect_uri='+microsoftData.redirectUri+'&response_type=code&client_id='+microsoftData.clientId+'&state='+JSON.stringify(microsoftData.state);
	//github
	var githubData = {
	  clientId: configItems.github_oauth_client_id,
	  redirectUri: window.location.origin+'/oauth/github',
	  authorizationEndpoint: 'https://github.com/login/oauth/authorize',
	  scope: ['read:user', 'user:email'],
	  scopeDelimiter: ' ',
	  state: vm.urlState,
	}
	vm.oauth_urls.github = githubData.authorizationEndpoint+'?scope='+githubData.scope.join(githubData.scopeDelimiter)+'&redirect_uri='+githubData.redirectUri+'&response_type=code&client_id='+githubData.clientId+'&state='+JSON.stringify(githubData.state);
	//amazon
	var amazonData = {
	  clientId: configItems.amazon_oauth_client_id,
	  redirectUri: window.location.origin+'/oauth/amazon',
		authorizationEndpoint: 'https://www.amazon.com/ap/oa',
		scope: ['profile'],
	  scopeDelimiter: ' ',
	  state: vm.urlState,
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
