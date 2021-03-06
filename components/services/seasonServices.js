angular.module('FrcPortal')
.service('seasonsService', function ($http) {
	return {
		getAllSeasonsFilter: function (params) {
			return $http.get('api/seasons?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getSeason: function (season_id) {
			var season_id = season_id != undefined && season_id != null ? season_id: '';
			return $http.get('api/seasons/'+season_id)
			.then(function(response) {
				return response.data;
			});
		},
		getSeasonAnnualRequirements: function (season_id) {
			var season_id = season_id != undefined && season_id != null ? season_id:'';
			return $http.get('api/seasons/'+season_id+'/annualRequirements')
			.then(function(response) {
				return response.data;
			});
		},
		addSeason: function (formData) {
			return $http.post('api/seasons',formData)
			.then(function(response) {
				return response.data;
			});
		},
		toggleAnnualReqs: function (formData) {
			var season_id = formData.season_id != undefined && formData.season_id != null ? formData.season_id:'';
			return $http.put('api/seasons/'+season_id+'/toggleAnnualReqs',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateSeasonMembershipForm: function (season_id) {
			return $http.put('api/seasons/'+season_id+'/updateMembershipForm')
			.then(function(response) {
				return response.data;
			});
		},
		pollMembershipForm: function (season_id) {
			return $http.put('api/seasons/'+season_id+'/pollMembershipForm')
			.then(function(response) {
				return response.data;
			});
		},
		updateSeason: function (formData) {
			var season_id = formData.season_id != undefined && formData.season_id != null ? formData.season_id:'';
			return $http.put('api/seasons/'+season_id,formData)
			.then(function(response) {
				return response.data;
			});
		},
		deleteSeason: function (season_id) {
			var season_id = season_id != undefined && season_id != null ? season_id:'';
			return $http.delete('api/seasons/'+season_id)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
