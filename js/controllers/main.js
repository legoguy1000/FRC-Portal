angular.module('FrcPortal')
.controller('mainController', [
	'$rootScope', '$auth', 'navService', '$mdSidenav', '$mdBottomSheet', '$log', '$q', '$state', '$mdToast', '$mdDialog', 'authed', 'usersService', '$scope', 'signinService',
	mainController
]);
function mainController($rootScope, $auth, navService, $mdSidenav, $mdBottomSheet, $log, $q, $state, $mdToast, $mdDialog, authed, usersService, $scope, signinService) {
	var main = this;

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
		.then(function(data) {
			main.isAuthed = data.auth;
			if(data.auth) {
				var data = {
					'allActions': true,
				}
				$rootScope.$broadcast('afterLoginAction',data);
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
				userInfo: $auth.getPayload().data,
			}
		})
		.then(function(data) {

		}, function() {
			$log.info('Dialog dismissed at: ' + new Date());
		});
	}


	main.initServiceWorkerState = function() {
		console.log('Initializing');
		// Are Notifications supported in the service worker?
	/*	if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
			console.warn('Notifications aren\'t supported.');
			return false;
		}

		// Check the current Notification permission.
		// If its denied, it's a permanent block until the
		// user changes the permission
		if (Notification.permission === 'denied') {
			console.warn('The user has blocked notifications.');
			return false;
		}

		// Check if push messaging is supported
		if (!('PushManager' in window)) {
			console.warn('Push messaging isn\'t supported.');
			return false;
		} */

		// We need the service worker registration to check for a subscription
		navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
			console.log('Service Worker Ready');
			// Do we already have a push message subscription?
		/*	serviceWorkerRegistration.pushManager.getSubscription()
			.then(function(subscription) {
				console.log('Checkig Subscription');
				// Enable any UI which subscribes / unsubscribes from
				// push messages.
			//	var pushButton = document.querySelector('.js-push-button');
			//	pushButton.disabled = false;

				if (!subscription) {
					console.log('Not Scubscribed');
					// We aren't subscribed to push, so set UI
					// to allow the user to enable push
					return false;
				}

				//console.log(subscription);
				// Keep your server in sync with the latest subscriptionId
				// sendSubscriptionToServer(subscription);
				var rawKey = subscription.getKey ? subscription.getKey('p256dh') : '';
				var key = rawKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawKey))) : '';
				var rawAuthSecret = subscription.getKey ? subscription.getKey('auth') : '';
				var authSecret = rawAuthSecret ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawAuthSecret))) : '';
				var endpoint = subscription.endpoint;
				var data = {'endpoint':endpoint, 'key':key, 'authSecret':authSecret};
				main.browserData = data;
				$scope.$apply( function () {
					main.enablePush.subscription = subscription;
					main.enablePush.status = true;
					main.enablePush.disabled = false;
					main.enablePush.endpoint = endpoint;
				});
				usersService.deviceNotificationUpdateEndpoint(data).then(function(response){
					console.log('Endpoint Updated');
				});
				console.log(data); */
				return true;
			})
			.catch(function(err) {
				//console.warn('Error during getSubscription()', err);
			});
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
		main.userInfo = $auth.getPayload().data;
		main.checkServiceWorker();
		//main.StartEventSource();
		if(main.userInfo.newUser) {
			newUserModal();
		}
	}

	if(main.isAuthed) {
		console.info('I\'m Authed');
		var data = {
			'allActions': true,
		}
		loginActions();
	}

	main.logout = function() {
		$rootScope.$broadcast('logOutAction');
		if($state.current.authenticate == true) {
			$state.go('main.home');
		}
		$auth.logout();
	}

	$rootScope.$on('afterLoginAction', function(event, data) {
		console.info('Login Initiated');
		if(data.allActions) {
			loginActions();
		}
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
	$rootScope.$on('updateSigninStatus', function(event, data) {
		console.info('LogOut Initiated');
		main.signInAuthed = signinService.isAuthed();
		if(data.response.status && data.logout) {
			main.logout();
		}
	});

}
