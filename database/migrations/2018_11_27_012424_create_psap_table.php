<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePsapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psap', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->unsignedInteger('psap_id')
                ->nullable()
                ->unique();
            $table->string('state', 2)
                ->index();
            $table->string('name');
            $table->string('county')
                ->index();
            $table->string('city')
                ->index();
            $table->longText('address');
            $table->unsignedInteger('zip')
                ->nullable()
                ->index();
            $table->string('admin_authority')
                ->nullable();
            $table->string('admin_name')
                ->nullable();
            $table->string('admin_email')
                ->nullable();
            $table->string('admin_phone')
                ->nullable();
            $table->string('admin_title')
                ->nullable();
            $table->dateTime('compliant_at')
                ->nullable();
            $table->dateTime('ready_at')
                ->nullable();
            $table->boolean('supports_tty')
                ->default(false);
            $table->boolean('supports_web')
                ->default(false);
            $table->boolean('supports_ip')
                ->default(false);
            $table->json('meta')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('psap');
    }
}
