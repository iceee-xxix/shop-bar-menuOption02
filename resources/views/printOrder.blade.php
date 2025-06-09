<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ใบเสร็จรับเงิน</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 5px 0;
            color: black;
            background: #ffffff;
            /* ชัดเจนว่าให้พื้นหลังขาว */
        }

        .receipt {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            /* จัดให้อยู่กึ่งกลางแนวนอน */
            background: #ffffff;
            /* border: 1px solid black; */
            padding: 10px;
            border-radius: 5px;
        }

        .receipt h2 {
            text-align: center;
            margin-top: 5px;
            font-size: 38px;
            margin-bottom: 0px;
            font-weight: bold;
            color: black;
        }

        .receipt span {
            font-weight: bold;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 1px;
        }

        .header .info,
        .header .tax-label {
            display: table-cell;
            vertical-align: top;
        }

        .header .info {
            text-align: left;
        }

        .header .tax-label {
            text-align: right;
            font-weight: bold;
            color: black;
        }

        .info p {
            margin: 4px 0;
            font-size: 26px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 10px;
            font-size: 26px;
        }

        th:nth-child(1),
        td:nth-child(1) {
            text-align: left;
            width: 60%;
        }

        th:nth-child(2),
        td:nth-child(2) {
            text-align: center;
            width: 10%;
        }

        th:nth-child(3),
        td:nth-child(3) {
            text-align: right;
            width: 30%;
        }

        .total {
            text-align: right;
            font-weight: bold;
            color: black;
            border-top: 2px solid #000;
            margin-top: 20px;
            padding-top: 12px;
            font-size: 18px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            color: black;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div id="print-area">
        <div class="receipt">
            <h2><span>{{$config->name}}</span></h2>
            <center>
                <?= $qr ?>
            </center>
            <div class="header">
                <div class="info">
                    <p><strong>เลขที่โต้ะ #{{$table->table_number}}</strong></p>
                    <p>วันที่: <?= date('Y-m-d H:i:s') ?></p>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order as $rs)
                    <tr>
                        <td>
                            <div>{{ $rs['menu']->name }}</div>
                            @foreach($rs['option'] as $option)
                            <div style="font-size: 12px; color: #6b7280;">+ {{$option['option']->type}}</div>
                            @endforeach
                        </td>
                        <td><?= $rs->quantity ?></td>
                        <td><?= number_format($rs->price, '2') ?> ฿</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="total">Total: <?= number_format($pay, '2') ?> ฿</p>
        </div>
    </div>
</body>

</html>
<script>
    window.print();
</script>