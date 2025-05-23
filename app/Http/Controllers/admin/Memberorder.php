<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Orders;
use App\Models\OrdersDetails;
use App\Models\Table;
use App\Models\User;
use App\Models\UsersCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PromptPayQR\Builder;

class Memberorder extends Controller
{

    public function Memberorder()
    {
        $data['function_key'] = 'Memberorder';
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
            $check = 0;
            foreach ($order as $rs) {
                $categoryId = UsersCategories::where('users_id', Session::get('user')->id)->value('categories_id');
                $orders = Orders::where('table_id', $rs->table_id)
                    ->where('status', 1)
                    ->get();
                foreach ($orders as $rc) {
                    $orderDetailsGrouped = OrdersDetails::join('menus', 'orders_details.menu_id', '=', 'menus.id')
                        ->where('orders_details.order_id', $rc->id)
                        ->where('menus.categories_member_id', $categoryId)
                        ->select('orders_details.*')
                        ->with('menu', 'option')
                        ->where('orders_details.status', 1)
                        ->get()
                        ->groupBy('menu_id');
                    if ($orderDetailsGrouped->isNotEmpty()) {
                        $check = 1;
                    }
                }
                if ($check == 1) {
                    if ($rs->status == 1) {
                        $status = '<button class="btn btn-sm btn-primary">ออเดอร์ใหม่</button>';
                    }
                    $flag_order = '<button class="btn btn-sm btn-success">สั่งหน้าร้าน</button>';
                    $action = '<button data-id="' . $rs->table_id . '" type="button" class="btn btn-sm btn-outline-primary modalShow">รายละเอียด</button>
                    <a href="' . route('printOrder', $rs->table_id) . '" target="_blank" type="button" class="btn btn-sm btn-outline-primary">ปริ้นออเดอร์</a>';
                    $info[] = [
                        'flag_order' => $flag_order,
                        'table_id' => $rs->table_id,
                        'remark' => $rs->remark,
                        'status' => $status,
                        'created' => $this->DateThai($rs->created_at),
                        'action' => $action
                    ];
                }
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
        $categoryId = UsersCategories::where('users_id', Session::get('user')->id)->value('categories_id');
        $orders = Orders::where('table_id', $request->input('id'))
            ->where('status', 1)
            ->get();
        $info = '';
        foreach ($orders as $order) {
            $orderDetailsGrouped = OrdersDetails::join('menus', 'orders_details.menu_id', '=', 'menus.id')
                ->where('orders_details.order_id', $order->id)
                ->where('menus.categories_member_id', $categoryId)
                ->select('orders_details.*')
                ->with('menu', 'option')
                ->where('orders_details.status', 1)
                ->get()
                ->groupBy('menu_id');
            if ($orderDetailsGrouped->isNotEmpty()) {
                $info .= '<div class="mb-3">';
                $info .= '<div class="row"><div class="col d-flex align-items-end"><h5 class="text-primary mb-2">เลขออเดอร์ #: ' . $order->id . '</h5></div></div>';
                foreach ($orderDetailsGrouped as $details) {
                    $menuName = optional($details->first()->menu)->name ?? 'ไม่พบชื่อเมนู';
                    $info .= '<ul class="list-group mb-1 shadow-sm rounded">';
                    foreach ($details as $detail) {
                        $option = $detail->option;
                        $optionType = $option ? $menuName . ' ' . $option->type : 'ไม่มีตัวเลือก';
                        $priceTotal = number_format($detail->quantity * $detail->price, 2);

                        $info .= '<li class="list-group-item d-flex bd-highlight align-items-center">';
                        $info .= '<div class="flex-grow-1 bd-highlight"><small class="text-muted">' . htmlspecialchars($optionType) . '</small> — <span class="fw-medium">จำนวน ' . $detail->quantity . '</span></div>';
                        $info .= '<button class="btn btn-sm btn-primary bd-highlight">' . $priceTotal . ' บาท</button>';
                        if ($detail->status == 1) {
                            $info .= '<button href="javascript:void(0)" class="btn btn-sm btn-info bd-highlight m-1 updatestatusMenu" data-id="' . $detail->id . '">อัพเดทสถานะ</button>';
                        }
                        $info .= '</li>';
                    }
                    $info .= '</ul>';
                }
                $info .= '</div>';
            }
        }
        echo $info;
    }

    public function MemberorderRider()
    {
        $data['function_key'] = 'MemberorderRider';
        $data['rider'] = User::where('is_rider', 1)->get();
        $data['config'] = Config::first();
        return view('order_member.order_rider', $data);
    }

    public function MemberorderRiderlistData()
    {
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];
        $order = Orders::select('orders.*', 'users.name')
            ->join('users', 'orders.users_id', '=', 'users.id')
            ->where('table_id')
            ->whereNot('users_id')
            ->whereNot('address_id')
            ->orderBy('created_at', 'desc')
            ->get();

        if (count($order) > 0) {
            $info = [];
            foreach ($order as $rs) {
                $check = 0;
                $categoryId = UsersCategories::where('users_id', Session::get('user')->id)->value('categories_id');
                $orderDetailsGrouped = OrdersDetails::join('menus', 'orders_details.menu_id', '=', 'menus.id')
                    ->where('orders_details.order_id', $rs->id)
                    ->where('menus.categories_member_id', $categoryId)
                    ->select('orders_details.*')
                    ->with('menu', 'option')
                    ->get()
                    ->groupBy('menu_id');
                if ($orderDetailsGrouped->isNotEmpty()) {
                    $check = 1;
                }
                if ($check == 1) {
                    $status = '';
                    if ($rs->status == 1) {
                        $status = '<button class="btn btn-sm btn-primary">กำลังทำอาหาร</button>';
                    }
                    $flag_order = '<button class="btn btn-sm btn-warning">สั่งออนไลน์</button>';
                    $action = '<button data-id="' . $rs->id . '" type="button" class="btn btn-sm btn-outline-primary modalShow m-1">รายละเอียด</button>
                    <a href="' . route('printOrderRider', $rs->id) . '" target="_blank" type="button" class="btn btn-sm btn-outline-primary">ปริ้นออเดอร์</a>';
                    $info[] = [
                        'flag_order' => $flag_order,
                        'name' => $rs->name,
                        'total' => $rs->total,
                        'remark' => $rs->remark,
                        'status' => $status,
                        'created' => $this->DateThai($rs->created_at),
                        'action' => $action
                    ];
                }
            }
            $data = [
                'data' => $info,
                'status' => true,
                'message' => 'success'
            ];
        }
        return response()->json($data);
    }

    public function printOrderAdmin($id)
    {
        $config = Config::first();
        $getOrder = Orders::where('table_id', $id)->whereIn('status', [1, 2])->get();
        $order_id = array();
        $qr = '';
        if ($config->promptpay != '') {
            $qr = Builder::staticMerchantPresentedQR($config->promptpay)->toSvgString();
            $qr = str_replace('<svg', '<svg width="150" height="150"', $qr);
            $qr = '<div style="width: 150px; height: 150px; overflow: hidden;">
                        <div style="transform: scale(1.25); transform-origin: center;">
                            ' . $qr . '
                        </div>
                    </div>';
        } elseif ($config->image_qr != '') {
            $qr = '<img width="150px" src="' . url('storage/' . $config->image_qr) . '">';
        }
        foreach ($getOrder as $rs) {
            $order_id[] = $rs->id;
        }
        $categoryId = UsersCategories::where('users_id', Session::get('user')->id)->value('categories_id');
        $order = OrdersDetails::whereIn('order_id', $order_id)
            ->with('menu', 'option')
            ->get();
        $table = Table::find($id);
        return view('printOrder', compact('config', 'order', 'qr', 'table'));
    }

    public function printOrderAdminCook($id)
    {
        $config = Config::first();
        $getOrder = Orders::where('table_id', $id)->where('status', 1)->get();
        $order_id = array();
        $qr = '';
        if ($config->promptpay != '') {
            $qr = Builder::staticMerchantPresentedQR($config->promptpay)->toSvgString();
            $qr = str_replace('<svg', '<svg width="150" height="150"', $qr);
            $qr = '<div style="width: 150px; height: 150px; overflow: hidden;">
                        <div style="transform: scale(1.25); transform-origin: center;">
                            ' . $qr . '
                        </div>
                    </div>';
        } elseif ($config->image_qr != '') {
            $qr = '<img width="150px" src="' . url('storage/' . $config->image_qr) . '">';
        }
        foreach ($getOrder as $rs) {
            $order_id[] = $rs->id;
        }
        $categoryId = UsersCategories::where('users_id', Session::get('user')->id)->value('categories_id');
        $order = OrdersDetails::whereIn('order_id', $order_id)
            ->with('menu', 'option')
            ->get();
        $table = Table::find($id);
        return view('printOrder', compact('config', 'order', 'qr', 'table'));
    }

    public function printOrder($id)
    {
        $config = Config::first();
        $getOrder = Orders::where('table_id', $id)->where('status', 1)->get();
        $order_id = array();
        $qr = '';
        if ($config->promptpay != '') {
            $qr = Builder::staticMerchantPresentedQR($config->promptpay)->toSvgString();
            $qr = str_replace('<svg', '<svg width="150" height="150"', $qr);
            $qr = '<div style="width: 150px; height: 150px; overflow: hidden;">
                        <div style="transform: scale(1.25); transform-origin: center;">
                            ' . $qr . '
                        </div>
                    </div>';
        } elseif ($config->image_qr != '') {
            $qr = '<img width="150px" src="' . url('storage/' . $config->image_qr) . '">';
        }
        foreach ($getOrder as $rs) {
            $order_id[] = $rs->id;
        }
        $categoryId = UsersCategories::where('users_id', Session::get('user')->id)->value('categories_id');
        $order = OrdersDetails::join('menus', 'orders_details.menu_id', '=', 'menus.id')
            ->whereIn('order_id', $order_id)
            ->with('menu', 'option')
            ->where('menus.categories_member_id', $categoryId)
            ->get();
        return view('printOrder', compact('config', 'order', 'qr'));
    }

    public function printOrderRider($id)
    {
        $config = Config::first();
        $order_id = array();
        $qr = '';
        if ($config->promptpay != '') {
            $qr = Builder::staticMerchantPresentedQR($config->promptpay)->toSvgString();
            $qr = str_replace('<svg', '<svg width="150" height="150"', $qr);
            $qr = '<div style="width: 150px; height: 150px; overflow: hidden;">
                        <div style="transform: scale(1.25); transform-origin: center;">
                            ' . $qr . '
                        </div>
                    </div>';
        } elseif ($config->image_qr != '') {
            $qr = '<img width="150px" src="' . url('storage/' . $config->image_qr) . '">';
        }
        $getOrder = Orders::where('id', $id)->where('status', 1)->get();
        foreach ($getOrder as $rs) {
            $order_id[] = $rs->id;
        }
        $categoryId = UsersCategories::where('users_id', Session::get('user')->id)->value('categories_id');
        $order = OrdersDetails::join('menus', 'orders_details.menu_id', '=', 'menus.id')
            ->whereIn('order_id', $order_id)
            ->with('menu', 'option')
            ->where('menus.categories_member_id', $categoryId)
            ->get();
        return view('printOrder', compact('config', 'order', 'qr'));
    }
}
