angular.module('FrcPortal')
.service('eventsService', function ($http) {
	return {
		getAllEventsFilter: function (params) {
			return $http.get('site/getAllEventsFilter.php?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getEvent: function (event_id,reqs,user_id) {
			var eid = event_id != undefined ? event_id: '';
			var reqs_str = reqs == true ? '&reqs=true': '';
			var uid = user_id != undefined ? '&user_id='+user_id: '';
			return $http.get('site/getEvent.php?event_id='+eid+reqs_str+uid)
			.then(function(response) {
				return response.data;
			});
		},
		getEventRegistrationStatus: function (event_id,user_id) {
			var eid = event_id != undefined ? event_id: '';
			var uid = user_id != undefined ? '&user_id='+user_id : '';
			return $http.get('site/getEventRegistrationStatus.php?event_id='+eid+uid)
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
		syncGoogleCalEvent: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.put('api/events/'+event_id+'/syncGoogleCalEvent')
			.then(function(response) {
				return response.data;
			});
		},
		updateEvent: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.put('api/events/'+event_id, formData)
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
		addEventRoom: function (formData) {
			return $http.post('site/addEventRoom.php',formData)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
