<?php
namespace hannespries\SSEngine\Adapter;

use hannespries\SSEngine\AdapterInterface;

class FSAdapter implements AdapterInterface {
    private $folder = '';

    public function __construct($folder)
    {
        $this->folder = $folder;
    }

    private function readFile($entity) {
        if(!is_file($this->folder . $entity . '.json')) {
            return [];
        }
        return json_decode(file_get_contents($this->folder . $entity . '.json'), true);
    }

    private function persistFile($entity, $data){
        file_put_contents($this->folder . $entity . '.json', json_encode($data));
    }

    public function reset($entity)
    {
        $data = $this->readFile($entity);
        foreach ($data as $index => $item) {
            if(!isset($item['stop']) || strlen($item['stop'])){
                $data[$index]['stop'] = date('Y-m-d H:i:s');
            }
        }
        $this->persistFile($entity, $data);
    }

    public function getCurrent($entity)
    {
        $data = $this->readFile($entity);
        return count($data) > 0 ? $data[count($data) -1]['state'] : null;
    }

    public function add($entity, $name, $autoClose = false)
    {
        $data = $this->readFile($entity);
        $data[] = [
            'state' => $name,
            'start' => $autoClose ? date('Y-m-d H:i:s') : null,
            'stop' => $autoClose ? date('Y-m-d H:i:s') : null,
        ];
        $this->persistFile($entity, $data);
    }

    public function isCurrentRunning($entity)
    {
        $data = $this->readFile($entity);
        $item = count($data) > 0 ? $data[count($data)-1] : null;
        return $item !== null && isset($item['start']) && !isset($item['stop']);
    }

    public function markRunning($entity)
    {
        if(!$this->isCurrentRunning($entity)) {
            $data = $this->readFile($entity);
            $data[count($data) -1]['start'] = date('Y-m-d H:i:s');
            $this->persistFile($entity, $data);
        }
    }
}
