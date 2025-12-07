<!DOCTYPE html>
<html>
<head>
    <title>Berita Acara Permainan</title>
    <style>
        body { font-family: sans-serif; padding: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #666; }
        
        .meta-box { margin-bottom: 20px; }
        .meta-box table { width: 100%; }
        .meta-box td { padding: 5px; }

        .result-box { margin-bottom: 30px; border: 1px solid #000; padding: 10px; }
        .result-table { width: 100%; border-collapse: collapse; }
        .result-table th, .result-table td { padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .result-table th { background-color: #f0f0f0; }
        
        .winner-row { background-color: #fff9c4; font-weight: bold; }
        .loser-row { background-color: #ffebee; color: #c62828; }

        .history-section h3 { border-left: 5px solid #333; padding-left: 10px; margin-bottom: 15px; }
        .history-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .history-table th, .history-table td { border: 1px solid #999; padding: 6px; text-align: left; }
        .history-table th { background-color: #eee; }

        /* AREA TANDA TANGAN */
        .signature-section { margin-top: 50px; page-break-inside: avoid; }
        .signature-table { width: 100%; text-align: center; }
        .signature-box { height: 80px; } /* Ruang untuk paraf */
        .sign-name { font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        .sign-title { font-size: 11px; color: #555; }
        
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>BERITA ACARA HASIL PERMAINAN</h1>
        <p>Sistem Remi Digital - Kode Ruangan: <strong>{{ $game->room_code }}</strong></p>
        <p>Tanggal: {{ $game->created_at->format('d F Y, H:i') }} WIB</p>
    </div>

    <div class="result-box">
        <h3 style="margin-top:0; text-align:center;">KLASEMEN AKHIR</h3>
        <table class="result-table">
            <thead>
                <tr>
                    <th>PERINGKAT</th>
                    <th>NAMA PEMAIN</th>
                    <th>TOTAL SKOR</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($players as $index => $p)
                    <tr class="{{ $index == 0 ? 'winner-row' : ($loop->last ? 'loser-row' : '') }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->score }}</td>
                        <td>
                            @if($index == 0) 👑 THE KING 
                            @elseif($loop->last) 🤡 BADUT 
                            @else WARGA BIASA @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="history-section">
        <h3>Log Aktivitas (5 Terakhir)</h3>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pemain</th>
                    <th>Kejadian</th>
                    <th>Poin</th>
                </tr>
            </thead>
            <tbody>
                @foreach($histories->sortByDesc('id')->take(10) as $h)
                    <tr>
                        <td>{{ $h->created_at->format('H:i:s') }}</td>
                        <td>{{ $h->player->name }}</td>
                        <td>
                            @if($h->type == 'reset') KENA RESET SKOR
                            @elseif($h->type == 'cekih_bonus') BERHASIL CEKIH
                            @elseif($h->type == 'cekih_penalty') KORBAN CEKIH
                            @else INPUT RONDE
                            @endif
                        </td>
                        <td>{{ $h->points_added > 0 ? '+' : '' }}{{ $h->points_added }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p style="font-size:10px; color:#666; margin-top:5px;">*Hanya menampilkan 10 aktivitas terakhir untuk menghemat kertas.</p>
    </div>

    <div class="signature-section">
        <p style="text-align: center; margin-bottom: 20px;">
            Dengan ini hasil permainan dinyatakan <strong>SAH</strong> dan tidak dapat diganggu gugat.<br>
            Pihak yang kalah wajib menerima ejekan/hukuman sesuai kesepakatan.
        </p>

        <table class="signature-table">
            <tr>
                <td width="40%">
                    <div class="sign-title">Mengetahui,<br>JUARA 1 (THE KING)</div>
                    <div class="signature-box"></div>
                    <div class="sign-name">{{ $champion->name }}</div>
                </td>
                <td width="20%">
                    </td>
                <td width="40%">
                    <div class="sign-title">Menyetujui,<br>POSISI BUNCIT (BADUT)</div>
                    <div class="signature-box"></div>
                    <div class="sign-name">{{ $loser->name }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dicetak otomatis oleh Sistem Remi Digital pada {{ date('Y-m-d H:i:s') }}
    </div>

</body>
</html>