@extends('layouts.app')

@section('title', 'Subjects')

@section('content')
    <style>
        body {
            background-color: #F6F9FF; /* Background color of the page */
        }

        .card {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .table {
            background-color: #ffffff; /* Table background */
        }

        .table th {
            background-color: #E3A833; /* Header background color */
            color: white; /* Header text color */
        }

        .table tbody tr:hover {
            background-color: #f0f0f0; /* Row hover effect */
        }

        .btn-primary {
            background-color: #E3A833; /* Primary button color */
            border-color: #E3A833; /* Border color for primary buttons */
        }

        .btn-danger {
            background-color: #ff4d4d; /* Danger button color */
            border-color: #ff4d4d; /* Border color for danger buttons */
        }
    </style>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">

                <!-- Success message -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @elseif(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card" style="background-color: #fff; border: 1px solid #cddfff;">
                    <div class="card-body">
                        <h5 class="card-title">Subjects</h5>
                        @if(auth()->check() && auth()->user()->user_type === 'admin')
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createSubjectModal">
                            Create Subject
                        </button>
                        @endif
                          @if(auth()->check() && auth()->user()->user_type === 'teacher')
                          <div class="mb-3 mt-3">
                            <!-- Choose Subjects and Enroll Students Buttons Side by Side -->
                            <a href="{{ route('subjects.choose') }}" class="btn btn-primary me-2">
                                Choose Subjects
                            </a>
                        </div>
                          @endif
                          
                        <!-- Button to trigger modal -->
                       
                        <table class="table table-bordered" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Course Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $subject)
                                    <tr>
                                        <td>{{ $subject->id }}</td>
                                        <td>{{ $subject->course_code }}</td>
                                        <td>{{ $subject->name }}</td>
                                        <td>{{ $subject->description }}</td>
                                        <td class="text-center">
                                        @if(auth()->check() && auth()->user()->user_type === 'teacher')
                                        <a href="{{ route('subjects.showEnroll', ['subject_id' => $subject->id]) }}" class="btn btn-primary me-2">
                                        Enroll Students
                                        </a>
                                       
                                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="otherDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Other
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="otherDropdown">

                                                <li><a class="dropdown-item" href="{{ route('attendance.index', ['subject_id' => $subject->id]) }}">Check Attendance</a></li>
                                                <li><a class="dropdown-item" href="{{ route('students.shuffle', ['subject_id' => $subject->id]) }}">Manage Recitation</a></li>
                                                <li><a class="dropdown-item" href="{{ route('class-card.index', ['subject_id' => $subject->id]) }}">Record Grades</a></li>
                                            </ul>
                                            <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Export
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                                <li><a class="dropdown-item" href="{{ route('students.exportPrelim', ['subject_id' => $subject->id]) }}">Export Prelim</a></li>
                                                <li><a class="dropdown-item" href="{{ route('students.exportMidterm', ['subject_id' => $subject->id]) }}">Export Midterm</a></li>
                                                <li><a class="dropdown-item" href="{{ route('students.exportFinals', ['subject_id' => $subject->id]) }}">Export Finals</a></li>
                                            </ul>
                                        @endif
                                        @if(auth()->check() && auth()->user()->user_type === 'admin')
                                        <button type="button" class="btn btn-sm btn-primary" onclick="showEditModal({{ json_encode($subject) }})">Edit</button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="showDeleteModal({{ $subject->id }})">Delete</button>                                          
                                         @endif
                                            
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Create Subject Modal -->
    <div class="modal fade" id="createSubjectModal" tabindex="-1" aria-labelledby="createSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSubjectModalLabel">Create Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form for creating subject -->
                    <form method="POST" action="{{ route('subjects.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="course_code" class="form-label">Course Code</label>
                            <input type="text" class="form-control" id="course_code" name="course_code" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubjectModalLabel">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form for editing subject -->
                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="edit_course_code" class="form-label">Course Code</label>
                            <input type="text" class="form-control" id="edit_course_code" name="course_code" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Subject Modal -->
    <div class="modal fade" id="deleteSubjectModal" tabindex="-1" aria-labelledby="deleteSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSubjectModalLabel">Delete Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this subject?</p>
                    <!-- Form for deleting subject -->
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Include subject-modals.js -->
    <script src="{{ asset('js/subject-modals.js') }}"></script>

@endsection
