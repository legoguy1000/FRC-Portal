angular.module('FrcPortal')
.directive('seasonCards', function() {
	return {
		restrict: 'E',
		transclude: true,
		scope: { seasons:'=', user:'=', max:'=' },
		templateUrl: 'js/directives/seasonCards/seasonCards.html',
    controller: function($scope) {
      $scope.limit = $scope.max;

			$scope.increase = function() {
				$scope.limit = $scope.limit+1;
				console.log('limit: '+$scope.limit);
				console.log('upper: '+$scope.limit-$scope.max);
			}
			$scope.decrease = function() {
				$scope.limit = $scope.limit-1;
				console.log('limit: '+$scope.limit);
				console.log('upper: '+$scope.limit-$scope.max);
			}
    },
    link: function ($scope, element, attrs) { } //DOM manipulation
	};
});
