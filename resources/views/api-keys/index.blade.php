<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">API Keys</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            {{-- Create form --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Create Key</h3>
                <form method="POST" action="{{ route('api-keys.store') }}" class="flex gap-3">
                    @csrf
                    <div class="flex-1">
                        <x-text-input name="name" type="text" class="block w-full" :value="old('name')" placeholder="ESP32 Front Door" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <x-primary-button>Generate</x-primary-button>
                </form>
            </div>

            {{-- Key list --}}
            <div class="bg-white shadow rounded-lg divide-y">
                @forelse($apiKeys as $apiKey)
                    <div class="px-6 py-4 space-y-3">
                        {{-- Update form (name + key) --}}
                        <form id="update-key-{{ $apiKey->id }}" method="POST" action="{{ route('api-keys.update', $apiKey) }}">
                            @csrf @method('PATCH')
                        </form>

                        {{-- Delete form --}}
                        <form id="delete-key-{{ $apiKey->id }}" method="POST" action="{{ route('api-keys.destroy', $apiKey) }}"
                            onsubmit="return confirm('Delete this key?')">
                            @csrf @method('DELETE')
                        </form>

                        {{-- Name row --}}
                        <div class="flex items-center gap-2">
                            <input type="text" name="name" form="update-key-{{ $apiKey->id }}" value="{{ $apiKey->name }}"
                                class="flex-1 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required />
                            <button type="submit" form="update-key-{{ $apiKey->id }}" class="text-sm text-indigo-600 hover:underline">Save</button>
                            <button type="submit" form="delete-key-{{ $apiKey->id }}" class="text-sm text-red-600 hover:underline">Delete</button>
                        </div>

                        {{-- Key row --}}
                        <div class="flex items-center gap-2">
                            <input type="text" name="key" form="update-key-{{ $apiKey->id }}" value="{{ $apiKey->key }}"
                                class="flex-1 border-gray-300 rounded-md shadow-sm text-xs font-mono focus:ring-indigo-500 focus:border-indigo-500" required />
                            <button type="button"
                                onclick="navigator.clipboard.writeText(this.previousElementSibling.value).then(() => { this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy', 1500) })"
                                class="shrink-0 text-xs text-gray-500 hover:text-gray-700 border border-gray-300 rounded px-2 py-1">Copy</button>
                        </div>

                        <p class="text-xs text-gray-400">
                            Created {{ $apiKey->created_at->format('d M Y') }}
                            @if($apiKey->last_used_at)
                                · Last used {{ $apiKey->last_used_at->diffForHumans() }}
                            @else
                                · Never used
                            @endif
                        </p>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-gray-400">No API keys yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
