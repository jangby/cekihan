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
        <div class="fixed inset-0 z-50 bg-black/95 flex flex-col items-center justify-center text-center p-10">
            @if($loser)
                <div class="mb-10 animate-pulse">
                    <div class="text-[100px]">💀</div>
                    <h2 class="text-red-500 text-3xl font-bold uppercase tracking-widest">GAME OVER</h2>
                    <h1 class="text-white text-5xl font-black mt-2">{{ $loser->name }} BANGKRUT!</h1>
                    <p class="text-gray-400 mt-2">Skor menyentuh {{ $loser->score }}</p>
                </div>
            @endif

            <div class="bg-gradient-to-b from-yellow-600 to-yellow-800 p-1 rounded-3xl shadow-2xl transform scale-125">
                <div class="bg-gray-900 rounded-[22px] p-10 border border-yellow-500/30">
                    <div class="text-6xl mb-4">👑</div>
                    <p class="text-yellow-500 font-bold tracking-[0.5em] text-sm uppercase mb-2">THE CHAMPION</p>
                    <h1 class="text-6xl font-black text-white mb-4">{{ $winner->name ?? '...' }}</h1>
                    <div class="text-8xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-yellow-200">{{ $winner->score ?? 0 }}</div>
                </div>
            </div>

            <button onclick="window.location.reload()" class="mt-20 bg-white text-black font-bold py-3 px-8 rounded-full hover:bg-gray-200 transition">Main Lagi (Reset)</button>
        </div>
    @endif
</div>