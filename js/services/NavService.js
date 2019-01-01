angular.module('FrcPortal')
.service('navService', ['$q',
	navService
]);
function navService($q){
    var menuItems = [
      {
        name: 'Dashboard',
        icon: 'dashboard',
        sref: 'main.home'
      }, {
        name: 'Events',
        icon: 'event',
        sref: 'main.events'
      }, {
        name: 'Clock In/Out',
        icon: 'access_time',
        sref: 'main.signin'
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
        sref: 'main.admin'
      }
    ];

    return {
      loadAllItems : function() {
        return $q.when(menuItems);
      }
    };
}
