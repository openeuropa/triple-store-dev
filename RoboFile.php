<?php

use Robo\Contract\ConfigAwareInterface;
use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Triple store setup commands.
 */
class RoboFile extends \Robo\Tasks implements ConfigAwareInterface {

  use \Robo\Common\ConfigAwareTrait;

  /**
   * Set default command options.
   *
   * @hook option *
   */
  public function setDefaultOptions(Command $command, AnnotationData $annotationData) {
    // Make sure that default option values can be overridden by env variables.
    $import_dir = getenv('IMPORT_DIR') ?: $this->getConfig()->get('import_dir');
    $host = getenv('DBA_HOST') ?: $this->getConfig()->get('backend.host');
    $port = getenv('DBA_PORT') ?: $this->getConfig()->get('backend.port');
    $username = getenv('DBA_USERNAME') ?: $this->getConfig()->get('backend.username');
    $password = getenv('DBA_PASSWORD') ?: $this->getConfig()->get('backend.password');

    // Set default command options.
    $command->addOption('import-dir', '', InputOption::VALUE_OPTIONAL, 'Data import directory.', $import_dir);
    $command->addOption('host', '', InputOption::VALUE_OPTIONAL, 'Virtuoso backend host.', $host);
    $command->addOption('port', '', InputOption::VALUE_OPTIONAL, 'Virtuoso backend port.', $port);
    $command->addOption('username', '', InputOption::VALUE_OPTIONAL, 'Virtuoso backend username.', $username);
    $command->addOption('password', '', InputOption::VALUE_OPTIONAL, 'Virtuoso backend password.', $password);
  }

  /**
   * Fetch data.
   *
   * @command fetch
   */
  public function fetch() {
    $tasks = [];
    $tasks[] = $this->taskFilesystemStack()->mkdir($this->input->getOption('import-dir'));
    foreach ($this->config->get('data') as $datum) {
      // Fetch raw RDF file source.
      $tasks[] = $this->taskExec('wget')->option('-O', $this->getFilePath($datum))->arg($datum['url']);
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
    $directory = $this->input->getOption('import-dir');
    return $this->taskRunQueries([
      "ld_dir('{$directory}', '*.rdf', NULL);",
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
   * Update information about OP vocabularies with automatic updating URLs.
   *
   * @command update_version
   */
  public function updateVersions() {
    $web_driver = \Facebook\WebDriver\Remote\RemoteWebDriver::create(
      "http://selenium:4444/wd/hub",
      [
        "browserName" => "chrome",
        "browserVersion" => "103.0",
      ]
    );
    $current_voc_titles = [];
    foreach ($this->config->get('data') as $datum) {
      $current_voc_titles[] = $datum['title'];
    }

    try{
      $parsedown = new Parsedown();
      $parsed_readme = $parsedown->parse(file_get_contents('README.md'));
      $raw_readme = file_get_contents('README.md');
      $updated_readme = FALSE;
      $crawler = new \Symfony\Component\DomCrawler\Crawler($parsed_readme);
      $links_to_op_vocs = $crawler->filter('li>a');
      /** @var \Facebook\WebDriver\WebDriverBy $webdriver_by */
      $webdriver_by = \Facebook\WebDriver\WebDriverBy::class;
      foreach ($links_to_op_vocs as $link) {
        // Use only links to OP.
        if (!in_array($link->textContent, $current_voc_titles)) {
          continue;
        }
        $web_driver->get($link->getAttribute('href'));
        sleep(10);

        // Check if used link is latest.
        $is_latest = (function ($web_driver) use ($webdriver_by) {
          try {
            return $web_driver->findElement($webdriver_by::cssSelector('div.eu-vocabularies-header .eu-vocabularies-latest-version'))->getText() === 'LATEST';
          } catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
            return false;
          }
        })($web_driver);

        if ($is_latest) {
          continue;
        }

        // Find last version.
        $latests_link = $web_driver->findElement($webdriver_by::cssSelector('div.tab-content .eu-vocabularies-latest-version'))->findElement($webdriver_by::xpath('../span/a'));
        $latests_link->click();
        sleep(10);
        $title = str_replace(' ', '[[:space:]]', $link->textContent);
        $regexp = '/^([[:space:]]\-[[:space:]]\[' . $title . '\])(\(.*\))$/m';
        $raw_readme = preg_replace($regexp, '$1' . '(' . $web_driver->getCurrentURL() . ')',  $raw_readme);
        $updated_readme = TRUE;

        // Visit page with links to rdf files.
        $web_driver->findElement($webdriver_by::linkText('Downloads'))->click();
        sleep(10);
        $rdf_link_url = $web_driver->findElement($webdriver_by::partialLinkText('-skos-ap-act.rdf'))->getAttribute('href');
        parse_str(parse_url(urldecode($rdf_link_url))['query'], $query);
        $rdf_urls_for_update[$link->textContent] = $query['cellarURI'];
      }

    }
    catch(Exception $e){
      echo 'Message: ' .$e->getMessage();
    }
    $web_driver->quit();
    $web_driver->close();

    // Update robo.yml file.
    $rdf_data = $this->config->get('data');
    $updated = FALSE;
    foreach ($rdf_data as $index => $rdf_info) {
      if (!empty($rdf_urls_for_update[$rdf_info['title']])) {
        $updated = TRUE;
        $rdf_data[$index]['url'] = $rdf_urls_for_update[$rdf_info['title']];
      }
    }
    if ($updated) {
      $this->config->set('data', $rdf_data);
      $exported = $this->config->export();
      unset($exported['command']);
      unset($exported['options']);
      $content = \Symfony\Component\Yaml\Yaml::dump($exported, 10);
      file_put_contents('robo.yml', $content);
    }
    // Update README.md file.
    if ($updated_readme) {
      file_put_contents('README.md', $raw_readme);
    }

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
    $host = $this->input->getOption('host');
    $port = $this->input->getOption('port');
    $username = $this->input->getOption('username');
    $password = $this->input->getOption('password');

    $tasks = [];
    $tasks[] = $this->taskWriteToFile('query.sql')->append(TRUE)->lines($queries);
    $tasks[] = $this->taskExec("cat query.sql");
    $tasks[] = $this->taskExec("isql-v -H {$host} -S {$port} -U {$username} -P {$password} < query.sql");
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
    return $this->input->getOption('import-dir')."/{$datum['name']}.{$format}";
  }

}
