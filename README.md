# RECAPP Web Service
The RECAPP Web Service repository comprises all required back-end components including a schema-less CouchDB API and PHP/Javascript web application that enables researchers to configure and coordinate the delivery of free recall, serial recall and recognition memory studies to participant mobile devices. The RECAPP framework currently supports clients for iOS devices (iPhone, iPad). For more information regarding the setup and deployment of the RECAPP iOS client see [here](https://github.com/Recall-Project/recapp-ios-client).

## Prerequisities & Configuration
#### Hosting Environment
The PHP web application and CouchDB database must be deployed on a node running:
- ubuntu 14.04 or later OS
- CouchDB (compiled with the [GeoCouch](https://github.com/couchbase/geocouch) extension)
- PHP5.5.x
- Apache Server

All HTTP requests rely on URL rewrite rules, therefore Apache ReWrite module must be enabled in apache.conf. See [here](https://www.digitalocean.com/community/tutorials/how-to-set-up-mod_rewrite-for-apache-on-ubuntu-14-04) for more details. RECAPP framework rewrite rules are defined in .htaccess.

#### CouchDB Setup
RECAPP utilises [Sag]( https://github.com/sbisbee/sag) an open-source CouchDB integration module to communicate with CouchDB from PHP. A CouchDB database must first be initialised along with a service account with administrative privilages to access the database used to storage the RECAPP data model. These credentials must be configured in index.php:  
```sh
/index.php
define('ADMIN_USER', 'USER_NAME_HERE');
define('ADMIN_PASSWORD','PASS_HERE');
define('ESM_STORE','DB_STORE_HERE');
define('DB_HOST','HOST_HERE');
```

Once the CouchDB database has been initialised the following tw couchdb design documents must be stored that provide custom view and list capabilities:
```sh
/couch-documents/statistics-document.js
/couch-documents/recapp-document.js
```

#### Javascript
The hostname of the RECAPP web service must also be configured in the Javascript file:
```sh
/public/js/templates.js
var hostname = 'HOSTNAME_HERE';
```

## Development & Attribution
The RECAPP Framework was developed by Lancaster University and Essex University as part of the EU funded RECALL Project . The Recall project acknowledges the financial support of the Future and Emerging Technologies (FET) programme within the 7th Framework Programme for Research of the European Commission, under FET grant number: 612933. We kindly ask that any re-use of the RECAPP codebase as part of an application or future research is clearly attributed.
