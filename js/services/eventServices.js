angular.module('FrcPortal')
.service('eventsService', function ($http) {
	return {
		getAllEventsFilter: function (params) {
			return $http.get('api/events?'+params)
			.then(function(response) {
				return response.data;
			});
		},
		getEvent: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id)
			.then(function(response) {
				return response.data;
			});
		},
		getEventRequirements: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id+'/eventRequirements')
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
			return $http.post('api/events',formData)
			.then(function(response) {
				return response.data;
			});
		},
		getEventRoomList: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id+'/rooms')
			.then(function(response) {
				return response.data;
			});
		},
		getEventCarList: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id+'/carList')
			.then(function(response) {
				return response.data;
			});
		},
		getGoogleCalendarEvents: function (q,timeMin,timeMax) {
			return $http.get('api/events/searchGoogleCalendar?q='+q+'&timeMax='+timeMax+'&timeMin='+timeMin)
			.then(function(response) {
				return response.data;
			});
		},
		toggleEventReqs: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.put('api/events/'+event_id+'/toggleEventReqs',formData)
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
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.post('api/events/'+event_id+'/register', formData)
			.then(function(response) {
				return response.data;
			});
		},
		deleteEvent: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.delete('api/events/'+event_id)
			.then(function(response) {
				return response.data;
			});
		},
		updateEventRoomList: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.put('api/events/'+event_id+'/rooms',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateEventCarList: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.put('api/events/'+event_id+'/carList',formData)
			.then(function(response) {
				return response.data;
			});
		},
		addEventRoom: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.post('api/events/'+event_id+'/rooms',formData)
			.then(function(response) {
				return response.data;
			});
		},
	};
});
