<?php
session_start();

if (isset($_POST["form_type"]) && $_POST["form_type"] === 'produtos') {

    // 2 - Realizar login     
    login($_POST["email"], $_POST["password"]);

    // Validar se enviou produtos
    if ($_POST["products"] == "") {
        $_SESSION['error_message'] = 'Nenhum produto foi enviado. Tente novamente.';
        header("Location: index.php");
        exit();
    }

    // Separar o id dos produtos em um array
    $produtos = str_replace("NOME - ", "", $_POST["products"]);
    $produtos = explode(',', $produtos);

    // 2 - Permitir todos os usuários para o produto
    foreach ($produtos as $produto) {
        $produto = trim($produto);
        $produto = str_pad($produto, 6, "0", STR_PAD_LEFT);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://webcred.eaglecred.com.br/ajx/ajx_parametrizaproduto.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => false,  // Desabilitar verificação SSL
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_COOKIEJAR => 'cookies.txt',  // Salva os cookies recebidos
            CURLOPT_COOKIEFILE => 'cookies.txt', // Reutiliza os cookies armazenados
            CURLOPT_POSTFIELDS => '{
            "CodProduto": "' . $produto . '",
            "usuarios": [],
            "todosForamSelecionados": true,
            "process": "permitiremlote"
        }',
        ));

        $response = curl_exec($curl);
        // var_dump($response);
        // die;

        if (!$response) {
            $_SESSION['error_message'] = 'Erro ao autorizar usuários no produto: ' . $produto . '. Tente novamente.';
            header("Location: index.php");
            exit();
        }

        // var_dump($response);
        curl_close($curl);
    }
    $_SESSION['success_message'] = 'Usuários de produtos permitidos com sucesso.';
    header("Location: index.php");
    exit();
}

if (isset($_POST["form_type"]) && $_POST["form_type"] === 'csv') {

    // 1 - Realizar login
    login($_POST["email"], $_POST["password"]);

    // Pega informações do arquivo
    $arquivo = $_FILES["csv_file"];
    $nomeArquivo = $arquivo["name"];
    $caminhoTemporario = $arquivo["tmp_name"];

    if (pathinfo($nomeArquivo, PATHINFO_EXTENSION) !== "csv") {
        $_SESSION['error_message'] = 'Apenas arquivos .csv são permitidos.';
        header("Location: index.php");
        exit();
    }

    if (($handle = fopen($caminhoTemporario, "r")) !== false) {

        // Lê cada linha do CSV
        while (($dados = fgetcsv($handle, 1000, ";")) !== false) {
            $codigoEnviado = trim($dados[0]);
            $produtos = explode(',', trim($dados[1])); // Transforma os produtos em array para manipular

            foreach ($produtos as $produto) {
                $produto = trim($produto);  
                $produto = str_pad($produto, 6, "0", STR_PAD_LEFT);

                // Buscar produto pelo codigo
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://webcred.eaglecred.com.br/permissoesprodutousuario.php?codproduto=$produto",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_COOKIEJAR => 'cookies.txt',  // Salva os cookies recebidos
                    CURLOPT_COOKIEFILE => 'cookies.txt', // Reutiliza os cookies armazenados
                ));

                $response = curl_exec($curl);
                // echo $response;
                // die;

                if (!$response) {
                    $_SESSION['error_message'] = 'Erro ao autorizar usuários no produto: ' . $produto . '. Tente novamente.';
                    header("Location: index.php");
                    exit();
                }

                curl_close($curl);

                $dom = new DOMDocument();
                @$dom->loadHTML($response); // Usamos @ para suprimir possíveis warnings de HTML malformado

                $xpath = new DOMXPath($dom);

                $dados = [];

                // Encontra todas as linhas da tabela
                $rows = $xpath->query('//tr');

                foreach ($rows as $row) {
                    $checkbox = $xpath->query('.//input[@type="checkbox"]', $row)->item(0);
                    $cpf_cnpj = $checkbox ? $checkbox->getAttribute('value') : null;

                    $codigo = $xpath->query('.//td[4]', $row)->item(0);
                    $codigo = $codigo ? trim($codigo->nodeValue) : null;

                    // Se ambos os valores foram encontrados, adiciona ao array
                    if ($cpf_cnpj && $codigo) {
                        $dados[] = [
                            'cpf_cnpj' => $cpf_cnpj,
                            'codigo' => $codigo
                        ];
                    }
                }

                // Verifica se o código é igual (case insensitive) ou começa com $codigoEnviado (case insensitive)
                $resultados = array_filter($dados, function ($item) use ($codigoEnviado) {
                    return strcasecmp($item['codigo'], $codigoEnviado) === 0 || stripos($item['codigo'], $codigoEnviado) === 0;
                });

                $cpfs_cnpjs = array_column($resultados, 'cpf_cnpj');
                $cpfs_cnpjs_json = json_encode($cpfs_cnpjs);

                // Autorizar usuários
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://webcred.eaglecred.com.br/ajx/ajx_parametrizaproduto.php',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_SSL_VERIFYPEER => false,  // Desabilitar verificação SSL
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_COOKIEJAR => 'cookies.txt',  // Salva os cookies recebidos
                    CURLOPT_COOKIEFILE => 'cookies.txt', // Reutiliza os cookies armazenados
                    CURLOPT_POSTFIELDS => '{
                        "CodProduto": "' . $produto . '",
                        "usuarios": ' . $cpfs_cnpjs_json . ',
                        "todosForamSelecionados": false,
                        "process": "permitiremlote"
                        }',
                ));

                $response = curl_exec($curl);
                // die;

                if (!$response) {
                    $_SESSION['error_message'] = 'Erro ao autorizar usuários no produto: ' . $produto . '. Tente novamente.';
                    header("Location: index.php");
                    exit();
                }
            }
        }
        fclose($handle);
    } else {
        $_SESSION['error_message'] = 'Erro ao abrir o arquivo.';
        header("Location: index.php");
        exit();
    }

    $_SESSION['success_message'] = 'Usuários específicos de produtos permitidos com sucesso.';
    header("Location: index.php");
    exit();
}



function login($email, $password)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://webcred.eaglecred.com.br/ajx/login.php',
        CURLOPT_RETURNTRANSFER => true, // 
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_COOKIEJAR => 'cookies.txt',  // Salva os cookies recebidos
        CURLOPT_COOKIEFILE => 'cookies.txt', // Reutiliza os cookies armazenados
        CURLOPT_SSL_VERIFYPEER => false,  // Desabilitar verificação SSL
        CURLOPT_POSTFIELDS => array(
            'l' => $email,
            's' => $password,
            'r' => '1',
            // 'v' => $hashv,
        ),
    ));

    $response = curl_exec($curl);
    // exit($response);

    // Validar
    if (!$response) {
        echo 'Erro cURL: ' . curl_error($curl);
        $_SESSION['error_message'] = 'Erro inesperado. Tente novamente.';
        header("Location: index.php");
        exit();
    }

    if ($response == 'erro|Login ou senha invalida') {
        $_SESSION['error_message'] = 'Usuário ou senha incorretos. Tente novamente.';
        header("Location: index.php");
        exit();
    }
}
