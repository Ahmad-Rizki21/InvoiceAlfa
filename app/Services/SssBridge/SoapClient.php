<?php

declare(strict_types=1);

namespace App\Services\SssBridge;

use App\Services\SssBridge\Enums\Api;
use App\Services\SssBridge\Exceptions\FatalErrorException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Log;
use SoapClient as BaseSoapClient;
use SoapFault;
use Throwable;

class SoapClient
{
    /**
     * Soap client instance
     *
     * @var \SoapClient
     */
    protected BaseSoapClient $client;

    /**
     * Log the responses
     *
     * @var bool
     */
    protected bool $debug = false;

    /**
     * The API name
     *
     * @var \App\Services\SssBridge\Enums\Api
     */
    protected Api $apiName = Api::Main;

    public function __construct(string $wsdlUrl, array $options = [])
    {
        $options = array_merge_recursive([
            'trace' => 1,
            'exception' => true,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
        ], $options);

        $this->client = new BaseSoapClient($wsdlUrl, $options);
    }

    public function call(string $operation, $payload): array
    {
        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        } else if (is_object($payload)) {
            $payload = get_object_vars($payload);
        }

        if (! is_array($payload)) {
            $payload = (array) $payload;
        }

        try {
            $response = $this->client->__soapCall($operation, $payload);

            if (is_object($response)) {
                $response = $this->convertResponseToArray($response);
            }

            if (! is_array($response)) {
                $this->getLogger()->warning("[Soap@$operation] does not returning an array", [
                    'payload' => $payload,
                    'response' => $response,
                ]);

                return [];
            }

            return $response;
        } catch (SoapFault $e) {
            $this->getLogger()->error("[Soap@$operation] " . $e->getMessage(), [
                'payload' => $payload,
                'faultcode' => $e->faultcode,
                'faultstring' => $e->faultstring,
                'faultactor' => $e->faultactor,
                'detail' => $e->detail,
                '_name' => $e->_name,
                'headerfault' => $e->headerfault,
            ]);

            $message = __((implode(' ', array_filter([$e->faultstring, $e->detail]))) ?: 'Internal server error. Please try again.');

            throw new FatalErrorException($message, $e);
        } catch (Throwable $e) {
            Log::error($e);

            throw new FatalErrorException(__('Internal server error. Please try again.'), $e);
        }
    }

    public function __call($method, $parameters)
    {
        return $this->call($method, ...$parameters);
    }

    public function getLogger()
    {
        return Log::channel($this->debug ? $this->apiName->value : 'null');
    }

    protected function convertResponseToArray($response)
    {
        if (is_object($response) || is_array($response)) {
            $result = (array) $response;

            foreach ($result as $key => $value) {
                $result[$key] = $this->convertResponseToArray($value);
            }

            return $result;
        }

        return $response;
    }

    public function ddWsdl()
    {
        dd([
            $this->client->__getTypes(),
            $this->client->__getFunctions(),
        ]);
    }
}
