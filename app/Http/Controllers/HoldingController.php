<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHoldingRequest;
use App\Http\Requests\UpdateHoldingRequest;
use App\Models\Cryptocurrency;
use App\Models\Holding;
use App\Models\Platform;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class HoldingController extends Controller
{
    /**
     * Show the form for creating a new holding.
     */
    public function create(): View
    {
        return view('holdings.create', [
            'holding' => new Holding,
            'cryptocurrencies' => $this->cryptocurrencyOptions(),
            'platforms' => $this->platformOptions(),
        ]);
    }

    /**
     * Store a newly created holding.
     */
    public function store(StoreHoldingRequest $request): RedirectResponse
    {
        Holding::create($request->validated());

        return redirect()
            ->route('portfolio.index')
            ->with('status', 'Holding added to the portfolio.');
    }

    /**
     * Show the form for editing an existing holding.
     */
    public function edit(Holding $holding): View
    {
        return view('holdings.edit', [
            'holding' => $holding,
            'cryptocurrencies' => $this->cryptocurrencyOptions(),
            'platforms' => $this->platformOptions(),
        ]);
    }

    /**
     * Update an existing holding.
     */
    public function update(UpdateHoldingRequest $request, Holding $holding): RedirectResponse
    {
        $holding->update($request->validated());

        return redirect()
            ->route('portfolio.index')
            ->with('status', 'Holding updated.');
    }

    /**
     * Remove a holding from the portfolio.
     */
    public function destroy(Holding $holding): RedirectResponse
    {
        $holding->delete();

        return redirect()
            ->route('portfolio.index')
            ->with('status', 'Holding removed from the portfolio.');
    }

    /**
     * @return Collection<int, Cryptocurrency>
     */
    private function cryptocurrencyOptions(): Collection
    {
        return Cryptocurrency::query()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Platform>
     */
    private function platformOptions(): Collection
    {
        return Platform::query()->orderBy('name')->get();
    }
}
