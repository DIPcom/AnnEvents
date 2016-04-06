<?php


namespace DIPcom\AnnEvents\Mapping;

/**
* @Annotation
* @Target({"PROPERTY","ANNOTATION"})
*/
final class On implements Annotation{
    /**
     *
     * @var string
     */
    public $name;
}
