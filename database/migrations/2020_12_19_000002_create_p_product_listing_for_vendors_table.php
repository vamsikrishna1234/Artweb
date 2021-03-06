<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePProductListingForVendorsTable extends Migration
{
    public function up()
    {
        Schema::create('p_product_listing_for_vendors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('expire_on');
            $table->date('start_from');
            $table->string('active')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
