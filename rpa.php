<?php

function fetchPage($url)
{
    // Inicializa o cURL
    $ch = curl_init();

    // Configura o cURL
    curl_setopt($ch, CURLOPT_URL, $url); // Define a URL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desabilita a verificação de certificados SSL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna o resultado como uma string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Habilita o redirecionamento
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');  // Reutiliza cookies de sessão
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

    // Executa o cURL
    $html = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro no cURL: <br>' . curl_error($ch);
    }

    curl_close($ch);

    return $html;
}

function login($usuario, $senha, $html)
{
    $url_login = "https://sistema.7vitrines.com/login";

    preg_match('/<meta name="csrf-token" content="(.+?)"/', $html, $matches);
    $csrf_token = $matches[1] ?? '';

    if (!$csrf_token) {
        echo 'Não foi possível encontrar o token CSRF.';
        exit;
    }

    $ch = curl_init();

    // Configura o cURL para a requisição POST de login
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
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

    $newHtml = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro no cURL ao realizar login: <br>' . curl_error($ch);
    }

    curl_close($ch);

    return $newHtml;
}

function extractTableToJson($html)
{
    $dom = new DOMDocument();
    @$dom->loadHTML($html); // Suprime warnings por HTML inválido

    // Usa XPath para localizar as linhas da tabela
    $xpath = new DOMXPath($dom);
    $rows = $xpath->query('//table/tbody/tr');

    $data = [];

    // Laço para percorrer as linhas
    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');

        $data[] = [
            'ID' => $cols->item(0)->nodeValue,
            'Empresa' => $cols->item(1)->nodeValue,
            'Endereço' => $cols->item(2)->nodeValue,
            'Referência' => $cols->item(3)->nodeValue,
        ];
    }

    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// Processa a requisição POST com as credenciais do usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        echo "Usuário e senha são obrigatórios!";
        exit;
    }

    $html = fetchPage("https://sistema.7vitrines.com/login");

    if (strpos($html, 'Sair') === false) { // Se não tem a opção "Sair", não está logado
        $html = login($usuario, $senha, $html);
    }

    // Após o login, redireciona para a página /teste
    $html = fetchPage("https://sistema.7vitrines.com/teste");

    $json = extractTableToJson($html);
    echo $json;
}