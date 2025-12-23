{{-- TITLE: Liste des Engagement --}}
@extends('imp.layout')

@section('content')
    <h2 class="text-center">ÉTAT DES ENGAGEMENTS - {{ $annee }}</h2>

    <table class="bordered">
        <thead style="background-color: #ddd;">
            <tr>
                <th width="10%">N°</th>
                <th width="10%">Date</th>
                <th>Section</th>
                <th>Imputation</th>
                <th>Objet</th>
                <th width="15%">Montant</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($engagements as $op)
                <tr>
                    <td class="text-center">{{ $op->Num_operation }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($op->Creer_le)->format('d/m/Y') }}</td>
                    <td>{{ $op->section->Num_section }}</td>
                    <td>
                        {{ $op->obj1->Num }}
                        @if($op->obj2)/{{ $op->obj2->Num }}@endif
                    </td>
                    <td>{{ $op->designation }}</td>
                    <td class="text-right text-bold">{{ number_format($op->Mont_operation, 2, ',', ' ') }}</td>
                </tr>
                @php $total += $op->Mont_operation; @endphp
            @endforeach
            <tr style="background-color: #eee;">
                <td colspan="5" class="text-left text-bold">TOTAL GÉNÉRAL</td>
                <td class="text-right text-bold">{{ number_format($total, 2, ',', ' ') }} DA</td>
            </tr>
        </tbody>
    </table>
@endsection