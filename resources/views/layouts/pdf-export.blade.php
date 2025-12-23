<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #2c3e50; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #777; font-size: 10px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        
        th { 
            background-color: #2c3e50; 
            color: white; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 10px;
        }
        
        tr:nth-child(even) { background-color: #f9f9f9; }

        .footer { 
            position: fixed; 
            bottom: 0; 
            width: 100%; 
            text-align: center; 
            font-size: 9px; 
            color: #aaa; 
            border-top: 1px solid #eee; 
            padding-top: 5px; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>G-STOCK BUDGET - Édité le {{ date('d/m/Y à H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    {{-- On boucle sur les colonnes sélectionnées --}}
                    @foreach($row->toArray() as $key => $cell)
                        {{-- On ignore les colonnes inutiles (comme created_at si non demandé) --}}
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Document généré automatiquement par le système G-Stock.
    </div>
</body>
</html>