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
	vm.currentTime = new Date().getTime();

	vm.menuOptions = [
		{
			label: 'Toggle Event Registration',
			onClick: function($event){
				var user = $event.dataContext;
				var req = 'registration';
				var action = true;
				vm.rcToggleEventReqs(user, req, action);
			}
		}, {
			label: 'Edit Registration',
			onClick: function($event){
				var user = $event.dataContext;
				vm.showRegistrationForm(null,user);
			}
		}, {
			divider: true,
		}, {
			label: 'Toggle Payment',
			onClick: function($event){
				var user = $event.dataContext;
				var req = 'payment';
				var action = true;
				vm.rcToggleEventReqs(user, req, action);
			}
		}, {
			label: 'Toggle Permission Slip',
			onClick: function($event){
				var user = $event.dataContext;
				var req = 'permission_slip';
				var action = true;
				vm.rcToggleEventReqs(user, req, action);
			}
		}, {
			label: 'Toggle Food',
			onClick: function($event){
				var user = $event.dataContext;
				var req = 'food';
				var action = true;
				vm.rcToggleEventReqs(user, req, action);
			}
		}, {
			label: 'Confirm Attendance',
			onClick: function($event){
				var user = $event.dataContext;
				var req = 'attendance_confirmed';
				var action = true;
				vm.rcToggleEventReqs(user, req, action);
			}
		},
	];



	vm.getEventTypeList = function () {
		vm.promise =	eventsService.getEventTypes().then(function(response){
			vm.eventTypes = response.data;
		});
	};
	vm.getEventTypeList();

	vm.event_id = $stateParams.event_id;
	vm.event = {};
	vm.users = null;
	vm.getEvent = function () {
		vm.loading = true;
		eventsService.getEvent(vm.event_id).then(function(response){
			vm.event = response.data;
	/*		if(vm.event.registration_deadline != null) {
				vm.event.registration_deadline_moment = moment(vm.event.registration_deadline);
			} */
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

	vm.clearDeadline = function () {
		vm.event.registration_deadline_moment = null;
		vm.event.registration_deadline_gcalid = null;
		vm.event.registration_deadline_formatted = null;
	};


	vm.syncGoogleCalEvent = function () {
		vm.loading = true;
		eventsService.syncGoogleCalEvent(vm.event_id).then(function(response){
			vm.event = response.data;
			if(vm.event.registration_deadline != null) {
				vm.event.registration_deadline_moment = moment(vm.event.registration_deadline);
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

	vm.updateEvent = function () {
		vm.loading = true;
		var data = {
			'event_id': vm.event_id,
			'poc': vm.event.poc,
			'type': vm.event.type,
			'registration_deadline': vm.event.registration_deadline_date.long_date,
			'registration_deadline_gcalid': vm.event.registration_deadline_gcalid,
		};
		eventsService.updateEvent(data).then(function(response){
			if(response.status) {
				vm.event = response.data;
				if(vm.event.registration_deadline != null) {
					vm.event.registration_deadline_moment = moment(vm.event.registration_deadline);
				}
			}
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
				admin: true,
			}
		})
		.then(function(response) {
			vm.users = response.data;
		}, function() { });
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
		}, function() { });
  };

	vm.showTimeSlotListModal = function(ev) {
		$mdDialog.show({
			controller: timeSlotModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/timeSlotModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				eventInfo: {
					'event_id': vm.event_id,
					'name':vm.event.name,
				},
				admin: true,
			}
		})
		.then(function(response) {
			//vm.users = response.data;
		}, function() { });
	};

	vm.rcToggleEventReqs = function(user, req, action) {
		if(vm.selectedUsers.length > 1) {

		} else if ((vm.selectedUsers.length == 1 && vm.selectedUsers[0].user_id == user.user_id) || vm.selectedUsers.length == 0) {
			var users = [];
			users.push(user);
			vm.toggleEventReqs2(users, req, action);
		}
	}

	vm.toggleEventReqs2 = function (users, req, action) {
		var data = {
			'event_id': vm.event_id,
			'users': users,
			'requirement':req,
			'action': action
		}
		vm.promise = eventsService.toggleEventReqs(data).then(function(response){
			if(response.status && response.data) {
				vm.users = response.data;
			}
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
				vm.users = response.data;
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
			var user_id = answer.data.user_id;
			var index = null;
			var len = vm.users.length;
			for (var i = 0; i < len; i++) {
				if(vm.users[i].user_id == user_id) {
					index = i;
					break;
				}
			}
			if(index != null) {
				vm.users[index] = answer.data;
			}
		}, function() { });
	}

	vm.showComments = function(ev,userInfo) {
		$mdDialog.show(
      $mdDialog.alert()
        .clickOutsideToClose(true)
        .title('Registration comments for '+userInfo.full_name)
        .textContent(userInfo.event_requirements.comments)
        .ariaLabel('Registration comments for '+userInfo.full_name)
        .ok('close')
        .targetEvent(ev)
    );
	}

	vm.searchEventModal = function (ev) {
		$mdDialog.show({
			controller: eventSearchModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/eventSearchModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
			}
		})
		.then(function(response) {
			console.log(response);
			console.log(vm.event.registration_deadline_date.long_date);
			vm.event.registration_deadline_date.long_date = response.end.long_date;
			console.log(vm.event.registration_deadline_date.long_date);
			vm.event.registration_deadline_gcalid = response.google_cal_id;
			$log.info('asdf');
		}, function() {
			$log.info('Dialog dismissed at: ' + new Date());
		});
	}
}
