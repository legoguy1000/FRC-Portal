angular.module('FrcPortal')
.controller('mainController', [
	'$rootScope', 'team_number', '$auth', 'navService', '$mdSidenav', '$mdBottomSheet', '$log', '$q', '$state', '$mdToast', '$mdDialog', 'authed', 'usersService', '$scope', 'signinService',
	mainController
]);
function mainController($rootScope, team_number, $auth, navService, $mdSidenav, $mdBottomSheet, $log, $q, $state, $mdToast, $mdDialog, authed, usersService, $scope, signinService) {
	var main = this;

	main.team_number = team_number;
	main.menuItems = [ ];
	main.selectItem = selectItem;
	main.toggleItemsList = toggleItemsList;
	main.showActions = showActions;
	main.title = $state.current.data.title;
	main.showSimpleToast = showSimpleToast;
	main.toggleRightSidebar = toggleRightSidebar;
	main.loginModal = loginModal;
	main.newUserModal = newUserModal;
	main.isAuthed = authed;
	main.notifications = [];
	main.signInAuthed = signinService.isAuthed();
	main.browserData = {}

	main.enablePush = {
		status:false,
		disabled:true,
		subscription: null,
		endpoint: null,
	};

	navService
	  .loadAllItems()
	  .then(function(menuItems) {
		main.menuItems = [].concat(menuItems);
	  });

	function toggleRightSidebar() {
		$mdSidenav('right').toggle();
	}

	function toggleItemsList() {
	  var pending = $mdBottomSheet.hide() || $q.when(true);

	  pending.then(function(){
		$mdSidenav('left').toggle();
	  });
	}

	function selectItem (item) {
	  main.title = item.name;
	  main.toggleItemsList();
	  main.showSimpleToast(main.title);
	}

	function showActions($event) {
		$mdBottomSheet.show({
		  parent: angular.element(document.getElementById('content')),
		  templateUrl: 'views/partials/bottomSheet.html',
		  controller: [ '$mdBottomSheet', SheetController],
		  controllerAs: "main",
		  bindToController : true,
		  targetEvent: $event
		}).then(function(clickedItem) {
		  clickedItem && $log.debug( clickedItem.name + ' clicked!');
		});

		function SheetController( $mdBottomSheet ) {
		  var main = this;

		  main.actions = [
			{ name: 'Share', icon: 'share', url: 'https://twitter.com/intent/tweet?text=Angular%20Material%20Dashboard%20https://github.com/flatlogic/angular-material-dashboard%20via%20@flatlogicinc' },
			{ name: 'Star', icon: 'star', url: 'https://github.com/flatlogic/angular-material-dashboard/stargazers' }
		  ];

		  main.performAction = function(action) {
			$mdBottomSheet.hide(action);
		  };
		}
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
			main.isAuthed = $auth.isAuthenticated();
			if(response.auth) {
				main.userInfo = response.userInfo;
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
				main.userInfo = response.userInfo;
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
		//main.userInfo = $auth.getPayload().data;
		main.checkServiceWorker();
		//main.StartEventSource();
		if(main.userInfo.first_login) {
			newUserModal();
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
	});
	$rootScope.$on('checkAuth', function(event, data) {
		console.info('Chcking Auth');
		if(main.isAuthed && !$auth.isAuthenticated()) {
			$rootScope.$broadcast('logOutAction',data);
		}
	});


}
