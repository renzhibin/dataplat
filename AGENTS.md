# AGENTS.md

## Cursor Cloud specific instructions

### Overview

This is **bi.analysis** (小猪BI / Data Analysis Platform), a BI and data analytics monorepo with two main services:

1. **PHP Frontend** (`datawork/`) — Yii 1.x web application served by Nginx + PHP-FPM on port 80
2. **Python Backend API** (`fakecube/`) — web.py REST API served by uWSGI behind Nginx on port 8001

### Required services

| Service | How to start |
|---------|-------------|
| MySQL 8.0 | `sudo mysqld --user=mysql &` |
| Redis | `sudo redis-server --daemonize yes` |
| PHP-FPM 7.3 | `sudo php-fpm7.3 -D` |
| Nginx | `sudo nginx` |
| Python API (uWSGI) | See command below |

**Start Python API:**
```bash
PYTHONPATH=/workspace/fakecube/src:/workspace/fakecube/src/mms/conf:/workspace/fakecube/src/mms/lib:/workspace/fakecube/src/mms/lib/db:/workspace/fakecube/src/mms/bin \
  ~/.local/bin/uwsgi --socket 127.0.0.1:7625 --module webapi \
  --chdir /workspace/fakecube/src/web_api --master --processes 2 --threads 2 \
  --buffer-size 65535 \
  --daemonize /workspace/fakecube/log/system_log/web_api/uwsgi_webapi.log \
  --pidfile /workspace/fakecube/data/webapi.pid
```

### Important caveats

- **Python 2.7 required**: The `fakecube/` backend is written for Python 2.7. Python 2.7.18 is installed at `/usr/local/python2.7/bin/python2.7` (symlinked to `/usr/local/bin/python2.7`). uWSGI and pip packages are installed in `~/.local/`.
- **Node 10 required for Gulp**: The `gulpfile.js` uses Gulp 3.x which requires Node.js 10.x. Use `nvm use 10` before running `npx gulp`. Node 22 is the default for other tasks.
- **PHP 7.3 required**: This codebase is written for PHP 5.x/7.x. PHP 7.3 (from ondrej/php PPA) is the best compatible version. PHP 7.4 has Smarty typed property inheritance issues, and PHP 8.x has `count()` TypeError on non-array types. The `index.php` suppresses `E_DEPRECATED` errors for compatibility.
- **MySQL auth plugin**: MySQL 8.0's default `caching_sha2_password` is not supported by PHP 7.3's MySQL client. Use `mysql_native_password`: `ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root';`
- **PYTHONPATH is critical**: The uWSGI process needs `mms/conf`, `mms/lib`, `mms/lib/db`, and `mms/bin` on its PYTHONPATH for Python 2-style implicit relative imports to work.
- **Config files**: `fakecube/src/mms/conf/env.py` and `mms_mysql_conf.py` are gitignored. Create `env.py` from `env.py.bak` and update `mms_mysql_conf.py` with local MySQL credentials (`root`/`root` at `127.0.0.1:3306`).
- **MySQL root password**: Set to `root` for local development. Databases required: `metric_meta`, `metric_real_data`, `metric`, `dt_db`, `dandelion`. Schema is at `conf/data_plat.sql` (imports into `metric_meta`).
- **Log directories**: Must exist before starting uWSGI: `fakecube/log/system_log/web_api/` and `fakecube/log/system_log/mms/`.
- **INNER_LOGIN_INTERFACE**: Set to `true` in `.env` to use the built-in login form instead of SSO (SSO service is not available locally).
- **Nginx config**: Custom site config at `/etc/nginx/sites-available/bi-analysis`. PHP frontend on port 80 (using `php7.3-fpm.sock`), Python API proxied on port 8001 to uWSGI at 127.0.0.1:7625.
- **Test user**: `admin@test.com` / `admin123` (created in `metric_meta.t_visual_user`). Login uses plain text password matching.

### Verify services

```bash
curl -s http://localhost/site/index    # Should return 200 with login page HTML
curl -s http://localhost:8001/list_app/ # Should return JSON: {"status": 0, "msg": "success", "data": []}
```
