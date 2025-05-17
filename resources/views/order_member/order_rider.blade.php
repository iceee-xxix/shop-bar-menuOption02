@extends('admin.layout')
@section('style')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    svg {
        width: 100%;
    }
</style>
@endsection
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 col-md-12 order-1 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h6>รายการออเดอร์ทั้งหมด</h6>
                        <hr>
                    </div>
                    <div class="card-body">
                        <table id="myTable" class="display table-responsive">
                            <thead>
                                <tr>
                                    <th class="text-center">สั่งออนไลน์</th>
                                    <th class="text-left">ชื่อลูกค้า</th>
                                    <th class="text-center">ยอดราคา</th>
                                    <th class="text-left">หมายเหตุ</th>
                                    <th class="text-left">วันที่สั่ง</th>
                                    <th class="text-center">สถานะ</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-detail">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายละเอียดออเดอร์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="body-html">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script>
    var language = '{{asset("assets/js/datatable-language.js")}}';
    $(document).ready(function() {
        $("#myTable").DataTable({
            language: {
                url: language,
            },
            processing: true,
            scrollX: true,
            order: [
                [4, 'desc']
            ],
            ajax: {
                url: "{{route('MemberorderRiderlistData')}}",
                type: "post",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },

            columns: [{
                    data: 'flag_order',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'name',
                    class: 'text-left',
                    width: '15%'
                },
                {
                    data: 'total',
                    class: 'text-center',
                    width: '10%'
                },
                {
                    data: 'remark',
                    class: 'text-left',
                    width: '15%'
                },
                {
                    data: 'created',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'status',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'action',
                    class: 'text-center',
                    width: '15%',
                    orderable: false
                },
            ]
        });
    });
</script>
<script>
    $(document).on('click', '.modalShow', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            type: "post",
            url: "{{ route('listOrderDetailRider') }}",
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#modal-detail').modal('show');
                $('#body-html').html(response);
            }
        });
    });

    $(document).on('click', '.cancelOrderSwal', function(e) {
        var id = $(this).data('id');
        $('#modal-detail').modal('hide');
        Swal.fire({
            title: "ต้องการยกเลิกออเดอร์นี้ใช่หรือไม่",
            showCancelButton: true,
            confirmButtonText: "ยืนยัน",
            denyButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.ajax({
                    type: "post",
                    url: "{{ route('cancelOrder') }}",
                    data: {
                        id: id
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status == true) {
                            $('#myTable').DataTable().ajax.reload(null, false);
                            Swal.fire(response.message, "", "success");
                        } else {
                            Swal.fire(response.message, "", "error");
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.cancelMenuSwal', function(e) {
        var id = $(this).data('id');
        $('#modal-detail').modal('hide');
        Swal.fire({
            title: "ต้องการยกเลิกเมนูนี้ใช่หรือไม่",
            showCancelButton: true,
            confirmButtonText: "ยืนยัน",
            denyButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.ajax({
                    type: "post",
                    url: "{{ route('cancelMenu') }}",
                    data: {
                        id: id
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status == true) {
                            $('#myTable').DataTable().ajax.reload(null, false);
                            Swal.fire(response.message, "", "success");
                        } else {
                            Swal.fire(response.message, "", "error");
                        }
                    }
                });
            }
        });
    });
</script>
@endsection