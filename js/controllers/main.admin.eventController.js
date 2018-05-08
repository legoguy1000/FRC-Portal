angular.module('FrcPortal')
.controller('main.admin.eventController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService','usersService','$mdToast','$mdMenu',
	mainAdminEventController
]);
function mainAdminEventController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService,usersService,$mdToast,$mdMenu) {
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
	vm.selectedUsers = [];
	vm.eventTypes = [
		'Demo',
		'Community Serivce',
		'Season Event',
		'Off Season Event',
		'Other'
	];
	vm.limitOptions = [5,10,25,50,100];
	vm.query = {
		filter: '',
		limit: 10,
		order: 'full_name',
		page: 1
	};

	vm.event_id = $stateParams.event_id;
	vm.event = {};
	vm.users = null;
	vm.getEvent = function () {
		vm.loading = true;
		eventsService.getEvent(vm.event_id).then(function(response){
			vm.event = response.data;
			vm.loading = false;
		});
	};
	vm.getEvent();


	vm.getEventRequirements = function() {
		vm.promise = eventsService.getEventRequirements(vm.event_id).then(function(response){
			vm.users = response.data;
		});
	}
	vm.getEventRequirements();



	vm.syncGoogleCalEvent = function () {
		vm.loading = true;
		eventsService.syncGoogleCalEvent(vm.event_id).then(function(response){
			vm.event = response.data;
			vm.loading = false;
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
		});
	};

	vm.updateEvent = function () {
		vm.loading = true;
		var data = {
			'event_id': vm.event_id,
			'event_poc': vm.event.event_poc,
			'type': vm.event.type,
		};
		eventsService.updateEvent(data).then(function(response){
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
		var confirm = $mdDialog.confirm()
					.title('Delete event '+vm.event.name)
					.textContent('Are you sure you want to delete event '+vm.event.name+'?  This action is unreversable and any registration data will be removed.'	)
					.ariaLabel('Delete Event')
					.ok('Delete')
					.cancel('Cancel');
		$mdDialog.show(confirm).then(function() {
			vm.loading = true;
			eventsService.deleteEvent(vm.event.event_id).then(function(response) {
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
			vm.users = response.data;
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
				vm.users = response.data;
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

	vm.showRegistrationForm = function(ev,userInfo) {
		var eventInfo = angular.copy(vm.event);
		delete eventInfo.requirements;
		$mdDialog.show({
			controller: eventRegistrationController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/eventRegistrationModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				'eventInfo': eventInfo,
				'userInfo': userInfo
			}
		})
		.then(function(answer) {
			var user_id = answer.data[0].user_id;
			var index = null;
			var len = vm.event.requirements.data.length;
			for (var i = 0; i < len; i++) {
			  if(vm.event.requirements.data[i].user_id == user_id) {
					index = i;
			    break;
			  }
			}
			if(index != null) {
				vm.event.requirements.data[index] = answer.data[0];
			}
		}, function() {

		});
	}

}
