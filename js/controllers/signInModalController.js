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
	var config = {
		video: document.getElementById('scanner'),
		mirror: false,
	};

	var scanner = new Instascan.Scanner(config);
  scanner.addListener('scan', function (content) {
    console.log(content);
  });
  Instascan.Camera.getCameras().then(function (cameras) {
		if (cameras.length > 1) {
      scanner.start(cameras[1]);
    } else if (cameras.length > 0) {
      scanner.start(cameras[0]);
    } else {
      console.error('No cameras found.');
    }
  }).catch(function (e) {
    console.error(e);
  });

	/*
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
	} */
}
