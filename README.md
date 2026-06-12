# Provider Guide (providerguide.com.au)

Drupal 11 site, run locally with [DDEV](https://ddev.readthedocs.io/).

## Stack

- **Drupal:** 11.3.x (core managed via Composer)
- **PHP:** 8.3
- **Database:** MariaDB 10.11
- **Docroot:** `web/`
- **Default theme:** `ndis` (custom, subtheme of `electra`) · **Admin theme:** `claro`

## Local setup

```bash
# 1. Start the environment
ddev start

# 2. Install Composer dependencies (core, contrib, vendor are NOT in git)
ddev composer install

# 3. Import the database (dump is not committed — obtain it separately)
ddev import-db --file=path/to/database.sql.gz

# 4. Rebuild caches
ddev drush cr

# 5. Open the site
ddev launch
```

Get a one-time admin login link with `ddev drush uli`.

After importing the database, sync the tracked configuration into it:

```bash
ddev drush config:import -y && ddev drush cr
```

## Configuration management

Site configuration is version-controlled in `config/sync/` (outside the docroot;
`$settings['config_sync_directory']` is set in `settings.php`). The workflow:

```bash
# After changing config in the UI (locally), export and commit it:
ddev drush config:export -y
git add config/sync && git commit -m "Config: <what changed>" && git push

# On another environment, apply committed config:
git pull && drush config:import -y && drush cr
```

Secrets are never committed. The SMTP password is blanked in `config/sync` and
supplied at runtime via a `$config['smtp.settings']['smtp_password']` override in
`web/sites/default/settings.local.php` (gitignored). Add other secrets the same way.

## Deploying to production

Production (`ssh cbb`, `/home/mailbook/providerguide.com.au/ndis`) is a clone of
this repo. To deploy:

```bash
cd ~/providerguide.com.au/ndis
git pull
composer install
vendor/bin/drush config:import -y
vendor/bin/drush updb -y
vendor/bin/drush cr
```

`settings.local.php` and `web/sites/default/files/` are gitignored, so deploys
never touch local secrets or uploaded files.

## Notes

- `vendor/`, `web/core/`, `web/modules/contrib/`, `web/themes/contrib/` and database
  dumps are intentionally **not** committed. `composer install` reproduces the exact
  locked versions; import the DB dump separately.
- Some contrib (e.g. `electra`, `title_field_for_manage_display`) have no official
  Drupal 11 release and are installed via the
  [`mglaman/composer-drupal-lenient`](https://github.com/mglaman/composer-drupal-lenient)
  plugin — see the `extra.drupal-lenient.allowed-list` in `composer.json`.
- Custom code lives in `web/modules/custom/` and `web/themes/custom/`.
