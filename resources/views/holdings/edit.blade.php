<x-layouts.app title="Edit holding">
    <a href="{{ route('portfolio.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
        &larr; Back to portfolio
    </a>

    <h1 class="mt-4 text-2xl font-semibold tracking-tight">
        Edit {{ $holding->cryptocurrency->symbol }} on {{ $holding->platform->name }}
    </h1>

    <form method="POST" action="{{ route('holdings.update', $holding) }}" class="mt-6 max-w-md rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
        @csrf
        @method('PUT')
        @include('holdings._form')
    </form>
</x-layouts.app>
