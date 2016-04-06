<?php

namespace DIPcom\AnnEvents\DI;


use Nette;
use Nette\PhpGenerator as Code;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Annotations\AnnotationRegistry;
use DIPcom\AnnEvents\Guideline;

class AnnEventsExtension extends Nette\DI\CompilerExtension{
    
    public $defaults = array(
        "tempDir" => "",
        "appDir" => ""
    );
    
    private $mapping = array(
        "interface" => "\DIPcom\AnnEvents\Mapping\Annotation",
        "listener"=>"DIPcom\AnnEvents\Mapping\Listener",
        "event"=>"DIPcom\AnnEvents\Mapping\Event"
    );
    
    
    /**
     *
     * @var array 
     */
    private $guideline = array();
    
    
    /**
     *
     * @var AnnotationReader
     */
    private $reader;
    
    
    public function __construct() {
       $this->guideline = new Guideline();
       $this->reader = new AnnotationReader();
    }


    
    public function loadConfiguration() {
        
        $builder = $this->getContainerBuilder();
        $this->defaults['tempDir'] = $builder->parameters['tempDir'];
        $this->defaults['appDir'] = $builder->parameters['appDir'];
        $this->defaults = $this->getConfig($this->defaults);

        $reader = new AnnotationReader();

        foreach($this->getClasses() as $name => $dir){
            $reflClass = new \ReflectionClass($name);
            $c_ann = $reader->getClassAnnotation($reflClass, $this->mapping["interface"]);
            
            if($c_ann){    
                $this->addDefinition($name, $builder);
                $this->addPropertys($reflClass, $c_ann);
                $this->addMethods($reflClass, $c_ann);                
            }
            
            
        }
        $this->guideline->install($builder);
    }
    
    /**
     * 
     * @param \ReflectionClass $class
     * @param Object $c_ann
     */
    public function addPropertys(\ReflectionClass $class, $c_ann){
        if($this->isListener($c_ann)){ 
            foreach($class->getProperties() as $ref_prop){
                $annotation = $this->reader->getPropertyAnnotation($ref_prop, 'DIPcom\AnnEvents\Mapping\On');
                if($annotation){
                    $this->guideline->addListener($ref_prop, $annotation);
                }
            }
        }
    }
    
    
    /**
     * 
     * @param \ReflectionClass $class
     * @param Object $c_ann
     */
    public function addMethods(\ReflectionClass $class, $c_ann){
        if($this->isEvent($c_ann)){ 
            
            foreach($class->getMethods() as $ref_met){
                $annotation = $this->reader->getMethodAnnotation($ref_met, 'DIPcom\AnnEvents\Mapping\Target');
                if($annotation){
                    $this->guideline->addEvent($ref_met, $annotation);
                }
            }
        }
    }
    
    
    
    /**
     * @param Object $class
     * @return boolean
     */
    private function isListener($class){
        return $class instanceof $this->mapping["listener"];
    }
    
    
    /**
     * @param Object $class
     * @return boolean
     */
    private function isEvent($class){
        return $class instanceof $this->mapping["event"];
    }

    /**
     * @return array of class => filename
     */
    public function getClasses(){
        $loader = new Nette\Loaders\RobotLoader;
        $loader->setCacheStorage(new Nette\Caching\Storages\FileStorage($this->defaults['tempDir']));
        $loader->addDirectory($this->defaults["appDir"]);
        $loader->register();
        return $loader->getIndexedClasses();
    }
    
    
    /**
     * 
     * @param string $class_name
     * @param Nette\DI\CompilerExtension $builder
     * @return string
     */
    public function addDefinition($class_name,  Nette\DI\ContainerBuilder $builder){
        $name = str_replace('\\','_',$class_name);
        $builder->addDefinition($this->prefix(str_replace('\\','_',$name)))
            ->setClass($class_name)
            ->setInject(true);
        return $name;
    }
    
    
    
    public function afterCompile(Code\ClassType $class){
        
       
    }



    /**
     * @param \Nette\Configurator $configurator
     */
    public static function register(Nette\Configurator $configurator){
        $configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
                $compiler->addExtension('annEvents', new EventLoaderExtension());
        };
    }

}

