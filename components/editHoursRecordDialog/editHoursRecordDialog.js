angular.module('FrcPortal')
.controller('editHoursRecordDialogController', ['$log','$element','$mdDialog', '$scope', 'hoursRecord','$mdToast','timeService',
	editHoursRecordDialogController
]);
function editHoursRecordDialogController($log,$element,$mdDialog,$scope,hoursRecord,$mdToast,timeService) {
	var vm = this;

	vm.hoursRecord = hoursRecord;
	vm.loading = false;
	vm.cancel = function() {
		$mdDialog.cancel();
	}

	vm.timeIn = moment(vm.hoursRecord.time_in);
	vm.timeOut = moment(vm.hoursRecord.time_out);

	vm.editSubmit = function() {
		vm.loading = true;
		var data = {
			'hours_id': vm.hoursRecord.hours_id,
			'time_in': vm.timeIn.format(),
			'time_out': vm.timeOut.format(),
		};
		timeService.editMeetingHours(data).then(function(response) {
			if(response.status) {
				$mdDialog.hide(response.data);
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

	vm.deleteMeetingHours = function () {
		var hours = vm.hoursRecord.hours.toFixed(2);
		var confirm = $mdDialog.confirm()
					.title('Delete Hours Record for '+vm.hoursRecord.user.full_name)
					.textContent('Are you sure you want to remove '+hours+' hour'+(hours>1 ? 's ':' ')+'for '+vm.hoursRecord.user.full_name+'?   This action is unreversable.'	)
					.ariaLabel('Delete Hours Record')
					.ok('Delete')
					.cancel('Cancel');
		$mdDialog.show(confirm).then(function() {
			vm.sil.promise = timeService.deleteMeetingHours(vm.hoursRecord.hours_id).then(function(response){
				vm.getSignIns();
				$mdToast.show(
					$mdToast.simple()
						.textContent(response.msg)
						.position('top right')
						.hideDelay(3000)
				);
			});
		});
	};
}
