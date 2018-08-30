# FRC Portal (In Dev)

This project was developed for my FIRST Robotics Team 2363, Triple Helix.  We needed a way to handle team and event registration as well as hours and metrics tracking.


Version 2.10.0 (Current Dev Branch)
* Added button to download latest responses from Google form
* Organized functions to poll Membership Form
* Updated Change User Status cronjob to execute on the last day of month
* Re-added google maps embed to event page
* Update look of Login Settings page
* Minor style changes
* Add Event Requirements toggles on Event Admin page & add server side logic to update
* Add button to test Slack notifications

Version 2.9.0
* Add Monolog to app
* Add error checking for calendar syncing
* Event and Season registration will list all users who are Active or have a Registration
* Fix credential issue for Google APIs
* Removed Google Maps JavaScript API as it now requires a billing account
* Fixed disappearing deadline on sync or update

Version 2.8.0
* Minor fixes
* Upload form for Google Service Account Credentials file
* Fix initial install script
* Developing Roles and Permissions (not implemented yet)
* Created new Auth Class to set logged in user throughout app
* Fixed routes without requiring Admin Permissions
* Update composer decencies

Version 2.7.0
* Fix various styling issues
* Add setting to require a team email to login
* Replace login notification with toast
* Pull Google Calendar Event info for event registration deadline events
* Lazy load controllers, filters, services
* Update event registration menu.  Simplify process.
* Update New Event Dialog. New Event Dialog -> Event Search.
* Added right-click context menu for Event Admin.  Removed Edit Registration button.
* Updated event registration slack notifications.
* Added DB export before DB modifications


## Getting Started
*

### Prerequisites
* PHP 7.0 or greater
* MySQL
* Composer
*

### Installing
Clone Git Repo
```
git clone https://github.com/legoguy1000/FRC-Portal.git
```
Create a "secured" folder in "/api/app"
```
mkdir secured/
```
Copy config.example.ini to "secured"
```
cp config.example.ini secured/config.ini
```
Edit config.ini file with database credentials (/api/app/secured/)
Run initalInstall.php script located in (/api/app/)
```
php initalInstall.php
```
To upgrade an existing install pull the latest version from git and run the postUpgrade.php script
```
git pull
php postUpgrade.php
```
Go to url and login using provided admin credentials and configure accordingly

## Running the tests

Explain how to run the automated tests for this system

### Break down into end to end tests

Explain what these tests test and why

```
Give an example
```

### And coding style tests

Explain what these tests test and why

```
Give an example
```

## Deployment

Add additional notes about how to deploy this on a live system

## Built With

* [AngularJS](https://angularjs.org/) - The web framework used
* [Angular Material](https://material.angularjs.org) - Theme
* [PHP](https://php.net) - Backend
* [Slim PHP Framework](https://www.slimframework.com/) - API framework
* [Eloquent](https://laravel.com/docs/5.6/eloquent) - OOP model
* [MySQL](https://www.mysql.com/) - SQL Database

## Contributing

Please read [CONTRIBUTING.md](https://gist.github.com/PurpleBooth/b24679402957c63ec426) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/legoguy1000/FRC-Portal/tags).

## Authors

* **Alex Resnick** - *Project Owner* - [legoguy1000](https://github.com/legoguy1000)

See also the list of [contributors](https://github.com/your/project/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Hat tip to anyone whose code was used
* Inspiration
* etc
