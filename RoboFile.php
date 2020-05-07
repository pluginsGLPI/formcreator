<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @copyright Copyright © 2011 - 2020 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

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
      $constantName = "PLUGIN_" . strtoupper($this->getPluginName()) . "_VERSION";
      $pattern = "#^define\('$constantName', '([^']*)'\);$#m";
      preg_match($pattern, $setupContent, $matches);
      if (isset($matches[1])) {
         return $matches[1];
      }
      throw new Exception("Could not determine version of the plugin");
   }

   protected function getIsRelease() {
      $currentRev = Git::getCurrentCommitHash();
      $setupContent = Git::getFileFromGit('setup.php', $currentRev);
      $constantName = "PLUGIN_" . strtoupper($this->getPluginName()) . "_IS_OFFICIAL_RELEASE";
      $pattern = "#^define\('$constantName', ([^\)]*)\);$#m";
      preg_match($pattern, $setupContent, $matches);
      if (isset($matches[1])) {
         return $matches[1];
      }
      throw new Exception("Could not determine release status of the plugin");
   }

   protected function getGLPIMinVersion() {
      $setupFile = $this->getProjectPath(). "/setup.php";
      $setupContent = file_get_contents($setupFile);
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

   /**
    * Build an redistribuable archive
    *
    * @param string $release 'release' if the archive is a release
    */
   public function archiveBuild($release = 'release') {
      $release = strtolower($release);
      $version = $this->getVersion();

      if (!SemVer::isSemVer($version)) {
         throw new Exception("$version is not semver compliant. See http://semver.org/");
      }

      if ($release != 'release') {
         if ($this->getIsRelease() === 'true') {
            throw new Exception('The Official release constant must be false');
         }
      } else {
         if ($this->getIsRelease() !== 'true') {
            throw new Exception('The Official release constant must be true');
         }

         if (Git::tagExists($version)) {
            throw new Exception("The tag $version already exists");
         }

         // if (!Git::isTagMatchesCurrentCommit($version)) {
         //    throw new Exception("HEAD is not pointing to the tag of the version to build");
         // }

         $versionTag = $this->getVersionTagFromXML($version);
         if (!is_array($versionTag)) {
            throw new Exception("The version does not exists in the XML file");
         }
      }

      $this->taskGitStack()
         ->stopOnFail()
         ->add('data/font-awesome_.php')
         ->commit('build(form): update font awesome data')
         ->run();

      // update version in package.json
      $this->sourceUpdatePackageJson($version);
      if ($release == 'release') {
         $this->updateChangelog();
      }

      $diff = $this->gitDiff(['package.json']);
      $diff = implode("\n", $diff);
      if ($diff != '') {
         $this->taskGitStack()
            ->stopOnFail()
            ->add('package.json')
            ->commit('docs: bump version in package.json')
            ->run();
      }

      // Update locales
      $this->localesGenerate();
      $this->taskGitStack()
         ->stopOnFail()
         ->add('locales/*')
         ->commit('docs(locales): update translations')
         ->run();

      $this->buildFaData();
      $rev = 'HEAD';
      $pluginName = $this->getPluginName();
      $pluginPath = $this->getProjectPath();
      $archiveWorkdir = "$pluginPath/output/dist/archive_workdir";
      $archiveFile = "$pluginPath/output/dist/glpi-" . $this->getPluginName() . "-$version.tar.bz2";

      if (is_file($archiveFile)) {
         if (!is_writable(($archiveFile))) {
            throw new \RuntimeException('Failed to delete previous build (file is not writable)' . $archiveFile);
         }
         unlink($archiveFile);
      }
      $this->taskDeleteDir($archiveWorkdir)->run();
      mkdir($archiveWorkdir, 0777, true);
      $filesToArchive = implode(' ', $this->getFileToArchive($rev));

      // Extract from the repo all files we want to have in the redistribuable archive
      $this->_exec("git archive --prefix=$pluginName/ $rev $filesToArchive | tar x -C '$archiveWorkdir'");

      // Add extra files to workdir
      $success = copy(__DIR__ . '/data/font-awesome_9.4.php', "$archiveWorkdir/$pluginName/data/font-awesome_9.4.php");
      $success = $success && copy(__DIR__ . '/data/font-awesome_9.5.php', "$archiveWorkdir/$pluginName/data/font-awesome_9.5.php");

      if (!$success) {
         throw new RuntimeException("failed to generate Font Awesome resources");
      }
      // Add composer dependencies
      $this->_exec("composer install --no-dev --working-dir='$archiveWorkdir/$pluginName'");

      // Create the final archive
      $this->taskPack($archiveFile)
         ->addDir("$pluginName", "$archiveWorkdir/$pluginName")
         ->run();
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

   protected function getPluginXMLDescription() {
      $pluginXML = 'plugin.xml';
      if (!is_file($pluginXML) || !is_readable($pluginXML)) {
         throw Exception("plugin.xml file not found");
      }

      $xml = simplexml_load_string(file_get_contents($pluginXML));
      if ($xml === false) {
         throw new Exception("$pluginXML is not valid XML");
      }
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

   /**
    * Update the changelog
    */
   public function updateChangelog() {
       exec("node_modules/.bin/conventional-changelog -p angular -i CHANGELOG.md -s", $output, $retCode);
      if ($retCode > 0) {
         throw new Exception("Failed to update the changelog");
      }

      $diff = $this->gitDiff(['CHANGELOG.md']);
      $diff = implode("\n", $diff);
      if ($diff != '') {
         $this->taskGitStack()
            ->stopOnFail()
            ->add('CHANGELOG.md')
            ->commit('docs(changelog): update changelog')
            ->run();
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

   /**
    * Update headers in source files
    */
   public function codeHeadersUpdate() {
      $toUpdate = $this->getTrackedFiles('HEAD');
      foreach ($toUpdate as $file) {
         $this->replaceSourceHeader($file);
      }
   }

   /**
    * Read the header template from a file
    * @throws Exception
    * @return string
    */
   protected static function getHeaderTemplate() {
      if (empty($this->headerTemplate)) {
         $this->headerTemplate = file_get_contents(__DIR__ . '/tools/HEADER');
         if (empty($this->headerTemplate)) {
            throw new Exception('Header template file not found');
         }
      }

      $copyrightRegex = "#Copyright (\(c\)|©) (\d{4}\s*-\s*)(\d{4}) #iUm";
      $year = date("Y");
      $replacement = 'Copyright © ${2}' . $year . ' ';
      $this->headerTemplate = preg_replace($copyrightRegex, $replacement, $this->headerTemplate);

      return $this->headerTemplate;
   }

   /**
    * Format header template for a file type based on extension
    *
    * @param string $extension
    * @param string $template
    * @return string
    */
   protected function getFormatedHeaderTemplate($extension, $template) {
      switch ($extension) {
         case 'php':
         case 'css':
            $lines = explode("\n", $template);
            foreach ($lines as &$line) {
               $line = rtrim(" * $line");
            }
            return implode("\n", $lines);
            break;

         default:
            return $template;
      }
   }

   /**
    * Update source code header in a source file
    * @param string $filename
    */
   protected function replaceSourceHeader($filename) {
      $filename = __DIR__ . "/$filename";

      // define regex for the file type
      $ext = pathinfo($filename, PATHINFO_EXTENSION);
      switch ($ext) {
         case 'php':
            $prefix              = "\<\?php\\n/\*(\*)?\\n";
            $replacementPrefix   = "<?php\n/**\n";
            $suffix              = "\\n( )?\*/";
            $replacementSuffix   = "\n */";
            break;

         case 'css':
            $prefix              = "/\*(\*)?\\n";
            $replacementPrefix   = "/**\n";
            $suffix              = "\\n( )?\*/";
            $replacementSuffix   = "\n */";
            break;
         default:
            // Unhandled file format
            return;
      }

      // format header template for the file type
      $header = trim($this->getHeaderTemplate());
      $formatedHeader = $replacementPrefix . $this->getFormatedHeaderTemplate($ext, $header) . $replacementSuffix;

      // get the content of the file to update
      $source = file_get_contents($filename);

      // update authors in formated template
      $headerMatch = [];
      $originalAuthors = [];
      $authorsRegex = "#^.*(\@author .*)$#Um";
      preg_match('#^' . $prefix . '(.*)' . $suffix . '#Us', $source, $headerMatch);
      if (isset($headerMatch[0])) {
         $originalHeader = $headerMatch[0];
         preg_match_all($authorsRegex, $originalHeader, $originalAuthors);
         if (!is_array($originalAuthors)) {
            $originalAuthors = [$originalAuthors];
         }
         if (isset($originalAuthors[1])) {
            $originalAuthors[1] = array_unique($originalAuthors[1]);
            $originalAuthors = $this->getFormatedHeaderTemplate($ext, implode("\n", $originalAuthors[1]));
            $countOfAuthors = preg_match_all($authorsRegex, $formatedHeader);
            if ($countOfAuthors !== false) {
               // Empty all author lines except the last one
               $formatedHeader = preg_replace($authorsRegex, '', $formatedHeader, $countOfAuthors - 1);
               // remove the lines previously reduced to zero
               $lines = explode("\n", $formatedHeader);
               $formatedHeader = [];
               foreach ($lines as $line) {
                  if ($line !== '') {
                     $formatedHeader[] = $line;
                  };
               }
               $formatedHeader = implode("\n", $formatedHeader);
               $formatedHeader = preg_replace($authorsRegex, $originalAuthors, $formatedHeader, 1);
            }
         }
      }

      // replace the header if it exists
      $source = preg_replace('#^' . $prefix . '(.*)' . $suffix . '#Us', $formatedHeader, $source, 1);
      if (empty($source)) {
         throw new Exception("An error occurred while processing $filename");
      }

      file_put_contents($filename, $source);
   }

   /**
    * @param array $files files to commit
    * @param string $version1
    * @param string $version2
    */
   protected function gitDiff(array $files, $version1 = '', $version2 = '') {
      if (count($files) < 1) {
         $arg = '-u';
      } else {
         $arg = '"' . implode('" "', $files) . '"';
      }

      if ($version1 == '' && $version2 == '') {
         $fromTo = '';
      } else {
         $fromTo = "$version1..$version2";
      }

      exec("git diff $fromTo -- $arg", $output, $retCode);
      if ($retCode > 0) {
         throw new Exception("Failed to diff $fromTo");
      }

      return $output;
   }

   protected function gitLog($a, $b = '', $options = []) {
      $options = implode(' ', $options);
      exec("git log $a..$b", $output, $retCode);
      if ($retCode > 0) {
         throw new Exception("Failed to log $a..$b");
      }
      return $output;
   }

   public function buildFaData() {
      $versions = [
         [
            [
               'fa' => 'https://raw.githubusercontent.com/glpi-project/glpi/9.4.2/lib/font-awesome/webfonts/fa-regular-400.svg',
               'fab' => 'https://raw.githubusercontent.com/glpi-project/glpi/9.4.2/lib/font-awesome/webfonts/fa-brands-400.svg',
               'fas' => 'https://raw.githubusercontent.com/glpi-project/glpi/9.4.2/lib/font-awesome/webfonts/fa-solid-900.svg',
            ], // GLPI 9.4
            'font-awesome_9.4.php',
         ],
         /* In GLPI 9.5 Font Awesome is a node dependency
         [
            [
               'fa' => 'https://raw.githubusercontent.com/glpi-project/glpi/9.4.2/lib/font-awesome/webfonts/fa-regular-400.svg',
               'fab' => 'https://raw.githubusercontent.com/glpi-project/glpi/9.4.2/lib/font-awesome/webfonts/fa-brands-400.svg',
               'fas' => 'https://raw.githubusercontent.com/glpi-project/glpi/9.4.2/lib/font-awesome/webfonts/fa-solid-900.svg',
            ],
            'font-awesome_9.5.php',
         ],
         */
      ];

      foreach ($versions as $version) {
         $fanames = [];
         $searchRegex = '#glyph-name=\"([^\"]*)\"#i';
         foreach ($version[0] as $key => $svgSource) {
            $svg = file_get_contents($svgSource);
            $matches = null;
            preg_match_all($searchRegex, $svg, $matches);
            foreach ($matches[1] as $name) {
               $fanames["$key fa-$name"] = $name;
            }

            $list = '<?php' . PHP_EOL . 'return ' . var_export($fanames, true) . ';';
            $outFile = __DIR__ . '/data/' . $version[1];
            $size = file_put_contents($outFile, $list);
            if ($size != strlen($list)) {
               throw new RuntimeException('Failed to build the list of font awesome pictograms');
            }
         }
      }

      //For GLPI 9.5 and later
      $versions = [
         'font-awesome_9.5.php' => [
            'package-lock.json' => 'https://raw.githubusercontent.com/glpi-project/glpi/9.5/bugfixes/package-lock.json',
         ],
      ];

      $faRepo = 'https://raw.githubusercontent.com/FortAwesome/Font-Awesome';
      $searchRegex = '#glyph-name=\"([^\"]*)\"#i';
      foreach ($versions as $outFile => $version) {
         // Determine all Font Awesome files sources
         $outFile = __DIR__ . '/data/' . $outFile;
         $json = $version['package-lock.json'];
         $json = json_decode(file_get_contents($json), true);
         $faVersion = $json['dependencies']['@fortawesome/fontawesome-free']['version'];
         $faSvgFiles = [
            'fa' => "$faRepo/$faVersion/webfonts/fa-regular-400.svg",
            'fab' => "$faRepo/$faVersion/webfonts/fa-brands-400.svg",
            'fas' => "$faRepo/$faVersion/webfonts/fa-solid-900.svg",
         ];

         $fanames = [];
         foreach ($faSvgFiles as $key => $svgSource) {
            $svg = file_get_contents($svgSource);
            $matches = null;
            preg_match_all($searchRegex, $svg, $matches);
            foreach ($matches[1] as $name) {
               $fanames["$key fa-$name"] = $name;
            }
            $list = '<?php' . PHP_EOL . 'return ' . var_export($fanames, true) . ';';
            $size = file_put_contents($outFile, $list);
            if ($size != strlen($list)) {
               throw new RuntimeException('Failed to build the list of font awesome pictograms');
            }
         }
      }
   }

   public function buildLog($a, $b = 'HEAD') {
      $log = ConventionalChangelog::buildLog($a, $b);
      echo implode(PHP_EOL, $log);
   }
}

class Git
{

   public static function getCurrentCommitHash() {
      exec('git rev-parse HEAD', $output, $retCode);
      if ($retCode != '0') {
         throw new Exception("failed to get curent commit hash");
      }
      return $output[0];
   }

   public static function isTagMatchesCurrentCommit($tag) {
      $commitHash = self::getCurrentCommitHash();
      exec("git tag -l --contains $commitHash", $output, $retCode);
      if (isset($output[0]) && $output[0] == $tag) {
         return  true;
      }

      return false;
   }

   public static function getTagOfCommit($hash) {
      exec("git tag -l --contains $hash", $output, $retCode);
      if (isset($output[0])) {
         return $output[0];
      }

      return false;
   }

   public static function getTagDate($tag) {
      exec("git log -1 --format=%ai $tag", $output, $retCode);
      if ($retCode != '0') {
         throw new Exception("failed to get date of a tag");
      }
      if (isset($output[0])) {
         return new DateTime($output[0]);
      }

      return false;
   }

   /**
    * get highest sember tag from list
    * @param array $tags list of tags
    */
    public static function getLastTag($tags, $prefix = '') {
      // Remove prefix from all tags
      if ($prefix !== '') {
         $newTags = [];
         foreach ($tags as $tag) {
            if (substr($tag, 0, strlen($prefix)) == $prefix) {
               $tag = substr($tag, strlen($prefix));
            }
            $newTags[] = $tag;
         }
         $tags = $newTags;
      }

      // get tags and keey only sember compatible ones
      $tags = array_filter($tags, [SemVer::class, 'isSemVer']);

      // Sort tags
      usort($tags, function ($a, $b) {
         return version_compare($a, $b);
      });

      return end($tags);
   }

   public static function createCommitList($commits) {
      // Biuld list of commits, latest is oldest
      $commitObjects = [];
      foreach ($commits as $commit)  {
         $line = explode(' ', $commit, 2);
         $commitObject = new StdClass();
         $commitObject->hash = $line[0];
         $commitObject->subject = $line[1];
         $commitObjects[] = $commitObject;
         $commitObject->body = self::getCommitBody($commitObject->hash);
      }

      return $commitObjects;
   }

   public static function getLog($a, $b = 'HEAD') {
      exec("git log --oneline $a..$b", $output, $retCode);
      if ($retCode != '0') {
         // An error occured
         throw new Exception("Unable to get log from the repository");
      }

      return $output;
   }

   public static function getRemotes() {
      exec("git remote -v", $output, $retCode);
      if ($retCode != '0') {
         // An error occured
         throw new Exception("Unable to get remotes of the repository");
      }
      $remotes = [];
      foreach ($output as $line) {
         $line = explode("\t", $line);
         $line[1] = explode(' ', $line[1]);
         $line[1] = $line[1][0];
         // 0 = name
         // 1 = URL
         // 2 = (fetch) or (push)
         if (strpos($line[1], 'git@') === 0) {
            // remote type is SSH
            $split = explode('@', $line[1]);
            //$user = $split[0];
            $split = explode(':', $split[1]);
            $url = 'https://' . $split[0] . '/' . $split[1];
         }
         $remotes[$line[0]] = $url;
      }

      return $remotes;
   }


   public static function getAllTags() {
      exec("git tag -l", $output, $retCode);
      if ($retCode != '0') {
         // An error occured
         throw new Exception("Unable to get tags from the repository");
      }
      return $output;
   }

   public static function tagExists($version) {
      $tags = self::getAllTags();
      return in_array($version, $tags);
   }

      /**
    * Get a file from git tree
    * @param string $path
    * @param string $rev a commit hash, a tag or a branch
    * @throws Exception
    * @return string content of the file
    */
    public static function getFileFromGit($path, $rev) {
      $output = shell_exec("git show $rev:$path");
      if ($output === null) {
         throw new Exception ("coult not get file from git: $rev:$path");
      }
      return $output;
   }

   public static function getCommitBody($hash) {
      $output = shell_exec("git log $hash --max-count=1 --pretty=format:\"%b\"");
      return $output;
   }

   public static function getCurrentBranch() {
      $output = shell_exec("git rev-parse --abbrev-ref HEAD");
      if ($output === null) {
         throw new Exception ("could not get current branch");
      }
      return $output;

   }
}

class ConventionalChangelog
{
   /**
    * @param array $commmits commits to filter
    */
   public static function filterCommits($commits) {
      $types = [
         'build', 'chore', 'ci', 'docs', 'fix', 'feat', 'perf', 'refactor', 'style', 'test'
      ];
      $types = implode('|', $types);
      $scope = "(\((?P<scope>[^\)]*)\))?";
      $subject = ".*";
      $filter = "/^(?P<type>$types)$scope:(?P<subject>$subject)$/";
      $filtered = [];
      $matches = null;
      foreach ($commits as $commit) {
         if (preg_match($filter, $commit->subject, $matches) === 1) {
            $commit->type = $matches['type'];
            $commit->scope = '';
            if (isset($matches['scope']) && strlen($matches['scope']) > 0) {
               $commit->scope = $matches['scope'];
            }
            $commit->subject = trim($matches['subject']);
            $filtered[] = $commit;
         }
      }

      return $filtered;
   }

   public static function compareCommits($a, $b) {
      // comapre scopes
      if ($a->scope < $b->scope) {
         return -1;
      } else if($a->scope > $b->scope) {
         return 1;
      }

      // then compare subject
      if ($a->subject < $b->subject) {
         return -1;
      } else if ($a->subject > $b->subject) {
         return 1;
      }

      return 0;
   }


   public static function buildLog($a, $b = 'HEAD') {
      if (!Git::tagExists($b)) {
         // $b is not a tag, try to find a matching one
         if (Git::getTagOfCommit($b) === false) {
            // throw new Exception("current HEAD does not match a tag");
         }
      }

      // get All tags between $a and $b
      $tags = Git::getAllTags();

      // Remove non semver compliant versions
      $tags = array_filter($tags, function ($tag) {
         return Semver::isSemVer($tag);
      });

      $startVersion = $a;
      $endVersion = $b;
      $prefix = 'v';
      if (substr($startVersion, 0, strlen($prefix)) == $prefix) {
         $startVersion = substr($startVersion, strlen($prefix));
      }
      if (substr($endVersion, 0, strlen($prefix)) == $prefix) {
         $endVersion = substr($endVersion, strlen($prefix));
      }

      $tags = array_filter($tags, function ($version) use ($startVersion, $endVersion) {
         $prefix = 'v';
         if (substr($version, 0, strlen($prefix)) == $prefix) {
            $version = substr($version, strlen($prefix));
         }
         if (version_compare($version, $startVersion) < 0) {
            return false;
         }
         if ($endVersion !== 'HEAD' && version_compare($version, $endVersion) > 0) {
            return false;
         }
         return true;
      });

      // sort tags older DESCENDING
      usort($tags, function ($a, $b) {
         return version_compare($b, $a);
      });

      $log = [];
      if ($b === 'HEAD') {
         array_unshift($tags, $b);
      }
      $startRef = array_shift($tags);
      if ($startRef === null) {
         throw new RuntimeException("$a not found");
      }
      while ($endRef = array_shift($tags)) {
         $log = array_merge($log, self::buildLogOneBump($startRef, $endRef));
         $startRef = $endRef;
      }

      return $log;
   }

   public static function buildLogOneBump($a, $b) {
      $tag = $a;
      if (!Git::tagExists($b)) {
         // $b is not a tag, try to find a matching one
         if ($tag = Git::getTagOfCommit($b) === false) {
            $tag = 'Unreleased';
         }
      }

      // get remote
      $remotes = Git::getRemotes();
      $remote = $remotes['origin'];

      // Get all commits from A to B
      $commits = Git::createCommitList(Git::getLog($b, $a));

      // Remove non conventional commits
      $commits = self::filterCommits($commits);

      // Keep only useful commits for changelog
      $fixes = array_filter($commits, function ($commit) {
         if (in_array($commit->type, ['fix'])) {
            return true;
         }
         return false;
      });
      $feats = array_filter($commits, function ($commit) {
         if (in_array($commit->type, ['feat'])) {
            return true;
         }
         return false;
      });

      // Sort commits
      usort($fixes, [self::class, 'compareCommits']);
      usort($feats, [self::class, 'compareCommits']);

      // generate markdown log
      $log = [];

      $tagDate = (new DateTime())->format('Y-m-d');
      $compare = "$remote/compare/$b..";
      if ($tag !== 'Unreleased') {
         $tagDate = Git::getTagDate($tag)->format('Y-m-d');
         $compare .= $tag;
      } else {
         $compare .= Git::getCurrentBranch();
      }
      $log[] = '<a name="' . $tag . '"></a>';
      $log[] = '## [' . $tag . '](' . $compare . ') (' . $tagDate . ')';

      if (count($fixes) > 0) {
         $log[] = '';
         $log[] = '';
         $log[] = '### Bug Fixes';
         $log[] = '';
         foreach ($fixes as $commit) {
            $log[]  = self::buildLogLine($commit, $remote);
         }
      }

      if (count($feats) > 0) {
         $log[] = '';
         $log[] = '';
         $log[] = '### Features';
         $log[] = '';
         foreach ($feats as $commit) {
            $log[]  = self::buildLogLine($commit, $remote);
         }
      }

      $log[] = '';
      $log[] = '';
      $log[] = '';

      return $log;
   }

   public static function buildLogLine($commit, $remote) {
      $line = '* ';
      $scope = $commit->scope;
      if ($scope !== '') {
         $scope = "**$scope:**";
         $line .= $scope;
      }
      $hash = $commit->hash;
      $line .= " $commit->subject"
      . " ([$hash]($remote/commit/$hash))";

      // Search for closed issues
      $body = explode(PHP_EOL, $commit->body);
      $pattern = '/^((close|closes|fix|fixed) #(?P<id>\\d+)(,\s+)?)/i';
      $commit->close = [];
      foreach ($body as $bodyLine) {
         $matches = null;
         if (preg_match($pattern, $bodyLine, $matches)) {
            if (!is_array($matches['id'])) {
               $matches['id'] = [$matches['id']];
            }
            $commit->close = $matches['id'];
         }
      }
      if (count($commit->close) > 0) {
         foreach ($commit->close as &$issue) {
            $issue = "[#$issue]($remote/issues/$issue)";
         }
         $line .= ', closes ' . implode(', ', $commit->close);

      }

      return $line;
   }
}

class SemVer
{
      /**
    * Check the version is made of numbers separated by dots
    *
    * Returns true if the version is well formed, false otherwise
    *
    * @param string $version
    * @return boolean
    */
    public static function isSemVer($version) {
      $semverPattern = '#\bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z\-]+(?:\.[\da-z\-]+)*)?(?:\+[\da-z\-]+(?:\.[\da-z\-]+)*)?\b#i';
      if (preg_match($semverPattern, $version) !== 1) {
         return false;
      }

      return true;
   }
}