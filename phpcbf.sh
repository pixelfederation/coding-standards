#!/bin/bash

./vendor/bin/phpcbf --standard=src/phpcs.ruleset.xml --extensions=php --tab-width=4 -sp src
