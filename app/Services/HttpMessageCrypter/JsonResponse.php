<?php

declare(strict_types=1);

namespace App\Services\HttpMessageCrypter;

use Illuminate\Http\JsonResponse as BaseJsonResponse;

class JsonResponse extends BaseJsonResponse
{
    /**
     * Get the json_decoded data from the response.
     *
     * @param  bool  $assoc
     * @param  int  $depth
     * @return mixed
     */
    public function getData($assoc = false, $depth = 512)
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public function setData($data = []): static
    {
        if (is_string($data)) {
            $this->original = $data;

            $this->data = $data;

            return $this->update();
        }

        return parent::setData($data);
    }
}
