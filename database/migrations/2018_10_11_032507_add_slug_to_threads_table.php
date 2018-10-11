<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Thread;

class AddSlugToThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->string('slug')->after('id');
        });

        Thread::chunk(100, function($threads) {
            foreach ($threads as $thread) {
                $thread->slug = Thread::generateUniqueSlug($thread->title);
                $thread->save();
            }
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
