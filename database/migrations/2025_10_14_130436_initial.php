<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->index(); // superadmin, moderator, regular
            $table->integer('state')->nullable()->index();
            $table->timestamps();
        });

        // 2. Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable()->index();
            $table->string('avatar')->nullable();
            $table->integer('state')->nullable()->index();
            $table->timestamps();
        });

        // 3. Games
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('state')->nullable()->index();
            $table->timestamps();
        });

        // 4. Challenges
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id')->nullable()->index();
            $table->integer('created_by')->nullable()->index();
            $table->string('title');
            $table->text('description');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->integer('reward_points')->default(0);
            $table->integer('state')->nullable()->index();
            $table->timestamps();
        });

        // 5. Challenge Participants
        Schema::create('challenge_participants', function (Blueprint $table) {
            $table->id();
            $table->integer('challenge_id')->nullable()->index();
            $table->integer('user_id')->nullable()->index();
            $table->integer('total_points')->default(0); // dari vote dll
            $table->integer('state')->nullable()->index();
            $table->timestamps();
        });

        // 5. Challenge Participants
        Schema::create('challenge_participant_posts', function (Blueprint $table) {
            $table->id();
            $table->integer('challenge_participant_id')->nullable()->index();
            $table->string('captions', 250)->nullable();
            $table->integer('state')->nullable()->index();
            $table->timestamps();
        });

        // 6. Challenge Media (foto/video bukti)
        Schema::create('challenge_media', function (Blueprint $table) {
            $table->id();
            $table->integer('challenge_participant_id')->nullable()->index();
            $table->enum('type', ['image', 'video']);
            $table->string('file_path')->nullable();
            $table->enum('state', [0, 1])->nullable()->index();
            $table->timestamps();
        });

        // 7. Votes (upvote/downvote peserta)
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->integer('challenge_participant_id')->nullable()->index();
            $table->integer('user_id')->nullable()->index();
            $table->enum('type', ['up', 'down']);
            $table->enum('state', [0, 1])->default(1)->index();
            $table->timestamps();
        });

        // 8. Comments (komentar pada peserta)
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->integer('challenge_participant_id')->nullable()->index();
            $table->integer('user_id')->nullable()->index();
            $table->text('content')->nullable();
            $table->timestamps();
        });

        // 9. Bookmarks
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->index();
            $table->integer('challenge_id')->nullable()->index();
            $table->timestamps();
        });

        // 10. Points (log aktivitas poin user)
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->index();
            $table->integer('amount')->default(1);
            $table->string('source'); // e.g. 'win_challenge', 'upvote', 'giveaway'
            $table->timestamps();
        });

        // 11. Rewards (hadiah penukaran poin)
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('required_points');
            $table->string('type')->default('voucher'); // voucher, discount, item
            $table->string('code')->nullable(); // kode voucher
            $table->timestamps();
        });

        // 12. Redemptions (penukaran poin user)
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->index();
            $table->integer('reward_id')->nullable()->index();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });

        // 13. Giveaways
        Schema::create('giveaways', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->timestamps();
        });

        // 14. Giveaway Entries (peserta giveaway)
        Schema::create('giveaway_entries', function (Blueprint $table) {
            $table->id();
            $table->integer('giveaway_id')->nullable()->index();
            $table->integer('user_id')->nullable()->index();
            $table->integer('chance_weight')->default(1); // makin besar = makin sering muncul
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giveaway_entries');
        Schema::dropIfExists('giveaways');
        Schema::dropIfExists('redemptions');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('points');
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('challenge_media');
        Schema::dropIfExists('challenge_participants');
        Schema::dropIfExists('challenge_participant_posts');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('games');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
