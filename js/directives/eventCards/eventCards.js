angular.module('FrcPortal')
.directive('eventCards', function() {
	return {
		restrict: 'E',
		transclude: true,
		scope: { events:'=', user:'=', max:'=' },
		templateUrl: 'js/directives/eventCards/eventCards.html',
    controller: function($scope) {
      $scope.limit = 0;

			$scope.increase = function() {
				$scope.limit = $scope.limit+1;
				console.log('limit: '+$scope.limit);
				console.log('begin: '+($scope.limit-$scope.max));
			}
			$scope.decrease = function() {
				$scope.limit = $scope.limit-1;
				console.log('limit: '+$scope.limit);
				console.log('begin: '+($scope.limit-$scope.max));
			}
    },
    link: function ($scope, element, attrs) { } //DOM manipulation
	};
});
