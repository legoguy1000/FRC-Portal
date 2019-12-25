angular.module('FrcPortal')
.controller('main.timeinController', ['$rootScope', '$timeout', '$q', '$scope', '$state', 'schoolsService', 'usersService', 'signinService', '$mdDialog', '$auth','$mdToast', '$stateParams',
	mainTimeinController
]);
function mainTimeinController($rootScope, $timeout, $q, $scope, $state, schoolsService, usersService, signinService, $mdDialog, $auth, $mdToast, $stateParams) {
  var vm = this;

	var confirm = $mdDialog.confirm()
				.title('Do you want to sign in/out?')
				.textContent('Please confirm that you want to sign in or out.')
				.ariaLabel('Sign In/Out')
				.ok('Yes')
				.cancel('Cancel');
	$mdDialog.show(confirm).then(function() {
		var data = {
			'token': $stateParams.token
		};
		signinService.signInOutQR(data).then(function(response) {
			if(response.status) {
				var dialog = $mdDialog.alert()
										.clickOutsideToClose(true)
										.textContent(response.msg)
										.ariaLabel('Time In/Out')
										.ok('Close');
				$mdDialog.show(dialog);
				$timeout( function(){
						$mdDialog.cancel();
						$state.go('main.home');
					}, 2000 );
			} else {
				$mdToast.show(
					$mdToast.simple()
						.textContent(response.msg)
						.position('top right')
						.hideDelay(3000)
				);
				$state.go('main.home');
			}
		});
	}, function() {
		$state.go('main.home');
	});

	$rootScope.$on('400BadRequest', function(event,response) {
		vm.loading = false;
		$mdToast.show(
			$mdToast.simple()
				.textContent(response.msg)
				.position('top right')
				.hideDelay(3000)
		);
		$state.go('main.home');
	});
}
