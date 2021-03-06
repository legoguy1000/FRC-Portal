angular.module('FrcPortal')
.controller('main.admin.eventsController', ['$log','$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog','settingsService',
	mainAdminEventsController
]);
function mainAdminEventsController($log,$timeout, $q, $scope, $state, eventsService, $mdDialog,settingsService) {
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
	vm.firstPortal = false;

	vm.showFilter = function () {
		vm.filter.show = true;
		vm.query.filter = '';
	};
	vm.removeFilter = function () {
		vm.filter.show = false;
		vm.query.filter = '';

		if(vm.filter.form && vm.filter.form.$dirty) {
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
	vm.getFirstPortalCredentials = function () {
		settingsService.getFirstPortalCredentials().then(function(response){
			vm.firstPortal = response.data && response.data.email != '';
		});
	};
	vm.getFirstPortalCredentials();


	function newEventModal() {
		$mdDialog.show({
			controller: newEventModalController,
			controllerAs: 'vm',
			templateUrl: 'components/newEventModal/newEventModal.html',
			parent: angular.element(document.body),
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			multiple: true,
			locals: {
			}
		})
		.then(function(response) {
			vm.getEvents();
		}, function() {
			$log.info('Dialog dismissed at: ' + new Date());
		});
	}

	vm.showEventTypesModal = function(ev) {
		$mdDialog.show({
			controller: eventTypesModalController,
			controllerAs: 'vm',
			templateUrl: 'components/eventTypesModal/eventTypesModal.html',
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
