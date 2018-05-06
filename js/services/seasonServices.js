angular.module('FrcPortal')
.service('seasonsService', function ($http) {
	return {
		getAllSeasonsFilter: function (params) {
			return $http.get('api/seasons?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getAllSeasons: function () {
			return $http.get('site/getAllSeasons.php')
			.then(function(response) {
				return response.data;
			});
		},
		getSeason: function (season_id) {
			var sid = season_id != undefined ? season_id: '';
			return $http.get('site/getSeason.php?season_id='+sid)
			.then(function(response) {
				return response.data;
			});
		},
		addSeason: function (formData) {
			return $http.post('site/addSeason.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		toggleAnnualReqs: function (formData) {
			return $http.post('site/toggleAnnualReqs.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateSeasonMembershipForm: function (formData) {
			return $http.post('site/updateSeasonMembershipForm.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateSeason: function (formData) {
			return $http.post('site/updateSeason.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		deleteSeason: function (formData) {
			return $http.post('site/deleteSeason.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
