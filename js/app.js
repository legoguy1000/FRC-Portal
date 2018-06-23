angular.module('FrcPortal', [
	'ngAnimate',
	'ngSanitize',
	'ui.router',
	'ngMaterial',
	'md.data.table',
	'ngMdIcons',
	'satellizer',
//	'nvd3',
	'ui.router.default',
	'chart.js',
	'bc.AngularKeypad',
	'angularRipple',
	'moment-picker',
	'ngCsv',
	'ngMap',
	'dndLists',
	'timer',
	'mdColorPicker',
	'oc.lazyLoad',
	'vAccordion',
	'shContextMenu',
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
			},// Any property in resolve should return a promise and is executed before the view is loaded
			mainController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load(['js/controllers/main.js', 'js/filters.js']);
	    }],
	    services: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load([
								 'js/services/NavService.js',
								 'js/services/schoolServices.js',
								 'js/services/userServices.js',
								 'js/services/seasonServices.js',
								 'js/services/eventServices.js',
								 'js/services/signinServices.js',
								 'js/services/metricsServices.js',
								 'js/services/timeServices.js',
								 'js/services/settingServices.js',
						 ]);
	    }]
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
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    homeController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.homeController.js');
	    }]
	  }
	  })
	  .state('main.oauthSuccess', {
		url: '/oauth?clientId&code&redirectUri',
		templateUrl: 'views/main.oauth.html',
		controller: function($scope){
    	var vm = this;
	  },
		controllerAs: 'vm',
		authenticate: false,
		data: {
		  title: ''
		}
	  })
	  .state('main.profile', {
		url: '/profile',
		templateUrl: 'views/main.profile.html',
		controller: 'main.profileController',
		controllerAs: 'vm',
		authenticate: true,
		params: {
        firstLogin: false
    },
		data: {
		  title: 'Profile'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    profileController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.profileController.js');
	    }]
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
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    signinController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.signinController.js');
	    }]
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
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    adminController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.adminController.js');
	    }]
	  }
	  })
	  .state('main.admin.users', {
		url: '/users',
		templateUrl: 'views/main.admin.users.html',
		controller: 'main.admin.usersController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Users'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    usersController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.admin.usersController.js');
	    }]
	  }
	  })
	  .state('main.admin.user', {
		url: '/user/{user_id}',
		templateUrl: 'views/main.admin.user.html',
		controller: 'main.admin.userController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Users'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    userController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.admin.userController.js');
	    }]
	  }
	  })
	  .state('main.admin.seasons', {
		url: '/seasons',
		templateUrl: 'views/main.admin.seasons.html',
		controller: 'main.admin.seasonsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Seasons'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    seasonsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.admin.seasonsController.js');
	    }]
	  }
	  })
	  .state('main.admin.season', {
		url: '/seasons/{season_id}',
		templateUrl: 'views/main.admin.season.html',
		controller: 'main.admin.seasonController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Seasons'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    seasonController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.admin.seasonController.js');
	    }]
	  }
	  })
	  .state('main.admin.events', {
		url: '/events',
		templateUrl: 'views/main.admin.events.html',
		controller: 'main.admin.eventsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Events'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    eventsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.admin.eventsController.js');
	    }]
	  }
	  })
	  .state('main.admin.event', {
		url: '/events/{event_id}',
		templateUrl: 'views/main.admin.event.html',
		controller: 'main.admin.eventController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Event'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    eventController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.admin.eventController.js');
	    }]
	  }
	  })
	  .state('main.admin.time', {
		url: '/time',
		templateUrl: 'views/main.admin.time.html',
		controller: 'main.admin.timeController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Time'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    timeController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.admin.timeController.js');
	    }]
	  }
	  })
	  .state('main.admin.schools', {
		url: '/schools',
		templateUrl: 'views/main.admin.schools.html',
		controller: 'main.admin.schoolsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Schools'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    schoolsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.admin.schoolsController.js');
	    }]
	  }
	  })
	  .state('main.admin.metrics', {
		url: '/metrics',
		templateUrl: 'views/main.admin.metrics.html',
		controller: 'main.admin.metricsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Metrics'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    metricsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.admin.metricsController.js');
	    }]
	  }
	  })
	  .state('main.admin.settings', {
		url: '/settings',
		templateUrl: 'views/main.admin.settings.html',
		controller: 'main.admin.settingsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Site Settings'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    settingsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.admin.settingsController.js');
	    }]
	  }
	  })
	/*	.state('main.admin.exemptHours', {
		 url: '/exemptHours',
		 templateUrl: 'views/main.admin.exemptHours.html',
		 controller: 'main.admin.exemptHoursController',
		 controllerAs: 'vm',
		 authenticate: true,
		 data: {
			 title: 'Admin | Eempt Hours'
		 }
	 }) */
 	  .state('main.event', {
 		url: '/events/{event_id}',
 		templateUrl: 'views/main.event.html',
 		controller: 'main.eventController',
 		controllerAs: 'vm',
		//authenticate: true,
 		data: {
 		  title: 'Events'
 		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    eventController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('js/controllers/main.eventController.js');
	    }]
	  }
	});
/*	 .state('main.event.info', {
	 url: '/info',
	 templateUrl: 'views/main.event.html',
	 controller: 'main.eventController',
	 controllerAs: 'vm',
	 authenticate: true,
	 data: {
		 title: 'Events'
	 }
 });*/

	$urlRouterProvider.otherwise('/home');


	$mdThemingProvider.definePalette('tripplehelixpurple', {
	  '50': '',
	  '100': 'd1c0de',
	  '200': '',
	  '300': '',
	  '400': '',
	  '500': '662e91',
	  '600': '5e2989',
	  '700': '',
	  '800': '',
	  '900': '',
	  'A100': 'c599ff',
	  'A200': '',
	  'A400': '',
	  'A700': '',
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
	  '50': '',
	  '100': 'feeab8',
	  '200': '',
	  '300': '',
	  '400': '',
	  '500': 'fdb813',
	  '600': 'fdb111',
	  '700': '',
	  '800': '',
	  '900': '',
	  'A100': 'ffffff',
	  'A200': '',
	  'A400': '',
	  'A700': '',
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
		.primaryPalette('tripplehelixpurple', {
      'default': '500', // by default use shade 400 from the pink palette for primary intentions
      'hue-1': '100', // use shade 100 for the <code>md-hue-1</code> class
      'hue-2': '600', // use shade 600 for the <code>md-hue-2</code> class
      'hue-3': 'A100' // use shade A100 for the <code>md-hue-3</code> class
    })
		.accentPalette('tripplehelixyellow', {
      'default': '500', // by default use shade 400 from the pink palette for primary intentions
      'hue-1': '100', // use shade 100 for the <code>md-hue-1</code> class
      'hue-2': '600', // use shade 600 for the <code>md-hue-2</code> class
      'hue-3': 'A100' // use shade A100 for the <code>md-hue-3</code> class
    });
	//	.warnPalette('tripplehelixyellow');
})
.config( [
    '$compileProvider',
    function( $compileProvider )
    {
        $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|slack):/);
        // Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
    }
])
.config(function ($qProvider) {
    $qProvider.errorOnUnhandledRejections(false);
})
.config(function($httpProvider) {
	$httpProvider.interceptors.push(function authInterceptor($q, $injector) {
		return {
			// If a token was sent back, save it
			response: function(res) {
				var $auth = $injector.get('$auth');
				var $rootScope = $injector.get('$rootScope');
				if(res.data.token) {
					$auth.setToken(res.data.token);
				}
				return res;
			},
			responseError: function(rejection) {
				if (rejection.status === 401) {
					// Return a new promise
					var $mdDialog = $injector.get('$mdDialog');
					var $auth = $injector.get('$auth');
					var $rootScope = $injector.get('$ocLazyLoad');
					var $ocLazyLoad = $injector.get('$ocLazyLoad');
					console.log(rejection);
					$ocLazyLoad.load('js/controllers/loginModalController.js').then(function() {
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
								$rootScope.$broadcast('afterLoginAction');
								return $injector.get('$http')(rejection.config);
							}
						}, function() {
							$log.info('Dialog dismissed at: ' + new Date());
							$log.error('Authentication Required');
							$rootScope.$broadcast('logOutAction');
						});
					});
				} else if (rejection.status === 400) {
					// Return a new promise
					var $mdToast = $injector.get('$mdToast');
					//console.log(rejection);
					$mdToast.show(
			      $mdToast.simple()
			        .textContent(rejection.data.msg)
			        .position('top right')
			        .hideDelay(3000)
			    );
				} else if (rejection.status === 403) {
					// Return a new promise
					var $mdToast = $injector.get('$mdToast');
					//console.log(rejection);
					$mdToast.show(
			      $mdToast.simple()
			        .textContent(rejection.data.msg)
			        .position('top right')
			        .hideDelay(3000)
			    );
				}
				return $q.reject(rejection);
			}
		}
	});
})
.run(function($transitions, $rootScope, $state, $auth, $mdDialog, $log, $location, $window, $ocLazyLoad) {
	// initialise google analytics
  $window.ga('create', 'UA-114656092-1', 'auto');

  // track pageview on state change
	$transitions.onSuccess({}, function(transition) {
  	$window.ga('send', 'pageview', $location.path());
	  console.log(
	      "Successful Transition from " + transition.from().name +
	      " to " + transition.to().name
	  );
	});
	$transitions.onStart({to: function(state) { return state != null && state.authenticate;}}, function(trans) {
		var toState = trans.$to();

		if (!$auth.isAuthenticated()){
			trans.abort();
			/* event.preventDefault();  */
			$log.info('Need logged in');
			//alert(JSON.stringify(fromState, null, 4));
			$ocLazyLoad.load('js/controllers/loginModalController.js').then(function() {
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
						$log.info(toState.name);
						$log.info(trans.params());
						$state.go(toState.name, trans.params());
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
					$rootScope.$broadcast('logOutAction');
				});
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
.config(function($authProvider, configItems) {

	var hdBool = configItems.require_team_email && configItems.team_domain != '';
	var hdVar = hdBool ? 'hd' : '';
	$authProvider.google({
		clientId: configItems.google_oauth_client_id,
		url: '/api/auth/google',
	//	url: '/api/v1/login/google',
		authorizationEndpoint: 'https://accounts.google.com/o/oauth2/auth',
		redirectUri: window.location.origin+'/oauth',
		requiredUrlParams: ['scope','prompt'],
		optionalUrlParams: ['display', hdVar],
		scope: ['profile', 'email','https://www.googleapis.com/auth/plus.login'],
		scopePrefix: 'openid',
		scopeDelimiter: ' ',
		display: 'popup',
		prompt: 'select_account',
		hd: hdBool ? configItems.team_domain : '',
		type: '2.0',
		popupOptions: { width: 452, height: 633 }
	});
 	$authProvider.facebook({
		clientId: configItems.facebook_oauth_client_id,
		name: 'facebook',
		url: '/api/auth/facebook',
	//	url: '/api/v1/login/facebook ',
		authorizationEndpoint: 'https://www.facebook.com/v3.0/dialog/oauth',
		redirectUri: window.location.origin+'/oauth',
		requiredUrlParams: ['display', 'scope'],
		optionalUrlParams: ['auth_type'],
		scope: ['public_profile','email'],
		auth_type: 'rerequest',
		scopeDelimiter: ',',
		display: 'popup',
		type: '2.0',
		popupOptions: { width: 580, height: 400 }
	});
	$authProvider.live({
		url: '/api/auth/live',
	//	url: '/api/v1/login/live',
		clientId: configItems.microsoft_oauth_client_id,
		authorizationEndpoint: 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
		redirectUri: window.location.origin+'/oauth',
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

	$authProvider.httpInterceptor = function() { return true; },
	$authProvider.withCredentials = true;
	$authProvider.tokenRoot = null;
	$authProvider.baseUrl = '/';
	$authProvider.loginUrl = '/api/auth/login';
	$authProvider.tokenName = 'token';
	$authProvider.tokenPrefix = 'satellizer';
	$authProvider.authHeader = 'Authorization';
	$authProvider.authToken = 'Bearer';
	$authProvider.storageType = 'localStorage';
})
.config(['momentPickerProvider', function (momentPickerProvider) {
	//momentPickerProvider.options({ hoursFormat: 'LT' });
}]);
