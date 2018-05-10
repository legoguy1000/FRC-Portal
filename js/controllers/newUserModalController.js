angular.module('FrcPortal')
.controller('newUserModalController', ['$rootScope','$mdDialog', '$scope', 'userInfo', 'usersService', 'schoolsService',
	newUserModalController
]);
function newUserModalController($rootScope,$mdDialog,$scope,userInfo,usersService,schoolsService) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}

	vm.userInfo = userInfo;
	if(vm.userInfo.school_id) {
		vm.userInfo.schoolData = {
			school_id: vm.userInfo.school_id,
			school_name: vm.userInfo.school_name,
		}
	}

	console.log(vm.userInfo.schoolData);
	vm.selectedItem  = null;
    vm.searchText    = null;
    vm.querySearch   = querySearch;
	
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

	vm.updateUser = function() {
		usersService.updateUserPersonalInfo(vm.userInfo).then(function(response) {
			if(response.status) {
				vm.cancel();
				$rootScope.$broadcast('afterLoginAction');
			}
		});
	}
}
