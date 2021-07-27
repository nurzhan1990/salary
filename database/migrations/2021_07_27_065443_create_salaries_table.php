<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->integer('salary');
            $table->integer('work_days_month');
            $table->integer('worked_days_month');
            $table->boolean('deduction_mzp');
            $table->integer('year');
            $table->integer('month');
            $table->boolean('pensioner');
            $table->boolean('disabled_person');
            $table->integer('disabled_person_category')->nullable();
            $table->integer('salary_sum');
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
        Schema::dropIfExists('salaries');
    }
}
