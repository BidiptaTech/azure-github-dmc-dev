<?php

namespace Tests\Unit\Finance;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CountryRateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Temp table shadows the real countries table for this connection only; real data is untouched.
        Schema::create('countries', function ($t) {
            $t->temporary();
            $t->id();
            $t->string('name');
            $t->string('currency')->nullable();
            $t->decimal('current_rate', 18, 8)->nullable();
            $t->timestamp('rate_updated_at')->nullable();
            $t->timestamps();
        });
    }

    public function test_rate_lookup_handles_missing_invalid_and_sgd(): void
    {
        Country::create(['name' => 'Indonesia', 'currency' => 'IDR', 'current_rate' => 12000.5]);
        Country::create(['name' => 'Zeroland', 'currency' => 'ZZZ', 'current_rate' => 0]);
        Country::create(['name' => 'Nullland', 'currency' => 'NNN']);

        $this->assertSame(12000.5, Country::sgdRateFor('idr'));
        $this->assertSame(1.0, Country::sgdRateFor('SGD'));
        $this->assertNull(Country::sgdRateFor('ZZZ'));
        $this->assertNull(Country::sgdRateFor('NNN'));
        $this->assertNull(Country::sgdRateFor('XXX'));
    }

    public function test_sgd_preview_uses_stored_rate_and_fails_safely(): void
    {
        Country::create(['name' => 'Indonesia', 'currency' => 'IDR', 'current_rate' => 12000]);
        $controller = app(\App\Http\Controllers\WalletManagementController::class);

        $ok = $controller->sgdPreview(Request::create('/x', 'GET', ['amount' => 10, 'to' => 'IDR']));
        $this->assertEquals(120000, $ok->getData()->converted_amount);

        $missing = $controller->sgdPreview(Request::create('/x', 'GET', ['amount' => 10, 'to' => 'THB']));
        $this->assertSame(422, $missing->getStatusCode());
        $this->assertFalse($missing->getData()->success);
    }
}
