angular.module('FrcPortal')
.controller('signInModalController', ['$log','$element','$mdDialog', '$scope', 'usersService','$mdToast','userInfo','signinService','$interval',
	signInModalController
]);
function signInModalController($log,$element,$mdDialog,$scope,usersService,$mdToast,userInfo,signinService,$interval) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.userInfo = userInfo;
	vm.pin = '';
	vm.users = null;
	var tick = function() {
		vm.clock = Date.now();
	}
	tick();
	$interval(tick, 1000);
	var signInBool = true;
	vm.signinOut = function($event) {
		if(signInBool) {
			signInBool = false;
			var data = {
				'user_id': vm.userInfo.user_id,
				'pin':vm.pin,
				'token': signinService.getToken()
			};
			signinService.signInOut(data).then(function(response) {
				vm.pin = '';
				/*
				var dialog = $mdDialog.alert()
										.clickOutsideToClose(true)
										.textContent(response.msg)
										.ariaLabel('Time In/Out')
										.ok('Got it!');
				$mdDialog.show(dialog);
				$timeout( function(){
						$mdDialog.cancel();
					}, 2000 ); */
				if(response.status) {
					vm.users = response.signInList;
					$mdDialog.hide(vm.users);
				}
				signInBool = true;
			});
		}
	}

	vm.keyDown = function(e) {
		if(e.keyCode == 46 || e.keyCode == 8) {
				//console.log('backspace');
		}
		//console.log(e.keyCode);
	}
	$(document).keyup(function (e) {
			//console.log(e);
			if(vm.pin.length >= 4 && vm.pin.length <= 8) {
				if(e.originalEvent.code == 'Enter') {
					//e.preventDefault();
				//	e.stopPropagation()
					//vm.signinOut();
				}
			}
	});

}
