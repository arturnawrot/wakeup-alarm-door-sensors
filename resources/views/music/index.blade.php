<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Music</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            {{-- Upload form --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Upload Track</h3>
                <form method="POST" action="{{ route('music.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Track Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" placeholder="Morning Alarm" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="file" value="MP3 File (max 50 MB)" />
                        <input id="file" name="file" type="file" accept=".mp3,audio/mpeg"
                            class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" required />
                        <x-input-error :messages="$errors->get('file')" class="mt-1" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Upload</x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Track list --}}
            <div class="bg-white shadow rounded-lg divide-y">
                @forelse($music as $track)
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ $track->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $track->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <audio controls src="{{ $track->url }}" class="h-8"></audio>
                            <form method="POST" action="{{ route('music.destroy', $track) }}" onsubmit="return confirm('Delete this track?')">
                                @csrf @method('DELETE')
                                <button class="text-sm text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-gray-400">No tracks uploaded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
