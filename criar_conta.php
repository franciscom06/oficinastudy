<?php
session_start();
include('conexao_bd.php');

$erro_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome_utilizador = $_POST['nome_utilizador'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $palavra_passe = $_POST['palavra_passe'];
    $palavra_passe_ = $_POST['confirmar_palavra_passe'];
    $ano_escolaridade = $_POST['ano_escolaridade'];

    if ($palavra_passe_ !== $palavra_passe) {
        $erro_msg = "As palavras-passe não são iguais!";
    }

    $sql_check = "SELECT * FROM `login` WHERE email = ? OR username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $email, $username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $erro_msg = "Erro: O e-mail ou nome de utilizador já está a ser utilizado. Por favor, utilize outro!";
    }

    if (empty($erro_msg)) {

        $palavra_passe = $_POST['palavra_passe'];

        $nivel = "3";

        $sql = "INSERT INTO `login` (nome_utilizador, username, email, palavra_passe, nivel, ano_escolaridade) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $nome_utilizador, $username, $email, $palavra_passe, $nivel, $ano_escolaridade);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $erro_msg = "Erro ao registrar: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .container_principal {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-image: url("https://static.vecteezy.com/system/resources/previews/002/375/687/non_2x/school-stationary-background-free-vector.jpg");
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-card {
            text-decoration:none;
            color:white;
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            border-radius: 0.5rem;
            background-color: #2596be;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        /* Posiciona o ícone do olho sempre visível */
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-10%);
            cursor: pointer;
            font-size: 22px;
            color: white;
        }

        .login-link {
            margin-left: 5px;
            display: block;
            color:rgb(0, 0, 0);
            text-decoration: none;
        }
        
        .login-link:hover {
            color: #272727;
        }
    </style>
</head>
<body>
    <div class="container-fluid container_principal">
        <div class="login-card">
            <h2 class="text-center mb-4">REGISTAR</h2>

            <?php if (!empty($erro_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $erro_msg; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="nome_utilizador" class="form-label">Nome Real</label>
                    <input type="text" class="form-control" id="nome_utilizador" name="nome_utilizador" placeholder="Introduza o nome real" required />
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Nome utilizador</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Introduza o Nome de utilizador" required />
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Introduza o Email" required />
                </div>
                <div class="mb-3 position-relative">
                    <label for="palavra_passe" class="form-label">Palavra-passe</label>
                    <input type="password" class="form-control" id="palavra_passe" name="palavra_passe" placeholder="Introduza a Palavra-passe" required>
                    <span class="toggle-password" onclick="togglePassword()">🙈</span> <!-- Começa com o macaco -->
                </div>
                <div class="mb-3">
                    <label for="confirmar_palavra_passe" class="form-label">Confirmar Palavra-passe</label>
                    <input type="password" class="form-control" id="confirmar_palavra_passe" name="confirmar_palavra_passe" placeholder="Confirme a Palavra-passe" required />
                </div>
                <div class="mb-3">
                <label for="ano_escolaridade" class="form-label">Ano de Escolaridade</label>
                    <select class="form-control" id="ano_escolaridade" name="ano_escolaridade" required>
                        <option value="">Selecione o Ano</option>
                        <option value="7">7º Ano</option>
                        <option value="8">8º Ano</option>
                        <option value="9">9º Ano</option>
                        <option value="10">10º Ano</option>
                        <option value="11">11º Ano</option>
                        <option value="12">12º Ano</option>
                    </select>
                </div>
                <button type="submit" class="btn btn btn-dark w-100">Confirmar</button>
                <div class="d-flex align-items-center mt-3">
                    Já tens uma conta?  <a href="login.php" class="login-link">Faz login aqui!</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Fade-in na página ao carregar
        document.body.style.opacity = 0;
        document.body.style.transition = "opacity 0.8s ease-in-out";
        setTimeout(() => document.body.style.opacity = 1, 200);

        // 2. Efeito nos botões ao passar o mouse
        document.querySelectorAll("button, .btn-add, .btn-add_").forEach(button => {
            button.addEventListener("mouseover", () => {
                button.style.transform = "scale(1.05)";
                button.style.transition = "transform 0.2s ease-in-out";
            });
            button.addEventListener("mouseout", () => {
                button.style.transform = "scale(1)";
            });
        });

        // 3. Animação no campo de upload
        const fileInput = document.getElementById("anexo");
        if (fileInput) {
            fileInput.addEventListener("mouseover", () => {
                fileInput.style.backgroundColor = "#e3f2fd";
                fileInput.style.transition = "background-color 0.3s ease";
            });
            fileInput.addEventListener("mouseout", () => {
                fileInput.style.backgroundColor = "#fafafa";
            });
        }

        // 4. Animação de slide no menu (se existir um menu lateral)
        const menuButton = document.getElementById("menu-toggle"); // Suponha que tenha um botão de menu
        const menu = document.getElementById("menu"); // Suponha que tenha um menu

        if (menuButton && menu) {
            menu.style.transform = "translateX(-100%)"; // Esconder menu no início
            menu.style.transition = "transform 0.5s ease-in-out";
            
            menuButton.addEventListener("click", () => {
                if (menu.style.transform === "translateX(0%)") {
                    menu.style.transform = "translateX(-100%)";
                } else {
                    menu.style.transform = "translateX(0%)";
                }
            });
        }
    });


        // Função para mostrar/ocultar senha com ícone de olho animado
        function togglePassword() {
            var input = document.getElementById("palavra_passe");
            var icon = document.querySelector(".toggle-password");

            if (input.type === "password") {
                input.type = "text";  // Torna a senha visível
                icon.innerHTML = "🐵"; // Ícone de mostrar a senha
            } else {
                input.type = "password";  // Torna a senha oculta
                icon.innerHTML = "🙈"; // Ícone de esconder a senha
            }
        }    

</script>


</body>
</html>
