<?php

// database/migrations/YYYY_MM_DD_HHMMSS_add_two_factor_columns_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwoFactorColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            // Si tenías dark_mode, puedes renombrar o reutilizarla si no la necesitas para otra cosa
            // $table->dropColumn('dark_mode'); // Si quieres eliminarla
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_enabled']);
            // $table->boolean('dark_mode')->default(false); // Si la eliminaste y quieres restaurarla
        });
    }
}
