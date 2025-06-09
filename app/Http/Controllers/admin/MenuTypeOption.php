<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\MenuTypeOption as ModelsMenuTypeOption;
use Illuminate\Http\Request;

class MenuTypeOption extends Controller
{
    public function menuTypeOption($id)
    {
        $data['function_key'] = 'menu';
        $data['id'] = $id;
        return view('menuTypeOption.index', $data);
    }

    public function menuTypeOptionlistData(Request $request)
    {
        $id = $request->input('id');
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];
        $option = ModelsMenuTypeOption::where('menu_id', $id)->get();

        if (count($option) > 0) {
            $info = [];
            foreach ($option as $rs) {
                $option = '<a href="' . route('menuOption', $rs->id) . '" title="แก้ไข"><i class="bx bx-plus-circle"></i></a>';
                $action = '<a href="' . route('menuTypeOptionEdit', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="แก้ไข"><i class="bx bx-edit-alt"></i></a>
                <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger deleteMenu" title="ลบ"><i class="bx bxs-trash"></i></button>';
                $info[] = [
                    'name' => $rs->name,
                    'option' => $option,
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

    public function menuTypeOptionCreate($id)
    {
        $data['function_key'] = 'menu';
        $data['id'] = $id;
        return view('menuTypeOption.create', $data);
    }

    public function menuTypeOptionEdit($id)
    {
        $data['function_key'] = 'menu';
        $data['id'] = $id;
        $data['info'] = ModelsMenuTypeOption::find($id);
        return view('menuTypeOption.edit', $data);
    }

    public function menuTypeOptionSave(Request $request)
    {
        $input = $request->input();
        $option = new ModelsMenuTypeOption();
        $option->menu_id = $input['id'];
        $option->name = $input['name'];
        $option->is_selected = isset($input['is_selected']) ? 1 : 0;
        $option->amout = isset($input['is_selected']) ? $input['amout'] : 0;
        if ($option->save()) {
            return redirect()->route('menuTypeOption', $input['id'])->with('success', 'บันทึกรายการเรียบร้อยแล้ว');
        }
        return redirect()->route('menuTypeOption', $input['id'])->with('error', 'ไม่สามารถบันทึกข้อมูลได้');
    }

    public function menuTypeOptionUpdate(Request $request)
    {
        $input = $request->input();
        $option = ModelsMenuTypeOption::find($input['id']);
        $option->name = $input['name'];
        $option->is_selected = isset($input['is_selected']) ? 1 : 0;
        $option->amout = isset($input['is_selected']) ? $input['amout'] : 0;
        if ($option->save()) {
            return redirect()->route('menuTypeOption', $option->menu_id)->with('success', 'บันทึกรายการเรียบร้อยแล้ว');
        }
        return redirect()->route('menu')->with('error', 'ไม่สามารถบันทึกข้อมูลได้');
    }

    public function menuTypeOptionDelete(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'ลบข้อมูลไม่สำเร็จ',
        ];
        $id = $request->input('id');
        if ($id) {
            $delete = ModelsMenuTypeOption::find($id);
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
