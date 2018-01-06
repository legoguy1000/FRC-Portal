angular.module('FrcPortal')
.controller('loginModalController', ['$auth', '$mdDialog',
	loginModalController
]);
function loginModalController($auth,$mdDialog) {
	var vm = this;

	vm.authenticate = function(provider) {
		$auth.authenticate(provider).then(function(response){ 
		//	toastr[response.data.type](response.data.msg, 'Login');
			alert(response.data.msg)
			var authed = $auth.isAuthenticated();
			if(authed) { 
				var data = {
					'auth': true,
				}
				$mdDialog.hide(data);
			}
		});
    };
	
	vm.cancel = function() {
		$mdDialog.cancel();
	}
}