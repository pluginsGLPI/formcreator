<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFilePlugin extends \Robo\Tasks
{
   /**
    * Minify all
    *
    * @return void
    */
   public function minify() {
      $this->minifyCSS()
         ->minifyJS();
   }

   /**
    * Minify CSS stylesheets
    *
    * @return void
    */
   public function minifyCSS() {
      $css_dir = __DIR__ . '/css';
      if (is_dir($css_dir)) {
         foreach (glob("$css_dir/*.css") as $css_file) {
            if (!$this->endsWith($css_file, 'min.css')) {
               $this->taskMinify($css_file)
                  ->to(str_replace('.css', '.min.css', $css_file))
                  ->type('css')
                  ->run();
            }
         }
      }
      return $this;
   }

   /**
    * Minify JavaScript files stylesheets
    *
    * @return void
    */
   public function minifyJS() {
      $js_dir = __DIR__ . '/js';
      if (is_dir($js_dir)) {
         foreach (glob("$js_dir/*.js") as $js_file) {
            if (!$this->endsWith($js_file, 'min.js')) {
               $this->taskMinify($js_file)
                  ->to(str_replace('.js', '.min.js', $js_file))
                  ->type('js')
                  ->run();
            }
         }
      }
      return $this;
   }

   /**
    * Extract translatable strings
    *
    * @return void
    */
   public function localesExtract() {
      $this->_exec('tools/extract_template.sh');
      return $this;
   }

   /**
    * Push locales to transifex
    *
    * @return void
    */
   public function localesPush() {
      $this->_exec('tx push -s');
      return $this;
   }

   /**
    * Pull locales from transifex.
    *
    * @param integer $percent Completeness percentage
    *
    * @return void
    */
   public function localesPull($percent = 70) {
      $this->_exec('tx pull -a --minimum-perc=' .$percent);
      return $this;
   }

   /**
    * Build MO files
    *
    * @return void
    */
   public function localesMo() {
      $this->_exec('./tools/release --compile-mo');
      return $this;
   }

   /**
    * Extract and send locales
    *
    * @return void
    */
   public function localesSend() {
      $this->localesExtract()
           ->localesPush();
      return $this;
   }

   /**
    * Retrieve locales and generate mo files
    *
    * @param integer $percent Completeness percentage
    *
    * @return void
    */
   public function localesGenerate($percent = 70) {
      $this->localesPull($percent)
           ->localesMo();
      return $this;
   }

   /**
    * Checks if a string ends with another string
    *
    * @param string $haystack Full string
    * @param string $needle   Ends string
    *
    * @return boolean
    * @see http://stackoverflow.com/a/834355
    */
   private function endsWith($haystack, $needle) {
      $length = strlen($needle);
      if ($length == 0) {
         return true;
      }

      return (substr($haystack, -$length) === $needle);
   }
}