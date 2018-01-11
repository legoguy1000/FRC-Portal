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
		order: 'lname',
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
	$(document).keyup(function (e) {
	    console.log(e);
			if(vm.pin.length >= 4 && vm.pin.length <= 8) {
				if(e.originalEvent.code == 'Enter') {
					e.preventDefault();
					e.stopPropagation()
					vm.signinOut();
				}
			}
			/*if(vm.pin.length < 8) {
				if(e.originalEvent.code == 'Digit1' || e.originalEvent.code == 'Numpad1') {
					vm.pin = vm.pin+'1';
				} else if(e.originalEvent.code == 'Digit2' || e.originalEvent.code == 'Numpad2') {
					vm.pin = vm.pin+'2';
				} else if(e.originalEvent.code == 'Digit3' || e.originalEvent.code == 'Numpad3') {
					vm.pin = vm.pin+'3';
				} else if(e.originalEvent.code == 'Digit4' || e.originalEvent.code == 'Numpad4') {
					vm.pin = vm.pin+'4';
				} else if(e.originalEvent.code == 'Digit5' || e.originalEvent.code == 'Numpad5') {
					vm.pin = vm.pin+'5';
				} else if(e.originalEvent.code == 'Digit6' || e.originalEvent.code == 'Numpad6') {
					vm.pin = vm.pin+'6';
				} else if(e.originalEvent.code == 'Digit7' || e.originalEvent.code == 'Numpad7') {
					vm.pin = vm.pin+'7';
				} else if(e.originalEvent.code == 'Digit8' || e.originalEvent.code == 'Numpad8') {
					vm.pin = vm.pin+'8';
				} else if(e.originalEvent.code == 'Digit9' || e.originalEvent.code == 'Numpad9') {
					vm.pin = vm.pin+'9';
				} else if(e.originalEvent.code == 'Digit0' || e.originalEvent.code == 'Numpad0') {
					vm.pin = vm.pin+'0';
				}
			}
			if(vm.pin.length > 0) {
					if(e.originalEvent.code == 'Backspace' || e.originalEvent.code == 'Delete') {
						vm.pin = vm.pin.slice(0, -1);
					}
			}*/
	});

}
