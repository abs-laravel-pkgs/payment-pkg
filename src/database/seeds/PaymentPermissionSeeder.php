<?php
namespace Abs\PaymentPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class PaymentPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			10100 => [
				'display_order' => 10,
				'parent_id' => null,
				'name' => 'payments',
				'display_name' => 'Payments',
			],
			10101 => [
				'display_order' => 1,
				'parent_id' => 10100,
				'name' => 'view-all-payments',
				'display_name' => 'View All',
			],

		];

		foreach ($permissions as $permission_id => $permsion) {
			$permission = Permission::firstOrNew([
				'id' => $permission_id,
			]);
			$permission->fill($permsion);
			$permission->save();
		}
	}
}