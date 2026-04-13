<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Comment;
use App\Models\Anime;
use Spatie\Permission\Models\Role;

echo "Comment System Verification\n";
echo str_repeat('=', 50) . "\n\n";

// Check CUSTOMER role
$customerRole = Role::where('name', 'CUSTOMER')->first();
if ($customerRole) {
    echo "✅ CUSTOMER role exists\n";
    $customerCount = User::role('CUSTOMER')->count();
    echo "   → {$customerCount} users have CUSTOMER role\n\n";
} else {
    echo "❌ CUSTOMER role not found\n\n";
}

// List customer users
echo "Customer Users:\n";
echo str_repeat('-', 50) . "\n";
$customers = User::role('CUSTOMER')->get();
foreach ($customers as $customer) {
    $commentCount = Comment::where('user_id', $customer->id)->count();
    echo "  {$customer->name} ({$customer->email})\n";
    echo "    → {$commentCount} comments\n";
    echo "    → Can access panel: " . ($customer->canAccessPanel(app(\Filament\Panel::class)) ? 'Yes' : 'No') . "\n";
}

echo "\n";

// Comment statistics
echo "Comment Statistics:\n";
echo str_repeat('-', 50) . "\n";
$totalComments = Comment::count();
$animeComments = Comment::whereNull('episode_id')->whereNull('parent_id')->count();
$episodeComments = Comment::whereNotNull('episode_id')->whereNull('parent_id')->count();
$replies = Comment::whereNotNull('parent_id')->count();

echo "  Total comments: {$totalComments}\n";
echo "  Anime comments: {$animeComments}\n";
echo "  Episode comments: {$episodeComments}\n";
echo "  Replies: {$replies}\n\n";

// Sample comments
echo "Sample Comments:\n";
echo str_repeat('-', 50) . "\n";
$sampleComments = Comment::with(['user', 'anime', 'episode'])
    ->whereNull('parent_id')
    ->take(5)
    ->get();

foreach ($sampleComments as $comment) {
    echo "  User: {$comment->user->name}\n";
    if ($comment->anime) {
        echo "  Anime: {$comment->anime->title}\n";
    }
    if ($comment->episode) {
        echo "  Episode: {$comment->episode->episode_number}\n";
    }
    echo "  Comment: " . substr($comment->comment, 0, 60) . "...\n";
    echo "  Likes: {$comment->likes_count}\n";
    echo "  Replies: " . $comment->replies->count() . "\n";
    echo "\n";
}

echo "✅ Comment system verification complete!\n";
