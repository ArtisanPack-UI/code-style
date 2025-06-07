<?php
use function array_merge;
use Illuminate\Support\Facades\DB;
use const PHP_EOL;

// This class has multiple coding standard violations
class sampleClass {
    // Missing type declaration
    private $property1 = "This should use single quotes";

    // Missing visibility
    public $property2 = 'This is fine';

    // Trait use statement not at the top of the class
    public function myConstructor() {
        // Non-Yoda conditional
        if ($this->property1 === true) {
            echo "Hello world";
        }
    }

    // Use trait after method (should be at top)
    use SomeTrait;

    // Missing return type
    public function testFunction($param) {
        // Spaces instead of tabs for indentation
        $array = array(
            'key1' => 'value1',
            'key2' => 'value2',
        );

        // Non-aligned variable assignments
        $var1 = 1;
        $longVariableName = 2;
        $v = 3;

        // If statement without space after if
        if($param) {
            return $param;
        }

        // Parameter without type declaration
        foreach ($array as $key => $value) {
            echo $key.$value;
        }
    }

    // Magic method not uppercase
    public function __construct() {
        // Empty
    }
}

// Second class in the same file (violation)
class AnotherClass {
    // Empty
}
