<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepo
    ) {}

    /**
     * List all users.
     */
    public function index(Request $request)
    {
        if ($request->has('q')) {
            $users = $this->userRepo->search($request->query('q'), 15);
        } else {
            $users = $this->userRepo->paginate(15);
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(int $id)
    {
        $user = $this->userRepo->findOrFail($id);
        $this->userRepo->update($id, ['is_active' => !$user->is_active]);

        $status = !$user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }

    /**
     * Show user details.
     */
    public function show(int $id)
    {
        $user = $this->userRepo->findOrFail($id, ['*']);
        $user->load(['profile', 'familyMembers', 'medicines']);

        return view('admin.users.show', compact('user'));
    }
}
