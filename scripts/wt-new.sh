#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
REGISTRY="${PROJECT_ROOT}/.worktree-registry"
WORKTREE_BASE="$(cd "${PROJECT_ROOT}/.." && pwd)/keiba-db-gen2-wt"
MAIN_SOURCE="${PROJECT_ROOT}/source"

if [[ $# -ne 2 ]]; then
  echo "Usage: $0 <issue-num> <branch-name>" >&2
  exit 1
fi

ISSUE_NUM="$1"
BRANCH_NAME="$2"

if ! [[ "$ISSUE_NUM" =~ ^[0-9]+$ ]]; then
  echo "Error: issue-num must be a positive integer" >&2
  exit 1
fi

if ! [[ "$BRANCH_NAME" =~ ^[a-zA-Z0-9/_-]+$ ]]; then
  echo "Error: branch-name contains invalid characters" >&2
  exit 1
fi

if [[ -f "$REGISTRY" ]] && grep -qE "^${ISSUE_NUM} " "$REGISTRY"; then
  echo "Error: issue ${ISSUE_NUM} already exists in registry" >&2
  exit 1
fi

get_next_offset() {
  if [[ ! -f "$REGISTRY" ]]; then
    echo 1
    return
  fi
  local used_offsets
  used_offsets=$(grep -vE '^\s*#|^\s*$' "$REGISTRY" | awk '{print $2}' | sort -n)
  local candidate=1
  while echo "$used_offsets" | grep -q "^${candidate}$"; do
    candidate=$((candidate + 1))
  done
  echo "$candidate"
}

OFFSET=$(get_next_offset)
WORKTREE_PATH="${WORKTREE_BASE}/issue-${ISSUE_NUM}"
WT_SOURCE="${WORKTREE_PATH}/source"

echo "==> Creating worktree: issue-${ISSUE_NUM} (offset=${OFFSET})"
echo "    Path   : ${WORKTREE_PATH}"
echo "    Branch : ${BRANCH_NAME}"

mkdir -p "${WORKTREE_BASE}"

git -C "${PROJECT_ROOT}" worktree add "${WORKTREE_PATH}" -b "${BRANCH_NAME}"

echo "==> Installing PHP dependencies via disposable container..."
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "${WT_SOURCE}:/var/www/html" \
  -w /var/www/html \
  laravelsail/php85-composer:latest \
  composer install --ignore-platform-reqs

echo "==> Installing Node dependencies..."
(cd "${WT_SOURCE}" && pnpm install --frozen-lockfile)

echo "==> Configuring .env..."
cp "${MAIN_SOURCE}/.env" "${WT_SOURCE}/.env"

APP_PORT="800${OFFSET}"
VITE_PORT="$((5173 + OFFSET))"
DB_PORT="$((3306 + OFFSET))"
DB_DATABASE="keiba_wt${OFFSET}"
COMPOSE_PROJECT_NAME="sail-wt-${OFFSET}"

replace_or_append() {
  local key="$1"
  local value="$2"
  local file="$3"
  if grep -qE "^${key}=" "$file"; then
    sed -i "s|^${key}=.*|${key}=${value}|" "$file"
  else
    echo "${key}=${value}" >> "$file"
  fi
}

replace_or_append "COMPOSE_PROJECT_NAME" "${COMPOSE_PROJECT_NAME}" "${WT_SOURCE}/.env"
replace_or_append "APP_PORT"             "${APP_PORT}"             "${WT_SOURCE}/.env"
replace_or_append "VITE_PORT"            "${VITE_PORT}"            "${WT_SOURCE}/.env"
replace_or_append "FORWARD_DB_PORT"      "${DB_PORT}"              "${WT_SOURCE}/.env"
replace_or_append "DB_DATABASE"          "${DB_DATABASE}"          "${WT_SOURCE}/.env"

if [[ ! -f "$REGISTRY" ]]; then
  echo "# issue_num offset worktree_path" > "$REGISTRY"
fi
echo "${ISSUE_NUM} ${OFFSET} ${WORKTREE_PATH}" >> "$REGISTRY"

echo ""
echo "======================================================"
echo " Worktree created successfully!"
echo "======================================================"
echo " Issue     : #${ISSUE_NUM}"
echo " Branch    : ${BRANCH_NAME}"
echo " Path      : ${WORKTREE_PATH}"
echo " APP_PORT  : ${APP_PORT}  =>  http://localhost:${APP_PORT}"
echo " VITE_PORT : ${VITE_PORT}"
echo " DB_PORT   : ${DB_PORT}"
echo " DB        : ${DB_DATABASE}"
echo ""
echo " Next steps:"
echo "   cd ${WT_SOURCE}"
echo "   ./vendor/bin/sail up -d"
echo "   ./vendor/bin/sail artisan migrate"
echo ""
echo " Run tests:"
echo "   DB_DATABASE=testing_wt${OFFSET} ./vendor/bin/sail artisan test"
echo "======================================================"
