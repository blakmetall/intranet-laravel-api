<?php


// users
Route::group(['prefix' => 'users', 'middleware' => ['auth:api']], function () {
    Route::get('all', 'UsersCtrl@all');
    Route::post('store', 'UsersCtrl@store');
    Route::get('get/{user}', 'UsersCtrl@get');
    Route::post('update/{user}', 'UsersCtrl@update');
    Route::get('delete/{id}', 'UsersCtrl@delete');
    Route::get('restore/{id}', 'UsersCtrl@restore');

    Route::get('getCurrent', 'UsersCtrl@getCurrent');
    Route::get('getDirectory/{filterOption?}', 'UsersCtrl@getDirectory');
    Route::get('getAssignableUsersForTask', 'UsersCtrl@getAssignableUsersForTask');

    // roles
    Route::group(['prefix' => 'roles'], function () {
        Route::get('all', 'UsersRolesCtrl@all');
    });

    // permissions groups
    Route::group(['prefix' => 'permissions-groups', 'middleware' => ['auth:api']], function () {
        Route::get('all', 'UsersPermissionsGroupsCtrl@all');
        Route::post('store', 'UsersPermissionsGroupsCtrl@store');
        Route::get('get/{group}', 'UsersPermissionsGroupsCtrl@get');
        Route::post('update/{group}', 'UsersPermissionsGroupsCtrl@update');
        Route::get('delete/{id}', 'UsersPermissionsGroupsCtrl@delete');

        // single permissions
        Route::group(['prefix' => 'permissions'], function () {
            Route::get('all', 'UsersPermissionsCtrl@all');
            Route::post('store', 'UsersPermissionsCtrl@store');
            Route::get('get/{permission}', 'UsersPermissionsCtrl@get');
            Route::post('update/{permission}', 'UsersPermissionsCtrl@update');
            Route::get('delete/{id}', 'UsersPermissionsCtrl@delete');
        });
    });
});


// events
Route::group(['prefix' => 'events', 'middleware' => ['auth:api']], function () {
    Route::get('all', 'EventsCtrl@all');
    Route::post('store', 'EventsCtrl@store');
    Route::get('get/{event}', 'EventsCtrl@get');
    Route::post('edit/{event}', 'EventsCtrl@edit');
    Route::get('delete/{id}', 'EventsCtrl@delete');

    // event categories
    Route::get('categories/all', 'EventsCategoriesCtrl@all');
    Route::get('categories/get/{category}', 'EventsCategoriesCtrl@get');
    Route::post('categories/update/{category}', 'EventsCategoriesCtrl@update');
    Route::post('categories/store', 'EventsCategoriesCtrl@store');
    Route::get('categories/delete/{id}', 'EventsCategoriesCtrl@delete');
});


// Tasks
Route::group(['prefix' => 'tasks', 'middleware' => ['auth:api']], function () {
    Route::get('all', 'TasksCtrl@all');
    Route::post('store', 'TasksCtrl@store');
    Route::get('get/{task}', 'TasksCtrl@get');
    Route::post('update/{task}', 'TasksCtrl@update');
    Route::get('archive/{task}', 'TasksCtrl@archive');
    Route::get('delete/{id}', 'TasksCtrl@delete');

    Route::get('statusList', 'TasksCtrl@statusList');
    Route::post('changeStatus/{task}', 'TasksCtrl@changeStatus');
});


// Notifications
Route::group(['prefix' => 'notifications', 'middleware' => ['auth:api']], function () {
    Route::get('all', 'NotificationsCtrl@all');
    Route::get('delete/{id}', 'NotificationsCtrl@delete');
    Route::get('deleteAll', 'NotificationsCtrl@deleteAll');
    Route::get('setViewedStatus/{id}', 'NotificationsCtrl@setViewedStatus');
});

// Chat
Route::group(['prefix' => 'chat', 'middleware' => ['auth:api']], function () {
    Route::get('get/unreadMessages/count', 'ChatCtrl@getUnreadMessagesCount');
    Route::get('get/{from_user}/{limit?}', 'ChatCtrl@get');
    Route::post('send/{to_user}', 'ChatCtrl@send');
});


// hotels
Route::group(['prefix' => 'hotels', 'middleware' => ['auth:api']], function () {
    Route::get('all', 'HotelsCtrl@all');
    Route::post('store', 'HotelsCtrl@store');
    Route::get('get/{hotel}', 'HotelsCtrl@get');
    Route::post('update/{hotel}', 'HotelsCtrl@update');
    Route::get('delete/{id}', 'HotelsCtrl@delete');
    Route::get('restore/{id}', 'HotelsCtrl@restore');

    Route::get('order/{hotel}/{direction}', 'HotelsCtrl@order');

    Route::get('brandsiteSections/{hotelBrandsiteSection}', 'HotelsBrandsiteSectionsCtrl@get');

    Route::get('{hotel}/brandsiteSections', 'HotelsCtrl@getBrandsiteSections');
    Route::post('{hotel}/brandsiteSections/update', 'HotelsCtrl@updateBrandsiteSections');
});


// companies
Route::group(['prefix' => 'companies', 'middleware' => ['auth:api']], function () {
    Route::get('all', 'CompaniesCtrl@all');
    Route::post('store', 'CompaniesCtrl@store');
    Route::get('get/{company}', 'CompaniesCtrl@get');
    Route::post('update/{company}', 'CompaniesCtrl@update');
    Route::get('delete/{id}', 'CompaniesCtrl@delete');
    Route::get('restore/{id}', 'CompaniesCtrl@restore');

    Route::get('categories', 'CompaniesCtrl@categories');
});


// addresses
Route::group(['prefix' => 'addresses', 'middleware' => ['auth:api']], function () {
    Route::get('all', 'AddressesCtrl@all');
    Route::post('store', 'AddressesCtrl@store');
    Route::get('get/{address}', 'AddressesCtrl@get');
    Route::post('update/{address}', 'AddressesCtrl@update');
    Route::get('delete/{id}', 'AddressesCtrl@delete');
    Route::get('restore/{id}', 'AddressesCtrl@restore');
});

// countries
Route::group(['prefix' => 'countries', 'middleware' => ['auth:api']], function () {
    Route::get('all', 'CountriesCtrl@all');
    Route::get('get/{country}', 'CountriesCtrl@get');
});

// states
Route::group(['prefix' => 'states', 'middleware' => ['auth:api']], function () {
    Route::get('all', 'StatesCtrl@all');
    Route::get('get/{state}', 'StatesCtrl@get');
});

// quality
Route::group(['prefix' => 'quality', 'middleware' => ['auth:api']], function () {
    Route::get('all', 'QualityAssuranceVisitsCtrl@all');
    Route::post('store', 'QualityAssuranceVisitsCtrl@store');
    Route::get('get/{visit}', 'QualityAssuranceVisitsCtrl@get');
    Route::post('update/{visit}', 'QualityAssuranceVisitsCtrl@update');
    Route::get('delete/{id}', 'QualityAssuranceVisitsCtrl@delete');

    Route::group(['prefix' => 'status'], function () {
        Route::get('all', 'QualityAssuranceVisitsStatusesCtrl@all');
    });

    // visit extension request
    Route::group(['prefix' => 'extension'], function () {
        Route::get('all', 'QualityVisitsExtensionsRequestsCtrl@all');
        Route::post('store', 'QualityVisitsExtensionsRequestsCtrl@store');
        Route::get('get/{extensionRequest}', 'QualityVisitsExtensionsRequestsCtrl@get');
        Route::post('update/{extensionRequest}', 'QualityVisitsExtensionsRequestsCtrl@update');
        Route::get('delete/{id}', 'QualityVisitsExtensionsRequestsCtrl@delete');

        Route::post('approve/{extensionRequest}', 'QualityVisitsExtensionsRequestsCtrl@approve');
        Route::post('deny/{extensionRequest}', 'QualityVisitsExtensionsRequestsCtrl@deny');
    });

    // visit exensions request
    Route::group(['prefix' => 'exensions'], function () {
        Route::get('all', 'QualityVisitsExensionsRequestsCtrl@all');
        Route::post('store', 'QualityVisitsExensionsRequestsCtrl@store');
        Route::get('get/{exensionRequest}', 'QualityVisitsExensionsRequestsCtrl@get');
        Route::post('update/{exensionRequest}', 'QualityVisitsExensionsRequestsCtrl@update');
        Route::get('delete/{id}', 'QualityVisitsExensionsRequestsCtrl@delete');

        Route::post('approve/{exensionRequest}', 'QualityVisitsExensionsRequestsCtrl@approve');
        Route::post('deny/{exensionRequest}', 'QualityVisitsExensionsRequestsCtrl@deny');
    });

});


// brandsite
Route::group(['prefix' => 'brandsite', 'middleware' => ['auth:api']], function () {

    // sections
    Route::group(['prefix' => 'sections'], function () {
        Route::get('all', 'BrandsiteSectionsCtrl@all');
        Route::post('store', 'BrandsiteSectionsCtrl@store');
        Route::get('get/{section}', 'BrandsiteSectionsCtrl@get');
        Route::post('update/{section}', 'BrandsiteSectionsCtrl@update');
        Route::get('delete/{id}', 'BrandsiteSectionsCtrl@delete');
        Route::get('restore/{id}', 'BrandsiteSectionsCtrl@restore');
    });

});


// mahgazine
Route::group(['prefix' => 'mahgazine', 'middleware' => ['auth:api']], function () {

    Route::group(['prefix' => 'editions'], function () {
        Route::get('all', 'MahgazineEditionsCtrl@all');
        Route::post('store', 'MahgazineEditionsCtrl@store');
        Route::get('get/{edition}', 'MahgazineEditionsCtrl@get');
        Route::post('update/{edition}', 'MahgazineEditionsCtrl@update');
        Route::get('delete/{id}', 'MahgazineEditionsCtrl@delete');
        Route::get('restore/{id}', 'MahgazineEditionsCtrl@restore');

    });

    Route::group(['prefix' => 'sections'], function () {
        Route::get('all', 'MahgazineSectionsCtrl@all');
        Route::post('store', 'MahgazineSectionsCtrl@store');
        Route::get('get/{section}', 'MahgazineSectionsCtrl@get');
        Route::post('update/{section}', 'MahgazineSectionsCtrl@update');
        Route::get('delete/{id}', 'MahgazineSectionsCtrl@delete');
        Route::get('restore/{id}', 'MahgazineSectionsCtrl@restore');

        Route::get('order/{section}/{direction}', 'MahgazineSectionsCtrl@order');
    });

    Route::group(['prefix' => 'articles'], function () {
        Route::get('all', 'MahgazineArticlesCtrl@all');
        Route::post('store', 'MahgazineArticlesCtrl@store');
        Route::get('get/{article}', 'MahgazineArticlesCtrl@get');
        Route::post('update/{article}', 'MahgazineArticlesCtrl@update');
        Route::get('delete/{id}', 'MahgazineArticlesCtrl@delete');
        Route::get('restore/{id}', 'MahgazineArticlesCtrl@restore');

        Route::get('order/{article}/{direction}', 'MahgazineArticlesCtrl@order');
    });

});


// folders
Route::group(['prefix' => 'folders', 'middleware' => ['auth:api']], function () {

    Route::get('get/{folder}', 'FoldersCtrl@get');
    Route::post('store', 'FoldersCtrl@store');
    Route::post('update/{folder}', 'FoldersCtrl@update');
    Route::get('delete/{id}', 'FoldersCtrl@delete');
    Route::get('restore/{id}', 'FoldersCtrl@restore');

    Route::post('get/{folder}/getChilds', 'FoldersCtrl@getChilds');
    Route::get('get/{folder}/getRootTree', 'FoldersCtrl@getRootTree');
    Route::get('get/{folder}/getBreadcrumbs', 'FoldersCtrl@getBreadcrumbs');
    Route::get('get/{folder}/getUsersPermitted', 'FoldersCtrl@getUsersPermitted');

    Route::get('getRoot/{polymorphic_id}/{polymorphic_type}', 'FoldersCtrl@getRoot');
    Route::post('getRootFolderAvailableFeatures', 'FoldersCtrl@getRootFolderAvailableFeatures');

});


// files
Route::group(['prefix' => 'files', 'middleware' => ['auth:api']], function () {
    //Route::get('all', 'FilesCtrl@all');
    //Route::get('get/{address}', 'FilesCtrl@get');
    Route::post('update/{file}', 'FilesCtrl@update');
    Route::get('delete/{id}', 'FilesCtrl@delete');
    //Route::get('restore/{id}', 'FilesCtrl@restore');

    // routes to manage single upload file ( receive post parameters to manage file replacement if applies )
    Route::post('singleUpload/{item_type}/{item_id}/{input_id}', 'FilesCtrl@singleUpload');

    // routes for folder / files module
    Route::post('getFromFolder/{folder}', 'FilesCtrl@getFromFolder');
    Route::post('uploadToFolder/{folder}', 'FilesCtrl@uploadToFolder');
    Route::post('{file}/updateFeatures', 'FilesCtrl@updateFeatures');
    Route::post('{file}/updateFeaturedStatus', 'FilesCtrl@updateFeaturedStatus');
    Route::post('{file}/updateFlipPageStatus', 'FilesCtrl@updateFlipPageStatus');
});

// Settings
Route::group(['prefix' => 'settings', 'middleware' => ['auth:api']], function () {
    Route::get('get', 'SettingsCtrl@get');
    Route::post('update', 'SettingsCtrl@update');
});

// Download Route
Route::group(['prefix' => 'downloads'], function () {
    Route::get('{file_id}/{file_slug}/{media_size?}', 'DownloadsCtrl@download');
});

//session and user
Route::group(['prefix'=> 'session'], function () {
    Route::post('login', 'AuthCtrl@login');
    Route::post('logout', 'AuthCtrl@logout');

    Route::post('requestRecovery', 'AuthCtrl@requestRecovery');
    Route::get('verifyRecoveryKey/{recovery_key}', 'AuthCtrl@verifyRecoveryKey');
    Route::post('resetPassword', 'AuthCtrl@resetPassword');
});