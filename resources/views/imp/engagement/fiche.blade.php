{{-- TITLE: Fiche d'Engagement --}}
@extends('imp.layout')

@section('content')

    <div class="titre-doc">
        بطاقة التزام رقم {{ $op->Num_operation }}<br>
        <span style="font-size: 14px;">FICHE D'ENGAGEMENT N° {{ $op->Num_operation }}</span>
    </div>

    {{-- Cadre Informations Générales --}}
    <table class="bordered">
        <tr>
            <td width="20%" class="text-bold bg-light">الميزانية / Budget</td>
            <td>{{ $op->budget->designation ?? '' }} ({{ $op->EXERCICE }})</td>
            <td width="20%" class="text-bold bg-light">التاريخ / Date</td>
            <td width="20%">{{ \Carbon\Carbon::parse($op->Creer_le)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="text-bold bg-light">القسم / Section</td>
            <td colspan="3">
                <strong>{{ $op->section->Num_section }}</strong> - {{ $op->section->NOM_section }}
            </td>
        </tr>
    </table>

    <br>

    {{-- Cadre Imputation (Nomenclature) --}}
    <div style="border: 1px solid #000; padding: 10px; background: #f9f9f9; page-break-inside: avoid;">
        <div style="text-align: center; border-bottom: 1px solid #ccc; margin-bottom: 10px; padding-bottom: 5px;">
            <strong>التعيين المحاسبي (IMPUTATION BUDGÉTAIRE)</strong>
        </div>
        <table style="width: 100%; border: none;">
            <tr>
                <td width="25%" class="text-bold" style="border: none;">Chapitre (الباب)</td>
                <td style="border: none;">: <strong>{{ $op->obj1->Num }}</strong> - {{ $op->obj1->designation }}</td>
            </tr>
            @if($op->obj2)
            <tr>
                <td class="text-bold" style="border: none; padding-left: 20px;">Article (المادة)</td>
                <td style="border: none;">: <strong>{{ $op->obj2->Num }}</strong> - {{ $op->obj2->designation }}</td>
            </tr>
            @endif
            @if($op->obj3)
            <tr>
                <td class="text-bold" style="border: none; padding-left: 40px;">S/Article</td>
                <td style="border: none;">: <strong>{{ $op->obj3->Num }}</strong> - {{ $op->obj3->designation }}</td>
            </tr>
            @endif
            @if($op->obj4)
            <tr>
                <td class="text-bold" style="border: none; padding-left: 60px;">Rubrique</td>
                <td style="border: none;">: <strong>{{ $op->obj4->Num }}</strong> - {{ $op->obj4->designation }}</td>
            </tr>
            @endif
            @if($op->obj5)
            <tr>
                <td class="text-bold" style="border: none; padding-left: 80px;">S/Rubrique</td>
                <td style="border: none;">: <strong>{{ $op->obj5->Num }}</strong> - {{ $op->obj5->designation }}</td>
            </tr>
            @endif
        </table>
    </div>

    <br>

    {{-- Cadre Détails Opération --}}
    <table class="bordered">
        <tr>
            <td width="25%" class="text-bold bg-light">المستفيد / Bénéficiaire</td>
            <td>
                @if($op->bonCommande && $op->bonCommande->fournisseur)
                    {{ $op->bonCommande->fournisseur->Nom }} {{ $op->bonCommande->fournisseur->Societe }}
                @else
                    {{ $op->Beneficiaire ?? '---' }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="text-bold bg-light">موضوع النفقة / Objet</td>
            <td style="padding: 15px;">
                {{ $op->designation }}
                
                {{-- Affichage du lien BC si existant --}}
                @if($op->bonCommande)
                    <br>
                    <span style="font-size: 11px; color: #333; font-style: italic;">
                        (Référence : Bon de Commande N° {{ $op->bonCommande->Num_bon }} du {{ \Carbon\Carbon::parse($op->bonCommande->date)->format('d/m/Y') }})
                    </span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="text-bold bg-light">المبلغ / Montant</td>
            <td class="text-bold text-center" style="font-size: 16px; background-color: #e8f5e9;">
                {{ number_format($op->Mont_operation, 2, ',', ' ') }} DA
            </td>
        </tr>
    </table>

    <br><br><br>

    {{-- Cadre Signatures --}}
    <div style="width: 100%; page-break-inside: avoid;">
        <div style="width: 45%; float: right; border: 1px solid #000; height: 120px; padding: 5px; text-align:center;">
            <strong>إمضاء الآمر بالصرف</strong><br>
            <span style="font-size: 10px;">Signature de l'Ordonnateur</span>
        </div>
        <div style="width: 45%; float: left; border: 1px solid #000; height: 120px; padding: 5px; text-align:center;">
            <strong>تأشيرة المراقب المالي</strong><br>
            <span style="font-size: 10px;">Visa du Contrôle Financier</span>
            
            @if($op->cf && $op->cf->VISA_cf)
                <div style="margin-top: 25px; border: 2px solid #000; padding: 5px; display: inline-block; transform: rotate(-2deg);">
                    <strong>VISA N° {{ $op->cf->VISA_cf }}</strong><br>
                    Le {{ \Carbon\Carbon::parse($op->cf->Date_retour)->format('d/m/Y') }}
                </div>
            @endif
        </div>
    </div>

@endsection