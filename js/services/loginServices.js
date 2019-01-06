angular.module('FrcPortal')
.service('loginService', function ($http) {
	return {
		oauth: function (formData) {
			var pro = formData.provider != undefined && formData.provider != null ? formData.provider:'';
			if(pro == '') {
				return {
					status: false,
					msg: 'Invalid login attempt'
				};
			}
			return $http.post('auth/'+pro,formData)
			.then(function(response) {
				return response.data;
			});
		},
		google: function (formData) {
			return $http.post('auth/google',formData)
			.then(function(response) {
				return response.data;
			});
		},
		facebook: function (formData) {
			return $http.post('auth/facebook',formData)
			.then(function(response) {
				return response.data;
			});
		},
		microsoft: function (formData) {
			return $http.post('auth/microsoft',formData)
			.then(function(response) {
				return response.data;
			});
		},
		amazon: function (formData) {
			return $http.post('auth/amazon',formData)
			.then(function(response) {
				return response.data;
			});
		},
		github: function (formData) {
			return $http.post('auth/github',formData)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
