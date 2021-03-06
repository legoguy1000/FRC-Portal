angular.module('FrcPortal')
.service('settingsService', function ($http) {
	return {
		getAllSettings: function (params) {
			return $http.get('api/settings?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getSettingById: function (setting_id) {
			return $http.get('api/settings/'+setting_id)
			.then(function(response) {
				return response.data;
			});
		},
		getSettingBySetting: function (setting) {
			return $http.get('api/settings/'+setting)
			.then(function(response) {
				return response.data;
			});
		},
		getSettingBySection: function (setting) {
			var setting = setting != undefined && setting != null ? setting:'';
			return $http.get('api/settings/section/'+setting)
			.then(function(response) {
				return response.data;
			});
		},
		getConfigSettings: function () {
			return $http.get('api/settings/config')
			.then(function(response) {
				return response.data;
			});
		},
		updateSetting: function (formData) {
			var setting_id = formData.setting_id != undefined && formData.setting_id != null ? formData.setting_id:'';
			return $http.put('api/settings/'+setting_id,formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateSettingBySection: function (formData) {
			var section = formData.section != undefined && formData.section != null ? formData.section:'';
			return $http.put('api/settings/section/'+section,formData.data)
			.then(function(response) {
				return response.data;
			});
		},
		getAllTimezones: function () {
			return $http.get('api/public/timezones')
			.then(function(response) {
				return response.data;
			});
		},
		getServiceAccountCredentials: function () {
			return $http.get('api/settings/serviceAccountCredentials')
			.then(function(response) {
				return response.data;
			});
		},
		removeServiceAccountCredentials: function () {
			return $http.delete('api/settings/serviceAccountCredentials')
			.then(function(response) {
				return response.data;
			});
		},
		testSlack: function () {
			return $http.post('api/settings/testSlack')
			.then(function(response) {
				return response.data;
			});
		},
		testEmail: function () {
			return $http.post('api/settings/testEmail')
			.then(function(response) {
				return response.data;
			});
		},
		getOAuthCredentialsByProvider: function (provider) {
			var provider = provider != undefined && provider != null ? provider:'';
			return $http.get('api/settings/oauth/'+provider)
			.then(function(response) {
				return response.data;
			});
		},
		updateOAuthCredentialsByProvider: function (formData) {
			var provider = formData.provider != undefined && formData.provider != null ? formData.provider:'';
			return $http.put('api/settings/oauth/'+provider,formData)
			.then(function(response) {
				return response.data;
			});
		},
		getFirstPortalCredentials: function () {
			return $http.get('api/settings/firstPortalCredentials')
			.then(function(response) {
				return response.data;
			});
		},
		updateFirstPortalCredentials: function (formData) {
			return $http.post('api/settings/firstPortalCredentials',formData)
			.then(function(response) {
				return response.data;
			});
		},
		removeFirstPortalCredentials: function () {
			return $http.delete('api/settings/firstPortalCredentials')
			.then(function(response) {
				return response.data;
			});
		},
		getUpdateBranches: function () {
			return $http.get('api/settings/update/branches')
			.then(function(response) {
				return response.data;
			});
		},
		checkUpdates: function () {
			return $http.get('api/settings/update/check')
			.then(function(response) {
				return response.data;
			});
		},
		updatePortal: function () {
			return $http.post('api/settings/update')
			.then(function(response) {
				return response.data;
			});
		},
	};
});
