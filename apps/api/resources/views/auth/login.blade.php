@extends('layouts.operations')
@section('title', 'Login')
@section('content')
<div class="login-shell panel yellow">
    <span class="route-code">STAFF ACCESS / SECURE</span>
    <h1>Operations login</h1>
    <p class="muted">Akses staf untuk booking, diagnosis, quotation, payment, dan status repair.</p>
    <form method="post" action="{{ route('operations.login.submit') }}">
        @csrf
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus></div>
        <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required></div>
        <div class="field"><label><input name="remember" type="checkbox" value="1"> Ingat sesi</label></div>
        <button type="submit">Masuk</button>
    </form>
</div>
@endsection
