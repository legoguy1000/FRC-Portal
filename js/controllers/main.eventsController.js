angular.module('FrcPortal')
.controller('main.eventsController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','configItems','$sce','$stateParams',
	mainEventsController
]);
function mainEventsController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,configItems,$sce, $stateParams) {
    var vm = this;

		vm.registrationFormVisible = false;
		vm.state = $state.current.name;


		vm.slack_url = configItems.slack_url;
		vm.slack_team_id = configItems.slack_team_id;

		vm.events = [];
		vm.eventTypes = [];
		//vm.event_start_moment = moment();
		vm.query = {
			limit: 0,
			order: 'event_start',
			search: {
				name: $stateParams.name != undefined ? $stateParams.name:'',
				type: $stateParams.type != undefined ? $stateParams.type:'',
				event_start: $stateParams.event_start != undefined ? $stateParams.event_start:vm.event_start_moment.format('MMMM Do YYYY'),
				event_end: $stateParams.event_end != undefined ? $stateParams.event_end:'',
			}
		};

		vm.getEvents = function () {
			vm.promise = eventsService.getAllEventsFilter($.param(vm.query)).then(function(response){
				vm.events = response.data;
				vm.total = response.total;
				vm.maxPage = response.maxPage;
			});
		};
		vm.getEventTypeList = function () {
			vm.promise =	eventsService.getEventTypes().then(function(response){
				vm.eventTypes = response.data;
			});
		};

		vm.getEvents();
		vm.getEventTypeList();


		vm.showRegistrationForm = function(ev) {
			var eventInfo = vm.event;
			if(!$scope.main.isAuthed) {
				return;
			}
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
					'userInfo': $scope.main.userInfo
				}
			})
			.then(function(answer) {

			}, function() { });
		}
}
