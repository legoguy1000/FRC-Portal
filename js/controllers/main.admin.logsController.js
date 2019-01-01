angular.module('FrcPortal')
.controller('main.admin.logsController', ['$timeout', '$q', '$scope', '$state', '$timeout', 'logsService', '$mdDialog',
	mainAdminLogsController
]);
function mainAdminLogsController($timeout, $q, $scope, $state, $timeout, logsService, $mdDialog) {
    var vm = this;

	vm.selected = [];
	vm.filter = {
		show: false,
	};
	vm.query = {
		filter: '',
		limit: 10,
		order: '-created_at',
		page: 1,
		search: {
			level: '',
			user_id: '',
		}
	};
	vm.logs = [];
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
	$scope.$watchGroup(['vm.query.filter', 'vm.query.search.level','vm.query.search.user_id'], function(newValues, oldValues, scope) {
		//console.log(newValues);
		//console.log(oldValues);
		$timeout.cancel(timeoutPromise);  //does nothing, if timeout alrdy done
		if(!oldValues) {
			bookmark = vm.query.page;
		}
		if(newValues !== oldValues) {
			vm.query.page = 1;
		}
		if(!newValues) {
			vm.query.page = bookmark;
		}
		timeoutPromise = $timeout(function(){   //Set timeout
			vm.getLogs();
		},500);
	});

	vm.getLogs = function () {
		vm.promise = logsService.getAllLogsFilter($.param(vm.query)).then(function(response){
			vm.logs = response.data;
			vm.total = response.total;
			vm.maxPage = response.maxPage;
		});
	};
}
