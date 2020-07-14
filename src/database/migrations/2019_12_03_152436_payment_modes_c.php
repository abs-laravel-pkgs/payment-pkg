<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PaymentModesC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('payment_modes')) {
			Schema::create('payment_modes', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('company_id');
				$table->string('code', 191);
				$table->string('name', 191);
				$table->string('description', 255)->nullable();
				$table->boolean('has_payment_gateway');
				$table->unsignedInteger('display_order')->default(9999);
				$table->unsignedInteger('created_by_id')->nullable();
				$table->unsignedInteger('updated_by_id')->nullable();
				$table->unsignedInteger('deleted_by_id')->nullable();
				$table->timestamps();
				$table->softDeletes();

				$table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE')->onUpdate('cascade');

				$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

				$table->unique(["company_id", "code"]);
				$table->unique(["company_id", "name"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('payment_modes');
	}
}
