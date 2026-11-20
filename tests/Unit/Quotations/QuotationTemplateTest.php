<?php

namespace Tests\Unit\Quotations;

use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateVersion;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuotationTemplateTest extends TestCase
{
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

        $migration = require database_path('migrations/2026_11_02_161000_create_quotation_template_tables.php');
        $migration->up();
    }

    public function test_a_dmc_can_have_one_itinerary_and_one_detailed_template(): void
    {
        $dmcId = $this->insertUser(101, 'DMC One');

        $itinerary = QuotationTemplate::query()->create([
            'dmc_id' => $dmcId,
            'quotation_type' => QuotationTemplate::TYPE_ITINERARY,
        ]);
        $detailed = QuotationTemplate::query()->create([
            'dmc_id' => $dmcId,
            'quotation_type' => QuotationTemplate::TYPE_DETAILED,
        ]);

        $this->assertNotEquals($itinerary->id, $detailed->id);
        $this->assertSame(2, QuotationTemplate::query()->where('dmc_id', $dmcId)->count());
        $this->assertSame(QuotationTemplate::TYPE_ITINERARY, $itinerary->quotation_type);
        $this->assertSame(QuotationTemplate::TYPE_DETAILED, $detailed->quotation_type);
    }

    public function test_a_dmc_cannot_have_duplicate_templates_for_the_same_type(): void
    {
        $dmcId = $this->insertUser(102, 'DMC Two');

        QuotationTemplate::query()->create([
            'dmc_id' => $dmcId,
            'quotation_type' => QuotationTemplate::TYPE_ITINERARY,
        ]);

        $this->expectException(QueryException::class);
        QuotationTemplate::query()->create([
            'dmc_id' => $dmcId,
            'quotation_type' => QuotationTemplate::TYPE_ITINERARY,
        ]);
    }

    public function test_a_template_can_have_multiple_versions(): void
    {
        $template = $this->makeTemplate(103);

        $first = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED);
        $second = $this->makeVersion($template, 2, QuotationTemplateVersion::STATUS_DRAFT);

        $this->assertSame(2, $template->versions()->count());
        $this->assertSame(1, $first->version);
        $this->assertSame(2, $second->version);
        $this->assertSame($template->id, $first->quotation_template_id);
        $this->assertSame($template->id, $second->quotation_template_id);
    }

    public function test_a_template_cannot_have_duplicate_version_numbers(): void
    {
        $template = $this->makeTemplate(104);
        $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_DRAFT);

        $this->expectException(QueryException::class);
        $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED);
    }

    public function test_template_relationships_return_the_dmc_and_versions(): void
    {
        $dmcId = $this->insertUser(105, 'DMC Rel');
        $creatorId = $this->insertUser(205, 'Creator');
        $template = QuotationTemplate::query()->create([
            'dmc_id' => $dmcId,
            'quotation_type' => QuotationTemplate::TYPE_DETAILED,
        ]);
        $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_DRAFT, $creatorId);
        $this->makeVersion($template, 2, QuotationTemplateVersion::STATUS_PUBLISHED, $creatorId);

        $template->refresh();

        $this->assertInstanceOf(User::class, $template->dmc);
        $this->assertSame($dmcId, (int) $template->dmc->userId);
        $this->assertSame('DMC Rel', $template->dmc->name);
        $this->assertCount(2, $template->versions);
        $this->assertSame([1, 2], $template->versions->pluck('version')->all());
        $this->assertSame($template->id, $template->versions->first()->template->id);
        $this->assertSame($creatorId, (int) $template->versions->first()->createdBy->userId);
    }

    public function test_active_version_relationship_resolves_correctly(): void
    {
        $template = $this->makeTemplate(106);
        $draft = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_DRAFT);
        $published = $this->makeVersion($template, 2, QuotationTemplateVersion::STATUS_PUBLISHED, null, now());

        $template->active_version_id = $published->id;
        $template->save();
        $template->refresh();

        $this->assertNotNull($template->activeVersion);
        $this->assertSame($published->id, $template->activeVersion->id);
        $this->assertSame(QuotationTemplateVersion::STATUS_PUBLISHED, $template->activeVersion->status);
        $this->assertNotEquals($draft->id, $template->activeVersion->id);
    }

    public function test_published_and_older_versions_remain_accessible(): void
    {
        $template = $this->makeTemplate(107);
        $v1 = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED, null, now()->subDay());
        $v2 = $this->makeVersion($template, 2, QuotationTemplateVersion::STATUS_PUBLISHED, null, now());

        $v1->status = QuotationTemplateVersion::STATUS_ARCHIVED;
        $v1->save();
        $template->active_version_id = $v2->id;
        $template->save();

        $this->assertSame(2, QuotationTemplateVersion::query()->where('quotation_template_id', $template->id)->count());
        $this->assertTrue(
            QuotationTemplateVersion::query()
                ->where('id', $v1->id)
                ->where('status', QuotationTemplateVersion::STATUS_ARCHIVED)
                ->exists()
        );
        $this->assertSame($v2->id, $template->fresh()->active_version_id);
        $this->assertSame(
            [1, 2],
            $template->versions()->orderBy('version')->pluck('version')->all()
        );
        $this->assertNotNull(QuotationTemplateVersion::query()->find($v1->id));
    }

    private function insertUser(int $userId, string $name): int
    {
        DB::table('users')->insert([
            'userId' => $userId,
            'name' => $name,
            'email' => 'user'.$userId.'@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $userId;
    }

    private function makeTemplate(int $dmcId, string $type = QuotationTemplate::TYPE_ITINERARY): QuotationTemplate
    {
        $this->insertUser($dmcId, 'DMC '.$dmcId);

        return QuotationTemplate::query()->create([
            'dmc_id' => $dmcId,
            'quotation_type' => $type,
        ]);
    }

    private function makeVersion(
        QuotationTemplate $template,
        int $version,
        string $status,
        ?int $createdBy = null,
        $publishedAt = null
    ): QuotationTemplateVersion {
        return QuotationTemplateVersion::query()->create([
            'quotation_template_id' => $template->id,
            'version' => $version,
            'html_content' => '<div>quotation</div>',
            'css_content' => 'body { color: #000; }',
            'status' => $status,
            'change_notes' => 'test version '.$version,
            'created_by' => $createdBy,
            'published_at' => $publishedAt,
        ]);
    }
}
