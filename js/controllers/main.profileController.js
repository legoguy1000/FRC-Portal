angular.module('FrcPortal')
.controller('main.profileController', ['$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'signinService', '$mdDialog', '$auth','$mdToast', '$stateParams', '$window', 'generalService',
	mainProfileController
]);
function mainProfileController($timeout, $q, $scope, schoolsService, usersService, signinService, $mdDialog, $auth, $mdToast, $stateParams, $window, generalService) {
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
	vm.selectedTab = 0;
	if($stateParams.firstLogin) {
		vm.selectedTab = 3;
		var dialog = $mdDialog.alert()
								.clickOutsideToClose(false)
								.textContent('Please fill in the missing items of your profile.  Students, please check that your student ID number has been set as your PIN by using the change PIN form.  You should receive an "error".  Mentors (and students who want to change their PIN), please use this form to set up a new PIN.')
								.ariaLabel('First Login')
								.ok('OK');
		$mdDialog.show(dialog);
	}	else if($stateParams.linkedAccounts) {
		vm.selectedTab = 1;
	}
	console.info($stateParams.selectedTab);
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
		linkedAccounts: false,
	}
	vm.user = $scope.main.userInfo;

	vm.notificationOptions = {
		sign_in_out: 'Clock In & Out',
		new_season: 'New Season',
		new_event: 'New Event',
		joint_team: 'Membership Form',
		dues: 'Annual Dues',
		stims: 'STIMS/TIMS Registration',
		event_registration: 'Event Registration'
	}

	function querySearch (query) {
		var data = {
			filter: query,
			limit: 0,
			order: 'school_name',
			page: 1,
			listOnly: true
		};
		return schoolsService.getAllSchoolsFilter($.param(data));
	}

/*
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
	vm.getProfileInfo(); */

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
		vm.loading.linkedAccounts = true;
		usersService.getUserLinkedAccounts($scope.main.userInfo.user_id).then(function(response) {
			vm.linkedAccounts = response.data;
			vm.loading.linkedAccounts = false;
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
		var data = {
			user_id: vm.user.user_id,
			fname: vm.user.fname,
			lname: vm.user.lname,
			email: vm.user.email,
			team_email: vm.user.team_email,
			phone: vm.user.phone,
			user_type: vm.user.user_type,
			gender: vm.user.gender,
			school_id: vm.user.school != null ? vm.user.school.school_id : null,
			grad_year: vm.user.grad_year,
		}
		usersService.updateUserPersonalInfo(data).then(function(response) {
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

	vm.deleteUserLinkedAccount = function(auth_id) {
		vm.loading.linkedAccounts = true;
		var data = {
			user_id: $scope.main.userInfo.user_id,
			auth_id: auth_id
		}
		usersService.deleteUserLinkedAccount(data).then(function(response){
			if(response.status) {
				vm.linkedAccounts = response.data;
			}
			vm.loading.linkedAccounts = false;
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	}

	vm.showSeasonHoursGraph = function(ev,year) {
		generalService.showSeasonHoursGraph(ev, $scope.main.userInfo.user_id, year);
		/*$mdDialog.show({
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
		.then(function() {}, function() {});*/
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
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	}

	vm.requestMissingHours = function() {
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

	vm.loginModal = function(ev) {
		$mdDialog.show({
			controller: loginModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/loginModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				loginData: {
					loading: false,
					state: 'main.profile',
					state_params: {
						linkedAccounts: true
					}
				}
			}
		})
		.then(function(response) {

		}, function() {
			$log.info('Dialog dismissed at: ' + new Date());
		});
	}
}
