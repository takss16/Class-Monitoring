@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    body {
        background-color: #F6F9FF; /* Background color */
    }

    .card {
        background-color: #FFFFFF; /* Card background */
        border: 1px solid #E3A833; /* Optional: border color to match theme */
        border-radius: 8px; /* Rounded corners */
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        height: 100%; /* Ensure cards take full height of the column */
        display: flex; /* Use flex to align items */
        flex-direction: column; /* Stack items vertically */
        justify-content: center; /* Center content vertically */
        align-items: center; /* Center content horizontally */
        padding: 20px; /* Padding inside the card */
    }

    .card h3 {
        font-size: 2em; /* Adjust font size for better visibility */
        margin: 0; /* Remove default margin */
    }

    .card p {
        font-size: 1.2em; /* Slightly larger text for description */
    }

    .container {
        margin-top: 20px; /* Spacing from the top */
    }

    .row {
        margin-bottom: 20px; /* Spacing between rows of cards */
    }
</style>

<div class="container">
    <h2 class="mb-4">Welcome, {{ Auth::user()->name }}!</h2>

    <div class="row text-center">
        <!-- Total Students Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h3>{{ $studentsCount }}</h3>
                    <p>Total Students</p>
                </div>
            </div>
        </div>

        <!-- Total Subjects Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h3>{{ $subjectsCount }}</h3>
                    <p>Total Subjects</p>
                </div>
            </div>
        </div>

        <!-- Total Sections Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h3>{{ $sectionsCount }}</h3>
                    <p>Total Sections</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
