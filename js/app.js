angular.module('FrcPortal', [
	'ngAnimate',
	'ngSanitize',
	'ui.router',
	'ngMaterial',
	'md.data.table',
	'ngMdIcons',
	'satellizer',
	'nvd3',
	'ui.router.default',
	'chart.js',
	'bc.AngularKeypad',
	'angularRipple',
	'moment-picker',
	'ngCsv',
]).config(function ($stateProvider, $urlRouterProvider, $mdThemingProvider, $mdIconProvider, $locationProvider) {

	$locationProvider.html5Mode({ enabled: true, requireBase: true });
	$stateProvider
	  .state('main', {
		url: '',
		templateUrl: 'views/main.html',
		controller: 'mainController',
		controllerAs: 'main',
		abstract: true,
		resolve: {
			authed: function($auth) {
				return $auth.isAuthenticated();
			},
		},
	  })
	  .state('main.home', {
		url: '/home',
		templateUrl: 'views/main.home.html',
		controller: 'main.homeController',
		controllerAs: 'vm',
		authenticate: false,
		data: {
		  title: 'Home'
		}
	  })
	  .state('main.profile', {
		url: '/profile',
		templateUrl: 'views/main.profile.html',
		controller: 'main.profileController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Profile'
		}
	  })
	  .state('main.signin', {
		url: '/signin',
		templateUrl: 'views/main.signin.html',
		controller: 'main.signinController',
		controllerAs: 'vm',
		authenticate: false,
		data: {
		  title: 'Sign In'
		}
	  })
	  .state('main.admin', {
		url: '/admin',
		templateUrl: 'views/main.admin.html',
		controller: 'main.adminController',
		controllerAs: 'admin',
		abstract: true,
		authenticate: true,
		admin: true,
		default: 'main.admin.users',
		data: {
		  title: 'Admin'
		}
	  })
	  .state('main.admin.users', {
		url: '/users',
		templateUrl: 'views/main.admin.users.html',
		controller: 'main.admin.usersController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Users'
		}
	  })
	  .state('main.admin.seasons', {
		url: '/seasons',
		templateUrl: 'views/main.admin.seasons.html',
		controller: 'main.admin.seasonsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Seasons'
		}
	  })
	  .state('main.admin.season', {
		url: '/seasons/{season_id}',
		templateUrl: 'views/main.admin.season.html',
		controller: 'main.admin.seasonController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Seasons'
		}
	  })
	  .state('main.admin.events', {
		url: '/events',
		templateUrl: 'views/main.admin.events.html',
		controller: 'main.admin.eventsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Events'
		}
	  })
	  .state('main.admin.event', {
		url: '/events/{event_id}',
		templateUrl: 'views/main.admin.event.html',
		controller: 'main.admin.eventController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Seasons'
		}
	  })
	  .state('main.admin.user', {
		url: '/user/{user_id}',
		templateUrl: 'views/main.admin.user.html',
		controller: 'main.admin.userController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Users'
		}
	  })
	  .state('main.admin.time', {
		url: '/time',
		templateUrl: 'views/main.admin.time.html',
		controller: 'main.admin.timeController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Events'
		}
	  })
	  .state('main.admin.schools', {
		url: '/schools',
		templateUrl: 'views/main.admin.schools.html',
		controller: 'main.admin.schoolsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Events'
		}
	  })
	  .state('main.admin.metrics', {
		url: '/metrics',
		templateUrl: 'views/main.admin.metrics.html',
		controller: 'main.admin.metricsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Metrics'
		}
	  })
		.state('main.admin.exemptHours', {
		 url: '/exemptHours',
		 templateUrl: 'views/main.admin.exemptHours.html',
		 controller: 'main.admin.exemptHoursController',
		 controllerAs: 'vm',
		 authenticate: true,
		 data: {
			 title: 'Metrics'
		 }
		 })
 	  .state('main.event', {
 		url: '/events/{event_id}',
 		templateUrl: 'views/main.event.html',
 		controller: 'main.eventController',
 		controllerAs: 'vm',
		abstract: true,
		authenticate: true,
		default: 'main.event.info',
		//admin: true,
 		data: {
 		  title: 'Seasons'
 		}
 	  })
	 .state('main.event.info', {
	 url: '/info',
	 templateUrl: 'views/main.event.info.html',
	 //controller: 'main.event.infoController',
	// controllerAs: 'vm',
	 authenticate: true,
	 //admin: true,
	 data: {
		 title: 'Seasons'
	 }
	 });

	$urlRouterProvider.otherwise('/home');


	$mdThemingProvider.definePalette('tripplehelixpurple', {
	  '50': 'ede6f2',
	  '100': 'd1c0de',
	  '200': 'b397c8',
	  '300': '946db2',
	  '400': '7d4da2',
	  '500': '662e91',
	  '600': '5e2989',
	  '700': '53237e',
	  '800': '491d74',
	  '900': '381262',
	  'A100': 'c599ff',
	  'A200': 'a866ff',
	  'A400': '8b33ff',
	  'A700': '7d1aff',
	  'contrastDefaultColor': 'light',
	  'contrastDarkColors': [
		'50',
		'100',
		'200',
		'300',
		'A100',
		'A200'
	  ],
	  'contrastLightColors': [
		'400',
		'500',
		'600',
		'700',
		'800',
		'900',
		'A400',
		'A700'
	  ]
	});
	$mdThemingProvider.definePalette('tripplehelixyellow', {
	  '50': 'fff6e3',
	  '100': 'feeab8',
	  '200': 'fedc89',
	  '300': 'fecd5a',
	  '400': 'fdc336',
	  '500': 'fdb813',
	  '600': 'fdb111',
	  '700': 'fca80e',
	  '800': 'fca00b',
	  '900': 'fc9106',
	  'A100': 'ffffff',
	  'A200': 'fff8f0',
	  'A400': 'ffdfbd',
	  'A700': 'ffd3a3',
	  'contrastDefaultColor': 'light',
	  'contrastDarkColors': [
		'50',
		'100',
		'200',
		'300',
		'400',
		'500',
		'600',
		'700',
		'800',
		'900',
		'A100',
		'A200',
		'A400',
		'A700'
	  ],
	  'contrastLightColors': []
	});
	$mdThemingProvider.theme('default')
		.primaryPalette('tripplehelixpurple')
		.accentPalette('tripplehelixyellow');
	//	.warnPalette('tripplehelixyellow');
})
.config(function($httpProvider) {
	$httpProvider.interceptors.push(function authInterceptor($q, $injector) {
		return {
			// automatically attach Authorization header
			request: function(config) {
				var signinService = $injector.get('signinService');
				var urlArr = config.url.split('/');
				if(urlArr[urlArr.length  - 1] == 'signInOut.php') {
					var token = signinService.getToken();
					if(token) {
						config.headers.Authorization = 'Bearer ' + token;
					}
					return config;
				}else {
					return config;
				}
			},
			// If a token was sent back, save it
			response: function(res) {
				var $auth = $injector.get('$auth');
				var signinService = $injector.get('signinService');
				var $rootScope = $injector.get('$rootScope');
				if(res.data.token) {
					$auth.setToken(res.data.token);
				} else if (res.data.signin_token) {
					signinService.saveToken(res.data.signin_token);
					var data = {
						'response': res.data,
						'logout': true
					}
					$rootScope.$broadcast('updateSigninStatus',data);
				}
				return res;
			},
			responseError: function(rejection) {
				if (rejection.status === 401) {
					// Return a new promise
					var $mdDialog = $injector.get('$mdDialog');
					var $auth = $injector.get('$auth');
					var $rootScope = $injector.get('$rootScope');
					console.log(rejection);
					var fullLogin = true;
					var urlArr = rejection.config.url.split('/');
					if(urlArr[urlArr.length  - 1] == 'authorizeSignIn.php') {
						fullLogin = false;
					}
					$mdDialog.show({
						controller: loginModalController,
						controllerAs: 'vm',
						templateUrl: 'views/partials/loginModal.tmpl.html',
						parent: angular.element(document.body),
						clickOutsideToClose:true,
						fullscreen: true // Only for -xs, -sm breakpoints.
					})
					.then(function(data) {
						if(data.auth) {
							var data = {
								'allActions': fullLogin,
							}
							$rootScope.$broadcast('afterLoginAction',data);
							return $injector.get('$http')(rejection.config);
						}
					}, function() {
						$log.info('Dialog dismissed at: ' + new Date());
						$log.error('Authentication Required');
					});
				}
				/* else if (rejection.status === 500) {
					// Return a new promise
					var $uibModal = $injector.get('$mdDialog');
					var $auth = $injector.get('$auth');
					var $rootScope = $injector.get('$rootScope');

					$mdDialog.show({
						controller: loginModalController,
						controllerAs: 'vm',
						templateUrl: 'views/partials/loginModal.tmpl.html',
						parent: angular.element(document.body),
						targetEvent: ev,
						clickOutsideToClose:true,
						fullscreen: true // Only for -xs, -sm breakpoints.
					})
					.then(function(data) {
						if(data.auth) {
							$rootScope.$broadcast('afterLoginAction');
							return $injector.get('$http')(rejection.config);
						}
					}, function() {
						$log.info('Dialog dismissed at: ' + new Date());
						$log.error('Authentication Required');
					});




					var openLoginModal = function () {
						var modalInstance = $uibModal.open({
							animation: true,
							templateUrl: './views/modals/500ErrorModal.html',
							controller: '500ErrorModal-ctrl',
							resolve: {
								'data':rejection.data
							}
						});
						modalInstance.result.then(function (data) {
							}, function () {
								//$log.info('Modal dismissed at: ' + new Date());
						});
					};
					openLoginModal();
				} */
				/* If not a 401, do nothing with this error.
				* This is necessary to make a `responseError`
				* interceptor a no-op. */
				return $q.reject(rejection);
			}
		}
	});
})
.run(function($transitions, $rootScope, $state, $auth, $mdDialog, $log) {
	$transitions.onStart({to: function(state) { return state != null && state.authenticate;}}, function(trans) {
		var toState = trans.$to();

		if (!$auth.isAuthenticated()){
			trans.abort();
			/* event.preventDefault();  */
			$log.info('Need logged in');
			//alert(JSON.stringify(fromState, null, 4));
			$mdDialog.show({
				controller: loginModalController,
				controllerAs: 'vm',
				templateUrl: 'views/partials/loginModal.tmpl.html',
				parent: angular.element(document.body),
				clickOutsideToClose:true,
				fullscreen: true // Only for -xs, -sm breakpoints.
			})
			.then(function(data) {
				if(data.auth) {
					var data = {
						'allActions': true,
					}
					$rootScope.$broadcast('afterLoginAction',data);
					$log.info('Logged in');
					$state.go(toState.name, toState.params);
				}
				else if(trans.$from().name == '') {
					$state.go('main.home');
				}
			}, function() {
				$log.info('Dialog dismissed at: ' + new Date());
				$log.error('Authentication Required');
				if(trans.$from().name == '') {
					$state.go('main.home');
				}
			});
		} else if((toState.admin || toState.parent.admin) && !$auth.getPayload().data.admin) {
			trans.abort();
			$log.info('Need Admin');
			$mdDialog.show(
				$mdDialog.alert()
				.parent(angular.element(document.body))
				.clickOutsideToClose(true)
				.title('Unauthorized')
				.textContent('You are not authorized to access this page.')
				.ariaLabel('Unauthorized')
				.ok('Got it!')
			);
			if(trans.$from().name == '') {
				$state.go('main.home');
			}
		}
	});
})
.config(function($authProvider) {
	$authProvider.google({
		clientId: '1094835789171-3hss4dvsp904prfjilpade224tajlibh.apps.googleusercontent.com',
		url: '/site/auth_google.php',
	//	url: '/api/v1/login/google',
		authorizationEndpoint: 'https://accounts.google.com/o/oauth2/auth',
		redirectUri: window.location.origin,
		requiredUrlParams: ['scope','prompt'],
		optionalUrlParams: ['display'],
		scope: ['profile', 'email','https://www.googleapis.com/auth/plus.login'],
		scopePrefix: 'openid',
		scopeDelimiter: ' ',
		display: 'popup',
		prompt: 'select_account',
		type: '2.0',
		popupOptions: { width: 452, height: 633 }
	});
 	$authProvider.facebook({
		clientId: '1347987445311447',
		name: 'facebook',
		url: '/site/auth_facebook.php',
	//	url: '/api/v1/login/facebook',
		authorizationEndpoint: 'https://www.facebook.com/v2.11/dialog/oauth',
		redirectUri: window.location.origin+'/',
		requiredUrlParams: ['display', 'scope'],
		scope: ['email'],
		scopeDelimiter: ',',
		display: 'popup',
		type: '2.0',
		popupOptions: { width: 580, height: 400 }
	});
	$authProvider.live({
		url: '/site/auth_live.php',
	//	url: '/api/v1/login/live',
		clientId: '027f5fe4-87bb-4731-8284-6d44da287677',
		authorizationEndpoint: 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
		redirectUri: window.location.origin,
		requiredUrlParams: ['scope', 'response_mode', 'nonce'],
		scope: ['openid','email',' profile','User.Read'], //,'User.Read','User.ReadBasic.All'
		scopeDelimiter: ' ',
		//display: 'popup',
		responseType: 'id_token+code',
		responseMode: 'fragment',
		nonce: '678910',
		type: '2.0',
		popupOptions: { width: 500, height: 560 }
	});
/*	$authProvider.linkedin({
		url: '/site/auth_linkedin.php',
		clientId: '778o827lbrsltx',
		authorizationEndpoint: 'https://www.linkedin.com/uas/oauth2/authorization',
		redirectUri: window.location.origin,
		requiredUrlParams: ['state'],
		scope: ['r_emailaddress', 'r_basicprofile'],
		scopeDelimiter: ' ',
		state: 'STATE',
		type: '2.0',
		popupOptions: { width: 527, height: 582 }
	});
	$authProvider.yahoo({
		url: '/site/auth_yahoo.php',
		authorizationEndpoint: 'https://api.login.yahoo.com/oauth2/request_auth',
		redirectUri: window.location.origin,
		scope: [],
		scopeDelimiter: ',',
		type: '2.0',
		popupOptions: { width: 559, height: 519 }
	}); */



	$authProvider.httpInterceptor = function() { return true; },
	$authProvider.withCredentials = true;
	$authProvider.tokenRoot = null;
	$authProvider.baseUrl = '/';
	$authProvider.loginUrl = '/site/auth/login';
	$authProvider.signupUrl = '/site/auth/signup';
	$authProvider.unlinkUrl = '/site/auth_unlink.php';
	$authProvider.tokenName = 'token';
	$authProvider.tokenPrefix = 'satellizer';
	$authProvider.authHeader = 'Authorization';
	$authProvider.authToken = 'Bearer';
	$authProvider.storageType = 'localStorage';
})
.config(['momentPickerProvider', function (momentPickerProvider) {
	//momentPickerProvider.options({ hoursFormat: 'LT' });
}]);
