<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $door->friendly_name }}</h2>
            <a href="{{ route('doors.index') }}" class="text-sm text-gray-600 hover:underline">← Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            {{-- Door details --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Door Details</h3>
                <form method="POST" action="{{ route('doors.update', $door) }}" class="space-y-4">
                    @csrf @method('PATCH')

                    <div>
                        <x-input-label for="friendly_name" value="Friendly Name" />
                        <x-text-input id="friendly_name" name="friendly_name" type="text" class="mt-1 block w-full" :value="old('friendly_name', $door->friendly_name)" required />
                        <x-input-error :messages="$errors->get('friendly_name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="esp32_device_name" value="ESP32 Device Name" />
                        <x-text-input id="esp32_device_name" name="esp32_device_name" type="text" class="mt-1 block w-full" :value="old('esp32_device_name', $door->esp32_device_name)" required />
                        <x-input-error :messages="$errors->get('esp32_device_name')" class="mt-1" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Save</x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Alarm Slots --}}
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Alarm Slots</h3>
                    <form method="POST" action="{{ route('doors.clear-wakeup', $door) }}" onsubmit="return confirm('Clear wakeup status?')">
                        @csrf
                        <button class="text-xs px-3 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200 border border-orange-300">
                            [DEV] Clear Wakeup Status
                        </button>
                    </form>
                </div>

                {{-- Existing slots --}}
                @if($door->alarmSlots->isNotEmpty())
                    <div class="space-y-4 mb-6">
                        @foreach($door->alarmSlots as $slot)
                            @php $status = $slotStatuses[$slot->id]; @endphp

                            <div class="border rounded-lg p-4 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $slot->is_active ? $status['class'] : 'bg-gray-100 text-gray-400' }}">
                                        <span class="{{ $slot->is_active ? $status['dot'] : 'text-gray-400' }}">●</span>
                                        {{ $status['label'] }}
                                    </span>
                                </div>

                                <form method="POST" action="{{ route('alarm-slots.update', $slot) }}" class="space-y-4">
                                    @csrf @method('PATCH')

                                    @include('doors._slot_fields', [
                                        'slot'   => $slot,
                                        'music'  => $music,
                                        'prefix' => 'slot-'.$slot->id,
                                    ])

                                    <div class="flex items-center justify-between pt-1">
                                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" {{ $slot->is_active ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                            Active
                                        </label>
                                        <x-primary-button>Save</x-primary-button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('alarm-slots.destroy', $slot) }}"
                                    onsubmit="return confirm('Remove this slot?')" class="flex justify-end border-t pt-3">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-500 hover:underline">Remove slot</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 mb-6">No alarm slots yet.</p>
                @endif

                {{-- Add slot form --}}
                <div class="border-t pt-5">
                    <h4 class="text-sm font-medium text-gray-700 mb-4">Add New Slot</h4>
                    <form method="POST" action="{{ route('alarm-slots.store', $door) }}" class="space-y-4">
                        @csrf

                        @include('doors._slot_fields', [
                            'music'  => $music,
                            'prefix' => 'new-slot',
                        ])

                        <div class="flex justify-end">
                            <x-primary-button>Add Slot</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
