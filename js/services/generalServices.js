angular.module('FrcPortal')
.service('generalService', function ($mdDialog) {
	return {
		showSeasonHoursGraph: function (ev, user_id, year) {
			$mdDialog.show({
				controller: SeasonHoursGraphModalController,
				controllerAs: 'vm',
				templateUrl: 'views/partials/SeasonHoursGraphModal.tmpl.html',
				parent: angular.element(document.body),
				targetEvent: ev,
				clickOutsideToClose:true,
				fullscreen: true, // Only for -xs, -sm breakpoints.
				locals: {
					data: {
						'user_id': user_id,
						'year': year
					},
				}
			})
			.then(function() {}, function() {});
		},
	};
});
