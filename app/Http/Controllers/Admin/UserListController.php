<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class UserListController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $users = User::query()
            ->select('users.*')
            ->selectSub(function ($query) {
                $query->from('posts')
                    ->selectRaw('count(*)')
                    ->whereColumn('posts.user_id', 'users.id');
            }, 'events_created_count')
            ->selectSub(function ($query) {
                $query->from('post_users')
                    ->selectRaw('count(*)')
                    ->whereColumn('post_users.user_id', 'users.id');
            }, 'events_joined_count')
            ->selectSub(function ($query) {
                $query->from('followers')
                    ->selectRaw('count(*)')
                    ->whereColumn('followers.user_id', 'users.id');
            }, 'followers_count')
            ->selectSub(function ($query) {
                $query->from('followers')
                    ->selectRaw('count(*)')
                    ->whereColumn('followers.follower_id', 'users.id');
            }, 'followings_count')
            ->when(
                $search,
                fn($q) =>
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
            )
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/UserList', [
            'users' => $users,
            'filters' => ['search' => $search],
            'auth' => [
                'admin' => auth('admin')->user(),
            ],
        ]);
    }


    public function destroy(User $user)
    {
        $user->delete(); 
        return back()->with('success', 'User deleted successfully.');
    }
}
