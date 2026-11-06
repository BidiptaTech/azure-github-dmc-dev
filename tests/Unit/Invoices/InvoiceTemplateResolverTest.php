<?php

namespace Tests\Unit\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\InvoiceTemplateVersion;
use App\Services\Invoices\InvoiceTemplateResolver;
use App\Services\Invoices\UnsupportedInvoiceTypeException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoiceTemplateResolverTest extends TestCase
{
    private InvoiceTemplateResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->unsignedBigInteger('userId')->primary();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->primary();
            $table->unsignedBigInteger('dmc_id')->nullable();
            $table->string('invoice_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $migration = require database_path('migrations/2026_11_04_180000_create_invoice_template_tables.php');
        $migration->up();

        $this->resolver = new InvoiceTemplateResolver();
    }

    public function test_type_from_axes_matrix(): void
    {
        $this->assertSame('proforma', InvoiceTemplateResolver::typeFromAxes('proforma', 'full', 'standard'));
        $this->assertSame('proforma_price_only', InvoiceTemplateResolver::typeFromAxes('proforma', 'price-only', null));
        $this->assertSame('final', InvoiceTemplateResolver::typeFromAxes('final', 'full', null));
        $this->assertSame('final_price_only', InvoiceTemplateResolver::typeFromAxes('final', 'price-only', 'standard'));
        $this->assertSame('alternate', InvoiceTemplateResolver::typeFromAxes('proforma', 'full', 'alternate'));
        $this->assertSame('alternate', InvoiceTemplateResolver::typeFromAxes('final', 'price-only', 'alternate'));
    }

    public function test_no_custom_template_returns_system_default(): void
    {
        $dmcId = $this->insertUser(401);
        $invoice = $this->makeInvoice($dmcId);

        $result = $this->resolver->resolve($invoice, InvoiceTemplate::TYPE_PROFORMA);

        $this->assertSame(InvoiceTemplateResolver::SOURCE_SYSTEM, $result['source']);
        $this->assertSame($dmcId, $result['dmc_id']);
        $this->assertSame('invoices.pdf.proforma', $result['default_view']);
        $this->assertNull($result['html_content']);
    }

    public function test_published_active_version_returns_custom(): void
    {
        $dmcId = $this->insertUser(402);
        $invoice = $this->makeInvoice($dmcId);

        $template = InvoiceTemplate::query()->create([
            'dmc_id' => $dmcId,
            'invoice_type' => InvoiceTemplate::TYPE_FINAL,
            'active_version_id' => null,
        ]);

        $version = InvoiceTemplateVersion::query()->create([
            'invoice_template_id' => $template->id,
            'version' => 1,
            'html_content' => '<div>{{ branding.company_name|default("Invoice") }}</div>',
            'css_content' => '.x{color:red}',
            'status' => InvoiceTemplateVersion::STATUS_PUBLISHED,
            'created_by' => $dmcId,
            'published_at' => now(),
        ]);

        $template->active_version_id = $version->id;
        $template->save();

        $result = $this->resolver->resolve($invoice, InvoiceTemplate::TYPE_FINAL);

        $this->assertSame(InvoiceTemplateResolver::SOURCE_CUSTOM, $result['source']);
        $this->assertSame((int) $template->id, $result['template_id']);
        $this->assertSame((int) $version->id, $result['version_id']);
        $this->assertSame('invoices.pdf.final', $result['default_view']);
        $this->assertStringContainsString('branding.company_name', (string) $result['html_content']);
    }

    public function test_unsupported_type_throws(): void
    {
        $this->expectException(UnsupportedInvoiceTypeException::class);
        $invoice = $this->makeInvoice($this->insertUser(403));
        $this->resolver->resolve($invoice, 'not-a-type');
    }

    private function insertUser(int $userId): int
    {
        DB::table('users')->insert([
            'userId' => $userId,
            'name' => 'DMC '.$userId,
            'email' => 'dmc'.$userId.'@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $userId;
    }

    private function makeInvoice(?int $dmcId): Invoice
    {
        $id = (int) (microtime(true) * 1000) % 1000000 + random_int(1, 999);
        DB::table('invoices')->insert([
            'invoice_id' => $id,
            'dmc_id' => $dmcId,
            'invoice_type' => 'proforma',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $invoice = Invoice::query()->where('invoice_id', $id)->firstOrFail();
        if ($dmcId !== null) {
            $invoice->setRelation('dmc', \App\Models\User::query()->where('userId', $dmcId)->first());
        }

        return $invoice;
    }
}
