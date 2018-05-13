angular.module('FrcPortal')
.service('schoolsService', function ($http) {
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
		updateSetting: function (formData) {
			var setting_id = formData.setting_id != undefined && formData.setting_id != null ? formData.setting_id:'';
			return $http.put('api/settings/'+setting_id,formData)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
