{{--
    Bordered report tables in the same style as the AKSIC print sheet.
    Plain CSS (not Tailwind utilities) so the look does not depend on a rebuild.
    Usage: <table class="aksic-grid"> ... </table>
--}}
@once
    <style>
        table.aksic-grid { width: 100%; border-collapse: collapse; font-size: 13px; color: #111; background: #fff; }
        table.aksic-grid th, table.aksic-grid td { border: 1px solid #b8b8b8; padding: 6px 8px; vertical-align: middle; }
        table.aksic-grid thead th { background: #f1f5f2; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #333; text-align: left; }
        table.aksic-grid thead tr.group th { background: #14532d; color: #fff; text-align: center; }
        table.aksic-grid tfoot td { background: #f1f5f2; font-weight: 700; }
        table.aksic-grid td.num, table.aksic-grid th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        table.aksic-grid td.ctr, table.aksic-grid th.ctr { text-align: center; }
        table.aksic-grid tbody tr:nth-child(even) td { background: #fafafa; }
        table.aksic-grid .neg { color: #b91c1c; font-weight: 700; }
        table.aksic-grid .bar { height: 6px; background: #e5e7eb; border-radius: 3px; margin-top: 3px; overflow: hidden; }
        table.aksic-grid .bar > span { display: block; height: 100%; background: #15803d; }
        table.aksic-grid .bar > span.warn { background: #d97706; }
        table.aksic-grid .bar > span.over { background: #b91c1c; }
        .dark table.aksic-grid { color: #111; }
        @media print {
            table.aksic-grid { font-size: 9.5px; }
            table.aksic-grid th, table.aksic-grid td { padding: 3px 5px; }
            table.aksic-grid thead th, table.aksic-grid tfoot td, table.aksic-grid thead tr.group th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.aksic-grid thead { display: table-header-group; }
            table.aksic-grid tr { break-inside: avoid; }
        }
    </style>
@endonce
