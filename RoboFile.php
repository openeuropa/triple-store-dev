<?php

use Robo\Contract\ConfigAwareInterface;

/**
 * Triple store setup commands.
 */
class RoboFile extends \Robo\Tasks implements ConfigAwareInterface {

  use \Robo\Common\ConfigAwareTrait;

  /**
   * Fetch data.
   *
   * @command fetch
   */
  public function fetch() {

    $tasks = [];
    $tasks[] = $this->taskFilesystemStack()->mkdir($this->config->get('import_dir'));
    foreach ($this->config->get('data') as $datum) {

      if (!empty($datum['url'])) {
        // Fetch raw RDF file source.
        $tasks[] = $this->taskExec('wget')->option(
          '-O',
          $this->getFilePath($datum)
        )->arg($datum['url']);
      }
      // Create graph IRI file for import.
      $tasks[] = $this->taskWriteToFile($this->getFilePath($datum, 'rdf.graph'))->text($datum['graph']);

      // If a ZIP archive extract it and move content to its final destination.
      if ($datum['format'] === 'zip') {
        $tasks[] = $this->taskExtract($this->getFilePath($datum))->to($datum['name']);
        $tasks[] = $this->taskFilesystemStack()->copy($datum['name'].'/'.$datum['file'], $this->getFilePath($datum, 'rdf'));
        $tasks[] = $this->taskFilesystemStack()->remove($datum['name']);
      }
    }

    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Import data.
   *
   * @command import
   */
  public function import() {
    return $this->taskRunQueries([
      "ld_dir('./import', '*.rdf', NULL);",
      "rdf_loader_run();",
      "exec('checkpoint');",
      "WAIT_FOR_CHILDREN;",
    ]);
  }

  /**
   * Purge data.
   *
   * @command purge
   */
  public function purge() {
    return $this->taskRunQueries([
      'DELETE FROM DB.DBA.load_list;',
      'DELETE FROM DB.DBA.RDF_QUAD;',
    ]);
  }

  /**
   * Run list of queries via isql-v.
   *
   * @param array $queries
   *    Queries to be executed.
   *
   * @return \Robo\Collection\CollectionBuilder
   *    Task collection.
   */
  private function taskRunQueries(array $queries) {
    $backend = $this->getConfig()->get('backend');

    $tasks = [];
    $tasks[] = $this->taskWriteToFile('query.sql')->append(TRUE)->lines($queries);
    $tasks[] = $this->taskExec("cat query.sql");
    $tasks[] = $this->taskExec("isql-v -U {$backend['username']} -P {$backend['password']} < query.sql");
    $tasks[] = $this->taskFilesystemStack()->remove('query.sql');

    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Get full file path.
   *
   * @param array $datum
   *    File properties as in robo.yml.
   * @param string $format
   *    File format extension, i.e. "rdf", "zip", etc.
   *
   * @return string
   *    Full file path.
   */
  private function getFilePath(array $datum, $format = '') {
    $format = empty($format) ? $datum['format'] : $format;
    return $this->config->get('import_dir')."/{$datum['name']}.{$format}";
  }

}
