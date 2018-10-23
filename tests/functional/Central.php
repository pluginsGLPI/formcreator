<?php

namespace tests\units;

class Central extends CommonFunctionalTtestCase {
   public function testCentralHasTab() {
      $this->takeScreenshot();
      $output = $this->crawler->filter('#page > div > div > ul > li > a[title="Forms"]');
      $this->dump($output->text());
      $this->string($output->text())->isEqualTo("Forms");
   }
}
