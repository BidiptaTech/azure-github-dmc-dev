<?php

namespace App\Services\OnlinePricing;

use App\Helpers\CommonHelper;
use App\Models\SupplierMaster;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class MarkupContextFactory
{
    public function __construct(
        private AdminMarkupResolver $adminMarkupResolver,
        private DmcMarkupResolver $dmcMarkupResolver,
    ) {}

    /**
     * @param  'hotels'|'attractions'  $serviceType
     */
    public function create(?Authenticatable $user, ?SupplierMaster $adminSupplier, string $serviceType): MarkupContext
    {
        $dmcId = null;

        if ($user instanceof User) {
            $dmcId = CommonHelper::getDmcId($user);
            if (! $dmcId && (int) ($user->role_id ?? 0) === 11) {
                $dmcId = (int) $user->userId;
            }
        }

        return new MarkupContext(
            adminRule: $this->adminMarkupResolver->fromSupplier($adminSupplier),
            dmcRule: $this->dmcMarkupResolver->forService($dmcId, $serviceType),
            dmcId: $dmcId,
            supplierCode: $adminSupplier?->code,
            serviceType: $serviceType,
        );
    }
}
