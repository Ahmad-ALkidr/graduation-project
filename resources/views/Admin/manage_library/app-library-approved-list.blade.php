@extends('Admin.layouts.app')

@section('title', 'Approved Student Files')

@section('content')
    <div class="card mb-4" style="margin: 25px;">
        <div class="card-header border-bottom">
            <h4 class="card-title mb-3">Approved Student Files</h4>
        </div>
        <div class="container p-2 pt-2">
            <table id="example" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Student</th>
                        <th>Date Approved</th>
                        <th>Time</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($approvedFiles as $file)
                        <tr>
                            <td>{{ $file->id }}</td>
                            <td>{{ $file->title }}</td>
                            <td><span class="badge bg-label-success">{{ $file->type }}</span></td>
                            <td>{{ $file->user->first_name }} {{ $file->user->last_name }}</td>
                            <td>{{ $file->updated_at->format('Y-m-d') }}</td>
                            <td>{{ $file->created_at->format('h:i A') }}</td>
                            <td class="actions">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('admin.library-files.view', $file) }}" target="_blank"
                                        class="btn btn-sm btn-icon btn-secondary me-2" data-bs-toggle="tooltip"
                                        title="View File">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.library-files.download', $file) }}"
                                        class="btn btn-sm btn-icon btn-primary me-2" data-bs-toggle="tooltip"
                                        title="Download File">
                                        <i class="ti ti-download"></i>
                                    </a>
                                    <form action="{{ route('admin.library-files.delete', $file) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-danger"
                                            data-bs-toggle="tooltip" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No approved files found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
