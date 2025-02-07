<?php
session_start();  // Inicie a sessão

// Verifique se há uma mensagem de erro e a exiba
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automação Eaglecred</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./jquery.loadingModal.css">

</head>

<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="container text-center mx-auto p-4" style="max-width: 350px; background-color: #1D81C0;">
            <!-- Campos de login (fora dos formulários) -->
            <div class="d-flex flex-column align-items-center gap-2 mb-2">
                <h1 class="text-white">Automação Eaglecred</h1>
                <input id="email" class="row form-control" type="text" placeholder="Usuario">
                <div class="input-group">
                    <input id="password" class="form-control" type="password" placeholder="Senha">
                    <button class="btn btn-outline" style="border-color: var(--bs-tertiary-bg); color: var(--bs-tertiary-bg);" type="button" onclick="togglePassword()">
                        <i id="toggleIcon" class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Formulário 1: Autorizar todos os usuários de produto -->
            <form action="./eagle.php" method="post" onsubmit="copyCredentials(this); showLoadingModal();">
                <div class="d-flex flex-column align-items-center gap-2">
                    <h4 class="text-white">Autorizar todos os usuários de produto:</h4>
                    <textarea class="form-control mt-2" name="products" placeholder="Produtos (separados por vírgula)" rows="4"></textarea>
                    <button type="submit" class="btn mt-2 form-control" style="background-color: white;">Enviar</button>
                </div>
                <!-- Campos ocultos para usuário e senha -->
                <input type="hidden" name="email">
                <input type="hidden" name="password">
                <input type="hidden" name="form_type" value="produtos">
            </form>

            <br>

            <!-- Formulário 2: Autorizar usuários específicos de produtos (upload de CSV) -->
            <form action="./eagle.php" method="post" enctype="multipart/form-data" onsubmit="copyCredentials(this); showLoadingModal();">
                <div class="d-flex flex-column align-items-center gap-2">
                    <h4 class="text-white">Autorizar usuários específicos de produtos:</h4>
                    <div id="drop-area" class="drop-zone">
                        <p>Arraste e solte aqui ou clique para selecionar o arquivo</p>
                        <input type="file" name="csv_file" id="fileInput" accept=".csv" hidden>
                    </div>
                    <p id="file-name" class="text-white"></p>
                    <button type="submit" class="btn mt-2 form-control" style="background-color: white;">Enviar CSV</button>
                </div>
                <!-- Campos ocultos para usuário e senha -->
                <input type="hidden" name="email">
                <input type="hidden" name="password">
                <input type="hidden" name="form_type" value="csv">
            </form>
        </div>
    </div>

    <script>
        // Função para copiar os valores dos campos de login para os campos ocultos
        function copyCredentials(form) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            form.querySelector('input[name="email"]').value = email;
            form.querySelector('input[name="password"]').value = password;
        }

        // Função para alternar a visibilidade da senha
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Lógica para o upload de arquivo
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('file-name');

        dropArea.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                fileName.textContent = `Arquivo selecionado: ${file.name}`;
            }
        });

        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.style.backgroundColor = 'transparent';
        });

        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.style.backgroundColor = 'transparent';
            const file = e.dataTransfer.files[0];
            if (file) {
                fileInput.files = e.dataTransfer.files;
                fileName.textContent = `Arquivo selecionado: ${file.name}`;
            }
        });

        function showLoadingModal(mensagem = null) {

            $('body').loadingModal({
                text: mensagem === null ? 'Carregando...' : mensagem
            });

            var delay = function(ms) {
                    return new Promise(function(r) {
                        setTimeout(r, ms)
                    })
                },
                time = 30000;

            delay(time)
                .then(function() {
                    $('body').loadingModal('animation', 'rotatingPlane');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('animation', 'wave');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('animation', 'wanderingCubes');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('animation', 'spinner');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('animation', 'chasingDots');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('animation', 'threeBounce');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('animation', 'circle');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('animation', 'cubeGrid');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('animation', 'fadingCircle');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('animation', 'foldingCube');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('color', 'black').loadingModal('text', 'Done :-)');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('hide');
                    return delay(time);
                })
                .then(function() {
                    $('body').loadingModal('destroy');
                });

        }

        function hideLoadingModal() {
            $('body').loadingModal('destroy');

            return true;

        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="//code.jquery.com/jquery-3.1.1.slim.min.js"></script>
    <script src="./jquery.loadingModal.js"></script>

</body>

</html>

<style>
    .drop-zone {
        border: 2px solid white;
        padding: 20px;
        text-align: center;
        width: 100%;
        cursor: pointer;
        color: white;
        transition: background-color 0.3s;
    }

    .drop-zone:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }
</style>