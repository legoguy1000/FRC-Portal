angular.module('FrcPortal')
.controller('main.signinController', ['$rootScope', '$timeout', '$q', '$auth', '$scope', 'signinService', '$mdDialog', '$interval','$sce',
	mainSigninController
]);
function mainSigninController($rootScope, $timeout, $q, $auth, $scope, signinService, $mdDialog, $interval,$sce) {
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
	vm.eventSource;

	var signInBool = true;

	var tick = function() {
		vm.clock = Date.now();
	}
	tick();
	$interval(tick, 1000);

	vm.signInAuthed = signinService.isAuthed();
	vm.getUsers = function() {
		vm.promise = signinService.signInUserList().then(function(response) {
			vm.users = response;
		});
	}
	vm.getUsers();


	vm.genQrCodeUrl = function() {
		var tok = signinService.getToken();
		if(tok != '') {
			return $sce.trustAsResourceUrl('https://chart.googleapis.com/chart?cht=qr&chl='+tok+'&chs=360x360&choe=UTF-8&chld=L|1');
		}
		return '';
	}

	vm.qrCodeUrl = vm.genQrCodeUrl();
	//vm.signInAuthed = signinService.isAuthed();
	vm.authorizeSignIn = function() {
		var data = {
			auth_token: null,
			auth_code: null
		};
		if($auth.isAuthenticated()) {
			data.auth_token = $auth.getToken();
			sendAuth(data);
		} else {
			var confirm = $mdDialog.prompt()
	      .title('Please enter your code to authorize sign in.')
	      .textContent('Currently this is your signin PIN')
	      .placeholder('Authorization Code')
	      .ariaLabel('')
	      .initialValue('')
	      .required(true)
	      .ok('Submit')
	      .cancel('Cancel');
				$mdDialog.show(confirm).then(function(result) {
		      data.auth_code = result;
					sendAuth(data);
		    }, function() {});
		}
	}
	function sendAuth(data) {
		signinService.authorizeSignIn(data).then(function(response) {
			var dialog = $mdDialog.alert()
									.clickOutsideToClose(true)
									.textContent(response.msg)
									.ariaLabel('Time In/Out')
									.ok('OK');
			$mdDialog.show(dialog);
			if(response.status && response.signin_token != undefined) {
				signinService.saveToken(response.signin_token);
				vm.qrCodeUrl = vm.genQrCodeUrl();
				startEventSource();
			}
			vm.signInAuthed = signinService.isAuthed();
		});
	}
	vm.deauthorizeSignIn = function() {
		var jti = signinService.getTokenJti();
		var data = {'jti':jti};
		signinService.deauthorizeSignIn(data).then(function(response) {
			if(response.status == true) {
				signinService.logout();
				vm.genQrCodeUrl();
				console.log('Connection closed');
				vm.eventSource.close();
			}
			vm.signInAuthed = signinService.isAuthed();
		});
	}

	function startEventSource() {
		console.log('start ES');
		vm.eventSource = new EventSource("api/sse.php");
		vm.eventSource.addEventListener("open", function (event) {
			if(typeof event.data !== 'undefined'){
				console.log(event.data);
			}
		});
		vm.eventSource.addEventListener("message", function (event) {
			if(typeof event.data !== 'undefined'){
				console.log(event.data);
				//vm.users = event.data;
			}
		});
		vm.eventSource.addEventListener("error", function (event) {
			if(typeof event.data !== 'undefined'){
				console.log(event.data);
			}
		});
	}


/*
	vm.signinOut = function($event, numbers) {
		if(signInBool) {
			signInBool = false;
			vm.user_id = '';
			if(vm.selected_user[0]) {
				vm.user_id = vm.selected_user[0].user_id;
			}
			var data = {
				'user_id': vm.user_id,
				'pin':vm.pin,
				'token': signinService.getToken()
			};
			signinService.signInOut(data).then(function(response) {
				vm.pin = '';
				vm.selected_user = [];
				var dialog = $mdDialog.alert()
										.clickOutsideToClose(true)
										.textContent(response.msg)
										.ariaLabel('Time In/Out')
										.ok('Got it!');
				$mdDialog.show(dialog);
				$timeout( function(){
			      $mdDialog.cancel();
			    }, 2000 );
				if(response.status) {
					vm.users = response.signInList;
				}
				signInBool = true;
			});
		}
	} */

	//signInModal
	vm.showSignInModal = function(userInfo) {
		signInBool = true;
		if(!vm.signInAuthed) {
			var dialog = $mdDialog.alert()
									.clickOutsideToClose(true)
									.textContent('Sign in/out is not authorized from this device at this time.  Please see a mentor.')
									.ariaLabel('Time In/Out')
									.ok('Got it!');
			$mdDialog.show(dialog);
			$timeout( function(){
					$mdDialog.cancel();
				}, 2000 );
			return;
		}
		var confirm = $mdDialog.prompt()
			.title('Sign In/Out for '+userInfo.full_name)
			.textContent('Please enter your PIN to sign in/out')
			.placeholder('PIN (eg. 123456)')
			.ariaLabel('')
			.initialValue('')
			.required(true)
			.ok('Submit')
			.cancel('Cancel');
			$mdDialog.show(confirm).then(function(result) {
				var data = {
					'user_id': userInfo.user_id,
					'pin': result,
					'token': signinService.getToken()
				};
				signinService.signInOut(data).then(function(response) {
					vm.pin = '';
					vm.selected_user = [];
					var dialog = $mdDialog.alert()
											.clickOutsideToClose(true)
											.textContent(response.msg)
											.ariaLabel('Time In/Out')
											.ok('Got it!');
					$mdDialog.show(dialog);
					$timeout( function(){
				      $mdDialog.cancel();
				    }, 2000 );
					if(response.status) {
						vm.users = response.signInList;
					}
					signInBool = true;
				});
			}, function() {	});
/*		$mdDialog.show({
			controller: signInModalController,
			controllerAs: 'vm',
			templateUrl: 'views/partials/signInModal.tmpl.html',
			parent: angular.element(document.body),
			targetEvent: ev,
			clickOutsideToClose:true,
			fullscreen: true, // Only for -xs, -sm breakpoints.
			locals: {
				userInfo: userInfo,
			}
		})
		.then(function(currentMap) {
			vm.season.membership_form_map = currentMap;
			vm.updateSeason();
		}, function() { });*/
	};
}
