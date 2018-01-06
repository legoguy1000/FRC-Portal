angular.module('FrcPortal')
.controller('main.admin.seasonsController', ['$timeout', '$q', '$scope', '$state', 'seasonsService', '$mdDialog', '$log',
	mainAdminSeasonsController
]);
function mainAdminSeasonsController($timeout, $q, $scope, $state, seasonsService, $mdDialog, $log) {
     var vm = this;

	
	
	vm.selected = [];
	vm.filter = {
		show: false,
	};
	vm.newSeasonModal = newSeasonModal;
	vm.query = {
		filter: '',
		limit: 10,
		order: '-year',
		page: 1
	};
	vm.seasons = [];
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
			vm.getSeasons();
		},500);
		
	});
	
	vm.getSeasons = function () {
		vm.promise = seasonsService.getAllSeasonsFilter($.param(vm.query)).then(function(response){
			vm.seasons = response.data;
			vm.total = response.total;
			vm.maxPage = response.maxPage;
		});
	};

	function newSeasonModal(ev) {
		$mdDialog.show({
			controller: newSeasonModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/newSeasonModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				userInfo: {},
			}
		})
		.then(function(response) {
			vm.seasons = response.data.data;
			vm.total = response.data.total;
			vm.maxPage = response.data.maxPage;
			$log.info('asdf');
		}, function() {
			$log.info('Dialog dismissed at: ' + new Date());
		});
	}
	
}
