<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MyEventController;
use App\Http\Controllers\MyParticipationController;
use App\Http\Controllers\Auth\PendingApprovalController;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\Admin\UserListController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('pending-approval', PendingApprovalController::class)
    ->name('pending-approval');

Route::get('/u/{user:username}', [ProfileController::class, 'index'])
    ->name('profile');


Route::middleware('auth')->group(function () {

    Route::post('/profile/roles', [ProfileController::class, 'updateRoles'])
     ->name('profile.roles.update');

    Route::post('/profile/media', [ProfileController::class, 'storeMedia'])
         ->name('profile.media.store');

    Route::delete('/profile/media/{attachment}', [ProfileController::class, 'destroyMedia'])
         ->name('profile.media.destroy');
         

    Route::get('/search', [SearchController::class, 'search'])->name('search');


    Route::get('/location/autocomplete', [LocationController::class, 'autocomplete']);
    Route::get('/reverse-geocode', [LocationController::class, 'reverseGeocode']);



    //Group Chats
    Route::get('/my-chats', [GroupController::class, 'index'])->name('chats.mine');
    Route::post('/groups/{group}/messages/{message}/pin', [ChatController::class, 'pin']);

    Route::get('/groups/{group}/messages', [ChatController::class, 'fetchMessages'])->name('groups.messages.fetch');
    Route::post('/groups/{group}/messages', [ChatController::class, 'store'])->name('groups.messages.store');

    Route::put('/groups/{group}/messages/{message}', [ChatController::class, 'update'])
        ->name('groups.messages.update');
    Route::delete('/groups/{group}/messages/{message}', [ChatController::class, 'destroy'])
        ->name('groups.messages.destroy');

    Route::post(
        '/groups/{group}/messages/{message}/pin',
        [ChatController::class, 'pin']
    )
        ->name('groups.messages.pin');

    // My Events
    Route::get('/my-events', [MyEventController::class, 'index'])->name('events.mine');
    Route::post('/applicants/{postUserId}/approve', [MyEventController::class, 'approve'])->name('event.applicant.approve');
    Route::post('/applicants/{postUserId}/reject', [MyEventController::class, 'reject'])->name('event.applicant.reject');
    
    Route::post(
        '/participants/{postUserId}/remove',
        [MyEventController::class, 'remove']
    )->name('event.participant.remove');
    
    //My Participations
    Route::get('/my-participations', [MyParticipationController::class, 'index'])->name('participations.mine');
    Route::post('/participations/{post}/cancel', [MyParticipationController::class, 'cancel'])->name('event.cancel');




    //    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/update-images', [ProfileController::class, 'updateImage'])
        ->name('profile.updateImages');

    Route::post('/user/follow/{user}', [UserController::class, 'follow'])->name('user.follow');

    // Posts
    Route::prefix('/post')->group(function () {
        Route::get('/{post}', [PostController::class, 'view'])
            ->name('post.view');

        Route::post('', [PostController::class, 'store'])
            ->name('post.create');

        Route::put('/{post}', [PostController::class, 'update'])
            ->name('post.update');

        Route::delete('/{post}', [PostController::class, 'destroy'])
            ->name('post.destroy');

        Route::get('/download/{attachment}', [PostController::class, 'downloadAttachment'])
            ->name('post.download');

        Route::post('/{post}/reaction', [PostController::class, 'postReaction'])
            ->name('post.reaction');

        Route::post('/{post}/join', [PostController::class, 'join'])->name('post.join');

        Route::post('/{post}/comment', [PostController::class, 'createComment'])
            ->name('post.comment.create');

        Route::post('/ai-post', [PostController::class, 'aiPostContent'])
            ->name('post.aiContent');

        Route::post('/fetch-url-preview', [PostController::class, 'fetchUrlPreview'])
            ->name('post.fetchUrlPreview');

        Route::post('/{post}/pin', [PostController::class, 'pinUnpin'])
            ->name('post.pinUnpin');
    });





    // Comments
    Route::delete('/comment/{comment}', [PostController::class, 'deleteComment'])
        ->name('comment.delete');

    Route::put('/comment/{comment}', [PostController::class, 'updateComment'])
        ->name('comment.update');

    Route::post('/comment/{comment}/reaction', [PostController::class, 'commentReaction'])
        ->name('comment.reaction');
});

require __DIR__ . '/auth.php';

Route::prefix('admin')->name('admin.')->group(function () {
    // Admin login/logout
    Route::get('login', [AdminAuth::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuth::class, 'login']);
    Route::post('logout', [AdminAuth::class, 'logout'])->name('logout');

    // Protected admin routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('users/pending', [UserApprovalController::class, 'index'])->name('users.pending');
        Route::post('users/{user}/approve', [UserApprovalController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/reject', [UserApprovalController::class, 'reject'])->name('users.reject');

        Route::get('/users', [UserListController::class, 'index'])->name('users.list');
        Route::delete('/users/{user}', [UserListController::class, 'destroy'])->name('users.destroy');
    });
});
