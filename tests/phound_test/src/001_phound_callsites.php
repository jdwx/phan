<?php
class A {
    public const foo = 1;
    public static $foo = 2;
    public $fooz = 3;
    function foo() { echo "foo\n"; }
    static function bar() { echo "bar\n"; }
    function baz() {
        echo "in baz\n";
        echo 'const: ' . self::foo . "\n"; // AST_CLASS_CONST
        echo 'static var: ' . self::$foo . "\n"; // AST_STATIC_PROP
        echo 'instance var: ' . $this->fooz . "\n"; // AST_PROP
        echo 'instance method: ' . $this->foo() . "\n"; // AST_METHOD_CALL
        echo 'static method: ' . self::bar() . "\n"; // AST_STATIC_CALL
    }

    public function getBOrC() {
        if (random_int(0, 10) < 5) {
            return new B;
        } else {
            return new C;
        }
    }

    public function getBOrCClassName() {
        if (random_int(0, 10) < 5) {
            return B::class;
        } else {
            return C::class;
        }
    }

    function callBOrC() {
        $b_or_c = $this->getBOrC();
        $b_or_c->foo();
        echo $b_or_c->bar;

        $class_name = $this->getBOrCClassName();
        $class_name::zoo();
        echo $class_name::$baz;
        echo $class_name::BOO;
    }
}

class B {
    const BOO = 0;
    public $bar = 1;
    public static $baz = 2;
    public $prop_only_public_in_b = 0;
    function foo() {}
    static function zoo() {}
    function methodOnlyDefinedInB() {}
}

class C {
    const BOO = 0;
    public $bar = 1;
    public static $baz = 2;
    private $prop_only_public_in_b = 0;
    function foo() {}
    static function zoo() {}
}

function yo() { echo "yo\n"; }

class TestConstructor {
    function __construct() {}
}
class TestConstructor2 extends TestConstructor {
    function __construct() {
        parent::__construct();
    }
}

$a = new A;
$a->foo(); // AST_METHOD_CALL
A::bar(); // AST_STATIC_CALL
yo(); // AST_CALL
call_user_func([$a, 'foo']); // AST_CALL
call_user_func([$a->getBOrC(), 'foo']);
call_user_func_array(A::class . '::bar', []); // AST_CALL
$a->baz(); // AST_METHOD_CALL

$class_name = 'A';
$method_name = 'bar';
(new $class_name)->$method_name();

$class_name = $a->getBOrCClassName();
(new $class_name)->foo();

$cl = Closure::fromCallable([$a, 'foo']);
$cl();

$cl = Closure::fromCallable([$a->getBOrC(), 'foo']);
$cl();

$b_or_c = $a->getBOrC();
$method_name = (random_int(0, 10) < 5) ? 'methodOnlyDefinedInB' : 'methodDefinedNowhere';
$b_or_c->$method_name();
echo $b_or_c->prop_only_public_in_b;
