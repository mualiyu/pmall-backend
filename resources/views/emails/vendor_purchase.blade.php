@extends('vendor.mail.html.layout')

@section('content')
    <h2 style="font-weight: 600;">Hello {{ $sale->vendor_name ?? 'Vendor' }},</h2>

    <p>You have a new purchase on your store.</p>

    <!-- <p><strong>Customer Email:</strong> {{ $email }}</p>
    <p><strong>Customer Phone:</strong> {{ $phone }}</p> -->

    <table width="100%" cellpadding="5" cellspacing="0" border="1" style="border-collapse: collapse; margin-top: 15px;">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->products ?? [] as $item)
                <tr>
                    <td>{{ $item['name'] ?? '' }}</td>
                    <td>{{ $item->pivot->quantity ?? '' }}</td>
                    <td>{{ $item->pivot->price ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px;">Total: <strong>₦{{ number_format($sale->total ?? 0, 2) }}</strong></p>

    <p>Thank you for using {{ config('app.name') }}!</p>
@endsection
