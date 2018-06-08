angular.module('FrcPortal')
.controller('eventTypesModalController', ['$log','$element','$mdDialog', '$scope', 'eventsService','$mdToast',
	eventTypesModalController
]);
function eventTypesModalController($log,$element,$mdDialog,$scope,eventsService,$mdToast) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.event_types = [];
	vm.query = {
		filter: '',
		limit: 10,
		order: '-student_count',
		page: 1
	};
	vm.limitOptions = [10,25,50,100];
	vm.loading = false;
	vm.eventTypeEdit = null;
	vm.formData = {};
	//function get room list
	vm.getEventTypeList = function () {
		vm.loading = true;
		vm.promise =	eventsService.getEventTypes().then(function(response){
			vm.event_types = response.data;
			vm.loading = false;
		});
	};
	vm.getEventTypeList();

	vm.editType = function (event_type) {
		vm.eventTypeEdit = event_type.type_id;
	};
	vm.cancelEdit = function () {
		vm.eventTypeEdit = null;
	};

	vm.updateType = function (event_type) {
		vm.loading = true;
		eventsService.updateEventType(event_type).then(function(response) {
			vm.loading = false;
			if(response.status) {
				vm.eventTypeEdit = null;
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	};

	vm.addNewType = function () {
		vm.loading = true;
		eventsService.addNewEventType(vm.formData).then(function(response) {
			vm.loading = false;
			if(response.status) {
				vm.formData = null;
				vm.newTypeForm.$setPristine();
				vm.newTypeForm.$setUntouched();
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	};
}
