<?php

function acessarLoginPage()
{
    $url_login = "https://sistema.7vitrines.com/login";

    // Inicializa o cURL
    $ch = curl_init();

    // Configura o cURL para login
    curl_setopt($ch, CURLOPT_URL, $url_login); // Define a URL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desabilita a verificação de certificados SSL, pois estava impedindo de acessar o site
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna o resultado como uma string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Habilita o redirecionamento

    // Caso queira considerar o usuário já logado, basta utilizar esses cookies
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');  // Armazena cookies de sessão
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt'); // Reutiliza cookies

    // Executa o curl
    $html = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo 'Erro no cURL de página login: <br>' . curl_error($ch);
    }
    
    curl_close($ch);
    
    return $html;
}

function realizarLogin($usuario, $senha, $html)
{
    $url_login = "https://sistema.7vitrines.com/login";

    preg_match('/<meta name="csrf-token" content="(.+?)"/', $html, $matches);
    $csrf_token = $matches[1] ?? '';

    if (!$csrf_token) {
        echo 'Não foi possível encontrar o token CSRF.';
        exit;
    }

    $ch = curl_init();

    // Configura o cURL para a segunda requisição (POST) para enviar os dados de login
    curl_setopt($ch, CURLOPT_URL, $url_login); // URL de login
    curl_setopt($ch, CURLOPT_POST, true); // Define o método como POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $usuario,
        'password' => $senha,
        '_token' => $csrf_token // Inclui o token CSRF
    ]));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Habilita o redirecionamento automático
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Reutiliza cookies de sessão
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

    // Executa a requisição POST para login
    $newHtml = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro no cURL de realizar login: <br>' . curl_error($ch);
    }

    // Fecha o cURL
    curl_close($ch);

    echo 'Login realizado com sucesso';
    return $newHtml;
}

// Processa a requisição POST com as credenciais do usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        echo "Usuário e senha são obrigatórios!";
        exit;
    }

    $html = acessarLoginPage();

    if (strpos($html, 'Sair') == false) { // Se não tem o botão de sair, está deslogado
        echo "Não está logado!<br>";
        $html = realizarLogin($usuario, $senha, $html); // realiza o login
    }

    echo $html;

    exit;
}