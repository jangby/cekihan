<!DOCTYPE html>
<html>
<head>
    <title>Surat Pernyataan Menyerah</title>
    <style>
        body { font-family: 'Times New Roman', serif; padding: 40px; color: #000; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 3px double #000; padding-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; text-transform: uppercase; text-decoration: underline; margin-bottom: 10px; }
        .content { margin-bottom: 40px; text-align: justify; }
        .strong { font-weight: bold; }
        .quote { font-style: italic; background: #eee; padding: 10px; margin: 20px 0; border-left: 5px solid #000; }
        .signature-table { width: 100%; margin-top: 50px; text-align: center; }
        .sign-box { height: 80px; margin-bottom: 10px; }
        .stamp { border: 2px solid red; color: red; transform: rotate(-15deg); display: inline-block; padding: 5px 20px; font-weight: bold; font-size: 20px; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">SURAT PERNYATAAN MENYERAH</div>
        <div>Nomor: {{ $game->room_code }}/LOSE/{{ date('Y') }}</div>
    </div>

    <div class="content">
        <p>Saya yang bertanda tangan di bawah ini:</p>
        
        <table>
            <tr><td width="150">Nama</td><td>: <strong>{{ $player->name }}</strong> (Si Penyerah)</td></tr>
            <tr><td>Posisi</td><td>: Pemain {{ $player->position }}</td></tr>
            <tr><td>Status Mental</td><td>: <strong>Terguncang / Tidak Kuat</strong></td></tr>
        </table>

        <p>Dengan ini menyatakan secara sadar dan tanpa paksaan dari pihak manapun (selain tekanan permainan yang berat), bahwa saya:</p>

        <h2 style="text-align: center;">🏳️ MENYERAH / SURRENDER 🏳️</h2>

        <p>Dari permainan Kartu Remi Digital yang berlangsung di ruangan <strong>{{ $game->room_code }}</strong>. Saya mengakui bahwa:</p>
        
        <ol>
            <li>Skill saya belum cukup mumpuni untuk melawan teman-teman saya.</li>
            <li>Mental saya tidak kuat menghadapi gempuran Cekih dan Reset Poin.</li>
            <li>Saya bersedia menerima ejekan (roasting) sewajarnya selama 24 jam ke depan.</li>
        </ol>

        <div class="quote">
            "Lebih baik mundur dengan rasa malu daripada maju lalu bangkrut total." <br>
            - {{ $player->name }}, {{ date('Y') }}
        </div>

        <p>Demikian surat pernyataan ini saya buat dengan sebenar-benarnya sambil menahan air mata.</p>
        
        <div style="text-align: center;">
            <div class="stamp">FIX NO DEBAT<br>KALAH TOTAL</div>
        </div>
    </div>

    <table class="signature-table">
        <tr>
            <td width="50%">
                <div>Saksi / Raja Saat Ini,</div>
                <div class="sign-box"></div>
                <div class="strong">{{ $winner->name ?? 'Sistem' }}</div>
            </td>
            <td width="50%">
                <div>Yang Menyatakan,</div>
                <div class="sign-box">
                    <br><br><small>(Tanda Tangan Basah)</small>
                </div>
                <div class="strong">{{ $player->name }}</div>
            </td>
        </tr>
    </table>

</body>
</html>