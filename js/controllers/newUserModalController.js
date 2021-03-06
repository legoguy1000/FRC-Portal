angular.module('FrcPortal')
.controller('newUserModalController', ['$rootScope','$mdDialog', '$scope', 'userInfo', 'usersService', 'schoolsService', '$window',
	newUserModalController
]);
function newUserModalController($rootScope,$mdDialog,$scope,userInfo,usersService,schoolsService,$window) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}

	vm.userInfo = userInfo;

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
				$window.localStorage['userInfo'] = angular.toJson(response.data);
				$mdDialog.hide(response);
			}
		});
	}
}
