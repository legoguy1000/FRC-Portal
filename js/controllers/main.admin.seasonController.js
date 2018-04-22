angular.module('FrcPortal')
.controller('main.admin.seasonController', ['$timeout', '$q', '$scope', '$state', 'seasonsService', '$mdDialog', '$log','$stateParams',
	mainAdminSeasonController
]);
function mainAdminSeasonController($timeout, $q, $scope, $state, seasonsService, $mdDialog, $log,$stateParams) {
    var vm = this;

	vm.filter = {
		show: false,
	};
	vm.showFilter = function () {
		vm.filter.show = true;
		vm.query.filter = '';
	};
	vm.removeFilter = function () {
		vm.filter.show = false;
		vm.query.filter = '';

		if(vm.filter.form.$dirty) {
			vm.filter.form.$setPristine();
		}
	};

	vm.season_id = $stateParams.season_id;
	vm.season = {};
	vm.getSeason = function () {
		vm.promise = seasonsService.getSeason(vm.season_id).then(function(response){
			vm.season = response.data;
		});
	};
	vm.selectedUsers = [];

	vm.getSeason();
	vm.limitOptions = [5,10,25,50,100];
	vm.query = {
		filter: '',
		limit: 5,
		order: 'full_name',
		page: 1
	};
	vm.fabOpen = false;

	vm.toggleAnnualReqs = function (req) {
		var data = {
			'season_id': vm.season_id,
			'users': vm.selectedUsers,
			'requirement':req
		}
		vm.promise = seasonsService.toggleAnnualReqs(data).then(function(response){
			if(response.status && response.data) {
				vm.season.requirements.data = response.data;
			}

		});
	};

	vm.updateSeasonMembershipForm = function () {
		var data = {
			'year': vm.season.year
		};
		vm.promise = seasonsService.updateSeasonMembershipForm(data).then(function(response){
			vm.season.join_spreadsheet = response.data.join_spreadsheet;
		});
	};
}
