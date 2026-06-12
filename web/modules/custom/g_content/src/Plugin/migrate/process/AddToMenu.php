<?php

namespace Drupal\g_content\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Add item to menu
 *
 * @MigrateProcessPlugin(
 *   id = "add_to_menu"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * field_text:
 *   plugin: add_to_menu
 *   source: text
 * @endcode
 *
 */
class AddToMenu extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    //Get internal path
    $internal_path = $this->getInternal($value);

    //Fetch menu
    $menu_name = 'main';
    $storage = \Drupal::entityManager()->getStorage('menu_link_content');
    $menu_links = $storage->loadByProperties(['menu_name' => $menu_name]);

    //Check if menu item already exists

    //If yes, check if it should be updated

    //If no
    // Get nid + title
    // Create item

    //    $menu_link = MenuLinkContent::create([
    //      'title' => $title,
    //      'link' => ['uri' => 'internal:/node/' . $nid],
    //      'menu_name' => 'main',
    //      'expanded' => TRUE,
    //    ]);
    //    $menu_link->save();

    //For parent/child

    //    \Drupal::entityTypeManager()
    //      ->getStorage('menu')
    //      ->create([
    //        'id' => 'test-menu',
    //        'label' => 'Test menu',
    //        'description' => 'Description text.',
    //      ])
    //      ->save();
    //
    //    $menu_link_1 = MenuLinkContent::create([
    //      'title' => 'Link 1',
    //      'link' => ['uri' => 'internal:/foo'],
    //      'menu_name' => 'test-menu',
    //      'expanded' => TRUE,
    //    ]);
    //    $menu_link_1->save();
    //
    //    $menu_link_2 = MenuLinkContent::create([
    //      'title' => 'Link 2',
    //      'link' => ['uri' => 'internal:/bar'],
    //      'menu_name' => 'test-menu',
    //      'expanded' => TRUE,
    //      'parent' => $menu_link_1->getPluginId(),
    //    ]);
    //    $menu_link_2->save();

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

    return FALSE;
  }


}
