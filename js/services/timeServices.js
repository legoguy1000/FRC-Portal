angular.module('FrcPortal')
.service('timeService', function ($http) {
	return {
		getAllSignInsFilter: function (params) {
			return $http.get('site/getAllSignInsFilter.php?'+params)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
