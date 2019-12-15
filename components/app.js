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
//	'bc.AngularKeypad',
//	'angularRipple',
	'moment-picker',
	'ngCsv',
//	'ngMap',
	'dndLists',
	'timer',
	'mdColorPicker',
	'oc.lazyLoad',
	'vAccordion',
	'shContextMenu',
	'ngFileUpload',
	//'dcbClearInput',
]).config(function ($stateProvider, $urlRouterProvider, $mdThemingProvider, $mdIconProvider, $locationProvider) {

	$locationProvider.html5Mode({ enabled: true, requireBase: true });
	$stateProvider
	  .state('main', {
		url: '',
		templateUrl: 'components/main/main.html',
		controller: 'mainController',
		controllerAs: 'main',
		abstract: true,
		resolve: {
			authed: function($auth) {
				return $auth.isAuthenticated();
			},// Any property in resolve should return a promise and is executed before the view is loaded
			mainController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load(['components/main/main.js', 'components/filters.js']);
	    }],
	    services: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load([
								 'components/services/NavService.js',
								 'components/services/schoolServices.js',
								 'components/services/userServices.js',
								 'components/services/seasonServices.js',
								 'components/services/eventServices.js',
								 'components/services/signinServices.js',
								 'components/services/metricsServices.js',
								 'components/services/timeServices.js',
								 'components/services/settingServices.js',
								 'components/services/generalServices.js',
								 'components/services/logServices.js',
								 'components/services/loginServices.js',
								 'components/services/otherServices.js',
								 'components/services/webauthnServices.js',
						 ]);
	    }]
		},
	  })
	  .state('main.home', {
		url: '/home',
		templateUrl: 'components/main.home/main.home.html',
		controller: 'main.homeController',
		controllerAs: 'vm',
		authenticate: false,
		data: {
		  title: 'Home'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    homeController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.home/main.home.js');
	    }]
	  }
	  })
	  .state('main.oauthSuccess', {
		url: '/oauth?clientId&code&redirectUri',
		templateUrl: 'components/main.oauth/main.oauth.html',
		authenticate: false,
		data: {
		  title: ''
		}
	  })
	  .state('main.oauth', {
		url: '/oauth/{provider}?code&state',
		templateUrl: 'components/main.oauth/main.oauth.html',
		controller: 'main.oauthController',
		controllerAs: 'vm',
		authenticate: false,
		data: {
		  title: ''
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    homeController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
       	return $ocLazyLoad.load(['components/loginModal/loginModal.js','components/main.oauth/main.oauth.js']);
	    }]
	  }
		})
	  .state('main.profile', {
		url: '/profile?signin',
		templateUrl: 'components/main.profile/main.profile.html',
		controller: 'main.profileController',
		controllerAs: 'vm',
		authenticate: true,
		params: {
			firstLogin: false,
      linkedAccounts: false,
    },
		data: {
		  title: 'Profile'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    profileController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.profile/main.profile.js');
	    }]
	  }
	  })
		.state('main.timein', {
		url: '/timein?token',
		templateUrl: 'components/main.timein/main.timein.html',
		controller: 'main.timeinController',
		controllerAs: 'vm',
		authenticate: true,
		params: {},
		data: {
			title: 'Sign In'
		},
		resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
			profileController: ['$ocLazyLoad', function($ocLazyLoad) {
				// you can lazy load files for an existing module
							 return $ocLazyLoad.load('components/main.timein/main.timein.js');
			}]
		}
		})
	  .state('main.signin', {
		url: '/signin',
		templateUrl: 'components/main.signin/main.signin.html',
		controller: 'main.signinController',
		controllerAs: 'vm',
		authenticate: false,
		data: {
		  title: 'Hours Sign In Dashboard'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    signinController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.signin/main.signin.js');
	    }]
	  }
	  })
	  .state('main.admin', {
		url: '/admin',
		templateUrl: 'components/main.admin/main.admin.html',
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
	             return $ocLazyLoad.load('components/main.admin/main.admin.js');
	    }]
	  }
	  })
	  .state('main.admin.users', {
		url: '/users',
		templateUrl: 'components/main.admin.users/main.admin.users.html',
		controller: 'main.admin.usersController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Users'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    usersController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.admin.users/main.admin.users.js');
	    }]
	  }
	  })
	  .state('main.admin.user', {
		url: '/user/{user_id}',
		templateUrl: 'components/main.admin.user/main.admin.user.html',
		controller: 'main.admin.userController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Users'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    userController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.admin.user/main.admin.user.js');
	    }]
	  }
	  })
	  .state('main.admin.seasons', {
		url: '/seasons',
		templateUrl: 'components/main.admin.seasons/main.admin.seasons.html',
		controller: 'main.admin.seasonsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Seasons'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    seasonsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.admin.seasons/main.admin.seasons.js');
	    }]
	  }
	  })
	  .state('main.admin.season', {
		url: '/seasons/{season_id}',
		templateUrl: 'components/main.admin.season/main.admin.season.html',
		controller: 'main.admin.seasonController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Seasons'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    seasonController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.admin.season/main.admin.season.js');
	    }]
	  }
	  })
	  .state('main.admin.events', {
		url: '/events',
		templateUrl: 'components/main.admin.events/main.admin.events.html',
		controller: 'main.admin.eventsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Events'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    eventsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.admin.events/main.admin.events.js');
	    }]
	  }
	  })
	  .state('main.admin.event', {
		url: '/events/{event_id}',
		templateUrl: 'components/main.admin.event/main.admin.event.html',
		controller: 'main.admin.eventController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Event'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    eventController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.admin.event/main.admin.event.js');

	    }]
	  }
	  })
	  .state('main.admin.time', {
		url: '/time',
		templateUrl: 'components/main.admin.time/main.admin.time.html',
		controller: 'main.admin.timeController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Time'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    timeController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load(['components/main.admin.time/main.admin.time.js','js/directives/humanize-duration.js']);
	    }]
	  }
	  })
	  .state('main.admin.schools', {
		url: '/schools',
		templateUrl: 'components/main.admin.schools/main.admin.schools.html',
		controller: 'main.admin.schoolsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Schools'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    schoolsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.admin.schools/main.admin.schools.js');
	    }]
	  }
	  })
	  .state('main.admin.metrics', {
		url: '/metrics',
		templateUrl: 'components/main.admin.metrics/main.admin.metrics.html',
		controller: 'main.admin.metricsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Metrics'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    metricsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
         	return $ocLazyLoad.load('components/main.admin.metrics/main.admin.metrics.js');
	    }]
	  }
	  })
	  .state('main.admin.settings', {
		url: '/settings',
		templateUrl: 'components/main.admin.settings/main.admin.settings.html',
		controller: 'main.admin.settingsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Site Settings'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    settingsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
           return $ocLazyLoad.load(['components/main.admin.settings/main.admin.settings.js','components/oAuthCredentialModal/oAuthCredentialModal.js']);
	    }]
	  }
	  })
	  .state('main.admin.logs', {
		url: '/logs',
		templateUrl: 'components/main.admin.logs/main.admin.logs.html',
		controller: 'main.admin.logsController',
		controllerAs: 'vm',
		authenticate: true,
		data: {
		  title: 'Admin | Logs'
		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    settingsController: ['$ocLazyLoad', 'adminController', function($ocLazyLoad,adminController) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.admin.logs/main.admin.logs.js');
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
	 .state('main.events', {
	 url: '/events?name&type&event_start&event_end',
	 templateUrl: 'components/main.events/main.events.html',
	 controller: 'main.eventsController',
	 controllerAs: 'vm',
	 data: {
		 title: 'Events'
	 },
	 resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
		 eventsController: ['$ocLazyLoad', function($ocLazyLoad) {
			 // you can lazy load files for an existing module
							return $ocLazyLoad.load('components/main.events/main.events.js');
		 }]
	 }
	 })
 	  .state('main.event', {
 		url: '/events/{event_id}',
 		templateUrl: 'components/main.event/main.event.html',
 		controller: 'main.eventController',
 		controllerAs: 'vm',
		//authenticate: true,
 		data: {
 		  title: 'Events'
 		},
	  resolve: { // Any property in resolve should return a promise and is executed before the view is loaded
	    eventController: ['$ocLazyLoad', function($ocLazyLoad) {
	      // you can lazy load files for an existing module
	             return $ocLazyLoad.load('components/main.event/main.event.js');
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
})
.config(['$mdThemingProvider', 'configItems', function ($mdThemingProvider, configItems) {

	var primaryPalette = null;
	var accentPalette = null;
	function multiply(rgb1, rgb2) {
		rgb1.b = Math.floor(rgb1.b * rgb2.b / 255);
		rgb1.g = Math.floor(rgb1.g * rgb2.g / 255);
		rgb1.r = Math.floor(rgb1.r * rgb2.r / 255);
		return tinycolor('rgb ' + rgb1.r + ' ' + rgb1.g + ' ' + rgb1.b);
	}
	function getColorObject(value, name) {
		var c = tinycolor(value);
		return {
			name: name,
			hex: c.toHexString(),
			darkContrast: c.isLight()
		};
	}

	// Function to calculate all colors from base
	// These colors were determined by finding all
	// HSL values for a google palette, calculating
	// the difference in H, S, and L per color
	// change individually, and then applying these
	// here.
	function computeColors(hex) {
		// Return array of color objects.
		var baseLight = tinycolor('#ffffff');
		var baseDark = multiply(tinycolor(hex).toRgb(), tinycolor(hex).toRgb());
		var baseTriad = tinycolor(hex).tetrad();
		return [
			getColorObject(tinycolor.mix(baseLight, hex, 12), '50'),
			getColorObject(tinycolor.mix(baseLight, hex, 30), '100'),
			getColorObject(tinycolor.mix(baseLight, hex, 50), '200'),
			getColorObject(tinycolor.mix(baseLight, hex, 70), '300'),
			getColorObject(tinycolor.mix(baseLight, hex, 85), '400'),
			getColorObject(tinycolor.mix(baseLight, hex, 100), '500'),
			getColorObject(tinycolor.mix(baseDark, hex, 87), '600'),
			getColorObject(tinycolor.mix(baseDark, hex, 70), '700'),
			getColorObject(tinycolor.mix(baseDark, hex, 54), '800'),
			getColorObject(tinycolor.mix(baseDark, hex, 25), '900'),
			getColorObject(tinycolor.mix(baseDark, baseTriad[4], 15).saturate(80).lighten(65), 'A100'),
			getColorObject(tinycolor.mix(baseDark, baseTriad[4], 15).saturate(80).lighten(55), 'A200'),
			getColorObject(tinycolor.mix(baseDark, baseTriad[4], 15).saturate(100).lighten(45), 'A400'),
			getColorObject(tinycolor.mix(baseDark, baseTriad[4], 15).saturate(100).lighten(40), 'A700')
		];
	}
 	function createAjsPaletteJsonObject(colors) {
		var exportable = {};
		var darkColors = [];
		var lightColors = [];
		angular.forEach(colors, function (value, key) {
				exportable[value.name] = value.hex.replace('#', '');
				if (value.darkContrast) {
						darkColors.push(value.name);
				}else{
						lightColors.push(value.name);
				}
		});
		exportable.contrastDefaultColor = 'light';
		exportable.contrastDarkColors = darkColors;
		exportable.contrastLightColors = lightColors;
		return exportable;
	}

	$mdThemingProvider.definePalette('primary', {
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
	$mdThemingProvider.definePalette('secondary', {
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

	//Primary Color
	if(configItems.team_color_primary != undefined && configItems.team_color_primary != '') {
		var primaryPalette = createAjsPaletteJsonObject(computeColors(configItems.team_color_primary));
		$mdThemingProvider.definePalette('primary', primaryPalette);
	} else {
		var primaryPalette = createAjsPaletteJsonObject(computeColors('#662e91'));
		$mdThemingProvider.definePalette('primary', primaryPalette);
	}
	//Accent Color
	if(configItems.team_color_secondary != undefined && configItems.team_color_secondary != '') {
		var accentPalette = createAjsPaletteJsonObject(computeColors(configItems.team_color_secondary));
		$mdThemingProvider.definePalette('secondary', accentPalette);
	} else {
		var accentPalette = createAjsPaletteJsonObject(computeColors('#fdb813'));
		$mdThemingProvider.definePalette('primary', accentPalette);
	}
	//CSS
	var tag = document.getElementById('variableCSS');
	if(!tag) {
		style = document.createElement('style');
	} else {
		style = tag;
	}
	style.type = 'text/css';
	style.id = 'variableCSS';
	style.innerHTML = ':root { --primary-color: #'+primaryPalette['500']+'; }';
	style.innerHTML += ':root { --accent-color: #'+accentPalette['500']+'; }';
	style.innerHTML += '.backgroundPrimary { background-color: #'+primaryPalette['500']+'; }';
	style.innerHTML += '.colorPrimary { color: #'+primaryPalette['500']+'; }';
	style.innerHTML += '.backgroundAccent { color: #'+accentPalette['500']+'; }';
	style.innerHTML += '.colorAccent { color: #'+accentPalette['500']+'; }';
	if(!tag) {
		document.getElementsByTagName('head')[0].appendChild(style);
	}


	$mdThemingProvider.theme('default')
		.primaryPalette('primary', {
      'default': '500', // by default use shade 400 from the pink palette for primary intentions
      'hue-1': '100', // use shade 100 for the <code>md-hue-1</code> class
      'hue-2': '600', // use shade 600 for the <code>md-hue-2</code> class
      'hue-3': 'A100' // use shade A100 for the <code>md-hue-3</code> class
    })
		.accentPalette('secondary', {
      'default': '500', // by default use shade 400 from the pink palette for primary intentions
      'hue-1': '100', // use shade 100 for the <code>md-hue-1</code> class
      'hue-2': '600', // use shade 600 for the <code>md-hue-2</code> class
      'hue-3': 'A100' // use shade A100 for the <code>md-hue-3</code> class
    });
	//	.warnPalette('tripplehelixyellow');
}])
.config( ['$compileProvider', function( $compileProvider ) {
        $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|slack):/);
        // Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
}])
.config(function ($qProvider) {
    $qProvider.errorOnUnhandledRejections(false);
})
.config(function($httpProvider) {
	$httpProvider.interceptors.push(function authInterceptor($q, $injector) {
		return {
			// If a token was sent back, save it
			response: function(res) {
				var $auth = $injector.get('$auth');
				if(res.data.token) {
					$auth.setToken(res.data.token);
				}
				if(res.data.status != undefined && res.data.status == false && res.data.msg != '') {
					console.log(res.data.msg);
				}
				if(res.data.status != undefined && res.data.status == false && res.data.error != '') {
					console.log(res.data.error);
				}
				return res;
			},
			responseError: function(rejection) {
				if (rejection.status === 401) {
					// Return a new promise
					var $mdDialog = $injector.get('$mdDialog');
					var $rootScope = $injector.get('$rootScope');
					var $ocLazyLoad = $injector.get('$ocLazyLoad');
					console.log(rejection);
					$ocLazyLoad.load('components/loginModal/loginModal.js').then(function() {
						$mdDialog.show({
							controller: loginModalController,
							controllerAs: 'vm',
							templateUrl: 'components/loginModal/loginModal.html',
							parent: angular.element(document.body),
							clickOutsideToClose:true,
							fullscreen: true, // Only for -xs, -sm breakpoints.
							locals: {
								loginData: {
									loading: false,
								}
							}
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
					var $rootScope = $injector.get('$rootScope');
					$rootScope.$broadcast('400BadRequest', rejection.data);
					//var $mdToast = $injector.get('$mdToast');
					//console.log(rejection);
					/*$mdToast.show(
			      $mdToast.simple()
			        .textContent(rejection.data.msg)
			        .position('top right')
			        .hideDelay(3000)
			    );*/
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
				} else if (rejection.status === 404) {
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
.run(function($transitions, $rootScope, $state, $auth, $mdDialog, $log, $location, $window, $ocLazyLoad, configItems) {
	// initialise google analytics
	if(configItems.google_analytics_id != '' && configItems.google_analytics_id != undefined) {
  	$window.ga('create', configItems.google_analytics_id, 'auto');
	}

  // track pageview on state change
	$transitions.onSuccess({}, function(transition) {
  	$window.ga('send', 'pageview', $location.path());
	  console.log(
	      "Successful Transition from " + transition.from().name +
	      " to " + transition.to().name
	  );
		$rootScope.$broadcast('stateChange');
	});
	// track pageview on state change
	$transitions.onStart({}, function(transition) {
		$rootScope.pageRefresh = false;
		if(transition.from().name == '') {
			$rootScope.pageRefresh = true;
		}
	});
	$transitions.onStart({to: function(state) { return state != null && state.authenticate;}}, function(trans) {
		var toState = trans.$to();
		if (!$auth.isAuthenticated()){
			trans.abort();
			/* event.preventDefault();  */
			$log.info('Need logged in');
			//alert(JSON.stringify(trans.params('from'), null, 4));
			var from_params_json = JSON.stringify(trans.params('from'));
			var from_params = angular.fromJson(from_params_json);
			delete from_params["#"];
			$ocLazyLoad.load(['components/services/loginServices.js','components/services/webauthnServices.js', 'components/loginModal/loginModal.js']).then(function() {
				$mdDialog.show({
					controller: loginModalController,
					controllerAs: 'vm',
					templateUrl: 'components/loginModal/loginModal.html',
					parent: angular.element(document.body),
					clickOutsideToClose:true,
					fullscreen: true, // Only for -xs, -sm breakpoints.
					loginData: {
						loading: false,
						state: toState.name,
						state_params: trans.params(),
						state_from: {
							'name': trans.$from().name,
							'params': from_params,
						}
					}
				})
				.then(function(data) {
					if(!data.auth && trans.$from().name == '') {
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
		if(toState.name == 'main.profile' && $auth.getPayload().data.localadmin) {
			trans.abort();
			$log.info('Local Admin does not have a profile');
			$mdDialog.show(
				$mdDialog.alert()
				.parent(angular.element(document.body))
				.clickOutsideToClose(true)
				.title('404')
				.textContent('Local Admin does not have a profile')
				.ariaLabel('404')
				.ok('Got it!')
			);
			if(trans.$from().name == '') {
				$state.go('main.home');
			}
		}
	});
	$transitions.onStart({to: 'main.timein'}, function(trans) {
		event.preventDefault();
		var $state = trans.router.stateService;
		return $state.target("main.home");
	});
});
/*
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
		scope: ['profile', 'email'], //,'https://www.googleapis.com/auth/plus.login' Remove Google Plus
		scopePrefix: 'openid',
		scopeDelimiter: ' ',
		display: 'popup',
		prompt: 'select_account',
		hd: hdBool ? configItems.team_domain : '',
		oauthType: '2.0',
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
		oauthType: '2.0',
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
		oauthType: '2.0',
		popupOptions: { width: 500, height: 560 }
	});
	$authProvider.oauth2({
	  name: 'github',
	  url: '/api/auth/github',
	  clientId: configItems.github_oauth_client_id,
	  redirectUri: window.location.origin+'/oauth',
	  authorizationEndpoint: 'https://github.com/login/oauth/authorize',
	  defaultUrlParams: ['client_id', 'redirect_uri'],
	  requiredUrlParams: ['scope'],
	  optionalUrlParams: null,
	  scope: ['read:user', 'user:email'],
	  scopePrefix: null,
	  scopeDelimiter: ' ',
	  state: null,
	  oauthType: '2.0',
	  popupOptions: { width: 580, height: 400 },
	  responseType: 'code',
	  responseParams: {
	    code: 'code',
	    clientId: 'clientId',
	    redirectUri: 'redirectUri'
  }
});
$authProvider.oauth2({
	name: 'amazon',
	url: '/api/auth/amazon',
	clientId: configItems.amazon_oauth_client_id,
	redirectUri: window.location.origin+'/oauth',
	authorizationEndpoint: 'https://www.amazon.com/ap/oa',
	defaultUrlParams: ['response_type', 'client_id', 'redirect_uri'],
	requiredUrlParams: ['scope'],
	optionalUrlParams: null,
	scope: ['profile'],
	scopePrefix: null,
	scopeDelimiter: ' ',
	state: null,
	oauthType: '2.0',
	popupOptions: { width: 580, height: 400 },
	responseType: 'code',
	responseParams: {
		code: 'code',
		clientId: 'clientId',
		redirectUri: 'redirectUri'
}
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
});*/
//.config(['momentPickerProvider', function (momentPickerProvider) {
	//momentPickerProvider.options({ hoursFormat: 'LT' });
//}]);
