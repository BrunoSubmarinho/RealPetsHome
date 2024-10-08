<?php
// Inicia a sessão para usar as variáveis de sessão
session_start();

// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php';

// Verifica se o ID do animal foi passado na URL
if (isset($_GET['id'])) {
    $id = $_GET['id']; // Obtém o ID do animal

    // Prepara a consulta SQL para buscar os dados do animal a ser editado
    $sql = "SELECT * FROM animais WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $animal = $result->fetch_assoc();
    $stmt->close();

    // Verifica se o animal foi encontrado
    if (!$animal) {
        echo "Animal não encontrado.";
        exit();
    }

    // Verifica se o formulário foi enviado (método POST)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Obtém os dados enviados pelo formulário
        $nome = $_POST['nome'];
        $especie = $_POST['especie'];
        $idade = $_POST['idade'];
        $descricao = $_POST['descricao'];
        $genero = $_POST['genero'];
        $opcao_compra = $_POST['opcao_compra'];
        $preco = $_POST['preco'];

        // Inicializa a variável $foto_sql como string vazia para uso posterior
        $foto_sql = "";

        // Verifica se uma nova foto foi enviada
        if ($_FILES['foto']['name']) {
            $foto = $_FILES['foto']['name'];
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($foto);
        
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto_sql = ", foto=?";
            } else {
                echo "Erro ao mover o arquivo da foto.";
            }
        }

        $sql = "UPDATE animais SET nome=?, especie=?, idade=?, descricao=?, genero=?, opcao_compra=?, preco=? $foto_sql WHERE id=?";
        $stmt = $conn->prepare($sql);

        if ($foto_sql) {
            $stmt->bind_param("ssisssssi", $nome, $especie, $idade, $descricao, $genero, $opcao_compra, $preco, $foto, $id);
        } else {
            $stmt->bind_param("ssissssi", $nome, $especie, $idade, $descricao, $genero, $opcao_compra, $preco, $id);
        }

        if ($stmt->execute()) {
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Erro: " . $stmt->error;
        }

        $stmt->close();
    }
} else {
    echo "ID do animal não foi passado na URL.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Animal</title>
    <link rel="stylesheet" href="../css/editar_animal.css">
    <script>
        // Função para confirmar salvamento
        function confirmarSalvamento(event) {
            if (!confirm("Tem certeza que deseja salvar as alterações?")) {
                event.preventDefault(); // Cancela a ação se o usuário não confirmar
            }
        }

        // Adiciona o evento ao carregar a página
        window.onload = function() {
            document.querySelector('form').addEventListener('submit', confirmarSalvamento);
        }
    </script>
</head>
<body>
    <form action="" method="POST" enctype="multipart/form-data">
        <h2>Editar Animal</h2>
        <label>Nome:</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($animal['nome']); ?>" required>
        <label>Espécie:</label>
        <input type="text" name="especie" value="<?php echo htmlspecialchars($animal['especie']); ?>" required>
        <label>Idade:</label>
        <input type="number" name="idade" value="<?php echo htmlspecialchars($animal['idade']); ?>" required>
        <label>Descrição:</label>
        <input type="text" name="descricao" value="<?php echo htmlspecialchars($animal['descricao']); ?>" required>
        <label>Gênero:</label>
        <input type="text" name="genero" value="<?php echo htmlspecialchars($animal['genero']); ?>" required>
        <label>Opção de Compra:</label>
        <input type="text" name="opcao_compra" value="<?php echo htmlspecialchars($animal['opcao_compra']); ?>" required>
        <label>Preço:</label>
        <input type="number" step="0.01" name="preco" value="<?php echo htmlspecialchars($animal['preco']); ?>" required>
        <label>Foto:</label>
        <input type="file" name="foto">
        <?php if ($animal['foto']) { ?>
            <img src="../uploads/<?php echo htmlspecialchars($animal['foto']); ?>" alt="Foto" style="width: 100px;">
        <?php } ?>
        <input type="submit" value="Salvar">
        <a href="admin_dashboard.php">Voltar</a>
    </form>
</body>
</html>
