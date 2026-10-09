<?php

namespace App\Support;

final class Like
{
    /** '%term%' for a LIKE that matches $term literally (MySQL's default escape is "\"). */
    public static function contains(string $term): string
    {
        return '%'.addcslashes($term, '%_\\').'%';
    }
}
