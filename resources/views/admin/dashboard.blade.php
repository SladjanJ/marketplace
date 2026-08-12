@extends('layout')

@section('content')
<h1>Admin dashboard</h1>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>User</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ads as $ad)
                <tr>
                    <td>{{ $ad->title }}</td>
                    <td>{{ $ad->user->name }}</td>
                    <td>{{ $ad->status }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.ads.approve', $ad) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.ads.reject', $ad) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
