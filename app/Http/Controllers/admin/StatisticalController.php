<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paid;
use App\Models\Product_Group;
use App\Models\Products;
use App\Models\Purchase;
use App\Models\Purchase_item;
use App\Models\Sales;
use App\Models\Sales_item;
use App\Models\Staff;
use App\Models\Customer;
use App\Models\Target;
use App\Models\TargetPurchase;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class StatisticalController extends Controller
{
    public function salerSalary(Request $request)
    {
        $staffs = Staff::select('id', 'fullname')->get();
        $productGroups = Product_Group::select('id', 'group_name')->get();
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $product_group_id = $request->product_group_id;
        $staff_id = $request->staff_id;
        $data = Sales_item::join("sales", "sales_items.sales_id", "sales.id")
            ->join("products", "sales_items.product_id", "products.id")
            ->join("product_groups", "products.group", "product_groups.id")
            ->join("users", "sales.user_id", "users.id")
            ->join("staff", "sales.staff_id", "staff.id")
            ->join("customers", "sales.customer_id", "customers.id")
            ->where('sales.trash', 0)
            ->when($from_date, function (Builder $query, $from_date) {
                $formatted_date = Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d');
                $query->whereDate('sales.date', '>=', $formatted_date);
            })
            ->when($to_date, function (Builder $query, $to_date) {
                $formatted_date = Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d');
                $query->whereDate('sales.date', '<=', $formatted_date);
            })
            ->when($product_group_id, function (Builder $query, int $product_group_id) {
                $query->where('product_groups.id', $product_group_id);
            })
            ->when($staff_id, function (Builder $query, int $staff_id) {
                $query->where('staff.id', $staff_id);
            })
            ->select(
                DB::raw("DISTINCT DATE_FORMAT(sales.date,'%d-%m-%Y') as sale_date"),
                "staff.fullname as staff",
                "customers.fullname as customer",
                "products.name as product",
                "product_groups.commission as commission",
                "product_groups.commission_type as commission_type",
                "sales_items.quality as quantity",
                "sales_items.price as price",
            )
            ->get();
        $totalSalary = 0;
        foreach ($data as $da) {
            $da->allPrice = $da->quantity * $da->price;
            if ($da->commission_type > 0) {
                $temp = $da->quantity / $da->commission_type;
                $da->salary = $da->commission * $temp;
                $da->bonus = $da->commission . '/' . $da->commission_type;
            } else {
                $da->salary = $da->allPrice * $da->commission * 0.01;
                $da->bonus = $da->commission;
            }

            $totalSalary += $da->salary;
        }
        $modifiedData = $data->map(function ($item) {
            return collect($item)->forget('commission')->forget('commission_type')->all();
        });
        $response = [
            "staffs" => $staffs,
            "productGroups" => $productGroups,
            "data" => $modifiedData,
            "totalSalary" => $totalSalary,
        ];
        return response()->json($response, 200);
    }
    public function discountReport(Request $request)
    {
        $staffs = Staff::select('id', 'fullname')->get();
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $staff_id = $request->staff_id;
        $data = Sales::join("staff", "sales.staff_id", "staff.id")
            ->join("customers", "sales.customer_id", "customers.id")
            ->join("sales_items", "sales_items.sales_id", "sales.id")
            ->join("products", "products.id", "sales_items.product_id")
            ->join("discounts", "products.id", "discounts.product_id")
            ->where('sales.trash', 0)
            ->when($from_date, function (Builder $query, $from_date) {
                $formatted_date = Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d');
                $query->whereDate('sales.date', '>=', $formatted_date);
            })
            ->when($to_date, function (Builder $query, $to_date) {
                $formatted_date = Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d');
                $query->whereDate('sales.date', '<=', $formatted_date);
            })
            ->when($staff_id, function (Builder $query, int $staff_id) {
                $query->where('staff.id', $staff_id);
            })
            ->select(
                DB::raw("DISTINCT DATE_FORMAT(sales.date,'%d-%m-%Y') as sale_date,
                DATE_FORMAT(discounts.from_date,'%d-%m-%Y') as from_date,
                DATE_FORMAT(discounts.to_date,'%d-%m-%Y') as to_date
                "),
                "staff.fullname as staff",
                "customers.fullname as customer",
                "products.code as product_code",
                "products.buy_price as buy_price",
                "sales_items.quality as quantity",
                "sales_items.get_more as bonus",
                "discounts.discount as cpn_discount",
                "sales_items.discount as real_discount",
            )
            ->get();
        $totalDisparity = 0;
        foreach ($data as $da) {
            if (strtotime($da->sale_date) <= strtotime($da->from_date) || strtotime($da->sale_date) >= strtotime($da->to_date)) {
                $da->cpn_discount = 0;
            }
            $da->cpn_discount_price = $da->buy_price * $da->quantity - $da->buy_price * $da->quantity * $da->cpn_discount * 0.01;
            $da->real_discount_price = $da->buy_price * $da->quantity - $da->buy_price * $da->quantity * $da->real_discount * 0.01;
            $da->disparity = ($da->buy_price * $da->quantity - $da->buy_price * $da->quantity * $da->real_discount * 0.01) - ($da->buy_price * $da->quantity - $da->buy_price * $da->quantity * $da->cpn_discount * 0.01);
            $totalDisparity += ($da->buy_price * $da->quantity - $da->buy_price * $da->quantity * $da->real_discount * 0.01) - ($da->buy_price * $da->quantity - $da->buy_price * $da->quantity * $da->cpn_discount * 0.01);
        }
        $response = [
            "staffs" => $staffs,
            "data" => $data,
            "totalDisparity" => number_format($totalDisparity, 0),
        ];
        return response()->json($response, 200);
    }
    public function realSales(Request $request)
    {
        $staffs = Staff::select('id', 'fullname')->get();
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $product_group_id = $request->product_group_id;
        $staff_id = $request->staff_id;
        $customer_id = $request->customer_id;
        $product_id = $request->product_id;
        $data = Sales::join("staff", "sales.staff_id", "staff.id")
            ->join("customers", "sales.customer_id", "customers.id")
            ->join("sales_items", "sales_items.sales_id", "sales.id")
            ->join("products", "products.id", "sales_items.product_id")
            ->join("product_groups", "products.group", "product_groups.id")
            ->where('sales.trash', 0)
            ->when($from_date, function (Builder $query, $from_date) {
                $formatted_date = Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d');
                $query->whereDate('sales.date', '>=', $formatted_date);
            })
            ->when($to_date, function (Builder $query, $to_date) {
                $formatted_date = Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d');
                $query->whereDate('sales.date', '<=', $formatted_date);
            })
            ->when($product_group_id, function (Builder $query, int $product_group_id) {
                $query->where('product_groups.id', $product_group_id);
            })
            ->when($staff_id, function (Builder $query, int $staff_id) {
                $query->where('staff.id', $staff_id);
            })
            ->when($customer_id, function (Builder $query, int $customer_id) {
                $query->where('customers.id', $customer_id);
            })
            ->when($product_id, function (Builder $query, int $product_id) {
                $query->where('products.id', $product_id);
            })
            ->select(
                DB::raw("DISTINCT DATE_FORMAT(sales.date,'%d-%m-%Y') as sale_date"),
                "staff.fullname as staff",
                "customers.fullname as customer",
                "products.code as product",
                "products.sale_price as original_sale_price",
                "sales_items.quality as quantity",
                "sales_items.get_more as bonus",
                "sales_items.discount as discount",
                "sales_items.price as final_price",
            )
            ->get();
        $totalTarget = Target::sum('target');
        $salesWithoutBN = 0;
        $salesWithBN = 0;
        $salesWithoutDiscount = 0;
        $totalProduct = 0;

        foreach ($data as $da) {
            $da->thisTTPriceWithBN = $da->original_sale_price * $da->quantity + $da->original_sale_price * $da->quantity * $da->bonus * 0.01;

            $da->thisTTPriceWithoutBN = ($da->original_sale_price * $da->quantity - $da->original_sale_price * $da->quantity * $da->bonus * 0.01) - ($da->original_sale_price * $da->quantity - $da->original_sale_price * $da->quantity * $da->bonus * 0.01) * $da->discount * 0.01;

            $salesWithBN += $da->original_sale_price * $da->quantity + $da->original_sale_price * $da->quantity * $da->bonus;

            $salesWithoutBN += $da->original_sale_price * $da->quantity - $da->original_sale_price * $da->quantity * $da->bonus * 0.01;

            $salesWithoutDiscount += $da->original_sale_price * $da->quantity - $da->original_sale_price * $da->quantity * $da->discount * 0.01;

            $totalProduct += $da->quantity;
        }
        $response = [
            "staffs" => $staffs,
            "data" => $data,
            "salesWithBN" => $salesWithBN,
            "salesWithoutBN" => $salesWithoutBN,
            "salesWithoutDiscount" => $salesWithoutDiscount,
            "totalTarget" => $totalTarget,
            "reachTargetPercent" => number_format($salesWithBN / $totalTarget * 100, 0),
            "totalProduct" => $totalProduct,
        ];
        return response()->json($response, 200);
    }
    public function importSales(Request $request)
    {
        $products = Products::select('id', 'name')->get();
        $productGroup = Product_Group::select('id', 'group_name')->get();
        $product_group_id = $request->product_group_id;
        $product_id = $request->product_id;
        $data = Purchase_item::join('purchases', 'purchase_items.purchases_id', 'purchases.id')
            ->join('products', 'purchase_items.product_id', 'products.id')
            ->join('product_groups', 'products.group', 'product_groups.id')
            ->where('purchases.trash', 0)
            ->when($product_group_id, function (Builder $query, int $product_group_id) {
                $query->where('product_groups.id', $product_group_id);
            })
            ->when($product_id, function (Builder $query, int $product_id) {
                $query->where('products.id', $product_id);
            })
            ->select(
                DB::raw("DISTINCT DATE_FORMAT(purchases.date,'%d-%m-%Y') as date"),
                "products.code as code",
                DB::raw("SUM(purchase_items.quality) as quantity"),
                "purchase_items.get_more as bonus",
                "purchase_items.price as price",
                "purchase_items.discount as discount",
                "product_groups.id as product_group_id",
            )
            ->groupBy(
                'products.code',
                'purchases.date',
                'purchase_items.get_more',
                'purchase_items.price',
                'purchase_items.discount',
                'product_groups.id',
            )
            ->get();
        $target = TargetPurchase::join('purchases', 'target_purchases.date', 'purchases.date')
            ->where('purchases.trash', 0)
            ->join('purchase_items', 'purchase_items.purchases_id', 'purchases.id')
            ->select(DB::raw("SUM(purchase_items.quality) as quantity"), 'target_purchases.target as target')
            ->groupBy('target_purchases.date', 'target_purchases.target')
            ->get();
        $quantity = 0;
        $purchase_target = 0;
        $reached = 0;
        foreach ($target as $ta) {
            $quantity += $ta->quantity;
            $purchase_target += $ta->target;
        }
        $reached = $quantity / $purchase_target * 100;
        $salesWithoutBN = 0;
        $saleWithBN = 0;
        foreach ($data as $da) {
            $da->thisTTPrice = $da->price * $da->quantity;
            $salesWithoutBN += $da->thisTTPrice - $da->thisTTPrice * $da->bonus * 0.01;
            $saleWithBN += $da->thisTTPrice + $da->thisTTPrice * $da->bonus * 0.01;
        }
        $response = [
            "products" => $products,
            "productGroup" => $productGroup,
            "data" => $data,
            "saleWithBN" => $saleWithBN,
            "salesWithoutBN" => $salesWithoutBN,
            "totalProduct" => $quantity,
            "finaltarget" => $purchase_target,
            "reached" => $reached > 0 ? number_format($reached, 0) : 0,
        ];
        return response()->json($response, 200);
    }
    public function guaranteeProduct(Request $request)
    {
        $staffs = Staff::select('id', 'fullname')->get();
        $productGroups = Product_Group::select('id', 'group_name')->get();
        $customers = Customer::select('id', 'fullname')->get();
        $products = Products::select('id', 'name')->get();
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $product_id = $request->product_id;
        $product_group_id = $request->product_group_id;
        $staff_id = $request->staff_id;
        $data = Sales_item::join("sales", "sales_items.sales_id", "sales.id")
            ->join("products", "sales_items.product_id", "products.id")
            ->join("product_groups", "products.group", "product_groups.id")
            ->join("staff", "sales.staff_id", "staff.id")
            ->join("customers", "sales.customer_id", "customers.id")
            ->where('products.guarantee', '>', 0)
            ->where('sales.trash', 0)
            ->when($from_date, function (Builder $query, $from_date) {
                $formatted_date = Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d');
                $query->whereDate('sales.date', '>=', $formatted_date);
            })
            ->when($to_date, function (Builder $query, $to_date) {
                $formatted_date = Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d');
                $query->whereDate('sales.date', '<=', $formatted_date);
            })
            ->when($customer_id, function (Builder $query, int $customer_id) {
                $query->where('customers.id', $customer_id);
            })
            ->when($product_id, function (Builder $query, int $product_id) {
                $query->where('products.id', $product_id);
            })
            ->when($product_group_id, function (Builder $query, int $product_group_id) {
                $query->where('product_groups.id', $product_group_id);
            })
            ->when($staff_id, function (Builder $query, int $staff_id) {
                $query->where('staff.id', $staff_id);
            })
            ->select(
                DB::raw("DISTINCT DATE_FORMAT(sales.date,'%d-%m-%Y') as date"),
                "sales_items.id as sale_code",
                "staff.fullname as staffname",
                "customers.fullname as customername",
                "products.code as code",
                "sales_items.quality as quantity",
            )
            ->get();
        $response = [
            "staffs" => $staffs,
            "productGroups" => $productGroups,
            "customers" => $customers,
            "products" => $products,
            "data" => $data,
        ];
        return response()->json($response, 200);
    }
    public function payRequest(Request $request)
    {
        $target = Target::select(
            DB::raw("DISTINCT DATE_FORMAT(from_date, '%m-%Y') as month"),
            DB::raw("SUM(target) as target")
        )
            ->groupBy('month')->get();
        $target_purchase = TargetPurchase::select(
            DB::raw("DISTINCT DATE_FORMAT(date, '%m-%Y') as month"),
            DB::raw("SUM(target) as target_purchase")
        )
            ->groupBy('month')->get();
        $target_purchase_reach = Purchase::join('purchase_items', 'purchase_items.purchases_id', 'purchases.id')
            ->select(
                DB::raw("DISTINCT DATE_FORMAT(purchases.date, '%m-%Y') as month"),
                DB::raw("SUM(quality*price) as target_purchase_reach")
            )
            ->groupBy('month')->get();
        $target_reach = Sales::join('sales_items', 'sales_items.sales_id', 'sales.id')
            ->select(
                DB::raw("DISTINCT DATE_FORMAT(sales.date, '%m-%Y') as month"),
                DB::raw("SUM(quality*price) as target_reach")
            )
            ->groupBy('month')->get();
        $paid = Paid::select(
            DB::raw("DISTINCT DATE_FORMAT(paids.date, '%m-%Y') as month"),
            DB::raw("SUM(money) as tranfer")
        )
            ->groupBy('month')->get();
        $debt = Sales::join('staff', 'sales.staff_id', 'staff.id')
            ->select(
                DB::raw("DISTINCT DATE_FORMAT(sales.date, '%m-%Y') as month"),
                DB::raw("SUM(staff.debt) as debt")
            )
            ->groupBy('month')->get();
        $mergedCollection = $target->concat($target_purchase)
            ->concat($target_purchase_reach)
            ->concat($target_reach)
            ->concat($paid)
            ->concat($debt)
            ->groupBy('month')
            ->map(function ($items) {
                $mergedData = array_merge(...$items->toArray());
                $mergedData['purchase_reach_percent'] = number_format($mergedData['target_purchase_reach'] / $mergedData['target_purchase'] * 100, 0);
                $mergedData['commission'] = $mergedData['purchase_reach_percent'] >= 100 ? $mergedData['target_purchase_reach'] * 0.05 : $mergedData['target_purchase_reach'] * 0.03;
                $mergedData['debt'] = -$mergedData['debt'];
                $mergedData['total'] = $mergedData['debt'] + $mergedData['commission'] + $mergedData['tranfer'];
                return $mergedData;
            });
        $response = [
            'data' => $mergedCollection->values()->all(),
        ];
        return response()->json($response, 200);
    }
}
