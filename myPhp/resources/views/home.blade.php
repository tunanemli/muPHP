@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
    <div class="container">
        <h1>{{ $title }}</h1>
        
        @if($users->count() > 0)
            <ul>
                @foreach($users as $user)
                    <li>{{ $user->name }} - {{ $user->email }}</li>
                @endforeach
            </ul>
        @else
            <p>No users found.</p>
        @endif
        
        @auth
            <form method="POST" action="/logout">
                @csrf
                @method('POST')
                <button type="submit">Logout</button>
            </form>
        @else
            <a href="/login">Login</a>
        @endauth
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="/css/home.css">
@endpush

@push('scripts')
    <script src="/js/home.js"></script>
@endpush 