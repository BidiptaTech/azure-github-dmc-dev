<?php

namespace Tests\Unit\Quotations;

use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateVersion;
use App\Models\User;
use App\Services\Quotations\QuotationTemplateDraftService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class QuotationTemplateDraftServiceTest extends TestCase
{
    private QuotationTemplateDraftService $service;

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

        $migration = require database_path('migrations/2026_11_02_161000_create_quotation_template_tables.php');
        $migration->up();

        $this->service = new QuotationTemplateDraftService();
    }

    public function test_dmc_can_have_itinerary_and_detailed_templates_independently(): void
    {
        $dmcId = $this->insertDmc(501);
        $adminId = $this->insertSuperAdmin(1);

        $itinerary = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>itinerary</div>',
            'css_content' => '',
        ]);
        $detailed = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_DETAILED, $adminId, [
            'html_content' => '<div>detailed</div>',
            'css_content' => '',
        ]);

        $this->assertSame(QuotationTemplate::TYPE_ITINERARY, $itinerary->template->quotation_type);
        $this->assertSame(QuotationTemplate::TYPE_DETAILED, $detailed->template->quotation_type);
        $this->assertSame(2, QuotationTemplate::query()->where('dmc_id', $dmcId)->count());
        $this->assertNotEquals($itinerary->quotation_template_id, $detailed->quotation_template_id);
    }

    public function test_new_template_creates_draft_version_one(): void
    {
        $dmcId = $this->insertDmc(502);
        $adminId = $this->insertSuperAdmin(1);

        $version = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>new</div>',
        ]);

        $this->assertSame(1, $version->version);
        $this->assertSame(QuotationTemplateVersion::STATUS_DRAFT, $version->status);
        $this->assertNull($version->template->active_version_id);
        $this->assertSame($adminId, (int) $version->created_by);
    }

    public function test_saving_existing_draft_updates_in_place(): void
    {
        $dmcId = $this->insertDmc(503);
        $adminId = $this->insertSuperAdmin(1);

        $first = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>v1</div>',
            'change_notes' => 'first',
        ]);

        $second = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>v1-updated</div>',
            'change_notes' => 'second save',
        ]);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, $second->version);
        $this->assertSame('<div>v1-updated</div>', $second->html_content);
        $this->assertSame('second save', $second->change_notes);
        $this->assertSame(1, QuotationTemplateVersion::query()->where('quotation_template_id', $first->quotation_template_id)->count());
        $this->assertSame($adminId, (int) $second->created_by);
    }

    public function test_editing_published_template_creates_new_draft_without_mutating_published(): void
    {
        $dmcId = $this->insertDmc(504);
        $adminId = $this->insertSuperAdmin(1);

        $draft = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>original</div>',
            'css_content' => 'body{}',
        ]);

        $template = $draft->template;
        $draft->status = QuotationTemplateVersion::STATUS_PUBLISHED;
        $draft->published_at = now();
        $draft->save();
        $template->active_version_id = $draft->id;
        $template->save();

        $publishedHtml = $draft->html_content;
        $activeBefore = $template->active_version_id;

        $newDraft = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>new-draft</div>',
            'css_content' => 'h1{}',
            'change_notes' => 'from published',
        ]);

        $this->assertSame(2, $newDraft->version);
        $this->assertSame(QuotationTemplateVersion::STATUS_DRAFT, $newDraft->status);
        $this->assertSame('<div>new-draft</div>', $newDraft->html_content);

        $published = QuotationTemplateVersion::query()->findOrFail($draft->id);
        $this->assertSame(QuotationTemplateVersion::STATUS_PUBLISHED, $published->status);
        $this->assertSame($publishedHtml, $published->html_content);

        $template->refresh();
        $this->assertSame($activeBefore, $template->active_version_id);
    }

    public function test_saving_draft_does_not_change_active_version_id(): void
    {
        $dmcId = $this->insertDmc(505);
        $adminId = $this->insertSuperAdmin(1);

        $v1 = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_DETAILED, $adminId, [
            'html_content' => '<div>a</div>',
        ]);
        $v1->status = QuotationTemplateVersion::STATUS_PUBLISHED;
        $v1->save();
        $template = $v1->template;
        $template->active_version_id = $v1->id;
        $template->save();

        $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_DETAILED, $adminId, [
            'html_content' => '<div>b</div>',
        ]);

        $this->assertSame($v1->id, $template->fresh()->active_version_id);
    }

    public function test_version_numbers_remain_unique(): void
    {
        $dmcId = $this->insertDmc(506);
        $adminId = $this->insertSuperAdmin(1);

        $v1 = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>1</div>',
        ]);
        $v1->status = QuotationTemplateVersion::STATUS_PUBLISHED;
        $v1->save();

        $v2 = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>2</div>',
        ]);

        $versions = QuotationTemplateVersion::query()
            ->where('quotation_template_id', $v1->quotation_template_id)
            ->pluck('version')
            ->all();

        $this->assertSame([1, 2], $versions);
        $this->assertNotEquals($v1->id, $v2->id);
    }

    public function test_created_by_comes_from_authenticated_argument_not_payload(): void
    {
        $dmcId = $this->insertDmc(507);
        $adminId = $this->insertSuperAdmin(1);
        $this->insertSuperAdmin(99);

        $version = $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>x</div>',
            'created_by' => 99,
        ]);

        $this->assertSame($adminId, (int) $version->created_by);
        $this->assertNotSame(99, (int) $version->created_by);
    }

    public function test_invalid_quotation_type_is_rejected(): void
    {
        $dmcId = $this->insertDmc(508);
        $adminId = $this->insertSuperAdmin(1);

        $this->expectException(InvalidArgumentException::class);
        $this->service->saveDraft($dmcId, 'invoice', $adminId, [
            'html_content' => '<div>x</div>',
        ]);
    }

    public function test_empty_html_is_rejected(): void
    {
        $dmcId = $this->insertDmc(509);
        $adminId = $this->insertSuperAdmin(1);

        $this->expectException(InvalidArgumentException::class);
        $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => "   \n\t  ",
        ]);
    }

    public function test_excessively_large_html_is_rejected(): void
    {
        $dmcId = $this->insertDmc(510);
        $adminId = $this->insertSuperAdmin(1);

        $this->expectException(InvalidArgumentException::class);
        $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => str_repeat('a', QuotationTemplateDraftService::MAX_HTML_CHARS + 1),
        ]);
    }

    public function test_excessively_large_css_is_rejected(): void
    {
        $dmcId = $this->insertDmc(511);
        $adminId = $this->insertSuperAdmin(1);

        $this->expectException(InvalidArgumentException::class);
        $this->service->saveDraft($dmcId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>ok</div>',
            'css_content' => str_repeat('b', QuotationTemplateDraftService::MAX_CSS_CHARS + 1),
        ]);
    }

    public function test_non_dmc_user_cannot_own_template(): void
    {
        $agentId = $this->insertUser(800, 'Agent', 2, 33);
        $adminId = $this->insertSuperAdmin(1);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->saveDraft($agentId, QuotationTemplate::TYPE_ITINERARY, $adminId, [
            'html_content' => '<div>x</div>',
        ]);
    }

    public function test_opening_does_not_create_records_via_service_absence(): void
    {
        $dmcId = $this->insertDmc(512);
        $this->assertSame(0, QuotationTemplate::query()->where('dmc_id', $dmcId)->count());
        $this->assertSame(0, QuotationTemplateVersion::query()->count());
    }

    private function insertUser(int $userId, string $name, int $userType, int $roleId): int
    {
        DB::table('users')->insert([
            'userId' => $userId,
            'name' => $name,
            'email' => 'user'.$userId.'@example.test',
            'user_type' => $userType,
            'role_id' => $roleId,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $userId;
    }

    private function insertDmc(int $userId): int
    {
        return $this->insertUser($userId, 'DMC '.$userId, 2, 11);
    }

    private function insertSuperAdmin(int $userId): int
    {
        return $this->insertUser($userId, 'Super Admin '.$userId, 1, 1);
    }
}
