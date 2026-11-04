<?php

namespace Tests\Unit\Quotations;

use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateVersion;
use App\Models\User;
use App\Services\Quotations\QuotationTemplateDraftService;
use App\Services\Quotations\QuotationTemplatePublishService;
use App\Services\Quotations\QuotationTemplateResolver;
use App\Models\Tour;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class QuotationTemplatePublishServiceTest extends TestCase
{
    private QuotationTemplateDraftService $draftService;

    private QuotationTemplatePublishService $publishService;

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
            $table->unsignedTinyInteger('user_type')->nullable();
            $table->unsignedInteger('role_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tours', function (Blueprint $table) {
            $table->unsignedBigInteger('tour_id')->primary();
            $table->unsignedBigInteger('dmc_id')->nullable();
            $table->timestamps();
        });

        $migration = require database_path('migrations/2026_11_02_161000_create_quotation_template_tables.php');
        $migration->up();

        $this->draftService = new QuotationTemplateDraftService();
        $this->publishService = new QuotationTemplatePublishService($this->draftService);
    }

    public function test_publish_activates_draft_and_archives_previous_published(): void
    {
        $dmcId = $this->insertDmc(157);
        $adminId = $this->insertSuperAdmin(1);

        $v1 = $this->draftService->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>first</div>',
        ]);
        $this->publishService->publishDraft($dmcId, QuotationTemplate::TYPE_ITINERARY);

        $v2 = $this->draftService->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>second</div>',
        ]);
        $published = $this->publishService->publishDraft($dmcId, QuotationTemplate::TYPE_ITINERARY);

        $this->assertSame($v2->id, $published->id);
        $this->assertSame(QuotationTemplateVersion::STATUS_PUBLISHED, $published->status);
        $this->assertNotNull($published->published_at);

        $template = QuotationTemplate::query()->findOrFail($published->quotation_template_id);
        $this->assertSame($published->id, $template->active_version_id);

        $archived = QuotationTemplateVersion::query()->findOrFail($v1->id);
        $this->assertSame(QuotationTemplateVersion::STATUS_ARCHIVED, $archived->status);
    }

    public function test_resolver_returns_custom_after_publish(): void
    {
        $dmcId = $this->insertDmc(158);
        $adminId = $this->insertSuperAdmin(1);

        $this->draftService->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>live</div>',
            'css_content' => 'body{color:#000}',
        ]);

        $tour = new Tour();
        $tour->tour_id = 9001;
        $tour->dmc_id = $dmcId;
        $tour->setRelation('dmc', User::query()->findOrFail($dmcId));

        $before = (new QuotationTemplateResolver())->resolve($tour, QuotationTemplate::TYPE_ITINERARY);
        $this->assertSame(QuotationTemplateResolver::SOURCE_SYSTEM, $before['source']);

        $this->publishService->publishDraft($dmcId, QuotationTemplate::TYPE_ITINERARY);

        $after = (new QuotationTemplateResolver())->resolve($tour, QuotationTemplate::TYPE_ITINERARY);
        $this->assertSame(QuotationTemplateResolver::SOURCE_CUSTOM, $after['source']);
        $this->assertSame('<div>live</div>', $after['html_content']);
    }

    public function test_publish_without_draft_fails(): void
    {
        $dmcId = $this->insertDmc(159);
        $this->expectException(InvalidArgumentException::class);
        $this->publishService->publishDraft($dmcId, QuotationTemplate::TYPE_ITINERARY);
    }

    public function test_deactivate_clears_active_version_and_resolver_uses_system(): void
    {
        $dmcId = $this->insertDmc(160);
        $adminId = $this->insertSuperAdmin(1);

        $this->draftService->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>custom</div>',
        ]);
        $published = $this->publishService->publishDraft($dmcId, QuotationTemplate::TYPE_ITINERARY);

        $template = $this->publishService->deactivate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $this->assertNull($template->active_version_id);

        $stillPublished = QuotationTemplateVersion::query()->findOrFail($published->id);
        $this->assertSame(QuotationTemplateVersion::STATUS_PUBLISHED, $stillPublished->status);

        $tour = new Tour();
        $tour->tour_id = 9002;
        $tour->dmc_id = $dmcId;
        $tour->setRelation('dmc', User::query()->findOrFail($dmcId));

        $resolved = (new QuotationTemplateResolver())->resolve($tour, QuotationTemplate::TYPE_ITINERARY);
        $this->assertSame(QuotationTemplateResolver::SOURCE_SYSTEM, $resolved['source']);
    }

    public function test_activate_restores_published_active_version(): void
    {
        $dmcId = $this->insertDmc(161);
        $adminId = $this->insertSuperAdmin(1);

        $this->draftService->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>again</div>',
        ]);
        $published = $this->publishService->publishDraft($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $this->publishService->deactivate($dmcId, QuotationTemplate::TYPE_ITINERARY);

        $activated = $this->publishService->activate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $this->assertSame($published->id, $activated->id);

        $template = QuotationTemplate::query()->findOrFail($published->quotation_template_id);
        $this->assertSame($published->id, $template->active_version_id);

        $tour = new Tour();
        $tour->tour_id = 9003;
        $tour->dmc_id = $dmcId;
        $tour->setRelation('dmc', User::query()->findOrFail($dmcId));

        $resolved = (new QuotationTemplateResolver())->resolve($tour, QuotationTemplate::TYPE_ITINERARY);
        $this->assertSame(QuotationTemplateResolver::SOURCE_CUSTOM, $resolved['source']);
        $this->assertSame('<div>again</div>', $resolved['html_content']);
    }

    private function insertDmc(int $userId): int
    {
        User::query()->create([
            'userId' => $userId,
            'name' => 'DMC '.$userId,
            'email' => 'dmc'.$userId.'@example.test',
            'user_type' => 2,
            'role_id' => 11,
            'is_active' => 1,
        ]);

        return $userId;
    }

    private function insertSuperAdmin(int $userId): int
    {
        User::query()->create([
            'userId' => $userId,
            'name' => 'Admin '.$userId,
            'email' => 'admin'.$userId.'@example.test',
            'user_type' => 1,
            'role_id' => 1,
            'is_active' => 1,
        ]);

        return $userId;
    }
}
