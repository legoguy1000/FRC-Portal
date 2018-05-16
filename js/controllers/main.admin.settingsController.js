angular.module('FrcPortal')
.controller('main.admin.settingsController', ['$state', '$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'settingsService', '$mdDialog','$stateParams','$mdToast',
	mainAdminSettingsController
]);
function mainAdminSettingsController($state, $timeout, $q, $scope, schoolsService, usersService, settingsService, $mdDialog, $stateParams,$mdToast) {
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
	vm.currentMenu = 'team';

	vm.selectSettingMenu = function(menu) {
		vm.currentMenu = menu;
	}

/*	vm.getAllSettings = function () {
		vm.loading = true;
		settingsService.getAllSettings().then(function(response){
			vm.loading = false;
			vm.settings = response.data.grouped;
		});
	};
	vm.getAllSettings(); */

	vm.getSettingBySection = function (section) {
		vm.loading = true;
		settingsService.getSettingBySection(section).then(function(response){
			vm.loading = false;
			vm.settings[section] = response.data;
		});
	};
	vm.getSettingBySection('team');
	vm.getSettingBySection('login');
	vm.getSettingBySection('notification');

	vm.updateSettingBySection = function (section) {
		vm.loading = true;
		var data = {
			'section': section,
			'data': vm.settings[section]
		}
		settingsService.updateSettingBySection(data).then(function(response){
			vm.loading = false;
			vm.settings[section] = response.data;
		});
	};
}
