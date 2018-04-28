angular.module('FrcPortal')
.service('eventsService', function ($http) {
	return {
		getAllEventsFilter: function (params) {
			return $http.get('site/getAllEventsFilter.php?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getEvent: function (event_id,reqs) {
			var eid = event_id != undefined ? event_id: '';
			var reqs_str = reqs == true ? '&reqs=true': false;
			return $http.get('site/getEvent.php?event_id='+eid+reqs_str)
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
		getEventRoomList: function (event_id) {
			var eid = event_id != undefined ? event_id: '';
			return $http.get('site/getEventRoomList.php?event_id='+eid)
			.then(function(response) {
				return response.data;
			});
		},
		getEventCarList: function (event_id) {
			var eid = event_id != undefined ? event_id: '';
			return $http.get('site/getEventCarList.php?event_id='+eid)
			.then(function(response) {
				return response.data;
			});
		},
		getGoogleCalendarEvents: function (q,timeMin,timeMax) {
		/*	var q = q != undefined ? q: '';
			var timeMax = timeMax != undefined ? timeMax: '';
			var timeMin = timeMin != undefined ? timeMin: ''; */
			return $http.get('site/getGoogleCalendarEvents.php?q='+q+'&timeMax='+timeMax+'&timeMin='+timeMin)
			.then(function(response) {
				return response.data;
			});
		},
		toggleEventReqs: function (formData) {
			return $http.post('site/toggleEventReqs.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		syncGoogleCalEvent: function (formData) {
			return $http.post('site/syncGoogleCalEvent.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateEvent: function (formData) {
			return $http.post('site/updateEvent.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		registerForEvent: function (formData) {
			return $http.post('site/registerForEvent.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		deleteEvent: function (formData) {
			return $http.post('site/deleteEvent.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateEventRoomList: function (formData) {
			return $http.post('site/updateEventRoomList.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateEventCarList: function (formData) {
			return $http.post('site/updateEventCarList.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
