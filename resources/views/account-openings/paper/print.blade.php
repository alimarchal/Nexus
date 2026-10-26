<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $accountOpeningRequest->request_number }} - Old Account Opening Form</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h3 { font-size: 13px; margin: 16px 0 6px; padding-bottom: 3px; border-bottom: 1px solid #000; text-transform: uppercase; }
        dl { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 24px; margin: 0; }
        dt { color: #444; }
        dd { margin: 0; font-weight: bold; }
        .md\:col-span-2 { grid-column: span 2; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: left; }
        .paper-section { page-break-inside: avoid; }
        @media print { .no-print { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <p class="no-print"><button onclick="window.print()">Print</button></p>
    <h1>Bank of Azad Jammu &amp; Kashmir &mdash; Account Opening Form (old form)</h1>
    <p>{{ $accountOpeningRequest->request_number }} &middot; {{ $accountOpeningRequest->branch?->code }} {{ $accountOpeningRequest->branch?->name }} &middot; {{ $accountOpeningRequest->request_date?->format('d.m.Y') }} &middot; {{ $accountOpeningRequest->editionLabel() }} form</p>

    @include('account-openings.paper._values', ['printing' => true])
    @include('account-openings.paper._uploads', ['printing' => true])
</body>
</html>
