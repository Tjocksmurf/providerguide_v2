<?php

namespace Drupal\g_content\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Lock up and return internal id from an alias.
 * Skip row if no value is returned.
 *
 * @MigrateProcessPlugin(
 *   id = "pathtouri"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * field_text:
 *   plugin: pathtouri
 *   source: text
 * @endcode
 *
 */
class PathToUri extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {


    if(!substr( $value, 0, 1 ) === "/"){
      throw new MigrateSkipRowException();
    }

    $internal = $this->getInternal($value);

    if(!$internal){
      throw new MigrateSkipRowException();
    }

    return $internal;
  }

  /**
   * @param $path
   *
   * @return false|string
   */
  function getInternal($path) {
    $system_path = \Drupal::service('path.alias_manager')
      ->getPathByAlias($path);

    if ($system_path && $system_path != $path) {
      return 'internal:' . $system_path;
    }

    return false;
  }


}
