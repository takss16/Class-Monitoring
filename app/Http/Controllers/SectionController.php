<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Models\ClassCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{

    public function showStudents($sectionId)
    {
        // Get the authenticated user
        $user = auth()->user();
    
        // Fetch the section
        $section = Section::findOrFail($sectionId);
    
        // Fetch students enrolled in the section through ClassCard
        $students = ClassCard::with('student')
            ->where('section_id', $sectionId)
            ->when($user->user_type === 'teacher', function ($query) use ($user) {
                return $query->where('user_id', $user->id); // Filter by authenticated user's ID for teachers
            })
            ->get()
            ->map(function ($classCard) {
                return $classCard->student; // Extract the associated student
            });
            
    
        // Pass data to the view
        return view('sections.students', compact('section', 'students'));
    }
    
    


    public function index()
{
    if (auth()->user()->user_type == 'admin') {
        $sections = Section::all();
    } else {
        $sections = ClassCard::where('user_id', Auth::id())
        ->whereHas('section') // Ensure there's an associated section
        ->with('section')     // Load the related section
        ->get()
        ->pluck('section')    // Extract sections
        ->unique('id')        // Remove duplicates based on the section ID
        ->values();           // Reindex the collection (optional, for clean output)

    }
    
  
    return view('sections.index', compact('sections'));
}

    // public function index()
    // {   
    //     $sections = Section::all();
    //     return view('sections.index', compact('sections'));
    // }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $sectionExists = Section::where('name', $request->name)->where('description', $request->description)->where('user_id', Auth::id())
            ->exists();

        // Optional: Check if student is already enrolled
        if ($sectionExists) {
            return redirect()->back()->with('error', 'Section already exists.');
        }

        Section::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('sections.index')->with('success', 'Section created successfully.');
    }

    // Method to update an existing section
    public function update(Request $request, Section $section)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
       
        $sectionExists = Section::where('name', $request->name)->where('description', $request->description)->where('user_id', Auth::id())
        ->exists();
        // Optional: Check if student is already enrolled
        if ($sectionExists) {
            return redirect()->back()->with('error', 'Section already exists.');
        }

        $section->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('sections.index')->with('success', 'Section updated successfully.');
    }

    // Method to delete a section
    public function destroy(Section $section)
    {
        // Ensure the section belongs to the authenticated user
        if ($section->user_id != Auth::id()) {
            return redirect()->route('sections.index')->with('error', 'You are not authorized to delete this section.');
        }

        $section->delete();

        return redirect()->route('sections.index')->with('success', 'Section deleted successfully.');
    }


    // API
    public function getSectionApi()
    {   
        $sections = Section::where('user_id', Auth::id())->get();

        return response()->json([
            'success' => true,
            'sections' => $sections,
        ]);
    }

    public function getSectionDetailsApi($id)
    {
        $section = Section::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found or you are not authorized to view it.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'section' => $section,
        ]);
    }

    public function storeSectionApi(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create a new subject
        $section = new Section();
        $section->name = $request->name;
        $section->description = $request->description;
        $section->user_id = Auth::id(); // Set the authenticated user ID

        // Save the subject to the database
        $section->save();

        // Return a success response
        return response()->json(['success' => true, 'section' => $section], 201);
    }

    public function updateSectionDetailsApi(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Find the subject by ID
        $section = Section::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        // Update the subject fields
        $section->name = $validatedData['name'];
        $section->description = $validatedData['description'] ?? $section->description;

        // Save changes
        $section->save();

        return response()->json([
            'success' => true,
            'section' => $section,
            'message' => 'Section updated successfully',
        ]);
    }

    public function destroySectionApi(Section $section)
    {
        // Check if the authenticated user is the owner of the subject
        if ($section->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Delete the subject
        $section->delete();

        return response()->json(['success' => true, 'message' => 'Section deleted successfully.'], 200);
    }

}
