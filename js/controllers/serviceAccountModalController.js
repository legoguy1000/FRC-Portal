angular.module('FrcPortal')
.controller('serviceAccountModalController', ['$log','$element','$mdDialog', '$scope', 'usersService','$mdToast','credentials','Upload',
	serviceAccountModalController
]);
function serviceAccountModalController($log,$element,$mdDialog,$scope,usersService,$mdToast,credentials,Upload) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.credentialsArr = credentials;

	vm.close = function() {
		$mdDialog.hide(vm.credentials);
	}

	// upload later on form submit or something similar
	vm.submit = function() {
		if (vm.form.credentials.$valid && vm.credentials) {
			vm.upload(vm.credentials);
		}
	};

	// upload on file select or drop
	vm.upload = function (file) {
			Upload.upload({
					url: 'api/settings/serviceAccountCredentials',
					data: {file: file}
			}).then(function (resp) {
					console.log('Success ' + resp.config.data.file.name + 'uploaded. Response: ' + resp.data);
			}, function (resp) {
					console.log('Error status: ' + resp.status);
			}, function (evt) {
					var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
					console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file.name);
			});
	};
}
