<?php

use App\Http\Controllers\Customized\DashboardController;
use App\Http\Controllers\Customized\NCD\AppointmentsReviewController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\ProjectDataReportingController;
use App\Http\Controllers\Queries\FieldFilteringController;
use App\Http\Controllers\Queries\FieldNameReportController;
use App\Http\Controllers\Queries\FieldRecordContainingResponseController;
use App\Http\Controllers\Queries\FieldResponseListController;
use App\Http\Controllers\Queries\FieldEventResponseListController;
use App\Http\Controllers\Queries\ProcessFilteredQueryController;
use App\Http\Controllers\Queries\UnionFilteredQueryController;
use App\Http\Controllers\Queries\IntersectionFilteredQueryController;
use App\Http\Controllers\Queries\GetEventsController;
use App\Http\Controllers\Queries\GetRespondentEventsFieldDataController;
use App\Http\Controllers\Queries\RepondentRecordTrackingController;
use App\Http\Controllers\Queries\ExceptFilteredQueryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        //'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    //
    Route::resource('projects', ProjectsController::class,['only' =>['index','show']])
    ->name('index', 'projects')
    ->name('show', 'projects.show');

    Route::get('project/metadata/{projectId}', [ProjectDataReportingController::class,'read'])->name('project-metadata');
    Route::get('project/{projectId}/record/{recordId}', [ProjectDataReportingController::class,'getRespondentResponses'])->name('record-data');

    //data

    Route::get('project/{projectId}/search',[ProjectDataReportingController::class, 'filterDashboard'])->name('search-filter');
    Route::get('project/{projectId}/data', [ProjectDataReportingController::class,'dataQuery'])->name('data-query');

    //Queries
    Route::get('project/{projectId}/filtering', FieldFilteringController::class)->name('filtering');
    Route::get('project/{projectId}/fieldname/{fieldName}', FieldNameReportController::class)->name('project-item-report');
    Route::get('project/{projectId}/question/{fieldName}/response/{value}', FieldResponseListController::class)->name('data-for-counts');
    Route::get('project/{projectId}/event/{eventId}/question/{fieldName}/response/{value}', FieldEventResponseListController::class)->name('events-for-counts');
    Route::get('project/{projectId}/event/{eventId}/respondent/{record}', FieldRecordContainingResponseController::class)->name('data-for-record-survey');
    Route::get('project/{projectId}/process/filtered/query', ProcessFilteredQueryController::class)->name('data-for-record-survey');
    Route::get('project/{projectId}/union/{unionType}/filtered/query', UnionFilteredQueryController::class)->name('union-queries-survey');
    Route::get('project/{projectId}/except/filtered/query', ExceptFilteredQueryController::class)->name('except-queries-survey');
    Route::get('project/{projectId}/intersection/filtered/{type}/query', IntersectionFilteredQueryController::class)->name('intersection-queries-survey');
    Route::get('project/{projectId}/fieldname/{fieldName}/events',GetEventsController::class)->name('events-survey'); //no view
    Route::get('project/{projectId}/record/{record}/fieldname/{fieldName}', GetRespondentEventsFieldDataController::class)->name('record-field-project');
    Route::get('project/{projectId}/record/{recordId}/tracking', RepondentRecordTrackingController::class)->name('record-tracking');

    //Custom Pages
    Route::get('project/{projectId}/custom-pages', [DashboardController::class,'read'])->name('custom-pages');
    //NCD
    Route::get('project/{projectId}/custom-pages/appointment-reviews',AppointmentsReviewController::class)->name('appointment-reviews');
});
