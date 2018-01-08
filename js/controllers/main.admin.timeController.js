angular.module('FrcPortal')
.controller('main.admin.timeController', ['$timeout', '$q', '$scope', '$state', '$timeout', 'signinService', 'timeService',
	mainAdminTimeController
]);
function mainAdminTimeController($timeout, $q, $scope, $state, $timeout, signinService, timeService) {
    var vm = this;


		vm.filter = {
			show: false,
		};
		vm.query = {
			filter: '',
			limit: 10,
			order: 'full_name',
			page: 1
		};
		vm.users = [];
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
				vm.getSignIns();
			},500);

		});

		vm.getSignIns = function () {
			vm.promise = timeService.getAllSignInsFilter($.param(vm.query)).then(function(response){
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
