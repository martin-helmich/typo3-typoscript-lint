#!/usr/bin/env bash

VERSION="${1#v}"

if [ -z "${VERSION}" ] ; then
  echo "missing version argument" >&2
  exit 1
fi

set -e

if [ ! -f phar-composer-1.1.0.phar ] ; then
  curl -JOL https://github.com/clue/phar-composer/releases/download/v1.1.0/phar-composer-1.1.0.phar
fi

phar_name="typoscript-lint-${VERSION}.phar"

chmod +x phar-composer-*.phar
./phar-composer-*.phar build . "${phar_name}"

echo "${signing_key}" | gpg --import

gpg --detach-sign --output "${phar_name}.asc" "${phar_name}"
