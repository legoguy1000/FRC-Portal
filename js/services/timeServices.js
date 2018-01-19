angular.module('FrcPortal')
.service('timeService', function ($http) {
	return {
		getAllSignInsFilter: function (params) {
			return $http.get('site/getAllSignInsFilter.php?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getAllMissingHoursRequestsFilter: function (params) {
			return $http.get('site/getAllMissingHoursRequestsFilter.php?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		approveDenyHoursRequest: function (formData) {
			return $http.post('site/approveDenyHoursRequest.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		getAlExemptHoursFilter: function (params) {
			return $http.get('site/getAlExemptHoursFilter.php?'+params)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
