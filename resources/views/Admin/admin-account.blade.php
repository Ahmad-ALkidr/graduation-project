@extends('Admin.layouts.app')

@section('title')
    {{ 'admin-account' }}
@endsection


@section('content')
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Account</h4>
        <div class="row">
            <!-- admin Sidebar -->
            <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
                <!-- admin Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="user-avatar-section">
                            <div class="d-flex align-items-center flex-column">
                                <img class="img-fluid rounded mb-3 pt-1 mt-4"
                                    src="@if ($adminData->profile_picture) {{ asset('storage/' . $adminData->profile_picture) }} @else {{ asset('assets/img/avatars/' . ($adminData->gender == 'male' ? '1.png' : '16.png')) }} @endif"
                                    height="130" width="130" alt="User avatar" />
                                <div class="user-info text-center">
                                    <h4 class="mb-2">{{ $adminData->first_name . ' ' . $adminData->last_name }}</h4>
                                    <span
                                        class="badge bg-label-secondary mt-1">{{ ucwords($adminData->role->value) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start me-4 mt-3 gap-2">
                            <span class="badge bg-label-primary p-2 rounded">
                                <i class="ti ti-school ti-sm"></i>
                            </span>
                            <div>
                                <p class="mb-0 fw-medium">{{ $studentCount }}</p>
                                <small>Students Number</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mt-3 gap-2">
                            <span class="badge bg-label-info p-2 rounded"> <i class="ti ti-user-check ti-sm"></i>
                            </span>
                            <div>
                                <p class="mb-0 fw-medium">{{ $academicCount }}</p>
                                <small>Academics Number</small>
                            </div>
                        </div>
                        <p class="mt-4 small text-uppercase text-muted">Details</p>
                        <div class="info-container">
                            <ul class="list-unstyled">
                                <li class="mb-2 pt-1">
                                    <span class="fw-medium me-1">Email:</span>
                                    <span>{{ $adminData->email }}</span>
                                </li>
                                <li class="mb-2 pt-1">
                                    <span class="fw-medium me-1">Status:</span>
                                    @if ($adminData->status == 1)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inactive</span>
                                    @endif
                                </li>
                                <li class="mb-2 pt-1">
                                    <span class="fw-medium me-1">Role:</span>
                                    <span>{{ ucwords($adminData->role->value) }}</span>
                                </li>
                                <li class="mb-2 pt-1">
                                    <span class="fw-medium me-1">Contact:</span>
                                    <span>{{ $adminData->phone == null ? '---' : $adminData->phone }}</span>
                                </li>
                                <li class="mb-2 pt-1">
                                    <span class="fw-medium me-1">Gender:</span>
                                    <span>{{ ucwords($adminData->gender) }}</span>
                                </li>
                                <li class="mb-2 pt-1">
                                    <span class="fw-medium me-1">Created At:</span>
                                    <span>{{ date('Y-m-d', strtotime($adminData->created_at)) }}</span>
                                </li>
                            </ul>
                            <div class="d-flex justify-content-center">
                                <a href="javascript:;" class="btn btn-primary me-3" data-bs-target="#editUser"
                                    data-bs-toggle="modal">Edit</a>

                                {{--                                account delete --}}
                                <a href="{{ route('admin.delete', $adminData->id) }}"
                                    class="btn btn-label-danger suspend-user">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /admin Card -->

            </div>
            <!--/ admin Sidebar -->

            <!-- admin Content -->
            <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">

                <!-- Change Password -->
                <div class="card mb-4">
                    <h5 class="card-header">Change Password</h5>
                    <div class="card-body">
                        <form action="{{ route('admin.change.password') }}" method="post"
                            onsubmit="return validatePassword()">
                            @csrf
                            <input type="hidden" value="{{ $adminData->id }}" name="user_id" autocomplete="off" />

                            <div class="alert alert-warning" role="alert">
                                <h5 class="alert-heading mb-2">Ensure that these requirements are met</h5>
                                <span>Minimum 8 characters long, uppercase & symbol</span>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-12 col-sm-6 form-password-toggle">
                                    <label class="form-label" for="password">New Password</label>
                                    <div class="input-group input-group-merge">
                                        <input class="form-control" type="password" id="password" name="password" />
                                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                    </div>
                                    <span id="newPasswordError" class="text-danger"></span>
                                </div>

                                <div class="mb-3 col-12 col-sm-6 form-password-toggle">
                                    <label class="form-label" for="password_confirmation">Confirm New Password</label>
                                    <div class="input-group input-group-merge">
                                        <input class="form-control" type="password" name="password_confirmation"
                                            id="password_confirmation" />
                                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                    </div>
                                    <span id="confirmPasswordError" class="text-danger"></span>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary me-2">Change Password</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--/ Change Password -->

                <!-- Show My Messages Table -->
                <div class="card mb-4">
                    <div class="table-responsive mb-3">
                        <table class="table datatable-invoice border-top">
                            <p>Show My Messages</p>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th><i class="ti ti-trending-up text-secondary"></i></th>
                                    <th>Total</th>
                                    <th>Issued Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <!-- /Show My Table -->

            </div>
            <!--/ admin Content -->
        </div>

        <!-- Modal -->
        <!-- Edit admin Modal -->
        <div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                <div class="modal-content p-3 p-md-5">
                    <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center mb-4">
                            <h3 class="mb-2">Edit Admin Information</h3>
                            <p class="text-muted">Updating Admin details will receive a privacy audit.</p>
                        </div>
                        <form action="{{ route('admin.edit') }}" method="post" class="row g-3"
                            enctype="multipart/form-data" onsubmit="return true">
                            @csrf
                            <input type="hidden" value="{{ $adminData->id }}" name="id" autocomplete="off" />

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditUserFirstName">First Name</label>
                                <input type="text" id="modalEditUserFirstName" name="first_name" class="form-control"
                                    value="{{ $adminData->first_name }}" required />
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditUserLastName">Last Name</label>
                                <input type="text" id="modalEditUserLastName" name="last_name" class="form-control"
                                    value="{{ $adminData->last_name }}" required />
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditUserEmail">Email</label>
                                <input type="email" id="modalEditUserEmail" name="email" class="form-control"
                                    value="{{ $adminData->email }}" required />
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditUserStatus">Gender</label>
                                <select id="modalEditUserStatus" name="gender" class="select2 form-select"
                                    aria-label="Default select example" required>
                                    <option value="male" @if ($adminData->gender == 'male') selected @endif>Male</option>
                                    <option value="female" @if ($adminData->gender == 'female') selected @endif>Female
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditUserPhone">Birth Date</label>
                                <div class="input-group">
                                    <input type="text" id="modalEditUserDate" name="created_at" class="form-control"
                                        value="{{ date('Y-m-d', strtotime($adminData->created_at)) }}" />
                                </div>

                            </div>
                            {{--                        check Admin --}}
                            @if (Auth::guard('admin')->user()->hasRole('admin'))
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="modalEditUserStatus">Status</label>
                                    <select id="modalEditUserStatus" name="status" class="select2 form-select"
                                        aria-label="Default select example" required>
                                        <option value="1" @if ($adminData->status == 1) selected @endif>Active
                                        </option>
                                        <option value="0" @if ($adminData->status == 0) selected @endif>Inactive
                                        </option>
                                    </select>
                                </div>
                                {{-- <div class="col-12 col-md-6">
                                    <label class="form-label" for="modalEditUserStatus">Role</label>
                                    <select id="modalEditUserStatus" name="role" class="select2 form-select"
                                        aria-label="Default select example" required>
                                        @foreach (app\Enums\RoleEnum::cases() as $role)
                                            <option value="{{ $role->value }}" @selected($adminData->role->value == $role->value)>
                                                {{ ucwords($role->value) }}
                                            </option>
                                        @endForeach
                                    </select>
                                </div> --}}
                            @endif
                            {{--                            Upload new photo --}}
                            <div class="card-body">
                                <div class="d-flex align-items-start align-items-sm-center gap-4">
                                    <img src="{{ $adminData->profile_picture
                                        ? asset('storage/' . $adminData->profile_picture)
                                        : ($adminData->gender == 'male'
                                            ? asset('assets/img/avatars/14.png')
                                            : asset('assets/img/avatars/16.png')) }}"
                                        alt="Admin-avatar" class="d-block w-px-100 h-px-100 rounded" />


                                    <div class="button-wrapper">
                                        <label for="upload" class="btn btn-primary me-2 mb-3" tabindex="0">
                                            <span class="d-none d-sm-block">Upload new photo</span>
                                            <i class="ti ti-upload d-block d-sm-none"></i>
                                            <input type="file" id="upload" class="account-file-input" hidden
                                                name="profile_picture" accept="image/png, image/jpeg" />
                                        </label>
                                        <button type="button" class="btn btn-label-secondary account-image-reset mb-3">
                                            <i class="ti ti-refresh-dot d-block d-sm-none"></i>
                                            <span class="d-none d-sm-block">Reset</span>
                                        </button>

                                        <div class="text-muted">Allowed JPG or PNG. Max size of 800K</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Edit admin Modal -->



        <!-- /Modal -->
    </div>
    <!-- / Content -->
@endsection


@push('scripts')
    {{--    Check Password --}}
    <script>
        function validatePassword() {
            var newPassword = document.getElementById('password').value;
            var confirmPassword = document.getElementById('password_confirmation').value;
            var newPasswordError = document.getElementById('newPasswordError');
            var confirmPasswordError = document.getElementById('confirmPasswordError');

            newPasswordError.textContent = '';
            confirmPasswordError.textContent = '';

            if (newPassword !== confirmPassword) {
                confirmPasswordError.textContent = 'Passwords do not match';
                return false;
            }

            if (newPassword.length < 8) {
                newPasswordError.textContent = 'Password must be at least 8 characters long';
                return false;
            }

            // You can add more validation here, like checking for uppercase, symbols, etc.

            return true;
        }
    </script>

    {{--      Update/reset user image of account page --}}
    <script>
        let accountUserImage = document.getElementById('adminuploadedAvatar');
        const fileInput = document.querySelector('.account-file-input'),
            resetFileInput = document.querySelector('.account-image-reset');

        if (accountUserImage) {
            const resetImage = accountUserImage.src;
            fileInput.onchange = () => {
                if (fileInput.files[0]) {
                    accountUserImage.src = window.URL.createObjectURL(fileInput.files[0]);
                }
            };
            resetFileInput.onclick = () => {
                fileInput.value = '';
                accountUserImage.src = resetImage;
            };
        }
    </script>
@endpush
