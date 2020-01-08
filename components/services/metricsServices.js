angular.module('FrcPortal')
.service('metricsService', function ($http) {
	return {
		reportsAvgHrsPerPersonPerYear: function (start,end) {
			return $http.get('api/reports/hoursPerPersonPerYear?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
		reportsAvgHrsPerUserTypePerYear: function (start,end) {
			return $http.get('api/reports/hoursPerUserTypePerYear?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
		topHourUsers: function (year) {
			if(year == '' || year == undefined) {
				var d = new Date();
				year = d.getFullYear();
			}
			return $http.get('api/reports/topHourUsers/'+year)
			.then(function(response) {
				return response.data;
			});
		},
		reportsActiveUsersPerYear: function (start,end) {
			return $http.get('api/reports/activeUsersPerYear?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
		reportsHoursPerEventPerYear: function (year) {
			return $http.get('api/reports/hoursPerEventPerYear?year='+year)
			.then(function(response) {
				return response.data;
			});
		},
		reportsHoursPerEventTypePerYear: function (year) {
			return $http.get('api/reports/hoursPerEventTypePerYear?year='+year)
			.then(function(response) {
				return response.data;
			});
		},
		reportsAvgHrsPerGenderPerYear: function (start,end) {
			return $http.get('api/reports/hoursPerGenderPerYear?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
		reportsHoursPerGradePerYear: function (start,end) {
			return $http.get('api/reports/hoursPerGradePerYear?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
		reportsHoursPerWeek: function (year) {
			return $http.get('api/reports/hoursPerWeek?year='+year)
			.then(function(response) {
				return response.data;
			});
		},
		reportsHoursPerDayOfWeek: function (start,end) {
			return $http.get('api/reports/hoursPerDayOfWeek?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
