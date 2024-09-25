<?php

use App\Http\Requests\RequestCallRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => ['json.response','cors']], function () {
    Route::post('/user/login','Api\User\AuthController@login');
    Route::post('/user/register','Api\User\AuthController@register');

    Route::group(['prefix' => 'user','namespace' => 'Api\User' , 'middleware' => ['auth:api','user-middleware']], function () {
        Route::get('info','AuthController@userInfo');
        Route::post('change-password','AuthController@changePassword');
        Route::post('update-info','AuthController@updateInfo');
        Route::post('change-online-status','AuthController@changeOnlineStatus');
        

        Route::get('unfriends-list','FriendController@unfriendsList');
        Route::get('friends-list','FriendController@friendsList');
        Route::get('received-friend-requests-list','FriendController@friendRequestsReceivedList');
        Route::get('sent-friend-requests-list','FriendController@SentFriendRequestsList');
        Route::post('send-friend-request','FriendController@friendRequest');
        Route::post('accept-or-reject-friend-request/{id}','FriendController@AcceptOrRejectFriendRequest');
        Route::get('search','FriendController@search');

        Route::get('get-messages/{userId}','MessageController@getMessages');
        Route::post('send-message','MessageController@sendMessage');
        Route::post('send-group-message','MessageController@sendMessageGroup');
        Route::post('send-emergency-message','MessageController@sendEmergencyMessage');
        Route::get('chats-list','MessageController@userChatsList');
        
        // friendRequest
        // AcceptOrRejectFriendRequest
        Route::get('all_notifications','AuthController@all_notifications');
        Route::post('request-call','MessageController@requestCall');

        Route::get('user-profile/{id}','AuthController@user_profile');
        Route::post('create-admin','AuthController@make_user_admin');
        Route::post('logout', 'AuthController@logout');
        Route::get('get-group-messages','MessageController@all_group_message');
        Route::get('see-notification/{id}','AuthController@see_notification');
        Route::post('activation_user', 'AuthController@activation_user');

        //////////////////
        Route::get('group_Users','AuthController@group_Users');
        Route::post('update_location', 'AuthController@update_location');
        Route::get('group','AuthController@auth_group');
        Route::get('specific_group/{id}','AuthController@specific_group');
        
    });
});
