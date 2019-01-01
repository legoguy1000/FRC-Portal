angular.module('FrcPortal')
.controller('main.admin.settingsController', ['$state', '$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'settingsService', '$mdDialog','$stateParams','$mdToast','Upload',
	mainAdminSettingsController
]);
function mainAdminSettingsController($state, $timeout, $q, $scope, schoolsService, usersService, settingsService, $mdDialog, $stateParams,$mdToast,Upload) {
	var vm = this;

	vm.userInfo = {};
	vm.seasonInfo = null;
	vm.loadingUser = false;
	vm.loading = false;
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
	vm.settings = {
		'team': {},
		'login': {},
		'notification': {},
		'other': {},
		'cronjob': {},
	};
	vm.serviceAccountCredentials = {};
	vm.timezones = [];

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
	vm.getSettingBySection('other');
	vm.getSettingBySection('cronjob');

	vm.getAllTimezones = function () {
		settingsService.getAllTimezones().then(function(response){
			vm.timezones = response;
		});
	};
	vm.getAllTimezones();

	vm.getServiceAccountCredentials = function () {
		settingsService.getServiceAccountCredentials().then(function(response){
			vm.serviceAccountCredentials = response.data;
		});
	};
	vm.getServiceAccountCredentials();

	vm.updateSettingBySection = function (section) {
		vm.loading = true;
		var data = {
			'section': section,
			'data': vm.settings[section]
		}
		settingsService.updateSettingBySection(data).then(function(response){
			vm.loading = false;
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
			//vm.settings[section] = response.data;
		});
	};
	// upload on file select or drop
	vm.uploadSAFile = function (file) {
			Upload.upload({
					url: 'api/settings/serviceAccountCredentials',
					data: {file: file}
			}).then(function (resp) {
				var response = resp.data;
				if(response.status) {
					vm.serviceAccountCredentials = {
						client_email: response.data.client_email
					};
				}
				$mdToast.show(
		      $mdToast.simple()
		        .textContent(response.msg)
		        .position('top right')
		        .hideDelay(3000)
		    );
					//console.log('Success ' + resp.config.data.file.name + 'uploaded. Response: ' + resp.data);
			}, function (resp) {
					console.log('Error status: ' + resp.status);
			}, function (evt) {
					var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
					console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file.name);
			});
	};
/*
	vm.showServiceAccountModal = function(ev) {
		$mdDialog.show({
			controller: serviceAccountModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/serviceAccountModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				credentials: vm.serviceAccountCredentials
			}
		})
		.then(function(response) {
			vm.serviceAccountCredentials = response;
		}, function() { });
	};
*/
	vm.testSlack = function() {
		vm.loading = false;
		settingsService.testSlack().then(function(response){
			vm.loading = false;
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
			//vm.settings[section] = response.data;
		});
	}

	vm.searchText    = null;
		/**
	 * Create filter function for a query string
	 */
	function createFilterFor(query) {
		var lowercaseQuery = query.toLowerCase();

		return function filterFn(tz) {
			return (tz.toLowerCase().indexOf(lowercaseQuery) != -1);
		};
	}

		/**
	 * Search for states... use $timeout to simulate
	 * remote dataservice call.
	 */
	vm.TzSearch = function (query) {
		var results = query ? vm.timezones.filter( createFilterFor(query) ) : vm.timezones;
		return results;
	}

}
