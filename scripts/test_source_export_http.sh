#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$root"

# This negative probe must never accept an external or production target.
base_url="http://127.0.0.1:8888"
plugin_path="/wp-content/plugins/package-unifier"

for filename in export.php php_files_report.txt; do
  if [[ -e "$root/$filename" ]]; then
    echo "SOURCE_EXPOSURE=FAIL unexpected source artifact: $filename" >&2
    exit 1
  fi

  status="$(curl --silent --show-error --max-time 20 --max-redirs 0 --output /dev/null \
    --write-out '%{http_code}' "$base_url$plugin_path/$filename")"

  case "$status" in
    403|404)
      echo "SOURCE_EXPOSURE=PASS path=$filename status=$status"
      ;;
    *)
      echo "SOURCE_EXPOSURE=FAIL path=$filename unexpected_http_status=$status" >&2
      exit 1
      ;;
  esac
done

echo "SOURCE_EXPOSURE_ISOLATED=READY"
