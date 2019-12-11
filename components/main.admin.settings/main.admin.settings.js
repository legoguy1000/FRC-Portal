angular.module('FrcPortal')
.controller('main.admin.settingsController', ['$rootScope', '$state', '$timeout', '$q', '$scope', 'schoolsService', 'usersService', 'settingsService', '$mdDialog','$stateParams','$mdToast','Upload','generalService','configItems',
	mainAdminSettingsController
]);
function mainAdminSettingsController($rootScope, $state, $timeout, $q, $scope, schoolsService, usersService, settingsService, $mdDialog, $stateParams,$mdToast,Upload,generalService, configItems) {
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
	/*
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
	} */
	vm.serviceAccountCredentials = {};
	vm.firstcredentials = {};
	vm.timezones = [];

	vm.selectSettingMenu = function(menu) {
		vm.currentMenu = menu;
	}
	vm.branchOptions = [];
	vm.team_colors = {
		team_color_primary: '',
		team_color_secondary: ''
	};
/*	vm.getAllSettings = function () {
		vm.loading = true;
		settingsService.getAllSettings().then(function(response){
			vm.loading = false;
			vm.settings = response.data.grouped;
		});
	};
	vm.getAllSettings(); */

	vm.getSettingBySection = function (section, callback) {
		vm.loading = true;
		settingsService.getSettingBySection(section).then(function(response){
			vm.loading = false;
			vm.settings[section] = response.data;
			if(callback != undefined && callback != null) {
				callback();
			}
		});
	};

	vm.getSettingBySection('team',null);
	vm.getSettingBySection('login',null);
	vm.getSettingBySection('notification',null);
	vm.getSettingBySection('other',null);
	vm.getSettingBySection('cronjob',null);

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

	vm.getFirstPortalCredentials = function () {
		settingsService.getFirstPortalCredentials().then(function(response){
			vm.firstcredentials = response.data;
		});
	};
	vm.getFirstPortalCredentials();

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
			if(section == 'team') {
				updateColors();
			}
		});
	};

	function updateColors() {
		if(configItems.team_color_primary != vm.settings.team.team_color_primary) {
			configItems.team_color_primary = vm.settings.team.team_color_primary
		}
		if(configItems.team_color_secondary != vm.settings.team.team_color_secondary) {
			configItems.team_color_secondary = vm.settings.team.team_color_secondary
		}
	}
	// upload on file select or drop
	vm.uploadSAFile = function (file) {
			vm.loading = true;
			if(file == null) {
				vm.loading = false;
				return;
			}
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
				vm.loading = false;
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

	/*
	vm.getUpdateBranches = function() {
		settingsService.getUpdateBranches().then(function(response){
			vm.branchOptions = response.data;
		});
	}
	vm.getUpdateBranches();

	vm.checkUpdates = function(manual) {
		if(manual == true) { vm.loading = true; }
		settingsService.checkUpdates().then(function(response){
			var latest_release = response.data.latest_release;
			var latest_version = response.data.latest_version;
			if(!latest_release && response.data.update_available==false) {
				latest_release = 'v'+vm.update.current_version;
			}
			vm.update.latest_version = latest_release+'-'+latest_version.substring(0, 7);
			if(manual == true) { vm.loading = false; }
		});
	}
	vm.checkUpdates();
	*/
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
			templateUrl: 'components/oAuthCredentialModal/oAuthCredentialModal.html',
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
	vm.showFirstPortalCredentialsModal = function(ev) {
		$mdDialog.show({
			controller: firstPortalCredentialModalController,
			controllerAs: 'vm',
			templateUrl: 'components/firstPortalCredentialModal/firstPortalCredentialModal.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: { }
		}).then(function(response){
			if(response.status) {
				vm.firstcredentials = response.data;
			}
		});
	}

	vm.removeFirstCredentials = function() {
		vm.loading = true;
		settingsService.removeFirstPortalCredentials().then(function(response) {
			if(response.status) {
				vm.firstcredentials = response.data;
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
			vm.loading = false;
		});
	}

	vm.removeServiceAccountCredentials = function() {
		vm.loading = true;
		settingsService.removeServiceAccountCredentials().then(function(response) {
			if(response.status) {
				vm.serviceAccountCredentials = response.data;
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
			vm.loading = false;
		});
	}
}
