<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class VerifyEmailNotification extends Notification
{
  use Queueable;

  /**
   * Get the notification's delivery channels.
   *
   * @param mixed $notifiable
   * @return array
   */
  public function via($notifiable)
  {
    return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param mixed $notifiable
   * @return MailMessage
   */
  public function toMail($notifiable)
  {
    return (new MailMessage)
      ->subject(trans('notifications.email.verify.subject'))
      ->markdown('notifications.email.verify', [
        'user' => $notifiable,
        'link' => $this->generateVerificationUrl($notifiable)
      ]);
  }

  /**
   * Get the array representation of the notification.
   *
   * @param mixed $notifiable
   * @return array
   */
  public function toArray($notifiable)
  {
    return [
      'user' => $notifiable,
      'url' => $this->generateVerificationUrl($notifiable)
    ];
  }

  private final function generateVerificationUrl($user)
  {
    return url()->temporarySignedRoute('user.verify.email',
      now()->addHours(config('auth.verification.expires')), [
        'email' => $user->getEmailForVerification()
      ]);
  }
}
