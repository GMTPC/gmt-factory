<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorizeUserManagement();

        return view('master.users', [
            'users' => User::query()->orderBy('name')->paginate(25),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeUserManagement();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'department' => ['required', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'กรุณากรอกชื่อ',
            'username.required' => 'กรุณากรอกชื่อผู้ใช้งาน',
            'username.unique' => 'ชื่อผู้ใช้งานนี้ถูกใช้แล้ว',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้ถูกใช้แล้ว',
            'department.required' => 'กรุณากรอกแผนก',
            'position.required' => 'กรุณากรอกตำแหน่ง',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'department' => $validated['department'],
            'position' => $validated['position'],
            'password' => $validated['password'],
        ]);

        return redirect()->route('master-users.index')->with('status', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');
    }

    public function edit(User $user): View
    {
        $this->authorizeUserManagement();

        return view('master.user-edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUserManagement();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,'.$user->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'department' => ['required', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'กรุณากรอกชื่อ',
            'username.required' => 'กรุณากรอกชื่อผู้ใช้งาน',
            'username.unique' => 'ชื่อผู้ใช้งานนี้ถูกใช้แล้ว',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้ถูกใช้แล้ว',
            'department.required' => 'กรุณากรอกแผนก',
            'position.required' => 'กรุณากรอกตำแหน่ง',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ]);

        if ($this->isManager($user) && ! $this->isConfiguredManagerName($validated['username']) && ! $this->anotherManagerExists($user)) {
            return back()->withErrors(['username' => 'ต้องเหลือบัญชีผู้ดูแลอย่างน้อยหนึ่งบัญชี'])->withInput();
        }

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->department = $validated['department'];
        $user->position = $validated['position'];
        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        $user->save();

        return redirect()->route('master-users.index')->with('status', 'บันทึกการแก้ไขผู้ใช้เรียบร้อยแล้ว');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeUserManagement();

        if ((int) $user->id === (int) auth()->id()) {
            return back()->withErrors(['user' => 'ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้']);
        }

        if ($this->isManager($user) && ! $this->anotherManagerExists($user)) {
            return back()->withErrors(['user' => 'ไม่สามารถลบบัญชีผู้ดูแลคนสุดท้ายได้']);
        }

        $user->delete();

        return redirect()->route('master-users.index')->with('status', 'ลบบัญชีผู้ใช้เรียบร้อยแล้ว');
    }

    private function authorizeUserManagement(): void
    {
        $username = strtolower((string) auth()->user()?->username);
        abort_unless(in_array($username, config('cutboard.manager_usernames', ['manager']), true), 403);
    }

    private function isManager(User $user): bool
    {
        return $this->isConfiguredManagerName((string) $user->username);
    }

    private function isConfiguredManagerName(string $username): bool
    {
        return in_array(strtolower($username), config('cutboard.manager_usernames', ['manager']), true);
    }

    private function anotherManagerExists(User $except): bool
    {
        return User::query()
            ->where('id', '<>', $except->id)
            ->get(['username'])
            ->contains(fn (User $candidate): bool => $this->isConfiguredManagerName((string) $candidate->username));
    }
}
