angular.module('FrcPortal')
.service('usersService', function ($http) {
	return {
		getAllUsers: function () {
			return $http.get('site/getAllUsers.php')
			.then(function(response) {
				return response.data;
			});
		},
		getAllUsersFilter: function (params) {
			return $http.get('site/getAllUsersFilter.php?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getNotificationEndpoints: function () {
			return $http.get('site/getNotificationEndpoints.php')
			.then(function(response) {
				return response.data;
			});
		},
		signInUserList: function () {
			return $http.get('site/signInUserList.php')
			.then(function(response) {
				return response.data;
			});
		},
		/* getUserById: function (id) {
			return $http.get('api/v1/users/'+id)
			.then(function(response) {
				return response.data;
			});
		}, */
		updateUserPersonalInfo: function (formData) {
			return $http.post('site/updateUserPersonalInfo.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		deviceNotificationSubscribe: function (formData) {
			return $http.post('site/deviceNotificationSubscribe.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		deviceNotificationUnsubscribe: function (formData) {
			return $http.post('site/deviceNotificationUnsubscribe.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		deviceNotificationUpdateEndpoint: function (formData) {
			return $http.post('site/deviceNotificationUpdateEndpoint.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		editDeviceLabel: function (formData) {
			return $http.post('site/editDeviceLabel.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		checkPin: function (formData) {
			return $http.post('site/checkPin.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		changePin: function (formData) {
			return $http.post('site/changePin.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateNotificationPreferences: function (formData) {
			return $http.post('site/updateNotificationPreferences.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		getProfileInfo: function (user_id) {
			var a = user_id != undefined && user_id != null ? user_id:'';
			return $http.get('site/getProfileInfo.php?user_id='+a)
			.then(function(response) {
				return response.data;
			});
		},
		userHoursbyDate: function (user,year) {
			return $http.get('site/userHoursbyDate.php?user='+user+'&year='+year)
			.then(function(response) {
				return response.data;
			});
		},
		requestMissingHours: function (formData) {
			return $http.post('site/requestMissingHours.php',formData)
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
	};
});
