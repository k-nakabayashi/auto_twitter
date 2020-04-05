<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AutoFuctionFreezeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $tw_account;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $tw_account)
    {
        $this->user = $user;
        $this->tw_account = $tw_account;
    }

    /**l
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        return $this
            ->markdown('function_mails.freeze')
            ->subject("オートついったー：アカウントが凍結されました。")
            ->with([
                'name' => $this->user->name,
                'account_name' => $this->tw_account->name."@".$this->tw_account->screen_name,
                ]
            );
    }
}
