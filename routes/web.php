<?php

use App\Helper\File\JsonResponse;
use App\Http\Controllers\Admin\PToApproveForModeratorController;
use App\Http\Controllers\Admin\SToApproveForModeratorController;
use App\Http\Controllers\Admin\UsersController;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Storage;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

Route::get('/debug/reset/db', function (){



    // dd($status);
    $exitCode = Artisan::call("migrate:fresh",[
        '--seed'=>true
    ]);

    //  $exitCode = Artisan::call('migrate:fresh', [
    //      '--seed'
    // ]);

    return redirect()->route('home');


});

Route::get('test/api',function (Kreait\Firebase\Auth $auth){
    $refreshToken="AG8BCnfsxPNmvSUZF6aeE-dzh2Za63K4PM-SZn02nrHD_0hQp4R-HUpWjEfDaxygHaAelrJfeWMH18aX9urOvTQP8wUFvnO5DEiIMAJpKziffeL0sCkiELrA0-geBGQ_Eq2upmi2JP5iFmpfaWcMGbMAUxnmjsoUaW6jG5zKzIDsZJ1tmC8XqugOclS9Q1M-BPAm2vkR6y9Ex_MSfwgYE9Lxrks-YWa_sg";
//$refreshToken.="021";


    $fb=new \App\Helper\File\FirebaseAuthenticator ($refreshToken);

    dd($fb->auth());
});

Route::post('test/largeFileUpload',function (Request $r){

   $input=$r->only(['file_id','collection', 'model', 'current_part', 'current_part_data', 'file_ext','file_name','file_size','current_part_data','total_part']);



   if($input['current_part']<1 &&  !array_key_exists('file_id', $input) )$input['file_id']=\Illuminate\Support\Str::uuid()->toString();

   if(array_key_exists('file_id', $input) &&  $input['file_id']==='new')$input['file_id']=\Illuminate\Support\Str::uuid()->toString();

   $data=[
       'file_id'=>$input['file_id'],
       'collection'=>$input['collection'],
       'model'=>$input['model'],
       'current_part'=>$input['current_part'],
       'file_ext'=>$input['file_ext'],
       'file_name'=>$input['file_name'],
       'file_size'=>$input['file_size'],
       'total_part'=>$input['total_part']
   ];
   $dbData=$data;
   $dbData['raw_data']=$input['current_part_data'];
    $dbData['part']=$dbData['current_part'];

    \App\Models\LargeFileMediaLibrary::create($dbData);
    $pathToStoreRaw=['large-media-library',$input['file_id']];
    Storage::disk('root')->putFileAs(implode('/',$pathToStoreRaw), $r->file('current_part_data'),implode('.', [$input['current_part'],'p']));
    $allPartRaw=\App\Models\LargeFileMediaLibrary::where('file_id',$input['file_id'])->get();
    $allPart=\App\Models\LargeFileMediaLibrary::where('file_id',$input['file_id'])->get()->toArray();

   $totalPart=reset($allPart)['total_part'];
   if($totalPart==count($allPart)) {
       $data['upload_finish'] = true;
       if(true){
           $id = $input['file_id'];

           $first = $allPartRaw->first();
           $allId = $allPartRaw->pluck('id')->toArray();
           $total = $first->total_part;

           if ($allPartRaw->count() == $total) {


               $file = [
                   'name' => $first->file_id,
                   'total_part' => $total,
                   'model' => $first->model,
                   'collection' => $first->model,
                   'final_path' => null,
                   'chunk_to_join' => [],
                   'file_store_name' => $first->file_name,
                   'file_store_ext' => $first->file_ext,
               ];

               //  dd($allPart);

               foreach ($allPart as $k => $filePart) {
                   $fileSrc=implode('.',[$filePart['part'],'p']);
                   $getFileContent=Storage::disk('root')->get(implode('/',array_merge($pathToStoreRaw,[$fileSrc])));
                   Storage::disk('root')->delete(implode('/',array_merge($pathToStoreRaw,[$fileSrc])));

                   $pathToStorecurrent = array_merge($pathToStoreRaw, ['part', implode('.', [$file['name'], 'p'])]);
                   $pathToStoreFinal = array_merge($pathToStoreRaw, ['final', implode('.', [$file['file_store_name'], $file['file_store_ext']])]);
                   $f = Storage::disk('root')->append(implode('/', $pathToStorecurrent),$getFileContent);
                   if ($filePart['id'] == last($allId)) {
                       $f = Storage::disk('root')->move(implode('/', $pathToStorecurrent), implode('/', $pathToStoreFinal));
                       \App\Models\LargeFileMediaLibrary::where('file_id',$file['name'])->delete();
                       $data=[
                           'file_id'=>$input['file_id'],
                           'collection'=>$input['collection'],
                           'model'=>$input['model'],
                           'current_part'=>$input['current_part'],
                           'file_ext'=>$input['file_ext'],
                           'file_name'=>$input['file_name'],
                           'file_size'=>$input['file_size'],
                           'total_part'=>$input['total_part'],
                           'final_path'=>implode('/', $pathToStoreFinal),
                           'part'=>0,
                       ];
                       \App\Models\LargeFileMediaLibrary::create($data);
                       $data['upload_finish'] = true;
                   }


               }


           }
       }

   }


   return JsonResponse::data(['data'=>$data]);

})->name('uploadUrl');

Route::get('sitemap/gen', function () {
    $path=public_path('sitemap.xml');

    $sitemap=[
        'Home'=>route('home'),
        'About us'=>route('aboutusForFrontEnd'),
        'artist_form'=>route('artist_form'),
        'group_form'=>route('group_form'),
        'vendort_form'=>route('vendor_form'),
        'Contact us'=>route('contactusForFrontEnd')
    ];
    Storage::disk('public_raw')->delete('sitemap.xml');
    $sitemapC=Sitemap::create();

    foreach ($sitemap as $n=>$r){
        $sitemapC ->add(
            Url::create($r)
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.1)) ;
    }
    $sitemapC ->writeToFile($path);

    return response()->file($path);
});

Route::get('/', [\App\Http\Controllers\Frontend\HomeController::class,'home'])->name('home');


Route::resource('post', 'HomeController'); 



Route::get('/add-product-service', [\App\Http\Controllers\Frontend\HomeController::class,'add_list'])->name('add_list');


Route::post('login',[\App\Http\Controllers\Frontend\HomeController::class,'loginPost'])->name('loginForFrontEnd');
Route::post('logout',[\App\Http\Controllers\Frontend\HomeController::class,'logoutPost'])->name('logoutForFrontEnd');
Route::post('register',[\App\Http\Controllers\Frontend\HomeController::class,'registerPost'])->name('registerForFrontEnd');


Route::get('artist_form',[\App\Http\Controllers\Frontend\HomeController::class,'artist_form'])->name('artist_formForFrontEnd');
Route::get('group_form',[\App\Http\Controllers\Frontend\HomeController::class,'group_form'])->name('group_formForFrontEnd');

Route::get('vendor_form',[\App\Http\Controllers\Frontend\HomeController::class,'vendor_form'])->name('vendor_formForFrontEnd');


Route::get('about-us',[\App\Http\Controllers\Frontend\HomeController::class,'aboutUs'])->name('aboutusForFrontEnd');
Route::get('contact-us',[\App\Http\Controllers\Frontend\HomeController::class,'contactUs'])->name('contactusForFrontEnd');
Route::get('artist_details',[\App\Http\Controllers\Frontend\HomeController::class,'artist_details'])->name('artist_detailsForFrontEnd');

Route::get('sitemap',[\App\Http\Controllers\Frontend\HomeController::class,'sitemap'])->name('sitemapForFrontEnd');


Route::prefix('vendor')->group(function (){
    Route::get('/', [\App\Http\Controllers\Frontend\HomeController::class,'vendor_dashboard'])->name('vendor_dashboard');
});

Route::prefix('user')->group(function (){
    Route::get('/', [\App\Http\Controllers\Frontend\HomeController::class,'user_dashboard'])->name('user_dashboard');
    Route::get('/profile', [\App\Http\Controllers\Frontend\HomeController::class,'user_profile'])->name('user_profile');
});

Route::get('/', [\App\Http\Controllers\Frontend\HomeController::class,'home'])->name('home');
Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

//////Backend



Route::group(['prefix' => 'admin', ], function () {
    Auth::routes(['register' => false]);

});

Route::group(['prefix' => 'moderator','as'=>'moderator.' ], function () {
    Route::get('/',[\App\Http\Controllers\Auth\LoginController::class,'showLoginFormForModerator'])->name('moderator_login');
    Auth::routes(['register' => false]);
    Route::get('/login',[\App\Http\Controllers\Auth\LoginController::class,'showLoginFormForModerator'])->name('moderator_login');



});

Route::get('test/users/custom/indexData', [UsersController::class,'indexData'])->name('users.indexData');


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');
    Route::any('users/custome/indexData', [UsersController::class,'indexData'])->name('users.indexData');

    // Message Boxes
    Route::delete('message-boxes/destroy', 'MessageBoxController@massDestroy')->name('message-boxes.massDestroy');
    Route::post('message-boxes/media', 'MessageBoxController@storeMedia')->name('message-boxes.storeMedia');
    Route::post('message-boxes/ckmedia', 'MessageBoxController@storeCKEditorImages')->name('message-boxes.storeCKEditorImages');
    Route::resource('message-boxes', 'MessageBoxController');

    // Plans
    Route::delete('plans/destroy', 'PlansController@massDestroy')->name('plans.massDestroy');
    Route::resource('plans', 'PlansController');

    // Ads
    Route::delete('ads/destroy', 'AdsController@massDestroy')->name('ads.massDestroy');
    Route::resource('ads', 'AdsController');

    // Sliders
    Route::delete('sliders/destroy', 'SliderController@massDestroy')->name('sliders.massDestroy');
    Route::post('sliders/media', 'SliderController@storeMedia')->name('sliders.storeMedia');
    Route::post('sliders/ckmedia', 'SliderController@storeCKEditorImages')->name('sliders.storeCKEditorImages');
    Route::resource('sliders', 'SliderController');

    // Services For Users
    Route::resource('services-for-users', 'ServicesForUserController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Products For Users
    Route::resource('products-for-users', 'ProductsForUsersController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Massage Box For Moderators
    Route::resource('massage-box-for-moderators', 'MassageBoxForModeratorController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Massage Box For Users
    Route::resource('massage-box-for-users', 'MassageBoxForUserController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Profile For Moderators
    Route::resource('profile-for-moderators', 'ProfileForModeratorController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Profile For Users
    Route::resource('profile-for-users', 'ProfileForUserController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Top Navigations
    Route::delete('top-navigations/destroy', 'TopNavigationController@massDestroy')->name('top-navigations.massDestroy');
    Route::resource('top-navigations', 'TopNavigationController');

    // Highlighted Categories
    Route::delete('highlighted-categories/destroy', 'HighlightedCategoriesController@massDestroy')->name('highlighted-categories.massDestroy');
    Route::resource('highlighted-categories', 'HighlightedCategoriesController');

    // Highlighted Sub Categories
    Route::delete('highlighted-sub-categories/destroy', 'HighlightedSubCategoriesController@massDestroy')->name('highlighted-sub-categories.massDestroy');
    Route::resource('highlighted-sub-categories', 'HighlightedSubCategoriesController');

    // Client Reviews
    Route::delete('client-reviews/destroy', 'ClientReviewController@massDestroy')->name('client-reviews.massDestroy');
    Route::post('client-reviews/media', 'ClientReviewController@storeMedia')->name('client-reviews.storeMedia');
    Route::post('client-reviews/ckmedia', 'ClientReviewController@storeCKEditorImages')->name('client-reviews.storeCKEditorImages');
    Route::resource('client-reviews', 'ClientReviewController');

    // Articles
    Route::delete('articles/destroy', 'ArticlesController@massDestroy')->name('articles.massDestroy');
    Route::post('articles/media', 'ArticlesController@storeMedia')->name('articles.storeMedia');
    Route::post('articles/ckmedia', 'ArticlesController@storeCKEditorImages')->name('articles.storeCKEditorImages');
    Route::resource('articles', 'ArticlesController');

    // Article Tags
    Route::delete('article-tags/destroy', 'ArticleTagsController@massDestroy')->name('article-tags.massDestroy');
    Route::resource('article-tags', 'ArticleTagsController');

    // Website Settings
    Route::delete('website-settings/destroy', 'WebsiteSettingsController@massDestroy')->name('website-settings.massDestroy');
    Route::resource('website-settings', 'WebsiteSettingsController');

    // Categories For Admins
    Route::delete('categories-for-admins/destroy', 'CategoriesForAdminController@massDestroy')->name('categories-for-admins.massDestroy');
    Route::post('categories-for-admins/media', 'CategoriesForAdminController@storeMedia')->name('categories-for-admins.storeMedia');
    Route::post('categories-for-admins/ckmedia', 'CategoriesForAdminController@storeCKEditorImages')->name('categories-for-admins.storeCKEditorImages');
    Route::resource('categories-for-admins', 'CategoriesForAdminController');

    // Sub Category For Admins
    Route::delete('sub-category-for-admins/destroy', 'SubCategoryForAdminController@massDestroy')->name('sub-category-for-admins.massDestroy');
    Route::post('sub-category-for-admins/media', 'SubCategoryForAdminController@storeMedia')->name('sub-category-for-admins.storeMedia');
    Route::post('sub-category-for-admins/ckmedia', 'SubCategoryForAdminController@storeCKEditorImages')->name('sub-category-for-admins.storeCKEditorImages');
    Route::resource('sub-category-for-admins', 'SubCategoryForAdminController');

    // Product For Vendors
    Route::delete('product-for-vendors/destroy', 'ProductForVendorController@massDestroy')->name('product-for-vendors.massDestroy');
    Route::post('product-for-vendors/media', 'ProductForVendorController@storeMedia')->name('product-for-vendors.storeMedia');
    Route::post('product-for-vendors/ckmedia', 'ProductForVendorController@storeCKEditorImages')->name('product-for-vendors.storeCKEditorImages');
    Route::resource('product-for-vendors', 'ProductForVendorController');

    // Service For Vendors
    Route::delete('service-for-vendors/destroy', 'ServiceForVendorController@massDestroy')->name('service-for-vendors.massDestroy');
    Route::post('service-for-vendors/media', 'ServiceForVendorController@storeMedia')->name('service-for-vendors.storeMedia');
    Route::post('service-for-vendors/ckmedia', 'ServiceForVendorController@storeCKEditorImages')->name('service-for-vendors.storeCKEditorImages');
    Route::resource('service-for-vendors', 'ServiceForVendorController');

    // P Product Listing For Vendors
    Route::delete('p-product-listing-for-vendors/destroy', 'PProductListingForVendorController@massDestroy')->name('p-product-listing-for-vendors.massDestroy');
    Route::resource('p-product-listing-for-vendors', 'PProductListingForVendorController');

    // P Service Listing For Vendors
    Route::delete('p-service-listing-for-vendors/destroy', 'PServiceListingForVendorController@massDestroy')->name('p-service-listing-for-vendors.massDestroy');
    Route::resource('p-service-listing-for-vendors', 'PServiceListingForVendorController');

    // Permission Group For Admins
    Route::delete('permission-group-for-admins/destroy', 'PermissionGroupForAdminController@massDestroy')->name('permission-group-for-admins.massDestroy');
    Route::resource('permission-group-for-admins', 'PermissionGroupForAdminController');

    // Feedback For Admins
    Route::delete('feedback-for-admins/destroy', 'FeedbackForAdminController@massDestroy')->name('feedback-for-admins.massDestroy');
    Route::post('feedback-for-admins/media', 'FeedbackForAdminController@storeMedia')->name('feedback-for-admins.storeMedia');
    Route::post('feedback-for-admins/ckmedia', 'FeedbackForAdminController@storeCKEditorImages')->name('feedback-for-admins.storeCKEditorImages');
    Route::resource('feedback-for-admins', 'FeedbackForAdminController');

    // Query From Website For Admins
    Route::delete('query-from-website-for-admins/destroy', 'QueryFromWebsiteForAdminController@massDestroy')->name('query-from-website-for-admins.massDestroy');
    Route::post('query-from-website-for-admins/media', 'QueryFromWebsiteForAdminController@storeMedia')->name('query-from-website-for-admins.storeMedia');
    Route::post('query-from-website-for-admins/ckmedia', 'QueryFromWebsiteForAdminController@storeCKEditorImages')->name('query-from-website-for-admins.storeCKEditorImages');
    Route::resource('query-from-website-for-admins', 'QueryFromWebsiteForAdminController');

    // P To Approve For Moderators
    Route::resource('p-to-approve-for-moderators', 'PToApproveForModeratorController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);
    Route::group(['prefix'=>'p-to-approve-for-moderators','as'=>'p-to-approve-for-moderators.'],function (){
        Route::get('approve', [PToApproveForModeratorController::class,'approve'])->name('approve');
    });


    // S To Approve For Moderators
    Route::resource('s-to-approve-for-moderators', 'SToApproveForModeratorController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    Route::group(['prefix'=>'s-to-approve-for-moderators','as'=>'s-to-approve-for-moderators.'],function (){
        Route::get('approve', [SToApproveForModeratorController::class,'approve'])->name('approve');
    });

    // To Approve Vendor For Admins
    Route::resource('to-approve-vendor-for-admins', 'ToApproveVendorForAdminController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Payment Gateway For Admins
    Route::post('payment-gateway-for-admins/media', 'PaymentGatewayForAdminController@storeMedia')->name('payment-gateway-for-admins.storeMedia');
    Route::post('payment-gateway-for-admins/ckmedia', 'PaymentGatewayForAdminController@storeCKEditorImages')->name('payment-gateway-for-admins.storeCKEditorImages');
    Route::resource('payment-gateway-for-admins', 'PaymentGatewayForAdminController', ['except' => ['create', 'store', 'destroy']]);

    // Email Settings For Admins
    Route::resource('email-settings-for-admins', 'EmailSettingsForAdminController', ['except' => ['create', 'store', 'destroy']]);

    // Message Box For Vendors
    Route::resource('message-box-for-vendors', 'MessageBoxForVendorController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Profile For Vendors
    Route::resource('profile-for-vendors', 'ProfileForVendorController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Highlighted Cities For Admins
    Route::delete('highlighted-cities-for-admins/destroy', 'HighlightedCitiesForAdminController@massDestroy')->name('highlighted-cities-for-admins.massDestroy');
    Route::post('highlighted-cities-for-admins/media', 'HighlightedCitiesForAdminController@storeMedia')->name('highlighted-cities-for-admins.storeMedia');
    Route::post('highlighted-cities-for-admins/ckmedia', 'HighlightedCitiesForAdminController@storeCKEditorImages')->name('highlighted-cities-for-admins.storeCKEditorImages');
    Route::resource('highlighted-cities-for-admins', 'HighlightedCitiesForAdminController');

    // Payment For Admins
    Route::delete('payment-for-admins/destroy', 'PaymentForAdminController@massDestroy')->name('payment-for-admins.massDestroy');
    Route::resource('payment-for-admins', 'PaymentForAdminController');
});
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
// Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});
