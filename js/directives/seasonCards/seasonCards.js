angular.module('FrcPortal')
.directive('seasonCards', function() {
	return {
		require: 'ngModel',
		restrict: 'E',
		transclude: true,
		scope: { seasons:'=' },
		templateUrl: 'js/directives/seasonCards/seasonCards.html',
    controller: function($scope) {
      var dir = this;
      dir.limit = 3;
    },
    controllerAs: 'dir',
    link: function ($scope, element, attrs) { } //DOM manipulation
	};
});
