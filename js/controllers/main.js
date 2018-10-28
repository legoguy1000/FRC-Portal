angular.module('FrcPortal')
.controller('mainController', [
	'$rootScope', 'configItems', '$auth', 'navService', '$mdSidenav', '$mdBottomSheet', '$log', '$q', '$state', '$mdToast', '$mdDialog', 'authed', 'usersService', '$scope', 'signinService', '$window', '$ocLazyLoad',
	mainController
]);
function mainController($rootScope, configItems, $auth, navService, $mdSidenav, $mdBottomSheet, $log, $q, $state, $mdToast, $mdDialog, authed, usersService, $scope, signinService, $window, $ocLazyLoad) {
	var main = this;

	main.configItems = configItems;
	main.team_number = configItems.team_number;
	main.team_logo_url = configItems.team_logo_url;
	main.menuItems = [ ];
	main.selectItem = selectItem;
	main.toggleItemsList = toggleItemsList;
	main.title = $state.current.data.title;
	main.showSimpleToast = showSimpleToast;
	main.toggleRightSidebar = toggleRightSidebar;
	main.loginModal = loginModal;
	main.newUserModal = newUserModal;
	main.isAuthed = authed;
	main.notifications = [];
	main.signInAuthed = signinService.isAuthed();
	main.browserData = {}

	//lazy load dialog controllers
	$ocLazyLoad.load('js/controllers/loginModalController.js');
	$ocLazyLoad.load('js/controllers/newUserModalController.js');
	$ocLazyLoad.load('js/controllers/newSeasonModalController.js');
	$ocLazyLoad.load('js/controllers/newEventModalController.js');
	$ocLazyLoad.load('js/controllers/SeasonHoursGraphModalController.js');
	$ocLazyLoad.load('js/controllers/roomListModalController.js');
	$ocLazyLoad.load('js/controllers/carListModalController.js');
	$ocLazyLoad.load('js/controllers/eventFoodModalController.js');
	$ocLazyLoad.load('js/controllers/eventRegistrationModalController.js');
	$ocLazyLoad.load('js/controllers/timeSlotModalController.js');
	$ocLazyLoad.load('js/controllers/eventSearchModalController.js');
	$ocLazyLoad.load('js/controllers/editTimeSlotModalController.js');
	$ocLazyLoad.load('js/controllers/eventTypesModalController.js');
	$ocLazyLoad.load('js/controllers/timeSheetModalController.js');
	$ocLazyLoad.load('js/controllers/userCategoriesModalController.js');
	$ocLazyLoad.load('js/controllers/serviceAccountModalController.js');
	$ocLazyLoad.load('js/controllers/googleFormMapModalController.js');
  $ocLazyLoad.load('js/controllers/signInModalController.js');

	navService
	  .loadAllItems()
	  .then(function(menuItems) {
		main.menuItems = [].concat(menuItems);
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
			templateUrl: 'views/partials/loginModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true // Only for -xs, -sm breakpoints.
		})
		.then(function(response) {
			if(response.auth) {
				$rootScope.$broadcast('afterLoginAction');
			}
		}, function() {
			$log.info('Dialog dismissed at: ' + new Date());
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


	main.initServiceWorkerState = function() {
		console.log('Initializing');
		navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
			console.log('Service Worker Ready');
				return true;
			})
			.catch(function(err) {	});
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
		main.checkServiceWorker();
		//main.StartEventSource();
		if(main.userInfo.first_login) {
			//newUserModal();
			$state.go('main.profile',{'firstLogin': true});
		}
	}

	if(main.isAuthed) {
		console.info('I\'m Authed');
		loginActions();
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


}
