angular.module('FrcPortal')
.controller('main.eventController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService',
	mainEventController
]);
function mainEventController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService) {
    var vm = this;

		vm.state = $state.current.name;
		vm.tabs = [
				{
					name: 'Event Information',
					icon: 'dashboard',
					sref: 'main.event.info({event_id:$stateParams.event_id})'
				},
				{
					name: 'Registration',
					icon: 'dashboard',
					sref: 'main.event.register({event_id:$stateParams.event_id})'
				},
			];
		vm.slide = 'slide-left';

		vm.clickTab = function(tab) {
			var clicked = vm.tabs.indexOf(tab);
			var cur = vm.tabs.map(function(e) { return e.sref; }).indexOf($state.current.name);
			//$log.log(cur +' -> '+ clicked);
			if(clicked > cur) {
				vm.slideLeft();
			} else {
				vm.slideRight();
			}
		//
		}
		vm.slideLeft = function() {
			vm.slide = 'slide-left';
		}
		vm.slideRight = function() {
			vm.slide = 'slide-right';
		}
		vm.clickBack = function() {
			vm.slideRight();
		}



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
	vm.getEvent = function () {
		vm.promise = eventsService.getEvent(vm.event_id).then(function(response){
			vm.event = response.data;
		});
	};

	vm.registerForEvent = function () {
		var data = {
			'event_id': vm.event_id,
		};
		eventsService.registerForEvent(data).then(function(response){
			//vm.event = response.data;
		});
	};

	vm.getEvent();
	vm.limitOptions = [5,10,25,50,100];
	vm.query = {
		filter: '',
		limit: 5,
		order: 'full_name',
		page: 1
	};

}
