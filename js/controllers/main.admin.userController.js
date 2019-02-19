angular.module('FrcPortal')
.controller('main.admin.userController', ['$rootScope', '$state', '$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'signinService', '$mdDialog','$stateParams','$mdToast', 'generalService',
	mainAdminUserController
]);
function mainAdminUserController($rootScope, $state, $timeout, $q, $scope, schoolsService, usersService, signinService, $mdDialog, $stateParams,$mdToast, generalService) {
    var vm = this;

	vm.user_id = $stateParams.user_id;
	vm.userInfo = {};
	vm.seasonInfo = null;
	vm.loadingUser = false;

  vm.selectedItem  = null;
  vm.searchText    = null;
  vm.querySearch   = querySearch;

	vm.query = {
		filter: '',
		limit: 1,
		order: '-year',
		page: 1
	};
	vm.limitOptions = [1,5,10];


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

	vm.getProfileInfo = function() {
		vm.loadingUser = true;
		usersService.getProfileInfo($stateParams.user_id).then(function(response){
			vm.userInfo = response.data;
			vm.loadingUser = false;
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
		vm.loadingReqs = usersService.getUserAnnualRequirements($stateParams.user_id).then(function(response){
			vm.seasonInfo = response.data;
		});
	}
	vm.getUserAnnualRequirements();


	vm.updateUser = function() {
		vm.loadingUser = true;
		var data = {
			user_id: vm.userInfo.user_id,
			fname: vm.userInfo.fname,
			lname: vm.userInfo.lname,
			email: vm.userInfo.email,
			team_email: vm.userInfo.team_email,
			phone: vm.userInfo.phone,
			user_type: vm.userInfo.user_type,
			gender: vm.userInfo.gender,
			school_id: vm.userInfo.school != null ? vm.userInfo.school.school_id : null,
			grad_year: vm.userInfo.grad_year,
			admin: vm.userInfo.admin,
			status: vm.userInfo.status,
		}
		usersService.updateUserPersonalInfo(data).then(function(response) {
			vm.loadingUser = false;
			if(response.status) {

			}
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
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
			usersService.deleteUser(vm.userInfo.user_id).then(function(response) {
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

	$rootScope.$on('400BadRequest', function(event,response) {
		vm.loadingUser = false;
		$mdToast.show(
			$mdToast.simple()
				.textContent(response.msg)
				.position('top right')
				.hideDelay(3000)
		);
	});

	vm.showSeasonHoursGraph = function(ev,year) {
		generalService.showSeasonHoursGraph(ev, vm.user_id, year);
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
					'user_id': vm.user_id,
					'year': year
				},
			}
		})
		.then(function(answer) {}, function() {}); */
	}
}
