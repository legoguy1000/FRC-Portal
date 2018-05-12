angular.module('FrcPortal')
.controller('main.profileController', ['$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'signinService', '$mdDialog', '$auth','$mdToast',
	mainProfileController
]);
function mainProfileController($timeout, $q, $scope, schoolsService, usersService, signinService, $mdDialog, $auth, $mdToast) {
    var vm = this;

  vm.selectedItem  = null;
  vm.searchText    = null;
  vm.querySearch   = querySearch;
	vm.notificationPreferences = [];
	vm.linkedAccounts = [];
	vm.seasonInfo = [];
	vm.eventInfo = [];
	vm.limitOptions = [1,5,10];
	vm.rmhData = {};
	vm.changePinNum = null;
	vm.querySeasons = {
		filter: '',
		limit: 1,
		order: '-year',
		page: 1
	};
	vm.queryEvents = {
		filter: '',
		limit: 5,
		order: '-event_start',
		page: 1
	};
	vm.loading = {
		note_types: false,
		note_devices: false,
		profile: false,
		rmh: false,
	}
	vm.user = $scope.main.userInfo;

	vm.notificationOptions = {
		sign_in_out: 'Clock In & Out',
		new_season: 'New Season',
		new_event: 'New Event',
		joint_team: 'Season - Join team',
		dues: 'Season - Pay Dues',
		stims: 'Season - Complete STIMS/TIMS',
	}

	function querySearch (query) {
		return schoolsService.searchAllSchools(query);
	}

	vm.getProfileInfo = function() {
		vm.loading.note_devices = true;
		usersService.getProfileInfo($scope.main.userInfo.user_id).then(function(response) {
			//vm.user = response.data;
			//vm.notificationEndpoints = response.data.endpoints;
			//vm.linkedAccounts = response.data.linkedAccounts;
			//vm.notificationPreferences = response.data.notificationPreferences;

			vm.loading.note_devices = false;
		});
	}
	vm.getProfileInfo();

	vm.getUserAnnualRequirements = function() {
		vm.loading.note_devices = true;
		usersService.getUserAnnualRequirements($scope.main.userInfo.user_id).then(function(response) {
			vm.seasonInfo = response.data;
			vm.loading.note_devices = false;
		});
	}
	vm.getUserAnnualRequirements();

	vm.getUserEventRequirements = function() {
		vm.loading.note_devices = true;
		usersService.getUserEventRequirements($scope.main.userInfo.user_id).then(function(response) {
			vm.eventInfo = response.data;
			vm.loading.note_devices = false;
		});
	}
	vm.getUserEventRequirements();

	vm.getUserLinkedAccounts = function() {
		vm.loading.note_devices = true;
		usersService.getUserLinkedAccounts($scope.main.userInfo.user_id).then(function(response) {
			vm.linkedAccounts = response.data;
			vm.loading.note_devices = false;
		});
	}
	vm.getUserLinkedAccounts();

	vm.getUserNotificationPreferences = function() {
		vm.loading.note_devices = true;
		usersService.getUserNotificationPreferences($scope.main.userInfo.user_id).then(function(response) {
			vm.notificationPreferences = response.data;
			vm.loading.note_devices = false;
		});
	}
	vm.getUserNotificationPreferences();

	vm.updateUser = function() {
		vm.loading.profile = true;
		usersService.updateUserPersonalInfo($scope.main.userInfo).then(function(response) {
			vm.loading.profile = false;
			if(response.status) {
				$window.localStorage['userInfo'] = angular.toJson(response.data);
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
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

	vm.changePin = function() {
		vm.loadingDevices = true;
		var data = {
			user_id: $scope.main.userInfo.user_id,
			pin: vm.changePinNum
		}
		usersService.changePin(data).then(function(response){
			if(response.status) {
				vm.changePinNum = null;
				vm.changePinForm.$setPristine();
				vm.changePinForm.$setUntouched();
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	}

	vm.updateNotePrefs = function(method,type,value) {
		vm.loading.note_types = true;
		var data = {
			'method': method,
			'type': type,
			'value': value,
			'user_id': $scope.main.userInfo.user_id
		}
		usersService.updateNotificationPreferences(data).then(function(response){
			vm.loading.note_types = false;
		});
	}

	vm.requestMissingHours = function(method,type,value) {
		vm.loading.rmh = true;
		var data = vm.rmhData;
		data.user_id = $scope.main.userInfo.user_id;
		usersService.requestMissingHours(data).then(function(response){
			vm.loading.rmh = false;
			if(response.status) {
				vm.rmhData = {};
				vm.rmhForm.$setPristine();
				vm.rmhForm.$setUntouched();
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	}

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
