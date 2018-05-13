angular.module('FrcPortal')
.controller('newEventModalController', ['$log','$element','$mdDialog', '$scope', 'userInfo', 'usersService', 'schoolsService', 'seasonsService','$mdToast',
	newEventModalController
]);
function newEventModalController($log,$element,$mdDialog,$scope,userInfo,usersService,eventsService,seasonsService,$mdToast) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.data = {};
	vm.showGoogle = true;
	vm.loading = {
		searchGoogle: false,
	}
	vm.searchGoogle = {
		q:null,
		timeMax:null,
		timeMin:null,
	};
	vm.googleEvents = {
		data: [],
		total: 0
	};
	vm.query = {
		filter: '',
		limit: 5,
		order: 'event_start',
		page: 1
	};

	vm.eventTypes = [
		'Demo',
		'Community Serivce',
		'Season Event',
		'Off Season Event',
		'Other'
	];

	vm.searchGoogleFunc = function() {
		var data = vm.searchGoogle;
		vm.loading.searchGoogle = eventsService.getGoogleCalendarEvents(data.q, vm.searchGoogle.timeMin, vm.searchGoogle.timeMax).then(function(response) {
			if(response.status) {
					vm.googleEvents.data = response.data.results;
					vm.googleEvents.total = response.data.count;
			}
		});
	}

	vm.selectGoogleEvent = function(data) {
		vm.data = data;
		vm.data.start_moment = moment(vm.data.event_start);
		vm.data.end_moment = moment(vm.data.event_end);
		vm.showGoogle = false;

	}

	vm.backToSearch = function() {
		vm.data = {};
		vm.showGoogle = true;
	}



	vm.addEvent = function() {
		eventsService.addEvent(vm.data).then(function(response) {
			if(response.status) {
				$mdDialog.hide(response);
			}
			$mdToast.show(
	      $mdToast.simple()
	        .textContent(response.msg)
	        .position('top right')
	        .hideDelay(3000)
	    );
		});
	}
}
