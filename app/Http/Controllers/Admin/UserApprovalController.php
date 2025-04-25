<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserApproved;
use App\Notifications\UserRejected;
use Inertia\Inertia;
use Inertia\Response;

class UserApprovalController extends Controller
{
    public function index(): Response
    {
        $pendingUsers = User::where('status', 'pending')->latest()->get()->map(function ($user) {
            $user->roles = is_array($user->roles)
                ? $user->roles
                : (!empty($user->roles) ? json_decode($user->roles, true) : []);
            $user->demo_url = $user->demo_path ? asset('storage/' . $user->demo_path) : null;
            return $user;
        });




        return Inertia::render('Admin/UserApproval', [
            'pendingUsers' => $pendingUsers,
            'auth' => [
                'admin' => auth('admin')->user(),
            ],
        ]);
    }

    public function approve(User $user)
    {
        $user->update(['status' => 'approved']);

        

        Notification::route('mail', $user->email)
            ->notify(new UserApproved($user));

        return redirect()->route('admin.users.pending')->with('success', 'User approved and verification email sent.');
    }

    public function reject(User $user)
    {
        $user->update(['status' => 'rejected']);

        Notification::route('mail', $user->email)
            ->notify(new UserRejected($user));

        return redirect()->route('admin.users.pending')->with('success', 'User rejected.');
    }
}
