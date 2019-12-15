angular.module('FrcPortal')
.service('webauthnService', function ($mdDialog,$http) {
	return {
		bufferEncode: function(value) {
		    return btoa(String.fromCharCode.apply(null, new Uint8Array(value)));
		},
		getRegisterOptions: function () {
			return $http.get('api/webauthn/register')
			.then(function(response) {
				return response.data;
			});
		},
		getAuthenticationOptions: function (user_id) {
			return $http.get('api/webauthn/authenticate/'+user_id)
			.then(function(response) {
				return response.data;
			});
		},
		registerCredential: function (formData) {
			return $http.post('api/webauthn/register',formData)
			.then(function(response) {
				return response.data;
			});
		},
		authenticate: function (formData) {
			return $http.post('api/webauthn/authenticate',formData)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
