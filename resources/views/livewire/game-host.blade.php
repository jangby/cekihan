<div class="min-h-screen bg-gray-900 flex flex-col items-center justify-center p-6 text-white">
    <div class="absolute top-10 text-center">
        <p class="text-gray-400 text-sm uppercase tracking-widest">KODE RUANGAN</p>
        <h1 class="text-6xl font-black text-yellow-500 tracking-widest">{{ $game->room_code }}</h1>
    </div>

    <div class="bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-700 flex flex-col items-center text-center max-w-2xl w-full">
        
        {{-- PERBAIKAN DISINI: Kita hitung jumlah pemain langsung dari array --}}
        @if(count($players) < 4)
            
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-white mb-2">
                    Menunggu <span class="text-yellow-400">Pemain {{ count($players) + 1 }}</span>
                </h2>
                <p class="text-gray-400">Scan QR Code ini untuk bergabung.</p>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-lg mb-6">
                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)->generate($qrCodeUrl) !!}
            </div>

            <p class="text-xs text-gray-500 font-mono bg-gray-900 px-3 py-1 rounded">
                {{ $qrCodeUrl }}
            </p>

            <div class="animate-pulse text-yellow-500 font-bold mt-4">
                Menunggu scan... ({{ count($players) }}/4 Masuk)
            </div>

        @else
            <div class="text-center py-10">
                <div class="text-6xl mb-4 animate-bounce">✅</div>
                <h2 class="text-4xl font-bold text-green-400 mb-6">Semua Pemain Siap!</h2>
                
                <p class="text-gray-400 mb-8">Pastikan semua pemain sudah stand-by di HP masing-masing.</p>

                <button wire:click="startGame" class="bg-yellow-500 hover:bg-yellow-600 text-black font-black py-4 px-10 rounded-full text-2xl shadow-lg transform transition hover:scale-105 hover:rotate-1">
                    MULAI PERMAINAN 🚀
                </button>
            </div>
        @endif

    </div>

    <div class="mt-12 w-full max-w-4xl">
        <div class="grid grid-cols-4 gap-4">
            @for ($i = 1; $i <= 4; $i++)
                @php 
                    // Cek apakah pemain di posisi $i sudah ada di database
                    $player = collect($players)->firstWhere('position', $i);
                @endphp

                <div class="bg-gray-800 rounded-xl p-4 border-2 {{ $player ? 'border-green-500 bg-gray-700' : 'border-gray-700 border-dashed' }} flex flex-col items-center justify-center h-32 relative overflow-hidden transition-all duration-500">
                    @if($player)
                        <div class="absolute top-0 right-0 bg-green-500 text-black text-[10px] font-bold px-2 py-1 rounded-bl-lg">
                            READY
                        </div>
                        <span class="text-3xl mb-1">👤</span>
                        <h3 class="font-bold text-lg truncate w-full text-center text-yellow-400">{{ $player['name'] }}</h3>
                        <p class="text-xs text-gray-300">Pemain {{ $i }}</p>
                    @else
                        <span class="text-3xl mb-1 opacity-20">👤</span>
                        <h3 class="font-bold text-lg text-gray-600">Kosong</h3>
                        <p class="text-xs text-gray-600">Pemain {{ $i }}</p>
                    @endif
                </div>
            @endfor
        </div>
    </div>
</div>