angular.module('FrcPortal')
.controller('main.admin.usersController', ['$timeout', '$q', '$scope', '$state', '$timeout', 'schoolsService', 'usersService', '$mdDialog',
	mainAdminUsersController
]);
function mainAdminUsersController($timeout, $q, $scope, $state, $timeout, schoolsService, usersService, $mdDialog) {
    var vm = this;

	vm.selected = [];
	vm.filter = {
		show: false,
	};
	vm.query = {
		filter: '',
		limit: 10,
		order: 'full_name',
		page: 1,
		search: {
			name: '',
			user_type: '',
			school: '',
			email: '',
			gender: '',
			status: true,
		}
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
	$scope.$watch('vm.query.search', function (newValue, oldValue) {
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
			vm.getUsers();
		},500);

	});

	vm.getUsers = function () {
		vm.promise = usersService.getAllUsersFilter($.param(vm.query)).then(function(response){
			vm.users = response.data;
			vm.total = response.total;
			vm.maxPage = response.maxPage;
		});
	};

	vm.showUserCategoriessModal = function(ev) {
		$mdDialog.show({
			controller: userCategoriesModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/userCategoriesModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {	}
		})
		.then(function(response) {}, function() { });
	};
}
