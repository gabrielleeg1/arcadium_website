<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

trait CreatesApplication
{
  /**
   * Creates the application.
   *
   * @return Application
   */
  public function createApplication()
  {
    $app = require __DIR__ . '/../bootstrap/app.php';

    $app->make(Kernel::class)->bootstrap();

    Storage::fake();
    Notification::fake();

    return $app;
  }
}
