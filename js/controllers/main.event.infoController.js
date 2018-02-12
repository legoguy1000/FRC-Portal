angular.module('FrcPortal')
.controller('main.event.infoController', ['$timeout', '$q', '$scope', '$state', 'eventsService', '$mdDialog', '$log','$stateParams','seasonsService', 'NgMap',
	mainAdminEventInfoController
]);
function mainAdminEventInfoController($timeout, $q, $scope, $state, eventsService, $mdDialog, $log,$stateParams,seasonsService,NgMap) {
	var vmi = this;
	NgMap.getMap().then(function(map) {
    console.log(map.getCenter());
    console.log('markers', map.markers);
    console.log('shapes', map.shapes);
  });


}
