<?php

namespace App\Http\Controllers;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Score;
use Illuminate\Support\Facades\Log;
use App\Models\ClassCard;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function adminIndex(Request $request)
    {
        // Get the currently authenticated user
        $user = auth()->user();
    
        // Fetch the section filter from the request
        $sectionId = $request->input('section_id');
        
        // Start with the query to fetch all students
        $query = Student::query();
        
        // Apply section filter if selected
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }
        
        // Get the filtered or unfiltered list of students, ordered by id in descending order
        $students = $query->orderBy('id', 'desc')->get();
        

        $sections = Section::all(); 
        
        // Return the view with the list of students and sections
        return view('student.index', compact('students', 'sections', 'sectionId'));
    }
    
    
    public function index(Request $request)
{
    // Fetch section and subject filters from the request
    $sectionId = $request->input('section_id');
    $subjectId = $request->input('subject_id');

    // Start with the query to fetch students enrolled in ClassCard
    $query = ClassCard::with('student');

    // Apply the section filter if provided
    if ($sectionId) {
        $query->where('section_id', $sectionId);
    }

    // Apply the subject filter if provided
    if ($subjectId) {
        $query->where('subject_id', $subjectId);
    }

    // Get the ClassCard records and map them to students
    $students = $query->get()
        ->map(function ($classCard) {
            return $classCard->student; // Extract the associated student from each ClassCard
        });

    // Fetch all sections (for both admin and teacher)
    $sections = ClassCard::where('user_id', Auth::id())
        ->whereHas('section') // Ensure there's an associated section
        ->with('section')     // Load the related section
        ->get()
        ->pluck('section')    // Extract sections
        ->unique('id')        // Remove duplicates based on the section ID
        ->values();     

    // Fetch subjects (for both admin and teacher)
    $subjects = Subject::all();

    // Return the view with the list of students, sections, and subjects
    return view('student.index', compact('students', 'sections', 'subjects'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'student_number' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'student_type' => 'nullable|in:regular,irregular', // Add validation for student_type
        ]);

        // Create the student
        $student = Student::create([
            'student_number' => $request->student_number,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'course' => $request->course,
            'section_id' => $request->section_id,
            'user_id' => Auth::id(),
            'student_type' => $request->student_type, // Add student_type to the create method
        ]);


        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        // Validate the incoming request
        $request->validate([
            'student_number' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id', // Validate section exists
            'student_type' => 'nullable|in:regular,irregular', // Add validation for student_type
        ]);

        // Check if the authenticated user is authorized to update the student
        if ($student->user_id !== Auth::id()) {
            return redirect()->route('students.index')->with('error', 'You are not authorized to update this student.');
        }

        // Update the student with the request data
        $student->update([
            'student_number' => $request->student_number,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'course' => $request->course,
            'section_id' => $request->section_id, // Update section
            'student_type' => $request->student_type, // Update student_type
        ]);

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        // Ensure the student being deleted belongs to the currently authenticated user
        if ($student->user_id !== Auth::id()) {
            return redirect()->route('students.index')->with('error', 'You are not authorized to delete this student.');
        }

        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    public function uploadCSV(Request $request)
    {
        // Validate the uploaded CSV file and associated fields
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        // Additional validation for student_type
        $csvData = file($request->file('csv_file')->getRealPath());
        $header = str_getcsv(array_shift($csvData)); // Get the header row
        $validStudentTypes = ['regular', 'irregular'];

        foreach ($csvData as $line) {
            $row = str_getcsv($line);
            $studentData = array_combine($header, $row);
            
            // Validate student_type
            if (!in_array($studentData['student_type'], $validStudentTypes)) {
                return redirect()->route('students.index')->with('error', 'Invalid student type in CSV. Allowed values are: ' . implode(', ', $validStudentTypes));
            }
        }

        // Log a message to confirm that the file upload passed validation
        Log::info('CSV file upload request validated successfully.');

        // Get the authenticated user ID
        $userId = Auth::id();

        // Try importing the CSV and catch any errors that may occur during the import
        try {
            // Import the CSV data using the StudentsImport class
            Excel::import(new StudentsImport($userId, $request->section_id, $request->subject_id), $request->file('csv_file'));

            // Log the success message after a successful import
            Log::info('CSV file processed successfully.');

            // Redirect back to the students index page with a success message
            return redirect()->route('students.index')->with('success', 'CSV uploaded and students imported successfully.');

        } catch (\Exception $e) {
            // Log the error message if an exception occurs
            Log::error('CSV import failed: ' . $e->getMessage());

            // Redirect back to the students index page with an error message
            return redirect()->route('students.index')->with('error', 'An error occurred while uploading the CSV. Please try again.');
        }
    }

    public function exportStudents()
    {
        // Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the headers
        $headers = [
            'Student Number', 'First Name', 'Last Name', 'Middle Name', 
            'Date of Birth (0000(Y)-00(M)-00(D))', 'Gender', 'Course', 'Student Type'
        ];
        $columnIndex = 'A';

        // Add headers to the first row and protect them
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '1', $header);
            $columnIndex++;
        }

        // Auto-size each column based on content
        foreach (range('A', chr(ord('A') + count($headers) - 1)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Protect the entire sheet with an optional password
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword('your_password'); // Optional password

        // Allow editing in the rows below the header (starting from row 2)
        foreach (range('A', chr(ord('A') + count($headers) - 1)) as $col) {
            $sheet->getStyle("{$col}2:{$col}100")->getProtection()->setLocked(
                \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
            );
        }

        // Set the filename
        $fileName = 'studentSheet.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");

        // Save the spreadsheet to the output
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function uploadExcel(Request $request)
    {
        // Validate the uploaded Excel file and associated fields
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx',
            'section_id' => 'required|exists:sections,id',
        ]);

        // Load the spreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('excel_file')->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        // Retrieve student types
        $validStudentTypes = ['regular', 'irregular'];

        // Loop through each row of the spreadsheet (starting from row 2 to skip headers)
        for ($row = 2; $row <= $highestRow; $row++) {
            // Retrieve each cell's value
            $studentNumber = $sheet->getCell("A$row")->getValue();
            $firstName = $sheet->getCell("B$row")->getValue();
            $lastName = $sheet->getCell("C$row")->getValue();
            $middleName = $sheet->getCell("D$row")->getValue();
            $dateOfBirth = $sheet->getCell("E$row")->getValue(); // Assuming this is the date of birth
            $gender = $sheet->getCell("F$row")->getValue();
            $course = $sheet->getCell("G$row")->getValue();
            $studentType = $sheet->getCell("H$row")->getValue();

            // Check if student type is empty or the row is effectively empty
            if (empty($studentNumber) && empty($firstName) && empty($lastName)) {
                break; // Stop processing if the row is empty
            }

            // Validate student type
            if (!empty($studentType) && !in_array(strtolower($studentType), $validStudentTypes)) {
                return redirect()->back()->with('error', "Invalid student type on row $row. Allowed values are: " . implode(', ', $validStudentTypes));
            }

            // Validate and format date of birth
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth)) {
                $dateOfBirthFormatted = (new \DateTime($dateOfBirth))->format('Y-m-d');
            } else {
                // If the date format is invalid, handle the error
                return redirect()->back()->with('error', "Invalid date format on row $row. Expected format is YYYY-MM-DD.");
            }

            // Now proceed to create the student record
            Student::create([
                'student_number' => $studentNumber,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
                'date_of_birth' => $dateOfBirthFormatted,
                'gender' => $gender,
                'course' => $course,
                'student_type' => strtolower($studentType),
                'section_id' => $request->section_id,
                'user_id' => auth()->user()->id,
            ]);
        }

        // Redirect back to the students index page with a success message
        return redirect()->route('students.index')->with('success', 'Excel file uploaded and students imported successfully.');
    }









    public function shuffleStudent(Request $request)
    {
        // Get the authenticated teacher ID
        $teacherId = auth()->user()->id;
        $subjectId = $request->input('subject_id');

        // Fetch sections and subjects for the dropdowns
        $sections = ClassCard::where('user_id', Auth::id())
        ->whereHas('section') // Ensure there's an associated section
        ->with('section')     // Load the related section
        ->get()
        ->pluck('section')    // Extract sections
        ->unique('id')        // Remove duplicates based on the section ID
        ->values();     

        // Initialize variables to hold selected section and other necessary data
        $sectionId = null;
        $classCards = []; // To store relevant class card details for recitation
        $term = null; // Placeholder for the term (adjust based on your requirements)

        // Check if the request is a POST (form submission)
        if ($request->isMethod('post')) {
            $sectionId = $request->input('section_id');

            if($sectionId == "") {
                return redirect()->back()->with('error', 'Please Select a section first!');
            }

            // Fetch students based on the selected subject and section
            $students = ClassCard::with('student')->where('user_id', $teacherId)
                ->when($subjectId, function ($query) use ($subjectId) {
                    return $query->where('subject_id', $subjectId);
                })
                ->when($sectionId, function ($query) use ($sectionId) {
                    return $query->where('section_id', $sectionId);
                })
                ->get();

            // Shuffle the students
            $shuffledStudents = $students->shuffle();

            // Fetch relevant class card information, assuming you want to get the class cards for the selected section and subject
            $classCards = ClassCard::where('user_id', $teacherId)
                ->when($subjectId, function ($query) use ($subjectId) {
                    return $query->where('subject_id', $subjectId);
                })
                ->when($sectionId, function ($query) use ($sectionId) {
                    return $query->where('section_id', $sectionId);
                })
                ->get();

            // Optionally, set the term if it's a part of the request (you can modify how you handle this)
            $term = $request->input('term'); // Assuming you have a term input in your form

            // Return the view with the shuffled students, class card info, and form options
            return view('student.shuffle_student', compact('sections', 'subjectId', 'shuffledStudents', 'classCards', 'term'));
        }

        // If not a POST request, just show the form
        return view('student.shuffle_student', compact('sections', 'subjectId'));
    }


    public function storeRecitation(Request $request)
    {
        // Validate the input data
        $request->validate([
            'term' => 'required|in:1,2,3',
            'scores.*.score' => 'required|numeric|min:0|max:100',
            'scores.*.over_score' => 'required|numeric|min:0|max:100',
            'scores.*.class_card_id' => 'required|exists:class_cards,id',
        ]);
    
        foreach ($request->scores as $studentId => $scoreData) {
            // Validate that over_score is greater than or equal to score
            if ($scoreData['score'] > $scoreData['over_score']) {
                return redirect()->back()->withErrors(['scores.' . $studentId . '.over_score' => 'Over score must be greater than or equal to score.'])->withInput();
            }
    
            // Initialize item number
            $item = 1;
    
            // Find the next available item number
            while (Score::where('student_id', $studentId)
                    ->where('term', $request->term)
                    ->where('type', 3) // Assuming type 3 is for recitation
                    ->where('item', $item)
                    ->exists()) {
                $item++; // Increment item number until a free item number is found
            }
    
            // Save the score
            $recitationScore = new Score();
            $recitationScore->student_id = $studentId; // ID of the student
            $recitationScore->score = $scoreData['score']; // Student's score
            $recitationScore->over_score = $scoreData['over_score']; // Student's over score
            $recitationScore->term = $request->term; // Term selected from the dropdown
            $recitationScore->class_card_id = $scoreData['class_card_id']; // Class card ID for the student
            $recitationScore->type = 3; // Type for recitation
            $recitationScore->item = $item; // Set the next available item number
            $recitationScore->save(); // Save the score record
        }
    
        return redirect()->back()->with('success', 'Recitation scores saved successfully!');
    }
    

    public function groupShuffle(Request $request)
    {
        try {
            // Get the authenticated teacher ID
            $teacherId = auth()->user()->id;

            $sections = ClassCard::where('user_id', Auth::id())
            ->whereHas('section') // Ensure there's an associated section
            ->with('section')     // Load the related section
            ->get()
            ->pluck('section')    // Extract sections
            ->unique('id')        // Remove duplicates based on the section ID
            ->values();     
            // dd($sections);


            $subjects = Subject::where('user_id', $teacherId)->get();

            // Check if the request is a POST (form submission)
            if ($request->isMethod('post')) {
                $subjectId = $request->input('subject_id');
                $sectionId = $request->input('section_id');
                $studentsPerGroup = $request->input('students_per_group');

                // Validate the inputs
                if (empty($subjectId) || empty($sectionId) || empty($studentsPerGroup)) {
                    return redirect()->back()->withErrors(['error' => 'Please fill in all the form fields.']);
                }

                if (!is_numeric($studentsPerGroup)) {
                    return redirect()->back()->withErrors(['error' => 'Number of students per group must be an integer.']);
                }

                // Fetch students based on the selected subject and section
                $students = ClassCard::with('student')->where('user_id', $teacherId)
                    ->when($subjectId, function ($query) use ($subjectId) {
                        return $query->where('subject_id', $subjectId);
                    })
                    ->when($sectionId, function ($query) use ($sectionId) {
                        return $query->where('section_id', $sectionId);
                    })
                    ->get();

                if ($students->count() === 0) {
                    return redirect()->back()->withErrors(['error' => 'No students found for the selected subject and section.']);
                }

                // Shuffle the students
                $shuffledStudents = $students->shuffle();

                // Create groups
                $groups = [];
                foreach ($shuffledStudents as $index => $student) {
                    $groupIndex = floor($index / $studentsPerGroup); // Determine the group index
                    if (!isset($groups[$groupIndex])) {
                        $groups[$groupIndex] = [];
                    }
                    $groups[$groupIndex][] = $student; // Add the student to the corresponding group
                }

                // Return the view with the groups and form options
                return view('student.group_shuffle', compact('sections', 'subjects', 'groups', 'studentsPerGroup', 'subjectId', 'sectionId'));
            }

            // If not a POST request, just show the form
            return view('student.group_shuffle', compact('sections', 'subjects'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getStudentApi(Request $request)
    {
        // Get the currently authenticated teacher's ID
        $teacherId = Auth::id();

        // Fetch the subject and section filter from the request
        // $subjectId = $request->input('subject_id');
        // $sectionId = $request->input('section_id');

        // Start with the query to fetch students related to the teacher
        $query = Student::where('user_id', $teacherId);

        // // Apply subject filter if selected
        // if ($subjectId) {
        //     $query->where('subject_id', $subjectId);
        // }

        // // Apply section filter if selected
        // if ($sectionId) {
        //     $query->where('section_id', $sectionId);
        // }

        // Get the filtered or unfiltered list of students, ordered by id in descending order
        $students = $query->orderBy('id', 'desc')->get();

        // Fetch sections and subjects related to the teacher for the dropdowns
        // $sections = Section::where('user_id', $teacherId)->get();
        // $subjects = Subject::where('user_id', $teacherId)->get();

        // Return the view with the list of students and dropdown data
        // return view('student.index', compact('students', 'sections', 'subjects'));
        return response()->json([
            'success' => true,
            'students' => $students, // Return the token
        ]);
    }

    public function storeStudentApi(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'student_number' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id', // Ensure section_id exists in sections table
            'student_type' => 'nullable|in:regular,irregular', // Validation for student_type
        ]);

        // Create the student
        $student = Student::create([
            'student_number' => $request->student_number,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'course' => $request->course,
            'section_id' => $request->section_id,
            'user_id' => Auth::id(), // Associate the student with the authenticated user
            'student_type' => $request->student_type,
        ]);

        // Return a success response
        return response()->json([
            'success' => true,
            'student' => $student, // Include the newly created student in the response
        ], 201); // 201 Created
    }


}
