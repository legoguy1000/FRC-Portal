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
		seasonsService.updateSeasonMembershipForm(data).then(function(response){
			vm.season.join_spreadsheet = response.data.join_spreadsheet;
		});
	};

	vm.updateSeason = function () {
		var data = {
			'year': vm.season.year,
			'season_id': vm.season.season_id,
			'start_date': vm.season.start_date,
			'bag_day': vm.season.bag_day,
			'end_date': vm.season.end_date,
			'game_logo': vm.season.game_logo,
			'game_name': vm.season.game_name,
			'hour_requirement': vm.season.hour_requirement,
			'join_spreadsheet': vm.season.join_spreadsheet,
		};
		vm.promise = seasonsService.updateSeason(data).then(function(response){
			if(response.status) {
				var reqs = vm.season.requirements
				vm.season = response.data;
				vm.season.requirements = reqs;
			}
		});
	};
}
