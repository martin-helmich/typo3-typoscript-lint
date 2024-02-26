#!/usr/bin/env bash

VERSION="${1#v}"

phar_composer_version="1.4.0"
build_dir=".build/workspace"

if [ -z "${VERSION}" ] ; then
  echo "missing version argument" >&2
  exit 1
fi

set -e

rm -rf "${build_dir}"
mkdir -p "${build_dir}"

cp -a typoscript-lint typoscript-lint.dist.yml services.yml src vendor composer.json composer.lock LICENSE "${build_dir}/"
cd "${build_dir}/vendor" && rm -rf */*/tests/ */*/src/tests/ */*/docs/ */*/*.md */*/composer.* */*/phpunit.* */*/.gitignore */*/.*.yml */*/*.xml && cd - >/dev/null

if [ ! -f phar-composer-${phar_composer_version}.phar ] ; then
  curl -JOL https://github.com/clue/phar-composer/releases/download/v${phar_composer_version}/phar-composer-${phar_composer_version}.phar
fi

phar_name="typoscript-lint-${VERSION}.phar"

chmod +x phar-composer-${phar_composer_version}.phar
./phar-composer-${phar_composer_version}.phar build "./${build_dir}" "${phar_name}"

if [ -n "${signing_key}" ] ; then
  echo "${signing_key}" | gpg --import

  gpg --list-keys
  gpg --detach-sign -u "${signing_key_fingerprint}" --output "${phar_name}.asc" "${phar_name}"
fi
