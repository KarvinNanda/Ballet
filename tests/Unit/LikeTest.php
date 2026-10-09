<?php

namespace Tests\Unit;

use App\Support\Like;
use PHPUnit\Framework\TestCase;

class LikeTest extends TestCase
{
    public function test_plain_text_is_wrapped_in_wildcards(): void
    {
        $this->assertSame('%abc%', Like::contains('abc'));
    }

    public function test_percent_is_escaped(): void
    {
        $this->assertSame('%50\%%', Like::contains('50%'));
    }

    public function test_underscore_is_escaped(): void
    {
        $this->assertSame('%a\_b%', Like::contains('a_b'));
    }

    public function test_backslash_is_doubled(): void
    {
        $this->assertSame('%c:\\\\x%', Like::contains('c:\x'));
    }

    public function test_zero_is_a_valid_term(): void
    {
        $this->assertSame('%0%', Like::contains('0'));
    }
}
