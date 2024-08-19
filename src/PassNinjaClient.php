<?php
namespace PassNinja;
// require __DIR__. './../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PassNinja\Exceptions\PassNinjaInvalidArgumentsException;

class PassNinjaClient
{
    private $basePath = 'https://api.passninja.com';
    private $client;
    public $pass = [];
    public $passTemplate = [];

    public function __construct($accountId, $apiKey, $client=null)
    {
        if (!is_string($accountId) || !is_string($apiKey)) {
            throw new PassNinjaInvalidArgumentsException(
                'Must provide both accountId and apiKey to PassNinjaClient constructor.'
            );
        }

        $this->client = !$client ? new Client(
            [
                'base_uri'=>$this->basePath,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-account-id' => $accountId,
                    'x-api-key' => $apiKey,
                ]
            ]
        ) : $client;

        $this->initBindings();
    }

    private function initBindings()
    {
        $this->pass['create'] = [$this, 'createPass'];
        $this->pass['get'] = [$this, 'getPass'];
        $this->pass['put'] = [$this, 'putPass'];
        $this->pass['delete'] = [$this, 'deletePass'];
        $this->pass['find'] = [$this, 'findPasses'];
        $this->pass['decrypt'] = [$this, 'decryptPass'];
        $this->passTemplate['find'] = [$this, 'findPassTemplate'];
    }

    private function extractInvalidKeys(array $clientPassData): array
    {
        $invalidKeys = [];
        foreach ($clientPassData as $key => $value) {
            if (!is_string($value)) {
                $invalidKeys[$key] = $value;
            }
        }
        return $invalidKeys;
    }

    private function fetchRequiredKeysSet(string $passType): array
    {
        try {
            $response = $this->client->get("/v1/passtypes/keys/$passType");
            $data = json_decode($response->getBody(), true);
            return array_flip($data['keys']);
        } catch (RequestException $e) {
            throw new PassNinjaInvalidArgumentsException('Failed to fetch required keys');
        }
    }

    private function extractMissingRequiredKeys(string $passType, array $clientPassData): array
    {
        $requiredKeys = $this->fetchRequiredKeysSet($passType);
        foreach (array_keys($clientPassData) as $key) {
            unset($requiredKeys[$key]);
        }
        return array_keys($requiredKeys);
    }

    public function findPassTemplate(string $passTemplateId): array
    {
        if (!is_string($passTemplateId)) {
            throw new PassNinjaInvalidArgumentsException(
                'Must provide passTemplateId to PassNinjaClient.findPassTemplate method.'
            );
        }
        try {
            $response = $this->client->get("/v1/pass_templates/" . urlencode($passTemplateId));
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new \RuntimeException('Failed to find pass template', 0, $e);
        }
    }

    public function createPass($passType, $clientPassData): array
    {

        if (!is_string($passType)) {
            throw new PassNinjaInvalidArgumentsException(
                'Must provide passType to PassNinjaClient.createPass method.'
            );
        }
        $invalidKeys = $this->extractInvalidKeys($clientPassData);
        if (!empty($invalidKeys)) {
            throw new PassNinjaInvalidArgumentsException(
                'Invalid templateStrings provided in clientPassData object. Invalid keys: ' . json_encode($invalidKeys)
            );
        }
        $missingRequiredKeys = $this->extractMissingRequiredKeys($passType, $clientPassData);
        if (!empty($missingRequiredKeys)) {
            throw new PassNinjaInvalidArgumentsException(
                'Some keys that are required for this passType are missing on the provided clientPassData object. Missing keys: ' . json_encode($missingRequiredKeys)
            );
        }
        try {
            $response = $this->client->post('/v1/passes', [
                'json' => [
                    'passType' => $passType,
                    'pass' => $clientPassData,
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return [
                'url' => $data['urls']['landing'],
                'serialNumber' => $data['serialNumber'],
                'passType' => $data['passType'],
            ];
        } catch (RequestException $e) {
            throw new \RuntimeException('Failed to create pass', 0, $e);
        }
    }

    public function getPass($passType, $serialNumber): array
    {
        if (!is_string($passType) || !is_string($serialNumber)) {
            throw new PassNinjaInvalidArgumentsException(
                'Must provide both passType and serialNumber to PassNinjaClient.getPass method.'
            );
        }
        try {
            $response = $this->client->get("/v1/passes/" . urlencode($passType) . "/" . urlencode($serialNumber));
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new \RuntimeException('Failed to get pass', 0, $e);
        }
    }

    public function putPass($passType, $serialNumber, $clientPassData): array
    {
        if (!is_string($passType) || !is_string($serialNumber)) {
            throw new PassNinjaInvalidArgumentsException(
                'Must provide both passType and serialNumber to PassNinjaClient.putPass method.'
            );
        }
        $invalidKeys = $this->extractInvalidKeys($clientPassData);
        if (!empty($invalidKeys)) {
            throw new PassNinjaInvalidArgumentsException(
                'Invalid templateStrings provided in clientPassData object. Invalid keys: ' . json_encode($invalidKeys)
            );
        }
        try {
            $response = $this->client->put("/v1/passes/" . urlencode($passType) . "/" . urlencode($serialNumber), [
                'json' => [
                    'passType' => $passType,
                    'pass' => $clientPassData,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new \RuntimeException('Failed to update pass', 0, $e);
        }
    }

    public function deletePass($passType, $serialNumber): string
    {
        if (!is_string($passType) || !is_string($serialNumber)) {
            throw new PassNinjaInvalidArgumentsException(
                'Must provide both passType and serialNumber to PassNinjaClient.deletePass method.'
            );
        }
        try {
            $res = $this->client->delete("/v1/passes/" . urlencode($passType) . "/" . urlencode($serialNumber));
            
            return $serialNumber;
        } catch (RequestException $e) {
            throw new \RuntimeException('Failed to delete pass', 0, $e);
        }
    }

    public function findPasses(string $passType): array
    {
        if (!is_string($passType)) {
            throw new PassNinjaInvalidArgumentsException(
                'Must provide passType to PassNinjaClient.find method.'
            );
        }
        try {
            $response = $this->client->get("/v1/passes/" . urlencode($passType));
            $data = json_decode($response->getBody(), true);
            return $data['passes'];
        } catch (RequestException $e) {
            throw new \RuntimeException('Failed to find passes', 0, $e);
        }
    }

    public function decryptPass(string $passType, string $payload): array
    {
        if (!is_string($passType) || !is_string($payload)) {
            throw new PassNinjaInvalidArgumentsException(
                'Must provide passType and payload to PassNinjaClient.decrypt method.'
            );
        }
        try {
            $response = $this->client->post("/v1/passes/" . urlencode($passType) . "/decrypt", [
                'json' => [
                    'payload' => $payload,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new \RuntimeException('Failed to decrypt pass', 0, $e);
        }
    }
}
