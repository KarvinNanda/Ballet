<?php

namespace Tests\Feature\Staff;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Tests\Feature\Teacher\TeacherTestCase;

/** The class attendance PDF: one row per student who appears on any session, one cell per session date. */
class ClassAttendanceReportTest extends TeacherTestCase
{
    /** Renders the HTML the controller hands to dompdf instead of building a PDF. */
    private function reportHtml(int $classId, string $teacher): string
    {
        $html = null;
        Pdf::shouldReceive('loadView')->once()->andReturnUsing(function (string $view, array $data) use (&$html) {
            $html = view($view, $data)->render();

            // A real (unmocked) wrapper with a tiny document: the controller still calls setPaper()/stream() on it.
            return app()->make(\Barryvdh\DomPDF\PDF::class)->loadHTML('<p>report</p>');
        });

        $this->asRole('head')->post(route('head.report.class.print', ['header' => $classId, 'teacher' => $teacher]))->assertOk();

        return (string) $html;
    }

    /** @return list<list<string>> body rows as lists of trimmed cell texts */
    private function bodyRows(string $html): array
    {
        preg_match('/<tbody>(.*?)<\/tbody>/s', $html, $body);
        preg_match_all('/<tr>(.*?)<\/tr>/s', $body[1] ?? '', $rows);

        return array_map(function (string $row) {
            preg_match_all('/<td>(.*?)<\/td>/s', $row, $cells);

            return array_map(fn ($c) => trim(html_entity_decode($c)), $cells[1]);
        }, $rows[1]);
    }

    public function test_every_row_has_one_cell_per_session_and_late_joiners_are_listed(): void
    {
        $teacher = $this->newTeacher('Report Teacher');
        $classId = $this->classFor($teacher, 'Grade 1', 0);
        [$ana, $budi, $citra] = [
            Student::factory()->create(['LongName' => 'Ana Report']),
            Student::factory()->create(['LongName' => 'Budi Report']),
            Student::factory()->create(['LongName' => 'Citra Report']),
        ];
        $first = $this->sessionAt($classId, '2026-09-09 16:00:00');
        $second = $this->sessionAt($classId, '2026-09-16 16:00:00');
        // Ana both days, Budi only the first, Citra joined later (second day only, as Sick).
        $this->recordRaw($first, [$ana->id => ['Attend', null], $budi->id => ['Absent', null]]);
        $this->recordRaw($second, [$ana->id => ['Permission', null], $citra->id => ['Sick', null]]);

        $html = $this->reportHtml($classId, 'Report Teacher');

        $this->assertSame([
            ['1', 'Ana Report', 'V', 'I'],
            ['2', 'Budi Report', 'A', ''],
            ['3', 'Citra Report', '', 'S'],
        ], $this->bodyRows($html));
        $this->assertStringContainsString('<th>09 Sep 2026</th>', $html);
        $this->assertStringContainsString('<th>16 Sep 2026</th>', $html);
    }

    public function test_legacy_indonesian_descriptions_get_their_letters(): void
    {
        $teacher = $this->newTeacher('Legacy Teacher');
        $classId = $this->classFor($teacher, 'Grade 1', 0);
        $dewi = Student::factory()->create(['LongName' => 'Dewi Legacy']);
        $eka = Student::factory()->create(['LongName' => 'Eka Legacy']);
        $session = $this->sessionAt($classId, '2026-09-09 16:00:00');
        $this->recordRaw($session, [$dewi->id => ['Sakit', null], $eka->id => ['Izin', null]]);

        $this->assertSame([['1', 'Dewi Legacy', 'S'], ['2', 'Eka Legacy', 'I']], $this->bodyRows($this->reportHtml($classId, 'Legacy Teacher')));
    }
}
