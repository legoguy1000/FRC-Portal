angular.module('FrcPortal')
.controller('signInModalController', ['$log','$element','$mdDialog', '$scope', 'usersService','$mdToast','userInfo','signinService','$interval','$document','$timeout',
	signInModalController
]);
function signInModalController($log,$element,$mdDialog,$scope,usersService,$mdToast,userInfo,signinService,$interval,$document,$timeout) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.userInfo = userInfo;
	vm.pin = '';
	vm.scanContent = '';
	var tick = function() {
		vm.clock = Date.now();
	}
	vm.loading = false;
	vm.msg = '';
	vm.hideVideo = false;
	tick();
	$interval(tick, 1000);


	$timeout(function() {
		var config = {
			video: document.getElementById('scanner'),
			mirror: false,
		};

		vm.startCamera = function(cameras, scanner) {
			if (cameras.length > 1) {
				scanner.start(cameras[1]);
			} else if (cameras.length > 0) {
				scanner.start(cameras[0]);
			} else {
				console.error('No cameras found.');
			}
		}
		vm.scanner = new Instascan.Scanner(config);
		vm.scanner.addListener('scan', function (content) {
			vm.loading = true;
			vm.scanContent = content;
			vm.stop();
			var data = {
				'token': content
			};
			signinService.signInOutQR(data).then(function(response) {
				vm.loading = false;
				vm.msg = response.msg;
				if(response.status) {
					$timeout( function(){
						vm.close(response.signInList);
					}, 2000 );
				}
			}, function(response) {
				vm.loading = false;
				vm.startCamera(vm.cameras, scanner);
			});
		});
		Instascan.Camera.getCameras().then(function (cameras) {
			vm.cameras = cameras;
			vm.startCamera(vm.cameras, vm.scanner);
		}).catch(function (e) {
			console.error(e);
		});

		vm.stop = function() {
			vm.hideVideo = true;
			scanner.stop();
		}

		vm.cancel = function() {
			vm.stop();
			$mdDialog.cancel();
		}

		vm.close = function(data) {
			vm.stop();
			$mdDialog.hide(data);
		}
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
					}, 2000 ); /*
				if(response.status) {
					vm.users = response.signInList;
					$mdDialog.hide(vm.users);
				}
				signInBool = true;
			});
		}
	} */
}
