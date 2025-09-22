<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationIdAndSuperFlagToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('id');
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'is_super_doctor')) {
                $table->boolean('is_super_doctor')->default(false)->after('location_id');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_super_doctor')) {
                $table->dropColumn('is_super_doctor');
            }
            if (Schema::hasColumn('users', 'location_id')) {
                $table->dropForeign(['location_id']);
                $table->dropColumn('location_id');
            }
        });
    }
}
