<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Section;
use App\Models\ClassCard;
use App\Models\Enrollment;


class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::where('user_id', Auth::id())->get();
        return view('subjects.index', compact('subjects'));
    }
    public function chooseSubjects()
    {
        $subjects = Subject::all();

        $sections = Section::all();
        return view('subjects.choose', compact('subjects', 'sections'));
    }

    public function create()
    {
        return view('subjects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subjectExists = Subject::where('course_code', $request->input('course_code'))
            ->where('name', $request->input('name'))
            ->exists();

        if ($subjectExists) {
            return back()->with('error', 'Subject already exists.');
        }

        $subject = new Subject([
            'course_code' => $request->input('course_code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        $subject->user_id = Auth::id();
        $subject->save();

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        return view('subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'course_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($subject->user_id !== Auth::id()) {
            return redirect()->route('subjects.index')->with('error', 'You are not authorized to update this subject.');
        }

        $subjectExists = Subject::where('course_code', $request->course_code) 
            ->where('name', $request->name)
            ->exists();

        // Optional: Check if student is already enrolled
        if ($subjectExists) {
            return redirect()->back()->with('error', 'Subject already exists.');
        }

        $subject->update([
            'course_code' => $request->course_code,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->user_id !== Auth::id()) {
            return redirect()->route('subjects.index')->with('error', 'You are not authorized to delete this subject.');
        }

        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }

    public function showEnroll(Request $request)
    {
        $teacherId = auth()->user()->id;

        $subjectID = $request->input('subject_id');

        $query = Student::where('user_id', $teacherId);
        $students = $query->orderBy('id', 'desc')->get();

        $sections = Section::where('user_id', $teacherId)->get();
        $subject = Subject::where('user_id', Auth::id())->where('id', $subjectID)->first();
        $enrolls = ClassCard::where('subject_id', $subjectID)->get();
        return view('subjects.enroll', compact('students', 'subject', 'sections', 'enrolls'));
    }

    public function enrollStudents(Request $request)
    {
        // Validate the request data
        $request->validate([
            'enroll_type' => 'required|in:single,section', // Ensure valid enroll type
        ]);

        // If the enroll type is 'section', enroll all students in that section
        if ($request->enroll_type === 'section' && $request->section_id) {
            $students = Student::where('section_id', $request->section_id)->get();

            foreach ($students as $student) {
                // Check if the student is already enrolled in the subject
                $enrollmentExists = ClassCard::where('student_id', $student->id)
                    ->where('subject_id', $request->subject_id)
                    ->exists();

                // Only enroll if the student is not already enrolled
                if (!$enrollmentExists) {
                    ClassCard::create([
                        'student_id' => $student->id,
                        'user_id' => Auth::id(),
                        'subject_id' => $request->subject_id,
                        'section_id' => $request->section_id,
                    ]);
                }
            }

            return redirect()->route('subjects.showEnroll', ['subject_id' => $request->subject_id])
                ->with('success', 'All students from the selected section enrolled successfully.');
        }

        // If the enroll type is 'single', enroll the selected student
        if ($request->enroll_type === 'single' && $request->student_id) {
            // Check if the student is already enrolled in the subject
            $enrollmentExists = ClassCard::where('student_id', $request->student_id)
                ->where('subject_id', $request->subject_id)
                ->exists();

            // Optional: Check if student is already enrolled
            if ($enrollmentExists) {
                return redirect()->back()->with('error', 'Student is already enrolled in this subject.');
            }

            // Create the enrollment record
            ClassCard::create([
                'student_id' => $request->student_id,
                'user_id' => Auth::id(),
                'subject_id' => $request->subject_id,
                'section_id' => $request->section_id,
            ]);

            return redirect()->route('subjects.showEnroll', ['subject_id' => $request->subject_id])
                ->with('success', 'Student enrolled successfully.');
        }

        return redirect()->back()->with('error', 'Please select a section or a student.');
    }




    public function unEnrollStudent(ClassCard $enroll)
    {
        if ($enroll->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to unenroll this student.');
        }

        $enroll->delete();
        return redirect()->back()->with('success', 'Student unenrolled successfully.');
    }

    // API
    public function getSubjectApi()
    {
        $subjects = Subject::where('user_id', Auth::id())->get();
        
        return response()->json([
            'success' => true,
            'subjects' => $subjects, // Return the token
        ]);
    }

    public function getSubjectDetailsApi($id)
    {
        $subject = Subject::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found or you are not authorized to view it.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'subject' => $subject,
        ]);
    }

    public function storeSubjectApi(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'course_code' => 'required|string|max:255|unique:subjects,course_code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create a new subject
        $subject = new Subject();
        $subject->course_code = $request->course_code;
        $subject->name = $request->name;
        $subject->description = $request->description;
        $subject->user_id = Auth::id(); // Set the authenticated user ID

        // Save the subject to the database
        $subject->save();

        // Return a success response
        return response()->json(['success' => true, 'subject' => $subject], 201);
    }

    public function updateSubjectDetailsApi(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'course_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Find the subject by ID
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found',
            ], 404);
        }

        // Update the subject fields
        $subject->course_code = $validatedData['course_code'];
        $subject->name = $validatedData['name'];
        $subject->description = $validatedData['description'] ?? $subject->description;

        // Save changes
        $subject->save();

        return response()->json([
            'success' => true,
            'subject' => $subject,
            'message' => 'Subject updated successfully',
        ]);
    }

    public function destroySubjectApi(Subject $subject)
    {
        // Check if the authenticated user is the owner of the subject
        if ($subject->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Delete the subject
        $subject->delete();

        return response()->json(['success' => true, 'message' => 'Subject deleted successfully.'], 200);
    }
}
