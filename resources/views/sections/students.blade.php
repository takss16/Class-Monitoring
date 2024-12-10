@extends('layouts.app')

@section('title', 'Enroll Students')

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
               
                <div class="card" style="background-color: #fff; border: 1px solid #cddfff;">
                    {{-- <div class="card-header">
                        <h5 class="card-title">Enroll Students - {{ $subject->name }}</h5>
                    </div> --}}
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="text-center mt-4">
                                    <h2>Students in Section: {{ $section->name }}-{{ $section->description}}</h2>
                                </div>
                                    @if($students->isEmpty())
                                        <p>No students are enrolled in this section.</p>
                                    @else
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($students as $student)
                                                    <tr>
                                                        <td>{{ $student->id }}</td>
                                                        <td>{{ $student->first_name }} {{ $student->last_name }}{{ $student->middle_name ? ' ' . $student->middle_name : '' }}</td>
                                                        <td>{{ $student->student_type }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif

                                    <a href="{{ route('sections.index') }}" class="btn btn-secondary">Back to Sections</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
   

@endsection
