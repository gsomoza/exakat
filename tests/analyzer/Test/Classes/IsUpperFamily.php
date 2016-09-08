<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Classes_IsUpperFamily extends Analyzer {
    /* 6 methods */

    public function testClasses_IsUpperFamily01()  { $this->generic_test('Classes/IsUpperFamily.01'); }
    public function testClasses_IsUpperFamily02()  { $this->generic_test('Classes/IsUpperFamily.02'); }
    public function testClasses_IsUpperFamily03()  { $this->generic_test('Classes/IsUpperFamily.03'); }
    public function testClasses_IsUpperFamily04()  { $this->generic_test('Classes/IsUpperFamily.04'); }
    public function testClasses_IsUpperFamily05()  { $this->generic_test('Classes/IsUpperFamily.05'); }
    public function testClasses_IsUpperFamily06()  { $this->generic_test('Classes/IsUpperFamily.06'); }
}
?>