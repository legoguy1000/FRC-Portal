angular.module('FrcPortal')
.controller('main.admin.settingsController', ['$rootScope', '$state', '$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'settingsService', '$mdDialog','$stateParams','$mdToast','Upload','generalService',
	mainAdminSettingsController
]);
function mainAdminSettingsController($rootScope, $state, $timeout, $q, $scope, schoolsService, usersService, settingsService, $mdDialog, $stateParams,$mdToast,Upload,generalService) {
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
	vm.update = {
		branch_name: $scope.main.versionInfo.branch_name,
		latest_version: null,
		current_version: $scope.main.versionInfo.current_version,
		current_tag: $scope.main.versionInfo.tag,
	}
	vm.versionInfo = {};
	if(vm.update.branch_name == undefined) {
		generalService.getVersion().then(function(response) {
			vm.versionInfo = response;
			vm.update.branch_name = vm.versionInfo.branch_name;
			vm.update.current_version = response.current_version;
			vm.update.current_tag = response.tag;
		});
	}
	vm.serviceAccountCredentials = {};
	vm.timezones = [];

	vm.selectSettingMenu = function(menu) {
		vm.currentMenu = menu;
	}
	vm.branchOptions = [];

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

	vm.getUpdateBranches = function() {
		settingsService.getUpdateBranches().then(function(response){
			vm.branchOptions = response.data;
		});
	}
	vm.getUpdateBranches();

	vm.checkUpdates = function() {
		settingsService.checkUpdates().then(function(response){
			var latest_release = response.data.latest_release;
			var latest_version = response.data.latest_version;
			if(latest_release==null && response.data.update_available==false) {
				latest_release = vm.update.current_version;
			}
			vm.update.latest_version = latest_release+'-'+latest_version.substring(0, 6);
		});
	}
	vm.checkUpdates();

	$rootScope.$on('400BadRequest', function(event,response) {
		vm.loading = false;
		$mdToast.show(
			$mdToast.simple()
				.textContent(response.msg)
				.position('top right')
				.hideDelay(3000)
		);
	});
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

	vm.showOAuthCredentialsModal = function(ev,provider) {
		$mdDialog.show({
			controller: oAuthCredentialModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/oAuthCredentialModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				provider: provider,
			}
		}).then(function(response){
			if(response.status) {
				vm.updateSettingBySection('login');
			}
		});
	}
}
