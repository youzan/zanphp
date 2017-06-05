<?php

namespace Kdt\Iron\Nova\Foundation\Traits;

trait StructSpecManager
{
    /**
     * @var array
     */
    protected $_TSPEC = [];

    /**
     * @var array
     */
    protected $structSpec = [];

    /**
     * @return array
     */
    public function getStructSpec()
    {
        return $this->structSpec;
    }

    /**
     * @return array
     */
    public function toArray(){
        $structSpec = $this->getStructSpec();
        $arr = [];
        foreach($structSpec as $struct){
            $keyName =  $struct['var'];
            $arr[$keyName] = $this->$keyName;
        }
        return $arr;
    }

    public function toDb( array $dbMap,array $filter = []){
        $structMap = $this->toArray();

        $record = [];
        foreach($dbMap as $dbField => $structField){
            if($filter && (in_array($structField,$filter) || in_array($dbField,$filter))){
                continue;
            }
            if(isset($structMap[$structField])){
                $record[$dbField] = $structMap[$structField];
            }
        }
        return $record;
    }

    public function toStruct(array $dbMap,array $data){
        foreach($dbMap as $dbField => $structField){
            if(property_exists($this,$structField) && isset($data[$dbField])){
                $this->$structField = $data[$dbField];
            }
        }
        return $this;
    }

    /**
     * for php-ext:thrift-protocol
     */
    private function staticSpecInjecting()
    {
        $this->_TSPEC = $this->structSpec;
    }
}