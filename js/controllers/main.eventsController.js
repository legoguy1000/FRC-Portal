angular.module('FrcPortal')
.controller('main.eventsController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','configItems','$sce',
	mainEventsController
]);
function mainEventsController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,configItems,$sce) {
    var vm = this;

		vm.registrationFormVisible = false;
		vm.state = $state.current.name;


		vm.slack_url = configItems.slack_url;
		vm.slack_team_id = configItems.slack_team_id;

		vm.event_id = $stateParams.event_id;
		vm.events = [];
		vm.getEvents = function () {
			vm.promise = eventsService.getAllEventsFilter().then(function(response){
				vm.events = response.data;
				vm.total = response.total;
				vm.maxPage = response.maxPage;
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

		vm.getMapsSrc = function () {
			return $sce.trustAsResourceUrl('https://maps.google.com/maps?q='+vm.event.location+'&t=m&z=12&output=embed&iwloc=near');
		};

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
