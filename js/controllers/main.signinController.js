angular.module('FrcPortal')
.controller('main.signinController', ['$rootScope', '$timeout', '$q', '$scope', 'signinService', '$mdDialog', '$interval', 'usersService', 'signinService',
	mainSigninController
]);
function mainSigninController($rootScope, $timeout, $q, $scope, signinService, $mdDialog, $interval, usersService, signinService) {
    var vm = this;

	vm.pin = '';
	vm.selected_user = [];
	vm.users = [];
	vm.limitOptions = [5,10,25,50,100];
	vm.query = {
		filter: '',
		limit: 10,
		order: 'full_name',
		page: 1
	};

	var tick = function() {
		vm.clock = Date.now();
	}
	tick();
	$interval(tick, 1000);

	vm.getUsers = function() {
		usersService.signInUserList().then(function(response) {
			vm.users = response;
		});
	}
	vm.getUsers();

	//vm.signInAuthed = signinService.isAuthed();
	vm.authorizeSignIn = function() {
		signinService.authorizeSignIn().then(function(response) {
			var data = {
				'response': response,
				'logout': false
			}
			$rootScope.$broadcast('updateSigninStatus',data);
			/* if(response.status == true) {
				$scope.main.logout();
				//signinService.saveToken(response.signin_token);
				//vm.signInAuthed = signinService.isAuthed();
			} */
		});
	}
	vm.deauthorizeSignIn = function() {
		var jti = signinService.getTokenJti();
		var data = {'jti':jti};
		signinService.deauthorizeSignIn(data).then(function(response) {
			if(response.status == true) {
				signinService.logout();
				$rootScope.$broadcast('updateSigninStatus');
			}
		});
	}

	vm.signinOut = function($event, numbers) {
		console.log(vm.pin);
		vm.user_id = ''
		if(vm.selected_user[0]) {
			vm.user_id = vm.selected_user[0].user_id;
		}
		var data = {
			'user_id': vm.user_id,
			'pin':vm.pin
		};
		signinService.signInOut(data).then(function(response) {
			$mdDialog.show(
				$mdDialog.alert()
				.clickOutsideToClose(true)
				.textContent(response.msg)
				.ariaLabel('Time In/Out')
				.ok('Got it!')
			);
			if(response.status) {
				vm.users = response.signInList;
			}
			vm.pin = '';
			vm.selected_user = [];
		});

	}

	vm.keyDown = function(e) {
		if(e.keyCode == 46 || e.keyCode == 8) {
        console.log('backspace');
    }
		console.log(e.keyCode);
	}

}
