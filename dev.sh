#!/usr/bin/env bash
# Local dev server: WordPress in WebAssembly, theme + plugin mounted live from this repo.
cd "$(dirname "$0")"
exec npx -y @wp-playground/cli@latest server \
  --port=9400 \
  --mount="$PWD/plugin/study-spots-core:/wordpress/wp-content/plugins/study-spots-core" \
  --mount="$PWD/theme/study-spots:/wordpress/wp-content/themes/study-spots" \
  --blueprint=blueprint.local.json \
  --login "$@"
