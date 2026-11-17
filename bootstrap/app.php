<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware phân quyền của nhóm (xem docs/CONVENTIONS.md)
        $middleware->alias([
            'role'          => \App\Http\Middleware\RoleMiddleware::class,
            'shop.approved' => \App\Http\Middleware\EnsureShopApproved::class,
        ]);

        // Người đã đăng nhập mà vào trang login/register thì đưa về trang chính theo vai trò
        $middleware->redirectUsersTo(fn (Request $request) => route($request->user()->homeRoute()));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
