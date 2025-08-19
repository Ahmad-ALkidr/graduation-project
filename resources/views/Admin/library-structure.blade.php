@extends('Admin.layouts.app')

@section('title', 'Library Structure Management')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y"
     x-data="{
        library: {{ Js::from($libraryTree) }},
        selectedCollegeId: null,
        selectedDepartmentId: null,
        selectedYear: null,
        selectedSemester: null,
        selectedSubjectId: null,

        init() {
            this.selectedCollegeId = parseInt(sessionStorage.getItem('selectedCollegeId')) || null;
            this.selectedDepartmentId = parseInt(sessionStorage.getItem('selectedDepartmentId')) || null;
            this.selectedYear = parseInt(sessionStorage.getItem('selectedYear')) || null;
            this.selectedSemester = sessionStorage.getItem('selectedSemester') || null;
            this.selectedSubjectId = parseInt(sessionStorage.getItem('selectedSubjectId')) || null;
        },

        get selectedCollege() { return this.library.find(c => c.id === this.selectedCollegeId); },
        get selectedDepartment() { return this.selectedCollege?.departments.find(d => d.id === this.selectedDepartmentId); },
        get selectedYearData() { return this.selectedDepartment?.years.find(y => y.year === this.selectedYear); },
        get selectedSemesterData() { return this.selectedYearData?.semesters.find(s => s.semester === this.selectedSemester); },
        get selectedSubject() { return this.selectedSemesterData?.subjects.find(s => s.id === this.selectedSubjectId); }
     }"
     x-init="init()">

    <!-- Horizontal Scroll Container -->
    <div class="horizontal-scroll-container" style="overflow-x: auto; white-space: nowrap; padding-bottom: 15px;">
        <div class="d-inline-flex align-items-start gap-4">

            <!-- Colleges Column -->
            <div class="card h-100" style="width: 250px; display: inline-block; vertical-align: top;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Colleges</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCollegeModal">Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                            <template x-for="college in library" :key="college.id">
                                <tr @click="
                                        selectedCollegeId = college.id; sessionStorage.setItem('selectedCollegeId', college.id);
                                        selectedDepartmentId = null; sessionStorage.removeItem('selectedDepartmentId');
                                        selectedYear = null; sessionStorage.removeItem('selectedYear');
                                        selectedSemester = null; sessionStorage.removeItem('selectedSemester');
                                        selectedSubjectId = null; sessionStorage.removeItem('selectedSubjectId');
                                    "
                                    :class="{'table-primary': selectedCollegeId === college.id}">
                                    <td x-text="college.name"></td>
                                    <td><i class="ti ti-chevron-right"></i></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Departments Column -->
            <div class="card h-100" style="width: 250px; display: inline-block; vertical-align: top;" x-show="selectedCollegeId" x-transition>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Departments</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                            <template x-for="department in selectedCollege?.departments" :key="department.id">
                                <tr @click="
                                        selectedDepartmentId = department.id; sessionStorage.setItem('selectedDepartmentId', department.id);
                                        selectedYear = null; sessionStorage.removeItem('selectedYear');
                                        selectedSemester = null; sessionStorage.removeItem('selectedSemester');
                                        selectedSubjectId = null; sessionStorage.removeItem('selectedSubjectId');
                                    "
                                    :class="{'table-primary': selectedDepartmentId === department.id}">
                                    <td x-text="department.name"></td>
                                    <td><i class="ti ti-chevron-right"></i></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Years Column -->
            <div class="card h-100" style="width: 250px; display: inline-block; vertical-align: top;" x-show="selectedDepartmentId" x-transition>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Years</h5>
                </div>
                 <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                            <template x-for="year in selectedDepartment?.years" :key="year.year">
                                <tr @click="
                                        selectedYear = year.year; sessionStorage.setItem('selectedYear', year.year);
                                        selectedSemester = null; sessionStorage.removeItem('selectedSemester');
                                        selectedSubjectId = null; sessionStorage.removeItem('selectedSubjectId');
                                    "
                                    :class="{'table-primary': selectedYear === year.year}">
                                    <td x-text="'Year ' + year.year"></td>
                                    <td><i class="ti ti-chevron-right"></i></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Semesters Column -->
            <div class="card h-100" style="width: 250px; display: inline-block; vertical-align: top;" x-show="selectedYear" x-transition>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Semesters</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                            <template x-for="semester in selectedYearData?.semesters" :key="semester.semester">
                                <tr @click="
                                        selectedSemester = semester.semester; sessionStorage.setItem('selectedSemester', semester.semester);
                                        selectedSubjectId = null; sessionStorage.removeItem('selectedSubjectId');
                                    "
                                    :class="{'table-primary': selectedSemester === semester.semester}">
                                    <td x-text="semester.semester + ' Semester'"></td>
                                    <td><i class="ti ti-chevron-right"></i></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Subjects Column -->
            <div class="card h-100" style="width: 250px; display: inline-block; vertical-align: top;" x-show="selectedSemester" x-transition>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Subjects</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal">Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                            <template x-for="subject in selectedSemesterData?.subjects" :key="subject.id">
                                <tr @click="
                                        selectedSubjectId = subject.id; sessionStorage.setItem('selectedSubjectId', subject.id);
                                    "
                                    :class="{'table-primary': selectedSubjectId === subject.id}">
                                    <td x-text="subject.name"></td>
                                    <td><i class="ti ti-chevron-right"></i></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Materials Column -->
            <div class="card h-100" style="width: 250px; display: inline-block; vertical-align: top;" x-show="selectedSubjectId" x-transition>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Materials</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMaterialModal">Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                            <template x-for="material in selectedSubject?.materials" :key="material.id">
                                <tr>
                                    <td x-text="material.title"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- All Modals -->
    <!-- Add College Modal -->
    <div class="modal fade" id="addCollegeModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.library.structure.colleges.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Add New College</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <label for="name" class="form-label">College Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.library.structure.departments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="college_id" :value="selectedCollegeId">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Add New Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <label for="dept_name" class="form-label">Department Name</label>
                        <input type="text" class="form-control" name="name" id="dept_name" required>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Subject/Course Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.library.structure.subjects.store') }}" method="POST">
                @csrf
                <input type="hidden" name="department_id" :value="selectedDepartmentId">
                <input type="hidden" name="year" :value="selectedYear">
                <input type="hidden" name="semester" :value="selectedSemester">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Add New Subject</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" name="name" id="subject_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="academic_id" class="form-label">Assign to Academic</label>
                            <select name="academic_id" id="academic_id" class="form-select" required>
                                <option value="" disabled selected>Select an Academic</option>
                                @foreach(\App\Models\User::where('role', \App\Enums\RoleEnum::ACADEMIC)->get() as $academic)
                                    <option value="{{ $academic->id }}">{{ $academic->first_name }} {{ $academic->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Material Modal -->
    <div class="modal fade" id="addMaterialModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.library.structure.materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="subject_id" :value="selectedSubjectId">
                <input type="hidden" name="department_id" :value="selectedDepartmentId">
                <input type="hidden" name="year" :value="selectedYear">
                <input type="hidden" name="semester" :value="selectedSemester">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Add New Material</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="material_title" class="form-label">Material Title</label>
                            <input type="text" class="form-control" name="title" id="material_title" required>
                        </div>
                        <div class="mb-3">
                            <label for="material_file" class="form-label">File (PDF, DOC, etc.)</label>
                            <input type="file" class="form-control" name="file" id="material_file" required>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
