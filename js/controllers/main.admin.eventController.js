angular.module('FrcPortal')
.controller('main.admin.eventController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService','usersService','$mdToast',
	mainAdminEventController
]);
function mainAdminEventController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService,usersService,$mdToast) {
    var vm = this;

	vm.filter = {
		show: false,
	};
	vm.loading = false;
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
		vm.loading = true;
		vm.promise = eventsService.getEvent(vm.event_id).then(function(response){
			vm.event = response.data;
			reqs = vm.event.requirements;
			vm.loading = false;
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
		vm.loading = true;
		var data = {
			'event_id': vm.event_id
		};
		vm.promise = eventsService.syncGoogleCalEvent(data).then(function(response){
			vm.event = response.data;
			vm.event.requirements = reqs;
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
		});
		vm.loading = false;
	};

	vm.updateEvent = function () {
		vm.loading = true;
		var data = {
			'event_id': vm.event_id,
			'pocInfo': vm.event.pocInfo,
			'type': vm.event.type,
		};
		vm.promise = eventsService.updateEvent(data).then(function(response){
			vm.event = response.data;
			vm.event.requirements = reqs;
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
		});
		vm.loading = false;
	};

	vm.deleteEvent = function() {
		var data = {
			event_id: vm.event.event_id,
		};
		var confirm = $mdDialog.confirm()
					.title('Delete event '+vm.event.name)
					.textContent('Are you sure you want to delete event '+vm.event.name+'?  This action is unreversable and any registration data will be removed.'	)
					.ariaLabel('Delete Event')
					.ok('Delete')
					.cancel('Cancel');
		$mdDialog.show(confirm).then(function() {
			vm.loading = true;
			eventsService.deleteEvent(data).then(function(response) {
				if(response.status) {
					$mdDialog.show(
						$mdDialog.alert()
							.title('Event Deleted')
							.textContent('Event  '+vm.event.name+' has been deleted.  You will now be redirected to the event list.')
							.ariaLabel('Event Deleted')
							.ok('OK')
					).then(function() {
						$scope.admin.clickBack();
						$state.go('main.admin.events');
					}, function() {});
				}
				vm.loading = false;
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
    .then(function(response) {
			vm.event.requirements = response.userInfo;
    }, function() {

    });
  };

	vm.showCarListModal = function(ev) {
	    $mdDialog.show({
	      controller: carListModalController,
				controllerAs: 'vm',
	      templateUrl: 'views/partials/carListModal.tmpl.html',
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
			.then(function(response) {
				vm.event.requirements = response.userInfo;
	    }, function() {

	    });
	  };
	vm.toggleEventReqs = function (req) {
		vm.loading = true;
		var data = {
			'event_id': vm.event_id,
			'users': vm.selectedUsers,
			'requirement':req
		}
		vm.promise = eventsService.toggleEventReqs(data).then(function(response){
			if(response.status && response.data) {
				vm.event.requirements.data = response.data;
			}
			vm.loading = false;
		});
	};

	vm.searchUsers = function (search) {
		var data = {
			filter: search,
			limit: 0,
			order: 'full_name',
			page: 1,
			listOnly: true,
			return: [
				'fname',
				'lname',
				'full_name',
				'user_id',
			]
		};
		return usersService.getAllUsersFilter($.param(data));
	};

}
