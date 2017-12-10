# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
# @author Bernhard Posselt <dev@bernhard-posselt.com>
# @copyright Bernhard Posselt 2016

# Generic Makefile for building and packaging a Nextcloud app which uses npm and
# Composer.
#
# Dependencies:
# * make
# * which
# * curl: used if phpunit and composer are not installed to fetch them from the web
# * tar: for building the archive
#
# If no composer.json is in the app root directory, the Composer step
# will be skipped.

app_name=$(notdir $(CURDIR))
build_tools_directory=$(CURDIR)/build/tools
source_build_directory=$(CURDIR)/build/source/telephoneprovider
source_artifact_directory=$(CURDIR)/build/artifacts/source
source_package_name=$(source_artifact_directory)/$(app_name)
appstore_build_directory=$(CURDIR)/build/appstore/telephoneprovider
appstore_artifact_directory=$(CURDIR)/build/artifacts/appstore
appstore_package_name=$(appstore_artifact_directory)/$(app_name)
gcp=$(shell which gcp 2> /dev/null)

ifeq (, $(gcp))
	copy_command=cp
else
	copy_command=gcp
endif

# code signing
# assumes the following:
# * the app is inside the nextcloud/apps folder
# * the private key is located in ~/.nextcloud/telephoneprovider.key
# * the certificate is located in ~/.nextcloud/telephoneprovider.crt
configdir=$(CURDIR)/../../config
private_key=$(HOME)/.nextcloud/$(app_name).key
certificate=$(HOME)/.nextcloud/$(app_name).crt
sign_skip_msg="Skipping signing, no key and certificate found in $(private_key) and $(certificate)"
openssl_msg="SHA512 signature for appstore package"
ifneq (,$(wildcard $(private_key)))
ifneq (,$(wildcard $(certificate)))
	CAN_SIGN=true
endif
endif

TAR_CAN_EXCLUDE_VCS=$(shell expr `tar --version | grep ^tar | sed 's/^.* //g'` \>= 1.28)

all: appstore

# Removes the appstore build
.PHONY: clean
clean:
	rm -rf ./build

# Builds the source and appstore package
.PHONY: dist
dist:
	make source
	make appstore

# Builds the source package
.PHONY: source
source:
	rm -rf $(source_build_directory)
	mkdir -p $(source_build_directory)
	tar cvzf $(source_package_name).tar.gz ../$(app_name) \
	--exclude-vcs \
	--exclude="../$(app_name)/build" \
	--exclude="../$(app_name)/*.log" \

# Builds the source package for the app store, ignores php and js tests
# Builds the source package for the app store, ignores php and js tests
.PHONY: appstore
appstore:
	rm -rf $(appstore_build_directory) $(appstore_artifact_directory)
	mkdir -p $(appstore_build_directory) $(appstore_artifact_directory)
	$(copy_command) --parents -r \
	"appinfo" \
	"img" \
	"lib" \
	"COPYING" \
	"CHANGELOG.md" \
	$(appstore_build_directory)
ifeq "$(TAR_CAN_EXCLUDE_VCS)" "1"
	tar -czf $(appstore_package_name).tar.gz -C $(appstore_build_directory)/../ --exclude-vcs-ignores --exclude='.gitignore' $(app_name)
else
	tar -czf $(appstore_package_name).tar.gz -C $(appstore_build_directory)/../ --exclude='.gitignore' $(app_name)
endif
ifdef CAN_SIGN
	@echo $(openssl_msg)
	openssl dgst -sha512 -sign $(private_key) $(appstore_package_name).tar.gz | openssl base64
else
	@echo $(sign_skip_msg)
endif

# Command for running JS and PHP tests. Works for package.json files in the js/
# and root directory. If phpunit is not installed systemwide, a copy is fetched
# from the internet
.PHONY: test
test:
ifeq (, $(shell which phpunit 2> /dev/null))
	@echo "No phpunit command available, downloading a copy from the web"
	mkdir -p $(build_tools_directory)
	curl -sSL https://phar.phpunit.de/phpunit.phar -o $(build_tools_directory)/phpunit.phar
	php $(build_tools_directory)/phpunit.phar -c phpunit.xml
	php $(build_tools_directory)/phpunit.phar -c phpunit.integration.xml
else
	phpunit -c phpunit.xml --coverage-clover build/php-unit.clover
	phpunit -c phpunit.integration.xml --coverage-clover build/php-unit.clover
endif
