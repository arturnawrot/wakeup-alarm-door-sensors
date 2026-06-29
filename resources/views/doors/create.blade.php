<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Door</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('doors.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="friendly_name" value="Friendly Name" />
                        <x-text-input id="friendly_name" name="friendly_name" type="text" class="mt-1 block w-full" :value="old('friendly_name')" placeholder="Front Door" required />
                        <x-input-error :messages="$errors->get('friendly_name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="esp32_device_name" value="ESP32 Device Name" />
                        <x-text-input id="esp32_device_name" name="esp32_device_name" type="text" class="mt-1 block w-full" :value="old('esp32_device_name')" placeholder="esp32-front-door" required />
                        <x-input-error :messages="$errors->get('esp32_device_name')" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('doors.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                        <x-primary-button>Create Door</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
