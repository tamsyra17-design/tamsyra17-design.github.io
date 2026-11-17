<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional inspectable 3D model (meshopt-compressed GLB) and its poster.
     * Each holds either a public-disk path from the admin upload or an
     * absolute URL.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('model_3d_url', 2048)->nullable()->after('is_rentable');
            $table->string('model_3d_poster', 2048)->nullable()->after('model_3d_url');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['model_3d_url', 'model_3d_poster']);
        });
    }
};
