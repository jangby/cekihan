<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Permainan - {{ $game->room_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4 md:p-8 text-gray-800">
    <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-xl overflow-hidden">
        <div class="bg-gray-900 p-6 text-white flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">RIWAYAT PERMAINAN</h1>
                <p class="text-gray-400">Room: {{ $game->room_code }}</p>
            </div>
            <button onclick="window.print()" class="bg-yellow-500 hover:bg-yellow-600 text-black px-4 py-2 rounded font-bold text-sm">
                🖨️ Simpan PDF
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-xs leading-normal">
                        <th class="py-3 px-6">#</th>
                        <th class="py-3 px-6">Pemain</th>
                        <th class="py-3 px-6">Aksi</th>
                        <th class="py-3 px-6 text-right">Poin</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @foreach($histories as $index => $log)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6">{{ $index + 1 }}</td>
                            <td class="py-3 px-6 font-bold">{{ $log->player->name }}</td>
                            <td class="py-3 px-6">
                                @if($log->type == 'round') <span class="bg-blue-100 text-blue-600 py-1 px-3 rounded-full text-xs">Input Ronde</span>
                                @elseif($log->type == 'cekih_bonus') <span class="bg-green-100 text-green-600 py-1 px-3 rounded-full text-xs">Dapat Cekih</span>
                                @elseif($log->type == 'cekih_penalty') <span class="bg-red-100 text-red-600 py-1 px-3 rounded-full text-xs">Kena Cekih</span>
                                @elseif($log->type == 'reset') <span class="bg-red-600 text-white py-1 px-3 rounded-full text-xs">RESET SKOR</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-right font-bold {{ $log->points_added >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $log->points_added > 0 ? '+' : '' }}{{ $log->points_added }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-6 bg-gray-50 border-t">
            <h3 class="font-bold mb-4">HASIL AKHIR</h3>
            <div class="grid grid-cols-4 gap-4 text-center">
                @foreach($players->sortByDesc('score') as $p)
                    <div class="bg-gray-200 p-3 rounded">
                        <div class="text-xs text-gray-500">Juara {{ $loop->iteration }}</div>
                        <div class="font-bold">{{ $p->name }}</div>
                        <div class="text-xl">{{ $p->score }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>