<?php
namespace hannespries\SSEngine;

interface AdapterInterface{
    public function reset($entity);
    public function getCurrent($entity);
    public function add($entity, $name, $autoClose = false);
    public function isCurrentRunning($entity);
    public function markRunning($entity);
}