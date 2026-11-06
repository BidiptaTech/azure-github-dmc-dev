<?php

namespace App\Services\AttractionSuppliers;

use App\Services\AttractionSuppliers\Adapters\SgAttractionsAdapter;
use App\Services\AttractionSuppliers\Contracts\AttractionSupplierAdapter;
use InvalidArgumentException;

class AttractionSupplierFactory
{
    public function make(string $code): AttractionSupplierAdapter
    {
        return match ($code) {
            'sg_attractions' => app(SgAttractionsAdapter::class),
            default => throw new InvalidArgumentException("Unsupported attraction supplier [{$code}]."),
        };
    }
}
