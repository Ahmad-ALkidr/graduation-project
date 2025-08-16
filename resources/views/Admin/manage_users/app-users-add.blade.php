@extends('Admin.layouts.app')

@section('title', 'Add New User')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <form action="{{ route('admin.users.admin.store') }}" method="post">
            @csrf
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1 mt-3">Add a new User</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-3">
                    <a href="{{ route('admin.users.list.academics') }}" class="btn btn-label-secondary">Discard</a>
                    <button type="submit" class="btn btn-primary">Publish User</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" placeholder="John" name="first_name" value="{{ old('first_name') }}" required />
                            @error('first_name')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" placeholder="Doe" name="last_name" value="{{ old('last_name') }}" required />
                            @error('last_name')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="user@example.com" name="email" value="{{ old('email') }}" required />
                            @error('email')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" class="form-control" id="password" placeholder="••••••••" name="password" required />
                            @error('password')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="birth_date">Birth Date</label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required />
                            @error('birth_date')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="" disabled selected>Select Gender</option>
                                <option value="male" @selected(old('gender') == 'male')>Male</option>
                                <option value="female" @selected(old('gender') == 'female')>Female</option>
                            </select>
                            @error('gender')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="role">Role</label>
                            <select name="role" id="role-selector" class="form-select" required>
                                <option value="" disabled selected>Select Role</option>
                                <option value="student" @selected(old('role') == 'student')>Student</option>
                                <option value="academic" @selected(old('role') == 'academic')>Academic</option>
                            </select>
                            @error('role')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div id="student-fields" class="row g-3" style="display: none;">
                            <div class="col-md-4">
                                <label class="form-label" for="college">College</label>
                                <input type="text" class="form-control" id="college" name="college" placeholder="e.g., Engineering" value="{{ old('college') }}" />
                                @error('college')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="major">Major</label>
                                <input type="text" class="form-control" id="major" name="major" placeholder="e.g., Software" value="{{ old('major') }}" />
                                @error('major')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="year">Year</label>
                                <input type="number" class="form-control" id="year" name="year" placeholder="e.g., 3" value="{{ old('year') }}" />
                                @error('year')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelector = document.getElementById('role-selector');
        const studentFields = document.getElementById('student-fields');

        function toggleStudentFields() {
            if (roleSelector.value === 'student') {
                studentFields.style.display = 'flex'; // Use flex to match the 'row' class
            } else {
                studentFields.style.display = 'none';
            }
        }

        // Run on page load in case of validation errors
        toggleStudentFields();

        // Run when the role is changed
        roleSelector.addEventListener('change', toggleStudentFields);
    });
</script>
@endpush
