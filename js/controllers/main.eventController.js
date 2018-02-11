angular.module('FrcPortal')
.controller('main.eventController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService',
	mainAdminEventController
]);
function mainEventController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService) {
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
	vm.getEvent = function () {
		vm.promise = eventsService.getEvent(vm.event_id).then(function(response){
			vm.event = response.data;
		});
	};

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
		limit: 5,
		order: 'full_name',
		page: 1
	};

}
