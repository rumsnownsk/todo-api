<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->integer('created_at')->unsigned();
            $table->integer('updated_at')->unsigned();
        });
        // Заполняем начальные данные
        DB::table('statuses')->insert([
            ['name' => 'Pending', 'slug' => 'pending', 'created_at' => time(), 'updated_at' => time()],
            ['name' => 'In Progress', 'slug' => 'in_progress', 'created_at' => time(), 'updated_at' => time()],
            ['name' => 'Completed', 'slug' => 'completed', 'created_at' => time(), 'updated_at' => time()]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
