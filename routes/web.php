<?php

use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\CheckCertificate;
use App\Http\Controllers\Impersonate;
use App\Http\Controllers\OpenAiController;
use App\Http\Controllers\SearchDispatecherController;
use App\Http\Controllers\SiteController;
use App\Http\Livewire\Admin\Settings\Payment\Fawaterk;
use App\Livewire\Frontend\BlogDetails;
use App\Livewire\Frontend\Blogs;
use App\Livewire\Frontend\Checkout;
use App\Livewire\Frontend\ThankYou;
use App\Livewire\Pages\Common\Bookings\UserBooking;
use App\Livewire\Pages\Common\Dispute\Dispute;
use App\Livewire\Pages\Common\Dispute\ManageDispute;
use App\Livewire\Pages\Common\ProfileSettings\AccountSettings;
use App\Livewire\Pages\Common\ProfileSettings\IdentityVerification;
use App\Livewire\Pages\Common\ProfileSettings\PersonalDetails;
use App\Livewire\Pages\Common\ProfileSettings\Resume;
use App\Livewire\Pages\Student\BillingDetail\BillingDetail;
use App\Livewire\Pages\Student\Favourite\Favourites;
use App\Livewire\Pages\Student\CertificateList;
use App\Livewire\Pages\Student\Invoices;
use App\Livewire\Pages\Student\RescheduleSession;
use App\Livewire\Pages\Tutor\ManageAccount\ManageAccount;
use App\Livewire\Pages\Tutor\ManageSessions\ManageSubjects;
use App\Livewire\Pages\Tutor\ManageSessions\MyCalendar;
use App\Livewire\Pages\Tutor\ManageSessions\SessionDetail;
use App\Livewire\Payouts;
use App\Services\HesabePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use function JmesPath\search;


Route::get('/dbNew', function () {
    return 'No thing to do!';
});

Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    return response()->json([
        'status' => 'success',
        'message' => 'All Cleared!'
    ]);
});

Route::get('/rebuild-storage-link', function () {
    $link = public_path('storage');
    if (File::exists($link)) {
        File::delete($link);
    }
    Artisan::call('storage:link');
    return 'Storage link rebuilt successfully.';
});

Route::get('check-certificate', [CheckCertificate::class, 'index'])->name('check-certificate');
Route::get('checkCertificateAr', [CheckCertificate::class, 'checkAr'])->name('checkCertificateAr');
Route::get('checkCertificate', [CheckCertificate::class, 'checkEn'])->name('checkCertificate');

Route::get('auth/{provider}', [SocialController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialController::class, 'callback'])->name('social.callback');
Route::view('courses-short-code', 'courses-short-code');

Route::middleware(['locale', 'maintenance'])->group(function () {
    Route::get('find-tutors', [SearchController::class, 'findTutors'])->name('find-tutors');
    Route::get('/blogs', Blogs::class)->name('blogs');
    Route::get('/blog/{slug}', BlogDetails::class)->name('blog-details');

    Route::middleware(['auth', 'verified', 'onlineUser'])->group(function () {
        Route::post('/openai/submit', [OpenAiController::class, 'submit'])->name('openai.submit');
        Route::post('favourite-tutor', [SearchController::class, 'favouriteTutor'])->name('favourite-tutor');
        Route::get('logout', [SiteController::class, 'logout'])->name('logout');
        Route::get('user/identity-confirmation/{id}', [PersonalDetails::class, 'confirmParentVerification'])->name('confirm-identity');
        Route::get('google/callback', [SiteController::class, 'getGoogleToken'])->name('getGoogleToken');
        Route::get('/google/redirect', [SiteController::class, 'redirectToGoogleCalendar'])->name('tutor.google.redirect');

        Route::middleware('role:tutor|student')->get('checkout', Checkout::class)->name('checkout');
        Route::middleware('role:tutor|student')->get('thank-you/{id}', ThankYou::class)->name('thank-you');

        Route::middleware('role:tutor')->prefix('tutor')->name('tutor.')->group(function () {
            Route::get('dashboard', ManageAccount::class)->name('dashboard');
            Route::get('payouts', Payouts::class)->name('payouts');
            Route::get('profile', fn() => redirect('tutor.profile.personal-details'))->name('profile');

            Route::prefix('profile')->name('profile.')->group(function () {
                Route::get('personal-details', PersonalDetails::class)->name('personal-details');
                Route::get('account-settings', AccountSettings::class)->name('account-settings');
                Route::prefix('resume')->name('resume.')->group(function () {
                    Route::get('education', Resume::class)->name('education');
                    Route::get('experience', Resume::class)->name('experience');
                    Route::get('certificate', Resume::class)->name('certificate');
                });
                Route::get('identification', IdentityVerification::class)->name('identification');
            });
            Route::prefix('bookings')->name('bookings.')->group(function () {
                Route::get('manage-subjects', ManageSubjects::class)->name('subjects');
                Route::get('manage-sessions', MyCalendar::class)->name('manage-sessions');
                Route::get('session-detail/{date}', SessionDetail::class)->name('session-detail');
                Route::get('upcoming-bookings', UserBooking::class)->name('upcoming-bookings');
            });
            Route::get('invoices', Invoices::class)->name('invoices');
            Route::get('disputes', Dispute::class)->name('disputes');
            Route::get('manage-dispute/{id}', ManageDispute::class)->name('manage-dispute');
        });

        Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
            Route::get('profile', fn() => redirect('tutor.profile.personal-details'))->name('profile');
            Route::prefix('profile')->name('profile.')->group(function () {
                Route::get('personal-details', PersonalDetails::class)->name('personal-details');
                Route::get('account-settings', AccountSettings::class)->name('account-settings');
                Route::get('identification', IdentityVerification::class)->name('identification');
            });
            Route::get('bookings', UserBooking::class)->name('bookings');
            Route::get('invoices', Invoices::class)->name('invoices');
            Route::get('billing-detail', BillingDetail::class)->name('billing-detail');
            Route::get('favourites', Favourites::class)->name('favourites');
            Route::get('reschedule-session/{id}', RescheduleSession::class)->name('reschedule-session');
            Route::get('complete-booking/{id}', [SiteController::class, 'completeBooking'])->name('complete-booking');
            Route::get('certificates', CertificateList::class)->name('certificate-list');
            Route::get('disputes', Dispute::class)->name('disputes');
            Route::get('manage-dispute/{id}', ManageDispute::class)->name('manage-dispute');
        });
    });

    Route::post('/remove-cart', [SiteController::class, 'removeCart']);
    Route::get('tutor/{slug}', [SearchController::class, 'tutorDetail'])->name('tutor-detail');
    Route::get('{gateway}/process/payment', [SiteController::class, 'processPayment'])->name('payment.process');
    Route::get('/payment/hesabe/callback', [SiteController::class, 'hesabePaymentCallback'])->name('payment.hesabe.callback');
    Route::get('checkout/cancel', fn() => redirect()->route('invoices')->with('payment_cancel', __('general.payment_cancelled_desc')))->name('checkout.cancel');
    Route::post('payfast/webhook', [SiteController::class, 'payfastWebhook'])->name('payfast.webhook');
    Route::post('payment/success', [SiteController::class, 'paymentSuccess'])->name('post.success');
    Route::get('payment/success', [SiteController::class, 'paymentSuccess'])->name('get.success');
    Route::post('switch-lang', [SiteController::class, 'switchLang'])->name('switch-lang');
    Route::post('switch-currency', [SiteController::class, 'switchCurrency'])->name('switch-currency');
    Route::get('exit-impersonate', [Impersonate::class, 'exitImpersonate'])->name('exit-impersonate');
    Route::get('pay/{id}', [SiteController::class, 'preparePayment'])->name('pay');
    Route::get('session/{id}', [SiteController::class, 'sessionDetail'])->name('session-detail');
    Route::post('book-session', [SiteController::class, 'bookSession'])->name('book-session');
    Route::get('/search', function (Request $request) {
        $type = $request->input('type');
        $q = $request->input('q');

        if ($type === 'courses') {
            // return redirect()->route('courses.search-courses', ['q' => $q, 'type' => 'courses']);
            return redirect()->route('courses.search-courses', $request->query());
        }
        if ($type === 'tutors') {
            // return redirect()->route('find-tutors', ['q' => $q, 'type' => 'tutors']);
            return redirect()->route('find-tutors', $request->query());
        }

        // fallback (optional)
        abort(404);
    })->name('search');

    require __DIR__ . '/auth.php';
    require __DIR__ . '/admin.php';
    require __DIR__ . '/optionbuilder.php';
    if (!request()->is('api/*')) {
        require __DIR__ . '/pagebuilder.php';
    }

    // route::get('home-four',[HomefourController::class,'index','index'])->name('home4');
});
