<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TemplateBuilderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'templates.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'templates.publish', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['templates.create', 'templates.publish']);
    }

    public function test_template_builder_persists_options_and_conditional_rules(): void
    {
        $response = $this->actingAs($this->user)->post(route('templates.store'), $this->payload('Hemograma'));

        $template = Template::where('name', 'Hemograma')->firstOrFail();
        $response->assertRedirect(route('templates.edit', $template));
        $this->assertSame('grupo_sanguineo', $template->schema[0]['fields'][0]['slug']);
        $this->assertSame('A+', $template->schema[0]['fields'][0]['options'][0]['label']);
        $this->assertSame('show', $template->schema[0]['fields'][1]['rules'][0]['action']);
        $this->assertDatabaseHas('template_field_options', ['label' => 'A+', 'value' => 'a']);
        $this->assertDatabaseHas('template_conditional_rules', ['source_field_slug' => 'grupo_sanguineo', 'operator' => 'equals']);
    }

    public function test_publish_marks_current_version_and_template_as_published(): void
    {
        $this->actingAs($this->user)->post(route('templates.store'), $this->payload('Coagulación'));
        $template = Template::where('name', 'Coagulación')->firstOrFail();

        $this->actingAs($this->user)->post(route('templates.publish', $template))->assertRedirect(route('templates.preview', $template));

        $template->refresh();
        $this->assertTrue($template->isPublished());
        $this->assertSame(1, $template->published_version_number);
        $this->assertDatabaseHas('template_versions', ['template_id' => $template->id, 'version_number' => 1, 'status' => 'published']);
    }

    public function test_update_creates_new_version_for_template(): void
    {
        $this->actingAs($this->user)->post(route('templates.store'), $this->payload('Química'));
        $template = Template::where('name', 'Química')->firstOrFail();

        $this->actingAs($this->user)->put(route('templates.update', $template), $this->payload('Química clínica'))
            ->assertRedirect(route('templates.index'));

        $this->assertSame(2, $template->versions()->count());
        $this->assertDatabaseHas('template_versions', ['template_id' => $template->id, 'version_number' => 2]);
    }

    public function test_published_template_with_clinical_use_cannot_be_edited(): void
    {
        $this->actingAs($this->user)->post(route('templates.store'), $this->payload('Microbiología'));
        $template = Template::where('name', 'Microbiología')->firstOrFail();
        $this->actingAs($this->user)->post(route('templates.publish', $template));
        $area = Area::create(['name' => 'Laboratorio', 'slug' => 'laboratorio', 'status' => true]);
        $service = Service::create(['area_id' => $area->id, 'template_id' => $template->id, 'name' => 'Cultivo', 'price' => 10, 'status' => true]);
        OrderItem::create(['itemable_type' => Service::class, 'itemable_id' => $service->id, 'price' => 10, 'status' => 'pending']);

        $this->actingAs($this->user)->put(route('templates.update', $template), $this->payload('Microbiología editada'))
            ->assertSessionHasErrors('template');
    }

    private function payload(string $name): array
    {
        return [
            'name' => $name,
            'sections' => [[
                'title' => 'Resultados',
                'fields' => [[
                    'label' => 'Grupo sanguíneo', 'type' => 'select', 'column' => 6, 'required' => 1,
                    'options' => [['label' => 'A+', 'value' => 'a'], ['label' => 'O+', 'value' => 'o']],
                ], [
                    'label' => 'Observación', 'type' => 'textarea', 'column' => 6,
                    'rules' => [['source_field_slug' => 'grupo_sanguineo', 'operator' => 'equals', 'comparison_value' => 'a', 'action' => 'show']],
                ]],
            ]],
        ];
    }
}
