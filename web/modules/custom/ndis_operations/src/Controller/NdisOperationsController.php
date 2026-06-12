<?php

namespace Drupal\ndis_operations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Query\EntityFieldQuery;
/**
 * Returns responses for ndis operations routes.
 */
class NdisOperationsController extends ControllerBase
{

  /**
   * Builds the response.
   */
  public function build()
  {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

  /**
   * Builds the response.
   */
  public function resave()
  {

    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'provider');
    $query->sort('changed', 'ASC');
    $query->range(0, 500);
    $result = $query->execute();

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $node_storage->loadMultiple($result);


   foreach ($nodes as $n){
     $n->set('field_grade', 4190);
     $n->save();
   }


    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
