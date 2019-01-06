angular.module('FrcPortal')
.controller('main.oauthController', ['$rootScope', '$state', '$auth', '$mdToast', '$state', '$stateParams', 'configItems', '$sce', 'loginService',
	mainOauthController
]);
function mainOauthController($rootScope, $state, $auth, $mdToast, $state, $stateParams, configItems, $sce, loginService) {
    var vm = this;

		//$stateParams.provider;
		//$stateParams.code;
		var data = $stateParams;
		loginService.oauth(data).then(function(response) {
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.data.msg)
					.position('top right')
					.hideDelay(3000)
			);
			var authed = $auth.isAuthenticated();
			if(authed) {
				$window.localStorage['userInfo'] = angular.toJson(response.data.userInfo);
				$rootScope.$emit('afterLoginAction');
				$state.go('main.home');
			}
		})
}
