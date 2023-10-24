<?php

declare(strict_types=1);

namespace App\Services\SssBridge;

use App\Services\SssBridge\Enums\Api;
use App\Services\SssBridge\Exceptions\FailedOperationException;
use App\Services\SssBridge\Exceptions\FatalErrorException;
use App\Services\SssBridge\Exceptions\NoReturnException;

class RcsMemberClient extends HttpClient
{
    /**
     * {@inheritdoc}
     */
    protected Api $apiName = Api::RcsMember;

    public function __construct($options = [])
    {
        $config = config('services.sss_bridge.' . $this->apiName->value);

        $baseUrl = $config['base_url'];

        parent::__construct($baseUrl, $options);

        $this->debug = $config['debug'] ?? false;
    }

    public function getBasicInfo(string $ssNumber, string $token)
    {
        $response = $this->newRequest(headers: [
            'token' => $token
        ])->get("/{$ssNumber}/basicinfo");

        $body = $response->json();

        $replyCode = null;

        if (isset($body['replyStatus'], $body['replyStatus']['replyCode'])) {
            $replyCode = $body['replyStatus']['replyCode'];
        } else if (isset($body['replyCode'])) {
            $replyCode = $body['replyCode'];
        }

        if (! $replyCode) {
            throw new FatalErrorException(__('Internal server error. Please try again.'));
        }

        if ($replyCode !== '10000') {
            $message = __('Failed to get member information.');

            if (isset($body['remarks'])) {
                $message = $body['remarks'];
            }

            throw new FailedOperationException($message);
        }

        return $body;
    }

    public function getCoverage(string $ssNumber, string $token)
    {
        $response = $this->newRequest(headers: [
            'token' => $token
        ])->get("/{$ssNumber}/coverage");

        $body = $response->json();

        $replyCode = null;

        if (isset($body['replyStatus'], $body['replyStatus']['replyCode'])) {
            $replyCode = $body['replyStatus']['replyCode'];
        } else if (isset($body['replyCode'])) {
            $replyCode = $body['replyCode'];
        }

        if (! $replyCode) {
            throw new FatalErrorException(__('Internal server error. Please try again.'));
        }

        if ($replyCode !== '10000') {
            $message = __('Failed to get coverage information.');

            if (isset($body['remarks'])) {
                $message = $body['remarks'];
            }

            throw new FailedOperationException($message);
        }

        return $body;
    }

    public function getStatus(string $ssNumber, string $token)
    {
        $response = $this->newRequest(headers: [
            'token' => $token
        ])->get("/{$ssNumber}/status");

        $body = $response->json();

        $replyCode = null;

        if (isset($body['replyStatus'], $body['replyStatus']['replyCode'])) {
            $replyCode = $body['replyStatus']['replyCode'];
        } else if (isset($body['replyCode'])) {
            $replyCode = $body['replyCode'];
        }

        if (! $replyCode) {
            throw new FatalErrorException(__('Internal server error. Please try again.'));
        }

        if ($replyCode !== '10000') {
            $message = __('Failed to get status information.');

            if (isset($body['remarks'])) {
                $message = $body['remarks'];
            }

            throw new FailedOperationException($message);
        }

        return $body;
    }

    public function getContacts(string $ssNumber, string $token)
    {
        $response = $this->newRequest(headers: [
            'token' => $token
        ])->get("/{$ssNumber}/contacts");

        $body = $response->json();

        $replyCode = null;

        if (isset($body['replyStatus'], $body['replyStatus']['replyCode'])) {
            $replyCode = $body['replyStatus']['replyCode'];
        } else if (isset($body['replyCode'])) {
            $replyCode = $body['replyCode'];
        }

        if (! $replyCode) {
            throw new FatalErrorException(__('Internal server error. Please try again.'));
        }

        if ($replyCode !== '10000') {
            $message = __('Failed to get contacts information.');

            if (isset($body['remarks'])) {
                $message = $body['remarks'];
            }

            throw new FailedOperationException($message);
        }

        return $body;
    }

    public function getAddress(string $ssNumber, string $token)
    {
        $response = $this->newRequest(headers: [
            'token' => $token
        ])->get("/{$ssNumber}/address");

        $body = $response->json();

        $replyCode = null;

        if (isset($body['replyStatus'], $body['replyStatus']['replyCode'])) {
            $replyCode = $body['replyStatus']['replyCode'];
        } else if (isset($body['replyCode'])) {
            $replyCode = $body['replyCode'];
        }

        if (! $replyCode) {
            throw new FatalErrorException(__('Internal server error. Please try again.'));
        }

        if ($replyCode !== '10000') {
            $message = __('Failed to get address information.');

            if (isset($body['remarks'])) {
                $message = $body['remarks'];
            }

            throw new FailedOperationException($message);
        }

        return $body;
    }

    public function getEmployment(string $ssNumber, string $token)
    {
        $response = $this->newRequest(headers: [
            'token' => $token
        ])->get("/{$ssNumber}/employment");

        $body = $response->json();

        $replyCode = null;

        if (isset($body['replyStatus'], $body['replyStatus']['replyCode'])) {
            $replyCode = $body['replyStatus']['replyCode'];
        } else if (isset($body['replyCode'])) {
            $replyCode = $body['replyCode'];
        }

        if (! $replyCode) {
            throw new FatalErrorException(__('Internal server error. Please try again.'));
        }

        if ($replyCode !== '10000') {
            $message = __('Failed to get employment information.');

            if (isset($body['remarks'])) {
                $message = $body['remarks'];
            }

            throw new FailedOperationException($message);
        }

        return $body;
    }

    public function getSelfEmployed(string $ssNumber, string $token)
    {
        $response = $this->newRequest(headers: [
            'token' => $token
        ])->get("/{$ssNumber}/selfemployed");

        $body = $response->json();

        $replyCode = null;

        if (isset($body['replyStatus'], $body['replyStatus']['replyCode'])) {
            $replyCode = $body['replyStatus']['replyCode'];
        } else if (isset($body['replyCode'])) {
            $replyCode = $body['replyCode'];
        }

        if (! $replyCode) {
            throw new FatalErrorException(__('Internal server error. Please try again.'));
        }

        if ($replyCode !== '10000') {
            $message = __('Failed to get self-employed information.');

            if (isset($body['remarks'])) {
                $message = $body['remarks'];
            }

            throw new FailedOperationException($message);
        }

        return $body;
    }
}
