@extends('Admin.layouts.app')

@section('title', 'Subjects')

@section('content')
    <!-- Add New Subject Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1" aria-hidden="true"
         x-data="{ selectedCollegeId: '' }">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.subjects.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Subject Name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="e.g., Programming 1" required>
                        </div>
                        <div class="mb-3">
                            <label for="academic_id" class="form-label">Assign to Academic</label>
                            <select id="academic_id" name="academic_id" class="form-select" required>
                                <option value="" disabled selected>Select an Academic</option>
                                @foreach($academics as $academic)
                                    <option value="{{ $academic->id }}">{{ $academic->first_name }} {{ $academic->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- College Dropdown -->
                        <div class="mb-3">
                            <label for="college_id" class="form-label">College</label>
                            <select id="college_id" class="form-select" x-model="selectedCollegeId" required>
                                <!-- The value is now an empty string to match the initial state -->
                                <option value="" disabled selected>Select a College</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dynamic Department Dropdown -->
                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select id="department_id" name="department_id" class="form-select" required :disabled="!selectedCollegeId">
                                <option value="" disabled selected>Select a Department</option>
                                @foreach($departments as $department)
                                    <template x-if="selectedCollegeId == {{ $department->college_id }}">
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    </template>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <label for="year" class="form-label">Year</label>
                                <input type="number" id="year" name="year" class="form-control" min="1" max="6" placeholder="e.g., 1" required>
                            </div>
                            <div class="col-6">
                                <label for="semester" class="form-label">Semester</label>
                                <!-- Semester values are now 1 and 2 -->
                                <select id="semester" name="semester" class="form-select" required>
                                    <option value="1">First</option>
                                    <option value="2">Second</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/ Add New Subject Modal -->

    <div class="card mb-4" style="margin: 25px;">
        <div class="card-header border-bottom" style="display: flex; justify-content: space-between; align-items: baseline;">
            <h4 class="card-title mb-3">Subjects List</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                <span class="d-none d-sm-inline-block">Add New Subject</span>
            </button>
        </div>
        <div class="container p-2 pt-2">
            <table class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Academic</th>
                        <th>Department</th>
                        <th>Year</th>
                        <th>Semester</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjects as $subject)
                        @foreach ($subject->courses as $course)
                            <tr>
                                <td>{{ $subject->name }}</td>
                                <td>{{ $subject->academic->first_name }} {{ $subject->academic->last_name }}</td>
                                <td>{{ $course->department->name }}</td>
                                <td>{{ $course->year }}</td>
                                <!-- Display semester correctly -->
                                <td>{{ $course->semester == 1 ? 'First' : 'Second' }}</td>
                                <td>
                                    <form action="{{ route('admin.subjects.delete', $subject) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-danger" data-bs-toggle="tooltip" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No subjects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
             <div class="mt-3">
                {{ $subjects->links() }}
            </div>
        </div>
    </div>
@endsection
