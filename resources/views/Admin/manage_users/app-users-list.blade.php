@extends('Admin.layouts.app')

@section('title', 'Users List')

@push('head')
    {{-- Head content remains the same --}}
@endpush

@section('content')
    <div class="card mb-4" style="margin: 25px;">
        <div class="card-header border-bottom" style="display: flex; justify-content: space-between; align-items: baseline;">
            <h4 class="card-title mb-3">Users List</h4>
        </div>
        <div class="container p-2 pt-2">
            <table id="example" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>FULL NAME</th>
                        <th>EMAIL</th>
                        <th>ROLE</th>
                        <th>COLLEGE</th>
                        <th>YEAR</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usersData as $user)
                        <tr>
                            <td>{{ $user->id }}</td>

                            <td>{{ $user->first_name }} {{ $user->last_name }}</td>

                            <td>{{ $user->email }}</td>

                            <td><span class="badge bg-label-primary">{{ $user->role->value }}</span></td>

                            <td>{{ $user->college ?? 'N/A' }}</td>
                            <td>{{ $user->year ?? 'N/A' }}</td>

                            <td class="actions">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('admin.delete', $user) }}" class="text-body text-danger delete-user">
                                        <i class="ti ti-trash ti-sm mx-2"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Scripts content remains the same --}}
@endpush
