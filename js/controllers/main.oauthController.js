angular.module('FrcPortal')
.controller('main.oauthController', ['$rootScope', '$state', '$auth', '$mdToast', '$state', '$stateParams', 'configItems', '$sce', 'loginService','$window','$mdDialog',
	mainOauthController
]);
function mainOauthController($rootScope, $state, $auth, $mdToast, $state, $stateParams, configItems, $sce, loginService,$window,$mdDialog) {
    var vm = this;

		//$stateParams.provider;
		var state = angular.fromJson($stateParams.state.replace(/&#34;/g,'"'));
		var redirect = state.current_state;
		var params = state.state_params;
		var dialog;
		function loginModal() {
			dialog = $mdDialog.show({
				controller: loginModalController,
				controllerAs: 'vm',
				templateUrl: 'views/partials/loginModal.tmpl.html',
				parent: angular.element(document.body),
				clickOutsideToClose:false,
				fullscreen: true, // Only for -xs, -sm breakpoints.
				locals: {
					loginData: {
						loading: true,
					}
				}
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
				if(response.status) {
					var authed = $auth.isAuthenticated();
					if(authed) {
						$window.localStorage['userInfo'] = angular.toJson(response.userInfo);
						$rootScope.$emit('afterLoginAction');
						$state.go(redirect,params).then(function() {
							$mdDialog.cancel();
						});
					}
				}
			});
		}

		loginModal();
		sendCode();


}
