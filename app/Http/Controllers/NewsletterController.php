<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'name'  => 'nullable|string|max:100',
        ]);

        $subscriber = NewsletterSubscriber::subscribe($request->email, $request->name);

        $message = $subscriber->wasRecentlyCreated
            ? 'You\'re subscribed! Thank you for joining CryptoInfo.'
            : 'You\'re already subscribed. We\'ll keep you updated!';

        return back()->with('newsletter_status', $message);
    }
}
