<?php
/**
 * WordPress Configuration for Tech Media Portal
 * Environment variables loaded from .env
 */

// Load environment variables
function tp_load_env($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}
tp_load_env(__DIR__ . '/.env');

// ** Database settings ** //
define('DB_NAME',     getenv('DB_NAME')     ?: 'techportal');
define('DB_USER',     getenv('DB_USER')     ?: 'techportal');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_CHARSET',  getenv('DB_CHARSET')  ?: 'utf8mb4');
define('DB_COLLATE',  getenv('DB_COLLATE')  ?: '');

// ** Authentication Keys and Salts ** //
define('AUTH_KEY',         'FEb|J0`|T~R8+fV9pU_&-as]=x$Fy,v)}XQkP5xwu{1/x>.Q1WXD|%7n%l,Oer+L');
define('SECURE_AUTH_KEY',  'LbUY`pK rAbjM@<h}EPt[>y9x>F}Q<q,f9Uv12;e3pWd/<cLsM+jX,Go{Z@]ZbnI');
define('LOGGED_IN_KEY',    ':N1Bh$f|4&I4[_LXK>F~c!trb.MUw;<-^Gt4K5bL,c|r,k|Iqc&fD|8!eOYm}B4[');
define('NONCE_KEY',        'nN;<[{+nH0$2|59zKAD%]fq*YGv5FC1|5aDh|K/?8u(4rw ZSmVOC/C2!|lA;($h');
define('AUTH_SALT',        'o{Ea^G|Z.KJ%C7)`JGx],kg3*+ls^xU#:O%p^w=+C{;I-q56M?e15]*-R@oJ;OvL');
define('SECURE_AUTH_SALT', 'Urk}4P!E>X_D *;el~i7sT[9!Bgd1w_Qch(R/n2=v{AY@Vjt,RjPbXfO8e.+2$U2');
define('LOGGED_IN_SALT',   'E!b2jJ+Da? G~z2f70^{)8_v+NhxT3LaeTgJ|=KjX4||dsFP<fxs{<+=G0hnC^3^');
define('NONCE_SALT',       'B~e]NsO&Ob=]_8#QL@dbg*-B69l>~H}BC#WEFEjf$H-Iu,n_IoVi,rMI)9wKMDVW');

// ** WordPress Database Table Prefix ** //
$table_prefix = 'wp_';

// ** Debug Mode ** //
define('WP_DEBUG',      filter_var(getenv('WP_DEBUG'), FILTER_VALIDATE_BOOLEAN) ?: false);
define('WP_DEBUG_LOG',  filter_var(getenv('WP_DEBUG_LOG'), FILTER_VALIDATE_BOOLEAN) ?: false);
define('WP_DEBUG_DISPLAY', filter_var(getenv('WP_DEBUG_DISPLAY'), FILTER_VALIDATE_BOOLEAN) ?: false);

// ** Memory Limits ** //
define('WP_MEMORY_LIMIT',     getenv('WP_MEMORY_LIMIT')     ?: '256M');
define('WP_MAX_MEMORY_LIMIT', getenv('WP_MAX_MEMORY_LIMIT') ?: '512M');

// ** Custom Directories ** //
define('WP_CONTENT_DIR', __DIR__ . '/wp-content');
define('WP_CONTENT_URL', rtrim(getenv('WP_HOME') ?: 'https://techportal.24.jugaar.ai', '/') . '/wp-content');

// ** Security ** //
define('DISALLOW_FILE_EDIT', true);

// ** Performance & Caching ** //
define('WP_POST_REVISIONS', 5);
define('AUTOSAVE_INTERVAL', 300);
define('EMPTY_TRASH_DAYS', 14);
define('WP_CRON_LOCK_TIMEOUT', 120);

// ** Auto Updates ** //
define('WP_AUTO_UPDATE_CORE', 'minor');

// ** Force SSL for Admin ** //
define('FORCE_SSL_ADMIN', true);

// ** Limit Post Revisions for Speed ** //
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 5);
}

// ** Disable WordPress ZIP file uploads (security) ** //
define('ALLOW_UNFILTERED_UPLOADS', false);

// ** Block direct access to wp-config.php ** //
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'wp-config.php') {
    http_response_code(403);
    exit('Access denied');
}

// ** Absolute path to WordPress directory ** //
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// ** Sets up WordPress vars and included files ** //
require_once ABSPATH . 'wp-settings.php';
