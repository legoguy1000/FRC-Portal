angular.module('FrcPortal')
.service('schoolsService', function ($http) {
	return {
		getAllSchools: function () {
			return $http.get('site/getAllSchools.php')
			.then(function(response) {
				return response.data;
			});
		},
		getAllSchoolsFilter: function (params) {
			return $http.get('site/getAllSchoolsFilter.php?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		searchAllSchools: function (search) {
			return $http.get('site/searchAllSchools.php?search='+search)
			.then(function(response) {
				return response.data;
			});
		},
		getSchoolById: function (year) {
			return $http.get('api/v1/general/seasonInfo/'+year)
			.then(function(response) {
				return response.data;
			});
		},
	};
});