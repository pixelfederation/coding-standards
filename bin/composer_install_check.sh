#!/bin/sh

set -e

out="$(composer install --dry-run 2>&1 || true)"

if echo "$out" | grep -q "Nothing to install, update or remove"; then
  echo "✅ ./vendors in sync with composer.lock"
  exit 0
else
  echo "❌ ./vendors not in sync with composer.lock!"
  echo "---- Composer output ----"
  echo "$out"
  echo "--------------------------"
  exit 1
fi
