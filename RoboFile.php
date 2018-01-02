<?php
use Robo\Task\Docker\Commit;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
require_once 'RoboFilePlugin.php';
class RoboFile extends RoboFilePlugin
{
   protected static $banned = [
         'dist',
         'vendor',
         '.git',
         '.gitignore',
         '.tx',
         '.settings',
         '.project',
         '.buildpath',
         'tools',
         'tests',
         'screenshot*.png',
         'RoboFile*.php',
         'plugin.xml',
         'phpunit.xml.*',
         '.travis.yml',
         'save.sql',
   ];

   protected function getProjectPath() {
      return __DIR__;
   }

   protected function getPluginName() {
      return basename($this->getProjectPath());
   }

   protected function getVersion() {
      $setupFile = $this->getProjectPath(). "/setup.php";
      $setupContent = file_get_contents($setupFile);
      $pluginName = $this->getPluginName();
      $constantName = "PLUGIN_" . strtoupper($this->getPluginName()) . "_VERSION";
      $pattern = "#^define\('$constantName', '([^']*)'\);$#m";
      preg_match($pattern, $setupContent, $matches);
      if (isset($matches[1])) {
         return $matches[1];
      }
      throw new Exception("Could not determine version of the plugin");
   }

   protected function getGLPIMinVersion() {
      $setupFile = $this->getProjectPath(). "/setup.php";
      $setupContent = file_get_contents($setupFile);
      $pluginName = $this->getPluginName();
      $constantName = "PLUGIN_" . strtoupper($this->getPluginName()) . "_GLPI_MIN_VERSION";
      $pattern = "#^define\('$constantName', '([^']*)'\);$#m";
      preg_match($pattern, $setupContent, $matches);
      if (isset($matches[1])) {
         return $matches[1];
      }

      throw new Exception("Could not determine version of the plugin");
   }

   /**
    * Override to change the banned list
    * @return array
    */
   protected function getBannedFiles() {
      return static::$banned;
   }

   //Own plugin's robo stuff
   public function archiveBuild() {
      $version = $this->getVersion();

      if (!$this->isSemVer($version)) {
         throw new Exception("$version is not semver compliant. See http://semver.org/");
      }

      if ($this->tagExists($version)) {
         throw new Exception("The tag $version already exists");
      }

      //if (!$this->isTagMatchesCurrentCommit($version)) {
         //throw new Exception("HEAD is not pointing to the tag of the version to build");
      //}

      $versionTag = $this->getVersionTagFromXML($version);
      if (!is_array($versionTag)) {
         throw new Exception("The version does not exists in the XML file");
      }

      // update version in package.json
      $this->sourceUpdatePackageJson($version);
      $this->sourceUpdateComposerJson($version);

      $this->updateChangelog();

      $this->gitCommit(['package.json', 'composer.json'], "docs: bump version in JSON files");
      $this->gitCommit(['CHANGELOG.md'], "docs(changelog): update changelog");

      $pluginName = $this->getPluginName();
      $pluginPath = $this->getProjectPath();
      $targetFile = $pluginPath. "/dist/glpi-" . $this->getPluginName() . "-$version.tar.bz2";
      $toArchive = implode(' ', $this->getFileToArchive("HEAD"));
      @mkdir($pluginPath. "/dist");
      $this->_exec("git archive --prefix=$pluginName/ HEAD $toArchive | bzip2 > $targetFile");
   }

   protected function getTrackedFiles($version) {
      $output = [];
      exec("git ls-tree -r '$version' --name-only", $output, $retCode);
      if ($retCode != '0') {
         throw new Exception("Unable to get tracked files");
      }
      return $output;
   }

   protected function getFileToArchive($version) {
      $filesToArchive = $this->getTrackedFiles($version);

      // prepare banned items for regex
      $patterns = [];
      foreach ($this->getBannedFiles() as $bannedItem) {
         $pattern = "#" . preg_quote("$bannedItem", "#") . "#";
         $pattern = str_replace("\\?", ".", $pattern);
         $pattern = str_replace("\\*", ".*", $pattern);
         $patterns[] = $pattern;
      }

      // remove banned files from the list
      foreach ($patterns as $pattern) {
         $filteredFiles = [];
         foreach ($filesToArchive as $file) {
            if (preg_match($pattern, $file) == 0) {
               //Include the tracked file
               $filteredFiles[] = $file;
            }
         }

         // Repeat filtering from result with next banned files pattern
         $filesToArchive = $filteredFiles;
      }

      return $filesToArchive;
   }

   protected function getAllTags() {
      exec("git tag -l", $output, $retCode);
      if ($retCode != '0') {
         // An error occured
         throw new Exception("Unable to get tags from the repository");
      }
      return $output;
   }

   protected function tagExists($version) {
      $tags = $this->getAllTags();
      return in_array($version, $tags);
   }

   /**
    * Check the version is made of numbers separated by dots
    *
    * Returns true if the version is well formed, false otherwise
    *
    * @param string $version
    * @return boolean
    */
   protected function isSemVer($version) {
      $semverPattern = '#\bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z\-]+(?:\.[\da-z\-]+)*)?(?:\+[\da-z\-]+(?:\.[\da-z\-]+)*)?\b#i';
      if (preg_match($semverPattern, $version) !== 1) {
         return false;
      }

      return true;
   }

   protected function getPluginXMLDescription() {
      $pluginXML = 'plugin.xml';
      if (!is_file($pluginXML) || !is_readable($pluginXML)) {
         throw Exception("plugin.xml file not found");
      }

      $xml = simplexml_load_string(file_get_contents($pluginXML));
      $json = json_encode($xml);
      return json_decode($json, true);
   }

   protected function getVersionTagFromXML($versionToSearch) {
      $xml = $this->getPluginXMLDescription();
      foreach ($xml['versions']['version'] as $version) {
         if ($version['num'] == $versionToSearch) {
            // version found
            return $version;
         }
      }

      return null;
   }

   protected function getCurrentCommitHash() {
      exec('git rev-parse HEAD', $output, $retCode);
      if ($retCode != '0') {
         throw new Exception("failed to get curent commit hash");
      }
      return $output[0];
   }

   protected function isTagMatchesCurrentCommit($tag) {
      $commitHash = $this->getCurrentCommitHash();
      exec("git tag -l --contains $commitHash", $output, $retCode);
      if (isset($output[0]) && $output[0] == $tag) {
         return  true;
      }

      return false;
   }

   /**
    * @param array $files files to commit
    * @param string $commitMessage commit message
    */
   protected function gitCommit(array $files, $commitMessage) {
      if (count($files) < 1) {
         $arg = '-u';
      } else {
         $arg = '"' . implode('" "', $files) . '"';
      }
       exec("git add $arg", $output, $retCode);
      if ($retCode > 0) {
         throw new Exception("Failed to add files for $commitMessage");
      }

       exec("git commit -m \"$commitMessage\"", $output, $retCode);
      if ($retCode > 0) {
         throw new Exception("Failed to commit $commitMessage");
      }

       return true;
   }

   /**
    */
   protected function updateChangelog() {
       exec("node_modules/.bin/conventional-changelog -p angular -i CHANGELOG.md -s", $output, $retCode);
      if ($retCode > 0) {
         throw new Exception("Failed to update the changelog");
      }

       return true;
   }

   public function localesExtract() {
      $potfile = strtolower("glpi.pot");
      $phpSources = "*.php ajax/*.php front/*.php inc/*.php inc/fields/*.php install/*.php js/*.php";
      // extract locales from source code
      $command = "xgettext $phpSources -o locales/$potfile -L PHP --add-comments=TRANS --from-code=UTF-8 --force-po";
      $command.= " --keyword=_n:1,2,4t --keyword=__s:1,2t --keyword=__:1,2t --keyword=_e:1,2t --keyword=_x:1c,2,3t --keyword=_ex:1c,2,3t";
      $command.= " --keyword=_sx:1c,2,3t --keyword=_nx:1c,2,3,5t";
      $this->_exec($command);
      return $this;
   }

   /**
    * Build MO files
    *
    * @return void
    */
   public function localesMo() {
      $localesPath = $this->getProjectPath() . '/locales';
      if ($handle = opendir($localesPath)) {
         while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
               $poFile = "$localesPath/$file";
               if (pathinfo($poFile, PATHINFO_EXTENSION) == 'po') {
                  $moFile = str_replace('.po', '.mo', $poFile);
                  $command = "msgfmt $poFile -o $moFile";
                  $this->_exec($command);
               }
            }
         }
         closedir($handle);
      }
      return $this;
   }

   /**
    *
    * @param string $filename
    * @param string $version
    */
   protected function updateJsonFile($filename, $version) {
       // get Package JSON
       $filename = __DIR__ . "/$filename";
       $jsonContent = file_get_contents($filename);
       $jsonContent = json_decode($jsonContent, true);

       // update version
      if (empty($version)) {
         echo "Version not found in setup.php\n";
         return;
      }
       $jsonContent['version'] = $version;
       file_put_contents($filename, json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
   }

   /**
    * @param $version
    */
   protected function sourceUpdatePackageJson($version) {
       $this->updateJsonFile('package.json', $version);
   }

   /**
    * @param string $version
    */
   protected function sourceUpdateComposerJson($version) {
       $this->updateJsonFile('composer.json', $version);
   }
}
