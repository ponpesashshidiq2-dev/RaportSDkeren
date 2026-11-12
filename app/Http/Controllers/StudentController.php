<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::query()
            ->with(['classroom', 'creator'])
            ->visibleTo($request->user())
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($qq) use ($term) {
                    $qq->where('nama', 'like', "%{$term}%")
                        ->orWhere('nisn', 'like', "%{$term}%")
                        ->orWhere('nis', 'like', "%{$term}%");
                });
            })
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('students.index', compact('students'));
    }

    public function create(): View
    {
        return $this->formView(new Student(['is_active' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validated($request);
        $attributes['created_by'] = $this->resolveOwner($request, null);

        Student::create($attributes);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        $this->authorizeStudent($student);

        return $this->formView($student);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($student);

        $attributes = $this->validated($request, $student);
        $attributes['created_by'] = $this->resolveOwner($request, $student);

        $student->update($attributes);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorizeStudent($student);

        $student->delete();

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function importForm(): View
    {
        return view('students.import', [
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
        ]);
    }

    /**
     * Import Excel/CSV data siswa (Laravel Excel / SheetJS-compatible .xlsx).
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'kelas_id' => ['nullable', 'exists:classrooms,id'],
        ], [
            'file.mimes' => 'Format file harus .xlsx, .xls, atau .csv.',
        ]);

        StudentsImport::$importedCount = 0;

        DB::transaction(function () use ($request) {
            Excel::import(
                new StudentsImport(
                    $request->filled('kelas_id') ? $request->integer('kelas_id') : null,
                    $request->user()->id,
                ),
                $request->file('file')
            );
        });

        $count = StudentsImport::$importedCount;

        return redirect()->route('students.index')
            ->with('success', "Import selesai. {$count} baris data siswa berhasil diproses.");
    }

    /**
     * Data untuk form siswa + opsi pemilik data ("Input Oleh").
     */
    private function formView(Student $student): View
    {
        $user = request()->user();
        $canChooseOwner = $user->isOneOf(User::ROLE_SUPER_ADMIN, User::ROLE_KEPALA_SEKOLAH);

        $owners = $canChooseOwner
            ? User::query()
                ->whereIn('role', [
                    User::ROLE_SUPER_ADMIN,
                    User::ROLE_KEPALA_SEKOLAH,
                    User::ROLE_GURU,
                    User::ROLE_WALI_KELAS,
                ])
                ->orderBy('name')
                ->get()
            : collect();

        return view('students.form', [
            'student' => $student,
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
            'owners' => $owners,
            'canChooseOwner' => $canChooseOwner,
        ]);
    }

    /**
     * Pemilik data siswa (created_by):
     * - Super Admin / Kepala Sekolah: boleh memilih (created_by form) — dipakai untuk
     *   menautkan siswa buatan admin ke guru agar guru tersebut dapat melihat/menginputnya.
     * - Guru / Wali Kelas: tidak bisa mengubah; saat membuat baru = dirinya sendiri.
     */
    private function resolveOwner(Request $request, ?Student $student): ?int
    {
        $user = $request->user();
        $canChoose = $user->isOneOf(User::ROLE_SUPER_ADMIN, User::ROLE_KEPALA_SEKOLAH);

        if ($canChoose && $request->filled('created_by')) {
            return $request->integer('created_by');
        }

        if ($student !== null) {
            return $student->created_by;
        }

        return $user->id;
    }

    /**
     * Tolak akses ke siswa yang bukan milik user (created_by) — kecuali admin/kepsek.
     */
    private function authorizeStudent(Student $student): void
    {
        $allowed = request()->user()?->canSeeStudent($student) ?? false;

        abort_unless(
            $allowed,
            403,
            'Anda tidak memiliki akses ke data siswa ini. Siswa hanya terlihat oleh pembuat/input datanya.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn'.($student ? ','.$student->id : '')],
            'nis' => ['nullable', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'nama_wali' => ['nullable', 'string', 'max:255'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
