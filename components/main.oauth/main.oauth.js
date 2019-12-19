angular.module('FrcPortal')
.controller('main.oauthController', ['$rootScope', '$scope', '$state', '$auth', '$mdToast', '$stateParams', 'configItems', '$sce', 'loginService','$window','$mdDialog',
	mainOauthController
]);
function mainOauthController($rootScope, $scope, $state, $auth, $mdToast, $stateParams, configItems, $sce, loginService,$window,$mdDialog) {
    var vm = this;

		//$stateParams.provider;
		var stateEncoded = $stateParams.state;
		var state = angular.fromJson(atob(stateEncoded));
		vm.redirect = state.current_state;
		vm.params = state.state_params;
		vm.state_from = state.state_from;
		var dialog;
		vm.authed = false;
		function loginModal() {
			dialog = $mdDialog.show({
				controller: loginModalController,
				controllerAs: 'vm',
				templateUrl: 'components/loginModal/loginModal.html',
				parent: angular.element(document.body),
				clickOutsideToClose:false,
				fullscreen: true, // Only for -xs, -sm breakpoints.
				locals: {
					loginData: {
						loading: true,
						oauth: true,
					}
				}
			});
		}
		function redirectUser(response) {
			if(response.status) {
				vm.authed = $auth.isAuthenticated();
				if(vm.authed) {
					$window.localStorage['userInfo'] = angular.toJson(response.userInfo);
					$rootScope.$emit('afterLoginAction',{loginType: 'oauth', loginProvider: $stateParams.provider});
				}
			}
			var state = 'main.home';
			var params = {};
			if(vm.redirect != '' && (vm.authed || vm.state_from == null)) {
				state = vm.redirect;
				params = vm.params;
			} else if(vm.state_from != null) {
				state = vm.state_from.name;
				params = vm.state_from.params;
			}
			$state.go(state,params).then(function() {
				$rootScope.$emit('closeLoginModal');
			});
		}
		function sendCode() {
			var data = $stateParams;
			loginService.oauth(data).then(function(response) {
				$mdToast.show(
					$mdToast.simple()
						.textContent(response.msg)
						.position('top right')
						.hideDelay(3000)
				);
				redirectUser(response);
			});
		}

		loginModal();
		sendCode();

		$rootScope.$on('400BadRequest', function(event,response) {
			vm.loading = false;
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
			redirectUser(response);
		});


}
