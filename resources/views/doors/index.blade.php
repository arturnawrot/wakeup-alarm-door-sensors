<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Doors</h2>
            <a href="{{ route('doors.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                Add Door
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if(session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            @php
                $allStatuses = [
                    ['label' => 'Upcoming',   'class' => 'bg-blue-100 text-blue-600',    'dot' => 'text-blue-400'],
                    ['label' => 'Waiting…',   'class' => 'bg-yellow-100 text-yellow-700','dot' => 'text-yellow-500'],
                    ['label' => 'Woke up ✓',  'class' => 'bg-green-100 text-green-700',  'dot' => 'text-green-500'],
                    ['label' => 'Missed',     'class' => 'bg-red-100 text-red-600',      'dot' => 'text-red-500'],
                    ['label' => 'Not today',  'class' => 'bg-gray-100 text-gray-400',    'dot' => 'text-gray-400'],
                ];
                $activeLabels = collect($slotStatuses)->flatten(1)->pluck('label')->unique()->values()->toArray();
            @endphp

            <div class="bg-white shadow rounded-lg px-5 py-3 flex items-center gap-2 flex-wrap">
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wide mr-1">Legend</span>
                @foreach($allStatuses as $s)
                    @php $active = in_array($s['label'], $activeLabels); @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-opacity
                        {{ $active ? $s['class'] . ' opacity-100 ring-1 ring-inset ring-current' : 'bg-gray-50 text-gray-300 opacity-60' }}">
                        <span class="{{ $active ? $s['dot'] : 'text-gray-300' }}">●</span>
                        {{ $s['label'] }}
                    </span>
                @endforeach
            </div>

            @forelse($doors as $door)
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $door->friendly_name }}</h3>
                                @if($door->is_alarm_firing)
                                    <span class="px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700 rounded-full">ALARM FIRING</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Device: <code class="bg-gray-100 px-1 rounded">{{ $door->esp32_device_name }}</code></p>
                            @if($door->last_interacted_at)
                                <p class="text-sm text-gray-500">Last interaction: {{ $door->last_interacted_at->diffForHumans() }}</p>
                            @else
                                <p class="text-sm text-gray-400">No interactions recorded</p>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('doors.edit', $door) }}" class="text-sm text-indigo-600 hover:underline">Edit / Slots</a>
                            <form method="POST" action="{{ route('doors.destroy', $door) }}" onsubmit="return confirm('Delete this door?')">
                                @csrf @method('DELETE')
                                <button class="text-sm text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>

                    @if($door->alarmSlots->isNotEmpty())
                        <div class="mt-4 border-t pt-4">
                            <p class="text-xs font-medium text-gray-500 uppercase mb-2">Alarm Slots</p>
                            <div class="space-y-1">
                                @foreach($door->alarmSlots as $slot)
                                    @php $status = $slotStatuses[$door->id][$slot->id]; @endphp
                                    <div class="flex items-center gap-3 text-sm text-gray-700">
                                        <span class="{{ $slot->is_active ? $status['dot'] : 'text-gray-400' }}">●</span>
                                        <span>{{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}</span>
                                        <span class="text-gray-400">{{ implode(', ', array_map('ucfirst', $slot->days)) }}</span>
                                        @if($slot->music)
                                            <span class="text-gray-400">♪ {{ $slot->music->name }}</span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $status['class'] }}">{{ $status['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white shadow rounded-lg p-12 text-center text-gray-400">
                    No doors yet. <a href="{{ route('doors.create') }}" class="text-indigo-600 hover:underline">Add one</a>.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
