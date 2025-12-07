<script src="https://cdn.tailwindcss.com"></script>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<div class="min-h-screen bg-gray-900 text-white p-2 md:p-4 flex flex-col font-sans overflow-x-hidden">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-2 border-b border-gray-800 pb-4 md:border-0">
        <div class="text-center md:text-left">
            <h1 class="text-xs md:text-xl font-bold text-gray-500">ROOM: <span class="text-yellow-500">{{ $game->room_code }}</span></h1>
        </div>
        <div class="text-center">
            <h2 class="text-2xl md:text-4xl font-black text-white tracking-widest">PAPAN SKOR</h2>
        </div>
        <div class="text-center md:text-right">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] md:text-xs font-medium bg-green-900 text-green-300 animate-pulse">
                ● LIVE
            </span>
        </div>
    </div>

    <div class="flex-grow grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-6 content-start">
        @php 
            $someonePassed100 = $players->max('score') >= 100; 
        @endphp

        @foreach($players as $player)
            @php
                $isJongkok = $someonePassed100 && $player->score < 100;
                $bgClass = $isJongkok ? 'bg-red-900 border-red-500' : 'bg-gray-800 border-gray-700';
            @endphp
            
            <div class="relative flex flex-col items-center justify-between rounded-xl md:rounded-3xl border-2 md:border-4 {{ $bgClass }} p-2 md:p-6 shadow-lg transition-all duration-500 min-h-[180px] md:min-h-[300px]">
                
                <div class="absolute top-2 left-2 bg-gray-700 px-2 py-0.5 rounded text-[10px] md:text-sm font-bold text-gray-400">
                    P{{ $player->position }}
                </div>

                @if($isJongkok)
                    <div class="absolute top-2 right-2 animate-bounce z-10">
                        <span class="bg-red-600 text-white font-black px-2 py-1 rounded-full shadow border border-white transform rotate-12 inline-block text-[10px] md:text-sm">
                            JONGKOK!
                        </span>
                    </div>
                @endif

                <div class="mt-6 md:mt-8 text-center w-full px-1">
                    <h3 class="text-sm md:text-2xl font-bold truncate">{{ $player->name }}</h3>
                </div>

                <div class="text-center my-1 md:my-4 flex-grow flex flex-col justify-center">
                    <span class="text-4xl md:text-8xl font-black leading-none {{ $player->score < 0 ? 'text-red-400' : 'text-yellow-400' }}">
                        {{ $player->score }}
                    </span>
                    <p class="text-gray-400 text-[9px] md:text-xs uppercase tracking-widest mt-1">TOTAL POIN</p>
                </div>

                <div class="w-full bg-black/20 rounded md:rounded-xl p-1 md:p-2 text-center mt-auto">
                    @if($player->score >= 1000)
                        <span class="text-green-400 font-bold text-[10px] md:text-base">🏆 WINNER</span>
                    @elseif($player->score <= -500)
                        <span class="text-red-500 font-bold text-[10px] md:text-base">💀 GAME OVER</span>
                    @else
                        <span class="text-gray-500 text-[10px] md:text-sm">Target: 1000</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-800 rounded-xl p-3 border border-gray-700">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 font-bold text-xs">LOG AKTIVITAS</span>
            <span class="text-[10px] text-gray-500 italic">Geser untuk melihat -></span>
        </div>
        <div class="flex space-x-2 overflow-x-auto whitespace-nowrap pb-2">
            @forelse($logs as $log)
                <div class="inline-block px-3 py-1 rounded bg-gray-900 border border-gray-600 text-[10px] md:text-sm">
                    <span class="font-bold text-yellow-500">{{ $log->player->name }}</span>
                    @if($log->type == 'reset') <span class="text-red-400 font-bold">RESET!</span>
                    @elseif($log->type == 'cekih_bonus') <span class="text-green-400 font-bold">CEKIH!</span>
                    @elseif($log->type == 'cekih_penalty') <span class="text-red-400">Kena Cekih</span>
                    @else <span class="text-gray-300">Input</span>
                    @endif
                    <span class="font-bold {{ $log->points_added >= 0 ? 'text-blue-400' : 'text-red-400' }}">
                        {{ $log->points_added >= 0 ? '+' : '' }}{{ $log->points_added }}
                    </span>
                </div>
            @empty
                <div class="text-gray-600 italic text-xs">Belum ada data...</div>
            @endforelse
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

        <div class="fixed inset-0 z-50 bg-gray-900/95 flex flex-col items-center justify-start md:justify-center pt-10 md:pt-0 p-4 backdrop-blur-sm overflow-y-auto">
            
            <h1 class="text-3xl md:text-5xl font-black text-white mb-6 tracking-widest uppercase drop-shadow-2xl">
                HASIL AKHIR
            </h1>

            <div class="flex items-end justify-center gap-2 w-full max-w-4xl mb-8 transform md:scale-100 origin-bottom">
                
                @php $rank2 = $players->sortByDesc('score')->skip(1)->first(); @endphp
                <div class="flex flex-col items-center w-1/4">
                    <div class="mb-1 text-gray-300 font-bold text-xs md:text-xl truncate w-full text-center">{{ $rank2->name }}</div>
                    <div class="h-24 md:h-40 w-full bg-gray-400 rounded-t-lg flex items-end justify-center pb-2 border-t-4 border-gray-300">
                        <span class="text-2xl font-black text-gray-600">2</span>
                    </div>
                    <div class="mt-2 bg-gray-800 px-2 py-0.5 rounded text-white font-bold text-xs">{{ $rank2->score }}</div>
                </div>

                @php $rank1 = $players->sortByDesc('score')->first(); @endphp
                <div class="flex flex-col items-center w-1/3 z-10 -mx-2">
                    <div class="text-4xl mb-2 animate-bounce">👑</div>
                    <div class="mb-1 text-yellow-400 font-black text-sm md:text-3xl truncate w-full text-center">{{ $rank1->name }}</div>
                    <div class="h-40 md:h-64 w-full bg-gradient-to-b from-yellow-400 to-yellow-600 rounded-t-lg flex items-end justify-center pb-4 border-t-4 border-yellow-200 shadow-xl">
                        <span class="text-4xl md:text-6xl font-black text-yellow-900">1</span>
                    </div>
                    <div class="mt-[-15px] bg-yellow-500 px-4 py-1 rounded-full text-black font-black text-lg shadow-lg relative z-20">
                        {{ $rank1->score }}
                    </div>
                </div>

                @php $rank3 = $players->sortByDesc('score')->skip(2)->first(); @endphp
                <div class="flex flex-col items-center w-1/4">
                    <div class="mb-1 text-orange-300 font-bold text-xs md:text-xl truncate w-full text-center">{{ $rank3->name }}</div>
                    <div class="h-20 md:h-32 w-full bg-orange-700 rounded-t-lg flex items-end justify-center pb-2 border-t-4 border-orange-500">
                        <span class="text-2xl font-black text-orange-900">3</span>
                    </div>
                    <div class="mt-2 bg-gray-800 px-2 py-0.5 rounded text-white font-bold text-xs">{{ $rank3->score }}</div>
                </div>
            </div>

            @php $rank4 = $players->sortByDesc('score')->skip(3)->first(); @endphp
            @if($rank4)
                <div class="w-full max-w-xs bg-red-900/50 p-3 rounded-xl border border-red-500/30 flex items-center gap-3 mx-auto mb-6">
                    <div class="text-3xl">🤡</div>
                    <div class="text-left">
                        <h3 class="text-red-400 font-bold text-[10px] uppercase">BADUT HARI INI</h3>
                        <p class="text-white font-bold text-sm">{{ $rank4->name }} ({{ $rank4->score }})</p>
                    </div>
                </div>
            @endif
            
            <div class="flex flex-col gap-3 w-full max-w-xs">
                <a href="{{ route('game.download-pdf', $game->id) }}" class="bg-blue-600 text-white font-bold py-3 px-4 rounded-lg w-full flex justify-center items-center gap-2">
                    <span>🖨️</span> Cetak PDF
                </a>
                <button onclick="window.location.reload()" class="bg-gray-700 text-white font-bold py-3 px-4 rounded-lg w-full">
                    Main Lagi
                </button>
            </div>
        </div>
    @endif
</div>