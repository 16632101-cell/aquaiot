<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // 💡 เพิ่มบรรทัดนี้ด้วย เพื่อให้ระบบเรียกใช้งานไฟล์ api.php 
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // 💡 เพิ่มคำสั่งนี้ เพื่อยกเว้นการตรวจสอบ CSRF สำหรับฝั่ง Arduino
        $middleware->validateCsrfTokens(except: [
            'api/*', 
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
