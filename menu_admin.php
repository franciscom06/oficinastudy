<?php
session_start();
include('conexao_bd.php');

$nivel_util = $_SESSION['utilizador_nivel'];


// Definir filtros (usando GET para persistência)
$search = $_GET['search'] ?? '';
$estado_filter = $_GET['estado_filter'] ?? '';
$atribuido_a_filter = $_GET['atribuido_a_filter'] ?? '';

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    /* Container principal */
    body, html {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow-y: auto;
        overflow-x: hidden;
    }  

    .container_principal {
        font-family: "Arial", sans-serif;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        background-image: url("https://getwallpapers.com/wallpaper/full/1/9/b/1492084-popular-i-am-the-best-wallpaper-2560x1600-for-4k-monitor.jpg");
        height: 100vh; 
        overflow: auto;
    }

    /* Container de conteúdo */
    .container {
        background-color: #1c2633;
        color: #e0e0e0;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(255, 255, 255, 0.3);
        max-width: 100%;
        margin-top: 20px;
        width: 90%;
        box-sizing: border-box;
        overflow: hidden;
        animation: slideUp 1s ease-out;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }  

    /* Título */
    h1 {
        text-align: center;
        font-size: 32px;
        color: #0081d5;
        margin-bottom: 30px;
        opacity: 0;
        animation: fadeIn 1s ease-out 0.5s forwards; /* Animação suave de fadeIn */
    }

    /* Botões */
    .btn-add, .btn-add_, .btn-add__ {
        margin-left:5px;
        text-decoration: none;
        background-color: #0081d5;
        color: white;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s; /* Animação ao passar o rato */
    }

    .btn-add:hover, .btn-add_:hover, .btn-add__:hover {
        box-shadow: 0 4px 20px rgba(255, 255, 255, 0.2);
        background-color: #015791;
        transform: scale(1.1); /* Aumenta o tamanho quando passa o rato */
    }

    .buttons-container {
    display: flex;
    gap: 10px;
    justify-content: left;
    margin-bottom: 20px;
    opacity: 0;
    animation: fadeIn 1s ease-out 0.5s forwards; /* Animação de FadeIn */
}

.buttons-container a {
    text-decoration: none;
    background-color: #0081d5;
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s;
    margin-bottom: 10px; /* Adicionado para espaçamento */
    white-space: nowrap; /* Evita que o texto quebre em várias linhas */
}



    /* Tabela de atualizações */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
        animation: fadeIn 1s ease-out 2s forwards; /* Animação suave para tabela */
    }

    th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #3a4756;
    /* Evita quebra de linha */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    /* Remova o max-width para manter o conteúdo completo em telas pequenas */
    max-width: 250px;
}

/* Para telas menores, remova a restrição de largura */
@media (max-width: 600px) {
    th, td {
        max-width: none;
    }
}
    th {
        background-color: #0081d5;
        color: white;
    }

    td {
        position: relative;
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #3a4756;
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }   

    /* Animação de FadeIn */
    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    /* Animação de SlideUp */
    @keyframes slideUp {
        0% { transform: translateY(20px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .search-container {
            flex-direction: column;
            align-items: center;
        }

        .search-container input, .search-container select, .search-container button {
            width: 100%;
            margin-bottom: 10px;
        }
    }
    </style>
</head>
<body>
    <div class="container_principal">
        <div class="container">
            <div class="buttons-container">
                <a href="logout.php" class="btn-add__">Logout</a>
                <?php 
                if ($nivel_util == 1) { 
                    echo '<a href="alterar_niveis.php" class="btn-add">Alterar Níveis</a>';
                } 
                ?>               
            </div>

            <h1>Painel de Administração</h1>

            <div class="table-responsive">
                <table id="updatesTable">
                    <thead>
                        <tr>
                            <th>Titulo</th>
                            <th>Descrição</th>
                            <!-- adicionar o que quiser a lista -->

                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</body>
</html>