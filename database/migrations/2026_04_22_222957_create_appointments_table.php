<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
		Schema::create('appointments', function (Blueprint $table) {
			$table->id();
			$table->foreignId('patient_id')->constrained()->onDelete('cascade');
			$table->foreignId('vet_id')->nullable()->constrained('users'); 
			$table->date('date');
			$table->time('time');
			$table->text('observations')->nullable();
			$table->boolean('is_finished')->default(false);
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
        Schema::dropIfExists('appointments');
    }
}
