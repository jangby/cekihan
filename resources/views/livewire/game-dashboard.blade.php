<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<div class="min-h-screen bg-gray-900 text-white p-4 flex flex-col">
    
    <div class="flex justify-between items-center mb-6 px-4">
        <h1 class="text-2xl font-bold text-gray-500">ROOM: <span class="text-yellow-500">{{ $game->room_code }}</span></h1>
        <div class="text-center"><h2 class="text-4xl font-black text-white tracking-widest">PAPAN SKOR</h2></div>
        <div class="text-right"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-900 text-green-300">● LIVE</span></div>
    </div>

    <div class="flex-grow grid grid-cols-4 gap-4">
        @php $someonePassed100 = $players->max('score') >= 100; @endphp
        @foreach($players as $player)
            @php
                $isJongkok = $someonePassed100 && $player->score < 100;
                $bgClass = $isJongkok ? 'bg-red-900 border-red-500' : 'bg-gray-800 border-gray-700';
            @endphp
            <div class="relative flex flex-col items-center justify-between rounded-3xl border-4 {{ $bgClass }} p-6 shadow-2xl transition-all duration-500">
                <div class="absolute top-4 left-4 bg-gray-700 px-3 py-1 rounded text-sm font-bold text-gray-400">P{{ $player->position }}</div>
                @if($isJongkok)<div class="absolute top-4 right-4 animate-bounce"><span class="bg-red-600 text-white font-black px-4 py-2 rounded-full shadow-lg border-2 border-white transform rotate-12 inline-block">JONGKOK! 🦵</span></div>@endif
                <div class="mt-8 text-center"><h3 class="text-3xl font-bold truncate max-w-[200px]">{{ $player->name }}</h3></div>
                <div class="text-center my-4"><span class="text-8xl font-black {{ $player->score < 0 ? 'text-red-400' : 'text-yellow-400' }}">{{ $player->score }}</span><p class="text-gray-400 text-sm uppercase tracking-widest mt-2">TOTAL POIN</p></div>
                <div class="w-full bg-black/20 rounded-xl p-3 text-center">
                    @if($player->score >= 1000)<span class="text-green-400 font-bold">🏆 WINNER</span>
                    @elseif($player->score <= -500)<span class="text-red-500 font-bold">💀 GAME OVER</span>
                    @else<span class="text-gray-500 text-sm">Target: 1000</span>@endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="h-32 mt-6 bg-gray-800 rounded-xl p-4 overflow-hidden border border-gray-700 flex flex-col md:flex-row gap-4">
        <div class="w-full md:w-1/6 flex items-center justify-center border-r border-gray-700"><span class="text-gray-400 font-bold text-sm">RIWAYAT<br>AKTIVITAS</span></div>
        <div class="flex-grow flex items-center space-x-4 overflow-x-auto whitespace-nowrap mask-image-linear">
            @forelse($logs as $log)
                <div class="inline-block px-4 py-2 rounded-lg bg-gray-900 border border-gray-600">
                    <span class="font-bold text-yellow-500">{{ $log->player->name }}</span>
                    @if($log->type == 'reset')<span class="text-red-400 font-bold">KENA RESET! 😱</span><span class="text-gray-400 text-xs">({{ $log->points_added }})</span>
                    @elseif($log->type == 'cekih_bonus')<span class="text-green-400 font-bold">CEKIH! 🎯</span><span class="text-green-400">+{{ $log->points_added }}</span>
                    @elseif($log->type == 'cekih_penalty')<span class="text-red-400">Kena Cekih 🤕</span><span class="text-red-400">{{ $log->points_added }}</span>
                    @else<span class="text-gray-300">Input Putaran</span><span class="{{ $log->points_added >= 0 ? 'text-blue-400' : 'text-red-400' }} font-bold">{{ $log->points_added >= 0 ? '+' : '' }}{{ $log->points_added }}</span>@endif
                </div>
            @empty<div class="text-gray-600 italic">Belum ada aktivitas skor...</div>@endforelse
        </div>
    </div>

    @if($showEndScreen)
        <script>
            var duration = 5 * 1000;
            var animationEnd = Date.now() + duration;
            var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 100 };

            function randomInOut(min, max) { return Math.random() * (max - min) + min; }

            var interval = setInterval(function() {
                var timeLeft = animationEnd - Date.now();
                if (timeLeft <= 0) { return clearInterval(interval); }
                var particleCount = 50 * (timeLeft / duration);
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInOut(0.1, 0.3), y: Math.random() - 0.2 } }));
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInOut(0.7, 0.9), y: Math.random() - 0.2 } }));
            }, 250);
        </script>

        <div class="fixed inset-0 z-50 bg-gray-900/95 flex flex-col items-center justify-center text-center p-4 backdrop-blur-sm">
            
            <h1 class="text-5xl font-black text-white mb-10 tracking-[10px] uppercase drop-shadow-2xl">HASIL AKHIR</h1>

            <div class="flex items-end justify-center gap-4 w-full max-w-4xl mb-12">
                
                @php $rank2 = $players->sortByDesc('score')->skip(1)->first(); @endphp
                <div class="flex flex-col items-center w-1/4">
                    <div class="mb-2 text-gray-300 font-bold text-xl">{{ $rank2->name }}</div>
                    <div class="h-40 w-full bg-gray-400 rounded-t-lg flex items-end justify-center pb-4 shadow-2xl border-t-4 border-gray-300">
                        <span class="text-4xl font-black text-gray-600">2</span>
                    </div>
                    <div class="mt-2 bg-gray-800 px-4 py-1 rounded text-white font-bold">{{ $rank2->score }}</div>
                </div>

                @php $rank1 = $players->sortByDesc('score')->first(); @endphp
                <div class="flex flex-col items-center w-1/3 z-10 -mx-2">
                    <div class="text-6xl mb-4 animate-bounce">👑</div>
                    <div class="mb-2 text-yellow-400 font-black text-3xl uppercase">{{ $rank1->name }}</div>
                    <div class="h-64 w-full bg-gradient-to-b from-yellow-400 to-yellow-600 rounded-t-lg flex items-end justify-center pb-6 shadow-[0_0_50px_rgba(234,179,8,0.6)] border-t-4 border-yellow-200">
                        <span class="text-6xl font-black text-yellow-800">1</span>
                    </div>
                    <div class="mt-4 bg-yellow-600 px-6 py-2 rounded-full text-black font-black text-2xl shadow-lg transform scale-110">
                        {{ $rank1->score }}
                    </div>
                </div>

                @php $rank3 = $players->sortByDesc('score')->skip(2)->first(); @endphp
                <div class="flex flex-col items-center w-1/4">
                    <div class="mb-2 text-orange-300 font-bold text-xl">{{ $rank3->name }}</div>
                    <div class="h-32 w-full bg-orange-700 rounded-t-lg flex items-end justify-center pb-4 shadow-2xl border-t-4 border-orange-500">
                        <span class="text-4xl font-black text-orange-900">3</span>
                    </div>
                    <div class="mt-2 bg-gray-800 px-4 py-1 rounded text-white font-bold">{{ $rank3->score }}</div>
                </div>

            </div>

            @php $rank4 = $players->sortByDesc('score')->skip(3)->first(); @endphp
            @if($rank4)
                <div class="mt-8 bg-red-900/50 p-4 rounded-xl border border-red-500/30 flex items-center gap-4 max-w-lg">
                    <div class="text-4xl">🤡</div>
                    <div class="text-left">
                        <h3 class="text-red-400 font-bold text-xs uppercase">BADUT HARI INI</h3>
                        <p class="text-white font-bold text-lg">{{ $rank4->name }} ({{ $rank4->score }})</p>
                        <p class="text-red-300 text-xs italic">"Jangan lupa traktir kopi sebagai hukuman."</p>
                    </div>
                </div>
            @endif
            
            <div class="flex gap-4 mt-10">
                <a href="{{ route('game.download-pdf', $game->id) }}" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-6 rounded-lg transition">
    🖨️ Cetak Berita Acara
</a>
                <button onclick="window.location.reload()" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition">
                    Main Lagi (Reset)
                </button>
            </div>

        </div>
    @endif
</div>