angular.module('FrcPortal')
.controller('main.adminController', ['$log','$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog',
	mainAdminController
]);
function mainAdminController($log,$timeout, $q, $scope, $state, eventsService, $mdDialog) {
    var admin = this;

	//$log.log($state.current)
	admin.state = $state.current.name;
	$scope.main.title = $state.current.data.title;
	admin.tabs = [
      {
        name: 'Users',
        icon: 'dashboard',
        sref: 'main.admin.users',
				alt: 'main.admin.user'
      }, {
        name: 'Seasons',
        icon: 'dashboard',
        sref: 'main.admin.seasons',
				alt: 'main.admin.season'
      }, {
        name: 'Events',
        icon: 'dashboard',
        sref: 'main.admin.events',
				alt: 'main.admin.event'
      }, {
        name: 'Schools',
        icon: 'dashboard',
        sref: 'main.admin.schools'
      }, {
        name: 'Metrics',
        icon: 'dashboard',
        sref: 'main.admin.metrics'
      }, {
        name: 'Time Management',
        icon: 'dashboard',
        sref: 'main.admin.time'
      }, {
        name: 'Site Settings',
        icon: 'dashboard',
        sref: 'main.admin.settings'
      }, /* {
        name: 'Exempt Hours',
        icon: 'dashboard',
        sref: 'main.admin.exemptHours'
      }, */
    ];
	admin.slide = 'slide-left';

	admin.clickTab = function(tab) {
		var clicked = admin.tabs.indexOf(tab);
		var cur = admin.tabs.map(function(e) { return e.sref; }).indexOf($state.current.name);
		//$log.log(cur +' -> '+ clicked);

		if(clicked > cur) {
			admin.slideLeft();
		} else {
			admin.slideRight();
		}
	//
	}

	admin.slideLeft = function() {
		admin.slide = 'slide-left';
	}
	admin.slideRight = function() {
		admin.slide = 'slide-right';
	}
	admin.clickBack = function() {
		admin.slideRight();
	}
}
