<?php
namespace watoki\scrut;

use watoki\factory\Factory;
use watoki\factory\Injector;

abstract class Specification extends \PHPUnit_Framework_TestCase {

    /** @var \watoki\scrut\FixtureProvider */
    protected $fixtureProvider;

    /** @var Factory */
    public $factory;

    /**
     * @var array|callable[] invoked by tearDown
     */
    public $undos = array();

    protected function background() {}

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);

        $factory = new Factory();

        $this->fixtureProvider = new FixtureProvider($this, $factory);
        $factory->setProvider(Fixture::$CLASS, $this->fixtureProvider);

        $injector = new Injector($factory);
        $injector->injectPropertyAnnotations($this, array($this, 'annotationPropertyFilter'));

        $this->setTimeZone();
    }

    protected function setTimeZone() {
        date_default_timezone_set('UTC');
    }

    public function annotationPropertyFilter($annotation) {
        return strpos($annotation, '<-') !== false;
    }

    protected function setUp() {
        $this->factory = new Factory();

        $this->onFixtures(function (Fixture $fixture) {
            $fixture->setUp();
        });
    }

    protected function runTest() {
        $this->background();
        return parent::runTest();
    }

    protected function tearDown() {
        $this->onFixtures(function (Fixture $fixture) {
            $fixture->tearDown();
        });

        foreach ($this->undos as $undo) {
            $undo();
        }
    }

    /**
     * @param callable $do
     */
    private function onFixtures($do) {
        foreach ($this->fixtureProvider->getProvidedFixtures() as $fixture) {
            $do($fixture);
        }
    }

}