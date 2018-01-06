angular.module('FrcPortal')
.service('metricsService', function ($http) {
	return {
		reportsAvgHrsPerPersonPerYear: function (start,end) {
			return $http.get('site/reportsAvgHrsPerPersonPerYear.php?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
		reportsAvgHrsPerUserTypePerYear: function (start,end) {
			return $http.get('site/reportsAvgHrsPerUserTypePerYear.php?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
		topHourUsers: function (year) {
			return $http.get('site/topHourUsers.php?year='+year)
			.then(function(response) {
				return response.data;
			});
		},
		reportsActiveUsersPerYear: function (start,end) {
			return $http.get('site/reportsActiveUsersPerYear.php?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
		reportsHoursPerEventPerYear: function (year) {
			return $http.get('site/reportsHoursPerEventPerYear.php?year='+year)
			.then(function(response) {
				return response.data;
			});
		},
		reportsAvgHrsPerGenderPerYear: function (start,end) {
			return $http.get('site/reportsAvgHrsPerGenderPerYear.php?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
		reportsHoursPerGradePerYear: function (start,end) {
			return $http.get('site/reportsHoursPerGradePerYear.php?start_date='+start+'&end_date='+end)
			.then(function(response) {
				return response.data;
			});
		},
		reportsHoursPerWeek: function (year) {
			return $http.get('site/reportsHoursPerWeek.php?year='+year)
			.then(function(response) {
				return response.data;
			});
		},
	};
});