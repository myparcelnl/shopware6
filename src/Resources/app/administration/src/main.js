import './init/api-service.init';
import './module/my-parcel';
import './service/myparcelApiTestService';
import './service/myparcelDropOffService'
import './component/myparcel-api-test-button';

import localeDE from './snippet/de_DE.json';
import localeEN from './snippet/en_GB.json';
import localeNL from './snippet/nl_NL.json';
Shopware.Locale.extend('de-DE', localeDE);
Shopware.Locale.extend('en-GB', localeEN);
Shopware.Locale.extend('nl-NL', localeNL);
