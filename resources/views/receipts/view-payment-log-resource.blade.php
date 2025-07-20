<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лист пациента</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            background: #fff;
            padding: 30px;
            color: #000;
        }

        .form-container {
            max-width: 500px;
            margin: auto;
            border: 1px solid #ccc;
            padding: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        section {
            margin-bottom: 25px;
        }

        h4 {
            margin-bottom: 10px;
            font-weight: bold;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .line {
            border-bottom: 1px solid #000;
            height: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th, table td {
            border: 1px solid #000;
            text-align: center;
            padding: 5px;
        }

        .signature {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
        }
        .print-button {
            position: absolute;
            top: 20px;
            right: 40px;
        }

        .print-button button {
            padding: 6px 12px;
            font-size: 14px;
            background-color: #389EAD;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        @media print {
            .print-button {
                display: none;
            }
        }

    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">Печать</button>
    </div>
    <div class="form-container">
        <h2>ЛИСТ ПАЦИЕНТА — САНАТОРНОЕ ЛЕЧЕНИЕ</h2>

        <section>
            <h4>1. Основные данные пациента</h4>
            <div class="row">
                <span>Ф.И.О :{{$payment->patient->full_name}}</span>
            </div>
            <div class="row">
                <span>Год рождения: {{$payment->patient->birth_date}}</span>
            </div>
            <div class="row">
                <span>Адрес: {{$payment->patient->district->name}} {{$payment->patient->address}}</span>
            </div>
            <div class="row">
                <span>Дата регистрации: {{$payment->medicalHistory->created_at->format('Y-m-d')}}</span>
            </div>
        </section>

        <section>
            <h4>2. Жалобы пациента</h4>
            <div class="line"></div>
        </section>

        <section>
            <h4>3. Назначенные процедуры</h4>
            <table>
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Название процедуры</th>
                        <th>Кол-во раз</th>
                        <th>Стоимость (за 1)</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payment->procedurePayments as $procedurePayment)
                        @foreach ($procedurePayment->procedurePaymentDetails as $key=> $detail)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{$detail->procedure->name}}</td>
                                <td>{{$detail->sessions}}</td>
                                <td>{{number_format($detail->price,0,',',' ')}}</td>
                                <td>{{number_format($detail->price * $detail->sessions,0,',',' ')}} сум</td>
                            </tr>
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="5">нет процедур</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        
        <section>
            <h4>4. Назначенные анализы</h4>
            <table>
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Название анализ</th>
                        <th>Кол-во раз</th>
                        <th>Стоимость (за 1)</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payment->labTestPayments as $labTestPayment)
                        @foreach ($labTestPayment->labTestPaymentDetails as $key=> $detail)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{$detail->labTest->name}}</td>
                                <td>{{$detail->sessions}}</td>
                                <td>{{number_format($detail->price,0,',',' ')}}</td>
                                <td>{{number_format($detail->price * $detail->sessions,0,',',' ')}}</td>
                            </tr>
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="5">нет анализы</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section>
            <h4>5. Проживание и питание</h4>
            <div class="row">
                <span>Тип палаты: {{$payment->accommodationPayments->sum('ward_day')}} день * {{number_format($payment->medicalHistory->accommodation->tariff_price,0,',',' ')}}</span>
                <span>Сумма: {{ number_format($payment->accommodationPayments->sum('ward_day')*$payment->medicalHistory->accommodation->tariff_price,0,',',' ')}}</span>
            </div>
            <div class="row">
                <span>Питание: {{$payment->accommodationPayments->sum('meal_day')}} день * {{number_format($payment->medicalHistory->accommodation->meal_price,0,',',' ')}}</span>
                <span>Сумма: {{ number_format($payment->accommodationPayments->sum('meal_day')*$payment->medicalHistory->accommodation->meal_price,0,',',' ')}}</span>
            </div>
        </section>

        <section>
            <h4>6. Финансовая информация</h4>
            <div class="row">
                <span>Общая сумма оплаты: {{number_format($payment->medicalHistory->getTotalCost(),0,',',' ')}}</span>
            </div>
            <div class="row">
                <span>Оплачено: {{number_format($payment->getTotalPaidAmount(),0,',',' ')}}</span>
            </div>
        </section>

        <section>
            <h4>7. Номер койки и место</h4>
            <div class="row">
                <span>Палата №: {{$payment->medicalHistory->accommodation->ward->name}}</span>
                {{-- <span>Койка №: {{$payment->medicalHistory->accommodation->bed->number}}</span> --}}
            </div>
        </section>

        <section class="signature">
            <div>
                Подпись пациента 
            </div>
            <div>
                Подпись врача/администратора 
            </div>
        </section>
    </div>
</body>
</html>
