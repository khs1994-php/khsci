#!/bin/sh

NPM_USERNAME=$INPUT_USERNAME
NPM_PASSWORD=$INPUT_PASSWORD
NPM_EMAIL=${INPUT_EMAIL:-}
NPM_TOKEN=$INPUT_API_KEY
PLUGIN_TAG=${INPUT_TAG:-}
NPM_REGISTRY=${INPUT_REGISTRY:-}
PLUGIN_SKIP_VERIFY=${INPUT_SKIP_VERIFY:-}
PLUGIN_FAIL_ON_VERSION_CONFLICT=${INPUT_FAIL_ON_VERSION_CONFLICT:-}
PLUGIN_ACCESS=${INPUT_ACCESS:-}

exec /bin/drone-npm