<?php
namespace hannespries\SSEngine;

class Engine{
    const TYPE_START = 0;
    const TYPE_DEFAULT = 1;
    const TYPE_END = 2;
    const TYPE_ERROR = 3;

    /**
     * @var AdapterInterface
     */
    private $adapter = null;
    private $states = [];

    public function __construct(AdapterInterface $adapter, $states = [])
    {
        $this->adapter = $adapter;
        $this->states = $states;
    }

    public function addState($name, $next, $onError, $type = 1) {
        $this->states[$name] = [
            'next' => $next,
            'onError' => $onError,
            'type' => $type
        ];
    }

    public function getCurrentState($entity) {
        return $this->adapter->getCurrent($entity);
    }

    public function isPossibleToStart($entity, $name) {
        $current = $this->getCurrentState($entity);
        return $current === $name && $this->adapter->isCurrentRunning($entity);
    }

    public function next($entity) {
        $result = false;

        $current = $this->getCurrentState($entity);
        if(!$this->adapter->isCurrentRunning($entity) && !in_array($this->states[$current]['type'], [self::TYPE_ERROR, self::TYPE_END])) {
            $this->adapter->add($entity, $this->states[$current]['next']);
            $result = true;
        }

        return $result;
    }

    public function startCurrent($entity) {
        $result = false;

        $current = $this->getCurrentState($entity);

        if(!$this->adapter->isCurrentRunning($entity) && !in_array($this->states[$current]['type'], [self::TYPE_ERROR, self::TYPE_END])) {
            $this->adapter->markRunning($entity);
            $result = true;
        }

        return $result;
    }

    public function closeCurrent($entity, $asError = false, $autoNext = false) {
        $result = false;

        $current = $this->getCurrentState($entity);

        if($this->adapter->isCurrentRunning($entity) && !in_array($this->states[$current]['type'], [self::TYPE_ERROR, self::TYPE_END])) {
            $this->adapter->reset($entity);

            if($asError) {
                $this->adapter->add($entity, $this->states[$current]['onError'], true);
            }
            $result = true;
            if($autoNext) {
                $result = $this->next($entity);
            }
        }
        return $result;
    }

    public function init($entity, $firstStepOfLane, $reset = false) {
        $result = false;

        if($reset) {
            $this->adapter->reset($entity);
        }

        $current = $this->getCurrentState($entity);
        if($current === null || in_array($this->states[$current]['type'], [self::TYPE_ERROR, self::TYPE_END])) {
            if($this->states[$firstStepOfLane]['type'] === self::TYPE_START) {
                $this->adapter->add($entity, $firstStepOfLane);
                $result = true;
            }
        }

        return $result;
    }
}