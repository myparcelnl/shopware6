# Version 2.3.1

- For Shopware 6.4

# Version 2.3.0

#### New features
- feat(checkout): allow package type choice

#### Improvements
- fix(settings): remove superfluous settings and correct labels
- fix(checkout): show correct shipping prices
- fix(checkout): prevent changes to address that are not propagated

# Version 2.2.0

#### New features
- Add support for Shopware 6.5

# Version 2.1.0

#### New features
- Add label description

#### Improvements
- Improve MyParcel order grid view
- Allow shipping international orders when mailbox is default
- Add delivery options translations in config
- Allow postal codes with trailing spaces

# Version 2.0.0

#### Breaking changes
- To provide a better customer experience in the checkout we've reduced the number of MyParcel shipping methods in Shopware to one. This means the old shipping methods have been disabled, and should not be re-enabled again. Orders made with those shipping methods also no longer show up in the MyParcel Orders section in the administration.
- In preparation for upcoming features and enhancements, we've had to drop support for older Shopware versions. As such, the minimum required version of Shopware is now 6.4.1.

#### New features
- Improved customer experience in the checkout: The available shipping options dynamically change based on the shipping address and the selected settings from your MyParcel backoffice.
- Worldwide shipments: Shipping to countries outside of Europe is now available.

# Version 1.3.3
- Fixed administration module not being available

# Version 1.3.2
- Added cut-off time option
- Improved snippets
- Added insured shipments

# Version 1.3.1
- Added the option to disable the date selector in checkout
- Fixed a bug that caused exceptions when first selecting another type of shipping method and then changing it to MyParcel option
- Fixed some other minor bugs

# Version 1.3.0
- Adding the possibility to set which fields should be used for the address

# Version 1.2.1
- Fixed bug in Javascript that tried to retrieve the shipping options on all pages

# Version 1.2.0
- Added support for pickup points for carriers that support those

# Version 1.1.0
- Added Shopware 6.4 compatibility

# Version 1.0.1
- Added api test in plugin config.
- Fixed deinstallation of plugin letting data behind
- 
# Version 1.0.0
- Initial release
