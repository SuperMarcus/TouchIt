#!/bin/bash

PLUGIN_FILES="$(ls)"
SERVER_CHANNEL_DEVELOPMENT="development"
SERVER_CHANNEL_BETA="beta"
SERVER_CHANNEL_STABLE="stable"
IGNORE_CERT="yes"
DEV_TOOLS_DOWNLOAD_URL="https://github.com/PocketMine/DevTools/releases/download/v1.9.0/DevTools_v1.9.0.phar"

shopt -s expand_aliases
type wget > /dev/null 2>&1
if [ $? -eq 0 ]; then
	if [ "$IGNORE_CERT" == "yes" ]; then
		alias download_file="wget --no-check-certificate -q -O -"
	else
		alias download_file="wget -q -O -"
	fi
else
	type curl >> /dev/null 2>&1
	if [ $? -eq 0 ]; then
		if [ "$IGNORE_CERT" == "yes" ]; then
			alias download_file="curl --insecure --silent --location"
		else
			alias download_file="curl --silent --location"
		fi
	else
		echo "[Build] error, curl or wget not found!"
		exit 1
	fi
fi

echo "[Build] Preparing dependents..."

mkdir "plugins"
mkdir "plugins/TouchIt_Build"
mkdir "server"
mkdir "build"

pecl install channel://pecl.php.net/pthreads-2.0.10
pecl install channel://pecl.php.net/weakref-0.2.6
echo | pecl install channel://pecl.php.net/yaml-1.1.1

echo "[Build] Moving files..."

mv ${PLUGIN_FILES} "plugins/TouchIt_Build"

echo "[Build] Fetching PocketMine-MP version data..."

DEVELOPMENT_VERSION_DATA=$(download_file "http://www.pocketmine.net/api/?channel=$SERVER_CHANNEL_DEVELOPMENT")

DEVELOPMENT_VERSION=$(echo "$DEVELOPMENT_VERSION_DATA" | grep '"version"' | cut -d ':' -f2- | tr -d ' ",')
DEVELOPMENT_BUILD=$(echo "$DEVELOPMENT_VERSION_DATA" | grep build | cut -d ':' -f2- | tr -d ' ",')
DEVELOPMENT_API_VERSION=$(echo "$DEVELOPMENT_VERSION_DATA" | grep api_version | cut -d ':' -f2- | tr -d ' ",')
DEVELOPMENT_VERSION_DOWNLOAD=$(echo "$DEVELOPMENT_VERSION_DATA" | grep '"download_url"' | cut -d ':' -f2- | tr -d ' ",')

echo "[Build] Development: $DEVELOPMENT_VERSION API $DEVELOPMENT_API_VERSION (build $DEVELOPMENT_BUILD)"

BETA_VERSION_DATA=$(download_file "http://www.pocketmine.net/api/?channel=$SERVER_CHANNEL_BETA")

BETA_VERSION=$(echo "$BETA_VERSION_DATA" | grep '"version"' | cut -d ':' -f2- | tr -d ' ",')
BETA_BUILD=$(echo "$BETA_VERSION_DATA" | grep build | cut -d ':' -f2- | tr -d ' ",')
BETA_API_VERSION=$(echo "$BETA_VERSION_DATA" | grep api_version | cut -d ':' -f2- | tr -d ' ",')
BETA_VERSION_DOWNLOAD=$(echo "$BETA_VERSION_DATA" | grep '"download_url"' | cut -d ':' -f2- | tr -d ' ",')

echo "[Build] Beta: $BETA_VERSION API $BETA_API_VERSION (build $BETA_BUILD)"

STABLE_VERSION_DATA=$(download_file "http://www.pocketmine.net/api/?channel=$SERVER_CHANNEL_STABLE")

STABLE_VERSION=$(echo "$STABLE_VERSION_DATA" | grep '"version"' | cut -d ':' -f2- | tr -d ' ",')
STABLE_BUILD=$(echo "$STABLE_VERSION_DATA" | grep build | cut -d ':' -f2- | tr -d ' ",')
STABLE_API_VERSION=$(echo "$STABLE_VERSION_DATA" | grep api_version | cut -d ':' -f2- | tr -d ' ",')
STABLE_VERSION_DOWNLOAD=$(echo "$STABLE_VERSION_DATA" | grep '"download_url"' | cut -d ':' -f2- | tr -d ' ",')

echo "[Build] Stable: $STABLE_VERSION API $STABLE_API_VERSION (build $STABLE_BUILD)"

echo "[Build] Downloading..."

download_file "$DEVELOPMENT_VERSION_DOWNLOAD" > "server/development.phar"
download_file "$BETA_VERSION_DOWNLOAD" > "server/beta.phar"
download_file "$STABLE_VERSION_DOWNLOAD" > "server/stable.phar"
download_file "$DEV_TOOLS_DOWNLOAD_URL" > "plugins/devtools.phar"

echo "[Build] Preparation finished"