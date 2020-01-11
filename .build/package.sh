#!/usr/bin/env bash

VERSION="${1#v}"
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

if [ ! -f phar-composer-1.1.0.phar ] ; then
  curl -JOL https://github.com/clue/phar-composer/releases/download/v1.1.0/phar-composer-1.1.0.phar
fi

phar_name="typoscript-lint-${VERSION}.phar"

chmod +x phar-composer-*.phar
./phar-composer-*.phar build "./${build_dir}" "${phar_name}"

echo "${signing_key}" | gpg --import

gpg --detach-sign --output "${phar_name}.asc" "${phar_name}"
