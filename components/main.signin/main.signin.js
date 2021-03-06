angular.module('FrcPortal')
.controller('main.signinController', ['$rootScope', '$timeout', '$q', '$auth', '$scope', 'signinService', '$mdDialog', '$interval','$sce','configItems',
	mainSigninController
]);
function mainSigninController($rootScope, $timeout, $q, $auth, $scope, signinService, $mdDialog, $interval,$sce,configItems) {
    var vm = this;

	vm.pin = '';
	vm.qrCode = null;
	vm.selected_user = [];
	vm.users = [];
	vm.limitOptions = [5,10,25,50,100];
	vm.query = {
		filter: '',
		limit: 10,
		order: 'lname',
		page: 1
	};
	vm.loading = false;
	vm.signInAuthed = signinService.isAuthed();
	vm.configItems = configItems;
	//vm.tokenInterval

	var eventSource;
	var signInBool = true;
	//.tokenIntervalTime = 60000*50;

	var tick = function() {
		vm.clock = Date.now();
	}
	tick();
	$interval(tick, 1000);

	var getToken = function() {
		vm.loading = true;
		var data = {};
		var tok = signinService.getToken();
		if(tok != '') {
			data.token = tok
		}
		signinService.generateSignInToken(data).then(function(response) {
			vm.loading = false;
			if(response.status) {
				signinService.saveToken(response.signin_token);
				vm.qrCodeUrl = vm.genQrCodeUrl();
			} else {
				$interval.cancel(vm.tokenInterval);
			}
			vm.signInAuthed = signinService.isAuthed();
		});
		vm.getUsers();
	}

	vm.getUsers = function() {
		var tok = signinService.getToken();
		if(tok != '') {
			var token = tok;
		}
		vm.promise = signinService.signInUserList(token).then(function(response) {
			vm.users = response;
		});
	}

	vm.genQrCodeUrl = function() {
		var tok = signinService.getToken();
		if(tok != '') {
			var qr_value = vm.configItems.env_url+'/timein?token='+tok;
			return $sce.trustAsResourceUrl('https://chart.googleapis.com/chart?cht=qr&chl='+qr_value+'&chs=360x360&choe=UTF-8&chld=L|1');
		}
		return '';
	}

	vm.qrCodeUrl = vm.genQrCodeUrl();
	vm.authorizeSignIn = function() {
		var data = {
			auth_code: null
		};
		if($auth.isAuthenticated()) {
			sendAuth();
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
				//vm.tokenInterval = $interval(getToken, vm.tokenIntervalTime);
				vm.getUsers();
			}
			vm.signInAuthed = signinService.isAuthed();
		});
	}

	if(vm.signInAuthed || $auth.isAuthenticated()) {
		vm.getUsers();
	} else {
		vm.authorizeSignIn();
	}

	vm.deauthorizeSignIn = function() {
		var jti = signinService.getTokenJti();
		var data = {'jti':jti};
		signinService.deauthorizeSignIn(data).then(function(response) {
			if(response.status == true) {
				signinService.logout();
				//vm.genQrCodeUrl();
				vm.qr_code = null;
				$interval.cancel(vm.tokenInterval);
				//console.log('Connection closed');
				//eventSource.close();
			}
			vm.signInAuthed = signinService.isAuthed();
		});
	}

	function startEventSource() {
		if(eventSource != undefined) {
			eventSource.close();
		}
		/*console.log('start ES');
		eventSource = new EventSource("api/sse.php");
		eventSource.addEventListener("open", function (event) {
			if(typeof event.data !== 'undefined'){
				console.log(event.data);
			}
		});
		eventSource.addEventListener("message", function (event) {
			if(typeof event.data !== 'undefined'){
				console.log('Last ID: '+event.lastEventId);
				console.log('CUr ID: '+event.id);
				vm.users = JSON.parse(event.data);
			}
		});
		eventSource.addEventListener("error", function (event) {
			if(typeof event.data !== 'undefined'){
				console.log(event.data);
			}
		});*/
	}
/*
	if(vm.signInAuthed) {
		startEventSource();
	} else if(!vm.signInAuthed && eventSource != undefined) {
		eventSource.close();
	} */


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
			templateUrl: 'components/signInModal/signInModal.html',
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
