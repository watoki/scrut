<?php
namespace watoki\scrut;

use rtens\mockster\ClassResolver;
use watoki\factory\Factory;

abstract class TestCase extends \PHPUnit_Framework_TestCase {

    /** @var Factory */
    protected $factory;

    public $undos = array();

    protected function background() {}

    protected function setUp() {
        $this->factory = new Factory();
        $this->loadDependencies();
        $this->background();
    }

    protected function tearDown() {
        foreach ($this->undos as $undo) {
            $undo();
        }
    }

    protected function loadDependencies() {
        $refl = new \ReflectionClass($this);
        $resolver = new ClassResolver($refl);

        $matches = array();
        preg_match_all('/@property (\S+) (\S+)/', $refl->getDocComment(), $matches);

        foreach ($matches[0] as $i => $match) {
            $className = $matches[1][$i];
            $property = $matches[2][$i];

            $class = $resolver->resolve($className);

            if (!$class) {
                $me = get_class($this);
                throw new \Exception("Error while loading dependency [$property] of [$me]: Could not find class [$className].");
            }

            $this->$property = $this->factory->getInstance($class, array('test' => $this));

        }
    }

    public function runAllTests() {
        $me = get_class($this);
        $result = new \PHPUnit_Framework_TestResult();

        foreach (get_class_methods($this) as $method) {
            if (substr($method, 0, 4) == 'test') {
                /** @var TestCase $test */
                $test = new $me($method);

                $test->run($result);
            }
        }

        return $result;
    }

}