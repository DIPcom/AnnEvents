<?php

namespace DIPcom\AnnEvents;

use Nette;
use DIPcom\AnnEvents\Mapping\On;
use DIPcom\AnnEvents\Mapping\Target;

class Guideline extends Nette\Object{
    
    
    
    private $listener = array();
    
    
    
    public function addEvent(\ReflectionMethod $method, Target $annotations){
        if(!$this->issetListener($annotations)){
            throw new \Exception('Annotation @Target error. '.$annotations->listener.'::'.$annotations->referenced." listener doesn't exist in ".$method->class.'::'.$method->name.'()');
        }
        $call = array(
            "class" => $method->class,
            "method" => $method->name
        );
        $this->listener[str_replace('\\','_',$annotations->listener)][$annotations->referenced]['call'][] = $call;
    }
    
    
    
    public function addListener(\ReflectionProperty $property, On $annotation){
        $class_name = str_replace('\\','_',$property->class);

        if(!isset($this->listener[$class_name])){
            $this->listener[$class_name] = array();
        }
        $this->listener[$class_name][$property->name] = array(
                'name' => $annotation->name,
                'call' => array()
        );
        
    }
    
    
    
    /**
     * 
     * @param Target $target
     * @return boolean
     */
    private function issetListener(Target $target){
        return isset($this->listener[str_replace('\\','_',$target->listener)][$target->referenced]);
    }
    
    
    
    public function install(Nette\DI\ContainerBuilder $builder){

        foreach($this->listener as $class_name => $on){
            
            $definition = $builder->getDefinition("annevents.".$class_name);

            foreach($on as $on_name => $data){
                foreach($data['call'] as $add){
                    $definition->addSetup(
                            '$service->'.$on_name.'[]=?->'.$add['method'],
                            array($builder->getDefinition('annevents.'.str_replace('\\','_',$add['class'])))
                        );
                }
            }
        }
        
    }
    
}
