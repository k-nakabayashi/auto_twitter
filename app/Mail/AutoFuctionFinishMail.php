<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class AutoFuctionFinishMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $tw_account;
    public $auto_function_name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $tw_account, $auto_function_name)
    {
        $this->auto_function_name = $auto_function_name;
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
        $auto_function_name = "";
        $pre = "自動";
        $suff = "が完了しました。";
        switch ($this->auto_function_name) {
            case 'follow':
                $auto_function_name = 'フォロー';
                break;
            case 'unfollow':
                $auto_function_name = 'アンフォロー';
                break;
            case 'favorite':
                $auto_function_name = 'いいね';
                break;
            case 'tweet':
                $auto_function_name = 'ツイート';
                $pre = "ご予約のツイート";
                $suff = "を投稿しました。";
                break;            
        }
        
        $text = $pre.$auto_function_name.$suff;
        
        return $this
            ->markdown('function_mails.finish')
            ->subject("オートついったー：".$text)
            ->with([
                'auto_function_name' =>  $auto_function_name,
                'name' => $this->user->name,
                'account_name' => $this->tw_account->name."@".$this->tw_account->screen_name,
                'text' => $text,
                ]
            );
    }
}
