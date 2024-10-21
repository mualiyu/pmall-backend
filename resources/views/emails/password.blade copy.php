@extends('layouts.mail')

@section('content')

<h2>Introduction</h2>

Your six-digit PIN is <h4>{{$mailData['pin']}}</h4>
<p>Please do not share your One Time Pin With Anyone. You made a request to reset your password. Please discard if this wasn't you.</p>


Thanks,<br>
{{ config('app.name') }}
@endsection
