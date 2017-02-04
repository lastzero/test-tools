<?php

namespace TestTools\Fixture;

class NonSerializableExceptionContainer
{
    private $className;
    private $message;
    private $code;

    public function __construct(\Exception $e)
    {
        $this->className = get_class($e);
        $this->message = $e->getMessage();
        $this->code = $e->getCode();
    }

    public function recreate()
    {
        $e = \Mockery::mock($this->className);
        $e->makePartial();

        $property = new \ReflectionProperty($this->className, "message");
        $property->setAccessible(true);
        $property->setValue($e, $this->message);
        $property = new \ReflectionProperty($this->className, "code");
        $property->setAccessible(true);
        $property->setValue($e, $this->code);

        return $e;
    }
}
