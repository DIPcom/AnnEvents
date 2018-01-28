<?php


namespace DIPcom\AnnEvents\Mapping;

/**
* @Annotation
* @Target({"METHOD","ANNOTATION"})
*/
final class Target implements Annotation{
    
    /**
     *
     * @var string
     */
    public $listener;
    
    /**
     *
     * @var string
     */
    public $referenced;
    
    
    

    
}
