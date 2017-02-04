<?php

namespace TestTools\Tests\Fixture;

class NotSerializable
{
    public function __sleep()
    {
        throw new \Exception ('Can not be serialized!');
    }
}
