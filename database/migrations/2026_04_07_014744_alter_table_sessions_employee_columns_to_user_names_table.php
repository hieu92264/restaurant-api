<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function usingSqlite(): bool
    {
        return DB::getDriverName() === 'sqlite';
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->usingSqlite()) {
            $this->upSqlite();

            return;
        }

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->string('opened_by_employee_user_name')->nullable()->after('table_id');
            $table->string('closed_by_employee_user_name')->nullable()->after('opened_by_employee_user_name');
        });

        DB::statement('
            UPDATE table_sessions
            SET
                opened_by_employee_user_name = (
                    SELECT user_name
                    FROM users
                    WHERE users.id = table_sessions.opened_by_employee_id
                ),
                closed_by_employee_user_name = (
                    SELECT user_name
                    FROM users
                    WHERE users.id = table_sessions.closed_by_employee_id
                )
        ');

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropForeign(['opened_by_employee_id']);
            $table->dropForeign(['closed_by_employee_id']);
            $table->dropColumn(['opened_by_employee_id', 'closed_by_employee_id']);
        });

        DB::statement('
            ALTER TABLE table_sessions
            CHANGE opened_by_employee_user_name opened_by_employee VARCHAR(255) NOT NULL,
            CHANGE closed_by_employee_user_name closed_by_employee VARCHAR(255) NULL
        ');

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->foreign('opened_by_employee')
                ->references('user_name')
                ->on('users');
            $table->foreign('closed_by_employee')
                ->references('user_name')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->usingSqlite()) {
            $this->downSqlite();

            return;
        }

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('opened_by_employee_user_id')->nullable()->after('table_id');
            $table->unsignedBigInteger('closed_by_employee_user_id')->nullable()->after('opened_by_employee_user_id');
        });

        DB::statement('
            UPDATE table_sessions
            SET
                opened_by_employee_user_id = (
                    SELECT id
                    FROM users
                    WHERE users.user_name = table_sessions.opened_by_employee
                ),
                closed_by_employee_user_id = (
                    SELECT id
                    FROM users
                    WHERE users.user_name = table_sessions.closed_by_employee
                )
        ');

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropForeign(['opened_by_employee']);
            $table->dropForeign(['closed_by_employee']);
            $table->dropColumn(['opened_by_employee', 'closed_by_employee']);
        });

        DB::statement('
            ALTER TABLE table_sessions
            CHANGE opened_by_employee_user_id opened_by_employee_id BIGINT UNSIGNED NOT NULL,
            CHANGE closed_by_employee_user_id closed_by_employee_id BIGINT UNSIGNED NULL
        ');

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->foreign('opened_by_employee_id')
                ->references('id')
                ->on('users');
            $table->foreign('closed_by_employee_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    private function upSqlite(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->string('opened_by_employee_user_name')->nullable()->after('table_id');
            $table->string('closed_by_employee_user_name')->nullable()->after('opened_by_employee_user_name');
        });

        DB::statement('
            UPDATE table_sessions
            SET
                opened_by_employee_user_name = (
                    SELECT user_name
                    FROM users
                    WHERE users.id = table_sessions.opened_by_employee_id
                ),
                closed_by_employee_user_name = (
                    SELECT user_name
                    FROM users
                    WHERE users.id = table_sessions.closed_by_employee_id
                )
        ');

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropForeign(['opened_by_employee_id']);
            $table->dropForeign(['closed_by_employee_id']);
            $table->dropColumn(['opened_by_employee_id', 'closed_by_employee_id']);
        });

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->renameColumn('opened_by_employee_user_name', 'opened_by_employee');
            $table->renameColumn('closed_by_employee_user_name', 'closed_by_employee');
        });

        Schema::enableForeignKeyConstraints();
    }

    private function downSqlite(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('opened_by_employee_user_id')->nullable()->after('table_id');
            $table->unsignedBigInteger('closed_by_employee_user_id')->nullable()->after('opened_by_employee_user_id');
        });

        DB::statement('
            UPDATE table_sessions
            SET
                opened_by_employee_user_id = (
                    SELECT id
                    FROM users
                    WHERE users.user_name = table_sessions.opened_by_employee
                ),
                closed_by_employee_user_id = (
                    SELECT id
                    FROM users
                    WHERE users.user_name = table_sessions.closed_by_employee
                )
        ');

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropForeign(['opened_by_employee']);
            $table->dropForeign(['closed_by_employee']);
            $table->dropColumn(['opened_by_employee', 'closed_by_employee']);
        });

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->renameColumn('opened_by_employee_user_id', 'opened_by_employee_id');
            $table->renameColumn('closed_by_employee_user_id', 'closed_by_employee_id');
        });

        Schema::enableForeignKeyConstraints();
    }
};
