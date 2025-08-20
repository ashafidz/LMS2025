<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ModuleRecapExport implements FromView, ShouldAutoSize
{
    protected $students;
    protected $gradableLessons;
    protected $scores;
    protected $module;

    public function __construct($students, $gradableLessons, $scores, $module)
    {
        $this->students = $students;
        $this->gradableLessons = $gradableLessons;
        $this->scores = $scores;
        $this->module = $module;
    }

    public function view(): View
    {
        return view('instructor.recap.partials._recap_table', [
            'students' => $this->students,
            'gradableLessons' => $this->gradableLessons,
            'scores' => $this->scores,
            'module' => $this->module,
        ]);
    }
}
