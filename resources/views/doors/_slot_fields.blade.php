{{-- $slot (AlarmSlot|null), $music (Collection), $prefix (string for unique IDs) --}}
<div class="grid grid-cols-2 gap-4">
    <div>
        <x-input-label :for="$prefix.'-start'" value="Start Time" />
        <x-text-input :id="$prefix.'-start'" name="start_time" type="time" class="mt-1 block w-full"
            :value="old('start_time', isset($slot) ? substr($slot->start_time, 0, 5) : '')" required />
        <x-input-error :messages="$errors->get('start_time')" class="mt-1" />
    </div>
    <div>
        <x-input-label :for="$prefix.'-end'" value="End Time" />
        <x-text-input :id="$prefix.'-end'" name="end_time" type="time" class="mt-1 block w-full"
            :value="old('end_time', isset($slot) ? substr($slot->end_time, 0, 5) : '')" required />
        <x-input-error :messages="$errors->get('end_time')" class="mt-1" />
    </div>
</div>

<div>
    <x-input-label value="Days" />
    <div class="mt-2 flex flex-wrap gap-3">
        @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
            <label class="flex items-center gap-1 text-sm text-gray-700 cursor-pointer">
                <input type="checkbox" name="days[]" value="{{ $day }}"
                    {{ in_array($day, old('days', isset($slot) ? $slot->days : [])) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 shadow-sm">
                {{ ucfirst($day) }}
            </label>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('days')" class="mt-1" />
</div>

<div>
    <x-input-label :for="$prefix.'-music'" value="Music" />
    <select :id="$prefix.'-music'" name="music_id"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
        <option value="">— Select a track —</option>
        @foreach($music as $track)
            <option value="{{ $track->id }}"
                {{ old('music_id', isset($slot) ? $slot->music_id : '') == $track->id ? 'selected' : '' }}>
                {{ $track->name }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('music_id')" class="mt-1" />
</div>
