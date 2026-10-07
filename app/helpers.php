<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('staff_prefix')) {
    /** "admin" or "head", read from the current route name ("admin", "admin.student.index", ...). */
    function staff_prefix(): string
    {
        $prefix = strtok((string) Route::currentRouteName(), '.');

        if (! in_array($prefix, ['admin', 'head'], true)) {
            throw new LogicException('staff_route() used outside an admin/head route: '.Route::currentRouteName());
        }

        return $prefix;
    }
}

if (! function_exists('staff_route')) {
    /** route() for the current staff area: staff_route('student.index') is admin.student.index or head.student.index. */
    function staff_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(staff_prefix().'.'.$name, $parameters, $absolute);
    }
}
