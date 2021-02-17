<?php


use hannespries\SSEngine\Adapter\FSAdapter;
use hannespries\SSEngine\Engine;
use PHPUnit\Framework\TestCase;

class Test extends TestCase{
    private function getEngine(){
        $folder = preg_replace("/\\\\/", '/', __DIR__) . '/../../test_data/';
        if(!is_dir($folder)) {
            mkdir($folder, 777, true);
        }

        $states = [
            'start' => [
                'next' => 'step1',
                'onError' => 'failed',
                'type' => Engine::TYPE_START
            ],
            'step1' => [
                'next' => 'step2',
                'onError' => 'failed',
                'type' => Engine::TYPE_DEFAULT
            ],
            'step2' => [
                'next' => 'stop',
                'onError' => 'failed',
                'type' => Engine::TYPE_DEFAULT
            ],
            'stop' => [
                'type' => Engine::TYPE_END
            ],
            'failed' => [
                'type' => Engine::TYPE_ERROR
            ]

        ];

        $adapter = new FSAdapter($folder);
        return new Engine($adapter, $states);
    }

    public function test_firststep() {
        $engine = $this->getEngine();
        $entity = 'ent' . time();

        $engine->init($entity, 'start', true);
        $engine->startCurrent($entity); //start
        $engine->closeCurrent($entity, false, true);
        $engine->startCurrent($entity); //step1

        $this->assertEquals('step1', $engine->getCurrentState($entity));
    }

    public function test_startable() {
        $engine = $this->getEngine();
        $entity = 'ent' . time();

        $engine->init($entity, 'start', true);
        $engine->startCurrent($entity); //start
        $engine->closeCurrent($entity, false, true);

        $this->assertTrue($engine->startCurrent($entity));
    }

    public function test_notstartable() {
        $engine = $this->getEngine();
        $entity = 'ent' . time();

        $engine->init($entity, 'start', true);
        $engine->startCurrent($entity); //start
        $engine->closeCurrent($entity, false, true);
        $engine->startCurrent($entity);

        $this->assertFalse($engine->startCurrent($entity));
    }

    public function test_failed() {
        $engine = $this->getEngine();
        $entity = 'ent' . time();

        $engine->init($entity, 'start', true);
        $engine->startCurrent($entity); //start
        $engine->closeCurrent($entity, false, true);
        $engine->startCurrent($entity); //step1
        $engine->closeCurrent($entity, true);

        $this->assertEquals('failed', $engine->getCurrentState($entity));
    }
}