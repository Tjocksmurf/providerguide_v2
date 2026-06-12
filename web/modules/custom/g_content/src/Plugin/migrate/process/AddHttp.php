<?php

namespace Drupal\g_content\Plugin\migrate\process;

use Drupal\Component\Utility\NestedArray;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * addhttp a value from an array.
 *
 * The addhttp process plugin is used to pull data from an input array, which
 * may have multiple levels. One use case is addhttping data from field arrays
 * in previous versions of Drupal. For instance, in Drupal 7, a field array
 * would be indexed first by language, then by delta, then finally a key such as
 * 'value'.
 *
 * Available configuration keys:
 * - source: The input value - must be an array.
 * - index: The array of keys to access the value.
 * - default: (optional) A default value to assign to the destination if the
 *   key does not exist.
 *
 * Examples:
 *
 * @code
 * process:
 *   new_text_field:
 *     plugin: addhttp
 *     source: some_text_field
 *     index:
 *       - und
 *       - 0
 *       - value
 * @endcode
 *
 * The PHP equivalent of this would be:
 * @code
 * $destination['new_text_field'] = $source['some_text_field']['und'][0]['value'];
 * @endcode
 * If a default value is specified, it will be returned if the index does not
 * exist in the input array.
 *
 * @code
 * plugin: addhttp
 * source: some_text_field
 * default: 'Default title'
 * index:
 *   - title
 * @endcode
 *
 * If $source['some_text_field']['title'] doesn't exist, then the plugin will
 * return "Default title".
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "addhttp",
 *   handle_multiples = TRUE
 * )
 */
class AddHttp extends ProcessPluginBase
{

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property)
  {

    if (!str_starts_with($value, 'http')) {
      $new_value = 'http://' . $value;
    } else {
      $new_value = $value;
    }

    return $new_value;

  }

}
