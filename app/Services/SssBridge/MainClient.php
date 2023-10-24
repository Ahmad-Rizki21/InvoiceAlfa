<?php

declare(strict_types=1);

namespace App\Services\SssBridge;

use App\Services\SssBridge\Enums\Api;
use App\Services\SssBridge\Exceptions\ExpiredTransactionTokenException;
use App\Services\SssBridge\Exceptions\FailedOperationException;
use App\Services\SssBridge\Exceptions\NoReturnException;

class MainClient extends SoapClient
{
    /**
     * {@inheritdoc}
     */
    protected Api $apiName = Api::Main;

    public function __construct($options = [])
    {
        $config = config('services.sss_bridge.' . $this->apiName->value);

        $wsdlUrl = $config['wdsl_url'];

        parent::__construct($wsdlUrl, $options);

        $this->debug = $config['debug'] ?? false;
    }

    public function call(string $operation, $payload): array
    {
        $response = parent::call($operation, $payload);

        // $result = $response['return'] ?? null;

        // if (! $result) {
        //     $message = __('Failed to get response');

        //     $this->getLogger()->error("[Soap@$operation] " . $message, [
        //         'payload' => $payload,
        //         'response' => $response,
        //     ]);

        //     throw new NoReturnException($message);
        // }

        if (! isset($response['returnMessage'])) {
            $response['returnMessage'] = '';
        }

        if (! isset($response['processFlag'])) {
            $response['processFlag'] = '0';
        }

        if (isset($response['item'], $response['item'][0])) {
            if (isset($response['item'][0]['processFlag'])) {
                $response['processFlag'] = $response['item'][0]['processFlag'];
            } else if (isset($response['item'][0]['processFlg'])) {
                $response['processFlag'] = $response['item'][0]['processFlg'];
            }

            if (isset($response['item'][0]['returnMessage'])) {
                $response['returnMessage'] = $response['item'][0]['returnMessage'];
            }
        }

        if ($response['processFlag'] != 1) {
            $message = $response['returnMessage'] ?: __('Failed to get response');

            $this->getLogger()->error("[Soap@$operation] " . $message, [
                'payload' => $payload,
                'response' => $response,
            ]);

            if (
                $message === 'NOT ALLOWED' &&
                is_array($payload) &&
                (array_key_exists('tranToken', $payload) || array_key_exists('id', $payload))
            ) {
                throw new ExpiredTransactionTokenException(Api::Main);
            }

            throw new FailedOperationException($message, $response, $payload);
        }

        $this->getLogger()->info("[Soap@$operation] success", [
            'payload' => $payload,
            'response' => $response,
        ]);

        return $response;
    }
}
