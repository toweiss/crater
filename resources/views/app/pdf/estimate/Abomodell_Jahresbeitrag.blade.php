<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Auftragsangebot - {{ $estimate->estimate_number }}</title>
    <meta name="viewport" content="width=794, initial-scale=1.0">
    <style>
        body {
            margin: 0 auto;
            font-family: Calibri, Arial, sans-serif;
            background: #fff;
            color: #222;
        }
        .angebot-container {
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
    <div class="angebot-container">
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
            {{ $estimate->company->name }}&nbsp;|&nbsp;{{ $estimate->company->address->address_street_1 }}&nbsp;|&nbsp;{{ $estimate->company->address->zip }} {{ $estimate->company->address->city }}<br>
        </div>
        <div class="empfaenger">
            {!! $billing_address !!}
        </div>
        <div class="datum">
            {{ $estimate->company->address->city }}, {{ $estimate->formattedEstimateDate }}
        </div>
        <div class="titel">
            Auftragsangebot {{ $estimate->estimate_number }}
        </div>
        <div style="margin-bottom: 20px; font-size: 9pt; text-align: left;">
            Sehr geehrte {{ $estimate->customer->name }},<br>
            hiermit möchten wir Ihnen folgendes Auftragsangebot machen:
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
                    @if($estimate->discount_per_item === 'YES')
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
            @foreach ($estimate->items as $item)
                <tr class="item-row">
                    <!-- Index der Position -->
                    <td>{{$index}}</td>
                    <!-- Name und Beschreibung der Position -->
                    <td style="text-align: left;">
                        <span class="item-description-title">{{ $item->name }}</span><br>
                        <span class="item-description">{!! nl2br(htmlspecialchars($item->description)) !!}</span><br>
                        <!-- Beginn der Dienstleistung -->
                        <span class="item-description">
                            möglicher Beginn der Dienstleistung: {{\Carbon\Carbon::parse($estimate->getCustomFieldBySlug('CUSTOM_ESTIMATE_STARTDATUM')->default_answer)->locale('de')->translatedFormat('d.m.Y')}}
                        </span>
                    </td>
                    <!-- Custom Fields -->
                    @foreach($customFields as $field)
                        <td>{{ $item->getCustomFieldValueBySlug($field->slug) }}</td>
                    @endforeach
                    <!-- Menge -->
                    <td>{{$item->quantity}}</td>
                    <!-- Preis -->
                    <td>{{number_format($item->price / 100, 2, ',', '.')}} {{$estimate->customer->currency->symbol}}</td>
                    <!-- Rabatt pro Position -->
                    @if($estimate->discount_per_item === 'YES')
                        <td>
                            @if($item->discount_type === 'fixed')
                                {!! format_money_pdf($item->discount_val, $estimate->customer->currency) !!}
                            @endif
                            @if($item->discount_type === 'percentage')
                                {{$item->discount}}%
                            @endif
                        </td>
                    @endif
                    <!-- Gesamtbetrag der Position -->
                    <td>{{number_format($item->total / 100, 2, ',', '.')}} {{$estimate->customer->currency->symbol}}</td>
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
        <table style="width: 300px; float: right; border-collapse: collapse; margin-bottom: 0px; table-layout: fixed;">
            <!-- <colgroup>
                <col style="">
                <col style="">
            </colgroup> -->
            <!-- Zwischensumme -->
            <tr>
                <td style="width:60%; font-size: 9pt; text-align: right; padding: 4px 8px;">@lang('pdf_subtotal'):</td>
                <td style="width:40%;font-size: 9pt; text-align: right; padding: 4px 8px;">{{number_format($estimate->sub_total / 100, 2, ',', '.')}} {{$estimate->customer->currency->symbol}}</td>
            </tr>

            <!-- Rabatt -->
            @if($estimate->discount > 0)
                @if ($estimate->discount_per_item === 'NO')
                    <tr>
                        <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">
                            @if($estimate->discount_type === 'fixed')
                                @lang('pdf_discount_label'):
                            @endif
                            @if($estimate->discount_type === 'percentage')
                                @lang('pdf_discount_label') Vorauszahlung (-{{$estimate->discount}}%)
                            @endif
                        </td>
                        <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">
                            @if($estimate->discount_type === 'fixed')
                            - {{number_format($estimate->discount_val / 100, 2, ',', '.')}} {{$estimate->customer->currency->symbol}}:
                        @endif
                        @if($estimate->discount_type === 'percentage')
                            - {{number_format($estimate->discount_val / 100, 2, ',', '.')}} {{$estimate->customer->currency->symbol}}
                        @endif
                        </td>
                    </tr>
                @endif
            @endif

            <!-- Steuer -->
            @if ($estimate->tax_per_item === 'YES')
                @foreach ($taxes as $tax)
                    <tr>
                        <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">{{$tax->name.' ('.$tax->percent.'%)'}}:</td>
                        <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">{{number_format($tax->amount / 100, 2, ',', '.')}} {{$estimate->customer->currency->symbol}}</td>
                    </tr>
                @endforeach
            @else
                @foreach ($estimate->taxes as $tax)
                    <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">{{$tax->name.' ('.$tax->percent.'%)'}}:</td>
                    <td style="font-size: 9pt; text-align: right; padding: 4px 8px;">{{number_format($tax->amount / 100, 2, ',', '.')}} {{$estimate->customer->currency->symbol}}</td>
                @endforeach
            @endif
            <!-- Trenner -->
             <tr>
                <td colspan="2" style="border-top: 3px solid #222; padding:0;"></td>
            </tr>
            <!-- Gesamtsumme -->
            <tr>
                <td style="font-weight:bold;font-size: 10pt; text-align: right; padding: 4px 8px;">@lang('pdf_total'):</td>
                <td style="font-weight:bold;font-size: 10pt; text-align: right; padding: 4px 8px;">{{number_format($estimate->total / 100, 2, ',', '.')}} {{$estimate->customer->currency->symbol}}</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
        <div style="height: 25px;"></div>
        <div style="font-size: 7pt; text-align: right; width: 100%;">
            Die erste Rechnung in Höhe von {{number_format($estimate->total / 100, 2, ',', '.')}} {{$estimate->customer->currency->symbol}} (brutto) wird zu Beginn des ersten Monats fällig.<br>
            Dieses Angebot ist 30 Tage (bis zum {{ $estimate->formattedExpiryDate }}) ohne Unterschrift gültig.<br>
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
        <div style="width: 100%; margin: 0 auto;">
            <hr style="border: none; border-top: 1px solid #888; width: 100%; margin: 0 0 8px 0;">
        <div style="font-size: 9pt; text-align: left; margin-bottom: 20px;">
            Hiermit beauftragen wir die whitemacs GmbH, die oben aufgeführten Leistungen entsprechend dem Angebot durchzuführen und akzeptieren sowohl die übergebenen Allgemeinen Geschäftsbedingungen als auch auch die übergebene Auftragsverarbeitungsvereinbarung.
        </div>
            <div style="height: 70px;"></div>
            <div style="width: 40%; border-top: 1px solid #888; margin-bottom: 4px;"></div>
            <div style="width: 40%; font-size: 8pt;">Datum und Zeichnungsberechtigte(r)</div>
            <div style="height: 20px;"></div>
            <hr style="border: none; border-top: 2px solid #222; width: 100%; margin: 0 0 6px 0;">
            <table style="width: 100%; border-collapse: collapse; font-size: 7pt;">
                <tr>
                    <td style="vertical-align: top; width: 33%;">
                        <div>whitemacs GmbH</div>
                        <div>info@whitemacs.com</div>
                        <div>+49 176 42797594</div>
                    </td>
                    <td style="text-align: center; vertical-align: top; width: 33%;">
                        <div>Ust.-Id: DE363731037</div>
                        <div>Amtsgericht Mannheim</div>
                        <div>HRB 748479</div>
                    </td>
                    <td style="text-align: right; vertical-align: top; width: 34%;">
                        <div>Geschäftsführer:</div>
                        <div>Tobias Weiß</div>
                        <div>tobias.weiss@whitemacs.com</div>
                    </td>
                </tr>
            </table>
        </div>
    </footer>
</body>
</html>
