angular.module('FrcPortal')
.controller('main.profileController', ['$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'signinService', '$mdDialog', '$auth',
	mainProfileController
]);
function mainProfileController($timeout, $q, $scope, schoolsService, usersService, signinService, $mdDialog, $auth) {
    var vm = this;

    vm.selectedItem  = null;
    vm.searchText    = null;
    vm.querySearch   = querySearch;
	vm.notificationEndpoints = [];
	vm.linkedAccounts = [];
	vm.seasonInfo = {};
	vm.rmhData = {};
	vm.showPastReqs = false;
	vm.checkPinNum = null;
	vm.checkPinMsg = '';
	vm.changePinNum = null;
	vm.changePinMsg = '';
	vm.query = {
		filter: '',
		limit: 5,
		order: '-year',
		page: 1
	};
	vm.loading = {
		note_types: false,
		note_devices: false,
		profile: false,
		rmh: false,
	}

	vm.notificationOptions = {
		sign_in_out: 'Clock In & Out',
		new_season: 'New Season',
		new_event: 'New Event',
		joint_team: 'Season - Join team',
		dues: 'Season - Pay Dues',
		stims: 'Season - Complete STIMS/TIMS',
	}

	if($scope.main.userInfo.school_id != null) {
			$scope.main.userInfo.schoolData = {
			school_id: $scope.main.userInfo.school_id,
			school_name: $scope.main.userInfo.school_name,
		}
	}
	function querySearch (query) {
		return schoolsService.searchAllSchools(query);
	}

	vm.getProfileInfo = function() {
		vm.loading.note_devices = true;
		usersService.getProfileInfo().then(function(response){
			vm.notificationEndpoints = response.data.endpoints;
			vm.linkedAccounts = response.data.linkedAccounts;
			vm.seasonInfo = response.data.seasonInfo;
			vm.notificationPreferences = response.data.notificationPreferences;
			vm.loading.note_devices = false;
		});
	}
	vm.getProfileInfo();

	vm.updateUser = function() {
		vm.loading.profile = true;
		usersService.updateUserPersonalInfo($scope.main.userInfo).then(function(response) {
			vm.loading.profile = false;
			if(response.status) {

			}
		});
	}

	vm.showSeasonHoursGraph = function(ev,year) {
		$mdDialog.show({
			controller: SeasonHoursGraphModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/SeasonHoursGraphModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				data: {
					'user_id': $scope.main.userInfo.user_id,
					'year': year
				},
			}
		})
		.then(function(answer) {}, function() {});
	}

	vm.checkPin = function() {
		vm.loadingDevices = true;
		vm.checkPinMsg = '';
		var data = {
			pin: vm.checkPinNum
		}
		usersService.checkPin(data).then(function(response){
			vm.checkPinMsg = response.msg;
			if(response.status) {
				vm.checkPinNum = null;
				vm.checkPinForm.$setPristine();
				vm.checkPinForm.$setUntouched();
			}
		});
	}
	vm.changePinMsg = '';
	vm.changePin = function() {
		vm.loadingDevices = true;
		vm.changePinMsg = '';
		var data = {
			pin: vm.changePinNum
		}
		usersService.changePin(data).then(function(response){
			vm.changePinMsg = response.msg;
			if(response.status) {
				vm.changePinNum = null;
				vm.changePinForm.$setPristine();
				vm.changePinForm.$setUntouched();
			}
		});
	}

	vm.updateNotePrefs = function(method,type,value) {
		vm.loading.note_types = true;
		var data = {
			'method': method,
			'type': type,
			'value': value,
		}
		usersService.updateNotificationPreferences(data).then(function(response){
			vm.loading.note_types = false;
		});
	}

	vm.requestMissingHours = function(method,type,value) {
		vm.loading.rmh = true;
		vm.rmhMsg = '';
		var data = vm.rmhData;
		usersService.requestMissingHours(data).then(function(response){
			vm.loading.rmh = false;
			vm.rmhMsg = response.msg;
			if(response.status) {
				vm.rmhData = {};
				vm.rmhForm.$setPristine();
				vm.rmhForm.$setUntouched();
			}
		});
	}

	vm.subscribePush = function() {
	  // Disable the button so it can't be changed while
	  // we process the permission request
	  $scope.main.enablePush.disabled = true;

	  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
		serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly: true})
		  .then(function(subscription) {
			// The subscription was successful

			// TODO: Send the subscription.endpoint to your server
			// and save it to send a push message at a later date

	//	  return sendSubscriptionToServer(subscription);
			var rawKey = subscription.getKey ? subscription.getKey('p256dh') : '';
			var key = rawKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawKey))) : '';
			var rawAuthSecret = subscription.getKey ? subscription.getKey('auth') : '';
			var authSecret = rawAuthSecret ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawAuthSecret))) : '';
			var endpoint = subscription.endpoint;
			var data = {'endpoint':endpoint, 'key':key, 'authSecret':authSecret};
			usersService.deviceNotificationSubscribe(data).then(function(response){

			});
			console.log(data);
			$scope.$apply( function () {
				$scope.main.enablePush.subscription = subscription;
				$scope.main.enablePush.status = true;
				$scope.main.enablePush.disabled = false;
				$scope.main.enablePush.endpoint = endpoint;
			});
		  })
		  .catch(function(e) {
			if (Notification.permission === 'denied') {
			  // The user denied the notification permission which
			  // means we failed to subscribe and the user will need
			  // to manually change the notification permission to
			  // subscribe to push messages
			  console.warn('Permission for Notifications was denied');
			  $scope.main.enablePush.disabled = true;
			} else {
			  // A problem occurred with the subscription; common reasons
			  // include network errors, and lacking gcm_sender_id and/or
			  // gcm_user_visible_only in the manifest.
			  console.error('Unable to subscribe to push.', e);
			  $scope.main.enablePush.disabled = false;
			}
		  });
	  });
	}

	vm.unsubscribePush = function() {
		$scope.main.enablePush.disabled = true;
		if($scope.main.enablePush.status && $scope.main.enablePush.subscription) {
			$scope.main.enablePush.subscription.unsubscribe().then(function(event) {
				console.log('Unsubscribed!', event);
				var data = {'endpoint':$scope.main.enablePush.endpoint};
				usersService.deviceNotificationUnsubscribe(data).then(function(response){

				});
				$scope.$apply( function () {
					$scope.main.enablePush.status = false;
					$scope.main.enablePush.disabled = false;
				});
			}).catch(function(error) {
				console.log('Error unsubscribing', error);
			});
		}
	}



	vm.showDeviceEdit = function(ev, device) {
		// Appending dialog to document.body to cover sidenav in docs app
		var confirm = $mdDialog.prompt()
			.title('Edit Device')
			.textContent('Input a label for the device below.')
			.placeholder('Device Label')
			.ariaLabel('Device Label')
			.initialValue(device.label)
			.targetEvent(ev)
			.required(true)
			.ok('Submit')
			.cancel('Cancel');
		$mdDialog.show(confirm).then(function(result) {
			var data = {
				note_id: device.note_id,
				label: result
			}
			usersService.editDeviceLabel(data).then(function(response){
					if(response.status) {
						vm.notificationEndpoints = response.endpoints;
					}
			});
		}, function() {

		});
	};

	vm.showDeviceDelete = function(ev, device) {
	// Appending dialog to document.body to cover sidenav in docs app
	var confirm = $mdDialog.confirm()
		.title('Device Deletetion Confirmation')
		.textContent('Please confirm that you would like to delete device '+device.label+'.  If you can readd the device again later.')
		.ariaLabel('Delete Device')
		.targetEvent(ev)
		.ok('Delete')
		.cancel('Cancel');
		$mdDialog.show(confirm).then(function() {

		}, function() {

		});
	};

	vm.linkAccount = function(provider) {
	  $auth.link(provider,{'link_account':true, 'provider':provider})
		.then(function(response) {
			if(response.data.status) {
				vm.linkedAccounts = response.data.linkedAccounts;
			}
		})
		.catch(function(response) {
		  // Handle errors here.
		});
	};
}
