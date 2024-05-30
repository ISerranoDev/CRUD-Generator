<?php
namespace ISerranoDev\CrudGenerator\Utils\Tools;

use Symfony\Component\HttpFoundation\JsonResponse;

class APIJsonResponse extends JsonResponse {

    public function __construct(mixed $data = null, bool $success = true, string $message = "OK", int $status = 200, array $headers = [], bool $json = false)
    {
        $data = [
            "success" => $success,
            "data" => $data,
            "message" => $message,
        ];
        parent::__construct($data, $status, $headers, $json);
    }
}