<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Template extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'schema', 'status', 'publication_status', 'published_version_number', 'published_at', 'published_by'];
    protected $casts = ['schema' => 'array', 'status' => 'boolean', 'published_at' => 'datetime'];

    public function services() { return $this->hasMany(Service::class); }
    public function versions() { return $this->hasMany(TemplateVersion::class); }
    public function currentVersion() { return $this->hasOne(TemplateVersion::class)->latestOfMany('version_number'); }
    public function publishedBy() { return $this->belongsTo(User::class, 'published_by'); }

    public function isPublished(): bool { return $this->publication_status === 'published'; }

    public function hasClinicalUse(): bool
    {
        return Service::query()
            ->where('template_id', $this->id)
            ->whereHas('orderItems')
            ->exists();
    }

    public function normalizedSchema(): array
    {
        $schema = $this->schema ?? [];
        if ($schema && array_is_list($schema) && isset($schema[0]['label'])) {
            return [[
                'title' => 'General',
                'slug' => 'general',
                'fields' => $schema,
            ]];
        }
        return $schema;
    }

    public static function uniqueCode(string $name): string
    {
        $base = Str::slug($name) ?: 'plantilla';
        $code = $base;
        $i = 2;
        while (static::where('code', $code)->exists()) {
            $code = $base.'-'.$i++;
        }
        return $code;
    }
}
