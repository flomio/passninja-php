<?php
use PassNinja\Exceptions\PassNinjaInvalidArgumentsException;
use PassNinja\PassNinjaClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class PassNinjaClientTest extends TestCase
{
    private $testClient;
    private $dummyAccountId = 'dummy-account-id';
    private $dummyApiKey = 'dummy-api-key';
    private $createdPassObject;

    private $passTypesKeysFixturePath = __DIR__ . '/fixtures/passTypesKeys.json';
    private $createPassFixturePath = __DIR__ . '/fixtures/createPass.json';
    private $getPassFixturePath = __DIR__ . '/fixtures/getPass.json';
    private $putPassFixturePath = __DIR__ . '/fixtures/putPass.json';

    protected function setUp(): void
    {
        $this->testClient = new PassNinjaClient($this->dummyAccountId, $this->dummyApiKey);
    }

    private function loadFixture(string $filePath): array
    {
        return json_decode(file_get_contents($filePath), true);
    }

    private function setUpClient($handler){
        return new Client([
            'base_uri'=>'https://api.passninja.com',
            'headers' => [
                'Content-Type' => 'application/json',
                'x-account-id' => $this->dummyAccountId,
                'x-api-key' => $this->dummyApiKey,
            ],
            'handler' => $handler
        ]);
    }

    private function setUpMockPassNinja($responseData){
        $responseArray = array_map(fn($res)=> new Response(200, [], $res), $responseData);
        $mock = new MockHandler($responseArray);
        
        $handlerStack = HandlerStack::create($mock);
        $client = $this->setUpClient($handlerStack);
        $passNinjaClient = new PassNinjaClient($this->dummyAccountId,$this->dummyApiKey,$client);
        return $passNinjaClient;
    }

    public function testConstructorWithoutAccountIdAndApiKeyThrowsException()
    {
        $this->expectException(PassNinjaInvalidArgumentsException::class);
        new PassNinjaClient(null, null);
    }

    public function testConstructorWithAccountIdAndApiKeyRunsWithoutException()
    {
        $this->testClient = new PassNinjaClient($this->dummyAccountId, $this->dummyApiKey);
        $this->assertInstanceOf(PassNinjaClient::class, $this->testClient);
    }

    public function testCreatePassWithoutPassTypeThrowsException()
    {
        $this->expectException(PassNinjaInvalidArgumentsException::class);
        $this->testClient->pass['create'](null, null);
    }

    public function testCreatePassWithInvalidClientPassDataKeysThrowsException()
    {
        $this->expectException(PassNinjaInvalidArgumentsException::class);
        $this->testClient->pass['create']('demo.coupon', ['firstName' => null]);
    }

    public function testCreatePassWithMissingClientPassDataKeysThrowsException()
    {
        $this->expectException(PassNinjaInvalidArgumentsException::class);
        $this->testClient->pass['create']('demo.coupon', [
            'barcode' => '12345',
            'description' => 'This is a test description.',
        ]);
    }

    public function testCreatePassWithValidPassTypeAndValidClientPassDataRunsSuccessfully()
    {
        $passType = 'demo.coupon';

        $createPassFixture = $this->loadFixture($this->createPassFixturePath);
        $passTypesKeysFixture = $this->loadFixture($this->passTypesKeysFixturePath);

        $passNinja = $this->setUpMockPassNinja([json_encode($passTypesKeysFixture) ,json_encode($createPassFixture)]);


        // Mock HTTP client to return the fixture data
        $this->createdPassObject = $passNinja->pass['create']($passType, [
            "logoText"=>'Example Loyalty',
            "organizationName"=>'My org',
            "description"=>'This is a loyalty card',
            "expiration"=>'2025-12-01T23:59:59Z',
            "memberName"=>'Tasio Victoria',
            "specialOffer"=>'Free Drinks at 4:30PM!',
            "loyaltyLevel"=>'level one',
            "barcode"=>'www.google.com',
        ]);
        // print_r($this->createdPassObject);
        $this->assertEquals($this->createdPassObject['url'], $createPassFixture['urls']['landing']);
        $this->assertEquals($this->createdPassObject['serialNumber'], $createPassFixture['serialNumber']);
    }

    public function testGetPassWithoutPassTypeOrSerialNumberThrowsException()
    {
        $this->expectException(PassNinjaInvalidArgumentsException::class);
        $this->testClient->pass['get'](null, null);
    }

    public function testGetPassWithPassTypeAndSerialNumberRunsSuccessfully()
    {
        // Mock HTTP client to return the fixture data
        $getPassFixture = $this->loadFixture($this->getPassFixturePath);

        $passNinja = $this->setUpMockPassNinja([json_encode($getPassFixture)]);

        $getPassResponse = $passNinja->pass['get'](
            $getPassFixture['passType'],
            $getPassFixture['serialNumber']
        );
        
        $this->assertEquals($getPassResponse['serialNumber'], $getPassFixture['serialNumber']);
    }

    public function testPutPassWithoutPassTypeOrSerialNumberThrowsException()
    {
        $this->expectException(PassNinjaInvalidArgumentsException::class);
        $this->testClient->pass['put'](null, null, []);
    }

    public function testPutPassWithSerialNumberAndClientStatsDataRunsSuccessfully()
    {
        // Mock HTTP client to return the fixture data
        $putPassFixture = $this->loadFixture($this->putPassFixturePath);
        $passNinja = $this->setUpMockPassNinja([json_encode($putPassFixture)]);

        $putPassResponse = $passNinja->pass['put'](
            $putPassFixture['passType'],
            $putPassFixture['serialNumber'],
            [
                'logoText' => 'Put Example Loyalty',
                'organizationName' => 'Put my org',
                'description' => 'Put this is a loyalty card',
                'expiration' => '2025-12-01T23:59:59Z',
                'memberName' => 'Put Victoria',
                'specialOffer' => 'Put Free Drinks at 4:30PM!',
                'loyaltyLevel' => 'put level one',
                'barcode' => 'www.put.com',
            ]
        );
        $this->assertEquals($putPassResponse['serialNumber'], $putPassFixture['serialNumber']);
    }

    public function testDeletePassWithoutSerialNumberOrPassTypeThrowsException()
    {
        $this->expectException(PassNinjaInvalidArgumentsException::class);
        $this->testClient->pass['delete'](null, null);
    }

    public function testDeletePassWithSerialNumberRunsSuccessfully()
    {
        // Mock HTTP client to return the fixture data
        $createPassFixture = $this->loadFixture($this->createPassFixturePath);

        $passNinja = $this->setUpMockPassNinja([$createPassFixture['serialNumber']]);
        
        $deletePassResponse = $passNinja->pass['delete']($createPassFixture['passType'], $createPassFixture['serialNumber']);
        $this->assertEquals($deletePassResponse, $createPassFixture['serialNumber']);

    }
}