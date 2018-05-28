<?php
use PHPUnit\Framework\TestCase;
use Mindk\Framework\Config\Config;
class ConfigTest extends TestCase
{
    /**
     * Test init
     * @depends testAccessibility
     */
    public function testInitialization() {
        $config = new Config(['foo' => 'alpha']);
        $this->assertTrue( $config->has('foo') );
        $this->assertFalse( $config->has('baz'), "BAZ test failed" );
    }
    /**
     * Test init
     * @dataProvider initValues
     */
    public function testAccessibility($key, $value) {
        $config = new Config();
        $config->set([$key => $value]);
        $this->assertTrue($config->has($key));
        $this->assertEquals($value, $config->get($key));
        $this->assertEquals($value, $config->{$key});
    }
    public function initValues() {
        return [
            ['foo', 'alpha'],
            ['bar', 'bravo']
        ];
    }
}
