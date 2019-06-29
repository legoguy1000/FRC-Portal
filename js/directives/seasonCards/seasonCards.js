angular.module('FrcPortal')
.directive('seasonCards', function() {
	return {
		restrict: 'E',
		transclude: true,
		scope: { seasons:'@' },
		templateUrl: 'seasonCards.html',
    controller: function($scope) {
      $scope.limit = 3;
    },
    link: function ($scope, element, attrs) { } //DOM manipulation
	};
});
