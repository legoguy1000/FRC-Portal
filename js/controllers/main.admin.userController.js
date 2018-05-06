angular.module('FrcPortal')
.controller('main.admin.userController', ['$state', '$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'signinService', '$mdDialog','$stateParams',
	mainAdminUserController
]);
function mainAdminUserController($state, $timeout, $q, $scope, schoolsService, usersService, signinService, $mdDialog, $stateParams) {
    var vm = this;

	vm.user_id = $stateParams.user_id;
	vm.userInfo = {};
	vm.seasonInfo = {};

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
		limit: 1,
		order: '-year',
		page: 1
	};
	vm.limitOptions = [1,5,10];


	function querySearch (query) {
		return schoolsService.searchAllSchools(query);
	}

	vm.getProfileInfo = function() {
		vm.loadingDevices = true;
		usersService.getProfileInfo($stateParams.user_id).then(function(response){
			vm.userInfo = response.data;
			if(vm.userInfo.school_id != null) {
			/*	vm.userInfo.schoolData = {
					school_id: vm.userInfo.school_id,
					school_name: vm.userInfo.school_name,
				} */
			}
		});
	}
	vm.getProfileInfo();

	vm.getUserAnnualRequirements = function() {
		vm.loadingDevices = true;
		usersService.getUserAnnualRequirements($stateParams.user_id).then(function(response){
			vm.seasonInfo = response.data;
		});
	}

	vm.updateUser = function() {
		vm.loadingDevices = true;
		usersService.updateUserPersonalInfo(vm.userInfo).then(function(response) {
			vm.loadingDevices = false;
			if(response.status) {

			}
		});
	}

	vm.deleteUser = function() {
		var data = {
			user_id: vm.userInfo.user_id,
		};
		var confirm = $mdDialog.confirm()
          .title('Delete User "'+vm.userInfo.full_name+'"')
          .textContent('Are you sure you want to delete user "'+vm.userInfo.full_name+'"?  This action is unreversable and any accumulated hours will be removed.'	)
          .ariaLabel('Delete User')
          .ok('Delete')
          .cancel('Cancel');
    $mdDialog.show(confirm).then(function() {
			usersService.deleteUser(data).then(function(response) {
				if(response.status) {
					$mdDialog.show(
			      $mdDialog.alert()
			        .title('User Deleted')
			        .textContent('User "'+vm.userInfo.full_name+'" has been deleted.  You will now be redirected to the user list.')
			        .ariaLabel('User Deleted')
			        .ok('OK')
			    ).then(function() {
			      $scope.admin.clickBack();
						$state.go('main.admin.users');
			    }, function() {});
				}
			});
    }, function() {});
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
}
