angular.module('FrcPortal')
.controller('main.eventController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService','configItems',
	mainEventController
]);
function mainEventController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService,configItems) {
    var vm = this;

		vm.registrationFormVisible = false;
		vm.state = $state.current.name;


		vm.slack_url = configItems.slack_url;
		vm.slack_team_id = configItems.slack_team_id;

		vm.event_id = $stateParams.event_id;
		vm.event = {};
		vm.getEvent = function () {
			var user_id = $scope.main.isAuthed ? $scope.main.userInfo.user_id : null;
			var reqs = $scope.main.isAuthed ? true : false;
			vm.promise = eventsService.getEventPublic(vm.event_id, reqs, user_id).then(function(response){
				vm.event = response.data;
				$scope.main.title += ' - '+vm.event.name;
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

	    }, function() {

	    });
		}
}
