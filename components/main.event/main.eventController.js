angular.module('FrcPortal')
.controller('main.eventController', ['$rootScope','$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService','configItems','$sce',
	mainEventController
]);
function mainEventController($rootScope, $timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService,configItems,$sce) {
    var vm = this;

		vm.registrationFormVisible = false;
		vm.slack_url = configItems.slack_url;
		vm.slack_team_id = configItems.slack_team_id;

		vm.event_id = $stateParams.event_id;
		vm.event = {};
		$scope.main.title += ' - '+vm.event.name;
		vm.getEvent = function () {
			var params = {
				users: true,
			};
			vm.promise = eventsService.getEvent(vm.event_id, params).then(function(response){
				vm.event = response.data;
				$scope.main.title_extra = ' - '+vm.event.name;
				//$scope.main.title += ' - '+vm.event.name;
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
			if(vm.event.location != undefined && vm.event.location != '') {
				return $sce.trustAsResourceUrl('https://maps.google.com/maps?q='+vm.event.location+'&t=m&z=12&output=embed&iwloc=near');
			}
			return undefined;
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

		$rootScope.$on('afterLoginAction', function(event) {
			vm.getEvent();
		});

		$rootScope.$on('400BadRequest', function(event,response) {
			vm.loading = false;
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
}
