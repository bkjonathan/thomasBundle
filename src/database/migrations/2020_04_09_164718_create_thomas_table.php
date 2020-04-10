<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThomasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('languageable');
            $table->string('type_for')->default('name');
            $table->text('myanmar')->nullable();
            $table->text('chinese')->nullable();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('socials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('socialable');
            $table->text('google')->nullable();
            $table->text('facebook')->nullable();
            $table->text('twitter')->nullable();
            $table->text('codepen')->nullable();
            $table->text('instagram')->nullable();
            $table->timestamps();
        });



        Schema::create('images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('imageable');
            $table->text('url');
            $table->text('type');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('socials');
        Schema::dropIfExists('images');
    }
}
