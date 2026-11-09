<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->is_credit_note ? 'Gutschrift' : 'Rechnung' }} - {{ $invoice->invoice_number }}</title>
    <meta name="viewport" content="width=794, initial-scale=1.0">
    <style>
        body {
            margin: 0 auto;
            font-family: Calibri, Arial, sans-serif;
            background: #fff;
            color: #222;
        }
        .rechnung-container {
            width: 100%;
            box-sizing: border-box;
            padding: 0;
            padding-bottom: 200px; /* Höhe des Footers, ggf. anpassen */
        }
        .logo-block {
            position: relative;
            height: 100px; /* reduziert */
        }
        .logo-block .logos {
            position: absolute;
            top: 0;
            right: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }
        .logo-block .logos a {
            display: block;
            margin-bottom: 4px;
            text-align: right; /* sorgt für rechtsbündige Ausrichtung des Inhalts */
        }
        .logo-block .logos a:last-child {
            margin-bottom: 0;
        }
        .logo-block .logos a:first-child img {
            max-height: 40px !important;
            height: 40px !important;
            width: auto !important;
            display: block;
        }
        .logo-block .logos img:last-child {
            max-height: 70px;
        }
        .absender {
            font-size: 7pt;
            margin-top: 8px;
            margin-bottom: 6px;
            text-align: left;
        }
        .empfaenger {
            font-size: 9pt;
            line-height: 1.2;
            margin-bottom: 18px;
            text-align: left;
        }
        .datum {
            font-size: 9pt;
            text-align: right;
            margin-bottom: 24px;
        }
        .titel {
            font-size: 12pt;
            font-weight: bold;
            text-align: left;
            margin-bottom: 20px;
        }
        .items-table {
            width:100%;
            border-collapse:collapse;
            margin-bottom: 16px;
            table-layout:fixed;
             font-size: 9pt;
        }

        .item-table-heading {
            font-weight:bold;
            text-align:center;
            padding:6px 0;
        }

        .item-row {
            text-align:center;
            padding:6px 0;
            vertical-align: top;
        }

        .item-description-title {
            font-weight:bold;
        }
        .item-description {
            font-weight: normal;
        }
        .items-table td:last-child {
            text-align: right;
        }
        .items-table thead tr:last-child td {
            padding-bottom: 18px; /* Abstand nach der Trennlinie */
        }
        .items-table .item-row td {
            padding-top: 12px;
            padding-bottom: 12px; /* Abstand zwischen Artikeln */
        }
        .notes-label {
            font-size: 9pt;
            line-height: 22px;
            letter-spacing: 0.05em;
            color: #040405;
            width: 70%;
            height: 19.87px;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="rechnung-container">
        <!-- Logo-Bereich -->
        <div class="logo-block">
            <div class="logos">
                <a href="https://whitemacs.com" target="_blank">
                    <img src="{{ $logo }}" alt="whitemacs Logo">
                </a>
                <a href="https://www.baden-wuerttemberg.de/de/service/presse/pressemitteilung/pid/sicherheitspreis-2024-verliehen?highlight=whitemacs" target="_blank">
                    <img src="https://sub.whitemacs.com/uploads/images/Preislogo_Sicherheitsforum_Sonderpreis.png" alt="Sicherheitspreis Logo">
                </a>
            </div>
        </div>
        <!-- Absender, Empfänger, Datum und Titel -->
        <div class="absender">
            {{ $invoice->company->name }}&nbsp;|&nbsp;{{ $invoice->company->address->address_street_1 }}&nbsp;|&nbsp;{{ $invoice->company->address->zip }} {{ $invoice->company->address->city }}<br>
        </div>
        <div class="empfaenger">
            {!! $billing_address !!}
        </div>
        <div class="datum">
            {{ $invoice->company->address->city }}, {{ $invoice->formattedInvoiceDate }}
        </div>
        <div class="titel">
            {{ $invoice->is_credit_note ? 'Gutschrift' : 'Rechnung' }} {{ $invoice->invoice_number }}
            @if($invoice->is_credit_note && $invoice->originalInvoice)
                <br><span style="font-size: 9pt;">zur Rechnung {{ $invoice->originalInvoice->invoice_number }}</span>
            @endif
        </div>
        <div style="margin-bottom: 20px; font-size: 9pt; text-align: left;">
            Sehr geehrte {{ $invoice->customer->name }},<br>
            @if($invoice->is_credit_note)
                hiermit schreiben wir Ihnen für die Rechnung {{ optional($invoice->originalInvoice)->invoice_number }} folgenden Betrag gut:
            @else
                vielen Dank für die Nutzung unserer Dienstleistung. Hierfür berechnen wir wie folgt:
            @endif
        </div>

        <!-- ######################################################################### -->
        <!-- Tabelle mit den einzelnen Positionen -->
        <!-- ######################################################################### -->
        <table width="100%" class="items-table" cellspacing="0" border="0">

            <!-- Tabellen Kopfzeile -->
            <thead>
                <tr>
                    <th style="width:5%;" class="item-table-heading">#</th>
                    <th style="width:65%;" class="item-table-heading">@lang('pdf_items_label')</th>
                        @foreach($customFields as $field)
                        <th class="text-right item-table-heading">{{ $field->label }}</th>
                    @endforeach
                    <th class="item-table-heading">@lang('pdf_quantity_label')</th>
                    <th class="item-table-heading" style="width:12.5%;">@lang('pdf_price_label')</th>
                    @if($invoice->discount_per_item === 'YES')
                        <th class="item-table-heading">@lang('pdf_discount_label')</th>
                    @endif
                    <th class="item-table-heading" style="text-align:right; width:12.5%;">@lang('pdf_amount_label')</th>
                </tr>
                <tr>
                    <td colspan="5" style="border-bottom: 3px solid #222; padding:0;"></td>
                </tr>
            </thead>
            <!-- Tabellen Inhalt -->
            @php
                $index = 1
            @endphp
            <!-- Durchlauf durch die einzelnen Positionen -->
            @foreach ($invoice->items as $item)
                <tr class="item-row">
                    <!-- Index der Position -->
                    <td>{{$index}}</td>
                    <!-- Name und Beschreibung der Position -->
                    <td style="text-align: left;">
                        <span class="item-description-title">{{ $item->name }}</span><br>
                        <span class="item-description">{!! nl2br(htmlspecialchars($item->description)) !!}</span><br>

                        <!-- Abrechnungszeitraum -->
                        <span class="item-description">
                            Abrechnungszeitraum:
                            @php
                                $beginn = optional($invoice->getCustomFieldBySlug('CUSTOM_INVOICE_ABRECHNUNGSZEITRAUM_BEGINN'))->default_answer;
                                $ende = optional($invoice->getCustomFieldBySlug('CUSTOM_INVOICE_ABRECHNUNGSZEITRAUM_ENDE'))->default_answer;
                            @endphp
                            @if($beginn && $ende)
                                {{ \Carbon\Carbon::parse($beginn)->locale('de')->translatedFormat('d.m.Y') }} - {{ \Carbon\Carbon::parse($ende)->locale('de')->translatedFormat('d.m.Y') }}
                            @else
                                @php
                                    $invoiceDate = \Carbon\Carbon::parse($invoice->invoice_date);
                                    $start = $invoiceDate->copy()->subMonth()->startOfMonth();
                                    $end = $invoiceDate->copy()->subMonth()->endOfMonth();
                                @endphp
                                {{ $start->locale('de')->translatedFormat('d.m.Y') }} - {{ $end->locale('de')->translatedFormat('d.m.Y') }}
                            @endif
                        </span>
                        <!-- <span class="item-description">
                            Abrechnungszeitraum:
                            {{\Carbon\Carbon::parse($invoice->getCustomFieldBySlug('CUSTOM_INVOICE_ABRECHNUNGSZEITRAUM_BEGINN')->default_answer)->locale('de')->translatedFormat('d.m.Y')}}
                            @php
                                $date = $invoice->getCustomFieldBySlug('CUSTOM_INVOICE_ABRECHNUNGSZEITRAUM_ENDE')->default_answer;
                            @endphp
                            @if($date)
                                - {{\Carbon\Carbon::parse($date)->locale('de')->translatedFormat('d.m.Y')}}
                            @endif

                        </span>
                        <span class="item-description">
                            Abrechnungszeitraum:
                            @php
                                $invoiceDate = \Carbon\Carbon::parse($invoice->invoice_date);
                                $start = $invoiceDate->copy()->subMonth()->startOfMonth();
                                $end = $invoiceDate->copy()->subMonth()->endOfMonth();
                            @endphp
                            {{ $start->locale('de')->translatedFormat('d.m.Y') }} - {{ $end->locale('de')->translatedFormat('d.m.Y') }}
                        </span> -->
                    </td>
                    <!-- Custom Fields -->
                    @foreach($customFields as $field)
                        <td>{{ $item->getCustomFieldValueBySlug($field->slug) }}</td>
                    @endforeach
                    <!-- Menge -->
                    <td>{{$item->quantity}}</td>
                    <!-- Preis -->
                    <td>{{number_format($item->price / 100, 2, ',', '.')}} {{$invoice->customer->currency->symbol}}</td>
                    <!-- Rabatt pro Position -->
                    @if($invoice->discount_per_item === 'YES')
                        <td>
                            @if($item->discount_type === 'fixed')
                                {!! format_money_pdf($item->discount_val, $invoice->customer->currency) !!}
                            @endif
                            @if($item->discount_type === 'percentage')
                                {{$item->discount}}%
                            @endif
                        </td>
                    @endif
                    <!-- Gesamtbetrag der Position -->
                    <td>{{number_format($item->total / 100, 2, ',', '.')}} {{$invoice->customer->currency->symbol}}</td>
                </tr>
                @php
                    $index += 1
                @endphp
            @endforeach
            <tr>
                    <td colspan="5" style="border-top: 3px solid #222; padding:0;"></td>
            </tr>
        </table>

        <!-- ######################################################################### -->
        <!-- Gesamtsumme -->
        <!-- ######################################################################### -->
        <table style="width: 250px; float: right; border-collapse: collapse; margin-bottom: 0px; table-layout: fixed;">
            <!-- <colgroup>
                <col style="">
                <col style="">
            </colgroup> -->
            <!-- Zwischensumme -->
            <tr>
                <td style="width:60%; font-size: 9pt; text-align: right; padding: 4px 8px;">@lang('pdf_subtotal'):</td>
                <td style="width:40%;font-size: 9pt; text-align: right; padding: 4px 8px;">{{number_format($invoice->sub_total / 100, 2, ',', '.')}} {{$invoice->customer->currency->symbol}}</td>
            </tr>

            <!-- Rabatt -->
            @if($invoice->discount > 0)
                @if ($invoice->discount_per_item === 'NO')
                    <tr>
                        <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">
                            @if($invoice->discount_type === 'fixed')
                                @lang('pdf_discount_label'):
                            @endif
                            @if($invoice->discount_type === 'percentage')
                                @lang('pdf_discount_label') ({{$invoice->discount}}%):
                            @endif
                        </td>
                        <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">
                            @if($invoice->discount_type === 'fixed')
                            - {{number_format($invoice->discount_val / 100, 2, ',', '.')}} {{$invoice->customer->currency->symbol}}
                        @endif
                        @if($invoice->discount_type === 'percentage')
                            - {{number_format($invoice->discount_val / 100, 2, ',', '.')}} {{$invoice->customer->currency->symbol}}
                        @endif
                        </td>
                    </tr>
                @endif
            @endif

            <!-- Steuer -->
            @if ($invoice->tax_per_item === 'YES')
                @foreach ($taxes as $tax)
                    <tr>
                        <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">{{$tax->name.' ('.$tax->percent.'%)'}}:</td>
                        <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">{{number_format($tax->amount / 100, 2, ',', '.')}} {{$invoice->customer->currency->symbol}}</td>
                    </tr>
                @endforeach
            @else
                @foreach ($invoice->taxes as $tax)
                    <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">{{$tax->name.' ('.$tax->percent.'%)'}}:</td>
                    <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">{{number_format($tax->amount / 100, 2, ',', '.')}} {{$invoice->customer->currency->symbol}}</td>
                @endforeach
            @endif
            <!-- Trenner -->
             <tr>
                <td colspan="2" style="border-top: 3px solid #222; padding:0;"></td>
            </tr>
            <!-- Gesamtsumme -->
            <tr>
                <td style="font-weight:bold;font-size: 10pt; text-align: right; padding: 4px 8px;">@lang('pdf_total'):</td>
                <td style="font-weight:bold;font-size: 10pt; text-align: right; padding: 4px 8px;">{{number_format($invoice->total / 100, 2, ',', '.')}} {{$invoice->customer->currency->symbol}}</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
        <div style="height: 50px;"></div>
        <div style="font-size: 8pt; text-align: right; width: 100%;">
            Dieser Betrag ist bis zum {{ $invoice->formattedDueDate }} an das unten angegebene Konto zu überweisen.<br>
            Bitte geben Sie bei der Überweisung die Rechnungsnummer <b>{{ $invoice->invoice_number }}</b> an.
        </div>
        <div style="height: 5px;"></div>
        <!-- ######################################################################### -->
        <!-- Notizen -->
        <!-- ######################################################################### -->
        @if ($notes)
            <div class="notes-label">
                <b>@lang('pdf_notes')</b> <br>
                {!! $notes !!}
            </div>
        @endif
    </div>

    <!-- ######################################################################### -->
    <!-- Feste Fußzeile am unteren Seitenrand -->
    <!-- ######################################################################### -->
    <footer style="position: fixed; left: 0; right: 0; bottom: 0; margin: 0 auto;">
        <hr style="border: none; border-top: 2px solid #222; width: 100%; margin: 0 0 6px 0;">
        <table style="width: 100%; border-collapse: collapse; font-size: 7pt;">
            <tr>
                <td style="vertical-align: top; width: 33%;">
                    <div>whitemacs GmbH</div>
                    <div>info@whitemacs.com</div>
                    <div>+49 176 42797594</div>
                </td>
                <td style="text-align: center; vertical-align: top; width: 33%;">
                    <div>Volksbank Bühl</div>
                    <div>DE63 6629 1400 0005 3309 98</div>
                    <div>Steuernr.: 36057/98209</div>
                </td>
                <td style="text-align: right; vertical-align: top; width: 34%;">
                    <div>Geschäftsführer:</div>
                    <div>Tobias Weiß</div>
                    <div>tobias.weiss@whitemacs.com</div>
                </td>
            </tr>
        </table>
    </footer>
</body>
</html>
