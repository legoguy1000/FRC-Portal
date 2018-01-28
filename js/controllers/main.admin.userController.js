angular.module('FrcPortal')
.controller('main.admin.userController', ['$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'signinService', '$mdDialog','$stateParams',
	mainAdminUserController
]);
function mainAdminUserController($timeout, $q, $scope, schoolsService, usersService, signinService, $mdDialog, $stateParams) {
    var vm = this;

	vm.user_id = $stateParams.user_id;
	vm.userInfo = {};

    vm.selectedItem  = null;
    vm.searchText    = null;
    vm.querySearch   = querySearch;
	vm.notificationEndpoints = [];
	vm.linkedAccounts = [];
	vm.seasonInfo = {};
	vm.loadingDevices = false;
	vm.showPastReqs = false;
	vm.query = {
		filter: '',
		limit: 5,
		order: '-year',
		page: 1
	};



	function querySearch (query) {
		return schoolsService.searchAllSchools(query);
	}

	vm.getProfileInfo = function() {
		vm.loadingDevices = true;
		usersService.getProfileInfo($stateParams.user_id).then(function(response){
			vm.userInfo = response.data;
			if(vm.userInfo.school_id != null) {
				vm.userInfo.schoolData = {
					school_id: vm.userInfo.school_id,
					school_name: vm.userInfo.school_name,
				}
			}
		});
	}
	vm.getProfileInfo();

	vm.updateUser = function() {
		vm.loadingDevices = true;
		usersService.updateUserPersonalInfo(vm.userInfo).then(function(response) {
			vm.loadingDevices = false;
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
					'user_id': vm.user_id,
					'year': year
				},
			}
		})
		.then(function(answer) {}, function() {});
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
}
