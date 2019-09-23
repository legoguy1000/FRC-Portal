angular.module('FrcPortal')
.service('logsService', function ($http, $mdToast, $auth) {
	return {
		getAllLogsFilter: function (params) {
			return $http.get('api/logs?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		showErrorToast: function (responseData) {
			var toast = $mdToast.simple()
				.textContent(responseData.msg)
				.position('top right')
				.hideDelay(3000);
			if($auth.getPayload().data.admin) {
				toast.action('Show Error');
				$mdToast.show(toast).then(function(resp) {
					if (resp === 'ok') {
						console.log(responseData.error);
					}
				});
			} else {
				$mdToast.show(toast);
			}
		},
	};
});
