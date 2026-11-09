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

    /**
     * Same stack the hotel search uses (admin supplier + logged-in DMC).
     *
     * Recheck often happens under a different user than the enquiry, so when the
     * session has no DMC rule we fall back to the tour's DMC.
     */
    public function forHotelSupplier(
        ?Authenticatable $user,
        ?string $supplierCode,
        ?int $fallbackDmcId = null,
    ): MarkupContext {
        $code = strtolower(trim((string) $supplierCode));

        if ($code === 'tiniva') {
            $code = 'tinivia';
        }

        $supplier = $code !== ''
            ? SupplierMaster::query()->where('code', $code)->first()
            : null;

        $context = $this->create($user, $supplier, 'hotels');

        if ($context->dmcRule instanceof MarkupRule || ! $fallbackDmcId) {
            return $context;
        }

        $dmcUser = User::query()->where('userId', $fallbackDmcId)->first();

        return $this->create($dmcUser instanceof User ? $dmcUser : $user, $supplier, 'hotels');
    }
}
