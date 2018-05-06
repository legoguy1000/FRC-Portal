angular.module('FrcPortal')
.service('signinService', function ($window, $http) {
	return {
		parseJwt: function(token) {
			if(token != '' && token != undefined) {
				var base64Url = token.split('.')[1];
				var base64 = base64Url.replace('-', '+').replace('_', '/');
				return JSON.parse($window.atob(base64));
			}
			else {
				return false;
			}
		},
		saveToken: function(token) {
			$window.localStorage['signin_token'] = token;
		},
		getToken: function() {
			return $window.localStorage['signin_token'];
		},
		getTokenJti: function() {
			var token = this.getToken();
			var data = this.parseJwt(token);
			return data.jti;
		},
		isAuthed: function() {
			var token = this.getToken();
			if(token) {
				var params = this.parseJwt(token);
				return Math.round(new Date().getTime() / 1000) <= params.exp;
			}
			else {
				return false;
			}
		},
		logout: function() {
			$window.localStorage.removeItem('signin_token');
		},
		signInUserList: function () {
			return $http.get('api/sign_in/list')
			.then(function(response) {
				return response.data;
			});
		},
		authorizeSignIn: function (formData) {
			this.logout();
			return $http.post('api/sign_in/authorize',formData)
			.then(function(response) {
				return response.data;
			});
		},
		deauthorizeSignIn: function (formData) {
			return $http.post('api/sign_in/deauthorize',formData)
			.then(function(response) {
				return response.data;
			});
		},
		signInOut: function (formData) {
			return $http.post('api/sign_in',formData,{skipAuthorization: true})
			.then(function(response) {
				return response.data;
			});
		},
	};
});
