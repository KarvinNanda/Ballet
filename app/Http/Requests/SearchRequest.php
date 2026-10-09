<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * GET list pages: a crafted filter (array, very long string, unknown status) is dropped instead of rejected,
 * because redirecting "back" from a GET whose Referer is the same bad URL would loop.
 */
class SearchRequest extends FormRequest
{
    private const FREE_TEXT = ['keyword', 'search', 'text'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    /** @return array<string, list<string>> fixed-set query keys and their allowed values */
    protected function choices(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        // Always the query bag: with a JSON Content-Type getInputSource() is the JSON body, not the query string.
        // Views read the global request (request('keyword')) and a FormRequest holds a copy of the bags, so clean both.
        $this->sanitize($this->query);
        if (app('request') !== $this) {
            $this->sanitize(app('request')->query);
        }
    }

    private function sanitize(ParameterBag $source): void
    {
        $all = $source->all();

        foreach (self::FREE_TEXT as $key) {
            if (! array_key_exists($key, $all)) {
                continue;
            }
            $value = $all[$key];
            if (! is_string($value) || trim($value) === '') {
                $source->remove($key);
            } else {
                $source->set($key, mb_substr(trim($value), 0, 100));
            }
        }

        foreach ($this->choices() as $key => $allowed) {
            if (array_key_exists($key, $all) && ! in_array($all[$key], $allowed, true)) {
                $source->remove($key);
            }
        }
    }
}
