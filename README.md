# :telephone_receiver: Telephone Provider for Nextcloud's ContactsMenu
[![Build Status](https://travis-ci.org/georgehrke/telephoneprovider.svg?branch=master)](https://travis-ci.org/georgehrke/telephoneprovider)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/georgehrke/telephoneprovider/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/georgehrke/telephoneprovider/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/georgehrke/telephoneprovider/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/georgehrke/telephoneprovider/?branch=master)

This app searches your contacts for their phone numbers and shows `tel://` links in Nextcloud's new ContactsMenu, so you can call them right away.

Place this app in **nextcloud/apps/**

## Building the app

The app can be built by using the provided Makefile by running:

    make

This requires the following things to be present:
* make
* which
* tar: for building the archive
* curl: used if phpunit and composer are not installed to fetch them from the web
* npm: for building and testing everything JS, only required if a package.json is placed inside the **js/** folder

The make command will install or update Composer dependencies if a composer.json is present and also **npm run build** if a package.json is present in the **js/** folder. The npm **build** script should use local paths for build systems and package managers, so people that simply want to build the app won't need to install npm libraries globally, e.g.:

## Running tests
You can use the provided Makefile to run all tests by using:

    make test

This will run the PHP unit and integration tests and if a package.json is present in the **js/** folder will execute **npm run test**

Of course you can also install [PHPUnit](http://phpunit.de/getting-started.html) and use the configurations directly:

    phpunit -c phpunit.xml

or:

    phpunit -c phpunit.integration.xml

for integration tests
