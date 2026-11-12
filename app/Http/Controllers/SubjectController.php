<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjectsByMuatan = Subject::query()
            ->where('is_active', true)
            ->orderByRaw("FIELD(muatan, 'agama', 'nasional', 'lokal')")
            ->orderBy('name')
            ->get()
            ->groupBy('muatan');

        return view('subjects.index', compact('subjectsByMuatan'));
    }

    public function create(): View
    {
        return view('subjects.form', ['subject' => new Subject()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Subject::create($this->validated($request));

        return redirect()->route('subjects.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        return view('subjects.form', compact('subject'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $subject->update($this->validated($request, $subject));

        return redirect()->route('subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'code' => ['nullable', 'string', 'max:30', 'unique:subjects,code'.($subject ? ','.$subject->id : '')],
            'name' => ['required', 'string', 'max:255'],
            'muatan' => ['required', 'in:agama,nasional,lokal'],
            'kkm' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'phase' => ['nullable', 'string', 'max:5'],
        ], [
            'kkm.numeric' => 'KKM harus berupa angka.',
            'kkm.max' => 'KKM maksimal 100.',
        ]);
    }
}
