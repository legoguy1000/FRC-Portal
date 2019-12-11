angular.module('FrcPortal')
.service('navService', ['$q',
	navService
]);
function navService($q){
    var menuItems = [
      {
        name: 'Dashboard',
        icon: 'dashboard',
        sref: 'main.home',
				admin: false
      }, {
        name: 'Events',
        icon: 'event',
        sref: 'main.events',
				admin: false,
				admin: false
      }, {
        name: 'Clock In/Out',
        icon: 'access_time',
        sref: 'main.signin',
				admin: false
      },
      /* {
        name: 'Profile',
        icon: 'person',
        sref: 'main.profile'
      }, */
      /* {
        name: 'Table',
        icon: 'view_module',
        sref: 'main.table'
      },
      {
        name: 'Data Table',
        icon: 'view_module',
        sref: 'main.data-table'
      }, */
      {
        name: 'Admin',
        icon: 'settings',
        sref: 'main.admin',
				admin: true
      }
    ];

    return {
      loadAllItems : function() {
        return $q.when(menuItems);
      }
    };
}
