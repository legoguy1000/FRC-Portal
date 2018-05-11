angular.module('FrcPortal')
.service('usersService', function ($http) {
	return {
		getAllUsersFilter: function (params) {
			return $http.get('/api/users?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		updateUserPersonalInfo: function (formData) {
			var user_id = formData.user_id != undefined && formData.user_id != null ? formData.user_id:'';
			return $http.put('api/users/'+user_id,formData)
			.then(function(response) {
				return response.data;
			});
		},
		changePin: function (formData) {
			var user_id = formData.user_id != undefined && formData.user_id != null ? formData.user_id:'';
			return $http.put('api/users/'+user_id+'/pin',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateNotificationPreferences: function (formData) {
			var user_id = formData.user_id != undefined && formData.user_id != null ? formData.user_id:'';
			return $http.put('api/users/'+user_id+'/notificationPreferences',formData)
			.then(function(response) {
				return response.data;
			});
		},
		getProfileInfo: function (user_id) {
			var user_id = user_id != undefined && user_id != null ? user_id:'';
			return $http.get('api/users/'+user_id)
			.then(function(response) {
				return response.data;
			});
		},
		getUserAnnualRequirements: function (user_id, season_id = null) {
			var user_id = user_id != undefined && user_id != null ? user_id:'';
			var season = season_id != undefined && season_id != null ? '/'+season_id:'';
			return $http.get('api/users/'+user_id+'/annualRequirements'+season)
			.then(function(response) {
				return response.data;
			});
		},
		getUserEventRequirements: function (user_id, event_id = null) {
			var user_id = user_id != undefined && user_id != null ? user_id:'';
			var event = event_id != undefined && event_id != null ? '/'+event_id:'';
			return $http.get('api/users/'+user_id+'/eventRequirements'+event)
			.then(function(response) {
				return response.data;
			});
		},
		getUserLinkedAccounts: function (user_id) {
			var user_id = user_id != undefined && user_id != null ? user_id:'';
			return $http.get('api/users/'+user_id+'/linkedAccounts')
			.then(function(response) {
				return response.data;
			});
		},
		getUserNotificationPreferences: function (user_id) {
			var user_id = user_id != undefined && user_id != null ? user_id:'';
			return $http.get('api/users/'+user_id+'/notificationPreferences')
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
			var user_id = formData.user_id != undefined && formData.user_id != null ? formData.user_id:'';
			return $http.post('api/users/'+user_id+'/requestMissingHours',formData)
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
		searchQueryBuilder: function (search) {
			return $http.get('site/searchQueryBuilder.php?search='+search)
			.then(function(response) {
				return response.data;
			});
		},
		deleteUser: function (user_id) {
			var user_id = user_id != undefined && user_id != null ? user_id:'';
			return $http.delete('api/users/'+user_id)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
