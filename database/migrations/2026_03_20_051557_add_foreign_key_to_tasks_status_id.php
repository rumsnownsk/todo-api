<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Устанавливаем внешний ключ
            $table->foreign('status_id')
                ->references('id')          // ссылается на поле id
                ->on('statuses')         // в таблице statuses
                ->onDelete('set null')   // при удалении статуса — ставим NULL
                ->onUpdate('cascade');  // при обновлении id статуса — каскадно обновляем
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks_status_id', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
        });
    }
};
