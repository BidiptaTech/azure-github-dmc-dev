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

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string|null>  $credentials
     * @return array{success: bool, order_ref_id: ?string, external_status: ?string, message: ?string, provider: mixed}
     */
    public function createOrder(array $payload, array $credentials): array;

    /**
     * @param  array<string, string|null>  $credentials
     * @return array{success: bool, provider_status: ?int, message: ?string, provider: mixed}
     */
    public function updateOrder(string $orderRefId, string $action, array $credentials): array;
}
