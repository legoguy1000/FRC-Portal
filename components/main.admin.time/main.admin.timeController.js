angular.module('FrcPortal')
.controller('main.admin.timeController', ['$timeout', '$q', '$scope', '$state', '$timeout', 'signinService', 'timeService', '$mdToast', '$mdDialog',
	mainAdminTimeController
]);
function mainAdminTimeController($timeout, $q, $scope, $state, $timeout, signinService, timeService, $mdToast, $mdDialog) {
    var vm = this;

		vm.limitOptions = [10,25,50,100];
		vm.sil = {
			filter: {
				show: false,
			},
			query: {
				filter: '',
				limit: 10,
				order: '-time_in',
				page: 1
			},
			users: [],
		};
		vm.mhrl = {
			filter: {
				show: false,
			},
			query: {
				filter: '',
				limit: 10,
				order: '-time_in',
				page: 1
			},
			users: [],
		};
		vm.requestRow = '';


		vm.showFilter = function (list) {
			vm[list].filter.show = true;
			vm[list].query.filter = '';
		};
		vm.removeFilter = function (list) {
			vm[list].filter.show = false;
			vm[list].query.filter = '';

			if(vm[list].filter.form.$dirty) {
				vm[list].filter.form.$setPristine();
			}
		};

		vm.showRequestRow = function(req) {
			if(vm.requestRow == req) {
				vm.requestRow = '';
			} else {
				vm.requestRow = req;
			}
		}
		var timeoutPromise1;
		$scope.$watch('vm.sil.query.filter', function (newValue, oldValue) {
			$timeout.cancel(timeoutPromise1);  //does nothing, if timeout alrdy done
			if(!oldValue) {
				bookmark = vm.sil.query.page;
			}
			if(newValue !== oldValue) {
				vm.sil.query.page = 1;
			}
			if(!newValue) {
				vm.sil.query.page = bookmark;
			}
			timeoutPromise1 = $timeout(function(){   //Set timeout
				vm.getSignIns();
			},500);

		});

		vm.getSignIns = function () {
			vm.sil.promise = timeService.getAllSignInsFilter($.param(vm.sil.query)).then(function(response){
				vm.users = response.data;
				vm.sil.total = response.total;
				vm.sil.maxPage = response.maxPage;
			});
		};

		vm.mhrl = {
			filter: {
				show: false,
			},
			query: {
				filter: '',
				limit: 10,
				order: '-time_in',
				page: 1
			},
			users: [],
		};

		var timeoutPromise2;
		$scope.$watch('vm.mhrl.query.filter', function (newValue, oldValue) {
			$timeout.cancel(timeoutPromise2);  //does nothing, if timeout alrdy done
			if(!oldValue) {
				bookmark = vm.mhrl.query.page;
			}
			if(newValue !== oldValue) {
				vm.mhrl.query.page = 1;
			}
			if(!newValue) {
				vm.mhrl.query.page = bookmark;
			}
			timeoutPromise2 = $timeout(function(){   //Set timeout
				vm.getAllMissingHoursRequestsFilter();
			},500);

		});

		vm.getAllMissingHoursRequestsFilter = function () {
			vm.mhrl.promise = timeService.getAllMissingHoursRequestsFilter($.param(vm.mhrl.query)).then(function(response){
				vm.requests = response.data;
				vm.mhrl.total = response.total;
				vm.mhrl.maxPage = response.maxPage;
			});
		};

		vm.approveDenyHoursRequest = function (request) {
			vm.mhrl.promise = timeService.approveMissingHoursRequest(request).then(function(response){
				if(response.status) {
					vm.getAllMissingHoursRequestsFilter();
				}
				$mdToast.show(
		      $mdToast.simple()
		        .textContent(response.msg)
		        .position('top right')
		        .hideDelay(3000)
		    );
			});
		};
		vm.denyMissingHoursRequest = function (request) {
			vm.mhrl.promise = timeService.denyMissingHoursRequest(request).then(function(response){
				if(response.status) {
					vm.getAllMissingHoursRequestsFilter();
				}
				$mdToast.show(
		      $mdToast.simple()
		        .textContent(response.msg)
		        .position('top right')
		        .hideDelay(3000)
		    );
			});
		};


		vm.showTimeSheetModal = function() {
			$mdDialog.show({
				controller: timeSheetModalController,
				controllerAs: 'vm',
				templateUrl: 'views/partials/timeSheetModal.tmpl.html',
				parent: angular.element(document.body),
				clickOutsideToClose:true,
				fullscreen: true, // Only for -xs, -sm breakpoints.
				locals: { }
			})
			.then(function(response) {}, function() {});
		}




}
