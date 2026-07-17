<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{
    private array $fieldTypes = ['text', 'textarea', 'number', 'date', 'select', 'radio', 'checkbox'];
    private array $operators = ['equals', 'not_equals', 'contains', 'greater_than', 'less_than'];
    private array $actions = ['show', 'hide', 'require'];

    public function index()
    {
        $templates = Template::with('currentVersion')->orderBy('name')->get();
        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        $template = new Template(['schema' => $this->defaultSchema()]);
        return view('admin.templates.create', ['template' => $template, 'schema' => $template->schema]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $schema = $this->normalizeSchema($data['sections']);

        $template = DB::transaction(function () use ($data, $schema) {
            $template = Template::create([
                'code' => Template::uniqueCode($data['name']),
                'name' => $data['name'],
                'schema' => $schema,
                'publication_status' => 'draft',
            ]);
            $this->persistVersion($template, 1, 'draft', $schema);
            return $template;
        });

        return redirect()->route('templates.edit', $template)->with('success', 'Plantilla creada correctamente.');
    }

    public function edit(Template $template)
    {
        if ($template->isPublished() && $template->hasClinicalUse()) {
            return redirect()->route('templates.preview', $template)
                ->with('warning', 'La plantilla publicada ya tiene uso clínico; cree una nueva versión antes de modificarla.');
        }

        return view('admin.templates.edit', ['template' => $template, 'schema' => $template->normalizedSchema()]);
    }

    public function update(Request $request, Template $template)
    {
        if ($template->isPublished() && $template->hasClinicalUse()) {
            return back()->withErrors(['template' => 'No se puede editar una plantilla publicada con uso clínico.']);
        }

        $data = $this->validated($request, $template);
        $schema = $this->normalizeSchema($data['sections']);

        DB::transaction(function () use ($template, $data, $schema) {
            $next = ($template->versions()->max('version_number') ?? 0) + 1;
            $template->update(['name' => $data['name'], 'schema' => $schema, 'publication_status' => 'draft']);
            $this->persistVersion($template, $next, 'draft', $schema);
        });

        return redirect()->route('templates.index')->with('success', 'Plantilla actualizada y versionada.');
    }

    public function publish(Request $request, Template $template)
    {
        DB::transaction(function () use ($request, $template) {
            $version = $template->currentVersion ?: $this->persistVersion($template, 1, 'draft', $template->normalizedSchema());
            $version->update(['status' => 'published', 'published_at' => now(), 'published_by' => $request->user()?->id]);
            $template->update([
                'publication_status' => 'published',
                'published_version_number' => $version->version_number,
                'published_at' => now(),
                'published_by' => $request->user()?->id,
            ]);
        });

        return redirect()->route('templates.preview', $template)->with('success', 'Plantilla publicada correctamente.');
    }

    public function destroy(Template $template)
    {
        if ($template->isPublished() && $template->hasClinicalUse()) {
            return back()->withErrors(['template' => 'No se puede eliminar una plantilla publicada con uso clínico.']);
        }
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Plantilla eliminada.');
    }

    public function preview(Template $template)
    {
        return view('admin.templates.preview', ['template' => $template, 'schema' => $template->normalizedSchema()]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.title' => ['required', 'string', 'max:120'],
            'sections.*.fields' => ['required', 'array', 'min:1'],
            'sections.*.fields.*.label' => ['required', 'string', 'max:120'],
            'sections.*.fields.*.type' => ['required', Rule::in($this->fieldTypes)],
            'sections.*.fields.*.column' => ['required', 'integer', 'min:1', 'max:12'],
            'sections.*.fields.*.required' => ['nullable', 'boolean'],
            'sections.*.fields.*.options' => ['nullable', 'array'],
            'sections.*.fields.*.options.*.label' => ['required_with:sections.*.fields.*.options', 'string', 'max:120'],
            'sections.*.fields.*.rules' => ['nullable', 'array'],
            'sections.*.fields.*.rules.*.source_field_slug' => ['required_with:sections.*.fields.*.rules', 'string', 'max:120'],
            'sections.*.fields.*.rules.*.operator' => ['required_with:sections.*.fields.*.rules', Rule::in($this->operators)],
            'sections.*.fields.*.rules.*.comparison_value' => ['nullable', 'string', 'max:120'],
            'sections.*.fields.*.rules.*.action' => ['required_with:sections.*.fields.*.rules', Rule::in($this->actions)],
        ]);
    }

    private function normalizeSchema(array $sections): array
    {
        return collect($sections)->values()->map(function ($section, $sIndex) {
            $title = $section['title'];
            return [
                'title' => $title,
                'slug' => Str::slug($title, '_'),
                'fields' => collect($section['fields'])->values()->map(function ($field, $fIndex) {
                    $slug = Str::slug($field['label'], '_');
                    return [
                        'label' => $field['label'], 'slug' => $slug, 'type' => $field['type'],
                        'column' => (int) $field['column'], 'required' => (bool) ($field['required'] ?? false),
                        'options' => collect($field['options'] ?? [])->filter(fn ($o) => filled($o['label'] ?? null))->values()->map(fn ($o, $i) => ['label' => $o['label'], 'value' => $o['value'] ?? Str::slug($o['label'], '_')])->all(),
                        'rules' => collect($field['rules'] ?? [])->filter(fn ($r) => filled($r['source_field_slug'] ?? null))->values()->map(fn ($r) => Arr::only($r, ['source_field_slug', 'operator', 'comparison_value', 'action']))->all(),
                    ];
                })->all(),
            ];
        })->all();
    }

    private function persistVersion(Template $template, int $number, string $status, array $schema): TemplateVersion
    {
        $version = $template->versions()->create(['version_number' => $number, 'status' => $status, 'schema_snapshot' => $schema]);
        foreach ($schema as $sIndex => $sectionData) {
            $section = $version->sections()->create(['title' => $sectionData['title'], 'slug' => $sectionData['slug'], 'sort_order' => $sIndex + 1]);
            foreach ($sectionData['fields'] as $fIndex => $fieldData) {
                $field = $section->fields()->create(Arr::except($fieldData, ['options', 'rules']) + ['sort_order' => $fIndex + 1]);
                foreach ($fieldData['options'] ?? [] as $oIndex => $option) $field->options()->create($option + ['sort_order' => $oIndex + 1]);
                foreach ($fieldData['rules'] ?? [] as $rule) $field->rules()->create($rule);
            }
        }
        return $version;
    }

    private function defaultSchema(): array
    {
        return [['title' => 'General', 'slug' => 'general', 'fields' => [['label' => '', 'type' => 'text', 'column' => 12, 'required' => false, 'options' => [], 'rules' => []]]]];
    }
}
