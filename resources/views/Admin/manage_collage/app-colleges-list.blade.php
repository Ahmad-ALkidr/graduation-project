@extends('Admin.layouts.app')

@section('title', 'Colleges')

@section('content')
    <div class="modal fade" id="addCollegeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Add New College</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.colleges.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <label for="name" class="form-label">College Name</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Enter College Name" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card mb-4" style="margin: 25px;">
        <div class="card-header border-bottom" style="display: flex; justify-content: space-between; align-items: baseline;">
            <h4 class="card-title mb-3">Colleges List</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCollegeModal">
                <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                <span class="d-none d-sm-inline-block">Add New College</span>
            </button>
        </div>
        <div class="container p-2 pt-2">
            <table class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>College Name</th>
                        <th>Created At</th>
                        <th>Time</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($colleges as $college)
                        <tr>
                            <td>{{ $college->id }}</td>
                            <td>{{ $college->name }}</td>
                            <td>{{ $college->created_at->format('Y-m-d') }}</td>
                            <td>{{ $college->created_at->format('h:i A') }}</td>
                            <td>
                                <form action="{{ route('admin.colleges.delete', $college) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-icon btn-danger" data-bs-toggle="tooltip" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No colleges found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
             <div class="mt-3">
                {{ $colleges->links() }}
            </div>
        </div>
    </div>
@endsection
