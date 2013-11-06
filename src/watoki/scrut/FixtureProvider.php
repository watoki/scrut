<?php
namespace watoki\scrut;

use watoki\factory\providers\DefaultProvider;

class FixtureProvider extends DefaultProvider {

    private $spec;

    function __construct(Specification $spec) {
        parent::__construct($spec->factory, true);
        $this->spec = $spec;
    }

    public function provide($class, array $args = array()) {
        $instance = parent::provide($class, array('spec' => $this->spec));
        $this->spec->factory->setSingleton($class, $instance);
        return $instance;
    }
}