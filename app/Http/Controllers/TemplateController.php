<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        // Obtenemos todas para que Alpine las maneje en el cliente
        $templates = Template::orderBy('name')->get();
        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string',
            'fields.*.type' => 'required|string',
            'fields.*.column' => 'required|integer|min:1|max:12',
        ]);

        $schema = collect($request->fields)->map(function($field) {
            return [
                'label' => $field['label'],
                'type' => $field['type'],
                'column' => (int) ($field['column'] ?? 12),
                'slug' => str()->slug($field['label'], '_')
            ];
        })->toArray();

        Template::create([
            'name' => $request->name,
            'schema' => $schema
        ]);

        return redirect()->route('templates.index')->with('success', 'Plantilla creada correctamente.');
    }

    public function edit(Template $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string',
            'fields.*.type' => 'required|string',
            'fields.*.column' => 'required|integer|min:1|max:12',
        ]);

        $schema = collect($request->fields)->map(function($field) {
            return [
                'label' => $field['label'],
                'type' => $field['type'],
                'column' => (int) ($field['column'] ?? 12),
                'slug' => str()->slug($field['label'], '_')
            ];
        })->toArray();

        $template->update([
            'name' => $request->name,
            'schema' => $schema
        ]);

        return redirect()->route('templates.index')->with('success', 'Plantilla actualizada.');
    }

    public function destroy(Template $template)
    {
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Plantilla eliminada.');
    }

    public function preview(Template $template)
    {
        // Aseguramos que el esquema sea un array
        $schema = is_string($template->schema) ? json_decode($template->schema, true) : $template->schema;
        
        return view('admin.templates.preview', compact('template', 'schema'));
    }
}