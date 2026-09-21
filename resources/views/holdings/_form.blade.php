@php
    $isEdit = $holding->exists;
@endphp

<div>
    <label for="cryptocurrency_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
        Cryptocurrency
    </label>
    <select
        id="cryptocurrency_id"
        name="cryptocurrency_id"
        class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500 dark:border-slate-700 dark:bg-slate-900"
    >
        <option value="" disabled @selected(old('cryptocurrency_id', $holding->cryptocurrency_id) === null)>
            Select a cryptocurrency
        </option>
        @foreach ($cryptocurrencies as $cryptocurrency)
            <option
                value="{{ $cryptocurrency->id }}"
                @selected((int) old('cryptocurrency_id', $holding->cryptocurrency_id) === $cryptocurrency->id)
            >
                {{ $cryptocurrency->name }} ({{ $cryptocurrency->symbol }})
            </option>
        @endforeach
    </select>
    @error('cryptocurrency_id')
        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="mt-5">
    <label for="platform_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
        Platform
    </label>
    <select
        id="platform_id"
        name="platform_id"
        class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500 dark:border-slate-700 dark:bg-slate-900"
    >
        <option value="" disabled @selected(old('platform_id', $holding->platform_id) === null)>
            Select a platform
        </option>
        @foreach ($platforms as $platform)
            <option
                value="{{ $platform->id }}"
                @selected((int) old('platform_id', $holding->platform_id) === $platform->id)
            >
                {{ $platform->name }}
            </option>
        @endforeach
    </select>
    @error('platform_id')
        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="mt-5">
    <label for="quantity" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
        Quantity
    </label>
    <input
        type="number"
        id="quantity"
        name="quantity"
        step="0.00000001"
        min="0.00000001"
        value="{{ old('quantity', $holding->quantity) }}"
        class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500 dark:border-slate-700 dark:bg-slate-900"
    >
    @error('quantity')
        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="mt-8 flex items-center gap-3">
    <button
        type="submit"
        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
    >
        {{ $isEdit ? 'Save changes' : 'Add holding' }}
    </button>
    <a href="{{ route('portfolio.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
        Cancel
    </a>
</div>
