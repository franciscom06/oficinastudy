<?php
session_start();
include('conexao_bd.php');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $palavra_passe = $_POST['palavra_passe'];

    $sql = "SELECT * FROM `login` WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $nivel = $user['nivel']; 
        $palavra_passe_guardada = $user['palavra_passe'];
  
        if ($palavra_passe == $palavra_passe_guardada) {
            $_SESSION['utilizador_id'] = $user['ID'];
            $_SESSION['utilizador_nome'] = $user['nome_utilizador'];
            $_SESSION['utilizador_email'] = $user['email'];
            $_SESSION['utilizador_nivel'] = $user['nivel'];

            error_log("Utilizador logado: " . $_SESSION['utilizador_nome']);

            if ($nivel == 1) {
                $_SESSION['admin'] = true; 
                header("Location: menu_admin.php");
                exit();
            } 
            if ($nivel == 2) {
                $_SESSION['professor'] = true; 
                header("Location: menu_professor.php");
                exit();
            } else {
                header("Location: index.html"); 
                exit();
            }
        } else {
            $erro_msg = "Palavra-passe incorreta.";
        }
    } else {
        $erro_msg = "E-mail não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Animação de Fade-in ao carregar */
        body {
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
        }

        .container_principal {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-image: url("https://static.vecteezy.com/system/resources/previews/002/375/687/non_2x/school-stationary-background-free-vector.jpg");
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-card {
            color: white;
            background-color: #2596be;
            width: 100%;
            max-width: 450px;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Animação no erro */
        .alert-danger {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
        }

        /* Efeito nos inputs */
        .form-control {
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            border-color: #007bff;
        }

        /* Botão de login com efeito */
        .btn-primary {
            transition: all 0.3s ease;
            background-color:rgb(0, 0, 0);
            border: none;
            padding: 10px;
        }

        .btn-primary:hover {
            background-color: #272727;
            transform: scale(1.05);
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

        /* Link de registro */
        .register-link {
            margin-left:5px;
            display: block;
            color:rgb(0, 0, 0);
            text-decoration: none;
        }

        .register-link:hover {
            color: #272727;
        }
    </style>
</head>
<body>

    <div class="container-fluid container_principal">
        <div class="login-card">
            <h2>LOGIN</h2>

            <?php if (!empty($erro_msg)): ?>
                <div class="alert alert-danger">
                    <?php echo $erro_msg; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="mb-3 position-relative">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Introduza o Email" required>
                </div>

                <div class="mb-3 position-relative">
                    <label for="palavra_passe" class="form-label">Palavra-passe</label>
                    <input type="password" class="form-control" id="palavra_passe" name="palavra_passe" placeholder="Introduza a Palavra-passe" required>
                    <span class="toggle-password" onclick="togglePassword()">🙈</span> <!-- Começa com o macaco -->
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
                <div class="d-flex align-items-center mt-3">
                    Ainda não tens uma conta? <a href="criar_conta.php" class="register-link">Regista-te aqui!</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Animação de Fade-in ao carregar
        document.addEventListener("DOMContentLoaded", function() {
            document.body.style.opacity = 1;
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