@extends('layouts.mail')

@section('content')

<h2>Sale Success</h2>

<p>Dear {{$sale->customer->fname}}, You have successfully purchased items on PMALL NG. Here are your transaction
    details:
</p>

<h3>Sale Details:</h3>
<ul>
    <li>Total Amount: {{ $sale->total_amount }}</li>
    {{-- <li>Payment Method: {{ $sale->payment_method }}</li> --}}
    <li>Status: {{ $sale->status }}</li>
    <li>Created At: {{ $sale->created_at }}</li>
</ul>

<h3>Products:</h3>
<ul>
    @foreach($sale->products as $product)
    <li>
        Product Name: {{ $product->name }} <br>
        Quantity: {{ $product->pivot->quantity }} <br>
        Price: {{ $product->price }} <br>
        Total: {{ $product->price * $product->pivot->quantity }}
    </li>
    @endforeach
</ul>

<p>Thank you for your patronage!</p>

Thanks,<br>
{{ config('app.name') }}
@endsection