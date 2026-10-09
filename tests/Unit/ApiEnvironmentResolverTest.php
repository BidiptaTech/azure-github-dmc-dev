<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ApiEnvironmentResolver;
use App\Services\SupplierConfigResolver;
use RuntimeException;
use Tests\TestCase;

class ApiEnvironmentResolverTest extends TestCase
{
    public function test_it_blocks_when_online_api_is_disabled(): void
    {
        $user = $this->masterDmc(['online_api' => false, 'live_api' => false]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Online hotel and attraction APIs are disabled');

        (new ApiEnvironmentResolver())->resolve($user);
    }

    public function test_it_uses_demo_when_live_api_is_off(): void
    {
        $user = $this->masterDmc(['online_api' => true, 'live_api' => false]);

        $this->assertSame(
            ApiEnvironmentResolver::DEMO,
            (new ApiEnvironmentResolver())->resolve($user)
        );
    }

    public function test_it_uses_live_when_live_api_is_on(): void
    {
        $user = $this->masterDmc(['online_api' => true, 'live_api' => true]);

        $this->assertSame(
            ApiEnvironmentResolver::LIVE,
            (new ApiEnvironmentResolver())->resolve($user)
        );
    }

    public function test_it_prefers_stored_booking_environment(): void
    {
        $user = $this->masterDmc(['online_api' => true, 'live_api' => true]);

        $environment = (new ApiEnvironmentResolver())->resolveForRecord([
            'onlineHotelBooking' => ['api_environment' => 'demo'],
        ], $user);

        $this->assertSame(ApiEnvironmentResolver::DEMO, $environment);
    }

    public function test_supplier_config_exposes_demo_and_live_env_keys(): void
    {
        $resolver = new SupplierConfigResolver();

        $demo = $resolver->fieldsFor('tinivia', 'demo');
        $live = $resolver->fieldsFor('tinivia', 'live');

        $this->assertSame('TINIVA_DEMO_API_BASE_URL', $demo['base_url']['env']);
        $this->assertSame('TINIVA_LIVE_API_BASE_URL', $live['base_url']['env']);
        $this->assertSame('TINIVA_API_BASE_URL', $demo['base_url']['legacy_env']);
        $this->assertArrayNotHasKey('legacy_env', $live['base_url']);

        $mgDemo = $resolver->fieldsFor('mg_bedbank', 'demo');
        $this->assertSame('MG_BEDBANK_DEMO_API_BASE_URL', $mgDemo['base_url']['env']);

        $sgLive = $resolver->fieldsFor('sg_attractions', 'live');
        $this->assertSame('SG_ATTRACTIONS_LIVE_API_BASE_URL', $sgLive['base_url']['env']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function masterDmc(array $attributes): User
    {
        $user = new User();
        $user->userId = 1010;
        $user->role_id = 10;
        $user->forceFill($attributes);

        return $user;
    }
}
