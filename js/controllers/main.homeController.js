angular.module('FrcPortal')
.controller('main.homeController', ['$timeout', '$q', '$scope', '$state', 'metricsService', 'configItems', '$sce',
	mainHomeController
]);
function mainHomeController($timeout, $q, $scope, $state, metricsService, configItems, $sce) {
    var vm = this;

	vm.loadingTopUsers = false;
	vm.topUsersYear = new Date().getFullYear();
	vm.topUsers = [];
	vm.topHourUsers = topHourUsers;
	function topHourUsers() {
		vm.loadingTopUsers = true;
		metricsService.topHourUsers(vm.topUsersYear).then(function(response) {
			vm.topUsers = response;
			vm.loadingTopUsers = false;
		});
	}

	vm.getCalendarSrc = function () {
  return $sce.trustAsResourceUrl('https://calendar.google.com/calendar/embed?src='+configItems.google_calendar_id+'&ctz=America/New_York');
};

	/* vm.test = function() {
		navigator.usb.requestDevice({ filters: [{ vendorId: 0xc00c         }] })
		.then(device => {
		  console.log(device.productName);      // "Arduino Micro"
		  console.log(device.manufacturerName); // "Arduino LLC"
		})
		.catch(error => { console.log(error); });
	} */
}
