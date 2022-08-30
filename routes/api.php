<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\CommandeItemController;
use App\Http\Controllers\SystemController;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Validator;





/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


 
Route::post('/sanctum/token', function (Request $request) {

    $data = $request->all();

    $validator = Validator::make($data,[
        'username' => 'required',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $error = '';

    if($validator->fails()){
        return 'invalide user informations.';
    }
 
    $user = User::where('username', $request->username)->first();

    if($user != null)
    {
        $error = 'password incorrect.';
    }
    else
    {
        $error = 'username incorrect.';
    }
 
    if (! $user || ! Hash::check($request->password, $user->password)) {
        return $error;
        /*throw ValidationException::withMessages([
            'username' => ['The provided credentials are incorrect.'],
        ]);*/
    }
    if($user->blocked)
    {
        return 'user is blocked.';
    }
 
    return '{ "sanctum_token" : "' . $user->createToken($request->device_name)->plainTextToken . '" , "role" : "' . $user->role . '" , "blocked" : ' . $user->blocked . ' }';
});


Route::group([ 'prefix' => 'client'], function() {

    Route::controller(App\Http\Controllers\RestaurantController::class)->group(function () {
        Route::get('/getrestaurant', 'getRestaurant');
        Route::get('/showimage/{imagename}', 'showImage');
    });
        
    Route::controller(App\Http\Controllers\CategoriesController::class)->group(function () {
        Route::get('/getallcategories',  'getAllCategories');

    });

    Route::controller(App\Http\Controllers\ItemsController::class)->group(function () {
        Route::get('/getallitems',  'getAllItems');
    });

});

Route::group([ 'prefix' => 'system'], function() {

    Route::controller(App\Http\Controllers\SystemController::class)->group(function () {

        Route::get('/ifsystemhasadmin', 'ifSystemHasAdmin');
        Route::post('/createadmin', 'createAdmin');
        Route::post('/securitycodetest', 'securityCodeTest');
        Route::get('/iftokenisvalid',  'ifTokenIsValid');

    });

});


Route::group(['middleware' => ['auth:sanctum']], function () {

    

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/sanctum/logout', function (Request $request) {
        $user = $request->user();
        $result = $user->tokens()->delete();
        return $result;
    
    }); 

    Route::group([ 'prefix' => 'users'], function() {

        
            Route::controller(App\Http\Controllers\UserController::class)->group(function () {
                Route::post('/talkwithserver', 'talkWithServer');
                Route::get('/lastvisit/{id}', 'getLastVisit');
                Route::get('/getallusers', 'getAllUsers');
                Route::post('/changerole', 'changeRole');
                Route::post('/saveimage', 'saveImage');
                Route::get('/getimage/{imagename}', 'getImage');
                Route::get('/showimage/{imagename}', 'showImage');
                Route::post('/deleteuser', 'deleteUser');
                Route::post('/blockuser', 'blockUser');
                Route::post('/unblockuser', 'unBlockUser');
                Route::post('/getuser', 'getUser');
                Route::post('/updateuser', 'updateUser');
                Route::post('/createuser', 'createUser');
            });
       
    });

    Route::group([ 'prefix' => 'statistics'], function() {

        
        Route::controller(App\Http\Controllers\StatisticsController::class)->group(function () {
            Route::get('/getnumberofusers', 'getNumberOfUsers');
            Route::get('/getnumberofcommandes/{date}', 'getNumberOfCommandes');
            Route::post('/getusersoftheday', 'getUsersOfTheDay');
            Route::get('/getcommnadesdates', 'getCommnadesDates');
            Route::get('/getnowdate', 'getNowDate');

            
        });
   
});

Route::group([ 'prefix' => 'restaurant'], function() {
        
    Route::controller(App\Http\Controllers\RestaurantController::class)->group(function () {
        Route::get('/getrestaurant', 'getRestaurant');
        Route::post('/createrestaurant', 'createRestaurant');
        Route::post('/updaterestaurant', 'updateRestaurant');
        Route::post('/saveimage', 'saveImage');
        Route::get('/showimage/{imagename}', 'showImage');

    });

});

    Route::group([ 'prefix' => 'categories'], function() {
        
            Route::controller(App\Http\Controllers\CategoriesController::class)->group(function () {
                Route::get('/getallcategories',  'getAllCategories');
                Route::post('/deletecategory', 'deleteCategory');
                Route::post('/getcategory', 'getCategory');
                Route::post('/updatecategory',  'updateCategory');
                Route::post('/createcategory',  'createCategory');
            });
       
    });

    Route::group([ 'prefix' => 'items'], function() {
            Route::controller(App\Http\Controllers\ItemsController::class)->group(function () {
                Route::get('/getallitems',  'getAllItems');
                Route::post('/saveimage', 'saveImage');
                Route::post('/getitemsbyids', 'getItemsByIds');
                Route::get('/showimage/{imagename}', 'showImage');
                Route::post('/deleteitem', 'deleteItem');
                Route::post('/getitembyqrcode', 'getItemByQrCode');
                Route::post('/getitembyid', 'getItemById');
                Route::post('/updateitem',  'updateItem');
                Route::post('/createitem',  'createItem');
                Route::get('/generateqrcode', 'generateQrCode');
                Route::post('/validatqrcode',  'validateQrCode');
            });
    });


    Route::group([ 'prefix' => 'commandes'], function() {
        Route::controller(App\Http\Controllers\CommandeController::class)->group(function () {
            Route::get('/getcommande/{id}',  'getCommande');
            Route::post('/createcommande', 'createCommande'); 
            Route::post('/deletewaitingcommande', 'deleteWaitingCommande');
            Route::post('/updatecommandestate', 'updateCommandeState');
            Route::post('/setcommandeonpreparing', 'setCommandeOnPreparing');
            Route::post('/setcommandeprepared', 'setCommandePrepared');
            Route::post('/setcommandedelivered', 'setCommandeDelivered');
            Route::post('/setcommandepayed', 'setCommandePayed');
            Route::post('/getnotpreparedyetcommandes', 'getNotPreparedYetCommandes');
            Route::post('/getnotdeliveredyetcommandes', 'getNotDeliveredYetCommandes');
            Route::post('/getwaitingpreparingcommandes', 'getWaitingPreparingCommandes');
            Route::post('/getonpreparingcommandes', 'getOnPreparingCommandes');
            Route::get('/getnotpayedyetcommandes/{id_server}', 'getNotPayedYetCommandes');
        });
    });

    Route::group([ 'prefix' => 'commandesitems'], function() {
        Route::controller(App\Http\Controllers\CommandeItemController::class)->group(function () {
            Route::get('/getcommandeitems/{idCommande}',  'getCommandeItems');
        });
    });
    
});






















