<?php

/**
 * @file
 * Mark forced-compat contrib extensions as Drupal 11 compatible.
 *
 * These projects have no upstream Drupal 11 release yet and are installed via
 * the composer-drupal-lenient plugin. The plugin relaxes Composer's resolver
 * but does NOT touch each extension's info.yml `core_version_requirement`, so
 * Drupal itself still reports them as incompatible (and blocks update.php).
 *
 * This script patches their info.yml to advertise ^11. It runs automatically on
 * `composer install` / `composer update` (see composer.json "scripts"), so the
 * change survives every re-install of the gitignored contrib code.
 */

$root = dirname(__DIR__);

$targets = [
  'web/modules/contrib/block_visibility_conditions/block_visibility_conditions.info.yml',
  'web/modules/contrib/select2/select2.info.yml',
  'web/modules/contrib/taxonomy_delete/taxonomy_delete.info.yml',
  'web/modules/contrib/title_field_for_manage_display/title_field_for_manage_display.info.yml',
  'web/themes/contrib/electra/electra.info.yml',
];

foreach ($targets as $rel) {
  $file = $root . '/' . $rel;
  if (!is_file($file)) {
    continue;
  }
  $content = file_get_contents($file);

  if (preg_match('/^core_version_requirement:/m', $content)) {
    // Already uses the modern key: append ^11 if not present.
    if (strpos($content, '^11') !== false) {
      continue;
    }
    $content = preg_replace_callback(
      '/^core_version_requirement:[ \t]*(.+)$/m',
      function ($m) {
        $val = trim($m[1], " \t'\"");
        return "core_version_requirement: '" . $val . " || ^11'";
      },
      $content,
      1
    );
  }
  else {
    // Legacy "core: 8.x": drop it and add a modern requirement.
    $content = preg_replace('/^core:[ \t]*\S+[ \t]*\R?/m', '', $content);
    $content = "core_version_requirement: '^8 || ^9 || ^10 || ^11'\n" . $content;
  }

  file_put_contents($file, $content);
  echo "d11-compat: patched $rel\n";
}
