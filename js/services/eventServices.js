angular.module('FrcPortal')
.service('eventsService', function ($http) {
	return {
		getAllEventsFilter: function (params) {
			return $http.get('site/getAllEventsFilter.php?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getEvent: function (event_id) {
			var eid = event_id != undefined ? event_id: '';
			return $http.get('site/getEvent.php?event_id='+eid)
			.then(function(response) {
				return response.data;
			});
		},
		addEvent: function (formData) {
			return $http.post('site/addEvent.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
	};
});