<?php

namespace App\Http\Controllers;
use App\Models\Student;
use App\Models\ClassCard;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        // Fetch students or any necessary data
        $teacherId = auth()->user()->id;
        $subjectId = $request->input('subject_id');

        $students = Student::where('user_id', $teacherId)->orderBy('id')->get();

        $enrolledStudents = ClassCard::with('student', 'subject')
        ->where('subject_id', $subjectId)
        ->whereHas('student', function ($query) use ($teacherId) {
            $query->where('user_id', $teacherId);
        })
        ->get();

        // If no enrolled students are found, redirect back with an error message
        if ($enrolledStudents->isEmpty()) {
            return redirect()->back()->with('error', 'There are no enrolled students for this subject yet.');
        }

        $student_id = $request->input('student_id') ?? $enrolledStudents[0]->student->id;

        $student = $students->find($student_id);
        if (!$student) {
            return redirect()->route('class-card.index')->with('error', 'Student not found.');
        }

        // Get all student IDs that belong to the teacher
        $studentIds = $enrolledStudents->pluck('student.id')->toArray();

        $subjectName = Subject::find($subjectId)->name;

        $sections = Section::where('user_id', $teacherId)->get();

        // Determine previous and next student IDs
        $currentIndex = array_search($student_id, $studentIds);
        $prevStudentId = $currentIndex > 0 ? $studentIds[$currentIndex - 1] : null;
        $nextStudentId = $currentIndex < count($studentIds) - 1 ? $studentIds[$currentIndex + 1] : null;

        // Fetch attendance records for the student
        $attendanceRecords = Attendance::where('student_id', $student_id)->where('type', 1)->get(); // For lectures
        $labAttendanceRecords = Attendance::where('student_id', $student_id)->where('type', 2)->get(); // For labs
        // return $labAttendanceRecords;
        return view('attendance.index', compact('student', 'enrolledStudents', 'prevStudentId', 'nextStudentId', 'attendanceRecords', 'labAttendanceRecords', 'sections', 'subjectId', 'subjectName', 'studentIds'));
    }


    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'student_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'section_id' => 'required|integer',
            'day' => 'required|integer',
            'attendance_date' => 'required|integer', // Ensure this matches the request
            'type' => 'required|integer',
            'status' => 'required|integer',
        ]);

        // Create or update attendance logic
        try {
            Attendance::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'subject_id' => $request->subject_id,
                    'section_id' => $request->section_id,
                    'day' => $request->day,
                    'attendance_date' => $request->attendance_date,
                    'type' => $request->type,
                ],
                [
                    'status' => $request->status,
                ]
            );

            return response()->json(['success' => 'Attendance recorded successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            // Find and delete the attendance record based on the provided criteria
            Attendance::where('student_id', $request->student_id)
                ->where('subject_id', $request->subject_id)
                ->where('section_id', $request->section_id)
                ->where('day', $request->day)
                ->where('attendance_date', $request->attendance_date)
                ->where('type', $request->type)
                ->delete();

            return response()->json(['success' => 'Attendance deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    
}
