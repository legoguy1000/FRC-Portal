angular.module('FrcPortal')
.controller('main.admin.seasonController', ['$timeout', '$q', '$scope', '$state', 'seasonsService', '$mdDialog', '$log','$stateParams','$mdToast','$mdMenu', 'otherService',
	mainAdminSeasonController
]);
function mainAdminSeasonController($timeout, $q, $scope, $state, seasonsService, $mdDialog, $log,$stateParams,$mdToast,$mdMenu, otherService) {
    var vm = this;

	vm.loading = false;
	vm.filter = {
		show: false,
	};
	vm.showFilter = function () {
		vm.filter.show = true;
		vm.query.filter = {};
	};
	vm.removeFilter = function () {
		vm.filter.show = false;
		vm.query.filter = {};

		if(vm.filter.form.$dirty) {
			vm.filter.form.$setPristine();
		}
		$mdSidenav('season_reqs_filter').toggle();
	};
	vm.limitOptions = [5,10,25,50,100];
	vm.query = {
		filter: {},
		limit: 10,
		order: 'full_name',
		page: 1
	};
	vm.fabOpen = false;

	vm.season_id = $stateParams.season_id;
	vm.season = {};
	vm.users = null;
	vm.getSeason = function () {
		vm.loading = true;
		seasonsService.getSeason(vm.season_id).then(function(response){
			vm.season = response.data;
			//$scope.main.title += ' - '+vm.season.game_name;
		}).finally(function() {
			vm.loading = false;
		});
	};
	vm.getSeason();

	vm.getUserAnnualRequirements = function() {
		vm.promise = seasonsService.getSeasonAnnualRequirements(vm.season_id).then(function(response){
			vm.users = response.data;
		});
	}
	vm.getUserAnnualRequirements();

	vm.menuOptions = [
			{
				label: 'Toggle Annual Team Registration',
				onClick: function($event){
					var user = $event.dataContext;
					var req = 'join_team';
					var action = true;
					vm.rcToggleAnnualReqs(user, req, action);
				}
			}, {
				divider: true,
			}, {
				label: 'Toggle STIMS/TIMS Completetion',
				onClick: function($event){
					var user = $event.dataContext;
					var req = 'stims';
					var action = true;
					vm.rcToggleAnnualReqs(user, req, action);
				}
			}, {
				label: 'Toggle Dues Payment',
				onClick: function($event){
					var user = $event.dataContext;
					var req = 'dues';
					var action = true;
					vm.rcToggleAnnualReqs(user, req, action);
				}
			}
		];

		vm.selectedUsers = [];
		vm.selectUsers = function(user_id) {
			var inc = vm.selectedUsers.includes(user_id);
			if(!inc) {
				vm.selectedUsers.push(user_id);
			} else {
				var i = vm.selectedUsers.indexOf(user_id);
				vm.selectedUsers.splice(i,1);
			}
		}

		vm.rcToggleAnnualReqs = function(user, req, action) {
			if(vm.selectedUsers.length >= 1) {
				vm.toggleAnnualReqs2(vm.selectedUsers, req, action);
			} else if (vm.selectedUsers.length == 0 && user != null) {
				var users = [];
				users.push(user.user_id);
				vm.toggleAnnualReqs2(users, req, action);
			}
		}

		vm.toggleAnnualReqs2 = function (users, req, action) {
			var data = {
				'season_id': vm.season_id,
				'users': users,
				'requirement':req,
			}
			vm.promise = seasonsService.toggleAnnualReqs(data).then(function(response){
				if(response.status && response.data) {
					vm.users = response.data;
					if(vm.selectUsers.length > 1) {
						vm.selectUsers = [];
					}
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
		seasonsService.updateSeasonMembershipForm(vm.season.season_id).then(function(response){
			if(response.status) {
				vm.season.join_spreadsheet = response.data.join_spreadsheet;
				$mdToast.show(
		      $mdToast.simple()
		        .textContent(response.msg)
		        .position('top right')
		        .hideDelay(3000)
		    );
			} else {
				otherService.showErrorToast(response);
			}
			vm.loading = false;
		});
	};

	vm.pollMembershipForm = function () {
		vm.loading = true;
		seasonsService.pollMembershipForm(vm.season.season_id).then(function(response){
			if(response.status) {
				vm.users = response.data;
				$mdToast.show(
					$mdToast.simple()
						.textContent(response.msg)
						.position('top right')
						.hideDelay(3000)
				);
			} else {
				otherService.showErrorToast(response);
			}
			vm.loading = false;

		});
	};

	vm.updateSeason = function () {
		vm.loading = true;
		var data = {
			'year': vm.season.year,
			'season_id': vm.season.season_id,
			'start_date': vm.season.date.start.long_date,
			'bag_day': vm.season.no_bagday ? null:vm.season.date.bag.long_date,
			'end_date': vm.season.date.end.long_date,
			'game_logo': vm.season.game_logo,
			'game_name': vm.season.game_name,
			'hour_requirement': vm.season.hour_requirement,
			'hour_requirement_week': vm.season.hour_requirement_week,
			'join_spreadsheet': vm.season.join_spreadsheet,
			'membership_form_map': vm.season.membership_form_map,
		};
		vm.promise = seasonsService.updateSeason(data).then(function(response){
			if(response.status) {	}
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
		var confirm = $mdDialog.confirm()
					.title('Delete season '+vm.season.game_name+' '+'('+vm.season.year+')')
					.textContent('Are you sure you want to delete season '+vm.season.game_name+' '+'('+vm.season.year+')?  This action is unreversable and any events and registration data will be removed.'	)
					.ariaLabel('Delete Season')
					.ok('Delete')
					.cancel('Cancel');
		$mdDialog.show(confirm).then(function() {
			seasonsService.deleteSeason(vm.season.season_id).then(function(response) {
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

	vm.showGoogleFormMapModal = function(ev) {
		$mdDialog.show({
			controller: googleFormMapModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/googleFormMapModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				seasonInfo: vm.season,
			}
		})
		.then(function(currentMap) {
			vm.season.membership_form_map = currentMap;
			vm.updateSeason();
		}, function() { });
	};

	vm.openMenu = function() {
		$mdMenu.open();
	}



}
