@extends('admin-dashboard.layouts.app')

@section('title', 'Edit User')


@push('head')
    {{-- quill Editor   --}}
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        #editor {
            min-height: 200px; /* Adjust the height as needed */
        }
    </style>

    <!-- Vendors CSS -->
    {{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">--}}
    <link rel="stylesheet" href="{{asset('assets')}}/vendor/libs/flatpickr/flatpickr.css"/>
    {{--    <link rel="stylesheet" href="{{asset('assets')}}/vendor/libs/tagify/tagify.css"/>--}}

@endpush

@section('content')

    <!-- Content -->
    <div class="app-ecommerce" style="margin: 40px;">

        <!-- Add Blog -->
        <form action="{{route('user.update')}}" class="add-new-blog" method="post"
              id="myForm" enctype="multipart/form-data" onsubmit="return validateForm()">
            @csrf
            @method('PUT')
            <input
                type="hidden"
                value="{{$userData->id}}"
                name="id"
                autocomplete="off"/>
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1 mt-3">Edit User</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-3">
                    <div class="d-flex gap-3">
                        <a href="{{route('user.list')}}" class="btn btn-label-secondary ajax-link">Discard</a>
                    </div>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </div>
            <div class="row">
                <!-- First column-->
                <!-- User Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-tile mb-0">User information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col">
                                        <label class="form-label" for="add-f_name">First Name</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="add-f_name"
                                            placeholder="First Name"
                                            name="f_name"
                                            aria-label="First Name"
                                            value="{{$userData->f_name}}"/>
                                        <div id="f_name-error" class="invalid-feedback">
                                            Please provide a valid First Name.
                                        </div>
                                    </div>
                                    <div class="col">
                                        <label class="form-label" for="add-l_name">Last Name</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="add-l_name"
                                            placeholder="Last Name"
                                            name="l_name"
                                            aria-label="Last Name"
                                            value="{{$userData->l_name}}"/>
                                        <div id="l_name-error" class="invalid-feedback">
                                            Please provide a valid Last Name.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col">
                                        <label class="form-label" for="add-email">Email</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="add-email"
                                            placeholder="Email"
                                            name="email"
                                            aria-label="Email"
                                            value="{{$userData->email}}"/>
                                        <div id="email-error" class="invalid-feedback">
                                            Please provide a valid Email.
                                        </div>
                                    </div>
                                    <div class="col">
                                        <label class="form-label" for="add-password">Password</label>
                                        <input
                                            type="password"
                                            class="form-control"
                                            id="add-password"
                                            placeholder="Password"
                                            name="password"
                                            aria-label="Password"
                                            value="{{$userData->password}}"/>
                                        <div id="password-error" class="invalid-feedback">
                                            Please provide a valid Password.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col">
                                        <label class="form-label" for="add-location">Location</label>
                                        <select name="location" class="form-control">
                                            @foreach(\Modules\Users\Enum\UserLocationEnum::cases() as $location)
                                                <option value="{{ $location->value }}">{{ ucfirst($location->value) }}</option>
                                            @endforeach
                                        </select>
                                        <div id="location-error" class="invalid-feedback">
                                            Please provide a valid Location.
                                        </div>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-control">
                                            @foreach(\Modules\Users\Enum\UserGenderEnum::cases() as $gender)
                                                <option value="{{ $gender->value }}">{{ ucfirst($gender->value) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col">
                                        <label class="form-label" for="add-phone">Phone</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="add-phone"
                                            placeholder="Phone"
                                            name="phone"
                                            aria-label="Phone"
                                            value="{{$userData->phone}}"/>
                                        <div id="phone-error" class="invalid-feedback">
                                            Please provide a valid Phone.
                                        </div>
                                    </div>
                                    <div class="col">
                                        <label class="form-label" for="add-birth-date">Birth Date</label>
                                        <input
                                            type="date"
                                            class="form-control"
                                            id="add-birth-date"
                                            placeholder="Birth Date"
                                            name="birth_date"
                                            aria-label="Birth Date"
                                            value="{{$userData->birth_date}}"/>
                                        <div id="birth-date-error" class="invalid-feedback">
                                            Please provide a valid Birth Date.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                <!-- /User Information -->
            </form>
            <!-- /Second column -->
        </div>
    </div>
    <!-- / Content -->

@endsection

@push('scripts')
    <!-- Vendors JS -->
    <script src="{{asset('assets')}}/vendor/libs/flatpickr/flatpickr.js"></script>
    <!-- Page JS -->
    <script src="{{asset('assets')}}/js/forms-pickers.js"></script>


    <!-- Include the Quill library -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <script>
        // Initialize Quill editor
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Enter the Description',
            modules: {
                toolbar: [
                    [{'header': [1, 2, false]}],
                    ['bold', 'italic', 'underline'],
                    [{'color': []}, {'background': []}], // Add color and background color options
                    [{'list': 'ordered'}, {'list': 'bullet'}],
                    [{'indent': '-1'}, {'indent': '+1'}],
                    [{'direction': 'rtl'}],
                    ['link', 'code-block']
                ]
            }
        });

        // Set initial content for the editor
        var initialContent = `@php echo $userData->description @endphp`;
        quill.clipboard.dangerouslyPasteHTML(initialContent);

        // Form submission handler
        var form = document.getElementById('myForm');
        form.onsubmit = function () {
            // Populate hidden form on submit
            var hiddenContent = document.getElementById('hidden-content');
            hiddenContent.value = quill.root.innerHTML;

            // Optionally, log the form data to the console to check
            console.log(hiddenContent.value);

            // If you want to prevent the form submission to the server for testing, uncomment the next line
            // return false;
        };
    </script>

    {{--inputs validate--}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.querySelector('.add-new-user');

            form.addEventListener('submit', function (event) {
                event.preventDefault(); // Prevent the form from submitting

                var isValid = true;

                // Reset all errors
                form.querySelectorAll('.is-invalid').forEach(function (element) {
                    element.classList.remove('is-invalid');
                });

                // Validate each input/select
                var f_name = document.getElementById('add-f_name');
                if (f_name.value.trim() === '') {
                    f_name.classList.add('is-invalid');
                    document.getElementById('f_name-error').style.display = 'block';
                    isValid = false;
                } else if (f_name.value.length < 5) {
                    f_name.classList.add('is-invalid');
                    document.getElementById('f_name-error').textContent = 'First Name must be at least 5 characters.';
                    isValid = false;
                }

                // writer name
                var l_name = document.getElementById('add-l_name');
                if (l_name.value.trim() === '') {
                    l_name.classList.add('is-invalid');
                    document.getElementById('l_name-error').style.display = 'block';
                    isValid = false;
                }

                // If all inputs are valid, submit the form
                if (isValid) {
                    form.submit();
                }
            });

        });
    </script>
@endpush
