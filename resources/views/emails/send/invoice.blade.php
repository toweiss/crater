@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => ''])
        @if($data['company']['logo'])
            <img class="header-logo" src="{{ asset($data['company']['logo']) }}" alt="{{ $data['company']['name'] }}" width="200" style="height:auto; display:block; margin:auto;">
        @else
            {{$data['company']['name']}}
        @endif
        @endcomponent
    @endslot

    {{-- Body --}}
    <!-- Body here -->

    {{-- Subcopy --}}
    @slot('subcopy')
        @component('mail::subcopy')
            {!! $data['body'] !!}
            @if(!$data['attach']['data'])
                @component('mail::button', ['url' => $data['url']])
                    View Invoice
                @endcomponent
            @endif
        @endcomponent
    @endslot
    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            Diese E-Mail wurde automatisch generiert. Bei Rückfragen melden Sie sich gerne an:<br>
            <a href="mailto:info@whitemacs.com">info@whitemacs.com</a><br>
            <a href="https://www.baden-wuerttemberg.de/de/service/presse/pressemitteilung/pid/sicherheitspreis-2024-verliehen?highlight=whitemacs" target="_blank">
                <img src="https://sub.whitemacs.com/uploads/images/Preislogo_Sicherheitsforum_Sonderpreis.png" width="200" alt="Sicherheitspreis Logo">
            </a><br>
            <table cellpadding="0" cellspacing="0">
                <tr><td style="height:6px;"></td></tr>
                <tr>
                    <td>
                    <div style="height:1px;background:#DAE4EC;"></div>
                    </td>
                </tr>
                <tr><td style="height:8px;"></td></tr>
                <!-- Zeile 1 -->
                <tr>
                    <td style="white-space:nowrap;font-size:12px;color:#aeaeae;">
                    whitemacs GmbH
                    <span style="color:#E6330A;">•</span>
                    Industriestr. 12
                    <span style="color:#E6330A;">•</span>
                    77815 Bühl
                    </td>
                </tr>
                <!-- Zeile 2 -->
                <tr>
                    <td style="white-space:nowrap;font-size:12px;color:#aeaeae;">
                    <a href="tel:+4917642797594" style="text-decoration:none;color:#aeaeae;">+49&nbsp;176&nbsp;42797594</a>
                    <span style="color:#E6330A;">•</span>
                    <a href="https://whitemacs.com" style="text-decoration:none;color:#aeaeae;">whitemacs.com</a>
                    <span style="color:#E6330A;">•</span>
                    <a href="mailto:info@whitemacs.com" style="text-decoration:none;color:#aeaeae;">info@whitemacs.com</a>
                    </td>
                </tr>
                <tr><td style="height:8px;"></td></tr>
                <tr>
                    <td>
                    <div style="height:1px;background:#DAE4EC;"></div>
                    </td>
                </tr>
                <tr><td style="height:8px;"></td></tr>
                <!-- Fußzeile -->
                <tr>
                    <td style="font-size:12px;color:#aeaeae;">
                    whitemacs GmbH&nbsp;| Sitz der Gesellschaft: Bühl&nbsp;| Handelsregister Mannheim: HRB 748479<br>
                    Geschäftsführer: Tobias Weiß
                    </td>
                </tr>
                <tr><td style="height:6px;"></td></tr>
                </table>
        @endcomponent

    @endslot
@endcomponent
