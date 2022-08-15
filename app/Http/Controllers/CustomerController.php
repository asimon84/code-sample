<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCustomers;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use League\Flysystem\Exception;

class CustomerController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        try {
            $customers = Customer::all();

            // Clear redis just for testing and demonstration purposes only
            // Also just wanted to demonstrate redis pipeline and collection filter
            Redis::pipeline(function ($pipe) use ($customers) {
                for ($i = 1; $i <= $customers->count(); $i++) {
                    $customer = $customers->filter(function ($item) use ($i) {
                        return $item->id == $i;
                    })->first();

                    $pipe->del("customer:$i", $customer);
                }
            });

            //Process redis again from scratch
            ProcessCustomers::dispatch()->onQueue('default');
        } catch (Exception $e) {
            $customers = [];
        } finally {
            return view('customers', ['customers' => $customers]);
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function show(Request $request, int $id)
    {
        return Redis::get('customer:' . $id);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'required',
        ], [
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone Number is required.',
        ]);

        $customer = Customer::create($data);

        Redis::set('customer:' . $customer->id, $customer);

        return back()->with('success', 'Customer created successfully!');
    }

    /**
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'required',
        ], [
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone Number is required.',
        ]);

        $customer = Customer::where('id', $id)
            ->update([
                'name' => $data['name'],
                'phone' => $data['phone']
            ]);

        Redis::set('customer:' . $customer->id, $customer);
    }

    /**
     * @param Request $request
     * @param int $id
     */
    public function delete(Request $request, int $id)
    {
        Customer::find($id)->delete();
        Redis::del('customer:' . $id);
    }
}
