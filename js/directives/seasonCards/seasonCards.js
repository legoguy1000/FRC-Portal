angular.module('FrcPortal')
.directive('seasonCards', function() {
	return {
		restrict: 'E',
		replace: true,
		transclude: true,
		scope: { seasons:'@' },
		templateUrl: 'seasonCards.html',
    controller: function($scope) {
      $scope.limit = 3;
    },
		compile: function(element, attrs, linker) {
			return function(scope, element) {
			  linker(scope, function(clone) {
				element.append(clone);
			  });
			};
		}
	};
});
