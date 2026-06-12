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

## Notes

- `vendor/`, `web/core/`, `web/modules/contrib/`, `web/themes/contrib/` and database
  dumps are intentionally **not** committed. `composer install` reproduces the exact
  locked versions; import the DB dump separately.
- Some contrib (e.g. `electra`, `title_field_for_manage_display`) have no official
  Drupal 11 release and are installed via the
  [`mglaman/composer-drupal-lenient`](https://github.com/mglaman/composer-drupal-lenient)
  plugin — see the `extra.drupal-lenient.allowed-list` in `composer.json`.
- Custom code lives in `web/modules/custom/` and `web/themes/custom/`.
