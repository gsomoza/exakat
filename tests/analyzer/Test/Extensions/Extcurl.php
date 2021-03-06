<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Extensions_Extcurl extends Analyzer {
    /* 3 methods */

    public function testExtensions_Extcurl01()  { $this->generic_test('Extensions_Extcurl.01'); }
    public function testExtensions_Extcurl02()  { $this->generic_test('Extensions_Extcurl.02'); }
    public function testExtensions_Extcurl03()  { $this->generic_test('Extensions_Extcurl.03'); }
}
?>