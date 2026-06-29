<?php

namespace App\Http\Controllers;

use App\Models\Music;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MusicController extends Controller
{
    public function index(): View
    {
        $music = auth()->user()->music()->latest()->get();

        return view('music.index', compact('music'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:mp3,mpeg', 'max:51200'],
        ]);

        $path = $request->file('file')->store('music', 'public');

        auth()->user()->music()->create([
            'name' => $request->name,
            'path' => $path,
        ]);

        return redirect()->route('music.index')->with('success', 'Track uploaded.');
    }

    public function destroy(Music $music): RedirectResponse
    {
        abort_if($music->user_id !== auth()->id(), 403);

        Storage::disk('public')->delete($music->path);
        $music->delete();

        return redirect()->route('music.index')->with('success', 'Track deleted.');
    }
}
