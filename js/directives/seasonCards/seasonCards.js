angular.module('FrcPortal')
.directive('seasonCards', function() {
	return {
		restrict: 'E',
		transclude: true,
		scope: { seasons:'=', user:'=', max:'=' },
		templateUrl: 'js/directives/seasonCards/seasonCards.html',
    controller: function($scope) {
      $scope.limit = $scope.max;
    },
    link: function ($scope, element, attrs) { } //DOM manipulation
	};
});
