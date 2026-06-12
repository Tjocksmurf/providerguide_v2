<?php

namespace Drupal\migrate_google_sheets\Plugin\migrate_plus\data_parser;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\migrate_plus\DataFetcherPluginManager;
use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Obtain Google Sheet data for migration.
 *
 * @DataParser(
 *   id = "google_sheets",
 *   title = @Translation("Google Sheets")
 * )
 */
class GoogleSheets extends Json implements ContainerFactoryPluginInterface {

  /**
   * Array of headers from the first row.
   *
   * @var array
   */
  protected $headers = [];

  /**
   * Constructs a GoogleSheets object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\migrate_plus\DataFetcherPluginManager $fetcherPluginManager
   *   The data fetcher plugin manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The system time service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    DataFetcherPluginManager $fetcherPluginManager,
    protected CacheBackendInterface $cache,
    protected ConfigFactoryInterface $configFactory,
    protected TimeInterface $time,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $fetcherPluginManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.migrate_plus.data_fetcher'),
      $container->get('cache.default'),
      $container->get('config.factory'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceData(string $url, string|int $item_selector = '') {
    $source_data = $this->fetchSourceData($url);

    // For Google Sheets, the actual row data lives under table->rows.
    if (isset($source_data['values'])) {
      // Set headers from first row.
      $first_row = array_shift($source_data['values']);
      $columns = $first_row;

      $this->headers = array_map(function ($col) {
        return strtolower($col);
      }, $columns);

      return $source_data['values'];
    }

    return [];
  }

  /**
   * Fetch the source data from the URL.
   *
   * @param string $url
   *   The sheets API URL.
   */
  protected function fetchSourceData(string $url): array {
    $parsed_url = UrlHelper::parse($url);

    // Add API key from config if it exists and a key isn't already in the URL.
    $api_key = $this->configFactory->get('migrate_google_sheets.settings')->get('api_key');
    if ($api_key && !in_array('key', array_keys($parsed_url['query']))) {
      $parsed_url['query'] = array_merge($parsed_url['query'], ['key' => $api_key]);
      $url = Url::fromUri(urldecode($parsed_url['path']), $parsed_url)->toString();
    }

    $cache_lifetime = $this->configuration['cache_lifetime'] ?? 0;
    $cid = 'migrate_google_sheets:' . md5($url);
    $cached_response = $this->cache->get($cid);
    $now = $this->time->getRequestTime();
    $cache_expiration = $now + $cache_lifetime;

    if ($cache_lifetime && $cached_response && $cached_response->data && $cached_response->expire > $now) {
      return $cached_response->data;
    }

    $response = $this->getDataFetcherPlugin()->getResponseContent($url);

    // Convert objects to associative arrays.
    $source_array = json_decode($response, TRUE);

    // If json_decode() has returned NULL, it might be that the data isn't
    // valid utf8 - see http://php.net/manual/en/function.json-decode.php#86997.
    if (!$source_array) {
      $utf8response = mb_convert_encoding($response, 'UTF-8');
      $source_array = json_decode($utf8response, TRUE);
    }

    if ($cache_lifetime) {
      $this->cache->set($cid, $source_array, $cache_expiration);
    }

    return $source_array;
  }

  /**
   * {@inheritdoc}
   */
  protected function fetchNextRow(): void {
    $current = $this->iterator->current();
    if (is_array($current)) {
      foreach ($this->fieldSelectors() as $field_name => $selector) {
        // Actual values are stored in c[<column index>]['v'].
        $column_index = array_search(strtolower($selector), $this->headers);
        if ($column_index >= 0 && isset($current[$column_index])) {
          $this->currentItem[$field_name] = $current[$column_index];
        }
        else {
          $this->currentItem[$field_name] = '';
        }
      }
      $this->iterator->next();
    }
  }

}
