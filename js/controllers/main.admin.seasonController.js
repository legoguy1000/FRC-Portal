angular.module('FrcPortal')
.controller('main.admin.seasonController', ['$timeout', '$q', '$scope', '$state', 'seasonsService', '$mdDialog', '$log','$stateParams','$mdToast',
	mainAdminSeasonController
]);
function mainAdminSeasonController($timeout, $q, $scope, $state, seasonsService, $mdDialog, $log,$stateParams,$mdToast) {
    var vm = this;

	vm.loading = false;
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
	vm.limitOptions = [5,10,25,50,100];
	vm.query = {
		filter: '',
		limit: 10,
		order: 'full_name',
		page: 1
	};
	vm.fabOpen = false;
	vm.selectedUsers = [];
	vm.season_id = $stateParams.season_id;
	vm.season = {};
	vm.users = null;
	vm.getSeason = function () {
		vm.loading = true;
		vm.promise = seasonsService.getSeason(vm.season_id).then(function(response){
			vm.season = response.data;
			//$scope.main.title += ' - '+vm.season.game_name;
			vm.loading = false;
		});
	};
	vm.getSeason();

	vm.getUserAnnualRequirements = function() {
		vm.loadingReqs = seasonsService.getSeasonAnnualRequirements(vm.season_id).then(function(response){
			vm.users = response.data;
		});
	}
	vm.getUserAnnualRequirements();


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
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
		});
	};

	vm.updateSeasonMembershipForm = function () {
		vm.loading = true;
		var data = {
			'year': vm.season.year
		};
		seasonsService.updateSeasonMembershipForm(data).then(function(response){
			if(response.status) {
				vm.season.join_spreadsheet = response.data.join_spreadsheet;
			}
			vm.loading = false;
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
		});
	};

	vm.updateSeason = function () {
		vm.loading = true;
		var data = {
			'year': vm.season.year,
			'season_id': vm.season.season_id,
			'start_date': vm.season.start_date_formatted,
			'bag_day': vm.season.bag_day_formatted,
			'end_date': vm.season.end_date_formatted,
			'game_logo': vm.season.game_logo,
			'game_name': vm.season.game_name,
			'hour_requirement': vm.season.hour_requirement,
			'join_spreadsheet': vm.season.join_spreadsheet,
		};
		vm.promise = seasonsService.updateSeason(data).then(function(response){
			if(response.status) {
				//var reqs = vm.season.requirements
				//vm.season = response.data;
				//vm.season.requirements = reqs;
			}
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
			vm.loading = false;
		});
	};

	vm.deleteSeason = function() {
		var data = {
			season_id: vm.season.season_id,
		};
		var confirm = $mdDialog.confirm()
					.title('Delete season '+vm.season.game_name+' '+'('+vm.season.year+')')
					.textContent('Are you sure you want to delete season '+vm.season.game_name+' '+'('+vm.season.year+')?  This action is unreversable and any events and registration data will be removed.'	)
					.ariaLabel('Delete Season')
					.ok('Delete')
					.cancel('Cancel');
		$mdDialog.show(confirm).then(function() {
			seasonsService.deleteSeason(data).then(function(response) {
				if(response.status) {
					$mdDialog.show(
						$mdDialog.alert()
							.title('Season Deleted')
							.textContent('Season  '+vm.season.game_name+' '+'('+vm.season.year+') has been deleted.  You will now be redirected to the season list.')
							.ariaLabel('Season Deleted')
							.ok('OK')
					).then(function() {
						$scope.admin.clickBack();
						$state.go('main.admin.seasons');
					}, function() {});
				}
			});
		}, function() {});
	}
}
