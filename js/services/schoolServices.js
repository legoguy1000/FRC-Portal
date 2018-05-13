angular.module('FrcPortal')
.service('schoolsService', function ($http) {
	return {
		getAllSchoolsFilter: function (params) {
			return $http.get('api/schools?'+params)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
