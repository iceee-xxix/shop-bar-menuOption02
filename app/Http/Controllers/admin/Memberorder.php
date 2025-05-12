<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Orders;
use App\Models\OrdersDetails;
use App\Models\User;
use App\Models\UsersCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Memberorder extends Controller
{

    public function Memberorder()
    {
        $data['function_key'] = 'order';
        $data['rider'] = User::where('is_rider', 1)->get();
        $data['config'] = Config::first();
        return view('order_member.order', $data);
    }

    public function MemberorderlistData()
    {
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];
        $order = DB::table('orders as o')
            ->select(
                'o.table_id',
                DB::raw('SUM(o.total) as total'),
                DB::raw('MAX(o.created_at) as created_at'),
                DB::raw('MAX(o.status) as status'),
                DB::raw('MAX(o.remark) as remark')
            )
            ->whereNot('table_id')
            ->groupBy('o.table_id')
            ->orderByDesc('created_at')
            ->where('status', 1)
            ->get();

        if (count($order) > 0) {
            $info = [];
            foreach ($order as $rs) {
                if ($rs->status == 1) {
                    $status = '<button class="btn btn-sm btn-primary">ออเดอร์ใหม่</button>';
                }
                $flag_order = '<button class="btn btn-sm btn-success">สั่งหน้าร้าน</button>';
                $action = '<button data-id="' . $rs->table_id . '" type="button" class="btn btn-sm btn-outline-primary modalShow m-1">รายละเอียด</button>';
                $info[] = [
                    'flag_order' => $flag_order,
                    'table_id' => $rs->table_id,
                    'remark' => $rs->remark,
                    'status' => $status,
                    'created' => $this->DateThai($rs->created_at),
                    'action' => $action
                ];
            }
            $data = [
                'data' => $info,
                'status' => true,
                'message' => 'success'
            ];
        }
        return response()->json($data);
    }

    function DateThai($strDate)
    {
        $strYear = date("Y", strtotime($strDate)) + 543;
        $strMonth = date("n", strtotime($strDate));
        $strDay = date("j", strtotime($strDate));
        $time = date("H:i", strtotime($strDate));
        $strMonthCut = array("", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
        $strMonthThai = $strMonthCut[$strMonth];
        return "$strDay $strMonthThai $strYear" . " " . $time;
    }

    public function MemberorderlistOrderDetail(Request $request)
    {
        $id = UsersCategories::where('users_id', Session::get('user')->id)->first()->categories_id;
        $orders = Orders::where('table_id', $request->input('id'))
            ->where('status', 1)
            ->get();
        $info = '';
        foreach ($orders as $rs) {
            $orderdetails = OrdersDetails::select('menu_id')
                ->join('menus', 'orders_details.menu_id', '=', 'menus.id')
                ->where('order_id', $rs->id)
                ->where('menus.categories_member_id', $id)
                ->groupBy('menu_id')
                ->get();
            $order_id = $rs->id;
            if (count($orderdetails) > 0) {
                foreach ($orderdetails as $key => $value) {
                    $order = OrdersDetails::where('order_id', $order_id)
                        ->where('menu_id', $value->menu_id)
                        ->with('menu', 'option')
                        ->get();
                    $info .= '<div class="card text-white bg-primary mb-3"><div class="card-body"><h5 class="card-title text-white">' . $order[0]['menu']->name . '</h5><p class="card-text">';
                    foreach ($order as $rs) {
                        $info .= '' . $rs['menu']->name . ' (' . $rs['option']->type . ') จำนวน ' . $rs->quantity . ' ราคา ' . ($rs->quantity * $rs->price) . ' บาท <br>';
                    }
                    $info .= '</p></div></div>';
                }
            }
        }
        echo $info;
    }
}
