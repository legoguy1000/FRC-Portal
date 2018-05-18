angular.module('FrcPortal')
.controller('main.homeController', ['$timeout', '$q', '$scope', '$state', 'metricsService', 'google_calendar_id',
	mainHomeController
]);
function mainHomeController($timeout, $q, $scope, $state, metricsService, google_calendar_id) {
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
  return 'https://calendar.google.com/calendar/embed?src='+google_calendar_id+'&ctz=America/New_York';
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
