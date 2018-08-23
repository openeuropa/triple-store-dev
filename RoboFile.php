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
    foreach ($this->getConfig()->get('data') as $datum) {

      // RDF destination file.
      $source = "/tmp/{$datum['name']}.{$datum['format']}";

      // Fetch raw RDF file source.
      $tasks[] = $this->taskExec('wget')
          ->option('-O', $source)
          ->arg($datum['url']);

      if ($datum['format'] === 'zip') {
        $destination = "/tmp/{$datum['name']}.rdf";

        // Extract archive.
        $tasks[] = $this->taskExtract($source)->to($datum['name']);

        // Move RDF file to final destination.
        $tasks[] = $this->taskFilesystemStack()
          ->copy($datum['name'].'/'.$datum['file'], $destination);

        // Remove working directory.
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
    $backend = $this->getConfig()->get('backend');
    $collection = $this->collectionBuilder();
    foreach ($this->getConfig()->get('data') as $datum) {
      $task = $this->taskExec('curl')
        ->option('digest')
        ->option('verbose')
        ->option('user', $backend['username'].':'.$backend['password'])
        ->option('url', "{$backend['base_url']}/sparql-graph-crud-auth?graph-uri={$datum['graph']}")
        ->option('-T', "/tmp/{$datum['name']}.rdf");
      $collection->addTask($task);
    }
    return $collection->run();
  }

  /**
   * Purge data.
   *
   * @command purge
   */
  public function purge() {
    $backend = $this->getConfig()->get('backend');
    $this->_exec("echo 'DELETE FROM DB.DBA.RDF_QUAD;' | isql-v -U {$backend['username']} -P {$backend['password']} >/dev/null");
  }

}
