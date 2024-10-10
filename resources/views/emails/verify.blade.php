<x-mail::message>
# Welcome to Pmall
{{-- # Email Verification --}}

Thank you for signing up.
{{-- Use this link to verify your email <a href="{{url('/tokens?token='.$pin)}}">{{url('/tokens?token='.$pin)}}</a> --}}
@if ($store_name!=null)
Your Store Name is {{$store_name}}
@endif
<br>
Username/Email: {{$email}}
<br>
Password: {{$pass}}


{{-- <x-mail::button :url="''">
Button Text
</x-mail::button> --}}

Thank you for registering with Pmall.<br>
{{ config('app.name') }}
</x-mail::message>
