<?php namespace Graker\Photoalbums\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateAlbumsTable extends Migration
{

    public function up()
    {
        Schema::create('graker_photoalbums_albums', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('graker_photoalbums_albums');
    }

}
