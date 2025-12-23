<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impression - Engagement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 20mm;
            background: white;
        }
        
        @media print {
            body {
                padding: 10mm;
            }
            .no-print {
                display: none;
            }
        }
        
        .titre-doc {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            padding: 10px;
            border-bottom: 2px solid #000;
        }
        
        table.bordered {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table.bordered td, 
        table.bordered th {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        .text-bold {
            font-weight: bold;
        }
        
        .bg-light {
            background-color: #f5f5f5;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* Bouton d'impression */
        .print-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-btn:hover {
            background: #0056b3;
        }
        
        @page {
            size: A4;
            margin: 15mm;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        🖨️ Imprimer
    </button>
    
    <div class="content">
        @yield('content')
    </div>
    
    <script>
        // Auto-print option (décommenter si désiré)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>