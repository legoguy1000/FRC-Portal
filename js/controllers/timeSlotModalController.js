angular.module('FrcPortal')
.controller('timeSlotModalController', ['$log','$element','$mdDialog', '$scope', '$auth', 'eventInfo', 'admin', 'usersService', 'eventsService','$mdToast',
	timeSlotModalController
]);
function timeSlotModalController($log,$element,$mdDialog,$scope,$auth,eventInfo,admin,usersService,eventsService,$mdToast) {
	var vm = this;

	vm.eventInfo = eventInfo;
	vm.admin = admin && $auth.getPayload().data.admin;
	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.save = function() {
		$mdDialog.hide(vm.time_slots);
	}
	vm.time_slots = [];

	//function get room list
	vm.getEventTimeSlotList = function () {
		eventsService.getEventTimeSlotList(vm.eventInfo.event_id).then(function(response) {
			vm.time_slots = response.data;
		});
	};
	vm.getEventTimeSlotList();

	vm.editTimeSlot = function (ev, newTS = true, timeSlotInfo = {}) {
		if(newTS == false) {
			timeSlotInfo.time_end_moment = moment(timeSlotInfo.time_end);
			timeSlotInfo.time_start_moment = moment(timeSlotInfo.time_start);
		}
		$mdDialog.show({
			controller: editTimeSlotModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/editTimeSlotModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			multiple: true,
			locals: {
				eventInfo: vm.eventInfo,
				newTS: newTS,
				timeSlotInfo: timeSlotInfo
			}
		})
		.then(function(response) {
			vm.time_slots = response;
		}, function() {});
	}

	vm.toggleRegistrationEventTimeSlot = function(time_slot_id) {
		var data = {
			'user_id': vm.eventInfo.user_id,
			'time_slot_id': time_slot_id,
		};
		usersService.toggleRegistrationEventTimeSlot(data).then(function(response) {
			if(response.status) {
				vm.time_slots = response.data;
			}
			vm.loading = false;
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
		});
	}
	vm.checkReg = function(time_slot) {
		var index = false;
		if(!vm.admin) {
			var len = time_slot.registrations.length;
			for (var i = 0; i < len; i++) {
				if(time_slot.registrations[i].user_id == vm.eventInfo.user_id) {
					index = true;
					break;
				}
			}
		}
		return index;
	}
/*	vm.registerTimeSlot = function(time_slot_id) {
		var data = {
			'user_id': vm.eventInfo.user_id,
			'time_slot_id': time_slot_id,
		};
		usersService.registerEventTimeSlot(data).then(function(response) {
			if(response.status) {
				vm.time_slots = response.data;
			}
			vm.loading = false;
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
		});
	}

	vm.unregisterTimeSlot = function(time_slot_id) {
		var data = {
			'user_id': vm.eventInfo.user_id,
			'time_slot_id': time_slot_id,
		};
		usersService.unregisterEventTimeSlot(data).then(function(response) {
			if(response.status) {
				vm.time_slots = response.data;
			}
			vm.loading = false;
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	}
 */

}
