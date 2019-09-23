angular.module('FrcPortal')
.service('logsService', function ($http) {
	return {
		getAllLogsFilter: function (params) {
			return $http.get('api/logs?'+params)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
