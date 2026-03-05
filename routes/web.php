<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ReferenceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TicketPurchaseController;
use App\Http\Controllers\Admin\TicketSaleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\SuperAdmin\AdminController as SuperAdminAdminController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});


Route::prefix('super-admin')->name('superadmin.')->group(function () {


    Route::middleware('auth:superadmin')->group(function () {
        Route::get('/dashboard', function () {
            return view('super-admin.dashboard');
        })->name('dashboard');

        Route::get('/admins', [SuperAdminAdminController::class, 'index'])->name('admins.index');
        Route::post('/admins', [SuperAdminAdminController::class, 'store'])->name('admins.store');
        Route::get('/admins/{user}/edit', [SuperAdminAdminController::class, 'edit'])->name('admins.edit');
        Route::put('/admins/{user}', [SuperAdminAdminController::class, 'update'])->name('admins.update');
        Route::patch('/admins/{user}/status', [SuperAdminAdminController::class, 'updateStatus'])->name('admins.status');
        Route::delete('/admins/{user}', [SuperAdminAdminController::class, 'destroy'])->name('admins.destroy');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});


Route::middleware(['auth', 'ensure.permission'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
    Route::get('/vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
    Route::put('/vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
    Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');
    Route::get('/vendors/{vendor}/history', [VendorController::class, 'history'])->name('vendors.history');


    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/references', [ReferenceController::class, 'index'])->name('references.index');
    Route::post('/references', [ReferenceController::class, 'store'])->name('references.store');
    Route::get('/references/{reference}/edit', [ReferenceController::class, 'edit'])->name('references.edit');
    Route::get('/references/{reference}/history', [ReferenceController::class, 'history'])->name('references.history');
    Route::put('/references/{reference}', [ReferenceController::class, 'update'])->name('references.update');
    Route::delete('/references/{reference}', [ReferenceController::class, 'destroy'])->name('references.destroy');



    Route::get('/ticket-purchases', [TicketPurchaseController::class, 'index'])->name('ticket_purchases.index');
    Route::get('/ticket-purchases/create', [TicketPurchaseController::class, 'create'])->name('ticket_purchases.create');
    Route::post('/ticket-purchases', [TicketPurchaseController::class, 'store'])->name('ticket_purchases.store');
    Route::get('/ticket-purchases/{ticketPurchase}/edit', [TicketPurchaseController::class, 'edit'])->name('ticket_purchases.edit');
    Route::put('/ticket-purchases/{ticketPurchase}', [TicketPurchaseController::class, 'update'])->name('ticket_purchases.update');
    Route::delete('/ticket-purchases/{ticketPurchase}', [TicketPurchaseController::class, 'destroy'])->name('ticket_purchases.destroy');

    Route::get('/ticket-purchases/{ticketPurchase}/payment-history', [TicketPurchaseController::class, 'paymentHistory'])
        ->name('ticket_purchases.payment_history');



    // History edit
    Route::get('/ticket-purchases/{ticketPurchase}/payment-history/add', [TicketPurchaseController::class, 'addPaymentForm'])
        ->name('ticket_purchases.payment_history.add');

    Route::post('/ticket-purchases/{ticketPurchase}/add-payment', [TicketPurchaseController::class, 'addPayment'])
        ->name('ticket_purchases.payment_history.store');

    Route::get('/ticket-purchases/payment-history/{history}/edit', [TicketPurchaseController::class, 'editPaymentHistory'])
        ->name('ticket_purchases.payment_history.edit');

    Route::put('/ticket-purchases/payment-history/{history}', [TicketPurchaseController::class, 'updatePaymentHistory'])
        ->name('ticket_purchases.payment_history.update');





    Route::get('/ticket-sales', [TicketSaleController::class, 'index'])->name('ticket_sales.index');
    Route::get('/ticket-sales/create', [TicketSaleController::class, 'create'])->name('ticket_sales.create');
    Route::post('/ticket-sales', [TicketSaleController::class, 'store'])->name('ticket_sales.store');
    Route::get('/ticket-sales/{ticketSale}/edit', [TicketSaleController::class, 'edit'])->name('ticket_sales.edit');
    Route::put('/ticket-sales/{ticketSale}', [TicketSaleController::class, 'update'])->name('ticket_sales.update');
    Route::delete('/ticket-sales/{ticketSale}', [TicketSaleController::class, 'destroy'])->name('ticket_sales.destroy');

    Route::get('/ticket-sales/{ticketSale}/payment-history', [TicketSaleController::class, 'paymentHistory'])
        ->name('ticket_sales.payment_history');

    Route::get('/ticket-sales/{ticketSale}/payment-history/add', [TicketSaleController::class, 'addPaymentForm'])
        ->name('ticket_sales.payment_history.add');

    Route::post('/ticket-sales/{ticketSale}/payment-history', [TicketSaleController::class, 'storePaymentHistory'])
        ->name('ticket_sales.payment_history.store');
    Route::get('/ticket-sales/{ticketSale}/payment-history', [TicketSaleController::class, 'paymentHistory'])->name('ticket_sales.payment_history');
    Route::get('/ticket-sales/{ticketSale}/payment-history', [TicketSaleController::class, 'paymentHistory'])
        ->name('ticket_sales.payment_history');
    Route::get('/ticket-sales/{ticketSale}/payment-history/{history}/edit', [TicketSaleController::class, 'editPaymentHistory'])
        ->name('ticket_sales.payment_history.edit');
    Route::put('/ticket-sales/{ticketSale}/payment-history/{history}', [TicketSaleController::class, 'updatePaymentHistory'])
        ->name('ticket_sales.payment_history.update');





    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});





Route::get('/', function () {
    return redirect()->route('login');
    // return view('website/home');
});
