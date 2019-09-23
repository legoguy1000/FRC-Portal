angular.module('FrcPortal')
.service('otherService', function ($http, $mdToast, $auth) {
	return {
		showErrorToast: function (responseData) {
			var toast = $mdToast.simple()
				.textContent(responseData.msg)
				.position('top right')
				.hideDelay(3000);
			if($auth.getPayload().data.admin) {
				toast.action('Show Error');
				$mdToast.show(toast).then(function(resp) {
					if (resp === 'ok') {
						alert(responseData.error);
					}
				});
			} else {
				$mdToast.show(toast);
			}
		},
	};
});
