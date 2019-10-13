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

self.addEventListener('fetch', function(event) {
	if ( event.request.url.match( '^.*(sse.php).*$' ) ) {
		return false;
	}
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        // Cache hit - return response
        if (response) {
          //return response;
        }
        return fetch(event.request);
      }
    )
  );
});
