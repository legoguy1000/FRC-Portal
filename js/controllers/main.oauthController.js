angular.module('FrcPortal')
.controller('main.oauthController', ['$scope', '$state', '$auth', '$mdToast', '$state', '$stateParams', 'configItems', '$sce', 'loginService','$window',
	mainOauthController
]);
function mainOauthController($scope, $state, $auth, $mdToast, $state, $stateParams, configItems, $sce, loginService,$window) {
    var vm = this;

		//$stateParams.provider;
		//$stateParams.code;
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
					$state.go('main.home');
				}
			}
		})
}
