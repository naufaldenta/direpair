#!/usr/bin/env bash
set -Eeuo pipefail

readonly API_ROOT="/home/direpair/api.direpair.id"
readonly ENV_TEMPLATE="${API_ROOT}/.env.cpanel.example"
readonly ENV_FILE="${API_ROOT}/.env"

if [[ "${PWD}" != "${API_ROOT}" ]]; then
    cd "${API_ROOT}"
fi

if [[ ! -f "${ENV_TEMPLATE}" ]]; then
    echo "ERROR: ${ENV_TEMPLATE} tidak ditemukan. Upload seluruh isi apps/api terlebih dahulu."
    exit 1
fi

if [[ -f "${ENV_FILE}" ]]; then
    echo "ERROR: ${ENV_FILE} sudah ada. Setup dihentikan agar konfigurasi production tidak tertimpa."
    echo "Jika ini instalasi ulang, backup lalu hapus .env secara manual sebelum menjalankan skrip lagi."
    exit 1
fi

printf "Password database cPanel untuk user direpair_apiuser: "
read -rs DIREPAIR_DB_PASSWORD_INPUT
printf "\n"

if [[ -z "${DIREPAIR_DB_PASSWORD_INPUT}" ]]; then
    echo "ERROR: Password database tidak boleh kosong."
    exit 1
fi

printf "Hostname frontend Vercel (tanpa https://, contoh direpair-abc.vercel.app): "
read -r DIREPAIR_FRONTEND_HOST_INPUT
DIREPAIR_FRONTEND_HOST_INPUT="${DIREPAIR_FRONTEND_HOST_INPUT#https://}"
DIREPAIR_FRONTEND_HOST_INPUT="${DIREPAIR_FRONTEND_HOST_INPUT%/}"

if [[ ! "${DIREPAIR_FRONTEND_HOST_INPUT}" =~ ^[a-z0-9.-]+$ ]]; then
    echo "ERROR: Hostname frontend tidak valid."
    exit 1
fi

DIREPAIR_FRONTEND_ORIGIN_INPUT="https://${DIREPAIR_FRONTEND_HOST_INPUT}"
export DIREPAIR_DB_PASSWORD_INPUT DIREPAIR_FRONTEND_ORIGIN_INPUT
DIREPAIR_STATUS_TOKEN_INPUT="$(php -r 'echo bin2hex(random_bytes(32));')"
export DIREPAIR_STATUS_TOKEN_INPUT

php -r '
$template = file_get_contents(".env.cpanel.example");
if ($template === false) { fwrite(STDERR, "Template .env gagal dibaca.\n"); exit(1); }
$escape = static fn (string $value): string => str_replace(["\\", "\"", "\r", "\n"], ["\\\\", "\\\"", "", ""], $value);
$content = str_replace(
    ["__DIREPAIR_DB_PASSWORD__", "__DIREPAIR_STATUS_TOKEN_PEPPER__", "__DIREPAIR_FRONTEND_ORIGIN__"],
    [$escape((string) getenv("DIREPAIR_DB_PASSWORD_INPUT")), (string) getenv("DIREPAIR_STATUS_TOKEN_INPUT"), $escape((string) getenv("DIREPAIR_FRONTEND_ORIGIN_INPUT"))],
    $template
);
if (file_put_contents(".env", $content, LOCK_EX) === false) { fwrite(STDERR, ".env gagal dibuat.\n"); exit(1); }
'

unset DIREPAIR_DB_PASSWORD_INPUT DIREPAIR_STATUS_TOKEN_INPUT
chmod 600 .env

mkdir -p storage/app/private storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

composer install --no-dev --classmap-authoritative --no-interaction --prefer-dist
php artisan key:generate --force
php artisan config:clear
php artisan migrate --force

readonly ADMIN_EMAIL="admin@direpair.id"
readonly ADMIN_PASSWORD="$(php -r 'echo bin2hex(random_bytes(12));')"
export DIREPAIR_ADMIN_EMAIL="${ADMIN_EMAIL}"
export DIREPAIR_ADMIN_PASSWORD="${ADMIN_PASSWORD}"

php artisan tinker --execute='\App\Models\User::updateOrCreate(["email" => getenv("DIREPAIR_ADMIN_EMAIL")], ["name" => "Direpair Administrator", "password" => getenv("DIREPAIR_ADMIN_PASSWORD")]);'
unset DIREPAIR_ADMIN_EMAIL DIREPAIR_ADMIN_PASSWORD

php artisan optimize

echo ""
echo "Setup API selesai."
echo "Login       : https://api.direpair.id/operations/login"
echo "Admin email : ${ADMIN_EMAIL}"
echo "Admin pass  : ${ADMIN_PASSWORD}"
echo "Simpan password ini sekarang, login, lalu ganti dengan password milikmu."
echo "Health check: https://api.direpair.id/api/v1/health"
echo "Frontend    : ${DIREPAIR_FRONTEND_ORIGIN_INPUT}"
unset DIREPAIR_FRONTEND_ORIGIN_INPUT
