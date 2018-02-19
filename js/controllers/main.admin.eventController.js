angular.module('FrcPortal')
.controller('main.admin.eventController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService','$mdDialog',
	mainAdminEventController
]);
function mainAdminEventController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService,$mdDialog) {
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



	vm.showRoomListModal = function(ev) {
    $mdDialog.show({
      controller: roomListModalController,
			controllerAs: 'vm',
      templateUrl: 'views/partials/roomListModal.tmpl.html',
      parent: angular.element(document.body),
      targetEvent: ev,
      clickOutsideToClose:true,
      fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				eventInfo: {
					'event_id': vm.event_id,
					'name':vm.event.name
				},
			}
    })
    .then(function(answer) {

    }, function() {

    });
  };
	vm.list = [
      {
        "label": "Item A1"
      },
      {
        "label": "Item A2"
      },
      {
        "label": "Item A3"
      }
    ];
	vm.list1 = [
      {
        "label": "Item B2"
      },
      {
        "label": "Item B1"
      },
      {
        "label": "Item B3"
      }
    ];
}
