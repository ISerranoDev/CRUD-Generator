<?php

namespace ISerranoDev\CrudGenerator\Utils\Validator;

use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class FilterRequest extends ValidationRequest {

    ////////////////////////////////////////////////////////////////
    // Scheme used to parse the incoming request to the properties.
    ////////////////////////////////////////////////////////////////

    protected array $schema = [
        'filter_filters' => 'filter_filters',
        'filter_order' => 'filter_order',
        'current_request' => 'current_request',
        'page' => 'page',
        'limit' => 'limit',
        'all' => 'all',
    ];

    ////////////////////////////////////////////////////////////////
    // Properties
    ////////////////////////////////////////////////////////////////

    protected ?array $filter_filters = [];

    protected ?array $filter_order = [];

    protected ?string $current_request = "";

    protected ?int $page = 1;

    protected ?int $limit = 25;

    protected bool $all = false;

    protected function includeQueryParams(): bool
    {
        return true;
    }

}