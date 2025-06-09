@extends('admin.layout')
@section('style')
@endsection
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 col-md-12 order-1">
                <div class="row d-flex justify-content-center">
                    <div class="col-12">
                        <form action="{{route('menuTypeOptionSave')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card">
                                <div class="card-header">
                                    เพิ่มตัวเลือก
                                    <hr>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-2">
                                        <div class="col-md-12">
                                            <label for="name" class="form-label">ชื่อตัวเลือก : </label>
                                            <input type="text" class="form-control" id="name" name="name" required autocomplete="off">
                                        </div>
                                        <div class="col-md-12">
                                            <input class="form-check-input" type="checkbox" id="is_selected" name="is_selected">
                                            <label class="form-check-label" for="is_selected">
                                                ต้องการบังคับให้เลือก
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="amout" class="form-label">จำนวนที่ต้องการให้บังคับ : </label>
                                            <input type="text" class="form-control" id="amout" name="amout" autocomplete="off" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <span class="text-danger" style="font-size: 13px;">
                                                (ในกรณีที่ไม่ต้องการให้บังคับ ไม่ต้องใส่จำนวนและไม่ต้องบังคับให้เลือก)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="id" id="id" value="{{$id}}">
                                <div class="card-footer d-flex justify-content-end">
                                    <a href="{{route('menuTypeOption',$id)}}" class="btn btn-sm btn-outline-primary mx-1">ย้อนกลับ</a>
                                    <button type="submit" class="btn btn-sm btn-outline-success">บันทึก</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection