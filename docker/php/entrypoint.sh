#!/usr/bin/env bash
#
# Arranque del contenedor de la aplicacion.
#
# Espera a que PostgreSQL acepte conexiones, se asegura de que exista APP_KEY
# y ejecuta las migraciones pendientes. Todo lo que se deja fuera de aqui
# (app, nginx, node, queue) arranca en paralelo.

set -euo pipefail

cd /var/www/html

# El codigo viene montado desde el host y en Windows los permisos del volumen
# no respetan el chmod de la imagen, asi que se ajustan en cada arranque.
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

echo "==> Esperando a PostgreSQL..."

until php -r '
    $host = getenv("DB_HOST");
    $port = getenv("DB_PORT") ?: "5432";
    $socket = @fsockopen($host, (int) $port, $errno, $errstr, 3);
    exit($socket ? 0 : 1);
' 2>/dev/null; do
    sleep 2
done

echo "    PostgreSQL responde en ${DB_HOST:-postgres}:${DB_PORT:-5432}"

# APP_KEY: el archivo .env del host es de la instalacion con XAMPP, pero si
# falta la clave la aplicacion no puede ni cifrar la sesion.
if ! grep -qE '^APP_KEY=base64:.+' .env 2>/dev/null; then
    echo "==> No hay APP_KEY en .env, generando una..."
    php artisan key:generate --force --no-interaction
fi

echo "==> Aplicando migraciones pendientes..."
php artisan migrate --force --no-interaction

# Las vistas compiladas y la configuracion cacheada viven en el volumen del
# host: si el host ya las genero con otro PHP, se regeneran aqui.
php artisan optimize:clear --no-interaction >/dev/null 2>&1 || true

# Los assets se compilan dentro de la imagen, pero si el host desarrollo con
# Vite esta generando los suyos, no deben pisarse entre si.
if [ ! -f public/build/manifest.json ]; then
    echo "    AVISO: no hay manifest de Vite. El sitio no cargara los estilos"
    echo "    hasta que ejecutes:  docker compose run --rm node npm run build"
fi

echo "==> Listo. El sitio responde en ${APP_URL:-http://localhost:8080}"
echo ""

exec "$@"