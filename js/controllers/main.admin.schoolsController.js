angular.module('FrcPortal')
.controller('main.admin.schoolsController', ['$timeout', '$q', '$scope', '$state', 'schoolsService', '$mdDialog', '$log',
	mainAdminSchoolsController
]);
function mainAdminSchoolsController($timeout, $q, $scope, $state, schoolsService, $mdDialog, $log) {
   var vm = this;


	vm.showSchoolModal = schoolModal;
	vm.selected = [];
	vm.filter = {
		show: false,
	};
	//vm.newSchoolModal = newSchoolModal;
	vm.query = {
		filter: '',
		limit: 10,
		order: '-student_count',
		page: 1
	};
	vm.schools = [];
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
			vm.getSchools();
		},500);

	});

	vm.getSchools = function () {
		vm.promise = schoolsService.getAllSchoolsFilter($.param(vm.query)).then(function(response){
			vm.schools = response.data;
			vm.total = response.total;
			vm.maxPage = response.maxPage;
		});
	};

	vm.deleteSchool = function (school_id) {
		vm.promise = schoolsService.deleteSchool(school_id).then(function(response){
			if(response.status) {
				vm.schools = response.data;
				vm.total = response.total;
				vm.maxPage = response.maxPage;
			}
		});
	};

	function schoolModal(ev, newSchool, schoolData) {
		$mdDialog.show({
			controller: newSchoolModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/newSchoolModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				schoolInfo: {
					new: newSchool,
					data: newSchool ? {} : schoolData,
				},
			}
		})
		.then(function(response) {
			vm.seasons = response.data.results;
			vm.total = response.data.total;
			vm.maxPage = response.data.maxPage;
			$log.info('asdf');
		}, function() {
			$log.info('Dialog dismissed at: ' + new Date());
		});
	}

}
