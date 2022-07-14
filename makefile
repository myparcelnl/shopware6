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

clean-node: ## Removes node_modules
	rm -rf src/Resources/app/administration/node_modules
	rm -rf src/Resources/app/storefront/node_modules

# ------------------------------------------------------------------------------------------------------------

build: ## Builds the package
	@cd ../../.. && PUPPETEER_SKIP_DOWNLOAD=1 ./bin/build-js.sh

release: ## Create a new release
	@make clean
	@make install
	@make build
	@make zip

zip: ## Creates a new ZIP package
	@cd .. && rm -rf MyPaShopware-$(PLUGIN_VERSION).zip
	@cd .. && zip -qq -r -0 MyPaShopware-$(PLUGIN_VERSION).zip MyPaShopware/ -x '.editorconfig' '*.git*' '*.reports*' '*.travis.yml*' '*/tests*' '*/makefile' '*.DS_Store' '*/phpunit.xml' '*/.phpstan.neon' '*/.php_cs.php' '*/phpinsights.php'
