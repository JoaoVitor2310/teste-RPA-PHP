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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Eaglecred</title>
</head>

<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="container text-center mx-auto p-4 bg-primary" style="max-width: 350px;">

            <form action="./eagle.php" method="post">
                <div class="d-flex flex-column align-items-center gap-2">
                    <h1 class="text-white">Automação Eaglecred</h1>
                    <input class="row form-control" type="text" name="email" placeholder="Email">
                    <div class="input-group">
                        <input id="password" class="form-control" type="password" name="password"
                            placeholder="Senha">
                        <button class="btn btn-outline "
                            style="border-color: var(--bs-tertiary-bg); color: var(--bs-tertiary-bg);" type="button"
                            onclick="togglePassword()">
                            <i id="toggleIcon" class="bi bi-eye"></i>
                        </button>
                    </div>
                    <textarea class="form-control mt-2" name="products" placeholder="Produtos (separados por vírgula)"
                        rows="4">NOME - 12,16</textarea>
                    <button type="submit" class="btn mt-2 form-control" style="background-color: white;">Enviar</button>
                </div>
            </form>

        </div>
    </div>


    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const icon = document.getElementById("toggleIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>