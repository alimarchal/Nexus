{{--
    Bordered report tables in the same style as the AKSIC print sheet.
    Plain CSS (not Tailwind utilities) so the look does not depend on a rebuild.
    Usage: <table class="aksic-grid"> ... </table>
--}}
@once
    <style>
        /* Word-style table: black header/footer rows with white text, black grid, white body. */
        table.aksic-grid { width: 100%; border-collapse: collapse; font-size: 13px; color: #000; background: #fff; }
        table.aksic-grid th, table.aksic-grid td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; color: #000; background: #fff; }
        table.aksic-grid thead th { background: #000; color: #fff; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; text-align: left; }
        table.aksic-grid thead tr.group th { background: #000; color: #fff; text-align: center; }
        table.aksic-grid tfoot td { background: #000; color: #fff; font-weight: 700; }
        table.aksic-grid td.num, table.aksic-grid th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        table.aksic-grid td.ctr, table.aksic-grid th.ctr { text-align: center; }
        table.aksic-grid td a { color: #1d4ed8; }
        table.aksic-grid .neg { color: #b91c1c; font-weight: 700; }
        table.aksic-grid tfoot td.neg { color: #fca5a5; }
        table.aksic-grid .bar { height: 6px; background: #e5e7eb; border-radius: 3px; margin-top: 3px; overflow: hidden; }
        table.aksic-grid .bar > span { display: block; height: 100%; background: #15803d; }
        table.aksic-grid .bar > span.warn { background: #d97706; }
        table.aksic-grid .bar > span.over { background: #b91c1c; }

        /* Scrollable table with sticky header/footer. Borders are drawn per cell
           (separate model) so they stay attached to the sticky rows, and the
           sticky cells are opaque so rows never show through while scrolling. */
        .aksic-scroll { max-height: 32rem; overflow: auto; border: 1px solid #000; }
        .aksic-scroll table.aksic-grid { border-collapse: separate; border-spacing: 0; }
        .aksic-scroll table.aksic-grid th, .aksic-scroll table.aksic-grid td { border-width: 0 1px 1px 0; }
        .aksic-scroll table.aksic-grid tr > :last-child { border-right-width: 0; }
        .aksic-scroll table.aksic-grid tbody tr:last-child td { border-bottom-width: 0; }
        .aksic-scroll table.aksic-grid thead th { position: sticky; top: 0; z-index: 2; border-bottom-color: #fff; }
        .aksic-scroll table.aksic-grid tfoot td { position: sticky; bottom: 0; z-index: 2; border-top: 1px solid #000; border-bottom-width: 0; }

        @media print {
            table.aksic-grid { font-size: 9.5px; }
            table.aksic-grid th, table.aksic-grid td { padding: 3px 5px; }
            table.aksic-grid thead th, table.aksic-grid tfoot td, table.aksic-grid thead tr.group th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.aksic-grid thead { display: table-header-group; }
            table.aksic-grid tr { break-inside: avoid; }
            .aksic-scroll { max-height: none; overflow: visible; border: 0; }
            .aksic-scroll table.aksic-grid thead th, .aksic-scroll table.aksic-grid tfoot td { position: static; }
        }
    </style>
@endonce
