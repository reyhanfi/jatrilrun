<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserBaruNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private readonly array $account;

    public function __construct(User $user)
    {
        $this->account = [
            'name' => $user->name,
            'email' => $user->email,
            'message' => 'Akun baru mendaftar melalui Google.',
        ];

        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun baru Jatrilrun melalui Google')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->account['message'])
            ->line('Nama: '.$this->account['name'])
            ->line('Email: '.$this->account['email'])
            ->action('Buka Dashboard', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->account;
    }
}
