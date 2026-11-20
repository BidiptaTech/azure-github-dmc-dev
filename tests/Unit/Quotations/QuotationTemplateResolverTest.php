<?php

namespace Tests\Unit\Quotations;

use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateVersion;
use App\Models\Tour;
use App\Services\Quotations\QuotationTemplateResolver;
use App\Services\Quotations\UnsupportedQuotationTypeException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuotationTemplateResolverTest extends TestCase
{
    private QuotationTemplateResolver $resolver;

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

        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dmc_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('master_dmc_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $migration = require database_path('migrations/2026_11_02_161000_create_quotation_template_tables.php');
        $migration->up();

        $this->resolver = new QuotationTemplateResolver();
    }

    public function test_no_custom_template_returns_system_default(): void
    {
        $dmcId = $this->insertUser(301, 'DMC Without Template');
        $tour = $this->makeTour($dmcId);

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_ITINERARY);

        $this->assertSame(QuotationTemplateResolver::SOURCE_SYSTEM, $result['source']);
        $this->assertSame($dmcId, $result['dmc_id']);
        $this->assertSame(QuotationTemplate::TYPE_ITINERARY, $result['quotation_type']);
        $this->assertNull($result['template_id']);
        $this->assertNull($result['version_id']);
        $this->assertNull($result['html_content']);
        $this->assertSame('single-tour-package.quotation', $result['default_view']);
        $this->assertSame('quotation-template:'.$dmcId.':itinerary:system', $result['identifier']);
    }

    public function test_missing_dmc_relationship_returns_system_default(): void
    {
        $tour = $this->makeTour(null);

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_DETAILED);

        $this->assertSame(QuotationTemplateResolver::SOURCE_SYSTEM, $result['source']);
        $this->assertNull($result['dmc_id']);
        $this->assertNull($result['template_id']);
        $this->assertSame('single-tour-package.detailedqutation', $result['default_view']);
        $this->assertSame('quotation-template:none:detailed:system', $result['identifier']);
    }

    public function test_itinerary_custom_template_returns_published_active_version(): void
    {
        $dmcId = $this->insertUser(302, 'Itinerary DMC');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<section>Itinerary</section>', 'h1{color:red}');
        $this->activate($template, $version);

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_ITINERARY);

        $this->assertSame(QuotationTemplateResolver::SOURCE_CUSTOM, $result['source']);
        $this->assertSame($dmcId, $result['dmc_id']);
        $this->assertSame($template->id, $result['template_id']);
        $this->assertSame($version->id, $result['version_id']);
        $this->assertSame(1, $result['version_number']);
        $this->assertSame('<section>Itinerary</section>', $result['html_content']);
        $this->assertSame('h1{color:red}', $result['css_content']);
        $this->assertSame('quotation-template:'.$dmcId.':itinerary:'.$version->id, $result['identifier']);
        $this->assertSame('single-tour-package.quotation', $result['default_view']);
    }

    public function test_detailed_custom_template_returns_published_active_version(): void
    {
        $dmcId = $this->insertUser(303, 'Detailed DMC');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_DETAILED);
        $version = $this->makeVersion($template, 3, QuotationTemplateVersion::STATUS_PUBLISHED, '<article>Packaged</article>', '');
        $this->activate($template, $version);

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_DETAILED);

        $this->assertSame(QuotationTemplateResolver::SOURCE_CUSTOM, $result['source']);
        $this->assertSame(QuotationTemplate::TYPE_DETAILED, $result['quotation_type']);
        $this->assertSame($version->id, $result['version_id']);
        $this->assertSame('<article>Packaged</article>', $result['html_content']);
        $this->assertSame('single-tour-package.detailedqutation', $result['default_view']);
    }

    public function test_draft_active_version_falls_back_to_system_default(): void
    {
        $dmcId = $this->insertUser(304, 'Draft DMC');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_DRAFT, '<div>Draft</div>');
        $this->activate($template, $version);

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_ITINERARY);

        $this->assertSame(QuotationTemplateResolver::SOURCE_SYSTEM, $result['source']);
        $this->assertNull($result['template_id']);
        $this->assertNull($result['html_content']);
    }

    public function test_archived_active_version_falls_back_to_system_default(): void
    {
        $dmcId = $this->insertUser(305, 'Archived DMC');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_ARCHIVED, '<div>Old</div>');
        $this->activate($template, $version);

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_ITINERARY);

        $this->assertSame(QuotationTemplateResolver::SOURCE_SYSTEM, $result['source']);
        $this->assertNull($result['version_id']);
    }

    public function test_active_version_from_another_template_falls_back(): void
    {
        $dmcId = $this->insertUser(306, 'Owner DMC');
        $otherDmcId = $this->insertUser(406, 'Other DMC');
        $tour = $this->makeTour($dmcId);
        $ownTemplate = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $otherTemplate = $this->makeTemplate($otherDmcId, QuotationTemplate::TYPE_ITINERARY);
        $foreignPublished = $this->makeVersion($otherTemplate, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<div>Foreign</div>');
        $ownTemplate->active_version_id = $foreignPublished->id;
        $ownTemplate->save();

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_ITINERARY);

        $this->assertSame(QuotationTemplateResolver::SOURCE_SYSTEM, $result['source']);
        $this->assertNull($result['html_content']);
        $this->assertNotEquals($foreignPublished->id, $result['version_id']);
    }

    public function test_empty_html_falls_back_to_system_default(): void
    {
        $dmcId = $this->insertUser(307, 'Empty HTML DMC');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_DETAILED);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '   ', 'body{}');
        $this->activate($template, $version);

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_DETAILED);

        $this->assertSame(QuotationTemplateResolver::SOURCE_SYSTEM, $result['source']);
        $this->assertNull($result['html_content']);
    }

    public function test_empty_css_with_valid_html_is_accepted(): void
    {
        $dmcId = $this->insertUser(308, 'Empty CSS DMC');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<p>OK</p>', '');
        $this->activate($template, $version);

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_ITINERARY);

        $this->assertSame(QuotationTemplateResolver::SOURCE_CUSTOM, $result['source']);
        $this->assertSame('<p>OK</p>', $result['html_content']);
        $this->assertSame('', $result['css_content']);
    }

    public function test_multiple_versions_use_explicit_active_version_not_latest(): void
    {
        $dmcId = $this->insertUser(309, 'Multi Version DMC');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $v1 = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<div>One</div>');
        $v2 = $this->makeVersion($template, 2, QuotationTemplateVersion::STATUS_PUBLISHED, '<div>Two</div>');
        $this->activate($template, $v1);

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_ITINERARY);

        $this->assertSame(QuotationTemplateResolver::SOURCE_CUSTOM, $result['source']);
        $this->assertSame($v1->id, $result['version_id']);
        $this->assertSame('<div>One</div>', $result['html_content']);
        $this->assertNotEquals($v2->id, $result['version_id']);
        $this->assertSame(1, $result['version_number']);
    }

    public function test_unsupported_quotation_type_throws(): void
    {
        $dmcId = $this->insertUser(310, 'Type DMC');
        $tour = $this->makeTour($dmcId);

        $this->expectException(UnsupportedQuotationTypeException::class);
        $this->expectExceptionMessage('Unsupported quotation type [packaged]');

        $this->resolver->resolve($tour, 'packaged');
    }

    public function test_dmc_identity_comes_from_tour_dmc_relationship_not_agent(): void
    {
        $owningDmcId = $this->insertUser(311, 'Owning DMC');
        $agentAsDmcId = $this->insertUser(411, 'Agent Mistaken As DMC');
        $tour = $this->makeTour($owningDmcId, $agentAsDmcId);

        $owningTemplate = $this->makeTemplate($owningDmcId, QuotationTemplate::TYPE_ITINERARY);
        $owningVersion = $this->makeVersion($owningTemplate, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<div>Owner</div>');
        $this->activate($owningTemplate, $owningVersion);

        $agentTemplate = $this->makeTemplate($agentAsDmcId, QuotationTemplate::TYPE_ITINERARY);
        $agentVersion = $this->makeVersion($agentTemplate, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<div>Agent</div>');
        $this->activate($agentTemplate, $agentVersion);

        $result = $this->resolver->resolve($tour, QuotationTemplate::TYPE_ITINERARY);

        $this->assertSame($owningDmcId, $result['dmc_id']);
        $this->assertSame($owningVersion->id, $result['version_id']);
        $this->assertSame('<div>Owner</div>', $result['html_content']);
        $this->assertSame($owningDmcId, (int) $tour->dmc->userId);
        $this->assertSame($agentAsDmcId, (int) $tour->agent_id);
        $this->assertNotEquals($agentVersion->id, $result['version_id']);
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

    private function makeTour(?int $dmcId, ?int $agentId = 999): Tour
    {
        return Tour::query()->create([
            'dmc_id' => $dmcId,
            'agent_id' => $agentId,
        ]);
    }

    private function makeTemplate(int $dmcId, string $type): QuotationTemplate
    {
        return QuotationTemplate::query()->create([
            'dmc_id' => $dmcId,
            'quotation_type' => $type,
        ]);
    }

    private function makeVersion(
        QuotationTemplate $template,
        int $version,
        string $status,
        string $html,
        string $css = 'body{color:#000}'
    ): QuotationTemplateVersion {
        return QuotationTemplateVersion::query()->create([
            'quotation_template_id' => $template->id,
            'version' => $version,
            'html_content' => $html,
            'css_content' => $css,
            'status' => $status,
            'published_at' => $status === QuotationTemplateVersion::STATUS_PUBLISHED ? now() : null,
        ]);
    }

    private function activate(QuotationTemplate $template, QuotationTemplateVersion $version): void
    {
        $template->active_version_id = $version->id;
        $template->save();
    }
}
