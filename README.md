Installation plugin
Install myparcel plugin to shopware 6 in the following steps.

from shopware installation folder:

`cd custom/plugins`

Then download the plugin with following command:

`git clone git@bitbucket.org:kiener-dev/kienermyparcel.git KienerMyParcel`

then go in to the plugin directory:

`cd KienerMyParcel`

In this folder run a composer install command:

`composer install`

Then continue installation in the shopware administration or continue to install in the console

If you install through console:

go back to shopware main folder:

`cd ../../..`

Then run following command:

`bin/console plugin:refresh`

to refresh plugin list, after that run

`bin/console plugin:install KienerMyParcel --activate -c`

*--activate* = activate plugin after installation

*-c* = Clear cache after installation

Fill in all nessecary field in the plugin configuration and configure shipment methods to merchants need
