<div class="min-h-screen bg-gray-900 text-white flex flex-col items-center justify-center p-6">
    
    @if(!$joined)
        <div class="w-full max-w-md bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-700">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-black text-yellow-500 mb-2">JOIN GAME</h1>
                <p class="text-gray-400">Kamu akan menjadi:</p>
                <div class="mt-2 inline-block bg-blue-600 px-4 py-1 rounded-full text-sm font-bold">
                    PEMAIN {{ $position }}
                </div>
            </div>

            <form wire:submit.prevent="joinGame" class="space-y-6">
                <div>
                    <label class="block text-gray-400 text-sm font-bold mb-2">Nama Panggilan</label>
                    <input type="text" wire:model="name" 
                        class="w-full bg-gray-900 border-2 border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 placeholder-gray-600 text-lg text-center"
                        placeholder="Contoh: Udin" autofocus>
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <button type="submit" 
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-black py-4 rounded-xl text-xl shadow-lg transform active:scale-95 transition">
                    MASUK PERMAINAN 🚀
                </button>
            </form>
        </div>

    @else
        <div class="text-center animate-pulse">
            <div class="text-6xl mb-4">⏳</div>
            <h2 class="text-2xl font-bold text-white mb-2">Selamat Datang, {{ $name }}!</h2>
            <p class="text-gray-400">Menunggu Host memulai permainan...</p>
            
            <div class="mt-8 p-4 bg-gray-800 rounded-lg border border-gray-700">
                <p class="text-sm text-gray-500">Posisi Kamu</p>
                <p class="text-3xl font-bold text-blue-400">PEMAIN {{ $position }}</p>
            </div>
        </div>
    @endif
</div>