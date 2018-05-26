angular.module('FrcPortal')
.controller('eventSearchModalController', ['$log','$element','$mdDialog', '$scope', 'usersService', 'schoolsService', 'seasonsService','$mdToast',
	eventSearchModalController
]);
function eventSearchModalController($log,$element,$mdDialog,$scope,usersService,eventsService,seasonsService,$mdToast) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.data = {};
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
		if(response.status) {
			$mdDialog.hide(vm.data);
		}
	}
}
