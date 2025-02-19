<?php
session_start();
include('conexao_bd.php');
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Níveis</title>
    <style>

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;        
        }

        /* Container principal */
        .container_principal {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            width: 100%;
            font-family: "Arial", sans-serif;
            min-height: 100vh;
            justify-content: center;
            background-image: url("https://getwallpapers.com/wallpaper/full/1/9/b/1492084-popular-i-am-the-best-wallpaper-2560x1600-for-4k-monitor.jpg");
        }

        /* Container centralizado */
        .container {
            background-color: #1c2633;
            color: #e0e0e0;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 100%;
            margin-top: 20px;
            width: 90%;
            box-sizing: border-box;
            overflow: hidden;
            animation: slideUp 1s ease-out; /* Animação de subida do container */
        }

        h1 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 20px;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #007bff;
            color: #fff;
        }

        table tr:nth-child(even) {
            background-color: #3a4756;
        }

        .btn-back {
            display: inline-block;
            background-color: #0081d5;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

    </style>
</head>
<body>
    <div class="container_principal">
        <div class="container">
            <h1>Alterar Níveis dos utilizadores</h1>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Nível Atual</th>
                        <th>Alterar Nível</th>
                        <th>Ações</th>
                    </tr>
                </thead>
            </table>
            <a href="menu_admin.php" class="btn-back">Voltar ao Painel</a>
        </div>
    </div>
</body>
</html>
