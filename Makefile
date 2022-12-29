# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.

# Variables that can be overridden by env variables
VERSION?=$(shell sed -ne 's/^\s*<version>\(.*\)<\/version>/\1/p' appinfo/info.xml)
GIT_TAG?=v$(VERSION)
OCC?=php ../../occ
NPM?=npm

# Release variables
VERSION_CHANGELOG:=$(shell sed -ne 's/^\#\#\s\([0-9\.]\+-*\w*\)\s-\s.*$$/\1/p' CHANGELOG.md | head -n1 )

# Upgrade: once we have git >= 2.22 everywhere we can use the more
# readable GIT_BRANCH:=$(shell git branch --show-current)
GIT_BRANCH:=$(shell git rev-parse --abbrev-ref HEAD)
GIT_REMOTE:=$(shell git config --get "branch.${GIT_BRANCH}.remote")

# Internal variables
APP_NAME:=$(notdir $(CURDIR))
PROJECT_DIR:=$(CURDIR)/../$(APP_NAME)
BUILD_DIR:=$(CURDIR)/build
BUILD_TOOLS_DIR:=$(BUILD_DIR)/tools
RELEASE_DIR:=$(BUILD_DIR)/release
CERT_DIR:=$(HOME)/.nextcloud/certificates

# So far just for removing releases again
NEXTCLOUD_API_URL:=https://apps.nextcloud.com/api/v1/apps/$(APP_NAME)

GITHUB_PROJECT_URL:=https://github.com/mejo-/$(APP_NAME)

# Install build tools
composer:
ifeq (, $(wildcard $(BUILD_TOOLS_DIR)/composer.phar))
	mkdir -p $(BUILD_TOOLS_DIR)
	cd $(BUILD_TOOLS_DIR) && curl -sS https://getcomposer.org/installer | php
endif

$(BUILD_TOOLS_DIR)/info.xsd:
	mkdir -p $(BUILD_TOOLS_DIR)
	curl https://apps.nextcloud.com/schema/apps/info.xsd \
	--silent --location --output $(BUILD_TOOLS_DIR)/info.xsd

# Install dependencies
node-modules:
	$(NPM) ci

composer-install-no-dev: composer
	php $(BUILD_TOOLS_DIR)/composer.phar install --prefer-dist --no-dev

# Clean build artifacts
clean:
	rm -rf js/*
	rm -rf $(RELEASE_DIR)/$(APP_NAME)

# Also remove build tools and dependencies
distclean: clean
	rm -rf $(BUILD_TOOLS_DIR)
	rm -rf node_modules
	rm -rf vendor

# Lint
lint: lint-js lint-appinfo

lint-js:
	$(NPM) run lint

lint-appinfo: $(BUILD_TOOLS_DIR)/info.xsd
	xmllint appinfo/info.xml --noout \
		--schema $(BUILD_TOOLS_DIR)/info.xsd

# Development

# Update psalm baseline
php-psalm-baseline:
	$(CURDIR)/vendor/bin/psalm.phar --set-baseline=tests/psalm-baseline.xml lib/
	$(CURDIR)/vendor/bin/psalm.phar --update-baseline lib/

# Build

build-js-dev:
	$(NPM) run dev

build-js-production:
	$(NPM) run build

# Build a release package
build: node-modules build-js-production composer-install-no-dev
	@if [ -n "$$(git status --porcelain)" ]; then \
		echo "Git repo not clean!"; \
		exit 1; \
	fi
	mkdir -p $(RELEASE_DIR)
	rsync -a --delete --delete-excluded \
		--exclude=".[a-z]*" \
		--exclude="Makefile" \
		--exclude="Dockerfile" \
		--exclude="TODO*" \
		--exclude="babel.config.js" \
		--exclude="build" \
		--exclude="composer.*" \
		--exclude="cypress" \
		--exclude="cypress.config.js" \
		--exclude="node_modules" \
		--exclude="package-lock.json" \
		--exclude="package.json" \
		--exclude="psalm.xml" \
		--exclude="src" \
		--exclude="stylelint.config.js" \
		--exclude="tests" \
		--exclude="webpack.*" \
	$(PROJECT_DIR) $(RELEASE_DIR)/
	@if [ -f $(CERT_DIR)/$(APP_NAME).key ]; then \
		echo "Signing code…"; \
		$(OCC) integrity:sign-app --privateKey="$(CERT_DIR)/$(APP_NAME).key" \
			--certificate="$(CERT_DIR)/$(APP_NAME).crt" \
			--path="$(RELEASE_DIR)/$(APP_NAME)"; \
	fi
	tar -czf $(RELEASE_DIR)/$(APP_NAME)-$(VERSION).tar.gz \
		-C $(RELEASE_DIR) $(APP_NAME)
	# Sign the release tarball
	@if [ -f $(CERT_DIR)/$(APP_NAME).key ]; then \
		echo "Signing release tarball…"; \
		openssl dgst -sha512 -sign $(CERT_DIR)/$(APP_NAME).key \
			$(RELEASE_DIR)/$(APP_NAME)-$(VERSION).tar.gz | openssl base64; \
	fi
	rm -rf $(RELEASE_DIR)/$(APP_NAME)

release-checks:
ifneq ($(VERSION),$(VERSION_CHANGELOG))
	$(error Version missmatch between `appinfo/info.xml`: $(VERSION) and `CHANGELOG.md`: $(VERSION_CHANGELOG))
endif
	@if git tag | grep -qFx $(GIT_TAG); then \
		echo "Git tag already exists!"; \
		echo "Delete it with 'git tag -d $(GIT_TAG)'"; \
		exit 1; \
	fi
	@if git ls-remote --tags $(GIT_REMOTE) "refs/tags/$(GIT_TAG)" | grep $(GIT_TAG); then \
		echo "Git tag already exists on remote $(GIT_REMOTE)!"; \
		echo "Delete it with 'git push $(GIT_REMOTE) :$(GIT_TAG)'"; \
		exit 1; \
	fi

# Prepare the release package for the app store
release: release-checks lint-appinfo build
	# Git tag and push
	git tag $(GIT_TAG) -m "Version $(VERSION)" && git push $(GIT_REMOTE) $(GIT_TAG)

	# Publish the release on Github
	gh release create $(GIT_TAG) -F CHANGELOG.md ./build/release/$(APP_NAME)-$(VERSION).tar.gz

	@echo "URL to release tarball (for app store): $(GITHUB_PROJECT_URL)/releases/download/$(GIT_TAG)/$(APP_NAME)-$(VERSION).tar.gz"

delete-release: delete-release-from-github delete-release-from-appstore

delete-release-from-github:
ifndef RELEASE_NAME
	  $(error Please specify the release to remove with $$RELEASE_NAME)
endif
	echo 'Removing release from Github.'
	gh release delete $(RELEASE_NAME) --cleanup-tag --yes

delete-release-from-appstore:
ifndef RELEASE_NAME
	  $(error Please specify the release to remove with $$RELEASE_NAME)
endif
ifndef NEXTCLOUD_PASSWORD
	  $(error Missing $$NEXTCLOUD_PASSWORD)
endif
	echo 'Removing release from nextcloud app store.'
	curl -s -X DELETE $(NEXTCLOUD_API_URL)/releases/$(RELEASE_NAME) \
		-u 'collectivecloud:$(NEXTCLOUD_PASSWORD)'

.PHONY: node-modules composer-install-no-dev clean distclean lint lint-js build-js-dev build-js-production build php-psalm-baseline release delete-release delete-release-from-gitlab delete-release-from-appstore
