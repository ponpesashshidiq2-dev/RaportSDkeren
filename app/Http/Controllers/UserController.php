<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Kelola Pengguna Multi-User (khusus Super Admin):
 * buat/ubah/hapus akun dengan role + tautan ke data siswa (untuk role siswa_orangtua).
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['classroom', 'student'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roleLabels = [
            User::ROLE_SUPER_ADMIN => 'Super Admin',
            User::ROLE_GURU => 'Guru',
            User::ROLE_WALI_KELAS => 'Wali Kelas',
            User::ROLE_KEPALA_SEKOLAH => 'Kepala Sekolah',
            User::ROLE_SISWA_ORANGTUA => 'Siswa / Orang Tua',
        ];

        return view('users.index', compact('users', 'roleLabels'));
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => new User(['role' => User::ROLE_GURU]),
            'students' => $this->studentsOptions(),
            'roleLabels' => $this->roleLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'student_id' => $data['role'] === User::ROLE_SISWA_ORANGTUA ? ($data['student_id'] ?? null) : null,
        ]);

        return redirect()->route('users.index')->with('success', "Pengguna \"{$data['name']}\" berhasil dibuat.");
    }

    public function edit(User $user): View
    {
        return view('users.form', [
            'user' => $user,
            'students' => $this->studentsOptions($user->student_id),
            'roleLabels' => $this->roleLabels(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $isSelf = $user->id === auth()->id();
        $data = $this->validated($request, $user, $isSelf);

        // Cegah akun mengunci dirinya sendiri: role sendiri tidak boleh diubah
        $role = $isSelf ? $user->role : $data['role'];

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->role = $role;
        $user->student_id = $role === User::ROLE_SISWA_ORANGTUA ? ($data['student_id'] ?? null) : null;

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $user = null, bool $isSelf = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'.($user ? ','.$user->id : '')],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:super_admin,guru,wali_kelas,kepala_sekolah,siswa_orangtua'],
            'student_id' => ['nullable', 'exists:students,id'],
        ];

        $data = $request->validate($rules, [
            'password.required' => 'Password wajib diisi untuk pengguna baru.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($data['role'] === User::ROLE_SISWA_ORANGTUA && empty($data['student_id'])) {
            abort(back()->withInput()->withErrors(['student_id' => 'Role Siswa/Orang Tua wajib ditautkan ke data siswa.']));
        }

        return $data;
    }

    /**
     * @return array<int, object{id:int,label:string,linked:bool}>
     */
    private function studentsOptions(?int $exceptLinkedUserId = null): array
    {
        return Student::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get()
            ->map(fn (Student $s) => (object) [
                'id' => $s->id,
                'label' => sprintf('%s - %s (Kelas %s)', $s->nisn ?? '-', $s->nama, $s->classroom?->name ?? '-'),
                'linked' => User::query()->where('student_id', $s->id)
                    ->when($exceptLinkedUserId, fn ($q) => $q->where('id', '!=', $exceptLinkedUserId))
                    ->exists(),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function roleLabels(): array
    {
        return [
            User::ROLE_SUPER_ADMIN => 'Super Admin',
            User::ROLE_GURU => 'Guru',
            User::ROLE_WALI_KELAS => 'Wali Kelas',
            User::ROLE_KEPALA_SEKOLAH => 'Kepala Sekolah',
            User::ROLE_SISWA_ORANGTUA => 'Siswa / Orang Tua',
        ];
    }
}
