<?php

namespace App\Providers;

use App\Comment;
use App\Observers\CommentObserver;
use App\Observers\PostObserver;
use App\Observers\RoleObserver;
use App\Observers\UserObserver;
use App\Payment\Contracts\PaymentHandlerContract;
use App\Payment\Handlers\BankSlipHandler as BankSlipPaymentHandler;
use App\Payment\Handlers\MercadoPagoHandler as MercadoPagoPaymentHandler;
use App\Payment\Handlers\PaypalHandler as PaypalPaymentHandler;
use App\Payment\PaymentService;
use App\Post;
use App\Role;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public final function register()
  {
    //
  }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public final function boot()
  {
    Log::info("Bootstrapped application.");

    $this->app->singleton(PaymentService::class);

    // Singleton payment handlers
    $this->app->singleton(BankSlipPaymentHandler::class);
    $this->app->singleton(MercadoPagoPaymentHandler::class, function () {
      /** @var PaymentHandlerContract $mercadoPagoPaymentHandler */
      $mercadoPagoPaymentHandler = $this->app->make(MercadoPagoPaymentHandler::class);
      $mercadoPagoPaymentHandler->setupCredentials();
      return $mercadoPagoPaymentHandler;
    });
    $this->app->singleton(PaypalPaymentHandler::class);

    JsonResource::withoutWrapping();

    User::observe(UserObserver::class);
    Post::observe(PostObserver::class);
    Role::observe(RoleObserver::class);
    Comment::observe(CommentObserver::class);
  }
}
