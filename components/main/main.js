angular.module('FrcPortal')
.controller('mainController', [
	'$rootScope', 'configItems', '$auth', 'navService', '$mdSidenav', '$mdBottomSheet', '$log', '$q', '$state', '$mdToast', '$mdDialog', 'authed', 'usersService', '$scope', 'signinService', '$window', '$ocLazyLoad', 'generalService',
	mainController
]);
function mainController($rootScope, configItems, $auth, navService, $mdSidenav, $mdBottomSheet, $log, $q, $state, $mdToast, $mdDialog, authed, usersService, $scope, signinService, $window, $ocLazyLoad, generalService) {
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
		if(main.userInfo != undefined && main.userInfo.first_login) {
			//newUserModal();
			$state.go('main.profile',{'firstLogin': true});
		}
	}

/*	var askAuthenticator = function() {
		var confirm = $mdDialog.confirm()
          .title('Would you like to use your fingerprint to login')
          .textContent('This device is capable of automatically logging you in using your fingerprint.')
          .ariaLabel('Lucky day')
          .ok('Yes')
          .cancel('No');
		if (window.PublicKeyCredential && window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable) {
	    	window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable().then(response => {
	      if (response == true) {
					$mdDialog.show(confirm).then(function() {
						var challenge = new Uint8Array(32);
						window.crypto.getRandomValues(challenge);
						var publicKey = {
						    'challenge': challenge,
						    'rp': {
									'name': main.configItems.team_name,
					        'id': main.configItems.env_url,
						    },
						    'user': {
						        'id': Uint8Array.from(main.userInfo.user_id, c=>c.charCodeAt(0)),
						        'name': main.userInfo.user_id,
						        'displayName': main.userInfo.full_name
						    },
						    'pubKeyCredParams': [
						        { 'type': 'public-key', 'alg': -7  },
						        { 'type': 'public-key', 'alg': -257 }
						    ]
						}
						return navigator.credentials.create({ 'publicKey': publicKey })
					}).then(newCredentialInfo => {
							console.log('SUCCESS', newCredentialInfo)
					});
	      }
	    });
	  }
	} */

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

	$rootScope.$on('afterLoginAction', function(event) {
		console.info('Login Initiated');
		loginActions();
		//askAuthenticator();
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

	function registerPlatformAuthenticator() {
	  const advancedOptions = {};
	  advancedOptions.userVerification = 'required';
	  advancedOptions.authenticatorAttachment = 'platform';
	  makeCredential(advancedOptions);
	}


}
