<div class="min-h-screen text-white pb-20 transition-colors duration-500 {{ $isUnderAttack ? 'bg-red-900' : 'bg-gray-900' }}">
    
    @if($isGameEnded)
        <div class="fixed inset-0 z-[100] flex flex-col items-center justify-center p-6 text-center overflow-hidden
            {{-- Background Beda-beda tiap Juara --}}
            @if($myRank == 1) bg-gradient-to-b from-yellow-700 to-yellow-900
            @elseif($myRank == 4) bg-gray-900 grayscale
            @else bg-blue-900
            @endif">
            
            @if($myRank == 1)
                {{-- JUARA 1: EMAS & PUJIAN --}}
                <div class="animate-bounce text-8xl mb-4">🏆</div>
                <h2 class="text-yellow-400 font-bold tracking-widest text-xl uppercase mb-1">CONGRATULATIONS</h2>
                <h1 class="text-5xl font-black text-white mb-6 drop-shadow-lg">KAMU JUARA 1!</h1>
                <p class="text-yellow-200 italic mb-10">"Skill menyala abangkuh! 🔥"</p>
            
            @elseif($myRank == 4)
                {{-- JUARA 4: EJEKAN & SURAM --}}
                <div class="animate-pulse text-8xl mb-4">🤡</div>
                <h2 class="text-gray-500 font-bold tracking-widest text-xl uppercase mb-1">YAHHHH...</h2>
                <h1 class="text-5xl font-black text-gray-300 mb-6">POSISI BUNCIT</h1>
                <p class="text-gray-400 italic mb-10 text-sm">
                    "Malu sama kucing... 🐈<br>
                    Mending uninstall aja bang."
                </p>

            @else
                {{-- JUARA 2 & 3: NORMAL --}}
                <div class="text-8xl mb-4">👏</div>
                <h2 class="text-blue-300 font-bold tracking-widest text-xl uppercase mb-1">NICE TRY</h2>
                <h1 class="text-5xl font-black text-white mb-6">JUARA {{ $myRank }}</h1>
                <p class="text-blue-200 italic mb-10">"Dikit lagi hoki, coba lagi tahun depan."</p>
            @endif

            <div class="bg-black/30 p-6 rounded-xl w-full max-w-sm border border-white/10 mb-6 backdrop-blur-md">
                <p class="text-xs uppercase text-gray-300">Skor Akhir Kamu</p>
                <p class="text-6xl font-black text-white mt-2 tracking-tighter">{{ $player->score }}</p>
            </div>

            <a href="{{ route('game.download-pdf', $game->id) }}" target="_blank" 
   class="bg-white text-black font-bold py-4 px-8 rounded-full shadow-lg hover:bg-gray-200 transition transform hover:scale-105 active:scale-95 flex items-center space-x-2">
    <span>🖨️</span>
    <span>DOWNLOAD SERTIFIKAT</span>
</a>
        </div>
    @endif

    <audio id="sfx-warning" src="/sounds/warning.mp3" loop></audio>
    <audio id="sfx-boom" src="/sounds/boom.mp3"></audio>
    @script
    <script>
        $wire.on('play-sound', (e) => {
            const warning = document.getElementById('sfx-warning');
            const boom = document.getElementById('sfx-boom');
            if(e.type === 'warning') { warning.currentTime=0; warning.play(); }
            else if (e.type === 'boom') { warning.pause(); boom.currentTime=0; boom.play(); }
        });
        $wire.on('hide-safe-message', () => {
            document.getElementById('sfx-warning').pause();
            setTimeout(() => $wire.set('showSafeMessage', false), 3000);
        });
        $wire.on('hide-bomb', () => { setTimeout(() => $wire.set('showBombAnimation', false), 2500); });
    </script>
    @endscript

    @if($isUnderAttack)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-red-900/90 backdrop-blur-sm animate-pulse">
            <div class="text-center p-6">
                <div class="text-8xl mb-4 animate-bounce">🚨</div>
                <h2 class="text-3xl font-black text-white uppercase tracking-widest mb-2">AWAS!</h2>
                <div class="bg-red-600 text-white px-6 py-4 rounded-xl border-4 border-white shadow-2xl transform rotate-2">
                    <p class="text-lg font-bold uppercase mb-1">DARI PEMAIN:</p>
                    <h1 class="text-4xl font-black">{{ $attackerName }}</h1>
                </div>
                <p class="text-red-200 mt-6 font-bold text-lg animate-pulse">SEDANG MENCEKIHMU!<br>HATI-HATI!</p>
            </div>
        </div>
    @endif

    @if($showBombAnimation)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 backdrop-blur-md">
            <div class="text-center animate-ping"><div class="text-[150px]">💣</div><h2 class="text-5xl font-black text-red-500 mt-4 tracking-tighter">DUAAARR!</h2><p class="text-white mt-2">Poinmu dicuri {{ $attackerName }}!</p></div>
        </div>
    @endif

    @if($showSafeMessage)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-green-800/90 backdrop-blur-md">
            <div class="text-center transform scale-110"><div class="text-[100px] animate-bounce">😮‍💨</div><h2 class="text-4xl font-bold text-green-200 mt-4">HUFT... AMAN!</h2><p class="text-green-100 font-medium mt-2">{{ $attackerName }} gak jadi nyerang.</p></div>
        </div>
    @endif

    <div class="relative z-10">
        <div class="bg-gray-800 p-6 rounded-b-3xl shadow-lg border-b border-gray-700 sticky top-0">
            <div class="flex justify-between items-end">
                <div><span class="text-blue-400 font-bold text-sm tracking-widest">POSISI {{ $player->position }}</span><h1 class="text-3xl font-bold truncate max-w-[150px]">{{ $player->name }}</h1></div>
                <div class="text-right"><span class="block text-gray-400 text-xs">SKOR SAYA</span><span class="text-5xl font-black {{ $isUnderAttack ? 'text-red-400' : 'text-yellow-500' }}">{{ $player->score }}</span></div>
            </div>
            @if(session()->has('message'))<div class="mt-4 bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-lg animate-pulse">{{ session('message') }}</div>@endif
        </div>

        <div class="p-6 space-y-8">
            @if($player->has_submitted_round)
                <div class="bg-gray-800 p-8 rounded-2xl border border-gray-700 text-center flex flex-col items-center justify-center min-h-[300px]">
                    <div class="text-6xl mb-4 animate-spin-slow">⏳</div>
                    <h2 class="text-2xl font-bold text-yellow-500 mb-2">MENUNGGU LAWAN</h2>
                    <div class="bg-gray-700 rounded-lg p-4 mt-4 w-full"><p class="text-gray-400 text-xs uppercase">Input Kamu:</p><p class="text-white font-black text-3xl">{{ $player->temp_round_score }}</p></div>
                </div>
            @else
                <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700">
                    <h2 class="text-gray-400 text-sm font-bold uppercase mb-4">Input Skor Putaran</h2>
                    <form wire:submit.prevent="submitScore">
                        <div class="flex items-center space-x-2 mb-4">
                            <button type="button" wire:click="$set('inputScore', {{ $inputScore - 5 }})" class="bg-red-900 text-red-200 p-4 rounded-xl font-bold active:scale-90">-5</button>
                            <input type="number" wire:model="inputScore" step="5" class="w-full bg-black border-2 border-gray-600 text-center text-3xl font-bold text-white py-3 rounded-xl focus:outline-none" placeholder="0">
                            <button type="button" wire:click="$set('inputScore', {{ $inputScore + 5 }})" class="bg-green-900 text-green-200 p-4 rounded-xl font-bold active:scale-90">+5</button>
                        </div>
                        @error('inputScore') <span class="text-red-500 text-sm block mb-2">{{ $message }}</span> @enderror
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl text-lg shadow-lg active:scale-95">KIRIM SKOR</button>
                    </form>
                </div>
                <button wire:click="openCekihModal" class="w-full bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-500 text-white font-black py-6 rounded-2xl text-2xl shadow-xl border-b-4 border-red-800 active:border-b-0 active:translate-y-1 transition group"><span class="inline-block group-active:scale-90 transition">⚡ CEKIH LAWAN!</span></button>
            @endif
        </div>
    </div>

    @if($showCekihModal)
        <div class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-gray-800 w-full max-w-sm rounded-3xl p-6 border border-gray-600 shadow-2xl">
                @if(!$cekihTargetId)
                    <h3 class="text-2xl font-bold text-white text-center mb-6">Siapa Targetnya?</h3>
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        @foreach($this->validCekihTargets as $target)
                            <button wire:click="lockTarget({{ $target->id }})" class="p-4 rounded-xl border-2 border-gray-600 bg-gray-700 hover:bg-gray-600 active:scale-95"><span class="block text-xs text-gray-400">Pemain {{ $target->position }}</span><span class="block text-xl font-bold">{{ $target->name }}</span></button>
                        @endforeach
                    </div>
                    <button wire:click="$set('showCekihModal', false)" class="w-full py-3 bg-gray-700 rounded-xl font-bold text-gray-300">Tutup</button>
                @else
                    <div class="text-center mb-6"><div class="animate-pulse text-red-500 text-5xl mb-2">🎯</div><h3 class="text-xl text-white">Target Terkunci:</h3><h2 class="text-3xl font-black text-red-400 mt-1">{{ \App\Models\Player::find($cekihTargetId)->name }}</h2><p class="text-xs text-gray-500 mt-2">Layar mereka sedang merah sekarang...</p></div>
                    <div class="mb-6">
                        <label class="text-gray-400 text-xs font-bold mb-2 block">PILIH NOMINAL</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach([50, 150, 250, 350] as $amt) <button wire:click="$set('cekihAmount', {{ $amt }})" class="py-2 rounded-lg text-sm font-bold border transition {{ $cekihAmount == $amt ? 'bg-white text-black border-white scale-110' : 'bg-transparent text-gray-400 border-gray-600' }}">{{ $amt }}</button> @endforeach
                        </div>
                    </div>
                    <div class="flex space-x-3"><button wire:click="cancelCekih" class="flex-1 py-4 bg-gray-700 hover:bg-gray-600 rounded-xl font-bold text-gray-300 border-b-4 border-gray-900">Gak Jadi 🫣</button><button wire:click="executeCekih" class="flex-1 py-4 bg-red-600 hover:bg-red-500 rounded-xl font-bold text-white shadow-lg border-b-4 border-red-800">SERANG! 💥</button></div>
                @endif
            </div>
        </div>
    @endif
</div>