@extends('layouts.app')

@section('title', 'Sections')

@section('content')
    <style>
        body {
            background-color: #F6F9FF;
        }

        .card {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .table {
            background-color: #ffffff; /* Card background */
        }

        .table th {
            background-color: #E3A833; /* Header background color */
            color: white; /* Text color for header */
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
                        <h5 class="card-title text-center" style="color: #012970;">Sections</h5>
                        <!-- Button to trigger create modal -->
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createSectionModal">
                            Create Section
                        </button>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                   
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sections as $section)
                                    <tr>
                                        <td>{{ $section->id }}</td>
                                        <td>{{ $section->name }}-{{ $section->description}}</td>
                                        
                                        <td>
                                            <div class="text-center">
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-sm btn-primary" onclick="showEditModal({{ $section }})">
                                                Edit
                                            </button>
                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-sm btn-danger" onclick="showDeleteModal({{ $section->id }})">
                                                Delete
                                            </button>
                                            </div>
                                           
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

    <!-- Create Section Modal -->
    <div class="modal fade" id="createSectionModal" tabindex="-1" aria-labelledby="createSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSectionModalLabel">Create Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form for creating section -->
                    <form method="POST" action="{{ route('sections.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Year Level</label>
                            <select class="form-control" id="name" name="name" required>
                                <option value="">Select Year Level</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Section</label>
                            <select class="form-control" id="description" name="description" required>
                                <option value="">Select Section</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                                <option value="F">F</option>
                                <option value="G">G</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Section Modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1" aria-labelledby="editSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSectionModalLabel">Edit Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form for editing section -->
                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Year Level</label>
                            <select class="form-control" id="edit_name" name="edit_name" required>
                                <option value="">Select Year Level</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Section</label>
                            <select class="form-control" id="edit_description" name="edit_description" required>
                                <option value="">Select Section</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                                <option value="F">F</option>
                                <option value="G">G</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Section Modal -->
    <div class="modal fade" id="deleteSectionModal" tabindex="-1" aria-labelledby="deleteSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSectionModalLabel">Delete Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this section?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Function to show the Edit Modal and populate its fields
function showEditModal(section) {
    // Set the form's action URL dynamically
    const editForm = document.getElementById('editForm');
    editForm.action = `/sections/${section.id}`;

    // Populate the Year Level and Section fields
    document.getElementById('edit_name').value = section.name; // Adjust 'name' based on your data model
    document.getElementById('edit_description').value = section.description; // Adjust 'description' based on your data model

    // Show the modal
    const editModal = new bootstrap.Modal(document.getElementById('editSectionModal'));
    editModal.show();
}

// Function to show the Delete Modal and set the form's action URL
function showDeleteModal(sectionId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/sections/${sectionId}`;

    // Show the modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteSectionModal'));
    deleteModal.show();
}

    </script>

    <script src="{{ asset('js/section-modal.js') }}"></script>
@endsection
