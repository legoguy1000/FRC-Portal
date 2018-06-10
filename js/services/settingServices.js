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
			return $http.get('api/settings/getServiceAccountCredentials')
			.then(function(response) {
				return response.data;
			});
		},
	};
});
