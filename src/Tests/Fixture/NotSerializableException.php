<?php

namespace TestTools\Tests\Fixture;

class NotSerializableException extends \Exception
{
    public function __sleep()
    {
        throw new \Exception ('Can not be serialized!');
    }
}
