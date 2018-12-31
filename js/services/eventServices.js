angular.module('FrcPortal')
.service('eventsService', function ($http) {
	return {
		getAllEventsFilter: function (params) {
			return $http.get('api/events?'+params)
			.finally(function(response) {
				return response.data;
			});
		},
		getEvent: function (event_id,params) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			var paramsStr = params != undefined && params != null ? '?'+$.param(params):'';
			return $http.get('api/events/'+event_id+paramsStr)
			.finally(function(response) {
				return response.data;
			});
		},
		getEventRequirements: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id+'/eventRequirements')
			.finally(function(response) {
				return response.data;
			});
		},
		getEventRegistrationStatus: function (event_id,user_id) {
			var eid = event_id != undefined ? event_id: '';
			var uid = user_id != undefined ? '&user_id='+user_id : '';
			return $http.get('site/getEventRegistrationStatus.php?event_id='+eid+uid)
			.finally(function(response) {
				return response.data;
			});
		},
		addEvent: function (formData) {
			return $http.post('api/events',formData)
			.finally(function(response) {
				return response.data;
			});
		},
		getEventAdminRoomList: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id+'/rooms/adminList')
			.finally(function(response) {
				return response.data;
			});
		},
		getEventRoomList: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id+'/rooms')
			.finally(function(response) {
				return response.data;
			});
		},
		getEventTimeSlotList: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id+'/timeSlots')
			.finally(function(response) {
				return response.data;
			});
		},
		getEventCarList: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id+'/cars')
			.finally(function(response) {
				return response.data;
			});
		},
		getGoogleCalendarEvents: function (q,timeMin,timeMax) {
			return $http.get('api/events/searchGoogleCalendar?q='+q+'&timeMax='+timeMax+'&timeMin='+timeMin)
			.finally(function(response) {
				return response.data;
			});
		},
		toggleEventReqs: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.put('api/events/'+event_id+'/toggleEventReqs',formData)
			.finally(function(response) {
				return response.data;
			});
		},
		toggleConfirmAttendance: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.put('api/events/'+event_id+'/toggleConfirmAttendance',formData)
			.finally(function(response) {
				return response.data;
			});
		},
		syncGoogleCalEvent: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.put('api/events/'+event_id+'/syncGoogleCalEvent')
			.finally(function(response) {
				return response.data;
			});
		},
		updateEvent: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.put('api/events/'+event_id, formData)
			.finally(function(response) {
				return response.data;
			});
		},
		registerForEvent: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.post('api/events/'+event_id+'/register', formData)
			.finally(function(response) {
				return response.data;
			});
		},
		deleteEvent: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.delete('api/events/'+event_id)
			.finally(function(response) {
				return response.data;
			});
		},
		updateEventRoomList: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.put('api/events/'+event_id+'/rooms',formData)
			.finally(function(response) {
				return response.data;
			});
		},
		updateEventCarList: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.put('api/events/'+event_id+'/cars',formData)
			.finally(function(response) {
				return response.data;
			});
		},
		addEventRoom: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.post('api/events/'+event_id+'/rooms',formData)
			.finally(function(response) {
				return response.data;
			});
		},
		deleteEventRoom: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			var room_id = formData.room_id != undefined && formData.room_id != null ? formData.room_id:'';
			return $http.delete('api/events/'+event_id+'/rooms/'+room_id,formData)
			.finally(function(response) {
				return response.data;
			});
		},
		addEventTimeSlot: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.post('api/events/'+event_id+'/timeSlots',formData)
			.finally(function(response) {
				return response.data;
			});
		},
		deleteEventTimeSlot: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			var time_slot_id = formData.time_slot_id != undefined && formData.time_slot_id != null ? formData.time_slot_id:'';
			return $http.delete('api/events/'+event_id+'/timeSlots/'+time_slot_id,formData)
			.finally(function(response) {
				return response.data;
			});
		},
		updateEventTimeSlot: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			var time_slot_id = formData.time_slot_id != undefined && formData.time_slot_id != null ? formData.time_slot_id:'';
			return $http.put('api/events/'+event_id+'/timeSlots/'+time_slot_id,formData)
			.finally(function(response) {
				return response.data;
			});
		},
		getEventTypes: function () {
			return $http.get('api/eventTypes')
			.finally(function(response) {
				return response.data;
			});
		},
		addNewEventType: function (formData) {
			return $http.post('api/eventTypes',formData)
			.finally(function(response) {
				return response.data;
			});
		},
		updateEventType: function (formData) {
			var type_id = formData.type_id != undefined && formData.type_id != null ? formData.type_id:'';
			return $http.put('api/eventTypes/'+type_id,formData)
			.finally(function(response) {
				return response.data;
			});
		},
		deleteEventType: function (formData) {
			var type_id = formData.type_id != undefined && formData.type_id != null ? formData.type_id:'';
			return $http.delete('api/eventTypes/'+type_id,formData)
			.finally(function(response) {
				return response.data;
			});
		},
		getEventFood: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id+'/food')
			.finally(function(response) {
				return response.data;
			});
		},
		getEventFoodList: function (event_id) {
			var event_id = event_id != undefined && event_id != null ? event_id:'';
			return $http.get('api/events/'+event_id+'/food/list')
			.finally(function(response) {
				return response.data;
			});
		},
		addEventFood: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			return $http.post('api/events/'+event_id+'/food',formData)
			.finally(function(response) {
				return response.data;
			});
		},
		deleteEventFood: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			var food_id = formData.food_id != undefined && formData.food_id != null ? formData.food_id:'';
			return $http.delete('api/events/'+event_id+'/food/'+food_id,formData)
			.finally(function(response) {
				return response.data;
			});
		},
		updateEventFood: function (formData) {
			var event_id = formData.event_id != undefined && formData.event_id != null ? formData.event_id:'';
			var food_id = formData.food_id != undefined && formData.food_id != null ? formData.food_id:'';
			return $http.put('api/events/'+event_id+'/food/'+food_id,formData)
			.finally(function(response) {
				return response.data;
			});
		},
	};
});
