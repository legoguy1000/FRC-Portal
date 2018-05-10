angular.module('FrcPortal')
.controller('main.profileController', ['$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'signinService', '$mdDialog', '$auth','$mdToast',
	mainProfileController
]);
function mainProfileController($timeout, $q, $scope, schoolsService, usersService, signinService, $mdDialog, $auth, $mdToast) {
    var vm = this;

  vm.selectedItem  = null;
  vm.searchText    = null;
  vm.querySearch   = querySearch;
	vm.notificationEndpoints = [];
	vm.linkedAccounts = [];
	vm.seasonInfo = [];
	vm.eventInfo = [];
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

	vm.updateUser = function() {
		vm.loading.profile = true;
		usersService.updateUserPersonalInfo($scope.main.userInfo).then(function(response) {
			vm.loading.profile = false;
			if(response.status) {
				$mdToast.show(
		      $mdToast.simple()
		        .textContent(response.msg)
		        .position('top right')
		        .hideDelay(3000)
		    );
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
