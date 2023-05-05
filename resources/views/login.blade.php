@extends('layout')
@section('title', 'Login')
@section('content')
<div class="container">
    <form action="{{ route('login.post') }}" method="POST" class="ms-auto me-auto mt-3" style="width: 500px">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" class="form-control" name="email">
            @if ($errors->has('email'))
                <div class="alert alert-danger">{{ $errors->first('email') }}</div>
            @endif
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password">
            @if ($errors->has('password'))
                <div class="alert alert-danger">{{ $errors->first('password') }}</div>
            @endif
        </div>
        <div class="mb-3">
            <label for="captcha">Please solve this math problem:</label>
            <?php
              $num1 = rand(1, 10);
              $num2 = rand(1, 10);
              echo '<span>'.$num1.' + '.$num2.' = ?</span>';
              echo '<input type="hidden" name="captcha_answer" value="'.($num1 + $num2).'">';
            ?>
            <input type="text" name="captcha" id="captcha" required>
            @if ($errors->has('captcha'))
                <div class="alert alert-danger">{{ $errors->first('captcha') }}</div>
            @endif
        </div>
        @if ($errors->has('cooldown'))
            <div class="alert alert-danger">{{ $errors->first('cooldown') }}</div>
        @endif
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
@endsection