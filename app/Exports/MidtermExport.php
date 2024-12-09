<?php

namespace App\Exports;

use App\Models\Scrore;
use App\Models\Student;
use App\Models\ClassCard;
use App\Models\Score;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MidtermExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $teacherId;
    protected $subjectId;

    public function __construct($teacherId, $subjectId = null)
    {
        $this->teacherId = $teacherId;
        $this->subjectId = $subjectId; // Save the subject ID for use in the export
    }

    public function collection()
    {
        $students = \DB::table('students')
        ->join('class_cards', 'students.id', '=', 'class_cards.student_id')
        ->join('sections', 'students.section_id', '=', 'sections.id')
        ->where('sections.user_id', $this->teacherId) // Ensure the section is associated with the teacher
        ->select(
            'students.id',
            'students.student_number',
            'students.first_name',
            'students.middle_name',
            'students.last_name',
            'students.course',
            'sections.name as section_name',
            'class_cards.id as class_card_id'
        )->where('class_cards.subject_id', $this->subjectId)
        ->orderBy('sections.id', 'ASC')
        ->get();

        return $students->map(function ($student) {
            // Fetch scores based on the class card ID
            $classCard = ClassCard::find($student->class_card_id);
            $scores = $classCard 
                ? Score::where('class_card_id', $classCard->id)->get()->groupBy('term') 
                : collect(); // Return an empty collection if no class card found

            // Initialize the 'prelim', 'midterm', and 'finals' terms
            $terms = ['prelim', 'midterm', 'finals'];
            foreach ($terms as $term) {
                $scores->put($term, $scores->get($term, collect())); 
            }

            // Calculate attendance
            $attendancePresent = Attendance::where('subject_id', $this->subjectId)
                ->where('type', 1)
                ->where('status', 1)
                ->where('student_id', $student->id)
                ->count() + 
                Attendance::where('subject_id', $this->subjectId)
                ->where('type', 2)
                ->where('status', 1)
                ->where('student_id', $student->id)
                ->count();

            $attendanceTotal = Attendance::where('subject_id', $this->subjectId)
                ->where('type', 1)
                ->where('student_id', $student->id)
                ->count() + 
                Attendance::where('subject_id', $this->subjectId)
                ->where('type', 2)
                ->where('student_id', $student->id)
                ->count();

            $subjectName = $classCard ? $classCard->subject->name : 'N/A';

            $weight = array(
                'performance_task' => 50,
                'recitation' => 15,
                'quiz' => 25,
                'attendance' => 10,
            );

            // Calculate the Midterm Grade based on scores (You can implement your own logic here)
            $midtermScorePT = $scores->get('midterm', collect())->where('type', 'performance_task')->sum('score');
            $midtermOverScorePT = $scores->get('midterm', collect())->where('type', 'performance_task')->sum('over_score');
            $midtermScoreQuiz = $scores->get('midterm', collect())->where('type', 'quiz')->sum('score');
            $midtermOverScoreQuiz = $scores->get('midterm', collect())->where('type', 'quiz')->sum('over_score');
            $midtermScoreRecitation = $scores->get('midterm', collect())->where('type', 'recitation')->sum('score');
            $midtermOverScoreRecitation = $scores->get('midterm', collect())->where('type', 'recitation')->sum('over_score');

            
            $midtermClassStanding = 
                (((( $midtermOverScorePT == 0 ? 0:$midtermScorePT / $midtermOverScorePT) * 35 + 65) / 100) * $weight['performance_task'] ) +
                (((( $midtermOverScoreQuiz == 0 ? 0:$midtermScoreQuiz / $midtermOverScoreQuiz) * 35 + 65) / 100) * $weight['quiz']) +
                (((( $midtermOverScoreRecitation == 0 ? 0:$midtermScoreRecitation / $midtermOverScoreRecitation) * 35 + 65) / 100) * $weight['recitation']) +
                (((( $attendanceTotal == 0 ? 0:$attendancePresent / $attendanceTotal) * 35 + 65) / 100) * $weight['attendance'])
            ;

            $midtermExamScoreLec = $scores->get('midterm', collect())->where('type', 'lec')->sum('score');
            $midtermExamOverScoreLec = $scores->get('midterm', collect())->where('type', 'lec')->sum('over_score');
            $midtermExamScoreLab = $scores->get('midterm', collect())->where('type', 'lab')->sum('score');
            $midtermExamOverScoreLab = $scores->get('midterm', collect())->where('type', 'lab')->sum('over_score');

            $midtermExam = 
                (((( $midtermExamOverScoreLec == 0 ? 0:$midtermExamScoreLec / $midtermExamOverScoreLec) * 35 + 65) / 100) * 50) +
                (((( $midtermExamOverScoreLab == 0 ? 0:$midtermExamScoreLab / $midtermExamOverScoreLab) * 35 + 65) / 100) * 50)
            ;

            return [
                'Student Number' => $student->student_number,
                'First Name' => $student->first_name,
                'Middle Name' => $student->middle_name,
                'Last Name' => $student->last_name,
                'Course' => $student->course,
                'Section' => $student->section_name,
                'Subject' => $subjectName,
                'Grade' => number_format(($midtermExam * 0.6) + ($midtermClassStanding * 0.4), 2),
                // Add other necessary fields here
            ];
        });
    }

    protected function calculateScoreSum($scores, $type, $term, $look)
    {
        return $scores->where('type', $type)->where('term', $term)->sum($look);
    }

    public function headings(): array
    {
        return [
            'StudentNumber',
            'First Name',
            'Middle Name',
            'Last Name',
            'Course',
            'Section',
            'Subject',
            'Grade'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Apply styles to the sheet
        return [
            // Style the first row as bold (headings)
            1 => ['font' => ['bold' => true]],
            // Additional styling can be added here
        ];
    }

    public function title(): string
    {
        return 'Midterm Grade'; // Custom sheet name
    }
}
