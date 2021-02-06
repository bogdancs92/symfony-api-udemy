<?php


namespace App\Tests;


use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{
    public function testAddition() {
        $this->assertEquals(5,2 +3,'Five exepected equal to 2 + 3');

        $value = true;
        $this->assertTrue($value);

        $array = [
            "key" => "value"
        ];
        $this->assertArrayHasKey("key",$array);
    }
}