angular.module('FrcPortal')
.controller('main.admin.eventController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService','$mdDialog','usersService',
	mainAdminEventController
]);
function mainAdminEventController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService,$mdDialog,usersService) {
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

	vm.event_id = $stateParams.event_id;
	vm.event = {};
	var reqs = [];
	vm.getEvent = function () {
		vm.promise = eventsService.getEvent(vm.event_id).then(function(response){
			vm.event = response.data;
			reqs = vm.event.requirements;
		});
	};
	vm.selectedUsers = [];

	vm.getSeasons = function () {
		vm.promise = seasonsService.getAllSeasons().then(function(response){
			vm.seasons = response.data;
		});
	};
	vm.getSeasons();

	vm.getEvent();
	vm.limitOptions = [5,10,25,50,100];
	vm.query = {
		filter: '',
		limit: 10,
		order: 'full_name',
		page: 1
	};

	vm.syncGoogleCalEvent = function () {
		var data = {
			'event_id': vm.event_id
		};
		eventsService.syncGoogleCalEvent(data).then(function(response){
			vm.event = response.data;
			vm.event.requirements = reqs;
		});
	};

	vm.updateEvent = function () {
		var data = {
			'event_id': vm.event_id,
			'pocInfo': vm.event.pocInfo,
			'type': vm.event.type,
		};
		eventsService.updateEvent(data).then(function(response){
			vm.event = response.data;
			vm.event.requirements = reqs;
		});
	};

	vm.deleteEvent = function() {
		var data = {
			season_id: vm.season.season_id,
		};
		var confirm = $mdDialog.confirm()
					.title('Delete event '+vm.event.name)
					.textContent('Are you sure you want to delete event '+vm.event.name+'?  This action is unreversable and any registration data will be removed.'	)
					.ariaLabel('Delete Event')
					.ok('Delete')
					.cancel('Cancel');
		$mdDialog.show(confirm).then(function() {
			eventsService.deleteEvent(data).then(function(response) {
				if(response.status) {
					$mdDialog.show(
						$mdDialog.alert()
							.title('Event Deleted')
							.textContent('Event  '+vm.event.name+' has been deleted.  You will now be redirected to the season list.')
							.ariaLabel('Event Deleted')
							.ok('OK')
					).then(function() {
						$scope.admin.clickBack();
						$state.go('main.admin.events');
					}, function() {});
				}
			});
		}, function() {});
	}

	vm.showRoomListModal = function(ev) {
    $mdDialog.show({
      controller: roomListModalController,
			controllerAs: 'vm',
      templateUrl: 'views/partials/roomListModal.tmpl.html',
      parent: angular.element(document.body),
      targetEvent: ev,
      clickOutsideToClose:true,
      fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				eventInfo: {
					'event_id': vm.event_id,
					'name':vm.event.name,
					//'room_info': vm.event.room_list
				},
			}
    })
    .then(function(answer) {

    }, function() {

    });
  };

	vm.toggleEventReqs = function (req) {
		var data = {
			'event_id': vm.event_id,
			'users': vm.selectedUsers,
			'requirement':req
		}
		vm.promise = eventsService.toggleEventReqs(data).then(function(response){
			if(response.status && response.data) {
				vm.event.requirements.data = response.data;
			}

		});
	};

	vm.searchUsers = function (search) {
		var data = {
			filter: search,
			limit: 0,
			order: 'full_name',
			page: 1,
			listOnly: true,
		};
		return usersService.getAllUsersFilter($.param(data));
	};

}
