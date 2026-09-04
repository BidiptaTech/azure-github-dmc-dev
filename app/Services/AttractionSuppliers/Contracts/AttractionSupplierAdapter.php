<?php

namespace App\Services\AttractionSuppliers\Contracts;

use App\Services\AttractionSuppliers\AttractionSearchRequest;

interface AttractionSupplierAdapter
{
    public function code(): string;

    /**
     * @param  array<string, string|null>  $credentials
     * @return array{attractions: array<int, array<string, mixed>>, provider: mixed}
     */
    public function fetchAttractions(AttractionSearchRequest $request, array $credentials): array;
}
