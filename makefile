#
# Makefile
#

.PHONY: help
.DEFAULT_GOAL := help

PLUGIN_VERSION=`php -r 'echo json_decode(file_get_contents("./composer.json"))->version;'`

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# ------------------------------------------------------------------------------------------------------------

install-prod: ## Installs only production dependencies
	@composer install --no-dev --no-autoloader --no-scripts --no-suggest --no-interaction
	@yarn workspaces focus
	@yarn install --frozen-lockfile

install: ## Installs dev dependencies
	@composer install
	@yarn install

clean: ## Cleans dist folders
	@rm -rf src/**/dist

# ------------------------------------------------------------------------------------------------------------

install-plugin: ## Builds the package and installs the plugin
	@cd $$PROJECT_ROOT && bin/console plugin:refresh
	make build
	@cd $$PROJECT_ROOT && bin/console plugin:install MyPaShopware --activate --clearCache

build: ## Builds the package
	@rm -rf "src/Resources/app/storefront/dist"
	@cd $$PROJECT_ROOT && SHOPWARE_ADMIN_BUILD_ONLY_EXTENSIONS=1 php psh.phar administration:build
	@cd $$PROJECT_ROOT && SHOPWARE_ADMIN_BUILD_ONLY_EXTENSIONS=1 php psh.phar storefront:build
	@cp 'node_modules/@myparcel/delivery-options/dist/myparcel.js' 'src/Resources/app/storefront/dist/storefront/js/myparcel.js'

release: ## Create a new release
	make clean
	make install-prod
	make build
	make zip

zip: ## Create a zip file
	@php update-composer-require.php --env=prod --shopware=^6.4.1 --admin --storefront
	@rm -rf "MyPaShopware-$(PLUGIN_VERSION).zip"
	@echo "Creating MyPaShopware-$(PLUGIN_VERSION).zip..."
	@zip -q -r -0 MyPaShopware-$(PLUGIN_VERSION).zip src vendor ./CHANGELOG* ./README.md ./composer.json ./composer.lock
	@php update-composer-require.php --env=dev --shopware=^6.4.1 --admin --storefront
