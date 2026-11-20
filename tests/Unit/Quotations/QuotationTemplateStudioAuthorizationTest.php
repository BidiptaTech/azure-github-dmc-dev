<?php

namespace Tests\Unit\Quotations;

use App\Http\Controllers\Admin\QuotationTemplateStudioController;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateVersion;
use App\Models\User;
use App\Services\Quotations\QuotationTemplateDraftService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View as IlluminateView;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class QuotationTemplateStudioAuthorizationTest extends TestCase
{
    private QuotationTemplateStudioController $controller;

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
            $table->string('password')->nullable();
            $table->unsignedTinyInteger('user_type')->nullable();
            $table->unsignedInteger('role_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        $migration = require database_path('migrations/2026_11_02_161000_create_quotation_template_tables.php');
        $migration->up();

        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureUserIsActive::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $this->controller = app(QuotationTemplateStudioController::class);
    }

    public function test_super_admin_middleware_allows_role_id_1_and_user_type_1(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        Auth::login($admin);

        $middleware = new EnsureSuperAdmin();
        $response = $middleware->handle(Request::create('/quotation-templates', 'GET'), function () {
            return response('allowed', 200);
        });

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('allowed', $response->getContent());
    }

    public function test_super_admin_middleware_rejects_other_admin_roles(): void
    {
        $admin = $this->makeUser(2, 'Admin Two', 1, 2);
        Auth::login($admin);

        $middleware = new EnsureSuperAdmin();
        $this->expectException(HttpException::class);
        $middleware->handle(Request::create('/quotation-templates', 'GET'), function () {
            return response('allowed', 200);
        });
    }

    public function test_super_admin_can_access_studio_index(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $this->makeUser(501, 'DMC One', 2, 11);
        Auth::login($admin);

        $view = $this->controller->index(Request::create('/quotation-templates', 'GET'));

        $this->assertInstanceOf(IlluminateView::class, $view);
        $this->assertSame('admin.quotation-templates.index', $view->name());
        $this->assertTrue($view->offsetExists('dmcs'));
    }

    public function test_non_super_admin_cannot_access_studio(): void
    {
        $admin = $this->makeUser(2, 'Admin Two', 1, 2);

        $this->actingAs($admin)
            ->get('/quotation-templates')
            ->assertForbidden();
    }

    public function test_agent_cannot_create_or_edit_templates(): void
    {
        $agent = $this->makeUser(33, 'Agent', 2, 33);
        $dmc = $this->makeUser(501, 'DMC', 2, 11);
        Auth::login($agent);

        try {
            $this->controller->store($this->draftRequest([
                'html_content' => '<div>hack</div>',
            ]), (int) $dmc->userId, 'itinerary');
            $this->fail('Expected HttpException for agent store.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        try {
            $this->controller->updateDraft($this->draftRequest([
                'html_content' => '<div>hack</div>',
            ]), (int) $dmc->userId, 'itinerary');
            $this->fail('Expected HttpException for agent update.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertSame(0, QuotationTemplate::query()->count());
    }

    public function test_dmc_user_cannot_create_or_edit_templates(): void
    {
        $dmc = $this->makeUser(501, 'DMC', 2, 11);
        Auth::login($dmc);

        try {
            $this->controller->store($this->draftRequest([
                'html_content' => '<div>own</div>',
            ]), (int) $dmc->userId, 'itinerary');
            $this->fail('Expected HttpException for DMC store.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        try {
            $this->controller->updateDraft($this->draftRequest([
                'html_content' => '<div>own</div>',
            ]), (int) $dmc->userId, 'detailed');
            $this->fail('Expected HttpException for DMC update.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        try {
            $this->controller->publishDraft(Request::create('/quotation-templates/'.$dmc->userId.'/itinerary/publish', 'POST'), (int) $dmc->userId, 'itinerary');
            $this->fail('Expected HttpException for DMC publish.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertSame(0, QuotationTemplate::query()->count());
    }

    public function test_super_admin_can_publish_draft(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $dmc = $this->makeUser(501, 'DMC', 2, 11);
        Auth::login($admin);

        $this->controller->store($this->draftRequest([
            'html_content' => '<div>ready</div>',
        ]), (int) $dmc->userId, 'itinerary');

        $response = $this->controller->publishDraft(
            Request::create('/quotation-templates/'.$dmc->userId.'/itinerary/publish', 'POST'),
            (int) $dmc->userId,
            'itinerary'
        );

        $this->assertTrue($response->isRedirect());
        $template = QuotationTemplate::query()
            ->where('dmc_id', $dmc->userId)
            ->where('quotation_type', 'itinerary')
            ->firstOrFail();
        $this->assertNotNull($template->active_version_id);
        $version = QuotationTemplateVersion::query()->findOrFail($template->active_version_id);
        $this->assertSame(QuotationTemplateVersion::STATUS_PUBLISHED, $version->status);
    }

    public function test_super_admin_can_deactivate_and_activate_custom_template(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $dmc = $this->makeUser(503, 'DMC Toggle', 2, 11);
        Auth::login($admin);

        $this->controller->store($this->draftRequest([
            'html_content' => '<div>toggle-me</div>',
        ]), (int) $dmc->userId, 'itinerary');
        $this->controller->publishDraft(
            Request::create('/quotation-templates/'.$dmc->userId.'/itinerary/publish', 'POST'),
            (int) $dmc->userId,
            'itinerary'
        );

        $deactivate = $this->controller->deactivate(
            Request::create('/quotation-templates/'.$dmc->userId.'/itinerary/deactivate', 'POST'),
            (int) $dmc->userId,
            'itinerary'
        );
        $this->assertTrue($deactivate->isRedirect());

        $template = QuotationTemplate::query()
            ->where('dmc_id', $dmc->userId)
            ->where('quotation_type', 'itinerary')
            ->firstOrFail();
        $this->assertNull($template->active_version_id);

        $activate = $this->controller->activate(
            Request::create('/quotation-templates/'.$dmc->userId.'/itinerary/activate', 'POST'),
            (int) $dmc->userId,
            'itinerary'
        );
        $this->assertTrue($activate->isRedirect());
        $this->assertNotNull($template->fresh()->active_version_id);
    }

    public function test_dmc_cannot_deactivate_or_activate(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $dmc = $this->makeUser(504, 'DMC Locked', 2, 11);

        Auth::login($admin);
        $this->controller->store($this->draftRequest([
            'html_content' => '<div>locked</div>',
        ]), (int) $dmc->userId, 'itinerary');
        $this->controller->publishDraft(
            Request::create('/quotation-templates/'.$dmc->userId.'/itinerary/publish', 'POST'),
            (int) $dmc->userId,
            'itinerary'
        );

        Auth::login($dmc);
        try {
            $this->controller->deactivate(
                Request::create('/quotation-templates/'.$dmc->userId.'/itinerary/deactivate', 'POST'),
                (int) $dmc->userId,
                'itinerary'
            );
            $this->fail('Expected HttpException for DMC deactivate.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        try {
            $this->controller->activate(
                Request::create('/quotation-templates/'.$dmc->userId.'/itinerary/activate', 'POST'),
                (int) $dmc->userId,
                'itinerary'
            );
            $this->fail('Expected HttpException for DMC activate.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertNotNull(
            QuotationTemplate::query()
                ->where('dmc_id', $dmc->userId)
                ->where('quotation_type', 'itinerary')
                ->value('active_version_id')
        );
    }

    public function test_unauthorized_requests_cannot_modify_another_dmcs_template(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $dmcA = $this->makeUser(501, 'DMC A', 2, 11);
        $dmcB = $this->makeUser(502, 'DMC B', 2, 11);
        $intruder = $this->makeUser(33, 'Agent', 2, 33);

        app(QuotationTemplateDraftService::class)->saveDraft(
            (int) $dmcA->userId,
            QuotationTemplate::TYPE_ITINERARY,
            (int) $admin->userId,
            ['html_content' => '<div>owned-by-a</div>', 'css_content' => '']
        );

        $templateA = QuotationTemplate::query()
            ->where('dmc_id', $dmcA->userId)
            ->where('quotation_type', 'itinerary')
            ->firstOrFail();
        $originalHtml = $templateA->versions()->first()->html_content;

        Auth::login($intruder);
        try {
            $this->controller->updateDraft($this->draftRequest([
                'html_content' => '<div>stolen</div>',
                'dmc_id' => $dmcB->userId,
                'created_by' => $intruder->userId,
            ]), (int) $dmcA->userId, 'itinerary');
            $this->fail('Expected HttpException for intruder update.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertSame(
            $originalHtml,
            $templateA->versions()->first()->fresh()->html_content
        );
        $this->assertSame(0, QuotationTemplate::query()->where('dmc_id', $dmcB->userId)->count());
    }

    public function test_opening_studio_does_not_create_a_template_record(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $dmc = $this->makeUser(501, 'DMC', 2, 11);
        Auth::login($admin);

        $view = $this->controller->show((int) $dmc->userId, QuotationTemplate::TYPE_ITINERARY);

        $this->assertInstanceOf(IlluminateView::class, $view);
        $this->assertSame('admin.quotation-templates.show', $view->name());
        $this->assertNull($view->getData()['template']);
        $this->assertSame(0, QuotationTemplate::query()->count());
        $this->assertSame(0, QuotationTemplateVersion::query()->count());
    }

    public function test_client_supplied_created_by_cannot_override_authenticated_user(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $imposter = $this->makeUser(99, 'Imposter Admin', 1, 1);
        $dmc = $this->makeUser(501, 'DMC', 2, 11);
        Auth::login($admin);

        $this->controller->store($this->draftRequest([
            'html_content' => '<div>secure</div>',
            'created_by' => $imposter->userId,
        ]), (int) $dmc->userId, 'itinerary');

        $version = QuotationTemplateVersion::query()->firstOrFail();
        $this->assertSame((int) $admin->userId, (int) $version->created_by);
        $this->assertNotSame((int) $imposter->userId, (int) $version->created_by);
    }

    public function test_client_supplied_dmc_ownership_cannot_bypass_path_dmc(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $dmcA = $this->makeUser(501, 'DMC A', 2, 11);
        $dmcB = $this->makeUser(502, 'DMC B', 2, 11);
        Auth::login($admin);

        $this->controller->store($this->draftRequest([
            'html_content' => '<div>for-a</div>',
            'dmc_id' => $dmcB->userId,
        ]), (int) $dmcA->userId, 'detailed');

        $this->assertSame(1, QuotationTemplate::query()->where('dmc_id', $dmcA->userId)->count());
        $this->assertSame(0, QuotationTemplate::query()->where('dmc_id', $dmcB->userId)->count());
    }

    public function test_invalid_quotation_type_route_is_rejected(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $dmc = $this->makeUser(501, 'DMC', 2, 11);
        Auth::login($admin);

        try {
            $this->controller->show((int) $dmc->userId, 'invoice');
            $this->fail('Expected 404 for invalid type.');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }

        $this->actingAs($admin)
            ->get('/quotation-templates/'.$dmc->userId.'/invoice')
            ->assertNotFound();
    }

    public function test_empty_html_is_rejected_over_http(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $dmc = $this->makeUser(501, 'DMC', 2, 11);
        Auth::login($admin);

        try {
            $this->controller->store($this->draftRequest([
                'html_content' => '   ',
            ]), (int) $dmc->userId, 'itinerary');
            $this->fail('Expected validation failure for empty HTML.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('html_content', $e->errors());
        }

        $this->assertSame(0, QuotationTemplate::query()->count());
    }

    public function test_excessively_large_html_is_rejected_over_http(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $dmc = $this->makeUser(501, 'DMC', 2, 11);
        Auth::login($admin);

        try {
            $this->controller->store($this->draftRequest([
                'html_content' => str_repeat('x', 500_001),
            ]), (int) $dmc->userId, 'itinerary');
            $this->fail('Expected validation failure for large HTML.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('html_content', $e->errors());
        }
    }

    public function test_studio_views_escape_metadata_and_do_not_execute_html(): void
    {
        $show = file_get_contents(resource_path('views/admin/quotation-templates/show.blade.php'));
        $version = file_get_contents(resource_path('views/admin/quotation-templates/version-show.blade.php'));
        $index = file_get_contents(resource_path('views/admin/quotation-templates/index.blade.php'));

        $this->assertStringNotContainsString('{!! $editableHtml !!}', $show);
        $this->assertStringNotContainsString('{!! $versionModel->html_content !!}', $version);
        $this->assertStringNotContainsString('{!! $dmc->name !!}', $show);
        $this->assertStringNotContainsString('{!! $dmc->name !!}', $index);
        $this->assertStringContainsString('{{ $dmc->name }}', $show);
        $this->assertStringContainsString('{{ $versionModel->html_content }}', $version);
        $this->assertStringContainsString('readonly', $version);
    }

    public function test_super_admin_can_create_itinerary_and_detailed_independently_via_http(): void
    {
        $admin = $this->makeUser(1, 'Super', 1, 1);
        $dmc = $this->makeUser(501, 'DMC', 2, 11);
        Auth::login($admin);

        $this->controller->store($this->draftRequest([
            'html_content' => '<div>itinerary</div>',
            'css_content' => 'body{}',
        ]), (int) $dmc->userId, 'itinerary');

        $this->controller->store($this->draftRequest([
            'html_content' => '<div>detailed</div>',
        ]), (int) $dmc->userId, 'detailed');

        $this->assertSame(2, QuotationTemplate::query()->where('dmc_id', $dmc->userId)->count());
        $this->assertSame(
            [QuotationTemplateVersion::STATUS_DRAFT, QuotationTemplateVersion::STATUS_DRAFT],
            QuotationTemplateVersion::query()->orderBy('id')->pluck('status')->all()
        );
        $this->assertNull(QuotationTemplate::query()->where('dmc_id', $dmc->userId)->first()->active_version_id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function draftRequest(array $data): Request
    {
        return Request::create('/quotation-templates', 'POST', $data);
    }

    private function makeUser(int $userId, string $name, int $userType, int $roleId): User
    {
        DB::table('users')->insert([
            'userId' => $userId,
            'name' => $name,
            'email' => 'user'.$userId.'@example.test',
            'password' => bcrypt('password'),
            'user_type' => $userType,
            'role_id' => $roleId,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->findOrFail($userId);
    }
}
