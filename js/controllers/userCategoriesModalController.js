angular.module('FrcPortal')
.controller('userCategoriesModalController', ['$log','$element','$mdDialog', '$scope', 'usersService','$mdToast',
	userCategoriesModalController
]);
function userCategoriesModalController($log,$element,$mdDialog,$scope,usersService,$mdToast) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.categories = [];
	vm.query = {
		filter: '',
		limit: 10,
		order: 'name',
		page: 1
	};
	vm.limitOptions = [10,25,50,100];
	vm.loading = false;
	vm.userCatEdit = null;
	vm.formData = {};
	//function get room list
	vm.getUserCategories = function () {
		vm.promise =	usersService.getUserCategories().then(function(response){
			vm.categories = response.data;
		});
	};
	vm.getUserCategories();

	vm.editCat = function (category) {
		vm.userCatEdit = category.cat_id;
	};
	vm.cancelEdit = function () {
		vm.userCatEdit = null;
	};

	vm.updateUserCategory = function (category) {
		vm.promise =	usersService.updateUserCategory(category).then(function(response) {
			if(response.status) {
				vm.userCatEdit = null;
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	};

	vm.addNewUserCategory = function () {
		vm.promise =	usersService.addNewUserCategory(vm.formData).then(function(response) {
			if(response.status) {
				vm.formData = null;
				vm.newTypeForm.$setPristine();
				vm.newTypeForm.$setUntouched();
				vm.categories = response.data;
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	};

	vm.deleteUserCategory = function (category) {
		vm.promise =	usersService.deleteUserCategory(category).then(function(response) {
			if(response.status) {
				vm.categories = response.data;
			}
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	};
}
