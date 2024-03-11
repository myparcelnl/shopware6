#
# Makefile
#

.PHONY: help
.DEFAULT_GOAL := help

PLUGIN_VERSION=`php -r 'echo json_decode(file_get_contents("MyPaShopware/composer.json"))->version;'`
SHOPWARE_COMPATIBILIY=^6.4.1

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# ------------------------------------------------------------------------------------------------------------

install-prod: ## Installs only production dependencies
	@php update-composer-require.php --env=prod --shopware=$(SHOPWARE_COMPATIBILIY)
	@composer install --no-dev --no-scripts --no-interaction --optimize-autoloader
	@php update-composer-require.php --env=prod --shopware=$(SHOPWARE_COMPATIBILIY) --release
	@yarn workspaces focus
	@yarn install --immutable

install: ## Installs dev dependencies
	@composer install
	@yarn install

clean: ## Cleans vendor
	@rm -rf vendor

# ------------------------------------------------------------------------------------------------------------

install-plugin: ## Builds the package and installs the plugin
	@php "$$PROJECT_ROOT/bin/console" plugin:refresh
	make build
	@php "$$PROJECT_ROOT/bin/console" plugin:install MyPaShopware --activate --clearCache

build: ## Builds the package
	@rm -rf "src/Resources/app/storefront/dist"
	@mkdir -p "src/Resources/app/storefront/dist"
	@rm -rf "src/Resources/public/administration"
	@cd "$$PROJECT_ROOT" && SHOPWARE_ADMIN_BUILD_ONLY_EXTENSIONS=1 php psh.phar administration:build
	@cd "$$PROJECT_ROOT" && SHOPWARE_ADMIN_BUILD_ONLY_EXTENSIONS=1 php psh.phar storefront:build
	#@cd "$$PROJECT_ROOT" && SHOPWARE_ADMIN_BUILD_ONLY_EXTENSIONS=1 bin/build-administration.sh
	#@cd "$$PROJECT_ROOT" && SHOPWARE_ADMIN_BUILD_ONLY_EXTENSIONS=1 bin/build-storefront.sh

release: ## Create a new release
	make clean
	make install-prod
	make build
	make zip

zip: ## Create a zip file
	@cd .. && rm -rf MyPaShopware-$(PLUGIN_VERSION).zip
	@cd .. && echo "Creating Zip file MyPaShopware-$(PLUGIN_VERSION).zip\n"
	@cd .. && zip -q -r -0 MyPaShopware-$(PLUGIN_VERSION).zip MyPaShopware/src MyPaShopware/vendor MyPaShopware/CHANGELOG* MyPaShopware/README.md MyPaShopware/composer.json MyPaShopware/package.json MyPaShopware/composer.lock && echo "MyPaShopware-$(PLUGIN_VERSION).zip created."
