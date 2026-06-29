<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(): View
    {
        $apiKeys = auth()->user()->apiKeys()->latest()->get();

        return view('api-keys.index', compact('apiKeys'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $plainKey = Str::random(64);

        auth()->user()->apiKeys()->create([
            'name' => $request->name,
            'key' => $plainKey,
        ]);

        return redirect()->route('api-keys.index')->with('new_key', $plainKey);
    }

    public function update(Request $request, ApiKey $apiKey): RedirectResponse
    {
        abort_if($apiKey->user_id !== auth()->id(), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:255', 'unique:api_keys,key,' . $apiKey->id],
        ]);

        $apiKey->update(['name' => $request->name, 'key' => $request->key]);

        return redirect()->route('api-keys.index')->with('success', 'API key updated.');
    }

    public function destroy(ApiKey $apiKey): RedirectResponse
    {
        abort_if($apiKey->user_id !== auth()->id(), 403);

        $apiKey->delete();

        return redirect()->route('api-keys.index')->with('success', 'API key deleted.');
    }
}
