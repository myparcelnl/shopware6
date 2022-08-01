#
# Makefile
#

.PHONY: help
.DEFAULT_GOAL := help

PLUGIN_VERSION=`php -r 'echo json_decode(file_get_contents("MyPaShopware/composer.json"))->version;'`

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# ------------------------------------------------------------------------------------------------------------

install: ## Installs all production dependencies
	@composer install --no-dev
	@cd src/Resources/app/administration && npm install --production
	@cd src/Resources/app/storefront && npm install --production

dev: ## Installs all dev dependencies
	@composer install
	@cd src/Resources/app/administration && npm install
	@cd src/Resources/app/storefront && npm install

clean: ## Cleans all dependencies
	rm -rf vendor
	rm -rf .reports | true
	@make clean-node

clean-node: ## Removes node_modules
	rm -rf src/Resources/app/administration/node_modules
	rm -rf src/Resources/app/storefront/node_modules

# ------------------------------------------------------------------------------------------------------------

build: ## Builds the package
	@rm -rf src/Resources/app/storefront/dist
	@cd ../../.. && php bin/console plugin:refresh
	@cd ../../.. && php bin/console plugin:install MyPaShopware --activate --clearCache | true
	@cd ../../.. && php bin/console plugin:refresh
	@cd ../../.. && php bin/console theme:dump
	@cd ../../.. && PUPPETEER_SKIP_DOWNLOAD=1 ./bin/build-js.sh
	@cd src/Resources/app/storefront && cp 'node_modules/@myparcel/delivery-options/dist/myparcel.js' 'dist/storefront/js/myparcel.js'
	@cd ../../.. && php bin/console theme:refresh
	@cd ../../.. && php bin/console theme:compile
	@cd ../../.. && php bin/console theme:refresh

release: ## Create a new release
	@make clean
	@make install
	@make build
	@make zip

zip: ## Creates a new ZIP package
	@php update-composer-require.php --shopware=^6.4.1 --env=prod
	@cd .. && echo "\nCreating Zip file MyPaShopware-$(PLUGIN_VERSION).zip\n"
	@cd .. && rm -rf MyPaShopware-$(PLUGIN_VERSION).zip
	@cd .. && zip -qq -r -0 MyPaShopware-$(PLUGIN_VERSION).zip MyPaShopware/ -x '*.editorconfig' '*.git*' '*.reports*' '*.travis.yml*' '*/tests*' '*/makefile' '*.DS_Store' '*/phpunit.xml' '*/.phpstan.neon' '*/.php_cs.php' '*/phpinsights.php' '*node_modules*' '*administration/build*' '*storefront/build*' '*/update-composer-require.php'
	@php update-composer-require.php --shopware=^6.4.1 --env=dev
