<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_method', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
            $table->index('organization_id');
        });

        if (Schema::hasTable('transaction')) {
            $paymentMethods = DB::table('payment_method')
                ->orderBy('id')
                ->get(['id', 'name', 'created_at', 'updated_at']);

            foreach ($paymentMethods as $paymentMethod) {
                $organizationIds = DB::table('transaction')
                    ->where('payment_method_id', $paymentMethod->id)
                    ->whereNotNull('organization_id')
                    ->distinct()
                    ->orderBy('organization_id')
                    ->pluck('organization_id')
                    ->values();

                if ($organizationIds->isEmpty()) {
                    continue;
                }

                $primaryOrganizationId = (int) $organizationIds->shift();

                DB::table('payment_method')
                    ->where('id', $paymentMethod->id)
                    ->update(['organization_id' => $primaryOrganizationId]);

                foreach ($organizationIds as $organizationId) {
                    $organizationId = (int) $organizationId;

                    $existingTargetId = DB::table('payment_method')
                        ->where('organization_id', $organizationId)
                        ->where('name', $paymentMethod->name)
                        ->value('id');

                    if ($existingTargetId) {
                        $targetId = (int) $existingTargetId;
                    } else {
                        $targetId = (int) DB::table('payment_method')->insertGetId([
                            'organization_id' => $organizationId,
                            'name' => $paymentMethod->name,
                            'created_at' => $paymentMethod->created_at,
                            'updated_at' => $paymentMethod->updated_at,
                        ]);
                    }

                    DB::table('transaction')
                        ->where('payment_method_id', $paymentMethod->id)
                        ->where('organization_id', $organizationId)
                        ->update(['payment_method_id' => $targetId]);
                }
            }

            $duplicates = DB::table('payment_method')
                ->select('organization_id', 'name', DB::raw('COUNT(*) as total'))
                ->whereNotNull('organization_id')
                ->groupBy('organization_id', 'name')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $duplicate) {
                $ids = DB::table('payment_method')
                    ->where('organization_id', $duplicate->organization_id)
                    ->where('name', $duplicate->name)
                    ->orderBy('id')
                    ->pluck('id');

                $keepId = (int) $ids->shift();

                foreach ($ids as $duplicateId) {
                    $duplicateId = (int) $duplicateId;

                    DB::table('transaction')
                        ->where('payment_method_id', $duplicateId)
                        ->update(['payment_method_id' => $keepId]);

                    DB::table('payment_method')
                        ->where('id', $duplicateId)
                        ->delete();
                }
            }
        }

        Schema::table('payment_method', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organization');
            $table->unique(['organization_id', 'name'], 'payment_method_organization_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_method', function (Blueprint $table) {
            $table->dropUnique('payment_method_organization_name_unique');
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
