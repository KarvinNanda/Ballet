<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rules extends Model
{
    use HasFactory;

    /** Placeholder for the studio logo inside rule HTML (raw, or URL-encoded by the sanitizer). */
    private const LOGO_PLACEHOLDERS = ['{{asset_url}}', '%7B%7Basset_url%7D%7D'];

    /** Content ready for {!! !!}: logo placeholder filled in, then sanitized (also cleans rows saved before sanitizing). */
    protected function safeContent(): Attribute
    {
        return Attribute::get(fn () => app(HtmlSanitizer::class)->clean(
            str_replace(self::LOGO_PLACEHOLDERS, asset('assets/img/logo-hitam.png'), (string) $this->content)
        ));
    }
}
