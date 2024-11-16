<?php

function fetchPage($url) // Função para fazer uma requisição GET para uma página
{

    // Configura o cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); // Define a URL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desabilita a verificação de certificados SSL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna o resultado como uma string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Habilita o redirecionamento
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');  // Armazena cookies recebidos em um arquivo
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt'); // Reutiliza cookies armazenados previamente

    // Executa o cURL
    $html = curl_exec($ch);

    if (curl_errno($ch)) { // Verifica se houve erro
        returnError('Erro no cURL: ' . curl_error($ch));
    }

    curl_close($ch); // Fecha o cURL

    return $html;
}

function login($usuario, $senha, $html) // Função para realizar o login
{
    $url_login = "https://sistema.7vitrines.com/login";

    preg_match('/<meta name="csrf-token" content="(.+?)"/', $html, $matches); // Encontra o token CSRF por um regex
    $csrf_token = $matches[1] ?? '';

    if (!$csrf_token) { // O token CSRF é utilizado para autenticar a requisição e garantir que ela venha de um cliente autorizado
        returnError('Não foi possível encontrar o token CSRF.');
    }

    // Configura o cURL para a requisição POST de login
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_login); // URL
    curl_setopt($ch, CURLOPT_POST, true); // Método será POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ // Insere os dados de login no corpo da requisição
        'email' => $usuario,
        'password' => $senha,
        '_token' => $csrf_token // Inclui o token CSRF
    ]));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Habilita o redirecionamento automático
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna o conteúdo da resposta como string
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desabilita a verificação de certificado SSL
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt'); // Armazena cookies recebidos em um arquivo
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt'); // Reutiliza cookies armazenados previamente
    
    $newHtml = curl_exec($ch); // Executa a requisição
    
    if (curl_errno($ch)) { // Verifica se houve erro
        returnError('Erro no cURL ao realizar login: ' . curl_error($ch));
    }
    
    curl_close($ch); // Fecha o cURL
    
    if (strpos($newHtml, 'These credentials do not match our records.') == true) { // Se aparecer essa mensagem, as credenciais estão incorretas
        returnError('Usuário ou senha incorretos');
    }

    return $newHtml; // Retorna o HTML
}

function extractTableToJson($html) // Função para extrair os dados da tabela e retornar como JSON
{
    $dom = new DOMDocument();
    @$dom->loadHTML($html); // Transforma a string HTML em um objeto DOM para ser manipulado 

    $xpath = new DOMXPath($dom); // Usa XPath para localizar as linhas da tabela
    $rows = $xpath->query('//table/tbody/tr');

    $data = []; // Array para armazenar os dados da tabela

    // Loop para percorrer as linhas e pegar os dados das colunas
    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td'); // Pega as colunas pelo nome da tag HTML

        $data[] = [ // Adiciona os dados da linha ao array
            'ID' => $cols->item(0)->nodeValue,
            'Empresa' => $cols->item(1)->nodeValue,
            'Endereço' => $cols->item(2)->nodeValue,
            'Referência' => $cols->item(3)->nodeValue,
        ];
    }

    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); // Converte o array para JSON
}

function returnError($message) // Função para retornar mensagens de erro em formato JSON
{
    echo json_encode(['error' => $message], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Processa a requisição POST com as credenciais do usuário
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($usuario) || empty($senha)) { // Verifica se o usuário enviou email e senha
        returnError('Usuário e senha são obrigatórios!');
    }

    $html = fetchPage("https://sistema.7vitrines.com/login"); // Acessa a página de login

    if (strpos($html, 'Sair') == false) { // Se não tem a opção "Sair", está deslogado
        $html = login($usuario, $senha, $html); // Realiza o login
    }

    $html = fetchPage("https://sistema.7vitrines.com/teste"); // Com o usuário logado, acessa a página /teste

    $json = extractTableToJson($html); // Extrai os dados da tabela e converte para JSON
    echo $json; // Envia os dados em formato Json para o cliente
}