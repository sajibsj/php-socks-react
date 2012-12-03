<?php

(include_once __DIR__.'/../vendor/autoload.php') OR die(PHP_EOL.'ERROR: composer autoloader not found, run "composer install" or see README for instructions'.PHP_EOL);

class ClientApiTest extends PHPUnit_Framework_TestCase{
    public function setUp(){
        $loop = React\EventLoop\Factory::create();

        $dnsResolverFactory = new React\Dns\Resolver\Factory();
        $dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

        $factory = new Socks\Factory($loop, $dns);

        $this->client = $factory->createClient('127.0.0.1', 9050);
    }

    /**
     * @expectedException UnexpectedValueException
     * @dataProvider providerInvalidAuthVersion
     */
    public function testInvalidAuthVersion($version)
    {
        $this->client->setAuth('username', 'password');
        $this->client->setProtocolVersion($version);
    }

    public function providerInvalidAuthVersion()
    {
        return array(array('4'), array('4a'));
    }

    public function testValidAuthVersion()
    {
        $this->client->setAuth('username', 'password');
        $this->assertNull($this->client->setProtocolVersion(5));
    }

    /**
     * @dataProvider providerValidProtocolVersion
     */
    public function testValidProtocolVersion($version)
    {
        $this->assertNull($this->client->setProtocolVersion($version));
    }

    public function providerValidProtocolVersion()
    {
        return array(array('4'), array('4a'), array('5'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidProtocolVersion()
    {
        $this->client->setProtocolVersion(3);
    }

    public function testValidResolveLocal()
    {
        $this->assertNull($this->client->setResolveLocal(false));
        $this->assertNull($this->client->setResolveLocal(true));
        $this->assertNull($this->client->setProtocolVersion('4'));
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidResolveRemote()
    {
        $this->client->setProtocolVersion('4');
        $this->client->setResolveLocal(false);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidResolveRemoteVersion()
    {
        $this->client->setResolveLocal(false);
        $this->client->setProtocolVersion('4');
    }

    public function testCreateHttpClient()
    {
        $this->assertInstanceOf('\React\HttpClient\Client', $this->client->createHttpClient());
    }

    public function testCreateSecureConnectionManager()
    {
        $this->assertInstanceOf('\Socks\SecureConnectionManager', $this->client->createSecureConnectionManager());
    }

    /**
     * @dataProvider providerAddress
     */
    public function testGetConnection($host, $port)
    {
        $this->assertInstanceOf('\React\Promise\PromiseInterface', $this->client->getConnection($host, $port));
    }

    public function providerAddress()
    {
        return array(
            array('localhost','80'),
            array('invalid domain','non-numeric')
        );
    }
}
