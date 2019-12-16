angular.module('FrcPortal')
.controller('loginModalController', ['$rootScope','$auth', '$mdDialog', '$window', 'configItems', '$mdToast', 'loginData','$state','$stateParams', 'loginService', 'webauthnService',
	loginModalController
]);
function loginModalController($rootScope,$auth,$mdDialog,$window, configItems, $mdToast, loginData, $state, $stateParams, loginService, webauthnService) {
	var vm = this;

	vm.configItems = configItems;
	vm.loading = loginData.loading != undefined ? loginData.loading:false;
	vm.linkedAccounts = loginData.state_params != undefined && loginData.state_params.linkedAccounts != undefined ? loginData.state_params.linkedAccounts:false;
	vm.state = loginData.state != undefined ? loginData.state:$state.current.name;
	var state_params = $stateParams;
	delete state_params['#'];
	vm.state_params = loginData.state_params != undefined ? loginData.state_params:state_params;
	vm.state_from = loginData.state_from != undefined ? loginData.state_from:null;
	vm.urlState = {
		'current_state': vm.state,
		'state_params': vm.state_params,
		'state_from': vm.state_from
	};
	vm.urlStateEncode = btoa(JSON.stringify(vm.urlState));
	vm.showlocallogin = false;
	vm.webauthn = $window.localStorage['webauthn_cred'] != null && $window.localStorage['webauthn_cred'] != undefined && !loginData.oauth && !vm.linkedAccounts;

	vm.loginForm = {};
	vm.webauthnLogin = function () {
		vm.loading = true;
		if(vm.webauthn) {
			var cred = angular.fromJson(window.localStorage['webauthn_cred']);
			webauthnService.getAuthenticationOptions(cred.user).then(response => {
				console.log('creating creds');
				var allowCredentials = response.allowCredentials == undefined ? [] : response.allowCredentials.map(function(val){
					var temp = val;
					var unsafeBase64 = atob(val.id.replace(/_/g, '/').replace(/-/g, '+'));
					temp.id = Uint8Array.from(unsafeBase64, c=>c.charCodeAt(0));
					return temp;
				})
				var publicKey = {
					challenge: Uint8Array.from(response.challenge, c=>c.charCodeAt(0)),
					allowCredentials: allowCredentials,
					authenticatorSelection: {
							authenticatorAttachment: "platform",
							userVerification: "preferred",
					},
				}
				console.log(publicKey);
				return navigator.credentials.get({ 'publicKey': publicKey });
			}).then(assertion => {
				console.log('SUCCESS', assertion);
				// Move data into Arrays incase it is super long
		    let authenticatorData = new Uint8Array(assertion.response.authenticatorData);
		    let attestationObject = new Uint8Array(assertion.response.attestationObject);
				let clientDataJSON = new Uint8Array(assertion.response.clientDataJSON);
				let signature = new Uint8Array(assertion.response.signature);
		    let userHandle = new Uint8Array(assertion.response.userHandle);
		    let rawId = new Uint8Array(assertion.rawId);
				var data = {
					id: assertion.id,
          rawId: webauthnService.bufferEncode(rawId),
          type: assertion.type,
          response: {
						authenticatorData: webauthnService.bufferEncode(authenticatorData),
            attestationObject: webauthnService.bufferEncode(attestationObject),
						clientDataJSON: webauthnService.bufferEncode(clientDataJSON),
						signature: webauthnService.bufferEncode(signature),
            userHandle: atob(webauthnService.bufferEncode(userHandle)),
          },
				};
				$window.localStorage['webauthn_cred'] = angular.toJson({
					credential_id: assertion.id,
					type: assertion.type,
					user: cred.user
				});
				return webauthnService.authenticate(data);
			}, error => {
				console.log(error);
				vm.loading = false;
			}).then(response => {
				vm.loading = false;
				$mdToast.show(
					$mdToast.simple()
						.textContent(response.msg)
						.position('top right')
						.hideDelay(3000)
				);
				var authed = $auth.isAuthenticated();
				if(authed) {
					$window.localStorage['userInfo'] = angular.toJson(response.userInfo);
					var data = {
						'auth': true,
						'userInfo': response.userInfo,
					}
					$rootScope.$emit('afterLoginAction',{loginType: 'webauthn'});
					$state.go(vm.state, vm.state_params);
					$mdDialog.hide(data);
				}
			});
		} else {
			vm.loading = false;
		}
  }
	if(vm.webauthn) {
		vm.webauthnLogin();
	}

	vm.login = function () {
		vm.loading = true;
		loginService.localadmin(vm.loginForm).then(function(response) {
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
			vm.loading = false;
			var authed = $auth.isAuthenticated();
			if(authed) {
				$window.localStorage['userInfo'] = angular.toJson(response.userInfo);
				var data = {
					'auth': true,
					'userInfo': response.userInfo,
				}
				$rootScope.$emit('afterLoginAction',{loginType: 'local_admin'});
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
		discord: '',
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
	}
	vm.oauth_urls.google = googleData.authorizationEndpoint+'?scope='+googleData.scope.join(googleData.scopeDelimiter)+'&redirect_uri='+googleData.redirectUri+'&response_type=code&client_id='+googleData.clientId+'&state='+vm.urlStateEncode+googleData.hd;
	//Facebook
	var facebookData = {
		clientId: configItems.facebook_oauth_client_id,
		authorizationEndpoint: 'https://www.facebook.com/v3.2/dialog/oauth',
		redirectUri: window.location.origin+'/oauth/facebook',
		scope: ['public_profile','email'],
		auth_type: 'rerequest',
		scopeDelimiter: ',',
	}
	vm.oauth_urls.facebook = facebookData.authorizationEndpoint+'?scope='+facebookData.scope.join(facebookData.scopeDelimiter)+'&redirect_uri='+facebookData.redirectUri+'&response_type=code&client_id='+facebookData.clientId+'&state='+vm.urlStateEncode;
	//microsoft
	var microsoftData = {
		clientId: configItems.microsoft_oauth_client_id,
		authorizationEndpoint: 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
		redirectUri: window.location.origin+'/oauth/microsoft',
		scope: ['openid','email',' profile','User.Read'],
		scopeDelimiter: ' ',
	}
	vm.oauth_urls.microsoft = microsoftData.authorizationEndpoint+'?scope='+microsoftData.scope.join(microsoftData.scopeDelimiter)+'&redirect_uri='+microsoftData.redirectUri+'&response_type=code&client_id='+microsoftData.clientId+'&state='+vm.urlStateEncode;
	//github
	var githubData = {
	  clientId: configItems.github_oauth_client_id,
	  redirectUri: window.location.origin+'/oauth/github',
	  authorizationEndpoint: 'https://github.com/login/oauth/authorize',
	  scope: ['read:user', 'user:email'],
	  scopeDelimiter: ' ',
	}
	vm.oauth_urls.github = githubData.authorizationEndpoint+'?scope='+githubData.scope.join(githubData.scopeDelimiter)+'&redirect_uri='+githubData.redirectUri+'&response_type=code&client_id='+githubData.clientId+'&state='+vm.urlStateEncode;
	//amazon
	var amazonData = {
	  clientId: configItems.amazon_oauth_client_id,
	  redirectUri: window.location.origin+'/oauth/amazon',
		authorizationEndpoint: 'https://www.amazon.com/ap/oa',
		scope: ['profile'],
	  scopeDelimiter: ' ',
	}
	vm.oauth_urls.amazon = amazonData.authorizationEndpoint+'?scope='+amazonData.scope.join(amazonData.scopeDelimiter)+'&redirect_uri='+amazonData.redirectUri+'&response_type=code&client_id='+amazonData.clientId+'&state='+vm.urlStateEncode;
	//discord
	var discordData = {
	  clientId: configItems.discord_oauth_client_id,
	  redirectUri: window.location.origin+'/oauth/discord',
		authorizationEndpoint: 'https://discordapp.com/api/oauth2/authorize',
		scope: ['identify','email'],
	  scopeDelimiter: ' ',
	}
	vm.oauth_urls.discord = discordData.authorizationEndpoint+'?scope='+discordData.scope.join(discordData.scopeDelimiter)+'&redirect_uri='+discordData.redirectUri+'&response_type=code&client_id='+discordData.clientId+'&state='+vm.urlStateEncode+'&prompt=none';

	vm.showlocal = function() {
		vm.showlocallogin = !vm.showlocallogin;
	}

	vm.cancel = function() {
		$mdDialog.cancel();
	}
}
