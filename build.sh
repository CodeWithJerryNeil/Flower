#!/usr/bin/env bash
# Builds dist/index.html (a full standalone page) from sunflower-card.html.
set -euo pipefail
cd "$(dirname "$0")"
mkdir -p dist
{
  printf '<!DOCTYPE html>\n<html lang="en">\n<head>\n<meta charset="UTF-8">\n<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">\n'
  sed -n '1,/^<style>$/{/^<style>$/!p}' sunflower-card.html
  echo '<style>'
  sed -n '/^<style>$/,/^<\/style>$/{/^<style>$/d;p}' sunflower-card.html
  printf '</head>\n<body>\n'
  sed -n '/^<\/style>$/,${/^<\/style>$/d;p}' sunflower-card.html
  printf '</body>\n</html>\n'
} > dist/index.html
