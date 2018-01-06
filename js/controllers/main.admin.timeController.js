angular.module('FrcPortal')
.controller('main.admin.timeController', ['$timeout', '$q', '$scope', '$state', '$timeout', 'signinService', 'usersService',
	mainAdminTimeController
]);
function mainAdminTimeController($timeout, $q, $scope, $state, $timeout, signinService, usersService) {
    var vm = this;

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
