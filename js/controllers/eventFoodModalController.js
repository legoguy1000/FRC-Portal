angular.module('FrcPortal')
.controller('eventFoodModalController', ['$log','$element','$mdDialog', '$scope', 'eventsService','$mdToast','eventsService','eventInfo',
	eventFoodModalController
]);
function eventFoodModalController($log,$element,$mdDialog,$scope,eventsService,$mdToast,eventsService,eventInfo) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.eventInfo = eventInfo;

	vm.event_food = [];
	vm.query = {
		filter: '',
		limit: 10,
		order: 'name',
		page: 1
	};
	vm.limitOptions = [10,25,50,100];
	vm.loading = false;
	vm.eventFoodEdit = null;
	vm.formData = {};
	//function get room list
	vm.getEventFoodList = function () {
		vm.promise =	eventsService.getEventFood().then(function(response){
			vm.event_food = response.data;
		});
	};
	vm.getEventFoodList();

	vm.editFood = function (event_type) {
		vm.eventFoodEdit = event_type.type_id;
	};
	vm.cancelEdit = function () {
		vm.eventFoodEdit = null;
	};

	vm.updateFood = function (event_type) {
		vm.promise =	eventsService.updateEventFood(event_type).then(function(response) {
			if(response.status) {
				vm.eventFoodEdit = null;
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	};

	vm.addNewFood = function () {
		vm.promise =	eventsService.addEventFood(vm.formData).then(function(response) {
			if(response.status) {
				vm.formData = null;
				vm.newFoodForm.$setPristine();
				vm.newFoodForm.$setUntouched();
				vm.event_food = response.data;
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	};

	vm.deleteFood = function (event_type) {
		vm.promise =	eventsService.deleteEventFood(event_type).then(function(response) {
			if(response.status) {
				vm.event_food = response.data;
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
