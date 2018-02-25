angular.module('FrcPortal')
.controller('newEventModalController', ['$log','$element','$mdDialog', '$scope', 'userInfo', 'usersService', 'schoolsService', 'seasonsService',
	newEventModalController
]);
function newEventModalController($log,$element,$mdDialog,$scope,userInfo,usersService,eventsService,seasonsService) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}

	vm.loading = {
		searchGoogle: false,
	}
	vm.searchGoogle = {};
	vm.searchGoogleFunc = function() {
		vm.loading.searchGoogle = true;
		var data = vm.searchGoogle;
		eventsService.getGoogleCalendarEvents(data.q, data.timeMin, data.timeMax).then(function(response) {
			if(response.status) {

			}
		});
	}



	vm.data = {};
	vm.searchSeason = '';
	vm.seasons;
	vm.clearSearchSeason = function() {
		vm.searchSeason = '';
	};

	vm.getSeasons = function () {
		vm.promise = seasonsService.getAllSeasons().then(function(response){
			vm.seasons = response.data;
		});
	};
	vm.getSeasons();

	vm.checkSingle = function () {
		$log.log('asdf');
		if(!vm.data.single_day) {
			$log.log('a');
			vm.data.event_end_full = angular.copy(vm.data.event_start_full);
		}
	}

	vm.addEvent = function() {
		eventsService.addEvent(vm.data).then(function(response) {
			if(response.status) {
				$mdDialog.hide(response);
			}
		});
	}

	vm.onYearChange = function (newValue, oldValue) {
		$log.log('Meeting changed from ' + oldValue + ' to ' + newValue);
	};

	$element.find('input').on('keydown', function(ev) {
		ev.stopPropagation();
	});

}
