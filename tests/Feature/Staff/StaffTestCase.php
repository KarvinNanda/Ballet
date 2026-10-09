<?php

namespace Tests\Feature\Staff;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

abstract class StaffTestCase extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::where('role', $role)->firstOrFail();
    }

    /** Safe to call repeatedly in one test: resets session and guards so AuthenticateSession does not log the previous user's successor out. */
    protected function asRole(string $role): static
    {
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        return $this->actingAs($this->user($role));
    }

    /** @return array{0: Student, 1: int} an active student and an active, non-frozen class they are not in yet, with a schedule */
    protected function studentAndFreeClass(): array
    {
        $student = Student::where('Status', 'aktif')->firstOrFail();
        $classId = DB::table('class_transactions')
            ->where('is_freeze', '!=', 1)
            ->whereIn('id', DB::table('schedules')->whereRaw('date >= curdate()')->pluck('class_id'))
            ->whereNotIn('id', DB::table('mapping_class_children')->where('student_id', $student->id)->pluck('class_id'))
            ->value('id');
        $this->assertNotNull($classId, 'seed has no free class for this student');

        return [$student, $classId];
    }

    /** "Course – Teacher" as ClassTransaction::label() builds it, computed independently from the tables. */
    protected function expectedClassLabel(int $classId): string
    {
        $course = DB::table('class_transactions')->join('class_types', 'class_types.id', 'class_transactions.class_type_id')
            ->where('class_transactions.id', $classId)->value('class_types.class_name');
        $teacher = DB::table('mapping_class_teachers')->join('users', 'users.id', 'mapping_class_teachers.user_id')
            ->where('mapping_class_teachers.class_id', $classId)->orderBy('users.id')->value('users.name');

        return ($course ?? 'Class').($teacher ? ' – '.$teacher : '');
    }

    /** The <tr> whose text contains $name. */
    protected function rowFor(string $html, string $name): string
    {
        preg_match_all('/<tr\b.*?<\/tr>/s', $html, $rows);
        foreach ($rows[0] as $row) {
            if (str_contains($row, e($name))) {
                return $row;
            }
        }
        $this->fail("no row for {$name}");
    }

    /** name => value of every control in the form posting to $action, as a browser would submit it unchanged. */
    protected function formFields(string $html, string $action): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($dom);
        $form = $xpath->query('//form[@action="'.$action.'"]')->item(0);
        $this->assertNotNull($form, "no form posting to {$action}");

        $fields = [];
        foreach ($xpath->query('.//input[@name] | .//select[@name] | .//textarea[@name]', $form) as $control) {
            if ($control->nodeName === 'input' && in_array($control->getAttribute('type'), ['checkbox', 'radio'], true) && ! $control->hasAttribute('checked')) {
                continue;
            }
            $fields[$control->getAttribute('name')] = match ($control->nodeName) {
                'textarea' => $control->textContent,
                'select' => ($xpath->query('.//option[@selected]', $control)->item(0) ?? $xpath->query('.//option', $control)->item(0))?->getAttribute('value'),
                default => $control->getAttribute('value'),
            };
        }

        return $fields;
    }
}
