<!-- Conexão com a base de dados, criei no PhpMyAdmin  -->
<!-- ---------------------------------------------------->

<?php
$servidor = "localhost";
$utilizador = "root";
$passe = "";
$banco = "oficina_study";

$conn = new mysqli($servidor, $utilizador, $passe, $banco);


if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>