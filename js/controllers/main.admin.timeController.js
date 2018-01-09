angular.module('FrcPortal')
.controller('main.admin.timeController', ['$timeout', '$q', '$scope', '$state', '$timeout', 'signinService', 'timeService',
	mainAdminTimeController
]);
function mainAdminTimeController($timeout, $q, $scope, $state, $timeout, signinService, timeService) {
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
			vm.promise = timeService.getAllSignInsFilter($.param(vm.query)).then(function(response){
				vm.sil.users = response.data;
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
			vm.promise = timeService.getAllMissingHoursRequestsFilter($.param(vm.query)).then(function(response){
				vm.users = response.data;
				vm.total = response.total;
				vm.maxPage = response.maxPage;
			});
		};
















	vm.signInAuthed = signinService.isAuthed();
	vm.authorizeSignIn = function() {
		signinService.authorizeSignIn().then(function(response) {
			if(response.status == true) {
				signinService.saveToken(response.signin_token);
				vm.signInAuthed = signinService.isAuthed();
			}
		});
	}
	vm.deauthorizeSignIn = function() {
		var jti = signinService.getTokenJti();
		var data = {'jti':jti};
		signinService.deauthorizeSignIn(data).then(function(response) {
			if(response.status == true) {
				signinService.logout();
				vm.signInAuthed = signinService.isAuthed();
			}
		});
	}




}
