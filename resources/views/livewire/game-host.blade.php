<script src="https://cdn.tailwindcss.com"></script>

<div class="min-h-screen bg-gray-900 flex flex-col items-center justify-center p-4 md:p-6 text-white font-sans overflow-x-hidden">
    
    <div class="relative md:absolute top-0 md:top-10 text-center mb-6 md:mb-0 mt-4 md:mt-0 z-10">
        <p class="text-gray-400 text-[10px] md:text-sm uppercase tracking-[0.2em]">KODE RUANGAN</p>
        <h1 class="text-5xl md:text-6xl font-black text-yellow-500 tracking-widest drop-shadow-lg">{{ $game->room_code }}</h1>
    </div>

    <div class="bg-gray-800 p-6 md:p-8 rounded-3xl shadow-2xl border border-gray-700 flex flex-col items-center text-center w-full max-w-sm md:max-w-2xl relative z-20">
        
        @if(count($players) < 4)
            
            <div class="mb-4 md:mb-6">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                    Menunggu <span class="text-yellow-400">Pemain {{ count($players) + 1 }}</span>
                </h2>
                <p class="text-gray-400 text-xs md:text-base">Scan QR Code untuk bergabung.</p>
            </div>

            <div class="bg-white p-3 md:p-4 rounded-xl shadow-lg mb-4 md:mb-6">
                <div class="hidden md:block">
                    {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)->generate($qrCodeUrl) !!}
                </div>
                <div class="block md:hidden">
                    {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate($qrCodeUrl) !!}
                </div>
            </div>

            <p class="text-[10px] md:text-xs text-gray-500 font-mono bg-gray-900 px-3 py-1 rounded mb-4 max-w-full truncate">
                {{ $qrCodeUrl }}
            </p>

            <button wire:click="resetLobby" 
                onclick="return confirm('⚠️ PERINGATAN:\n\nApakah Anda yakin ingin mereset lobi?\nSemua pemain yang sudah masuk akan dikeluarkan!')"
                class="text-[10px] md:text-xs bg-red-900/40 hover:bg-red-900 text-red-300 px-4 py-2 rounded-full transition border border-red-500/30 flex items-center gap-2">
                <span>🗑️</span> Reset / Kick Semua Pemain
            </button>

            <div class="animate-pulse text-yellow-500 font-bold mt-4 text-sm md:text-base">
                Menunggu scan... ({{ count($players) }}/4 Masuk)
            </div>

        @else
            
            <div class="text-center py-6 md:py-10">
                <div class="text-5xl md:text-6xl mb-4 animate-bounce">✅</div>
                <h2 class="text-2xl md:text-4xl font-bold text-green-400 mb-4 md:mb-6">Semua Pemain Siap!</h2>
                
                <p class="text-gray-400 mb-6 md:mb-8 text-sm md:text-base px-4">
                    Pastikan semua pemain sudah stand-by di HP masing-masing.
                </p>

                <button wire:click="startGame" class="bg-yellow-500 hover:bg-yellow-600 text-black font-black py-3 md:py-4 px-8 md:px-10 rounded-full text-lg md:text-2xl shadow-lg transform transition hover:scale-105 hover:rotate-1 w-full md:w-auto">
                    MULAI PERMAINAN 🚀
                </button>
                
                <div class="mt-6">
                    <button wire:click="resetLobby" 
                        onclick="return confirm('Reset ulang lobi?')"
                        class="text-xs text-red-400 hover:text-red-300 underline">
                        Batal & Reset Ulang
                    </button>
                </div>
            </div>

        @endif

    </div>

    <div class="mt-8 md:mt-12 w-full max-w-4xl">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            
            @for ($i = 1; $i <= 4; $i++)
                @php 
                    $player = collect($players)->firstWhere('position', $i);
                @endphp

                <div class="bg-gray-800 rounded-xl p-3 md:p-4 border-2 {{ $player ? 'border-green-500 bg-gray-700' : 'border-gray-700 border-dashed' }} flex flex-col items-center justify-center h-24 md:h-32 relative overflow-hidden transition-all duration-500">
                    
                    @if($player)
                        <div class="absolute top-0 right-0 bg-green-500 text-black text-[8px] md:text-[10px] font-bold px-2 py-1 rounded-bl-lg">
                            READY
                        </div>
                        <span class="text-2xl md:text-3xl mb-1">👤</span>
                        <h3 class="font-bold text-sm md:text-lg truncate w-full text-center text-yellow-400">
                            {{ $player['name'] }}
                        </h3>
                        <p class="text-[10px] md:text-xs text-gray-300">Pemain {{ $i }}</p>
                    @else
                        <span class="text-2xl md:text-3xl mb-1 opacity-20">👤</span>
                        <h3 class="font-bold text-sm md:text-lg text-gray-600">Kosong</h3>
                        <p class="text-[10px] md:text-xs text-gray-600">Pemain {{ $i }}</p>
                    @endif
                    
                </div>
            @endfor

        </div>
    </div>
    
    <div class="mt-8 text-gray-600 text-[10px] md:text-xs text-center">
        Sistem Remi Digital v2.0 • Realtime VPS
    </div>

</div>