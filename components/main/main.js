angular.module('FrcPortal')
.controller('mainController', [
	'$rootScope', 'configItems', '$auth', '$timeout', 'navService', '$mdSidenav', '$mdBottomSheet', '$log', '$q', '$state', '$mdToast', '$mdDialog', 'authed', 'usersService', '$scope', 'signinService', '$window', '$ocLazyLoad', 'generalService','webauthnService',
	mainController
]);
function mainController($rootScope, configItems, $auth, $timeout, navService, $mdSidenav, $mdBottomSheet, $log, $q, $state, $mdToast, $mdDialog, authed, usersService, $scope, signinService, $window, $ocLazyLoad, generalService,webauthnService) {
	var main = this;

	main.configItems = configItems;
	main.team_number = configItems.team_number;
	main.team_logo_url = configItems.team_logo_url;
	main.menuItems = [ ];
	main.selectItem = selectItem;
	main.toggleItemsList = toggleItemsList;
	main.title = $state.current.data.title;
	main.title_extra = '';
	main.showSimpleToast = showSimpleToast;
	main.toggleRightSidebar = toggleRightSidebar;
	main.loginModal = loginModal;
	main.newUserModal = newUserModal;
	main.signInModal = signInModal;
	main.isAuthed = authed;
	main.notifications = [];
	main.signInAuthed = signinService.isAuthed();
	main.browserData = {}
	main.versionInfo = {}
	main.loginProvider = null;
	main.newCredential = null;
	main.noCameras = true;

	//lazy load dialog controllers
	$ocLazyLoad.load('components/loginModal/loginModal.js');
	//$ocLazyLoad.load('components/newUserModal/newUserModal.js');
	$ocLazyLoad.load('components/newSeasonModal/newSeasonModal.js');
	$ocLazyLoad.load('components/newEventModal/newEventModal.js');
	$ocLazyLoad.load('components/SeasonHoursGraphModal/SeasonHoursGraphModal.js');
	$ocLazyLoad.load('components/roomListModal/roomListModal.js');
	$ocLazyLoad.load('components/carListModal/carListModal.js');
	$ocLazyLoad.load('components/eventFoodModal/eventFoodModal.js');
	$ocLazyLoad.load('components/eventRegistrationModal/eventRegistrationModal.js');
	$ocLazyLoad.load('components/timeSlotModal/timeSlotModal.js');
	$ocLazyLoad.load('components/eventSearchModal/eventSearchModal.js');
	$ocLazyLoad.load('components/editTimeSlotModal/editTimeSlotModal.js');
	$ocLazyLoad.load('components/eventTypesModal/eventTypesModal.js');
	$ocLazyLoad.load('components/timeSheetModal/timeSheetModal.js');
	//$ocLazyLoad.load('components/userCategoriesModal/userCategoriesModal.js');
	$ocLazyLoad.load('components/serviceAccountModal/serviceAccountModal.js');
	$ocLazyLoad.load('components/googleFormMapModal/googleFormMapModal.js');
	$ocLazyLoad.load('components/signInModal/signInModal.js');
  //$ocLazyLoad.load('https://rawgit.com/schmich/instascan-builds/master/instascan.min.js');
	$ocLazyLoad.load('components/newSchoolModal/newSchoolModal.js');
	$ocLazyLoad.load('components/firstPortalCredentialModal/firstPortalCredentialModal.js');

	navService.loadAllItems().then(function(menuItems) {
		main.menuItems = [].concat(menuItems);
  });

	generalService.getVersion().then(function(response) {
		main.versionInfo = response;
  });

	function toggleRightSidebar() {
		$mdSidenav('right').toggle();
	}

	function toggleItemsList() {
		$mdSidenav('left').toggle();
	}

	function selectItem (item) {
	  main.title = item.name;
	  main.showSimpleToast(main.title);
		main.toggleItemsList();
	}


	function showSimpleToast(title) {
	  $mdToast.show(
		$mdToast.simple()
		  .content(title)
		  .hideDelay(2000)
		  .position('bottom right')
	  );
	}

	function loginModal(ev) {
		$mdDialog.show({
			controller: loginModalController,
			controllerAs: 'vm',
			templateUrl: 'components/loginModal/loginModal.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				loginData: {
					loading: false,
				}
			}
		});
	}

	function newUserModal(ev) {
		$mdDialog.show({
			controller: newUserModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/newUserModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			//clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				userInfo: main.userInfo,
			}
		})
		.then(function(response) {
			if(response.status) {
				console.log('After Dialog')
				console.log(response.userInfo);
				$rootScope.$broadcast('afterLoginAction');
			}
		}, function() {
			$log.info('Dialog dismissed at: ' + new Date());
		});
	}

	function signInModal(ev) {
		$mdDialog.show({
			controller: signInModalController,
			controllerAs: 'vm',
			templateUrl: 'components/signInModal/signInModal.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			//clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				userInfo: main.userInfo,
			},
		});
	}

	main.initServiceWorkerState = function() {
		console.log('Initializing');
		navigator.serviceWorker.ready.then(function() {
			console.log('Service Worker Ready');
		})
		.catch(function(err) {
			consol.log(err);
		});
	}

	main.checkServiceWorker = function() {
		console.log('Check Service Worker');
		if ('serviceWorker' in navigator) {
			navigator.serviceWorker.register('/sw.js').then(main.initServiceWorkerState);
		} else {
			console.warn('Service workers aren\'t supported in this browser.');
		}
	}


	main.StartEventSource = function() {
		var source = new EventSource('site/eventSourceNotifications.php');
		source.onmessage = function (event) {
			//main.notifications = JSON.parse(event.data);
			console.log(JSON.parse(event.data));
			//alert(event.data);
		};
	}

	var loginActions = function() {
		main.isAuthed = $auth.isAuthenticated();
		main.userInfo = angular.fromJson(window.localStorage['userInfo']);
		//main.StartEventSource();
		main.checkCamera();
		// if(main.userInfo != undefined && main.userInfo.first_login) {
		// 	//newUserModal();
		// 	$state.go('main.profile',{'firstLogin': true});
		// }
	}

	main.askAuthenticator = function() {
		return $q(function(resolve, reject) {
			var confirm = $mdDialog.confirm()
          .title('Would you like to use your device to login')
          .textContent('This device is capable of automatically logging you in using device credentials (Windows Hello, Apple Touch ID, Android Security...).')
          .ariaLabel('Lucky day')
          .ok('Yes')
          .cancel('No');
			if (window.PublicKeyCredential && window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable) {
		    	window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable().then(response => {
		      if (response == true) {
						return $mdDialog.show(confirm);
					}
				}).then(function() {
					return webauthnService.getRegisterOptions();
				}).then(response => {
					console.log('creating creds');
					var excludeCredentials = response.excludeCredentials == undefined ? [] : response.excludeCredentials.map(function(val){
						var temp = val;
						var unsafeBase64 = atob(val.id.replace(/_/g, '/').replace(/-/g, '+'));
						temp.id = Uint8Array.from(unsafeBase64, c=>c.charCodeAt(0));
						return temp;
					})
					var publicKey = {
							challenge: Uint8Array.from(response.challenge, c=>c.charCodeAt(0)),
							rp: {
								'name': response.rp.name,
								'id': response.rp.id,
							},
							user: {
									'id': Uint8Array.from(response.user.id, c=>c.charCodeAt(0)),
									'name': response.user.name,
									'displayName': response.user.displayName
							},
							excludeCredentials: excludeCredentials,
							pubKeyCredParams: response.pubKeyCredParams,
							extensions: {
								txAuthSimple: 'Please verify your identity to FRC Portal'
							}
					}
					console.log(publicKey);
					return navigator.credentials.create({ 'publicKey': publicKey })
				}, error => {
					if(error) {
						console.log(error)
					}
				}).then(newCredential => {
						if(newCredential != undefined) {
							console.log('SUCCESS', newCredential);
							let attestationObject = new Uint8Array(newCredential.response.attestationObject);
					    let clientDataJSON = new Uint8Array(newCredential.response.clientDataJSON);
					    let rawId = new Uint8Array(newCredential.rawId);
							var data = {
								id: newCredential.id,
		            rawId: webauthnService.bufferEncode(rawId),
		            type: newCredential.type,
		            response: {
		                attestationObject: webauthnService.bufferEncode(attestationObject),
		                clientDataJSON: webauthnService.bufferEncode(clientDataJSON),
		            },
								name: '',
							};
							main.newCredential = data;
					    var confirm = $mdDialog.prompt()
					      .title('Please enter a name for this credential')
					      .textContent('Naming this credential will allow you to easily identify it.')
					      .placeholder('Credential Name')
					      .ariaLabel('Credential Name')
					      .required(true)
					      .ok('submit')
					      .cancel('cancel');
				    	return $mdDialog.show(confirm);
						}
					}, function(error) {
						if(error && error.name == 'InvalidStateError') {
							$window.localStorage['webauthn_cred'] = angular.toJson({user: main.userInfo.user_id});
							loginModal(null);
						}
						console.log(error.name)
						console.log(error.message)
					}).then(result => {
						if(main.newCredential != null) {
							var data = main.newCredential;
							data.name = result;
							data.platform = getCredPlatform();
							return webauthnService.registerCredential(data);
						}
				}, error => {
					if(error) {
						console.log(error)
					}
				}).then(response => {
						if(response) {
							if(response.status) {
								$window.localStorage['webauthn_cred'] = angular.toJson(response.data);
							}
							$mdToast.show(
								$mdToast.simple()
									.textContent(response.msg)
									.position('top right')
									.hideDelay(3000)
							);
							resolve();
						}	 else {
							reject();
						}
				});
		  }
		});
	}

	main.checkCamera = function() {
		var cameras = [];
		navigator.mediaDevices.enumerateDevices().then(function(devices) {
		  devices.forEach(function(device) {
				if(device.kind == 'videoinput') {
					cameras.push(device);
				}
			});
			main.noCameras = cameras.length == 0;
		})
	}
	main.checkServiceWorker();

	if(main.isAuthed) {
		console.info('I\'m Authed');
		loginActions();
	}
	if(!main.signInAuthed) {
		signinService.logout();
	}

	main.logout = function() {
		$rootScope.$broadcast('logOutAction');
		if($state.current.authenticate == true) {
			$state.go('main.home');
		}
		$auth.logout();
	}

	$rootScope.$on('afterLoginAction', function(event,args) {
		console.info('Login Initiated');
		loginActions();
		if(args.loginType == 'oauth' && !($window.localStorage['webauthn_cred'] != null && $window.localStorage['webauthn_cred'] != undefined)) {
			if (window.PublicKeyCredential && window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable) {
		    	window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable().then(response => {
		      if (response == true) {
						$timeout( function(){
								main.askAuthenticator();
							}, 600 );
					}
				})
			}
		}
	});

	$rootScope.$on('logOutAction', function(event, data) {
		console.info('LogOut Initiated');
		main.isAuthed = false;
		main.userInfo = null;
		$window.localStorage.removeItem('userInfo');
	});
	$rootScope.$on('checkAuth', function(event, data) {
		console.info('Chcking Auth');
		if(main.isAuthed && !$auth.isAuthenticated()) {
			$rootScope.$broadcast('logOutAction',data);
		}
	});
	$rootScope.$on('stateChange', function(event, data) {
		main.title = $state.current.data.title;
	});

	var getCredPlatform = function() {
	  var userAgent = window.navigator.userAgent,
	      platform = window.navigator.platform,
	      macosPlatforms = ['Macintosh', 'MacIntel', 'MacPPC', 'Mac68K'],
	      windowsPlatforms = ['Win32', 'Win64', 'Windows', 'WinCE'],
	      iosPlatforms = ['iPhone', 'iPad', 'iPod'],
	      device = null;

	  if (macosPlatforms.indexOf(platform) !== -1) {
	    device = 'mac';
	  } else if (platform == 'iPhone' || platform == 'iPod') {
	    device = 'iphone';
	  } else if (platform == 'iPad') {
	    device = 'ipad';
	  } else if (windowsPlatforms.indexOf(platform) !== -1) {
	    device = 'windows';
	  } else if (/Android/.test(userAgent)) {
	    device = 'android';
	  } else if (!device && /Linux/.test(platform)) {
	    device = 'linux';
	  }
	  return device;
	}




}
