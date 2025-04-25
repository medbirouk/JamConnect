<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;



class GroupController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();
        $groups = $user->groups()
        ->with([
            'post',
            'messages.user',
            'pinnedMessage',
            'users',           
        ])
        ->get();
        

        $groups->each(function ($group) {
            $group->setRelation('users', UserResource::collection($group->users)->collect());
        });

        return inertia('Chat/MyChats', [
            'groups' => $groups,
            'authUserId' => $user->id,
            'selectedGroupId' => $request->input('group'),
        ]);
    }
}
