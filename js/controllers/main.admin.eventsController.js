angular.module('FrcPortal')
.controller('main.admin.eventsController', ['$log','$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog',
	mainAdminEventsController
]);
function mainAdminEventsController($log,$timeout, $q, $scope, $state, eventsService, $mdDialog) {
     var vm = this;

	vm.selected = [];
	vm.newEventModal = newEventModal;

	vm.filter = {
		show: false,
	};
	vm.query = {
		filter: '',
		limit: 10,
		order: '-event_start',
		page: 1
	};
	vm.events = [];
	vm.limitOptions = [10,25,50,100];

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

	var timeoutPromise;
	$scope.$watch('vm.query.filter', function (newValue, oldValue) {
		$timeout.cancel(timeoutPromise);  //does nothing, if timeout alrdy done
		if(!oldValue) {
			bookmark = vm.query.page;
		}
		if(newValue !== oldValue) {
			vm.query.page = 1;
		}
		if(!newValue) {
			vm.query.page = bookmark;
		}
		timeoutPromise = $timeout(function(){   //Set timeout
			vm.getEvents();
		},500);

	});

	vm.getEvents = function () {
		vm.promise = eventsService.getAllEventsFilter($.param(vm.query)).then(function(response){
			vm.events = response.data;
			vm.total = response.total;
			vm.maxPage = response.maxPage;
		});
	};


	function newEventModal() {
		$mdDialog.show({
			controller: newEventModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/newEventModal.tmpl.html',
			parent: angular.element(document.body),
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			multiple: true,
			locals: {
			}
		})
		.then(function(response) {
			vm.events = response.data.results;
			vm.total = response.data.total;
			vm.maxPage = response.data.maxPage;
			$log.info('asdf');
		}, function() {
			$log.info('Dialog dismissed at: ' + new Date());
		});
	}

	vm.showEventTypesModal = function(ev) {
		$mdDialog.show({
			controller: eventTypesModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/eventTypesModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {	}
		})
		.then(function() {

		}, function() { });
	};
}
