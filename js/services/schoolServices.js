angular.module('FrcPortal')
.service('schoolsService', function ($http) {
	return {
		getAllSchoolsFilter: function (params) {
			return $http.get('api/schools?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		addSchool: function (formData) {
			return $http.post('api/schools', formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateSchool: function (formData) {
			var school_id = formData.school_id != undefined && formData.school_id != null ? formData.school_id:'';
			return $http.put('api/schools/'+school_id,formData)
			.then(function(response) {
				return response.data;
			});
		},
		deleteSchool: function (school_id) {
			return $http.delete('api/schools/'+school_id)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
