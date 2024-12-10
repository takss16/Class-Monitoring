<style>
    #sidebar {
        background-color: white; /* Set sidebar background to white */
        padding: 10px; /* Adds some padding to the sidebar */
        border-right: 2px solid #E3A833; /* Optional: Adds a border to enhance the design */
    }

    .side-link {
        margin: 10px 0; /* Adds margin for spacing */
    }
    
    .side-link a {
        display: flex; /* Makes icons and text align nicely */
        align-items: center; /* Centers items vertically */
        text-decoration: none; /* Removes underline from links */
        color: #333; /* Dark text color for visibility against white */
        padding: 10px; /* Adds padding for better click area */
        transition: background-color 0.3s; /* Smooth transition for hover effect */
    }

    .side-link a i {
        margin-right: 10px; /* Adds space between icon and text */
        font-size: 1.2em; /* Adjust icon size if needed */
    }

    .side-link a:hover {
        background-color: #E3A833; /* Hover background color */
        color: white; /* Text color on hover for contrast */
        border-radius: 5px; /* Rounded corners */
    }
</style>

<aside id="sidebar" class="sidebar">
    <!-- Dashboard Nav -->
    <div class="side-link">
        <a class="nav-link collapsed" href="{{ route('dashboard') }}">
            <i class="bi bi-house"></i><span> Dashboard</span>
        </a>
    </div>

    <!-- Sections, Subjects, and Manage Students Nav (only for teacher) -->
    @if(auth()->check() && auth()->user()->user_type === 'teacher')
        <div class="side-link">
            <a class="nav-link collapsed" href="{{ route('subjects.index') }}">
                <i class="bi bi-card-text"></i><span>My Subjects</span>
            </a>
        </div>
        <div class="side-link">
            <a class="nav-link collapsed" href="{{ route('sections.index') }}">
                <i class="bi bi-list-ol"></i><span>My Sections</span>
            </a>
        </div>
        <div class="side-link">
            <a class="nav-link collapsed" href="{{ route('students.index') }}">
                <i class="bi bi-people"></i><span> My Students</span>
            </a>
        </div>
        <div class="side-link">
            <a class="nav-link collapsed" href="{{ route('students.group.shuffle') }}">
                <i class="bi bi-people"></i><span> Shuffle Group</span>
            </a>
        </div>
    @endif

    <!-- User Management (only for admin) -->
    @if(auth()->check() && auth()->user()->user_type === 'admin')
    <div class="side-link">
            <a class="nav-link collapsed" href="{{ route('subjects.index') }}">
                <i class="bi bi-card-text"></i><span>Manage Subjects</span>
            </a>
        </div>
        <div class="side-link">
            <a class="nav-link collapsed" href="{{ route('sections.index') }}">
                <i class="bi bi-list-ol"></i><span>Manage Sections</span>
            </a>
        </div>
        <div class="side-link">
            <a class="nav-link collapsed" href="{{ route('admin.students.index') }}">
                <i class="bi bi-people"></i><span> Manage Students</span>
            </a>
        </div>
        <div class="side-link">
            <a class="nav-link collapsed" href="{{ route('users.index') }}">
                <i class="bi bi-people-fill"></i><span>User Management</span>
            </a>
        </div>
        <div class="side-link">
            <a class="nav-link collapsed" href="{{ route('register') }}">
                <i class="bi bi-person-plus-fill"></i><span>Register New User</span>
            </a>
        </div>
    @endif
</aside>
