angular.module('FrcPortal')
.service('timeService', function ($http) {
	return {
		getAllSignInsFilter: function (params) {
			return $http.get('api/hours/signIn/records?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getAllMissingHoursRequestsFilter: function (params) {
			return $http.get('api/hours/missingHoursRequests?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		approveMissingHoursRequest: function (request_id) {
			return $http.put('api/hours/missingHoursRequests/'+request_id+'/approve')
			.then(function(response) {
				return response.data;
			});
		},
		denyMissingHoursRequest: function (request_id) {
			return $http.put('api/hours/missingHoursRequests/'+request_id+'/deny')
			.then(function(response) {
				return response.data;
			});
		},
		getAllExemptHoursFilter: function (params) {
			return $http.get('site/getAllExemptHoursFilter.php?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getSignInTimeSheet: function (date) {
			return $http.get('api/hours/signIn/timeSheet/'+date)
			.then(function(response) {
				return response.data;
			});
		},
		deleteMeetingHours: function (hours_id) {
			return $http.delete('api/hours/'+hours_id)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
