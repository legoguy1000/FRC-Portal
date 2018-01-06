var CACHE_NAME = 'my-site-cache-v1';
var urlsToCache = [
	'/index.html',
	'/views/main.html',
	'/js/app.js',
	'/js/controllers/main.js',
	'https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.6.5/angular.min.js'
];



self.addEventListener('install', function(event) {
  // Perform install steps
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('activate', function(event) {

  var cacheWhitelist = ['my-site-cache-v1'];

  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('push', function(event) {  
	console.log('Received a push message', event);
	console.log(event.data);

	var title = 'Yay a message.';  
	var body = 'We have received a push message.';  
	var icon = '/favicons/android-chrome-512x512.png?v=47Myd2nElq';  
	var tag = 'simple-push-demo-notification-tag';
	var data =  {'title':title, 'body':body, 'tag':tag};
	data = event.data.json();
	console.log(data);
	
  event.waitUntil(  
    self.registration.showNotification(data.title, {  
      body: data.body,  
      icon: icon, 
	  tag: data.tag,
	  actions: [  
		   {action: 'acknowledge', title: '👍Acknowledge'},
		]  
    })  
  );  
});

self.addEventListener('notificationclick', function(event) {
    console.log('Notification click: tag ', event.notification);
    event.notification.close();
    var url = 'https://portal.team2363.org';
	tag = event.notification.tag;
	if (event.action === 'acknowledge') {  
		console.log('acknowledge');
		fetch(url+'/site/acknowledgeNotification.php',{method: 'post',headers: {"Content-type": "application/x-www-form-urlencoded; charset=UTF-8"},body: 'tag='+tag}).then(function(response) {
			if (response.status !== 200) {
				console.log('Looks like there was a problem. Status Code: ' +
				response.status);
				return;
			}
		}).catch(function(err) {
			console.log('Fetch Error :-S', err);
		});
	} else {
		event.waitUntil(
			clients.matchAll({includeUncontrolled: true, type: 'window'})
			.then(function(windowClients) {
				console.log(windowClients.length);
				for (var i = 0; i < windowClients.length; i++) {
					var client = windowClients[i];
					console.log(client.url);
					if (client.url.includes(url) && 'focus' in client) {
						return client.focus();
						console.log('focus');
					}
				}
				if (clients.openWindow) {
					console.log('new window');
					return clients.openWindow(url);
				}
			})
		);
	}
});

self.addEventListener('notificationclose', e => console.log(e.notification));