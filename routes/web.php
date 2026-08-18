<?php

use App\Http\Controllers\Admin\CrudController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/why-us', [PageController::class, 'whyUs'])->name('why-us');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/case-studies', [PageController::class, 'caseStudies'])->name('case-studies');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [PageController::class, 'blogShow'])->name('blog.show');
Route::get('/careers', [PageController::class, 'careers'])->name('careers');
Route::get('/careers/apply', [PageController::class, 'careersApply'])->name('careers.apply');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/start-project', [PageController::class, 'startProject'])->name('start-project');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::post('/contact', [FormController::class, 'contact'])->name('contact.submit');
Route::post('/start-project', [FormController::class, 'project'])->name('project.submit');
Route::post('/careers/apply', [FormController::class, 'jobApply'])->name('careers.apply.submit');
Route::post('/newsletter', [FormController::class, 'newsletter'])->name('newsletter.submit');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user && $user->isEmployee()) {
            return redirect()->route('employee.dashboard');
        }

        return redirect()->route('admin.dashboard');
    })->name('dashboard');

    Route::prefix('employee')->name('employee.')->middleware('employee')->group(function () {
        Route::get('/', [EmployeePortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/attendance', [EmployeePortalController::class, 'attendance'])->name('attendance');
        Route::post('/attendance/check-in', [EmployeePortalController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/check-out', [EmployeePortalController::class, 'checkOut'])->name('attendance.checkout');
        Route::get('/leaves', [EmployeePortalController::class, 'leaves'])->name('leaves');
        Route::post('/leaves', [EmployeePortalController::class, 'storeLeave'])->name('leaves.store');
        Route::get('/payroll', [EmployeePortalController::class, 'payroll'])->name('payroll');
        Route::get('/interviews', [EmployeePortalController::class, 'interviews'])->name('interviews');
        Route::get('/interviews/{id}', [EmployeePortalController::class, 'interviewShow'])->whereNumber('id')->name('interviews.show');
        Route::post('/interviews/{id}/complete', [EmployeePortalController::class, 'interviewComplete'])->whereNumber('id')->name('interviews.complete');
        Route::get('/announcements', [EmployeePortalController::class, 'announcements'])->name('announcements');
        Route::get('/tasks', [EmployeePortalController::class, 'tasks'])->name('tasks.index');
        Route::get('/tasks/feed', [EmployeePortalController::class, 'taskFeed'])->name('tasks.feed');
        Route::get('/tasks/{task}', [EmployeePortalController::class, 'showTask'])->whereNumber('task')->name('tasks.show');
        Route::get('/tasks/{task}/replies', [EmployeePortalController::class, 'taskReplies'])->whereNumber('task')->name('tasks.replies');
        Route::get('/tasks/{task}/stream', [EmployeePortalController::class, 'taskStream'])->whereNumber('task')->name('tasks.stream');
        Route::post('/tasks/{task}/reply', [EmployeePortalController::class, 'replyToTask'])->whereNumber('task')->name('tasks.reply');
        Route::post('/tasks/{task}/status', [EmployeePortalController::class, 'updateTaskStatus'])->whereNumber('task')->name('tasks.status');
        Route::get('/live-chat', [EmployeePortalController::class, 'chat'])->name('chat.index');
        Route::get('/live-chat/messages', [EmployeePortalController::class, 'chatMessages'])->name('chat.messages');
        Route::get('/live-chat/stream', [EmployeePortalController::class, 'chatStream'])->name('chat.stream');
        Route::post('/live-chat/send', [EmployeePortalController::class, 'sendChat'])->name('chat.send');
        Route::get('/notifications', [EmployeePortalController::class, 'notifications'])->name('notifications.index');
        Route::post('/notifications/read-all', [EmployeePortalController::class, 'markNotificationsRead'])->name('notifications.read-all');
        Route::get('/notifications/{id}', [EmployeePortalController::class, 'openNotification'])->whereNumber('id')->name('notifications.open');
        Route::get('/profile', [EmployeePortalController::class, 'profile'])->name('profile');
        Route::post('/password', [EmployeePortalController::class, 'updatePassword'])->name('password');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/overview', [DashboardController::class, 'overview'])->name('overview');
        Route::get('/career-page', [DashboardController::class, 'careerPage'])->name('career-page');
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [DashboardController::class, 'exportReport'])->name('reports.export');
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
        Route::get('/notifications', [\App\Http\Controllers\Admin\InboxController::class, 'notifications'])->name('notifications.index');
        Route::get('/messages', [\App\Http\Controllers\Admin\InboxController::class, 'messages'])->name('messages.index');
        Route::get('/inbox/summary', [\App\Http\Controllers\Admin\InboxController::class, 'summary'])->name('inbox.summary');
        Route::get('/account', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/account', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/account/password', [\App\Http\Controllers\Admin\ProfileController::class, 'password'])->name('profile.password');
        Route::post('/account/avatar', [\App\Http\Controllers\Admin\ProfileController::class, 'avatar'])->name('profile.avatar');
        Route::delete('/account/avatar', [\App\Http\Controllers\Admin\ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'edit'])->name('settings');
        Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('/integrations', fn () => redirect()->route('admin.settings'))->name('integrations');
        Route::get('/backup', fn () => redirect()->route('admin.settings'))->name('backup');

        Route::get('/contacts-inbox', [\App\Http\Controllers\Admin\ContactInquiryController::class, 'index'])->name('contacts.index');
        Route::get('/contacts-inbox/{id}', [\App\Http\Controllers\Admin\ContactInquiryController::class, 'show'])->whereNumber('id')->name('contacts.show');
        Route::patch('/contacts-inbox/{id}/status', [\App\Http\Controllers\Admin\ContactInquiryController::class, 'status'])->whereNumber('id')->name('contacts.status');
        Route::delete('/contacts-inbox/{id}', [\App\Http\Controllers\Admin\ContactInquiryController::class, 'destroy'])->whereNumber('id')->name('contacts.destroy');

        Route::get('/announcements-board', [\App\Http\Controllers\Admin\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements-board/create', [\App\Http\Controllers\Admin\AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements-board', [\App\Http\Controllers\Admin\AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements-board/{id}/edit', [\App\Http\Controllers\Admin\AnnouncementController::class, 'edit'])->whereNumber('id')->name('announcements.edit');
        Route::put('/announcements-board/{id}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'update'])->whereNumber('id')->name('announcements.update');
        Route::post('/announcements-board/{id}/publish', [\App\Http\Controllers\Admin\AnnouncementController::class, 'publish'])->whereNumber('id')->name('announcements.publish');
        Route::delete('/announcements-board/{id}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'destroy'])->whereNumber('id')->name('announcements.destroy');

        Route::get('/tasks', [\App\Http\Controllers\Admin\WorkTaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/create', [\App\Http\Controllers\Admin\WorkTaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [\App\Http\Controllers\Admin\WorkTaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{id}', [\App\Http\Controllers\Admin\WorkTaskController::class, 'show'])->whereNumber('id')->name('tasks.show');
        Route::get('/tasks/{id}/replies', [\App\Http\Controllers\Admin\WorkTaskController::class, 'replies'])->whereNumber('id')->name('tasks.replies');
        Route::post('/tasks/{id}/reply', [\App\Http\Controllers\Admin\WorkTaskController::class, 'reply'])->whereNumber('id')->name('tasks.reply');
        Route::post('/tasks/{id}/status', [\App\Http\Controllers\Admin\WorkTaskController::class, 'updateStatus'])->whereNumber('id')->name('tasks.status');
        Route::delete('/tasks/{id}', [\App\Http\Controllers\Admin\WorkTaskController::class, 'destroy'])->whereNumber('id')->name('tasks.destroy');

        Route::get('/live-chat', [\App\Http\Controllers\Admin\LiveChatController::class, 'index'])->name('chat.index');
        Route::get('/live-chat/{employeeId}/messages', [\App\Http\Controllers\Admin\LiveChatController::class, 'messages'])->whereNumber('employeeId')->name('chat.messages');
        Route::get('/live-chat/{employeeId}/stream', [\App\Http\Controllers\Admin\LiveChatController::class, 'stream'])->whereNumber('employeeId')->name('chat.stream');
        Route::post('/live-chat/{employeeId}/send', [\App\Http\Controllers\Admin\LiveChatController::class, 'send'])->whereNumber('employeeId')->name('chat.send');

        Route::get('/employees/search-hr', [CrudController::class, 'searchHrEmployees'])->name('employees.search-hr');
        Route::get('/attendance-board', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/leave-requests', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'index'])->name('leaves.index');
        Route::post('/leave-requests/{id}/decide', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'decide'])->whereNumber('id')->name('leaves.decide');

        Route::get('/payroll-board', [\App\Http\Controllers\Admin\PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll-board/create', [\App\Http\Controllers\Admin\PayrollController::class, 'create'])->name('payroll.create');
        Route::post('/payroll-board', [\App\Http\Controllers\Admin\PayrollController::class, 'store'])->name('payroll.store');
        Route::get('/payroll-board/{id}/edit', [\App\Http\Controllers\Admin\PayrollController::class, 'edit'])->whereNumber('id')->name('payroll.edit');
        Route::put('/payroll-board/{id}', [\App\Http\Controllers\Admin\PayrollController::class, 'update'])->whereNumber('id')->name('payroll.update');
        Route::get('/payroll-board/{id}/receipt', [\App\Http\Controllers\Admin\PayrollController::class, 'receipt'])->whereNumber('id')->name('payroll.receipt');
        Route::post('/payroll-board/{id}/mark-paid', [\App\Http\Controllers\Admin\PayrollController::class, 'markPaid'])->whereNumber('id')->name('payroll.mark-paid');
        Route::delete('/payroll-board/{id}', [\App\Http\Controllers\Admin\PayrollController::class, 'destroy'])->whereNumber('id')->name('payroll.destroy');

        Route::get('/works', [\App\Http\Controllers\Admin\ClientProjectController::class, 'index'])->name('works.index');
        Route::get('/works/create', [\App\Http\Controllers\Admin\ClientProjectController::class, 'create'])->name('works.create');
        Route::post('/works', [\App\Http\Controllers\Admin\ClientProjectController::class, 'store'])->name('works.store');
        Route::get('/works/{id}', [\App\Http\Controllers\Admin\ClientProjectController::class, 'show'])->whereNumber('id')->name('works.show');
        Route::get('/works/{id}/edit', [\App\Http\Controllers\Admin\ClientProjectController::class, 'edit'])->whereNumber('id')->name('works.edit');
        Route::put('/works/{id}', [\App\Http\Controllers\Admin\ClientProjectController::class, 'update'])->whereNumber('id')->name('works.update');
        Route::delete('/works/{id}', [\App\Http\Controllers\Admin\ClientProjectController::class, 'destroy'])->whereNumber('id')->name('works.destroy');
        Route::post('/works/{id}/payment', [\App\Http\Controllers\Admin\ClientProjectController::class, 'recordPayment'])->whereNumber('id')->name('works.payment');
        Route::post('/works/{id}/remind', [\App\Http\Controllers\Admin\ClientProjectController::class, 'remindPending'])->whereNumber('id')->name('works.remind');
        Route::post('/works/{id}/installments', [\App\Http\Controllers\Admin\ClientProjectController::class, 'storeInstallment'])->whereNumber('id')->name('works.installments.store');
        Route::post('/works/{id}/installments/{installmentId}/pay', [\App\Http\Controllers\Admin\ClientProjectController::class, 'payInstallment'])->whereNumber('id')->whereNumber('installmentId')->name('works.installments.pay');
        Route::post('/works/{id}/installments/{installmentId}/remind', [\App\Http\Controllers\Admin\ClientProjectController::class, 'remindInstallment'])->whereNumber('id')->whereNumber('installmentId')->name('works.installments.remind');
        Route::delete('/works/{id}/installments/{installmentId}', [\App\Http\Controllers\Admin\ClientProjectController::class, 'destroyInstallment'])->whereNumber('id')->whereNumber('installmentId')->name('works.installments.destroy');
        Route::post('/works/{id}/receipts', [\App\Http\Controllers\Admin\ClientProjectController::class, 'storeReceipt'])->whereNumber('id')->name('works.receipts.store');
        Route::delete('/works/{id}/receipts/{receiptId}', [\App\Http\Controllers\Admin\ClientProjectController::class, 'destroyReceipt'])->whereNumber('id')->whereNumber('receiptId')->name('works.receipts.destroy');

        Route::patch('/applications/{id}/status', [CrudController::class, 'applicationStatus'])->whereNumber('id')->name('applications.status');
        Route::post('/applications/bulk-status', [CrudController::class, 'applicationBulkStatus'])->name('applications.bulk-status');
        Route::post('/interviews/{id}/notify-hr', [CrudController::class, 'interviewNotifyHr'])->whereNumber('id')->name('interviews.notify-hr');
        Route::post('/interviews/bulk-notify-hr', [CrudController::class, 'interviewBulkNotifyHr'])->name('interviews.bulk-notify-hr');

        Route::get('/roles', [\App\Http\Controllers\Admin\RolePermissionController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [\App\Http\Controllers\Admin\RolePermissionController::class, 'create'])->name('roles.create');
        Route::post('/roles', [\App\Http\Controllers\Admin\RolePermissionController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}/edit', [\App\Http\Controllers\Admin\RolePermissionController::class, 'edit'])->whereNumber('id')->name('roles.edit');
        Route::put('/roles/{id}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'update'])->whereNumber('id')->name('roles.update');
        Route::delete('/roles/{id}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'destroy'])->whereNumber('id')->name('roles.destroy');
        Route::post('/roles/assign', [\App\Http\Controllers\Admin\RolePermissionController::class, 'assign'])->name('roles.assign');
        Route::post('/roles/create-user', [\App\Http\Controllers\Admin\RolePermissionController::class, 'createUser'])->name('roles.create-user');

        Route::get('/{module}', [CrudController::class, 'index'])->name('module.index');
        Route::get('/{module}/create', [CrudController::class, 'create'])->name('module.create');
        Route::post('/{module}', [CrudController::class, 'store'])->name('module.store');
        Route::get('/{module}/{id}', [CrudController::class, 'show'])->whereNumber('id')->name('module.show');
        Route::get('/{module}/{id}/edit', [CrudController::class, 'edit'])->whereNumber('id')->name('module.edit');
        Route::put('/{module}/{id}', [CrudController::class, 'update'])->whereNumber('id')->name('module.update');
        Route::delete('/{module}/{id}', [CrudController::class, 'destroy'])->whereNumber('id')->name('module.destroy');
    });
});
