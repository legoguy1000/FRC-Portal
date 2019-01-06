angular.module('FrcPortal')
.controller('main.oauthController', ['$rootScope', '$state', '$auth', '$mdToast', '$state', '$stateParams', 'configItems', '$sce', 'loginService','$window','$mdDialog',
	mainOauthController
]);
function mainOauthController($rootScope, $state, $auth, $mdToast, $state, $stateParams, configItems, $sce, loginService,$window,$mdDialog) {
    var vm = this;

		//$stateParams.provider;
		//$stateParams.code;
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
					loading: true,
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
					alert(authed);
					if(authed) {
						$window.localStorage['userInfo'] = angular.toJson(response.userInfo);
						$rootScope.$emit('afterLoginAction');
						$state.go('main.home').then(function() {
							dialog.cancel();
						});
					}
				}
			});
		}

		loginModal();
		sendCode();


}
