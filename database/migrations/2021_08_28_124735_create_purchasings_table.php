<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->datetime('date')->nullable();
            $table->string('price')->nullable();
            $table->string('discount')->nullable();
            $table->string('code')->unique();
            $table->mediumText('image')->nullable();
            $table->tinyInteger('discountType')->nullable()->comment('0 percent 1 value');
            $table->bigInteger('discountValue')->nullable();
            $table->string('status')->nullable()->comment('paid = bayar, dept = hutang');
            $table->integer('done')->default(0)->comment('0 baru, 1 proses, 2 selesai, 3 disetujui');
            $table->mediumText('description')->nullable();

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->softDeletesTz($column = 'deleted_at', $precision = 0);
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
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
        Schema::dropIfExists('purchasings');
    }
}
