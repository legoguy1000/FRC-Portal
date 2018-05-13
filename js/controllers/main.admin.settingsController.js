angular.module('FrcPortal')
.controller('main.admin.settingsController', ['$state', '$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'signinService', '$mdDialog','$stateParams','$mdToast',
	mainAdminSettingsController
]);
function mainAdminSettingsController($state, $timeout, $q, $scope, schoolsService, usersService, signinService, $mdDialog, $stateParams,$mdToast) {
	var vm = this;

	vm.userInfo = {};
	vm.seasonInfo = null;
	vm.loadingUser = false;

  vm.selectedItem  = null;
  vm.searchText    = null;
	vm.query = {
		filter: '',
		limit: 1,
		order: '-year',
		page: 1
	};
	vm.limitOptions = [1,5,10];
	vm.currentMenu = 'login';

	vm.selectSettingMenu = function(menu) {
		vm.currentMenu = menu;
	}


}
