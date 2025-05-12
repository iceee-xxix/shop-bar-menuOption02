<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Categories_member;
use Illuminate\Http\Request;

class CategoriesMember extends Controller
{
    public function memberCategory()
    {
        $data['function_key'] = __FUNCTION__;
        return view('member.category', $data);
    }

    public function membercategorylistData()
    {
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];
        $category = Categories_member::get();

        if (count($category) > 0) {
            $info = [];
            foreach ($category as $rs) {
                $action = '<a href="' . route('memberCategoryEdit', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="แก้ไข"><i class="bx bx-edit-alt"></i></a>
                <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger deleteCategory" title="ลบ"><i class="bx bxs-trash"></i></button>';
                $info[] = [
                    'name' => $rs->name,
                    'icon' => $rs->icon,
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

    public function memberCategoryCreate()
    {
        $data['function_key'] = 'memberCategory';
        return view('member.categories_create', $data);
    }

    public function memberCategorySave(Request $request)
    {
        $input = $request->input();
        if (!isset($input['id'])) {
            $category = new Categories_member();
            $category->name = $input['name'];
            if ($category->save()) {
                return redirect()->route('memberCategory')->with('success', 'บันทึกรายการเรียบร้อยแล้ว');
            }
        } else {
            $category = Categories_member::find($input['id']);
            $category->name = $input['name'];
            if ($category->save()) {
                return redirect()->route('memberCategory')->with('success', 'บันทึกรายการเรียบร้อยแล้ว');
            }
        }
        return redirect()->route('memberCategory')->with('error', 'ไม่สามารถบันทึกข้อมูลได้');
    }

    public function memberCategoryEdit($id)
    {
        $function_key = 'memberCategory';
        $info = Categories_member::find($id);

        return view('member.categories_edit', compact('info', 'function_key'));
    }

    public function memberCategoryDelete(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'ลบข้อมูลไม่สำเร็จ',
        ];
        $id = $request->input('id');
        if ($id) {
            $delete = Categories_member::find($id);
            if ($delete->delete()) {
                $data = [
                    'status' => true,
                    'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
                ];
            }
        }

        return response()->json($data);
    }
}
