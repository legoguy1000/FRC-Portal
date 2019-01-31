angular.module('FrcPortal')
.controller('signInModalController', ['$rootScope','$log','$element','$mdDialog', '$scope', 'usersService','$mdToast','userInfo','signinService','$interval','$document','$timeout','$document',
	signInModalController
]);
function signInModalController($rootScope,$log,$element,$mdDialog,$scope,usersService,$mdToast,userInfo,signinService,$interval,$document,$timeout,$document) {
	var vm = this;

	vm.cancel = function() {
		$mdDialog.cancel();
	}
	vm.userInfo = userInfo;
	vm.pin = '';
	vm.scanContent = '';
	var tick = function() {
		vm.clock = Date.now();
	}
	vm.loading = false;
	vm.msg = '';
	vm.hideVideo = false;
	vm.aniFrame;
	vm.localstream;
	tick();
	$interval(tick, 1000);

	$timeout(function() {
			vm.video = $document[0].createElement("video");
			//vm.scanner = $document[0].getElementById("scanner");
			vm.canvasElement = $document[0].getElementById("canvas");
		  vm.canvas = vm.canvasElement.getContext("2d")
		  function drawLine(begin, end, color) {
		    vm.canvas.beginPath();
		    vm.canvas.moveTo(begin.x, begin.y);
		    vm.canvas.lineTo(end.x, end.y);
		    vm.canvas.lineWidth = 4;
		    vm.canvas.strokeStyle = color;
		    vm.canvas.stroke();
		  }
		  // Use facingMode: environment to attemt to get the front camera on phones
			function startStream() {
			  navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } }).then(function(stream) {
			    vm.video.srcObject = stream;
					vm.localstream = stream;
			    vm.video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
			    vm.video.play();
			    vm.aniFrame = requestAnimationFrame(tick1);
			  });
			}

	  function tick1() {
	    if (vm.video.readyState === vm.video.HAVE_ENOUGH_DATA) {
	      vm.hideVideo = false;
	      vm.canvasElement.height = vm.video.videoHeight;
	      vm.canvasElement.width = vm.video.videoWidth;
	      vm.canvas.drawImage(vm.video, 0, 0, vm.canvasElement.width, vm.canvasElement.height);
				drawLine({0,vm.canvasElement.height/2}, {vm.canvasElement.width,vm.canvasElement.height/2}, "#FF3B58");
	      var imageData = vm.canvas.getImageData(0, 0, vm.canvasElement.width, vm.canvasElement.height);
	      var code = jsQR(imageData.data, imageData.width, imageData.height, {
	        inversionAttempts: "dontInvert",
	      });
	      if (code) {
	        drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
	        drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
	        drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
	        drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
					vm.scanner(code.data);
	      } else {
	      }
	    }
	    vm.aniFrame = requestAnimationFrame(tick1);
	  }

		startStream();

		vm.scanner = function(content) {
			vm.msg = '';
			vm.loading = true;
			vm.scanContent = content;
			vm.stop();
			var data = {
				'token': content
			};
			signinService.signInOutQR(data).then(function(response) {
				vm.loading = false;
				vm.msg = response.msg;
				if(response.status) {
				$timeout( function() {
						vm.close(response.signInList);
					}, 2000 );
				}
			});
		}

		vm.stop = function() {
			vm.hideVideo = true;
			vm.video.srcObject = null;
			vm.localstream.getTracks().forEach(function(track) { track.stop(); })
			cancelAnimationFrame(vm.aniFrame);
		}

		vm.cancel = function() {
			vm.stop();
			$mdDialog.cancel();
		}

		vm.close = function(data) {
			vm.stop();
			$mdDialog.hide(data);
		}

		$rootScope.$on('400BadRequest', function(event,response) {
			vm.loading = false;
			startStream();
			$mdToast.show(
				$mdToast.simple()
					.textContent(response.msg)
					.position('top right')
					.hideDelay(3000)
			);
		});
	});



}
