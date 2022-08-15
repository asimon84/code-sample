<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCustomers;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            Log::error($e->getMessage());
            $customers = $customers ?? [];
        } finally {
            return view('customers', ['customers' => $customers]);
        }
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @return string
     */
    public function show(Request $request, int $id): string
    {
        try {
            $data = Redis::get('customer:' . $id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $data = $data ?? '';
        } finally {
            return $data;
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function create(Request $request): RedirectResponse
    {
        try {
            $status = 'success';
            $message = 'Customer created successfully!';

            $data = $request->validate([
                'name' => 'required',
                'phone' => 'required',
            ], [
                'name.required' => 'Name is required.',
                'phone.required' => 'Phone Number is required.',
            ]);

            $customer = Customer::create($data);

            Redis::set('customer:' . $customer->id, $customer);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $status = 'error';
            $message = 'Customer could not be created.';
        } finally {
            return back()->with($status, $message);
        }
    }

    /**
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {
        try {
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
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param int $id
     */
    public function delete(Request $request, int $id)
    {
        try {
            Customer::find($id)->delete();
            Redis::del('customer:' . $id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
