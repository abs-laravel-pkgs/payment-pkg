<?php

namespace Abs\PaymentPkg;
use Abs\PaymentPkg\Payment;
use App\Address;
use App\Country;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class PaymentController extends Controller {

	public function __construct() {
	}

	public function getPaymentList(Request $request) {
		$Payment_list = Payment::withTrashed()
			->select(
				'Payments.id',
				'Payments.code',
				'Payments.name',
				DB::raw('IF(Payments.mobile_no IS NULL,"--",Payments.mobile_no) as mobile_no'),
				DB::raw('IF(Payments.email IS NULL,"--",Payments.email) as email'),
				DB::raw('IF(Payments.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->where('Payments.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->Payment_code)) {
					$query->where('Payments.code', 'LIKE', '%' . $request->Payment_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->Payment_name)) {
					$query->where('Payments.name', 'LIKE', '%' . $request->Payment_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->mobile_no)) {
					$query->where('Payments.mobile_no', 'LIKE', '%' . $request->mobile_no . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->email)) {
					$query->where('Payments.email', 'LIKE', '%' . $request->email . '%');
				}
			})
			->orderby('Payments.id', 'desc');

		return Datatables::of($Payment_list)
			->addColumn('code', function ($Payment_list) {
				$status = $Payment_list->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $Payment_list->code;
			})
			->addColumn('action', function ($Payment_list) {
				$edit_img = asset('public/theme/img/table/cndn/edit.svg');
				$delete_img = asset('public/theme/img/table/cndn/delete.svg');
				return '
					<a href="#!/Payment-pkg/Payment/edit/' . $Payment_list->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_Payment"
					onclick="angular.element(this).scope().deletePayment(' . $Payment_list->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->make(true);
	}

	public function getPaymentFormData($id = NULL) {
		if (!$id) {
			$Payment = new Payment;
			$address = new Address;
			$action = 'Add';
		} else {
			$Payment = Payment::withTrashed()->find($id);
			$address = Address::where('address_of_id', 24)->where('entity_id', $id)->first();
			$action = 'Edit';
		}
		$this->data['country_list'] = $country_list = Collect(Country::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Country']);
		$this->data['Payment'] = $Payment;
		$this->data['address'] = $address;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function savePayment(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Payment Code is Required',
				'code.max' => 'Maximum 255 Characters',
				'code.min' => 'Minimum 3 Characters',
				'name.required' => 'Payment Name is Required',
				'name.max' => 'Maximum 255 Characters',
				'name.min' => 'Minimum 3 Characters',
				'gst_number.required' => 'GST Number is Required',
				'gst_number.max' => 'Maximum 191 Numbers',
				'mobile_no.max' => 'Maximum 25 Numbers',
				// 'email.required' => 'Email is Required',
				'address_line1.required' => 'Address Line 1 is Required',
				'address_line1.max' => 'Maximum 255 Characters',
				'address_line1.min' => 'Minimum 3 Characters',
				'address_line2.max' => 'Maximum 255 Characters',
				'pincode.required' => 'Pincode is Required',
				'pincode.max' => 'Maximum 6 Characters',
				'pincode.min' => 'Minimum 6 Characters',
			];
			$validator = Validator::make($request->all(), [
				'code' => 'required|max:255|min:3',
				'name' => 'required|max:255|min:3',
				'gst_number' => 'required|max:191',
				'mobile_no' => 'nullable|max:25',
				// 'email' => 'nullable',
				'address_line1' => 'required|max:255|min:3',
				'address_line2' => 'max:255',
				'pincode' => 'required|max:6|min:6',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$Payment = new Payment;
				$Payment->created_by_id = Auth::user()->id;
				$Payment->created_at = Carbon::now();
				$Payment->updated_at = NULL;
				$address = new Address;
			} else {
				$Payment = Payment::withTrashed()->find($request->id);
				$Payment->updated_by_id = Auth::user()->id;
				$Payment->updated_at = Carbon::now();
				$address = Address::where('address_of_id', 24)->where('entity_id', $request->id)->first();
			}
			$Payment->fill($request->all());
			$Payment->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$Payment->deleted_at = Carbon::now();
				$Payment->deleted_by_id = Auth::user()->id;
			} else {
				$Payment->deleted_by_id = NULL;
				$Payment->deleted_at = NULL;
			}
			$Payment->gst_number = $request->gst_number;
			$Payment->save();

			if (!$address) {
				$address = new Address;

			}
			$address->fill($request->all());
			$address->company_id = Auth::user()->company_id;
			$address->address_of_id = 24;
			$address->entity_id = $Payment->id;
			$address->address_type_id = 40;
			$address->name = 'Primary Address';
			$address->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Payment Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['Payment Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deletePayment($id) {
		$delete_status = Payment::withTrashed()->where('id', $id)->forceDelete();
		if ($delete_status) {
			$address_delete = Address::where('address_of_id', 24)->where('entity_id', $id)->forceDelete();
			return response()->json(['success' => true]);
		}
	}
}
