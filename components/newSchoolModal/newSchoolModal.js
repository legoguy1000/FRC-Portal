angular.module('FrcPortal')
.controller('newSchoolModalController', ['$log','$mdDialog', '$scope', 'schoolInfo', 'schoolsService','$mdToast',
	newSchoolModalController
]);
function newSchoolModalController($log,$mdDialog,$scope,schoolInfo,schoolsService,$mdToast) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}

	vm.new = schoolInfo.new && schoolInfo.data.school_id == undefined;
	vm.data = vm.new ? {} : angular.copy(schoolInfo.data);

	vm.addSchool = function() {
		schoolsService.addSchool(vm.data).then(function(response) {
			after(response);
		});
	}

	vm.updateSchool = function() {
		schoolsService.updateSchool(vm.data).then(function(response) {
			 after(response);
		});
	}

	function after(response) {
		if(response.status) {
			$mdDialog.hide(response);
		}
		$mdToast.show(
			$mdToast.simple()
				.textContent(response.msg)
				.position('top right')
				.hideDelay(3000)
		);
	}

	vm.submitForm = function() {
		if(vm.new) {
			vm.addSchool();
		} else {
			vm.updateSchool();
		}
	}


}
